<?php
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

Class bxmod_seo extends CModule
{
    var $MODULE_ID = "bxmod.seo";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    function bxmod_seo()
    {
        $arModuleVersion = array();

        $path = str_replace( "\\", "/", __FILE__ );
        $path = substr( $path, 0, strlen($path) - strlen("/index.php") );
        include( $path."/version.php" );

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        
        $this->PARTNER_NAME = GetMessage("BXMOD_SEO_PARTNER_NAME");
        $this->PARTNER_URI = "http://bxmod.ru";

        $this->MODULE_NAME = GetMessage("BXMOD_SEO_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("BXMOD_SEO_MODULE_DESCRIPTION");
    }
    
    function InstallDB()
    {
        global $DB;
        
        $DB->Query("DROP TABLE IF EXISTS `bxmod_seo`");
        
        $DB->Query("CREATE TABLE `bxmod_seo` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `PARENT_ID` int(11) DEFAULT NULL COMMENT 'ID родителя',
          `ACTIVE` char(1) CHARACTER SET utf8 DEFAULT 'Y',
          `KEY` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Ключевая фраза',
          `SEO_TEXT` text CHARACTER SET utf8 COMMENT 'SEO текст',
          `META_KEYS` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Ключевые слова',
          `META_DESC` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Описание',
          `TITLE` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Тег Title',
          `H1` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Тег H1',
          `URL` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'URL страницы',
          `SORT` int(11) DEFAULT NULL COMMENT 'Сортировка',
          PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=1;");
        
        RegisterModule($this->MODULE_ID);
        
        return true;
    }
    
    function UnInstallDB()
    {
        global $DB;
        
        $DB->Query("DROP TABLE IF EXISTS `bxmod_seo`");
        
        UnRegisterModule($this->MODULE_ID);
        
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/". $this->MODULE_ID ."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/". $this->MODULE_ID ."/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/". $this->MODULE_ID ."/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/js/bxmod.seo");
        DeleteDirFilesEx("/bitrix/admin/bxmod_seo_core.php");
        DeleteDirFilesEx("/bitrix/admin/bxmod_seo_edit.php");
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        if (!IsModuleInstalled($this->MODULE_ID))
        {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
        }
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }
}
?>