<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

$arClassesList = array(
    "BxmodSeo" => "classes/mysql/BxmodSeo.php",
);

$moduleId = "bxmod.seo";

if (method_exists(CModule, "AddAutoloadClasses"))
{
    CModule::AddAutoloadClasses( $moduleId, $arClassesList );
}
else
{
    foreach ($arClassesList as $sClassName => $sClassFile)
    {
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$moduleId}/{$sClassFile}");
    }
}?>