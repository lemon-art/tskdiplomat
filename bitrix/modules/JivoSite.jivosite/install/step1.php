<?

if(!check_bitrix_sessid()) return;
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/JivoSite.jivosite/config.php');

IncludeModuleLangFile(__FILE__);

?>

<style>
	p.comment{
		width: 500px;
		font-style: italic;
		color: #888;
	}
</style>

<img src="http://www.jivosite.ru/wp-content/themes/Lotus/images/logo.png" alt="">


<?= GetMessage("SIGN_UP_FORM") ?> 

<p><a href="<?echo $APPLICATION->GetCurPage()?>">&laquo; <?= GetMessage("BACK_TO_MODULE_LIST") ?></a></p>
