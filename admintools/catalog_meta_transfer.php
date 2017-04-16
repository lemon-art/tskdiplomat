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
        array('IBLOCK_ID' => 12,'ACTIVE'=>'Y'),
        false,
        array('nTopCount' => 500),
        array('ID','IBLOCK_ID','NAME','PROPERTY_TITLE','PROPERTY_DESCRIPTION','PROPERTY_KEYWORDS','PROPERTY_H1')
        );

while($ar = $rs->GetNext()){
    
    $arFields = array();
    
    trace($ar);
    $ar['NAME'] = str_replace('http://tskdiplomat.ru', '', $ar['NAME']);
    $path = explode('/',$ar['NAME']);
    
    if($path[1] == 'catalog'){
        
        if(count($path) == 4){
            
            $rss = CIBlockSection::GetList(array('ID' => 'ASC'),array('IBLOCK_ID' => 3,'CODE' => $path[2]),false,array('ID','IBLOCK_ID','NAME','UF_*'));
            
            if($ars = $rss->GetNext()){
                trace($ars);
                
                if(strlen($ar['PROPERTY_TITLE_VALUE']) > 0 && $ar['PROPERTY_TITLE_VALUE'] !== $ars['UF_BROWSER_TITLE']){
                    $arFields['UF_BROWSER_TITLE'] = $ar['PROPERTY_TITLE_VALUE'];
                }
                if(strlen($ar['PROPERTY_DESCRIPTION_VALUE']) > 0 && $ar['PROPERTY_DESCRIPTION_VALUE'] !== $ars['UF_META_DESCRIPTION']){
                    $arFields['UF_META_DESCRIPTION'] = $ar['PROPERTY_DESCRIPTION_VALUE'];
                }
                if(strlen($ar['PROPERTY_KEYWORDS_VALUE']) > 0 && $ar['PROPERTY_KEYWORDS_VALUE'] !== $ars['UF_KEYWORDS']){
                    $arFields['UF_KEYWORDS'] = $ar['PROPERTY_KEYWORDS_VALUE'];
                }
                if(strlen($ar['PROPERTY_H1_VALUE']) > 0 && $ar['PROPERTY_H1_VALUE'] !== $ars['UF_ZH1']){
                    $arFields['UF_ZH1'] = $ar['PROPERTY_H1_VALUE'];
                }
                
                trace($arFields);
                
                if(count($arFields) > 0){
                    
                    if($bs->Update($ars['ID'],$arFields)){
                        
                        $el->Update($ar['ID'],array('ACTIVE' => 'N'));
                        
                        echo $ars['ID']. ' Updated.<br/>';
                    }else{
                        ShowError($bs->LAST_ERROR);
                    }
                    
                }
            }
            
            
        }
    }
    trace($path);
}
?>