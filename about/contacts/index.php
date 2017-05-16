<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "ТСК Дипломат - магазин стройматериалов в Москве: адреса, телефоны");
$APPLICATION->SetPageProperty("keywords", "Контакты - ТСК ДИПЛОМАТ");
$APPLICATION->SetPageProperty("description", "Адрес и телефоны оптового и розничного магазина стройматериалов ТСК Дипломат. Адреса баз продажи строительных материалов оптом, с доставкой.");
$APPLICATION->SetTitle("Контакты");
?><div class="page-title">
	<h1>Контакты</h1>
</div>
<div class="row contact-block">
	<div class="col-md-7 contact-left">
		<h2>ТСК ДИПЛОМАТ</h2>
		<ul>
			<li>
			<h3>Время работы офиса:</h3>
			<p>
				 пн-вс&nbsp;09:00-18:00 (приём заявок)
			</p>
			<h3>Время работы склада:</h3>
			<p>
				пн-нт&nbsp;09:00-19:00, суб&nbsp;09:00-15:00<br>
			</p>
 </li>
			<li>
			<h3>Телефоны:</h3>
			<p>
				 +7 (495) 663 71 82 &nbsp;(многоканальный)<br>
 <span class="ya-phone comagic_phone">+7 (495) 956 71 20</span>
			</p>
 </li>
			<li>
			<h3>Почта:</h3>
			<p>
 <a href="mailto:sales@tskdiplomat.ru">sales@tskdiplomat.ru</a>
			</p>
 </li>
			<li>
			<h3>Реквизиты:</h3>
			<p>
				 Идентификационный номер (ИНН): 7705935398 <br>
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
			<p>
				 115114 г. Москва, ул. Дербеневская, д. 24, стр. 3
			</p>
			<div class="buttons-set">
 <button class="office-map-button fancy button" href="#office-map">Офис</button>
			</div>
 </li>
			<li>
			<h3>Склад: </h3>
			<p>
				 Московская область, Красногорский район, <br>
				 пос. Нахабино, ул. 300-летия Инженерных войск, дом 1А
			</p>
			<p>
				 Время работы склада: пн-нт&nbsp;09:00-19:00, суб&nbsp;09:00-15:00
			</p>
			<div class="buttons-set">
 <button class="sklad-map-button fancy button" href="#sklad-map">Склад</button>
			</div>
 </li>
		</ul>
	</div>
</div>
<div class="page-title">
	<h2>Наши сотрудники:</h2>
</div>
 <?$APPLICATION->IncludeComponent(
	"bitrix:news.list",
	"mordokniga",
	Array(
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_SECTIONS_CHAIN" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"DISPLAY_DATE" => "N",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(0=>"",1=>"",),
		"FILTER_NAME" => "",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "34",
		"IBLOCK_TYPE" => "site",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"INCLUDE_SUBSECTIONS" => "Y",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "20",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Новости",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PROPERTY_CODE" => array(0=>"POSITION",1=>"PHONE",2=>"EMAIL",3=>"",),
		"SET_BROWSER_TITLE" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "N",
		"SHOW_404" => "N",
		"SORT_BY1" => "SORT",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_ORDER2" => "ASC"
	)
);?>
<div class="row contact-block">
	<div class="col-md-12 contact-left">
		<h2>Написать нам:</h2>
		<p>
 <b>Уважаемые покупатели! </b> <br>
			 Прежде чем задать свой вопрос, обратите внимание на раздел <a href="../faq/">Помощь покупателю</a>. Возможно, там уже есть исчерпывающая информация по решению вашей проблемы.
		</p>
		<p>
		</p>
		 <?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback",
	"template1",
	Array(
		"EMAIL_TO" => "sale@tskdiplomat.ru",
		"EVENT_MESSAGE_ID" => array(),
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"REQUIRED_FIELDS" => array(),
		"USE_CAPTCHA" => "Y"
	)
);?>
	</div>
</div>
<div id="office-map" style="display: none;">
	<p>
 <b style="font-size: 16px; color: red;">Офис: </b>115114 г. Москва, ул. Дербеневская, д. 24, стр. 3
	</p>
	 <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".default",
	Array(
		"CONTROLS" => array(0=>"ZOOM",1=>"TYPECONTROL",2=>"SCALELINE",),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.7183590239819;s:10:\"yandex_lon\";d:37.64484218100902;s:12:\"yandex_scale\";i:16;s:10:\"PLACEMARKS\";a:2:{i:0;a:3:{s:3:\"LON\";d:37.355757951736;s:3:\"LAT\";d:55.55985485925;s:4:\"TEXT\";s:23:\"ТСК Дипломат\";}i:1;a:3:{s:3:\"LON\";d:37.646223921165;s:3:\"LAT\";d:55.718096612005;s:4:\"TEXT\";s:23:\"ТСК ДИПЛОМАТ\";}}}",
		"MAP_HEIGHT" => "400",
		"MAP_ID" => "",
		"MAP_WIDTH" => "600",
		"OPTIONS" => array(0=>"ENABLE_SCROLL_ZOOM",1=>"ENABLE_DBLCLICK_ZOOM",2=>"ENABLE_RIGHT_MAGNIFIER",3=>"ENABLE_DRAGGING",)
	)
);?>
</div>
<div id="sklad-map" style="display: none;">
	<p id="sklad">
 <b style="font-size: 16px; color: red;">Склад: </b>Московская область, Красногорский район, <br>
		 пос. Нахабино, ул. 300-летия Инженерных войск, дом 1А
	</p>
 <img width="600" alt="Схема проезда склад" src="/upload/medialibrary/c23/skhema.jpg" height="365" title="Схема проезда склад" border="0"> <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".default",
	Array(
		"CONTROLS" => array(0=>"ZOOM",1=>"TYPECONTROL",2=>"SCALELINE",),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.84337379050271;s:10:\"yandex_lon\";d:37.14880703551857;s:12:\"yandex_scale\";i:12;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:37.14892745017697;s:3:\"LAT\";d:55.843435624490574;s:4:\"TEXT\";s:23:\"ТСК ДИПЛОМАТ\";}}}",
		"MAP_HEIGHT" => "400",
		"MAP_ID" => "",
		"MAP_WIDTH" => "600",
		"OPTIONS" => array(0=>"ENABLE_SCROLL_ZOOM",1=>"ENABLE_DBLCLICK_ZOOM",2=>"ENABLE_RIGHT_MAGNIFIER",3=>"ENABLE_DRAGGING",)
	)
);?>
</div>
 <br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>