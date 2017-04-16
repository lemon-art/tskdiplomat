<?
//die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
global $DB;

$rs = CFile::GetList(array('ID'=>'ASC'),array('MODULE_ID'=>'iblock'));

while($ar = $rs->GetNext()){
    $arFile = CFile::GetFileArray($ar['ID']);
    
    if(!file_exists($_SERVER["DOCUMENT_ROOT"].$arFile['SRC'])){
        //trace($ar);
        //trace($arFile);
        
        echo 'File: '.$ar['ID'];
        
        $strSql = 'SELECT * FROM b_iblock_element WHERE'
                . ' PREVIEW_PICTURE = '.$ar['ID'].' OR DETAIL_PICTURE = '.$ar['ID'];
        //echo $strSql;
        if(!$r = $DB->Query($strSql)){
            echo $DB->LAST_ERROR;
        }else{
            $a = $r->GetNext();
            echo ' ibelement: '.$a['ID'];
        }
        echo '<br/>';
        //break;
    }
}

/*    
$rs = CIBlockElement::GetList(
        array('ID' => 'ASC'), 
        array(
            'IBLOCK_ID' => 3,
            'IBLOCK_LID' => 's1',
            'IBLOCK_ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y',
            'MIN_PERMISSION' => 'R',
            'INCLUDE_SUBSECTIONS' => 'Y',
            'SECTION_ID' => 834,
            'CATALOG_SHOP_QUANTITY_1' => 1
            )
        );
while ($ar = $rs->GetNext()) {
    
    echo "[{$ar['ID']}] {$ar['NAME']}<br/>";
    //break;
}
*/
echo 'finish'; 