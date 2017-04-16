<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!-- banner-block-->
<div class="bottom_banners row">
    <?foreach($arResult["ITEMS"] as $arItem): ?>                            
        <div class="bottom_banner span6  <?=$arItem['CODE']?>">
            <a href="<?=$arItem["PROPERTIES"]["LINK"]["VALUE"] ?>">
                <div class="bottom_holder">
                    <?=$arItem["PREVIEW_TEXT"] ?>
                </div>
                <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"] ?>" alt=""/>
            </a>
        </div>
    <? endforeach; ?>                            

</div> <!-- bottom_banners -->
<!--// banner-block-->
<?//trace($arResult)?>