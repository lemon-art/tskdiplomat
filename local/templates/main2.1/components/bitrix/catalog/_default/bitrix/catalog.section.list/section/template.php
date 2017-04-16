<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="row page-title category-title">
        <h1><?=$arResult['SECTION']['NAME']?></h1>
</div>
  
<?if(count($arResult["SECTIONS"]) > 0 ):?>
<div class="row categories">
        <h2>В разделе:</h2>

<ul class="thumbnails subcategories">
<?
$TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = $TOP_DEPTH;

foreach($arResult["SECTIONS"] as $arSection):?>

    <li class="span3" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
	 <a  class="thumbnail bgcolor bordercolor" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
		<img class="bordercolor" src="<?=$arSection["PICTURE"]["SRC"]?>" alt="<?=$arSection["NAME"]?>" width="80" height="80">
		<h3><?=$arSection["NAME"]?></h3>
	</a>	
    </li>
<?endforeach;?>	
</ul>
</div>
<?endif?>
<?//trace($arResult)?>
