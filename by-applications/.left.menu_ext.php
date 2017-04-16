<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksNew=$APPLICATION->IncludeComponent("bitrix:menu.sections", "", array(
	"IS_SEF" => "Y",
	"SEF_BASE_URL" => "/by-applications/",
	"SECTION_PAGE_URL" => "#SECTION_CODE#/",
	"DETAIL_PAGE_URL" => "#SECTION_CODE#/#ELEMENT_CODE#.html",
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "35",
	"DEPTH_LEVEL" => "3",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600000"
	),
	false
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksNew);


?>

