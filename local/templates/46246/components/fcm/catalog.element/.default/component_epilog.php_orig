<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->AddHeadScript('/bitrix/js/jq/jquery-1.9.1.min.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/modernizr-2.5.3.min.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/jquery.browser.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/fancybox/jquery.fancybox.pack.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/zoom/jquery.jqzoom.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/idtabs/jquery.idTabs.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/serialscroll/jquery.serialScroll.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/scrollto/jquery.scrollTo.js');
$APPLICATION->AddHeadScript('/bitrix/js/jq/plugins/productcomments/productcomments.js');



$APPLICATION->SetAdditionalCSS('/bitrix/js/jq/plugins/fancybox/jquery.fancybox.css');
$APPLICATION->SetAdditionalCSS('/bitrix/js/jq/plugins/zoom/jquery.jqzoom.css');
$APPLICATION->SetAdditionalCSS('/bitrix/js/jq/plugins/productcomments/productcomments.css');
?>
<?
$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = array();
if (strlen($notifyOption) > 0)
	$arNotify = unserialize($notifyOption);

if (is_array($arNotify[SITE_ID]) &&
		$arNotify[SITE_ID]['use'] == 'Y' &&
		$USER->IsAuthorized() &&
		is_array($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]) &&
		!empty($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]))
{
	echo '<script type="text/javascript">';
	foreach ($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()] as $val)
	{
		echo 'if (BX("url_notify_'.$val.'"))';
		echo 'BX("url_notify_'.$val.'").innerHTML = \''.GetMessageJS("MAIN_NOTIFY_MESSAGE").'\';';
	}
	echo '</script>';
}
echo bitrix_sessid_post();
?>
<script>
function showAuth(type)
{
	if (type == 'auth')
	{
		BX('popup-buyer-auth-form').style["display"] = "block";
		BX('popup-buyer-title-auth').innerHTML = '<?=GetMessageJS('MAIN_NOTIFY_POPUP_AUTH');?>';
		BX('popup-buyer-title-mail').innerHTML = '<a href="javascript:void(0)" onClick="showAuth(\'mail\');"><?=GetMessageJS('MAIN_NOTIFY_POPUP_MAIL');?></a>';
		BX('popup_user_email').style["display"] = "none";
		BX('popup_user_email').value = '';
	}
	else
	{
		BX('popup-buyer-auth-form').style["display"] = "none";
		BX('popup-buyer-title-auth').innerHTML = '<a href="javascript:void(0)" onClick="showAuth(\'auth\');"><?=GetMessageJS('MAIN_NOTIFY_POPUP_AUTH');?></a>';
		BX('popup-buyer-title-mail').innerHTML = '<?=GetMessageJS('MAIN_NOTIFY_POPUP_MAIL');?>';
		BX('popup_user_email').style["display"] = "block";
		BX('notify_user_login').value = '';
		BX('notify_user_password').value = '';
	}
}
</script>