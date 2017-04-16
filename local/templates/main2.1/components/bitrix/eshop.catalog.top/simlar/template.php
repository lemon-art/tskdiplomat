<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["ITEMS"]) > 0): ?>
<div id="featured_products">
<h3>Аналогичные товары</h3>
<div class="block_content">
<ul>

<?foreach($arResult["ITEMS"] as $key => $arItem):
	if(is_array($arItem))
	{?>
<li class="ajax_block_product">
	<a class="product_image" href="<?=$arItem["DETAIL_PAGE_URL"]?>">
		<img class="item_img" itemprop="image" src="<?=$arItem["PREVIEW_IMG"]["SRC"]?>" width="<?=$arItem["PREVIEW_IMG"]["WIDTH"]?>" height="<?=$arItem["PREVIEW_IMG"]["HEIGHT"]?>" alt="<?=$arItem["NAME"]?>" />
	</a>

	<h5>
		<a class="product_link" href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["NAME"]?>">
			<?=$arItem["NAME"]?>
		</a>
	</h5>
				<?
				if(is_array($arItem["OFFERS"]) && !empty($arItem["OFFERS"]))   //if product has offers
				{
					if (count($arItem["OFFERS"]) > 1)
					{
                    ?>
                       <span itemprop = "price" class="item_price" style="color:#000">
                    <?
						echo GetMessage("CR_PRICE_OT")."&nbsp;";
						echo $arItem["PRINT_MIN_OFFER_PRICE"];
                    ?>
                        </span>
                    <?
					}
					else
					{
						foreach($arItem["OFFERS"] as $arOffer):?>
							<?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
								<?if($arPrice["CAN_ACCESS"]):?>
										<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
											<span itemprop = "discount-price" class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br>
											<span class="old-price"><?=$arPrice["PRINT_VALUE"]?></span><br>
											<?else:?>
											<span itemprop = "price" class="item_price price"><?=$arPrice["PRINT_VALUE"]?></span>
										<?endif?>
								<?endif;?>
							<?endforeach;?>
						<?endforeach;
					}
				}
				else // if product doesn't have offers
				{
                    foreach($arItem["PRICES"] as $code=>$arPrice):
                        if($arPrice["CAN_ACCESS"]):
                            ?>
                                <?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
                                    
                                    <span itemprop="price" class="price yourprice"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?>&nbsp;<s><?=$arPrice["PRINT_VALUE"]?></s></span>
                                <?else:?>
                                    <span itemprop="price" class="price"><?=$arPrice["PRINT_VALUE"]?></span>
                                <?endif;?>
                            <?
                        endif;
                    endforeach;
				}
				?>
	
 	<p class="product_desc">
	<a class="product_descr" href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="Подробнее...">
	 <?=$arItem["PREVIEW_TEXT"]?>
	 </a>
	 </p>
<div>
<a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_<?=$arItem["ID"]?>" href="<?=$arItem["ADD_URL"]?> " title="В корзину">В корзину</a>
</div>
</li>
	
	<?}
endforeach;
?>
</ul>
<div class="clearblock"></div>
</div>
</div>
<?endif?>
