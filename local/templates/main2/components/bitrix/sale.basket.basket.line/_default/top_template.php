<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();?>

<div class="block-cart-header">

    <div class="block-content">
        <div class="summary">
            <p><span class="price"><?=$arResult['TOTAL_PRICE']?></span></p>
        </div>
        <div class="cart-content">
            <div class="cart-indent">
                <div class="cart-content-header">
                    <p class="subtotal">
                        <span class="label">На сумму:</span> <span class="price"><?=$arResult['TOTAL_PRICE']?></span> </p>
                    <p class="block-subtitle"><?=$arResult['PRODUCTS']?></p>
                </div>
                <ol id="cart-sidebar" class="mini-products-list">
                    <?foreach ($arResult['CATEGORIES']['READY'] as $key => $arItem):?>
                    <li class="item">
                        <div class="product-control-buttons">
                            <a href="/ajax/cartremove.php?id=<?=$arItem['ID']?>" title="Удалить из корзины" onclick="return confirm('Вы желаете удалить этот товар из корзины?');" class="btn-remove">Удалить из корзины</a>
                            <?/*todo добавить редактирование в карточке
                            <a href="http://livedemo00.template-help.com/magento_47672/checkout/cart/configure/id/440/" title="Edit item" class="btn-edit">Edit item</a>
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
                    <button type="button" title="Заказать" class="button" onclick="setLocation('<?=$arParams['PATH_TO_ORDER']?>')"><span><span>Купить</span></span></button>
                    <button type="button" title="Корзина" class="button" onclick="setLocation('<?=$arParams['PATH_TO_BASKET']?>')"><span><span>Корзина</span></span></button>
                </div>
                <script type="text/javascript">decorateList('cart-sidebar', 'none-recursive')</script>
            </div>
        </div>
        <p class="mini-cart"><strong title=""><?=$arResult['NUM_PRODUCTS']?></strong> </p>
    </div>
</div>
<?//trace($arResult)?>
<?/*
<div class="bx_small_cart">
	<span class="icon_cart"></span>
	<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=GetMessage('TSB1_CART')?></a>
	<?if ($arParams['SHOW_NUM_PRODUCTS'] == 'Y' && ($arResult['NUM_PRODUCTS'] > 0 || $arParams['SHOW_EMPTY_VALUES'] == 'Y')):?>
		<strong><?=$arResult['NUM_PRODUCTS'].' '.$arResult['PRODUCT(S)']?></strong>
	<?endif?>
	<?if ($arParams['SHOW_TOTAL_PRICE'] == 'Y'):?>
		<br>
		<span class="icon_spacer"></span>
		<?=GetMessage('TSB1_TOTAL_PRICE')?>
		<?if ($arResult['NUM_PRODUCTS'] > 0 || $arParams['SHOW_EMPTY_VALUES'] == 'Y'):?>
			<strong><?=$arResult['TOTAL_PRICE']?></strong>
		<?endif?>
	<?endif?>
	<?if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'):?>
		<br>
		<span class="icon_info"></span>
		<a class="link_profile" href="<?=$arParams['PATH_TO_PERSONAL']?>"><?=GetMessage('TSB1_PERSONAL')?></a>
	<?endif?>
	<?if ($arParams['SHOW_AUTHOR'] == 'Y'):?>
		<br>
		<span class="icon_profile"></span>
		<?if ($USER->IsAuthorized()):
			$name = trim($USER->GetFullName());
			if (! $name)
				$name = trim($USER->GetLogin());
			if (strlen($name) > 15)
				$name = substr($name, 0, 12).'...';
			?>
			<a class="link_profile" href="<?=$arParams['PATH_TO_PROFILE']?>"><?=$name?></a>
			&nbsp;
			<a class="link_profile" href="?logout=yes"><?=GetMessage('TSB1_LOGOUT')?></a>
		<?else:?>
			<a class="link_profile" href="<?=$arParams['PATH_TO_REGISTER']?>?login=yes"><?=GetMessage('TSB1_LOGIN')?></a>
			&nbsp;
			<a class="link_profile" href="<?=$arParams['PATH_TO_REGISTER']?>?register=yes"><?=GetMessage('TSB1_REGISTER')?></a>
		<?endif?>
	<?endif?>
</div>
*/?>