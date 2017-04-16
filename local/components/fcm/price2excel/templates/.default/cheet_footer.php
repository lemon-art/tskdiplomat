<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$objPHPExcel->setActiveSheetIndex($SheetIndex);

$Sheet = $objPHPExcel->getActiveSheet();

$Sheet->setCellValueByColumnAndRow(2,$SheetRow+2,$arCity['PROPERTY_DELIVERY_PRICE_REMARK_VALUE']);
 
?>