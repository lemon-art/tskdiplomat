<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arResult["MENU_ITEMS"] = array();
$arResult["IBLOCK"] = "";

if(!CModule::IncludeModule('iblock'))
{
	$this->AbortResultCache();
	return array();
}

if(isset($arParams["IBLOCK_TYPE_ID"]))
{
	$arFilter = array(
		"TYPE"=>$arParams["IBLOCK_TYPE_ID"],
		"SITE_ID"=>SITE_ID,
	);
}
else
{
	if(!CModule::IncludeModule('catalog'))
	{
		$this->AbortResultCache();
		return array();
	}

	$dbRes = CCatalog::GetList(
		array(),
		array('LID' => SITE_ID)
	);

	$arFilter = array(
		"ID"=>array(),
		"SITE_ID"=>SITE_ID,
	);

	while($arRes = $dbRes->Fetch())
		$arFilter["ID"][] = $arRes["IBLOCK_ID"];
}

$dbIBlock = CIBlock::GetList(array('SORT' => 'ASC', 'ID' => 'ASC'), $arFilter);
$dbIBlock = new CIBlockResult($dbIBlock);

if ($arIBlock = $dbIBlock->GetNext())
{
	if(defined("BX_COMP_MANAGED_CACHE"))
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_id_".$arIBlock["ID"]);

	if($arIBlock["ACTIVE"] == "Y")
		$arResult["IBLOCK"] = $arIBlock;
}

if(defined("BX_COMP_MANAGED_CACHE"))
	$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_id_new");

$this->EndResultCache();


if (is_array($arResult["IBLOCK"]))
{
	$aMenuLinksExt = $APPLICATION->IncludeComponent("bitrix:menu.sections", "", array(
		"IS_SEF" => "Y",
		"SEF_BASE_URL" => "",
		"SECTION_PAGE_URL" => $arResult["IBLOCK"]['SECTION_PAGE_URL'],
		"DETAIL_PAGE_URL" => $arResult["IBLOCK"]['DETAIL_PAGE_URL'],
		"IBLOCK_TYPE" => $arResult["IBLOCK"]['IBLOCK_TYPE_ID'],
		"IBLOCK_ID" => $arResult["IBLOCK"]['ID'],
		"DEPTH_LEVEL" => "3",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	), false, Array('HIDE_ICONS' => 'Y'));

	$arResult["MENU_ITEMS"] = $aMenuLinksExt;
}

return $arResult["MENU_ITEMS"];
?>