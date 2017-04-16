<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(count($arResult["SECTIONS"]) > 0 ):?>
<div class="clearfix">
<ul class="services products-list">
<?
$TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = $TOP_DEPTH;

foreach($arResult["SECTIONS"] as $arSection):
    
        if($arSection['DEPTH_LEVEL'] > $TOP_DEPTH+1)
            continue;
    ?>

    <li class="item">
         <a  class="product-image" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
		<img class="bordercolor" src="<?=$arSection["DISPLAY_PICTURE"]["src"]?>" alt="<?=$arSection["NAME"]?>">
         </a>
        <div class="product-shop">
        <h2 class="product-name">
            <a  href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
		<?=$arSection["NAME"]?>
            </a>   
        </h2>   
            <p><?=$arSection['UF_PREVIEW_TEXT']?></p><br/>    
        <?if(count($arSection['SUBSECTIONS']) > 0){?>
        <ul>
            <?foreach ($arSection['SUBSECTIONS'] as $sub){
                $arSub = $arResult['SECTIONS'][$sub];
                ?>
            <li>
                <a  class="" href="<?=$arSub["SECTION_PAGE_URL"]?>" title="<?=$arSub["NAME"]?>">
                    <!--
                    <img class="bordercolor" src="<?=$arSub["DISPLAY_PICTURE"]["src"]?>" alt="<?=$arSub["NAME"]?>">
                    -->
                    <?=$arSub["NAME"]?>
                </a>	
            </li>
            <?}?>
        </ul>
        <?}?>
            <button type="button" title="Подробнее" class="button btn-cart details" onclick="setLocation('<?=$arSection["SECTION_PAGE_URL"]?>')">
                            <span>
                                <span>Подробнее</span>
                            </span>
            </button>
        </div>
</li>
<?endforeach;?>	
</ul>
</div>
<?endif?>
<?//trace($arResult)?>
