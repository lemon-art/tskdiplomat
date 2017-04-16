<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule('sale');
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$orderId = intval($_GET['ORDER_ID']);
$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($orderId, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$urlParams = '';
$errorMsg = '';

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && $bUserCanViewOrder && check_bitrix_sessid())
{
	$doc = (string)$_GET['doc'];
	if (strlen($doc) == 0)
	{
		$errorMsg .= GetMessage("SOP_ERROR_REPORT").'<br>';
	}
	else
	{
		if (isset($_GET['SHIPMENT_ID']) && intval($_GET['SHIPMENT_ID']) > 0)
		{
			$orderId = intval($_GET['ORDER_ID']);
			$shipmentId = intval($_GET['SHIPMENT_ID']);
			$params = array(
				'select' => array('BASKET_ID', 'QUANTITY'),
				'filter' => array(
					'DELIVERY.ORDER_ID' => $orderId,
					'ORDER_DELIVERY_ID' => $shipmentId
				)
			);
			$items = \Bitrix\Sale\Internals\ShipmentItemTable::getList($params);
			$quantity = array();
			$basketIds = array();
			while ($item = $items->fetch())
			{
				$basketIds[] = $item['BASKET_ID'];
				$quantity[] = $item['QUANTITY'];
			}
			$urlParams .= 'BASKET_IDS='.urlencode(join(',', $basketIds)).'&QUANTITIES='.urlencode(join(',', $quantity));
			LocalRedirect('/bitrix/admin/sale_print.php?PROPS_ENABLE=Y&doc='.CUtil::JSEscape($doc).'&ORDER_ID='.$orderId.'&'.$urlParams.'&SHIPMENT_ID='.$shipmentId);
		}
		else
		{
			$urlParams = "SHOW_ALL=Y";
			LocalRedirect('/bitrix/admin/sale_print.php?PROPS_ENABLE=Y&doc='.CUtil::JSEscape($doc).'&ORDER_ID='.$orderId.'&'.$urlParams);
		}
	}
}
else
{
	$errorMsg .= GetMessage('SOP_ERROR_ACCESS');
}

if (!empty($errorMsg))
{
	echo $errorMsg;
}

require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>