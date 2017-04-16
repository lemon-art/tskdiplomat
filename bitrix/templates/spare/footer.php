<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
 
	</div><!--//center column-->
<footer>
<div id="footer" class="clearfix">
	<div id="tmfooterlinks">
		<div>
		<div class="h4title">О компании</div>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "foot", Array(
	"ROOT_MENU_TYPE" => "foot1",	// Тип меню для первого уровня
	"MENU_CACHE_TYPE" => "A",	// Тип кеширования
	"MENU_CACHE_TIME" => "36000000",	// Время кеширования (сек.)
	"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
	"MENU_CACHE_GET_VARS" => "",	// Значимые переменные запроса
	"MAX_LEVEL" => "1",	// Уровень вложенности меню
	"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
	"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
	"DELAY" => "N",	// Откладывать выполнение шаблона меню
	"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
	),
	false
);?>
		</div>
		<div>
			<div class="h4title">Каталог</div>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "", array(
				"ROOT_MENU_TYPE" => "foot2",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
				),
				false
			);?>
		</div>
		<div>
			<div class="h4title">Ваш аккаунт</div>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "", array(
				"ROOT_MENU_TYPE" => "foot3",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
				),
				false
			);?>
		</div>
		<p>&copy; <?=date('Y');?> ТСК-ДИПЛОМАТ.</p>
	</div> 
	<div id="block_contact_infos">
	<div class="h4title">Контакты</div>
					<?$APPLICATION->IncludeComponent(
						"bitrix:main.include",
						"",
						Array(
							"AREA_FILE_SHOW" => "file",
							"PATH" => SITE_DIR."include/contacts.php",
							"AREA_FILE_RECURSIVE" => "N",
							"EDIT_MODE" => "html",
						),
						false,
						Array('HIDE_ICONS' => 'N')
					);?>
	</div>
  </div>
</footer>
	</div>
</div><!--wrapper 3-->
</div><!--wrapper 2-->
</div><!--wrapper 1-->
</body>
</html>
<?include $_SERVER['DOCUMENT_ROOT'].'/include/chMeta.php'?>