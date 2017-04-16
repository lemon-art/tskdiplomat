<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => 'Price to Excel',
	"DESCRIPTION" => 'create Excel price',
	"ICON" => "/images/search_page.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "fcm",
			"NAME" => 'ForteCom componrnts'
		)
	),
);

?>