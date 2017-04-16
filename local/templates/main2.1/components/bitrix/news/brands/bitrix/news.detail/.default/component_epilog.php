<?php

if($arResult['ID']):
    
$GLOBALS['arrBrandFilter'] = array('PROPERTY_MANUFACTURER' => $arResult['ID']);    

$page_element_count = (intval($_REQUEST['SIZEN_2']) > 0 )? intval($_REQUEST['SIZEN_2']): $arParams["PAGE_ELEMENT_COUNT"];
//допустимые сортировки
$arAvalaibleSorts = array(
    'name' => 'NAME',
    'price' => 'PROPERTY_MINIMUM_PRICE',
    'shows' => 'shows'
);

if(isset($_REQUEST['order']) && array_key_exists($_REQUEST['order'],$arAvalaibleSorts)){
    $arParams["ELEMENT_SORT_FIELD"] = $arAvalaibleSorts[$_REQUEST['order']];
}else{
    $arParams["ELEMENT_SORT_FIELD"] = 'shows';
}
//порядок сортировки
if(isset($_REQUEST['dir']) && $_REQUEST['dir'] == 'desc'){
    $arParams["ELEMENT_SORT_ORDER"] = 'desc';
}else{
    $arParams["ELEMENT_SORT_ORDER"] = 'asc';
}
//вид списка
$arAvalaibleViews = array(
    'grid',
    'list' 
);
if(isset($_REQUEST['view']) && in_array($_REQUEST['view'],$arAvalaibleViews)){
    $view_mode = $_REQUEST['view'];
}else{
    $view_mode = 'grid'; 
}

if(isset($_REQUEST['order']) && array_key_exists($_REQUEST['order'],$arAvalaibleSorts)){
    $arParams["ELEMENT_SORT_FIELD"] = $arAvalaibleSorts[$_REQUEST['order']];
}else{
    $arParams["ELEMENT_SORT_FIELD"] = 'shows';
}
?>
<div class="page-title category-title">
    <h1>Товары <?=$arResult['NAME']?></h1><a name="products"></a>
</div>
<div class="row brand-products">
    <div class="col-md-3">
        <span class="catalog-all-sections">
            <a href="<?=$APPLICATION->GetCurPageParam('',array('SECTION'))?>" >Все разделы</a>
        </span>
<?
$GLOBALS['arrBrandSectionsFilter'] = array('PROPERTY' => array('MANUFACTURER' => $arResult['ID']));
?>
<?$APPLICATION->IncludeComponent(
	"fcm:catalog.section.list", 
	"tree", 
	array(
                "FILTER_NAME" => 'arrBrandSectionsFilter',
		"COMPONENT_TEMPLATE" => "tree",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"SECTION_ID" => '',
		"SECTION_CODE" => '',
		"COUNT_ELEMENTS" => "N",
		"TOP_DEPTH" => "4",
		"SECTION_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"VIEW_MODE" => "LINE",
		"SHOW_PARENT_NAME" => "N",
		"SECTION_URL" => $APPLICATION->GetCurPageParam('SECTION=#SECTION_CODE##products',array('SECTION')),
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "N",
		"ADD_SECTIONS_CHAIN" => "N"
	),
	false
);?>
        
    </div>
    <div class="col-md-9">
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section", 
	".default", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"COMPONENT_TEMPLATE" => ".default",
		"SHOW_TOOLBAR" => "Y",
		"AVAILABLE_SORTS" => $arAvalaibleSorts,
		"VIEW_MODE" => $view_mode,
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
		"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
		"FILTER_NAME" => "arrBrandFilter",
		"HIDE_NOT_AVAILABLE" => "N",
		"ELEMENT_COUNT" => "9",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFERS_LIMIT" => "5",
		"TEMPLATE_THEME" => "blue",
		"PRODUCT_DISPLAY_MODE" => "N",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"PRODUCT_SUBSCRIPTION" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "N",
		"SHOW_CLOSE_POPUP" => "N",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_SUBSCRIBE" => "Подписаться",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"PRODUCT_QUANTITY_VARIABLE" => "undefined",
		"SEF_MODE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "N",
		"CACHE_FILTER" => "Y",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "N",
		"BASKET_URL" => "/personal/basket.php",
		"USE_PRODUCT_QUANTITY" => "N",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(
		),
		"OFFERS_CART_PROPERTIES" => array(
		),
		"ADD_TO_BASKET_ACTION" => "ADD",
		"DISPLAY_COMPARE" => "N",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_CODE" => (isset($_REQUEST["SECTION"]))?  htmlspecialchars($_REQUEST['SECTION']):'',
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
		"PAGE_ELEMENT_COUNT" => "9",
		"BACKGROUND_IMAGE" => "-",
		"MESS_BTN_COMPARE" => "Сравнить",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "undefined",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"BROWSER_TITLE" => "-",
		"SET_META_KEYWORDS" => "N",
		"META_KEYWORDS" => "-",
		"SET_META_DESCRIPTION" => "N",
		"META_DESCRIPTION" => "-",
		"SET_LAST_MODIFIED" => "N",
		"USE_MAIN_ELEMENT_SECTION" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => "",
		"DISABLE_INIT_JS_IN_COMPONENT" => "N"
	),
	false
);?>
    </div>
</div>    
<?endif;?>

<?//trace($arResult)?>