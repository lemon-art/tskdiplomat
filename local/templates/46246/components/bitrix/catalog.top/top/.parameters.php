<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"BLOCK_TITLE" => Array(
		"NAME" => GetMessage("T_IBLOCK_DESC_BLOCK_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => "-",
	),
	"PREVIEW_IMAGE_WIDTH" => Array(
		"NAME" => GetMessage("T_IBLOCK_DESC_PREVIEW_IMAGE_WIDTH"),
		"TYPE" => "STRING",
		"DEFAULT" => "220",
	),
	"PREVIEW_IMAGE_HEIGHT" => Array(
		"NAME" => GetMessage("T_IBLOCK_DESC_PREVIEW_IMAGE_HEIGHT"),
		"TYPE" => "STRING",
		"DEFAULT" => "220",
	),
);
?>
