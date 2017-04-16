<?

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);
Class sotbit_seometa extends CModule
{
	const MODULE_ID = 'sotbit.seometa';
	var $MODULE_ID = 'sotbit.seometa';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("SOTBIT_SEOMETA_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("SOTBIT_SEOMETA_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("SOTBIT_SEOMETA_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("SOTBIT_SEOMETA_PARTNER_URI");
	}

	function InstallEvents()
	{
		RegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlGroup", "GetControlDescr");
		RegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlIBlockFields", "GetControlDescr");
		RegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlIBlockProps", "GetControlDescr");
		RegisterModuleDependences("iblock", "OnTemplateGetFunctionClass", self::MODULE_ID, "CSeoMetaTags", "Event");
		RegisterModuleDependences("iblock", "OnTemplateGetFunctionClassHandler", self::MODULE_ID, "CSeoMetaTags", "EventHandler");

		$Sites = array();
		$rsSites = CSite::GetList( $by = "sort", $order = "desc", Array(
				"ACTIVE" => "Y"
				) );

		while( $arSite = $rsSites->Fetch() )
		{
			COption::SetOptionString("sotbit.seometa","NO_INDEX_".$arSite['LID'],"N");
		}



		return true;
	}

	function UnInstallEvents()
	{
		UnRegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlGroup", "GetControlDescr");
		UnRegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlIBlockFields", "GetControlDescr");
		UnRegisterModuleDependences(self::MODULE_ID, "OnCondCatControlBuildListSM", self::MODULE_ID, "SMCatalogCondCtrlIBlockProps", "GetControlDescr");
		UnRegisterModuleDependences("iblock", "OnTemplateGetFunctionClass", self::MODULE_ID, "CSeoMetaTags", "Event");
		UnRegisterModuleDependences("iblock", "OnTemplateGetFunctionClassHandler", self::MODULE_ID, "CSeoMetaTags", "EventHandler");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true);
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

		//CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::MODULE_ID."/install/components/sotbit/sotbit.seometa", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/sotbit/sotbit.seometa", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/themes/.default/icons/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default/icons");
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
		//DeleteDirFilesEx("/bitrix/components/sotbit/sotbit.seometa");
		return true;
	}
	function installDB()
	{
		global $DB, $APPLICATION;
		$DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/db/'.strtolower($DB->type).'/install.sql');
	}
	function UnInstallDB()
	{
		global $DB, $APPLICATION;
		$DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/db/'.strtolower($DB->type).'/uninstall.sql');
	}
	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();

		ModuleManager::registerModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		ModuleManager::unRegisterModule(self::MODULE_ID);
		$this->UnInstallFiles();
		$this->UnInstallDB();
		$this->UnInstallEvents();
	}
}
?>
