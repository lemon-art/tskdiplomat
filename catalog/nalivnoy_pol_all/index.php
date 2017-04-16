<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("description", "Мы предлагаем широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("title", "Все идеи строительства в одном портфеле - ТСК ДИПЛОМАТ");
$APPLICATION->SetTitle("Каталог ТСК Дипломат");
$APPLICATION->SetPageProperty("keywords", "материал, гост, цена, кровля, гидроизоляционный, рулонный, кровельный, строительный, купить");*/
?> 
<h1>Наливные полы</h1>
 <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "page",
		"AREA_FILE_SUFFIX" => "sub",
		"EDIT_TEMPLATE" => ""
	)
);?><?
global $arrFilter;
$arrFilter = Array( 
"SECTION_ID" => Array(539),
);

?> <?
global $arayFilter;
$arayFilter = Array( 
"ID" => Array(2769,2770,2771,318,319,1907,1946,1947,2277,2278,2279,443,445,447,450),
);

?> <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/catalog/static_page2.php",
		"EDIT_TEMPLATE" => ""
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>