<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

AddEventHandler("main", "OnBuildGlobalMenu", "global_menu_bxmod_seo_menu");

function global_menu_bxmod_seo_menu(&$aGlobalMenu, &$aModuleMenu) {
    $aModuleMenu[] = array(
        "parent_menu" => "global_menu_services", 
        "icon" => "iblock_menu_icon_types",
        "page_icon" => "default_page_icon",
        "text" => GetMessage("BXMOD_SEO_MENU_TEXT"),
        "url" => "/bitrix/admin/bxmod_seo_core.php?lang=" . LANGUAGE_ID,
        "more_url" => array(
            "/bitrix/admin/bxmod_seo_edit.php"
        )
    );
}
return false;
?>