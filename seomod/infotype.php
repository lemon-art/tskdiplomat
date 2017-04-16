<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("iblock")) {
$arFields = Array(
	'ID'=>'Seo',
	'SECTIONS'=>'Y',
	'IN_RSS'=>'N',
	'SORT'=>100,
	'LANG'=>Array(
		'ru'=>Array(
			'NAME'=>'Seo',
			'SECTION_NAME'=>'',
			'ELEMENT_NAME'=>''
			)
		)
	);

$obBlocktype = new CIBlockType;
$DB->StartTransaction();
$res = $obBlocktype->Add($arFields);
if(!$res)
{
   $DB->Rollback();
   
   $msg_json = array('id'=>'', 'msg'=>'Error: '.$obBlocktype->LAST_ERROR.'<br>');
   echo json_encode($msg_json);
}
else {
   $DB->Commit();
   
   $msg_json = array('id'=>'', 'msg'=>'yes');
   echo json_encode($msg_json);
   }
}
?>