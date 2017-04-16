<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
   die();
//add linked elements to cache

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

?>