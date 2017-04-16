<?
use \Bitrix\Sale\Order;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (isset($_REQUEST["orderNumber"]) && intval($_REQUEST["orderNumber"]) > 0)
{
	$paymentId = intval($_REQUEST["orderNumber"]);
	$orderId = intval($_REQUEST["customerNumber"]);
	if (CModule::IncludeModule("sale"))
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = Order::load($orderId);
		$payment = null;

		if ($order)
			$payment = $order->getPaymentCollection()->getItemById($paymentId);

		if ($payment)
		{
			$APPLICATION->IncludeComponent(
				"bitrix:sale.order.payment.receive",
				"",
				array(
					"PAY_SYSTEM_ID" => $payment->getField('PAY_SYSTEM_ID'),
					"PERSON_TYPE_ID" => $order->getPersonTypeId()
				),
			false
			);
		}
	}
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
