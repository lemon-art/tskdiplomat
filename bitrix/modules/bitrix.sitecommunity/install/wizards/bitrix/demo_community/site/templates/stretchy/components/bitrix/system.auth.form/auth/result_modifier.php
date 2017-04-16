<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["urlToOwnBlog"] = "";
$arResult["urlToOwnProfile"] = "";

if (CModule::IncludeModule("blog") && $GLOBALS["USER"]->IsAuthorized())
{
	$arResult["urlToCreateMessageInBlog"] = CComponentEngine::MakePathFromTemplate(
		$arParams["PATH_TO_BLOG_NEW_POST"],
		array("user_id" => $GLOBALS["USER"]->GetID(), "post_id" => "new"));
}

if ($GLOBALS["USER"]->IsAuthorized())
{
	$arResult["urlToOwnProfile"] = CComponentEngine::MakePathFromTemplate($arParams["PROFILE_URL"], array("user_id" => $GLOBALS["USER"]->GetID()));

	if (CModule::IncludeModule("socialnetwork"))
	{
		// live updates counter
		$lastLogID = CSocNetLogTools::GetCacheLastLogID("log");
		$arUserLogCache = CSocNetLogTools::GetUserCache("log", $GLOBALS["USER"]->GetID());

		$lastLogCommentID = CSocNetLogTools::GetCacheLastLogID("comment");
		$arUserLogCommentCache = CSocNetLogTools::GetUserCache("comment", $GLOBALS["USER"]->GetID());

		if ($arUserLogCache["MaxID"] >= $lastLogID)
			$arResult["LOG_ITEMS_TOTAL"] = $arUserLogCache["Count"];
		else
		{
			$arEventID = CUserOptions::GetOption("socialnetwork", "~log__0");
				
			$arFilter = array(
				"LOG_DATE_DAYS" => 3,
				"<=LOG_DATE" => ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL"),
				"SITE_ID" => array(SITE_ID, false)
			);

			if (intval($arUserLogCache["MaxViewedID"]) > 0)
				$arFilter[">ID"] = $arUserLogCache["MaxViewedID"];

			if (is_array($arEventID))
			{
				if (!in_array("all", $arEventID))
				{
					$event_id_fullset_tmp = array();
					foreach($arEventID as $event_id_tmp)
						$event_id_fullset_tmp = array_merge($event_id_fullset_tmp, CSocNetLogTools::FindFullSetByEventID($event_id_tmp));
					$arFilter["EVENT_ID"] = array_unique($event_id_fullset_tmp);
				}
			}
			elseif ($arEventID && $arEventID != "all")
				$arFilter["EVENT_ID"] = CSocNetLogTools::FindFullSetByEventID($arEventID);

			if (
				!$arFilter["EVENT_ID"] 
				|| (is_array($arFilter["EVENT_ID"]) && count($arFilter["EVENT_ID"]) <= 0)
			)
				unset($arFilter["EVENT_ID"]);

			if ($GLOBALS["USER"]->IsAuthorized())
				$arFilter["!USER_ID"] = $GLOBALS["USER"]->GetID();

			$arListParams = array(
				"CHECK_RIGHTS" => "Y",
				"USE_SUBSCRIBE" => "Y",
				"MIN_ID_JOIN" => true,
				"VISIBLE" => "Y"
			);

			if ($bCurrentUserIsAdmin)
				$arListParams["USER_ID"] = "A";
									
			$arResult["LOG_ITEMS_TOTAL"] = intval(CSocNetLog::GetList(
				array("LOG_DATE"=>"DESC"), 
				$arFilter, 
				array(),
				false, 
				array("COUNT" => "ID"),
				$arListParams
			));
						
			CSocNetLogTools::SetUserCache("log", $GLOBALS["USER"]->GetID(), $lastLogID, $arUserLogCache["MaxViewedID"], $arResult["LOG_ITEMS_TOTAL"]);
		}

		if ($arUserLogCommentCache["MaxID"] >= $lastLogCommentID)
			$arResult["LOG_COMMENT_ITEMS_TOTAL"] = $arUserLogCommentCache["Count"];
		else
		{
			$arEventID = CUserOptions::GetOption("socialnetwork", "~logcomment__0");

			$arFilter = array(
				"LOG_DATE_DAYS" => 3,
				"<=LOG_DATE" => ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL"),
				"SITE_ID" => array(SITE_ID, false)
			);

			if (intval($arUserLogCommentCache["MaxViewedID"]) > 0)
				$arFilter[">ID"] = $arUserLogCommentCache["MaxViewedID"];

			if (is_array($arEventID))
			{
				if (!in_array("all", $arEventID))
				{
					$event_id_fullset_tmp = array();
					foreach($arEventID as $event_id_tmp)
						$event_id_fullset_tmp = array_merge($event_id_fullset_tmp, CSocNetLogTools::FindFullSetByEventID($event_id_tmp));
					$arFilter["EVENT_ID"] = array_unique($event_id_fullset_tmp);
				}
			}
			elseif ($arEventID && $arEventID != "all")
				$arFilter["EVENT_ID"] = CSocNetLogTools::FindFullSetByEventID($arEventID);

			if (
				!$arFilter["EVENT_ID"] 
				|| (is_array($arFilter["EVENT_ID"]) && count($arFilter["EVENT_ID"]) <= 0)
			)
				unset($arFilter["EVENT_ID"]);

			if ($GLOBALS["USER"]->IsAuthorized())
				$arFilter["!USER_ID"] = $GLOBALS["USER"]->GetID();

			$arListParams = array(
				"CHECK_RIGHTS" => "Y",
				"USE_SUBSCRIBE" => "Y",
				"VISIBLE" => "Y"
			);

			if ($bCurrentUserIsAdmin)
				$arListParams["USER_ID"] = "A";

			$arResult["LOG_COMMENT_ITEMS_TOTAL"] = intval(CSocNetLogComments::GetList(
				array("LOG_DATE"=>"DESC"), 
				$arFilter, 
				array(),
				false, 
				array("COUNT" => "ID"),
				$arListParams
			));

			CSocNetLogTools::SetUserCache("comment", $GLOBALS["USER"]->GetID(), $lastLogCommentID, $arUserLogCommentCache["MaxViewedID"], $arResult["LOG_COMMENT_ITEMS_TOTAL"], false, $arUserLogCommentCache["LastViewTS"]);
		}
	}
}
?>