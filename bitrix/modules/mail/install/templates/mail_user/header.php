<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
	<style type="text/css">
		/* Client-specific Styles */

		#outlook a {padding:0;}
		body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
		.ExternalClass {width:100%;}
		.ExternalClass,
		.ExternalClass p,
		.ExternalClass span,
		.ExternalClass font,
		.ExternalClass td,
		.ExternalClass div {line-height: 100%;}
		#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}

		/* End reset */
	</style>
</head>
<body>
<? if (\Bitrix\Main\Loader::includeModule('mail')) : ?>
<?=\Bitrix\Mail\Message::getQuoteStartMarker(true); ?>
<? endif; ?>
<?
$protocol = \Bitrix\Main\Config\Option::get("main", "mail_link_protocol", 'https', $arParams["SITE_ID"]);
$serverName = $protocol."://".$arParams["SERVER_NAME"];
?>
<table cellpadding="0" cellspacing="0" border="0" align="center" bgcolor="#337e96" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;background-color: #337e96;border: none;height: 100%;width: 100%;">
	<tr>
		<td style="border-collapse: collapse;border-spacing: 0;padding: 0;"></td>
		<td align="left" valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 41px 0 24px;width: 693px;background: url('<?=$serverName?>/bitrix/templates/mail_user/images/top-cloud.png') no-repeat center 36px;"><?
			$str24 = '<span style="color: #c2d1d6;">24</span>';

			$companyName = (
				IsModuleInstalled('bitrix24')
					? COption::getOptionString('bitrix24', 'site_title', '')
					: COption::getOptionString('main', 'site_name', '')
			);

			if (empty($companyName))
			{
				$companyName = $arParams["SITE_NAME"];
			}

			$companyName .= (
				IsModuleInstalled('bitrix24')
					? (COption::GetOptionString("bitrix24", "logo24show", "Y") == "Y" ? $str24 : '')
					: $str24
			);

			?><h1 style="color: #ffffff;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 35px;font-weight: bold;margin: 0;padding: 0;"><?=$companyName?></h1>
		</td>
		<td style="border-collapse: collapse;border-spacing: 0;padding: 0;"></td>
	</tr>
	<tr>
		<td style="border-collapse: collapse;border-spacing: 0;text-align: right;padding: 31px 35px 0 0;vertical-align: top;width: 50%;">
			<img src="/bitrix/templates/mail_user/images/left-cloud.png" alt="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline-block;vertical-align: top;">
		</td>
		<td align="center" valign="top" style="border-collapse: collapse;border-spacing: 0;width: 693px;background-color: #ffffff;border-radius: 7px;padding: 36px 33px 21px;">

			<!-- ***************** END HEADER  ********************-->


			<!-- ***************** CONTENT  ********************-->			<!-- CONTENT -->