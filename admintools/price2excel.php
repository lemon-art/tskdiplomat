<?
//$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../..');
//require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

require($_SERVER["DOCUMENT_ROOT"]."/local/cron/price2excel.php");

?>
<?/*
$APPLICATION->IncludeComponent(
	"fcm:price2excel", 
	".default", 
	array(
                "CITY_ID" => USER_CITY_ID,
            
		"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "2",
		"SECTION_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"SECTION_SORT_FIELD" => "left_margin",
		"SECTION_SORT_ORDER" => "asc",
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_SORT_FIELD2" => "id",
		"ELEMENT_SORT_ORDER2" => "desc",
		"FILTER_NAME" => "arrFilter",
		"HIDE_NOT_AVAILABLE" => "N",
		"SECTION_COUNT" => "100",
		"ELEMENT_COUNT" => "999",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"DISPLAY_COMPARE" => "N",
		"USE_MAIN_ELEMENT_SECTION" => "N",
		"PRICE_CODE" => array(
			0 => "BASE_PRICE",
			1 => "PRICE_DELIVERED",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_PROPERTIES" => array(
		),
		"USE_PRODUCT_QUANTITY" => "N",
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB"
	),
	false
);
 * 
 */
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>