<?
die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

$CODE = strtoupper($_REQUEST['CODE']);
if(strlen($CODE) <= 0){die('CODE NOT SET');}

$STEP = intval($_REQUEST['STEP']);
if($STEP <= 0){die('STEP NOT SET');}

$el = new CIBlockElement;
if($STEP == 4){
    
$rs = CIBlockElement::GetList(
        array('NAME' => 'ASC'), 
        array('IBLOCK_ID' => 3,'!PROPERTY_TMP' => false ),
        false,
        false,
        array('ID','IBLOCK_ID', 'NAME','PROPERTY_'.$CODE,'PROPERTY_TMP')
        );
while ($ar = $rs->GetNext()) {
    
    //trace($ar);
    if(strlen($ar['PROPERTY_TMP_VALUE']) > 0){
        
        if(!CIBlockElement::SetPropertyValueCode($ar['ID'],'TMP', '')){
            echo 'ERROR SET PROPERTY <br/>';
            die();
        }else{
            echo $ar['ID'].' clear<br/>';
        }
    }
    
}

echo 'TMP - clear';
}

//STEP 1
if($STEP == 1){
    
$rs = CIBlockElement::GetList(
        array('NAME' => 'ASC'), 
        array('IBLOCK_ID' => 3,'!PROPERTY_'.$CODE => false ),
        false,
        false,
        array('ID','IBLOCK_ID', 'NAME','PROPERTY_'.$CODE,'PROPERTY_TMP')
        );
while ($ar = $rs->GetNext()) {
    
    //trace($ar);
    if($ar['PROPERTY_'.$CODE.'_VALUE'] !== $ar['PROPERTY_TMP_VALUE']){
        
        if(!CIBlockElement::SetPropertyValueCode($ar['ID'],'TMP', trim($ar['PROPERTY_'.$CODE.'_VALUE']))){
            echo 'ERROR SET PROPERTY <br/>';
            die();
        }else{
            echo 'OK<br/>';
        }
    }
    
}

echo 'TMP - set';
}
//STEP 2
if($STEP == 2){
$rs = CIBlockElement::GetList(
        array('NAME' => 'ASC'), 
        array('IBLOCK_ID' => 3,'!PROPERTY_TMP' => false ),
        false,
        false,
        array('ID','IBLOCK_ID', 'NAME','PROPERTY_'.$CODE,'PROPERTY_TMP')
        );
while ($ar = $rs->GetNext()) {
    
    //trace($ar);
    if(strlen($ar['PROPERTY_TMP_VALUE']) > 0){
        $arValues[strtoupper($ar['PROPERTY_TMP_VALUE'])] = $ar['PROPERTY_TMP_VALUE'];
    }
    
}

trace($arValues);
$rs = CIBlockProperty::GetList(array('ID'=>'ASC'),array('IBLOCK_ID' => 3,'CODE'=>$CODE));
if($ar = $rs->Fetch()){
    trace($ar);
    if($ar['PROPERTY_TYPE'] !== 'L'){
        
        $arFields = array('PROPERTY_TYPE' => 'L');
        
        $ibp = new CIBlockProperty;
        if($ibp->Update($ar['ID'],$arFields)){
            echo 'PROPERTY TYPE SET OK!<br/>';
        }else{
            echo 'PROPERTY TYPE SET ERROR!<br/>';
            echo $ibp->LAST_ERROR;
            die();
        }
    }else{
            echo 'PROPERTY TYPE ALREADY SETTED<br/>';
    }
    
    $ipValues = array();
    
    $rs = CIBlockProperty::GetPropertyEnum($ar['ID'],array('SORT'=>'ASC'),array('IBLOCK_ID' => 3));
    while($pv = $rs->Fetch()){
        $ipValues[$pv['ID']] = $pv['VALUE'];
    }
    
    
    trace($ipValues);
    $ibpenum = new CIBlockPropertyEnum;
    foreach ($arValues as $k => $v){
        if(!in_array($v, $ipValues)){
            echo 'NO VALUE ->';
            if($PropID = $ibpenum->Add(Array('PROPERTY_ID'=>$ar['ID'], 'VALUE'=>$v))){
                echo 'New ID:'.$PropID .'<br/>';
            }else{
                echo $ibpenum->LAST_ERROR;
                die();
            }
        }
    }
}    
    echo 'STEP2 - set';

}

//STEP 3
if($STEP == 3){
    
    $rs = CIBlockProperty::GetPropertyEnum($CODE,array('SORT'=>'ASC'),array('IBLOCK_ID' => 3));
    while($pv = $rs->Fetch()){
        $ipValues[strtoupper($pv['VALUE'])] = $pv['ID'];
    }

    trace($ipValues);
    
    
$rs = CIBlockElement::GetList(
        array('NAME' => 'ASC'), 
        array('IBLOCK_ID' => 3,'!PROPERTY_TMP' => false ),
        false,
        false,
        array('ID','IBLOCK_ID', 'NAME','PROPERTY_'.$CODE,'PROPERTY_TMP')
        );
while ($ar = $rs->GetNext()) {
    $key = trim(strtoupper($ar['PROPERTY_TMP_VALUE']));
    //trace($ar);
    if(strlen($key) > 0){
        
        if($ipValues[$key] > 0){
            if($ipValues[$key] !== $ar['PROPERTY_'.$CODE.'_VALUE']){
                if(!CIBlockElement::SetPropertyValueCode($ar['ID'],$CODE, $ipValues[$key])){
                    echo 'ERROR SET PROPERTY <br/>';
                    die();
                }else{
                    echo $ar['ID'].' set value OK<br/>';
                }                
            }else{
                echo $ar['ID'].' value correct<br/>';
            }
        }else{
            echo $ar['ID'].'-> ['.$ar['PROPERTY_TMP_VALUE'].'] value NOT IN LIST!<br/>';
        }
    }
    
}

    echo 'STEP3 - set';

}



      