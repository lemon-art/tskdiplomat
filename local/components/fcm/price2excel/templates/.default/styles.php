<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arCellFormat_SectionHeader = array(
            'font' => array(
                'size' => 14,
                'bold' => true
            ),
    /*
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                                'rgb' => '000000'
                            )
                        )
                ),
     * 
     */
            'alignment' => array(
                'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       	=> false,
            )
    );

$arCellFormat_SectionTableHeader = array(
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
            ),
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                                'rgb' => '000000'
                            )
                        )
                ),
            'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color'     => array(
                        'rgb' => '8D1D08'
                    )
                ),
            'alignment' => array(
                'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       	=> true,
            )
    );

$arCellFormat_SectionTableCell = array(
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                                'rgb' => '000000'
                            )
                        )
                ),
            'alignment' => array(
                'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_TOP,
                'wrap'       	=> true,
            )
    );

$arCellFormat_SectionTablePriceCell = array(
            'numberformat'=> array('code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1. "[\$ ₽-419]"),
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                                'rgb' => '000000'
                            )
                        )
                ),
            'alignment' => array(
                'horizontal' 	=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   	=> PHPExcel_Style_Alignment::VERTICAL_TOP,
                'wrap'       	=> true,
            ),
            'font' => array(
                'bold' => true
            ),
    );
?>