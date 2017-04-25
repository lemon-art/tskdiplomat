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
<?/*todo ????
<script type="text/javascript">
    var optionsPrice = new Product.OptionsPrice([]);
</script>
 * 
 */?>
<div id="messages_product_view"></div>
<div class="product-view">
    <div class="product-essential">
        <form action="" method="post" id="product_addtocart_form">
            <input name="form_key" type="hidden" value="fVFi3XShP8XElDJq" />
            <div class="no-display">
                <input type="hidden" name="product" value="21" />
                <input type="hidden" name="related_product" id="related-products-field" value="" />
            </div>
                  
<?/***************************************************/?>
<div class="product-img-box">
<? if(count($arResult['PICTURES']) > 3):?>    
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('.tumbSlider').jcarousel({
                vertical: false,
                visible:3,
                scroll: 1
            });
    });
</script>
<?endif?>
<style type="text/css">
.cloud-zoom-big {width:420px !important; height:420px !important;  }
</style>


<p class="product-image">
    <a href='<?=$arResult['PICTURES'][0]['SRC']?>' class='cloud-zoom' id='zoom1' rel="position:'right',showTitle:0,titleOpacity:0.5,lensOpacity:0.5,adjustX: 10,adjustY:-4,zoomWidth:420,zoomHeight:420">
        <?/*
	<img class="small" src="<?=$arResult['PICTURES'][0]['THUMBNAIL']['src']?>" alt='' title="" />
        */?>
        <img class="big" src="<?=$arResult['PICTURES'][0]['PREVIEW']['src']?>" alt='' title="" />
    </a>
</p>
<?if(count($arResult['PICTURES'])>1):?>
	 <div class="more-views">
        <h2></h2>
	<div class="container-slider">
		<div class="tumbSlider<?=(count($arResult['PICTURES'])<=3)?'-none':''?>">
			<ul class="slider">
                            <?foreach ($arResult['PICTURES'] as $key => $arImage):?>
				<li>
					<a href='<?=$arImage['SRC']?>' class='cloud-zoom-gallery' title=''
					rel="useZoom: 'zoom1', smallImage: '<?=$arImage['PREVIEW']['src']?>' ">
					<img src="<?=$arImage['THUMBNAIL']['src']?>" alt=""/>
					</a>
				</li>
                             <?endforeach?>   
                        </ul>
		</div>
	</div>
    </div>
<?endif;?>
</div>
<?/*==================================================*/?>                    
            <div class="product-shop">
                <div class="product-name">
                    <h1><?=$arResult['NAME']?></h1>
                    <?if(isset($arResult['BRAND'])):?>
                    <div class="brand">
                    <a href="<?=$arResult['BRAND']['DETAIL_PAGE_URL']?>" title="<?=$arResult['BRAND']['NAME']?>">
                        <?if(is_array($arResult['BRAND']['PICTURE'])):?>
                            <img src="<?=$arResult['BRAND']['PICTURE']['SRC']?>" alt="<?=$arResult['BRAND']['PICTURE']['ALT']?>" title="<?=$arResult['BRAND']['PICTURE']['TITLE']?>" />
                        <?else:?>
                            <?=$arResult['BRAND']['NAME'];?>
                        <?endif;?>    
                    </a>    
                    </div>    
                    <?endif;?>
                </div>
                <?if(count($arResult['GENERAL_PROPERTIES'])> 0):?>
                <div class="product-general-props">
                    <ul>
<?foreach ($arResult['GENERAL_PROPERTIES'] as $key => $arProp):?>
                        <li class="<?=strtolower($key)?>">
                            <span class="name">
                            <?=(strlen(GetMessage('TPL_PRODUCT_GP_'.$key))> 0)?GetMessage('TPL_PRODUCT_GP_'.$key):$arProp['NAME'];?>
                            </span>    
                            <span class="value"><?=$arProp['DISPLAY_VALUE'];?></span>
                        </li>    

<?endforeach;?>
                    </ul>
                </div>    
               <? endif;?>
<?/*todo 
<!--             <p class="availability in-stock">Availability: <span>In stock</span></p>
                -->
*/?>


<?
$minPrice = (isset($arResult['RATIO_PRICE']) ? $arResult['RATIO_PRICE'] : $arResult['MIN_PRICE']);
$boolDiscountShow = (0 < $minPrice['DISCOUNT_DIFF']);
?>
                <div class="price-box">
                    <?if(0 < $minPrice['DISCOUNT_DIFF']):?>
                        <span class="old-price">
                            <span class="price"><?=$minPrice['PRINT_VALUE']?></span>                                    
                        </span><br/>
                        <span class="special-price">
                            <span class="price"><?=$minPrice['PRINT_DISCOUNT_VALUE']?></span>                                    
                        </span><br/>
                        <span class="special-desc">
                            <?=GetMessage('CT_BCE_CATALOG_ECONOMY_INFO', array('#ECONOMY#' => $minPrice['PRINT_DISCOUNT_DIFF'])); ?>
                        </span> 
                    <?else:?>
                        <span class="regular-price">
                            <span class="price"><?=$minPrice['PRINT_DISCOUNT_VALUE']?></span>                                    
                        </span>
                    <?endif;?>
                </div>
                <?if($arResult['CATALOG_MEASURE'] > 0):?>
                    <div class="price-box-measure">
                        <span class="measure measureBx">
                            за <?=$arResult['CATALOG_MEASURE_RATIO']?> 
                            <?=$arResult['CATALOG_MEASURE_NAME']?> 
                        </span>
                    </div>    
                <?endif?>
                                <? if ($arResult['PROPERTIES']['TOORDER']['VALUE_XML_ID'] == 'TOORDER_YES'): ?>
                                    <span class="on_sale">НА ЗАКАЗ!</span>
                                <? else: ?>
                                    <span class="on_sale">В НАЛИЧИИ!</span>
                                <? endif; ?>
                <div class="clear"></div>
                <div class="short-description">
                    <h2>Описание</h2>
                    <div class="std">
                        <?=$arResult['PREVIEW_TEXT']?>
                    </div>
                </div>
                <?/* todo
                <div class="availability-only">
                    Only                10000000                left            
                </div>
                <p class="availability in-stock">Availability: <span>In stock</span></p>
                */?>
                <div class="clear"></div>

                <div class="add-to-box">
                    <div class="add-to-cart">
                        <div class="qty-block">
                            <label for="qty">Количество:</label>
                            <input type="text" name="qty" id="add2cart_qty" maxlength="12" value="1" title="Qty" class="input-text qty" />
                        </div>
                        <?/*
                        <button type="button" title="В корзину" class="button btn-cart add2cart" onclick="productAddToCartForm.submit(this)"><span><span>В корзину</span></span></button>
                         * 
                         */?>
                        <button type="button" title="В корзину" class="button btn-cart add2cart add2cart_<?=$arResult['ID']?>" data-id="<?=$arResult['ID']?>">В корзину</button>
                    </div>
                    <span class="or">или</span>


                    <ul class="add-to-links">
                        <li>
                            <a href="javascript:void(0);" class="link-wishlist add2wishlist" data-id="<?=$arResult['ID']?>">
                                <?=GetMessage('TPL_WISHLIST_LINK')?>
                            </a>
                        </li>
                        <li>
                            <span class="separator">|</span> 
                            <a href="<?=$arResult['COMPARE_URL']?>" class="link-compare add2compare" data-id="<?=$arResult['ID']?>" rel="nofollow"><?=GetMessage('CT_BCE_CATALOG_COMPARE');?></a></li>
                    </ul>
                </div>

                <div class="row-product">
                    <p class="no-rating">
                        <a href="">Оставьте свой отзыв</a></p>

                    <p class="email-friend">
                        <a href="">Послать ссылку другу</a>
                    </p>
                </div>
                <!-- Check whether the plugin is enabled -->

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
            </div>
            <div class="clearer"></div>
        </form>
        <?/*
        <script type="text/javascript">
        //<![CDATA[
            var productAddToCartForm = new VarienForm('product_addtocart_form');
            productAddToCartForm.submit = function(button, url) {
                if (this.validator.validate()) {
                    var form = this.form;
                    var oldUrl = form.action;

                    if (url) {
                        form.action = url;
                    }
                    var e = null;
                    try {
                        this.form.submit();
                    } catch (e) {
                    }
                    this.form.action = oldUrl;
                    if (e) {
                        throw e;
                    }

                    if (button && button != 'undefined') {
                        button.disabled = true;
                    }
                }
            }.bind(productAddToCartForm);

            productAddToCartForm.submitLight = function(button, url) {
                if (this.validator) {
                    var nv = Validation.methods;
                    delete Validation.methods['required-entry'];
                    delete Validation.methods['validate-one-required'];
                    delete Validation.methods['validate-one-required-by-name'];
                    // Remove custom datetime validators
                    for (var methodName in Validation.methods) {
                        if (methodName.match(/^validate-datetime-.* /i)) {
                            delete Validation.methods[methodName];
                        }
                    }

                    if (this.validator.validate()) {
                        if (url) {
                            this.form.action = url;
                        }
                        this.form.submit();
                    }
                    Object.extend(Validation.methods, nv);
                }
            }.bind(productAddToCartForm);
            //]]>
        </script>
        */?>
    </div>

    <div class="product-collateral">
        <div class="box-collateral box-description">
            <h2>Подробно</h2>
            <div class="box-collateral-content">
                <div class="std">
                    <?if(strlen($arResult['~DETAIL_TEXT']) > 300){?>
                        <div class="cat-text">
                            <div class="cat-text-container">
                                <?=$arResult['DETAIL_TEXT'];?>
                            </div>
                            <span class="cat-text-opener">
                                <span class="cat-open">Показать больше <i class="fa fa-chevron-down"></i></span>
                                <span class="cat-close">Показать меньше <i class="fa fa-chevron-up"></i></span>
                            </span>
                        </div>
                    <?}else{?>
                        <?=$arResult['~DETAIL_TEXT']?>
                    <?}?>
                </div>
            </div>
        </div>
        <?if(!empty($arResult['DISPLAY_PROPERTIES']['FILES'])){
            $arPropertyFiles = $arResult['DISPLAY_PROPERTIES']['FILES'];
            unset($arResult['DISPLAY_PROPERTIES']['FILES']);
        }?>
        <?if(!empty($arResult['DISPLAY_PROPERTIES'])){?>
        <div class="box-collateral box-description">
            <h2>Характеристики</h2>
            <div class="box-collateral-content">
                <div class="std">
                    <ul class="product-properties">
                    <?foreach($arResult['DISPLAY_PROPERTIES'] as $key => $arProperty):?>
                        <li>
                        <span class="name">
                                <?=(strlen(GetMessage('TPL_PROPERTY_NAME_'.$key)) > 0)?GetMessage('TPL_PROPERTY_NAME_'.$key):$arProperty['NAME'];?>
                        </span>
                        <span class="value">
                                <?if(is_array($arProperty['DISPLAY_VALUE'])):?>
                                        <?=implode(' / ',$arProperty['DISPLAY_VALUE'])?>
                                <?else:?>
                                        <?=$arProperty['DISPLAY_VALUE']?>
                                <?endif;?>
                        </span>
                        </li>
                    <?endforeach;?>
                    </ul>    
                </div>
            </div>
        </div>
        <?}?>
        <?if(is_array($arPropertyFiles)):?>
        <div class="box-collateral box-description">
            <h2>Файлы</h2>
            <div class="box-collateral-content">
                <div class="std">
                    <ul class="product-files">
                    <?foreach ($arPropertyFiles['FILE_VALUE'] as $arFile):
                            $type = explode('/',$arFile['CONTENT_TYPE']);
                        ?>
                        <li>

                            <a target="_blanc" class="file-ico <?=$type[1]?>" href="<?=$arFile['SRC']?>" title="<?=GetMessage('TPL_PRODUCT_FILE_LINK_TITLE', array('#NAME#' => $arFile['DESCRIPTION'].' '.$arFile['ORIGINAL_NAME']))?>">
                            <?if(strlen($arFile['DESCRIPTION']) > 0):?>
                                <?=$arFile['DESCRIPTION']?><br/>
                            <?endif;?>
                               <?=$arFile['ORIGINAL_NAME']?> 
                            <br/>
                            (<?=CFile::FormatSize($arFile['FILE_SIZE']);?>)
                            </a>
                        </li>
                    <?endforeach;?>
                    </ul>    
                </div>
            </div>
        </div>     
        <?endif;?>
<?
//global $USER;
//if ($USER->IsAdmin()):;

if(count($arResult["PROPERTIES"]["SOPUT_PRODUCTS"]["VALUE"]) > 0){
    global $arrRelatedFilter;
    $arrRelatedFilter = array('ID' => $arResult["PROPERTIES"]["SOPUT_PRODUCTS"]["VALUE"]);

?>
        <div class="box-collateral box-description related-products">
            <h2>Сопутствующие товары</h2>
            <div class="box-collateral-content">
                <div class="std">
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.top", 
	"related_products", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"SORT_FIELD" => "sort",
		"SORT_ORDER" => "rand",
		"HIDE_NOT_AVAILABLE" => "N",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"ELEMENT_COUNT" => "5",
		"LINE_ELEMENT_COUNT" => "5",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CONVERT_CURRENCY" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"ELEMENT_SORT_FIELD" => "RAND",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_SORT_FIELD2" => "sort",
		"ELEMENT_SORT_ORDER2" => "asc",
		"FILTER_NAME" => "arrRelatedFilter",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFERS_LIMIT" => "5",
		"TEMPLATE_THEME" => "blue",
		"PRODUCT_DISPLAY_MODE" => "N",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"PRODUCT_SUBSCRIPTION" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "N",
		"SHOW_CLOSE_POPUP" => "N",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_SUBSCRIBE" => "Подписаться",
		"MESS_BTN_COMPARE" => "Сравнить",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"SECTION_URL" => "",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"PRODUCT_QUANTITY_VARIABLE" => "",
		"SEF_MODE" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_FILTER" => "Y",
		"CURRENCY_ID" => "RUB",
		"USE_PRODUCT_QUANTITY" => "N",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(
		),
		"OFFERS_CART_PROPERTIES" => array(
		),
		"ADD_TO_BASKET_ACTION" => "ADD",
		"DISPLAY_COMPARE" => "N"
	),
	false
);?> 
                </div>
            </div>
        </div>
    <?
    }
    //endif;?>    
        <?
                    global $arCalcFilter;

                    $arCalcFilter = array(
                        'ID' => $arResult['ID'],
                        '!PROPERTY_CALC_CONSUM' => false
                    );
//trace($arResult);
                    ?>
                    <div class="calculator">
                        <?
                        $APPLICATION->IncludeComponent(
                            "fcm:calculator", "", array(
                            "IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'],
                            "IBLOCK_ID" => $arParams['IBLOCK_ID'],
                            "FILTER_NAME" => "arCalcFilter",
                            "CACHE_TYPE" => "A",
                            "CACHE_TIME" => "360000",
                            "CACHE_GROUPS" => "Y",
                            "TITLE" => 'Калькулятор расхода материала'
                            ), 
                            $component
                        );
                        ?>    
                    </div>    
                    <br/>        
        <?/*todo to future
<!--
        <div class="box-collateral box-tags">
            <h2>Product Tags</h2>
            <div class="box-collateral-content">
                <form id="addTagForm" action="http://livedemo00.template-help.com/magento_47672/tag/index/save/product/21/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93L214LXByb3RlY3RpdmUtZ2Vhci10aG9yLW14LXJpZGluZy1nZWFyLXRob3ItbXgtZ29nZ2xlcy5odG1s/" method="get">
                    <div class="form-add">
                        <label for="productTagName">Add Your Tags:</label>
                        <div class="input-box">
                            <input type="text" class="input-text required-entry" name="productTagName" id="productTagName" />
                        </div>
                        <button type="button" title="Add Tags" class="button" onclick="submitTagForm()">
                            <span>
                                <span>Add Tags</span>
                            </span>
                        </button>
                    </div>
                </form>
                <p class="note">Use spaces to separate tags. Use single quotes (') for phrases.</p>

                <script type="text/javascript">
                    //<![CDATA[
                    var addTagFormJs = new VarienForm('addTagForm');
                    function submitTagForm() {
                        if (addTagFormJs.validator.validate()) {
                            addTagFormJs.form.submit();
                        }
                    }
                    //]]>
                </script>
            </div>
        </div>
-->
         * 
         */?>
    </div>
</div>

<?/* todo to future
<!--
<div id="map-popup" class="map-popup" style="display:none;">
    <a href="#" class="map-popup-close" id="map-popup-close">x</a>
    <div class="map-popup-arrow"></div>
    <div class="map-popup-heading"><h2 id="map-popup-heading"></h2></div>
    <div class="map-popup-content" id="map-popup-content">
        <div class="map-popup-msrp" id="map-popup-msrp-box"><strong>Price:</strong> <span style="text-decoration:line-through;" id="map-popup-msrp"></span></div>
        <div class="map-popup-price" id="map-popup-price-box"><strong>Actual Price:</strong> <span id="map-popup-price"></span></div>
        <div class="map-popup-checkout">
            <form action="" method="POST" id="product_addtocart_form_from_popup">
                <input type="hidden" name="product" class="product_id" value="" id="map-popup-product-id" />
                <div class="additional-addtocart-box">
                </div>
                <button type="button" title="Add to Cart" class="button btn-cart" id="map-popup-button"><span><span>Add to Cart</span></span></button>
            </form>
        </div>
        <script type="text/javascript">
            //<![CDATA[
            document.observe("dom:loaded", Catalog.Map.bindProductForm);
            //]]>
        </script>
    </div>
    <div class="map-popup-text" id="map-popup-text">Our price is lower than the manufacturer's &quot;minimum advertised price.&quot;  As a result, we cannot show you the price in catalog or the product page. <br /><br /> You have no obligation to purchase the product once you know the price. You can simply remove the item from your cart.</div>
    <div class="map-popup-text" id="map-popup-text-what-this">Our price is lower than the manufacturer's &quot;minimum advertised price.&quot;  As a result, we cannot show you the price in catalog or the product page. <br /><br /> You have no obligation to purchase the product once you know the price. You can simply remove the item from your cart.</div>
</div>
-->
 * 
 */?>
<?/*
<script type="text/javascript">
    var lifetime = 3600;
    var expireAt = Mage.Cookies.expires;
    if (lifetime > 0) {
        expireAt = new Date();
        expireAt.setTime(expireAt.getTime() + lifetime * 1000);
    }
    Mage.Cookies.set('external_no_cache', 1, expireAt);
</script>
*/?>
<?if($_REQUEST['D'] == 'Y'):?>
<!--
<?trace($arResult)?>
-->
<?endif;?>
<script type="application/ld+json">
{
  "@context": "http://schema.org/",
  "@type": "Product",
  "name": "<?=$arResult['NAME'];?>",
  "image": "<?=$arResult['DETAIL_PICTURE']['SRC'];?>",
  "description": "<?=  htmlspecialchars($arResult['DETAIL_TEXT']);?>",
  "brand": {
    "@type": "Thing",
    "name": "<?=$arResult['BRAND']['NAME'];?>"
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "RUB",
    "price": "<?=$arResult['MIN_PRICE']['VALUE']?>",
    "itemCondition": "http://schema.org/NewCondition",
    "availability": "http://schema.org/InStock",
    "seller": {
      "@type": "Organization",
      "name": "ООО ТСК Дипломат"
    }
  }
}
</script>