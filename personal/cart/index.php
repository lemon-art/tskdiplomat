<?
if($_REQUEST["ajax"]=="Y"):
	$AJAX_MODE = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
        <?$APPLICATION->IncludeComponent("bitrix:eshop.sale.basket.basket", "ajax", Array(
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",	// Рассчитывать скидку для каждой позиции (на все количество товара)
	"COLUMNS_LIST" => array(	// Выводимые колонки
		0 => "NAME",
		1 => "PROPS",
		2 => "PRICE",
		3 => "QUANTITY",
		4 => "DELETE",
		5 => "DELAY",
		6 => "DISCOUNT",
	),
	"AJAX_MODE" => "N",	// Включить режим AJAX
	"AJAX_OPTION_JUMP" => "N",	// Включить прокрутку к началу компонента
	"AJAX_OPTION_STYLE" => "Y",	// Включить подгрузку стилей
	"AJAX_OPTION_HISTORY" => "N",	// Включить эмуляцию навигации браузера
	"PATH_TO_ORDER" => "/personal/order/make/",	// Страница оформления заказа
	"HIDE_COUPON" => "N",	// Спрятать поле ввода купона
	"QUANTITY_FLOAT" => "N",	// Использовать дробное значение количества
	"PRICE_VAT_SHOW_VALUE" => "Y",	// Отображать значение НДС
	"USE_PREPAYMENT" => "N",	// Использовать предавторизацию для оформления заказа (PayPal Express Checkout)
	"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
	"AJAX_OPTION_ADDITIONAL" => "",	// Дополнительный идентификатор
	),
	false
        );?>        
	<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
else:
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
	$template = ".default";

$APPLICATION->SetTitle("Корзина");
?>
<h1>Корзина</h1>
<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", ".default", Array(
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "Y",	// Рассчитывать скидку для каждой позиции (на все количество товара)
		"COLUMNS_LIST" => array(	// Выводимые колонки
			0 => "NAME",
			1 => "PROPS",
			2 => "PRICE",
			3 => "QUANTITY",
			4 => "DELETE",
			5 => "DELAY",
			6 => "DISCOUNT",
		),
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"PATH_TO_ORDER" => "/personal/order/make/",	// Страница оформления заказа
		"HIDE_COUPON" => "N",	// Спрятать поле ввода купона
		"QUANTITY_FLOAT" => "N",	// Использовать дробное значение количества
		"PRICE_VAT_SHOW_VALUE" => "Y",	// Отображать значение НДС
		"USE_PREPAYMENT" => "N",	// Использовать предавторизацию для оформления заказа (PayPal Express Checkout)
		"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
endif;	
?>