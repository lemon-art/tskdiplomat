<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<h1>Акции</h1>

<?if(count($arResult["ITEMS"]) > 0){?>
	<section class="row actions-list">
            <div class="col24-md-24 col24-lg-20" style="margin: 0 auto;">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
                        <article class="row" id="<?=$this->GetEditAreaId($arItem['ID']);?>"  style="margin: 0 auto;">
				<div class="row">
                                    <div class="col24-lg-5 col24-md-5">
					<div class="date">
                                            <span class="figure-calendar"></span>
                                                    <?=  substr($arItem["PROPERTIES"]["DATE_FROM"]['VALUE'],0,5);?>
                                            &nbsp;-&nbsp;
                                                    <?=strtolower($arItem["PROPERTIES"]["DATE_TO"]['VALUE']);?>
                                        </div>
                                    </div>
                                    <div class="col24-lg-12 col24-md-12">
					<h2><?echo $arItem["NAME"]?></h2>
                                    </div>
				</div>
                            <div class="row" style="text-align: center;">
                                        <?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
						<a href="<?=$arItem["DETAIL_PAGE_URL"];?>">
							<div class="img">
                                                            <img src="<?=$arItem['DETAIL_PICTURE']['SRC']?>" alt="<?=$arItem["NAME"]?>">
                                                        </div>
						</a>
                                        <?endif?>
	
                                                        <?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
                                                        <p>
                                                                <?echo $arItem["PREVIEW_TEXT"];?>
                                                        </p>
                                                        <?endif;?>
				</div>
                                    <div class="row">
					<a href="<?=$arItem["DETAIL_PAGE_URL"];?>" class="more pull-left"><i>подробнее</i> <span class="figure-separator"></span></a>
                                    </div>
			<div class="news-list-divider"></div>	
			</article>
<?endforeach;?>
		</div>
	</section>
        
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
    <?=$arResult["NAV_STRING"]?>
<?endif;?>
<?} //end if count items
?>

<?//="<pre>".print_r($arResult,1)."</pre>";?>