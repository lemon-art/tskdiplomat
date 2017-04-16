<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @global CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

if(isset($_REQUEST['back_url']))
{
	$backUrl = urldecode($_REQUEST["back_url"]);
}
else
{
	$backUrl = $arResult["FOLDER"];
	$backUrl .= CComponentEngine::MakePathFromTemplate(
		$arResult["URL_TEMPLATES"]["list"],
		array(
			"list_id" => $arResult["VARIABLES"]["list_id"],
			"section_id" => 0,
		)
	);
}
if(!preg_match('#^(?:/|\?|https?://)(?:\w|$)#D', $backUrl))
	$backUrl = '#';

$buttons = array(
	array(
		"TEXT" => GetMessage("CT_BL_LIST_GO_BACK"),
		"TITLE" => GetMessage("CT_BL_LIST_GO_BACK"),
		"LINK" => $backUrl,
		"ICON" => "btn-list",
	),
);
$APPLICATION->includeComponent(
	"bitrix:main.interface.toolbar",
	"",
	array("BUTTONS" => $buttons),
	$component
);

if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
}
else
{
	$moduleId = "lists";
	$entity = 'Bitrix\Lists\BizprocDocumentLists';
}
$APPLICATION->IncludeComponent("bitrix:bizproc.log", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"COMPONENT_VERSION" => 2,
	"ID" => $arResult["VARIABLES"]["document_state_id"],
	),
	$component
);