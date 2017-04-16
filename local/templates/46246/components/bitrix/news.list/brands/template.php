<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="box-brands brands-carousel">
<ul>
    <h2><?=GetMessage('TPL_BRANDS_BLOCK_TITLE');?></h2>
    <?foreach($arResult['ITEMS'] as $arItem):?>
    <li>
        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
        <img class="grayscale" src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" />
        </a>
    </li>    
    <?endforeach;?>
</ul>
</div>