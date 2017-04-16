<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Карта сайта");
$APPLICATION->SetPageProperty("description", "");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetTitle("Карта сайта");
?> 


<div class="mceContentBody">
<h1>Карта сайта</h1>
<br />

<ul class="site_map">
<li><a href="http://tskdiplomat.ru/">Главная</a></li>
<?$APPLICATION->IncludeComponent("bitrix:main.map","tree",Array(
        "LEVEL" => "11", 
        "COL_NUM" => "1", 
        "SHOW_DESCRIPTION" => "Y", 
        "SET_TITLE" => "Y", 
        "CACHE_TYPE" => "A", 
        "CACHE_TIME" => "3600" 
    )
);?>
	<?$APPLICATION->IncludeComponent("bitrix:menu", "tree", array(
	"ROOT_MENU_TYPE" => "",
	"MENU_CACHE_TYPE" => "A",
	"MENU_CACHE_TIME" => "3600",
	"MENU_CACHE_USE_GROUPS" => "Y",
	"MENU_CACHE_GET_VARS" => array(
	),
	"MAX_LEVEL" => "5",
	"CHILD_MENU_TYPE" => "",
	"USE_EXT" => "Y",
	"DELAY" => "N",
	"ALLOW_MULTI_SELECT" => "Y"
	),
	false
);?>
</ul>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>