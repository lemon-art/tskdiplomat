<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
if (count($arResult['ITEMS']) < 1)
	return;
	
?>
<div id="listpage">
<ul data-role="listview" data-inset="true">
<?
foreach ($arResult['ITEMS'] as $key => $arElement):
	$bHasPicture = is_array($arElement['PREVIEW_IMG']);
	$sticker = "";
	if (array_key_exists("PROPERTIES", $arElement) && is_array($arElement["PROPERTIES"]))
	{
		foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
			if (array_key_exists($propertyCode, $arElement["PROPERTIES"]) && intval($arElement["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
				$sticker .= "&nbsp;<span class=\"sticker\">".$arElement["PROPERTIES"][$propertyCode]["NAME"]."</span>";
	}

?>
	<li>
		<?if($bHasPicture):?>
				<img src="<?=$arElement["PREVIEW_IMG"]["SRC"]?>" />
		<?endif;?>

			<h3><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a><?=$sticker?></h3>
			<p><?=$arElement['PREVIEW_TEXT']?></p>

			<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])):?>
				<?
					$price_from = '';
					if($arElement['PROPERTIES']['MAXIMUM_PRICE']['VALUE'] > $arElement['PROPERTIES']['MINIMUM_PRICE']['VALUE'])
					{
						$price_from = GetMessage("CR_PRICE_OT");	
					}
				?>
				<?CModule::IncludeModule("sale")?>
				<p><span class="catalog-item-price"><?=$price_from?><?=FormatCurrency($arElement['PROPERTIES']['MINIMUM_PRICE']['VALUE'], CSaleLang::GetLangCurrency(SITE_ID))?></span></p>
			<?else:?>
			<?foreach($arElement["PRICES"] as $code=>$arPrice):
				if($arPrice["CAN_ACCESS"]):
?>
				<p>
				<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
					<span class="catalog-item-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span> <s><?=$arPrice["PRINT_VALUE"]?></s>
				<?else:?>
					<span class="catalog-item-price"><?=$arPrice["PRINT_VALUE"]?></span>
				<?endif;?>
				</p>
			<?
				endif;
			endforeach;
			?>
			<?endif;?>
	</li>
<?endforeach;?>
</ul>
</div>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"];?>
<?endif;?>