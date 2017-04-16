<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<ul id="header_user">
<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array(
		"REGISTER_URL" => SITE_DIR."auth/",
		"PROFILE_URL" => SITE_DIR."personal/",
		"SHOW_ERRORS" => "N"
		),
		false,
		Array()
);?>
<li id="shopping_cart">
<a href="<?=$arParams["PATH_TO_BASKET"]?>" title="Ваша корзина">Корзина:
<?if(count($arResult["ITEMS"]) > 0):?> 
	<span class="ajax_cart_quantity"><?=count($arResult["ITEMS"]);?></span>
	<?if(count($arResult["ITEMS"]) > 1):?>
		<span class="ajax_cart_product_txt hidden">товар</span>
		<span class="ajax_cart_product_txt_s">товаров</span>	
	<?else:?>
		<span class="ajax_cart_product_txt">товар</span>
		<span class="ajax_cart_product_txt_s hidden">товаров</span>	
	<?endif;?>
	<span class="ajax_cart_no_product  hidden">(пусто)</span>
<?else:?>	
	<span class="ajax_cart_quantity hidden"></span>
	<span class="ajax_cart_product_txt hidden">товар</span>
	<span class="ajax_cart_product_txt_s hidden">товаров</span>
	<span class="ajax_cart_no_product">(пусто)</span>
<?endif;?>	
</a>
</li>
</ul>
 
<script type="text/javascript">
var CUSTOMIZE_TEXTFIELD = 1;
var img_dir = '/prestashop_42844/themes/theme573/img/';
</script>
<script type="text/javascript">
var customizationIdMessage = 'Customization #';
var removingLinkText = 'удалить из корзины';
var freeShippingTranslation = 'Бесплатная доставка!';
var freeProductTranslation = 'Подарок!';
var delete_txt = 'Удалить';
</script>

<div id="cart_block" class="block exclusive">
<div class="h4title">
<a href="<?=$arParams["PATH_TO_BASKET"]?>">Корзина</a>
</div>
<div class="block_content">
 
<div id="cart_block_list">
<!--start--><dl class="products">
<?
$ci = count($arResult["ITEMS"]);
if($ci > 0):?>
	
		<?foreach($arResult["ITEMS"] as $key => $Item):?>
			<dt id="cart_block_product_<?=$Item["ID"]?>_0_0" <?($key<= 0)?'class="first_item"':($ci == $key+1)?'class="last_item"':'class="item"'?>>
				<a href="<?=$Item["DETAIL_PAGE_URL"]?>"></a>
				<span class="quantity-formated"><span class="quantity"><?=$Item["QUANTITY"]?></span>x</span>
				<a class="cart_block_product_name" href="<?=$Item["DETAIL_PAGE_URL"]?>" title="<?=$Item["NAME"]?>">
					<?=substr($Item["NAME"],0,30)?>...
				</a>
				<span class="remove_link">
					<a rel="nofollow" class="ajax_cart_block_remove_link" href="/personal/cart/?action=delete&id=<?=$Item["ID"]?>" title="remove this product from my cart">&nbsp;</a>
				</span>
				<span class="price"><?=$Item["PRICE_FORMATED"]?></span>
			</dt>
 		<?endforeach;?>
 
<?endif?>
	</dl><!--end-->
	<p <?=(count($arResult["ITEMS"]) <= 0)?'class="hidden"':''?> id="cart_block_no_products">Корзина пуста</p>
	
<div class="cart-prices">
<div class="cart-prices-block">
<span id="cart_block_shipping_cost" class="price ajax_cart_shipping_cost">0.00</span>
<span>Доставка:</span>
</div>
<div class="cart-prices-block">
<span id="cart_block_total" class="price ajax_block_cart_total">0.00</span>
<span>Итог:</span>
</div>
</div>
<p id="cart-buttons">
<a href="<?=$arParams["PATH_TO_BASKET"]?>" class="button_mini" title="Корзина">Перейти в корзину</a>
</p>
</div>
</div>
</div>

<?//="<pre>".print_r($arResult,1)."</pre>"?>
