<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?if(!empty($arResult['SEO'])):?>
<div class="row BxmodSContentBlock">
    <div class="col-main span12">
    <div class="block padding-s">
    <? if($arResult["SEO"]["H1"]): ?>
        <div class="page-title">
            <h1><?=$arResult["SEO"]["H1"]?></h1>
        </div>
    <? endif; ?>
    <?if( strlen( $arResult["SEO"]["SEO_TEXT"] ) > 0 ):?>
        <div class="std BxmodSContentText"><?=$arResult["SEO"]["SEO_TEXT"]?></div>
    <?endif?>
    <?if( !empty( $arResult["LINKS"] ) ):?>
        <div class="std BxmodSContentLinks">
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
    </div>    
</div>
<?endif;?>
<?//trace($arResult)?>