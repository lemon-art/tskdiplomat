<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Помощь покупателю");
?><p>В этом разделе вы можете найти ответы на многие вопросы, касающиеся работы нашего сайта. Если вы не нашли интересующей вас информации, то можете отправить нам запрос с помощью <a href="../contacts/">формы обратной связи</a>.</p>
 <?$APPLICATION->IncludeComponent("bitrix:support.faq.element.list", ".default", array(
		"IBLOCK_TYPE" => "services",
		"IBLOCK_ID" => "7",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"AJAX_MODE" => "N",
		"SECTION_ID" => "52",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>