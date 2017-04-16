<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="page-title category-title">
        <h1><?=$arResult['SECTION']['NAME']?></h1>
</div>
<div class="category-description std">
    <p class="category-image">
    <img src="<?=$arResult['SECTION']['PICTURE']['src']?>" alt="<?=$arResult['SECTION']['NAME']?>" />
    </p>
    <?=$arResult['SECTION']['DESCRIPTION']?>
</div>    
<?if(count($arResult["SECTIONS"]) > 0 ):?>
<div class="categories clearfix">
<h2>В разделе:</h2>
<ul class="subcategories">
<?
$TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = $TOP_DEPTH;

foreach($arResult["SECTIONS"] as $arSection):?>

<li id="<?=$this->GetEditAreaId($arSection['ID']);?>">
	 <a  class="bgcolor bordercolor" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
		<img class="bordercolor" src="<?=$arSection["PICTURE"]["SRC"]?>" alt="<?=$arSection["NAME"]?>" width="80" height="80">
		<span><?=$arSection["NAME"]?></span>
		<strong></strong>
	</a>	
</li>
<?endforeach;?>	
</ul>
</div>
<?endif?>
<?//trace($arResult)?>
