<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arResult['LIST_PAGE_URL'] = CComponentEngine::MakePathFromTemplate($arResult['LIST_PAGE_URL'],array());

if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 120;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 90;

global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_array($arResult["ITEMS"]) AND !empty($arResult["ITEMS"])):
    foreach ($arResult["ITEMS"] as $key => $arItem){
            
            if ($arItem['DETAIL_PICTURE']['ID'] > 0) {
                $image = CFile::ResizeImageGet($arItem['DETAIL_PICTURE']['ID'], array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                $arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
            }
            
            //$arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
            
           $arItems[$key] = $arItem;
    }
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
endif;
?>