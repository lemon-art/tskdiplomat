<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

?>
<div class="row brands-list">
    <?foreach ($arResult['ITEMS'] as $key => $arItem){?>
    <div class="col-md-4 brand-item">
        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="logo">
            <img
                border="0"
                src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
                alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
                title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
            />
        </a>
        <a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><h3><?echo $arItem["NAME"]?></h3></a>
        
        Товаров: <?=$arItem['PRODUCTS_COINT'];?>
<?
$TOP_DEPTH = 4;//$arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = 0;

foreach ($arItem['CATEGORIES'] as $arSection){
    
    echo '<!-- CURENT_DEPTH: '.$CURRENT_DEPTH.'-->';
	if($CURRENT_DEPTH < $arSection["DEPTH_LEVEL"])
		echo "\n",str_repeat("\t", $arSection["DEPTH_LEVEL"]-$TOP_DEPTH),"<ul class='lsnn'>";
	elseif($CURRENT_DEPTH == $arSection["DEPTH_LEVEL"])
		echo "</li>";
	else
	{
		while($CURRENT_DEPTH > $arSection["DEPTH_LEVEL"])
		{
			echo "</li>";
			echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</ul>","\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH-1);
			$CURRENT_DEPTH--;
		}
		echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</li>";
	}

	echo "\n",str_repeat("\t", $arSection["DEPTH_LEVEL"]-$TOP_DEPTH);                
                ?>
            <li>
                <a href="<?=$arSection['SECTION_PAGE_URL']?>"><?=$arSection['NAME']?></a> <?=$arSection['ELEMENT_CNT']?> 
            
            <?
            $CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];            
}
while($CURRENT_DEPTH > $TOP_DEPTH)
{
	echo "</li>";
	echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</ul>","\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH-1);
	$CURRENT_DEPTH--;
}                
                ?>
    </div> 
    <?}?>
</div>

<?//trace($arResult)?>
