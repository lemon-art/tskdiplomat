<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="block-cart-header">
    <h3  onclick="setLocation('<?=$arParams['PATH_TO_BASKET']?>')">Корзина:</h3>
    <div class="block-content">
        <div class="summary"   onclick="setLocation('<?=$arParams['PATH_TO_BASKET']?>')">
            <p class="amount-2" >
                <strong><?=$arResult['NUM_PRODUCTS']?> <?=$arResult['PRODUCT(S)']?></strong> - 
                <span class="price"><?=$arResult['TOTAL_PRICE']?></span>
            </p>
        </div>
        <div class="cart-content">
            <div class="cart-indent">
                <div class="cart-content-header">
                    <p class="subtotal">
                        <span class="lbl">На сумму:</span> <span class="price"><?=$arResult['TOTAL_PRICE']?></span> </p>
                    <p class="block-subtitle"><?=$arResult['PRODUCTS']?></p>
                </div>
                <ol id="cart-sidebar" class="mini-products-list">
                    <?foreach ($arResult['CATEGORIES']['READY'] as $key => $arItem):?>
                    <?if($key >= 3):?>
                    <li class="more-items">Показаны последние добавленные <span><?=$key?></span> из <span><?=count($arResult['CATEGORIES']['READY'])?></span>.</li>
                    <?
                        break;  
                    endif;?>

                    <li class="item">
                        <div class="product-control-buttons">
                            <a href="/ajax/cartremove.php?id=<?=$arItem['ID']?>" title="Удалить из корзины" onclick="return confirm('Вы желаете удалить этот товар из корзины?');" class="btn-remove">Удалить из корзины</a>
                            <?/*todo добавить редактирование в карточке
                            <a href="" title="Edit item" class="btn-edit">Edit item</a>
                             */?>
                        </div>
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>" class="product-image">
                            <img src="<?=$arItem['PICTURE_SRC']?>" alt="<?=$arItem['NAME']?>"/>
                        </a>
                        <p class="product-name">
                            <a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['NAME']?></a>
                        </p>
                        <div class="product-details">
                            <strong><?=$arItem['QUANTITY']?></strong> x
                            <span class="price"><?=$arItem['PRICE']?></span>
                        </div>
                    </li>
                    <?endforeach;?>
                </ol>
                <div class="actions">
                    <button type="button" title="Заказать" class="button" onclick="setLocation('<?=$arParams['PATH_TO_ORDER']?>')">Купить</button>
                    <button type="button" title="Корзина" class="button" onclick="setLocation('<?=$arParams['PATH_TO_BASKET']?>')">Корзина</button>
                </div>
                <script type="text/javascript">decorateList('cart-sidebar', 'none-recursive')</script>
            </div>
        </div>
    </div>
</div>
<?//trace($arResult)?>
<?
/*
$style = 'bx_cart_block';
$style_hack = ''; // TODO remove

if ($arParams["EMBED_MINICART"] == "Y")
{
	$style .= " bx_cart_top_inline";
	$style_hack = 'style="width: auto"';
}
else
{
	if ($arParams["SHOW_PRODUCTS"] == "Y")
		$style .= " bx_cart_sidebar";

	if ($arParams["POSITION_FIXED"] == "Y")
	{
		$style .= " bx_cart_fixed";
		$style .= $arParams["POSITION_TOP"] == "Y" ? ' top' : ' bottom';
		$style .= $arParams["POSITION_RIGHT"] == "Y" ? ' right' : ' left';
		if ($arParams["POSITION_HEIGHT"] == "Y")
			$style .= ' max_height';
		if ($arParams["DISPLAY_COLLAPSE"] == 'Y' && $arParams["SHOW_PRODUCTS"] == "Y")
			$style .= ' close';
		if ($arParams["SHOW_PRODUCTS"] == "N" || $arResult["NUM_PRODUCTS"] == 0)
			$style .= ' min';
	}
}
?>
<div id="bx_cart_block" class="<?=$style?>" <?=$style_hack?>>
	<?
	$frame = $this->createFrame("bx_cart_block", false)->begin();

	$component->includeComponentTemplate ('ajax_template');

	$frame->beginStub();
	?>
	<div class="bx_cart_top_inline">
		<span class="bx_cart_top_inline_icon"></span>
		<span class="bx_cart_top_inline_link"><?=GetMessage('TSB1_CART')?></span>
	</div>
	<?
	$frame->end();
	?>
</div>
<script>
	sbbl.elemBlock = BX("bx_cart_block");
	sbbl.elemStatus = BX("bx_cart_block_status");
	sbbl.strCollapse = '<?=GetMessage("TSB1_COLLAPSE")?>';
	sbbl.strExpand = '<?=GetMessage("TSB1_EXPAND")?>';

	sbbl.ajaxPath = '<?=$this->GetFolder()?>/ajax.php';
	sbbl.siteId = '<?=SITE_ID?>';
	sbbl.arParams = <?=CUtil::PhpToJSObject ($arParams)?>;

	BX.addCustomEvent(window, "OnBasketChange", sbbl.refreshCart);
</script>
 * 
 */