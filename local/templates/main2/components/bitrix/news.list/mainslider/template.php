<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?//TODO вынести отсюда подключение скрипта ?>
    <script type="text/javascript">
        /* index slider */
        jQuery(function() {
            jQuery('#camera_wrap').camera({
                alignmen: 'topCenter',
                height: '36.341%',
                minHeight: '134px',
                loader: false,
                pagination: false,
                fx: 'simpleFade',
                navigationHover: false,
                thumbnails: false,
                playPause: false
            });
        });
    </script>    
        <div class="camera_wrap camera_orange_skin" id="camera_wrap">                                
        <?foreach($arResult["ITEMS"] as $arItem):
            if(strlen($arItem['DETAIL_PICTURE']['SRC'])> 0):?>
                <div data-src="<?=$arItem['DETAIL_PICTURE']['SRC']?>">
            <?else:?>
                <div data-src="<?=SITE_TEMPLATE_PATH?>/images/slider_bg.jpg">
            <?endif;?> 

            <?if(!empty($arItem["PREVIEW_PICTURE"])):
                $image = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], array('width'=>400, 'height'=>400), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            ?>
                <div class="right_slider_side fadeIn  camera_effected">
                    <img src="<?=$image["src"]?>" />    
                </div>
            <?endif?>
                <div class="camera_caption fadeFromLeft">
                    <div class="left_slider_side">

                        <div class="caption">
                            <span><?=$arItem["PROPERTIES"]["SUBTITLE"]["VALUE"]?></span> 
                            <strong><?=$arItem["PROPERTIES"]["TITLE"]["VALUE"]?></strong>
                        </div>
                        <a href="<?=$arItem["PROPERTIES"]["LINK"]["VALUE"]?>" class="shop_now_btn">
                            <?=(strlen($arItem["PROPERTIES"]["LINK_TITLE"]["VALUE"])> 0)?$arItem["PROPERTIES"]["LINK_TITLE"]["VALUE"]:GetMessage('MAINSLIDER_DEFAULT_LINK_TITLE')?>
                        </a>
                        <p><?=$arItem["PREVIEW_TEXT"]?></p>
                    </div>
                </div>
            </div>                                        
        <?endforeach;?>
        </div>