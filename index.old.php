<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Все идеи строительства в одном портфеле - ТСК ДИПЛОМАТ");
$APPLICATION->SetPageProperty("description", "Торгово - Строительная Компания ДИПЛОМАТ предлагает широкий ассортимент строительных материалов по адекватным ценам.");
$APPLICATION->SetPageProperty("keywords", "магазин строительных материалов, строительные материалы, стройматериалы");
$APPLICATION->SetTitle("ТСК ДИПЛОМАТ - Интернет-магазин строительных материалов");
?>
<?
$APPLICATION->IncludeComponent("fcm:eshop.catalog.top", "featured1", Array(
	"IBLOCK_TYPE_ID" => "catalog",	// Тип инфо-блока
		"IBLOCK_ID" => "3",	// Инфо-блок
		"ELEMENT_SORT_FIELD" => "RAND",	// По какому полю сортируем элементы
		"ELEMENT_SORT_ORDER" => "asc",	// Порядок сортировки элементов
		"ELEMENT_COUNT" => "6",	// Количество выводимых элементов
		"FLAG_PROPERTY_CODE" => "SALELEADER",	// Тип товаров для отображения
		"OFFERS_LIMIT" => "5",	// Максимальное количество предложений для показа (0 - все)
		"OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "NAME",
			1 => "DETAIL_PICTURE",
			2 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "COLOR",
			1 => "WIDTH",
			2 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",	// По какому полю сортируем предложения товара
		"OFFERS_SORT_ORDER" => "asc",	// Порядок сортировки предложений товара
		"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
		"PRODUCT_ID_VARIABLE" => "id",	// Название переменной, в которой передается код товара для покупки
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
		"PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
		"SECTION_ID_VARIABLE" => "SECTION_ID",	// Название переменной, в которой передается код группы
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "180",	// Время кеширования (сек.)
		"CACHE_GROUPS" => "Y",	// Учитывать права доступа
		"DISPLAY_COMPARE" => "N",	// Выводить кнопку сравнения
		"PRICE_CODE" => array(	// Тип цены
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",	// Использовать вывод цен с диапазонами
		"SHOW_PRICE_COUNT" => "1",	// Выводить цены для количества
		"PRICE_VAT_INCLUDE" => "Y",	// Включать НДС в цену
		"DISPLAY_IMG_WIDTH" => "130",	// Ширина картинки для анонса
		"DISPLAY_IMG_HEIGHT" => "130",	// Высота картинки для анонса
		"SHARPEN" => "30",	// Резкость при масштабировании картинок (от 1 до 100)
	),
	false
);
?>
<?
$APPLICATION->IncludeComponent("fcm:eshop.catalog.top", "featured2", Array(
	"IBLOCK_TYPE_ID" => "catalog",	// Тип инфо-блока
		"IBLOCK_ID" => "3",	// Инфо-блок
		"PROPERTY_CODE" => array(
			0 => "MINIMUM_PRICE",
			1 => "MAXIMUM_PRICE",
		),
		"ELEMENT_SORT_FIELD" => "RAND",	// По какому полю сортируем элементы
		"ELEMENT_SORT_ORDER" => "asc",	// Порядок сортировки элементов
		"ELEMENT_COUNT" => "9",	// Количество выводимых элементов
		"FLAG_PROPERTY_CODE" => "NEWPRODUCT",	// Тип товаров для отображения
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
		"PRODUCT_ID_VARIABLE" => "id",	// Название переменной, в которой передается код товара для покупки
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
		"PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
		"SECTION_ID_VARIABLE" => "SECTION_ID",	// Название переменной, в которой передается код группы
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "180",	// Время кеширования (сек.)
		"CACHE_GROUPS" => "Y",	// Учитывать права доступа
		"DISPLAY_COMPARE" => "N",	// Выводить кнопку сравнения
		"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
		"PRICE_CODE" => array(	// Тип цены
			0 => "BASE",
		),
		"OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "NAME",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "COLOR",
			1 => "WIDTH",
			2 => "",
		),
		"USE_PRICE_COUNT" => "N",	// Использовать вывод цен с диапазонами
		"SHOW_PRICE_COUNT" => "1",	// Выводить цены для количества
		"PRICE_VAT_INCLUDE" => "Y",	// Включать НДС в цену
		"DISPLAY_IMG_WIDTH" => "130",	// Ширина картинки для анонса
		"DISPLAY_IMG_HEIGHT" => "130",	// Высота картинки для анонса
		"SHARPEN" => "30",	// Резкость при масштабировании картинок (от 1 до 100)
		"USE_PRODUCT_QUANTITY" => "N",
		"OFFERS_LIMIT" => "5",	// Максимальное количество предложений для показа (0 - все)
		"OFFERS_SORT_FIELD" => "sort",	// По какому полю сортируем предложения товара
		"OFFERS_SORT_ORDER" => "asc",	// Порядок сортировки предложений товара
		"CONVERT_CURRENCY" => "N",	// Показывать цены в одной валюте
		"OFFERS_CART_PROPERTIES" => "",	// Свойства предложений, добавляемые в корзину
	),
	false
);
?>
<?$APPLICATION->IncludeComponent(
	"bxmod:seo", 
	"seo_mod", 
	array(
		"BXMOD_SEO_SEO_PARTSTITLE" => "Другие разделы",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>