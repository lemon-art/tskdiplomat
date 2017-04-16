<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($arParams['JQUERY_EN'] != 'N')
	CUtil::InitJSCore(array("jquery"));

if(is_array($arParams["PROPERTY_FIELDS"]) && in_array("PHONE", $arParams["PROPERTY_FIELDS"]))
{
	$APPLICATION->AddHeadScript('/bitrix/js/altasib.feedback/jquery.maskedinput/jquery.maskedinput.min.js');
}

if($arParams['ALX_CHECK_NAME_LINK']=='Y')
{
	if($arParams['FANCYBOX_EN'] != 'N')
	{
		$APPLICATION->SetAdditionalCSS("/bitrix/js/altasib.feedback/fancybox2/css/jquery.fancybox.css");
		$APPLICATION->AddHeadScript('/bitrix/js/altasib.feedback/fancybox2/js/jquery.fancybox.pack.js');
	}
	$APPLICATION->AddHeadScript('/bitrix/js/altasib.feedback/jquery.form/jquery.form.min.js');
}

if($arParams["USE_CAPTCHA"] == "Y" && $arParams["CAPTCHA_TYPE"] == "recaptcha")
{
	$APPLICATION->AddHeadScript('https://www.google.com/recaptcha/api.js?onload=AltasibFeedbackOnload&render=explicit&hl='.LANGUAGE_ID);
}
?>