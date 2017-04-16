<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 200;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 200;

if(count($arResult['SECTIONS']) > 0){
    foreach($arResult['SECTIONS'] as $key => $arSection){

        if(is_array($arSection["PICTURE"])){
             $arSection["DISPLAY_PICTURE"] = CFile::ResizeImageGet(
                $arSection["PICTURE"], 
                array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), 
                BX_RESIZE_IMAGE_PROPORTIONAL, 
                true
               ); 
}
        
        $arNewSections[$arSection['ID']] = $arSection;
        if($arSection['DEPTH_LEVEL'] > 1){
            $arNewSections[$arSection['IBLOCK_SECTION_ID']]['SUBSECTIONS'][] = $arSection['ID'];
        }
        
    }
    $arResult['SECTIONS'] = $arNewSections;
    unset($arNewSections);
}
?>