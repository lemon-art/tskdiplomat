<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? $fVerComposite = (defined("SM_VERSION") && version_compare(SM_VERSION, "14.5.0") >= 0 ? true : false); ?>
<? if($fVerComposite) $this->setFrameMode(true); ?>

<?$ALX = "FID".$arParams["FORM_ID"];?>

<? if($arResult["FANCYBOX_".$ALX]!='Y' && !isset($arResult["FANCYBOX_".$ALX]) && $arParams['ALX_CHECK_NAME_LINK']=='Y'): ?>
	<a class="alx_feedback_popup" id="form_id_<?=$ALX?>" href=""><?=$arParams["ALX_NAME_LINK"];?></a>
<?
	$bShort = ($APPLICATION->get_cookie("ALTASIB_FDB_SEND_".$ALX) != 'Y');
	if($arParams['ALX_LOAD_PAGE']=='Y' && $bShort):?>
	<script type="text/javascript">
	<!--
		$(window).load(function (){

			$('#form_id_<?=$ALX?>').fancybox({
				'ajax' : {
					type	: "POST",
					data	: 'OPEN_POPUP_<?=$ALX?>=Y'
				},
				'titleShow': false,
				'type': 'ajax',
				'href': '',
				'overlayShow':false,
				'autoDimensions':false,
				'afterShow': function () {
					if(typeof AltasibFeedbackOnload_<?=$ALX?> != 'undefined')
						AltasibFeedbackOnload_<?=$ALX?>();
				},
				helpers: { overlay: null }
			}).trigger('click');
		});
	-->
	</script>
	<?endif;?>

<?else:?>

<script type="text/javascript">
<!--
<?if(is_array($arParams["PROPERTY_FIELDS"]) && in_array("PHONE", $arParams["PROPERTY_FIELDS"])):?>
$(function($){
	$('#PHONE_FID'+<?=$arParams["FORM_ID"]?>+'1').mask("9 (999) 999-99-99",{placeholder:""});
});
<?endif?>
<?if($arParams['ALX_CHECK_NAME_LINK']=='Y'):?>

$('#fb_close_<?=$ALX?>').click(function(){
	var formData = $(this.form).serializeArray();
	$.fancybox.hideActivity;

	$.ajax({
		url: window.location.href,
		type: 'POST',
		data: formData,
		async: false,
		cache: false,
		frameWidth: 300,
		success: function (returndata) {
			$.fancybox(returndata, {
				'width': 400,
				'autoDimensions':false,
				'afterShow': function () {
					<?if($arParams['LOCAL_REDIRECT_ENABLE'] == 'Y'):?>
					if(typeof AltasibFeedbackRedirect_<?=$ALX?> != 'undefined')
						AltasibFeedbackRedirect_<?=$ALX?>();
					<?endif?>
					if(typeof AltasibFeedbackOnload_<?=$ALX?> != 'undefined')
						AltasibFeedbackOnload_<?=$ALX?>();
				},
				helpers: { overlay: null }
			});
		}
	});
	return false;
});

<?endif;?>

<?if($arParams["REWIND_FORM"] == "Y" && ((count($arResult["FORM_ERRORS"]) > 0) || ($_REQUEST["success_".$ALX] == "yes"))):?>
$(document).ready(function(){
	document.location.hash = "alx_position_feedback";
});
<?endif?>
if (typeof ALX_ReloadCaptcha != 'function')
{
	function ALX_ReloadCaptcha(csid, ALX) {
		document.getElementById("alx_cm_CAPTCHA_"+ALX).src = '/bitrix/tools/captcha.php?captcha_sid='+csid+'&rnd='+Math.random();
	}
	function ALX_SetNameQuestion(obj, ALX) {
		var qw = obj.selectedIndex;
		document.getElementById("type_question_name_"+ALX).value = obj.options[qw].text;
	}
}
-->
</script>

<?$errorField = array();?>

<?foreach($arResult["FORM_ERRORS"] as $error):?>
	<?foreach($error as $k => $v):?>
		<?$errorField[] = $k;?>
	<?endforeach?>
<?endforeach?>
<?if($arParams["REWIND_FORM"] == "Y" && ((count($arResult["FORM_ERRORS"]) > 0) || ($arResult["success_".$ALX] == "yes"))):?>
	<a name="alx_position_feedback"></a>
<?endif?>

<div class="alx_feed_back_form alx_feed_back_default" id="alx_feed_back_default_<?=$ALX?>">
<?if(((count($arResult["FORM_ERRORS"]) == 0) && ($arResult["success_".$ALX] == "yes")) || ((count($arResult["FORM_ERRORS"]) == 0) && ($_REQUEST["success_".$ALX] == "yes"))):?>
	<div class="alx_feed_back_form_error_block">
		<table cellpadding="0" cellspacing="0" border="0" class="alx_feed_back_form_error_block_tbl">
		<tr>
			<td class="alx_feed_back_form_error_pic"><?=CFile::ShowImage($arParams["IMG_OK"])?></td>
			<td class="alx_feed_back_form_mess_ok_td_list">
				<div class="alx_feed_back_form_mess_ok"><?=$arParams["MESSAGE_OK"];?></div>
			</td>
		</tr>
		</table>
	</div>
	<script type="text/javascript">
	<!--
	function AltasibFeedbackRedirect_<?=$ALX?>(){
		<?if($arParams['LOCAL_REDIRECT_ENABLE'] == 'Y' && strlen($arParams['LOCAL_REDIRECT_URL']) > 0):?>
		document.location.href = '<?=(trim(htmlspecialcharsEx($arParams['LOCAL_REDIRECT_URL'])));?>';
		<?endif?>
	}
	-->
	</script>
<?endif?>
<?if($arParams["CHECK_ERROR"] == "Y"):?>
<?if(count($arResult["FORM_ERRORS"]) > 0):?>
	<div class="alx_feed_back_form_error_block">
		<table cellpadding="0" cellspacing="0" border="0" class="alx_feed_back_form_error_block_tbl">
		<tr>
			<td class="alx_feed_back_form_error_pic"><?=CFile::ShowImage($arParams["IMG_ERROR"])?></td>
			<td class="alx_feed_back_form_error_td_list">
			<div class="alx_feed_back_form_title_error"><?=GetMessage("ALX_TP_REQUIRED_ERROR")?></div>
				<ul class="alx_feed_back_form_error_list">
					<?foreach($arResult["FORM_ERRORS"] as $error):?>
						<?foreach($error as $v):?>
							<li><span>-</span> <?=$v?></li>
						<?endforeach?>
					<?endforeach?>
				</ul>
			</td>
		</tr>
		</table>
	</div>
<?endif?>
<?endif?>
<?
$hide = false;
if($arParams["HIDE_FORM"] == "Y" && ($_REQUEST["success_".$ALX] == "yes" || $arResult["success_".$ALX] == "yes"))
	$hide = true;

$actionPage = $APPLICATION->GetCurPage();
if(strpos($actionPage, "index.php") !== false)
	$actionPage = $APPLICATION->GetCurDir();
?>
<?if(!$hide):?>
<div class="alx_feed_back_form_feedback_poles">
<form id="f_feedback_<?=$ALX?>" name="f_feedback_<?=$ALX?>" action="<?=$actionPage?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="FEEDBACK_FORM_<?=$ALX?>" value="Y" />
	<?echo bitrix_sessid_post()?>
	<?if(count($arResult["TYPE_QUESTION"]) >= 1):?>
		<div class="alx_feed_back_form_item_pole">
			<div class="alx_feed_back_form_name"><?=$arParams["CATEGORY_SELECT_NAME"]?></div>
			<div class="alx_feed_back_form_inputtext_bg">
				<input type="hidden" id="type_question_name_<?=$ALX?>" name="type_question_name_<?=$ALX?>" value="<?=$arResult["TYPE_QUESTION"][0]["NAME"]?>">
				<select id="type_question_<?=$ALX?>" name="type_question_<?=$ALX?>" onchange="ALX_SetNameQuestion(this, '<?=$ALX?>');">
					<?foreach($arResult["TYPE_QUESTION"] as $arField):?>
						<?if(trim(htmlspecialcharsEx($_POST["type_question"])) == $arField["ID"]):?>
							<option value="<?=$arField["ID"]?>" selected><?=$arField["NAME"]?></option>
						<?else:?>
							<option value="<?=$arField["ID"]?>"><?=$arField["NAME"]?></option>
						<?endif?>
					<?endforeach?>
				</select>
			</div>
		</div>
	<?endif?>
	<?$k = 0;?>
	<?foreach($arResult["FIELDS"] as $arField):?>

		<div class="alx_feed_back_form_item_pole">
			<div class="alx_feed_back_form_name">
				<?=$arField["NAME"]?> <?if($arField["REQUIRED"]):?><span class="alx_feed_back_form_required_text">*</span><?endif?>
				<div class="alx_feed_back_form_hint"><?=$arField["HINT"]?></div>
			</div>
			<?/*LIST*/?>
		<?if($arField["TYPE"] == "L"):?>
			<?if($arField["LIST_TYPE"] == "L"):?>
				<div class="alx_feed_back_form_inputtext_bg">
				<?if($arField["MULTIPLE"] == "Y"):?>
					<select name="FIELDS[<?=$arField["CODE"]?>][]" multiple="multiple">
				<?else:?>
					<select name="FIELDS[<?=$arField["CODE"]?>]">
				<?endif;?>
				<?foreach($arField["ENUM"] as $v):?>
					<?if(!isset($_POST["FIELDS"][$arField["CODE"]]) && !isset($arResult["FORM_ERRORS"]["EMPTY_FIELD"][$arField["CODE"]])):?>
						<option value="<?=$v["ID"]?>" <?if($v['DEF'] == 'Y') echo 'selected="selected"';?> ><?=$v["VALUE"]?></option>
					<?else:?>
						<?if($arField["MULTIPLE"] == "Y"):?>
							<option value="<?=$v["ID"]?>" <?if(in_array($v['ID'], $_POST["FIELDS"][$arField["CODE"]])) echo 'selected="selected"';?> ><?=$v["VALUE"]?></option>
						<?else:?>
							<option value="<?=$v["ID"]?>" <?if($v['ID'] == $_POST["FIELDS"][$arField["CODE"]]) echo 'selected="selected"';?> ><?=$v["VALUE"]?></option>
						<?endif;?>
					<?endif;?>
				<?endforeach?>
					</select>
				</div>
				<?elseif($arField["LIST_TYPE"] == "C"):
					if($arField["MULTIPLE"] == "Y"):
						?><input type="hidden" name="FIELDS[<?=$arField["CODE"]?>]" value=""><?
						foreach($arField["ENUM"] as $v):
							if(!isset($_POST["FIELDS"][$arField["CODE"]]) && !isset($arResult["FORM_ERRORS"]["EMPTY_FIELD"][$arField["CODE"]])):?>
								<input id="<?=$v["ID"]?>" type="checkbox" name="FIELDS[<?=$arField["CODE"]?>][]" value="<?=$v["ID"]?>" <?if($v["DEF"] == "Y") echo 'checked="checked"';?>><label for="<?=$v["ID"]?>"><?=$v["VALUE"]?></label><br/>
							<?else:?>
								<input id="<?=$v["ID"]?>" type="checkbox" name="FIELDS[<?=$arField["CODE"]?>][]" value="<?=$v["ID"]?>" <?if(in_array($v['ID'], $_POST["FIELDS"][$arField["CODE"]])) echo 'checked="checked"';?>><label for="<?=$v["ID"]?>"><?=$v["VALUE"]?></label><br/>
							<?endif;
						endforeach;
					else:
						foreach($arField["ENUM"] as $v):
							if(!isset($_POST["FIELDS"][$arField["CODE"]]) && !isset($arResult["FORM_ERRORS"]["EMPTY_FIELD"][$arField["CODE"]])):?>
								<input id="<?=$v["ID"]?>" type="radio" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=$v["ID"]?>" <?if($v['DEF'] == 'Y') echo 'checked="checked"';?>><label for="<?=$v["ID"]?>"><?=$v["VALUE"]?></label><br />
							<?else:?>
								<input id="<?=$v["ID"]?>" type="radio" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=$v["ID"]?>" <?if($v['ID'] == $_POST["FIELDS"][$arField["CODE"]]) echo 'checked="checked"';?>><label for="<?=$v["ID"]?>"><?=$v["VALUE"]?></label><br />
							<?endif;
						endforeach;
					endif;
				endif;
			/*HTML/TEXT*/
		elseif($arField["USER_TYPE"] == "HTML"):?>
			<div class="alx_feed_back_form_inputtext_bg" id="error_<?=$arField["CODE"]?>">
				<?if(!empty($_POST["FIELDS"][$arField["CODE"]])):?>
					<textarea cols="" rows="" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" style="height:<?=$arField["USER_TYPE_SETTINGS"]["height"]?>px;"><?=trim(htmlspecialcharsEx($_POST["FIELDS"][$arField["CODE"]]))?></textarea>
				<?elseif(!empty($arField["AUTOCOMPLETE_VALUE"])):?>
					<textarea cols="" rows="" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" style="height:<?=$arField["USER_TYPE_SETTINGS"]["height"]?>px;"><?=trim(htmlspecialcharsEx($arField["AUTOCOMPLETE_VALUE"]))?></textarea>
				<?else:?>
					<textarea cols="" rows="" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" style="height:<?=$arField["USER_TYPE_SETTINGS"]["height"]?>px;" onblur="if(this.value==''){this.value='<?=$arField["DEFAULT_VALUE"]["TEXT"]?>'}" onclick="if(this.value=='<?=$arField["DEFAULT_VALUE"]["TEXT"]?>'){this.value=''}"><?=$arField["DEFAULT_VALUE"]["TEXT"]?></textarea>
				<?endif;?>
			</div>
		<?/*DATE*/?>
		<?elseif($arField["USER_TYPE"] == "DateTime"):?>
			<div class="alx_feed_back_form_inputtext_bg alx_feed_back_form_inputtext_bg_calendar" id="error_<?=$arField["CODE"]?>">
				<?if(!empty($_POST["FIELDS"][$arField["CODE"]])):?>
					<input type="text" size="40" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=trim(htmlspecialcharsEx($_POST["FIELDS"][$arField["CODE"]]))?>" class="alx_feed_back_form_inputtext" readonly="readonly" onclick="BX.calendar({node:this, field:'FIELDS[<?=$arField["CODE"]?>]', form: '', bTime: false, currentTime: '<?=(time()+date("Z")+CTimeZone::GetOffset())?>', bHideTime: false});" />
				<?else:?>
					<input type="text" size="40" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=$arField["DEFAULT_VALUE"]?>" class="alx_feed_back_form_inputtext" readonly="readonly" onclick="BX.calendar({node:this, field:'FIELDS[<?=$arField["CODE"]?>]', form: '', bTime: false, currentTime: '<?=(time()+date("Z")+CTimeZone::GetOffset())?>', bHideTime: false});" />
				<?endif;?>
				<div class="alx_feed_back_form_calendar_icon">
					<?
					require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
					define("ADMIN_THEME_ID", CAdminTheme::GetCurrentTheme());
					echo CAdminPage::ShowScript();
					echo Calendar("FIELDS[".$arField["CODE"]."]", "f_feedback_".$ALX);
					?>
				</div>
			</div>
			<?/*ELEMENTS*/?>
		<?elseif($arField["TYPE"] == "E"):?>
		<div class="alx_feed_back_form_element_bg" id="error_<?=$arField["CODE"]?>">
<?			if($arField["PROPERTY"]["MULTIPLE"] == "Y"):?>
<?				foreach($arField["LINKED_ELEMENTS"] as $arEl):?>
					<p class="alx_feed_back_form_checkbox">
						<input type="checkbox" name="FIELDS[<?=$arField["CODE"]?>][]" value="<?=$arEl["ID"]?>" id="<?=$arField["CODE"]?>1_<?=$arEl["ID"]?>" <?
							if(!empty($_POST["FIELDS"][$arField["CODE"]]) && in_array($arEl["ID"], $_POST["FIELDS"][$arField["CODE"]])):?>checked="checked"<?endif;?>/>
						<label for="<?=$arField["CODE"]?>1_<?=$arEl["ID"]?>"><?=$arEl["NAME"]?></label>
					</p>
<?				endforeach;
			endif;?>
		</div>
			<?/*STRING*/?>
		<?elseif($arField["TYPE"] != "F"):?>
			<div class="alx_feed_back_form_inputtext_bg" id="error_<?=$arField["CODE"]?>">
				<?if(!empty($_POST["FIELDS"][$arField["CODE"]])):?>
					<input type="text" size="40" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=trim(htmlspecialcharsEx($_POST["FIELDS"][$arField["CODE"]]))?>" class="alx_feed_back_form_inputtext" />
				<?elseif(!empty($arField["AUTOCOMPLETE_VALUE"])):?>
					<input type="text" size="40" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=trim(htmlspecialcharsEx($arField["AUTOCOMPLETE_VALUE"]))?>" class="alx_feed_back_form_inputtext" />
				<?else:?>
					<input type="text" size="40" id="<?=$arField["CODE"]?>1" name="FIELDS[<?=$arField["CODE"]?>]" value="<?=$arField["DEFAULT_VALUE"]?>" class="alx_feed_back_form_inputtext" onblur="if(this.value==''){this.value='<?=$arField["DEFAULT_VALUE"]?>'}" onclick="if(this.value=='<?=$arField["DEFAULT_VALUE"]?>'){this.value=''}" />
				<?endif;?>
			</div>
			<?/*FILE*/?>
		<?elseif($arField["TYPE"] == "F"):?>
			<input type="hidden" id="codeFileFields" name="codeFileFields[<?=$arField['CODE']?>]" value="<?=$arField['CODE']?>">
				<div class="alx_feed_back_form_inputtext_bg_file">
					<input type="hidden" name="FIELDS[myFile][<?=$arField["CODE"]?>]">
<?			if($arField["MULTIPLE"] == "Y"):?>
					<input type="file" id="alx_feed_back_form_file_input_add<?=$k++?>" name="myFile[<?=$arField['CODE']?>][]" class="alx_feed_back_form_file_input_add" size="<?=$arParams["WIDTH_FORM"]?>" multiple="true" ><br/>
<?			else:?>
					<input type="file" id="alx_feed_back_form_file_input_add<?=$k++?>" name="myFile[<?=$arField['CODE']?>]" class="alx_feed_back_form_file_input_add" size="<?=$arParams["WIDTH_FORM"]?>" />
<?			endif;?>
				</div>

<?endif?>
		</div>
	<?endforeach?>

	<? if(is_array($arParams["PROPERTY_FIELDS"])):?>
		<?if(in_array("FEEDBACK_TEXT", $arParams["PROPERTY_FIELDS"])):?>
		<div class="alx_feed_back_form_item_pole">
			<?if(in_array("FEEDBACK_TEXT_".$ALX, $errorField, true)):?><div class="alx_feed_back_form_item_pole alx_feed_back_form_error_pole"><?endif?>
			<div class="alx_feed_back_form_name"><?=GetMessage("ALX_TP_MESSAGE_TEXTMESS")?> <?if(in_array("FEEDBACK_TEXT_".$ALX, $arParams["PROPERTY_FIELDS_REQUIRED"])):?><span class="alx_feed_back_form_required_text">*</span><?endif?></div>
			<div class="alx_feed_back_form_inputtext_bg" id="error_EMPTY_TEXT"><textarea cols="" rows="" id="EMPTY_TEXT1" name="FEEDBACK_TEXT_<?=$ALX?>"><?=$arResult["FEEDBACK_TEXT"]?></textarea></div>
			<?if(in_array("FEEDBACK_TEXT_".$ALX, $errorField, true)):?></div><?endif?>
		</div>
		<?endif?>
	<?endif?>
	<?if($arParams["USE_CAPTCHA"]):?>
		<?if($arParams["CAPTCHA_TYPE"] != 'recaptcha'):?>
				<div class="alx_feed_back_form_item_pole">
				<?if(in_array("ALX_CP_WRONG_CAPTCHA", $errorField, true)):?><div class="alx_feed_back_form_item_pole alx_feed_back_form_error_pole"><?endif?>
					<div class="alx_feed_back_form_name"><?=GetMessage("ALX_TP_MESSAGE_INPUTF")?> <?=GetMessage("ALX_TP_MESSAGE_INPUTS")?> <span class="alx_feed_back_form_required_text">*</span></div>

			<? if($fVerComposite) $frame = $this->createFrame()->begin('loading... <img src="/bitrix/themes/.default/start_menu/main/loading.gif">');?>
						<?$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();?>
					<input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsEx($capCode)?>">
						<div><img id="alx_cm_CAPTCHA_<?=$ALX?>" src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsEx($capCode)?>" width="180" height="40"></div>
						<div style="margin-bottom:6px;"><small><a href="#" onclick="capCode='<?=htmlspecialcharsEx($capCode)?>'; ALX_ReloadCaptcha(capCode, '<?=$ALX?>'); return false;"><?=GetMessage("ALX_TP_RELOADIMG")?></a></small></div>
			<? if($fVerComposite) $frame->end();?>

						<div class="alx_feed_back_form_inputtext_bg"><input type="text" class="alx_feed_back_form_inputtext" id="captcha_word1" name="captcha_word" size="30" maxlength="50" value=""></div>
				<?if(in_array("ALX_CP_WRONG_CAPTCHA", $errorField, true)):?></div><?endif?>

				</div>
		<?else:?>
			<?if (isset($arResult["SITE_KEY"])):?>
				<div class="alx_feed_back_form_item_pole">
				<?if(in_array("ALX_CP_WRONG_CAPTCHA", $errorField, true)):?><div class="alx_feed_back_form_item_pole alx_feed_back_form_error_pole"><?endif?>
					<div class="alx_feed_back_form_name"><?=GetMessage("ALX_TP_MESSAGE_RECAPTCHA")?><span class="alx_feed_back_form_required_text">*</span></div>

			<? if($fVerComposite) $frame2 = $this->createFrame()->begin('loading... <img src="/bitrix/themes/.default/start_menu/main/loading.gif">');?>
					<script type="text/javascript">
					var AltasibFeedbackOnload_<?=$ALX?> = function() {
						grecaptcha.render('html_element_recaptcha', {'sitekey' : '<?=$arResult["SITE_KEY"];?>',
							'theme' : '<?=$arParams["RECAPTCHA_THEME"];?>', 'type' : '<?=$arParams["RECAPTCHA_TYPE"];?>' });
					};
					<?if($arParams['ALX_CHECK_NAME_LINK']=='Y'):?>
					$(window).load(function () {
						if(typeof AltasibFeedbackOnload_<?=$ALX?> != 'undefined')
							AltasibFeedbackOnload_<?=$ALX?>();
					});
					<?endif?>
					<?if($arParams['AJAX_MODE']=='Y'):?>
					var AltasibFeedbackOnAjaxSuccess = function(data, config) {
						if(typeof AltasibFeedbackOnload_<?=$ALX?> != 'undefined')
							AltasibFeedbackOnload_<?=$ALX?>();
						top.BX.removeCustomEvent(window, 'onAjaxSuccess', AltasibFeedbackOnAjaxSuccess);
					};
					top.BX.addCustomEvent(window, "onAjaxSuccess", AltasibFeedbackOnAjaxSuccess);
					<?endif?>
					</script>
					<div class="g-recaptcha" id="html_element_recaptcha" onload="AltasibFeedbackOnload_<?=$ALX?>()" data-sitekey="<?=$arResult["SITE_KEY"]?>"></div>

			<? if($fVerComposite) $frame2->end();?>

				<?if(in_array("ALX_CP_WRONG_CAPTCHA", $errorField, true)):?></div><?endif?>

				</div>
			<?endif;?>
		<?endif;?>
	<?endif?>
	<div class="alx_feed_back_form_submit_block">
		<input type="submit" class="fb_close" id="fb_close_<?=$ALX?>" name="SEND_FORM_<?=$ALX?>" value="<?=GetMessage('ALX_TP_MESSAGE_SUBMIT')?>" />
	</div>
</form>
</div>
<?endif?>
</div>
<style type="text/css">
#alx_feed_back_default_<?=$ALX?>
{
	width: <?=str_replace(" ", "", $arParams["WIDTH_FORM"])?> !important;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_error_block
{
	background-color:<?=str_replace(" ", "", $arParams["BACKCOLOR_ERROR"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_error_list
{
	color:<?=str_replace(" ", "", $arParams["COLOR_ERROR"])?>;
	font-size:<?=str_replace(" ", "", $arParams["SIZE_INPUT"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_title_error
{
	color:<?=str_replace(" ", "", $arParams["COLOR_ERROR_TITLE"])?>;
	font-size:<?=str_replace(" ", "", $arParams["SIZE_NAME"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_mess_ok
{
	font-size:<?=str_replace(" ", "", $arParams["SIZE_NAME"])?>;
	color:<?=str_replace(" ", "", $arParams["COLOR_MESS_OK"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_name
{
	font-size:<?=str_replace(" ", "", $arParams["SIZE_NAME"])?>;
	color:<?=str_replace(" ", "", $arParams["COLOR_NAME"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_hint
{
	font-size:<?=str_replace(" ", "", $arParams["SIZE_HINT"])?>;
	color:<?=str_replace(" ", "", $arParams["COLOR_HINT"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_inputtext_bg input,
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_inputtext_bg textarea,
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_inputtext_bg select,
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_filename,
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_file_button_bg
{
	font-size:<?=str_replace(" ", "", $arParams["SIZE_INPUT"])?>;
	color:<?=str_replace(" ", "", $arParams["COLOR_INPUT"])?>;
	font-family:tahoma;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_file_input_add
{
	font-size:<?=str_replace(" ", "", $arParams["SIZE_INPUT"])?> !important;
	color:<?=str_replace(" ", "", $arParams["COLOR_INPUT"])?>;
}
#alx_feed_back_default_<?=$ALX?> .alx_feed_back_form_feedback_poles .alx_feed_back_form_required_text
{
	color:red;
}
</style>

<?endif;?>

<?if($arParams['ALX_CHECK_NAME_LINK']=='Y'):?>
<script type="text/javascript">
$(document).ready(function(){var a;$("a").click(function(){"alx_feedback_popup"==$(this).attr("class")&&(a=$(this).attr("id").split("_")[2]);$(".alx_feedback_popup").fancybox({ajax:{type:"POST",data:"OPEN_POPUP_"+a+"=Y"},titleShow:!1,type:"ajax",href:"",afterShow:function(){"undefined"!=typeof AltasibFeedbackOnload_<?=$ALX?>&&AltasibFeedbackOnload_<?=$ALX?>()},overlayShow:!1,autoDimensions:!1,helpers:{overlay:null}})})});
</script>
<?endif?>