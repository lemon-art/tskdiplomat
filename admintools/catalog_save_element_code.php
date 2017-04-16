<?
die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$bs = new CIBlockSection;
$el = new CIBlockElement;


$rs = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array('IBLOCK_ID' => 3),
        false,
        false, //array('nTopCount' => 500),
        array('ID','IBLOCK_ID','NAME','DETAIL_PAGE_URL')
        );

while($el = $rs->GetNextElement()){
    
    $ar = $el->GetFields();
    $ar['PROPERTIES'] = $el->GetProperties();
    
    echo "[{$ar['ID']}] {$ar['NAME']} --> ";
    if(strlen($ar['CODE']) > 0 && !in_array($ar['CODE'], $ar['PROPERTIES']['OLD_CODE']['VALUE'])){
        
         $ar['PROPERTIES']['OLD_CODE']['VALUE'][] = $ar['CODE'];
         
         CIBlockElement::SetPropertyValueCode($ar['ID'],'OLD_CODE',$ar['PROPERTIES']['OLD_CODE']['VALUE']);
         
         echo ' CODE saved ';
    }
    if(strlen($ar['DETAIL_PAGE_URL']) > 0 && !in_array($ar['DETAIL_PAGE_URL'], $ar['PROPERTIES']['OLD_URL']['VALUE'])){
        
         $ar['PROPERTIES']['OLD_URL']['VALUE'][] = $ar['DETAIL_PAGE_URL'];
         
         CIBlockElement::SetPropertyValueCode($ar['ID'],'OLD_URL',$ar['PROPERTIES']['OLD_URL']['VALUE']);
         
         echo ' URL saved ';
    }
    //trace($ar);
    echo '<br/>';
}
?>