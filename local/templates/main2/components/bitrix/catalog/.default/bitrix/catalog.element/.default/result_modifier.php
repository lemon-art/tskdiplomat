<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
   die();
//add linked elements to cache

global $APPLICATION;

//редирект для удаления дублей => /section1/element/ or /section2/element/ redirect to /section1/section2/element/

$res = CIBlockElement::GetByID($arResult['ID']);
if($ar = $res->GetNext()) {

   if ($ar['IBLOCK_SECTION_ID']) {
      $res = CIBlockSection::GetByID($ar['IBLOCK_SECTION_ID']);
      if ($as = $res->GetNext()) {
          /*?>
          <!-- result_modifier 
          $arParams['SECTION_ID']: <?=$arParams['SECTION_ID']?><br/>
          $arParams['SECTION_CODE']: <?=$arParams['SECTION_CODE']?><br/>
          $arResult['IBLOCK_SECTION_ID']: <?=$arResult['IBLOCK_SECTION_ID']?><br/>
          $arResult['SECTION']['ID']: <?=$arResult['SECTION']['ID']?><br/>
          $ar['IBLOCK_SECTION_ID']: <?=$ar['IBLOCK_SECTION_ID']?><br/>
          $as['ID']: <?=$as['ID']?><br/>
          $arResult['DETAIL_PAGE_URL']: <?=$arResult['DETAIL_PAGE_URL']?><br/>
          
          -->
          <?*/
      }
   }
}

if($arResult['IBLOCK_SECTION_ID'] >0 && $arResult['SECTION']['ID'] > 0 && $arResult['IBLOCK_SECTION_ID'] !== $arResult['SECTION']['ID']){
    $res = CIBlockElement::GetByID($arResult['ID']);
    if($ar = $res->GetNext()) {
        echo '<!-- $ar';
        echo $ar['DETAIL_PAGE_URL'];
        echo '-->';
        if(strlen($ar['DETAIL_PAGE_URL']) > 0){
                    LocalRedirect($ar['DETAIL_PAGE_URL'],true,"301 Moved permanently");
        }
    }
}
//$url = "/catalog".$good_url;
//if( $url != $APPLICATION->GetCurPage(false)) 
//    LocalRedirect($url);

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
if(strlen($arResult['PROPERTIES']['MANUFACTURER']['VALUE']) > 0){
   $rs = CIBlockElement::GetList(array('ID' => 'ASC'),array('IBLOCK_ID' => $arResult['PROPERTIES']['MANUFACTURER']['LINK_IBLOCK_ID'],'ID' => $arResult['PROPERTIES']['MANUFACTURER']['VALUE'],'ACTIVE'=> 'Y'),false,false,array('ID','IBLOCK_ID','NAME','DETAIL_PAGE_URL','DETAIL_PICTURE'));
   if($ar = $rs->GetNext()){
        $s = '';
        if($ar['DETAIL_PICTURE'] > 0){
            $ar['PICTURE'] = CFile::GetFileArray($ar['DETAIL_PICTURE']);
            $s .= '<a href="'.$ar['DETAIL_PAGE_URL'].'" title="'.GetMessage('TPL_PRODUCT_GP_BRAND_LINK_TITLE',$ar['NAME']).'">';
            $s .= '<img src="'.$ar['DETAIL_PICTURE']['SRC'].'" alt="'.GetMessage('TPL_PRODUCT_GP_BRAND_LOGO_ALT',array('#NAME#' => $ar['NAME'])).'"/>';
            $s .= '</a>';
        }
        $s .= '<a href="'.$ar['DETAIL_PAGE_URL'].'" title="'.GetMessage('TPL_PRODUCT_GP_BRAND_LINK_TITLE',array('#NAME#' => $ar['NAME'])).'">'.$ar['NAME'].'</a>';

        //$arResult['PROPERTIES']['MANUFACTURER']['DISPLAY_VALUE'] = $s;
        //$arResult['PROPERTIES']['MANUFACTURER']['VALUE'] = $ar;
        $arResult['BRAND'] =  $ar;
        //unset($arResult['PROPERTIES']['MANUFACTURER'],$arResult['DISPLAY_PROPERTIES']['MANUFACTURER']);
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