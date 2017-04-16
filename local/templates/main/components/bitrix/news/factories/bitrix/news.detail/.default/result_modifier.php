<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//set sidebar content - factory sections
$arSections = array();

    if(!empty($arResult['PREVIEW_PICTURE'])){
         $arImg = CFile::ResizeImageGet(
                $arResult['PREVIEW_PICTURE'], 
                array('width' => 145, 'height' => 145), 
                BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true, false, false, false
                );
        
        $arResult['PREVIEW_PICTURE']['SRC'] = $arImg['src'];
    }    

if($arResult['ID'] > 0){        
        
        $arFilter = array(
            'IBLOCK_ID' =>2,
            'PROPERTY'=>array('SUPPLIER' => $arResult['ID']),
            'CNT_ACTIVE' => 'Y',
        );
        
        $dbRes = CIBlockSection::GetList(array('LEFT_MARGIN'=>'ASC'),$arFilter,true,array('ID','IBLOCK_ID','NAME','CODE','DEPTH_LEVEL','IBLOCK_SECTION_ID','UF_*'));
                        
        while ($arS = $dbRes->GetNext()){
            if($arS['ELEMENT_CNT'] > 0){
                $arSections[$arS['ID']] = $arS;
                if($arS['DEPTH_LEVEL'] > 1){
                    $arSections[$arS['IBLOCK_SECTION_ID']]['SECTIONS'][] = $arS['ID'];
                }
            }
        }

       // trace($arSections);
}

$arResult['SECTIONS'] = $arSections;


// SEO OG PROPERTIES
$arResult['OG_PROPERTIES'] = array(
    'og_title' => $arResult['NAME'],
    'og_description' => ($arResult['PREVIEW_TEXT'])?$arResult['PREVIEW_TEXT']:$arResult['DETAIL_TEXT'],
);

if(is_array($arResult['DETAIL_PICTURE'])){
    $arResult['OG_PROPERTIES']['og_image'] = $arResult['DETAIL_PICTURE']['SRC'];
}elseif(is_array($arResult['PREVIEW_PICTURE'])){
    $arResult['OG_PROPERTIES']['og_image'] = $arResult['PREVIEW_PICTURE']['SRC'];
} 

// set cahe keys
global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_object($cp))
{
    $cp->arResult['IS_OBJECT'] = 'Y';
    $cp->arResult['SECTIONS'] = $arResult['SECTIONS'];
    $cp->arResult['OG_PROPERTIES'] = $arResult['OG_PROPERTIES'];
    $cp->SetResultCacheKeys(array('OG_PROPERTIES','SECTIONS','IS_OBJECT'));
    $arResult['IS_OBJECT'] = $cp->arResult['IS_OBJECT'];
    $arResult['OG_PROPERTIES'] = $cp->arResult['OG_PROPERTIES'];
    

}
?>
