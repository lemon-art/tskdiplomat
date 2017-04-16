<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<nav class="wsmenu pm_buttoncolor gry-grdt">
<ul class="mobile-sub wsmenu-list">
<?
$previousLevel = 0;
foreach($arResult as $key => $arItem):?>
	<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
		<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
	<?endif?>
	<?if ($arItem["IS_PARENT"]):?>
		<?if ($arItem["DEPTH_LEVEL"] == 1):?>
			<li  class="level<?=$arItem["DEPTH_LEVEL"]-1?> <?=$key==0?'first ':''?><?=($arItem["SELECTED"])?'active ':''?> parent level-top">
                            <a href="<?=$arItem["LINK"]?>" class="level-top">
                                <span><?=$arItem["TEXT"]?></span>
                            </a>
                            <ul class="wsmenu-submenu">
		<?else:?>
			<li class="level<?=$arItem["DEPTH_LEVEL"]-1?> <?=($arItem["SELECTED"])?'active ':''?> parent">
                            <a href="<?=$arItem["LINK"]?>" class="parent">
                                <span><?=$arItem["TEXT"]?></span>
                            </a>
				<ul class="wsmenu-submenu-sub">
		<?endif?>
	<?else:?>
		<?if ($arItem["PERMISSION"] > "D"):?>
			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="level<?=$arItem["DEPTH_LEVEL"]-1?> <?=($arItem["SELECTED"])?'active ':''?>level-top">
                                    <a href="<?=$arItem["LINK"]?>">
                                        <span><?=$arItem["TEXT"]?></span>
                                    </a>
                                </li>
			<?else:?>
                                <li class="level<?=$arItem["DEPTH_LEVEL"]-1?> <?=($arItem["SELECTED"])?'active ':''?>">
                                    <a href="<?=$arItem["LINK"]?>">
                                        <span><?=$arItem["TEXT"]?></span>
                                    </a>
                                </li>
			<?endif?>
		<?else:?>
			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<li class="<?=($arItem["SELECTED"])?' active':''?> level-top"><a href="" class="<?if ($arItem["SELECTED"]):?>root-item-selected<?else:?>root-item<?endif?>" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?=$arItem["TEXT"]?></a></li>
			<?else:?>
				<li><a href="" class="denied" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?=$arItem["TEXT"]?></a></li>
			<?endif?>
		<?endif?>
	<?endif?>
	<?$previousLevel = $arItem["DEPTH_LEVEL"];?>
<?endforeach?>
<?if ($previousLevel > 1)://close last item tags?>
	<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
<?endif?>
</ul>
</nav>                            
<div class="menu-clear-left"></div>
<?endif?>

<?//trace($arResult)?>