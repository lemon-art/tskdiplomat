<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$bNewVers = ((defined("SM_VERSION") && version_compare(SM_VERSION, "15.0.7") >= 0) ? true : false);
if($_REQUEST['bxsender'] != 'fileman_html_editor' && (!$bNewVers || $_REQUEST["edit_file"] == "template")):?>
<div style="background-color: #fff; padding: 0; border-top: 1px solid #8E8E8E; border-bottom: 1px solid #8E8E8E; margin-bottom: 15px;"><div style="background-color: #8E8E8E; height: 30px; padding: 7px; border: 1px solid #fff">
	<a href="http://www.is-market.ru?param=cl" target="_blank"><img src="/bitrix/components/altasib/feedback.form/images/is-market.gif" style="float: left; margin-right: 15px;" border="0" /></a>
	<div style="margin: 13px 0px 0px 0px">
		<a href="http://www.is-market.ru?param=cl" target="_blank" style="color: #fff; font-size: 10px; text-decoration: none"><?=GetMessage("ALTASIB_IS")?></a>
	</div>
</div></div>
<?endif;

$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
}

$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$rsIBlock = CIBlock::GetList(Array(), Array("CODE" => "altasib_feedback"));
if($arr=$rsIBlock->Fetch())
	$defaultIBid = $arr["ID"];

if(empty($arCurrentValues["IBLOCK_ID"]) && !empty($defaultIBid))
	$arCurrentValues["IBLOCK_ID"] = $defaultIBid;

$arProperty_LNS = array();
$arPropAuto = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"]) ? $arCurrentValues["IBLOCK_ID"] : $arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

	if($arr["CODE"] != "USERIP")
	{
		if(!in_array($arr["PROPERTY_TYPE"], array("F", "E", "L"))
			&& $arr["USER_TYPE"] != "DateTime"
		)
			$arPropAuto[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

		if($arr["PROPERTY_TYPE"] != "F" && $arr["PROPERTY_TYPE"] != "L")
		{
			$arPropForNameEl[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		}
	}
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}
$arProperty["FEEDBACK_TEXT"] = GetMessage("PROP_FEEDBACK_TEXT");

$dEmailTo = COption::GetOptionString("main", "email_from");
$arSectionIB["SECTION_MAIL_ALL"] = Array(
	"PARENT" => "SECTION_MAIL",
	"NAME" => GetMessage("SECTION_MAIL_ALL"),
	"TYPE" => "STRING",
	"DEFAULT" => $dEmailTo
);

$rsIBlock = CIBlockSection::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"]) ? $arCurrentValues["IBLOCK_ID"] : $arCurrentValues["ID"])));
while($arr=$rsIBlock->Fetch())
	$arSectionIB["SECTION_MAIL".$arr["ID"]] = Array(
		"PARENT" => "SECTION_MAIL",
		"NAME" => $arr["NAME"],
		"TYPE" => "STRING",
		"DEFAULT" => ""
	);

if(is_array($arPropForNameEl))
	$arProperty_nameEl = array_merge(Array("ALX_DATE" => GetMessage("CURRENT_DATE"), "ALX_TEXT" => GetMessage("TEXT_MESS")),$arPropForNameEl);
else
	$arProperty_nameEl =Array("ALX_DATE" => GetMessage("CURRENT_DATE"), "ALX_TEXT" => GetMessage("TEXT_MESS"));


$arComponentParameters = array(
	"GROUPS" => array(
		"POPUP" => array(
			"NAME" => GetMessage("SECTION_POPUP"),
			"SORT" => "260",
		),
		"SECTION_MAIL" => array(
			"NAME" => GetMessage("SECTION_MAIL"),
			"SORT" => "280",
		),
		"LEAD" => array(
			"NAME" => GetMessage("SECTION_LEAD"),
			"SORT" => "800",
		),
		"SECTION_AUTOCOMPLETE" => array(
			"NAME" => GetMessage("SECTION_AUTOCOMPLETE"),
			"SORT" => "270",
		),
	),
	"PARAMETERS" => array(
		"AJAX_MODE"	=> Array(),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
			"DEFAULT" => "altasib_feedback",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
			"DEFAULT" => $defaultIBid,
		),
		"FORM_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_ID_FORM"),
			"TYPE" => "STRING",
			"DEFAULT" => 1
		),
		"EVENT_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_EVENT_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => "ALX_FEEDBACK_FORM"
		),
		"PROPERTY_FIELDS" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("F_PROPERTY_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty,
			"DEFAULT" => array("FIO","EMAIL","FEEDBACK_TEXT"),
		),
		"PROPERTY_FIELDS_REQUIRED" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("F_PROPERTY_FIELDS_REQ"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty,
			"DEFAULT" => array("FEEDBACK_TEXT"),
		),
		"NAME_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_NAME_ELEMENT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $arProperty_nameEl,
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "ALX_DATE",
		),
		"BBC_MAIL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_BBC_MAIL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),

		"MESSAGE_OK" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("MESS_OK"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MESSAGE_OK"),
			"COLS" => 50,
		),

		"CHECK_ERROR" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CHECK_ERROR"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"ACTIVE_ELEMENT" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_ACTIVE_ELEMENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SEND_MAIL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SEND_MAIL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"HIDE_FORM" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("HIDE_FORM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"USERMAIL_FROM" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USERMAIL_FROM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		"SHOW_MESSAGE_LINK" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SHOW_MESSAGE_LINK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"REWIND_FORM" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("REWIND_FORM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),

		"ADD_LEAD" => Array(
			"PARENT" => "LEAD",
			"NAME" => GetMessage("ADD_LEAD"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),

		"ALX_CHECK_NAME_LINK" => Array(
			"PARENT" => "POPUP",
			"NAME" => GetMessage("ALX_CHECKBOX_NAME_LINK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),

		"WIDTH_FORM" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_WIDTH_FORM"),
			"TYPE" => "STRING",
			"DEFAULT" => "50%"
		),
		"SIZE_NAME" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_SIZE_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "12px"
		),
		"COLOR_NAME" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_NAME"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#000000"
		),
		"SIZE_HINT" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_SIZE_HINT"),
			"TYPE" => "STRING",
			"DEFAULT" => "10px"
		),
		"COLOR_HINT" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_HINT"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#000000"
		),
		"SIZE_INPUT" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_SIZE_INPUT"),
			"TYPE" => "STRING",
			"DEFAULT" => "12px"
		),
		"COLOR_INPUT" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_INPUT"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#727272"
		),
		"BACKCOLOR_ERROR" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_BACKCOLOR_ERROR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#ffffff"
		),
		"COLOR_ERROR_TITLE" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_ERROR_TITLE"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#A90000"
		),
		"COLOR_ERROR" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_ERROR"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#8E8E8E"
		),
		"IMG_ERROR" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_IMG_ERROR"),
			"TYPE" => "STRING",
			"DEFAULT" => "/upload/altasib.feedback.gif"
		),
		"BORDER_RADIUS" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_BORDER_RADIUS"),
			"TYPE" => "STRING",
			"DEFAULT" => "3px"
		),
		"COLOR_MESS_OK" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_COLOR_MESS_OK"),
			"TYPE" => "COLORPICKER",
			"DEFAULT" => "#963258"
		),
		"IMG_OK" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_IMG_OK"),
			"TYPE" => "STRING",
			"DEFAULT" => "/upload/altasib.feedback.ok.gif"
		),
		"CATEGORY_SELECT_NAME" => Array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("APPEARANCE_CATEGORY_SELECT_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("APPEARANCE_CATEGORY_SELECT_NAME_DEF")
		),
		"PROPS_AUTOCOMPLETE_NAME" => array(
			"PARENT" => "SECTION_AUTOCOMPLETE",
			"NAME" => GetMessage("AUTOCOMPLETE_AUTHOR_NAME"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPropAuto,
			"DEFAULT" => "FIO",
		),
		"PROPS_AUTOCOMPLETE_EMAIL" => array(
			"PARENT" => "SECTION_AUTOCOMPLETE",
			"NAME" => GetMessage("AUTOCOMPLETE_AUTHOR_EMAIL"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPropAuto,
			"DEFAULT" => "EMAIL",
		),
		"PROPS_AUTOCOMPLETE_PERSONAL_PHONE" => array(
			"PARENT" => "SECTION_AUTOCOMPLETE",
			"NAME" => GetMessage("AUTOCOMPLETE_AUTHOR_PHONE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPropAuto,
			"DEFAULT" => "PHONE",
		),
	),
);

if($arCurrentValues["ADD_LEAD"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["LEAD_TITLE"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_TITLE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_NAME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_NAME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_SECOND_NAME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_SECOND_NAME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_LAST_NAME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_LAST_NAME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_COMPANY_TITLE"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_COMPANY_TITLE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_POST"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_POST"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_ADDRESS"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_ADDRESS"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_WORK"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_WORK"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_MOBILE"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_MOBILE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_FAX"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_FAX"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_HOME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_HOME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_PAGER"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_PAGER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_PHONE_OTHER"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_PHONE_OTHER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_EMAIL_WORK"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_EMAIL_WORK"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_EMAIL_HOME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_EMAIL_HOME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_EMAIL_OTHER"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_EMAIL_OTHER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_WORK"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_WORK"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_HOME"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_HOME"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_FACEBOOK"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_FACEBOOK"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_LIVEJOURNAL"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_LIVEJOURNAL"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_TWITTER"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_TWITTER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_WEB_OTHER"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_WEB_OTHER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_OPPORTUNITY"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_OPPORTUNITY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["LEAD_DATE_CLOSED"] = array(
		"PARENT" => "LEAD",
		"NAME" => GetMessage("LEAD_DATE_CLOSED"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
}

if($arCurrentValues["ALX_CHECK_NAME_LINK"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["ALX_LOAD_PAGE"] = array(
		"PARENT" => "POPUP",
		"NAME" => GetMessage("ALX_LOAD_PAGE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	);
	$arComponentParameters["PARAMETERS"]["ALX_NAME_LINK"] = array(
		"PARENT" => "POPUP",
		"NAME" => GetMessage("ALX_NAME_LINK"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("ALX_NAME_LINK_DEFAULT"),
		"COLS" => 50,
	);
	$arComponentParameters["PARAMETERS"]["FANCYBOX_EN"] = array(
		"PARENT" => "POPUP",
		"NAME" => GetMessage("FB_ADD_FANCY"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "N",
		"MULTIPLE" => "N",
		"REFRESH" => "N",
		"VALUES" => array("Y" => GetMessage("FB_ADD_JQUERY_YES"), "N" => GetMessage("FB_ADD_FANCY_NO")),
		"DEFAULT" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["WIDTH_FORM"]["DEFAULT"] = "100%";
}

$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("F_USE_CAPTCHA"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => "Y",
	"DEFAULT" => "Y",
);

if($arCurrentValues["USE_CAPTCHA"] == "Y")
{
	$arCaptcha = array("default" => GetMessage("F_CAPTCHA_BITRIX"), "recaptcha" => GetMessage("F_CAPTCHA_GOOGLE"));
	$arComponentParameters["PARAMETERS"]["CAPTCHA_TYPE"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("F_CAPTCHA_TYPE"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "N",
		"MULTIPLE" => "N",
		"REFRESH" => "Y",
		"VALUES" => $arCaptcha,
		"DEFAULT" => "default",
	);
	if($arCurrentValues["CAPTCHA_TYPE"]=="recaptcha")
	{
		$arReCThemes = array("dark" => GetMessage("RECAPTCHA_THEME_DARK"), "light" => GetMessage("RECAPTCHA_THEME_LIGHT"));
		$arComponentParameters["PARAMETERS"]["RECAPTCHA_THEME"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_RECAPTCHA_THEME"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"MULTIPLE" => "N",
			"VALUES" => $arReCThemes,
			"DEFAULT" => "light",
		);
		$arReCTypes = array("audio" => GetMessage("RECAPTCHA_TYPE_AUDIO"), "image" => GetMessage("RECAPTCHA_TYPE_IMAGE"));
		$arComponentParameters["PARAMETERS"]["RECAPTCHA_TYPE"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_RECAPTCHA_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"MULTIPLE" => "N",
			"VALUES" => $arReCTypes,
			"DEFAULT" => "image",
		);
	}
}

$arComponentParameters["PARAMETERS"]["JQUERY_EN"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("FB_ADD_JQUERY"),
	"TYPE" => "LIST",
	"ADDITIONAL_VALUES" => "N",
	"MULTIPLE" => "N",
	"REFRESH" => "N",
	"VALUES" => array("Y" => GetMessage("FB_ADD_JQUERY_YES"), "N" => GetMessage("FB_ADD_JQUERY_NO")),
	"DEFAULT" => "Y",
);

$arComponentParameters["PARAMETERS"]["LOCAL_REDIRECT_ENABLE"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("LOCAL_REDIRECT_ENABLE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

if($arCurrentValues["LOCAL_REDIRECT_ENABLE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["LOCAL_REDIRECT_URL"] = array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("LOCAL_REDIRECT_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"COLS" => 50,
	);
	unset(
		$arComponentParameters["PARAMETERS"]["HIDE_FORM"],
		$arComponentParameters["PARAMETERS"]["REWIND_FORM"]
	);
}

foreach($arSectionIB as $k => $v)
{
	$arComponentParameters["PARAMETERS"][$k] = $v;
}
?>