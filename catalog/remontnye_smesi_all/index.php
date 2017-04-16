<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("description", "Мы предлагаем широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("title", "Ремонтные смеси для бетона в Москве, купить сухую ремонтную смесь по низкой цене");
$APPLICATION->SetTitle("Каталог ТСК Дипломат");
$APPLICATION->SetPageProperty("keywords", "материал, гост, цена, кровля, гидроизоляционный, рулонный, кровельный, строительный, купить");*/
?>
<h1>Сухие ремонтные смеси</h1>
 <?
global $arrFilter;
$arrFilter = Array( 
"SECTION_ID" => Array(93),
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