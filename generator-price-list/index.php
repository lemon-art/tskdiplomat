<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Генератор прайс листа");
?><?
global $USER;
if ($USER->IsAdmin()):;
?> <?$APPLICATION->IncludeComponent(
	"slobel:price.generation",
	"",
	Array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"FIELD_CODE" => array("ID","NAME"),
		"PROPERTY_CODE" => array("ARTNUMBER","MANUFACTURER","MAN_LINK","MATERIAL","COLOR"),
		"CHECK_DATES" => "Y",
		"CHECK_STOCK" => "N",
		"PRICE_CODE" => "1",
		"CURRENCY" => "iblock",
		"FORMATED_FILE" => "xlsx",
		"FORMATED_FILE_NAME" => "price",
		"FORMATED_FILE_DIR" => "/upload/",
		"CHECK_SECTION" => "Y",
		"HEADER" => "Y",
		"NAME_COLS" => "Y",
		"NULL" => "-",
		"MULTI_SEPARATOR" => ",",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "0",
		"CACHE_NOTES" => "",
		"CACHE_GROUPS" => "N",
		"ELEMENT_SORT_BY" => "0",
		"ELEMENT_SORT" => "ASC",
		"COLOR" => "#C0C0C0",
		"FONT" => "Arial",
		"FONT_SIZE" => "10",
		"FONT_COLOR" => "#000000",
		"COLS_SECTION" => "N",
		"CHECK_PARENT" => "Y",
		"SECTION_SORT_BY" => "NAME",
		"SECTION_SORT" => "ASC"
	)
);?> <? endif; ?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>