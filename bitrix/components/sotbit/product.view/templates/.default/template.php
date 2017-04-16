<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $arViewFilter;
$arViewFilter = $arResult;
if(!empty($arResult))
{
        if($arParams["TYPE"] == 'PERELINKOVKA'){
            $theme = 'slider';
            echo "<h3>C этим товаром часто смотрят</h3>";
        }
        elseif($arParams["TYPE"] == 'SOPUT'){
            $theme = 'slider2';
            echo "<h3>Сопутсвующие товары</h3>";
        }
        else $theme = '';
	$APPLICATION->IncludeComponent("bitrix:catalog.top", $theme, array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ELEMENT_SORT_FIELD" => $arParams["SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["SORT_ORDER"],
		"ELEMENT_COUNT" => $arParams["ELEMENT_COUNT"],
		"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
		"DETAIL_URL" => $arParams["DETAIL_URL"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_COMPARE" => "N",
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"FILTER_NAME" => "arViewFilter",
		'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
		'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"]
		),
		$component
	);

}
