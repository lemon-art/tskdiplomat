<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$obParser = new CTextParser;
$arResult["DESCRIPTION_SHORT"] = $obParser->html_cut($arResult["DESCRIPTION"], 300);

if(!$arResult["ITEMS"]){
    $arRes["SECTION_INFO"] = array();
    //$obSec = CIBlockSection::GetList(array(), array("ID" => $arResult['IBLOCK_SECTION_ID']));
    $nav = GetIBlockSectionPath($arResult['IBLOCK_ID'], $arResult['IBLOCK_SECTION_ID']);
    if(!$nav->arResult) $url = '/';
        else {    
            $url = '/catalog/';
            foreach($nav->arResult as $result){
                $url .= $result['CODE'].'/';
            }
        }
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$url."");
}

foreach ($arResult["ITEMS"] as $key => $arElement) 
{
        $arResult["ITEMS"][$key]["~PREVIEW_TEXT"] = $obParser->html_cut($arElement["PREVIEW_TEXT"], 300);
		
	if(is_array($arElement["DETAIL_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arElement["DETAIL_PICTURE"],
			array("width" =>180, "height" => 180),
			BX_RESIZE_IMAGE_EXACT,
			false
		);
		$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);

		$arResult["ITEMS"][$key]["PREVIEW_PICTURE"] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => IntVal($arSize[0]),
			"HEIGHT" => IntVal($arSize[1]),
		);
	}
}	
	
?>	