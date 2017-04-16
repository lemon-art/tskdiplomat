<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

$orderSumAmount = $_POST["orderSumAmount"];
$orderSumCurrencyPaycash = $_POST["orderSumCurrencyPaycash"];
$orderSumBankPaycash = $_POST["orderSumBankPaycash"];
$action = $_POST["action"];
$orderCreatedDatetime = $_POST["orderCreatedDatetime"];
$paymentType = $_POST["paymentType"];
$orderId = IntVal($_POST["customerNumber"]);
$paymentId = IntVal($_POST["orderNumber"]);
$invoiceId = $_POST["invoiceId"];
$md5 = $_POST["md5"];
$paymentDateTime = $_POST["paymentDateTime"];

$bCorrectPayment = true;

/** @var \Bitrix\Sale\Order $order */
$order = Order::load($orderId);

if(!$order)
{
	$bCorrectPayment = False;
	$code = "200"; //неверные параметры
	$techMessage = "ID заказа неизвестен.";
}

$payment = $order->getPaymentCollection()->getItemById($paymentId);
if (!$payment)
{
	$bCorrectPayment = False;
	$code = "200"; //неверные параметры
	$techMessage = "ID оплаты неизвестен.";
}

$arOrder = $order->getFieldValues();

if ($bCorrectPayment)
	CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], '', array(), $payment->getFieldValues());

$sum = CSalePaySystemAction::GetParamValue("SHOULD_PAY");
$shopId = CSalePaySystemAction::GetParamValue("SHOP_ID");
$scid = CSalePaySystemAction::GetParamValue("SCID");
$customerNumber = CSalePaySystemAction::GetParamValue("ORDER_ID");
$shopPassword = CSalePaySystemAction::GetParamValue("SHOP_KEY");
$changePayStatus =  trim(CSalePaySystemAction::GetParamValue("CHANGE_STATUS_PAY"));
$head = "";

if(strlen($shopPassword) <=0 )
	$bCorrectPayment = False;

$strCheck = md5(implode(";", array($action, $orderSumAmount, $orderSumCurrencyPaycash, $orderSumBankPaycash, $shopId, $invoiceId,  $customerNumber, $shopPassword)));

if ($bCorrectPayment && ToUpper($md5) != ToUpper($strCheck))
{
	$bCorrectPayment = False;
	$code = "1"; // ошибка авторизации
}

if ($bCorrectPayment)
{
	if ($action=="checkOrder")
	{
		$head = "checkOrderResponse";

		if(doubleval($sum) == doubleval($orderSumAmount))
		{
			$code = "0";
		}
		else
		{
			$code = "100"; //неверные параметры
			$techMessage = "Сумма заказа не верна.";
		}
	}
	elseif ($action=="paymentAviso")
	{
		$head = "paymentAvisoResponse";

		$strPS_STATUS_DESCRIPTION = "";
		$strPS_STATUS_DESCRIPTION .= "номер плательщика - ".$customerNumber."; ";
		$strPS_STATUS_DESCRIPTION .= "дата платежа - ".$paymentDateTime."; ";
		$strPS_STATUS_MESSAGE = "";

		$arFields = array(
				"PS_STATUS" => "Y",
				"PS_STATUS_CODE" => substr($action, 0, 5),
				"PS_STATUS_DESCRIPTION" => "",
				"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
				"PS_SUM" => $orderSumAmount,
				"PS_CURRENCY" => $orderSumCurrencyPaycash,
				"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime()
			);

		// You can comment this code if you want PAYED flag not to be set automatically
		if (floatval($sum) == floatval($orderSumAmount))
		{
			if ($changePayStatus == "Y")
			{
				if (CSalePaySystemAction::GetParamValue("PAYED") == "Y")
				{
					$code = "0";
				}
				else
				{
					$result = $payment->setField('PAID', 'Y');
					if (!$result->isSuccess())
					{
						$code = "1000";
						$techMessage = "Ошибка оплаты заказа.";
					}
					else
						$code = "0";
				}
			}
		}
		else
		{
			$code = "200"; //неверные параметры
			$techMessage = "Сумма заказа не верна.";
		}

		$result = $payment->setFields($arFields);
		if($result->isSuccess())
			if(strlen($techMessage)<=0 && strlen($code)<=0)
				$code = "0";

		$order->save();
	}
	else
	{
		$code = "200"; //неверные параметры
		$techMessage = "Не известен тип запроса.";
	}
}

$APPLICATION->RestartBuffer();
$dateISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
header("Content-Type: text/xml");
header("Pragma: no-cache");
$text = "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";

if (strlen($head) > 0) // for common-HTTP 3.0. Will be empty if action is not supported yet or payment is not correct
{
	$text .= "<".$head." performedDatetime=\"".$dateISO."\"";
	if (strlen($techMessage) > 0)
		$text .= " code=\"".$code."\" shopId=\"".$shopId."\" invoiceId=\"".$invoiceId."\" techMessage=\"".$techMessage."\"/>";
	else
		$text .= " code=\"".$code."\" shopId=\"".$shopId."\" invoiceId=\"".$invoiceId."\"/>";
}

echo $text;
die();
?>