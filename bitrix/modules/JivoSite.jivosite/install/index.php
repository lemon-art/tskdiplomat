<?
IncludeModuleLangFile(__FILE__);

Class JivoSite_jivosite extends CModule
{
	var $MODULE_ID = "JivoSite.jivosite";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	
	function JivoSite_jivosite()
	{
		$this->MODULE_ID = "JivoSite.jivosite"; 
		$this->MODULE_NAME = GetMessage("MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MOD_DESCR");
		$this->PARTNER_NAME = GetMessage("PARTNER_NAME");//"JivoSite";
		$this->PARTNER_URI = "http://www.jivosite.ru";
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
	}

    function DoInstall()
    {
		global $APPLICATION, $step;
		
		$step = IntVal($step);
		
		if (!$step){
			
			$APPLICATION->IncludeAdminFile(
				GetMessage("STEP1"),/*"Установка JivoSite - Шаг 1 из 2"*/
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/JivoSite.jivosite/install/step1.php"
			);
		}else if ($step == 2){
			$APPLICATION->IncludeAdminFile(
				GetMessage("STEP2"),/*"Установка JivoSite - Шаг 2 из 2"*/
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/JivoSite.jivosite/install/step2.php"
			);
		}
    }

    function DoUninstall()
    {
		UnRegisterModuleDependences("main", "OnPageStart", "JivoSite.jivosite","JivoSiteClass", "addScriptTag");
		COption::RemoveOption("JivoSite.jivosite");
		UnRegisterModule("JivoSite.jivosite");
    }
}
?>
