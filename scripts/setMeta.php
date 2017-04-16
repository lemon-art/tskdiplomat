<?require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('iblock');
define('IBLOCK_ID', 3);

$arSelect = Array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID');
$arFilter = Array('IBLOCK_ID' => IBLOCK_ID, 'ACTIVE' => 'Y');
$rsFields = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while ($arFields = $rsFields->Fetch()) {
	//Set title
	$arTitle = array(
		': цена, характеристики',
		': купить на сайте',
		': купить в интернет-магазине',
		': характеристики, описание',
		': характеристики',
		': купить по низкой цене',
		': характеристики, наличие',
		': цена, наличие',
	);
	echo '<pre>' . print_r($arFields['NAME'].$arTitle[rand(0, 7)], true) . '</pre>';
	//CIBlockElement::SetPropertyValuesEx($arFields['ID'], IBLOCK_ID, array('TITLE' => $arFields['NAME'].$arTitle[rand(0, 7)]));

	//Set meta
	$arDescription = array(
		'В наличии',
		'В продаже',
		'Покупайте',
		'Предлагаем',
		'В наличии и под заказ',
		''
	);
	$arDescriptionP = array(
		'по цене от',
		'по стоимости от',
		'от',
	);

	$price = CPrice::GetBasePrice($arFields['ID']);

	$val = $arDescription[rand(0, 5)] . ' ' . $arFields['NAME'];
	$val .= ' ' . $arDescriptionP[rand(0, 2)] . ' ' . $price['PRICE'] . 'р.';

	if ($arFields['IBLOCK_SECTION_ID']){
		$arFilter = array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'ID' => $arFields['IBLOCK_SECTION_ID']);
		$rsSections = CIBlockSection::GetList(array(), $arFilter, false, array('NAME'));
		if ($arSection = $rsSections->Fetch()) {
			$val .= ' ' . $arSection['NAME'] . ' ';
		}
	}

	$arDescriptionE = array(
		'на выгодных условиях.',
		'с доставкой по Москве и в ваш регион.',
		'представлены с характеристиками.',
		'с описанием и ценами.',
		'различных назначений.',
		'в широком ассортименте.',
		'в качественном ассортименте.',
	);
	$val .= ' ' . $arDescriptionE[rand(0, 6)];

	if (mb_strlen($val) < 130){
		$nav = CIBlockSection::GetNavChain($arFields['IBLOCK_ID'], $arFields['IBLOCK_SECTION_ID']);
		if ($arSectionPath = $nav->GetNext()){
			$val .= ' ' . $arSectionPath['NAME'] . ' от компании ТСК Дипломат.';
		}

	}
	echo '<pre>' . print_r($val, true) . '</pre>';
	//CIBlockElement::SetPropertyValuesEx($arFields['ID'], IBLOCK_ID, array('META_DESCRIPTION' => $val));
}

