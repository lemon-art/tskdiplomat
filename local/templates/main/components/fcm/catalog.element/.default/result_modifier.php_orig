<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

		$arFilter = '';
		if($arParams["SHARPEN"] != 0)
		{
			$arFilter = array("name" => "sharpen", "precision" => $arParams["SHARPEN"]);
		}

if(intval($arParams["DISPLAY_THIMG_WIDTH"]) <=0 ) 
	$arParams["DISPLAY_THIMG_WIDTH"] = 80;
	
if(intval($arParams["DISPLAY_THIMG_HEIGHT"]) <=0 ) 
	$arParams["DISPLAY_THIMG_HEIGHT"] = 80;
	
if(intval($arParams["DISPLAY_BIMG_WIDTH"]) <=0 ) 
	$arParams["DISPLAY_BIMG_WIDTH"] = 304;
	
if(intval($arParams["DISPLAY_BIMG_HEIGHT"]) <=0 ) 
	$arParams["DISPLAY_BIMG_HEIGHT"] = 304;	
	
if(intval($arParams["DISPLAY_MIMG_WIDTH"]) <=0 ) 
	$arParams["DISPLAY_MIMG_WIDTH"] = 182;
	
if(intval($arParams["DISPLAY_MIMG_HEIGHT"]) <=0 ) 
	$arParams["DISPLAY_MIMG_HEIGHT"] = 182;	
	
if(intval($arParams["DISPLAY_LINKEDIMG_WIDTH"]) <=0 ) 
	$arParams["DISPLAY_LINKEDIMG_WIDTH"] = 80;
	
if(intval($arParams["DISPLAY_LINKEDIMG_HEIGHT"]) <=0 ) 
	$arParams["DISPLAY_LINKEDIMG_HEIGHT"] = 80;	
	
if($arResult["DETAIL_PICTURE"]["ID"] > 0 ){
	$arFileTmp = CFile::ResizeImageGet(

			$arResult["DETAIL_PICTURE"]["ID"],
			array("width" => $arParams["DISPLAY_MIMG_WIDTH"], "height" => $arParams["DISPLAY_MIMG_HEIGHT"]),
			BX_RESIZE_IMAGE_EXACT,
			true, $arFilter
		);
		
		$arResult["MIDDLE_PICTURE"] = array(
			"SRC" => $arFileTmp["src"],
			'WIDTH' => $arFileTmp["width"],
			'HEIGHT' => $arFileTmp["height"],
		);
}
	
if(count($arResult["MORE_PHOTO"]) > 0):
//подмешиваем детальную картинку
//$arResult["MORE_PHOTO"] = array_merge(array(0 => $arResult["DETAIL_PICTURE"]),$arResult["MORE_PHOTO"]);

foreach ($arResult["MORE_PHOTO"] as $key => $arPic)
{
	if($arPic["ID"] > 0)
	{
		
		$arFileTmp = CFile::ResizeImageGet(
			$arPic["ID"],
			array("width" => $arParams["DISPLAY_BIMG_WIDTH"], "height" => $arParams["DISPLAY_BIMG_HEIGHT"]),
			BX_RESIZE_IMAGE_EXACT,
			true, $arFilter
		);
		
		$arResult["MORE_PHOTO"][$key]["BIGIMAGE"] = array(
			"SRC" => $arFileTmp["src"],
			'WIDTH' => $arFileTmp["width"],
			'HEIGHT' => $arFileTmp["height"],
		);
		
		$arFileTmp = CFile::ResizeImageGet(
			$arPic["ID"],
			array("width" => $arParams["DISPLAY_THIMG_WIDTH"], "height" => $arParams["DISPLAY_THIMG_HEIGHT"]),
			BX_RESIZE_IMAGE_EXACT,
			true, $arFilter
		);
		
		$arResult["MORE_PHOTO"][$key]["THUMBNAIL"] = array(
			"SRC" => $arFileTmp["src"],
			'WIDTH' => $arFileTmp["width"],
			'HEIGHT' => $arFileTmp["height"],
		);
	}
	
}
endif;

if(count($arResult["PROPERTIES"]["FILES"]["VALUE"]) > 0):
	foreach ($arResult["PROPERTIES"]["FILES"]["VALUE"] as $key => $FileID):
	
		$arFile = CFile::GetFileArray($FileID);
		
		$arFile["DESCRIPTION"] = $arResult["PROPERTIES"]["FILES"]["DESCRIPTION"][$key];
		$arFile["~FILE_SIZE"] = CFile::FormatSize($arFile["FILE_SIZE"]);
		$arFile["SRC"] = CFile::GetPath($FileID);
		
		$mime = split("/",$arFile["CONTENT_TYPE"]);
		
		$arFile["MIME"] = $mime;
		
		if(CFile::IsImage($arFile["FILE_NAME"])){
			//если изображение
			
			$arFileTmp = CFile::ResizeImageGet(
				$FileID,
				array("width" => $arParams["DISPLAY_THIMG_WIDTH"], "height" => $arParams["DISPLAY_THIMG_HEIGHT"]),
				BX_RESIZE_IMAGE_EXACT,
				true, $arFilter
			);
		
			$arFile["THUMBNAIL"] = array(
				"SRC" => $arFileTmp["src"],
				'WIDTH' => $arFileTmp["width"],
				'HEIGHT' => $arFileTmp["height"],
			);			
			$arFile["HTML"] = CFile::Show2Images(
				$arFile["THUMBNAIL"]["SRC"],
				$arFile["SRC"],
				$arParams["DISPLAY_THIMG_WIDTH"],
				$arParams["DISPLAY_THIMG_HEIGHT"]
			);
		}elseif($mime[0] == "application" && in_array($mime[1],array("pdf","txt","xml","zip","msword","rtf","vnd.ms-excel","vnd.ms-powerpoint"))){
			$arFile["THUMBNAIL"]["SRC"]=SITE_TEMPLATE_PATH."/images/icon/application/".$mime[1].".png";
		}else{
			$arFile["THUMBNAIL"]["SRC"]=SITE_TEMPLATE_PATH."/images/icon/application/file.png";
		}
		
		$arResult["FILES"][] = $arFile;
	endforeach;
endif;

if(count($arResult["PROPERTIES"]["LINKED_ELEMENTS"]["VALUE"]) > 0){
	$arResult["LINKED_ELEMENTS"] = array();
	$prop = $arResult["PROPERTIES"]["LINKED_ELEMENTS"];
	$arSelect = array("ID","IBLOCK_ID","NAME","PREVIEW_TEXT","DETAIL_PICTURE","DETAIL_PAGE_URL");
	
	$arFilter = array(
		"IBLOCK_ID" => $prop["LINK_IBLOCK_ID"],
		"ID" => $prop["VALUE"],
		"ACTIVE" => "Y"
		);
			//for GetItemPrices	
			if(!$arParams["USE_PRICE_COUNT"])
			{
				foreach($arResult["CAT_PRICES"] as $key => $value)
				{
					$arSelect[] = $value["SELECT"];
					$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
				}	
			}
		
		$res = CIBlockElement::GetList(false,$arFilter,false,false,$arSelect);
		while($arElement = $res->GetNext()){

			$arElement["PRICES"] = CIBlockPriceTools::GetItemPrices($arElement["IBLOCK_ID"], $arResult["CAT_PRICES"], $arElement, $arParams['PRICE_VAT_INCLUDE'], $arResult['CONVERT_CURRENCY']);
			$arElement["CAN_BUY"] = CIBlockPriceTools::CanBuy($arElement["IBLOCK_ID"], $arResult["CAT_PRICES"], $arElement);

			$arElement["BUY_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arElement["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			$arElement["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arElement["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			
			if($arElement["DETAIL_PICTURE"] > 0){
					$arFileTmp = CFile::ResizeImageGet(
						$arElement["DETAIL_PICTURE"] ,
						array("width" => $arParams["DISPLAY_LINKEDIMG_WIDTH"], "height" => $arParams["DISPLAY_LINKEDIMG_HEIGHT"]),
						BX_RESIZE_IMAGE_EXACT,
						true, $arFilter
					);
				$arElement["THUMBNAIL_IMAGE"] = array(
					"SRC" => $arFileTmp["src"],
					'WIDTH' => $arFileTmp["width"],
					'HEIGHT' => $arFileTmp["height"],
				);
			}

			$arResult["LINKED_ELEMENTS"][] = $arElement;
		}
}
?>