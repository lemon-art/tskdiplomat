<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<div class="block block-account first">
    <div class="block-title">
        <strong>
            <span><?=(strlen($arParams['BLOCK_TITLE']) > 0 )? $arParams['BLOCK_TITLE'] : GetMessage('TPL_SIDEBAR_BLOCK_TITLE');?></span>
        </strong>
        <span class="toggle"></span>
    </div>
<div class="block-content">
    <ul>
    <?foreach($arResult as $arItem):?>
        <li>
            <a href="<?=$arItem["LINK"]?>" title='<?=$arItem['TEXT']?>' class="<?=($arItem["SELECTED"])?'current':'';?>">
                <?=$arItem['TEXT']?>
            </a>    
        </li>    
    <?endforeach;?>    
    </ul>
</div>    
</div>
<?endif?>