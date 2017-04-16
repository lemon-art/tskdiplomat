<?
if(isset($arResult['BREADCRUMB_TITLE']) && !empty($arResult['BREADCRUMB_TITLE']))
{
	$APPLICATION->AddChainItem($arResult['BREADCRUMB_TITLE'], $arResult['BREADCRUMB_LINK']);
}
?>