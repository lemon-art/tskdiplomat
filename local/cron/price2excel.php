<?php
/*
 * скрипт вызывает компонент формирования прайс листа Excel для каждго города.
 */
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('BX_CRONTAB_SUPPORT', true);

//echo 'Start'.date("Y-m-d H:i:s:u");

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

error_reporting(E_ERROR);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

set_time_limit(3600);
ignore_user_abort(true);

/**
 * @global $USER CUser
 */
if (!is_object($USER)) {
	$USER = new CUser();
}

\Bitrix\Main\Loader::includeModule('iblock');

            $t = getmicrotime();
            //echo 'Start create price for '.PHP_EOL;
                    $APPLICATION->IncludeComponent(
                    "fcm:price2excel", 
                    ".default", 
                    array(
                            
                            "UPLOAD_DIR" => '/upload/prices2excel/',
                            "UPLOAD_FILE_NAME" => "tskdiplomat_price.xlsx",

                            "COMPONENT_TEMPLATE" => ".default",
                            "IBLOCK_TYPE" => "catalog",
                            "IBLOCK_ID" => "3",
                        
                            "SECTION_SORT_FIELD" => "left_margin",
                            "SECTION_SORT_ORDER" => "asc",
                            "FILTER_NAME" => "arrFilter",
                            "HIDE_NOT_AVAILABLE" => "N",
                            "SECTION_COUNT" => "100",
                            "ELEMENT_COUNT" => "999",
                            "CACHE_TYPE" => "N",
                            "CACHE_TIME" => "36000000",
                            "CACHE_FILTER" => "Y",
                            "CACHE_GROUPS" => "Y",
                            "TABLE_COLUMNS" => array(
                                0 => "MANUFACTURER",
                                1 => "SI",
                            ),
                            "PRICE_CODE" => array(
                                    0 => "BASE",
                            ),
                            "USE_PRICE_COUNT" => "N",
                            "SHOW_PRICE_COUNT" => "1",
                            "PRICE_VAT_INCLUDE" => "Y",
                            "USE_PRODUCT_QUANTITY" => "N",
                            "CONVERT_CURRENCY" => "Y",
                            "CURRENCY_ID" => "RUB"
                    ),
                    false
            );
            
            
            //echo 'Finish create price for '.$arCity['NAME'].' : '.((getmicrotime()-$t)/1000).PHP_EOL;
            
?>
