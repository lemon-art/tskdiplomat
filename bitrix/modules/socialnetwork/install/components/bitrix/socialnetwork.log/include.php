<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$GLOBALS["CurUserCanAddComments"] = array();

if (!function_exists('__SLTransportSort'))
{
	function __SLTransportSort($a, $b)
	{
		$arPattern = array("M", "X", "D", "E");
		$a_key = array_search($a, $arPattern);
		$b_key = array_search($b, $arPattern);

		if ($a_key == $b_key)
			return 0;

		return ($a_key < $b_key) ? -1 : 1;
	}
}

if (!function_exists('__SLLogUpDateTSSort'))
{
	function __SLLogUpDateTSSort($a, $b)
	{
		if ($a["LOG_UPDATE_TS"] == $b["LOG_UPDATE_TS"])
		{
			if (array_key_exists("EVENT", $a))
				return ($a["EVENT"]["ID"] > $b["EVENT"]["ID"]) ? -1 : 1;
			else
				return 0;
		}

		return ($a["LOG_UPDATE_TS"] > $b["LOG_UPDATE_TS"]) ? -1 : 1;
	}
}

if (!function_exists('__SLLogDateTSSort'))
{
	function __SLLogDateTSSort($a, $b)
	{
		if ($a["LOG_DATE_TS"] == $b["LOG_DATE_TS"])
			if (array_key_exists("EVENT", $a))
				return ($a["EVENT"]["ID"] > $b["EVENT"]["ID"]) ? -1 : 1;
			else
				return 0;

		return ($a["LOG_DATE_TS"] > $b["LOG_DATE_TS"]) ? -1 : 1;
	}
}

if (!function_exists('__SLGetVisible'))
{
	function __SLGetVisible($arFields, $arCurrentUserSubscribe, $arMyEntities = array())
	{
		$bHasLogEventCreatedBy = CSocNetLogTools::HasLogEventCreatedBy($arFields["EVENT_ID"]);

		if (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N"];
		elseif ($bHasLogEventCreatedBy && array_key_exists("U_".$arFields["USER_ID"]."_".$arFields["EVENT_ID"]."_N_Y", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"]["U_".$arFields["USER_ID"]."_".$arFields["EVENT_ID"]."_N_Y"];
		elseif (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N"];
		elseif ($bHasLogEventCreatedBy && array_key_exists("U_".$arFields["USER_ID"]."_all_N_Y", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"]["U_".$arFields["USER_ID"]."_all_N_Y"];
		elseif
		(
			array_key_exists($arFields["ENTITY_TYPE"], $arMyEntities)
			&& in_array($arFields["ENTITY_ID"], $arMyEntities[$arFields["ENTITY_TYPE"]])
			&& array_key_exists($arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_Y_N", $arCurrentUserSubscribe["VISIBLE"])
		)
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_Y_N"];
		elseif
		(
			array_key_exists($arFields["ENTITY_TYPE"], $arMyEntities)
			&& in_array($arFields["ENTITY_ID"], $arMyEntities[$arFields["ENTITY_TYPE"]])
			&& array_key_exists($arFields["ENTITY_TYPE"]."_0_all_Y_N", $arCurrentUserSubscribe["VISIBLE"])
		)
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_0_all_Y_N"];
		elseif (array_key_exists($arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N"];
		elseif (array_key_exists($arFields["ENTITY_TYPE"]."_0_all_N_N", $arCurrentUserSubscribe["VISIBLE"]))
			$strVisible = $arCurrentUserSubscribe["VISIBLE"][$arFields["ENTITY_TYPE"]."_0_all_N_N"];
		else
			$strVisible = "Y";

		return $strVisible;
	}
}

if (!function_exists('__SLGetTransport'))
{
	function __SLGetTransport($arFields, $arCurrentUserSubscribe, $arMyEntities = array())
	{
		if (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N"];

		if (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N"];

		$bHasLogEventCreatedBy = CSocNetLogTools::HasLogEventCreatedBy($arFields["EVENT_ID"]);
		if ($bHasLogEventCreatedBy)
		{
			if ($arFields["EVENT_ID"])
			{
				if (array_key_exists("U_".$arFields["USER_ID"]."_all_N_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_N_Y"];
				elseif (array_key_exists("U_".$arFields["USER_ID"]."_all_Y_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_Y_Y"];
			}
		}

		if (
			!array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			&& !array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
		{
			if
			(
				array_key_exists($arFields["ENTITY_TYPE"], $arMyEntities)
				&& in_array($arFields["ENTITY_ID"], $arMyEntities[$arFields["ENTITY_TYPE"]])
				&& array_key_exists($arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_Y_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_Y_N"];
			elseif
			(
				array_key_exists($arFields["ENTITY_TYPE"], $arMyEntities)
				&& in_array($arFields["ENTITY_ID"], $arMyEntities[$arFields["ENTITY_TYPE"]])
				&& array_key_exists($arFields["ENTITY_TYPE"]."_0_all_Y_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_all_Y_N"];
			elseif (array_key_exists($arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N"];
			elseif (array_key_exists($arFields["ENTITY_TYPE"]."_0_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_all_N_N"];
			else
				$arTransport[] = "N";
		}

		$arTransport = array_unique($arTransport);
		usort($arTransport, "__SLTransportSort");

		return $arTransport;
	}
}

if (!function_exists('__SLGetLogRecord'))
{
	function __SLGetLogRecord($arEvents, $arParams, $arCurrentUserSubscribe, $arMyEntities, &$arTmpEventsOld, &$arTmpEventsNew, &$current_page_date)
	{
		$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

		if ($arTmpEvents == false)
			$arTmpEvents = array();

		$cache_time = 31536000;

		if (
			!$bCurrentUserIsAdmin
			&& $arParams["NEW_TEMPLATE"] == "Y"
		)
		{
			$cache = new CPHPCache;

			$arCacheID = array();
			$arKeys = array(
				"AVATAR_SIZE",
				"DESTINATION_LIMIT",
				"CHECK_PERMISSIONS_DEST",
				"NAME_TEMPLATE",
				"NAME_TEMPLATE_WO_NOBR",
				"SHOW_LOGIN",
				"DATE_TIME_FORMAT",
				"PATH_TO_USER",
				"PATH_TO_GROUP",
				"PATH_TO_CONPANY_DEPARTMENT"
			);
			foreach($arKeys as $param_key)
			{
				if (array_key_exists($param_key, $arParams))
					$arCacheID[$param_key] = $arParams[$param_key];
				else
					$arCacheID[$param_key] = false;
			}
			$cache_id = "log_post_".$arEvents["ID"]."_".md5(serialize($arCacheID))."_".SITE_TEMPLATE_ID."_".SITE_ID."_".LANGUAGE_ID."_".CTimeZone::GetOffset();
			$cache_path = "/sonet_log/";
		}

		if (
			is_object($cache)
			&& $cache->InitCache($cache_time, $cache_id, $cache_path)
		)
		{
			$arCacheVars = $cache->GetVars();
			$arEvents["FIELDS_FORMATTED"] = $arCacheVars["FIELDS_FORMATTED"];

			if ($arParams["USE_FOLLOW"] == "Y")
			{
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["FOLLOW"] = $arEvents["FOLLOW"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["~FOLLOW"] = $arEvents["~FOLLOW"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW_X1"] = $arEvents["DATE_FOLLOW_X1"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["~DATE_FOLLOW_X1"] = $arEvents["~DATE_FOLLOW_X1"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW"] = $arEvents["DATE_FOLLOW"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["~DATE_FOLLOW"] = $arEvents["~DATE_FOLLOW"];
			}

			if ($arParams["SHOW_RATING"] == "Y")
			{
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["RATING_USER_VOTE_VALUE"] = $arEvents["RATING_USER_VOTE_VALUE"];
				$arEvents["FIELDS_FORMATTED"]["EVENT"]["~RATING_USER_VOTE_VALUE"] = $arEvents["~RATING_USER_VOTE_VALUE"];
			}

			if (array_key_exists("CACHED_CSS_PATH", $arEvents["FIELDS_FORMATTED"]))
			{
				if (
					!is_array($arEvents["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]) 
					&& strlen($arEvents["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]) > 0
				)
					$GLOBALS['APPLICATION']->SetAdditionalCSS($arEvents["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]);
				elseif(is_array($arEvents["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]))
					foreach($arEvents["FIELDS_FORMATTED"]["CACHED_CSS_PATH"] as $css_path)
						$GLOBALS['APPLICATION']->SetAdditionalCSS($css_path);
			}
		}
		else
		{
			if (is_object($cache))
			{
				$cache->StartDataCache($cache_time, $cache_id, $cache_path);
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
					$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_CARD_".intval($arEvents["USER_ID"] / 100));
					$GLOBALS["CACHE_MANAGER"]->RegisterTag("SONET_LOG_".intval($arEvents["ID"]));

					if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group_".$arEvents["ENTITY_ID"]);
				}
			}
			$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

			//unset blog post fields as they are processed later by socialnetwork.blog.post
			if (
				$arEvents["EVENT_ID"] == "blog_post" 
				&& $arParams["NEW_TEMPLATE"] == "Y"
			)
			{
				if (array_key_exists("TEXT_MESSAGE", $arEvents))
					unset($arEvents["TEXT_MESSAGE"]);
				if (array_key_exists("~TEXT_MESSAGE", $arEvents))
					unset($arEvents["~TEXT_MESSAGE"]);
				if (array_key_exists("MESSAGE", $arEvents))
					unset($arEvents["MESSAGE"]);
				if (array_key_exists("~MESSAGE", $arEvents))
					unset($arEvents["~MESSAGE"]);
				if (array_key_exists("TITLE", $arEvents))
					unset($arEvents["TITLE"]);
				if (array_key_exists("~TITLE", $arEvents))
					unset($arEvents["~TITLE"]);
			}

			$arEvents["EVENT_ID_FULLSET"] = CSocNetLogTools::FindFullSetEventIDByEventID($arEvents["EVENT_ID"]);

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			{
				static $arSiteWorkgroupsPage;

				if (
					!$arSiteWorkgroupsPage
					&& IsModuleInstalled("extranet")
				)
				{
					$rsSite = CSite::GetList($by="sort", $order="desc", Array("ACTIVE" => "Y"));
					while($arSite = $rsSite->Fetch())
						$arSiteWorkgroupsPage[$arSite["ID"]] = COption::GetOptionString("socialnetwork", "workgroup_page", $arSite["DIR"]."workgroups/", $arSite["ID"]);
				}

				if (
					is_set($arEvents["URL"]) 
					&& is_array($arSiteWorkgroupsPage) 
					&& array_key_exists(SITE_ID, $arSiteWorkgroupsPage)
				)
					$arEvents["URL"] = str_replace("#GROUPS_PATH#", $arSiteWorkgroupsPage[SITE_ID], $arEvents["URL"]);
			}

			$arEventTmp = CSocNetLogTools::FindLogEventByID($arEvents["EVENT_ID"]);
			if (
				$arEventTmp
				&& is_array($arEventTmp) 
				&& array_key_exists("CLASS_FORMAT", $arEventTmp)
				&& array_key_exists("METHOD_FORMAT", $arEventTmp)
			)
			{
				$arEvents["FIELDS_FORMATTED"] = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arEvents, $arParams);
				if (
					$arParams["NEW_TEMPLATE"] == "Y"
					&& is_array($arEvents["FIELDS_FORMATTED"])
					&& array_key_exists("EVENT", $arEvents["FIELDS_FORMATTED"])
					&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT"])
				)
				{
					if (array_key_exists("~MESSAGE", $arEvents["FIELDS_FORMATTED"]["EVENT"]))
						unset($arEvents["FIELDS_FORMATTED"]["EVENT"]["~MESSAGE"]);
					if (array_key_exists("TEXT_MESSAGE", $arEvents["FIELDS_FORMATTED"]["EVENT"]))
						unset($arEvents["FIELDS_FORMATTED"]["EVENT"]["TEXT_MESSAGE"]);
					if (array_key_exists("~TEXT_MESSAGE", $arEvents["FIELDS_FORMATTED"]["EVENT"]))
						unset($arEvents["FIELDS_FORMATTED"]["EVENT"]["~TEXT_MESSAGE"]);
				}

				if (
					$arParams["NEW_TEMPLATE"] == "Y"
					&& is_array($arEvents["FIELDS_FORMATTED"])
					&& array_key_exists("EVENT_FORMATTED", $arEvents["FIELDS_FORMATTED"])
					&& is_array( $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					&& array_key_exists("SHORT_MESSAGE", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				)
					unset($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["SHORT_MESSAGE"]);
			}

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arEvents["ENTITY_ID"]));
			else
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arEvents["ENTITY_ID"]));

			$dateFormated = FormatDate(
				$GLOBALS['DB']->DateFormatToPHP(FORMAT_DATE),
				MakeTimeStamp
				(
				is_array($arEvents["FIELDS_FORMATTED"])
				&& array_key_exists("EVENT_FORMATTED", $arEvents["FIELDS_FORMATTED"])
				&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& array_key_exists("LOG_DATE_FORMAT", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					? $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
					: (
						array_key_exists("LOG_DATE_FORMAT", $arEvents)
						? $arEvents["LOG_DATE_FORMAT"]
						: $arEvents["LOG_DATE"]
					)
				)
			);

			$timeFormated = FormatDateFromDB(
				(
					is_array($arEvents["FIELDS_FORMATTED"])
					&& array_key_exists("EVENT_FORMATTED", $arEvents["FIELDS_FORMATTED"])
					&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					&& array_key_exists("LOG_DATE_FORMAT", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
						? $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
						: (
							array_key_exists("LOG_DATE_FORMAT", $arEvents) 
								? $arEvents["LOG_DATE_FORMAT"] 
								: $arEvents["LOG_DATE"]
						)
				), 
				(stripos($arParams["DATE_TIME_FORMAT"], 'a') || ($arParams["DATE_TIME_FORMAT"] == 'FULL' && IsAmPmMode()) !== false ? 'H:MI T' : 'HH:MI')
			);
			$dateTimeFormated = FormatDate(
				(!empty($arParams['DATE_TIME_FORMAT']) ? ($arParams['DATE_TIME_FORMAT'] == 'FULL' ? $GLOBALS['DB']->DateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME)) : $arParams['DATE_TIME_FORMAT']) : $GLOBALS['DB']->DateFormatToPHP(FORMAT_DATETIME)),
				MakeTimeStamp
				(
				is_array($arEvents["FIELDS_FORMATTED"])
				&& array_key_exists("EVENT_FORMATTED", $arEvents["FIELDS_FORMATTED"])
				&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& array_key_exists("LOG_DATE_FORMAT", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					? $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
					: (
						array_key_exists("LOG_DATE_FORMAT", $arEvents)
						? $arEvents["LOG_DATE_FORMAT"]
						: $arEvents["LOG_DATE"]
					)
				)
			);
			if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
			{
				$dateTimeFormated = ToLower($dateTimeFormated);
				$dateFormated = ToLower($dateFormated);
			}
			// strip current year
			if (!empty($arParams['DATE_TIME_FORMAT']) && ($arParams['DATE_TIME_FORMAT'] == 'j F Y G:i' || $arParams['DATE_TIME_FORMAT'] == 'j F Y g:i a'))
			{
				$dateTimeFormated = ltrim($dateTimeFormated, '0');
				$curYear = date('Y');
				$dateTimeFormated = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $dateTimeFormated);
			}

			$arEvents["MESSAGE_FORMAT"] = htmlspecialcharsback($arEvents["MESSAGE"]);
			if (StrLen($arEvents["CALLBACK_FUNC"]) > 0)
			{
				if (StrLen($arEvents["MODULE_ID"]) > 0)
					CModule::IncludeModule($arEvents["MODULE_ID"]);

				$arEvents["MESSAGE_FORMAT"] = call_user_func($arEvents["CALLBACK_FUNC"], $arEvents);
			}

			$arTmpUser = array(
				"NAME" => $arEvents["~USER_NAME"],
				"LAST_NAME" => $arEvents["~USER_LAST_NAME"],
				"SECOND_NAME" => $arEvents["~USER_SECOND_NAME"],
				"LOGIN" => $arEvents["~USER_LOGIN"]
			);

			$arTmpEvent = array(
				"ID" => $arEvents["ID"],
				"ENTITY_TYPE" => $arEvents["ENTITY_TYPE"],
				"ENTITY_ID" => $arEvents["ENTITY_ID"],
				"EVENT_ID" => $arEvents["EVENT_ID"],
				"LOG_DATE" => $arEvents["LOG_DATE"],
				"LOG_DATE_FORMAT" => $arEvents["LOG_DATE_FORMAT"],
				"LOG_TIME_FORMAT" => $timeFormated,
				"TITLE_TEMPLATE" => $arEvents["TITLE_TEMPLATE"],
				"TITLE" => $arEvents["TITLE"],
				"TITLE_FORMAT" => CSocNetLog::MakeTitle($arEvents["TITLE_TEMPLATE"], $arEvents["TITLE"], $arEvents["URL"], true),
				"MESSAGE" => $arEvents["MESSAGE"],
				"MESSAGE_FORMAT" => $arEvents["MESSAGE_FORMAT"],
				"URL" => $arEvents["URL"],
				"MODULE_ID" => $arEvents["MODULE_ID"],
				"CALLBACK_FUNC" => $arEvents["CALLBACK_FUNC"],
				"ENTITY_NAME" => (($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP) ? $arEvents["GROUP_NAME"] : CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin)),
				"ENTITY_PATH" => $path2Entity,
			);

			if (intval($arEvents["USER_ID"]) > 0)
			{
				$arTmpEvent["TITLE_FORMAT_EXT"] = $arTmpEvent["TITLE_FORMAT"];
				$arTmpEvent["CREATED_BY_NAME"] = $arEvents["~CREATED_BY_NAME"];
				$arTmpEvent["CREATED_BY_LAST_NAME"] = $arEvents["~CREATED_BY_LAST_NAME"];
				$arTmpEvent["CREATED_BY_SECOND_NAME"] = $arEvents["~CREATED_BY_SECOND_NAME"];
				$arTmpEvent["CREATED_BY_LOGIN"] = $arEvents["~CREATED_BY_LOGIN"];
				$arTmpEvent["USER_ID"] = $arEvents["USER_ID"];
			}
			else
				$arTmpEvent["TITLE_FORMAT_EXT"] = "";

			if (preg_match("/#USER_NAME#/i".BX_UTF_PCRE_MODIFIER, $arTmpEvent["TITLE_FORMAT"], $res))
			{
				if (intval($arEvents["USER_ID"]) > 0)
				{
					$arTmpCreatedBy = array(
						"NAME" => $arEvents["~CREATED_BY_NAME"],
						"LAST_NAME" => $arEvents["~CREATED_BY_LAST_NAME"],
						"SECOND_NAME" => $arEvents["~CREATED_BY_SECOND_NAME"],
						"LOGIN" => $arEvents["~CREATED_BY_LOGIN"]
					);

					$name_formatted = CUser::FormatName(
						$arParams["NAME_TEMPLATE_WO_NOBR"],
						$arTmpCreatedBy,
						$bUseLogin
					);
				}
				else
					$name_formatted = GetMessage("SONET_C73_CREATED_BY_ANONYMOUS");

				$arTmpEvent["TITLE_FORMAT"] = str_replace("#USER_NAME#", $name_formatted, $arTmpEvent["TITLE_FORMAT"]);
			}

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_USER)
			{
				$arTmpEvent["USER_NAME"] = $arTmpUser["NAME"];
				$arTmpEvent["USER_LAST_NAME"] = $arTmpUser["LAST_NAME"];
				$arTmpEvent["USER_SECOND_NAME"] = $arTmpUser["SECOND_NAME"];
				$arTmpEvent["USER_LOGIN"] = $arTmpUser["LOGIN"];
			}

			if (
				strlen($arTmpEvent["TITLE_FORMAT"]) <= 0
				&& strlen($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]) > 0
			)
				$arTmpEvent["TITLE_FORMAT"] = $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"];

			$arEvents["FIELDS_FORMATTED"]["LOG_TIME_FORMAT"] = $timeFormated;
			$arEvents["FIELDS_FORMATTED"]["LOG_UPDATE_TS"] = MakeTimeStamp($arEvents["LOG_UPDATE"]);

			$arEvents["FIELDS_FORMATTED"]["LOG_DATE_TS"] = MakeTimeStamp($arEvents["LOG_DATE"]);
			$arEvents["FIELDS_FORMATTED"]["LOG_DATE_DAY"] = ConvertTimeStamp(MakeTimeStamp($arEvents["LOG_DATE"]), "SHORT");
			$arEvents["FIELDS_FORMATTED"]["LOG_UPDATE_DAY"] = ConvertTimeStamp(MakeTimeStamp($arEvents["LOG_UPDATE"]), "SHORT");
			$arEvents["FIELDS_FORMATTED"]["COMMENTS_COUNT"] = $arEvents["COMMENTS_COUNT"];
			$arEvents["FIELDS_FORMATTED"]["TMP_ID"] = $arEvents["TMP_ID"];

			if (
				array_key_exists("EVENT_FORMATTED", $arEvents["FIELDS_FORMATTED"])
				&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& array_key_exists("LOG_DATE_FORMAT", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
			)
			{
				if (ConvertTimeStamp(MakeTimeStamp($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]), "SHORT") == ConvertTimeStamp())
					$arEvents["FIELDS_FORMATTED"]["DATETIME_FORMATTED"] = $timeFormated;
				else
					$arEvents["FIELDS_FORMATTED"]["DATETIME_FORMATTED"] = $dateTimeFormated;
			}
			else
			{
				if ($arEvents["FIELDS_FORMATTED"]["LOG_DATE_DAY"] == ConvertTimeStamp())
					$arEvents["FIELDS_FORMATTED"]["DATETIME_FORMATTED"] = $timeFormated;
				else
					$arEvents["FIELDS_FORMATTED"]["DATETIME_FORMATTED"] = $dateTimeFormated;
			}

			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arEvents["EVENT_ID"]);
			if (
				!array_key_exists("HAS_COMMENTS", $arEvents["FIELDS_FORMATTED"])
				|| $arEvents["FIELDS_FORMATTED"]["HAS_COMMENTS"] != "N"
			)
			{
				$arEvents["FIELDS_FORMATTED"]["HAS_COMMENTS"] = (
					$arCommentEvent
					&& (
						$arCommentEvent["EVENT_ID"] == "blog_comment_micro"
						|| !array_key_exists("ENABLE_COMMENTS", $arEvents)
						|| $arEvents["ENABLE_COMMENTS"] != "N"
					)
						? "Y"
						: "N"
				);
			}

			if (is_object($cache))
			{
				$arCacheData = Array(
					"FIELDS_FORMATTED" => $arEvents["FIELDS_FORMATTED"]
				);
				$cache->EndDataCache($arCacheData);
				if(defined("BX_COMP_MANAGED_CACHE"))
					$GLOBALS["CACHE_MANAGER"]->EndTagCache();
			}
		}

		if (is_array($arCurrentUserSubscribe))
		{
			$arEvents["FIELDS_FORMATTED"]["TRANSPORT"] = __SLGetTransport($arEvents, $arCurrentUserSubscribe, $arMyEntities);
			$arEvents["FIELDS_FORMATTED"]["VISIBLE"] = __SLGetVisible($arEvents, $arCurrentUserSubscribe, $arMyEntities);
		}
		
		$array_key = $arEvents["ENTITY_TYPE"]."_".$arEvents["ENTITY_ID"]."_".$arEvents["EVENT_ID"];
		if (array_key_exists($array_key, $GLOBALS["CurUserCanAddComments"]))
			$arEvents["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = ($GLOBALS["CurUserCanAddComments"][$array_key] == "Y" && $arEvents["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y" ? "Y" : "N");
		else
		{
			$feature = CSocNetLogTools::FindFeatureByEventID($arEvents["EVENT_ID"]);
			if ($feature && $arCommentEvent && array_key_exists("OPERATION_ADD", $arCommentEvent) && strlen($arCommentEvent["OPERATION_ADD"]) > 0)
				$GLOBALS["CurUserCanAddComments"][$array_key] = (CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arEvents["ENTITY_TYPE"], $arEvents["ENTITY_ID"], ($feature == "microblog" ? "blog" : $feature), $arCommentEvent["OPERATION_ADD"], $bCurrentUserIsAdmin) ? "Y" : "N");
			else
				$GLOBALS["CurUserCanAddComments"][$array_key] = "Y";

			$arEvents["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = ($GLOBALS["CurUserCanAddComments"][$array_key] == "Y" && $arEvents["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y" ? "Y" : "N");
		}

		if (array_key_exists("FAVORITES_USER_ID", $arEvents) && intval($arEvents["FAVORITES_USER_ID"]) > 0)
			$arEvents["FIELDS_FORMATTED"]["FAVORITES"] = "Y";
		else
			$arEvents["FIELDS_FORMATTED"]["FAVORITES"] = "N";

		if (
			$arParams["CHECK_PERMISSIONS_DEST"] == "N"
			&& !$bCurrentUserIsAdmin
			&& is_object($GLOBALS["USER"])
			&& (
				(
					array_key_exists("DESTINATION", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]) 
					&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"])
				)
				|| (
					array_key_exists("DESTINATION_CODE", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]) 
					&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"])
				)
			)
		)
		{
			$arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] = 0;

			$arGroupID = array();

			if (!empty($GLOBALS["SONET_GROUPS_ID_AVAILABLE"]))
				$arGroupID = $GLOBALS["SONET_GROUPS_ID_AVAILABLE"];
			else
			{
				// get tagged cached available groups and intersect
				$cache = new CPHPCache;	
				$cache_id = $GLOBALS["USER"]->GetID();
				$cache_path = "/sonet_groups_available/";

				if (
					$cache->InitCache($cache_time, $cache_id, $cache_path)
				)
				{
					$arCacheVars = $cache->GetVars();
					$arGroupID = $arCacheVars["arGroupID"];
				}
				else
				{
					$cache->StartDataCache($cache_time, $cache_id, $cache_path);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$GLOBALS["USER"]->GetID());
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group");
					}

					$rsGroup = CSocNetGroup::GetList(
						array(),
						array("CHECK_PERMISSIONS" => $GLOBALS["USER"]->GetID()),
						false,
						false,
						array("ID")
					);
					while($arGroup = $rsGroup->Fetch())
						$arGroupID[] = $arGroup["ID"];

					$arCacheData = array(
						"arGroupID" => $arGroupID
					);
					$cache->EndDataCache($arCacheData);
					if(defined("BX_COMP_MANAGED_CACHE"))
						$GLOBALS["CACHE_MANAGER"]->EndTagCache();
				}

				$GLOBALS["SONET_GROUPS_ID_AVAILABLE"] = $arGroupID;
			}

			if (
				array_key_exists("DESTINATION", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]) 
				&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"])
			)
			{
				foreach($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"] as $key => $arDestination)
				{
					if (
						array_key_exists("TYPE", $arDestination)
						&& array_key_exists("ID", $arDestination)
						&& $arDestination["TYPE"] == "SG"
						&& !in_array(intval($arDestination["ID"]), $arGroupID)
					)
					{
						unset($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"][$key]);
						$arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]++;
					}
				}

				if (
					intval($arParams["DESTINATION_LIMIT_SHOW"]) > 0
					&& count($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"]) > $arParams["DESTINATION_LIMIT_SHOW"]
				)
				{
					$arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_MORE"] = count($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"]) - $arParams["DESTINATION_LIMIT_SHOW"];
					$arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"] = array_slice($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"], 0, $arParams["DESTINATION_LIMIT_SHOW"]);
				}
			}
			elseif (
				array_key_exists("DESTINATION_CODE", $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]) 
				&& is_array($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"])
			)
			{
				foreach($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"] as $key => $right_tmp)
				{
					if (
						preg_match('/^SG(\d+)$/', $right_tmp, $matches)
						&& !in_array(intval($matches[1]), $arGroupID)						
					)
					{
						unset($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"][$key]);
						$arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]++;
					}
				}
			}
		}

		if ($arParams["NEW_TEMPLATE"] != "Y")
			$arTmpEventsOld[] = $arTmpEvent;
		$arTmpEventsNew[] = $arEvents["FIELDS_FORMATTED"];
	}
}

if (!function_exists('__SLGetLogCommentRecord'))
{
	function __SLGetLogCommentRecord($arComments, $arParams, $arCurrentUserSubscribe, $arMyEntities, &$arTmpComments, $bTooltip = true)
	{
		// for the same post log_update - time only, if not - date and time
		$dateFormated = FormatDate(
			$GLOBALS['DB']->DateFormatToPHP(FORMAT_DATE),
			MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComments) ? $arComments["LOG_DATE_FORMAT"] : $arComments["LOG_DATE"])
		);
		$timeFormated = FormatDateFromDB((array_key_exists("LOG_DATE_FORMAT", $arComments) ? $arComments["LOG_DATE_FORMAT"] : $arComments["LOG_DATE"]), (stripos($arParams["DATE_TIME_FORMAT"], 'a') || ($arParams["DATE_TIME_FORMAT"] == 'FULL' && IsAmPmMode()) !== false ? 'H:MI T' : 'HH:MI'));
		$dateTimeFormated = FormatDate(
			(!empty($arParams['DATE_TIME_FORMAT']) ? ($arParams['DATE_TIME_FORMAT'] == 'FULL' ? $GLOBALS['DB']->DateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME)) : $arParams['DATE_TIME_FORMAT']) : $GLOBALS['DB']->DateFormatToPHP(FORMAT_DATETIME)),
			MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComments) ? $arComments["LOG_DATE_FORMAT"] : $arComments["LOG_DATE"])
		);
		if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
		{
			$dateFormated = ToLower($dateFormated);
			$dateTimeFormated = ToLower($dateTimeFormated);
		}
		// strip current year
		if (!empty($arParams['DATE_TIME_FORMAT']) && ($arParams['DATE_TIME_FORMAT'] == 'j F Y G:i' || $arParams['DATE_TIME_FORMAT'] == 'j F Y g:i a'))
		{
			$dateTimeFormated = ltrim($dateTimeFormated, '0');
			$curYear = date('Y');
			$dateTimeFormated = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $dateTimeFormated);
		}

		//unset blog comment fields as they are processed later by socialnetwork.blog.post.comment
		if ($arComments["EVENT_ID"] == "blog_comment" && $arParams["NEW_TEMPLATE"] == "Y")
		{
			if (array_key_exists("TEXT_MESSAGE", $arComments))
				unset($arComments["TEXT_MESSAGE"]);
			if (array_key_exists("~TEXT_MESSAGE", $arComments))
				unset($arComments["~TEXT_MESSAGE"]);
			if (array_key_exists("MESSAGE", $arComments))
				unset($arComments["MESSAGE"]);
			if (array_key_exists("~MESSAGE", $arComments))
				unset($arComments["~MESSAGE"]);
		}

		$title = "";

		if ($arComments["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arComments["ENTITY_ID"]));
		else
			$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComments["ENTITY_ID"]));

		if (intval($arComments["USER_ID"]) > 0)
		{
			$suffix = (is_array($GLOBALS["arExtranetUserID"]) && in_array($arComments["USER_ID"], $GLOBALS["arExtranetUserID"]) ? GetMessage("SONET_LOG_EXTRANET_SUFFIX") : "");

			if ($bTooltip)
				$arCreatedBy = array(
					"TOOLTIP_FIELDS" => array(
						"ID" => $arComments["USER_ID"],
						"NAME" => $arComments["~CREATED_BY_NAME"],
						"LAST_NAME" => $arComments["~CREATED_BY_LAST_NAME"],
						"SECOND_NAME" => $arComments["~CREATED_BY_SECOND_NAME"],
						"LOGIN" => $arComments["~CREATED_BY_LOGIN"],
						"USE_THUMBNAIL_LIST" => "N",
						"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
						"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
						"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
						"SHOW_YEAR" => $arParams["SHOW_YEAR"],
						"CACHE_TYPE" => $arParams["CACHE_TYPE"],
						"CACHE_TIME" => $arParams["CACHE_TIME"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"].$suffix,
						"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"INLINE" => "Y"
					)
				);
			else
			{
				$arTmpUser = array(
					"NAME" => $arComments["~CREATED_BY_NAME"],
					"LAST_NAME" => $arComments["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" => $arComments["~CREATED_BY_SECOND_NAME"],
					"LOGIN" => $arComments["~CREATED_BY_LOGIN"]
				);
				$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
				$arCreatedBy = array(
					"FORMATTED" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin).$suffix,
					"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComments["USER_ID"], "id" => $arComments["USER_ID"]))
				);
			}
		}
		else
			$arCreatedBy = array("FORMATTED" => GetMessage("SONET_C73_CREATED_BY_ANONYMOUS"));

		$arTmpUser = array(
			"NAME" => $arComments["~USER_NAME"],
			"LAST_NAME" => $arComments["~USER_LAST_NAME"],
			"SECOND_NAME" => $arComments["~USER_SECOND_NAME"],
			"LOGIN" => $arComments["~USER_LOGIN"]
		);

		$arParamsTmp = $arParams;
		$arParamsTmp["AVATAR_SIZE"] = $arParams["AVATAR_SIZE_COMMENT"];

		$arTmpCommentEvent = array(
			"EVENT"	=> $arComments,
			"LOG_DATE" => $arComments["LOG_DATE"],
			"LOG_DATE_TS" => MakeTimeStamp($arComments["LOG_DATE"]),
			"LOG_DATE_DAY"	=> ConvertTimeStamp(MakeTimeStamp($arComments["LOG_DATE"]), "SHORT"),
			"LOG_TIME_FORMAT" => $timeFormated,
			"TITLE_TEMPLATE" => $title,
			"TITLE" => $title,
			"TITLE_FORMAT" => $title, // need to use url here
			"ENTITY_NAME" => (($arComments["ENTITY_TYPE"] == SONET_ENTITY_GROUP) ? $arComments["GROUP_NAME"] : CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin)),
			"ENTITY_PATH" => $path2Entity,
			"CREATED_BY" => $arCreatedBy,
			"AVATAR_SRC" => CSocNetLogTools::FormatEvent_CreateAvatar($arComments, $arParamsTmp)
		);

		if (is_array($arCurrentUserSubscribe) && $arParams["USER_COMMENTS"] != "Y")
		{
			$arTmpCommentEvent["TRANSPORT"] = __SLGetTransport($arComments, $arCurrentUserSubscribe, $arMyEntities);
			$arTmpCommentEvent["VISIBLE"] = __SLGetVisible($arComments, $arCurrentUserSubscribe, $arMyEntities);
		}

		$arEvent = CSocNetLogTools::FindLogCommentEventByID($arComments["EVENT_ID"]);

		if (
			$arEvent
			&& array_key_exists("CLASS_FORMAT", $arEvent)
			&& array_key_exists("METHOD_FORMAT", $arEvent)
		)
		{
			if ($arParams["USER_COMMENTS"] == "Y")
				$arLog = array();
			else
				$arLog = array(
					"TITLE" => $arComments["~LOG_TITLE"],
					"URL" => $arComments["~LOG_URL"],
					"PARAMS" => $arComments["~LOG_PARAMS"]
				);

			$arFIELDS_FORMATTED = call_user_func(array($arEvent["CLASS_FORMAT"], $arEvent["METHOD_FORMAT"]), $arComments, $arParams, false, $arLog);

			if ($arParams["USE_COMMENTS"] != "Y")
			{
				if (
					array_key_exists("CREATED_BY", $arFIELDS_FORMATTED)
					&& is_array($arFIELDS_FORMATTED["CREATED_BY"])
					&& array_key_exists("TOOLTIP_FIELDS", $arFIELDS_FORMATTED["CREATED_BY"])
				)
					$arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"] = $arFIELDS_FORMATTED["CREATED_BY"]["TOOLTIP_FIELDS"];
				$arTmpCommentEvent["ENTITY"] = $arFIELDS_FORMATTED["ENTITY"];
			}
		}

		$message = (
			$arFIELDS_FORMATTED
			&& array_key_exists("EVENT_FORMATTED", $arFIELDS_FORMATTED)
			&& array_key_exists("MESSAGE", $arFIELDS_FORMATTED["EVENT_FORMATTED"])
				? $arFIELDS_FORMATTED["EVENT_FORMATTED"]["MESSAGE"]
				: $arTmpCommentEvent["MESSAGE"]
		);

		$short_message = (
			$arFIELDS_FORMATTED
			&& array_key_exists("EVENT_FORMATTED", $arFIELDS_FORMATTED)
			&& array_key_exists("SHORT_MESSAGE", $arFIELDS_FORMATTED["EVENT_FORMATTED"])
				? $arFIELDS_FORMATTED["EVENT_FORMATTED"]["SHORT_MESSAGE"]
				: false
		);

		if (strlen($message) > 0)
			$arFIELDS_FORMATTED["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] = CSocNetTextParser::closetags(htmlspecialcharsback($message));

		if (
			strlen($message) > 0
			&& $arParams["NEW_TEMPLATE"] != "Y"
		)
		{
			if ($short_message)
				$arFIELDS_FORMATTED["EVENT_FORMATTED"]["SHORT_MESSAGE_CUT"] = $short_message;
			else
				$arFIELDS_FORMATTED["EVENT_FORMATTED"]["SHORT_MESSAGE_CUT"] = substr(HTMLToTxt(htmlspecialcharsback($message), "", array("/(<img\s[^>]*>)/is", "/(<a\s[^>]*>)/is", "/(<\/a>)/is")), 0, 1000);

			if (
				!array_key_exists("IS_MESSAGE_SHORT", $arFIELDS_FORMATTED["EVENT_FORMATTED"])
				&& strlen($arFIELDS_FORMATTED["EVENT_FORMATTED"]["SHORT_MESSAGE_CUT"]) == strlen(htmlspecialcharsback($arFIELDS_FORMATTED["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"]))
			)
				$arFIELDS_FORMATTED["EVENT_FORMATTED"]["IS_MESSAGE_SHORT"] = true;
		}

		if (
			$arParams["NEW_TEMPLATE"] != "Y"
			&& is_array($arFIELDS_FORMATTED)
			&& array_key_exists("EVENT_FORMATTED", $arFIELDS_FORMATTED)
		)
		{
			if (array_key_exists("SHORT_MESSAGE", $arFIELDS_FORMATTED["EVENT_FORMATTED"]))
				unset($arFIELDS_FORMATTED["EVENT_FORMATTED"]["SHORT_MESSAGE"]);
		}

		if ($arTmpCommentEvent["LOG_DATE_DAY"] == ConvertTimeStamp())
			$arFIELDS_FORMATTED["EVENT_FORMATTED"]["DATETIME"] = $timeFormated;
		else
			$arFIELDS_FORMATTED["EVENT_FORMATTED"]["DATETIME"] = $dateTimeFormated;

		$arFIELDS_FORMATTED["EVENT_FORMATTED"]["ALLOW_VOTE"] = CRatings::CheckAllowVote(array(
			"ENTITY_TYPE_ID" => $arComments["RATING_TYPE_ID"],
			"OWNER_ID" => $arComments["USER_ID"]
		));

		$arTmpCommentEvent["EVENT_FORMATTED"] = $arFIELDS_FORMATTED["EVENT_FORMATTED"];

		$arTmpComments[] = $arTmpCommentEvent;
	}
}

?>