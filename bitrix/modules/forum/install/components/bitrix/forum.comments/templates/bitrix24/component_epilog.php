<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($_REQUEST["AJAX_POST"] == "Y" && $_REQUEST["ENTITY_XML_ID"] == $arParams["ENTITY_XML_ID"] &&
	!empty($arResult["OUTPUT_LIST"]["JSON"]))
{
	if (!function_exists("__fcParseAnswer"))
	{
		function __fcParseAnswer(&$output, $arParams, $arResult)
		{
			$GLOBALS["APPLICATION"]->RestartBuffer();
			while(ob_end_clean());
			echo CUtil::PhpToJSObject($arResult["OUTPUT_LIST"]["JSON"]);
			die();
		}
	}
	AddEventHandler('forum', 'OnCommentsDisplayTemplate', __fcParseAnswer);
}
?>