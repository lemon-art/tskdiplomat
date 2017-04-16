<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<ul class="links">
<?

foreach($arResult as $i => $arItem):
	if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
		continue;
?>
        <li class="<?=($i == 0)?'first ':''?><?=($i == count($arResult))?'last ':''?><?=(strlen($arItem['PARAMS']['class']) > 0)?$arItem['PARAMS']['class'].' ':'';?>">
            <a href="<?=$arItem["LINK"]?>" title="<?=$arItem["TEXT"]?>"><?=$arItem["TEXT"]?></a>
        </li>
<?endforeach?>
        <?/*global $USER;
                if($USER->IsAuthorized()){?>
        <li class="last"><a href="" title="Выход">Выход</a></li>
                    
              <?  }else{?>
        <li class="last"><a href="" title="Войти">Войти</a></li>
              <?}*/?>
</ul>
<?endif?>