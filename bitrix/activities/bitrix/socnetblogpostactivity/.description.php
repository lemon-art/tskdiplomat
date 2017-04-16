<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("SNBPA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("SNBPA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "SocnetBlogPostActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
);
?>