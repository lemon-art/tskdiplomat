<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$objPHPExcel->setActiveSheetIndex($SheetIndex);

$Sheet = $objPHPExcel->getActiveSheet();

$Sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$Sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$Sheet->getPageSetup()->setFitToPage(true);
$Sheet->getPageSetup()->setFitToWidth(1);
$Sheet->getPageSetup()->setFitToHeight(0); 

//column widths
$Sheet->getColumnDimensionByColumn(0)->setAutoSize(false);
$Sheet->getColumnDimensionByColumn(0)->setWidth('2.91');
$Sheet->getColumnDimensionByColumn(1)->setAutoSize(false);
$Sheet->getColumnDimensionByColumn(1)->setWidth('52');
for ( $i = 2; $i <= 8 ; $i++){
    $Sheet->getColumnDimensionByColumn($i)->setAutoSize(false);
    $Sheet->getColumnDimensionByColumn($i)->setWidth('16');
}                        
/*
// Add a drawing to the worksheet
$Sheet->mergeCells('B1:G1');
$Sheet->getStyle('B1')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color'=> array('rgb' => 'F06918'))));
$Sheet->getRowDimension('1')->setRowHeight(24);
$Sheet->getRowDimension('2')->setRowHeight(5);

        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('line');
        $objDrawing->setDescription('line');
        $objDrawing->setPath($_SERVER["DOCUMENT_ROOT"].'/upload/excel/excel_top_line.jpg');

        $objDrawing->setCoordinates('B1');
        $objDrawing->setHeight(32);
        $objDrawing->setWidth(641);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
*/
        
// Add a drawing to the worksheet
$Sheet->mergeCells('B3:B5');        
$Sheet->getRowDimension('3')->setRowHeight(30);
$Sheet->getRowDimension('4')->setRowHeight(5);
$Sheet->getRowDimension('5')->setRowHeight(30);
        
        $objDrawing2 = new PHPExcel_Worksheet_Drawing();
        $objDrawing2->setName('Logo');
        $objDrawing2->setDescription('Logo');
        $objDrawing2->setPath($_SERVER["DOCUMENT_ROOT"].'/upload/excel/logo.jpg');

        $objDrawing2->setCoordinates('B3');
        $objDrawing2->setHeight('80');
        $objDrawing2->setWidth('218');
        $objDrawing2->setWorksheet($objPHPExcel->getActiveSheet());
        
        
        $objDrawing3 = new PHPExcel_Worksheet_Drawing();
        $objDrawing3->setName('phone');
        $objDrawing3->setDescription('phone');
        $objDrawing3->setPath($_SERVER["DOCUMENT_ROOT"].'/upload/excel/excel_phone_icon.jpg');

        $objDrawing3->setCoordinates('C3')->setOffsetX('-13')->setOffsetY('5');
        $objDrawing3->setHeight('26');
        $objDrawing3->setWidth('26');
        $objDrawing3->setWorksheet($objPHPExcel->getActiveSheet());        

$Sheet->mergeCells('C3:D3');        
$Sheet->setCellValue('C3', '+7 495 663 71 82');
$Sheet->getStyle('C3')->getFont()->setSize(22)->setBold(true);
$Sheet->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


$Sheet->mergeCells('E3:G3');        
$Sheet->setCellValue('E3', 'Вам ответят c 8:00 до 22:00, без выходных.');
$Sheet->getStyle('E3')->getFont()->getColor()->applyFromArray(array('rgb' => '999999'));
$border_style= array('borders' => array('left' => array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '999999'),)));
$Sheet->getStyle('E3')->applyFromArray($border_style);
$Sheet->getStyle('E3')->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objDrawing4 = new PHPExcel_Worksheet_Drawing();
        $objDrawing4->setName('phone');
        $objDrawing4->setDescription('phone');
        $objDrawing4->setPath($_SERVER["DOCUMENT_ROOT"].'/upload/excel/excel_phone_icon.jpg');

        $objDrawing4->setCoordinates('C5')->setOffsetX('-13')->setOffsetY('5');
        $objDrawing4->setHeight('26');
        $objDrawing4->setWidth('26');
        $objDrawing4->setWorksheet($objPHPExcel->getActiveSheet());        

$Sheet->mergeCells('C5:D5');        
$Sheet->setCellValue('C5', '+7 495 956 71 20');
$Sheet->getStyle('C5')->getFont()->setSize(22)->setBold(true);
$Sheet->getStyle('C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
$Sheet->mergeCells('E5:G5');
$Sheet->setCellValue('E5', strip_tags($arCity['~PROPERTY_PHONE_WORKTIME_VALUE']));
$Sheet->getStyle('E5')->getFont()->getColor()->applyFromArray(array('rgb' => '999999'));
$border_style= array('borders' => array('left' => array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '999999'),)));
$Sheet->getStyle('E5')->applyFromArray($border_style);
$Sheet->getStyle('E5')->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        

//$Sheet->mergeCells('C4:E4');        
//$Sheet->mergeCells('C5:E5');        
        //$objDrawing5 = new PHPExcel_Worksheet_Drawing();
        //$objDrawing5->setName('ballon');
        //$objDrawing5->setDescription('ballon');
        //$objDrawing5->setPath($_SERVER["DOCUMENT_ROOT"].'/upload/excel/excel_map_ballon.jpg');

        //$objDrawing5->setCoordinates('B6');
        //$objDrawing5->setHeight('21');
        //$objDrawing5->setWidth('24');
        //$objDrawing5->setWorksheet($objPHPExcel->getActiveSheet());
        
        
$objRichText = new PHPExcel_RichText();
//$run1 = $objRichText->createTextRun($arCity['NAME']);
//$run1->getFont()->setSize(18)->setBold(true);

//$run12 = $objRichText->createTextRun(' | ');
//$run12->getFont()->setSize(18)->setBold(true)->getColor()->setRGB('999999');

$run2 = $objRichText->createTextRun('Цены');
$run2->getFont()->setSize(18)->setBold(true);

$run21 = $objRichText->createTextRun(' на: ');
$run21->getFont()->setSize(14);

$run3 = $objRichText->createTextRun(date('d.m.Y'));
$run3->getFont()->setSize(14)->getColor()->setRGB('DE5705');

$Sheet->setCellValue("B6", $objRichText);   
$Sheet->getStyle('B6')->getAlignment()->setIndent(3);


$Sheet->getStyle('F6')->getFont()->getColor()->applyFromArray(array('rgb' => 'DE5705'));

$Sheet->setCellValue('F6','tskdiplomat.ru',true)->getHyperlink()->setUrl('http://tskdiplomat.ru');;
$Sheet->getStyle('F6')->getFont()->setSize(14)->setBold(false);
$Sheet->getStyle('F6')->getFont()->getColor()->applyFromArray(array('rgb' => 'DE5705'));

$Sheet->mergeCells('B7:F7');
$Sheet->setCellValue('B7', $arSection['NAME']);
$Sheet->getStyle('B7')->getFont()->setSize(18)->setBold(true);
$Sheet->getStyle('B7')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color'=> array('rgb' => 'fdbd03'))));
$Sheet->getStyle('B7')->getFont()->getColor()->applyFromArray(array('rgb' => 'FFFFFF'));


//$Sheet->mergeCells('B6:G6');
//$Sheet->getStyle('B6:G6')->getBorders()->getBottom()->applyFromArray(array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '999999')));

if(strlen($arSection['NAME']) > 31){
    $sheetName = substr($arSection['NAME'], 0,27)."...";
}else{
    $sheetName = $arSection['NAME'];
}
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle($sheetName);
$objPHPExcel->getActiveSheet()->setShowGridlines(false);
?>