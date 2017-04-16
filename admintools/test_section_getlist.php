<?
die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

    
$rs = CIBlockSection::GetList(
        array('ID' => 'ASC'), 
        array(
            'IBLOCK_ID' => 3,
            'HAS_ELEMENT' => 880,
            //'=CODE' => 'mineralnaya_vata'
            )
        );
while ($ar = $rs->GetNext()) {
    
    trace($ar);
    
}

echo 'finish'; 