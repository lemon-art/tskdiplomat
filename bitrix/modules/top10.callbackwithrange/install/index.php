<?
IncludeModuleLangFile(__FILE__);
Class top10_callbackwithrange extends CModule {
	const MODULE_ID = 'top10.callbackwithrange';
	var $MODULE_ID = 'top10.callbackwithrange'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct() {
		$arModuleVersion = Array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION		= $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE	= $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME			= GetMessage("top10.callbackwithrange_MODULE_NAME");
		$this->MODULE_DESCRIPTION	= GetMessage("top10.callbackwithrange_MODULE_DESC");

		$this->PARTNER_NAME			= GetMessage("top10.callbackwithrange_PARTNER_NAME");
		$this->PARTNER_URI			= GetMessage("top10.callbackwithrange_PARTNER_URI");
	}

	function InstallDB($arParams = Array())		{ return true; }
	function UnInstallDB($arParams = Array())	{ return true; }
	function InstallEvents() {
		// Part 1
		$arEventTypes = Array();
		
		$arEventTypes[] = Array(
			"LID"			=> "ru",
			"EVENT_NAME"	=> "TOP10_CALLBACK",
			"NAME"			=> GetMessage("TOP10_EVENT_CALLBACK_NAME"),
			"DESCRIPTION"	=> GetMessage("TOP10_EVENT_CALLBACK_DESCR"),
			"SORT"			=> 200
		);
		
		$type = new CEventType;
		foreach ($arEventTypes as $arEventType) {
			$type->Add($arEventType);
		}
		
		// Part 2
		$arMessages = Array();
		
		$arMessages[] = Array(
			"EVENT_NAME"	=> "TOP10_CALLBACK",
			"LID"			=> "s1",
			"EMAIL_FROM"	=> "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO"		=> "#EMAIL_TO#",
			"SUBJECT"		=> "#EMAIL_SUBJECT#",
			"MESSAGE"		=> "#MESSAGE#"
		);
		
		$message = new CEventMessage;
		foreach ($arMessages as $arMessage) {
			if($iMessageTplID = $message->Add($arMessage)) {
			
			} else {
				// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt',  $message->LAST_ERROR."\r\n", FILE_APPEND);
			}
		}
	
		return true;
	}
	
	function UnInstallEvents() {
		
	
		return true;
	}

	function InstallFiles($arParams = Array()) {
		if(is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components')) {
			if($dir = opendir($p)) {
				while(false !== $item = readdir($dir)) {
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		
		// COPY DESCRIPTION FILE
		$filePlusSource	= $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/sect_top10_callback_plus.php';
		$filePlus		= $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/sect_top10_callback_plus.php';
		
		if(file_exists($filePlusSource) && !file_exists($filePlus)) {
			$sFilePlusDestination = $_SERVER["DOCUMENT_ROOT"].SITE_DIR.'/sect_top10_callback_plus.php';
			CopyDirFiles($filePlusSource, $sFilePlusDestination);
			
			if(LANG_CHARSET == "UTF-8") {
				$sSectContent		= file_get_contents($sFilePlusDestination);
				$sSectContentInUTF8	= mb_convert_encoding($sSectContent, "UTF-8", "Windows-1251");
				file_put_contents($sFilePlusDestination, $sSectContentInUTF8);
			}
		}
		
		// clear cache of components tree
		$this->clearComponentsTreeCache();
		
		return true;
	}

	function UnInstallFiles() {
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components')) {
			if ($dir = opendir($p)) {
				while (false !== $item = readdir($dir)) {
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
		
		if(file_exists($filePlus = $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/sect_top10_callback_plus.php')) {
			unlink($filePlus);
		}
		
		// clear cache of components tree
		$this->clearComponentsTreeCache();
		
		return true;
	}

	function DoInstall() {
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall() {
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallEvents();
		$this->UnInstallFiles();
	}
	
	function clearComponentsTreeCache() {
		$arDirToDel = Array(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/managed_cache/MYSQL/fileman_component_tree",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/managed_cache/MYSQL/fileman_component_tree_array"
		);
		
		foreach($arDirToDel as $dir) {
			DeleteDirFilesEx($dir);
		}
		
		return true;
	}
}
?>