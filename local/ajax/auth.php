<?php
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResponse = array();
$email = $_GET['email'];
$password = $_GET['password'];
$remember = $_GET['remember']=="Y"?true:false;

if ($email AND $password) {
    $error_message = $USER->Login($email, $password, $remember);
    if ($USER->IsAuthorized()) {
        $arResponse['result'] = "ok"; 
    } else {
        $arResponse['result'] = "error";
        $arResponse['error']['email'] = strip_tags($error_message['MESSAGE']);
    }                                
} else {
    $arResponse['result'] = "error";
    if (!$email) $arResponse['error']['email'] = "Не указан email";
    if (!$password) $arResponse['error']['password'] = "Не указан пароль";
}

echo json_encode($arResponse);
?>
