<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("description", "Мы предлагаем широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("title", "Все идеи строительства в одном портфеле - ТСК ДИПЛОМАТ");
$APPLICATION->SetTitle("Каталог ТСК Дипломат");
$APPLICATION->SetPageProperty("keywords", "материал, гост, цена, кровля, гидроизоляционный, рулонный, кровельный, строительный, купить");*/
?> 
<h1>Шпатлевки</h1>
 <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "page",
		"AREA_FILE_SUFFIX" => "sub",
		"EDIT_TEMPLATE" => ""
	),
false
);?><?
global $arrFilter;
$arrFilter = Array( 
"SECTION_ID" => Array(99,474,122,499,496,128,538,114,444),
);

?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/catalog/static_page1.php",
		"EDIT_TEMPLATE" => ""
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>