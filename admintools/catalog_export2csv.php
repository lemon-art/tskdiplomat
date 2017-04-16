<?
die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;

$filePath = '/upload/catalog_export/';

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

    $arExportFields = array(
        "ID" => "ID",
        "XML_ID" => "XML_ID",
        "NAME" => "NAME",
        "DETAIL_PICTURE" => "DETAIL_PICTURE",
        "PREVIEW_PICTURE" => "PREVIEW_PICTURE",
        "PRICE" => "CATALOG_PRICE_1",
        "CURRENCY" => "CATALOG_CURRENCY_1",
        "MEASURE" => "CATALOG_MEASURE",
    );

$arFilter = array(
                'IBLOCK_ID' => 3, 
                '>ID' => 4577,
            ); 


    $fileName = 'export_my.csv';

if(!empty($_REQUEST['STEP'])){
    
    if($_REQUEST['STEP'] == "1"){
        
        $fp = fopen($_SERVER['DOCUMENT_ROOT'].$filePath.$fileName, 'w');

        if(!is_resource($fp))
            {
                echo $_SERVER['DOCUMENT_ROOT'].$filePath.$fileName."<br/>";
                echo "IBLOCK_ADM_EXP_CANNOT_CREATE_FILE<br>";
                die();
        }
        
        $strHeaders = "";
        $strHeaders = chr(239) . chr(187) . chr(191); //insert BOM
        foreach ($arExportFields as $k => $v){
            $strHeaders .= '"'.$k.'";';
        }
        $strHeaders .= PHP_EOL;

        fwrite($fp, $strHeaders);
        
        echo '<a href="?STEP=2 ">STEP 1 Completed. File headers sent. Continue</a>';
        
    }elseif($_REQUEST['STEP'] == "2"){
        
        $fp = fopen($_SERVER['DOCUMENT_ROOT'].$filePath.$fileName, 'a');
        
        if(!is_resource($fp))
        {
                echo "IBLOCK_ADM_EXP_CANNOT_CREATE_FILE<br>";
                die();
        }

        $rs = CIBlockElement::GetList(
                    array('ID' => 'ASC'), 
                    $arFilter, 
                    false, 
                    array('nTopCount' => 500), 
                    array(
                        'ID', 'IBLOCK_ID','NAME','ACTIVE',
                        'IBLOCK_SECTION_ID',
                        'XML_ID',
                        'PREVIEW_PICTURE',
                        'DETAIL_PICTURE',
                        'CATALOG_GROUP_1',
                        "PROPERTY_fld_artikul",
                        "PROPERTY_SUPPLIER",
                        "PROPERTY_SUPPLIER.NAME",
                        "PROPERTY_FLD_COLOR"
                        )
        );

        $LAST_ID = false;
        
        if(empty($arCatalogPrices)){
                
            $arCatalogPrices = CIBlockPriceTools::GetCatalogPrices($ar['IBLOCK_ID'],array("BASE_PRICE"));
                
        }

        
        while ($ar = $rs->GetNext()) {
            
            if(empty($arPath[$ar["IBLOCK_SECTION_ID"]])){
                    
                $rsPath = CIBlockSection::GetNavChain($ar['IBLOCK_ID'], $ar["IBLOCK_SECTION_ID"], array("NAME"));
            
                while($arPathSection = $rsPath->Fetch())
                    {
                        $arPath[$ar["IBLOCK_SECTION_ID"]][] = $arPathSection["NAME"];
                    }
            }
            
            $ar['SECTION_PATH'] = implode(" / ", $arPath[$ar["IBLOCK_SECTION_ID"]]);
            
            if($ar['DETAIL_PICTURE'] > 0){
                $arImg = CFile::GetFileArray($ar['DETAIL_PICTURE']);
                trace($arImg);
            }
            
            
            trace($ar); die();
            
            if($_REQUEST['EXPORT_PRODUCTS'] == 'Y'){

                $strRow = "";

                foreach ($arExportFields as $k => $v){
                    if(!empty($ar[$v])){
                        $strRow .= '"'.$ar[$v].'";';
                    }else{
                        $strRow .='"";';
                    }
                }

                $strRow .= PHP_EOL;

                fwrite($fp, $strRow);

                $strRow = "";
            }
            
            $LAST_ID = $ar['ID'];
        }
        
        if($LAST_ID){
            echo '<a href="?STEP=2&ID='.$LAST_ID.'&SUPPLIER='.intval($_REQUEST['SUPPLIER']).'"> STEP 2 '.$LAST_ID.' Continue</a>';
        }
        else{
            echo  'STEP 2 FINISHED <a href="'.$filePath.$fileName.'">'.$filePath.$fileName.'</a> download';
            
        }
    }

    fclose($fp);
}
if(empty($_REQUEST['STEP'])){
    echo '<a href="?STEP=1&SUPPLIER='.intval($_REQUEST['SUPPLIER']).' ">Papista Fale.....</a>';
    //return;
}
