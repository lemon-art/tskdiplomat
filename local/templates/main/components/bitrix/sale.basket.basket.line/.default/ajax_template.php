<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$this->setFrameMode(true);

if (!function_exists("sbbl_small_cart"))
{
	function sbbl_small_cart($arResult, $arParams)
	{
		?>
		<div class="bx_small_cart">

			<span class="icon_cart"></span>

			<?if ($arResult['NUM_PRODUCTS'] > 0 && $arParams['SHOW_NUM_PRODUCTS'] == 'N' && $arParams['SHOW_TOTAL_PRICE'] == 'N'):?>
				<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=GetMessage('TSB1_CART')?></a>
			<?else: echo GetMessage('TSB1_CART'); endif?>

			<?if($arParams['SHOW_NUM_PRODUCTS'] == 'Y'):?>
				<?if ($arResult['NUM_PRODUCTS'] > 0):?>
					<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=$arResult['NUM_PRODUCTS'].' '.$arResult['PRODUCT(S)']?></a>
				<?else:?>
					<?=$arResult['NUM_PRODUCTS'].' '.$arResult['PRODUCT(S)']?>
				<?endif?>
			<?endif?>

			<?if($arParams['SHOW_TOTAL_PRICE'] == 'Y'):?>
				<br>
				<span class="icon_spacer"></span> <?=GetMessage('TSB1_TOTAL_PRICE')?>
				<?if ($arResult['NUM_PRODUCTS'] > 0):?>
					<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=$arResult['TOTAL_PRICE']?></a>
				<?else:?>
					<?=$arResult['TOTAL_PRICE']?>
				<?endif?>
			<?endif?>


			<?if($arParams["SHOW_PERSONAL_LINK"] == "Y"):?>
				<br>
				<span class="icon_profile"></span>
				<a class="link_profile" href="<?=$arParams["PATH_TO_PERSONAL"]?>"><?=GetMessage("TSB1_PERSONAL")?></a>
			<?endif?>

			<?if ($arParams["SHOW_PRODUCTS"] == "Y" && $arResult['NUM_PRODUCTS'] > 0):?>
				<div class="bx_item_hr" style="margin-bottom:0"></div>
			<?endif?>

		</div>
		<?
	}
}

?>

<?if ($arParams["EMBED_MINICART"] == "Y"):?>

	<div class="bx_cart_top_inline">
		<span class="bx_cart_top_inline_icon"></span>
		<?if ($arResult['NUM_PRODUCTS'] > 0):?>
			<a class="bx_cart_top_inline_link" href="<?=$arParams['PATH_TO_BASKET']?>">
				<?=GetMessage('TSB1_CART')?>
				<?if($arParams['SHOW_NUM_PRODUCTS'] == 'Y'):?>
					<strong>(<?=$arResult['NUM_PRODUCTS']?>)</strong>
				<?endif?>
			</a>
			<?if($arParams['SHOW_TOTAL_PRICE'] == 'Y'):?>
				<br><?=GetMessage('TSB1_TOTAL_PRICE')?> <?=$arResult['TOTAL_PRICE']?>
			<?endif?>
		<?else:?>
			<span class="bx_cart_top_inline_link"><?=GetMessage('TSB1_CART')?></span>
		<?endif?>
	</div>

<?elseif ($arParams["SHOW_PRODUCTS"] == "N"):?>

	<?sbbl_small_cart($arResult, $arParams)?>

<?else:?>

	<?if ($arParams["POSITION_FIXED"] == "Y")
		sbbl_small_cart($arResult, $arParams);
	?>

	<div class="bx_item_listincart">

		<?if ($arParams["DISPLAY_COLLAPSE"] == "Y" && $arParams["POSITION_FIXED"] == "Y" && $arResult["CATEGORIES"]):?>
			<div id="bx_cart_block_status" class="status" onclick="sbbl.toggleExpandCollapseCart()"><?=GetMessage("TSB1_EXPAND")?></div>
		<?endif?>


		<?if ($arParams["POSITION_FIXED"] == "N"):?>
			<h3 class="bx_title_component">
				<?= $arResult["CATEGORIES"] ? GetMessage("TSB1_YOUR_CART") : $arResult["ERROR_MESSAGE"] ?>
				<span class="cart_icon_big"></span>
			</h3>
		<?endif?>

		<?foreach ($arResult["CATEGORIES"] as $category => $items):
			if (empty($items))
				continue;
			?>
			<div class="bx_item_status"><?=GetMessage("TSB1_$category")?></div>
			<?foreach ($items as $v):?>
				<div class="bx_itemincart">
					<div class="bx_item_delete" onclick="sbbl.removeItemFromCart(<?=$v['ID']?>)" title="<?=GetMessage("TSB1_DELETE")?>"></div>
					<?if ($arParams["SHOW_IMAGE"] == "Y"):?>
						<div class="bx_item_img_container">
							<?if ($v["PICTURE_SRC"]):?>
								<?if($v["DETAIL_PAGE_URL"]):?>
									<a href="<?=$v["DETAIL_PAGE_URL"]?>"><img src="<?=$v["PICTURE_SRC"]?>" alt="<?=$v["NAME"]?>"></a>
								<?else:?>
									<img src="<?=$v["PICTURE_SRC"]?>" alt="<?=$v["NAME"]?>" />
								<?endif?>
							<?endif?>
						</div>
					<?endif?>
					<div class="bx_item_title">
						<?if ($v["DETAIL_PAGE_URL"]):?>
							<a href="<?=$v["DETAIL_PAGE_URL"]?>"><?=$v["NAME"]?></a>
						<?else:?>
							<?=$v["NAME"]?>
						<?endif?>
					</div>
					<?if (true):/*$category != "SUBSCRIBE") TODO */?>
						<?if ($arParams["SHOW_PRICE"] == "Y"):?>
							<div class="bx_item_price">
								<strong><?=$v["PRICE_FMT"]?></strong>
								<?if ($v["FULL_PRICE"] != $v["PRICE_FMT"]):?>
									<span class="bx_item_oldprice"><?=$v["FULL_PRICE"]?></span>
								<?endif?>
							</div>
						<?endif?>
						<?if ($arParams["SHOW_SUMMARY"] == "Y"):?>
							<div class="bx_item_col_summ">
								<strong><?=$v["QUANTITY"]?></strong> <?=$v["MEASURE_NAME"]?> <?=GetMessage("TSB1_SUM")?>
								<strong><?=$v["SUM"]?></strong>
							</div>
						<?endif?>
					<?endif?>
				</div>
			<?endforeach?>
		<?endforeach?>


		<?if ($arParams["POSITION_FIXED"] == "N")
			sbbl_small_cart($arResult, $arParams);
		?>

		<?if($arParams["PATH_TO_ORDER"] && $arResult["CATEGORIES"]["READY"]):?>
			<div class="bx_button_container">
				<a href="<?=$arParams["PATH_TO_ORDER"]?>" class="bx_bt_button_type_2 bx_medium"><?=GetMessage("TSB1_2ORDER")?></a>
			</div>
		<?endif?>

	</div>

<?endif?>