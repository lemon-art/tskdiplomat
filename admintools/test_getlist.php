<?
//die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

    
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

echo 'finish'; 