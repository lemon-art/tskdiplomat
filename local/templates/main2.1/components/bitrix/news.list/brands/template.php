<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="box-brands">
    <h2><a href="<?=$arResult['LIST_PAGE_URL']?>" title="Все бренды"><?=GetMessage('TPL_BRANDS_BLOCK_TITLE');?></a></h2>
    <div class="brands-carousel jcarousel">
        <ul>
        <?foreach($arResult['ITEMS'] as $arItem):?>
        <li>
            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
            <img class="grayscale" src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" />
            </a>
        </li>    
        <?endforeach;?>
        </ul>
    </div>    
        <div class="jcarousel-prev"></div>
        <div class="jcarousel-next"></div>
</div>

<?//trace($arResult)?>