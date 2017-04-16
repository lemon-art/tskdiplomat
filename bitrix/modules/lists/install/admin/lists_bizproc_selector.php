<?
define("MODULE_ID", "lists");
if($_REQUEST['entity']=="BitrixListsBizprocDocumentLists")
	define("ENTITY", 'Bitrix\Lists\BizprocDocumentLists');
else
	define("ENTITY", "BizprocDocument");

$fp = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/admin/bizproc_selector.php";
if(file_exists($fp))
	require($fp);