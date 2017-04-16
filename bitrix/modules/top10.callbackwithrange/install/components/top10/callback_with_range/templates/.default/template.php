<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<a class="top10_popup-with-form" href="#top10_feedback"><?=$arParams["BTN_CALLBACK"];?></a>

<div
	id="top10_feedback"
	class="top10_black-popup-block mfp-hide"
	data-time-min="<?=$arResult["TIME_MIN"];?>"
	data-time-max="<?=$arResult["TIME_MAX"];?>"
	data-time-step="<?=$arParams["TIME_STEP"];?>">

	<header><?=$arParams["TOP10_CALLBACK_FORM"];?></header>
	<section class="top10_left">
		<?if($arParams["FOR_CALL"]):?>
			<strong><?=$arParams["FOR_CALL"];?></strong>
		<?endif;?>
		
		<div class="top10_phone"><?=$arParams["PHONE"];?></div>
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include",
			"",
			Array(
				"AREA_FILE_SHOW"		=> "sect",
				"AREA_FILE_SUFFIX"		=> "top10_callback_plus",
				"AREA_FILE_RECURSIVE"	=> "Y",
				"EDIT_TEMPLATE"			=> ""
			), $component, array("HIDE_ICONS" => "Y")
		);?>
	</section>
	<section class="top10_right">
		<?
			$actionPage = $APPLICATION->GetCurPage();
			if(strpos($actionPage, "index.php") !== false) $actionPage = $APPLICATION->GetCurDir();
		?>

		<?if($arParams["KEEP_PHONE"]):?>
			<strong><?=$arParams["KEEP_PHONE"];?></strong>
		<?endif;?>
		
		<div class="top10_phone-input">
			<input type="text"
				name="PHONE"
				title="<?=$arParams["TOP10_INPUT_PHONE"];?>"
				maxlength="30"
				form="top10_feedback-form"
				placeholder="<?=$arParams["PHONE_FIELD"];?>"
				<?if($arParams["PHONE_CHECK"]):?>
				pattern="<?=$arParams["PHONE_CHECK"]?>"
				<?endif;?>
				required="" />
		</div>
		
		<div class="top10_phone-input">
			<input type="text"
				name="NAME"
				title="<?=$arParams["TOP10_INPUT_NAME"];?>"
				maxlength="30"
				form="top10_feedback-form"
				placeholder="<?=$arParams["TOP10_NAME_FIELD"];?>"
				<?if($arParams["TOP10_NAME_CHECK"]):?>
				pattern="<?=$arParams["TOP10_NAME_CHECK"]?>"
				<?endif;?>
				required="" />
		</div>
		
		<p><?=$arParams["TOP10_CALLBACK_WHEN"];?></p>
		<div id="top10_slider-time-range"></div>
		<input form="top10_feedback-form" type="hidden" name="TIME_MIN" id="top10_slider-time-range-min" value="<?=$arResult["TIME_START"];?>" />
		<input form="top10_feedback-form" type="hidden" name="TIME_MAX" id="top10_slider-time-range-max" value="<?=$arResult["TIME_FINISH"];?>" />

		<p><?=$arParams["TOP10_CALLBACK_FREE"];?></p>
		<div class="top10_button-call">
			<input form="top10_feedback-form" type="hidden" name="AJAX" value="" />
			<input form="top10_feedback-form" type="hidden" name="ASP-TR" value="" />
			<input form="top10_feedback-form" type="submit" class="top10_submit" name="TOP10_SUBMIT" value="<?=$arParams["TOP10_SUBMIT_VALUE"];?>"/>
		</div>
	</section>
</div>

<form id="top10_feedback-form" action="<?=$actionPage;?>" method="POST">
	<?=bitrix_sessid_post();?>
</form>

<a class="top10_popup-success" href="#top10_feedback-success"></a>
<div
	id="top10_feedback-success"
	class="top10_black-popup-block-success mfp-hide">

	<header><?=$arParams["TOP10_CALLBACK_FORM"];?></header>
	<section><?=$arParams["SUCCESS_MSG"];?></section>
</div>