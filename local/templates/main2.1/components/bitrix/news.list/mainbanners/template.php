<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!-- banner-block-->
<div class="banners_row">
    <? foreach ($arResult["ITEMS"] as $k => $arItem): ?>   
    <div class="banner ban<?=$k+1?> id<?= $arItem['ID'] ?> typ_<?=strtolower($arItem['PROPERTIES']['TYPE']['VALUE_XML_ID'])?>">
            <a href="<?= $arItem["PROPERTIES"]["LINK"]["VALUE"] ?>">
            <?if($arItem['PROPERTIES']['TYPE']['VALUE_XML_ID'] !== 'IMAGE'){?>
                <div class="b_holder">
                    <h2><?= $arItem["PROPERTIES"]["TITLE"]["VALUE"] ?></h2>
                    <h3><?= $arItem["PROPERTIES"]["SUBTITLE"]["VALUE"] ?></h3>
                    <div class="b_discoount"><?= $arItem["PROPERTIES"]["DISCOUNT"]["VALUE"] ?></div>
                    <div class="b_price"><?= $arItem["PROPERTIES"]["PRICE"]["VALUE"] ?></div>
                </div>
                    <?if (!empty($arItem["PREVIEW_PICTURE"])):
                        $image = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], array('width' => 180, 'height' => 180), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                        ?>
                            <img src="<?= $image["src"] ?>" alt="<?=$arItem['NAME']?>"/>    
                    <? endif ?>                
            <?}else{?>
                    <img src="<?=$arItem["PREVIEW_PICTURE"]['SRC'] ?>" alt="<?=$arItem['NAME']?>"/>    
            <?}?>
                </a>
        </div>
    
    <? endforeach; ?>                            
</div>    
<!--// banner-block-->
<?
//trace($arResult)?>