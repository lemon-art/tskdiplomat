<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Калькулятор");

global $arCalcFilter;

$arCalcFilter = array(
    '!PROPERTY_CALC_CONSUM' => false
);

?>
    
    <?$APPLICATION->IncludeComponent(
	"fcm:calculator", 
	".default", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"FILTER_NAME" => "arCalcFilter",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
	),
	false
);?> 
    
    
    <?$APPLICATION->IncludeComponent(
	"fcm:calculator", 
	".default", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCKS" => array(
			0 => "3",
		),
		"NEWS_COUNT" => "5",
		"IBLOCK_SORT_BY" => "SORT",
		"IBLOCK_SORT_ORDER" => "ASC",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_NAME" => "arCalcFilter",
		"IBLOCK_URL" => "",
		"DETAIL_URL" => "",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"IBLOCK_ID" => "3"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>