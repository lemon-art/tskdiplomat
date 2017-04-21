<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройка генерациия sitemap.xml");
?>

<style>
	label span {
		width: 200px;
		margin-right: 20px;
		font-weight: 400;
		display: block;
		float: left;
	}

</style>

<?
if ( count( $_POST[changefreq] ) > 0){

	$arData = $_POST;
	//сохраняем настройки
	$fd = fopen($_SERVER["DOCUMENT_ROOT"]."/tools/files/sitemap_settings.txt", 'w') or die("не удалось создать файл");
	fwrite($fd, serialize($arData) );
	fclose($fd);
}



?>

<?
	//открываем файл с логами
	$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/logs/sitemap.txt");
	$arLogData = unserialize( $data );
	
	//открываем файл с настройками
	$data = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/tools/files/sitemap_settings.txt");
	$arSetting = unserialize( $data );
	
?>

<p>Последний sitemap.xml создан: <?=$arLogData["date"]?> <a href="#" id="sitemap_make">создать новый</a></p>
<p>Количество записей: <?=$arLogData["count"]?></p>
<p><a href="/sitemap.xml" target="_blank">Открыть sitemap.xml</a></p>


<img src="images/preloader.gif" id="preloader" style="display: none;">
<form action='' method="post" id="set_form">
	<h3>Настройка changefreq</h3>
	<label>
		<span>Статические страницы:</span>
		<select name="changefreq[0]">
			<option value="always" <?if ($arSetting[changefreq][0] == 'always' ):?>selected<?endif;?>>always</option>
			<option value="hourly" <?if ($arSetting[changefreq][0] == 'hourly'):?>selected<?endif;?>>hourly</option>
			<option value="daily" <?if ($arSetting[changefreq][0] == 'daily'):?>selected<?endif;?>>daily</option>
			<option value="monthly" <?if ($arSetting[changefreq][0] == 'monthly'):?>selected<?endif;?>>monthly</option>	
			<option value="yearly" <?if ($arSetting[changefreq][0] == 'yearly'):?>selected<?endif;?>>yearly</option>
			<option value="never" <?if ($arSetting[changefreq][0] == 'never'):?>selected<?endif;?>>never</option>			
		</select>
			
	</label>
	<br>
	<label>
		<span>Страницы производителей: </span>
		<select name="changefreq[1]">
			<option value="always" <?if ($arSetting[changefreq][1] == 'always' ):?>selected<?endif;?>>always</option>
			<option value="hourly" <?if ($arSetting[changefreq][1] == 'hourly'):?>selected<?endif;?>>hourly</option>
			<option value="daily" <?if ($arSetting[changefreq][1] == 'daily'):?>selected<?endif;?>>daily</option>
			<option value="monthly" <?if ($arSetting[changefreq][1] == 'monthly'):?>selected<?endif;?>>monthly</option>	
			<option value="yearly" <?if ($arSetting[changefreq][1] == 'yearly'):?>selected<?endif;?>>yearly</option>
			<option value="never" <?if ($arSetting[changefreq][1] == 'never'):?>selected<?endif;?>>never</option>			
		</select>
			
	</label>
	<br>
	<label>
		<span>Страницы новостей: </span>
		<select name="changefreq[4]">
			<option value="always" <?if ($arSetting[changefreq][1] == 'always' ):?>selected<?endif;?>>always</option>
			<option value="hourly" <?if ($arSetting[changefreq][1] == 'hourly'):?>selected<?endif;?>>hourly</option>
			<option value="daily" <?if ($arSetting[changefreq][1] == 'daily'):?>selected<?endif;?>>daily</option>
			<option value="monthly" <?if ($arSetting[changefreq][1] == 'monthly'):?>selected<?endif;?>>monthly</option>	
			<option value="yearly" <?if ($arSetting[changefreq][1] == 'yearly'):?>selected<?endif;?>>yearly</option>
			<option value="never" <?if ($arSetting[changefreq][1] == 'never'):?>selected<?endif;?>>never</option>			
		</select>
			
	</label>
	<br>
	<label>
		<span>Разделы каталога: </span>
		<select name="changefreq[2]">
			<option value="always" <?if ($arSetting[changefreq][2] == 'always' ):?>selected<?endif;?>>always</option>
			<option value="hourly" <?if ($arSetting[changefreq][2] == 'hourly'):?>selected<?endif;?>>hourly</option>
			<option value="daily" <?if ($arSetting[changefreq][2] == 'daily'):?>selected<?endif;?>>daily</option>
			<option value="monthly" <?if ($arSetting[changefreq][2] == 'monthly'):?>selected<?endif;?>>monthly</option>	
			<option value="yearly" <?if ($arSetting[changefreq][2] == 'yearly'):?>selected<?endif;?>>yearly</option>
			<option value="never" <?if ($arSetting[changefreq][2] == 'never'):?>selected<?endif;?>>never</option>		
		</select>
			
	</label>
	<br>
	<label>
		<span>Элементы каталога: </span>
		<select name="changefreq[3]">
			<option value="always" <?if ($arSetting[changefreq][3] == 'always' ):?>selected<?endif;?>>always</option>
			<option value="hourly" <?if ($arSetting[changefreq][3] == 'hourly'):?>selected<?endif;?>>hourly</option>
			<option value="daily" <?if ($arSetting[changefreq][3] == 'daily'):?>selected<?endif;?>>daily</option>
			<option value="monthly" <?if ($arSetting[changefreq][3] == 'monthly'):?>selected<?endif;?>>monthly</option>	
			<option value="yearly" <?if ($arSetting[changefreq][3] == 'yearly'):?>selected<?endif;?>>yearly</option>
			<option value="never" <?if ($arSetting[changefreq][3] == 'never'):?>selected<?endif;?>>never</option>			
		</select>
			
	</label>	
	<br>
	<br>
	<h3>Настройка priority</h3>
	<p>Укажите с новой строки ссылки для которых необходимо установить priority = 1</p>
	<textarea name="priority" cols='70' rows='10'><?=$arSetting[priority]?></textarea>
	<br>
	<br>
	<input type="submit" id="submitButton" value="Сохранить">
	
</form>



<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>

	
	$('#sitemap_make').click( function() { //кнопка пересоздать карту сайта


		$('#submitButton').hide();
		$('#set_form').hide();
		$('#preloader').show();
		$('input#submitButtonEdit').hide();
			$.post("/tools/generate_sitemap.php",
				   {},
				   function(result){ 
						window.location.href = '';
				  }
			);
		return false;
	});
	
	
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>