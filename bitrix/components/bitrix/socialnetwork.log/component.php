<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.log/include.php");

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!array_key_exists("SUBSCRIBE_ONLY", $arParams) || strLen($arParams["SUBSCRIBE_ONLY"]) <= 0)
	$arParams["SUBSCRIBE_ONLY"] = "Y";
	
if (!array_key_exists("CHECK_PERMISSIONS_DEST", $arParams) || strLen($arParams["CHECK_PERMISSIONS_DEST"]) <= 0)
	$arParams["CHECK_PERMISSIONS_DEST"] = "N";

if (!array_key_exists("USE_FOLLOW", $arParams) || strLen($arParams["USE_FOLLOW"]) <= 0)
	$arParams["USE_FOLLOW"] = "Y";
	
if(defined("DisableSonetLogVisibleSubscr") && DisableSonetLogVisibleSubscr === true)
	$arParams["SUBSCRIBE_ONLY"] = "N";

if(defined("DisableSonetLogFollow") && DisableSonetLogFollow === true)
	$arParams["USE_FOLLOW"] = "N";

if(!$GLOBALS["USER"]->IsAuthorized())
	$arParams["USE_FOLLOW"] = "N";

if(isset($arParams["DISPLAY"]))
{
	$arParams["SUBSCRIBE_ONLY"] = "N";
	$arParams["USE_FOLLOW"] = "N";	
}

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);
if (strlen($arParams["RATING_TYPE"]) <= 0)
{
	$arParams["RATING_TYPE"] = COption::GetOptionString("main", "rating_vote_template", COption::GetOptionString("main", "rating_vote_type", "standart") == "like"? "like": "standart");
	if ($arParams["RATING_TYPE"] == "like_graphic")
		$arParams["RATING_TYPE"] = "like";
	else if ($arParams["RATING_TYPE"] == "standart")
		$arParams["RATING_TYPE"] = "standart_text";
}
else
{
	if ($arParams["RATING_TYPE"] == "like_graphic")
		$arParams["RATING_TYPE"] = "like";
	else if ($arParams["RATING_TYPE"] == "standart")
		$arParams["RATING_TYPE"] = "standart_text";
}

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
{
	$arParams["~PATH_TO_GROUP"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#";
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($arParams["~PATH_TO_GROUP"]);
}

if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
{
	$arParams["PATH_TO_LOG_RSS"] = trim($arParams["PATH_TO_LOG_RSS"]);
	if (strlen($arParams["PATH_TO_LOG_RSS"]) <= 0)
	{
		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			$arParams["~PATH_TO_LOG_RSS"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_log_rss&entity_id=#group_id#&bx_hit_hash=#sign#&events=#events#";
		else
			$arParams["~PATH_TO_LOG_RSS"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_log_rss&entity_id=#user_id#&bx_hit_hash=#sign#&events=#events#";

		$arParams["PATH_TO_LOG_RSS"] = htmlspecialcharsbx($arParams["~PATH_TO_LOG_RSS"]);
	}
}
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
if (strlen($arParams["PATH_TO_SMILE"]) <= 0)
	$arParams["PATH_TO_SMILE"] = "/bitrix/images/socialnetwork/smile/";

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
if ($arParams["GROUP_ID"] <= 0)
	$arParams["GROUP_ID"] = IntVal($_REQUEST["flt_group_id"]);

if ($arParams["GROUP_ID"] > 0)
	$arParams["ENTITY_TYPE"] = SONET_ENTITY_GROUP;

if ($_REQUEST["skip_subscribe"] == "Y")
	$arParams["SUBSCRIBE_ONLY"] = "N";

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = IntVal($_REQUEST["flt_user_id"]);

if  (
	is_array($arParams["EVENT_ID"])
	&& count($arParams["EVENT_ID"]) > 0
	&& in_array("all", $arParams["EVENT_ID"])
)
	$arParams["EVENT_ID"] = array("all");
elseif (
	(!is_array($arParams["EVENT_ID"]) && strlen($arParams["EVENT_ID"]) <= 0)
	|| (is_array($arParams["EVENT_ID"]) && count($arParams["EVENT_ID"]) <= 0)
)
{
	if (
		array_key_exists("flt_event_id_all", $_REQUEST)
		&&
		(
			$_REQUEST["flt_event_id_all"] == "Y"
			|| !array_key_exists("flt_event_id", $_REQUEST)
		)
	)
	{
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = array("all");
		CUserOptions::DeleteOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));
	}
	elseif (array_key_exists("flt_event_id", $_REQUEST))
	{
		if (!is_array($_REQUEST["flt_event_id"]))
			$arParams["EVENT_ID"] = array($_REQUEST["flt_event_id"]);
		else
			$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"];

		CUserOptions::SetOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]), $arParams["EVENT_ID"]);
	}
	elseif(array_key_exists("log_filter_submit", $_REQUEST))
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = array("all");
	elseif($arParams["SHOW_EVENT_ID_FILTER"] != "N")
	{
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = CUserOptions::GetOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));
		if (!$_REQUEST["flt_event_id"])
			$_REQUEST["flt_event_id"] = array("all");
	}
}

$arParams["FLT_ALL"] = StrToUpper(Trim($arParams["FLT_ALL"]));
if (StrLen($arParams["FLT_ALL"]) <= 0)
	$arParams["FLT_ALL"] = StrToUpper(Trim($_REQUEST["flt_all"]));

if (is_array($_REQUEST["flt_created_by_id"]))
	$_REQUEST["flt_created_by_id"] = $_REQUEST["flt_created_by_id"][0];

preg_match('/^(\d+)$/', $_REQUEST["flt_created_by_id"], $matches);
if (count($matches) > 0)
	$arParams["CREATED_BY_ID"] = $_REQUEST["flt_created_by_id"];
else
{
	$arFoundUsers = CSocNetUser::SearchUser($_REQUEST["flt_created_by_id"], false);
	if (is_array($arFoundUsers) && count($arFoundUsers) > 0)
		$arParams["CREATED_BY_ID"] = key($arFoundUsers);
}

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
	array("#NOBR#", "#/NOBR#"),
	array("", ""),
	$arParams["NAME_TEMPLATE"]
);
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

$arParams["LOG_ID"] = IntVal($arParams["LOG_ID"]);

//$arFilter["ENTITY_TYPE"] = Trim($arFilter["ENTITY_TYPE"]);
//if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
//	$arFilter["ENTITY_TYPE"] = "";
if (StrLen($arParams["ENTITY_TYPE"]) <= 0)
	$arParams["ENTITY_TYPE"] = Trim($_REQUEST["flt_entity_type"]);
// if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
//	$arFilter["ENTITY_TYPE"] = "";

$arParams["LOG_DATE_DAYS"] = IntVal($arParams["LOG_DATE_DAYS"]);
if ($arParams["LOG_DATE_DAYS"] <= 0)
	unset($arParams["LOG_DATE_DAYS"]);

$arParams["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE"]) && intval($arParams["AVATAR_SIZE"]) != 0) ? intval($arParams["AVATAR_SIZE"]) : 42;
$arParams["AVATAR_SIZE_COMMENT"] = (isset($arParams["AVATAR_SIZE_COMMENT"]) && intval($arParams["AVATAR_SIZE_COMMENT"]) != 0) ? intval($arParams["AVATAR_SIZE_COMMENT"]) : 30;

$arParams["SET_LOG_CACHE"] = (isset($arParams["SET_LOG_CACHE"]) ? $arParams["SET_LOG_CACHE"] : "N");

$arParams["USE_COMMENTS"] = (isset($arParams["USE_COMMENTS"]) ? $arParams["USE_COMMENTS"] : "N");
$arParams["COMMENTS_IN_EVENT"] = (isset($arParams["COMMENTS_IN_EVENT"]) && intval($arParams["COMMENTS_IN_EVENT"]) > 0 ? $arParams["COMMENTS_IN_EVENT"] : "3");
$arParams["NEW_TEMPLATE"] = (isset($arParams["NEW_TEMPLATE"]) && $arParams["NEW_TEMPLATE"] == "Y" ? "Y" : "N");
$arParams["DESTINATION_LIMIT"] = (isset($arParams["DESTINATION_LIMIT"]) ? intval($arParams["DESTINATION_LIMIT"]) : 100);
$arParams["DESTINATION_LIMIT_SHOW"] = (isset($arParams["DESTINATION_LIMIT_SHOW"]) ? intval($arParams["DESTINATION_LIMIT_SHOW"]) : 3);

$arResult["AJAX_CALL"] = (array_key_exists("bxajaxid", $_REQUEST) || array_key_exists("AJAX_CALL", $_REQUEST));
$arResult["bReload"] = ($arResult["AJAX_CALL"] && $_REQUEST["RELOAD"] == "Y");

$arParams["SET_LOG_COUNTER"] = ($GLOBALS["USER"]->IsAuthorized() && $arParams["SET_LOG_CACHE"] == "Y" && (!$arResult["AJAX_CALL"] || $arResult["bReload"]) ? "Y" : "N");
$arParams["SET_LOG_PAGE_CACHE"] = "Y";

$arResult["SHOW_UNREAD"] = $arParams["SHOW_UNREAD"] = ($arParams["SET_LOG_COUNTER"] == "Y" ? "Y" : "N");
if (!$GLOBALS["USER"]->IsAuthorized())

$arResult["PresetFilters"] = false;
if (
	$GLOBALS["USER"]->IsAuthorized() 
	&& $arParams["LOG_ID"] <= 0
)
{
	$arPresetFilters = CUserOptions::GetOption("socialnetwork", "~log_filter_".SITE_ID, $GLOBALS["USER"]->GetID());
	if (!is_array($arPresetFilters))
		$arPresetFilters = CUserOptions::GetOption("socialnetwork", "~log_filter", $GLOBALS["USER"]->GetID());
}

if (is_array($arPresetFilters))
{

	if (!function_exists("__SL_PF_sort"))
	{
		function __SL_PF_sort($a, $b)
		{
			if ($a["SORT"] == $b["SORT"])
				return 0;
			return ($a["SORT"] < $b["SORT"]) ? -1 : 1;
		}
	}
	usort($arPresetFilters, "__SL_PF_sort");

	foreach ($arPresetFilters as $tmp_id_1 => $arPresetFilterTmp)
	{
		$bCorrect = true;

		if (array_key_exists("NAME", $arPresetFilterTmp))
		{
			switch($arPresetFilterTmp["NAME"])
			{
				case "#WORK#":
					$arPresetFilterTmp["NAME"] = GetMessage("SONET_INSTALL_LOG_PRESET_WORK");
					break;
				case "#FAVORITES#":
					$arPresetFilterTmp["NAME"] = GetMessage("SONET_INSTALL_LOG_PRESET_FAVORITES");
					break;
				case "#MY#":
					$arPresetFilterTmp["NAME"] = GetMessage("SONET_INSTALL_LOG_PRESET_MY");
					break;
			}
		}

		if (
			array_key_exists("FILTER", $arPresetFilterTmp)
			&& is_array($arPresetFilterTmp["FILTER"])
		)
		{
			foreach($arPresetFilterTmp["FILTER"] as $tmp_id_2 => $filterTmp)
			{
				if (
					(!is_array($filterTmp) && $filterTmp == "#CURRENT_USER_ID#")
					|| (is_array($filterTmp) && in_array("#CURRENT_USER_ID#", $filterTmp))
				)
				{
					if (!$GLOBALS["USER"]->IsAuthorized())
					{
						$bCorrect = false;
						break;
					}
					elseif (!is_array($filterTmp))
						$arPresetFilterTmp["FILTER"][$tmp_id_2] = $GLOBALS["USER"]->GetID();
					elseif (is_array($filterTmp))
						foreach($filterTmp as $tmp_id_3 => $valueTmp)
							if ($valueTmp == "#CURRENT_USER_ID#")
								$arPresetFilterTmp["FILTER"][$tmp_id_2][$tmp_id_3] = $GLOBALS["USER"]->GetID();
				}
			}
		}

		if ($bCorrect)
			$arResult["PresetFilters"][$arPresetFilterTmp["ID"]] = $arPresetFilterTmp;
	}

	if ($_REQUEST["preset_filter_id"] == "clearall")
		$preset_filter_id = false;
	elseif(array_key_exists("preset_filter_id", $_REQUEST) && strlen($_REQUEST["preset_filter_id"]) > 0)
		$preset_filter_id = $_REQUEST["preset_filter_id"];

	if(array_key_exists("preset_filter_id", $_REQUEST))
		CUserOptions::DeleteOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));

	if (
		strlen($preset_filter_id) > 0
		&& array_key_exists($preset_filter_id, $arResult["PresetFilters"])
		&& is_array($arResult["PresetFilters"][$preset_filter_id])
		&& array_key_exists("FILTER", $arResult["PresetFilters"][$preset_filter_id])
		&& is_array($arResult["PresetFilters"][$preset_filter_id]["FILTER"])
	)
	{
		if (array_key_exists("EVENT_ID", $arResult["PresetFilters"][$preset_filter_id]["FILTER"]))
		{
			$arParams["EVENT_ID"] = $arResult["PresetFilters"][$preset_filter_id]["FILTER"]["EVENT_ID"];
			$arResult["FILTER_COMMENTS"] = "N";
		}

		if (array_key_exists("CREATED_BY_ID", $arResult["PresetFilters"][$preset_filter_id]["FILTER"]))
			$arParams["CREATED_BY_ID"] = $arResult["PresetFilters"][$preset_filter_id]["FILTER"]["CREATED_BY_ID"];

		if (
			array_key_exists("FAVORITES_USER_ID", $arResult["PresetFilters"][$preset_filter_id]["FILTER"])
			&& $arResult["PresetFilters"][$preset_filter_id]["FILTER"]["FAVORITES_USER_ID"] == "Y"
		)
		{
			$arParams["FAVORITES"] = "Y";
			$arParams["SUBSCRIBE_ONLY"] = "N";
			$arResult["FILTER_COMMENTS"] = "N";
		}

		$arResult["PresetFilterActive"] = $preset_filter_id;
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
	}
	else
		$arResult["PresetFilterActive"] = false;
}

if (!isset($arResult["FILTER_COMMENTS"]))
{
	$arResult["FILTER_COMMENTS"] = (
		$arParams["FILTER_COMMENTS"] == "Y" 
		|| !array_key_exists("log_filter_submit", $_REQUEST) 
		|| (array_key_exists("flt_comments", $_REQUEST) && $_REQUEST["flt_comments"] == "Y") 
			? "Y"
			: "N"
	);
}

if (
	array_key_exists("flt_date_datesel", $_REQUEST)
	&& strlen($_REQUEST["flt_date_datesel"]) > 0
)
{
	switch($_REQUEST["flt_date_datesel"])
	{
		case "today":
			$arParams["LOG_DATE_FROM"] = $arParams["LOG_DATE_TO"] = ConvertTimeStamp();
			break;
		case "yesterday":
			$arParams["LOG_DATE_FROM"] = $arParams["LOG_DATE_TO"] = ConvertTimeStamp(time()-86400);
			break;
		case "week":
			$day = date("w");
			if($day == 0)
				$day = 7;
			$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time()-($day-1)*86400);
			$arParams["LOG_DATE_TO"] = ConvertTimeStamp(time()+(7-$day)*86400);
			break;
		case "week_ago":
			$day = date("w");
			if($day == 0)
				$day = 7;
			$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time()-($day-1+7)*86400);
			$arParams["LOG_DATE_TO"] = ConvertTimeStamp(time()-($day)*86400);
			break;
		case "month":
			$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1));
			$arParams["LOG_DATE_TO"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")+1, 0));
			break;
		case "month_ago":
			$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")-1, 1));
			$arParams["LOG_DATE_TO"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 0));
			break;
		case "days":
			$arParams["LOG_DATE_FROM"] = ConvertTimeStamp(time() - intval($_REQUEST["flt_date_days"])*86400);
			$arParams["LOG_DATE_TO"] = "";
			break;
		case "exact":
			$arParams["LOG_DATE_FROM"] = $arParams["LOG_DATE_TO"] = $_REQUEST["flt_date_from"];
			break;
		case "after":
			$arParams["LOG_DATE_FROM"] = $_REQUEST["flt_date_from"];
			$arParams["LOG_DATE_TO"] = "";
			break;
		case "before":
			$arParams["LOG_DATE_FROM"] = "";
			$arParams["LOG_DATE_TO"] = $_REQUEST["flt_date_to"];
			break;
		case "interval":
			$arParams["LOG_DATE_FROM"] = $_REQUEST["flt_date_from"];
			$arParams["LOG_DATE_TO"] = $_REQUEST["flt_date_to"];
			break;
	}
}
elseif (array_key_exists("flt_date_datesel", $_REQUEST))
{
	$arParams["LOG_DATE_FROM"] = "";
	$arParams["LOG_DATE_TO"] = "";
}
else
{
	if (array_key_exists("flt_date_from", $_REQUEST))
		$arParams["LOG_DATE_FROM"] = trim($_REQUEST["flt_date_from"]);

	if (array_key_exists("flt_date_to", $_REQUEST))
		$arParams["LOG_DATE_TO"] = trim($_REQUEST["flt_date_to"]);
}

if (
	array_key_exists("SUBSCRIBE_ONLY", $arParams)
	&& $arParams["SUBSCRIBE_ONLY"] == "Y"
	&& array_key_exists("flt_show_hidden", $_REQUEST)
	&& $_REQUEST["flt_show_hidden"] == "Y"
)
	$arParams["SHOW_HIDDEN"] = true;
elseif ($arParams["FAVORITES"] == "Y")
	$arParams["SHOW_HIDDEN"] = true;
else
	$arParams["SHOW_HIDDEN"] = false;

$arResult["SHOW_HIDDEN"] = $arParams["SHOW_HIDDEN"];

$arParams["AUTH"] = ((StrToUpper($arParams["AUTH"]) == "Y") ? "Y" : "N");

$arParams["LOG_CNT"] = (array_key_exists("LOG_CNT", $arParams) && intval($arParams["LOG_CNT"]) > 0 ? $arParams["LOG_CNT"] : 0);

$arParams["PAGE_SIZE"] = intval($arParams["PAGE_SIZE"]);
if($arParams["PAGE_SIZE"] <= 0)
{
	if ($arParams["USE_COMMENTS"] == "Y")
		$arParams["PAGE_SIZE"] = 20;
	else
		$arParams["PAGE_SIZE"] = 50;
}
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);

if(strlen($arParams["PATH_TO_USER_BLOG_POST"]) > 0)
	$arParams["PATH_TO_USER_MICROBLOG_POST"] = $arParams["PATH_TO_USER_BLOG_POST"];
$parent = $this->GetParent();
if (is_object($parent) && strlen($parent->__name) > 0)
{
	$arParams["PATH_TO_USER_MICROBLOG"] = $parent->arResult["PATH_TO_USER_BLOG"];
	if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
		$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arResult["PATH_TO_USER_BLOG_POST"];
	$arParams["PATH_TO_GROUP_MICROBLOG"] = $parent->arResult["PATH_TO_GROUP_BLOG"];
	$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arResult["PATH_TO_USER_BLOG_POST_EDIT"];
	if(strlen($arParams["PATH_TO_GROUP_MICROBLOG"]) <= 0)
		$arParams["PATH_TO_GROUP_MICROBLOG"] = $parent->arParams["PATH_TO_GROUP_BLOG"];
	if(strlen($arParams["PATH_TO_USER_MICROBLOG"]) <= 0)
		$arParams["PATH_TO_USER_MICROBLOG"] = $parent->arParams["PATH_TO_USER_BLOG"];
	if(strlen($arParams["PATH_TO_GROUP_MICROBLOG_POST"]) <= 0)
		$arParams["PATH_TO_GROUP_MICROBLOG_POST"] = $parent->arParams["PATH_TO_GROUP_BLOG_POST"];
	if(strlen($arParams["PATH_TO_GROUP_MICROBLOG_POST"]) <= 0)
		$arParams["PATH_TO_GROUP_MICROBLOG_POST"] = $parent->arResult["PATH_TO_GROUP_BLOG_POST"];
	if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
		$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arResult["PATH_TO_USER_BLOG_POST_EDIT"];
	if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
		$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arParams["PATH_TO_USER_BLOG_POST"];
	if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
		$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arParams["PATH_TO_USER_POST"];
	if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
		$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arParams["PATH_TO_USER_BLOG_POST_EDIT"];
	if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
		$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arParams["PATH_TO_USER_POST_EDIT"];
	if(strlen($arParams["BLOG_IMAGE_MAX_WIDTH"]) <= 0)
		$arParams["BLOG_IMAGE_MAX_WIDTH"] = $parent->arParams["BLOG_IMAGE_MAX_WIDTH"];
	if(strlen($arParams["BLOG_IMAGE_MAX_HEIGHT"]) <= 0)
		$arParams["BLOG_IMAGE_MAX_HEIGHT"] = $parent->arParams["BLOG_IMAGE_MAX_HEIGHT"];
	if(strlen($arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"]) <= 0)
		$arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"] = $parent->arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"];
	if(strlen($arParams["BLOG_ALLOW_POST_CODE"]) <= 0)
		$arParams["BLOG_ALLOW_POST_CODE"] = $parent->arParams["BLOG_ALLOW_POST_CODE"];
	if(strlen($arParams["BLOG_COMMENT_ALLOW_VIDEO"]) <= 0)
		$arParams["BLOG_COMMENT_ALLOW_VIDEO"] = $parent->arParams["BLOG_COMMENT_ALLOW_VIDEO"];
	$arParams["BLOG_GROUP_ID"] = $parent->arParams["BLOG_GROUP_ID"];
	if(isset($parent->arParams["BLOG_USE_CUT"]))
		$arParams["BLOG_USE_CUT"] = $parent->arParams["BLOG_USE_CUT"];

	$arParams["PHOTO_USER_IBLOCK_TYPE"] = $parent->arParams["PHOTO_USER_IBLOCK_TYPE"];
	$arParams["PHOTO_USER_IBLOCK_ID"] = $parent->arParams["PHOTO_USER_IBLOCK_ID"];
	$arParams["PHOTO_GROUP_IBLOCK_TYPE"] = $parent->arParams["PHOTO_GROUP_IBLOCK_TYPE"];
	$arParams["PHOTO_GROUP_IBLOCK_ID"] = $parent->arParams["PHOTO_GROUP_IBLOCK_ID"];
	$arParams["PHOTO_MAX_VOTE"] = $parent->arParams["PHOTO_MAX_VOTE"];
	$arParams["PHOTO_USE_COMMENTS"] = $parent->arParams["PHOTO_USE_COMMENTS"];
	$arParams["PHOTO_COMMENTS_TYPE"] = $parent->arParams["PHOTO_COMMENTS_TYPE"];
	$arParams["PHOTO_FORUM_ID"] = $parent->arParams["PHOTO_FORUM_ID"];
	$arParams["PHOTO_BLOG_URL"] = $parent->arParams["PHOTO_BLOG_URL"];
	$arParams["PHOTO_USE_CAPTCHA"] = $parent->arParams["PHOTO_USE_CAPTCHA"];

	if (
		(
			strlen($arParams["PHOTO_GROUP_IBLOCK_TYPE"]) <= 0
			|| intval($arParams["PHOTO_GROUP_IBLOCK_ID"]) <= 0
		)
		&& CModule::IncludeModule("iblock"))
	{
		$ttl = 86400;
		$cache_id = 'sonet_group_photo_iblock_'.SITE_ID;
		$cache_dir = '/bitrix/sonet_group_photo_iblock';
		$obCache = new CPHPCache;

		if($obCache->InitCache($ttl, $cache_id, $cache_dir))
		{
			$cacheData = $obCache->GetVars();
			$arParams["PHOTO_GROUP_IBLOCK_TYPE"] = $cacheData["PHOTO_GROUP_IBLOCK_TYPE"];
			$arParams["PHOTO_GROUP_IBLOCK_ID"] = $cacheData["PHOTO_GROUP_IBLOCK_ID"];
			unset($cacheData);
		}
		else
		{
			$rsIBlockType = CIBlockType::GetByID("photos");
			if ($arIBlockType = $rsIBlockType->Fetch())
			{
				$rsIBlock = CIBlock::GetList(
					array("SORT" => "ASC"),
					array(
						"IBLOCK_TYPE" => $arIBlockType["ID"],
						"=CODE" => array("group_photogallery", "group_photogallery_".SITE_ID),
						"ACTIVE" => "Y",
						"SITE_ID" => SITE_ID
					)
				);
				if ($arIBlock = $rsIBlock->Fetch())
				{
					$arParams["PHOTO_GROUP_IBLOCK_TYPE"] = $arIBlock["IBLOCK_TYPE_ID"];
					$arParams["PHOTO_GROUP_IBLOCK_ID"] = $arIBlock["ID"];
				}
			}

			if ($obCache->StartDataCache())
			{
				$obCache->EndDataCache(array(
					"PHOTO_GROUP_IBLOCK_TYPE" => $arIBlock["IBLOCK_TYPE_ID"],
					"PHOTO_GROUP_IBLOCK_ID" => $arIBlock["ID"]
				));
			}
		}
		unset($obCache);
	}

	$arParams["PATH_TO_USER_PHOTO"] = $parent->arResult["PATH_TO_USER_PHOTO"];
	$arParams["PATH_TO_GROUP_PHOTO"] = $parent->arResult["PATH_TO_GROUP_PHOTO"];
	if (strlen($arParams["PATH_TO_GROUP_PHOTO"]) <= 0)
		$arParams["PATH_TO_GROUP_PHOTO"] = $parent->arParams["PATH_TO_GROUP_PHOTO"];

	$arParams["PATH_TO_USER_PHOTO_SECTION"] = $parent->arResult["PATH_TO_USER_PHOTO_SECTION"];
	$arParams["PATH_TO_GROUP_PHOTO_SECTION"] = $parent->arResult["PATH_TO_GROUP_PHOTO_SECTION"];
	if (strlen($arParams["PATH_TO_GROUP_PHOTO_SECTION"]) <= 0)
		$arParams["PATH_TO_GROUP_PHOTO_SECTION"] = $parent->arParams["PATH_TO_GROUP_PHOTO_SECTION"];

	$arParams["PATH_TO_USER_PHOTO_ELEMENT"] = $parent->arResult["PATH_TO_USER_PHOTO_ELEMENT"];
	$arParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = $parent->arResult["PATH_TO_GROUP_PHOTO_ELEMENT"];
	if (strlen($arParams["PATH_TO_GROUP_PHOTO_ELEMENT"]) <= 0)
		$arParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = $parent->arParams["PATH_TO_GROUP_PHOTO_ELEMENT"];

	$arParams["PHOTO_COUNT"] = $parent->arParams["LOG_PHOTO_COUNT"];
	$arParams["PHOTO_THUMBNAIL_SIZE"] = $parent->arParams["LOG_PHOTO_THUMBNAIL_SIZE"];

	$arParams["FORUM_ID"] = $parent->arParams["FORUM_ID"];

	// parent of 2nd level
	$parent = $parent->GetParent();
	if (is_object($parent) && strlen($parent->__name) > 0)
	{
		if(strlen($arParams["PATH_TO_USER_MICROBLOG"]) <= 0)
			$arParams["PATH_TO_USER_MICROBLOG"] = $parent->arResult["PATH_TO_USER_BLOG"];
		if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
			$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arResult["PATH_TO_USER_BLOG_POST"];
		if(strlen($arParams["PATH_TO_GROUP_MICROBLOG"]) <= 0)
			$arParams["PATH_TO_GROUP_MICROBLOG"] = $parent->arResult["PATH_TO_GROUP_BLOG"];
		if(strlen($arParams["PATH_TO_GROUP_MICROBLOG"]) <= 0)
			$arParams["PATH_TO_GROUP_MICROBLOG"] = $parent->arParams["PATH_TO_GROUP_BLOG"];
		if(strlen($arParams["PATH_TO_USER_MICROBLOG"]) <= 0)
			$arParams["PATH_TO_USER_MICROBLOG"] = $parent->arParams["PATH_TO_USER_BLOG"];
		if(strlen($arParams["PATH_TO_GROUP_MICROBLOG_POST"]) <= 0)
			$arParams["PATH_TO_GROUP_MICROBLOG_POST"] = $parent->arParams["PATH_TO_GROUP_BLOG_POST"];
		if(strlen($arParams["PATH_TO_GROUP_MICROBLOG_POST"]) <= 0)
			$arParams["PATH_TO_GROUP_MICROBLOG_POST"] = $parent->arResult["PATH_TO_GROUP_BLOG_POST"];
		if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
			$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arResult["PATH_TO_USER_BLOG_POST_EDIT"];
		if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
			$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arParams["PATH_TO_USER_BLOG_POST"];
		if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
			$arParams["PATH_TO_USER_MICROBLOG_POST"] = $parent->arParams["PATH_TO_USER_POST"];
		if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
			$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arParams["PATH_TO_USER_BLOG_POST_EDIT"];
		if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
			$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = $parent->arParams["PATH_TO_USER_POST_EDIT"];
		if(strlen($arParams["BLOG_IMAGE_MAX_WIDTH"]) <= 0)
			$arParams["BLOG_IMAGE_MAX_WIDTH"] = $parent->arParams["BLOG_IMAGE_MAX_WIDTH"];
		if(strlen($arParams["BLOG_IMAGE_MAX_HEIGHT"]) <= 0)
			$arParams["BLOG_IMAGE_MAX_HEIGHT"] = $parent->arParams["BLOG_IMAGE_MAX_HEIGHT"];
		if(strlen($arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"]) <= 0)
			$arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"] = $parent->arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"];
		if(strlen($arParams["BLOG_ALLOW_POST_CODE"]) <= 0)
			$arParams["BLOG_ALLOW_POST_CODE"] = $parent->arParams["BLOG_ALLOW_POST_CODE"];
		if(strlen($arParams["BLOG_COMMENT_ALLOW_VIDEO"]) <= 0)
			$arParams["BLOG_COMMENT_ALLOW_VIDEO"] = $parent->arParams["BLOG_COMMENT_ALLOW_VIDEO"];
		if(intval($arParams["BLOG_GROUP_ID"]) <= 0)
			$arParams["BLOG_GROUP_ID"] = $parent->arParams["BLOG_GROUP_ID"];
		if(isset($parent->arParams["BLOG_USE_CUT"]))
			$arParams["BLOG_USE_CUT"] = $parent->arParams["BLOG_USE_CUT"];

		if(strlen($arParams["PHOTO_USER_IBLOCK_TYPE"]) <= 0)
			$arParams["PHOTO_USER_IBLOCK_TYPE"] = $parent->arParams["PHOTO_USER_IBLOCK_TYPE"];
		if(intval($arParams["PHOTO_USER_IBLOCK_ID"]) <= 0)
			$arParams["PHOTO_USER_IBLOCK_ID"] = $parent->arParams["PHOTO_USER_IBLOCK_ID"];
		if(strlen($arParams["PHOTO_GROUP_IBLOCK_TYPE"]) <= 0)
			$arParams["PHOTO_GROUP_IBLOCK_TYPE"] = $parent->arParams["PHOTO_GROUP_IBLOCK_TYPE"];
		if(intval($arParams["PHOTO_GROUP_IBLOCK_ID"]) <= 0)
			$arParams["PHOTO_GROUP_IBLOCK_ID"] = $parent->arParams["PHOTO_GROUP_IBLOCK_ID"];
		if(intval($arParams["PHOTO_MAX_VOTE"]) <= 0)
			$arParams["PHOTO_MAX_VOTE"] = $parent->arParams["PHOTO_MAX_VOTE"];
		if(strlen($arParams["PHOTO_USE_COMMENTS"]) <= 0)
			$arParams["PHOTO_USE_COMMENTS"] = $parent->arParams["PHOTO_USE_COMMENTS"];
		if(strlen($arParams["PHOTO_COMMENTS_TYPE"]) <= 0)
			$arParams["PHOTO_COMMENTS_TYPE"] = $parent->arParams["PHOTO_COMMENTS_TYPE"];
		if(intval($arParams["PHOTO_FORUM_ID"]) <= 0)
			$arParams["PHOTO_FORUM_ID"] = $parent->arParams["PHOTO_FORUM_ID"];
		if(strlen($arParams["PHOTO_BLOG_URL"]) <= 0)
			$arParams["PHOTO_BLOG_URL"] = $parent->arParams["PHOTO_BLOG_URL"];
		if(strlen($arParams["PHOTO_USE_CAPTCHA"]) <= 0)
			$arParams["PHOTO_USE_CAPTCHA"] = $parent->arParams["PHOTO_USE_CAPTCHA"];

		if(strlen($arParams["PATH_TO_USER_PHOTO"]) <= 0)
			$arParams["PATH_TO_USER_PHOTO"] = $parent->arResult["PATH_TO_USER_PHOTO"];
		if(strlen($arParams["PATH_TO_GROUP_PHOTO"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO"] = $parent->arResult["PATH_TO_GROUP_PHOTO"];
		if (strlen($arParams["PATH_TO_GROUP_PHOTO"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO"] = $parent->arParams["PATH_TO_GROUP_PHOTO"];

		if(strlen($arParams["PATH_TO_USER_PHOTO_SECTION"]) <= 0)
			$arParams["PATH_TO_USER_PHOTO_SECTION"] = $parent->arResult["PATH_TO_USER_PHOTO_SECTION"];
		if(strlen($arParams["PATH_TO_GROUP_PHOTO_SECTION"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO_SECTION"] = $parent->arResult["PATH_TO_GROUP_PHOTO_SECTION"];
		if (strlen($arParams["PATH_TO_GROUP_PHOTO_SECTION"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO_SECTION"] = $parent->arParams["PATH_TO_GROUP_PHOTO_SECTION"];

		if(strlen($arParams["PATH_TO_USER_PHOTO_ELEMENT"]) <= 0)
			$arParams["PATH_TO_USER_PHOTO_ELEMENT"] = $parent->arResult["PATH_TO_USER_PHOTO_ELEMENT"];
		if(strlen($arParams["PATH_TO_GROUP_PHOTO_ELEMENT"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = $parent->arResult["PATH_TO_GROUP_PHOTO_ELEMENT"];
		if (strlen($arParams["PATH_TO_GROUP_PHOTO_ELEMENT"]) <= 0)
			$arParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = $parent->arParams["PATH_TO_GROUP_PHOTO_ELEMENT"];

		if(intval($arParams["PHOTO_COUNT"]) <= 0)
			$arParams["PHOTO_COUNT"] = $parent->arParams["LOG_PHOTO_COUNT"];
		if(intval($arParams["PHOTO_THUMBNAIL_SIZE"]) <= 0)
			$arParams["PHOTO_THUMBNAIL_SIZE"] = $parent->arParams["LOG_PHOTO_THUMBNAIL_SIZE"];

		if(intval($arParams["FORUM_ID"]) <= 0)
			$arParams["FORUM_ID"] = $parent->arParams["FORUM_ID"];
	}
}
if(strlen($arParams["PATH_TO_USER_MICROBLOG_POST"]) <= 0)
	$arParams["PATH_TO_USER_MICROBLOG_POST"] = "/company/personal/user/#user_id#/blog/#post_id#/";
if(strlen($arParams["PATH_TO_USER_BLOG_POST_EDIT"]) <= 0)
	$arParams["PATH_TO_USER_BLOG_POST_EDIT"] = "/company/personal/user/#user_id#/blog/edit/#post_id#/";


if (intval($arParams["PHOTO_COUNT"]) <= 0)
	$arParams["PHOTO_COUNT"] = 6;
if (intval($arParams["PHOTO_THUMBNAIL_SIZE"]) <= 0)
	$arParams["PHOTO_THUMBNAIL_SIZE"] = 48;

$arParams["PAGER_DESC_NUMBERING"] = ($arParams["PAGER_DESC_NUMBERING"] == "Y");

if(
	IntVal($GLOBALS["USER"]->GetID()) > 0
	&& (
		(
			$arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP 
			&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $GLOBALS["USER"]->GetID(), "blog")
		) 
		|| (
			$arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP 
			&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["GROUP_ID"], "blog")
		)
	)
)
	$arResult["MICROBLOG_USER_ID"] = $GLOBALS["USER"]->GetID();

if ($this->__parent && $this->__parent->arResult && array_key_exists("PATH_TO_SUBSCRIBE", $this->__parent->arResult))
	$arResult["PATH_TO_SUBSCRIBE"] = $this->__parent->arResult["PATH_TO_SUBSCRIBE"];

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();

$GLOBALS["arExtranetGroupID"] = array();
$GLOBALS["arExtranetUserID"] = array();

if($GLOBALS["USER"]->IsAuthorized())
{
	if(defined("BX_COMP_MANAGED_CACHE"))
		$ttl = 2592000;
	else
		$ttl = 600;

	$cache_id = 'sonet_ex_gr_'.SITE_ID;
	$obCache = new CPHPCache;
	$cache_dir = '/bitrix/sonet_log_sg';

	if($obCache->InitCache($ttl, $cache_id, $cache_dir))
	{
		$tmpVal = $obCache->GetVars();
		$GLOBALS["arExtranetGroupID"] = $tmpVal['EX_GROUP_ID'];
		$GLOBALS["arExtranetUserID"] = $tmpVal['EX_USER_ID'];
		unset($tmpVal);
	}
	elseif (CModule::IncludeModule("extranet") && !CExtranet::IsExtranetSite())
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->StartTagCache($cache_dir);
		$dbGroupTmp = CSocNetGroup::GetList(
			array(),
			array(
				"SITE_ID" => CExtranet::GetExtranetSiteID()
			),
			false,
			false,
			array("ID")
		);
		while($arGroupTmp = $dbGroupTmp->Fetch())
		{
			$GLOBALS["arExtranetGroupID"][] = $arGroupTmp["ID"];
			$CACHE_MANAGER->RegisterTag('sonet_group_'.$arGroupTmp["ID"]);
		}

		$rsUsers = CUser::GetList(
			($by="ID"),
			($order="asc"),
			array(
				"GROUPS_ID" => array(CExtranet::GetExtranetUserGroupID()),
				"UF_DEPARTMENT" => false
			),
			array("FIELDS" => array("ID"))
		);
		while($arUser = $rsUsers->Fetch())
		{
			$GLOBALS["arExtranetUserID"][] = $arUser["ID"];
			$CACHE_MANAGER->RegisterTag('sonet_user2group_U'.$arUser["ID"]);
		}
		$CACHE_MANAGER->EndTagCache();
		if($obCache->StartDataCache())
			$obCache->EndDataCache(array(
				'EX_GROUP_ID' => $GLOBALS["arExtranetGroupID"],
				'EX_USER_ID' => $GLOBALS["arExtranetUserID"]
			));
	}
	unset($obCache);
}

if (
	$GLOBALS["USER"]->IsAuthorized() 
	|| $arParams["AUTH"] == "Y" 
	|| $arParams["SUBSCRIBE_ONLY"] != "Y"
)
{
	if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
	{
		if (
			array_key_exists("ENTITY_TYPE", $arParams) 
			&& strlen("ENTITY_TYPE") > 0 
			&& array_key_exists("ENTITY_ID", $arParams) 
			&& intval("ENTITY_ID") > 0
		)
		{
			$arResult["ActiveFeatures"] = CSocNetFeatures::GetActiveFeaturesNames($arParams["ENTITY_TYPE"], ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));

			foreach($arResult["ActiveFeatures"] as $featureID => $featureName)
			{
				$minoperation = $GLOBALS["arSocNetFeaturesSettings"][$featureID]["minoperation"];
				$bCanView = (array_key_exists($featureID, $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arParams["ENTITY_TYPE"], ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]), $featureID, $minoperation[count($minoperation)-1], CSocNetUser::IsCurrentUserModuleAdmin()));
				if (!$bCanView)
					unset($arResult["ActiveFeatures"][$featureID]);
			}
		}
		else
		{
			$arResult["ActiveFeatures"] = array();
			foreach ($GLOBALS["arSocNetFeaturesSettings"] as $featureID => $arFeature)
			{
				if (array_key_exists("subscribe_events", $arFeature) && is_array($arFeature["subscribe_events"]))
				{
					foreach($arFeature["subscribe_events"] as $event_id => $arEventTmp)
					{
						if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
							continue;

						$arTitleTmp = array();

						if (array_key_exists("ENTITIES", $arEventTmp) && is_array($arEventTmp["ENTITIES"]))
						{
							foreach($arEventTmp["ENTITIES"] as $entity_type_tmp => $arDescTmp)
								if (array_key_exists("TITLE", $arDescTmp) && !in_array($arDescTmp["TITLE"], $arTitleTmp))
									$arTitleTmp[] = $arDescTmp["TITLE"];
						}

						if (count($arTitleTmp) > 0)
							$arResult["ActiveFeatures"][$event_id] = implode("/", $arTitleTmp);
					}
				}
			}
		}

		$arSystemEvents = array();
		foreach ($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
		{
			if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
				continue;

			$arTitleTmp = array();
			if (array_key_exists("ENTITIES", $arEventTmp) && is_array($arEventTmp["ENTITIES"]))
			{
				foreach($arEventTmp["ENTITIES"] as $entity_type_tmp => $arDescTmp)
					if (array_key_exists("TITLE", $arDescTmp) && !in_array($arDescTmp["TITLE"], $arTitleTmp))
						$arTitleTmp[] = $arDescTmp["TITLE"];
			}
			if (count($arTitleTmp) > 0)
				$arSystemEvents[$event_id_tmp] = implode("/", $arTitleTmp);
		}

		$arResult["ActiveFeatures"] = array_merge(array("all" => false), $arResult["ActiveFeatures"], $arSystemEvents);

		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
		{
			$sUserRole = CSocNetUserToGroup::GetUserRole(
				$GLOBALS["USER"]->GetID(),
				$arParams["GROUP_ID"]
			);

			if (!in_array($sUserRole, array(SONET_ROLES_USER, SONET_ROLES_MODERATOR, SONET_ROLES_OWNER)) && !CSocNetUser::IsCurrentUserModuleAdmin())
				unset($arResult["ActiveFeatures"]["system"]);
		}
	}

	$arTmpEventsOld = array();
	$arTmpEventsNew = array();

	$arResult["IS_FILTERED"] = false;

	if (
		(
			($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N") 
			&& $arParams["SUBSCRIBE_ONLY"] == "N"
		)
		|| $arParams["GROUP_ID"] > 0
	)
	{
		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER)
		{
			$rsUser = CUser::GetByID($arParams["USER_ID"]);
			if ($arResult["User"] = $rsUser->Fetch())
				$strTitleFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);
		}
		elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			$arResult["Group"] = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
	}

	if ($arParams["LOG_ID"] > 0)
	{
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(str_replace("#LOG_ID#", $arParams["LOG_ID"], GetMessage("SONET_C73_ENTRY_PAGE_TITLE")));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem(GetMessage("SONET_C73_PAGE_TITLE"), $arParams["PATH_TO_LOG"]);
			$APPLICATION->AddChainItem(str_replace("#LOG_ID#", $arParams["LOG_ID"], GetMessage("SONET_C73_ENTRY_NAV_CHAIN_ITEM")));
		}
	}
	else
	{
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(GetMessage("SONET_C73_PAGE_TITLE"));

		if ($arParams["SET_NAV_CHAIN"] != "N")
			$APPLICATION->AddChainItem(GetMessage("SONET_C73_PAGE_TITLE"));
	}

	$arResult["Urls"]["ViewAll"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("flt_all=Y", array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));
	$arResult["Urls"]["ViewSubscr"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("", array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));

	if (CBXFeatures::IsFeatureEnabled("Workgroups"))
		$arResult["Urls"]["ViewGroups"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_GROUP, array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));
	else
		$arResult["Urls"]["ViewGroups"] = "";

	$arResult["Urls"]["ViewUsers"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_USER, array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));

	$arResult["Events"] = false;
	$arResult["EventsNew"] = false;

	$arFilter = array();

	if ($arParams["LOG_ID"] > 0)
		$arFilter["ID"] = $arParams["LOG_ID"];

	if(isset($arParams["DISPLAY"]))
	{
		$arResult["SHOW_UNREAD"] = $arParams["SHOW_UNREAD"] = "N";

		$arParams["SHOW_EVENT_ID_FILTER"] = "N";
		unset($arParams["LOG_DATE_DAYS"]);
		if($arParams["DISPLAY"] === "forme")
		{
			$arAccessCodes = $USER->GetAccessCodes();
			foreach($arAccessCodes as $i => $code)
				if(!preg_match("/^(U|D|DR)/", $code)) //Users and Departments
					unset($arAccessCodes[$i]);
			$arFilter["LOG_RIGHTS"] = $arAccessCodes;
			$arFilter["!USER_ID"] = $USER->GetID();
			$arResult["IS_FILTERED"] = true;
			$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
			$arParams["USE_FOLLOW"] = "N";
		}
		elseif($arParams["DISPLAY"] === "mine")
		{
			$arFilter["USER_ID"] = $USER->GetID();
			$arResult["IS_FILTERED"] = true;
			$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
			$arParams["USE_FOLLOW"] = "N";
		}
		elseif($arParams["DISPLAY"] === "my")
		{
			$arAccessCodes = $USER->GetAccessCodes();
			foreach($arAccessCodes as $i => $code)
				if(!preg_match("/^(U|D|DR)/", $code)) //Users and Departments
					unset($arAccessCodes[$i]);
			$arFilter["LOG_RIGHTS"] = $arAccessCodes;
			$arParams["SET_LOG_PAGE_CACHE"] = "N";
			$arParams["USE_FOLLOW"] = "N";
		}
		elseif($arParams["DISPLAY"] > 0)
		{
			$arFilter["USER_ID"] = intval($arParams["DISPLAY"]);
			$arResult["IS_FILTERED"] = true;
			$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
			$arParams["USE_FOLLOW"] = "N";
		}
	}

	if ($arParams["DESTINATION"] > 0)
		$arFilter["LOG_RIGHTS"] = $arParams["DESTINATION"];
	elseif ($arParams["GROUP_ID"] > 0)
	{
		$ENTITY_TYPE = SONET_ENTITY_GROUP;
		$ENTITY_ID = $arParams["GROUP_ID"];

		$arFilter["LOG_RIGHTS"] = "SG".intval($arParams["GROUP_ID"]);
		$arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
	}
	elseif ($arParams["USER_ID"] > 0)
	{
		$ENTITY_TYPE = $arFilter["ENTITY_TYPE"] = SONET_ENTITY_USER;
		$ENTITY_ID = $arFilter["ENTITY_ID"] = $arParams["USER_ID"];
	}
	elseif (StrLen($arParams["ENTITY_TYPE"]) > 0)
	{
		$ENTITY_TYPE = $arFilter["ENTITY_TYPE"] = $arParams["ENTITY_TYPE"];
		$ENTITY_ID = 0;
	}
	else
	{
		$ENTITY_TYPE = "";
		$ENTITY_ID = 0;
	}

	if (isset($arParams["EXACT_EVENT_ID"]))
	{
		$arFilter["EVENT_ID"] = array($arParams["EXACT_EVENT_ID"]);
		$arResult["IS_FILTERED"] = true;
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
	}
	elseif (is_array($arParams["EVENT_ID"]))
	{
		if (!in_array("all", $arParams["EVENT_ID"]))
		{
			$event_id_fullset_tmp = array();
			foreach($arParams["EVENT_ID"] as $event_id_tmp)
				$event_id_fullset_tmp = array_merge($event_id_fullset_tmp, CSocNetLogTools::FindFullSetByEventID($event_id_tmp));
			$arFilter["EVENT_ID"] = array_unique($event_id_fullset_tmp);

			$arResult["IS_FILTERED"] = true;
			$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
			$arParams["USE_FOLLOW"] = "N";
		}
	}
	elseif ($arParams["EVENT_ID"] && $arParams["EVENT_ID"] != "all")
	{
		$arFilter["EVENT_ID"] = CSocNetLogTools::FindFullSetByEventID($arParams["EVENT_ID"]);
		$arResult["IS_FILTERED"] = true;
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
	}

	if (IntVal($arParams["CREATED_BY_ID"]) > 0)
	{
		$arFilter["USER_ID"] = $arParams["CREATED_BY_ID"];
		$arResult["IS_FILTERED"] = true;
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
	}

	if (IntVal($arParams["GROUP_ID"]) > 0)
		$arResult["IS_FILTERED"] = true;

	if ($arParams["FLT_ALL"] == "Y")
		$arFilter["ALL"] = "Y";

	if (
		$ENTITY_TYPE != "" 
		&& $ENTITY_ID > 0
		&& !array_key_exists("EVENT_ID", $arFilter)
	)
	{
		$arFilter["EVENT_ID"] = array();

		foreach($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
		{
			if (
				array_key_exists("HIDDEN", $arEventTmp)
				&& $arEventTmp["HIDDEN"]
			)
				continue;

			$arFilter["EVENT_ID"][] = $event_id_tmp;
		}

		$arFeatures = CSocNetFeatures::GetActiveFeatures($ENTITY_TYPE, $ENTITY_ID);
		foreach($arFeatures as $feature_id)
		{
			if(
				array_key_exists($feature_id, $GLOBALS["arSocNetFeaturesSettings"])
				&& array_key_exists("subscribe_events", $GLOBALS["arSocNetFeaturesSettings"][$feature_id])
			)
				foreach ($GLOBALS["arSocNetFeaturesSettings"][$feature_id]["subscribe_events"] as $event_id_tmp => $arEventTmp)
					$arFilter["EVENT_ID"][] = $event_id_tmp;
		}
	}

	if (
		!$arFilter["EVENT_ID"]
		|| (is_array($arFilter["EVENT_ID"]) && count($arFilter["EVENT_ID"]) <= 0)
	)
		unset($arFilter["EVENT_ID"]);

	if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		$arFilter["SITE_ID"] = SITE_ID;
	else
		$arFilter["SITE_ID"] = array(SITE_ID, false);

	if ($arParams["LOG_DATE_DAYS"] > 0)
	{
		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
			$arFilter["LOG_DATE_DAYS"] = $arParams["LOG_DATE_DAYS"];
		else
		{
			$arrAdd = array(
				"DD" => -($arParams["LOG_DATE_DAYS"]),
				"MM" => 0,
				"YYYY" => 0,
				"HH" => 0,
				"MI" => 0,
				"SS" => 0,
			);
			$stmp = AddToTimeStamp($arrAdd, time()+$arResult["TZ_OFFSET"]);
			$arFilter[">=LOG_DATE"] = ConvertTimeStamp($stmp, "FULL");
		}
	}

	if (
		array_key_exists("LOG_DATE_FROM", $arParams)
		&& strlen(trim($arParams["LOG_DATE_FROM"])) > 0
		&& MakeTimeStamp($arParams["LOG_DATE_FROM"], CSite::GetDateFormat("SHORT")) < time()+$arResult["TZ_OFFSET"]
	)
	{
		$arFilter[">=LOG_DATE"] = $arParams["LOG_DATE_FROM"];
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
		$arResult["IS_FILTERED"] = true;
	}
	else
		unset($_REQUEST["flt_date_from"]);

	if (
		array_key_exists("LOG_DATE_TO", $arParams)
		&& strlen(trim($arParams["LOG_DATE_TO"])) > 0
		&& MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT")) < time()+$arResult["TZ_OFFSET"]
	)
	{
		$arFilter["<=LOG_DATE"] = ConvertTimeStamp(MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT"))+86399, "FULL");
		$arParams["SET_LOG_COUNTER"] = $arParams["SET_LOG_PAGE_CACHE"] = "N";
		$arParams["USE_FOLLOW"] = "N";
		$arResult["IS_FILTERED"] = true;
	}
	else
	{
		$arFilter["<=LOG_DATE"] = "NOW";
		unset($_REQUEST["flt_date_to"]);
	}

	if ($arParams["LOG_ID"] > 0)
		$arNavStartParams = false;
	else
	{
		if (intval($arParams["LOG_CNT"]) > 0)
			$arNavStartParams = array(
				"nTopCount" => $arParams["LOG_CNT"]
			);
		elseif ($arParams["AJAX_MODE"] == "Y" && (!$arResult["AJAX_CALL"] || $arResult["bReload"]) && !$arParams["PAGER_DESC_NUMBERING"])
		{
			$arNavStartParams = array("nTopCount" => $arParams["PAGE_SIZE"]);
			$arResult["PAGE_NUMBER"] = 1;
			$bFirstPage = true;
		}
		else
		{
			if (intval($_REQUEST["PAGEN_".($GLOBALS["NavNum"] + 1)]) > 0)
				$arResult["PAGE_NUMBER"] = intval($_REQUEST["PAGEN_".($GLOBALS["NavNum"] + 1)]);

			$arNavStartParams = array(
				"nPageSize" => $arParams["PAGE_SIZE"],
				"bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
				"bShowAll" => false,
				"iNavAddRecords" => 1,
				"bSkipPageReset" => true
			);
		}
	}
	if ($arParams["USE_FOLLOW"] == "Y")
		$arOrder = array("DATE_FOLLOW" => "DESC");
	elseif ($arParams["USE_COMMENTS"] == "Y")
		$arOrder = array("LOG_UPDATE" => "DESC");
	else
		$arOrder = array("LOG_DATE"	=> "DESC");

	if ($arParams["FAVORITES"] == "Y")
		$arFilter[">FAVORITES_USER_ID"] = 0;

	$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE_WO_NOBR"];

	if (intval($arParams["GROUP_ID"]) > 0)
		$arResult["COUNTER_TYPE"] = "SG".intval($arParams["GROUP_ID"]);
	elseif($arParams["EXACT_EVENT_ID"] == "blog_post")
		$arResult["COUNTER_TYPE"] = "blog_post";
	else
		$arResult["COUNTER_TYPE"] = "**";

	if ($arParams["LOG_ID"] <= 0)
	{
		if (!$arResult["AJAX_CALL"] || $arResult["bReload"])
		{
			$arResult["LAST_LOG_TS"] = CUserCounter::GetLastDate($GLOBALS["USER"]->GetID(), $arResult["COUNTER_TYPE"]);

			if($arResult["LAST_LOG_TS"] == 0)
				$arResult["LAST_LOG_TS"] = 1;
			else
			{
				//We substruct TimeZone offset in order to get server time
				//because of template compatibility
				$arResult["LAST_LOG_TS"] -= $arResult["TZ_OFFSET"];
			}
		}
		else
			$arResult["LAST_LOG_TS"] = intval($_REQUEST["ts"]);

		if ($arParams["SET_LOG_PAGE_CACHE"] == "Y")
		{
			$rsLogPages = CSocNetLogPages::GetList(
				array(
					"USER_ID" => $GLOBALS["USER"]->GetID(),
					"SITE_ID" => SITE_ID,
					"PAGE_SIZE" => $arParams["PAGE_SIZE"],
					"PAGE_NUM" => $arResult["PAGE_NUMBER"]
				),
				array("PAGE_LAST_DATE")
			);

			if ($arLogPages = $rsLogPages->Fetch())
			{
				$arFilter[">=LOG_UPDATE"] = $arLogPages["PAGE_LAST_DATE"];
				$arResult["SHOW_MORE_LINK"] = true;
			}
		}
	}

	if (
		$arParams["USE_FOLLOW"] == "Y"
		|| $arParams["SUBSCRIBE_ONLY"] == "Y"
	)
	{
		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
		{
			foreach($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"] as $entity_type_tmp => $arEntityTypeTmp)
				if (
					array_key_exists("HAS_MY", $arEntityTypeTmp)
					&& $arEntityTypeTmp["HAS_MY"] == "Y"
					&& array_key_exists("CLASS_MY", $arEntityTypeTmp)
					&& array_key_exists("METHOD_MY", $arEntityTypeTmp)
					&& strlen($arEntityTypeTmp["CLASS_MY"]) > 0
					&& strlen($arEntityTypeTmp["METHOD_MY"]) > 0
					&& method_exists($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"])
				)
					$arMyEntities[$entity_type_tmp] = call_user_func(array($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"]));
		}

		$arListParams = array(
			"CHECK_RIGHTS" => "Y",
		);

		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
		{
			$arListParams["USE_SUBSCRIBE"] = "Y";
			$arListParams["MY_ENTITIES"] = $arMyEntities;
			$arListParams["MIN_ID_JOIN"] = true;
		}
		elseif ($arParams["USE_FOLLOW"] == "Y")
			$arListParams["USE_FOLLOW"] = "Y";

		if ($bCurrentUserIsAdmin)
			$arListParams["USER_ID"] = "A";

		if (!$arParams["SHOW_HIDDEN"])
			$arListParams["VISIBLE"] = "Y";
		else
		{
			$arListParams["USE_SUBSCRIBE"] = "N";
			$arListParams["USE_FOLLOW"] = "N";
			$arResult["IS_FILTERED"] = true;
		}

		if ($arParams["USE_FOLLOW"] == "Y")
		{
			$dbEventsID = CSocNetLog::GetList(
				$arOrder,
				$arFilter,
				false,
				$arNavStartParams,
				array("ID", "DATE_FOLLOW"),
				$arListParams
			);

			if ($bFirstPage)
			{
				$arResult["NAV_STRING"] = "";
				$arResult["PAGE_ISDESC"] = $arParams["PAGER_DESC_NUMBERING"];
				$arResult["PAGE_NAVNUM"] = $GLOBALS["NavNum"]+1;
				$arResult["PAGE_NAVCOUNT"] = 1000000;
			}
			else
			{
				$arResult["NAV_STRING"] = $dbEventsID->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C73_NAV"), "", false);
				$arResult["PAGE_NUMBER"] = $dbEventsID->NavPageNomer;
				$arResult["PAGE_ISDESC"] = $dbEventsID->bDescPageNumbering;
				$arResult["PAGE_NAVNUM"] = $dbEventsID->NavNum;
				$arResult["PAGE_NAVCOUNT"] = $dbEventsID->NavPageCount;
			}

			$arEventsFollowID = array();
			while($arEvents = $dbEventsID->Fetch())
				$arEventsFollowID[] = $arEvents["ID"];

			if (count($arEventsFollowID) > 0)
				$dbEvents = CSocNetLog::GetList(
					$arOrder,
					array("ID" => $arEventsFollowID),
					false,
					false,
					array(),
					$arListParams				
				);
		}
		else
		{
			$dbEvents = CSocNetLog::GetList(
				$arOrder,
				$arFilter,
				false,
				$arNavStartParams,
				array(),
				$arListParams
			);

			if ($bFirstPage)
			{
				$arResult["NAV_STRING"] = "";
				$arResult["PAGE_ISDESC"] = $arParams["PAGER_DESC_NUMBERING"];
				$arResult["PAGE_NAVNUM"] = $GLOBALS["NavNum"]+1;
				$arResult["PAGE_NAVCOUNT"] = 1000000;
			}
			else
			{
				$arResult["NAV_STRING"] = $dbEvents->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C73_NAV"), "", false);
				$arResult["PAGE_NUMBER"] = $dbEvents->NavPageNomer;
				$arResult["PAGE_ISDESC"] = $dbEvents->bDescPageNumbering;
				$arResult["PAGE_NAVNUM"] = $dbEvents->NavNum;
				$arResult["PAGE_NAVCOUNT"] = $dbEvents->NavPageCount;
			}
		}

		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
		{
			// get current user subscriptions
			$arCurrentUserSubscribe = array(
				"TRANSPORT" => array(),
				"VISIBLE" => array()
			);
	
			if ($GLOBALS["USER"]->IsAuthorized())
			{
				$arFilter = array("USER_ID" => $GLOBALS["USER"]->GetID());
	
				$dbResultTmp = CSocNetLogEvents::GetList(
						array(),
						$arFilter
					);
	
				while($arSubscribesTmp = $dbResultTmp->Fetch())
				{
					if ($arSubscribesTmp["VISIBLE"] != "I")
						$arCurrentUserSubscribe["VISIBLE"][$arSubscribesTmp["ENTITY_TYPE"]."_".$arSubscribesTmp["ENTITY_ID"]."_".$arSubscribesTmp["EVENT_ID"]."_".$arSubscribesTmp["ENTITY_MY"]."_".$arSubscribesTmp["ENTITY_CB"]] = $arSubscribesTmp["VISIBLE"];
	
					if ($arSubscribesTmp["TRANSPORT"] != "I")
						$arCurrentUserSubscribe["TRANSPORT"][$arSubscribesTmp["ENTITY_TYPE"]."_".$arSubscribesTmp["ENTITY_ID"]."_".$arSubscribesTmp["EVENT_ID"]."_".$arSubscribesTmp["ENTITY_MY"]."_".$arSubscribesTmp["ENTITY_CB"]] = $arSubscribesTmp["TRANSPORT"];
				}
			}
		}
	}
	else
	{
		$arListParams = array(
			"CHECK_RIGHTS" => "Y",
			"USE_SUBSCRIBE" => "N"
		);

		if ($bCurrentUserIsAdmin)
			$arListParams["USER_ID"] = "A";

		$dbEvents = CSocNetLog::GetList(
			$arOrder,
			$arFilter,
			false,
			$arNavStartParams,
			array(),
			$arListParams
		);

		if ($arParams["LOG_ID"] <= 0)
		{
			if ($bFirstPage)
			{
				$arResult["NAV_STRING"] = "";
				$arResult["PAGE_ISDESC"] = $arParams["PAGER_DESC_NUMBERING"];
				$arResult["PAGE_NAVNUM"] = $GLOBALS["NavNum"]+1;
				$arResult["PAGE_NAVCOUNT"] = 1000000;
			}
			else
			{
				$arResult["NAV_STRING"] = $dbEvents->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C73_NAV"), "", false);
				$arResult["PAGE_NUMBER"] = $dbEvents->NavPageNomer;
				$arResult["PAGE_ISDESC"] = $dbEvents->bDescPageNumbering;
				$arResult["PAGE_NAVNUM"] = $dbEvents->NavNum;
				$arResult["PAGE_NAVCOUNT"] = $dbEvents->NavPageCount;
			}
		}
	}

	$cnt = 0;
	$arLogTmpID = array();

	if ($dbEvents)
	{
		while ($arEvents = $dbEvents->GetNext())
		{
			$cnt++;
			if ($cnt == 1)
			{
				if (
					($arParams["PAGER_DESC_NUMBERING"] && $dbEvents->NavPageNomer < $dbEvents->NavPageCount)
					|| (!$arParams["PAGER_DESC_NUMBERING"] && $dbEvents->NavPageNomer > 1)
				)
					$current_page_date = $arEvents["LOG_UPDATE"];
				else
				{
					$current_page_date = ConvertTimeStamp(time() + $arResult["TZ_OFFSET"], "FULL");
					$bNow = true;
				}
			}
			$arLogTmpID[] = ($arEvents["TMP_ID"] > 0 ? $arEvents["TMP_ID"] : $arEvents["ID"]);
			__SLGetLogRecord($arEvents, $arParams, $arCurrentUserSubscribe, $arMyEntities, $arTmpEventsOld, $arTmpEventsNew, $current_page_date);
		}
	}

	if (intval($arParams["LOG_CNT"]) > 0 || $bFirstPage)
		$last_date = $arTmpEventsNew[count($arTmpEventsNew)-1]["EVENT"][($arParams["USE_FOLLOW"] == "Y" ? "DATE_FOLLOW" : "LOG_UPDATE")];
	elseif (
		$arParams["LOG_ID"] <= 0
		&& $dbEvents
		&& $dbEvents->NavContinue() 
		&& $arEvents = $dbEvents->GetNext()
	)
	{
		$next_page_date = ($arParams["USE_FOLLOW"] == "Y" ? $arEvents["DATE_FOLLOW"] : $arEvents["LOG_UPDATE"]);
		if ($GLOBALS["USER"]->IsAuthorized())
		{
			if ($arResult["LAST_LOG_TS"] < MakeTimeStamp($next_page_date))
				$next_page_date = $arResult["LAST_LOG_TS"];
		}
	}

	// get comments
	$arFilter = array();
	if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		$arFilter["LOG_SITE_ID"] = SITE_ID;
	else
		$arFilter["LOG_SITE_ID"] = array(SITE_ID, false);

	if ($arResult["FILTER_COMMENTS"] == "Y" && $arParams["GROUP_ID"] > 0)
	{
		$ENTITY_TYPE = SONET_ENTITY_GROUP;
		$ENTITY_ID = $arParams["GROUP_ID"];

		$arFilter["LOG_RIGHTS"] = "SG".intval($arParams["GROUP_ID"]);
	}
	else
	{
		$ENTITY_TYPE = "";
		$ENTITY_ID = 0;
	}

	$bUseComments = true;
	if (
		(is_array($arParams["EVENT_ID"]) && !in_array("all", $arParams["EVENT_ID"]))
		|| ($arParams["EVENT_ID"] && !is_array($arParams["EVENT_ID"]) && $arParams["EVENT_ID"] != "all")
	)
	{
		$arFilter["EVENT_ID"] = array();

		if (!is_array($arParams["EVENT_ID"]))
			$arParams["EVENT_ID"] = array($arParams["EVENT_ID"]);

		$event_id_fullset_tmp = array();
		foreach($arParams["EVENT_ID"] as $event_id_tmp)
			$event_id_fullset_tmp = array_merge($event_id_fullset_tmp, CSocNetLogTools::FindFullSetByEventID($event_id_tmp));

		$event_id_fullset_tmp = array_unique($event_id_fullset_tmp);

		foreach($event_id_fullset_tmp as $event_id_tmp)
		{
			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($event_id_tmp);
			if ($arCommentEvent && is_array($arCommentEvent))
				$arFilter["EVENT_ID"][] = $arCommentEvent["EVENT_ID"];
		}
		$arFilter["EVENT_ID"] = array_unique($arFilter["EVENT_ID"]);

		if (count($arFilter["EVENT_ID"]) <= 0)
			$bUseComments = false;
	}

	if ($arParams["FAVORITES"] == "Y" && count($arLogTmpID) <= 0)
		$bUseComments = false;

	if(isset($arParams["EXACT_EVENT_ID"]))
		$bUseComments = false;

	if ($bUseComments)
	{
		if (
			$arParams["USE_FOLLOW"] == "Y"		
			|| ($arParams["USE_SUBSCRIBE"] == "Y" && $arParams["SHOW_HIDDEN"])
			|| $arResult["FILTER_COMMENTS"] != "Y"
		)
			$arFilter["LOG_ID"] = $arLogTmpID;		
		else
		{
			if ($bNow)
				$arFilter["<=LOG_DATE"] = "NOW";
			elseif ($current_page_date)
				$arFilter["<=LOG_DATE"] = $current_page_date;

			if ($next_page_date)
				$arFilter[">LOG_DATE"] = $next_page_date;
			elseif ($last_date)
				$arFilter[">=LOG_DATE"] = $last_date;
		}

		if (
			array_key_exists("LOG_DATE_FROM", $arParams)
			&& strlen(trim($arParams["LOG_DATE_FROM"])) > 0
			&& MakeTimeStamp($arParams["LOG_DATE_FROM"], CSite::GetDateFormat("SHORT")) < time()+$arResult["TZ_OFFSET"]
		)
			$arFilter[">=LOG_DATE"] = $arParams["LOG_DATE_FROM"];

		if (!array_key_exists("<=LOG_DATE", $arFilter))
		{
			if (
				array_key_exists("LOG_DATE_TO", $arParams)
				&& strlen(trim($arParams["LOG_DATE_TO"])) > 0
				&& MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT")) < time()+$arResult["TZ_OFFSET"]
			)
				$arFilter["<=LOG_DATE"] = ConvertTimeStamp(MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT"))+86399, "FULL");
			else
				$arFilter["<=LOG_DATE"] = "NOW";
		}

		if ($arResult["FILTER_COMMENTS"] == "Y" && IntVal($arParams["CREATED_BY_ID"]) > 0)
			$arFilter["USER_ID"] = $arParams["CREATED_BY_ID"];

		if ($arParams["LOG_ID"] <= 0)
		{
			if (!$arResult["AJAX_CALL"] || $arResult["bReload"])
				$_SESSION["SONET_LOG_ID"] = array();
			elseif (
				is_array($_SESSION["SONET_LOG_ID"])
				&& count($_SESSION["SONET_LOG_ID"]) > 0
			)
				$arFilter["!LOG_ID"] = $_SESSION["SONET_LOG_ID"];
		}

		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
			$arListParams = array(
				"USE_SUBSCRIBE" => "Y",
				"MY_ENTITIES" => $arMyEntities,
				"CHECK_RIGHTS" => "Y"
			);
		else
			$arListParams = array(
				"USE_SUBSCRIBE" => "N",
				"CHECK_RIGHTS" => "Y"
			);

		if ($bCurrentUserIsAdmin)
			$arListParams["USER_ID"] = "A";


		if (
			($arParams["USE_SUBSCRIBE"] == "Y" && $arParams["SHOW_HIDDEN"])
			|| $arParams["LOG_ID"] > 0
		)
			$arListParams["USE_SUBSCRIBE"] = "N";
		elseif ($arParams["USE_SUBSCRIBE"] == "Y")
			$arListParams["VISIBLE"] = "Y";

		$arSelect = array(
			"ID", "LOG_ID", "SOURCE_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID",
			"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
			"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
			"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
			"LOG_SITE_ID", "LOG_SOURCE_ID",
			"RATING_TYPE_ID", "RATING_ENTITY_ID", "RATING_TOTAL_VALUE", "RATING_TOTAL_VOTES", "RATING_TOTAL_POSITIVE_VOTES", "RATING_TOTAL_NEGATIVE_VOTES", "RATING_USER_VOTE_VALUE"
		);

		if ($arParams["USE_COMMENTS"] != "Y")
		{
			$arSelect[] = "LOG_TITLE";
			$arSelect[] = "LOG_URL";
			$arSelect[] = "LOG_PARAMS";
		}

		$dbComments = CSocNetLogComments::GetList(
			array("LOG_DATE" => "ASC"),
			$arFilter,
			false,
			false,
			$arSelect,
			$arListParams
		);

		$arTmpComments = array();

		$arLogTmpIDFromComments = array();
		while($arComments = $dbComments->GetNext())
		{
			$arLogTmpIDFromComments[] = $arComments["LOG_ID"];
			__SLGetLogCommentRecord($arComments, $arParams, $arCurrentUserSubscribe, $arMyEntities, $arTmpComments);
		}

		if (
			$arParams["USE_SUBSCRIBE"] == "Y"
			|| (
				$arResult["FILTER_COMMENTS"] == "Y"
				&& array_key_exists("log_filter_submit", $_REQUEST) 
				&& array_key_exists("flt_comments", $_REQUEST) 
				&& $_REQUEST["flt_comments"] == "Y"
			)
		)
		{
			$arLogIDHidden = array_diff(array_unique($arLogTmpIDFromComments), $arLogTmpID);
	
			if (
				$arParams["LOG_ID"] <= 0
				&& count($arLogIDHidden) > 0
			)
			{
				$arFilter = array(
					"ID" => $arLogIDHidden
				);
	
				$arListParams = array(
					"CHECK_RIGHTS"	=> "Y",
					"USE_SUBSCRIBE" => "N",
				);
	
				if ($bCurrentUserIsAdmin)
					$arListParams["USER_ID"] = "A";
	
				$dbEvents = CSocNetLog::GetList(
					$arOrder,
					$arFilter,
					false,
					false,
					array(),
					$arListParams
				);

				while ($arEvents = $dbEvents->GetNext())
					__SLGetLogRecord($arEvents, $arParams, $arCurrentUserSubscribe, $arMyEntities, $arTmpEventsOld, $arTmpEventsNew, $tmp_date);
			}
		}

		foreach ($arTmpComments as $arComment)
		{
			if ($arParams["USE_COMMENTS"] == "Y")
			{
				$bFound = false;
				foreach($arTmpEventsNew as $key => $arTmpEvent)
				{
					if ($arTmpEvent["EVENT"]["TMP_ID"] == $arComment["EVENT"]["LOG_ID"])
					{
						$arTmpEventsNew[$key]["COMMENTS"][] = $arComment;
						$bFound = true;
						break;
					}
				}
			}
			else
				$arTmpEventsNew[] = $arComment;

			if ($arParams["NEW_TEMPLATE"] != "Y")
				$arTmpEventsOld[] = $arComment;
		}
	}

	if (
		$arParams["USE_SUBSCRIBE"] == "Y"
		|| (
			$arResult["FILTER_COMMENTS"] == "Y"
			&& array_key_exists("log_filter_submit", $_REQUEST) 
			&& array_key_exists("flt_comments", $_REQUEST) 
			&& $_REQUEST["flt_comments"] == "Y"
		)
	)
	{
		if ($arParams["USE_COMMENTS"] == "Y")
		{
			if ($arParams["NEW_TEMPLATE"] != "Y")
				usort($arTmpEventsOld, "__SLLogUpDateTSSort");
			usort($arTmpEventsNew, "__SLLogUpDateTSSort");
		}
		else
		{
			if ($arParams["NEW_TEMPLATE"] != "Y")
				usort($arTmpEventsOld, "__SLLogDateTSSort");
			usort($arTmpEventsNew, "__SLLogDateTSSort");
		}
	}

	if ($arParams["NEW_TEMPLATE"] != "Y")
	{
		foreach ($arTmpEventsOld as $arTmpEvent)
		{
			$arDateTmp = ParseDateTime((array_key_exists("LOG_DATE_FORMAT", $arTmpEvent) && strlen($arTmpEvent["LOG_DATE_FORMAT"]) > 0 ? $arTmpEvent["LOG_DATE_FORMAT"] : $arTmpEvent["LOG_DATE"]), CSite::GetDateFormat("FULL"));
			$day = IntVal($arDateTmp["DD"]);
			$month = IntVal($arDateTmp["MM"]);
			$year = IntVal($arDateTmp["YYYY"]);
			$dateFormated = $day.' '.ToLower(($month > 0 && $month < 13) ? GetMessage('MONTH_'.$month.'_S') : "").' '.$year;

			$arResult["Events"][$dateFormated][] = $arTmpEvent;
		}
	}

	foreach ($arTmpEventsNew as $arTmpEvent)
	{
		if (
			!is_array($_SESSION["SONET_LOG_ID"])
			|| !in_array($arTmpEvent["EVENT"]["ID"], $_SESSION["SONET_LOG_ID"])
		)
			$_SESSION["SONET_LOG_ID"][] = $arTmpEvent["EVENT"]["ID"];

		if ($arParams["NEW_TEMPLATE"] != "Y")
		{
			$arDateTmp = ParseDateTime(($arParams["USE_COMMENTS"] == "Y" ? $arTmpEvent["EVENT"]["LOG_UPDATE"] : (array_key_exists("EVENT", $arTmpEvent) ? $arTmpEvent["EVENT"]["LOG_DATE"] : $arTmpEvent["LOG_DATE"])), CSite::GetDateFormat("FULL"));
			$day = IntVal($arDateTmp["DD"]);
			$month = IntVal($arDateTmp["MM"]);
			$year = IntVal($arDateTmp["YYYY"]);
			$dateFormated = $day.' '.ToLower(($month > 0 && $month < 13) ? GetMessage('MONTH_'.$month.'_S') : "").' '.$year;
		}
		else
			$dateFormated = MakeTimeStamp(($arParams["USE_COMMENTS"] == "Y" ? $arTmpEvent["EVENT"]["LOG_UPDATE"] : (array_key_exists("EVENT", $arTmpEvent) ? $arTmpEvent["EVENT"]["LOG_DATE"] : $arTmpEvent["LOG_DATE"])));

		if (
			$arParams["LOG_ID"] <= 0
			&& $arParams["USE_COMMENTS"] == "Y"
			&& array_key_exists("COMMENTS", $arTmpEvent)
			&& is_array($arTmpEvent["COMMENTS"])
			&& count($arTmpEvent["COMMENTS"]) > $arParams["COMMENTS_IN_EVENT"]
		)
		{
			if (
				((MakeTimeStamp($arTmpEvent["COMMENTS"][count($arTmpEvent["COMMENTS"])-1]["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"])
			)
			{
				foreach($arTmpEvent["COMMENTS"] as $j => $arComment)
					if ((MakeTimeStamp($arComment["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"])
					{
						if ((count($arTmpEvent["COMMENTS"]) - $j) <= $arParams["COMMENTS_IN_EVENT"])
							$arTmpEvent["COMMENTS"] = array_slice($arTmpEvent["COMMENTS"], -($arParams["COMMENTS_IN_EVENT"]), $arParams["COMMENTS_IN_EVENT"]);
						else
							$arTmpEvent["COMMENTS"] = array_slice($arTmpEvent["COMMENTS"], $j);
						break;
					}
			}
			else
				$arTmpEvent["COMMENTS"] = array_slice($arTmpEvent["COMMENTS"], -($arParams["COMMENTS_IN_EVENT"]), $arParams["COMMENTS_IN_EVENT"]);
		}

		$arResult["EventsNew"][$dateFormated][] = $arTmpEvent;
	}

	if ($arTmpEvent["EVENT"]["DATE_FOLLOW"])
		$dateLastPage = ConvertTimeStamp(MakeTimeStamp($arTmpEvent["EVENT"]["DATE_FOLLOW"], CSite::GetDateFormat("FULL")), "FULL");

	$arResult["WORKGROUPS_PAGE"] = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);

	if (
		$arParams["LOG_ID"] <= 0
		&& $GLOBALS["USER"]->IsAuthorized()
	)
		$arResult["LOG_COUNTER"] = CUserCounter::GetValue($GLOBALS["USER"]->GetID(), "**", SITE_ID);

	if (
		$GLOBALS["USER"]->IsAuthorized()
		&& $arParams["SET_LOG_COUNTER"] == "Y"
		&& $arParams["SET_LOG_PAGE_CACHE"] == "Y"
		&& !IsModuleInstalled("bitrix24")
	)
	{
		$max_id = CSocNetLogTools::GetCacheLastLogID("log");
		CSocNetLogTools::SetUserCache("log", $GLOBALS["USER"]->GetID(), $max_id, $max_id, 0);
		$max_id = CSocNetLogTools::GetCacheLastLogID("comment");
		CSocNetLogTools::SetUserCache("comment", $GLOBALS["USER"]->GetID(), $max_id, $max_id, 0, true);
	}

	if (
		$GLOBALS["USER"]->IsAuthorized()
		&& $arParams["SET_LOG_COUNTER"] == "Y"
	)
	{
		CUserCounter::ClearByUser(
			$GLOBALS["USER"]->GetID(), 
			SITE_ID, 
			$arResult["COUNTER_TYPE"]
		);

		CUserCounter::ClearByUser(
			$GLOBALS["USER"]->GetID(), 
			"**", 
			$arResult["COUNTER_TYPE"]
		);
	}

	if (
		$GLOBALS["USER"]->IsAuthorized()
		&& $arParams["SET_LOG_PAGE_CACHE"] == "Y"
		&& $dateLastPage
	)
	{
		CSocNetLogPages::Set(
			$GLOBALS["USER"]->GetID(), 
			$dateLastPage,
			$arParams["PAGE_SIZE"],
			$arResult["PAGE_NUMBER"],
			SITE_ID
		);
	}
}
else
	$arResult["NEED_AUTH"] = "Y";

$this->IncludeComponentTemplate();
?>