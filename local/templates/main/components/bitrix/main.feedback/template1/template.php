<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<div class="mfeedback">
<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}
if(strlen($arResult["OK_MESSAGE"]) > 0)
{
	?>
<script>
$(document).ready(function() {
	yaCounter22073026.reachGoal('order_communication');
	ga('send', 'event', 'order_feedback', 'communication', 'Обратная связь');
});
</script>	
	
	
	<div class="mf-ok-text"><?=$arResult["OK_MESSAGE"]?></div><?
}
?>

<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="contactForm">
    <fieldset>
        <div class="fieldset">
        <?=bitrix_sessid_post()?>
        <ul class="form-list">
            <li class="fields">
        	<div class="field">
                    <label for="user_name" class="required">
                       	<?=GetMessage("MFT_NAME")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])):?><em>*</em><?endif?>
                    </label>
                    <div class="input-box">
                        <input type="text" name="user_name" value="<?=$arResult["AUTHOR_NAME"]?>" class="input-text required-entry">
                    </div>
                </div>
        	<div class="field">
                    <label for="user_email" class="required">
                       	<?=GetMessage("MFT_EMAIL")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])):?><em>*</em><?endif?>
                    </label>
                    <div class="input-box">
                        <input type="text" name="user_email" value="<?=$arResult["AUTHOR_EMAIL"]?>" class="input-text required-entry">
                    </div>
                </div>
            </li>
            <li class="wide">
                <label for="MESSAGE" class="required">
                    <?=GetMessage("MFT_MESSAGE")?>
                    <?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"])):?><em>*</em><?endif?>
                </label>
                <div class="input-box">
                    <textarea name="MESSAGE" id="MESSAGE" title="Comment" class="required-entry input-text" cols="5" rows="3"><?=$arResult["MESSAGE"]?></textarea>
                </div>
            </li>

	<?if($arParams["USE_CAPTCHA"] == "Y"):?>
            <li class="wide" >
            <div class="mf-captcha">
		<div class="mf-text"><?=GetMessage("MFT_CAPTCHA")?></div>
		<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
		<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
		<div class="mf-text"><?=GetMessage("MFT_CAPTCHA_CODE")?><span class="mf-req">*</span></div>
		<input type="text" name="captcha_word" size="30" maxlength="50" value="">
            </div>
            </li>
	<?endif;?>
        </ul>
        </div>    
	<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
        <div class="buttons-set">
            <button type="submit" name="submit" class="button">
                <span>
                    <span>
                        <?=GetMessage("MFT_SUBMIT")?>
                    </span>
                </span>
        </div>
    </fieldset>
</form>
</div>