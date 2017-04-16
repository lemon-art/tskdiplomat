<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	if (!CModule::IncludeModule("sale"))
		{
			ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
			return;
		}

if($_REQUEST["action"] == "deleteajax")
{
	$delete = CSaleBasket::Delete($_REQUEST["id"]);
	if($delete){
	$APPLICATION->IncludeComponent(
			"bitrix:sale.basket.basket.line", ".default", array(
		"COMPONENT_TEMPLATE" => ".default",
		"PATH_TO_BASKET" => SITE_DIR . "personal/cart/",
		"SHOW_NUM_PRODUCTS" => "Y",
		"SHOW_TOTAL_PRICE" => "Y",
		"SHOW_EMPTY_VALUES" => "Y",
		"SHOW_PERSONAL_LINK" => "N",
		"PATH_TO_PERSONAL" => SITE_DIR . "personal/",
		"SHOW_AUTHOR" => "N",
		"PATH_TO_REGISTER" => SITE_DIR . "login/",
		"PATH_TO_PROFILE" => SITE_DIR . "personal/",
		"SHOW_PRODUCTS" => "Y",
		"SHOW_DELAY" => "N",
		"SHOW_NOTAVAIL" => "N",
		"SHOW_SUBSCRIBE" => "N",
		"SHOW_IMAGE" => "Y",
		"SHOW_PRICE" => "Y",
		"SHOW_SUMMARY" => "Y",
		"PATH_TO_ORDER" => SITE_DIR . "personal/order/make/",
		"POSITION_FIXED" => "N",
			), false
	);
	
	
	}
}

?>