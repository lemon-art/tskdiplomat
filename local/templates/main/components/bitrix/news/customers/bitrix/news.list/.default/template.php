<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["ITEMS"])>0):?>
	<ul class="custom-servis-ul">
		<?foreach($arResult["ITEMS"] as $arItem):?>
                    <li class="item-<?=strtolower($arItem['CODE'])?>">
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>">
                                    <h3><?echo $arItem["~NAME"]?></h3>
                                    <p><?=$arItem['~PREVIEW_TEXT']?></p><br/>
                                    <span class="readmore">подробнее...</span>
                                </a>
			<?else:?>
				<h3><?echo $arItem["~NAME"]?></h3>
                                <p><?=$arItem['~PREVIEW_TEXT']?></p>
			<?endif;?>

                    </li>
		<?endforeach;?>
	</ul>
<?endif?>
