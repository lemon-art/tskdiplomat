<?
die();
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

    $arTParams = array(
        "replace_space"=>"-",
        "replace_other"=>"-",
        "max_len" => 50,
        "change_case" =>'L',
        
        );

$els = new CIBlockElement;

$filename = $_SERVER['DOCUMENT_ROOT']."/upload_old/upload/catalog_export/export_my.csv";

echo $filename;
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$ar = explode(PHP_EOL,$contents);
foreach ($ar as $key => $line){
    $ar[$key] = explode(';',$line);
}

foreach ($ar[0] as $k => $v){
    if(strlen($v) > 0){
        $arHeaders[str_replace('"','',$v)] = intval($k);
    }
}
unset($ar[0]);


trace($arHeaders);
//trace($ar);
//die();
//    trace($arHeaders['XML_ID']);

foreach ($ar as $k => $v){
//    trace($arHeaders['XML_ID']);
    trace($v);
//    trace($v[$arHeaders['XML_ID']]);
//    die();
    
    $arFilter = array('IBLOCK_ID' => 3);
    //if(strlen($v[3]) <= 0) continue;
    if(strlen($v[$arHeaders['XML_ID']]) > 0){
        $arFilter['XML_ID'] = str_replace('"','',$v[$arHeaders['XML_ID']]);
    }else{
        continue;
    }
    
    $rs = CIBlockElement::GetList(
                array('ID' => 'ASC'), 
                $arFilter,
                false,
                false,
                array('ID','IBLOCK_ID','XML_ID','CODE','NAME','IBLOCK_SECTION_ID','PREVIEW_PICTURE','DETAIL_PICTURE')
            );

    if($el = $rs->GetNext()) {
        
        trace($el);
        
        
        $arFields = array(
            'IBLOCK_SECTION_ID' => str_replace('"','',$v[$arHeaders['IBLOCK_SECTION_ID']])
        );
        $arFields['CODE'] = Cutil::translit(trim($el['NAME']),"ru",$arParams);
        
        if($v[$arHeaders['PREVIEW_PICTURE']] !== ''){
            $arFields['PREVIEW_PICTURE'] =  CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/upload_old/".str_replace('"','',$v[$arHeaders['PREVIEW_PICTURE']]));
        }
        if($v[$arHeaders['DETAIL_PICTURE']] !== ''){
            $arFields['DETAIL_PICTURE'] =  CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/upload_old/".str_replace('"','',$v[$arHeaders['DETAIL_PICTURE']]));
        }
        trace($arFields);
//        die();
        
        if($els->Update($el['ID'],$arFields)){
           
            echo 'ELEMENT'.$el['ID'].' updated<br/>';
            $upd++;
        }else{
            echo 'ELEMENT'.$el['ID'].' error '.$els->LAST_ERROR.'<br/>';
            $correct++;
        }
//        return;
    
//    $LAST_ID = $ar['ID'];
    }else{
        echo $v[0].' NOT_FOUND<br/>';
        $notfound++;
    }

    $total++;
}
echo 'total:'.$total.' updated:'.$upd.' correct:'.$correct.' notfound:'.$notfound;
//echo '<a href="?ID='.$LAST_ID.'">'.$LAST_ID.'</a>';
?>