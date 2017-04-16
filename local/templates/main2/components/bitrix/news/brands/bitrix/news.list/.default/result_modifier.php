<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 260;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 120;

global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_array($arResult["ITEMS"]) AND !empty($arResult["ITEMS"])):
    
    $rs = CIBlockElement::GetList(
            array('PROPERTY_MANUFACTURER' => 'ASC'),
            array(
                'IBLOCK_ID' => 3,
                'ACTIVE'=>'Y',
                'PROPERTY_MANUFACTURER' => $arResult['ELEMENTS']
            ),
            array('PROPERTY_MANUFACTURER')
            );
            
    while($ar = $rs->GetNext()){
        //trace($ar);
        $arTotals[$ar['PROPERTY_MANUFACTURER_VALUE']] = $ar['CNT'];
    }
    
    //trace($arTotals);
    
    foreach ($arResult["ITEMS"] as $key => $arItem){
            
            if ($arItem['DETAIL_PICTURE']['ID'] > 0) {
                $image = CFile::ResizeImageGet($arItem['DETAIL_PICTURE']['ID'], array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                $arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
            }
            
            //$arItem['PREVIEW_PICTURE']['SRC'] = $image['src'];
            $rs = CIBlockSection::GetList(
                array('LEFT_MARGIN' => 'ASC'),
                array('IBLOCK_ID' => 3, 'PROPERTY' =>array('MANUFACTURER' => $arItem['ID'])),
                array('CNT_ACTIVE' => 'Y','ELEMENT_SUBSECTIONS' => 'N'),
                array('ID','NAME','IBLOCK_ID','DEPTH_LEVEL','SECTION_PAGE_URL')    
            );
            
            $rs->SetUrlTemplates('',$arItem['DETAIL_PAGE_URL'].'?SECTION=#SECTION_CODE#');
            while($ar = $rs->GetNext()){
                $arItem['CATEGORIES'][] = $ar;
            }
            $arItem['PRODUCTS_COINT'] = $arTotals[$arItem['ID']];
            
           $arItems[$arItem['ID']] = $arItem;
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