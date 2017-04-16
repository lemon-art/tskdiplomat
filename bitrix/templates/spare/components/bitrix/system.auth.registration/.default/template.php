<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<h1><?=GetMessage("AUTH_REGISTER")?></h1>
<div class="bx-auth">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && is_array($arParams["AUTH_RESULT"]) &&  $arParams["AUTH_RESULT"]["TYPE"] === "OK"):?>
<p><?echo GetMessage("AUTH_EMAIL_SENT")?></p>
<?else:?>

<?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):?>
	<p><?echo GetMessage("AUTH_EMAIL_WILL_BE_SENT")?></p>
<?endif?>
<noindex>
<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform" id="account-creation_form" class="std">
<fieldset class="account_creation">
<?
if (strlen($arResult["BACKURL"]) > 0)
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="REGISTRATION" />
	<h3>Ваши регистрационные данные</h3>
		<p class="required text">
			<label for="USER_NAME"><?=GetMessage("AUTH_NAME")?> <sup>*</sup></label>
			<input type="text" name="USER_NAME" maxlength="50" value="<?=$arResult["USER_NAME"]?>" class="bx-auth-input" />
		</p>		
		<p class="required text">
			<label for="USER_LAST_NAME"><?=GetMessage("AUTH_LAST_NAME")?> <sup>*</sup></label>
			<input type="text" name="USER_LAST_NAME" maxlength="50" value="<?=$arResult["USER_LAST_NAME"]?>" class="bx-auth-input" />
		</p>
		<p class="required text">
			<label for="USER_LOGIN"><?=GetMessage("AUTH_LOGIN_MIN")?> <sup>*</sup></label>
			<input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" class="bx-auth-input" />
		</p>
		<p class="required text">
			<label for="USER_PASSWORD"><?=GetMessage("AUTH_PASSWORD_REQ")?> <sup>*</sup></label>
			<input type="password" name="USER_PASSWORD" maxlength="50" value="<?=$arResult["USER_PASSWORD"]?>" class="bx-auth-input" />
			<?if($arResult["SECURE_AUTH"]):?>
				<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
				<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
				</noscript>
				<script type="text/javascript">
					document.getElementById('bx_auth_secure').style.display = 'inline-block';
				</script>
			<?endif?>
		</p>
		<p class="required text">
			<label for="USER_CONFIRM_PASSWORD"><?=GetMessage("AUTH_CONFIRM")?> <sup>*</sup></label>
			<input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="bx-auth-input" />
		</p>
		<p class="required text">
			<label for="USER_EMAIL"><?=GetMessage("AUTH_EMAIL")?> <sup>*</sup></label>
			<input type="text" name="USER_EMAIL" maxlength="255" value="<?=$arResult["USER_EMAIL"]?>" class="bx-auth-input" />
		</p>

<?// ********************* User properties ***************************************************?>
<?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>

	<h3><?=strLen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?></h3>
	<?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
		<?if ($arUserField["MANDATORY"]=="Y"):?>
		<p class="required text">
			<label for="USER_EMAIL"><?=$arUserField["EDIT_FORM_LABEL"]?> <sup>*</sup></label>
		<?else:?>
		<p class="text">
			<label for="USER_EMAIL"><?=$arUserField["EDIT_FORM_LABEL"]?> </label>
		
		<?endif;?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:system.field.edit",
				$arUserField["USER_TYPE"]["USER_TYPE_ID"],
				array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "bform"), null, array("HIDE_ICONS"=>"Y"));?>
		</p>		
	<?endforeach;?>
<?endif;?>
<?// ******************** /User properties ***************************************************

	/* CAPTCHA */
	if ($arResult["USE_CAPTCHA"] == "Y")
	{
		?>
		<h3><?=GetMessage("CAPTCHA_REGF_TITLE")?></h3>
		<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
		<p class="text">
			<label for="captcha_word"><?=GetMessage("CAPTCHA_REGF_PROMT")?> <sup>*</sup></label>
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
		</p>
		<p class="required text">
			<label for="captcha_word"></label>
			<input type="text" name="captcha_word" maxlength="50" value="" />
		</p>
		<?
	}
	/* CAPTCHA */
	?>
	<p class="cart_navigation required submit">

			<input type="submit" name="Register" value="<?=GetMessage("AUTH_REGISTER")?>" class="exclusive"/>
			
			<span><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></span>
			<span><sup>*</sup><?=GetMessage("AUTH_REQ")?></span>
	</p>
<p>
<a href="<?=$arResult["AUTH_AUTH_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_AUTH")?></b></a>
</p>
</fieldset>
</form>
</noindex>
<script type="text/javascript">
document.bform.USER_NAME.focus();
</script>

<?endif?>
</div>