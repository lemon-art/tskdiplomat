<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$page_element_count = (intval($_REQUEST['SIZEN_1']) > 0 )? intval($_REQUEST['SIZEN_1']): $arParams["PAGE_ELEMENT_COUNT"];
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

	$this->IncludeComponentTemplate();

?>