<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//$arResult["PREVIEW_TEXT"] = strip_tags($arResult["PREVIEW_TEXT"]);
if ($arParams['USE_COMPARE'])
{
	$delimiter = strpos($arParams['COMPARE_URL'], '?') ? '&' : '?';

	//$arResult['COMPARE_URL'] = str_replace("#ACTION_CODE#", "ADD_TO_COMPARE_LIST",$arParams['COMPARE_URL']).$delimiter."id=".$arResult['ID'];

	$arResult['COMPARE_URL'] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("action=ADD_TO_COMPARE_LIST&id=".$arResult['ID'], array("action", "id")));
}

if(is_array($arResult["DETAIL_PICTURE"]))
{
	$arFileTmp = CFile::ResizeImageGet(
		$arResult['DETAIL_PICTURE'],
		array("width" => 100, 'height' => 100),
		BX_RESIZE_IMAGE_PROPORTIONAL,
		true
	);
	$arResult['DETAIL_PICTURE_350'] = array(
		'SRC' => $arFileTmp["src"],
		'WIDTH' => $arFileTmp["width"],
		'HEIGHT' => $arFileTmp["height"],
	);
}

if (is_array($arResult['MORE_PHOTO']) && count($arResult['MORE_PHOTO']) > 0)
{
	unset($arResult['DISPLAY_PROPERTIES']['MORE_PHOTO']);

	foreach ($arResult['MORE_PHOTO'] as $key => $arFile)
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arFile,
			array("width" => 50, 'height' => 50),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arFile['PREVIEW_WIDTH'] = $arFileTmp["width"];
		$arFile['PREVIEW_HEIGHT'] = $arFileTmp["height"];

		$arFile['SRC_PREVIEW'] = $arFileTmp['src'];
		$arResult['MORE_PHOTO'][$key] = $arFile;
	}
}
?>