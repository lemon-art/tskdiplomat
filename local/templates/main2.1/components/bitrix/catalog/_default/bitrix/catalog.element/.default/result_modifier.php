<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
   die();
//add linked elements to cache

global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_object($cp))
{
    $cp->SetResultCacheKeys(array('LINKED_ELEMENTS','IS_OBJECT'));
}


//general props
$arResult['GENERAL_PROPERTIES'] = array();
//articul
if(strlen($arResult['PROPERTIES']['ARTICLE']['VALUE']) > 0){
   $arResult['GENERAL_PROPERTIES']['ARTICLE'] =  $arResult['PROPERTIES']['ARTICLE'];
   $arResult['GENERAL_PROPERTIES']['ARTICLE']['DISPLAY_VALUE'] = $arResult['GENERAL_PROPERTIES']['ARTICLE']['~VALUE'];
   unset($arResult['PROPERTIES']['ARTICLE'],$arResult['DISPLAY_PROPERTIES']['ARTICLE']);
}
//brand
if(strlen($arResult['PROPERTIES']['BRAND']['VALUE']) > 0){
   $rs = CIBlockElement::GetList(array('ID' => 'ASC'),array('IBLOCK_ID' => $arResult['PROPERTIES']['BRAND']['LINK_IBLOCK_ID'],'ID' => $arResult['PROPERTIES']['BRAND']['VALUE'],'ACTIVE'=> 'Y'),false,false,array('ID','IBLOCK_ID','NAME','DETAIL_PAGE_URL','PREVIEW_PICTURE'));
   if($ar = $rs->GetNext()){
        $s = '';
        if($ar['PREVIEW_PICTURE'] > 0){
            $ar['PREVIEW_PICTURE'] = CFile::GetFileArray($ar['PREVIEW_PICTURE']);
            $s .= '<a href="'.$ar['DETAIL_PAGE_URL'].'" title="'.GetMessage('TPL_PRODUCT_GP_BRAND_LINK_TITLE',$ar['NAME']).'">';
            $s .= '<img src="'.$ar['PREVIEW_PICTURE']['SRC'].'" alt="'.GetMessage('TPL_PRODUCT_GP_BRAND_LOGO_ALT',array('#NAME#' => $ar['NAME'])).'"/>';
            $s .= '</a>';
        }
        $s .= '<a href="'.$ar['DETAIL_PAGE_URL'].'" title="'.GetMessage('TPL_PRODUCT_GP_BRAND_LINK_TITLE',array('#NAME#' => $ar['NAME'])).'">'.$ar['NAME'].'</a>';

        $arResult['PROPERTIES']['BRAND']['DISPLAY_VALUE'] = $s;
        $arResult['PROPERTIES']['BRAND']['VALUE'] = $ar;
        $arResult['GENERAL_PROPERTIES']['BRAND'] =  $arResult['PROPERTIES']['BRAND'];
        unset($arResult['PROPERTIES']['BRAND'],$arResult['DISPLAY_PROPERTIES']['BRAND']);
   }
}

$arResult['PICTURES'] = array();
if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 308;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 308;
if(intval($arParams['THUMBNAIL_IMAGE_WIDTH']) <= 0 ) $arParams['THUMBNAIL_IMAGE_WIDTH'] = 95;
if(intval($arParams['THUMBNAIL_IMAGE_HEIGHT']) <= 0 ) $arParams['THUMBNAIL_IMAGE_HEIGHT'] = 95;

if(is_array($arResult['DETAIL_PICTURE'])){
    $arResult['PICTURES'][] = $arResult['DETAIL_PICTURE'];
    if(is_array($arResult['MORE_PHOTO'])){
       $arResult['PICTURES'] = array_merge($arResult['PICTURES'],$arResult['MORE_PHOTO']);
       unset($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']);
    }
}
foreach ($arResult['PICTURES'] as $k=>$i){
    
            if ($i['ID'] > 0) {
                $image = CFile::ResizeImageGet($i['ID'], array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                $i['PREVIEW'] = $image;
                $image = CFile::ResizeImageGet($i['ID'], array('width'=>$arParams['THUMBNAIL_IMAGE_WIDTH'], 'height'=>$arParams['THUMBNAIL_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                $i['THUMBNAIL'] = $image;
            }
            $arResult['PICTURES'][$k]=$i;
}

// BUG in GetDisplayValue with 1 value
if(count($arResult['DISPLAY_PROPERTIES']['FILES']['VALUE']) == 1){
    $arResult['DISPLAY_PROPERTIES']['FILES']['FILE_VALUE'] = array($arResult['DISPLAY_PROPERTIES']['FILES']['FILE_VALUE']);
}
?>