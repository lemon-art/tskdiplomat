<?php
//минимальная сумма заказа
$GLOBALS['MIN_ORDER_SUM'] = 2000;

AddEventHandler("sale", "OnBeforeOrderAdd", "CheckMinTotal");
function CheckMinTotal($arFields) {
    AddMessage2Log($arFields);
/*    
  if($arFields["PRICE"] < 3000 ) {
    $error = "Минимальная сумма заказа в нашем магазине для Москвы и МО - 3000 руб.";

    // что дальше???
    
  }
 * 
 */
}

function trace($arr,$cheadmin=false,$absolute=false){
	global $USER;
    if($cheadmin && !$USER->IsAdmin()) return false;
    
    if($absolute) echo '<dev style="position:absolute;width:600px;z-index:10000;">';
    echo '<pre>'.print_r($arr, true).'</pre>';
    if($absolute) echo '</dev>';
}

/**
 * Построение числа с падежом в количественных выражениях (1 товар, 5 товаров, 23 товара и т.д.)
 *
 * @param int $number
 * @param string $padej1
 * @param string $padej2
 * @param string $padej3
 * @return string
 */
function padej ($number, $padej1, $padej2, $padej3, $show_with_number = true) {
    if (is_array($padej1)) {
        $padej1 = array_values($padej1);
        $padej1 = $padej1[0];
        $padej2 = $padej1[1];
        $padej3 = $padej1[2];
    }
    // 5, "обзор", "обзора", "обзоров"
    if (
            ($number>=5 AND $number<=20) 
        OR 
            ($number > 20 AND substr($number, -1, 1) >= 5 AND substr($number, -1, 1) <= 9) 
        OR 
            substr($number, -1, 1) == 0
        OR 
            (strlen($number)>2 AND intval(substr($number, -2, 2))>=5 AND intval(substr($number, -2, 2))<=20)
        ) {
        return (($show_with_number)?$number." ":"").$padej3;
    } elseif (substr($number, -1, 1) == 1) {
        return (($show_with_number)?$number." ":"").$padej1;
    } else {
        return (($show_with_number)?$number." ":"").$padej2;
    }
}

					

if (preg_match('#PAGEN#', $_SERVER['REQUEST_URI'])) {
    AddEventHandler("main", "OnEpilog", "AddPagenavigation2Title");
    function AddPagenavigation2Title(){
        echo '<!--event_title-->';
        global $APPLICATION;
		
        if(!empty($GLOBALS['TPL_SYSTEM_PAGENAVIGATION_RESULT'])){
            foreach ($GLOBALS['TPL_SYSTEM_PAGENAVIGATION_RESULT'] as $key => $arNav){

				if(isset($_REQUEST['PAGEN_'.$key]) && intval($_REQUEST['PAGEN_'.$key]) > 0){
                    
					
					$title = $APPLICATION->GetPageProperty('title');
					if($APPLICATION->GetTitle() == "Новости") $title = "Новости";
					
					
                    $description = $APPLICATION->GetPageProperty('description');
					
                    $title .= " Страница {$arNav['NavPageNomer']} из {$arNav['NavPageCount']}";
                    $description .= " Страница {$arNav['NavPageNomer']} из {$arNav['NavPageCount']}";
                    
                    $APPLICATION->SetPageProperty('title',$title);
					
					$APPLICATION->SetPageProperty("description", $description);
					
                    echo "<!--event $title-->";
                }
            }
        }
    }
}


AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("MyClass", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("MyClass", "OnAfterIBlockElementAddHandler"));
AddEventHandler("catalog", "OnBeforePriceAdd", Array("MyClass", "OnBeforePriceAddHandler"));

class MyClass
{
	function OnBeforePriceAddHandler($arFields){
		if ($arFields['CATALOG_GROUP_ID'] == 1){
			$arSelect = Array('ID', 'IBLOCK_ID', 'PROPERTY_META_DESCRIPTION');
			$arFilter = Array('ID' => $arFields['PRODUCT_ID']);
			$rsFields = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			if ($arFieldsEl = $rsFields->Fetch()) {
				if (strpos($arFieldsEl['PROPERTY_META_DESCRIPTION_VALUE'], '#PRICE#') !== false){
					$val = str_replace('#PRICE#', $arFields['PRICE'], $arFieldsEl['PROPERTY_META_DESCRIPTION_VALUE']);
					CIBlockElement::SetPropertyValuesEx($arFieldsEl['ID'], $arFieldsEl['IBLOCK_ID'], array('META_DESCRIPTION' => $val));
				}
			}
		}
	}

	function OnAfterIBlockElementAddHandler(&$arFields)
	{
		if ($arFields['IBLOCK_ID'] == 3) {
			$arDescription = array(
				'В наличии',
				'В продаже',
				'Покупайте',
				'Предлагаем',
				'В наличии и под заказ',
				''
			);
			$arDescriptionP = array(
				'по цене от',
				'по стоимости от',
				'от',
			);

			$val = $arDescription[rand(0, 5)] . ' ' . $arFields['NAME'];
			$val .= ' ' . $arDescriptionP[rand(0, 2)] . ' #PRICE#р.';

			reset($arFields['IBLOCK_SECTION']);
			$section = current($arFields['IBLOCK_SECTION']);
			if ($section){
				$arFilter = array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'ID' => $section);
				$rsSections = CIBlockSection::GetList(array(), $arFilter, false, array('NAME'));
				if ($arSection = $rsSections->Fetch()) {
					$val .= ' ' . $arSection['NAME'] . ' ';
				}
			}

			$arDescriptionE = array(
				'на выгодных условиях.',
				'с доставкой по Москве и в ваш регион.',
				'представлены с характеристиками.',
				'с описанием и ценами.',
				'различных назначений.',
				'в широком ассортименте.',
				'в качественном ассортименте.',
			);
			$val .= ' ' . $arDescriptionE[rand(0, 6)];

			if (mb_strlen($val) < 130){
				$nav = CIBlockSection::GetNavChain($arFields['IBLOCK_ID'], $section);
				if ($arSectionPath = $nav->GetNext()){
					$val .= ' ' . $arSectionPath['NAME'] . ' от компании ТСК Дипломат.';
				}

			}

			CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFields['IBLOCK_ID'], array('META_DESCRIPTION' => $val));
		}
	}

	function OnBeforeIBlockElementAddHandler(&$arFields)
	{
		if ($arFields['IBLOCK_ID'] == 3){
			$arTitle = array(
				': цена, характеристики',
				': купить на сайте',
				': купить в интернет-магазине',
				': характеристики, описание',
				': характеристики',
				': купить по низкой цене',
				': характеристики, наличие',
				': цена, наличие',
			);
			$arFields['PROPERTY_VALUES'][20][n0]['VALUE'] = $arFields['NAME'].$arTitle[rand(0, 7)];
		}
	}
}