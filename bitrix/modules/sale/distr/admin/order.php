<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global string $DBType */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

// include functions
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$LOCAL_SITE_LIST_CACHE = array();
$LOCAL_PERSON_TYPE_CACHE = array();
$LOCAL_PAYED_USER_CACHE = array();
$LOCAL_STATUS_CACHE = array();

Loc::loadMessages(__FILE__);

$arUserGroups = $USER->GetUserGroupArray();
$intUserID = (int)$USER->GetID();

$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $arUserGroups),
		false,
		false,
		array("SITE_ID")
	);
while ($arAccessibleSite = $dbAccessibleSites->Fetch())
{
	if(!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
		$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
}

$bExport = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel');

$sTableID = "tbl_sale_order";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
$runtimeFields = array();

$arFilterFields = array(
	"filter_universal",
	"filter_id_from",
	"filter_id_to",
	"filter_account_number",
	"filter_date_from",
	"filter_date_to",
	"filter_date_update_from",
	"filter_date_update_to",
	"filter_date_paid_from",
	"filter_date_paid_to",
	"filter_lang",
	"filter_currency",
	"filter_price_from",
	"filter_price_to",
	"filter_status",
	"filter_date_status_from",
	"filter_by_recommendation",
	"filter_date_status_to",
	"filter_payed",
	"filter_canceled",
	"filter_deducted",
	"filter_allow_delivery",
	"filter_date_allow_delivery_to",
	"filter_date_allow_delivery_from",
	"filter_marked",
	"filter_buyer",
	"filter_product_id",
	"filter_product_xml_id",
	"filter_affiliate_id",
	"filter_discount_coupon",
	"filter_person_type",
	"filter_user_id",
	"filter_user_login",
	"filter_user_email",
	"filter_group_id",
	"filter_sum_paid",
	"filter_pay_system",
	"filter_xml_id",
);

$arOrderProps = array();
$arOrderPropsCode = array();
$dbProps = \Bitrix\Sale\Internals\OrderPropsTable::getList(array(
	'order' => array("PERSON_TYPE_ID" => "ASC", "SORT" => "ASC"),
	'select' => array("ID", "NAME", "PERSON_TYPE_NAME" => "PERSON_TYPE.NAME", "PERSON_TYPE_ID", "SORT", "IS_FILTERED", "TYPE", "CODE", "SETTINGS"),
));

while ($arProps = $dbProps->fetch())
{
	$key = "";

	if(strlen($arProps["CODE"]) > 0)
	{
		$key = $arProps["CODE"];

		if(empty($arOrderPropsCode[$key]))
			$arOrderPropsCode[$key] = $arProps;
	}
	else
	{
		$key = $arProps["ID"];
		$arOrderProps[IntVal($key)] = $arProps;
	}

	if($key)
	{
		if($arProps["IS_FILTERED"] == "Y" && $arProps["TYPE"] != "MULTISELECT" && $arProps["TYPE"] != "FILE")
			$arFilterFields[] = "filter_prop_".$key;
	}
}

$lAdmin->InitFilter($arFilterFields);

$filter_lang = trim($filter_lang);
if(strlen($filter_lang) > 0)
{
	if(!in_array($filter_lang, $arAccessibleSites) && $saleModulePermissions < "W")
		$filter_lang = "";
}

$arFilter = array();
if(IntVal($filter_id_from)>0) $arFilter[">=ID"] = IntVal($filter_id_from);
if(IntVal($filter_id_to)>0) $arFilter["<=ID"] = IntVal($filter_id_to);
if(strlen($filter_date_from)>0) $arFilter[">=DATE_INSERT"] = trim($filter_date_from);
if(strlen($filter_date_to)>0)
{
	if($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_INSERT"] = $filter_date_to;
	}
	else
	{
		$filter_date_to = "";
	}
}

if(strlen($filter_date_update_from)>0)
{
	$arFilter[">=DATE_UPDATE"] = trim($filter_date_update_from);
}
elseif($set_filter!="Y" && $del_filter != "Y")
{
	$filter_date_update_from_DAYS_TO_BACK = Option::get("sale", "order_list_date", 30);
	$filter_date_update_from = GetTime(time()-86400*Option::get("sale", "order_list_date", 30));
	$arFilter[">=DATE_UPDATE"] = $filter_date_update_from;
}

if(strlen($filter_date_update_to)>0)
{
	if($arDate = ParseDateTime($filter_date_update_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_update_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_update_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_UPDATE"] = $filter_date_update_to;
	}
	else
	{
		$filter_date_update_to = "";
	}
}

if(strlen($filter_date_paid_from)>0) $arFilter[">=DATE_PAYED"] = trim($filter_date_paid_from);

if(strlen($filter_date_paid_to)>0)
{
	if($arDate = ParseDateTime($filter_date_paid_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_paid_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_paid_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_PAYED"] = $filter_date_paid_to;
	}
	else
	{
		$filter_date_paid_to = "";
	}
}

if(strlen($filter_date_allow_delivery_from)>0) $arFilter[">=DATE_ALLOW_DELIVERY"] = trim($filter_date_allow_delivery_from);

if(strlen($filter_date_allow_delivery_to)>0)
{
	if($arDate = ParseDateTime($filter_date_allow_delivery_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_allow_delivery_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_allow_delivery_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_ALLOW_DELIVERY"] = $filter_date_allow_delivery_to;
	}
	else
	{
		$filter_date_allow_delivery_to = "";
	}
}

if(strlen($filter_lang)>0 && $filter_lang!="NOT_REF") $arFilter["LID"] = trim($filter_lang);
if(strlen($filter_currency)>0) $arFilter["CURRENCY"] = trim($filter_currency);

if(isset($filter_status) && !is_array($filter_status) && strlen($filter_status) > 0)
	$filter_status = array($filter_status);
if(isset($filter_status) && is_array($filter_status) && count($filter_status) > 0)
{
	$countFilter = count($filter_status);
	for ($i = 0; $i < $countFilter; $i++)
	{
		$filter_status[$i] = trim($filter_status[$i]);
		if(strlen($filter_status[$i]) > 0)
			$arFilter["STATUS_ID"][] = $filter_status[$i];
	}
}
if (strlen($filter_by_recommendation)>0) $arFilter["BY_RECOMMENDATION"] = trim($filter_by_recommendation);
if(strlen($filter_date_status_from)>0) $arFilter[">=DATE_STATUS"] = trim($filter_date_status_from);
if(strlen($filter_date_status_to)>0)
{
	if($arDate = ParseDateTime($filter_date_status_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_status_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_status_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_STATUS"] = $filter_date_status_to;
	}
	else
	{
		$filter_date_status_to = "";
	}
}

if(strlen($filter_payed)>0) $arFilter["PAYED"] = trim($filter_payed);
if(strlen($filter_canceled)>0) $arFilter["CANCELED"] = trim($filter_canceled);
if(strlen($filter_deducted)>0) $arFilter["DEDUCTED"] = trim($filter_deducted);
if(strlen($filter_allow_delivery)>0) $arFilter["ALLOW_DELIVERY"] = trim($filter_allow_delivery);
if(strlen($filter_marked)>0) $arFilter["MARKED"] = trim($filter_marked);
if(strlen($filter_buyer)>0) $arFilter["%BUYER"] = trim($filter_buyer);
if(strlen($filter_user_login)>0) $arFilter["USER.LOGIN"] = trim($filter_user_login);
if(strlen($filter_user_email)>0) $arFilter["USER.EMAIL"] = trim($filter_user_email);
if(IntVal($filter_user_id)>0) $arFilter["USER_ID"] = IntVal($filter_user_id);
if(is_array($filter_group_id) && count($filter_group_id) > 0)
{
	foreach($filter_group_id as $v)
	{
		if(IntVal($v) > 0)
			$arFilter["USER_GROUP.GROUP_ID"][] = $v;
	}
}

if(IntVal($filter_product_id)>0) $arFilter["BASKET.PRODUCT_ID"] = IntVal($filter_product_id);
if(strlen($filter_product_xml_id)>0) $arFilter["BASKET.PRODUCT_XML_ID"] = trim($filter_product_xml_id);
if(IntVal($filter_affiliate_id)>0) $arFilter["AFFILIATE_ID"] = IntVal($filter_affiliate_id);
if(strlen($filter_discount_coupon)>0) $arFilter["=ORDER_COUPONS.COUPON"] = trim($filter_discount_coupon);
if(floatval($filter_price_from)>0) $arFilter[">=PRICE"] = floatval($filter_price_from);
if(floatval($filter_price_to)>0) $arFilter["<PRICE"] = floatval($filter_price_to);
if(strlen($filter_xml_id)>0) $arFilter["%XML_ID"] = trim($filter_xml_id);

if(isset($filter_universal) && strlen($filter_universal) > 0)
	$arFilter["NAME_SEARCH"] = trim($filter_universal);
if(strlen($filter_account_number)>0) $arFilter["ACCOUNT_NUMBER"] = trim($filter_account_number);

if(strlen($filter_sum_paid) > 0)
{
	if($filter_sum_paid == "Y")
		$arFilter[">SUM_PAID"] = 0;
	else
		$arFilter["<=SUM_PAID"] = 0;
}

if(isset($filter_person_type) && is_array($filter_person_type) && count($filter_person_type) > 0)
{
	$countFilterPerson = count($filter_person_type);
	for ($i = 0; $i < $countFilterPerson; $i++)
	{
		if(IntVal($filter_person_type[$i]) > 0)
			$arFilter["PERSON_TYPE_ID"][] = $filter_person_type[$i];
	}
}

if(!empty($filter_pay_system) && is_array($filter_pay_system))
{
	$countFilterPay = count($filter_pay_system);
	$whereExpression = "";

	for ($i = 0; $i < $countFilterPay; $i++)
	{
		if(intval($filter_pay_system[$i]) <= 0)
			continue;

		if($whereExpression == "")
			$whereExpression .= "(";
		else
			$whereExpression .= " OR ";

		$whereExpression .= "PAY_SYSTEM_ID = ".intval($filter_pay_system[$i]);
	}

	if(strlen($whereExpression) > 0)
	{
		$whereExpression .= ")";

		$runtimeFields["REQUIRED_PS_PRESENTED"] = array(
			'data_type' => 'boolean',
			'expression' => array(
				'CASE WHEN EXISTS (SELECT ID FROM b_sale_order_payment WHERE ORDER_ID = %s AND '.$whereExpression.') THEN 1 ELSE 0 END',
				'ID'
			)
		);

		$arFilter["=REQUIRED_PS_PRESENTED"] = 1;
	}
}

$filterOrderPropValue = array();
$filterOrderProps = array();
foreach ($arOrderProps as $key => $value)
{
	if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
	{
		$tmp = trim(${"filter_prop_".$key});
		if(StrLen($tmp) > 0)
		{
			if($value["TYPE"]=="STRING" && !preg_match("/^\d+$/", $tmp))
				$filterName = "%PROPERTY_VALUE_".$key;
			else
				$filterName = "PROPERTY_VALUE_".$key;

			$filterOrderProps[$filterName] = $tmp;
			$filterOrderPropValue[$key] = $tmp;
		}
	}
}

foreach ($arOrderPropsCode as $key => $value)
{
	if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
	{
		$tmp = trim(${"filter_prop_".$key});
		if(StrLen($tmp) > 0)
		{
			if($value["TYPE"]=="STRING" && !preg_match("/^\d+$/", $tmp))
				$filterName = "%PROPERTY_VAL_BY_CODE_".$key;
			else
				$filterName = "PROPERTY_VAL_BY_CODE_".$key;

			$filterOrderProps[$filterName] = $tmp;
			$filterOrderPropValue[$key] = $tmp;
		}
	}
}

if($saleModulePermissions < "W")
{
	if(strlen($filter_lang) <= 0 && count($arAccessibleSites) > 0)
		$arFilter["LID"] = $arAccessibleSites;
}

$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('view'));

if($saleModulePermissions < "W")
{
	if(!$arFilter["STATUS_ID"])
		$arFilter["STATUS_ID"] = array();

	$intersected = array_intersect($arFilter["STATUS_ID"], $allowedStatusesView);

	if(!empty($arFilter["STATUS_ID"]))
	{
		if(empty($intersected))
		{
			$arFilter[]["STATUS_ID"] = $arFilter["STATUS_ID"];
			$arFilter[]["STATUS_ID"] = $allowedStatusesView;
			unset($arFilter["STATUS_ID"]);
		}
		else
		{
			$arFilter["STATUS_ID"] = $intersected;
		}
	}
	else
	{
		$arFilter["STATUS_ID"] = $allowedStatusesView;
	}

}

$arFilterTmp = $arFilter;

if($lAdmin->EditAction() && $saleModulePermissions >= "U")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = (int)$ID;
		$isOrderNeedSave = false;

		if(!$lAdmin->IsUpdated($ID))
			continue;

		/** @var \Bitrix\Sale\Order $editOrder */
		$editOrder = \Bitrix\Sale\Order::load($ID);

		if($editOrder)
		{
			if(array_key_exists("CANCELED", $arFields)
				&& ($arFields["CANCELED"] == "Y" || $arFields["CANCELED"] == "N")
				&& $arFields["CANCELED"] != $editOrder->getField("CANCELED"))
			{
				if(CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID))
				{
					/** @var \Bitrix\Sale\Result $res */
					$res = $editOrder->setField("CANCELED", $arFields["CANCELED"]);
					if($res->isSuccess())
					{
						$isOrderNeedSave = true;
					}
					else
					{
						$errMessages = $res->getErrorMessages();
						if(count($errMessages) > 0)
							$lAdmin->AddUpdateError(implode("<br>\n", $errMessages), $ID);
						else
							$lAdmin->AddUpdateError(Loc::getMessage("SOA_ERROR_CANCEL"), $ID);
					}
				}
				else
				{
					$lAdmin->AddUpdateError(Loc::getMessage("SOA_PERMS_CANCEL"), $ID);
				}
			}

			$statusId = $editOrder->getField("STATUS_ID");

			if(array_key_exists("STATUS_ID", $arFields)
				&& strlen($arFields["STATUS_ID"]) > 0
				&& $arFields["STATUS_ID"] != $statusId)
			{
				$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
					$USER->GetID(),
					\Bitrix\Sale\OrderStatus::getInitialStatus()
				);

				if(array_key_exists($statusId, $statusesList))
				{
					$res = $editOrder->setField("STATUS_ID", $arFields["STATUS_ID"]);

					if($res->isSuccess())
					{
						$isOrderNeedSave = true;
					}
					else
					{
						$errMessages = $res->getErrorMessages();
						if(count($errMessages) > 0)
							$lAdmin->AddUpdateError(implode("<br>\n", $errMessages), $ID);
						else
							$lAdmin->AddUpdateError(Loc::getMessage("SOA_ERROR_STATUS"), $ID);
					}
				}
				else
				{
					$lAdmin->AddUpdateError(Loc::getMessage("SOA_PERMS_STATUS"), $ID);
				}
			}

			if($isOrderNeedSave)
			{
				$res = $editOrder->save();

				if(!$res->isSuccess())
				{
					$errMessages = $res->getErrorMessages();
					if(count($errMessages) > 0)
						$lAdmin->AddUpdateError(implode("<br>\n", $errMessages), $ID);
					else
						$lAdmin->AddUpdateError(Loc::getMessage("SOA_ERROR_CANCEL"), $ID);
				}
			}
		}
		else
		{
			$lAdmin->AddUpdateError(Loc::getMessage("SOA_NO_ORDER"), $ID);
		}
	}
}

$bShowBasketProps = ((string)\Bitrix\Main\Config\Option::get('sale', 'show_basket_props_in_order_list') == 'Y');

//Filters by foreign entities
//User params
if(isset($arFilterTmp["NAME_SEARCH"]) && strlen($arFilterTmp["NAME_SEARCH"]) > 0)
{
	$nameSearch = $arFilterTmp["NAME_SEARCH"];
	unset($arFilterTmp["NAME_SEARCH"]);

	$arFilterTmp[] = array(
		"LOGIC" => "OR",
		"%USER.LOGIN" => $nameSearch,
		"%USER.NAME" => $nameSearch,
		"%USER.LAST_NAME" => $nameSearch,
		"%USER.SECOND_NAME" => $nameSearch,
		"%USER.EMAIL" => $nameSearch,
	);
}

$propIterator = 0;
//Order props params
foreach ($arOrderPropsCode as $key => $value)
{
	if($value["IS_FILTERED"] != "Y" || $value["TYPE"] == "MULTIPLE")
		continue;

	if(
		(isset($filterOrderProps["PROPERTY_VAL_BY_CODE_".$key]) && strlen($filterOrderProps["PROPERTY_VAL_BY_CODE_".$key]) > 0)
		|| (isset($filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key]) && strlen($filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key]) > 0)
	)
	{
		$propIterator++;

		$runtimeFields['PROP_'.$propIterator] = array(
			'data_type' => 'Bitrix\Sale\Internals\OrderPropsValueTable',
			'reference' => array(
				'ref.ORDER_ID' => 'this.ID',
			),
			'join_type' => 'inner'
		);

		$arFilterTmp["=PROP_".$propIterator.".CODE"] = $key;

		if (isset($filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key]))
			$arFilterTmp["%PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
		else
			$arFilterTmp["PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
	}
}

foreach ($arOrderProps as $key => $value)
{
	$propIterator++;

	if($value["IS_FILTERED"] != "Y" || $value["TYPE"] == "MULTIPLE")
		continue;

	if(
		(isset($filterOrderProps["PROPERTY_VALUE_".$key]) && strlen($filterOrderProps["PROPERTY_VALUE_".$key]) > 0)
		|| (isset($filterOrderProps["%PROPERTY_VALUE_".$key]) && strlen($filterOrderProps["%PROPERTY_VALUE_".$key]) > 0)
	)
	{
		$runtimeFields['PROP_'.$propIterator] = array(
			'data_type' => 'Bitrix\Sale\Internals\OrderPropsValueTable',
			'reference' => array(
				'ref.ORDER_ID' => 'this.ID',
			),
			'join_type' => 'inner'
		);

		$arFilterTmp["=PROP_".$propIterator.".ORDER_PROPS_ID"] = $key;

		if (isset($filterOrderProps["%PROPERTY_VALUE_".$key]))
			$arFilterTmp["%PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
		else
			$arFilterTmp["PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
	}
}

foreach(GetModuleEvents("sale", "OnOrderListFilter", true) as $arEvent)
	$arFilterTmp = ExecuteModuleEventEx($arEvent, array($arFilterTmp));

$arID = array();

if(($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "U")
{
	$arAffectedOrders = array();
	$forAll =($_REQUEST['action_target'] == 'selected');

	if($forAll)
	{
		$filter = $arFilterTmp;
		$arID = array();
	}
	else
	{
		$filter = array(
			"ID" => $arID,
			"STATUS_ID" => $allowedStatusesView
		);
	}

	$dbOrderList = \Bitrix\Sale\Internals\OrderTable::getList(array(
		'order' => array($by => $order),
		'filter' => $filter,
		'select' => array("ID", "PERSON_TYPE_ID", "PAYED", "CANCELED", "DEDUCTED", "STATUS_ID")
	));

	while ($arOrderList = $dbOrderList->fetch())
	{
		if($forAll)
			$arID[] = $arOrderList['ID'];

		$arAffectedOrders[$arOrderList["ID"]] = $arOrderList;
	}

	foreach ($arID as $ID)
	{
		if(strlen($ID) <= 0)
			continue;

		if(CSaleOrder::IsLocked($ID, $lockedBY, $dateLock) && $_REQUEST['action'] != "unlock")
		{
			$lAdmin->AddGroupError(str_replace("#DATE#", "$dateLock", str_replace("#ID#", "$lockedBY", Loc::getMessage("SOE_ORDER_LOCKED"))), $ID);
		}
		else
		{
			switch ($_REQUEST['action'])
			{
				case "delete":
					if(!($saleOrder = \Bitrix\Sale\Order::load($ID)))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SALE_DELETE_ERROR_CANT_FIND", array("#ID#" => $ID)));
						break;
					}
					if(!CSaleOrder::CanUserDeleteOrder($ID, $arUserGroups, $intUserID))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SO_NO_PERMS2DEL", array("#ID#" => $ID)), $ID);
						break;
					}

					$res = \Bitrix\Sale\Order::delete($ID);
					if(!$res->isSuccess())
						$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()));
					break;
				case "unlock":
					CSaleOrder::UnLock($ID);
					break;
				case "cancel":
					if(!CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP", array("#ID#" => $ID)), $ID);
						break;
					}
					/** @var \Bitrix\Sale\Order $saleOrder */
					if(!$saleOrder = \Bitrix\Sale\Order::load($ID))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SO_NO_ORDER", array("#ID#" => $ID)), $ID);
						break;
					}
					if($saleOrder->getField("CANCELED") == "Y")
					{
						$lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP_CANCEL", array("#ID#" => $ID)), $ID);
						break;
					}
					/** @var \Bitrix\Sale\Result $res */
					$res = $saleOrder->setField("CANCELED", "Y");
					if(!$res->isSuccess())
					{
						$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()), $ID);
						break;
					}
					$res = $saleOrder->save();
					if(!$res->isSuccess())
						$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()), $ID);
					break;
				case "cancel_n":
					if(!CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP", array("#ID#" => $ID)), $ID);
						break;
					}
					/** @var \Bitrix\Sale\Order $saleOrder */
					if(!$saleOrder = \Bitrix\Sale\Order::load($ID))
					{
						$lAdmin->AddGroupError(Loc::getMessage("SO_NO_ORDER", array("#ID#" => $ID)), $ID);
						break;
					}
					if($saleOrder->getField("CANCELED") == "N")
					{
						$lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP_CANCEL_N", array("#ID#" => $ID)), $ID);
						break;
					}
					/** @var \Bitrix\Sale\Result $res */
					$res = $saleOrder->setField("CANCELED", "N");
					if(!$res->isSuccess())
					{
						$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()), $ID);
						break;
					}
					$res = $saleOrder->save();
					if(!$res ->isSuccess())
						$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()), $ID);
					break;

				default:
					if(substr($_REQUEST['action'], 0, strlen("status_")) == "status_")
					{
						$statusID = substr($_REQUEST['action'], strlen("status_"));

						if(strlen($statusID) > 0)
						{
							$resStatus = StatusTable::getList(array(
								'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
								'filter' => array(
									'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
									'=ID' => $statusID
								),
							));

							if($arStatus = $resStatus->fetch())
							{
								if(CSaleOrder::CanUserChangeOrderStatus($ID, $statusID, $arUserGroups))
								{
									if($arAffectedOrders[$ID]["STATUS_ID"] != $statusID)
									{
										$saleOrder = \Bitrix\Sale\Order::load($ID);
										$res = $saleOrder->setField("STATUS_ID", $statusID);

										if(!$res->isSuccess())
										{
											$errMsgs = $res->getErrorMessages();

											if(count($errMsgs) > 0)
												$lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS", array("#ID#" => $ID, "#STATUS#" => $arStatus["NAME"])).": ".implode("<br>\n", $errMsgs), $ID);
											else
												$lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS", array("#ID#" => $ID, "#STATUS#" => $arStatus["NAME"])), $ID);
										}
										else
										{
											$res = $saleOrder->save();

											if(!$res ->isSuccess())
												$lAdmin->AddGroupError(implode("<br>\n", $res->getErrorMessages()), $ID);
										}
									}
									else
									{
										$lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS_ALREADY", Array("#ID#" => $ID, "#STATUS#" => $arStatus["NAME"])), $ID);
									}
								}
								else
								{
									$lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_STATUS_GROUP", Array("#ID#" => $ID, "#STATUS#" => $arStatus["NAME"])), $ID);
								}
							}
						}
					}

				break;
			}
		}
	}
}

$arColumn2Field = array(
		"ID" => array("ID"),
		"ACCOUNT_NUMBER" => array("ACCOUNT_NUMBER"),
		"LID" => array("LID"),
		"PERSON_TYPE" => array("PERSON_TYPE_ID"),
		"PAYED" => array("PAYED"),
		"CANCELED" => array("CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID"),
		"DEDUCTED" => array("DEDUCTED"),
		"MARKED" => array("MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED"),
		"STATUS_ID" => array("STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID"),
		"STATUS" => array("STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID"),
		"PRICE_DELIVERY" => array("PRICE_DELIVERY", "CURRENCY"),
		"PRICE" => array("PRICE", "CURRENCY"),
		"SUM_PAID" => array("SUM_PAID", "CURRENCY"),
		"USER" => array("USER_ID"),
		"DATE_INSERT" => array("DATE_INSERT"),
		"DATE_UPDATE" => array("DATE_UPDATE"),
		"TAX_VALUE" => array("TAX_VALUE", "CURRENCY"),
		"LOCK_STATUS" => array("LOCK_STATUS", "LOCK_USER_NAME"),
		"BASKET" => array(),
		"COMMENTS" => array("COMMENTS"),
		"REASON_CANCELED" => array("REASON_CANCELED"),
		"REASON_MARKED" => array("REASON_MARKED"),
		"USER_EMAIL" => array("USER_EMAIL"),
		"USER_DESCRIPTION" => array("USER_DESCRIPTION"),
		"EXTERNAL_ORDER" => array("EXTERNAL_ORDER")
	);

$arHeaders = array(
	array("id"=>"DATE_INSERT","content"=>Loc::getMessage("SI_DATE_INSERT"), "sort"=>"DATE_INSERT", "default"=>true),
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"USER","content"=>Loc::getMessage("SI_BUYER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"STATUS_ID","content"=>Loc::getMessage("SI_STATUS"), "sort"=>"STATUS_ID", "default"=>true, "title" => Loc::getMessage("SO_S_DATE_STATUS")),
	array("id"=>"PAYED","content"=>Loc::getMessage("SI_PAID"), "sort"=>"PAYED", "default"=>true, "title" => Loc::getMessage("SO_S_DATE_PAYED")),
	array("id"=>"ALLOW_DELIVERY","content"=>Loc::getMessage("SI_ALLOW_DELIVERY"), "sort"=>"ALLOW_DELIVERY", "default"=>false),
	array("id"=>"CANCELED","content"=>Loc::getMessage("SI_CANCELED"), "sort"=>"CANCELED", "default"=>true),
	array("id"=>"DEDUCTED","content"=>Loc::getMessage("SI_DEDUCTED"), "sort"=>"DEDUCTED", "default"=>true),
	array("id"=>"MARKED","content"=>Loc::getMessage("SI_MARKED"), "sort"=>"MARKED", "default"=>true),
	array("id"=>"PRICE","content"=>Loc::getMessage("SI_SUM"), "sort"=>"PRICE", "default"=>true),
	array("id"=>"BASKET","content"=>Loc::getMessage("SI_ITEMS"), "sort"=>"", "default"=>true),
	array("id"=>"DATE_UPDATE","content"=>Loc::getMessage("SI_DATE_UPDATE"), "sort"=>"DATE_UPDATE", "default"=>false),
	array("id"=>"LID","content"=>Loc::getMessage("SI_SITE"), "sort"=>"LID"),
	array("id"=>"PERSON_TYPE","content"=>Loc::getMessage("SI_PAYER_TYPE"), "sort"=>"PERSON_TYPE_ID"),
	array("id"=>"PAY_VOUCHER_NUM","content"=>Loc::getMessage("SI_NO_PP"), "sort"=>"", "default"=>false),
	array("id"=>"PAY_VOUCHER_DATE","content"=>Loc::getMessage("SI_DATE_PP"), "sort"=>"", "default"=>false),
	array("id"=>"STATUS","content"=>Loc::getMessage("SI_STATUS_OLD"), "sort"=>"STATUS_ID", "default"=>false),
	array("id"=>"PRICE_DELIVERY","content"=>Loc::getMessage("SI_DELIVERY"), "sort"=>"PRICE_DELIVERY", "default"=>false),
	array("id"=>"DELIVERY_DOC_NUM","content"=>Loc::getMessage("SI_DELIVERY_DOC_NUM"), "sort"=>"", "default"=>false),
	array("id"=>"DELIVERY_DOC_DATE","content"=>Loc::getMessage("SI_DELIVERY_DOC_DATE"), "sort"=>"", "default"=>false),
	array("id"=>"SUM_PAID","content"=>Loc::getMessage("SI_SUM_PAID"), "sort"=>"SUM_PAID"),
	array("id"=>"USER_EMAIL","content"=>Loc::getMessage("SALE_F_USER_EMAIL"), "sort"=>"USER_EMAIL", "default"=>false),
	array("id"=>"PAY_SYSTEM","content"=>Loc::getMessage("SI_PAY_SYS"), "sort"=>"", "default"=>false),
	array("id"=>"DELIVERY","content"=>Loc::getMessage("SI_DELIVERY_SYS"), "sort"=>"", "default"=>false),
	array("id"=>"PS_STATUS","content"=>Loc::getMessage("SI_PAYMENT_PS"), "sort"=>"", "default"=>false),
	array("id"=>"PS_SUM","content"=>Loc::getMessage("SI_PS_SUM"), "sort"=>"", "default"=>false),
	array("id"=>"TAX_VALUE","content"=>Loc::getMessage("SI_TAX"), "sort"=>"TAX_VALUE"),
	array("id"=>"BASKET_NAME","content"=>Loc::getMessage("SOA_BASKET_NAME"), "sort"=>""),
	array("id"=>"BASKET_PRODUCT_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_ID"), "sort"=>""),
	array("id"=>"BASKET_PRICE","content"=>Loc::getMessage("SOA_BASKET_PRICE"), "sort"=>""),
	array("id"=>"BASKET_QUANTITY","content"=>Loc::getMessage("SOA_BASKET_QUANTITY"), "sort"=>""),
	array("id"=>"BASKET_WEIGHT","content"=>Loc::getMessage("SOA_BASKET_WEIGHT"), "sort"=>""),
	array("id"=>"BASKET_NOTES","content"=>Loc::getMessage("SOA_BASKET_NOTES"), "sort"=>""),
	array("id"=>"BASKET_DISCOUNT_PRICE","content"=>Loc::getMessage("SOA_BASKET_DISCOUNT_PRICE"), "sort"=>""),
	array("id"=>"BASKET_CATALOG_XML_ID","content"=>Loc::getMessage("SOA_BASKET_CATALOG_XML_ID"), "sort"=>""),
	array("id"=>"BASKET_PRODUCT_XML_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_XML_ID"), "sort"=>""),
	array("id"=>"BASKET_DISCOUNT_NAME","content"=>Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_DISCOUNTS"), "sort"=>""),
	array("id"=>"BASKET_DISCOUNT_VALUE","content"=>Loc::getMessage("SOA_BASKET_DISCOUNT_VALUE"), "sort"=>""),
	array("id"=>"BASKET_DISCOUNT_COUPON","content"=>Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS_MULTI"), "sort"=>""),
	array("id"=>"BASKET_VAT_RATE","content"=>Loc::getMessage("SOA_BASKET_VAT_RATE"), "sort"=>""),
	array("id"=>"DATE_ALLOW_DELIVERY", "content"=>Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY"), "sort"=>"", "default"=>false),
	array("id"=>"ACCOUNT_NUMBER","content"=>Loc::getMessage("SOA_ACCOUNT_NUMBER"), "sort"=>""),
	array("id"=>"TRACKING_NUMBER","content"=>Loc::getMessage("SOA_TRACKING_NUMBER"), "sort"=>"", "default"=>false),
	array("id"=>"EXTERNAL_ORDER","content"=>Loc::getMessage("SOA_EXTERNAL_ORDER"), "sort"=>"", "default"=> false),
	array("id"=>"SHIPMENTS","content"=>Loc::getMessage("SOA_SHIPMENTS"), "sort"=>"", "default"=> true),
	array("id"=>"PAYMENTS","content"=>Loc::getMessage("SOA_PAYMENTS"), "sort"=>"", "default"=> true)
);

if($DBType == "mysql")
{
	$arHeaders[] = array("id"=>"COMMENTS","content"=>Loc::getMessage("SI_COMMENTS"), "sort"=>"COMMENTS", "default"=>false);
	$arHeaders[] = array("id"=>"PS_STATUS_DESCRIPTION","content"=>Loc::getMessage("SOA_PS_STATUS_DESCR"), "sort"=>"", "default"=>false);
	$arHeaders[] = array("id"=>"USER_DESCRIPTION","content"=>Loc::getMessage("SI_USER_DESCRIPTION"), "sort"=>"", "default"=>false);
	$arHeaders[] = array("id"=>"REASON_CANCELED","content"=>Loc::getMessage("SI_REASON_CANCELED"), "sort"=>"", "default"=>false);
	$arHeaders[] = array("id"=>"REASON_MARKED","content"=>Loc::getMessage("SI_REASON_MARKED"), "sort"=>"", "default"=>false);
}

foreach ($arOrderProps as $key => $value)
{
	$arHeaders[] = array("id" => "PROP_".$key, "content" => htmlspecialcharsbx($value["NAME"])." (".htmlspecialcharsbx($value["PERSON_TYPE_NAME"]).")", "sort" => "", "default" => false);
	$arColumn2Field["PROP_".$key] = array();
}
foreach ($arOrderPropsCode as $key => $value)
{
	$arHeaders[] = array("id" => "PROP_".$key, "content" => htmlspecialcharsbx($value["NAME"]), "sort" => "", "default" => false);
	$arColumn2Field["PROP_".$key] = array();
}

$lAdmin->AddHeaders($arHeaders);

$arSelectFields = array();
$arSelectFields[] = "ID";
$arSelectFields[] = "LID";
$arSelectFields[] = "LOCK_STATUS";
$arSelectFields[] = "LOCK_USER_NAME";

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$bNeedProps = false;
$bNeedBasket = false;
foreach ($arVisibleColumns as $visibleColumn)
{
	if(!$bNeedProps && SubStr($visibleColumn, 0, StrLen("PROP_")) == "PROP_")
		$bNeedProps = true;
	if(
		!$bNeedBasket
		&& $visibleColumn != 'BASKET_DISCOUNT_COUPON'
		&& $visibleColumn != 'BASKET_DISCOUNT_NAME'
		&& strpos($visibleColumn, "BASKET") !== false
	)
		$bNeedBasket = true;

	if(array_key_exists($visibleColumn, $arColumn2Field))
	{
		if(is_array($arColumn2Field[$visibleColumn]) && count($arColumn2Field[$visibleColumn]) > 0)
		{
			$countArColumn = count($arColumn2Field[$visibleColumn]);
			for ($i = 0; $i < $countArColumn; $i++)
			{
				if(!in_array($arColumn2Field[$visibleColumn][$i], $arSelectFields))
					$arSelectFields[] = $arColumn2Field[$visibleColumn][$i];
			}
		}
	}
}

$b = "sort";
$o = "asc";
$dbSite = CSite::GetList($b, $o, array());
while ($arSite = $dbSite->Fetch())
{
	$serverName[$arSite["LID"]] = $arSite["SERVER_NAME"];
	if(strlen($serverName[$arSite["LID"]]) <= 0)
	{
		if(defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
			$serverName[$arSite["LID"]] = SITE_SERVER_NAME;
		else
			$serverName[$arSite["LID"]] = \Bitrix\Main\Config\Option::get("main", "server_name", "");
	}

	$WEIGHT_UNIT[$arSite["LID"]] = htmlspecialcharsbx(\Bitrix\Main\Config\Option::get('sale', 'weight_unit', "", $arSite["LID"]));
	$WEIGHT_KOEF[$arSite["LID"]] = htmlspecialcharsbx(\Bitrix\Main\Config\Option::get('sale', 'weight_koef', 1, $arSite["LID"]));
}

$arGroupByTmp = array();

if($saleModulePermissions < "W")
{
	foreach($arSelectFields as $k => $v)
	{
		if(in_array($v, Array("COMMENTS")) && $saleModulePermissions < "U")
			unset($arSelectFields[$k]);
	}
}

if(!isset($order))
	$order = "DESC";

if($by == "STATUS_ID")
	$arFilterOrder["DATE_STATUS"] = $order;
elseif($by == "CANCELED")
	$arFilterOrder["DATE_CANCELED"] = $order;
else
	$arFilterOrder[$by] = $order;

$sScript = "";
$usePageNavigation = true;
$navyParams = array();

if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($sTableID));
	if ($navyParams['SHOW_ALL'])
	{
		$usePageNavigation = false;
	}
	else
	{
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}

if (in_array('USER_EMAIL', $arSelectFields))
{
	$arSelectFields["USER_EMAIL"] = 'USER.EMAIL';

	if ($searchIndex = array_search('USER_EMAIL', $arSelectFields))
		unset($arSelectFields[$searchIndex]);
}

$getListParams = array(
	'order' => $arFilterOrder,
	'filter' => $arFilterTmp,
	'group' => $arGroupByTmp,
	'select' => $arSelectFields,
	'runtime' => $runtimeFields
);

if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$totalPages = 0;

if ($usePageNavigation)
{
	$countQuery = new \Bitrix\Main\Entity\Query(\Bitrix\Sale\Internals\OrderTable::getEntity());
	$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($getListParams['filter']);
	foreach ($runtimeFields as $key => $field)
		$countQuery->registerRuntimeField($key, $field);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];

	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;

		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = 0;
	}
}

$dbOrderList = new CAdminResult(\Bitrix\Sale\Internals\OrderTable::getList($getListParams), $sTableID);

if ($usePageNavigation)
{
	$dbOrderList->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$dbOrderList->NavRecordCount = $totalCount;
	$dbOrderList->NavPageCount = $totalPages;
	$dbOrderList->NavPageNomer = $navyParams['PAGEN'];
	$dbOrderList->nSelectedCount = $totalCount;
}
else
{
	$dbOrderList->NavStart();
}

$lAdmin->NavText($dbOrderList->GetNavPrint(Loc::getMessage("SALE_PRLIST")));

$ordersIds = array();
$shipmentStatuses = array();
$rowsList = array();
$basketSeparator = '<hr size="1" width="90%">';
if ($bExport)
	$basketSeparator = "<br>";
$users = array();
$userIdFields = array('EMP_ALLOW_DELIVERY', 'EMP_DEDUCTED_ID', 'EMP_MARKED_ID', 'EMP_STATUS_ID', 'EMP_CANCELED_ID', 'EMP_PAYED_ID');
$formattedUserNames = array();
while ($arOrder = $dbOrderList->NavNext(true, "f_"))
{
	foreach ($userIdFields as $userIdField)
	{
		$uId = intval($arOrder[$userIdField]);
		if ($uId > 0 && !in_array($uId, $users))
			$users[] = $uId;
	}

	$formattedUserNames = GetFormatedUserName(array_values($users), false);

	$ordersIds[] = $arOrder['ID'];
	/**
	 * define personalization settings
	 */
	$isRecommended = false;

	// personalization
	if(\Bitrix\Main\Analytics\Catalog::isOn() || $bNeedBasket)
	{
		$arBasketItems = array();

		$dbItemsList = \Bitrix\Sale\Internals\BasketTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('=ORDER_ID' => $arOrder['ID'])
		));

		while ($arItem = $dbItemsList->fetch())
		{
			$arBasketItems[] = $arItem;

			if($arItem['RECOMMENDATION'])
				$isRecommended = true;
		}
	}

	/**
	 * build row
	 */
	$rowsList[$arOrder['ID']] = $row =& $lAdmin->AddRow($f_ID, $arOrder, "sale_order_view.php?ID=".$f_ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"));

	$lamp = "/bitrix/images/sale/".$arOrder['LOCK_STATUS'].".gif";
	if($arOrder['LOCK_STATUS']=="green")
		$lamp_alt = Loc::getMessage("SMOL_GREEN_ALT");
	elseif($arOrder['LOCK_STATUS']=="yellow")
		$lamp_alt = Loc::getMessage("SMOL_YELLOW_ALT");
	else
		$lamp_alt = str_replace("#LOCK_USER_NAME#", trim($arOrder['LOCK_USER_NAME']), Loc::getMessage("SMOL_RED_ALT"));

	//ID
	$idTmp = '<table><tr><td valign="top">';
	if(!$bExport)
		$idTmp .= "<img src='".$lamp."' hspace='4' alt='".htmlspecialcharsbx($lamp_alt)."' title='".htmlspecialcharsbx($lamp_alt)."' />";
	$idTmp .= "</td>
		<td><b><a href='/bitrix/admin/sale_order_view.php?ID=".$f_ID.GetFilterParams("filter_")."&lang=".LANGUAGE_ID."' title='".Loc::getMessage("SALE_DETAIL_DESCR")."'>".Loc::getMessage("SO_ORDER_ID_PREF").$arOrder["ID"]."</a></b></td>";
	$idTmp .= "</tr>";

	if($isRecommended)
	{
		$idTmp .= "<tr>
			<td rowspan='2'>
				<div class='bx-adm-bigdata-icon-medium'></div>
			</td>
		</tr>";
	}

	$idTmp .= "</table>";

	$row->AddField("ID", $idTmp);

	//LID
	$fieldValue = "";
	if(in_array("LID", $arVisibleColumns))
	{
		if(!isset($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]])
			|| empty($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]]))
		{
			$dbSite = CSite::GetByID($arOrder["LID"]);
			if($arSite = $dbSite->Fetch())
				$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]] = htmlspecialcharsEx($arSite["NAME"]);
		}
		$fieldValue = "[".$arOrder["LID"]."] ".$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]];
	}
	$row->AddField("LID", $fieldValue);

	//PERSON_TYPE
	$fieldValue = "";
	if(in_array("PERSON_TYPE", $arVisibleColumns))
	{
		if(!isset($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]])
			|| empty($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]]))
		{
			if($arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]))
				$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]] = htmlspecialcharsEx($arPersonType["NAME"]);
		}
		$fieldValue = "[";
		if($saleModulePermissions >= "W")
			$fieldValue .= '<a href="/bitrix/admin/sale_person_type.php?lang='.LANGUAGE_ID.'">';
		$fieldValue .= $arOrder["PERSON_TYPE_ID"];
		if($saleModulePermissions >= "W")
			$fieldValue .= "</a>";
		$fieldValue .= "] ".$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]];
	}
	$row->AddField("PERSON_TYPE", $fieldValue);

	//PAYED
	$fieldValue = "";
	if(in_array("PAYED", $arVisibleColumns))
	{
		$fieldValue .= '<span id="payed_'.$arOrder['ID'].'">'.(($arOrder["PAYED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
		$fieldValueTmp = $arOrder["DATE_PAYED"];
		if(strlen($arOrder["DATE_PAYED"]) > 0)
		{
			if(IntVal($arOrder["EMP_PAYED_ID"]) > 0)
				$fieldValueTmp .= '<br />'.$formattedUserNames[$arOrder["EMP_PAYED_ID"]];

			if(!$bExport)
			{
				$sScript .= "
						new top.BX.CHint({
							parent: top.BX('payed_".$arOrder["ID"]."'),
							show_timeout: 10,
							hide_timeout: 100,
							dx: 2,
							preventHide: true,
							min_width: 250,
							hint: '".CUtil::JSEscape($fieldValueTmp)."'
						});
				";
			}
		}
	}
	$row->AddField("PAYED", $fieldValue);

	//CANCELED
	if($row->bEditMode != true
		|| $row->bEditMode == true && !CSaleOrder::CanUserCancelOrder($f_ID, $arUserGroups, $intUserID))
	{
		$fieldValue = "";
		if(in_array("CANCELED", $arVisibleColumns))
		{
			$fieldValue .= '<span id="cancel_'.$arOrder['ID'].'">'.(($arOrder["CANCELED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
			$fieldValueTmp = $arOrder["DATE_CANCELED"];
			if(IntVal($arOrder["DATE_CANCELED"]) > 0)
			{
				if(IntVal($arOrder["EMP_CANCELED_ID"]) > 0)
					$fieldValueTmp .= '<br />'.$formattedUserNames[$arOrder["EMP_CANCELED_ID"]];

				if(!$bExport)
				{
					$sScript .= "
						new top.BX.CHint({
							parent: top.BX('cancel_".$arOrder["ID"]."'),
							show_timeout: 10,
							hide_timeout: 100,
							dx: 2,
							preventHide: true,
							min_width: 250,
							hint: '".CUtil::JSEscape($fieldValueTmp)."'
						});
					";
				}
			}
		}
		$row->AddField("CANCELED", $fieldValue, true);
	}
	else
	{
		$row->AddCheckField("CANCELED");
	}

	//STATUS
	if(in_array("STATUS", $arVisibleColumns))
	{
		if($row->bEditMode == true)
		{
			if($saleModulePermissions < "W")
			{
				$allowedStatusesFrom = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('from'));
				$isStatusAllowed = in_array($arOrder["STATUS_ID"], $allowedStatusesFrom);
			}
			else
			{
				$isStatusAllowed = true;
			}
		}

		if($row->bEditMode != true
			|| $row->bEditMode == true && !$isStatusAllowed)
		{
			$fieldValue = "";
			if(in_array("STATUS", $arVisibleColumns))
			{
				if(!isset($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]])
					|| empty($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]))
				{
					$arStatus =  StatusTable::getList(array(
						'select' => array(
							'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
						),
						'filter' => array(
							'=ID' => $arOrder["STATUS_ID"],
							'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
							'=TYPE' => 'O'
						),
						'limit'  => 1,
					))->fetch();

					if($arStatus)
						$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]] = htmlspecialcharsEx($arStatus["NAME"]);
				}

				$fieldValue .= "[";
				if($saleModulePermissions >= "W")
					$fieldValue .= '<a href="/bitrix/admin/sale_status_edit.php?ID='.$arOrder["STATUS_ID"].'&lang='.LANGUAGE_ID.'">';
				$fieldValue .= $arOrder["STATUS_ID"];
				if($saleModulePermissions >= "W")
					$fieldValue .= "</a>";

				$fieldValue .= "] ".$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]."<br />";

				$fieldValue .= $arOrder["DATE_STATUS"];

				if(IntVal($arOrder["EMP_STATUS_ID"]) > 0)
					$fieldValue .= '<br />'.$formattedUserNames[$arOrder["EMP_STATUS_ID"]];
			}
			$row->AddField("STATUS", $fieldValue, true);
		}
		else
		{
			if($row->VarsFromForm() && $_REQUEST["FIELDS"])
				$val = $_REQUEST["FIELDS"][$f_ID]["STATUS_ID"];
			else
				$val = $f_STATUS_ID;

			$fieldValue = '<select name="FIELDS['.$f_ID.'][STATUS_ID]">';

			$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
				$USER->GetID(),
				\Bitrix\Sale\OrderStatus::getInitialStatus()
			);

			foreach($statusesList as $statusId => $statusName)
				$fieldValue .= '<option value="'.$statusId.'"'.(($statusId == $val) ? " selected" : "").">[".$statusId."] ".htmlspecialcharsbx($statusName)."</option>";

			$fieldValue .= "</select>";
			$row->AddEditField("STATUS", $fieldValue);
		}
	}

	//STATUS_ID
	if(in_array("STATUS_ID", $arVisibleColumns))
	{
		$arStatusList = false;
		if($row->bEditMode)
		{
			if($saleModulePermissions < "W")
			{
				$allowedStatusesFrom = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('from'));
				$isStatusAllowed = in_array($arOrder["STATUS_ID"], $allowedStatusesFrom);
			}
			else
			{
				$isStatusAllowed = true;
			}
		}

		if($row->bEditMode !== true
			|| $row->bEditMode && !$isStatusAllowed)
		{
			$fieldValue = "";
			$fieldValueTmp = "";
			if(in_array("STATUS_ID", $arVisibleColumns))
			{
				if(!isset($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]])
					|| empty($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]))
				{
					$arStatus =  StatusTable::getList(array(
						'select' => array(
							'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
						),
						'filter' => array(
							'=ID' => $arOrder["STATUS_ID"],
							'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
							'=TYPE' => 'O'
						),
						'limit'  => 1,
					))->fetch();

					if($arStatus)
						$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]] = htmlspecialcharsEx($arStatus["NAME"]);
				}

				$fieldValueTmp .= "[";

				if($saleModulePermissions >= "W")
					$fieldValueTmp .= '<a href="/bitrix/admin/sale_status_edit.php?ID='.$arOrder["STATUS_ID"].'&lang='.LANGUAGE_ID.'">';

				$fieldValueTmp .= $arOrder["STATUS_ID"];

				if($saleModulePermissions >= "W")
					$fieldValueTmp .= "</a>";

				$fieldValueTmp .= "] ".$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]];
				$fieldValue .= '<span id="status_order_'.$arOrder["ID"].'">'.$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]].'</span>';
				$fieldValueTmp .= "<br />".$arOrder["DATE_STATUS"];

				if(IntVal($arOrder["EMP_STATUS_ID"]) > 0)
					$fieldValueTmp .= '<br />'.$formattedUserNames[$arOrder["EMP_STATUS_ID"]];

				if(!$bExport)
				{
					$sScript .= "
						new top.BX.CHint({
							parent: top.BX('status_order_".$arOrder["ID"]."'),
							show_timeout: 10,
							hide_timeout: 100,
							dx: 2,
							preventHide: true,
							min_width: 250,
							hint: '".CUtil::JSEscape($fieldValueTmp)."'
						});
					";
				}
			}
			$row->AddField("STATUS_ID", $fieldValue, true);
		}
		else
		{
			if($row->VarsFromForm() && $_REQUEST["FIELDS"])
				$val = $_REQUEST["FIELDS"][$f_ID]["STATUS_ID"];
			else
				$val = $f_STATUS_ID;

			$arStatusList = Array();
			$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
				$USER->GetID(),
				\Bitrix\Sale\OrderStatus::getInitialStatus()
			);

			foreach($statusesList as $statusId => $statusName)
				$arStatusList[$statusId] = "[".$statusId."] ".$statusName;

			$row->AddSelectField("STATUS_ID", $arStatusList);
		}
	}

	$row->AddField("PRICE_DELIVERY", '<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"])).'</span>');

	//MARKED
	$fieldValue = "";
	if(in_array("MARKED", $arVisibleColumns))
	{
		$fieldValue .= '<span id="MARKED_'.$arOrder['ID'].'" style="'.(($arOrder["MARKED"] == "Y") ? "color: #ff2400;" : "").'" >'.(($arOrder["MARKED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";
		$fieldValueTmp = $arOrder["DATE_MARKED"];
		if(strlen($arOrder["DATE_MARKED"]) > 0)
		{
			if(IntVal($arOrder["EMP_MARKED_ID"]) > 0)
				$fieldValueTmp .= '<br />'.$formattedUserNames[$arOrder["EMP_MARKED_ID"]];

			if($arOrder["MARKED"] == "Y" && isset($arOrder["REASON_MARKED"]) && strlen($arOrder["REASON_MARKED"]) > 0)
			{
				$fieldValueTmp .= "<br/>".$arOrder["REASON_MARKED"];
			}

			if(!$bExport)
			{
				$sScript .= "
						new top.BX.CHint({
							parent: top.BX('MARKED_".$arOrder["ID"]."'),
							show_timeout: 10,
							hide_timeout: 100,
							dx: 2,
							preventHide: true,
							min_width: 250,
							hint: '".CUtil::JSEscape($fieldValueTmp)."'
						});
				";
			}
		}
	}
	$row->AddField("MARKED", $fieldValue);

	$fieldValue = "";
	if(in_array("REASON_MARKED", $arVisibleColumns))
		$fieldValue = '<span id="REASON_MARKED_'.$arOrder["ID"].'" style="'.(($arOrder["MARKED"] == "Y") ? "color: #ff2400;" : "").'" >'.(($arOrder["MARKED"] == "Y") ? htmlspecialcharsbx($arOrder["REASON_MARKED"]) : "")."</span>";

	$row->AddField("REASON_MARKED", $fieldValue);

	$row->AddField("PRICE", '<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"])).'</span>');
	$row->AddField("SUM_PAID", '<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"])).'</span>');

	$fieldValue = "";

	if(in_array("USER", $arVisibleColumns))
		$fieldValue = GetFormatedUserName($arOrder["USER_ID"], false);

	$row->AddField("USER", $fieldValue);

	$paySystemsFields = array("PAY_SYSTEM_ID", "PAY_SYSTEM", "PAYMENTS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "PS_STATUS", "PS_SUM");
	$shipmentFields = array_intersect($arVisibleColumns, $paySystemsFields);
	if(!empty($shipmentFields))
	{
		$payments = array();
		/** @var \Bitrix\Main\DB\Result $res */
		$res = \Bitrix\Sale\Internals\PaymentTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ORDER_ID' => $arOrder['ID'])
		));
		while($payment = $res->fetch())
		{
			$payment["ID_LINKED"] = '[<a href="/bitrix/admin/sale_order_payment_edit.php?order_id='.$arOrder['ID'].'&payment_id='.$payment["ID"].'&lang='.LANGUAGE_ID.'">'.$payment["ID"].'</a>]';
			$payments[] = $payment;
		}
		unset($payment, $res);

		//PAYMENTS
		$fieldValue = "";
		if(in_array("PAYMENTS", $arVisibleColumns))
		{
			$paymentCount = count($payments);
			foreach($payments as $payment)
			{
				$fieldValue .= $payment["ID_LINKED"].", ".
					htmlspecialcharsbx($payment["PAY_SYSTEM_NAME"]).", ".
					($payment["PAID"] == "Y" ? Loc::getMessage("SOA_PAYMENTS_PAID") :  Loc::getMessage("SOA_PAYMENTS_UNPAID")).", ".
					(strlen($payment["PS_STATUS"]) > 0 ? Loc::getMessage("SOA_PAYMENTS_STATUS").": ".htmlspecialcharsbx($payment["PS_STATUS"]).", " : "").
					'<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency($payment["SUM"], $payment["CURRENCY"])).'<span>';

				if($paymentCount > 1)
				{
					if ($bExport)
						$fieldValue .= "<br>";
					else
						$fieldValue .= "<hr>";
				}
			}
		}
		$row->AddField("PAYMENTS", $fieldValue);

		//PAY_SYSTEM
		$fieldValue = "";
		if(in_array("PAY_SYSTEM", $arVisibleColumns))
		{
			foreach($payments as $payment)
			{
				$tmp = "";

				if($saleModulePermissions >= "W")
					$tmp .= '<a href="/bitrix/admin/sale_pay_system_edit.php?ID='.$payment["PAY_SYSTEM_ID"].'&lang='.LANGUAGE_ID.'">';

				$tmp .= htmlspecialcharsbx($payment["PAY_SYSTEM_NAME"]);

				if($saleModulePermissions >= "W")
					$tmp .= "</a>";

				if(count($payments) > 1)
					$fieldValue .= $payment["ID_LINKED"].", ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}
		$row->AddField("PAY_SYSTEM", $fieldValue);

		//PAY_VOUCHER_NUM
		$fieldValue = "";
		if(in_array("PAY_VOUCHER_NUM", $arVisibleColumns))
		{
			foreach($payments as $payment)
			{
				$tmp = htmlspecialcharsbx($payment["PAY_VOUCHER_NUM"]);

				if(count($payments) > 1)
					$fieldValue .= $payment["ID_LINKED"].", ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}
		$row->AddField("PAY_VOUCHER_NUM", $fieldValue);

		//PAY_VOUCHER_DATE
		$fieldValue = "";
		if(in_array("PAY_VOUCHER_DATE", $arVisibleColumns))
		{
			foreach($payments as $payment)
			{
				$tmp = $payment["PAY_VOUCHER_DATE"];

				if(count($payments) > 1)
					$fieldValue .= $payment["ID_LINKED"].", ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}
		$row->AddField("PAY_VOUCHER_DATE", $fieldValue);

		//PS_STATUS
		$fieldValue = "";
		if(in_array("PS_STATUS", $arVisibleColumns))
		{
			foreach($payments as $payment)
			{
				$tmp = "";

				if($payment["PS_STATUS"] == "Y")
					$tmp = Loc::getMessage("SO_SUCCESS")."<br />".$payment["PS_RESPONSE_DATE"];
				elseif($payment["PS_STATUS"] == "N")
					$tmp = Loc::getMessage("SO_UNSUCCESS")."<br />".$payment["PS_RESPONSE_DATE"];
				else
					$tmp = Loc::getMessage("SO_NONE");

				if(count($payments) > 1)
					$fieldValue .= $payment["ID_LINKED"].", ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}
		$row->AddField("PS_STATUS", $fieldValue);

		//PS_SUM
		$fieldValue = "";
		if(in_array("PS_SUM", $arVisibleColumns))
		{
			foreach($payments as $payment)
			{
				$tmp = '<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency(floatval($payment["PS_SUM"]), $payment["PS_CURRENCY"])).'</span>';

				if(count($payments) > 1)
					$fieldValue .= $payment["ID_LINKED"].", ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}

		$row->AddField("PS_SUM", $fieldValue);
	}

	$row->AddField("DATE_UPDATE", $arOrder["DATE_UPDATE"]);
	$row->AddField("TAX_VALUE", '<span style="white-space:nowrap;">'.htmlspecialcharsex(SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"])).'</span>');

	//BASKET POSITIONS
	$fieldValue = "";
	$fieldName = "";
	$fieldQuantity = "";
	$fieldProductID = "";
	$fieldPrice = "";
	$fieldWeight = "";
	$fieldNotes = "";
	$fieldDiscountPrice = "";
	$fieldCatalogXML = "";
	$fieldProductXML = "";
	$fieldDiscountName  = "";
	$fieldDiscountValue  = "";
	$fieldVatRate  = "";

	if($bNeedBasket)
	{
		$bNeedLine = false;
		$arElementId = array();

		$parentItemFound = false;

		foreach ($arBasketItems as $arItem)
		{
			$arElementId[] = $arItem["PRODUCT_ID"];

			if(CSaleBasketHelper::isSetParent($arItem) || CSaleBasketHelper::isSetItem($arItem))
				$parentItemFound = true;
		}

		if($parentItemFound === true && !empty($arBasketItems) && is_array($arBasketItems))
			$arBasketItems = CSaleBasketHelper::reSortItems($arBasketItems);

		$arBasketItems = getMeasures($arBasketItems);

		foreach ($arBasketItems as $arItem)
		{
			$measure = (isset($arItem["MEASURE_TEXT"])) ? $arItem["MEASURE_TEXT"] : Loc::getMessage("SO_SHT");

			if($bNeedLine && !CSaleBasketHelper::isSetItem($arItem))
			{
				$fieldName .= $basketSeparator;
				$fieldQuantity .= $basketSeparator;
				$fieldProductID .= $basketSeparator;
				$fieldPrice .= $basketSeparator;
				$fieldWeight .= $basketSeparator;
				$fieldNotes .= $basketSeparator;
				$fieldDiscountPrice .= $basketSeparator;
				$fieldCatalogXML .= $basketSeparator;
				$fieldProductXML .= $basketSeparator;
				$fieldDiscountValue  .= $basketSeparator;
				$fieldVatRate  .= $basketSeparator;
			}
			$bNeedLine = true;

			$hidden = "";
			$setItemClass = "";
			$linkClass = "";
			if(CSaleBasketHelper::isSetItem($arItem))
			{
				$hidden = 'style="display:none"';
				$setItemClass = 'class="set_item_'.$arItem["SET_PARENT_ID"].'"';
				$linkClass = "set-item-link-name";
			}

			$fieldValue .= "<div ".$hidden. " ".$setItemClass.">";

			if($arItem['RECOMMENDATION'])
				$fieldValue .= '<div class="bx-adm-bigdata-icon-medium-inner"></div>';

			$fieldValue .= "[".$arItem["PRODUCT_ID"]."] ";

			if(strpos($arItem["DETAIL_PAGE_URL"], "http") === false)
				$url = "http://".$serverName[$arOrder["LID"]].htmlspecialcharsBack($arItem["DETAIL_PAGE_URL"]);
			else
				$url = htmlspecialcharsBack($arItem["DETAIL_PAGE_URL"]);

			if(strlen($arItem["DETAIL_PAGE_URL"]) > 0)
				$fieldValue .= '<a href="'.htmlspecialcharsbx($url).'" class="'.$linkClass.'">';
			$fieldValue .= htmlspecialcharsbx($arItem["NAME"]);
			if(strlen($arItem["DETAIL_PAGE_URL"]) > 0)
				$fieldValue .= "</a>";

			$fieldValue .= " <nobr>(".$arItem["QUANTITY"]." ".$measure.")</nobr>";

			if($bShowBasketProps)
			{
				$dbProp = \Bitrix\Sale\Internals\BasketPropertyTable::getList(array(
					'order' => array("SORT" => "ASC", "ID" => "ASC"),
					'filter' => array("BASKET_ID" => $arItem["ID"], "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID"))
				));

				while($arProp = $dbProp -> fetch())
					if(strlen($arProp["VALUE"]) > 0)
						$fieldValue .= "<div><small>".htmlspecialcharsbx($arProp["NAME"]).": ".htmlspecialcharsbx($arProp["VALUE"])."</small></div>";
			}

			if(CSaleBasketHelper::isSetParent($arItem))
			{
				$fieldValue .= '<div class="set-link-block">';
				$fieldValue	.= '<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_'.$arItem["ID"].'" onclick="fToggleSetItems('.$arItem["ID"].')">'.Loc::getMessage("SOA_SHOW_SET")."</a>";
				$fieldValue .= "</div>";
			}

			if($bNeedLine)
				$fieldValue .= $basketSeparator;

			$fieldValue .= "</div>";

			if(strlen($arItem["NAME"]) > 0)
			{
				$fieldName .= "<nobr>";
				if(strlen($arItem["DETAIL_PAGE_URL"]) > 0)
					$fieldName .= '<a href="'.$url.'">';
				$fieldName .= htmlspecialcharsbx($arItem["NAME"]);
				if(strlen($arItem["DETAIL_PAGE_URL"]) > 0)
					$fieldName .= "</a>";
				$fieldName .= "</nobr>";
			}
			else
				$fieldName .= "<br />";
			if(strlen($arItem["QUANTITY"]) > 0)
				$fieldQuantity .= htmlspecialcharsbx($arItem["QUANTITY"])." ".$measure;
			else
				$fieldQuantity .= "<br />";
			if(strlen($arItem["PRODUCT_ID"]) > 0)
				$fieldProductID .= htmlspecialcharsbx($arItem["PRODUCT_ID"]);
			else
				$fieldProductID .= "<br />";
			if(strlen($arItem["PRICE"]) > 0)
				$fieldPrice .= "<nobr>".htmlspecialcharsex(SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"]))."</nobr>";
			else
				$fieldPrice .= "<br />";
			if(strlen($arItem["WEIGHT"]) > 0)
			{
				if((float)$WEIGHT_KOEF[$arOrder["LID"]] > 0)
					$fieldWeightCalc = (float)($arItem["WEIGHT"]/$WEIGHT_KOEF[$arOrder["LID"]]);
				else
					$fieldWeightCalc = (float)$arItem["WEIGHT"];
				if(!empty($arItem["QUANTITY"]))
				{
					$fieldWeightCalc *= $arItem["QUANTITY"];
				}
				$fieldWeight .= htmlspecialcharsbx(roundEx($fieldWeightCalc, SALE_WEIGHT_PRECISION).' '.$WEIGHT_UNIT[$arOrder["LID"]]);
			}
			else
				$fieldWeight .= "<br />";
			if(strlen($arItem["NOTES"]) > 0)
				$fieldNotes .= $arItem["NOTES"];
			else
				$fieldNotes .= "<br />";
			if(strlen($arItem["DISCOUNT_PRICE"]) > 0)
				$fieldDiscountPrice .= "<nobr>".htmlspecialcharsex(SaleFormatCurrency($arItem["DISCOUNT_PRICE"], $arItem["CURRENCY"]))."</nobr>";
			else
				$fieldDiscountPrice .= "<br />";
			if(strlen($arItem["CATALOG_XML_ID"]) > 0)
				$fieldCatalogXML .= $arItem["CATALOG_XML_ID"];
			else
				$fieldCatalogXML .= "<br />";
			if(strlen($arItem["PRODUCT_XML_ID"]) > 0)
				$fieldProductXML .= $arItem["PRODUCT_XML_ID"];
			else
				$fieldProductXML .= "<br />";
			if(strlen($arItem["DISCOUNT_VALUE"]) > 0)
			{
				$fieldDiscountValue .= roundEx($arItem["DISCOUNT_VALUE"], 2);
				if(strpos($arItem["DISCOUNT_VALUE"], "%") !== false)
					$fieldDiscountValue .= "%";
			}
			else
				$fieldDiscountValue .= "<br />";

			if(strlen($arItem["VAT_RATE"]) > 0)
				$fieldVatRate .= $arItem["VAT_RATE"];
			else
				$fieldVatRate .= "<br />";
		}
		unset($arItem);
	}
	$row->AddField("BASKET", $fieldValue);
	$row->AddField("BASKET_NAME", $fieldName);
	$row->AddField("BASKET_QUANTITY", $fieldQuantity);
	$row->AddField("BASKET_PRODUCT_ID", $fieldProductID);
	$row->AddField("BASKET_PRICE", $fieldPrice);
	$row->AddField("BASKET_WEIGHT", $fieldWeight);
	$row->AddField("BASKET_NOTES", $fieldNotes);
	$row->AddField("BASKET_DISCOUNT_PRICE", $fieldDiscountPrice);
	$row->AddField("BASKET_CATALOG_XML_ID", $fieldCatalogXML);
	$row->AddField("BASKET_PRODUCT_XML_ID", $fieldProductXML);
	$row->AddField("BASKET_DISCOUNT_VALUE", $fieldDiscountValue);
	$row->AddField("BASKET_VAT_RATE", $fieldVatRate);

	if($bNeedProps)
	{
		/** @var \Bitrix\Sale\Order $propOrder */
		$propOrder = \Bitrix\Sale\Order::load($arOrder["ID"]);

		/** @var Bitrix\Sale\PropertyValue  $property */
		foreach($propOrder->getPropertyCollection() as $property)
		{
			$code = $property->getField("CODE");
			$colName = "PROP_".(strlen($code) > 0 ? $code :  $property->getField("ORDER_PROPS_ID"));
			$row->AddField($colName, $property->getViewHtml());
		}
	}
	else
	{
		foreach (($arOrderProps + $arOrderPropsCode) as $key => $value)
			$row->AddField("PROP_".$key, "");
	}

	$row->AddField("EXTERNAL_ORDER", ($arOrder["EXTERNAL_ORDER"] !="Y" ? Loc::getMessage("SO_NO") : Loc::getMessage("SO_YES")));

	//SHIPMENTS etc.
	$shipmentFields = array("SHIPMENTS", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "DELIVERY", "DEDUCTED", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE");
	$shipmentFields = array_intersect($arVisibleColumns, $shipmentFields);
	if(!empty($shipmentFields))
	{
		$shipments = array();
		$res = \Bitrix\Sale\Internals\ShipmentTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array('ORDER_ID' => $arOrder['ID'], '!=SYSTEM' => 'Y')
		));

		while($shipment = $res->fetch())
		{
			$shipment["ID_LINKED"] = '[<a href="/bitrix/admin/sale_order_shipment_edit.php?order_id='.$arOrder['ID'].'&shipment_id='.$shipment["ID"].'&lang='.LANGUAGE_ID.'">'.$shipment["ID"].'</a>]';
			$shipments[] = $shipment;
		}

		if(in_array("SHIPMENTS", $arVisibleColumns))
		{
			$fieldValue = "";

			if (empty($shipmentStatuses))
			{
				$dbRes = StatusTable::getList(array(
					'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
					'filter' => array(
						'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
						'=TYPE' => 'D'
					),
				));

				while ($shipmentStatus = $dbRes->fetch())
					$shipmentStatuses[$shipmentStatus["ID"]] = $shipmentStatus["NAME"]." [".$shipmentStatus["ID"]."]";
			}

			$shipmentCount = count($shipments);
			foreach($shipments as $shipment)
			{
				$fieldValue .= $shipment["ID_LINKED"].", ".
					(strlen($shipment["DELIVERY_NAME"]) > 0 ? htmlspecialcharsbx($shipment["DELIVERY_NAME"]).", " : "").
					'<span style="white-space:nowrap;">'.htmlspecialcharsEx(SaleFormatCurrency($shipment["PRICE_DELIVERY"], $shipment["CURRENCY"]))."</span>, ".
					($shipment["ALLOW_DELIVERY"] == "Y" ? Loc::getMessage("SOA_SHIPMENTS_ALLOW_DELIVERY") : Loc::getMessage("SOA_SHIPMENTS_NOT_ALLOW_DELIVERY")).", ".
					($shipment["CANCELED"] == "Y" ? Loc::getMessage("SOA_SHIPMENTS_CANCELED").", " : "").
					($shipment["DEDUCTED"] == "Y" ? Loc::getMessage("SOA_SHIPMENTS_DEDUCTED").", " : "").
					($shipment["MARKED"] == "Y" ? Loc::getMessage("SOA_SHIPMENTS_MARKED").", " : "").
					(strlen($shipment["TRACKING_NUMBER"]) > 0 ? htmlspecialcharsbx($shipment["TRACKING_NUMBER"]).", " : "");

				if(strlen($shipment["STATUS_ID"]) > 0)
					$fieldValue .= $shipmentStatuses[$shipment["STATUS_ID"]] ? htmlspecialcharsbx($shipmentStatuses[$shipment["STATUS_ID"]]) : Loc::getMessage("SOA_SHIPMENTS_STATUS").": ".$shipment["STATUS_ID"];

				if($shipmentCount > 1)
				{
					if ($bExport)
						$fieldValue .= "<br>";
					else
						$fieldValue .= "<hr>";
				}
			}

			$row->AddField("SHIPMENTS", $fieldValue);
		}

		//ALLOW_DELIVERY
		$fieldValue = "";
		if(in_array("ALLOW_DELIVERY", $arVisibleColumns))
		{
			foreach($shipments as $shipment)
			{
				$tmp = '<span id="allow_deliv_'.$shipment["ID"].'">'.(($shipment["ALLOW_DELIVERY"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";

				if(count($shipments) > 1)
					$fieldValue .=  $shipment["ID_LINKED"]." ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;

				$fieldValueTmp = $shipment["DATE_ALLOW_DELIVERY"];
				if(strlen($shipment["DATE_ALLOW_DELIVERY"]) > 0)
				{
					if(IntVal($shipment["EMP_ALLOW_DELIVERY_ID"]) > 0)
						$fieldValueTmp .= '<br />'.$formattedUserNames[$shipment["EMP_ALLOW_DELIVERY_ID"]];

					if(!$bExport)
					{
						$sScript .= "
							new top.BX.CHint({
								parent: top.BX('allow_deliv_".$shipment["ID"]."'),
								show_timeout: 10,
								hide_timeout: 100,
								dx: 2,
								preventHide: true,
								min_width: 250,
								hint: '".CUtil::JSEscape($fieldValueTmp)."'
							});
						";
					}
				}

				$row->AddField("ALLOW_DELIVERY", $fieldValue);
			}
		}

		//DATE_ALLOW_DELIVERY
		$fieldValue = "";
		foreach($shipments as $shipment)
		{
			$tmp = strlen($shipment["DATE_ALLOW_DELIVERY"]) > 0 ? $shipment["DATE_ALLOW_DELIVERY"] : Loc::getMessage("SO_NO");

			if(count($shipments) > 1)
				$fieldValue .= $shipment["ID_LINKED"]." ".$tmp."<hr>";
			else
				$fieldValue .= $tmp;
		}
		$row->AddField("DATE_ALLOW_DELIVERY", $fieldValue);

		//DELIVERY
		$fieldValue = "";
		if(in_array("DELIVERY", $arVisibleColumns))
		{
			foreach($shipments as $shipment)
			{
				$tmp = "";

				if($saleModulePermissions >= "W")
					$tmp .= '<a href="/bitrix/admin/sale_delivery_service_edit.php?ID='.$shipment["DELIVERY_ID"].'&lang='.LANGUAGE_ID.'">';

				$tmp .= htmlspecialcharsbx($shipment["DELIVERY_NAME"]);

				if($saleModulePermissions >= "W")
					$tmp .= "</a>";

				if(count($shipments) > 1)
					$fieldValue .= $shipment["ID_LINKED"]." ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;
			}
		}

		$row->AddField("DELIVERY", $fieldValue);

		//DEDUCTED
		$fieldValue = "";
		if(in_array("DEDUCTED", $arVisibleColumns))
		{
			foreach($shipments as $shipment)
			{
				$tmp = '<span id="DEDUCTED_'.$shipment["ID"].'">'.(($shipment["DEDUCTED"] == "Y") ? Loc::getMessage("SO_YES") : Loc::getMessage("SO_NO"))."</span>";

				if(count($shipments) > 1)
					$fieldValue .= $shipment["ID_LINKED"]." ".$tmp."<hr>";
				else
					$fieldValue .= $tmp;

				$fieldValueTmp = $shipment["DATE_DEDUCTED"];
				if(strlen($shipment["DATE_DEDUCTED"]) > 0)
				{
					if(IntVal($shipment["EMP_DEDUCTED_ID"]) > 0)
						$fieldValueTmp .= '<br />'.$formattedUserNames[$shipment["EMP_DEDUCTED_ID"]];

					if(!$bExport)
					{
						$sScript .= "
							new top.BX.CHint({
								parent: top.BX('DEDUCTED_".$shipment["ID"]."'),
								show_timeout: 10,
								hide_timeout: 100,
								dx: 2,
								preventHide: true,
								min_width: 250,
								hint: '".CUtil::JSEscape($fieldValueTmp)."'
							});
					";
					}
				}
			}
		}
		$row->AddField("DEDUCTED", $fieldValue);

		//DELIVERY_DOC_NUM
		$fieldValue = "";
		foreach($shipments as $shipment)
		{
			$tmp = strlen($shipment["DELIVERY_DOC_NUM"]) > 0 ? htmlspecialcharsbx($shipment["DELIVERY_DOC_NUM"]) : Loc::getMessage("SO_NO");

			if(count($shipments) > 1)
				$fieldValue .= $shipment["ID_LINKED"]." ".$tmp."<hr>";
			else
				$fieldValue .= $tmp;
		}
		$row->AddField("DELIVERY_DOC_NUM", $fieldValue);

		//DELIVERY_DOC_DATE
		$fieldValue = "";
		foreach($shipments as $shipment)
		{
			$tmp = strlen($shipment["DELIVERY_DOC_DATE"]) > 0 ? $shipment["DELIVERY_DOC_DATE"] : Loc::getMessage("SO_NO");

			if(count($shipments) > 1)
				$fieldValue .= $shipment["ID_LINKED"]." ".$tmp."<hr>";
			else
				$fieldValue .= $tmp;
		}
		$row->AddField("DELIVERY_DOC_DATE", $fieldValue);
	}

	$row->AddViewField('BASKET_DISCOUNT_COUPON', ' ');
	$row->AddViewField('BASKET_DISCOUNT_NAME', ' ');

	$arActions = array();

	if(($arOrder['LOCK_STATUS'] == "red" && $saleModulePermissions >= "W") || $arOrder['LOCK_STATUS'] == "yellow")
	{
		$arActions[] = array(
			"ICON" => "unlock",
			"TEXT" => Loc::getMessage("IBEL_A_UNLOCK"),
			"TITLE" => Loc::getMessage("IBLOCK_UNLOCK_ALT"),
			"ACTION" => $lAdmin->ActionDoGroup($arOrder["ID"], "unlock", '')
		);
		$arActions[] = array("SEPARATOR" => true);
	}

	$arActions[] = array("ICON"=>"view", "TEXT"=>Loc::getMessage("SALE_DETAIL_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_view.php?ID=".$f_ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")), "DEFAULT"=>true);
	$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("SOA_ORDER_COPY"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_create.php?ID=".$f_ID."&lang=".LANGUAGE_ID."&".'SITE_ID='.$f_LID.'&'.bitrix_sessid_get().GetFilterParams("filter_")));
	$arActions[] = array("ICON"=>"print", "TEXT"=>Loc::getMessage("SALE_PRINT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_print.php?ID=".$f_ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")));
	if($arOrder['LOCK_STATUS'] != "red")
	{
		if(CSaleOrder::CanUserUpdateOrder($f_ID, $arUserGroups))
			$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_OEDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")));
		if($saleModulePermissions == "W"
			|| $f_PAYED != "Y" && CSaleOrder::CanUserDeleteOrder($f_ID, $arUserGroups, $intUserID))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".Loc::getMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
		}
	}

	$row->AddActions($arActions);
}

if (!empty($rowsList))
{
	if (in_array('BASKET_DISCOUNT_NAME', $arVisibleColumns))
	{
		$discountList = array();
		$discountsIterator = Sale\Internals\OrderRulesTable::getList(array(
			'select' => array('ORDER_ID', 'DISCOUNT_NAME' => 'ORDER_DISCOUNT.NAME', 'DISCOUNT_ID' => 'ORDER_DISCOUNT.ID'),
			'filter' => array('@ORDER_ID' => array_keys($rowsList)),
			'order' => array('ORDER_ID' => 'ASC', 'ID' => 'ASC')
		));
		while ($discount = $discountsIterator->fetch())
		{
			$discount['ORDER_ID'] = (int)$discount['ORDER_ID'];
			$discount['DISCOUNT_ID'] = (int)$discount['DISCOUNT_ID'];
			$discount['DISCOUNT_NAME'] = (string)$discount['DISCOUNT_NAME'];
			if (!isset($discountList[$discount['ORDER_ID']]))
				$discountList[$discount['ORDER_ID']] = array();
			$discountList[$discount['ORDER_ID']][$discount['DISCOUNT_ID']] = ($discount['DISCOUNT_NAME'] != '' ? $discount['DISCOUNT_NAME'] : $discount['DISCOUNT_ID']);
		}
		unset($discount, $discountsIterator);
		if (!empty($discountList))
		{
			foreach ($discountList as $order => $orderDiscounts)
				$rowsList[$order]->AddViewField('BASKET_DISCOUNT_NAME', implode('<br><br>', $orderDiscounts));
			unset($order, $orderDiscounts);
		}
		unset($discountList);
	}
	if (in_array('BASKET_DISCOUNT_COUPON', $arVisibleColumns))
	{
		$couponsList = array();
		$couponsIterator = Sale\Internals\OrderCouponsTable::getList(array(
			'select' => array('ORDER_ID', 'COUPON'),
			'filter' => array('@ORDER_ID' => array_keys($rowsList)),
			'order' => array('ORDER_ID' => 'ASC', 'ID' => 'ASC')
		));
		while ($coupon = $couponsIterator->fetch())
		{
			$coupon['ORDER_ID'] = (int)$coupon['ORDER_ID'];
			if (!isset($couponsList[$coupon['ORDER_ID']]))
				$couponsList[$coupon['ORDER_ID']] = array();
			$couponsList[$coupon['ORDER_ID']][] = $coupon['COUPON'];
		}
		unset($coupon, $couponsIterator);
		if (!empty($couponsList))
		{
			foreach ($couponsList as $order => $coupons)
				$rowsList[$order]->AddViewField('BASKET_DISCOUNT_COUPON', implode('<br><br>', $coupons));
			unset($order, $coupons);
		}
		unset($couponsList);
	}
}
unset($rowsList);

$arFooterArray = array(
	array(
		"title" => Loc::getMessage('SOAN_FILTERED1').":",
		"value" => $dbOrderList->SelectedRowsCount()
	),
);

// recommendation summary
$rcmValue = array();
$rcmCount = 0;

$runtime = array(
	new \Bitrix\Main\Entity\ExpressionField('SUM', 'SUM(sale_internals_order.PRICE)')
);

if(!empty($runtimeFields) && is_array($runtimeFields))
	$runtime = 	array_merge($runtime, $runtimeFields);

$getListParamsSum = array(
	'order' => array("CURRENCY" => "ASC"),
	'filter' => $arFilterTmp,
	'group' => array("CURRENCY"),
	'select' => array("CURRENCY", "SUM"),
	'runtime' => $runtime
);

if($saleModulePermissions == "W")
{
	$dbOrderList = \Bitrix\Sale\Internals\OrderTable::getList($getListParamsSum);

	while ($arOrderList = $dbOrderList->fetch())
	{
		$arFooterArray[] = array(
			"title" => Loc::getMessage("SOAN_ITOG")." ".$arOrderList["CURRENCY"].":",
			"value" => htmlspecialcharsex(SaleFormatCurrency($arOrderList["SUM"], $arOrderList["CURRENCY"]))
		);
	}

	// recommendation summary
	$rcmFilter = $arFilterTmp;
	$rcmFilter['>BASKET.RECOMMENDATION'] = 0;
}
elseif ($saleModulePermissions < "W")
{
	// also count recommendation stats
	$rcmValue = array();
	$rcmValueCur = array();
	$rcmCount = 0;
	$arOrdersSum = array();

	$arGroupByTmp[] = 'BASKET.RECOMMENDATION';
	$arGroupByTmp[] = 'BASKET_PRICE_TOTAL';
	$arGroupByTmp[] = 'BASKET.CURRENCY';

	$orderFilter = array(
		'order' => array('ID' => 'asc'),
		'filter' => $arFilterTmp,
		'group' => $arGroupByTmp,
		'select' => array(
			'ID',
			'CURRENCY',
			'PRICE',
			'BASKET_RECOMMENDATION' => 'BASKET.RECOMMENDATION',
			'BASKET_PRICE_TOTAL',
			'BASKET_CURRENCY' => 'BASKET.CURRENCY'
		)
	);

	if (!empty($runtimeFields) && is_array($runtimeFields))
		$orderFilter['runtime'] = $runtimeFields;

	$dbOrderList = \Bitrix\Sale\Internals\OrderTable::getList($orderFilter);

	$previousId = 0;

	while ($arOrder = $dbOrderList->Fetch())
	{
		if($arOrder['ID'] != $previousId)
		{
			if(!array_key_exists($arOrder["CURRENCY"], $arOrdersSum))
				$arOrdersSum[$arOrder["CURRENCY"]] = 0;
			$arOrdersSum[$arOrder["CURRENCY"]] += $arOrder["PRICE"];

			$previousId = $arOrder['ID'];
		}

		// recommendation stats
		if($arOrder['BASKET_RECOMMENDATION'])
		{
			if(!array_key_exists($arOrder["BASKET_CURRENCY"], $rcmValue))
			{
				$rcmValueCur[$arOrder["BASKET_CURRENCY"]] = 0;
			}

			$rcmValueCur[$arOrder["BASKET_CURRENCY"]] += $arOrder["BASKET_PRICE_TOTAL"];
			$rcmCount++;
		}
	}

	// show summary
	if (COption::GetOptionString("sale", "show_order_sum", "N")=="Y")
	{
		foreach ($arOrdersSum as $key => $value)
		{
			$arFooterArray[] = array(
			"title" => Loc::getMessage("SOAN_ITOG")." ".$key.":",
				"value" => $value
			);
		}
	}

	// recommendation stats
	foreach ($rcmValueCur as $currency => $sum)
	{
		$rcmValue[] = SaleFormatCurrency($sum, $currency);
	}
}
$order_sum = "";
foreach($arFooterArray as $val)
{
	$order_sum .= $val["title"]." ".$val["value"]."<br />";
}

$arResult = array(
	'RECOMMENDATION_ORDERS_COUNT' => $rcmCount,
	'RECOMMENDATION_ORDERS_VALUE' => htmlspecialcharsex(join(' / ', $rcmValue))
);

// prepare recommendation widget
ob_start();
?>

	<div class="adm-c-bigdatabar-container">
		<div class="adm-c-bigdatabar-summ"><?=Loc::getMessage('SALE_BIGDATA_SUM')?>
			<?if(!empty($arResult['RECOMMENDATION_ORDERS_VALUE'])):?>
				<strong><?=$arResult['RECOMMENDATION_ORDERS_VALUE']?></strong>
			<? else: ?>
				<?=Loc::getMessage('SALE_BIGDATA_SALES_NODATA')?>
			<? endif; ?>
		</div>
		<div class="adm-c-bigdatabar-content">
			<div class="adm-c-bigdatabar-line">
				<strong><?=Loc::getMessage('SALE_BIGDATA_SALES_TITLE')?></strong> <?=Loc::getMessage('SALE_BIGDATA_SALES_COUNT')?> <?=$arResult['RECOMMENDATION_ORDERS_COUNT']?>
			</div>
			<div class="adm-c-bigdatabar-line">
				<? $installed = (time()-Bitrix\Main\Config\Option::get('main', 'rcm_component_usage', 0)<3600*24);?>
				<? if($installed): ?>
					<span class="adm-c-bigdatabar-line-task"><?=Loc::getMessage('SALE_BIGDATA_WIDGET_ENABLED')?></span>
				<? else: ?>
					<span class="adm-c-bigdatabar-line-task bx-not-available"><?=Loc::getMessage('SALE_BIGDATA_WIDGET_DISABLED')?></span>
				<? endif; ?>

				<? $available = \Bitrix\Main\Analytics\Catalog::isOn(); ?>
				<? if($available): ?>
					<span class="adm-c-bigdatabar-line-task"><?=Loc::getMessage('SALE_BIGDATA_IS_ON')?></span>
				<? else: ?>
					<span class="adm-c-bigdatabar-line-task bx-not-available"><?=Loc::getMessage('SALE_BIGDATA_IS_OFF')?></span>
				<? endif; ?>

				<a href="sale_personalization.php?lang=<?=LANGUAGE_ID?>" class="adm-c-bigdatabar-line-task-link"><?=Loc::getMessage('SALE_BIGDATA_ABOUT')?></a>
			</div>
		</div>
		<div class="clb"></div>
	</div>
<?
$bigdataWidgetHtml = ob_get_contents();
ob_end_clean();

$lAdmin->BeginEpilogContent();
echo "<script>", $sScript, "\nif(document.getElementById('order_sum')) {setTimeout(function(){document.getElementById('order_sum').innerHTML = '".CUtil::JSEscape($order_sum)."';}, 10);}\n","</script>";
echo "<script>", $sScript, "\nif(document.getElementById('bigdatabar')) {setTimeout(function(){document.getElementById('bigdatabar').innerHTML = '".CUtil::JSEscape($bigdataWidgetHtml)."';}, 10);}\n","</script>";
?>
<script>
function exportData(val)
{
	var oForm = document.form_<?= $sTableID ?>;
	var expType = oForm.action_target.checked;

	var par = "mode=excel";
	if(!expType)
	{
		var num = oForm.elements.length;
		for (var i = 0; i < num; i++)
		{
			if(oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				par += "&OID[]=" + oForm.elements[i].value;
			}
		}
	}

	if(expType)
	{
		par += "<?= CUtil::JSEscape(GetFilterParams("filter_", false)); ?>";
	}

	if(par.length > 0)
	{
		window.open("sale_order_export.php?EXPORT_FORMAT="+val+"&"+par, "vvvvv");
	}
}
</script>
<?
$lAdmin->EndEpilogContent();

$arGroupActionsTmp = array(
	"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
	"cancel" => Loc::getMessage("SOAN_LIST_CANCEL"),
	"cancel_n" => Loc::getMessage("SOAN_LIST_CANCEL_N"),
);

	$allowedStatusesFrom = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('from'));

	foreach($allowedStatusesFrom as $status)
	{
		if(!isset($LOCAL_STATUS_CACHE[$status])
			|| empty($LOCAL_STATUS_CACHE[$status]))
		{
			$arStatus =  StatusTable::getList(array(
				'select' => array(
					'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
				),
				'filter' => array(
					'=ID' => $status,
					'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
					'=TYPE' => 'O'
				),
				'limit'  => 1,
			))->fetch();

			if($arStatus)
				$LOCAL_STATUS_CACHE[$status] = htmlspecialcharsEx($arStatus["NAME"]);
		}

		$arGroupActionsTmp["status_".$status] = Loc::getMessage("SOAN_LIST_STATUS_CHANGE").' "'.$LOCAL_STATUS_CACHE[$status].'"';
	}

	$arGroupActionsTmp["export_csv"] = array(
			"action" => "exportData('csv')",
			"value" => "export_csv",
			"name" => str_replace("#EXP#", "CSV", Loc::getMessage("SOAN_EXPORT_2"))
		);
	$arGroupActionsTmp["export_commerceml"] = array(
			"action" => "exportData('commerceml')",
			"value" => "export_commerceml",
			"name" => str_replace("#EXP#", "CommerceML", Loc::getMessage("SOAN_EXPORT_2"))
		);
	$arGroupActionsTmp["export_commerceml2"] = array(
			"action" => "exportData('commerceml2')",
			"value" => "export_commerceml2",
			"name" => str_replace("#EXP#", "CommerceML 2.0", Loc::getMessage("SOAN_EXPORT_2"))
		);

$strPath2Export = BX_PERSONAL_ROOT."/php_interface/include/sale_export/";
if(file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export))
{
	if($handle = opendir($_SERVER["DOCUMENT_ROOT"].$strPath2Export))
	{
		while (($file = readdir($handle)) !== false)
		{
			if($file == "." || $file == "..")
				continue;
			if(is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file) && substr($file, strlen($file)-4)==".php")
			{
				$export_name = substr($file, 0, strlen($file) - 4);
				$arGroupActionsTmp["export_".$export_name] = array(
					"action" => "exportData('".$export_name."')",
					"value" => "export_".$export_name,
					"name" => str_replace("#EXP#", $export_name, Loc::getMessage("SOAN_EXPORT_2"))
				);
			}
		}
	}
	closedir($handle);
}

$lAdmin->AddGroupActionTable($arGroupActionsTmp);
$aContext = array();

if($saleModulePermissions == "U")
	$allowedStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('update'));

if($saleModulePermissions == "W" || ($saleModulePermissions == "U" && !empty($allowedStatusesUpdate)))
{
	$siteLID = "";
	$arSiteMenu = array();
	$arSitesShop = array();
	$arSitesTmp = array();
	$rsSites = CSite::GetList($b = "id", $o = "asc", Array("ACTIVE" => "Y"));
	while ($arSite = $rsSites->GetNext())
	{
		$site = Option::get("sale", "SHOP_SITE_".$arSite["ID"], "");
		if($arSite["ID"] == $site)
		{
			$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
		}
		$arSitesTmp[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}

	$rsCount = count($arSitesShop);
	if($rsCount <= 0)
	{
		$arSitesShop = $arSitesTmp;
		$rsCount = count($arSitesShop);
	}

	if($rsCount == 1)
	{
		$siteLID = "&SITE_ID=".$arSitesShop[0]["ID"];
	}
	else
	{
		foreach ($arSitesShop as &$val)
		{
			$arSiteMenu[] = array(
				"TEXT" => $val["NAME"]." (".$val["ID"].")",
				"ACTION" => "window.location = 'sale_order_create.php?lang=".LANGUAGE_ID."&SITE_ID=".$val["ID"]."';"
			);
		}
		if(isset($val))
			unset($val);
	}

	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("SALE_A_NEWORDER"),
			"ICON" => "btn_new",
			"LINK" => "sale_order_create.php?lang=".LANGUAGE_ID.$siteLID,
			"TITLE" => Loc::getMessage("SALE_A_NEWORDER_TITLE"),
			"MENU" => $arSiteMenu
		),
	);
}

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

\Bitrix\Main\Page\Asset::getInstance()->addString('<style>.adm-filter-item-center, .adm-filter-content {overflow: visible !important;}</style>');

/*********************************************************************/
/********************  PAGE  *****************************************/
/*********************************************************************/

$APPLICATION->SetTitle(Loc::getMessage("SALE_SECTION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<script type="text/javascript">
function fToggleSetItems(setParentId)
{
	var elements = document.getElementsByClassName('set_item_' + setParentId);
	var hide = false;

	for (var i = 0; i < elements.length; ++i)
	{
		if(elements[i].style.display == 'none' || elements[i].style.display == '')
		{
			elements[i].style.display = 'table-row';
			hide = true;
		}
		else
			elements[i].style.display = 'none';
	}

	if(hide)
		BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_HIDE_SET")?>';
	else
		BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_SHOW_SET")?>';
}
</script>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$arFilterFieldsTmp = array(
	"filter_universal" => Loc::getMessage("SOA_ROW_BUYER"),
	"filter_date_insert" => Loc::getMessage("SALE_F_DATE"),
	"filter_date_update" => Loc::getMessage("SALE_F_DATE_UPDATE"),
	"filter_id_from" => Loc::getMessage("SALE_F_ID"),
	"filter_account_number" => Loc::getMessage("SALE_F_ACCOUNT_NUMBER"),
	"filter_currency" => Loc::getMessage("SALE_F_LANG_CUR"),
	"filter_price" => Loc::getMessage("SOA_F_PRICE"),
	"filter_status" => Loc::getMessage("SALE_F_STATUS"),
	"filter_date_status_from" => Loc::getMessage("SALE_F_DATE_STATUS"),
	"filter_by_recommendation" => Loc::getMessage("SALE_F_BY_RECOMMENDATION"),
	"filter_payed" => Loc::getMessage("SALE_F_PAYED"),
	"filter_pay_system" => Loc::getMessage("SALE_F_PAY_SYSTEM"),
	"filter_person_type" => Loc::getMessage("SALE_F_PERSON_TYPE"),
	"filter_canceled" => Loc::getMessage("SALE_F_CANCELED"),
	"filter_deducted" => Loc::getMessage("SALE_F_DEDUCTED"),
	"filter_allow_delivery" => Loc::getMessage("SALE_F_ALLOW_DELIVERY"),
	"filter_date_paid" => Loc::getMessage("SALE_F_DATE_PAID"),
	"filter_date_allow_delivery" => Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY"),
	"filter_marked" => Loc::getMessage("SALE_F_MARKED"),
	"filter_user_id" => Loc::getMessage("SALE_F_USER_ID"),
	"filter_user_login" => Loc::getMessage("SALE_F_USER_LOGIN"),
	"filter_user_email" => Loc::getMessage("SALE_F_USER_EMAIL"),
	"filter_group_id" => Loc::getMessage("SALE_F_USER_GROUP_ID"),
	"filter_product_id" => Loc::getMessage("SO_PRODUCT_ID"),
	"filter_product_xml_id" => Loc::getMessage("SO_PRODUCT_XML_ID"),
	"filter_affiliate_id" => Loc::getMessage("SO_AFFILIATE_ID"),
	"filter_coupon" => Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS"),
	"filter_sum_paid" => Loc::getMessage("SO_SUM_PAID"),
	"filter_xml_id" => Loc::getMessage("SO_XML_ID")
);

foreach (($arOrderProps+$arOrderPropsCode) as $key => $value)
{
	if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
		$arFilterFieldsTmp[] = $value["NAME"];
}

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFilterFieldsTmp
);

$oFilter->SetDefaultRows(array("filter_universal", "filter_status", "filter_canceled"));

$oFilter->AddPreset(array(
		"ID" => "find_prioritet",
		"NAME" => Loc::getMessage("SOA_PRESET_PRIORITET"),
		"FIELDS" => array(
			"filter_status" => "N",
			"filter_price_from" => "10000",
			"filter_price_to" => ""
			),
		//"SORT_FIELD" => array("DATE_INSERT" => "DESC"),
	));

$oFilter->AddPreset(array(
		"ID" => "find_allow_payed",
		"NAME" => Loc::getMessage("SOA_PRESET_PAYED"),
		"FIELDS" => array(
			"filter_canceled" => "N",
			"filter_payed" => "Y"
			),
		//"SORT_FIELD" => array("DATE_UPDATE" => "DESC"),
	));

$oFilter->AddPreset(array(
		"ID" => "find_order_null",
		"NAME" => Loc::getMessage("SOA_PRESET_ORDER_NULL"),
		"FIELDS" => array(
			"filter_canceled" => "N",
			"filter_payed" => "",
			"filter_status" => array("N", "P"),
			"filter_date_update_from_FILTER_PERIOD" => "before",
			"filter_date_update_from_FILTER_DIRECTION" => "previous",
			"filter_date_update_to" => ConvertTimeStamp(AddToTimeStamp(Array("DD" => -7))),
			),
		//"SORT_FIELD" => array("DATE_UPDATE" => "DESC"),
	));

$oFilter->Begin();
?>
	<tr>
		<td><?=Loc::getMessage('SOA_ROW_BUYER')?>:</td>
		<td>
			<input type="text" name="filter_universal" value="<?echo htmlspecialcharsbx($filter_universal)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><b><?echo Loc::getMessage("SALE_F_DATE");?>:</b></td>
		<td>
			<?echo CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DATE_UPDATE");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_update_from", $filter_date_update_from, "filter_date_update_to", $filter_date_update_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ID");?>:</td>
		<td>
			<script type="text/javascript">
				function filter_id_from_Change()
				{
					if(document.find_form.filter_id_to.value.length<=0)
					{
						document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
					}
				}
			</script>
			<?echo Loc::getMessage("SALE_F_FROM");?>
			<input type="text" name="filter_id_from" OnChange="filter_id_from_Change()" value="<?echo (IntVal($filter_id_from)>0)?IntVal($filter_id_from):""?>" size="10">
			<?echo Loc::getMessage("SALE_F_TO");?>
			<input type="text" name="filter_id_to" value="<?echo (IntVal($filter_id_to)>0)?IntVal($filter_id_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ACCOUNT_NUMBER");?>:</td>
		<td>
			<input type="text" name="filter_account_number" value="<?echo htmlspecialcharsEx($filter_account_number)?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_LANG_CUR");?>:</td>
		<td>
			<select name="filter_lang">
				<option value=""><?= htmlspecialcharsex(Loc::getMessage("SALE_F_ALL")) ?></option>
				<?
				$b1 = "SORT";
				$o1 = "ASC";
				$dbSitesList = CLang::GetList($b1, $o1);
				while ($arSitesList = $dbSitesList->Fetch())
				{
					if(!in_array($arSitesList["LID"], $arAccessibleSites)
						&& $saleModulePermissions < "W")
						continue;

					?><option value="<?= htmlspecialcharsbx($arSitesList["LID"])?>"<?if($arSitesList["LID"] == $filter_lang) echo " selected";?>>[<?= htmlspecialcharsex($arSitesList["LID"]) ?>]&nbsp;<?= htmlspecialcharsex($arSitesList["NAME"]) ?></option><?
				}
				?>
			</select>
			/
			<?echo CCurrency::SelectBox("filter_currency", $filter_currency, Loc::getMessage("SALE_F_ALL"), false, "", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SOA_F_PRICE");?>:</td>
		<td>
			<?echo Loc::getMessage("SOA_F_PRICE_FROM");?>
			<input type="text" name="filter_price_from" value="<?=(floatval($filter_price_from)>0)?floatval($filter_price_from):""?>" size="3">

			<?echo Loc::getMessage("SOA_F_PRICE_TO");?>
			<input type="text" name="filter_price_to" value="<?=(floatval($filter_price_to)>0)?floatval($filter_price_to):""?>" size="3">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo Loc::getMessage("SALE_F_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
		<td valign="top">
			<select name="filter_status[]" multiple size="3">
				<?
				$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
					$USER->GetID(),
					\Bitrix\Sale\OrderStatus::getInitialStatus()
				);
				foreach($statusesList as $statusCode => $statusName)
				{
					?><option value="<?= htmlspecialcharsbx($statusCode) ?>"<?if(is_array($filter_status) && in_array($statusCode, $filter_status)) echo " selected"?>>[<?= htmlspecialcharsbx($statusCode) ?>] <?= htmlspecialcharsEx($statusName) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DATE_STATUS");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_status_from", $filter_date_status_from, "filter_date_status_to", $filter_date_status_to, "find_form", "Y")?>
		</td>
	</tr>
	<!--TODO:-->
	<tr>
		<td><?echo Loc::getMessage("SALE_F_BY_RECOMMENDATION")?>:</td>
		<td>
			<select name="filter_by_recommendation">
				<option value=""><?echo GetMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if ($filter_payed=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_payed=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<!---->
	<tr>
		<td><?echo Loc::getMessage("SALE_F_PAYED")?>:</td>
		<td>
			<select name="filter_payed">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_payed=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_payed=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_F_PAY_SYSTEM")?>:</td>
		<td>
			<select name="filter_pay_system[]" multiple size="3">
				<option value=""><?echo GetMessage("SALE_F_ALL")?></option>
				<?
				$l = Sale\Internals\PaySystemTable::getList(array(
					'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
				));

				while ($paySystem = $l->fetch()):
					?><option value="<?echo htmlspecialcharsbx($paySystem["ID"])?>"<?if(is_array($filter_pay_system) && in_array($paySystem["ID"], $filter_pay_system)) echo " selected"?>>[<?echo htmlspecialcharsbx($paySystem["ID"]) ?>] <?echo htmlspecialcharsbx($paySystem["NAME"])?> <?echo "(".htmlspecialcharsbx($paySystem["LID"]).")";?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_PERSON_TYPE");?>:</td>
		<td>
			<select name="filter_person_type[]" multiple size="3">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<?
				$l = CSalePersonType::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array());
				while ($personType = $l->Fetch()):
					?><option value="<?echo htmlspecialcharsbx($personType["ID"])?>"<?if(is_array($filter_person_type) && in_array($personType["ID"], $filter_person_type)) echo " selected"?>>[<?echo htmlspecialcharsbx($personType["ID"]) ?>] <?echo htmlspecialcharsbx($personType["NAME"])?> <?echo "(".htmlspecialcharsbx(implode(", ", $personType["LIDS"])).")";?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_CANCELED")?>:</td>
		<td>
			<select name="filter_canceled">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_canceled=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_canceled=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DEDUCTED")?>:</td>
		<td>
			<select name="filter_deducted">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_deducted=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_deducted=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ALLOW_DELIVERY")?>:</td>
		<td>
			<select name="filter_allow_delivery">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_deducted=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_deducted=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DATE_PAID");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_paid_from", $filter_date_paid_from, "filter_date_paid_to", $filter_date_paid_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_allow_delivery_from", $filter_date_allow_delivery_from, "filter_date_allow_delivery_to", $filter_date_allow_delivery_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_MARKED")?>:</td>
		<td>
			<select name="filter_marked">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_marked=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_marked=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_ID");?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_LOGIN");?>:</td>
		<td>
			<input type="text" name="filter_user_login" value="<?echo htmlspecialcharsEx($filter_user_login)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_EMAIL");?>:</td>
		<td>
			<input type="text" name="filter_user_email" value="<?echo htmlspecialcharsEx($filter_user_email)?>" size="40">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_USER_GROUP_ID")?>:</td>
		<td>
			<?
			$z = CGroup::GetDropDownList("AND ID!=2");
			echo SelectBoxM("filter_group_id[]", $z, $filter_group_id, "", false, 5);
			?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SO_PRODUCT_ID")?></td>
		<td>
			<script type="text/javascript">
			function FillProductFields(arParams)
			{
				if(arParams["id"])
					document.find_form.filter_product_id.value = arParams["id"];

				el = document.getElementById("product_name_alt");
				if(el)
					el.innerHTML = arParams["name"] ? arParams["name"] : '';
			}

			function showProductSearchDialog()
			{
				var popup = makeProductSearchDialog({
					caller: 'order',
					lang: '<?=LANGUAGE_ID?>',
					callback: 'FillProductFields'
				});
				popup.Show();
			}

			function makeProductSearchDialog(params)
			{
				var caller = params.caller || '',
					lang = params.lang || 'ru',
					site_id = params.site_id || '',
					callback = params.callback || '',
					store_id = params.store_id || '0';

				var popup = new BX.CDialog({
					content_url: '/bitrix/admin/cat_product_search_dialog.php?lang='+lang+'&LID='+site_id+'&caller=' + caller + '&func_name='+callback+'&STORE_FROM_ID='+store_id,
					height: Math.max(500, window.innerHeight-400),
					width: Math.max(800, window.innerWidth-400),
					draggable: true,
					resizable: true,
					min_height: 500,
					min_width: 800
				});
				BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function(){
					popup.Get().style.position = 'fixed';
					popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
				}));
				return popup;
			}
			</script>
			<input name="filter_product_id" value="<?= htmlspecialcharsbx($filter_product_id) ?>" size="5" type="text">&nbsp;<input type="button" value="..." id="cat_prod_button" onClick="showProductSearchDialog()"><span id="product_name_alt" class="adm-filter-text-search"></span>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("SO_PRODUCT_XML_ID") ?>:</td>
		<td><input name="filter_product_xml_id" value="<?= htmlspecialcharsbx($filter_product_xml_id) ?>" size="40" type="text"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("SO_AFFILIATE_ID") ?>:</td>
		<td>
			<input type="text" name="filter_affiliate_id" value="<?= htmlspecialcharsbx($filter_affiliate_id) ?>" size="10" maxlength="10">
			<IFRAME name="hiddenframe_affiliate" id="id_hiddenframe_affiliate" src="" width="0" height="0" style="width:0px; height:0px; border: 0px"></IFRAME>
			<input type="button" class="button" name="FindAffiliate" OnClick="window.open('/bitrix/admin/sale_affiliate_search.php?func_name=SetAffiliateID', '', 'scrollbars=yes,resizable=yes,width=800,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 400)/2-5));" value="...">
			<span id="div_affiliate_name"></span>
			<script type="text/javascript">
			function SetAffiliateID(id)
			{
				document.find_form.filter_affiliate_id.value = id;
			}

			function SetAffiliateName(val)
			{
				if(val != "NA")
					document.getElementById('div_affiliate_name').innerHTML = val;
				else
					document.getElementById('div_affiliate_name').innerHTML = '<?= Loc::getMessage("SO1_NO_AFFILIATE") ?>';
			}

			var affiliateID = '';
			function ChangeAffiliateName()
			{
				if(affiliateID != document.find_form.filter_affiliate_id.value)
				{
					affiliateID = document.find_form.filter_affiliate_id.value;
					if(affiliateID != '' && !isNaN(parseInt(affiliateID, 10)))
					{
						document.getElementById('div_affiliate_name').innerHTML = '<i><?= Loc::getMessage("SO1_WAIT") ?></i>';
						window.frames["hiddenframe_affiliate"].location.replace('/bitrix/admin/sale_affiliate_get.php?ID=' + affiliateID + '&func_name=SetAffiliateName');
					}
					else
						document.getElementById('div_affiliate_name').innerHTML = '';
				}
				timerID = setTimeout('ChangeAffiliateName()',2000);
			}
			ChangeAffiliateName();
			</script>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS") ?>:</td>
		<td><input name="filter_discount_coupon" value="<?= htmlspecialcharsbx($filter_discount_coupon) ?>" size="40" type="text"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("SO_SUM_PAID") ?>:</td>
		<td>
			<select name="filter_sum_paid">
				<option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
				<option value="Y"<?if($filter_sum_paid=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
				<option value="N"<?if($filter_sum_paid=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SO_XML_ID')?>:</td>
		<td>
			<input type="text" name="filter_xml_id" value="<?echo htmlspecialcharsbx($filter_xml_id)?>" size="40">
		</td>
	</tr>

	<?
	foreach (($arOrderProps + $arOrderPropsCode) as $key => $value)
	{
		if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
		{
			?>
			<tr>
				<td valign="top"><?= $value["NAME"] ?>:</td>
				<td valign="top" style="overflow: visible; ">
					<?
					$inputParams  =  $value["SETTINGS"];
					$inputParams["TYPE"] = $value["TYPE"];
					$inputParams["IS_FILTER_FIELD"] = true;

					if($value["TYPE"] == "ENUM")
					{
						$inputParams["OPTIONS"] = array("" => Loc::getMessage("SALE_F_ALL"));
						$inputParams["OPTIONS"] = $inputParams["OPTIONS"] + \Bitrix\Sale\PropertyValue::loadOptions($value["ID"]);
					}

					echo \Bitrix\Sale\Internals\Input\Manager::getFilterEditHtml(
						"filter_prop_".$key,
						$inputParams,
						${"filter_prop_".$key}
					);
					?>
				</td>
			</tr>
			<?
		}
	}

$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<div class="adm-c-bigdatabar" id="bigdatabar">
	<?=$bigdataWidgetHtml?>
</div>
<?
$lAdmin->DisplayList();

echo BeginNote();
?>
<span id="order_sum"><? echo $order_sum;?></span>
<?
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");