<?
echo 'transfer';
//die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

use Bitrix\Iblock\InheritedProperty;

$bs = new CIBlockSection;
$el = new CIBlockElement;


$rs = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => 32,
            //'ACTIVE'=>'Y',
            'PROPERTY_KEYWORDS' => false,
            ),
        false,
        array('nTopCount' => 50),
        array('ID','IBLOCK_ID','NAME','CODE')
        );

while($ar = $rs->GetNext()){
    
    echo '#'.$ar['ID'].'- ['.$ar['CODE'].'] '.$ar['NAME'].' ---> ';
    if(strlen($ar['CODE']) > 0){
        
        $rss = CIBlockSection::GetList(
                array('CODE' => 'ASC'),
                array(
                    'IBLOCK_ID' => 3,
                    'CODE' => $ar['CODE'],
                    ),
                false,
                array('ID','NAME','IBLOCK_ID','UF_*') 
                );
        
        if($as = $rss->GetNext()){
            
            //trace($as);
            
            foreach ($as as $key => $value){
                
                if(strpos($key,'UF_') === 0){
                    
                    if(strlen($value) > 0){
                        
                       if(CIBlockElement::SetPropertyValueCode($ar['ID'],str_replace('UF_', '', $key),$value)){
                           echo 'set value '.$key.'/ ';
                       }else{
                           echo 'ERROR '.$key. '-'.$value.'/';
                       }
                    }
                    
                    //echo str_replace('UF_', '', $key).'<br/>';
                }
            }
            
            //$ipropValues = new InheritedProperty\SectionValues(3, $as['ID']);
            //$values = $ipropValues->getValues();
            
            //trace($values); 
            
        }else{
            echo 'SECTION NOT FOUND';
        }
        
    }else{
        
        echo 'EMPTY CODE!';
    }
    
    echo '<br/>';
}
?>