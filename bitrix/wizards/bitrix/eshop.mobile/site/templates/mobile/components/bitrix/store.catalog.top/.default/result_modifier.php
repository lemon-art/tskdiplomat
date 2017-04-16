<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach ($arResult['ITEMS'] as $key => $arElement)
{
	if(is_array($arElement["DETAIL_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arElement['DETAIL_PICTURE'],
			array("width" => 75, 'height' => 225),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arResult['ITEMS'][$key]['PREVIEW_IMG'] = array(
			'SRC' => $arFileTmp["src"],
			'WIDTH' => $arFileTmp["width"],
			'HEIGHT' => $arFileTmp["height"],
		);
	}
}
$arResult["ROWS"] = array();

foreach ($arResult['ITEMS'] as $key => $arElement)
{
	$arRow = array_splice($arResult["ITEMS"], 0, $arParams["LINE_ELEMENT_COUNT"]);
	while(count($arRow) < $arParams["LINE_ELEMENT_COUNT"])
		$arRow[]=false;
	if(!empty($arRow[0]))
		$arResult["ROWS"][]=$arRow;
}
?>
