<?php
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResult = array('_POST' => $_POST);
$name = htmlspecialchars(trim($_POST['name']));
$phone = htmlspecialchars(trim($_POST['phone']));

if(strlen($name)  <= 0){
    $arResult['STATUS'] = 'ERROR';
    $arResult['ERROR']['name'] =  'Не указано имя';
}
if(strlen($phone)  <= 0){
    $arResult['STATUS'] = 'ERROR';
    $arResult['ERROR']['phone'] = 'Не указан телефон';
}
$comment = htmlspecialchars(trim($_POST['comment']));

if ( $arResult['STATUS'] !== 'ERROR') {
    $id = CEvent::Send("BACKCALL_REQUEST", SITE_ID, array(
        'NAME' => $name,
        'PHONE' => $phone,
        'COMMENT' => $comment,
        'REGION' => USER_CITY_SERVER_NAME,
        'SALE_EMAIL' => COption::GetOptionString("sale", "order_email"),
    ));
    $arResult = array('STATUS' => 'OK','MESSAGE'=> '<h2>Ваше сообщение получено!</h2><p>В ближайшее время с Вами свяжется менеджер по указанному телефону.<br/><b>Спасибо!</b></p>');
    
    CStatEvent::AddByEvents("callback", "call", $id);
    
} 

echo json_encode($arResult);
die();
?>
