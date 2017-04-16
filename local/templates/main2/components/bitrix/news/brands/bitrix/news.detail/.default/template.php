<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="row brand-detail">
    <div class="col-md-12 <?=(!empty($arResult['PRODUCT_SECTION']))? 'product-section':''?> ">
    <?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
        <img
            class="detail_picture"
            src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>"
            alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>"
            title="<?=$arResult["DETAIL_PICTURE"]["TITLE"]?>"
        />
    <?endif?>
	<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
        <h1>
            <?
                if($arResult['PROPERTIES']['ZH1']['VALUE']){
                    echo $arResult['PROPERTIES']['ZH1']['VALUE'];
                }else{
                    echo $arResult["NAME"];
                }
            ?>    
        </h1>
	<?endif;?>
        
        <?if(empty($arResult['PRODUCT_SECTION'])):?>
                    <?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arResult["FIELDS"]["PREVIEW_TEXT"]):?>
                            <p><?=$arResult["FIELDS"]["PREVIEW_TEXT"];unset($arResult["FIELDS"]["PREVIEW_TEXT"]);?></p>
                    <?endif;?>
                    <?if($arResult["NAV_RESULT"]):?>
                            <?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>
                            <?echo $arResult["NAV_TEXT"];?>
                            <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
                    <?elseif(strlen($arResult["DETAIL_TEXT"])>0):?>
                            <?echo $arResult["DETAIL_TEXT"];?>
                    <?else:?>
                            <?echo $arResult["PREVIEW_TEXT"];?>
                    <?endif?>
                <!-- AddThis Button BEGIN -->

                <!-- AddThis API Config -->

                <script type='text/javascript'>
                var addthis_product = 'mag-1.0.1';
                var addthis_config 	= {
                pubid : 'unknown'

                }
                </script>
                <!-- AddThis API Config END -->
                <div class="addthis_toolbox addthis_default_style addthis_32x32_style" >
                    <a class="addthis_button_preferred_1"></a>
                    <a class="addthis_button_preferred_2"></a>
                    <a class="addthis_button_preferred_3"></a>
                    <a class="addthis_button_preferred_4"></a>
                    <a class="addthis_button_compact"></a>
                    <a class="addthis_counter addthis_bubble_style"></a>
                </div>  
                <script type="text/javascript" src="https://s7.addthis.com/js/300/addthis_widget.js"></script>
                <!-- AddThis Button END -->
                <style>
                #at3win #at3winheader h3 {
                        text-align:left !important;
                }
                </style>                
                
        <?endif;?>        
                
	<div style="clear:both"></div>
    </div>
</div>
  
<!--<?//trace($arResult)?>-->