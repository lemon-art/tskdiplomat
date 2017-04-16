<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("BXMOD_SEO_SEO_NAME"),
    "DESCRIPTION" => GetMessage("BXMOD_SEO_SEO_DESC"),
    "ICON" => "/images/catalog.gif",
    "SORT" => 10,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "content",
        "CHILD" => array(
            "ID" => "bxmod_seo",
            "NAME" => GetMessage("BXMOD_SEO_SEO_GROUP"),
            "SORT" => 10
        ),
    ),
);
?>