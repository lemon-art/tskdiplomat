<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$defaultParams = array(
	'POSITION_FIXED' => 'Y',
	'POSITION' => 'top left'
);

$arParams = array_merge($defaultParams, $arParams);
unset($defaultParams);
if ($arParams['POSITION_FIXED'] != 'N')
	$arParams['POSITION_FIXED'] = 'Y';

$arParams['POSITION'] = trim($arParams['POSITION']);
$arParams['POSITION'] = explode(' ', $arParams['POSITION']);
if (empty($arParams['POSITION']) || count($arParams['POSITION']) != 2)
	$arParams['POSITION'] = array('top', 'left');
if ($arParams['POSITION'][0] != 'bottom')
	$arParams['POSITION'][0] = 'top';
if ($arParams['POSITION'][1] != 'right')
	$arParams['POSITION'][1] = 'left';

//add images to arResult and session
if(intval($arParams['PREVIEW_IMAGE_WIDTH']) <= 0 ) $arParams['PREVIEW_IMAGE_WIDTH'] = 90;
if(intval($arParams['PREVIEW_IMAGE_HEIGHT']) <= 0 ) $arParams['PREVIEW_IMAGE_HEIGHT'] = 90;

if(count($arResult) > 0){
    foreach ($arResult as $key => $arItem){
        if(!is_array($arItem['IMAGE'])){
            $rs = CIblockElement::GetList(array('ID'=> 'ASC'),array('ID' => $arItem['ID']),false,false,array('ID','IBLOCK_ID','DETAIL_PICTURE'));
            if($ar = $rs->GetNext()){
                if ($ar['DETAIL_PICTURE'] > 0) {
                    $image = CFile::ResizeImageGet($ar['DETAIL_PICTURE'], array('width'=>$arParams['PREVIEW_IMAGE_WIDTH'], 'height'=>$arParams['PREVIEW_IMAGE_HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
                    $arResult[$key]['IMAGE']['SRC'] = $image['src'];
                }

            }
        }
    }
    $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"] = $arResult;
}
?>