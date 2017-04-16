<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "Calculator",
	"DESCRIPTION" => "",
	"ICON" => "/images/news_all.gif",
	"SORT" => 50,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "calculator",
			"NAME" => 'Calculator',
			"SORT" => 10,
		)
	),
);

?>