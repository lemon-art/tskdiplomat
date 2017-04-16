<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
    <div class="page-title">
        <span><a href="/news/"><?=GetMessage('BOX_MAIN_NEWS_TITLE');?></a></span>
    </div>
<div class="box-main-news row">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<div class="news-item col-md-6 col-sm-6 col-lg-3">
            <div class="thumb">
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="preview_picture"><img
						border="0"
						src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
						alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
						title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
						/></a>
			<?else:?>
				<img
					class="preview_picture"
					border="0"
					src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
					alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
					title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
					/>
			<?endif;?>
                                <span class="news-date-time"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span><br>
		<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
                                <span class="news-title">            
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><?echo $arItem["NAME"]?></a>
			<?else:?>
				<?echo $arItem["NAME"]?>
			<?endif;?>
                                </span>
		<?endif;?>
			
		<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
                                <p><?echo $arItem["PREVIEW_TEXT"];?></p>
		<?endif;?>
            </div>                    
	</div>
<?endforeach;?>
</div>