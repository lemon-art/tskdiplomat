<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");?>
<?
$ing_redir_301 = array(
'/catalog/hardware_screws_dowels_3.5' => '/catalog/hardware_screws_dowels_3.5/',
'/catalog/hardware_screws_dowels_3.8'=> '/catalog/hardware_screws_dowels_3.8/',
'/catalog/hardware_screws_dowels_4.8'=> '/catalog/hardware_screws_dowels_4.8/',
'/catalog/hardware_screws_dowels_4.2'=> '/catalog/hardware_screws_dowels_4.2/',
'/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh45'=> '/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh45/',
'/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh35'=> '/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh35/',
'/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh55'=> '/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh55/',
'/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh25'=> '/catalog/hardware_screws_dowels_3.5/drywall_screws_metal_3_5kh25/',
'/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh32'=> '/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh32/',
'/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh35'=> '/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh35/',
'/catalog/hardware_screws_dowels_4.8/drywall_screws_tree_4_8kh110'=> '/catalog/hardware_screws_dowels_4.8/drywall_screws_tree_4_8kh110/',
'/catalog/hardware_screws_dowels_4.8/drywall_screws_tree_4_8kh90'=> '/catalog/hardware_screws_dowels_4.8/drywall_screws_tree_4_8kh90/',
'/catalog/hardware_screws_dowels_4.2/drywall_screws_tree_4_2kh65'=> '/catalog/hardware_screws_dowels_4.2/drywall_screws_tree_4_2kh65/',
'/catalog/hardware_screws_dowels_4.2/drywall_screws_metal_4_2kh65'=> '/catalog/hardware_screws_dowels_4.2/drywall_screws_metal_4_2kh65/',
'/catalog/hardware_screws_dowels_4.2/drywall_screws_tree_4_2kh75'=> '/catalog/hardware_screws_dowels_4.2/drywall_screws_tree_4_2kh75/',
'/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh45'=> '/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh45/',
'/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh55'=> '/catalog/hardware_screws_dowels_3.8/drywall_screws_tree_3_8kh55/',
);
if(isset($ing_redir_301[$_SERVER['REQUEST_URI']])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://".$_SERVER['HTTP_HOST'].$ing_redir_301[$_SERVER['REQUEST_URI']]);
	exit();
}

?>