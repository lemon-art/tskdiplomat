<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//разделы текущего ИБ
$rs = CIBlockSection::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => $arParams['IBLOCK_ID']),
        false,
        array('ID','IBLOCK_ID','NAME','IBLOCK_SECTION_ID','SECTION_PAGE_URL')
    );

while ($ar = $rs->GetNext()){
    $arResult['SECTIONS'][$ar['ID']] = $ar;
}

//разделы каталога
$rs = CIBlockSection::GetList(
        array('LEFT_MARGIN' => 'ASC'),
        array('IBLOCK_ID' => 3),
        false,
        array('ID','IBLOCK_ID','NAME','IBLOCK_SECTION_ID','SECTION_PAGE_URL')
    );

while ($ar = $rs->GetNext()){
    $arResult['CATALOG_CATALOG_SECTIONS'][$ar['ID']] = $ar;
}


//запрашиваем разделы по всему каталогу товаров
$rs = CIBlockElement::GetList(
        array('IBLOCK_SECTION_ID'=>'ASC'),
        $arElementsFilter,
        array('IBLOCK_SECTION_ID','PROPERTY_SUPPLIER')
        );
        while ($ar = $rs->GetNext()){
            if($ar['CNT'] > 0){
                $arResult['PRODUCER_CATALOG_SECTIONS'][$ar['IBLOCK_SECTION_ID']][$ar['PROPERTY_SUPPLIER_VALUE']] = $ar['CNT'];
            }
        }        

$rs = CIBlockElement::GetList(array('SORT'=>'ASC','NAME'=>'ASC'),array('IBLOCK_ID'=>6),false,false,
        array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PAGE_URL',
            'IBLOCK_SECTION_ID'
            ));
while ($ar = $rs->GetNext()){
        if(!empty($ar['PREVIEW_PICTURE'])){
            $ar['PREVIEW_PICTURE'] = CFile::GetFileArray($ar['PREVIEW_PICTURE']);
            $arImg = CFile::ResizeImageGet(
                $ar['PREVIEW_PICTURE'], 
                array('width' => 60, 'height' => 60), 
                BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true, false, false, false
                );
            $ar['PREVIEW_PICTURE']['SRC'] = $arImg['src'];
        }
    
    $arResult['PRODUCERS'][$ar['ID']] = $ar;
}
//trace($arResult['PRODUCERS']);

$arItems = array();


foreach ($arResult['ITEMS'] as $arItem){
//trace($arItem);    
    if(!empty($arItem['PREVIEW_PICTURE'])){
         $arImg = CFile::ResizeImageGet(
                $arItem['PREVIEW_PICTURE'], 
                array('width' => 60, 'height' => 60), 
                BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true, false, false, false
                );
        
        $arItem['PREVIEW_PICTURE']['SRC'] = $arImg['src'];
    } 
    if(!empty($arItem['PROPERTIES']['CATALOG_SECTIONS']['VALUE'])){
        foreach ($arItem['PROPERTIES']['CATALOG_SECTIONS']['VALUE'] as $sid){
            if(count($arResult['PRODUCER_CATALOG_SECTIONS'][$sid])){
                foreach ($arResult['PRODUCER_CATALOG_SECTIONS'][$sid] as $k => $v){
                    $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['PRODUCERS'][$k][$arItem['ID']] = $arItem['ID'];
                }
            }
        }
    }
    
    $arItems[$arItem['ID']] = $arItem;
    
    $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['ITEMS'][] = $arItem['ID'];
    //break;
}

//trace($arResult['SECTIONS']);

$arResult['ITEMS'] = $arItems;

unset($arItems);

// set cahe keys
global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_object($cp))
{
    $cp->arResult['IS_OBJECT'] = 'Y';
    $cp->arResult['SECTIONS'] = $arResult['SECTIONS'];
    $cp->arResult['PRODUCERS'] = $arResult['PRODUCERS'];
    $cp->SetResultCacheKeys(array('SECTIONS','PRODUCERS','IS_OBJECT'));
    $arResult['IS_OBJECT'] = $cp->arResult['IS_OBJECT'];

}
?>        