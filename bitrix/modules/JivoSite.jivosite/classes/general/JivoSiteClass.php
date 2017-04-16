<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/JivoSite.jivosite/config.php');

class JivoSiteClass{
	
	public function addScriptTag(){
		global $APPLICATION;
		$widget_id = COption::GetOptionString("JivoSite.jivosite", "widget_id");
		$APPLICATION->AddHeadScript("//".JIVO_CODE_URL."/script/widget/$widget_id");
	}
	
}

?>