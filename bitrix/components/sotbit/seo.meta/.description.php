<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); 
$arComponentDescription = array(
	"NAME" => GetMessage("SM_NAME"),
	"DESCRIPTION" => GetMessage("SM_DESCRIPTION"),
	"ICON" => "/images/icon.png",
	"SORT" => 70,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("SM_CATALOG"),
			"SORT" => 362,
		),
	),
);
?>