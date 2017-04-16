<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?foreach ($arResult['CAT'] as $key => $arElement)
{
	if(is_array($arElement["PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arElement['PICTURE'],
			array("width" => 75, 'height' => 225),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arResult['CAT'][$key]['PICTURE_PREVIEW'] = array(
			'SRC' => $arFileTmp["src"],
			'WIDTH' => $arFileTmp["width"],
			'HEIGHT' => $arFileTmp["height"],
		);
	}
}
?>