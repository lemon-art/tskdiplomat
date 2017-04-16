<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "ТСК Дипломат - магазин стройматериалов в Москве: адреса, телефоны");
$APPLICATION->SetPageProperty("keywords", "Контакты - ТСК ДИПЛОМАТ");
$APPLICATION->SetPageProperty("description", "Адрес и телефоны оптового и розничного магазина стройматериалов ТСК Дипломат. Адреса баз продажи строительных материалов оптом, с доставкой.");
$APPLICATION->SetTitle("Контакты");
?> 
<div class="page-title">
    <h1>Контакты</h1>
</div>

 <div class="row contact-block">
     <div class="col-md-7 contact-left">
     <h2>ТСК ДИПЛОМАТ</h2>
     
     <ul>
         <li>
            <h3>Время работы:</h3>
            <p>пн-вс, 09:00-18:00</p>
         </li>
         <li>
            <h3>Телефоны:</h3>
            <p> 
                +7 (495) 663 71 82  (многоканальный)<br />
                <span class="ya-phone comagic_phone">+7 (495) 956 71 20</span>
            </p>
         </li>
         <li>
            <h3>Почта:</h3>
            <p>sales@tskdiplomat.ru</p>
         </li>
         <li>
            <h3>Реквизиты:</h3>
            <p> 
                Идентификационный номер (ИНН): 7705935398 
                <br />
                БИК: 044525503
            </p>
         </li>
     </ul>
     </div>
     <div class="col-md-5 contact-right">
         <h2>Адреса</h2> 
        <ul> 
         <li>
            <h3>Офис: </h3>
            <p>115114 г. Москва, ул. Дербеневская, д. 24, стр. 3</p>
            <div class="buttons-set">
                <button class="office-map-button fancy button" href="#office-map" ><span><span>Офис</span></span></button> 
            </div>
         </li>
         <li>
            <h3>Склад: </h3>
            <p>
                Московская область, Красногорский район, 
                <br />
                пос. Нахабино, ул. 300-летия Инженерных войск, дом 1А
            </p>
            <div class="buttons-set">
                <button class="sklad-map-button fancy button" href="#sklad-map" ><span><span>Склад</span></span></button> 
            </div>
         </li>
        </ul>
     </div>     
 </div> 

<div class="page-title">
    <h2>Наши сотрудники:</h2>
</div>
<?$APPLICATION->IncludeComponent("bitrix:news.list", "mordokniga", Array(
	"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_TYPE" => "site",	// Тип информационного блока (используется только для проверки)
		"IBLOCK_ID" => "34",	// Код информационного блока
		"NEWS_COUNT" => "20",	// Количество новостей на странице
		"SORT_BY1" => "SORT",	// Поле для первой сортировки новостей
		"SORT_ORDER1" => "ASC",	// Направление для первой сортировки новостей
		"SORT_BY2" => "SORT",	// Поле для второй сортировки новостей
		"SORT_ORDER2" => "ASC",	// Направление для второй сортировки новостей
		"FILTER_NAME" => "",	// Фильтр
		"FIELD_CODE" => array(	// Поля
			0 => "",
			1 => "",
		),
		"PROPERTY_CODE" => array(	// Свойства
			0 => "POSITION",
			1 => "PHONE",
			2 => "EMAIL",
			3 => "",
		),
		"CHECK_DATES" => "Y",	// Показывать только активные на данный момент элементы
		"DETAIL_URL" => "",	// URL страницы детального просмотра (по умолчанию - из настроек инфоблока)
		"AJAX_MODE" => "N",	// Включить режим AJAX
		"AJAX_OPTION_JUMP" => "N",	// Включить прокрутку к началу компонента
		"AJAX_OPTION_STYLE" => "Y",	// Включить подгрузку стилей
		"AJAX_OPTION_HISTORY" => "N",	// Включить эмуляцию навигации браузера
		"AJAX_OPTION_ADDITIONAL" => "",	// Дополнительный идентификатор
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "36000000",	// Время кеширования (сек.)
		"CACHE_FILTER" => "N",	// Кешировать при установленном фильтре
		"CACHE_GROUPS" => "Y",	// Учитывать права доступа
		"PREVIEW_TRUNCATE_LEN" => "",	// Максимальная длина анонса для вывода (только для типа текст)
		"ACTIVE_DATE_FORMAT" => "d.m.Y",	// Формат показа даты
		"SET_TITLE" => "N",	// Устанавливать заголовок страницы
		"SET_BROWSER_TITLE" => "N",	// Устанавливать заголовок окна браузера
		"SET_META_KEYWORDS" => "N",	// Устанавливать ключевые слова страницы
		"SET_META_DESCRIPTION" => "N",	// Устанавливать описание страницы
		"SET_LAST_MODIFIED" => "N",	// Устанавливать в заголовках ответа время модификации страницы
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",	// Включать инфоблок в цепочку навигации
		"ADD_SECTIONS_CHAIN" => "N",	// Включать раздел в цепочку навигации
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",	// Скрывать ссылку, если нет детального описания
		"PARENT_SECTION" => "",	// ID раздела
		"PARENT_SECTION_CODE" => "",	// Код раздела
		"INCLUDE_SUBSECTIONS" => "Y",	// Показывать элементы подразделов раздела
		"DISPLAY_DATE" => "N",	// Выводить дату элемента
		"DISPLAY_NAME" => "Y",	// Выводить название элемента
		"DISPLAY_PICTURE" => "Y",	// Выводить изображение для анонса
		"DISPLAY_PREVIEW_TEXT" => "Y",	// Выводить текст анонса
		"PAGER_TEMPLATE" => ".default",	// Шаблон постраничной навигации
		"DISPLAY_TOP_PAGER" => "N",	// Выводить над списком
		"DISPLAY_BOTTOM_PAGER" => "N",	// Выводить под списком
		"PAGER_TITLE" => "Новости",	// Название категорий
		"PAGER_SHOW_ALWAYS" => "N",	// Выводить всегда
		"PAGER_DESC_NUMBERING" => "N",	// Использовать обратную навигацию
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",	// Время кеширования страниц для обратной навигации
		"PAGER_SHOW_ALL" => "N",	// Показывать ссылку "Все"
		"PAGER_BASE_LINK_ENABLE" => "N",	// Включить обработку ссылок
		"SET_STATUS_404" => "N",	// Устанавливать статус 404
		"SHOW_404" => "N",	// Показ специальной страницы
		"MESSAGE_404" => "",	// Сообщение для показа (по умолчанию из компонента)
	),
	false
);?>

<div class="row contact-block">
     <div class="col-md-12 contact-left">
        <h2>Написать нам:</h2>

      
    <p><b>Уважаемые покупатели! </b> 
      <br />
     Прежде чем задать свой вопрос, обратите внимание на раздел <a href="../faq/" >Помощь покупателю</a>. 
     Возможно, там уже есть исчерпывающая информация по решению вашей проблемы.
    </p>
    <p></p>
   <?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback",
	"template1",
	Array(
		"USE_CAPTCHA" => "Y",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"EMAIL_TO" => "sale@tskdiplomat.ru",
		"REQUIRED_FIELDS" => array(),
		"EVENT_MESSAGE_ID" => array()
	)
    );?>
     </div>
</div>

<div id="office-map" style="display: none;"> 
  <p><b style="font-size: 16px; color: red;">Офис: </b>115114 г. Москва, ул. Дербеневская, д. 24, стр. 3</p>
 <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".default",
	Array(
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.7183590239819;s:10:\"yandex_lon\";d:37.64484218100902;s:12:\"yandex_scale\";i:16;s:10:\"PLACEMARKS\";a:2:{i:0;a:3:{s:3:\"LON\";d:37.355757951736;s:3:\"LAT\";d:55.55985485925;s:4:\"TEXT\";s:23:\"ТСК Дипломат\";}i:1;a:3:{s:3:\"LON\";d:37.646223921165;s:3:\"LAT\";d:55.718096612005;s:4:\"TEXT\";s:23:\"ТСК ДИПЛОМАТ\";}}}",
		"MAP_WIDTH" => "600",
		"MAP_HEIGHT" => "400",
		"CONTROLS" => array(0=>"ZOOM",1=>"TYPECONTROL",2=>"SCALELINE",),
		"OPTIONS" => array(0=>"ENABLE_SCROLL_ZOOM",1=>"ENABLE_DBLCLICK_ZOOM",2=>"ENABLE_RIGHT_MAGNIFIER",3=>"ENABLE_DRAGGING",),
		"MAP_ID" => ""
	)
);?> 
</div>
<div id="sklad-map"  style="display: none;"> 
  <p id="sklad"><b style="font-size: 16px; color: red;">Склад: </b>Московская область, Красногорский район, 
    <br />
   пос. Нахабино, ул. 300-летия Инженерных войск, дом 1А</p>
 <img src="/upload/medialibrary/c23/skhema.jpg" title="Схема проезда склад" border="0" alt="Схема проезда склад" width="600" height="365"  /> <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".default",
	Array(
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.84337379050271;s:10:\"yandex_lon\";d:37.14880703551857;s:12:\"yandex_scale\";i:12;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:37.14892745017697;s:3:\"LAT\";d:55.843435624490574;s:4:\"TEXT\";s:23:\"ТСК ДИПЛОМАТ\";}}}",
		"MAP_WIDTH" => "600",
		"MAP_HEIGHT" => "400",
		"CONTROLS" => array(0=>"ZOOM",1=>"TYPECONTROL",2=>"SCALELINE",),
		"OPTIONS" => array(0=>"ENABLE_SCROLL_ZOOM",1=>"ENABLE_DBLCLICK_ZOOM",2=>"ENABLE_RIGHT_MAGNIFIER",3=>"ENABLE_DRAGGING",),
		"MAP_ID" => ""
	)
);?> </div>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>