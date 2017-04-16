<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

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

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
$entity_type = (isset($_REQUEST["et"]) && is_string($_REQUEST["et"])) ? trim($_REQUEST["et"]): "";
$entity_id = isset($_REQUEST["eid"])? $_REQUEST["eid"]: "";
$cb_id = isset($_REQUEST["cb_id"])? $_REQUEST["cb_id"]: "";
$event_id = (isset($_REQUEST["evid"]) && is_string($_REQUEST["evid"])) ? trim($_REQUEST["evid"]): "";
$transport = (isset($_REQUEST["transport"]) && is_string($_REQUEST["transport"])) ? trim($_REQUEST["transport"]): "";
$visible = (isset($_REQUEST["visible"]) && is_string($_REQUEST["visible"])) ? trim($_REQUEST["visible"]): "";

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && !is_array($_REQUEST["ls"])? trim($_REQUEST["ls"]): "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
else
	define("LANGUAGE_ID", "en");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.log/include.php");

__IncludeLang(dirname(__FILE__)."/lang/".$lng."/ajax.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (in_array($action, array("get_comment", "get_comments")))
	{
		$GLOBALS["arExtranetGroupID"] = array();
		$GLOBALS["arExtranetUserID"] = array();

		if ($GLOBALS["USER"]->IsAuthorized())
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
					)
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
	}

	if (!$GLOBALS["USER"]->IsAuthorized())
		$arResult[0] = "*";
	elseif (!check_bitrix_sessid())
		$arResult[0] = "*";
	elseif ($action == "get_data")
	{
		if
		(
			intval($entity_id) > 0
			&& array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("CLASS_DESC_GET", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("METHOD_DESC_GET", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
		)
			$arEntityTmp = call_user_func(
				array(
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_DESC_GET"],
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_DESC_GET"]
				),
				$entity_id
			);
		else
			$arEntityTmp = array();

		if (intval($cb_id) > 0)
			$arCreatedByTmp = call_user_func(
				array(
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][SONET_SUBSCRIBE_ENTITY_USER]["CLASS_DESC_GET"],
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][SONET_SUBSCRIBE_ENTITY_USER]["METHOD_DESC_GET"]
				),
				$cb_id
			);
		else
			$arCreatedByTmp = array();

		$is_my = false;

		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("CLASS_MY_BY_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("METHOD_MY_BY_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
		)
			$is_my = call_user_func(
				array(
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY_BY_ID"],
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY_BY_ID"]
				),
				$entity_id
			);

		$arSubscribe = array();

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => $entity_id,
			"ENTITY_CB" => "N"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["EVENT"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"VISIBLE" => $arSubscribesTmp["VISIBLE"],
					"TRANSPORT_INHERITED" => false,
					"VISIBLE_INHERITED" => false
				);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
				$arSubscribe["ALL"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"VISIBLE" => $arSubscribesTmp["VISIBLE"],
					"TRANSPORT_INHERITED" => false,
					"VISIBLE_INHERITED" => false
				);
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" 		=> $GLOBALS["USER"]->GetID(),
			"ENTITY_TYPE" 	=> SONET_SUBSCRIBE_ENTITY_USER,
			"ENTITY_ID" 	=> $cb_id,
			"ENTITY_CB" 	=> "Y"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["CB_EVENT"] = array(
					"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
					"VISIBLE" => $arSubscribesTmp["VISIBLE"],
					"TRANSPORT_INHERITED" => false,
					"VISIBLE_INHERITED" => false
				);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["CB_ALL"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"VISIBLE" => $arSubscribesTmp["VISIBLE"],
						"TRANSPORT_INHERITED" => false,
						"VISIBLE_INHERITED" => false
					);
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => 0
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($is_my && $arSubscribesTmp["ENTITY_MY"] == "Y")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT_MY"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"VISIBLE" => $arSubscribesTmp["VISIBLE"],
						"TRANSPORT_INHERITED" => false,
						"VISIBLE_INHERITED" => false
					);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL_MY"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"VISIBLE" => $arSubscribesTmp["VISIBLE"],
						"TRANSPORT_INHERITED" => false,
						"VISIBLE_INHERITED" => false
					);
				else
					continue;
			}
			elseif ($arSubscribesTmp["ENTITY_MY"] == "N")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"VISIBLE" => $arSubscribesTmp["VISIBLE"],
						"TRANSPORT_INHERITED" => false,
						"VISIBLE_INHERITED" => false
					);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL"] = array(
						"TRANSPORT" => $arSubscribesTmp["TRANSPORT"],
						"VISIBLE" => $arSubscribesTmp["VISIBLE"],
						"TRANSPORT_INHERITED" => false,
						"VISIBLE_INHERITED" => false
					);
				else
					continue;
			}
		}

		$arTmp = array("TRANSPORT", "VISIBLE");
		foreach ($arTmp as $strTmp)
		{

			if ($strTmp == "TRANSPORT")
				$value_default = "N";
			elseif ($strTmp == "VISIBLE")
				$value_default = "Y";

			if (
				!array_key_exists("EVENT", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["EVENT"])
				|| $arSubscribe["EVENT"][$strTmp] == "I"
			)
			{
				if (
					array_key_exists("ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["ALL"])
					&& $arSubscribe["ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["ALL"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					$is_my
					&& array_key_exists("COMMON_EVENT_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
					&& $arSubscribe["COMMON_EVENT_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT_MY"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					$is_my
					&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_EVENT", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
					&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["EVENT"][$strTmp] = $value_default;
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				!array_key_exists("ALL", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["ALL"])
				|| $arSubscribe["ALL"][$strTmp] == "I"
			)
			{
				if (
					$is_my
					&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["ALL"][$strTmp] = $value_default;
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				$is_my
				&&
				(
					!array_key_exists("COMMON_EVENT_MY", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
					|| $arSubscribe["COMMON_EVENT_MY"][$strTmp] == "I"
				)
			)
			{
				if (
					array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_EVENT", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
					&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $value_default;
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				$is_my
				&&
				(
					!array_key_exists("COMMON_ALL_MY", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					|| $arSubscribe["COMMON_ALL_MY"][$strTmp] == "I"
				)
			)
			{
				if (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_ALL_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_ALL_MY"][$strTmp] = $value_default;
					$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				!array_key_exists("COMMON_EVENT", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
				|| $arSubscribe["COMMON_EVENT"][$strTmp] == "I"
			)
			{
				if (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_EVENT"][$strTmp] = $value_default;
					$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				!array_key_exists("COMMON_ALL", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				|| $arSubscribe["COMMON_ALL"][$strTmp] == "I"
			)
			{
				$arSubscribe["COMMON_ALL"][$strTmp] = $value_default;
				$arSubscribe["COMMON_ALL"][$strTmp."_INHERITED"] = true;
			}
		}

		$fullset_event_id = CSocNetLogTools::FindFullSetEventIDByEventID($event_id);
		if ($fullset_event_id)
			$arEvent = CSocNetLogTools::FindLogEventByID($fullset_event_id, $entity_type);
		else
			$arEvent = CSocNetLogTools::FindLogEventByID($event_id, $entity_type);

		if (!$arEvent)
		{
			$arEvent = CSocNetLogTools::FindLogEventByCommentID($event_id);
			if ($arEvent)
			{
				$fullset_event_id = CSocNetLogTools::FindFullSetEventIDByEventID($arEvent["EVENT_ID"]);
				if ($fullset_event_id)
					$arEvent = CSocNetLogTools::FindLogEventByID($fullset_event_id, $entity_type);
			}
		}

		if ($arEvent)
		{
			$arSubscribe["EVENT"]["TITLE"] = $arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS"];

			if (
				array_key_exists("NAME_FORMATTED", $arEntityTmp)
				&& strlen($arEntityTmp["NAME_FORMATTED"]) > 0
			)
			{
				$arSubscribe["EVENT"]["TITLE_1"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
					$arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS_1"]
				);
				$arSubscribe["EVENT"]["TITLE_2"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
					$arEvent["ENTITIES"][$entity_type]["TITLE_SETTINGS_2"]
				);
			}
		}

		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("TITLE_SETTINGS_ALL", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL"]) > 0
		)
			$arSubscribe["ALL"]["TITLE"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL"];

		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("TITLE_SETTINGS_ALL_1", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL_1"]) > 0
			&& array_key_exists("NAME_FORMATTED", $arEntityTmp)
			&& strlen($arEntityTmp["NAME_FORMATTED"]) > 0
		)
		{
			$arSubscribe["ALL"]["TITLE_1"] = str_replace(
				array("#TITLE#"),
				array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
				$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL_1"]
			);
			$arSubscribe["ALL"]["TITLE_2"] = str_replace(
				array("#TITLE#"),
				array(array_key_exists("~NAME_FORMATTED", $arEntityTmp) ? $arEntityTmp["~NAME_FORMATTED"] : $arEntityTmp["NAME_FORMATTED"]),
				$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL_2"]
			);
		}

		if (CSocNetLogTools::HasLogEventCreatedBy($event_id))
		{
			foreach ($arTmp as $strTmp)
			{
				if ($strTmp == "TRANSPORT")
					$value_default = "N";
				elseif ($strTmp == "VISIBLE")
					$value_default = "Y";

				if (
					!array_key_exists("CB_EVENT", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["CB_EVENT"])
					|| $arSubscribe["CB_EVENT"][$strTmp] == "I"
				)
				{
					if (
						array_key_exists("CB_ALL", $arSubscribe)
						&& array_key_exists($strTmp, $arSubscribe["CB_ALL"])
						&& $arSubscribe["CB_ALL"][$strTmp] != "I"
					)
					{
						$arSubscribe["CB_EVENT"][$strTmp] = $arSubscribe["CB_ALL"][$strTmp];
						$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
					}
					else
					{
						$arSubscribe["CB_EVENT"][$strTmp] = $value_default;
						$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
					}
				}

				if (
					!array_key_exists("CB_ALL", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["CB_ALL"])
					|| $arSubscribe["CB_ALL"][$strTmp] == "I"
				)
				{
					$arSubscribe["CB_ALL"][$strTmp] = $value_default;
					$arSubscribe["CB_ALL"][$strTmp."_INHERITED"] = true;
				}
			}

			$arSubscribe["CB_ALL"]["TITLE"]	= GetMessage("SUBSCRIBE_CB_ALL");

			if (
				array_key_exists("NAME_FORMATTED", $arCreatedByTmp)
				&& strlen($arCreatedByTmp["NAME_FORMATTED"]) > 0
			)
			{
				$arSubscribe["CB_ALL"]["TITLE_1"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arCreatedByTmp) ? $arCreatedByTmp["~NAME_FORMATTED"] : $arCreatedByTmp["NAME_FORMATTED"]),
					GetMessage("SUBSCRIBE_CB_ALL_1")
				);
				$arSubscribe["CB_ALL"]["TITLE_2"] = str_replace(
					array("#TITLE#"),
					array(array_key_exists("~NAME_FORMATTED", $arCreatedByTmp) ? $arCreatedByTmp["~NAME_FORMATTED"] : $arCreatedByTmp["NAME_FORMATTED"]),
					GetMessage("SUBSCRIBE_CB_ALL_2")
				);
			}
		}
		else
		{
			if (array_key_exists("CB_EVENT", $arSubscribe))
				unset($arSubscribe["CB_EVENT"]);
			if (array_key_exists("CB_ALL", $arSubscribe))
				unset($arSubscribe["CB_ALL"]);
		}

		$arSubscribe["SITE_ID"] = (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("HAS_SITE_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
			&& strlen($site_id) > 0
			?
				$site_id
			:
				false
		);

		$arResult["Subscription"] = $arSubscribe;

		$arResult["Transport"] = array(
			0 => array("Key" => "N", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_NONE")),
			1 => array("Key" => "M", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_MAIL")),
//			3 => array("Key" => "D", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_DIGEST")),
//			4 => array("Key" => "E", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_DIGEST_WEEK"))
		);

		if (CBXFeatures::IsFeatureEnabled("WebMessenger"))
			$arResult["Transport"][] = array("Key" => "X", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_XMPP"));

		$arResult["Visible"] = array(
			0 => array("Key" => "Y", "Value" => GetMessage("SUBSCRIBE_VISIBLE_Y")),
			1 => array("Key" => "N", "Value" => GetMessage("SUBSCRIBE_VISIBLE_N")),
		);

	}
	elseif ($action == "get_transport")
	{
		$arCurrentUserSubscribe = array(
			"TRANSPORT" => array()
		);

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
			if ($arSubscribesTmp["TRANSPORT"] != "I")
				$arCurrentUserSubscribe["TRANSPORT"][$arSubscribesTmp["ENTITY_TYPE"]."_".$arSubscribesTmp["ENTITY_ID"]."_".$arSubscribesTmp["EVENT_ID"]."_".$arSubscribesTmp["ENTITY_MY"]."_".$arSubscribesTmp["ENTITY_CB"]] = $arSubscribesTmp["TRANSPORT"];

		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("HAS_MY", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_MY"] == "Y"
			&& array_key_exists("CLASS_MY", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("METHOD_MY", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"]) > 0
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"]) > 0
			&& method_exists($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"], $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"])
		)
			$arMyEntities = call_user_func(array(
				$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY"],
				$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY"]
			));

		if (array_key_exists($entity_type."_".$entity_id."_".$event_id."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_".$entity_id."_".$event_id."_N_N"];

		if (array_key_exists($entity_type."_".$entity_id."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_".$entity_id."_all_N_N"];

		if (CSocNetTools::HasLogEventCreatedBy($event_id))
		{
			if ($event_id)
			{
				if (array_key_exists("U_".$cb_id."_all_N_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$cb_id."_all_N_Y"];
				elseif (array_key_exists("U_".$cb_id."_all_Y_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$cb_id."_all_Y_Y"];
			}
		}

		if (
			!array_key_exists($entity_type."_".$entity_id."_".$event_id."_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			&& !array_key_exists($entity_type."_".$entity_id."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"])
		)
		{
			if
			(
				array_key_exists($entity_id, $arMyEntities)
				&& array_key_exists($entity_type."_0_".$entity_id."_Y_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_0_".$event_id."_Y_N"];
			elseif
			(
				array_key_exists($entity_id, $arMyEntities)
				&& array_key_exists($entity_type."_0_all_Y_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_0_all_Y_N"];
			elseif (array_key_exists($entity_type."_0_".$event_id."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_0_".$event_id."_N_N"];
			elseif (array_key_exists($entity_type."_0_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$entity_type."_0_all_N_N"];
			else
				$arTransport[] = "N";
		}

		$arTransport = array_unique($arTransport);
		usort($arTransport, "__SLTransportSort");

		$arResult = $arTransport;
	}
	elseif ($action == "set")
	{
		$arFields = false;

		if (in_array($ls, array("EVENT", "ALL")))
		{
			$arFields = array(
				"USER_ID" => $GLOBALS["USER"]->GetID(),
				"ENTITY_TYPE" => $entity_type,
				"ENTITY_ID" => $entity_id,
				"ENTITY_CB" => "N"
			);

			if($ls == "EVENT")
				$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
			else
				$arEventID = array("all");

		}
		elseif (in_array($ls, array("CB_ALL")))
		{
			$arFields = array(
				"USER_ID" => $GLOBALS["USER"]->GetID(),
				"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" => $cb_id,
				"ENTITY_CB" => "Y"
			);

			$arEventID = array("all");
		}

		if ($arFields && (strlen($transport) > 0 || strlen($visible) > 0))
		{
			if (
				$arFields["ENTITY_CB"] != "Y"
				&& array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
				&& array_key_exists("HAS_SITE_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
				&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
				&& strlen($site_id) > 0
			)
				$arFieldsVal["SITE_ID"] = $site_id;
			else
				$arFieldsVal["SITE_ID"] = false;

			if (strlen($transport) > 0)
				$arFieldsVal["TRANSPORT"] = $transport;

			if (strlen($visible) > 0)
				$arFieldsVal["VISIBLE"] = $visible;

			foreach($arEventID as $event_id)
			{
				$arFields["EVENT_ID"] = $event_id;

				$dbResultTmp = CSocNetLogEvents::GetList(
					array(),
					$arFields,
					false,
					false,
					array("ID", "TRANSPORT", "VISIBLE")
				);

				$arFieldsSet = array_merge($arFields, $arFieldsVal);

				if ($arResultTmp = $dbResultTmp->Fetch())
				{
					if ($arFieldsVal["TRANSPORT"] == "I")
					{
						if ($arResultTmp["VISIBLE"] == "I")
							CSocNetLogEvents::Delete($arResultTmp["ID"]);
						else
							$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
					}
					elseif ($arFieldsVal["VISIBLE"] == "I")
					{
						if ($arResultTmp["TRANSPORT"] == "I")
							CSocNetLogEvents::Delete($arResultTmp["ID"]);
						else
							$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
					}
					else
						$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
				}
				elseif(
					$arFieldsVal["TRANSPORT"] != "I"
					&& $arFieldsVal["VISIBLE"] != "I"
				)
				{
					if (!array_key_exists("TRANSPORT", $arFieldsSet))
						$arFieldsSet["TRANSPORT"] = "I";
					if (!array_key_exists("VISIBLE", $arFieldsSet))
						$arFieldsSet["VISIBLE"] = "I";

					$idTmp = CSocNetLogEvents::Add($arFieldsSet);
				}
			}
		}
	}
	elseif ($action == "set_transport_arr")
	{
		$arFields = false;

		if (is_array($ls_arr))
		{
			foreach($ls_arr as $ls => $transport)
			{
				$ls = trim($ls);

				if (in_array($ls, array("EVENT", "ALL")))
				{
					$arFields = array(
						"USER_ID" => $GLOBALS["USER"]->GetID(),
						"ENTITY_TYPE" => $entity_type,
						"ENTITY_ID" => $entity_id,
						"ENTITY_CB" => "N"
					);

					if($ls == "EVENT")
						$arEventID = CSocNetLogTools::FindFullSetByEventID($event_id);
					else
						$arEventID = array("all");

				}
				elseif (in_array($ls, array("CB_ALL")))
				{
					$arFields = array(
						"USER_ID" => $GLOBALS["USER"]->GetID(),
						"ENTITY_TYPE" => SONET_SUBSCRIBE_ENTITY_USER,
						"ENTITY_ID" => $cb_id,
						"ENTITY_CB" => "Y"
					);

					$arEventID = array("all");
				}

				if ($arFields && (strlen($transport) > 0 || strlen($visible) > 0))
				{
					if (
						$arFields["ENTITY_CB"] != "Y"
						&& array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
						&& array_key_exists("HAS_SITE_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
						&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
						&& strlen($site_id) > 0
					)
						$arFieldsVal["SITE_ID"] = $site_id;
					else
						$arFieldsVal["SITE_ID"] = false;

					if (strlen($transport) > 0)
						$arFieldsVal["TRANSPORT"] = $transport;

					if (strlen($visible) > 0)
						$arFieldsVal["VISIBLE"] = $visible;

					foreach($arEventID as $event_id)
					{
						$arFields["EVENT_ID"] = $event_id;

						$dbResultTmp = CSocNetLogEvents::GetList(
							array(),
							$arFields,
							false,
							false,
							array("ID", "TRANSPORT", "VISIBLE")
						);

						$arFieldsSet = array_merge($arFields, $arFieldsVal);

						if ($arResultTmp = $dbResultTmp->Fetch())
						{
							if ($arFieldsVal["TRANSPORT"] == "I")
							{
								if ($arResultTmp["VISIBLE"] == "I")
									CSocNetLogEvents::Delete($arResultTmp["ID"]);
								else
									$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
							}
							elseif ($arFieldsVal["VISIBLE"] == "I")
							{
								if ($arResultTmp["TRANSPORT"] == "I")
									CSocNetLogEvents::Delete($arResultTmp["ID"]);
								else
									$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
							}
							else
									$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
						}
						elseif(
							$arFieldsVal["TRANSPORT"] != "I"
							&& $arFieldsVal["VISIBLE"] != "I"
						)
						{
							if (!array_key_exists("TRANSPORT", $arFieldsSet))
								$arFieldsSet["TRANSPORT"] = "I";
							if (!array_key_exists("VISIBLE", $arFieldsSet))
								$arFieldsSet["VISIBLE"] = "I";

							$idTmp = CSocNetLogEvents::Add($arFieldsSet);
						}
					}
				}
			}
		}
	}
	elseif ($action == "add_comment")
	{
		$log_id = $_REQUEST["log_id"];
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arLog["EVENT_ID"]);
			if ($arCommentEvent)
			{
				$feature = CSocNetLogTools::FindFeatureByEventID($arCommentEvent["EVENT_ID"]);

				if ($feature && array_key_exists("OPERATION_ADD", $arCommentEvent) && strlen($arCommentEvent["OPERATION_ADD"]) > 0)
					$bCanAddComments = CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arLog["ENTITY_TYPE"], $arLog["ENTITY_ID"], ($feature == "microblog" ? "blog" : $feature), $arCommentEvent["OPERATION_ADD"], $bCurrentUserIsAdmin);
				else
					$bCanAddComments = true;

				if ($bCanAddComments)
				{
					// add source object and get source_id, $source_url
					$arParams = array(
						"PATH_TO_SMILE" => $_REQUEST["p_smile"],
						"PATH_TO_USER_BLOG_POST" => $_REQUEST["p_ubp"],
						"PATH_TO_GROUP_BLOG_POST" => $_REQUEST["p_gbp"],
						"PATH_TO_USER_MICROBLOG_POST" => $_REQUEST["p_umbp"],
						"PATH_TO_GROUP_MICROBLOG_POST" => $_REQUEST["p_gmbp"],
						"BLOG_ALLOW_POST_CODE" => $_REQUEST["bapc"]
					);
					$parser = new logTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

					$comment_text = $_REQUEST["message"];
					CUtil::decodeURIComponent($comment_text);
					$comment_text = Trim($comment_text);

					if (strlen($comment_text) > 0)
					{
						$arSearchParams = array();

						if($arCommentEvent["EVENT_ID"] == "forum")
						{
							$arSearchParams["FORUM_ID"] = intval($_REQUEST["f_id"]);
							$arSearchParams["PATH_TO_GROUP_FORUM_MESSAGE"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#", 
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_FORUM_MESSAGE"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"] 
									: ""
							);
						}
						elseif ($arCommentEvent["EVENT_ID"] == "files_comment")
						{
							if (strlen($arLog["PARAMS"]) > 0)
							{
								$files_forum_id = 0;
								$arLogParams = explode("&", htmlspecialcharsback($arLog["PARAMS"]));
								foreach($arLogParams as $prm)
								{
									list($k, $v) = explode("=", $prm);
									if ($k == "forum_id")
									{
										$files_forum_id = $v;
										break;
									}
								}
							}
							$arSearchParams["FILES_FORUM_ID"] = $files_forum_id;
							$arSearchParams["PATH_TO_GROUP_FILES_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#", 
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_FILES_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"] 
									: ""
							);
						}
						elseif($arCommentEvent["EVENT_ID"] == "photo_comment")
						{
							if (strlen($arLog["PARAMS"]) > 0)
							{
								$photo_forum_id = 0;
								$arLogParams = unserialize(htmlspecialcharsback($arLog["PARAMS"]));
								if (
									is_array($arLogParams)
									&& array_key_exists("FORUM_ID", $arLogParams)
									&& intval($arLogParams["FORUM_ID"]) > 0
								)
									$photo_forum_id = $arLogParams["FORUM_ID"];
							}
							$arSearchParams["PHOTO_FORUM_ID"] = $photo_forum_id;
							$arSearchParams["PATH_TO_GROUP_PHOTO_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP 
									? str_replace(
										"#GROUPS_PATH#",
										COption::GetOptionString("socialnetwork", "workgroups_page", false, $site_id),
										$arLog["URL"]
									) 
									: ""
							);
							$arSearchParams["PATH_TO_USER_PHOTO_ELEMENT"] = (
								$arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER 
									? $arLog["URL"]
									: ""
							);
						}

						global $bxSocNetSearch;
						if (
							!empty($arSearchParams)
							&& !is_object($bxSocNetSearch)
						)
						{
							$bxSocNetSearch = new CSocNetSearch(
								($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER ? $arLog["ENTITY_ID"] : false), 
								($arLog["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? $arLog["ENTITY_ID"] : false),
								$arSearchParams
							);
							AddEventHandler("search", "BeforeIndex", Array($bxSocNetSearch, "BeforeIndex"));
						}

						$arAllow = array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"LOG_ANCHOR" => "N",
							"BIU" => "N",
							"IMG" => "N",
							"LIST" => "N",
							"QUOTE" => "N",
							"CODE" => "N",
							"FONT" => "N",
							"UPLOAD" => $arForum["ALLOW_UPLOAD"],
							"NL2BR" => "N",
							"SMILES" => "N"
						);

						$arFields = array(
							"ENTITY_TYPE" => $arLog["ENTITY_TYPE"],
							"ENTITY_ID" => $arLog["ENTITY_ID"],
							"EVENT_ID" => $arCommentEvent["EVENT_ID"],
							"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
							"MESSAGE" => $parser->convert($comment_text, array(), $arAllow),
							"TEXT_MESSAGE" => $comment_text,
							"MODULE_ID" => false,
							"LOG_ID" => $arLog["TMP_ID"],
							"USER_ID" => $GLOBALS["USER"]->GetID(),
							"PATH_TO_USER_BLOG_POST" => $arParams["PATH_TO_USER_BLOG_POST"],
							"PATH_TO_GROUP_BLOG_POST" => $arParams["PATH_TO_GROUP_BLOG_POST"],
							"PATH_TO_USER_MICROBLOG_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
							"PATH_TO_GROUP_MICROBLOG_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
							"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"]
						);

						$comment = CSocNetLogComments::Add($arFields, true);
						if (!is_array($comment) && intval($comment) > 0)
							$arResult["commentID"] = $comment;
						elseif (is_array($comment) &&  array_key_exists("MESSAGE", $comment) && strlen($comment["MESSAGE"]) > 0)
						{
							$arResult["strMessage"] = $comment["MESSAGE"];
							$arResult["commentText"] = $comment_text;
						}
					}
					else
						$arResult["strMessage"] = GetMessage("SONET_LOG_COMMENT_EMPTY");
				}
				else
					$arResult["strMessage"] = GetMessage("SONET_LOG_COMMENT_NO_PERMISSIONS");
			}
		}
	}
	elseif ($action == "get_comment")
	{
		$comment_id = $_REQUEST["cid"];

		if ($arComment = CSocNetLogComments::GetByID($comment_id))
		{
			$arResult["arComment"] = $arComment;

			$dateFormated = FormatDate(
				$GLOBALS['DB']->DateFormatToPHP(FORMAT_DATE),
				MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComment) ? $arComment["LOG_DATE_FORMAT"] : $arComment["LOG_DATE"])
			);

			$timeFormat = (isset($_REQUEST["dtf"]) ? $_REQUEST["dtf"] : CSite::GetTimeFormat());

			$timeFormated = FormatDateFromDB(
				(array_key_exists("LOG_DATE_FORMAT", $arComment) ? $arComment["LOG_DATE_FORMAT"] : $arComment["LOG_DATE"]),
				(stripos($timeFormat, 'a') || ($timeFormat == 'FULL' && IsAmPmMode()) !== false ? 'H:MI T' : 'HH:MI')
			);

			if (intval($arComment["USER_ID"]) > 0)
			{
				$arParams = array(
					"PATH_TO_USER" => $_REQUEST["p_user"],
					"NAME_TEMPLATE" => $_REQUEST["nt"],
					"SHOW_LOGIN" => $_REQUEST["sl"],
					"AVATAR_SIZE" => $_REQUEST["as"],
					"PATH_TO_SMILE" => $_REQUEST["p_smile"]
				);

				$arUser = array(
					"ID" => $arComment["USER_ID"],
					"NAME" => $arComment["~CREATED_BY_NAME"],
					"LAST_NAME" => $arComment["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" => $arComment["~CREATED_BY_SECOND_NAME"],
					"LOGIN" => $arComment["~CREATED_BY_LOGIN"],
					"PERSONAL_PHOTO" => $arComment["~CREATED_BY_PERSONAL_PHOTO"],
					"PERSONAL_GENDER" => $arComment["~CREATED_BY_PERSONAL_GENDER"],
				);
				$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
				$arCreatedBy = array(
					"FORMATTED" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, $bUseLogin),
					"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["USER_ID"], "id" => $arComment["USER_ID"]))
				);

			}
			else
				$arCreatedBy = array("FORMATTED" => GetMessage("SONET_C73_CREATED_BY_ANONYMOUS"));

			$arTmpCommentEvent = array(
				"LOG_DATE" => $arComment["LOG_DATE"],
				"LOG_DATE_FORMAT" => $arComment["LOG_DATE_FORMAT"],
				"LOG_DATE_DAY" => ConvertTimeStamp(MakeTimeStamp($arComment["LOG_DATE"]), "SHORT"),
				"LOG_TIME_FORMAT" => $timeFormated,
				"MESSAGE" => $arComment["MESSAGE"],
				"MESSAGE_FORMAT" => $arComment["~MESSAGE"],
				"CREATED_BY" => $arCreatedBy,
				"AVATAR_SRC" => CSocNetLogTools::FormatEvent_CreateAvatar($arUser, $arParams, ""),
				"USER_ID" => $arComment["USER_ID"]
			);

			$arEventTmp = CSocNetLogTools::FindLogCommentEventByID($arComment["EVENT_ID"]);
			if (
				$arEventTmp
				&& array_key_exists("CLASS_FORMAT", $arEventTmp)
				&& array_key_exists("METHOD_FORMAT", $arEventTmp)
			)
			{
				$arFIELDS_FORMATTED = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arComment, $arParams);
				$arTmpCommentEvent["MESSAGE_FORMAT"] = htmlspecialcharsback($arFIELDS_FORMATTED["EVENT_FORMATTED"]["MESSAGE"]);
			}

			$arResult["arCommentFormatted"] = $arTmpCommentEvent;
		}
	}
	elseif ($action == "get_comments")
	{
		$arResult["arComments"] = array();

		$log_tmp_id = $_REQUEST["logid"];

		if (intval($log_tmp_id) > 0)
		{
			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"AVATAR_SIZE_COMMENT" => $_REQUEST["as"],
				"PATH_TO_SMILE" => $_REQUEST["p_smile"]
			);

			$arFilter = array("LOG_ID" => $log_tmp_id);
			$arListParams = array("USE_SUBSCRIBE" => "N");

			$dbComments = CSocNetLogComments::GetList(
				array("LOG_DATE" => "ASC"),
				$arFilter,
				false,
				false,
				array(),
				$arListParams
			);

			while($arComments = $dbComments->GetNext())
				__SLGetLogCommentRecord($arComments, $arParams, false, false, $arTmpComments, false);

			$arResult["arComments"] = $arTmpComments;
		}
	}
	elseif ($action == "change_favorites" && $GLOBALS["USER"]->IsAuthorized())
	{
		$log_id = intval($_REQUEST["log_id"]);
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			if ($strRes = CSocNetLogFavorites::Change($GLOBALS["USER"]->GetID(), $log_id))
			{
				if ($strRes == "Y")
					CSocNetLogFollow::Set($GLOBALS["USER"]->GetID(), "L".$log_id, "Y");
				$arResult["bResult"] = $strRes;
			}
			else
			{
				if($e = $GLOBALS["APPLICATION"]->GetException())
					$arResult["strMessage"] = $e->GetString();
				else
					$arResult["strMessage"] = GetMessage("SONET_LOG_FAVORITES_CANNOT_CHANGE");
				$arResult["bResult"] = "E";
			}
		}
		else
		{
			$arResult["strMessage"] = GetMessage("SONET_LOG_FAVORITES_INCORRECT_LOG_ID");
			$arResult["bResult"] = "E";
		}
	}
	elseif ($action == "change_follow" && $GLOBALS["USER"]->IsAuthorized())
	{
		if ($strRes = CSocNetLogFollow::Set($GLOBALS["USER"]->GetID(), "L".intval($_REQUEST["log_id"]), ($_REQUEST["follow"] == "Y" ? "Y" : "N")))
			$arResult["SUCCESS"] = "Y";
		else
			$arResult["SUCCESS"] = "N";
	}
	elseif ($action == "get_more_destination")
	{
		$arResult["arDestinations"] = false;
		$log_id = intval($_REQUEST["log_id"]);
		$iDestinationLimit = intval($_REQUEST["dlim"]);

		if ($log_id > 0)
		{
			$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $log_id));
			while ($arRight = $dbRight->Fetch())
				$arRights[] = $arRight["GROUP_CODE"];

			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"PATH_TO_GROUP" => $_REQUEST["p_group"],
				"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"DESTINATION_LIMIT" => 100
			);

			$arDestinations = CSocNetLogTools::FormatDestinationFromRights($arRights, $arParams, $iMoreCount);
			if (is_array($arDestinations))
				$arResult["arDestinations"] = array_slice($arDestinations, $iDestinationLimit);
		}
	}
	elseif ($action == "get_more_destination")
	{
		$arResult["arDestinations"] = false;
		$log_id = intval($_REQUEST["log_id"]);
		$iDestinationLimit = intval($_REQUEST["dlim"]);

		if ($log_id > 0)
		{
			$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $log_id));
			while ($arRight = $dbRight->Fetch())
				$arRights[] = $arRight["GROUP_CODE"];

			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"PATH_TO_GROUP" => $_REQUEST["p_group"],
				"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"DESTINATION_LIMIT" => 100
			);

			$arDestinations = CSocNetLogTools::FormatDestinationFromRights($arRights, $arParams, $iMoreCount);
			if (is_array($arDestinations))
				$arResult["arDestinations"] = array_slice($arDestinations, $iDestinationLimit);
		}
	}

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>