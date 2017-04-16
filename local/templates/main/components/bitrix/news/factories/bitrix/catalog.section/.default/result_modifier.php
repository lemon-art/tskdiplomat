<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}
//колонки выводимые по умолчанию
$arResult['LIST_PROPERTIES_COLUMNS'] = array( 'fld_size','fld_mark','fld_color');
$arResult['LIST_PRICES_COLUMNS'] = array( 'BASE_PRICE','PRICE_DELIVERED');

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

if(intval($arParams['CITY_ID']) <=0 && defined(USER_CITY_ID) && USER_CITY_ID > 0){
    $arParams['CITY_ID'] = USER_CITY_ID;
}


$CElementMutator = new MCElementMutator;
$CElementMutator->InitCityID($arParams['CITY_ID']);
$arResult['CITY'] = $CElementMutator->GetCity();

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
       
        
        if($arItem['DETAIL_PICTURE']['ID'] > 0){
            $imgForPopUp = CFile::ResizeImageGet($arItem['DETAIL_PICTURE']['ID'], array('width' => 225, 'height' => 160), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false, false, false, false);
        }else{
            $imgForPopUp = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']['ID'], array('width' => 225, 'height' => 160), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false, false, false, false);
        }
        
        /********************************MUTATION*************************************************/
                $CElementMutator->SetItem($arItem);

                $CElementMutator->Mutate('list');
                $arItem = $CElementMutator->arItem;        
        
        if(!empty($arItem['MEASURE_PRICES'] )) {       
            foreach ($arItem['MEASURE_PRICES'] as $code => $arMSPrices){
                if(array_key_exists($code,$arItem['PRICES'])){
                    foreach ($arMSPrices as $c =>$msPrice){
                        $arItem['PRICES']['CALC_'.$code.'_'.$c] = $msPrice;
                    }        
                }
            }
        }
        /**************************** sections ********************************************************/
        if(empty($arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']])){
                    $arSection = $CElementMutator->GetSectionEnvironment();
            foreach ($arSection['UF_LISTVIEW']['COLUMNS'] as $k => $v){
                if($v['PROPERTY_ID'] == 'SUPPLIER'){
                    unset($arSection['UF_LISTVIEW']['COLUMNS'][$k]); 
                }
            }
            
            $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']] = $arSection;
            unset($arSection);
        }
        
        $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['ITEMS'][] = $arItem['ID'];
        
        
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