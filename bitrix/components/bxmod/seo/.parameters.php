<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "BXMOD_SEO_SEO_PARTSTITLE" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("BXMOD_SEO_SEO_PARTSTITLE"),
            "TYPE" => "STRING",
            "DEFAULT" => GetMessage("BXMOD_SEO_SEO_PARTSTITLE_DEFAULT"),
            "SORT" => 10
        ),
        "CACHE_TIME"  =>  Array("DEFAULT"=>36000000)
    )
);
?>