<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if (!empty($arResult["SECTION_USER_FIELDS"]["UF_BROWSER_TITLE"]))
	$APPLICATION->SetTitle($arResult["SECTION_USER_FIELDS"]["UF_BROWSER_TITLE"]);
else
	$APPLICATION->SetTitle($arResult["NAME"]);

if (!empty($arResult["SECTION_USER_FIELDS"]["UF_TITLE_H1"]))
	$APPLICATION->SetPageProperty("ADDITIONAL_TITLE", $arResult["SECTION_USER_FIELDS"]["UF_TITLE_H1"]);
else
	$APPLICATION->SetPageProperty("ADDITIONAL_TITLE", $arResult["NAME"]);?>

<?$wizTemplateId = COption::GetOptionString("main", "wizard_template_id", "eshop_vertical", SITE_ID);
$isVerticalTemplate = ($wizTemplateId == "eshop_vertical" || $wizTemplateId == "eshop_vertical_popup") ? true : false;
$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = unserialize($notifyOption);?>
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>
<ul class="lsnn listitem_horizontal<?//if ($isVerticalTemplate):?> pleft<?//endif?>">
<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
	<li class="R2D2" itemscope itemtype = "http://schema.org/Product">
        <div id="<?=$this->GetEditAreaId($arElement['ID']);?>">
		<table >
			<?
			$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

			$sticker = "";
			if (array_key_exists("PROPERTIES", $arElement) && is_array($arElement["PROPERTIES"]))
			{
				foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
					if (array_key_exists($propertyCode, $arElement["PROPERTIES"]) && intval($arElement["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
					{
						$sticker .= "<div class=\"badge specialoffer\">".$arElement["PROPERTIES"][$propertyCode]["NAME"]."</div>";
						break;
					}
			}
			?>
			<tr>
				<td <?//if ($isVerticalTemplate):?>rowspan="2"<?//endif?>>
					<?if($arParams["DISPLAY_COMPARE"]):?>
                    <noindex>
						<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])):?>
                        	<span class="checkbox">
								<a href="javascript:void(0)" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arElement["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arElement["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'compare');">
									<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
								</a>
							</span>
						<?else:?>
                        	<span class="checkbox">
								<a href="<?echo $arElement["COMPARE_URL"]?>" rel="nofollow" onclick="return addToCompare(this, 'list', '<?=GetMessage("CATALOG_IN_COMPARE")?>', '<?echo $arElement["DELETE_COMPARE_URL"]?>');" id="catalog_add2compare_link_<?=$arElement['ID']?>">
									<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
								</a>
							</span>
						<?endif?>
                    </noindex>
					<?endif?>
					<?if(is_array($arElement["PREVIEW_IMG"])):?>
                        <a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img itemprop="image" class="item_img" border="0" src="<?=$arElement["PREVIEW_IMG"]["SRC"]?>" width="<?=$arElement["PREVIEW_IMG"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_IMG"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a>
					<?elseif(is_array($arElement["PREVIEW_PICTURE"])):?>
                        <a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img itemprop="image" class="item_img" border="0" src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a>
					<?else:?>
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><div class="no-photo-div-big" style="height:130px; width:130px;"></div></a>
					<?endif?>
				</td>
				<td class="title">
					<h3><a class="item_title" href="<?=$arElement["DETAIL_PAGE_URL"]?>" title="<?=$arElement["NAME"]?>"><span itemprop="name"><?=$arElement["NAME"]?></span></a></h3>
                    <div itemprop = "description"><p><?=$arElement["PREVIEW_TEXT"]?></p></div>
				</td>
			<?//if ($isVerticalTemplate):?>
			</tr>
			<tr>
			<?//endif?>
                <td class="price">
					<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"]))  // Product has offers
					{
						if ($arElement["MIN_PRODUCT_OFFER_PRICE"] > 0):
					?>
							<div class="price<?if ($isVerticalTemplate):?>_vert<?endif?>">
								<?=GetMessage("CATALOG_SHOP_PRICE")?> <br/>
								<span <?if ($isVerticalTemplate):?>class="item_price"<?endif?>>
								<?
									if (count($arElement["OFFERS"]) > 1) echo GetMessage("CATALOG_PRICE_FROM");
									echo $arElement["MIN_PRODUCT_OFFER_PRICE_PRINT"];
								?>
								</span>
							</div>
						<?endif;?>
						<a href="javascript:void(0)" class="bt3 addtoCart" id="catalog_add2cart_offer_link_<?=$arElement['ID']?>" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arElement["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arElement["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'cart');"><span></span><?echo GetMessage("CATALOG_BUY")?></a>
					<?
					}
					else  // Product doesn't have offers
					{
						//if ($isVerticalTemplate)
						//{
						?>
							<?if ($arElement["MIN_PRODUCT_DISCOUNT_PRICE"] < $arElement["MIN_PRODUCT_PRICE"] && $arElement["MIN_PRODUCT_PRICE"] > 0 && $arElement["MIN_PRODUCT_DISCOUNT_PRICE"] > 0):?>
								<div class="retail_vert fwn">
									<?=GetMessage("CATALOG_SHOP_PRICE_OLD")?> <br/>
									<span class="old-price"><?if($arElement["MIN_PRODUCT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
								</div>
								<div class="price_vert discount-price">
									<?=GetMessage("CATALOG_SHOP_PRICE")?> <br/>
									<span class="discount-price"><?if($arElement["MIN_PRODUCT_DISCOUNT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_DISCOUNT_PRICE_PRINT"];?></span>
								</div>
							<?elseif ($arElement["MIN_PRODUCT_PRICE"] > 0):?>
								<div class="price_vert">
									<?=GetMessage("CATALOG_SHOP_PRICE")?> <br/>
									<span class="price"><?if($arElement["MIN_PRODUCT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
								</div>
							<?endif?>
						<?
						/*}
						else
						{
						?>
							<?if ($arElement["MIN_PRODUCT_DISCOUNT_PRICE"] < $arElement["MIN_PRODUCT_PRICE"] && $arElement["MIN_PRODUCT_PRICE"] > 0 && $arElement["MIN_PRODUCT_DISCOUNT_PRICE"] > 0):?>
								<div class="price">
									<?=GetMessage("CATALOG_SHOP_PRICE")?> <br/>
									<span><?if($arElement["MIN_PRODUCT_DISCOUNT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_DISCOUNT_PRICE_PRINT"];?></span>
								</div>
								<div class="retail">
									<?=GetMessage("CATALOG_SHOP_PRICE_OLD")?> <br/>
									<span><?if($arElement["MIN_PRODUCT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
								</div>
							<?elseif ($arElement["MIN_PRODUCT_PRICE"] > 0):?>
								<div class="price">
									<?=GetMessage("CATALOG_SHOP_PRICE")?> <br/>
									<span><?if($arElement["MIN_PRODUCT_PRICE"] > 0) echo $arElement["MIN_PRODUCT_PRICE_PRINT"];?></span>
								</div>
							<?endif?>
						<?
						}  */
						?>
						<?if($arElement["CAN_BUY"]):?>
							<a class="bt3 addtoCart" href="<?echo $arElement["ADD_URL"]?>" rel="nofollow" onclick="return addToCart(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', 'cart');" id="catalog_add2cart_link_<?=$arElement['ID']?>"><span></span><?=GetMessage("CATALOG_BUY")?></a>
						<?elseif ($arNotify[SITE_ID]['use'] == 'Y'):?>
							<?if ($USER->IsAuthorized()):?>
                            	<noindex><a href="<?echo $arElement["SUBSCRIBE_URL"]?>" rel="nofollow" class="bt2 bt2_right" onclick="return addToSubscribe(this, '<?=GetMessage("CATALOG_IN_SUBSCRIBE")?>');" id="catalog_add2cart_link_<?=$arElement['ID']?>"><span></span><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
							<?else:?>
                            	<noindex><a href="javascript:void(0)" rel="nofollow" class="bt2 right" onclick="showAuthForSubscribe(this, <?=$arElement['ID']?>, '<?echo $arElement["SUBSCRIBE_URL"]?>')" id="catalog_add2cart_link_<?=$arElement['ID']?>"><span></span><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
							<?endif;?>
						<?endif;
					}
					?>
                </td>
			</tr>
	    </table>
        </div>
		<div class="tlistitem_horizontal_shadow" <?if ($isVerticalTemplate) echo "style='width: 740px;'"?>></div>
		<?if(!(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])) && !$arElement["CAN_BUY"]):?>
        	<div class="badge notavailable"><?=GetMessage("CATALOG_NOT_AVAILABLE2")?></div>
		<?elseif (strlen($sticker)>0):?>
			<?=$sticker?>
		<?endif?>
	</li>
<?endforeach;?>
</ul>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>