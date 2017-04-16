<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(!intval($arParams["IBLOCK_ID"]))
        return;

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	global ${$arParams["FILTER_NAME"]};
	$arrFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arrFilter))
		$arrFilter = array();
}


if($this->StartResultCache(false,array($arrFilter,($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()))))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"NAME",
                'PROPERTY_CALC_THINK',
                'PROPERTY_CALC_MIN_THINK',
                'PROPERTY_CALC_MAX_THINK',
                'PROPERTY_CALC_CONSUM',
                'PROPERTY_CALC_WEIGHT',
                'PROPERTY_CALC_FORMULA',
                'PROPERTY_CALC_UNWEIGHT',
	);
	//WHERE
	//$arrFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
	$arrFilter["ACTIVE"] = "Y";
	$arrFilter["ACTIVE_DATE"] = "Y";
	$arrFilter["CHECK_PERMISSIONS"] = "Y";
	//ORDER BY
	$arOrder = array('NAME'=>'ASC');
        
	$rsItem = CIBlockElement::GetList($arOrder, $arrFilter, false, array("nTopCount"=>100), $arSelect);
	while($arItem = $rsItem->GetNext())
		{
            if($arItem['PROPERTY_CALC_FORMULA_ENUM_ID'] > 0){
                $arItem['PROPERTY_CALC_FORMULA_ENUM_VALUE'] = CIBlockPropertyEnum::GetByID($arItem['PROPERTY_CALC_FORMULA_ENUM_ID']);
            }
        $arResult["ITEMS"][] = $arItem;
		
        	}

	$this->SetResultCacheKeys(array(
	));
	$this->IncludeComponentTemplate();
}
?>
