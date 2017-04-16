<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
IncludeTemplateLangFile(__FILE__);
CUtil::InitJSCore();
$curPage = $APPLICATION->GetCurPage(true);

$APPLICATION->AddHeadScript('/bitrix/js/jq/jquery-1.7.2.min.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/jquery.easing.js');
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/js/tools.js');

$APPLICATION->AddHeadScript('/bitrix/js/jq/ajax-cart.js');
//$APPLICATION->AddHeadScript('/bitrix/js/jq/modernizr-2.5.3.min.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/autocomplete/jquery.autocomplete.js');
?>
<?
if (!preg_match('#\/$#', $_SERVER['REQUEST_URI']) && !strpos($_SERVER['REQUEST_URI'], ".") && !strpos($_SERVER['REQUEST_URI'], "?") && $_SERVER['REQUEST_URI'] != '/') {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "/");
    exit();
}
if (preg_match('#\?PAGEN_1=1$#', $_SERVER['REQUEST_URI'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://" . $_SERVER['HTTP_HOST'] . str_replace('?PAGEN_1=1', '', $_SERVER['REQUEST_URI']));
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="google-site-verification" content="0nd8pzhjE4CUfeyKPCofWB_IWF2u4CHdslTPZgMYIXE" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />

        <?
        if (isset($_GET['sort_order'])) {
            $tmp = explode('?', $_SERVER['REQUEST_URI']);
            echo '<link rel="canonical" href="http://' . $_SERVER['HTTP_HOST'] . $tmp[0] . '" />';
        }
        ?>


        <? $APPLICATION->ShowHead(); ?>
        <? if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") && !strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 10.0")): ?>
            <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/ie.css"/>
        <? endif ?>
        <title><? $APPLICATION->ShowTitle() ?></title>
        <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/lib/font-awesome/css/font-awesome.min.css" />
        <script src="<?= SITE_TEMPLATE_PATH ?>/lib/jquery.maskedinput-1.3.min.js"></script>
        <? /*
          <script src="http://code.jquery.com/jquery-migrate-1.1.1.js"></script>
         */ ?>
        <script type="text/javascript">
            var baseDir = 'http://<?= SITE_SERVER_NAME ?>/ajax_cart/';
            var baseUri = 'http://<?= SITE_SERVER_NAME ?>/ajax_cart/index.php';
            var static_token = '';
            var token = '';
            var priceDisplayPrecision = 2;
            var priceDisplayMethod = 1;
            var roundMode = 2;
        </script>
        <script type="text/javascript">
            jQuery(function () {
                jQuery(".mask").mask("8 (999) 999-9999");
            });
			$(document).ready(function() {
	
	$("a.fancy").fancybox();
});
        </script>
        <script type="text/javascript">
            var __cs = __cs || [];
            __cs.push(["setAccount", "RsV2xUZ56NtiXruYgzGWiClX4wN5giD7"]);
            __cs.push(["setHost", "//server.comagic.ru/comagic"]);
        </script>
        <script type="text/javascript" async src="//app.comagic.ru/static/cs.min.js"></script>

    </head>
    <body>
        <?php
        if (!$is404):
            ?>
            <div id="modal_window_wrapper">
                <div id="modal_window">
                </div>
            </div>
            <div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

            <?
            $APPLICATION->IncludeComponent(
                    "bitrix:main.include", ".default", Array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => SITE_DIR . "include/counters.php",
                "AREA_FILE_RECURSIVE" => "N",
                "EDIT_MODE" => "html",
                    ), false, Array('HIDE_ICONS' => 'N')
            );
            ?>

            <div id="wrapper1">
                <div id="wrapper2">
                    <div id="wrapper3" class="clearfix">
                        <header id="header" class="clearfix">


                            <a id="header_logo" href="/">
                                <img class="logo" src="/images/logo.png" alt="ТСК Дипломат  – оптовая и розничная продажа строительных материалов" title="ТСК Дипломат  – оптовая и розничная продажа строительных материалов" />
                            </a>
                            <div class="head-bg">
                                <div id="phones_block">
                                    <i class="fa fa-clock-o"></i><span>пн-вс, 09:00-18:00</span><br/>
                                    <i class="fa fa-map-marker"></i><span>115114 г. Москва, ул. Дербеневская, д. 24, стр. 3</span><br/>
                                    <i class="fa fa-phone"></i><span><strong>+7 (495) 663 71 82</strong> (многоканальный)</span>
                                    <i class="fa fa-phone"></i><span><strong>+7 (495) 956 71 20</strong></span><br/>
                                    <i class="fa fa-envelope"></i><a href="mailto:sales@tskdiplomat.ru">sales@tskdiplomat.ru</a>
                                    <span class="dop_info">Минимальный заказ 2000 рублей</span>
                                </div>
    <? /*
      <div id="languages_block_top">
      <ul id="first-languages">
      <li class="selected_language">
      en
      </li>
      <li>
      <a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=4" title="Español (Spanish)">
      es
      </a>
      </li>
      <li>
      <a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=5" title="Français (French)">
      fr
      </a>
      </li>
      </ul>
      </div>
     */ ?>
                                <? /*
                                  <div id="currencies_block_top">
                                  <form id="setCurrency" action="./SP NGDL H4 - SPARE PARTS_files/SP NGDL H4 - SPARE PARTS.htm" method="post" class="jqtransformdone">
                                  <label style="cursor: pointer;">Currency:</label>
                                  <div class="jqTransformSelectWrapper" style="z-index: 10; width: 110px;"><div><span style="width: 59px;">Dollar</span><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=1#" class="jqTransformSelectOpen"></a></div><ul style="width: 108px; display: none; visibility: visible;"><li><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=1#" index="0" class="selected">Dollar</a></li><li><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=1#" index="1">Euro</a></li></ul><select onchange="setCurrency(this.value);" class="jqTransformHidden" style="">
                                  <option value="1" selected="selected">Dollar</option>
                                  <option value="2">Euro</option>
                                  </select></div>
                                  <input type="hidden" name="id_currency" id="id_currency" value="">
                                  <input type="hidden" name="SubmitCurrency" value="">
                                  </form>
                                  </div>
                                 */ ?>
                                <?
                                $APPLICATION->IncludeComponent("bitrix:sale.basket.basket.small", ".default", Array(
                                    "PATH_TO_BASKET" => SITE_DIR . "personal/cart/", // Страница корзины
                                    "PATH_TO_PERSONAL" => SITE_DIR . "personal/", // Персональный раздел
                                    "SHOW_PERSONAL_LINK" => "N", // Отображать ссылку на персональный раздел
                                    "AJAX_MODE" => "Y",
                                        ), false
                                );
                                ?>
                                <div class="head-compare">
                                    <?
                                    $APPLICATION->IncludeComponent("bitrix:catalog.compare.list", "compare_mod", Array(
                                        "AJAX_MODE" => "Y", // Включить режим AJAX
                                        "IBLOCK_TYPE" => "catalog", // Тип инфоблока
                                        "IBLOCK_ID" => "3", // Инфоблок
                                        "POSITION_FIXED" => "Y",
                                        "POSITION" => "top left",
                                        "DETAIL_URL" => "/compare", // URL, ведущий на страницу с содержимым элемента раздела
                                        "COMPARE_URL" => "compare", // URL страницы с таблицей сравнения
                                        "NAME" => "CATALOG_COMPARE_LIST", // Уникальное имя для списка сравнения
                                        "AJAX_OPTION_JUMP" => "N", // Включить прокрутку к началу компонента
                                        "AJAX_OPTION_STYLE" => "Y", // Включить подгрузку стилей
                                        "AJAX_OPTION_HISTORY" => "N", // Включить эмуляцию навигации браузера
                                        "ACTION_VARIABLE" => "action",
                                        "PRODUCT_ID_VARIABLE" => "id",
                                        "AJAX_OPTION_ADDITIONAL" => "", // Дополнительный идентификатор
                                            ), false
                                    );
                                    ?>
                                </div>
                                <div class="callback">
                                    <?
                                    $APPLICATION->IncludeComponent(
                                            "top10:callback_with_range", ".default", array(
                                        "AJAX_MODE" => "Y",
                                        "AJAX_OPTION_ADDITIONAL" => "",
                                        "AJAX_OPTION_HISTORY" => "N",
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "Y",
                                        "BTN_CALLBACK" => "Обратный звонок",
                                        "COMPONENT_TEMPLATE" => ".default",
                                        "EMAIL_EVENT_TYPE" => array(
                                            0 => "TOP10_CALLBACK",
                                        ),
                                        "EMAIL_SEND" => "Y",
                                        "EMAIL_SUBJECT" => "Заявка на обратный звонок",
                                        "EMAIL_TO" => "sales@tskdiplomat.ru",
                                        "FOR_CALL" => "Для заказа просто позвоните:",
                                        "IBLOCK_ID" => "",
                                        "IBLOCK_SAVE" => "N",
                                        "IBLOCK_TYPE" => "news",
                                        "KEEP_PHONE" => "Или оставьте телефон:",
                                        "PHONE" => "8 (495) 956-7120",
                                        "PHONE_CHECK" => "[\\d\\s\\+]+.*",
                                        "PHONE_FIELD" => "Пример: 8 (888) 555-3535",
                                        "SCRIPT_BODY" => "N",
                                        "SCRIPT_JQ" => "N",
                                        "SCRIPT_JQUI" => "Y",
                                        "SUCCESS_MSG" => "Заявка принята, с вами свяжется наш менеджер в указанное время!",
                                        "TIME_FINISH" => "18:00",
                                        "TIME_MAX" => "20:00",
                                        "TIME_MIN" => "9:00",
                                        "TIME_START" => "09:00",
                                        "TIME_STEP" => "30",
                                        "TOP10_CALLBACK_FORM" => "Заказ обратного звонка",
                                        "TOP10_CALLBACK_FREE" => "Звонок бесплатный, перезвоним в ближайшее время!",
                                        "TOP10_CALLBACK_WHEN" => "Когда с вами связаться?",
                                        "TOP10_INPUT_NAME" => "Введите ваше имя",
                                        "TOP10_INPUT_PHONE" => "Введите номер телефона",
                                        "TOP10_NAME_CHECK" => ".*",
                                        "TOP10_NAME_FIELD" => "Ваше имя",
                                        "TOP10_SUBMIT_VALUE" => "Перезвоните мне!"
                                            ), false
                                    );
                                    ?>
                                </div>
                                <div class="callback_write">
                                    <?
                                    $APPLICATION->IncludeComponent(
                                            "altasib:feedback.form", ".default", array(
                                        "ACTIVE_ELEMENT" => "Y",
                                        "ADD_LEAD" => "N",
                                        "AJAX_MODE" => "Y",
                                        "AJAX_OPTION_ADDITIONAL" => "",
                                        "AJAX_OPTION_HISTORY" => "N",
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "Y",
                                        "ALX_CHECK_NAME_LINK" => "Y",
                                        "ALX_LOAD_PAGE" => "N",
                                        "ALX_NAME_LINK" => "Напишите нам",
                                        "BACKCOLOR_ERROR" => "#ffffff",
                                        "BBC_MAIL" => "",
                                        "BORDER_RADIUS" => "3px",
                                        "CAPTCHA_TYPE" => "default",
                                        "CATEGORY_SELECT_NAME" => "Выберите категорию",
                                        "CHECK_ERROR" => "Y",
                                        "COLOR_ERROR" => "#8E8E8E",
                                        "COLOR_ERROR_TITLE" => "#A90000",
                                        "COLOR_HINT" => "#000000",
                                        "COLOR_INPUT" => "#727272",
                                        "COLOR_MESS_OK" => "#963258",
                                        "COLOR_NAME" => "#000000",
                                        "COMPONENT_TEMPLATE" => ".default",
                                        "EVENT_TYPE" => "ALX_FEEDBACK_FORM",
                                        "FANCYBOX_EN" => "Y",
                                        "FORM_ID" => "1",
                                        "HIDE_FORM" => "N",
                                        "IBLOCK_ID" => "20",
                                        "IBLOCK_TYPE" => "altasib_feedback",
                                        "IMG_ERROR" => "/upload/altasib.feedback.gif",
                                        "IMG_OK" => "/upload/altasib.feedback.ok.gif",
                                        "JQUERY_EN" => "N",
                                        "LOCAL_REDIRECT_ENABLE" => "N",
                                        "MESSAGE_OK" => "Сообщение отправлено!",
                                        "NAME_ELEMENT" => "ALX_DATE",
                                        "PROPERTY_FIELDS" => array(
                                            0 => "PHONE",
                                            1 => "FIO",
                                            2 => "EMAIL",
                                            3 => "FEEDBACK_TEXT",
                                        ),
                                        "PROPERTY_FIELDS_REQUIRED" => array(
                                            0 => "PHONE",
                                            1 => "FIO",
                                            2 => "EMAIL",
                                            3 => "FEEDBACK_TEXT",
                                        ),
                                        "PROPS_AUTOCOMPLETE_EMAIL" => array(
                                            0 => "EMAIL",
                                        ),
                                        "PROPS_AUTOCOMPLETE_NAME" => array(
                                            0 => "FIO",
                                        ),
                                        "REWIND_FORM" => "N",
                                        "SECTION_MAIL_ALL" => "sales@tskdiplomat.ru",
                                        "SEND_MAIL" => "N",
                                        "SHOW_MESSAGE_LINK" => "Y",
                                        "SIZE_HINT" => "10px",
                                        "SIZE_INPUT" => "12px",
                                        "SIZE_NAME" => "12px",
                                        "USERMAIL_FROM" => "N",
                                        "USE_CAPTCHA" => "N",
                                        "WIDTH_FORM" => "100%"
                                            ), false
                                    );
                                    ?>
                                </div>
                            </div>
                            <div id="menu_search">
                                    <?
                                    $APPLICATION->IncludeComponent("bitrix:menu", "top", array(
                                        "ROOT_MENU_TYPE" => "top",
                                        "MENU_CACHE_TYPE" => "Y",
                                        "MENU_CACHE_TIME" => "36000000",
                                        "MENU_CACHE_USE_GROUPS" => "Y",
                                        "MENU_CACHE_GET_VARS" => array(
                                        ),
                                        "MAX_LEVEL" => "1",
                                        "CHILD_MENU_TYPE" => "left",
                                        "USE_EXT" => "N",
                                        "DELAY" => "N",
                                        "ALLOW_MULTI_SELECT" => "N"
                                            ), false
                                    );
                                    ?>


                                <div id="search_block_top">
                                <?
                                $APPLICATION->IncludeComponent(
                                        "bitrix:search.form", ".default", array(
                                    "PAGE" => "#SITE_DIR#search/",
                                    "USE_SUGGEST" => "N"
                                        ), false
                                );
                                ?>
                                </div>
                            </div>
                                    <? /*
                                      <script type="text/javascript">
                                      // <![CDATA[
                                      $('document').ready( function() {
                                      $("#search_query_top")
                                      .autocomplete(
                                      'http://livedemo00.template-help.com/prestashop_42844/index.php?controller=search', {
                                      minChars: 3,
                                      max: 10,
                                      width: 500,
                                      selectFirst: false,
                                      scroll: false,
                                      dataType: "json",
                                      formatItem: function(data, i, max, value, term) {
                                      return value;
                                      },
                                      parse: function(data) {
                                      var mytab = new Array();
                                      for (var i = 0; i < data.length; i++)
                                      mytab[mytab.length] = { data: data[i], value: data[i].cname + ' > ' + data[i].pname };
                                      return mytab;
                                      },
                                      extraParams: {
                                      ajaxSearch: 1,
                                      id_lang: 1
                                      }
                                      }
                                      )
                                      .result(function(event, data, formatted) {
                                      $('#search_query_top').val(data.pname);
                                      document.location.href = data.product_link;
                                      })
                                      });
                                      // ]]>
                                      </script>
                                     */ ?>
                            <? if ($curPage == SITE_DIR . "index.php"): ?>
                                <?
                                $APPLICATION->IncludeComponent(
                                        "bitrix:main.include", "", Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_DIR . "include/slider.php",
                                    "AREA_FILE_RECURSIVE" => "N",
                                    "EDIT_MODE" => "html",
                                        ), false, Array('HIDE_ICONS' => 'Y')
                                );
                                ?>
                            <? endif; ?>					

                        </header>
                        <div class="columns <?php if($curPage == '/catalog/compare/index.php') { echo "compare"; } ?> clearfix">
                            <? if ($curPage != "/catalog/compare/index.php"): ?>
                                <aside>
                                    <div id="left_column" class="column">
                                        <div id="categories_block_left" class="block">
                                            <div class="h4title">Каталог</div>
                                            <div class="block_content navigation">
                                <?
                                $APPLICATION->IncludeComponent("pseo:menu", ($USER->IsAdmin() ? "left_tree_mod" : "left_tree_mod"), array(
                                    "ROOT_MENU_TYPE" => "left",
                                    "MENU_CACHE_TYPE" => "A",
                                    "MENU_CACHE_TIME" => "360000",
                                    "MENU_CACHE_USE_GROUPS" => "N",
                                    "MENU_CACHE_GET_VARS" => array(
                                    ),
                                    "MAX_LEVEL" => "2",
                                    "CHILD_MENU_TYPE" => "left",
                                    "USE_EXT" => "Y",
                                    "DELAY" => "N",
                                    "ALLOW_MULTI_SELECT" => "N"
                                        ), false
                                );
                                ?>
                                            </div>
                                        </div>
                                                <?
                                                $APPLICATION->IncludeComponent(
                                                        "bitrix:main.include", ".default", Array(
                                                    "AREA_FILE_SHOW" => "page",
                                                    "AREA_FILE_SUFFIX" => "inc",
                                                    "AREA_FILE_RECURSIVE" => "N",
                                                    "EDIT_MODE" => "html",
                                                    "EDIT_TEMPLATE" => "page_inc.php"
                                                        )
                                                );
                                                ?><?
                                        $APPLICATION->IncludeComponent(
                                                "bitrix:main.include", ".default", Array(
                                            "AREA_FILE_SHOW" => "sect",
                                            "AREA_FILE_SUFFIX" => "inc",
                                            "AREA_FILE_RECURSIVE" => "Y",
                                            "EDIT_MODE" => "html",
                                            "EDIT_TEMPLATE" => "sect_inc.php"
                                                )
                                        );
                                        ?>
                                        <?
                                        $APPLICATION->IncludeComponent(
                                                "bitrix:voting.current", "", Array(
                                            "CHANNEL_SID" => "UF_BLOG_POST_VOTE",
                                            "VOTE_ID" => "1",
                                            "VOTE_ALL_RESULTS" => "Y",
                                            "CACHE_TYPE" => "A",
                                            "CACHE_TIME" => "3600",
                                            "AJAX_MODE" => "Y",
                                            "AJAX_OPTION_JUMP" => "Y",
                                            "AJAX_OPTION_STYLE" => "Y",
                                            "AJAX_OPTION_HISTORY" => "Y"
                                                )
                                        );
                                        ?>
                                    </div>
                                </aside>
                                    <? endif; ?>
                            <div id="<?php if ($curPage != '/catalog/compare/index.php') {
                                    echo 'center_column';
                                } else {
                                    echo 'center_column_wide';
                                } ?>" class="center_column">
                                    <? if ($curPage != SITE_DIR . "index.php"): ?>
                                    <div class="breadcrumb bordercolor">
                                        <div class="breadcrumb_inner">
                                <?
                                $APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default", Array(
                                    "START_FROM" => "0",
                                        )
                                );
                                ?>
                                        </div>
                                    </div>
                                        <? endif; ?>
                                    <? endif; ?>