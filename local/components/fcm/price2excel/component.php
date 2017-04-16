<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arParams['UPLOAD_DIR'] == ''){
    $arParams['UPLOAD_DIR']  = '/upload/price2excel/';
}
if($arParams['UPLOAD_FILE_NAME'] == ''){
    $arParams['UPLOAD_FILE_NAME']  = "tskdiplomat_price.xlsx";
}


if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

if ($this->InitComponentTemplate())
{
    $template = & $this->GetTemplate();
    $arResult['TEMPLATE_PATH'] = $template->GetFolder();
}else{
    
	ShowError("Не найден шаблон компонента");
	return;
}



// Подключаем класс для работы с excel
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/PHPExcel/PHPExcel.php");
//Подгружаем стили
require $_SERVER['DOCUMENT_ROOT'].$arResult['TEMPLATE_PATH'].'/styles.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("TSK Diplomat")
        ->setLastModifiedBy("Компания TSK Diplomat")
        ->setTitle("Прайс-лист компании TSK Diplomat")
        ->setSubject("TSK Diplomat price-list")
        ->setDescription("прайс-лист компании TSK Diplomat")
        ->setKeywords("ТСК Дипломат,TSK Diplomat, price-list")
        ->setCategory("TSK Diplomat, price");

$SheetIndex = -1;

//get catalog data


	global $CACHE_MANAGER;
        
	$arConvertParams = array();
        
	if ('Y' == $arParams['CONVERT_CURRENCY'])
	{
		if (!CModule::IncludeModule('currency'))
		{
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		}
		else
		{
			$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
			if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo)))
			{
				$arParams['CONVERT_CURRENCY'] = 'N';
				$arParams['CURRENCY_ID'] = '';
			}
			else
			{
				$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
				$arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
			}
		}
	}
	$arResult['CONVERT_CURRENCY'] = $arConvertParams;

	$bIBlockCatalog = false;
	$arCatalog = false;
	$bCatalog = CModule::IncludeModule('catalog');
	if ($bCatalog)
	{
		$arCatalog = CCatalog::GetByID($arParams["IBLOCK_ID"]);
		if (!empty($arCatalog) && is_array($arCatalog))
			$bIBlockCatalog = true;
	}
	$arResult['CATALOG'] = $arCatalog;
	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);
        //trace($arResult['PRICES']);
        $arFilter = array(
		"ACTIVE"=>"Y",
		"GLOBAL_ACTIVE"=>"Y",
		"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE"=>"Y",
	);
	//ORDER BY
	$arSort = array(
		$arParams["SECTION_SORT_FIELD"] => $arParams["SECTION_SORT_ORDER"],
		"ID" => "ASC",
	);
	//SELECT
	$arSelect = array('ID', 'IBLOCK_ID', 'NAME', 'DEPTH_LEVEL', 'SECTION_PAGE_URL', 'IBLOCK_SECTION_ID', 'UF_*');

	$currencyList = array();

	$bGetPropertyCodes = !empty($arParams["PROPERTY_CODE"]);
	$bGetProductProperties = !empty($arParams["PRODUCT_PROPERTIES"]);
	$bGetProperties = $bGetPropertyCodes || $bGetProductProperties;

	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
        
        //восстанавливаем ид города для свойства список

        $rs = CIBlockProperty::GetList(
                Array("SORT"=>"ASC"), 
                Array(
                    "IBLOCK_ID"=>$arParams['IBLOCK_ID'], 
                    ">DEPTH_LEVEL" => 1
                    )
                );
        while( $ar = $rs->Fetch()){
            $arProperties[$ar['CODE']] = $ar;
        }
        //trace($arProperties);
	while($arSection = $rsSections->GetNext())
	{
            //trace($arSection);
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], $arResult["ID"]);
		$arSection["IPROPERTY_VALUES"] = $ipropValues->getValues();
                
                if (strlen($arSection['~UF_LISTVIEW']) > 0) {
                    $arSection['UF_LISTVIEW'] = unserialize($arSection['~UF_LISTVIEW']);
                }
                //trace($arSection);break;
                
                if($arSection['DEPTH_LEVEL'] == 2){
                    if($SheetIndex >= 0){
                        //закрываем предидущий лист
                        //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$SheetRow+2)->getAlignment()->setWrapText(true);
                        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$SheetRow+2,  html_entity_decode($arCity['PROPERTY_DELIVERY_PRICE_REMARK_VALUE']));
                        //$objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow(1,$SheetRow+2,6,$SheetRow+2);
                        //include $_SERVER['DOCUMENT_ROOT'].$arResult['TEMPLATE_PATH'].'/cheet_footer.php';
                        
                        //защищаем лист
                        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
                        
                        //создаем новый лист
                        $objWorkSheet = $objPHPExcel->createSheet();
                        
                        $columns = count($arParams['TABLE_COLUMNS']) + count($arParams['PRICES']) + 1;
                        
                    }
                    
                    $SheetIndex++;
                    $SheetRow = 8;
                    //выводим шапку на лист
                    include $_SERVER['DOCUMENT_ROOT'].$arResult['TEMPLATE_PATH'].'/cheet_header.php';
                    
                    //echo '// MEW SHEET <br/>';
                    
                }elseif ($arSection['DEPTH_LEVEL'] > 2) {
                    
                    $Sheet = $objPHPExcel->getActiveSheet();
                    $SheetRow++;
                    //выводим разделы на лист
                    $Sheet->mergeCellsByColumnAndRow(1,$SheetRow,6,$SheetRow);
                    $Sheet->setCellValueByColumnAndRow(1,$SheetRow,$arSection['NAME']);
                    $cellStyle = $Sheet->getStyleByColumnAndRow(1,$SheetRow);
                    $cellStyle->applyFromArray($arCellFormat_SectionHeader);
                    $SheetRow++;
                    
                    $Sheet->setCellValueByColumnAndRow(1,$SheetRow,'Наименование');
                    $cellStyle = $Sheet->getStyleByColumnAndRow(1,$SheetRow);
                    $cellStyle->applyFromArray($arCellFormat_SectionTableHeader);
/*
                    $Sheet->setCellValueByColumnAndRow(2,$SheetRow,'Артикул');
                    $cellStyle = $Sheet->getStyleByColumnAndRow(2,$SheetRow);
                    $cellStyle->applyFromArray($arCellFormat_SectionTableHeader);
*/                    
                    $SheetRowColumn=2;
                    foreach ($arParams['TABLE_COLUMNS'] as $code){
                        $Sheet->setCellValueByColumnAndRow($SheetRowColumn,$SheetRow,strip_tags($arProperties[$code]['NAME']),true);
                        
                        $cellStyle = $Sheet->getStyleByColumnAndRow($SheetRowColumn,$SheetRow);
                        $cellStyle->applyFromArray($arCellFormat_SectionTableHeader);

                        $SheetRowColumn++;
                    }
                    foreach ($arParams['PRICE_CODE'] as $code){
                        $Sheet->setCellValueByColumnAndRow($SheetRowColumn,$SheetRow,strip_tags($arResult['PRICES'][$code]['TITLE']));
                        $cellStyle = $Sheet->getStyleByColumnAndRow($SheetRowColumn,$SheetRow);
                        $cellStyle->applyFromArray($arCellFormat_SectionTableHeader);
                        $SheetRowColumn++;
                    }
                    $SheetRow++;
                    
                    //get elements
                    $arSort   = array(
                        'PROPERTY_INDEX_PRICE_'.strtoupper($arCity['CODE']) => 'ASC'
                        );
                    $arSelect = array('ID','IBLOCK_ID', 'NAME',"CATALOG_QUANTITY",'IBLOCK_SECTION_ID');
                    
                    foreach($arResult["PRICES"] as &$value)
                    {
			if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
				continue;
			$arSelect[] = $value["SELECT"];
			$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
                    }
                    
                    $arFilter = array(
                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                'ACTIVE' => 'Y',
                                'SECTION_ID' => $arSection['ID']
                            );
 
                    
                    $rs = CIBlockElement::GetList($arSort,$arFilter,false,false,$arSelect);
                    
                    while($obItem = $rs->GetNextElement()){
                        
                        $arItem = $obItem->GetFields();
                        $arItem['PROPERTIES'] = $obItem->GetProperties();
                        
               		$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams['PRICE_VAT_INCLUDE'], $arConvertParams);
                        //trace($arItem);die();
                        foreach($arParams['TABLE_COLUMNS'] as $code){
                            
                            $arItem['DISPLAY_PROPERTIES'][$code] = CIBlockFormatProperties::GetDisplayValue($arItem, $arItem["PROPERTIES"][$code]);
                            $arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'] = str_replace('&nbsp;',' ',strip_tags($arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE']));
                        }                        
                        
//draw item row to excel
        
                        $Sheet->setCellValueByColumnAndRow(1,$SheetRow,$arItem['NAME']);
                                $cellStyle = $Sheet->getStyleByColumnAndRow(1,$SheetRow);
                                $cellStyle->applyFromArray($arCellFormat_SectionTableCell);

                        //$Sheet->setCellValueByColumnAndRow(2,$SheetRow,"'".$arItem['PROPERTIES']['fld_artikul']['VALUE']);
                        /*        
                        $Sheet->getCellByColumnAndRow(2,$SheetRow)->setValueExplicit($arItem['PROPERTIES']['fld_artikul']['VALUE'], PHPExcel_Cell_DataType::TYPE_STRING);
                                $cellStyle = $Sheet->getStyleByColumnAndRow(2,$SheetRow);
                                $cellStyle->applyFromArray($arCellFormat_SectionTableCell);
                        */
                                
                        $SheetRowColumn = 2;
                        foreach($arParams['TABLE_COLUMNS'] as $code)
			{
                            if(!empty($arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE'])){ 
                                $Sheet->setCellValueByColumnAndRow(
                                        $SheetRowColumn,
                                        $SheetRow,
                                        strip_tags($arItem['DISPLAY_PROPERTIES'][$code]['DISPLAY_VALUE']));
                            }    
                                $cellStyle = $Sheet->getStyleByColumnAndRow($SheetRowColumn,$SheetRow);
                                $cellStyle->applyFromArray($arCellFormat_SectionTableCell);
                                $SheetRowColumn++;
			}
                        foreach($arResult["PRICES"] as $key => $priceColumn)
			{
                            if(!empty($arItem['PRICES'][$key])){            
                                $cell = $Sheet->setCellValueByColumnAndRow($SheetRowColumn,$SheetRow,  $arItem['PRICES'][$key]['VALUE'],true);
                            }    
                                
                                $cellStyle = $Sheet->getStyleByColumnAndRow($SheetRowColumn,$SheetRow);
                                $cellStyle->applyFromArray($arCellFormat_SectionTablePriceCell);
                            //spec
                            if($arItem['PROPERTIES']['SPEC']['VALUE']){
                                $cellStyle->getFont()->getColor()->applyFromArray(array('rgb' => 'DE5705'));
                            }
                            
                            $SheetRowColumn++;
			}
                        
                        
                        $SheetRow++;
                        
                    }

                }    

		$arResult["SECTIONS"][]=$arSection;
		//if(count($arResult["SECTIONS"])>=$arParams["SECTION_COUNT"]){
                //    echo 'breack';
		//	break;
                //}        
	}


        $objPHPExcel->setActiveSheetIndex(0);
        
        //trace($objPHPExcel);
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //trace($objWriter);
        $fileName = $_SERVER['DOCUMENT_ROOT'].$arParams['UPLOAD_DIR'].$arParams['UPLOAD_FILE_NAME'];
        //$fileName = $arParams['UPLOAD_DIR'].$arParams['UPLOAD_FILE_NAME'];
        //echo $fileName;
        
        $objWriter->save($fileName);
        
        //header('Content-Type: application/vnd.ms-excel');
        //header('Content-Disposition: attachment;filename="your_name.xls"');
        //header('Cache-Control: max-age=0');
        // ...
        //$objWriter->save('php://output');

	//$this->IncludeComponentTemplate();


