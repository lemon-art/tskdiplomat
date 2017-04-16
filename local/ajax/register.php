<?php
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arResponse = array();

if (!$_GET['email']) {
    $arResponse['result'] = "error";
    $arResponse['error']['email'] = "Укажите e-mail";
}
if (!$_GET['name']) {
    $arResponse['result'] = "error";
    $arResponse['error']['name'] = "Укажите имя";
}
if ($arResponse['result']!="error") { // все ок, пытаемся его зарегистрировать
    $passwd = randString(6);
    $email = trim(strip_tags($_GET['email']));
    $new_user_id = $USER->Add(array(
        'LOGIN' => $email,
        'EMAIL' => $email,
        'PASSWORD' => $passwd,
        'CONFIORM_PASSWORD' => $passwd,
        'ACTIVE' => "Y",
        'NAME' => trim(strip_tags($_GET['name'])),
        'GROUP_ID' => array(5)
    ));
    if ($new_user_id>0) { // успешно зарегистрирован
        $arResponse['result'] = "ok";
        $USER->Authorize($new_user_id, true);
        CEvent::Send("NEW_USER_PASSWORD", "s1", array(
            'EMAIL' => $email,
            'PASSWORD' => $passwd
        ));
    } else { // Ошибка регистрации
        $arResponse['result'] = "error";
        $arResponse['error']['email'] = "Такой e-mail уже используется. Попробуйте восстановить пароль.";
    }
}   

echo json_encode($arResponse);
?>
