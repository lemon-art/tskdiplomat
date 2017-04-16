<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult["MESSAGE"] as $itemID=>$itemValue)
	echo ShowMessage(array("MESSAGE"=>$itemValue, "TYPE"=>"OK"));
foreach($arResult["ERROR"] as $itemID=>$itemValue)
	echo ShowMessage(array("MESSAGE"=>$itemValue, "TYPE"=>"ERROR"));

if($arResult["ALLOW_ANONYMOUS"]=="N" && !$USER->IsAuthorized()):
	echo ShowMessage(array("MESSAGE"=>GetMessage("CT_BSE_AUTH_ERR"), "TYPE"=>"ERROR"));
else:
?>
<div class="workarea personal">
	<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
		<?echo bitrix_sessid_post();?>
		<input type="hidden" name="PostAction" value="<?echo ($arResult["ID"]>0? "Update":"Add")?>" />
		<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
		<input type="hidden" name="RUB_ID[]" value="0" />

		<h2 style="margin-top:0"><?echo GetMessage("CT_BSE_SUBSCRIPTION_FORM_TITLE")?></h2>

		<?echo GetMessage("CT_BSE_EMAIL_LABEL")?><br/>
		<input type="text" name="EMAIL" class="input_text_style" value="<?echo $arResult["SUBSCRIPTION"]["EMAIL"]!=""? $arResult["SUBSCRIPTION"]["EMAIL"]: $arResult["REQUEST"]["EMAIL"];?>" class="subscription-email" />
		<br/>

		<div style="margin-bottom: 10px;"><?echo GetMessage("CT_BSE_FORMAT_LABEL")?></div>
		<input type="radio" name="FORMAT" id="MAIL_TYPE_TEXT" value="text" <?if($arResult["SUBSCRIPTION"]["FORMAT"] != "html") echo "checked"?> />
		<label for="MAIL_TYPE_TEXT" style="font-weight: normal;"><?echo GetMessage("CT_BSE_FORMAT_TEXT")?></label>
		<input type="radio" name="FORMAT" id="MAIL_TYPE_HTML" value="html" <?if($arResult["SUBSCRIPTION"]["FORMAT"] == "html") echo "checked"?> />
		<label for="MAIL_TYPE_HTML" style="font-weight: normal;"><?echo GetMessage("CT_BSE_FORMAT_HTML")?></label>
		<br/><br/>

		<div style="margin-bottom: 10px;"><?echo GetMessage("CT_BSE_RUBRIC_LABEL")?></div>
		<table>
		<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
			<tr>
				<td style="vertical-align:top;padding:0 5px;">
					<input type="checkbox" id="RUBRIC_<?echo $itemID?>" name="RUB_ID[]" value="<?=$itemValue["ID"]?>"<?if($itemValue["CHECKED"]) echo " checked"?> />
				</td>
				<td>
					<label for="RUBRIC_<?echo $itemID?>" style="font-weight: normal;"><?echo $itemValue["NAME"]?><p style="font-weight:normal;"><?echo $itemValue["DESCRIPTION"]?></p></label>
				</td>
			</tr>
		<?endforeach;?>
		</table>

		<?if($arResult["ID"]==0):?>
			<p><?echo GetMessage("CT_BSE_NEW_NOTE")?></p>
		<?else:?>
			<p><?echo GetMessage("CT_BSE_EXIST_NOTE")?></p>
		<?endif?>

		<input type="submit" name="Save" class="bt3" value="<?echo ($arResult["ID"] > 0? GetMessage("CT_BSE_BTN_EDIT_SUBSCRIPTION"): GetMessage("CT_BSE_BTN_ADD_SUBSCRIPTION"))?>" />

		<?if($arResult["ID"]>0 && $arResult["SUBSCRIPTION"]["CONFIRMED"] <> "Y"):?>
		<div class="subscription-utility">
			<p><?echo GetMessage("CT_BSE_CONF_NOTE")?></p>
			<input name="CONFIRM_CODE" type="text" class="input_text_style" style="display: inline-block;" value="<?echo GetMessage("CT_BSE_CONFIRMATION")?>" onblur="if (this.value=='')this.value='<?echo GetMessage("CT_BSE_CONFIRMATION")?>'" onclick="if (this.value=='<?echo GetMessage("CT_BSE_CONFIRMATION")?>')this.value=''" />
			<input type="submit" name="confirm" class="bt3" value="<?echo GetMessage("CT_BSE_BTN_CONF")?>" />
		</div>
		<?endif?>
	</form>

	<?if(!CSubscription::IsAuthorized($arResult["ID"])):?>
	<br/>
	<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
		<?echo bitrix_sessid_post();?>
		<input type="hidden" name="action" value="sendcode" />
		<p><?echo GetMessage("CT_BSE_SEND_NOTE")?></p>
		<input name="sf_EMAIL" type="text" class="input_text_style" style="display: inline-block;" value="<?echo GetMessage("CT_BSE_EMAIL")?>" onblur="if (this.value=='')this.value='<?echo GetMessage("CT_BSE_EMAIL")?>'" onclick="if (this.value=='<?echo GetMessage("CT_BSE_EMAIL")?>')this.value=''" />
		<input type="submit" class="bt3" value="<?echo GetMessage("CT_BSE_BTN_SEND")?>" />
	</form>
	<?endif?>
</div>
<?endif;?>