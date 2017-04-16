<?
//die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$bs = new CIBlockSection;
$el = new CIBlockElement;


$rs = CIBlockSection::GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_ID' => 3),
        false,
        array('ID','IBLOCK_ID','NAME','UF_*','SECTION_PAGE_URL'),
        false //array('nTopCount' => 1)
        );

while($ar = $rs->GetNext()){
    
    //trace($ar);
    
    //break;
    
    echo "[{$ar['ID']}] {$ar['NAME']} --> ";
    
    $arFields = array();
    
    if(strlen($ar['CODE']) > 0 && !in_array($ar['CODE'], $ar['UF_OLD_CODE'])){
        
         $arFields['UF_OLD_CODE'][] = $ar['CODE'];
         
         echo ' CODE saved ';
    }
    if(strlen($ar['SECTION_PAGE_URL']) > 0 && !in_array($ar['SECTION_PAGE_URL'], $ar['UF_OLD_URL'])){
        
         $arFields['UF_OLD_URL'][] = $ar['SECTION_PAGE_URL'];
         
         echo ' URL saved ';
    }
    
    if(count($arFields) > 0){
        if(!$bs->Update($ar['ID'],$arFields)){
            echo 'ERROR '.$bs->LAST_ERROR;
        }else{
            echo 'SAVED';
        }
    }
    echo '<br/>';
}
?>