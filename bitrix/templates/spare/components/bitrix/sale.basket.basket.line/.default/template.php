<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<a href="<?=$arParams["PATH_TO_BASKET"]?>" title="Ваша корзина">Корзина:
<span class="ajax_cart_quantity hidden" style="display: none;"><?=$arResult["PRODUCTS"];?></span>
<span class="ajax_cart_product_txt hidden" style="display: none;">товар</span>
<span class="ajax_cart_product_txt_s hidden" style="display: none;">товаров</span>
<span class="ajax_cart_no_product">(пусто)</span>
</a>

<div id="cart_block" class="block exclusive">
<h4>
<a href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=order">Cart</a>
</h4>
<div class="block_content">
 
<div id="cart_block_list" class="expanded">
<dl class="products"><dt class="first_item" id="cart_block_product_1_0_0" style="display: block;"><span class="quantity-formated"><span class="quantity">1</span>x</span><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=1" title="SP NGDL H4">SP NGDL H4</a><span class="remove_link"><a class="ajax_cart_block_remove_link" rel="nofollow" href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=cart&delete&id_product=1&ipa=0&token=584c8307886eb0c54cb526001b35cbbc"> </a></span><span class="price">$549.00</span></dt><dt class="item" id="cart_block_product_2_0_0" style="display: block;"><span class="quantity-formated"><span class="quantity">1</span>x</span><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=2&controller=product&id_lang=1" title="UUC7 shift knob">UUC7 shift...</a><span class="remove_link"><a class="ajax_cart_block_remove_link" rel="nofollow" href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=cart&delete&id_product=2&ipa=0&token=584c8307886eb0c54cb526001b35cbbc"> </a></span><span class="price">$215.00</span></dt><dt class="last_item" id="cart_block_product_3_0_0" style="display: block;"><span class="quantity-formated"><span class="quantity">1</span>x</span><a href="http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=3&controller=product&id_lang=1" title="Castrol Edge...">Castrol Ed...</a><span class="remove_link"><a rel="nofollow" class="ajax_cart_block_remove_link" href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=cart&delete&id_product=3&token=584c8307886eb0c54cb526001b35cbbc"> </a></span><span class="price">$39.20</span></dt></dl><p id="cart_block_no_products" style="display: none;">No products</p>
<div class="cart-prices">
<div class="cart-prices-block">
<span id="cart_block_shipping_cost" class="price ajax_cart_shipping_cost">$7.00</span>
<span>Shipping</span>
</div>
<div class="cart-prices-block">
<span id="cart_block_total" class="price ajax_block_cart_total">$810.20</span>
<span>Total</span>
</div>
</div>
<p id="cart-buttons">
<a href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=order" class="button_mini" title="Cart">Cart</a> <a href="http://livedemo00.template-help.com/prestashop_42844/index.php?controller=order" id="button_order_cart" class="exclusive" title="Check out"><span></span>Check out</a>
</p>
</div>
</div>
</div>
<?/*
<table class="table-basket-line">
	<?
	if (IntVal($arResult["NUM_PRODUCTS"])>0)
	{
		?>
		<tr>
			<td><a href="<?=$arParams["PATH_TO_BASKET"]?>" class="basket-line-basket"></a></td>
			<td><a href="<?=$arParams["PATH_TO_BASKET"]?>"><?=$arResult["PRODUCTS"];?></a></td>
		</tr>
		<?
	}
	else
	{
		?><tr>
			<td><div class="basket-line-basket"></div></td>
			<td><?=$arResult["ERROR_MESSAGE"]?></td>
		</tr><?
	}
	if($arParams["SHOW_PERSONAL_LINK"] == "Y")
	{
		?>
		<tr>
			<td><a href="<?=$arParams["PATH_TO_PERSONAL"]?>" class="basket-line-personal"></a></td>
			<td><a href="<?=$arParams["PATH_TO_PERSONAL"]?>"><?= GetMessage("TSB1_PERSONAL") ?></a></td>
		</tr>
		<?
	}
	?>
</table>
*/?>