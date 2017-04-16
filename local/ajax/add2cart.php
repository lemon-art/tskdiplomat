<?php
//TODO add statistic events
//TODO session control !!!
//define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResponse = array();
$r = false; // результат операции
$arRewriteFields = array();
$product_properties = array();
$added_items = 0;
$added_qty = 0;

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$arResponse['action'] = "add2cart";
if ($_GET['action']=="subscribe") {
    $arResponse['action'] = "subscribe";
    $arRewriteFields["SUBSCRIBE"] = "Y";
    $arRewriteFields["CAN_BUY"] = "N";
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

// пересчет текущего состояния корзины для ответа
CModule::IncludeModule("sale");
$rs = CSaleBasket::GetList(array(), array('FUSER_ID'=>CSaleBasket::GetBasketUserID(), 'DELAY'=>"N", 'SUBSCRIBE'=>"N", 'ORDER_ID'=>false));
while ($ar = $rs->Fetch()) {
    $arResponse['count']++;
    $arResponse['quantity'] += $ar['QUANTITY'];
    $arResponse['sum'] += $ar['PRICE']*$ar['QUANTITY'];
    $arResponse['currency'] = $ar['CURRENCY'];
    $arResponse['items'][] = $ar['PRODUCT_ID'];
}

$arResponse['count_formatted'] = padej($arResponse['count'], "товар", "товара", "товаров");
$arResponse['quantity_formatted'] = padej($arResponse['quantity'], "товар", "товара", "товаров");
$arResponse['sum_formatted'] = CurrencyFormat($arResponse['sum'], $arResponse['currency']);

//обновляем данные корзины в сессии
$_SESSION['BASKET_ITEMS']  = $arResponse['items'];

echo \Bitrix\Main\Web\Json::encode($arResponse);
?>