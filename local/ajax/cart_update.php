<?php
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResponse = array();

CModule::IncludeModule("sale");

$do = $_GET['do']; // операция
$id = intval($_GET['id']); // ID записи в корзине
$qty = intval($_GET['qty']); // Количество
$fuser = CSaleBasket::GetBasketUserID();

$arResponse['id'] = $id;

if ($do == "remove") {
    $arCartItem = CSaleBasket::GetByID($id);
    if ($arCartItem['FUSER_ID']==$fuser) {
        if (CSaleBasket::Delete($id)) {
            $arResponse['result'] = "ok";
        } else {
            $arResponse['result'] = "error";
        }
    }    
} elseif ($do == "removeAll") {
    if (CSaleBasket::DeleteAll($fuser)) {
        $arResponse['result'] = "ok";
    } else {
        $arResponse['result'] = "error";
    }
} elseif ($do == "quantity") {
    $arCartItem = CSaleBasket::GetByID($id);
    if ($arCartItem['FUSER_ID']==$fuser) {
        if (CSaleBasket::Update($id, array('QUANTITY'=>$qty))) {
            $arResponse['result'] = "ok";
        } else {
            $arResponse['result'] = "error";
        }
    }
}

// пересчитываем полную стоимость заказа
$arResponse['prices'] = array();
$rs = CSaleBasket::GetList(array(), array('FUSER_ID'=>$fuser, 'ORDER_ID'=>false, 'DELAY'=>"N", 'CAN_BUY'=>"Y"));
$currency = "RUB";
$arResponse['quantity_count'] = 0; 
$arResponse['items_count'] = 0;
while ($ar = $rs->Fetch()) {
    $arResponse['quantity_count'] += $ar['QUANTITY']; 
    $arResponse['items_count'] += 1; 
    $arResponse['total'] += $ar['PRICE']*$ar['QUANTITY']; 
    $arResponse['weight'] += $ar['WEIGHT']*$ar['QUANTITY']; 
    $arResponse['discount'] += $ar['DISCOUNT_PRICE']*$ar['QUANTITY'];
    $currency = $ar['CURRENCY'];
    $arResponse['prices'][$ar['ID']] = array(
        'price' => $ar['PRICE'],
        'price_formatted' => CurrencyFormat($ar['PRICE'], $currency),
        'total' => $ar['PRICE']*$ar['QUANTITY'],
        'total_formatted' => CurrencyFormat($ar['PRICE']*$ar['QUANTITY'], $currency),
        'old_price' => $ar['PRICE']+$ar['DISCOUNT_PRICE'],
        'old_price_formatted' => CurrencyFormat($ar['PRICE']+$ar['DISCOUNT_PRICE'], $currency),
    );
}

if ($arResponse['total']>0) $arResponse['total_formatted'] = CurrencyFormat($arResponse['total'], $currency);
if ($arResponse['discount']>0) $arResponse['discount_formatted'] = CurrencyFormat($arResponse['discount'], $currency);
$arResponse['count_formatted'] = padej($arResponse['items_count'], "товар", "товара", "товаров");
$arResponse['quantity_formatted'] = padej($arResponse['quantity_count'], "товар", "товара", "товаров");
$arResponse['sum_formatted'] = CurrencyFormat($arResponse['total'], $currency);
$arResponse['sum_formatted2'] = CurrencyFormatNumber($arResponse['total'], $currency)." <span>руб.</span>";

$WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", SITE_ID));
$WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, SITE_ID));
$arResponse["weight_formatted"] = roundEx(DoubleVal($arResponse['weight']/$WEIGHT_KOEF), SALE_VALUE_PRECISION)." ".$WEIGHT_UNIT;

echo json_encode($arResponse);
?>
