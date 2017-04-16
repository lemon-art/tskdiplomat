<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}
//колонки выводимые по умолчанию
$arResult['LIST_PROPERTIES_COLUMNS'] = array( 'fld_size','fld_mark','fld_color');
$arResult['LIST_PRICES_COLUMNS'] = array( 'BASE_PRICE','PRICE_DELIVERED');

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

if($arParams['CITY_ID'] > 0){
    $rs = CIBlockElement::GetList(array('ID'=>'ASC'),array('ID'=>$arParams['CITY_ID'],'IBLOCK_ID' => 9),false,false,array('ID','IBLOCK_ID','NAME'));
    $ob = $rs->GetNextElement();
    
    $arResult['CITY'] = $ob->GetFields();
    $arResult['CITY']['PROPERTIES'] = $ob->GetProperties();
    
}

// restore properties
$rs = CIBlockProperty::GetList(array('ID' => 'ASC'),array('IBLOCK_ID' => $arParams['IBLOCK_ID']));
while ($ap = $rs->GetNext()){
    $arResult['IBLOCK_PROPERTIES'][$ap['CODE']] = $ap;
    $arResult['IBLOCK_PROPERTIES_ID'][$ap['ID']] = $ap['CODE'];
}
                $rs = CCatalogGroup::GetList();
                while ($ap = $rs->GetNext()){
                    $arPrices[$ap['ID']] = $ap;
                }
//suppliers
$rs = CIBlockElement::GetList(
        array('NAME' => 'ASC'), 
        array('IBLOCK_ID' => 6),
        false,
        false,
        array('ID','NAME','PROPERTY_NAME_IN_PRICE','DETAIL_PAGE_URL')
        );
while ($ar = $rs->GetNext()) {
    $factoryShortName = explode(' ', trim($ar['NAME']));
    if(strlen($ar['PROPERTY_NAME_IN_PRICE_VALUE']) > 0){
        $ar['NAME'] = $ar['PROPERTY_NAME_IN_PRICE_VALUE'];
    }
    $ar['~DISPLAY_VALUE'] = '<a href="'.$ar['DETAIL_PAGE_URL'].'" title="'.$ar['~NAME'].'" class="_supplier_link">'.$ar['NAME'].'</a>';
    $arSuppliers[$ar['ID']] = $ar;
}                

//collect sections id
foreach ($arResult['ITEMS'] as $arItem){
    if($arItem['IBLOCK_SECTION_ID'] > 0){
        $arSectionsID[$arItem['IBLOCK_SECTION_ID']][] = $arItem['ID'];
    }
}
if (count($arSectionsID) > 0) {

    //родительски разделы
    $arFilter = array(
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'ID' => array_keys($arSectionsID),
    );

    $rs = CIBlockSection::GetList(
                    array('LEFT_MARGIN' => 'ASC'), $arFilter, false, array(
                'ID', 'IBLOCK_ID', 'NAME', 'SECTION_PAGE_URL', 'IBLOCK_SECTION_ID', 'UF_*'
    ));
    while ($as = $rs->GetNext()) {
        if (strlen($as['~UF_LISTVIEW']) > 0) {
            $as['UF_LISTVIEW'] = unserialize($as['~UF_LISTVIEW']);
        }
        
        foreach ($as['UF_LISTVIEW']['COLUMNS'] as $k => $v) {
            if($v['PROPERTY_ID'] == 'SUPPLIER'){
                unset($as['UF_LISTVIEW']['COLUMNS'][$k]);
                continue;
            }
            if (!array_key_exists($v['PROPERTY_ID'], $arResult['IBLOCK_PROPERTIES']))
                $arResult['IBLOCK_CALC_PROPERTIES'][] = $v['PROPERTY_ID'];
        }

        foreach ($as['UF_LISTVIEW']['PRICES'] as $k => $v) {
            if (!empty($arPrices[$v['PRICE_ID']]))
                $as['UF_LISTVIEW']['PRICES'][$k]['CODE'] = $arPrices[$v['PRICE_ID']]['NAME'];
        }
        
        $as['ITEMS'] = $arSectionsID[$as['ID']];

        $arResult['SECTIONS'][$as['ID']] = $as;
    }
}
//no image
	$arEmptyPreview = false;
	$strEmptyPreview = $this->GetFolder().'/images/no_photo.png';
	if (file_exists($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview))
	{
		$arSizes = getimagesize($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview);
		if (!empty($arSizes))
		{
			$arEmptyPreview = array(
				'SRC' => $strEmptyPreview,
				'WIDTH' => intval($arSizes[0]),
				'HEIGHT' => intval($arSizes[1])
			);
		}
		unset($arSizes);
	}

foreach ($arResult['ITEMS'] as $key => $arItem) {
    
        //offers
        if(count($arItem['OFFERS']) > 0){
            foreach ($arItem['OFFERS'] as $key => $arOffer){
                if(intval($arOffer['PROPERTIES']['CITY']['VALUE']) == intval($arParams['CITY_ID'])){
                    $arItem['PRICES'] = $arOffer['PRICES'];
                    $arItem['OFFER_PRICES'] = 'Y';
                    $arItem['CUROFFER_ID'] = $key;
                    $arItem['SPEC'] = $arOffer['PROPERTIES']['SPEC']['VALUE'];
                    $arItem['BUY_URL'] = $arOffer['BUY_URL'];
                    $arItem['ADD_URL'] = $arOffer['ADD_URL'];
                }
            }
        }
        
        //preview_image
        if(!empty($arItem['PREVIEW_PICTURE'])){    
            $imgTmp = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE'], array('width' => 90, 'height' => 90), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, TRUE);
            $arItem['PREVIEW_PICTURE']['SRC'] = $imgTmp['src'];
            $arItem['PREVIEW_PICTURE']['WIDTH'] = $imgTmp['width'];
            $arItem['PREVIEW_PICTURE']['HEIGHT'] = $imgTmp['height'];
            
        }elseif(!empty($arItem['DETAIL_PICTURE'])){
            $arItem['PREVIEW_PICTURE'] = CFile::GetFileArray($arItem['DETAIL_PICTURE']['ID']);
            $imgTmp = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE'], array('width' => 90, 'height' => 90), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, TRUE);
            $arItem['PREVIEW_PICTURE']['SRC'] = $imgTmp['src'];
            $arItem['PREVIEW_PICTURE']['WIDTH'] = $imgTmp['width'];
            $arItem['PREVIEW_PICTURE']['HEIGHT'] = $imgTmp['height'];

        }else{
            $arItem['PREVIEW_PICTURE'] = $arEmptyPreview;
        }   
        //format properties
	$factoryShortNameBuf = explode(' ',trim($arItem['PROPERTIES']['fld_Supplier']['VALUE']));
	$arItem['DISPLAY_PROPERTIES']['fld_Supplier']['VALUE'] = $factoryShortNameBuf[0];
        
        //вычисляемые поля
        if(count($arResult['IBLOCK_CALC_PROPERTIES']) > 0){
            MCGetCalcProperties($arResult['IBLOCK_CALC_PROPERTIES'], $arItem);
        /*    
        foreach ($arResult['IBLOCK_CALC_PROPERTIES'] as $code){
            switch ($code){
                case "SIZE":
                    $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = $arItem['PROPERTIES']['fld_size']['VALUE'];
                    break;
                case "SIZE_FULL":
                    $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = $arItem['PROPERTIES']['fld_width']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_length']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_height']['VALUE'];
                    break;
                case "SIZE_FULL2":
                    $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = $arItem['PROPERTIES']['fld_length']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_width']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_height']['VALUE'];
                    break;
                case "SIZE_FULL3":
                    $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = $arItem['PROPERTIES']['fld_length']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_width']['VALUE'] . 'x' . $arItem['PROPERTIES']['THICKNESS']['VALUE'];
                    break;
                case "SIZE_FULL4":
                    $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = $arItem['PROPERTIES']['fld_length']['VALUE'] . 'x' . $arItem['PROPERTIES']['fld_width']['VALUE'];
                    break;
                
                default :
            }
        }
         * 
         */
        }
        
        //SUPPLIER
        if($arItem['PROPERTIES']['SUPPLIER']['VALUE'] > 0 && is_array($arSuppliers[$arItem['PROPERTIES']['SUPPLIER']['VALUE']])){
            $arItem['DISPLAY_PROPERTIES']['SUPPLIER']['DISPLAY_VALUE'] = $arSuppliers[$arItem['PROPERTIES']['SUPPLIER']['VALUE']]['~DISPLAY_VALUE'];
        }
        
        if($arItem['DETAIL_PICTURE']['ID'] > 0){
            $imgForPopUp = CFile::ResizeImageGet($arItem['DETAIL_PICTURE']['ID'], array('width' => 225, 'height' => 160), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false, false, false, false);
        }else{
            $imgForPopUp = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']['ID'], array('width' => 225, 'height' => 160), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false, false, false, false);
        }
        
        $arPricesElement = MCPrices4Measures::GetPrices($arItem,$arParams['CITY_ID']);
        $arItem['PRICES'] = $arPricesElement['PRICES'];
        //вычисляемые цены
        
        MCPrices4Measures::Calculate($arItem);
        
        foreach ($arItem['MEASURE_PRICES'] as $code => $arMSPrices){
            if(array_key_exists($code,$arItem['PRICES'])){
                foreach ($arMSPrices as $c =>$msPrice){
                    $arItem['PRICES']['CALC_'.$code.'_'.$c] = $msPrice;
                }        
            }
        }
        
	$jsonData = array(
		'id'       => $arItem['ID'],
		'title'         => $arItem['NAME'],
		'image'         => $imgForPopUp['src'],
		'city'          => $arParams['cityProperty'][0]['NAME'],//TODO $cityTools->genitiveCity($arParams['cityProperty'][0]['NAME']),
                'attribures' => array(
        		'articul'       => $arItem['PROPERTIES']['fld_artikul']['VALUE'],
                	'supplier'       =>$arItem['DISPLAY_PROPERTIES']['SUPPLIER']['DISPLAY_VALUE'],
                        'destination'   => htmlspecialchars_decode($arItem['PROPERTIES']['fld_destination']['VALUE']),
                        'notation'      => htmlspecialchars_decode($arItem['PROPERTIES']['fld_application']['VALUE']),
                ),
            /*
                'properties' => array(
        		'length'        => array('title' =>$arItem['PROPERTIES']['fld_length']['NAME'] ,'value'=>$arItem['PROPERTIES']['fld_length']['VALUE']),
                	'width'         => array('title' =>$arItem['PROPERTIES']['fld_width']['NAME'] ,'value'=>$arItem['PROPERTIES']['fld_width']['VALUE']),
                        'height'        => array('title' =>$arItem['PROPERTIES']['fld_height']['NAME'] ,'value'=>$arItem['PROPERTIES']['fld_height']['VALUE']),
                        'surface'       => array('title' =>$arItem['PROPERTIES']['fld_surface']['NAME'] ,'value'=>$arItem['PROPERTIES']['fld_surface']['VALUE']),
                        'color'         => array('title' =>$arItem['PROPERTIES']['fld_color']['NAME'] ,'value'=>$arItem['PROPERTIES']['fld_color']['VALUE']),
                ),
             * 
             */
		'notes'         => htmlspecialchars_decode($arItem['PROPERTIES']['fld_MSales_notes']['VALUE']),
		'detailPageUrl' => $arItem['DETAIL_PAGE_URL']
	);
        $arParams['POPUP_PROPERTIES'] = array('fld_length','fld_width','fld_height','fld_surface','fld_color');
        
        foreach($arParams['POPUP_PROPERTIES'] as $propCode){
            if(!empty($arItem['PROPERTIES'][$propCode]['VALUE'])){
                $jsonData['properties'][$propCode]= array(
                    'title'=> $arItem['PROPERTIES'][$propCode]['NAME'],
                    'value'=> $arItem['PROPERTIES'][$propCode]['VALUE'],
                );
            }
        }
	foreach ($arItem['PRICES'] as $k => $price) {
                $jsonData['prices'][$k]= array(
                    'group' => $arResult['PRICES'][$k]['SELECT'],
                    'title' => GetMessage('TSC_PRICE_TITLE_'.$k),
                    'value' => $price['PRINT_VALUE']
                  );
                /*
		if (($price['CATALOG_GROUP_ID'] == $arParams['cityProperty'][0]['PROPERTY_ID_PRICE_VALUE']) && ($price['PRICE'] > 0)) {
			if (!isset($sectionHidePriceDetailPage[$arItem['IBLOCK_SECTION_ID']])) {
				$jsonData['price'] = $price['PRICE'];
				$jsonData['price_group_id'] = $price['CATALOG_GROUP_ID'];
			}
		}
		if (($price['CATALOG_GROUP_ID'] == $arParams['cityProperty'][0]['PROPERTY_ID_PRICE_DELIVERED_VALUE']) && ($price['PRICE'] > 0)) {
			$jsonData['delivery'] = $price['PRICE'];
			$jsonData['delivery_group_id'] = $price['CATALOG_GROUP_ID'];
		}
                 * 
                 */
	}

	$arItem['jsData'] = $jsonData;        
        
        $arItems[$arItem['ID']]= $arItem;
        //$arResult['SECTIONS'][$arItem['~IBLOCK_SECTION_ID']]['ELEMENTS'][]=$arItem['ID'];
}

$arResult['ITEMS'] = $arItems;


// set cahe keys
global $APPLICATION;

$cp = $this->__component; // объект компонента

if (is_object($cp))
{
    $cp->arResult['IS_OBJECT'] = 'Y';
    $cp->arResult['SECTIONS'] = $arResult['SECTIONS'];
    $cp->SetResultCacheKeys(array('SECTIONS','IS_OBJECT'));
    $arResult['IS_OBJECT'] = $cp->arResult['IS_OBJECT'];

}