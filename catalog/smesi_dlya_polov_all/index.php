<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("description", "Мы предлагаем широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("title", "Все идеи строительства в одном портфеле - ТСК ДИПЛОМАТ");
$APPLICATION->SetTitle("Каталог ТСК Дипломат");
$APPLICATION->SetPageProperty("keywords", "материал, гост, цена, кровля, гидроизоляционный, рулонный, кровельный, строительный, купить");*/
?> 
<h1>Смеси для устройства полов</h1>
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
"SECTION_ID" => Array(97,475,113,123,494,500,129,442,542),
);

?><?
global $arayFilter;
$arayFilter = Array( 
"ID" => Array(311,315,317,2317,2276,502,503,505,507,509,510,511,441,444,449,2407,2408,2433,2434,603,604,2042,2043,2044,2045,2763),
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