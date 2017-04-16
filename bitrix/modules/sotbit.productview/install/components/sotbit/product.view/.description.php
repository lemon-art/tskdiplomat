<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SBT_PRODUCTVIEW_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SBT_PRODUCTVIEW_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_rec.gif",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"),
			"SORT" => 350,
			"CHILD" => array(
				"ID" => "sotbit_analog_products",
			),
		),
	),
);
?>