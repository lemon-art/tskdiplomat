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

$filename = $_SERVER['DOCUMENT_ROOT']."/upload_old/upload/catalog_export/export_all.csv";

echo $filename;
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$ar = explode(PHP_EOL,$contents);
foreach ($ar as $key => $line){
    $ar[$key] = explode('";"',$line);
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
        
        CCatalogProduct::Add(array(
            'ID' => $el['ID'],
            'MEASURE' => str_replace('"','',$v[$arHeaders['MEASURE']]),
            'QUANTITY' => 1
            
        ));
        
                    $arPriceFields = array(
                        'CATALOG_GROUP_ID' => 1,
                        'PRODUCT_ID' => $el['ID'],
                        'PRICE' => str_replace('"','',$v[$arHeaders['PRICE']]),
                        'CURRENCY' =>str_replace('"','',$v[$arHeaders['CURRENCY']])
                    );
                    
                    trace($arPriceFields);
                    
                    $ps = CPrice::GetList(
                            array(),
                            array(
                                'PRODUCT_ID' => $el['ID'],
                                'CATALOG_GROUP_ID' => 1)
                            );
                    
                    if($arP = $ps->GetNext()){
                         if(!CPrice::Update($el["ID"], $arPriceFields)){
                             echo 'ERROR UPDATE '.$APPLICATION->GetException()->GetString();
                             return;
                         }
                         echo 'PRICE UPDATED';
                    }else{
                         if(!CPrice::Add($arPriceFields)){
                             echo 'ERROR_ADD '.$APPLICATION->GetException()->GetString();
                             return;
                         }
                         echo 'PRICE ADD';
                    }
        //die();
        
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