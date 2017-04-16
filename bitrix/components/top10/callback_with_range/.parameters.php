<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock")) return;

$rsIBlockType = CIBlockType::GetList(Array("sort"=>"asc"), Array("ACTIVE"=>"Y"));
while($arr=$rsIBlockType->Fetch()) {
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID)) {
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
	}
}

$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch()) {
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

// EVENTS TYPE
$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("ACTIVE" => "Y");
if($site !== false) {
	$arFilter["LID"] = $site;
}

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext()) {
	$arEvent[$arType["EVENT_NAME"]] = $arType["EVENT_TYPE"];
}
// EVENTS TYPE END.

$arComponentParameters = Array(
	"GROUPS" => Array(
		"EMAIL_SEND" => Array(
			"NAME" => GetMessage("TOP10_GROUPS_EMAIL_SEND")
		),
		"PHRASES" => Array(
			"NAME" => GetMessage("TOP10_GROUPS_PHRASES")
		),
		"SCRIPT" => Array(
			"NAME" => GetMessage("TOP10_GROUPS_SCRIPT")
		)
	),
	"PARAMETERS" => Array(
		"AJAX_MODE" => Array(
			"DEFAULT"	=> "Y"
		),

		// OPTOPNS IBLOCK
		"IBLOCK_SAVE" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_IBLOCK_SAVE"),
			"TYPE"		=> "CHECKBOX",
			"DEFAULT"	=> "N",
		),
		"IBLOCK_TYPE" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_IBLOCK_TYPE"),
			"TYPE"		=> "LIST",
			"VALUES"	=> $arIBlockType,
			"REFRESH"	=> "Y",
			"DEFAULT"	=> "",
		),
		"IBLOCK_ID" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_IBLOCK_ID"),
			"TYPE"		=> "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES"	=> $arIBlock,
			"REFRESH"	=> "Y",
			"DEFAULT"	=> "",
		),

		// OPTOPNS EMAIL
		"EMAIL_SEND" => Array(
			"PARENT"	=> "EMAIL_SEND",
			"NAME"		=> GetMessage("F_EMAIL_SEND"),
			"TYPE"		=> "CHECKBOX",
			"DEFAULT"	=> "N",
		),
		"EMAIL_TO" => Array(
			"PARENT"	=> "EMAIL_SEND",
			"NAME"		=> GetMessage("F_EMAIL_TO"),
			"DEFAULT"	=> htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
		),
		"EMAIL_SUBJECT" => Array(
			"PARENT"	=> "EMAIL_SEND",
			"NAME"		=> GetMessage("F_EMAIL_SUBJECT"),
			"DEFAULT"	=> GetMessage("F_EMAIL_SUBJECT_DEFAULT"),
		),
		"EMAIL_EVENT_TYPE" => Array(
			"PARENT"	=> "EMAIL_SEND",
			"NAME"		=> GetMessage("TOP10_EMAIL_EVENT_TYPE"),
			"TYPE"		=> "LIST",
			"DEFAULT"	=> "",
			"VALUES"	=> $arEvent,
			"MULTIPLE"	=> "Y",
		),

		// OPTOPNS TIME
		"TIME_MIN" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_TIME_MIN"),
			"DEFAULT"	=> GetMessage("F_TIME_MIN_DEFAULT"),
		),
		"TIME_MAX" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_TIME_MAX"),
			"VALUES"	=> $arIBlock,
			"DEFAULT"	=> GetMessage("F_TIME_MAX_DEFAULT"),
		),
		"TIME_START" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_TIME_START"),
			"DEFAULT"	=> GetMessage("F_TIME_START_DEFAULT"),
		),
		"TIME_FINISH" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_TIME_FINISH"),
			"DEFAULT"	=> GetMessage("F_TIME_FINISH_DEFAULT"),
		),
		"TIME_STEP" => Array(
			"PARENT"	=> "BASE",
			"NAME"		=> GetMessage("F_TIME_STEP"),
			"DEFAULT"	=> GetMessage("F_TIME_STEP_DEFAULT"),
		),

		// OPTOPNS OF INCLUDE JS
		"SCRIPT_BODY" => Array(
			"PARENT"	=> "SCRIPT",
			"NAME"		=> GetMessage("F_SCRIPT_BODY"),
			"TYPE"		=> "CHECKBOX",
			"DEFAULT"	=> "N",
		),
		"SCRIPT_JQ" => Array(
			"PARENT"	=> "SCRIPT",
			"NAME"		=> GetMessage("F_SCRIPT_JQ"),
			"TYPE"		=> "CHECKBOX",
			"DEFAULT"	=> "Y",
		),
		"SCRIPT_JQUI" => Array(
			"PARENT"	=> "SCRIPT",
			"NAME"		=> GetMessage("F_SCRIPT_JQUI"),
			"TYPE"		=> "CHECKBOX",
			"DEFAULT"	=> "Y",
		),

		// COMPONENT PHRASES
		"BTN_CALLBACK" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_BTN_CALLBACK"),
			"DEFAULT"	=> GetMessage("F_BTN_CALLBACK_DEFAULT"),
		),
		"TOP10_CALLBACK_FORM" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_CALLBACK_FORM"),
			"DEFAULT"	=> GetMessage("TOP10_CALLBACK_FORM_DEFAULT"),
		),
		"FOR_CALL" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_FOR_CALL"),
			"DEFAULT"	=> GetMessage("F_FOR_CALL_DEFAULT"),
		),
		"KEEP_PHONE" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_KEEP_PHONE"),
			"DEFAULT"	=> GetMessage("F_KEEP_PHONE_DEFAULT"),
		),
		"PHONE" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_PHONE"),
			"DEFAULT"	=> GetMessage("F_PHONE_DEFAULT"),
		),
		"PHONE_FIELD" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_PHONE_FIELD"),
			"DEFAULT"	=> GetMessage("F_PHONE_FIELD_DEFAULT"),
		),
		"PHONE_CHECK" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_PHONE_CHECK"),
			"DEFAULT"	=> "[\d\s\+]+.*",
		),
		"TOP10_INPUT_PHONE" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_INPUT_PHONE"),
			"DEFAULT"	=> GetMessage("TOP10_INPUT_PHONE_DEFAULT"),
		),
		"TOP10_INPUT_NAME" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_INPUT_NAME"),
			"DEFAULT"	=> GetMessage("TOP10_INPUT_NAME_DEFAULT"),
		),
		"TOP10_NAME_FIELD" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_NAME_FIELD"),
			"DEFAULT"	=> GetMessage("TOP10_NAME_FIELD_DEFAULT"),
		),
		"TOP10_NAME_CHECK" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_NAME_CHECK"),
			"DEFAULT"	=> "[À-ÿA-z]{2,}.*",
		),
		"TOP10_CALLBACK_WHEN" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_CALLBACK_WHEN"),
			"DEFAULT"	=> GetMessage("TOP10_CALLBACK_WHEN_DEFAULT"),
		),
		"TOP10_CALLBACK_FREE" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_CALLBACK_FREE"),
			"DEFAULT"	=> GetMessage("TOP10_CALLBACK_FREE_DEFAULT"),
		),
		"TOP10_SUBMIT_VALUE" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("TOP10_SUBMIT_VALUE"),
			"DEFAULT"	=> GetMessage("TOP10_SUBMIT_VALUE_DEFAULT"),
		),
		"SUCCESS_MSG" => Array(
			"PARENT"	=> "PHRASES",
			"NAME"		=> GetMessage("F_SUCCESS_MSG"),
			"DEFAULT"	=> GetMessage("F_SUCCESS_MSG_DEFAULT"),
		),
	),
);