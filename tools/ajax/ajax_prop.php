<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");



parse_str($_POST["data"]);
parse_str($_POST["n"]);

//echo $n;
//сохраняем в файл параметры проверки
$fd = fopen($_SERVER["DOCUMENT_ROOT"]."/tools/s_".$IBLOCK_ID.".txt", 'w') or die("не удалось создать файл");
fwrite($fd, $_POST["data"]);
fclose($fd);

//получаем названия для отчета
$res = CIBlock::GetByID( $IBLOCK_ID );
if($ar_res = $res->GetNext())
  $IBLOCK_ID_NAME = $ar_res['NAME'];
  
$res = CIBlockSection::GetByID( $SECTION_ID );
if($ar_res = $res->GetNext())
  $SECTION_ID_NAME = $ar_res['NAME'];
  
$arFilterProp = Array();
$arPropNames = Array();
$arFilterProp["LOGIC"] = "OR";
foreach( $prop as $key => $val){
	$arFilterProp["PROPERTY_".$key] = false;
	$res = CIBlockProperty::GetByID($key);
	if($ar_res = $res->GetNext())
		$arPropNames[] = $ar_res['NAME'];
} 
// 
  
$arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL");



$arFilter = array(
    "IBLOCK_ID" => $IBLOCK_ID,
	"ACTIVE"=>"Y",
	"SECTION_ID" => $SECTION_ID,
	$arFilterProp
); 

if ( $SECTION_ID )
	$arFilter["INCLUDE_SUBSECTIONS"] = "Y";

$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>2000), $arSelect);

//открыввем файл для записи результатов
$fd = fopen($_SERVER["DOCUMENT_ROOT"]."/tools/propChecked.txt", 'w') or die("не удалось создать файл");
fwrite($fd, "Результаты проверки\r\n\r\n" . PHP_EOL);
fwrite($fd, "Инфоблок: " . $IBLOCK_ID_NAME . "\r\n");
fwrite($fd, "Раздел: " . $SECTION_ID_NAME . "\r\n");
fwrite($fd, "Обязаельные свойства: " . implode(", ", $arPropNames) . "\r\n\r\n");
fwrite($fd, "Найдено элементов: " . $res->SelectedRowsCount() . "\r\n\r\n");
?>


<h2>Найдено элементов: <?=$res->SelectedRowsCount()?> (<a href="/tools/propChecked.txt" download>скачать отчет</a>)</h2>
<table id="itog" class="simple-little-table">
	<?while($ar_fields = $res->GetNext()):?>
		<?fwrite($fd, $ar_fields["ID"] . " - " . $ar_fields["NAME"] . "\r\n");?>
		<tr>
			
			<td>
				<?=$ar_fields["ID"]?>
			</td>
			<td>
				<?=$ar_fields["NAME"]?>
			</td>
		</tr>
	<?endwhile;?> 
</table>
<?fclose($fd);?>