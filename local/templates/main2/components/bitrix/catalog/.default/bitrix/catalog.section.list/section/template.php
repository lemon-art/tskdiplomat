<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="page-title category-title">
	<h1><?$APPLICATION->SetTitle($arResult['SECTION']['NAME']);$APPLICATION->ShowTitle(false);//=$arResult['SECTION']['NAME']?></h1>
</div>
<?/*
<div class="category-description">
    <?if(is_array($arResult['SECTION']['PICTURE'])){?>
    <div class="category-image">
        <img src="<?=$arResult['SECTION']['PICTURE']['src']?>" alt="<?=$arResult['SECTION']['PICTURE']['ALT']?>" />
    </div>
    <?}?>
                    <?if(strlen($arResult['SECTION']['DESCRIPTION']) > 300){?>
                        <div class="cat-text">
                            <div class="cat-text-container">
                                <?=$arResult['SECTION']['DESCRIPTION'];?>
                            </div>
                            <span class="cat-text-opener">
                                <span class="cat-open">Показать больше <i class="fa fa-chevron-down"></i></span>
                                <span class="cat-close">Показать меньше <i class="fa fa-chevron-up"></i></span>
                            </span>
                        </div>
                    <?}else{?>
                        <?=$arResult['SECTION']['DESCRIPTION']?>
                    <?}?>    
</div>
 * 
 */?>  
<?if(count($arResult["SECTIONS"]) > 0 ):?>
<div class="categories">
        <h2>В разделе:</h2>

<ul class="subcategories">
<?
$col = 0;
$hide = false;
foreach($arResult["SECTIONS"] as $arSection):
    
    if($col%3 == 0 && $col !== 0){?>
        <li class="divider <?//=($col >= 3)?"hid":""?>"></li>
    <?}
    $col++;
    ?>
    <li class="col-md-4 <?//=($col > 3)?"hid":""?>" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
        <?if(strlen($arSection["PICTURE"]["SRC"]) > 0){?>
	<a class="img" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
            <img class="" src="<?=$arSection["PICTURE"]["SRC"]?>" alt="<?=$arSection["NAME"]?>">
	</a>
        <?}else{?>
        <div class="noimage"></div>
        <?}?>
        <div class="cap">
            <a class="title" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
                <b><?=$arSection["NAME"]?></b>
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
        <?/*if($col > 3){?>
            <li class="divmore"><span class="on">Больше разделов</span><span class="off">Меньше разделов</span></li>
        <?}*/?>
</ul>
</div>
<?endif?>
<?//if($_REQUEST['D'] == 'Y')trace($arResult)?>
