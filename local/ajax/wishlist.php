<?php
//TODO add statistic events
//TODO session control !!!
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResponse = array();
$r = false; // результат операции
$arRewriteFields = array();
$product_properties = array();
$added_items = 0;
$added_qty = 0;

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$arResponse['action'] = "add2wishlist";
//add as delayed 
$arRewriteFields["DELAY"] = "Y";
    
if ($_GET['action']=="add2wishlist") {
    $arResponse['action'] = "add2wishlist";
}

if (is_array($_GET['id'])) {
    $id = array();
    $qty = array();
    foreach ($_GET['id'] as $i=>$v_id) {
        $id[$i] = $v_id;
        $qty[$i] = $_GET['qty'][$i];
    }
} else {
    $id = intval($_GET['id']);
    $qty = intval($_GET['qty']);
    if (!$qty) $qty = 1;
} 

if ($id) {
    if (is_numeric($id)) {
        Add2BasketByProductID($id, $qty, $arRewriteFields, $product_properties);
        $added_items = 1;
        $added_qty = $qty;
        $arItem = CIblockElement::GetList(array(), array('ID'=>$id), false, false, array('ID', 'NAME', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL'))->GetNext();
        $price = CCatalogProduct::GetOptimalPrice($id, $qty, $USER->GetUserGroupArray());
        
        $picture = CFile::ResizeImageGet($arItem['DETAIL_PICTURE'], array('width'=>90, 'height'=>90), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
        
        if (!$picture) $picture = "/bitrix/templates/main/upload/catalog/catalog_empty.jpg";
        
        //trace($arItem);
        
        $arResponse['product'][$id] = array(
            'name' => $arItem['NAME'], 
            'link' => $arItem['DETAIL_PAGE_URL'], 
            'qty' => $qty, 
            'picture' => $picture, 
            'price' => round($qty*$price['DISCOUNT_PRICE']), 
        );
        //trace($arResponse);
    } elseif (is_array($id)) {
        foreach ($id as $i=>$v_id) {
            $arItem = CIblockElement::GetList(array(), array('ID'=>$v_id), false, false, array('ID', 'NAME', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL'))->GetNext();
            $price = CCatalogProduct::GetOptimalPrice($v_id, $qty[$i], $USER->GetUserGroupArray());
            $picture = CFile::ResizeImageGet($arItem['DETAIL_PICTURE'], array('width'=>90, 'height'=>90), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
            if (!$picture) $picture = "/bitrix/templates/main/upload/catalog/catalog_empty.jpg";
            $arResponse['products'][$v_id] = array(
                'name' => $arItem['NAME'], 
                'link' => $arItem['DETAIL_PAGE_URL'], 
                'qty' => $qty[$i], 
                'picture' => $picture, 
                'price' => round($qty[$i]*$price['DISCOUNT_PRICE']), 
            );
            $added_items++;
            $added_qty += $qty[$i];
            Add2BasketByProductID($v_id, $qty[$i], $arRewriteFields, $product_properties);
        }
    }
} else {
    $arResponse['error'] = "Не указан ID товара";
}

$arResponse = $_SESSION['WISHLIST_ITEMS'];

echo \Bitrix\Main\Web\Json::encode($arResponse);

//AddMessage2Log('wishlist');
//DCBasketHelper::OnBasketUpdateHandler(0,&$arResponse);
//trace($GLOBALS['MAIN_MODULE_EVENTS']);
?>
