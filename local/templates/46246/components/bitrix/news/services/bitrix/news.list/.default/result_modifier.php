<?
foreach ($arResult['ITEMS'] as $key => $arItem){
    if(!empty($arItem['PROPERTIES']['PRICE']) && strlen($arItem['PROPERTIES']['PRICE']['VALUE']) > 0){
        
        $arItem['PRICE'] = $arItem['PROPERTIES']['PRICE'];
        
        if(CModule::IncludeModule("currency")){ 
            $arItem['PRICE']['DISPLAY_VALUE'] = CurrencyFormat($arItem['PROPERTIES']['PRICE']['VALUE'], "RUB");
        }else{
            $arItem['PRICE']['DISPLAY_VALUE'] = number_format($arItem['PROPERTIES']['PRICE']['VALUE'],0,"."," ") . "р.";
        }    
        
        unset($arItem['DISPLAY_PROPERTIES']['PRICE']);
        
        $arItem['NORM_H']['DISPLAY_VALUE'] = $arItem['PROPERTIES']['NORM_H']['VALUE'];
        
        unset($arItem['DISPLAY_PROPERTIES']['NORM_H']);
        
        $arResult['ITEMS'][$key]= $arItem;
    }
}