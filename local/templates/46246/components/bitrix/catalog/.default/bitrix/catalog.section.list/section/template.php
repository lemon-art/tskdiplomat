<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="row page-title category-title">
        <h1><?=$arResult['SECTION']['NAME']?></h1>
</div>
  
<?if(count($arResult["SECTIONS"]) > 0 ):?>
<div class="row categories">
        <h2>В разделе:</h2>

<ul class="subcategories">
<?
$col = 0;
$hide = false;
foreach($arResult["SECTIONS"] as $arSection):
    
    if($col%3 == 0 && $col !== 0){?>
        <li class="divider <?=($col >= 3)?"hid":""?>"></li>
    <?}
    $col++;
    ?>
    <li class="span3 <?=($col > 3)?"hid":""?>" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
        <?if(strlen($arSection["PICTURE"]["SRC"]) > 0){?>
	<a class="img" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
            <img class="" src="<?=$arSection["PICTURE"]["SRC"]?>" alt="<?=$arSection["NAME"]?>">
	</a>
        <?}else{?>
        <div class="noimage"></div>
        <?}?>
        <div class="cap">
            <a class="title" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
                <h3><?=$arSection["NAME"]?></h3>
            </a>
            <?if(count($arSection['SUBSECTIONS']) > 0){?>
            <ul>
                <?foreach ($arSection['SUBSECTIONS'] as $arSub){?>
                <li>
                    <a href="<?=$arSub["SECTION_PAGE_URL"]?>" title="<?=$arSub["NAME"]?>">
                        <?=$arSub["NAME"]?>
                    </a>
                </li>    
                <?}?> 
            </ul>    
            <?}?>
        </div>    
    </li>
<?endforeach;?>	
        <?if($col > 3){?>
            <li class="divmore"><span class="on">Больше разделов</span><span class="off">Меньше разделов</span></li>
        <?}?>
</ul>
</div>
<?endif?>
<?if($_REQUEST['D'] == 'Y')trace($arResult)?>
