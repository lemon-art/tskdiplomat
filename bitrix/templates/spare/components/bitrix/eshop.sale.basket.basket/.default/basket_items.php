<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
 
<h1 id="cart_title">Ваша корзина</h1>
<?/* 
<ul id="order_steps" class="step1">
<li class="step_current">
<span>Summary</span>
</li>
<li class="step_todo">
<span>Login</span>
</li>
<li class="step_todo">
<span>Address</span>
</li>
<li class="step_todo">
<span>Shipping</span>
</li>
<li id="step_end" class="step_todo">
<span>Payment</span>
</li>
</ul>
 */?>
<script type="text/javascript">
	// <![CDATA[
	var currencySign = '$';
	var currencyRate = '1';
	var currencyFormat = '1';
	var currencyBlank = '0';
	var txtProduct = "product";
	var txtProducts = "products";
	var deliveryAddress = 0;
	// ]]>
	</script>
<?$items = count($arResult["ITEMS"]["AnDelCanBuy"]);?>
<p <?=($items > 0)?'style="display:none"':''?> id="emptyCartWarning" class="warning">Вы ничего не положили в корзину.</p>
<p <?=($items <= 0)?'style="display:none"':''?> class="p-cart">В вашей корзине: <span id="summary_products_quantity"><?=$items?> товаров</span></p>

<div id="order-detail-content" class="table_block">
<table id="cart_summary" class="std">
<thead>
<tr>
<th class="cart_product first_item">Товар</th>
<th class="cart_description item">Описание</th>
<th class="cart_ref item">#</th>
<th class="cart_unit item">Цена</th>
<th class="cart_quantity item">Кол.</th>
<th class="cart_total item">Всего</th>
</tr>
</thead>
<tfoot>
<?/*
<tr class="cart_total_price">
<td colspan="5">Total products:</td>
<td colspan="2" class="price" id="total_product">$2,393.40</td>
</tr>
<tr class="cart_total_voucher" style="display:none">
<td colspan="5">
Total vouchers:
</td>
<td colspan="2" class="price-discount price" id="total_discount">
$0.00
</td>
</tr>
<tr class="cart_total_voucher" style="display: none;">
<td colspan="5">
Total gift-wrapping: </td>
<td colspan="2" class="price-discount price" id="total_wrapping">
$0.00
</td>
</tr>
<tr class="cart_total_delivery">
<td colspan="5">Total shipping:</td>
<td colspan="2" class="price" id="total_shipping">$7.00</td>
</tr>
<tr class="cart_total_price">
<td colspan="5">Total (tax excl.):</td>
<td class="price" id="total_price_without_tax">$2,400.40</td>
</tr>
<tr class="cart_total_tax">
<td colspan="5">Total tax:</td>
<td class="price" id="total_tax">$0.00</td>
</tr>
*/?>
<tr class="cart_total_price">
<td colspan="5">Итого:</td>
<td class="price" id="total_price"><span class="price"><?=$arResult["allSum_FORMATED"]?></span></td>
</tr>
</tfoot>
<tbody>
<?foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $key => $Item):?>
<tr id="product_<?=$Item["ID"]?>_0_0_0" class="<?=($key==0)?"first_item":($key+1 < $items)?"item":"last_item";?>  cart_item address_0 odd">
<td class="cart_product">
	<a href="<?=$Item["DETAIL_PAGE_URL"]?>">
		<?if(strlen($Item["DETAIL_PICTURE"]["SRC"]) > 0):?>
			<img src="<?=$Item["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$Item["NAME"]?>" width="80" height="80">
		<?else:?>
			<img src="/images/noimage.png" alt="<?=$Item["NAME"]?>" width="80" height="80" alt="Изображение временно отсутствует">
		<?endif;?>
	</a>
</td>
<td class="cart_description">
	<h5>
		<a href="<?=$Item["DETAIL_PAGE_URL"]?>"><?=$Item["NAME"]?></a>
	</h5>
</td>
<td class="cart_ref">--</td>
<td class="cart_unit">
	<span class="price" id="product_price_<?=$Item["ID"]?>_0_0">
		<?=$Item["PRICE_FORMATED"]?>
	</span>
</td>
<td class="cart_quantity">
	<div id="cart_quantity_button">
		<a rel="nofollow" class="cart_quantity_up" id="cart_quantity_up_<?=$Item["ID"]?>_0_0_0" href="<?=str_replace("#ID#", $Item["ID"], $arUrlTempl["add"])?>" title="Добавить">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/icon/quantity_up.png" alt="Добавить">
		</a>
		<input size="2" type="text" autocomplete="off" class="cart_quantity_input text" value="<?=$Item["QUANTITY"]?>" name="quantity_2_0_0_0">
		<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_<?=$Item["ID"]?>_0_0_0" href="<?=str_replace("#ID#", $Item["ID"], $arUrlTempl["shelve"])?>" title="Уменьшить">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/icon/quantity_down.png" alt="Уменьшить">
		</a>
	</div>
	<a rel="nofollow" class="cart_quantity_delete" id="<?=$Item["ID"]?>_0_0_0" href="<?=str_replace("#ID#", $Item["ID"], $arUrlTempl["delete"])?>">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/icon/delete-cart.gif" alt="Удалить" class="icon">
	</a>
	<input type="hidden" value="<?=$Item["QUANTITY"]?>" name="quantity_<?=$Item["ID"]?>_0_0_0_hidden">
</td>
<td class="cart_total">
<span class="price" id="total_product_price_<?=$Item["ID"]?>_0_0">
	<?=$Item["VALUE_FORMATED"]?>
</span>
</td>
</tr>
<?endforeach;?>
</tbody>
</table>

		<?if ($arParams["HIDE_COUPON"] != "Y"):?>
			<div id="cart_voucher" class="table_block">
				<div id="voucher">
					<fieldset class="bordercolor">
						<h2><label for="discount_name"><?=GetMessage("SALE_COUPON_VAL")?></label></h2>
						<p>
							<input type="text" class="discount_name" id="discount_name"
								value="<?=$arResult["COUPON"]?>"
								name="COUPON"/>
						</p>
						<?/*
						<p class="submit">
							<input type="hidden" name="submitDiscount">
							<input type="submit" name="submitAddDiscount" value="OK" class="button"/>
						</p>
						*/?>
					</fieldset>
				</div>
			</div>
		<?endif;?>	
				
<div id="HOOK_SHOPPING_CART"></div>
<p class="cart_navigation">
<a href="/personal/order/make/" class="exclusive standard-checkout" title="Перейти к оформлению заказа">Оформить заказ »</a>
<a href="/" class="button_large" title="Continue shopping">« Продолжить покупки</a>
</p>
</div>				



<?if($_REQUEST["D"] == "Y" && $USER->IsAdmin()):?>
<pre>
<?=print_r($arParams,1)?>
<?=print_r($arResult,1)?>
</pre>
<?endif?>
<?/*
<div id="id-cart-list">
	<div class="sort">
		<div class="sorttext"><?=GetMessage("SALE_PRD_IN_BASKET")?></div>
		<a href="javascript:void(0)" class="sortbutton current"><?=GetMessage("SALE_PRD_IN_BASKET_ACT")?></a>
		<?if ($countItemsDelay=count($arResult["ITEMS"]["DelDelCanBuy"])):?><a href="javascript:void(0)" onclick="ShowBasketItems(2);" class="sortbutton"><?=GetMessage("SALE_PRD_IN_BASKET_SHELVE")?> (<?=$countItemsDelay?>)</a><?endif?>
		<?if ($countItemsSubscribe=count($arResult["ITEMS"]["ProdSubscribe"])):?><a href="javascript:void(0)" onclick="ShowBasketItems(3);" class="sortbutton"><?=GetMessage("SALE_PRD_IN_BASKET_SUBSCRIBE")?> (<?=$countItemsSubscribe?>)</a><?endif?>
		<?if ($countItemsNotAvailable=count($arResult["ITEMS"]["nAnCanBuy"])):?><a href="javascript:void(0)" onclick="ShowBasketItems(4);" class="sortbutton"><?=GetMessage("SALE_PRD_IN_BASKET_NOTA")?> (<?=$countItemsNotAvailable?>)</a><?endif?>
	</div>
<?$numCells = 0;?>
<table class="equipment mycurrentorders" rules="rows" style="width:726px">
	<thead>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td><?= GetMessage("SALE_NAME")?></td>
				<td></td>
				<?$numCells += 2;?>
			<?endif;?>
			<?if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
				<td><?= GetMessage("SALE_VAT")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-type"><?= GetMessage("SALE_PRICE_TYPE")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-discount"><?= GetMessage("SALE_DISCOUNT")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-weight"><?= GetMessage("SALE_WEIGHT")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-quantity"><?= GetMessage("SALE_QUANTITY")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price"><?= GetMessage("SALE_PRICE")?></td>
				<?$numCells++;?>
			<?endif;?>
			<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-delay"></td>
				<?$numCells++;?>
			<?endif;?>
		</tr>
	</thead>
<?if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):?>
	<tbody>
	<?
	$i=0;
	foreach($arResult["ITEMS"]["AnDelCanBuy"] as $arBasketItems)
	{
		?>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td>
					<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
						<a class="deleteitem" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" onclick="//return DeleteFromCart(this);" title="<?=GetMessage("SALE_DELETE_PRD")?>"></a>
					<?endif;?>
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						<a href="<?=$arBasketItems["DETAIL_PAGE_URL"]?>">
					<?endif;?>
					<?if (strlen($arBasketItems["DETAIL_PICTURE"]["SRC"]) > 0) :?>
						<img src="<?=$arBasketItems["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arBasketItems["NAME"] ?>"/>
					<?else:?>
						<img src="/bitrix/components/bitrix/eshop.sale.basket.basket/templates/.default/images/no-photo.png" alt="<?=$arBasketItems["NAME"] ?>"/>
					<?endif?>
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						</a>
					<?endif;?>
				</td>
				<td class="cart-item-name">
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						<a href="<?=$arBasketItems["DETAIL_PAGE_URL"]?>">
					<?endif;?>
						<?=$arBasketItems["NAME"] ?>
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						</a>
					<?endif;?>
					<?if (in_array("PROPS", $arParams["COLUMNS_LIST"]))
					{
						foreach($arBasketItems["PROPS"] as $val)
						{
							echo "<br />".$val["NAME"].": ".$val["VALUE"];
						}
					}?>
				</td>
			<?endif;?>
			<?if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["VAT_RATE_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["NOTES"]?></td>
			<?endif;?>
			<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["WEIGHT_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
				<td>
					<input maxlength="18" type="text" name="QUANTITY_<?=$arBasketItems["ID"]?>" value="<?=$arBasketItems["QUANTITY"]?>" size="3" id="QUANTITY_<?=$arBasketItems["ID"]?>">
					<div class="count_nav">
						<a href="javascript:void(0)" class="plus" onclick="BX('QUANTITY_<?=$arBasketItems["ID"]?>').value++;"></a>
						<a href="javascript:void(0)" class="minus" onclick="if (BX('QUANTITY_<?=$arBasketItems["ID"]?>').value > 1) BX('QUANTITY_<?=$arBasketItems["ID"]?>').value--;"></a>
					</div>
				</td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price">
					<?if(doubleval($arBasketItems["FULL_PRICE"]) > 0):?>
						<div class="discount-price"><?=$arBasketItems["PRICE_FORMATED"]?></div>
						<div class="old-price"><?=$arBasketItems["FULL_PRICE_FORMATED"]?></div>
					<?else:?>
						<div class="price"><?=$arBasketItems["PRICE_FORMATED"];?></div>
					<?endif?>
				</td>
			<?endif;?>
			<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<td><a class="setaside" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["shelve"])?>"><?=GetMessage("SALE_OTLOG")?></a></td>
			<?endif;?>
		</tr>
		<?
		$i++;
	}
	?>
	</tbody>
</table>
<table class="myorders_itog">
	<tbody>
		<?if ($arParams["HIDE_COUPON"] != "Y"):?>
		<tr>
			<td rowspan="5" class="tal">
				<input class="input_text_style"
					<?if(empty($arResult["COUPON"])):?>
						onclick="if (this.value=='<?=GetMessage("SALE_COUPON_VAL")?>')this.value=''; this.style.color='black'"
						onblur="if (this.value=='') {this.value='<?=GetMessage("SALE_COUPON_VAL")?>'; this.style.color='#a9a9a9'}"
						style="color:#a9a9a9"
					<?endif;?>
						value="<?if(!empty($arResult["COUPON"])):?><?=$arResult["COUPON"]?><?else:?><?=GetMessage("SALE_COUPON_VAL")?><?endif;?>"
						name="COUPON">
			</td>
		</tr>
		<?endif;?>
		<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
			<tr>
				<td><?echo GetMessage("SALE_ALL_WEIGHT")?>:</td>
				<td><?=$arResult["allWeight_FORMATED"]?></td>
			</tr>
		<?endif;?>
		<?if (doubleval($arResult["DISCOUNT_PRICE"]) > 0):?>
			<tr>
				<td><?echo GetMessage("SALE_CONTENT_DISCOUNT")?><?
					if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0)
						echo " (".$arResult["DISCOUNT_PERCENT_FORMATED"].")";?>:
				</td>
				<td><?=$arResult["DISCOUNT_PRICE_FORMATED"]?></td>
			</tr>
		<?endif;?>
		<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
			<tr>
				<td><?echo GetMessage('SALE_VAT_EXCLUDED')?></td>
				<td><?=$arResult["allNOVATSum_FORMATED"]?></td>
			</tr>
			<tr>
				<td><?echo GetMessage('SALE_VAT_INCLUDED')?></td>
				<td><?=$arResult["allVATSum_FORMATED"]?></td>
			</tr>
		<?endif;?>
		<tr>
			<td><?= GetMessage("SALE_ITOGO")?>:</td>
			<td><?=$arResult["allSum_FORMATED"]?></td>
		</tr>
	</tbody>
</table>
<br/>
<table class="w100p" style="border-top:1px solid #d9d9d9;margin-bottom:40px;">
	<tr>
		<td style="padding:30px 2px;" class="tal"><input type="submit" value="<?echo GetMessage("SALE_UPDATE")?>" name="BasketRefresh" class="bt2"></td>
		<td style="padding:30px 2px;" class="tar"><input type="submit" value="<?echo GetMessage("SALE_ORDER")?>" name="BasketOrder"  id="basketOrderButton2" class="bt3"></td>
	</tr>
</table>
<?else:?>
	<tbody>
		<tr>
			<td colspan="<?=$numCells?>" style="text-align:center">
				<div class="cart-notetext"><?=GetMessage("SALE_NO_ACTIVE_PRD");?></div>
				<a href="<?=SITE_DIR?>" class="bt3"><?=GetMessage("SALE_NO_ACTIVE_PRD_START")?></a><br><br>
			</td>
		</tr>
	</tbody>
</table>
<?endif;?>
</div>
<?*/?>