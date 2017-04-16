<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
   die();
if (is_array($arResult['PROPERTIES']['LINKED_ELEMENTS']['VALUE']) && count($arResult['PROPERTIES']['LINKED_ELEMENTS']['VALUE']) > 0):
    
    $GLOBALS['DETAIL_LINKED_ELEMENTS'] = $arResult['PROPERTIES']['LINKED_ELEMENTS']['VALUE'];

endif;
?>
<?

//print_r( $arResult);
?>