<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<ul class="links">
<?

foreach($arResult as $i => $arItem):
	if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
		continue;
?>
        <li<?=($i == 0)?' class="first"':''?>><a href="<?=$arItem["LINK"]?>" title="<?=$arItem["TEXT"]?>" <?=(strlen($arItem['PARAMS']['class']) > 0)?' class="'.$arItem['PARAMS']['class'].'"':'';?>><?=$arItem["TEXT"]?></a></li>

<?endforeach?>
        <?/*global $USER;
                if($USER->IsAuthorized()){?>
        <li class="last"><a href="/?logout=yes" title="Войти на сайт">Войти</a></li>
                    
              <?  }else{?>
        <li class="last"><a href="/auth/" title="Выйти">Выход</a></li>
              <?}*/?>
</ul>
<?endif?>