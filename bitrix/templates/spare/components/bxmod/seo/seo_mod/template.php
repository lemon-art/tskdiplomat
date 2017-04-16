<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<div class="BxmodSContentBlock"> 
    <? if($arResult["SEO"]["H1"]): ?>
    <h1><?=$arResult["SEO"]["H1"]?></h1>
    <? endif; ?>
    <?if( strlen( $arResult["SEO"]["SEO_TEXT"] ) > 0 ):?>
        <div class="BxmodSContentText"><?=$arResult["SEO"]["SEO_TEXT"]?></div>
    <?endif?>
    <?if( !empty( $arResult["LINKS"] ) ):?>
        <div class="BxmodSContentLinks">
            <p>
            <? if($arResult["SEO"]["SEO_TEXT"] > 0): ?>
                <?if ( strlen( $arParams["BXMOD_SEO_SEO_PARTSTITLE"] ) > 0 ):?>
                    <?=$arParams["BXMOD_SEO_SEO_PARTSTITLE"]?>: 
                <?endif?>
                <?$i=1; foreach ( $arResult["LINKS"] AS $link ):?>
                    <a href="<?=$link["URL"]?>" title="<?=$link["TITLE"]?>"><?=$link["TITLE"]?></a><?if( $i<count( $arResult["LINKS"] ) ):?>, <?endif?>
                <?$i++; endforeach?>
            <? endif; ?>
            </p>
        </div>
    <?endif?>
</div>