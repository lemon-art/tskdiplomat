<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
   die();

global $APPLICATION;

$cp = $this->__component; // объект компонента


if($arParams['PREVIEW_IMAGE_WIDTH'] <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 400;
if($arParams['PREVIEW_IMAGE_HEIGHT'] <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 400;


if(!empty($arResult['DETAIL_PICTURE'])){
    
                $image = CFile::ResizeImageGet(
                        $arResult['DETAIL_PICTURE']['ID'], 
                        array(
                            'width'=>$arParams['PREVIEW_IMAGE_WIDTH'],
                            'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), 
                            BX_RESIZE_IMAGE_PROPORTIONAL, 
                            true
                        ); 
            $arResult['DETAIL_PICTURE']['SRC']=$image['src'];
}


if(strlen($_REQUEST['SECTION']) > 0){
    
    $rs = CIBlockSection::GetList(array(),array('IBLOCK_ID' => 3, 'CODE' => htmlspecialchars($_REQUEST['SECTION'])));
    
    if( $arSection = $rs->GetNext()){
        $arResult['PRODUCT_SECTION'] = $arSection;
        $arResult['PROPERTIES']['BROWSER_TITLE']['VALUE'] = "Товары {$arResult['NAME']} в категории {$arSection['NAME']}";
        $arResult['PROPERTIES']['ZH1']['VALUE'] = "Товары {$arResult['NAME']} / {$arSection['NAME']}";
        
        $arResult['PROPERTIES']['BROWSER_TITLE']['VALUE'] = str_replace(
                                    array('#SECTION_NAME#','#ELEMENT_NAME#'), 
                                    array($arSection['NAME'],$arResult['NAME']), 
                                    $arParams['BRAND_SECTION_TITLE_MASK']
                                );
        $arResult['PROPERTIES']['META_DESCRIPTION']['VALUE'] = str_replace(
                                    array('#SECTION_NAME#','#ELEMENT_NAME#'), 
                                    array($arSection['NAME'],$arResult['NAME']), 
                                    $arParams['BRAND_SECTION_DESCRIPTION_MASK']
                                );
        $arResult['PROPERTIES']['KEYWORDS']['VALUE'] = str_replace(
                                    array('#SECTION_NAME#','#ELEMENT_NAME#'), 
                                    array($arSection['NAME'],$arResult['NAME']), 
                                    $arParams['BRAND_SECTION_KEYWORDS_MASK']
                                );
    }
}

if (is_object($cp))
{
    // добавим в arResult
    $cp->arResult['IS_OBJECT'] = 'Y';
    $cp->SetResultCacheKeys(array('PRODUCT_SECTION'));
    // сохраним их в копии arResult, с которой работает шаблон
    $arResult['PRODUCT_SECTION'] = $cp->arResult['PRODUCT_SECTION'];
    $arResult['IS_OBJECT'] = $cp->arResult['IS_OBJECT'];

}
?>
