<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
	return;

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

$shopLocalization = $wizard->GetVar("shopLocalization");

$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_sku.xml";
if ($shopLocalization == "ua")
	$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices_sku_ua.xml";
else
	$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices_sku.xml";
$iblockCode = "furniture_offers_".WIZARD_SITE_ID;
$iblockType = "offers";

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false;
if ($arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"];
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CIBlock::Delete($arIBlock["ID"]);
		$iblockID = false;
		COption::SetOptionString("eshop", "demo_deleted", "N", "", WIZARD_SITE_ID);
	}
}

CModule::IncludeModule("catalog");

if($iblockID == false)
{
	$permissions = Array(
			"1" => "X",
			"2" => "R"
		);
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "sale_administrator"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}

	$IBLOCK_OFFERS_ID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"furniture_offers",
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);
	$iblockID1 = WizardServices::ImportIBlockFromXML(
		$iblockXMLFilePrices,
		"furniture_offers",
		$iblockType."_prices",
		WIZARD_SITE_ID,
		$permissions
	);

	if ($IBLOCK_OFFERS_ID < 1)
		return;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array (
			'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ),
			'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ),
			'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ),
			'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ),
			'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'CODE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ),
			'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_NAME' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'SECTION_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ),
			'SECTION_DESCRIPTION_TYPE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => 'text', ),
			'SECTION_DESCRIPTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ),
			'SECTION_XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'SECTION_CODE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), ),
		"CODE" => "furniture_offers",
		"XML_ID" => $iblockCode,
		//"NAME" => "[".WIZARD_SITE_ID."] ".$iblock->GetArrayByID($iblockID, "NAME")
	);

	$iblock->Update($IBLOCK_OFFERS_ID, $arFields);

	$iblockCodeFur = "furniture_".WIZARD_SITE_ID;
	$iblockTypeFur = "catalog";

	$rsIBlockFur = CIBlock::GetList(array(), array("XML_ID" => $iblockCodeFur, "TYPE" => $iblockTypeFur));
	if ($arIBlockFur = $rsIBlockFur->Fetch())
	{
		$ID_SKU = CCatalog::LinkSKUIBlock($arIBlockFur["ID"], $IBLOCK_OFFERS_ID);
	}

	$rsCatalogs = CCatalog::GetList(
		array(),
		array('IBLOCK_ID' => $IBLOCK_OFFERS_ID),
		false,
		false,
		array('IBLOCK_ID')
	);
	if ($arCatalog = $rsCatalogs->Fetch())
	{
		CCatalog::Update($IBLOCK_OFFERS_ID,array('PRODUCT_IBLOCK_ID' => $arIBlockFur["ID"],'SKU_PROPERTY_ID' => $ID_SKU));
	}
	else
	{
		CCatalog::Add(array('IBLOCK_ID' => $IBLOCK_OFFERS_ID, 'PRODUCT_IBLOCK_ID' => $arIBlockFur["ID"], 'SKU_PROPERTY_ID' => $ID_SKU));
	}

// form settings
/*	$f1 = 'edit1--#--'.GetMessage("WZD_OPTION_CATALOG_1").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_CATALOG_2").'--,--NAME--#--'.GetMessage("WZD_OPTION_CATALOG_3").'--,--CODE--#--'.GetMessage("WZD_OPTION_CATALOG_4").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_CATALOG_5").'--,';
	$f1 .= $fProps;
	if(CCatalog::GetByID($arIBlockFur["ID"]) && CCatalog::GetByID($IBLOCK_OFFERS_ID))
	{

		$f1 .= '--CATALOG--#--'.GetMessage("WZD_OPTION_CATALOG_20").'--;--cedit1--#--'.GetMessage("WZD_OPTION_CATALOG_27").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_6").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_7").'--,--cedit1_csection1--#----'.GetMessage("WZD_OPTION_CATALOG_9").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_CATALOG_30").'--;--';
		$f1 .= 'cedit2--#--'.GetMessage("WZD_OPTION_CATALOG_31").'--,--OFFERS--#--'.GetMessage("WZD_OPTION_CATALOG_31").'--;--';
	}
	else
	{
		$f1 .= '--OFFERS--#--'.GetMessage("WZD_OPTION_CATALOG_20").'--;--cedit1--#--'.GetMessage("WZD_OPTION_CATALOG_27").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_6").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_7").'--,--cedit1_csection1--#----'.GetMessage("WZD_OPTION_CATALOG_9").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_CATALOG_30").'--;--';
	}    */
//	CUserOptions::SetOption("form", "form_element_".$arIBlockFur["ID"], array ( 'tabs' => $f1, ));

	// form for sku
	/*$fOfferss = '--PROPERTY_'.$arProps["329"].'--#--'.GetMessage("WZD_OPTION_CATALOG_32").'--,--PROPERTY_'.$arProps["327"].'--#--'.GetMessage("WZD_OPTION_CATALOG_33").'--,';
	$f2 = 'edit1--#--'.GetMessage("WZD_OPTION_CATALOG_1").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_CATALOG_2").'--,--NAME--#--'.GetMessage("WZD_OPTION_CATALOG_3").'--,';
	$f2 .= $fOfferss;
	$f2 .= '--CATALOG--#--'.GetMessage("WZD_OPTION_CATALOG_20").'--;--';
	$f3 = 'sub_edit1--#--'.GetMessage("WZD_OPTION_CATALOG_1").'--,--SUB_ACTIVE--#--'.GetMessage("WZD_OPTION_CATALOG_2").'--,--SUB_NAME--#--'.GetMessage("WZD_OPTION_CATALOG_3").'--,';
	$f3 .= $fOfferss;
	$f3 .= '--CATALOG--#--'.GetMessage("WZD_OPTION_CATALOG_20").'--;--';

	CUserOptions::SetOption("form", "form_element_".$IBLOCK_OFFERS_ID, array ( 'tabs' => $f2, ));
	CUserOptions::SetOption("form", "form_subelement_".$IBLOCK_OFFERS_ID, array ( 'tabs' => $f3, ));  */
}
else
{
	$arSites = array();
	$db_res = CIBlock::GetSite($IBLOCK_OFFERS_ID);
	while ($res = $db_res->Fetch())
		$arSites[] = $res["LID"];
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		$iblock = new CIBlock;
		$iblock->Update($iblockID, array("LID" => $arSites));
	}
}
?>