<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("description", "Мы предлагаем широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("title", "Все идеи строительства в одном портфеле - ТСК ДИПЛОМАТ");
$APPLICATION->SetTitle("Каталог ТСК Дипломат");
$APPLICATION->SetPageProperty("keywords", "материал, гост, цена, кровля, гидроизоляционный, рулонный, кровельный, строительный, купить");*/
?> 

<h1>Прочие материалы</h1>

<?
global $arrFilter;
$arrFilter = Array( 
"SECTION_ID" => Array(447,111,498,124,105,510),
);

?>

<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/catalog/static_page1.php",
		"EDIT_TEMPLATE" => ""
	)
);?> 






 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>