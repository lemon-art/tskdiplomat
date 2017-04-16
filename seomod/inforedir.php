<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/seomod/edit_form_prop.php");
//if($USER->isAdmin()){

global $ID;
$ID = 0;
$propertyName = array();
$msg = 'yes';

//создаем инфоблок
if(CModule::IncludeModule("iblock")) {
$ib = new CIBlock;
$arFields = Array(
  "ACTIVE" => "Y",
  "NAME" => "Redirects",
  "CODE" => "redir",
  "IBLOCK_TYPE_ID" => "Seo",
  "SITE_ID" => "s1",
  "SORT" => "555",
  "DESCRIPTION" => "Redir",
  "DESCRIPTION_TYPE" => "text",
  "GROUP_ID" => Array("2"=>"D", "3"=>"R")
  );
  
if ($ID > 0){
  //$res = $ib->Update($ID, $arFields);
  echo "Error! Infoblock not create";
}
else
{
	$ID = $ib->Add($arFields);
	$res = ($ID>0);

  
	$propertyName[] = array("edit1", "Element"); // Ќазвание вкладки
	$propertyName[] = array("ACTIVE", "Active");
	$propertyName[] = array("NAME", "*From:"); // —войство со звездочкой - помечаетс€ как об€зательное

  
	/************/
	//добавл€ем свойства
	$ibp = new CIBlockProperty;

	$arFields = Array(
        "NAME" => "To",
        "ACTIVE" => "Y",
        "SORT" => "100",
        "CODE" => "NEWURL",
        "PROPERTY_TYPE" => "S",
        "USER_TYPE" => "",
        "IBLOCK_ID" => $ID,
        );    
    $PropID = $ibp->Add($arFields);
    $propertyName[] = array("PROPERTY_".$PropID,$arFields["NAME"]);
}

serialize_f($ID, $propertyName);
	
$msg_json = array('id'=>$ID, 'msg'=>$msg);

echo json_encode($msg_json);

}
//}
?>
