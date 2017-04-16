<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$is_error = true;
$error_text = '';
if (isset($_POST['product_id']) && isset($_POST['fio']) && isset($_POST['phone']) && isset($_POST['email'])) {
    if (CModule::IncludeModule("catalog") && CModule::IncludeModule("sale")) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $ar_res = CCatalogProduct::GetByIDEx($product_id);
        if ($ar_res) {
            $is_error = false;
            global $USER;
            $user_id = $USER->GetID();
            if (!$user_id) {
                //пароль для нового пользователя
                $pass = rand(100000, 999999);
                //группы, в которых он будет состоять
                $groups = array(3, 4, 5);
                $user_id = $USER->Add(array(
                    "NAME" => $_POST['fio'],
                    "EMAIL" => $_POST['email'],
                    "LOGIN" => getmicrotime().'_'.$_POST['email'],
                    "PERSONAL_PHONE" => $_POST['phone'],
                    "LID" => "ru",
                    "ACTIVE" => "Y",
                    "GROUP_ID" => $groups,
                    "PASSWORD" => $pass,
                    "CONFIRM_PASSWORD" => $pass,
                ));
                $USER->Authorize($user_id);
                $error_text = $USER->LAST_ERROR;
            }
            if ($user_id > 0) {
                CSaleBasket::DeleteAll(1, false);
                //PAY_SYSTEM_ID, PRICE_DELIVERY, DELIVERY_ID, DISCOUNT_VALUE, TAX_VALUE можно не указывать
                $arFields = array(
                    "LID" => SITE_ID,
                    "PERSON_TYPE_ID" => 3,
                    //Вместо ### укажите конкретный ID цены
                    "PRICE" => $ar_res['PRICES'][1]['PRICE'],
                    "PAYED" => "N",
                    "CANCELED" => "N",
                    "STATUS_ID" => "N",
                    "CURRENCY" => "RUB",
                    "USER_ID" => $user_id,
                    "USER_DESCRIPTION" => 'Быстрая покупка'
                );
                $ORDER_ID = IntVal(CSaleOrder::Add($arFields));
                if ($ORDER_ID > 0) {
                    $arProps = array();
                    $arFields = array(
                        "PRODUCT_ID" => $ar_res['ID'],
                        "PRICE" => $ar_res['PRICES'][1]['PRICE'],
                        "CURRENCY" => "RUB",
                        "WEIGHT" => $ar_res['PRODUCT']['WEIGHT'],
                        "QUANTITY" => $quantity,
                        "DELAY" => "N",
                        "LID" => $ar_res['LID'],
                        "CAN_BUY" => "Y",
                        "ORDER_ID" => $ORDER_ID,
                        "NAME" => $ar_res['NAME'],
                        "MODULE" => "catalog",
                        "NOTES" => "",
                        "DETAIL_PAGE_URL" => $ar_res['DETAIL_PAGE_URL'],
                    );
                    $add = CSaleBasket::Add($arFields);
                    if (intval($add) > 0)
                        echo 'ok';
                    
                    $strOrderList .= $ar_res['NAME']." - ".$quantity." ".$measure.": ".SaleFormatCurrency($ar_res['PRICES'][1]['PRICE'], "RUB");
                    $strOrderList .= "\n";

                    $arFields = Array(
                        "ORDER_ID" => $ORDER_ID,
                        "ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID))),
                        "ORDER_USER" => $_POST['fio'],
                        "PRICE" => SaleFormatCurrency($ar_res['PRICES'][1]['PRICE'], "RUB"),
                        "EMAIL" => $_POST['email'],
                        "ORDER_LIST" => $strOrderList,
                        "SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@" . $_SERVER['SERVER_NAME']),
                    );
                    $eventName = "SALE_NEW_ORDER";

                    $bSend = true;
                    foreach (GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent)
                        if (ExecuteModuleEventEx($arEvent, Array($ORDER_ID, &$eventName, &$arFields)) === false)
                            $bSend = false;
                        
                    if ($bSend) {
                        $event = new CEvent;
                        $event->Send($eventName, SITE_ID, $arFields, "N");
                    }
                }
            }
        }
    }
}
if ($error_text != '')
    echo str_replace('<br>', "\n", $error_text);
else if ($is_error) {
    echo 'ошибка';
}
?>