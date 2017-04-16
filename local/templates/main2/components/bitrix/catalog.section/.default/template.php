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
    
<div class="category-products">
    <?include 'toolbar.php';?>
 
<?if($arParams['VIEW_MODE'] !== 'list'):?>    
    <ul class="products-grid row">
        
<?
$i = 0;
foreach ($arResult['ITEMS'] as $arItem):
    ?>  
        <?if($i % ($arParams["LINE_ELEMENT_COUNT"]) == 0):?>
            </ul><ul  class="products-grid row"> 
        <?endif;?>
        <li class="item col-md-4">
            <span class="product-name before-name">
                  <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
            </span>
            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>" class="product-image">
                  <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" />
            </a>
                <div class="product-shop">
                <span class="product-name after-name">
                      <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                </span>
                <div class="desc_grid">
                      <?=$arItem['~PREVIEW_TEXT']?>
                </div>
                <div class="price-box">
<?
	if (!empty($arItem['MIN_PRICE']))
	{
		if ($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE'])
		{?>
                            <p class="old-price">
                                       <span class="price">
                                		<?=$arItem['MIN_PRICE']['PRINT_VALUE'];?>
                                       </span>
                            </p><br/>
                            <p class="special-price">
                                         <span class="price">
                                                <?=$arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?>
                                         </span>
                           </p>                            

              <?}else{?>
                            <span class="regular-price">
                                <span class="price">
                        		<? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?>
                                </span> 
                            </span>
              <?}
              
        }?>                            
                        </div>
                        <?/*
                         * TODO на будущее
                    
                    <div class="ratings">
                        <div class="rating-box">
                            <div class="rating" style="width:93%"></div>
                        </div>
                        <span class="amount"><a href="#" onclick="var t = opener ? opener.window : window; t.location.href='http://livedemo00.template-help.com/magento_47672/review/product/list/id/1/category/4/'; return false;">1 Review(s)</a></span>
                    </div>
                         * 
                         */?>

                    <div class="actions">
                        <?//if($arItem['CAN_BUY']):?>
                            <button type="button" title="<?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?>" class="button btn-cart add2cart add2cart_<?=$arItem['ID']?>" data-id="<?=$arItem['ID']?>" onclick="setLocation('<?=$arItem['BUY_LINK']; ?>')"  rel="nofollow">
                                    <?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?>
                            </button>
                        <?//endif;?>
                        <?/*
                        <button type="button" title="<?=GetMessage("CT_BCS_TPL_MESS_BTN_DETAIL")?>" class="button btn-cart details" onclick="setLocation('<?=$arItem['DETAIL_PAGE_URL']?>')">
                            <span>
                                <span><?=GetMessage("CT_BCS_TPL_MESS_BTN_DETAIL")?></span>
                            </span>
                        </button>
                         * 
                         */?>

                        <?/*
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/product/1/form_key/VQrTx9ZHq7OSSChG/')">
                            <span><span>Add to Cart</span></span>
                        </button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/7pc-genuine-buffalo-leather-motorcycle-luggage-set.html')">
                            <span><span>Details</span></span>
                        </button>
                         * 
                         */?>
                        <?/*
                         * TODO на будущее
                         
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/1/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/1/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                         * 
                         */?>
                    </div>
                    <ul class="add-to-links">
                            <li><span title="В избранное" class="link-wishlist add2wishlist" data-id="<?=$arItem['ID']?>">В избранное</span></li>
                            <li><span class="separator">|</span> <span  title="Сравнить" rel="" class="link-compare add2compare" data-id="<?=$arItem['ID']?>">Сравнить</span></li>
                    </ul>

                </div>
            <?/*
                         * TODO на будущее
                <div class="label-product">             
                </div>
             * 
             */?>
        </li>
<?
    $i++;
endforeach;?>        
<?else: //view mode?>
    <ol class="products-list" id="products-list">
<?foreach ($arResult['ITEMS'] as $arItem):?>        
        <li class="item">
            <div class="hover-class">
                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>" class="product-image">
                    <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" />
                </a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <span class="product-name">
                                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                            </span>
                            <div class="desc std">
                                <?=$arItem['~PREVIEW_TEXT']?>
                                <!-- <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>" class="link-learn">Learn More</a> -->
                            </div>
                            <div class="price-box">
 <div class="price-box">
<?
	if (!empty($arItem['MIN_PRICE']))
	{
		if ($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE'])
		{?>
                            <p class="old-price">
                                  <span class="price-label">Обычная цена:</span>
                                       <span class="price">
                                		<?=$arItem['MIN_PRICE']['PRINT_VALUE'];?>
                                       </span>
                            </p><br/>
                            <p class="special-price">
                                   <span class="price-label">Специальная цена:</span>
                                         <span class="price">
                                                <?=$arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?>
                                         </span>
                           </p>                            

              <?}else{?>
                            <span class="regular-price">
                                <span class="price">
                        		<? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?>
                                </span> 
                            </span>
              <?}
              
        }?>                            
                        </div>
                                <?/*
                                <span class="regular-price" id="product-price-19">
                                    <span class="price">$55.65</span>
                                </span>
*/?>
                            </div>

                        </div>
                        
                        <?//if($arItem['CAN_BUY']):?>
                            <button type="button" title="<?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?>" class="button btn-cart" onclick="setLocation('<?=$arItem['BUY_LINK']; ?>')"  rel="nofollow">
                                <span>
                                    <span><?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?></span>
                                </span>
                            </button>
                        <?//endif;?>
                        <button type="button" title="<?=GetMessage("CT_BCS_TPL_MESS_BTN_DETAIL")?>" class="button btn-cart details" onclick="setLocation('<?=$arItem['DETAIL_PAGE_URL']?>')">
                            <span>
                                <span><?=GetMessage("CT_BCS_TPL_MESS_BTN_DETAIL")?></span>
                            </span>
                        </button>
            <?/*            
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/product/19/form_key/UxSkQNhTh6WRGOX1/')">
                            <span>
                                <span>Add to Cart</span>
                            </span>
                        </button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/cycra-probend-classic-enduro-shields.html')"><span><span>Details</span></span></button>
             * 
             */?>
                        <?/*todo
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/19/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/19/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                         */?>
                         
                    </div>
                </div>
                <?/* todo
                <div class="label-product"><span class="sale">Sale</span></div>
                 * 
                 */?>
                <div class="clear"></div>
            </div>
        </li>
<?endforeach;?>

    <?endif?>        
        
     
        <?/*
        <li class="item col-xs-12 col-sm-4">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-4-pc-studded-motorcycle-luggage-set.html" title="Diamond Plate 4 pc Studded Motorcycle Luggage Set" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/228x/9df78eab33525d08d6e5fb8d27136e95/d/i/diamond_plate_4_pc_studded_motorcycle_luggage_set_1.png" alt="Diamond Plate 4 pc Studded Motorcycle Luggage Set" /></a>
                <div class="product-shop">
                    <div class="wrapper-hover">
                        <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-4-pc-studded-motorcycle-luggage-set.html" title="Diamond Plate 4 pc Studded Motorcycle Luggage Set">Diamond Plate 4 pc Studded Motorcycle Luggage Set</a></h2>
                        <div class="desc_grid">
                            We have a wonderful support service which is available for you 7 days ...                                                </div>

                        <div class="price-box map-info">
                            <a href="#" id="msrp-click-2noCyqZeElYEvAab5IDga">Click for price</a>
                            <script type="text/javascript">
                            var newLink = Catalog.Map.addHelpLink(
                                    $('msrp-click-2noCyqZeElYEvAab5IDga'),
                                    "Diamond Plate 4 pc Studded Motorcycle Luggage Set",
                                    "\n\n                \n    <div class=\"price-box\">\n                                \n                    <p class=\"old-price\">\n                <span class=\"price-label\">Regular Price:<\/span>\n                <span class=\"price\" id=\"old-price-2\">\n                    $35.95                <\/span>\n            <\/p>\n\n                        <p class=\"special-price\">\n                <span class=\"price-label\">Special Price<\/span>\n                <span class=\"price\" id=\"product-price-2\">\n                    $30.95                <\/span>\n            <\/p>\n                    \n    \n        <\/div>\n\n",
                                    '',
                                    "http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-4-pc-studded-motorcycle-luggage-set.html"
                                    );
                            newLink.product_id = '2';

                            </script>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-4-pc-studded-motorcycle-luggage-set.html')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-4-pc-studded-motorcycle-luggage-set.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/2/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/2/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear"></div>
            </div>
        </li>
        <li class="item last col-xs-12 col-sm-4">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-solid-genuine-leather-skull-cap-with-flames.html" title="Diamond Plate Solid Genuine Leather Skull Cap with Flames" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/228x/9df78eab33525d08d6e5fb8d27136e95/d/i/diamond_plate_solid_genuine_leather_skull_cap_with_flames_2.png" alt="Diamond Plate Solid Genuine Leather Skull Cap with Flames" /></a>
                <div class="product-shop">
                    <div class="wrapper-hover">
                        <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-solid-genuine-leather-skull-cap-with-flames.html" title="Diamond Plate Solid Genuine Leather Skull Cap with Flames">Diamond Plate Solid Genuine Leather Skull Cap with Flames</a></h2>
                        <div class="desc_grid">
                            The reason of our success is obvious and really simple – we take care ...                                                </div>



                        <div class="price-box">
                            <span class="regular-price" id="product-price-3">
                                <span class="price">$45.65</span>                                    </span>

                        </div>

                    </div>

                    <div class="actions">
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/product/3/form_key/VQrTx9ZHq7OSSChG/')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/diamond-plate-solid-genuine-leather-skull-cap-with-flames.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/3/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/3/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear"></div>
            </div>
        </li>
         * 
         */?>
    </ul>
    <?/*
    <ul class="products-grid row">
        <li class="item first col-xs-12 col-sm-4">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/cruiser/icon-super-duty-4-boots.html" title="Icon Super Duty 4 Boots" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/228x/9df78eab33525d08d6e5fb8d27136e95/i/c/icon_super_duty_4_boots_1.png" alt="Icon Super Duty 4 Boots" /></a>
                <div class="product-shop">
                    <div class="wrapper-hover">
                        <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/cruiser/icon-super-duty-4-boots.html" title="Icon Super Duty 4 Boots">Icon Super Duty 4 Boots</a></h2>
                        <div class="desc_grid">
                            Here at the Motorcycle store we can guarantee a full satisfaction of a...                                                </div>



                        <div class="price-box">

                            <p class="old-price">
                                <span class="price-label">Regular Price:</span>
                                <span class="price" id="old-price-4">
                                    $50.00                </span>
                            </p>

                            <p class="special-price">
                                <span class="price-label">Special Price</span>
                                <span class="price" id="product-price-4">
                                    $40.00                </span>
                            </p>


                        </div>

                    </div>

                    <div class="actions">
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/icon-super-duty-4-boots.html')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/icon-super-duty-4-boots.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/4/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/4/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear"></div>
            </div>
        </li>
        <li class="item col-xs-12 col-sm-4">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/cruiser/shoei-rj-platinum-r-open-face-helmet.html" title="Shoei RJ Platinum-R Open Face Helmet" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/228x/9df78eab33525d08d6e5fb8d27136e95/s/h/shoei_rj_platinum-r_open_face_helmet_1.png" alt="Shoei RJ Platinum-R Open Face Helmet" /></a>
                <div class="product-shop">
                    <div class="wrapper-hover">
                        <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/cruiser/shoei-rj-platinum-r-open-face-helmet.html" title="Shoei RJ Platinum-R Open Face Helmet">Shoei RJ Platinum-R Open Face Helmet</a></h2>
                        <div class="desc_grid">
                            We have a wonderful support service which is available for you 7 days ...                                                </div>



                        <div class="price-box">

                            <p class="old-price">
                                <span class="price-label">Regular Price:</span>
                                <span class="price" id="old-price-5">
                                    $39.99                </span>
                            </p>

                            <p class="special-price">
                                <span class="price-label">Special Price</span>
                                <span class="price" id="product-price-5">
                                    $30.00                </span>
                            </p>


                        </div>

                    </div>

                    <div class="actions">
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/product/5/form_key/VQrTx9ZHq7OSSChG/')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/shoei-rj-platinum-r-open-face-helmet.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/5/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/5/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear"></div>
            </div>
        </li>
        <li class="item last col-xs-12 col-sm-4">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/cruiser/zeyner-backfire-leather-backpack.html" title="Zeyner Backfire Leather Backpack" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/228x/9df78eab33525d08d6e5fb8d27136e95/z/e/zeyner_backfire_leather_backpack_2.png" alt="Zeyner Backfire Leather Backpack" /></a>
                <div class="product-shop">
                    <div class="wrapper-hover">
                        <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/cruiser/zeyner-backfire-leather-backpack.html" title="Zeyner Backfire Leather Backpack">Zeyner Backfire Leather Backpack</a></h2>
                        <div class="desc_grid">
                            The reason of our success is obvious and really simple – we take care ...                                                </div>



                        <div class="price-box">
                            <span class="regular-price" id="product-price-6">
                                <span class="price">$59.69</span>                                    </span>

                        </div>

                    </div>

                    <div class="actions">
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/zeyner-backfire-leather-backpack.html')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/cruiser/zeyner-backfire-leather-backpack.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/6/form_key/VQrTx9ZHq7OSSChG/" rel="tooltip" class="link-wishlist">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a  title="Add to Compare " href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/6/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9jcnVpc2VyLmh0bWw,/" rel="tooltip" class="link-compare ">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear"></div>
            </div>
        </li>
    </ul>
     * 
     */?>
    <script type="text/javascript">decorateGeneric($$('ul.products-grid'), ['odd', 'even', 'first', 'last'])</script>
    <?include 'toolbar.php';?>
<?/*
    <div class="toolbar-bottom">
        <div class="toolbar">
            <div class="pager">
                <p class="amount">
                    <strong>6 Item(s)</strong>
                </p>

                <div class="limiter">
                    <label>Show</label>
                    <select onchange="setLocation(this.value)">
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?limit=9" selected="selected">
                            9                </option>
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?limit=15">
                            15                </option>
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?limit=30">
                            30                </option>
                    </select>         </div>








            </div>

            <div class="sorter">
                <p class="view-mode">
                    <label>View as:</label>
                    <strong title="Grid" class="grid">Grid</strong>&nbsp;
                    <a href="http://livedemo00.template-help.com/magento_47672/cruiser.html?mode=list" title="List" class="list">List</a>&nbsp;
                </p>

                <div class="sort-by">
                    <div class="right">
                                                                    <a class="icon-arrow-up" href="http://livedemo00.template-help.com/magento_47672/cruiser.html?dir=desc&amp;order=position" title="Set Descending Direction"><!-- <img src="http://livedemo00.template-help.com/magento_47672/skin/frontend/default/theme571/images/i_asc_arrow.gif" alt="Set Descending Direction" class="v-middle" /> --></a>
                    </div>
                    <label>Sort By</label>
                    <select onchange="setLocation(this.value)">
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?dir=asc&amp;order=position" selected="selected">
                            Position                </option>
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?dir=asc&amp;order=name">
                            Name                </option>
                        <option value="http://livedemo00.template-help.com/magento_47672/cruiser.html?dir=asc&amp;order=price">
                            Price                </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
*/?>
</div>

<?

/*
if (!empty($arResult['ITEMS']))
{
	$templateLibrary = array('popup');
	$currencyList = '';
	if (!empty($arResult['CURRENCIES']))
	{
		$templateLibrary[] = 'currency';
		$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
	}
	$templateData = array(
		'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css',
		'TEMPLATE_CLASS' => 'bx_'.$arParams['TEMPLATE_THEME'],
		'TEMPLATE_LIBRARY' => $templateLibrary,
		'CURRENCIES' => $currencyList
	);
	unset($currencyList, $templateLibrary);

	$arSkuTemplate = array();
	if (!empty($arResult['SKU_PROPS']))
	{
		foreach ($arResult['SKU_PROPS'] as &$arProp)
		{
			$templateRow = '';
			if ('TEXT' == $arProp['SHOW_MODE'])
			{
				if (5 < $arProp['VALUES_COUNT'])
				{
					$strClass = 'bx_item_detail_size full';
					$strWidth = ($arProp['VALUES_COUNT']*20).'%';
					$strOneWidth = (100/$arProp['VALUES_COUNT']).'%';
					$strSlideStyle = '';
				}
				else
				{
					$strClass = 'bx_item_detail_size';
					$strWidth = '100%';
					$strOneWidth = '20%';
					$strSlideStyle = 'display: none;';
				}
				$templateRow .= '<div class="'.$strClass.'" id="#ITEM#_prop_'.$arProp['ID'].'_cont">'.
'<span class="bx_item_section_name_gray">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
'<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" style="width: '.$strWidth.';">';
				foreach ($arProp['VALUES'] as $arOneValue)
				{
					$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
					$templateRow .= '<li data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-onevalue="'.$arOneValue['ID'].'" style="width: '.$strOneWidth.';" title="'.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
				}
				$templateRow .= '</ul></div>'.
'<div class="bx_slide_left" id="#ITEM#_prop_'.$arProp['ID'].'_left" data-treevalue="'.$arProp['ID'].'" style="'.$strSlideStyle.'"></div>'.
'<div class="bx_slide_right" id="#ITEM#_prop_'.$arProp['ID'].'_right" data-treevalue="'.$arProp['ID'].'" style="'.$strSlideStyle.'"></div>'.
'</div></div>';
			}
			elseif ('PICT' == $arProp['SHOW_MODE'])
			{
				if (5 < $arProp['VALUES_COUNT'])
				{
					$strClass = 'bx_item_detail_scu full';
					$strWidth = ($arProp['VALUES_COUNT']*20).'%';
					$strOneWidth = (100/$arProp['VALUES_COUNT']).'%';
					$strSlideStyle = '';
				}
				else
				{
					$strClass = 'bx_item_detail_scu';
					$strWidth = '100%';
					$strOneWidth = '20%';
					$strSlideStyle = 'display: none;';
				}
				$templateRow .= '<div class="'.$strClass.'" id="#ITEM#_prop_'.$arProp['ID'].'_cont">'.
'<span class="bx_item_section_name_gray">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
'<div class="bx_scu_scroller_container"><div class="bx_scu"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" style="width: '.$strWidth.';">';
				foreach ($arProp['VALUES'] as $arOneValue)
				{
					$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
					$templateRow .= '<li data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-onevalue="'.$arOneValue['ID'].'" style="width: '.$strOneWidth.'; padding-top: '.$strOneWidth.';"><i title="'.$arOneValue['NAME'].'"></i>'.
'<span class="cnt"><span class="cnt_item" style="background-image:url(\''.$arOneValue['PICT']['SRC'].'\');" title="'.$arOneValue['NAME'].'"></span></span></li>';
				}
				$templateRow .= '</ul></div>'.
'<div class="bx_slide_left" id="#ITEM#_prop_'.$arProp['ID'].'_left" data-treevalue="'.$arProp['ID'].'" style="'.$strSlideStyle.'"></div>'.
'<div class="bx_slide_right" id="#ITEM#_prop_'.$arProp['ID'].'_right" data-treevalue="'.$arProp['ID'].'" style="'.$strSlideStyle.'"></div>'.
'</div></div>';
			}
			$arSkuTemplate[$arProp['CODE']] = $templateRow;
		}
		unset($templateRow, $arProp);
	}

	if ($arParams["DISPLAY_TOP_PAGER"])
	{
		?><? echo $arResult["NAV_STRING"]; ?><?
	}

	$strElementEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
	$strElementDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
	$arElementDeleteParams = array("CONFIRM" => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));
?><div class="bx_catalog_list_home col<? echo $arParams['LINE_ELEMENT_COUNT']; ?> <? echo $templateData['TEMPLATE_CLASS']; ?>"><?
foreach ($arResult['ITEMS'] as $key => $arItem)
{
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
	$strMainID = $this->GetEditAreaId($arItem['ID']);

	$arItemIDs = array(
		'ID' => $strMainID,
		'PICT' => $strMainID.'_pict',
		'SECOND_PICT' => $strMainID.'_secondpict',
		'STICKER_ID' => $strMainID.'_sticker',
		'SECOND_STICKER_ID' => $strMainID.'_secondsticker',
		'QUANTITY' => $strMainID.'_quantity',
		'QUANTITY_DOWN' => $strMainID.'_quant_down',
		'QUANTITY_UP' => $strMainID.'_quant_up',
		'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
		'BUY_LINK' => $strMainID.'_buy_link',
		'BASKET_ACTIONS' => $strMainID.'_basket_actions',
		'NOT_AVAILABLE_MESS' => $strMainID.'_not_avail',
		'SUBSCRIBE_LINK' => $strMainID.'_subscribe',
		'COMPARE_LINK' => $strMainID.'_compare_link',

		'PRICE' => $strMainID.'_price',
		'DSC_PERC' => $strMainID.'_dsc_perc',
		'SECOND_DSC_PERC' => $strMainID.'_second_dsc_perc',
		'PROP_DIV' => $strMainID.'_sku_tree',
		'PROP' => $strMainID.'_prop_',
		'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
		'BASKET_PROP_DIV' => $strMainID.'_basket_prop',
	);

	$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);

	$productTitle = (
		isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])&& $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
		? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
		: $arItem['NAME']
	);
	$imgTitle = (
		isset($arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
		? $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
		: $arItem['NAME']
	);

	$minPrice = false;
	if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
		$minPrice = (isset($arItem['RATIO_PRICE']) ? $arItem['RATIO_PRICE'] : $arItem['MIN_PRICE']);

	?><div class="<? echo ($arItem['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>"><div class="bx_catalog_item_container" id="<? echo $strMainID; ?>">
		<a id="<? echo $arItemIDs['PICT']; ?>" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_catalog_item_images" style="background-image: url('<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>')" title="<? echo $imgTitle; ?>"><?
	if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
	{
	?>
			<div id="<? echo $arItemIDs['DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $minPrice['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $minPrice['DISCOUNT_DIFF_PERCENT']; ?>%</div>
	<?
	}
	if ($arItem['LABEL'])
	{
	?>
			<div id="<? echo $arItemIDs['STICKER_ID']; ?>" class="bx_stick average left top" title="<? echo $arItem['LABEL_VALUE']; ?>"><? echo $arItem['LABEL_VALUE']; ?></div>
	<?
	}
	?>
		</a><?
	if ($arItem['SECOND_PICT'])
	{
		?><a id="<? echo $arItemIDs['SECOND_PICT']; ?>" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_catalog_item_images_double" style="background-image: url('<? echo (
				!empty($arItem['PREVIEW_PICTURE_SECOND'])
				? $arItem['PREVIEW_PICTURE_SECOND']['SRC']
				: $arItem['PREVIEW_PICTURE']['SRC']
			); ?>');" title="<? echo $imgTitle; ?>"><?
		if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
		{
		?>
			<div id="<? echo $arItemIDs['SECOND_DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $minPrice['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $minPrice['DISCOUNT_DIFF_PERCENT']; ?>%</div>
		<?
		}
		if ($arItem['LABEL'])
		{
		?>
			<div id="<? echo $arItemIDs['SECOND_STICKER_ID']; ?>" class="bx_stick average left top" title="<? echo $arItem['LABEL_VALUE']; ?>"><? echo $arItem['LABEL_VALUE']; ?></div>
		<?
		}
		?>
		</a><?
	}
	?><div class="bx_catalog_item_title"><a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" title="<? echo $productTitle; ?>"><? echo $productTitle; ?></a></div>
	<div class="bx_catalog_item_price"><div id="<? echo $arItemIDs['PRICE']; ?>" class="bx_price"><?
	if (!empty($minPrice))
	{
		if ('N' == $arParams['PRODUCT_DISPLAY_MODE'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
		{
			echo GetMessage(
				'CT_BCS_TPL_MESS_PRICE_SIMPLE_MODE',
				array(
					'#PRICE#' => $minPrice['PRINT_DISCOUNT_VALUE'],
					'#MEASURE#' => GetMessage(
						'CT_BCS_TPL_MESS_MEASURE_SIMPLE_MODE',
						array(
							'#VALUE#' => $minPrice['CATALOG_MEASURE_RATIO'],
							'#UNIT#' => $minPrice['CATALOG_MEASURE_NAME']
						)
					)
				)
			);
		}
		else
		{
			echo $minPrice['PRINT_DISCOUNT_VALUE'];
		}
		if ('Y' == $arParams['SHOW_OLD_PRICE'] && $minPrice['DISCOUNT_VALUE'] < $minPrice['VALUE'])
		{
			?> <span><? echo $minPrice['PRINT_VALUE']; ?></span><?
		}
	}
	unset($minPrice);
	?></div></div><?
	$showSubscribeBtn = false;
	$compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCS_TPL_MESS_BTN_COMPARE'));
	if (!isset($arItem['OFFERS']) || empty($arItem['OFFERS']))
	{
		?><div class="bx_catalog_item_controls"><?
		if ($arItem['CAN_BUY'])
		{
			if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
			{
			?>
		<div class="bx_catalog_item_controls_blockone"><div style="display: inline-block;position: relative;">
			<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
			<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
			<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
			<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"><? echo $arItem['CATALOG_MEASURE_NAME']; ?></span>
		</div></div>
			<?
			}
			?>
		<div id="<? echo $arItemIDs['BASKET_ACTIONS']; ?>" class="bx_catalog_item_controls_blocktwo">
			<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow"><?
			if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
			{
				echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCS_TPL_MESS_BTN_BUY'));
			}
			else
			{
				echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET'));
			}
			?></a>
		</div>
			<?
			if ($arParams['DISPLAY_COMPARE'])
			{
				?>
				<div class="bx_catalog_item_controls_blocktwo">
					<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
				</div><?
			}
		}
		else
		{
			?><div id="<? echo $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="bx_catalog_item_controls_blockone"><span class="bx_notavailable"><?
			echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
			?></span></div><?
			if ($arParams['DISPLAY_COMPARE'] || $showSubscribeBtn)
			{
			?>
				<div class="bx_catalog_item_controls_blocktwo"><?
				if ($arParams['DISPLAY_COMPARE'])
				{
					?><a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a><?
				}
				if ($showSubscribeBtn)
				{
				?>
				<a id="<? echo $arItemIDs['SUBSCRIBE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><?
					echo ('' != $arParams['MESS_BTN_SUBSCRIBE'] ? $arParams['MESS_BTN_SUBSCRIBE'] : GetMessage('CT_BCS_TPL_MESS_BTN_SUBSCRIBE'));
					?></a><?
				}
				?>
			</div><?
			}
		}
		?><div style="clear: both;"></div></div><?
		if (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']))
		{
?>
			<div class="bx_catalog_item_articul">
<?
			foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp)
			{
				?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
					echo (
						is_array($arOneProp['DISPLAY_VALUE'])
						? implode('<br>', $arOneProp['DISPLAY_VALUE'])
						: $arOneProp['DISPLAY_VALUE']
					);
			}
?>
			</div>
<?
		}
		$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
		if ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET'] && !$emptyProductProperties)
		{
?>
		<div id="<? echo $arItemIDs['BASKET_PROP_DIV']; ?>" style="display: none;">
<?
			if (!empty($arItem['PRODUCT_PROPERTIES_FILL']))
			{
				foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo)
				{
?>
					<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
<?
					if (isset($arItem['PRODUCT_PROPERTIES'][$propID]))
						unset($arItem['PRODUCT_PROPERTIES'][$propID]);
				}
			}
			$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
			if (!$emptyProductProperties)
			{
?>
				<table>
<?
					foreach ($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo)
					{
?>
						<tr><td><? echo $arItem['PROPERTIES'][$propID]['NAME']; ?></td>
							<td>
<?
								if(
									'L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE']
									&& 'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']
								)
								{
									foreach($propInfo['VALUES'] as $valueID => $value)
									{
										?><label><input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?></label><br><?
									}
								}
								else
								{
									?><select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]"><?
									foreach($propInfo['VALUES'] as $valueID => $value)
									{
										?><option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? 'selected' : ''); ?>><? echo $value; ?></option><?
									}
									?></select><?
								}
?>
							</td></tr>
<?
					}
?>
				</table>
<?
			}
?>
		</div>
<?
		}
		$arJSParams = array(
			'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
			'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
			'SHOW_ADD_BASKET_BTN' => false,
			'SHOW_BUY_BTN' => true,
			'SHOW_ABSENT' => true,
			'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
			'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
			'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
			'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
			'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
			'PRODUCT' => array(
				'ID' => $arItem['ID'],
				'NAME' => $productTitle,
				'PICT' => ('Y' == $arItem['SECOND_PICT'] ? $arItem['PREVIEW_PICTURE_SECOND'] : $arItem['PREVIEW_PICTURE']),
				'CAN_BUY' => $arItem["CAN_BUY"],
				'SUBSCRIPTION' => ('Y' == $arItem['CATALOG_SUBSCRIPTION']),
				'CHECK_QUANTITY' => $arItem['CHECK_QUANTITY'],
				'MAX_QUANTITY' => $arItem['CATALOG_QUANTITY'],
				'STEP_QUANTITY' => $arItem['CATALOG_MEASURE_RATIO'],
				'QUANTITY_FLOAT' => is_double($arItem['CATALOG_MEASURE_RATIO']),
				'SUBSCRIBE_URL' => $arItem['~SUBSCRIBE_URL'],
				'BASIS_PRICE' => $arItem['MIN_BASIS_PRICE']
			),
			'BASKET' => array(
				'ADD_PROPS' => ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET']),
				'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
				'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
				'EMPTY_PROPS' => $emptyProductProperties,
				'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
				'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
			),
			'VISUAL' => array(
				'ID' => $arItemIDs['ID'],
				'PICT_ID' => ('Y' == $arItem['SECOND_PICT'] ? $arItemIDs['SECOND_PICT'] : $arItemIDs['PICT']),
				'QUANTITY_ID' => $arItemIDs['QUANTITY'],
				'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
				'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
				'PRICE_ID' => $arItemIDs['PRICE'],
				'BUY_ID' => $arItemIDs['BUY_LINK'],
				'BASKET_PROP_DIV' => $arItemIDs['BASKET_PROP_DIV'],
				'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
				'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
				'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
			),
			'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
		);
		if ($arParams['DISPLAY_COMPARE'])
		{
			$arJSParams['COMPARE'] = array(
				'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
				'COMPARE_PATH' => $arParams['COMPARE_PATH']
			);
		}
		unset($emptyProductProperties);
?><script type="text/javascript">
var <? echo $strObName; ?> = new JCCatalogSection(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script><?
	}
	else
	{
		if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
		{
			$canBuy = $arItem['JS_OFFERS'][$arItem['OFFERS_SELECTED']]['CAN_BUY'];
			?>
		<div class="bx_catalog_item_controls no_touch">
			<?
			if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
			{
			?>
		<div class="bx_catalog_item_controls_blockone">
			<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
			<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
			<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
			<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"></span>
		</div>
			<?
			}
			?>
		<div id="<? echo $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="bx_catalog_item_controls_blockone" style="display: <? echo ($canBuy ? 'none' : ''); ?>;"><span class="bx_notavailable"><?
			echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
		?></span></div>
		<div id="<? echo $arItemIDs['BASKET_ACTIONS']; ?>" class="bx_catalog_item_controls_blocktwo" style="display: <? echo ($canBuy ? '' : 'none'); ?>;">
			<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow"><?
			if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
			{
				echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCS_TPL_MESS_BTN_BUY'));
			}
			else
			{
				echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET'));
			}
			?></a>
		</div>
		<?
	if ($arParams['DISPLAY_COMPARE'])
	{
	?>
<div class="bx_catalog_item_controls_blocktwo">
	<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
</div><?
	}
	?>
				<div style="clear: both;"></div>
			</div>
			<?
			unset($canBuy);
		}
		else
		{
			?>
		<div class="bx_catalog_item_controls no_touch">
			<a class="bx_bt_button_type_2 bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
			echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCS_TPL_MESS_BTN_DETAIL'));
			?></a>
		</div>
			<?
		}
		?>
		<div class="bx_catalog_item_controls touch">
			<a class="bx_bt_button_type_2 bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
			echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCS_TPL_MESS_BTN_DETAIL'));
			?></a>
		</div>
		<?
		$boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $arItem['OFFERS_PROPS_DISPLAY']);
		$boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
		if ($boolShowProductProps || $boolShowOfferProps)
		{
?>
			<div class="bx_catalog_item_articul">
<?
			if ($boolShowProductProps)
			{
				foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp)
				{
				?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
					echo (
						is_array($arOneProp['DISPLAY_VALUE'])
						? implode(' / ', $arOneProp['DISPLAY_VALUE'])
						: $arOneProp['DISPLAY_VALUE']
					);
				}
			}
			if ($boolShowOfferProps)
			{
?>
				<span id="<? echo $arItemIDs['DISPLAY_PROP_DIV']; ?>" style="display: none;"></span>
<?
			}
?>
			</div>
<?
		}
		if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
		{
			if (!empty($arItem['OFFERS_PROP']))
			{
				$arSkuProps = array();
				?><div class="bx_catalog_item_scu" id="<? echo $arItemIDs['PROP_DIV']; ?>"><?
				foreach ($arSkuTemplate as $code => $strTemplate)
				{
					if (!isset($arItem['OFFERS_PROP'][$code]))
						continue;
					echo '<div>', str_replace('#ITEM#_prop_', $arItemIDs['PROP'], $strTemplate), '</div>';
				}
				foreach ($arResult['SKU_PROPS'] as $arOneProp)
				{
					if (!isset($arItem['OFFERS_PROP'][$arOneProp['CODE']]))
						continue;
					$arSkuProps[] = array(
						'ID' => $arOneProp['ID'],
						'SHOW_MODE' => $arOneProp['SHOW_MODE'],
						'VALUES_COUNT' => $arOneProp['VALUES_COUNT']
					);
				}
				foreach ($arItem['JS_OFFERS'] as &$arOneJs)
				{
					if (0 < $arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'])
					{
						$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
						$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
					}
				}
				unset($arOneJs);
				?></div><?
				if ($arItem['OFFERS_PROPS_DISPLAY'])
				{
					foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer)
					{
						$strProps = '';
						if (!empty($arJSOffer['DISPLAY_PROPERTIES']))
						{
							foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp)
							{
								$strProps .= '<br>'.$arOneProp['NAME'].' <strong>'.(
									is_array($arOneProp['VALUE'])
									? implode(' / ', $arOneProp['VALUE'])
									: $arOneProp['VALUE']
								).'</strong>';
							}
						}
						$arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
					}
				}
				$arJSParams = array(
					'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
					'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
					'SHOW_ADD_BASKET_BTN' => false,
					'SHOW_BUY_BTN' => true,
					'SHOW_ABSENT' => true,
					'SHOW_SKU_PROPS' => $arItem['OFFERS_PROPS_DISPLAY'],
					'SECOND_PICT' => $arItem['SECOND_PICT'],
					'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
					'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
					'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
					'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
					'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
					'DEFAULT_PICTURE' => array(
						'PICTURE' => $arItem['PRODUCT_PREVIEW'],
						'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
					),
					'VISUAL' => array(
						'ID' => $arItemIDs['ID'],
						'PICT_ID' => $arItemIDs['PICT'],
						'SECOND_PICT_ID' => $arItemIDs['SECOND_PICT'],
						'QUANTITY_ID' => $arItemIDs['QUANTITY'],
						'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
						'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
						'QUANTITY_MEASURE' => $arItemIDs['QUANTITY_MEASURE'],
						'PRICE_ID' => $arItemIDs['PRICE'],
						'TREE_ID' => $arItemIDs['PROP_DIV'],
						'TREE_ITEM_ID' => $arItemIDs['PROP'],
						'BUY_ID' => $arItemIDs['BUY_LINK'],
						'ADD_BASKET_ID' => $arItemIDs['ADD_BASKET_ID'],
						'DSC_PERC' => $arItemIDs['DSC_PERC'],
						'SECOND_DSC_PERC' => $arItemIDs['SECOND_DSC_PERC'],
						'DISPLAY_PROP_DIV' => $arItemIDs['DISPLAY_PROP_DIV'],
						'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
						'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
						'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
					),
					'BASKET' => array(
						'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
						'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
						'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
						'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
						'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
					),
					'PRODUCT' => array(
						'ID' => $arItem['ID'],
						'NAME' => $productTitle
					),
					'OFFERS' => $arItem['JS_OFFERS'],
					'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
					'TREE_PROPS' => $arSkuProps,
					'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
				);
				if ($arParams['DISPLAY_COMPARE'])
				{
					$arJSParams['COMPARE'] = array(
						'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
						'COMPARE_PATH' => $arParams['COMPARE_PATH']
					);
				}
				?>
<script type="text/javascript">
var <? echo $strObName; ?> = new JCCatalogSection(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
				<?
			}
		}
		else
		{
			$arJSParams = array(
				'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
				'SHOW_QUANTITY' => false,
				'SHOW_ADD_BASKET_BTN' => false,
				'SHOW_BUY_BTN' => false,
				'SHOW_ABSENT' => false,
				'SHOW_SKU_PROPS' => false,
				'SECOND_PICT' => $arItem['SECOND_PICT'],
				'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
				'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
				'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
				'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
				'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
				'DEFAULT_PICTURE' => array(
					'PICTURE' => $arItem['PRODUCT_PREVIEW'],
					'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
				),
				'VISUAL' => array(
					'ID' => $arItemIDs['ID'],
					'PICT_ID' => $arItemIDs['PICT'],
					'SECOND_PICT_ID' => $arItemIDs['SECOND_PICT'],
					'QUANTITY_ID' => $arItemIDs['QUANTITY'],
					'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
					'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
					'QUANTITY_MEASURE' => $arItemIDs['QUANTITY_MEASURE'],
					'PRICE_ID' => $arItemIDs['PRICE'],
					'TREE_ID' => $arItemIDs['PROP_DIV'],
					'TREE_ITEM_ID' => $arItemIDs['PROP'],
					'BUY_ID' => $arItemIDs['BUY_LINK'],
					'ADD_BASKET_ID' => $arItemIDs['ADD_BASKET_ID'],
					'DSC_PERC' => $arItemIDs['DSC_PERC'],
					'SECOND_DSC_PERC' => $arItemIDs['SECOND_DSC_PERC'],
					'DISPLAY_PROP_DIV' => $arItemIDs['DISPLAY_PROP_DIV'],
					'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
					'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
					'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
				),
				'BASKET' => array(
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
					'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
					'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
					'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
				),
				'PRODUCT' => array(
					'ID' => $arItem['ID'],
					'NAME' => $productTitle
				),
				'OFFERS' => array(),
				'OFFER_SELECTED' => 0,
				'TREE_PROPS' => array(),
				'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
			);
			if ($arParams['DISPLAY_COMPARE'])
			{
				$arJSParams['COMPARE'] = array(
					'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
					'COMPARE_PATH' => $arParams['COMPARE_PATH']
				);
			}
?>
<script type="text/javascript">
var <? echo $strObName; ?> = new JCCatalogSection(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
		}
	}
?></div></div><?
}
?><div style="clear: both;"></div>
</div>
<script type="text/javascript">
BX.message({
	BTN_MESSAGE_BASKET_REDIRECT: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_BASKET_REDIRECT'); ?>',
	BASKET_URL: '<? echo $arParams["BASKET_URL"]; ?>',
	ADD_TO_BASKET_OK: '<? echo GetMessageJS('ADD_TO_BASKET_OK'); ?>',
	TITLE_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_TITLE_ERROR') ?>',
	TITLE_BASKET_PROPS: '<? echo GetMessageJS('CT_BCS_CATALOG_TITLE_BASKET_PROPS') ?>',
	TITLE_SUCCESSFUL: '<? echo GetMessageJS('ADD_TO_BASKET_OK'); ?>',
	BASKET_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
	BTN_MESSAGE_SEND_PROPS: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_SEND_PROPS'); ?>',
	BTN_MESSAGE_CLOSE: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE') ?>',
	BTN_MESSAGE_CLOSE_POPUP: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE_POPUP'); ?>',
	COMPARE_MESSAGE_OK: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_OK') ?>',
	COMPARE_UNKNOWN_ERROR: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_UNKNOWN_ERROR') ?>',
	COMPARE_TITLE: '<? echo GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_TITLE') ?>',
	BTN_MESSAGE_COMPARE_REDIRECT: '<? echo GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT') ?>',
	SITE_ID: '<? echo SITE_ID; ?>'
});
</script>
<?
	if ($arParams["DISPLAY_BOTTOM_PAGER"])
	{
		?><? echo $arResult["NAV_STRING"]; ?><?
	}
}
 * 
 */?>
<?
//trace($arParams);
//trace($arResult);
?>