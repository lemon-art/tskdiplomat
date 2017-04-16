<?            
IncludeModuleLangFile(__FILE__);
$iModuleID = "sotbit.seometa";
if ($APPLICATION->GetGroupRight($iModuleID) != "D") {

	$rsSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE"=>"Y"));
	while ($arSite = $rsSites->Fetch())
	{
		$Sites[]=$arSite;
	}
	unset($rsSites);
	unset($arSite);

	$Paths=array('settings'=>'.php');
	if(count($Sites)==1)//If one site
	{
		foreach($Paths as $key=>$Path)
			$Settings[$key]=array(
				"text" => GetMessage("MENU_SEOMETA_".$key."_SETTINGS_TEXT"),
				"url" => "sotbit.seometa_".$key.$Path."?lang=".LANGUAGE_ID.'&site='.$Sites[0]['LID'],
				"title" => GetMessage("MENU_SEOMETA_".$key."_SETTINGS_TEXT")
			);
	}
	else//If some site
	{
		$Items=array();
		foreach($Paths as $key=>$Path)
		{
			foreach($Sites as $Site)
			{
				$Items[$key][]=array(
					"text" => '['.$Site['LID'].'] '.$Site['NAME'],
					"url" => "sotbit.seometa_".$key.$Path."?lang=".LANGUAGE_ID.'&site='.$Site['LID'],
					"title" => $Site['NAME']
				);
			}
		}


		foreach($Paths as $key=>$Path)
			$Settings[$key]=array(
				"text" => GetMessage("MENU_SEOMETA_".$key."_SETTINGS_TEXT"),
					"items_id" => "menu_sotbit.seometa_settings".$key,
				"items"=>
						$Items[$key]
				,
				"title" => GetMessage("MENU_SEOMETA_".$key."_SETTINGS_TEXT")
			);
	}



    $aMenu = array(
        "parent_menu" => "global_menu_marketing",
        "section" => 'sotbit.seometa',
        "sort" => 2000,
        "text" => GetMessage("MENU_SEOMETA_TEXT"),
        "title" => GetMessage("MENU_SEOMETA_TITLE"),
        "url" => "sotbit.seometa_list.php?lang=" . LANGUAGE_ID,
        "icon" => "seometa_menu_icon",
        "page_icon" => "seometa_page_icon",
        "items_id" => "menu_sotbit.seometa",
        "items" => array(
            array(
                "text" => GetMessage("MENU_SEOMETA_ADMIN_TEXT"),
                "url" => "sotbit.seometa_list.php?lang=" . LANGUAGE_ID,
                "more_url" => array(
                    "sotbit.seometa_list.php",
                    "sotbit.seometa_edit.php",
                    "sotbit.seometa_section_edit.php"
                ),
                "title" => GetMessage("MENU_SEOMETA_ADMIN_TITLE")
            ),
            array(
                "text" => GetMessage("MENU_SEOMETA_SITEMAP_ADMIN_TEXT"),
                "url" => "sotbit.seometa_sitemap_list.php?lang=" . LANGUAGE_ID,
                "more_url" => array(
                    "sotbit.seometa_sitemap_list.php",
                    "sotbit.seometa_sitemap_edit.php"
                ),
                "title" => GetMessage("MENU_SEOMETA_SITEMAP_ADMIN_TITLE")
            ),
			array(
				"text" => GetMessage("MENU_SEOMETA_SETTINGS_TEXT"),
				"title" => GetMessage("MENU_SEOMETA_SETTINGS_TEXT"),
				"dynamic" => true,
				"items_id" => "menu_sotbit.seometa.settings",
				"items"=>array(
					$Settings['settings'],
				),
			),
            array(
                "text" => GetMessage("MENU_SEOMETA_CHPU_TEXT"),
                "url" => "sotbit.seometa_chpu_list.php?lang=" . LANGUAGE_ID,
                "more_url" => array(
                    "sotbit.seometa_chpu_list.php",
                    "sotbit.seometa_chpu_edit.php",
                    "sotbit.seometa_section_chpu_edit.php"
                ),
                "title" => GetMessage("MENU_SEOMETA_CHPU_TITLE")
            ),
            array(
                "text" => GetMessage("MENU_SEOMETA_STATISTICS_TEXT"),                       
                "dynamic" => true,
                "items_id" => "menu_sotbit.seometa.statistics",
                "title" => GetMessage("MENU_SEOMETA_STATISTICS_TITLE"),
                "items" => array(
                    array(
                        "items_id" => "menu_sotbit.seometa.statistics",
                        "text" => GetMessage("MENU_SEOMETA_STATISTICS_GRAPH_TITLE"),
                        "url" => "sotbit.seometa_stat_graph.php?lang=" . LANGUAGE_ID,      
                        "title" => GetMessage("MENU_SEOMETA_STATISTICS_GRAPH_TITLE")
                    ),
                    array(
                        "items_id" => "menu_sotbit.seometa.statistics",
                        "text" => GetMessage("MENU_SEOMETA_STATISTICS_LIST_TITLE"),
                        "url" => "sotbit.seometa_stat_list.php?lang=" . LANGUAGE_ID,      
                        "title" => GetMessage("MENU_SEOMETA_STATISTICS_LIST_TITLE")
                    ),
                ),
            ),
        )
    );
    return $aMenu;
}

return false;
?>