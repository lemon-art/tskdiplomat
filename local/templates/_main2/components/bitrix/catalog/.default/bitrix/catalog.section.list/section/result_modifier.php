<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 220;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 220;

global $APPLICATION;

$cp = $this->__component; // объект компонента

if($arResult['SECTION']["PICTURE"] > 0){
       $arResult['SECTION']["PICTURE"] = CFile::ResizeImageGet(
               $arResult['SECTION']["PICTURE"], 
               array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), 
               BX_RESIZE_IMAGE_PROPORTIONAL, 
               true
               ); 
}

if (is_array($arResult["SECTIONS"]) AND !empty($arResult["SECTIONS"])):
    
    foreach ($arResult["SECTIONS"] as $key => $arItem){
            
            if($arItem['DEPTH_LEVEL'] == $arResult['SECTION']['DEPTH_LEVEL'] + 1){
                
                if ($arItem['PICTURE'] > 0) {
                    $image = CFile::ResizeImageGet($arItem['PICTURE'], array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                    $arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
                }
            
                $arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
            
                $arItems[$arItem['ID']] = $arItem;
           
            }elseif ($arItem['DEPTH_LEVEL'] == $arResult['SECTION']['DEPTH_LEVEL'] + 2) {
                
                if($arItem['IBLOCK_SECTION_ID'] > 0 && $arItem['IBLOCK_SECTION_ID'] !== $arResult['SECTION']['ID']){
                    $arItems[$arItem['IBLOCK_SECTION_ID']]['SUBSECTIONS'][] = $arItem;
                }
                
            }
            
    }
    
    $arResult['SECTIONS'] = $arItems;
endif;

    if (is_object($cp))
        {
            // добавим в arResult компонента два поля - MY_TITLE и IS_OBJECT
            $cp->arResult['ITEMS'] = $arItems;
            $cp->arResult['IS_OBJECT'] = 'Y';
            $cp->SetResultCacheKeys(array('ITEMS','IS_OBJECT'));
            // сохраним их в копии arResult, с которой работает шаблон
            $arResult['ITEMS'] = $cp->arResult['ITEMS'];
            $arResult['IS_OBJECT'] = $cp->arResult['IS_OBJECT'];
        }   
?>