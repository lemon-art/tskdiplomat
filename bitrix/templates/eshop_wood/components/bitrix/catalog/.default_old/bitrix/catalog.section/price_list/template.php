<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if (!empty($arResult["SECTION_USER_FIELDS"]["UF_BROWSER_TITLE"]))
	$APPLICATION->SetTitle($arResult["SECTION_USER_FIELDS"]["UF_BROWSER_TITLE"]);
else
	$APPLICATION->SetTitle($arResult["NAME"]);

if (!empty($arResult["SECTION_USER_FIELDS"]["UF_TITLE_H1"]))
	$APPLICATION->SetPageProperty("ADDITIONAL_TITLE", $arResult["SECTION_USER_FIELDS"]["UF_TITLE_H1"]);
else
	$APPLICATION->SetPageProperty("ADDITIONAL_TITLE", $arResult["NAME"]);?>
<?
$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = unserialize($notifyOption);
?>
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>
<table class="equipment"  rules="rows" style="width:726px">
	<thead>
	<tr>
		<td><?=GetMessage("CATALOG_ELEMENT_NAME")?></td>
		<td><?=GetMessage("CATALOG_ELEMENT_PRICE")?></td>
		<td></td>
		<?if($arParams["DISPLAY_COMPARE"]):?>
		<td></td>
		<?endif?>
	</tr>
	</thead>
	<tbody>
    <?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
		<tr class="R2D2" id="<?=$this->GetEditAreaId($arElement['ID']);?>">
			<?
			$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

			$sticker = "";
			if (array_key_exists("PROPERTIES", $arElement) && is_array($arElement["PROPERTIES"]))
			{
				foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
					if (array_key_exists($propertyCode, $arElement["PROPERTIES"]) && intval($arElement["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
					{
						$sticker .= "<sup class='".ToLower($propertyCode)."'>".$arElement["PROPERTIES"][$propertyCode]["NAME"]."</sup>";
						break;
					}
			}
			?>
			<td>
				<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" title="<?=$arElement["NAME"]?>" class="item_title"><?=$arElement["NAME"]?></a>
				<?if(!(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])) && !$arElement["CAN_BUY"]):?>
					<sup class="notavailable"><?=GetMessage("CATALOG_NOT_AVAILABLE2")?></sup>
				<?elseif (strlen($sticker)>0):?>
					<?=$sticker?>
				<?endif?>
			</td>
			<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"]))  // Product has offers
			{
            ?>
				<td>
            <?
				if ($arElement["MIN_PRODUCT_OFFER_PRICE"] > 0):?>
					<span class="item_price price"><?
					if (count($arElement["OFFERS"]) > 1) echo GetMessage("CATALOG_PRICE_FROM");
					echo $arElement["MIN_PRODUCT_OFFER_PRICE_PRINT"];
					?></span>
				<?endif;
            ?>
				</td>
				<td>
					<a href="javascript:void(0)" id="catalog_add2cart_offer_link_<?=$arElement['ID']?>" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arElement["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arElement["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'cart');"><span></span><?echo GetMessage("CATALOG_BUY")?></a>
				</td>
                <?
			}
			else  // Product doesn't have offers
			{
            ?>
			<td>
			<?if ($arElement["MIN_PRODUCT_DISCOUNT_PRICE"] < $arElement["MIN_PRODUCT_PRICE"] && $arElement["MIN_PRODUCT_PRICE"] > 0 && $arElement["MIN_PRODUCT_DISCOUNT_PRICE"] > 0):?>
				<span class="discount-price"><?=$arElement["MIN_PRODUCT_DISCOUNT_PRICE_PRINT"];?></span>
				<span class="old-price"><?=$arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
			<?elseif ($arElement["MIN_PRODUCT_PRICE"] > 0):?>
				<span class="price"><?=$arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
			<?endif?>
			</td>
			<td>
			<?if($arElement["CAN_BUY"]):?>
				<a href="<?echo $arElement["ADD_URL"]?>" rel="nofollow" onclick="return addToCart(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', 'cart');" id="catalog_add2cart_link_<?=$arElement['ID']?>"><span></span><?=GetMessage("CATALOG_BUY")?></a>
			<?elseif ( $arNotify[SITE_ID]['use'] == 'Y'):?>
				<?if ($USER->IsAuthorized()):?>
					<noindex><a href="<?echo $arElement["SUBSCRIBE_URL"]?>" rel="nofollow" onclick="return addToSubscribe(this, '<?=GetMessage("CATALOG_IN_SUBSCRIBE")?>');" id="catalog_add2cart_link_<?=$arElement['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
					<?else:?>
					<noindex><a href="javascript:void(0)" rel="nofollow" onclick="showAuthForSubscribe(this, <?=$arElement['ID']?>, '<?echo $arElement["SUBSCRIBE_URL"]?>')" id="catalog_add2cart_link_<?=$arElement['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
				<?endif;?>
			<?endif;?>
			</td>
			<?
			}
			?>
			<?if($arParams["DISPLAY_COMPARE"]):?>
				<td>
					<noindex>
					<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])):?>
						<a href="javascript:void(0)" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arElement["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arElement["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'compare');">
							<?=GetMessage("CATALOG_COMPARE")?>
						</a>
					<?else:?>
						<a href="<?echo $arElement["COMPARE_URL"]?>" rel="nofollow" onclick="return addToCompare(this, 'list_price', '<?=GetMessage("CATALOG_IN_COMPARE")?>');" id="catalog_add2compare_link_<?=$arElement['ID']?>">
						   <?=GetMessage("CATALOG_COMPARE")?>
						</a>
					<?endif?>
					</noindex>
				</td>
			<?endif?>
		</tr>
	<?endforeach;?>
	</tbody>
</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>