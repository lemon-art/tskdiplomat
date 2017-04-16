<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("sale") || !CModule::IncludeModule("iblock"))
	return;

$arPrice = array();
if(CModule::IncludeModule("catalog"))
{
	$rsPrice=CCatalogGroup::GetList($v1="sort", $v2="asc");
	while($arr=$rsPrice->Fetch()) $arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
}
else
{
	$arPrice = $arProperty_N;
}
$arTypes = CIBlockParameters::GetIBlockTypes();
$arIBlocks=Array(); 

$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
    $arIBlocks[$arRes["ID"]] = $arRes["NAME"];

if($arCurrentValues["IBLOCK_ID"])
{
    $rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]));
    while ($arr=$rsProp->Fetch())
    {
        $arProperty["SECTION_ID"] = GetMessage("SBT_SECTION_ID");
        if($arr["PROPERTY_TYPE"] != "F")
            $arProperty["PROPERTY_".$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
 
        /*if($arr["PROPERTY_TYPE"]=="N")
            $arProperty_N[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"]; 
 
        if($arr["PROPERTY_TYPE"]!="F")
        {
            if($arr["MULTIPLE"] == "Y")
                $arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"]; 
            elseif($arr["PROPERTY_TYPE"] == "L")
                $arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"]; 
            elseif($arr["PROPERTY_TYPE"] == "E" && $arr["LINK_IBLOCK_ID"] > 0)
                $arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"]; 
        }*/
        
        
    }    
}

$arSort = CIBlockParameters::GetElementSortFields(
    array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
    array('KEY_LOWERCASE' => 'Y')
);

$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
$rsPrice = CCatalogGroup::GetList($v1="sort", $v2="asc");
while($arr=$rsPrice->Fetch()) $arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];

$arAscDesc = array(
    "asc" => GetMessage("IBLOCK_SORT_ASC"),
    "desc" => GetMessage("IBLOCK_SORT_DESC"),
);

$arDate = array("1"=>GetMessage("SBT_PRODUCTVIEW_1"), "7"=>GetMessage("SBT_PRODUCTVIEW_7"), 30=>GetMessage("SBT_PRODUCTVIEW_30"), 180=>GetMessage("SBT_PRODUCTVIEW_180"));

$arComponentParameters = array(
	"GROUPS" => array(
		"PRICES" => array(
			"NAME" => GetMessage("IBLOCK_PRICES"),
		),
	),
	"PARAMETERS" => array(
        "IBLOCK_TYPE" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arTypes,
            "DEFAULT" => "catalog",
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ),
		"ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBT_PRODUCTVIEW_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
        "CODE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_CODE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "DATE_FROM" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_DATE_FROM"),
            "TYPE" => "STRING",
            "DEFAULT" => 30,
        ),
        "LIMIT" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_LIMIT"),
            "TYPE" => "STRING",
            "DEFAULT" => 10000,
        ),
        "SORT_FIELD" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_SORT_FIELD"),
            "TYPE" => "LIST",
            "VALUES" => $arSort,
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "rand",
        ),
        "SORT_ORDER" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_SORT_ORDER"),
            "TYPE" => "LIST",
            "VALUES" => $arAscDesc,
            "DEFAULT" => "rand",
            "ADDITIONAL_VALUES" => "Y",
        ),
        "DETAIL_PROPS_ANALOG" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_PROPS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "DEFAULT" => "SECTION_ID", 
            "VALUES" => $arProperty,
            "ADDITIONAL_VALUES" => "Y",
        ),
        /*"PRICE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_ANALOG_PRICE"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "DEFAULT" => "", 
            "VALUES" => $arPriceFilter,
            "ADDITIONAL_VALUES" => "Y",
        ),*/
        "HIDE_NOT_AVAILABLE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SBT_PRODUCTVIEW_HIDE_NOT_AVAILABLE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
		/*"MIN_BUYES" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_MIN_BUYES"),
			"TYPE" => "STRING",
			"DEFAULT" => "2",
		),*/

		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("IBLOCK_DETAIL_URL"),
			"",
			"URL_TEMPLATES"
		),
		"BASKET_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/basket.php",
		),
		"ACTION_VARIABLE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME"		=> GetMessage("IBLOCK_ACTION_VARIABLE"),
			"TYPE"		=> "STRING",
			"DEFAULT"	=> "action"
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME"		=> GetMessage("IBLOCK_PRODUCT_ID_VARIABLE"),
			"TYPE"		=> "STRING",
			"DEFAULT"	=> "id"
		),
		"ELEMENT_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),
		"LINE_ELEMENT_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_LINE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPrice,
		),
		"USE_PRICE_COUNT" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_USE_PRICE_COUNT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SHOW_PRICE_COUNT" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_SHOW_PRICE_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "1"
		),
		"PRICE_VAT_INCLUDE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_VAT_INCLUDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);

if (CModule::IncludeModule('catalog') && CModule::IncludeModule('currency'))
{
	$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_SRP_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
	{
		$arCurrencyList = array();
		$rsCurrencies = CCurrency::GetList(($by = 'SORT'), ($order = 'ASC'));
		while ($arCurrency = $rsCurrencies->Fetch())
		{
			$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
		}
		$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_SRP_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arCurrencyList,
			'DEFAULT' => CCurrency::GetBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}

}
?>