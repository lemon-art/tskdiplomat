<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

	$dir = $APPLICATION->GetCurDir();
	if ($dir !== $arResult["SEF_FOLDER"]) {
		@define("ERROR_404","Y");
	}
$APPLICATION->IncludeComponent(
	"bitrix:sale.personal.order.list",
	"list",
	array(
		"PATH_TO_DETAIL" => $arResult["PATH_TO_DETAIL"],
		"PATH_TO_CANCEL" => $arResult["PATH_TO_CANCEL"],
		"PATH_TO_COPY" => $arResult["PATH_TO_LIST"].'?ID=#ID#',
		"PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
		"SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
		"ORDERS_PER_PAGE" => 0,
		"SET_TITLE" =>$arParams["SET_TITLE"],
		"ID" => $arResult["VARIABLES"]["ID"],
		"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
	),
	$component
);
?>
