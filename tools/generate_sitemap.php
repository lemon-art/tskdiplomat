<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/tools/class/sitemap.php");
CModule::IncludeModule("iblock");

$map = new CSitemap;

$siteName = 'http://tskdiplomat.ru';

$count = 0;

	//открываем файл с настройками
	$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/files/sitemap_settings.txt");
	$arSetting = unserialize( $data );
	$arPrior = explode(PHP_EOL, trim($arSetting[priority]));
	$arPriority = Array();
	foreach ( $arPrior as $val ){
		$arPriority[] = trim($val);
	}
	


	$arUrls = $map -> GetTree($_SERVER["DOCUMENT_ROOT"]."/", 10); //получаем страницы из структуры сайта
	$arUrls[] = '/catalog/';

	


	foreach ($arUrls as $arUrl){
		$url = $siteName.$arUrl;
		if ( $map -> CheckPage( $url ) ){						//проверяем страницу
			$lastUpdate = $map -> GetLastUpdate( $url );		//получаем дату изменения
			$changefreq = $arSetting[changefreq][0];
			if ( in_array( $url, $arPriority ) ){
				$priority = 1;
			}
			else {
				$priority = 0.5;
			}
			$xml_data = $map -> sitemap_url_gen($url, $lastUpdate, $changefreq, $priority);
			$parm = $map -> sitemap_file_save($xml_data, $parm);
			$count++;
		}
	}


	//новости
	$arUrls = $map -> GetElementsUrl( 1 );
	$arUrls[] = '/news/';
		
	foreach ($arUrls as $arUrl){
		$url = $siteName.$arUrl['URL'];
		$lastUpdate = $arUrl['DATE'];						//получаем дату изменения
		$changefreq = $arSetting[changefreq][4];
			if ( in_array( $url, $arPriority ) ){
				$priority = 1;
			}
			else {
				$priority = 0.5;
			}
		$xml_data = $map -> sitemap_url_gen($url, $lastUpdate, $changefreq, $priority);
		$parm = $map -> sitemap_file_save($xml_data, $parm);
		$count++;
	}
	
	//бренды
	$arUrls = $map -> GetElementsUrl( 32 );
	$arUrls[] = '/news/';
		
	foreach ($arUrls as $arUrl){
		$url = $siteName.$arUrl['URL'];
		$lastUpdate = $arUrl['DATE'];						//получаем дату изменения
		$changefreq = $arSetting[changefreq][1];
			if ( in_array( $url, $arPriority ) ){
				$priority = 1;
			}
			else {
				$priority = 0.5;
			}
		$xml_data = $map -> sitemap_url_gen($url, $lastUpdate, $changefreq, $priority);
		$parm = $map -> sitemap_file_save($xml_data, $parm);
		$count++;
	}
	
	
	//разделы
	$arSectionUrls = Array();

	$arUrls = $map -> GetSectionsUrl( 3 );
	
	
	foreach ($arUrls as $arUrl){
		$url = $siteName.$arUrl['URL'];
		$lastUpdate = $arUrl['DATE'];						//получаем дату изменения
		$changefreq = $arSetting[changefreq][2];
		if ( in_array( $url, $arPriority ) ){
			$priority = 1;
		}
		else {
			$priority = 0.5;
		}
		$xml_data = $map -> sitemap_url_gen($url, $lastUpdate, $changefreq, $priority);
		$parm = $map -> sitemap_file_save($xml_data, $parm);
		$count++;
		$arSectionUrls[] = $arUrl['URL'];
	}
	
	//элементы
	$arUrls = $map -> GetElementsUrl( 3 );
	
	foreach ($arUrls as $arUrl){
		$url = $siteName.$arUrl['URL'];
		$lastUpdate = $arUrl['DATE'];						//получаем дату изменения
		$changefreq = $arSetting[changefreq][3];
			if ( in_array( $url, $arPriority ) ){
				$priority = 1;
			}
			else {
				$priority = 0.5;
			}
		$xml_data = $map -> sitemap_url_gen($url, $lastUpdate, $changefreq, $priority);
		$parm = $map -> sitemap_file_save($xml_data, $parm);
		$count++;
	}
	
	$parm['end'] = 1;
	$xml_data = '';
	$parm = $map -> sitemap_file_save($xml_data, $parm);

	$arData = Array(
		'date'	 => date("d.m.Y G:i"),
		'count'  => $count
	);
	
	//сохраняем логи
	$fd = fopen($_SERVER["DOCUMENT_ROOT"]."/tools/logs/sitemap.txt", 'w') or die("не удалось создать файл");
	fwrite($fd, serialize($arData) );
	fclose($fd);
	
	
?>