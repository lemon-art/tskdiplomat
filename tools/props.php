<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проверка элементов на заполненность свойствами");

CModule::IncludeModule("iblock");

if ( $_GET["IBLOCK_ID"]){
	$IBLOCK_ID = $_GET["IBLOCK_ID"];
}
else {
	$IBLOCK_ID = 3;
}

//считываем ранее введенные в фильтр данные
$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/s_".$IBLOCK_ID.".txt");
parse_str($data);
$arProp = Array();
foreach ( $prop as $key=>$val){
	$arProp[] = $key;
}

$arSections = getSectionList(
 Array(
    'IBLOCK_ID' => $IBLOCK_ID
 ),
 Array(
    'NAME',
    'ID',
	'DEPTH_LEVEL'
 )
);
?>


<style>
.prop_list {
	list-style: none;
	float: left;
	width: 30%;
}

.prop_list li{

}

TABLE {
    border-collapse: collapse; /* Убираем двойные линии между ячейками */
    width: 90%; /* Ширина таблицы */
   }
   TH, TD {
    border: 1px solid black; /* Параметры рамки */
    text-align: left; /* Выравнивание по центру */
    padding: 4px; /* Поля вокруг текста */
   }

.buttons {  
    text-align: center;
    margin: 30px;
}
</style>

<div class="conteiner">


	<form name="" id="props" method="post" action="">

		<input type="hidden" value="<?=$IBLOCK_ID?>" name="IBLOCK_ID">

	

		<label>
			Выберите раздел: 
			<select name="SECTION_ID" id="">
				<?foreach ( $arSections as $arSection):?>
					<option value="<?=$arSection["ID"]?>" <?if ( $SECTION_ID == $arSection["ID"]):?>selected<?endif;?>>
						<?for ($i=1; $i < $arSection["DEPTH_LEVEL"]; $i++):?>..<?endfor;?>
						<?=$arSection["NAME"]?>
					</option>
				<?endforeach;?>
			</select>
			
		</label>
		<div style="clear: both;"></div>
		<ul class="prop_list">
	<?
	$count = 0;
	$properties = CIBlockProperty::GetList(Array("name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
	while ($prop_fields = $properties->GetNext()){
		$count++;
	}
	$n = 0;
	$properties = CIBlockProperty::GetList(Array("name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
	while ($prop_fields = $properties->GetNext()):?>
		
		<?if ( $prop_fields["SORT"] > 0 || $prop_fields["CODE"] == 'BREND'):?>
			<?if ( ($n == round($count/3, 0, PHP_ROUND_HALF_UP)) || ($n == 2*round($count/3, 0, PHP_ROUND_HALF_UP)) ):?>
				</ul>
				<ul class="prop_list">
			<?endif;?>
			<li>
				<label>
					<input type="checkbox" class="check" name="prop[<?=$prop_fields["ID"]?>]" <?if ( in_array($prop_fields["ID"], $arProp) ):?>checked<?endif;?>>  <?=$prop_fields["NAME"]?>
				<label>
			</li>
			<?$n++;?>
		<?endif;?>
		

	<?endwhile;?>
		</ul>
		<div style="clear: both;"></div>
		<div class="buttons">
			<input type="submit" id="submitButton" value="Запросить">
			<input type="button" id="resetButton" value="Сбросить">
		</div>
	</form>


	<div id="itog">

	</div>
</div>


<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<script>

	$('input#submitButton').click( function() {
		
		n = 1;
		$("#itog").empty();
		sendPost ($('form#props').serialize(), n);
		
		
		
		return false;
	});
	
	$('input#resetButton').click( function() {
		
		$('.check').removeAttr('checked');	
		return false;
	});
	
	function sendPost (data, n){
			$.post("/tools/ajax/ajax_prop.php",
				   {'data': $('form#props').serialize(), 'n': n},
				   function(result){ 
						//alert(result);
						$( "#itog" ).append( result );
						n++
						if ( n < 2){
							sendPost (data, n)
						}
				  }
			);
	}

</script>

<?
function getSectionList($filter, $select)
{
   $dbSection = CIBlockSection::GetList(
      Array(
               'LEFT_MARGIN' => 'ASC',
      ),
      array_merge( 
          Array(
             'ACTIVE' => 'Y',
             'GLOBAL_ACTIVE' => 'Y'
          ),
          is_array($filter) ? $filter : Array()
      ),
      false,
      array_merge(
          Array(
             'ID',
             'IBLOCK_SECTION_ID'
          ),
         is_array($select) ? $select : Array()
      )
   );
	$arSections = Array();
	
   while( $arSection = $dbSection-> GetNext(true, false) ){

	   $arSections[] = $arSection;
       //$SID = $arSection['ID'];
       //$PSID = (int) $arSection['IBLOCK_SECTION_ID'];

       //$arLincs[$PSID]['CHILDS'][$SID] = $arSection;

       //$arLincs[$SID] = &$arLincs[$PSID]['CHILDS'][$SID];
   }

   return $arSections;
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
