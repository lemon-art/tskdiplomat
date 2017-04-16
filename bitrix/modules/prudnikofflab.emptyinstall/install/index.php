<?
global $MESS;
IncludeModuleLangFile(str_replace("\\", "/", __FILE__));

if(class_exists('prudnikofflab_emptyinstall'))
	return;

class prudnikofflab_emptyinstall extends CModule
{
	var $MODULE_ID = 'prudnikofflab.emptyinstall';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	
	function prudnikofflab_emptyinstall()
	{
		$arModuleVersion = array();
		
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - 10);
        include($path.'/version.php');
        
        $this->MODULE_NAME = GetMessage('PRUDNIKOFFLAB_EMPTYINSTALL_NAME'); 
        $this->MODULE_DESCRIPTION = GetMessage("PRUDNIKOFFLAB_EMPTYINSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage('PRUDNIKOFFLAB');
		$this->PARTNER_URI = GetMessage('PRUDNIKOFFLAB_URI'); 

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
	}

	function DoInstall()
	{
		$this->InstallFiles();
		RegisterModule($this->MODULE_ID);
	}
	
	function InstallEvents()
	{
		return true;
	}
	
	function InstallFiles()
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/wizards/prudnikofflab/empty_install', $_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/prudnikofflab/empty_install', true, true);
		return true;
	}
	
	function UnInstallEvents()
	{
		return true;
	}
	
 	function InstallDB()
    {
        return true;
    }
    
    function InstallPublic()
	{
		return true;
	}
	
	function UnInstallDB()
	{
	}
	
	function UnInstallFiles()
	{
		return true;
	}

	function DoUninstall()
	{
		DeleteDirFilesEx('/bitrix/wizards/prudnikofflab/empty_install');
		UnRegisterModule($this->MODULE_ID);
	}
}
?>