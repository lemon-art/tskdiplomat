<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Если не подключен модуль, то прекращаем
if( !CModule::IncludeModule("bxmod.seo") ) {
    return false;
}

// URL для поиска ключа
$arParams["URL"] = $_SERVER["REQUEST_URI"];

// Ищем ключ
$arResult["SEO"] = BxmodSeo::FindKey( $arParams["URL"] );

// Если не нашли - отменяем кеширование
if ( !$arResult["SEO"] ) {
    return false;
}

// Тег H1 выставляем в зависимости от заполненности полей
$arResult["SEO"]["H1"] = strlen( $arResult["SEO"]["H1"] ) > 0 ? $arResult["SEO"]["H1"] : $arResult["SEO"]["TITLE"];
if ( strlen( $arResult["SEO"]["H1"] ) < 1 ) $arResult["SEO"]["H1"] = $arResult["SEO"]["KEY"];

// Тег TITLE выставляем в зависимости от заполненности полей
$arResult["SEO"]["TITLE"] = strlen( $arResult["SEO"]["TITLE"] ) > 0 ? $arResult["SEO"]["TITLE"] : $arResult["SEO"]["H1"];
if ( strlen( $arResult["SEO"]["TITLE"] ) < 1 ) $arResult["SEO"]["TITLE"] = $arResult["SEO"]["KEY"];

// Устанавливаем заголовок страницы
$APPLICATION->SetPageProperty("title", $arResult["SEO"]["TITLE"]);

// Значения для отложенной установки тегов
if ( strlen( $arResult["SEO"]["TITLE"] ) > 0 && !defined( "BXMOD_SEO_TAG_TITLE" ) ) {
    define("BXMOD_SEO_TAG_TITLE", $arResult["SEO"]["TITLE"]);
}

// Устанавливаем Keywords
if ( strlen( $arResult["SEO"]["META_KEYS"] ) > 0 ) {
    // Значения для отложенной установки тегов
    if ( !defined( "BXMOD_SEO_TAG_KEYWORDS" ) ) {
        define("BXMOD_SEO_TAG_KEYWORDS", $arResult["SEO"]["META_KEYS"]);
    }
}

// Устанавливаем Description
if ( strlen( $arResult["SEO"]["META_DESC"] ) > 0 ) {
    // Значения для отложенной установки тегов
    if ( !defined( "BXMOD_SEO_TAG_DESCRIPTION" ) ) {
        define("BXMOD_SEO_TAG_DESCRIPTION", $arResult["SEO"]["META_DESC"]);
    }
}

// Ищем ссылки на корневой раздел, родительский раздел и все дочерние разделы
$arResult["LINKS"] = BxmodSeo::FindLinks( $arResult["SEO"]["ID"] );
// Текст ссылок выставляем в зависимости от заполненности полей
foreach ( $arResult["LINKS"] AS $k => $v ) {
    // если ссылка на страницу пуста, то не используем ее
    if( strlen( $arResult["LINKS"][$k]["URL"] ) < 1 ) {
        unset( $arResult["LINKS"][$k] );
        continue;
    }

    $arResult["LINKS"][$k]["TITLE"] = strlen( $v["TITLE"] ) > 0 ? $v["TITLE"] : $v["H1"];
    if ( strlen( $v["H1"] ) < 1 ) $arResult["LINKS"][$k]["TITLE"] = $v["KEY"];

    // ищем ключевую фразу ссылки в тексте
    if ( mb_stripos( $arResult["SEO"]["SEO_TEXT"], $arResult["LINKS"][$k]["TITLE"], NULL, SITE_CHARSET ) !== false ) {

        // Перекодировка на случай не utf-8 сайтов
        if ( mb_strpos( SITE_CHARSET, "1251", NULL, SITE_CHARSET ) !== false ) {
            $arResult["SEO"]["SEO_TEXT"] = iconv("cp1251", "utf-8", $arResult["SEO"]["SEO_TEXT"]);
            $arResult["LINKS"][$k]["TITLE"] = iconv("cp1251", "utf-8", $arResult["LINKS"][$k]["TITLE"]);
        }

        // если нашли, то заменяем строку в тексте на ссылку
        $arResult["SEO"]["SEO_TEXT"] = preg_replace("/(". $arResult["LINKS"][$k]["TITLE"] .")/iu", '<a href="'. $arResult["LINKS"][$k]["URL"] .'" title="'. $arResult["LINKS"][$k]["TITLE"] .'">\\1</a>', $arResult["SEO"]["SEO_TEXT"], 1);

        // Перекодировка на случай не utf-8 сайтов
        if ( mb_strpos( SITE_CHARSET, "1251", NULL, SITE_CHARSET ) !== false ) {
            $arResult["SEO"]["SEO_TEXT"] = iconv("utf-8", "cp1251", $arResult["SEO"]["SEO_TEXT"]);
        }

        // и удаляем из списка ссылок текущую, т.к. она уже будет выводиться в тексте
        unset( $arResult["LINKS"][$k] );
    }
}

$this->IncludeComponentTemplate();
?>