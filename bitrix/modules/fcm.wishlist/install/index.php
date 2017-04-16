<?
IncludeModuleLangFile(__FILE__);
Class fcm_wishlist extends CModule
{
	const MODULE_ID = 'fcm.wishlist';
	var $MODULE_ID = 'fcm.wishlist'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("fcm.wishlist_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("fcm.wishlist_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("fcm.wishlist_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("fcm.wishlist_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_fcm_wishlist WHERE 1=0", true))
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/db/".strtolower($DB->type)."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			//RegisterModule(self::MODULE_ID);
			//CModule::IncludeModule(self::MODULE_ID);

			//COption::SetOptionString("vote", "VOTE_DIR", "");
			//COption::SetOptionString("vote", "VOTE_COMPATIBLE_OLD_TEMPLATE", "N");

			//RegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", 100, "/modules/".self::MODULE_ID."/keepvoting.php");
			//RegisterModuleDependences("main", "OnUserTypeBuildList", "vote", "CUserTypeVote", "GetUserTypeDescription", 200);
			//RegisterModuleDependences("main", "OnUserLogin", "vote", "CVoteUser", "OnUserLogin", 200);

			//RegisterModuleDependences("im", "OnGetNotifySchema", "vote", "CVoteNotifySchema", "OnGetNotifySchema");
                        
                        RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CFcmWishlist', 'OnBuildGlobalMenu');

			return true;
		}
                
	}

	function UnInstallDB($arParams = array())
	{
            		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		//delete agents
		CAgent::RemoveModuleAgents(self::MODULE_ID);

		$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = '".self::MODULE_ID."'");
		while($arRes = $db_res->Fetch())
			CFile::Delete($arRes["ID"]);

		// Events
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/events/del_events.php");

		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CFcmWishlist', 'OnBuildGlobalMenu');
		//UnRegisterModule(self::MODULE_ID);

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		return true;
	}

	function InstallEvents()
	{
		global $DB;
		$sIn = "'FCM_WISHLIST'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		$sIn = "'FCM_WISHLIST'";
		$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
                
         	$bReWriteAdditionalFiles = (($GLOBALS["public_rewrite"] == "Y") ? True : False);

		if($GLOBALS["install_public"] == "Y" && !empty($GLOBALS["public_dir"]))
		{
			$sites = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
			while($site = $sites->Fetch())
			{
				if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/public/".$site["LANGUAGE_ID"]))
					CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/public/".$site["LANGUAGE_ID"], $site['ABS_DOC_ROOT'].$site["DIR"].$GLOBALS["public_dir"], $bReWriteAdditionalFiles, true);
			}
		}

                
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
