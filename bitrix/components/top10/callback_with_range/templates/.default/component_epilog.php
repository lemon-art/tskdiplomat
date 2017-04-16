<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetAdditionalCSS($templateFolder."/css/magnific-popup.css");
$APPLICATION->SetAdditionalCSS($templateFolder."/css/jquery-ui.min.css");

// Include js
$arJS = Array();
if($arParams["SCRIPT_JQ"] == "Y")
	$arJS[] = $templateFolder."/js/jquery-1.9.1.min.js"; //

if($arParams["SCRIPT_JQUI"] == "Y")
	$arJS[] = $templateFolder."/js/jquery-ui.min.js";


$arJS[] = $templateFolder."/js/magnific.js";
$arJS[] = $templateFolder."/js/slider-time-range-init.js";

// If include js in body
if($arParams["SCRIPT_BODY"] == "Y") {
	foreach($arJS as $sScript) {
		echo '<script type="text/javascript" src="'.$sScript.'"></script>';
	}
} else {
	foreach($arJS as $sScript) {
		$APPLICATION->AddHeadScript($sScript, true);
	}
}