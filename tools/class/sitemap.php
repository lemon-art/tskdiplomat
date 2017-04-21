<?
		use Bitrix\Highloadblock as HL;
		use Bitrix\Main\Entity;

class CSitemap
{
    private $sitemap_urls = array();
    private $base;
    private $protocol;
    private $domain;
    private $check = array();
    private $proxy = "";
	

	//проверка страницы
	function CheckPage($url){
		
		//проверяем ответ сервера
		$arServer = get_headers($url);
		if ( $arServer[0] == 'HTTP/1.1 200 OK' ){
			return true;
		}
		else {
			return false;
		}
	
	}
	
	//определяем дату изменения файла
	function GetLastUpdate($url){
		
		return date ("Y-m-d", filemtime($_SERVER["DOCUMENT_ROOT"]."/".$arUrl."index.php"));
	
	}
	
	//сканирует страницы сайта по менюшкам
	function GetTree($dir, $max_depth, $get_description = false)
	{
		$GLOBALS["arrMainMenu"] = Array("about", "foot1", "foot2");
		//$GLOBALS["arrChildMenu"] = explode(",",COption::GetOptionString("main","map_left_menu_type","left"));
		$GLOBALS["arrSearchPath"] = array();
		$GLOBALS["arrSearchUrls"] = array();
		$GLOBALS["arrSearchUrls"][] = "";
		global $USER;
		global $APPLICATION;
		$arMap = $this->GetTreeRecursive($dir, 0, $max_depth, $get_description);

		return $arMap;
	}
	
	
	function GetTreeRecursive($PARENT_PATH, $level, $max_depth, $get_description = false){
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $arrMainMenu, $arrChildMenu, $arrSearchPath, $arrSearchUrls, $APPLICATION, $USER;
		
		static $arIndexes = false;
		if($arIndexes === false)
			$arIndexes = GetDirIndexArray();

		$arrMenu = $level == 0 ? $arrMainMenu : $arrChildMenu;
		
		echo "<pre>";
		print_r( $arrMenu );
		echo "</pre>";

		$map = array();

		if(is_array($arrMenu) && count($arrMenu)>0)
		{
			foreach($arrMenu as $mmenu)
			{
				$menu_file = ".".trim($mmenu).".menu.php";
				$menu_file_ext = ".".trim($mmenu).".menu_ext.php";

				$aMenuLinks = array();
				$bExists = false;

				if(file_exists($PARENT_PATH.$menu_file))
				{
					include($PARENT_PATH.$menu_file);
					$bExists = true;
				}

				if(file_exists($PARENT_PATH.$menu_file_ext))
				{
					include($PARENT_PATH.$menu_file_ext);
					$bExists = true;
				}

				if ($bExists && is_array($aMenuLinks))
				{
					foreach ($aMenuLinks as $aMenu)
					{
						if (strlen($aMenu[0]) <= 0) continue;
						if(count($aMenu)>4)
						{
							$CONDITION = $aMenu[4];
							if(strlen($CONDITION)>0 && (!eval("return ".$CONDITION.";")))
								continue;
						}

						$search_child = false;
						$search_path = '';
						$full_path = '';
						if ($aMenu[1] <> '')
						{
							if(preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $aMenu[1]))
							{
								$full_path = $aMenu[1];
							}
							else
							{
								$full_path = trim(Rel2Abs(substr($PARENT_PATH, strlen($_SERVER["DOCUMENT_ROOT"])), $aMenu[1]));

								$slash_pos = strrpos($full_path, "/");
								if ($slash_pos !== false)
								{
									$page = substr($full_path, $slash_pos+1);
									if(($pos = strpos($page, '?')) !== false)
										$page = substr($page, 0, $pos);
									if($page == '' || $page == 'index.php' || in_array($page, $arIndexes))
									{
										$search_path = substr($full_path, 0, $slash_pos+1);
										$search_child = true;
									}
								}
							}
						}

						if ($full_path <> '')
						{
							$FILE_ACCESS = (preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $full_path)) ? "R" : $APPLICATION->GetFileAccessPermission($full_path);

							if ($FILE_ACCESS!="D" && $aMenu[3]["SEPARATOR"]!="Y")
							{
								$is_dir = ($search_child && is_dir($_SERVER["DOCUMENT_ROOT"].$search_path)) ? "Y" : "N";
								if ($is_dir=="Y")
								{
									$search_child &= $level < $max_depth;
									$search_child &= !in_array($search_path, $arrSearchPath);
								}
								else
								{
									$search_child = false;
								}

								$ar = array();
								$ar["LEVEL"] = $level;
								if(isset($aMenu[3]["DEPTH_LEVEL"]) && $aMenu[3]["DEPTH_LEVEL"] > 1)
									$ar["LEVEL"] += ($aMenu[3]["DEPTH_LEVEL"] - 1);

								if($ar["LEVEL"] > $max_depth)
									continue;

								$ar["ID"] = md5($full_path.$ar["COUNTER"]);
								$ar["IS_DIR"] = is_dir($_SERVER["DOCUMENT_ROOT"].$full_path) ? "Y" : "N";
								$ar["NAME"] = $aMenu[0];
								$ar["PATH"] = $PARENT_PATH;
								$ar["FULL_PATH"] = $full_path;
								$ar["SEARCH_PATH"] = $search_path;
								$ar["DESCRIPTION"] = "";
								
								if ( !in_array($full_path, $arrSearchUrls )){ 
									$arrSearchUrls[] = $full_path;
								}


								if ($search_child)
								{
									$arrSearchPath[] = $search_path;
									$ar["CHILDREN"] = $this->GetTreeRecursive($_SERVER["DOCUMENT_ROOT"].$ar["SEARCH_PATH"], $level+1, $max_depth, $get_description);
								}

								$map[] = $ar;
							}
						}
					}
				}
			}
		}

		return $arrSearchUrls;
	}
	
	
	//получаем ссылки из фильтров
	function GetFilterUrl( $arSectionUrls ){
		
		$arFilterUrls = Array();
		$IBLOCK_ID = 19;
		
		//открываем файл с массивом соответствия адресов страниц
		$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/files/seo_url.txt");
		$arUrlData = unserialize( $data );
		
		
		CModule::IncludeModule("iblock");
		$arElementsUrls = Array();
		
		$arFilter = Array('IBLOCK_ID'=>$IBLOCK_ID, 'ACTIVE'=>'Y');
		$db_list = CIBlockElement::GetList(Array(), $arFilter);
		while($ar_result = $db_list->GetNext()) {
			
			$url = $ar_result['NAME'];
			
			if ( $arUrlData[$url] ){
				$url = $arUrlData[$url];
			}
			
			if ( !in_array($url, $arSectionUrls) ){  		//проверяем не были ли занесены данные ссылки в результат ранее
				$arElementsUrls[] = Array(
					'URL'  => $url,
					'DATE' => ConvertDateTime($ar_result['TIMESTAMP_X'], "YYYY-MM-DD")
				);
			}
			
			
			
		}
		return $arElementsUrls;

	}
	
	//получаем ссылки на разделы инфоблока
	function GetSectionsUrl( $IBLOCK_ID ){
		
		CModule::IncludeModule("iblock");
		$arSectionUrls = Array();
		
		
		//открываем файл с массивом соответствия адресов страниц
		$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/files/seo_url.txt");
		$arUrlData = unserialize( $data );
		
		$arFilter = Array('IBLOCK_ID'=>$IBLOCK_ID, 'GLOBAL_ACTIVE'=>'Y');
		$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true, Array('UF_CUSTOM_URL', 'CODE', 'SECTION_PAGE_URL', 'TIMESTAMP_X'));
		while($ar_result = $db_list->GetNext()) {
			if ( $ar_result['UF_CUSTOM_URL'] ){
				if ( $arUrlData[$ar_result['UF_CUSTOM_URL']] ){
					$section_url = $arUrlData[$ar_result['UF_CUSTOM_URL']];
				}
				else {
					$section_url = $ar_result['UF_CUSTOM_URL'];
				}
				
			}
			else {
				$section_url = $ar_result['SECTION_PAGE_URL'];
			}
			
			$arSectionUrls[] = Array(
				'URL'  => $section_url,
				'DATE' => ConvertDateTime($ar_result['TIMESTAMP_X'], "YYYY-MM-DD")
			);
			
		}
		
		return $arSectionUrls;
		
		
	}
	
	//получаем ссылки на элементы инфоблока
	function GetElementsUrl( $IBLOCK_ID ){
		
		CModule::IncludeModule("iblock");
		$arElementsUrls = Array();
		
		$arFilter = Array('IBLOCK_ID'=>$IBLOCK_ID, 'ACTIVE'=>'Y');
		$db_list = CIBlockElement::GetList(Array(), $arFilter);
		while($ar_result = $db_list->GetNext()) {
			$arElementsUrls[] = Array(
				'URL'  => $ar_result['DETAIL_PAGE_URL'],
				'DATE' => ConvertDateTime($ar_result['TIMESTAMP_X'], "YYYY-MM-DD")
			);
			
		}
		return $arElementsUrls;
		
		
	}
	
	
	
	
	//генерирует одну запись xml
	function sitemap_url_gen($url, $lastmod = '', $changefreq = '', $priority = ''){
	
 
		  $search = array('&', '\'', '"', '>', '<');
		  $replace = array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;');
		  $url = str_replace($search, $replace, $url);
		  $lastmod = (empty($lastmod)) ? '' : '
			<lastmod>'.$lastmod.'</lastmod>';
		  $changefreq = (empty($changefreq)) ? '' : '
			<changefreq>'.$changefreq.'</changefreq>';
		  $priority = (empty($priority)) ? '' : '
			<priority>'.$priority.'</priority>';
		  $res = '
		  <url>
			<loc>'.$url.'</loc>'.$lastmod.$changefreq.$priority.'
		  </url>';
		  return $res;
		  
	} 
	
	
	//сохраняет в файл
	function sitemap_file_save($data, $parm = array(), $max_url_in_file = 50000, $max_size_file = 10485760){
		  $parm['url_counter_all'] = 0;
		  $sitemap_file_name = 'sitemap';
		  $first_str = '<?xml version="1.0" encoding="UTF-8"?>
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		  $last_str = '
			</urlset>';
		  
		  if (!isset($parm['fp']))
			{
			$parm['counter_files'] = (isset($parm['counter_files'])) ? $parm['counter_files'] : 1;
			$parm['cycling_protect'] = (isset($parm['cycling_protect'])) ? $parm['cycling_protect'] : 0;
			$parm['name_file'] = $sitemap_file_name.$parm['counter_files'].'.xml';
			if ($parm['counter_files'] == 1)
			  {
			  $parm['name_file'] = $_SERVER['DOCUMENT_ROOT'] . "/" . $sitemap_file_name.'.xml';
			  $file_in_dir = scandir($_SERVER['DOCUMENT_ROOT']);
			  $i = 0;
			  $d_files = '';
			  foreach ($file_in_dir AS $val)
				{
				if (preg_match('~^'.$sitemap_file_name.'[0-9]{0,5}\.xml$~', $val))
				  {
				  unlink($val); 
				  $d_files .= $val.', ';
				  $i++;
				  }
				}
			  $d_files = mb_substr($d_files, 0, -2);
			  $parm['log'][0] = 'Deleted '.$i.' file(s): '.$d_files.'.';
			  }
			  
			$parm['fp'] = fopen($parm['name_file'], "w");
				
			if (!$parm['fp'])
			  exit('Cannot create file '.$name_file.'. Check access to file or parent directory.');
			else 
			  {
			  fwrite($parm['fp'], $first_str);
			  $parm['size_counter'] = mb_strlen($first_str.$last_str);
			  }
			}
			
		  $parm['size_counter'] = $parm['size_counter'] + mb_strlen($data);

		  if ($parm['size_counter'] <= $max_size_file OR $parm['cycling_protect'] == 1) 
			{
			fwrite($parm['fp'], $data);
			$parm['url_counter']++;
			$parm['cycling_protect'] = 0;
			}
		  else
			{
			fwrite($parm['fp'], $last_str);
			fclose($parm['fp']);
			unset($parm['fp']);
			$parm['size_counter'] = $parm['size_counter'] - mb_strlen($data);
			$parm['log'][$parm['counter_files']]['file'] = $parm['name_file'];
			$parm['log'][$parm['counter_files']]['size'] = $parm['size_counter'];
			$parm['log'][$parm['counter_files']]['urls'] = $parm['url_counter'];
			$parm['log'][$parm['counter_files']]['end'] = 'exceeded size counter';
			$parm['counter_files'] ++;
			$parm['url_counter'] = 0;
			$parm['cycling_protect'] = 1;
			$parm = sitemap_file_save($data, $parm, $max_url_in_file, $max_size_file);
			return $parm;
			}
		  
		  if ($parm['url_counter'] >= $max_url_in_file OR $parm['end'])
			{
			$action_txt = ($parm['end']) ? 'end urls' : 'exceeded url counter';
			if (empty($data))
			  $parm['url_counter']--;
			fwrite($parm['fp'], $last_str);
			fclose($parm['fp']);
			unset($parm['fp']);
			$parm['log'][$parm['counter_files']]['file'] = $parm['name_file'];
			$parm['log'][$parm['counter_files']]['size'] = $parm['size_counter'];
			$parm['log'][$parm['counter_files']]['urls'] = $parm['url_counter'];
			$parm['log'][$parm['counter_files']]['end'] = $action_txt;
			$parm['counter_files'] ++;
			$parm['url_counter'] = 0;
			}

		  return $parm;
	}
	
	
	
	
}