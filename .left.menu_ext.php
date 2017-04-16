<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;
/*
if (!function_exists("GetTreeRecursive")) //Include from main.map component
{
$aMenuLinksExt=$APPLICATION->IncludeComponent("bitrix:eshop.menu.sections", "", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000"
	),
	false,
	Array('HIDE_ICONS' => 'Y')
);
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
}
*/
$aMenuLinksExt=$APPLICATION->IncludeComponent("pseo:menu.sections", "", array(
	"IS_SEF" => "Y",
	"SEF_BASE_URL" => "/catalog/",
	"SECTION_PAGE_URL" => "#SECTION_CODE#/",
	"DETAIL_PAGE_URL" => "#SECTION_CODE#/#ELEMENT_CODE#",
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "3",
	"DEPTH_LEVEL" => "5",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000"
	),
	false
);
$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);

?>