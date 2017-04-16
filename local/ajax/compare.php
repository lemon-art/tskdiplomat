<?php
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$key = 15; //Catalog IBLOCK_ID

if (isset($_GET['clear'])) {
    $_SESSION['CATALOG_COMPARE_LIST'] = array();
} else {
    $id = intval($_GET['id']);
    CModule::IncludeModule("iblock");
    $el = CIblockElement::GetList(array(), array('ID'=>$id, 'IBLOCK_ID'=>$key), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "DETAIL_PAGE_URL"))->GetNext();
    
    if (!is_array($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'])) $_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'] = array();

    $arResponse = array();
    if (isset($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'][$id])) {
        unset($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'][$id]);
    } elseif (isset($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'][$id])) {
        unset($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'][$id]);
    } else {
        $_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS'][$id] = $el;
    }
}

$arResponse['id'] = array_keys($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS']);
$arResponse['count'] = count($_SESSION['CATALOG_COMPARE_LIST'][$key]['ITEMS']);

echo json_encode($arResponse);
?>
