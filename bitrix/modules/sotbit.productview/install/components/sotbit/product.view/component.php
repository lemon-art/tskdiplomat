<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Catalog\CatalogViewedProductTable as CatalogViewedProductTable;
/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;
if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("sale") || !CModule::IncludeModule("sotbit.productview") || !CSotbitProductView::getDemo())
{
	//ShowError(GetMessage("SAP_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);
if($arParams["ID"] <= 0 && !$arParams["CODE"])
	return;
$arParams["ELEMENT_COUNT"] = IntVal($arParams["ELEMENT_COUNT"]);
if($arParams["ELEMENT_COUNT"] <= 0)
	$arParams["ELEMENT_COUNT"] = 5;

$arParams['CONVERT_CURRENCY'] = (isset($arParams['CONVERT_CURRENCY']) && 'Y' == $arParams['CONVERT_CURRENCY'] ? 'Y' : 'N');
$arParams['CURRENCY_ID'] = trim(strval($arParams['CURRENCY_ID']));
if ('' == $arParams['CURRENCY_ID'])
{
	$arParams['CONVERT_CURRENCY'] = 'N';
}
elseif ('N' == $arParams['CONVERT_CURRENCY'])
{
	$arParams['CURRENCY_ID'] = '';
}
$arrFilter = array();
$arAnalogFilter = array();
if($arParams["MODE"]=="BASKET")
{
    $arParams["CACHE_TYPE"] = "N";
    $arParams["~CACHE_TYPE"] = "N";
}
if($this->StartResultCache(false, array($arrFilter, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()))))
{
    if($arParams["CODE"])
    {
            $arParams["ID"] = CIBlockFindTools::GetElementID(
            $arParams["ID"],
            $arParams["CODE"],
            false,
            false,
            array(
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "IBLOCK_LID" => SITE_ID,
                "IBLOCK_ACTIVE" => "Y",
                "ACTIVE_DATE" => "Y",
                "ACTIVE" => "Y",
                "CHECK_PERMISSIONS" => "Y",
            )
            );
    }
    $now = time()+CTimeZone::GetOffset();
    $timeEnd = $now-intval($arParams["DATE_FROM"])*60*60*24;;  
    $date = date($DB->DateFormatToPHP(FORMAT_DATETIME), $timeEnd);
    if(!$arParams["LIMIT"])
        $arParams["LIMIT"] = 10000;
        
    $viewedIterator = CatalogViewedProductTable::GetList(array(
            "filter" => array(
                    'SITE_ID' => SITE_ID,
                    '>=DATE_VISIT' => $date,
                    "!FUSER_ID" => CSaleBasket::GetBasketUserID(),
            ),
            'order' => array(
                    'DATE_VISIT' => "DESC" 
            ),
            'limit' => $arParams["LIMIT"]
    ));
    while ($viewedProduct = $viewedIterator->fetch())
    {
        $ids[] = $viewedProduct['PRODUCT_ID'];
        $arView[] = $viewedProduct;
    }
    $arResult = CSotbitProductView::GetProductElements($arView, $arParams["ID"]);

    $this->EndResultCache();
} 
$this->IncludeComponentTemplate();
?>