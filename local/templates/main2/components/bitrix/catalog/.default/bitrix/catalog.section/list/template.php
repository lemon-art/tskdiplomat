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
                                <?=$arItem['PREVIEW_TEXT']?>
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
                <div class="label-product"></div>
                <div class="clear"></div>
            </div>
        </li>
<?endforeach;?>
   <?/*     
        <li class="item">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/snow/cycra-series-one-handguards.html" title="Cycra Series One Handguards" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/210x/9df78eab33525d08d6e5fb8d27136e95/c/y/cycra_series_one_handguards_3.png" alt="Cycra Series One Handguards" /></a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/snow/cycra-series-one-handguards.html" title="Cycra Series One Handguards">Cycra Series One Handguards</a></h2>
                            <div class="desc std">
                                We have a <strong>wonderful support</strong> service which is available for you <strong>7 days in week</strong>, 24 hours a day. All you have to do is just submit a <i>ticket</i> or call us and you’ll receive <strong>professional support</strong> immediately. Of course motorcycling is not just a <i>hobby</i> or <i>business</i>. This is a <i>life style</i> with its own unique <i>philosophy</i> and original world view.                                <!-- <a href="http://livedemo00.template-help.com/magento_47672/snow/cycra-series-one-handguards.html" title="Cycra Series One Handguards" class="link-learn">Learn More</a> -->
                            </div>



                            <div class="price-box">
                                <span class="regular-price" id="product-price-20">
                                    <span class="price">$25.95</span>                                    </span>

                            </div>

                        </div>
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/cycra-series-one-handguards.html')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/cycra-series-one-handguards.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/20/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/20/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear
                     "></div>
            </div>

        </li>
        <li class="item">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/snow/mx-protective-gear-thor-mx-riding-gear-thor-mx-goggles.html" title="Mx Protective Gear Thor Mx Riding Gear Thor Mx Goggles" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/210x/9df78eab33525d08d6e5fb8d27136e95/m/x/mx_protective_gear_thor_mx_riding_gear_thor_mx_goggles_3.png" alt="Mx Protective Gear Thor Mx Riding Gear Thor Mx Goggles" /></a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/snow/mx-protective-gear-thor-mx-riding-gear-thor-mx-goggles.html" title="Mx Protective Gear Thor Mx Riding Gear Thor Mx Goggles">Mx Protective Gear Thor Mx Riding Gear Thor Mx Goggles</a></h2>
                            <div class="desc std">
                                The reason of <i>our success</i> is obvious and really simple – we take care about each and every one of our clients and treat then with same respect and attention as we did <strong>10 years</strong> ago. Of course it doesn’t matter whether you are rookie with your first <strong>Yamaha</strong>, or an experienced <strong>road</strong> wolf with a solid <i>reputation</i> in the biker community.                                 <!-- <a href="http://livedemo00.template-help.com/magento_47672/snow/mx-protective-gear-thor-mx-riding-gear-thor-mx-goggles.html" title="Mx Protective Gear Thor Mx Riding Gear Thor Mx Goggles" class="link-learn">Learn More</a> -->
                            </div>



                            <div class="price-box">
                                <span class="regular-price" id="product-price-21">
                                    <span class="price">$25.95</span>                                    </span>

                            </div>

                        </div>
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/product/21/form_key/UxSkQNhTh6WRGOX1/')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/mx-protective-gear-thor-mx-riding-gear-thor-mx-goggles.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/21/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/21/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear
                     "></div>
            </div>

        </li>
        <li class="item">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-force-knee-guard.html" title="Thor Force Knee Guard" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/210x/9df78eab33525d08d6e5fb8d27136e95/t/h/thor_force_knee_guard_1.png" alt="Thor Force Knee Guard" /></a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/snow/thor-force-knee-guard.html" title="Thor Force Knee Guard">Thor Force Knee Guard</a></h2>
                            <div class="desc std">
                                Here at the <strong>Motorcycle store</strong> we can guarantee a full <i>satisfaction</i> of any customer that would visit our <strong>online</strong> shop. It is hard to find more reliable customer <strong>service</strong> than the one that we have.                                <!-- <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-force-knee-guard.html" title="Thor Force Knee Guard" class="link-learn">Learn More</a> -->
                            </div>



                            <div class="price-box">
                                <span class="regular-price" id="product-price-22">
                                    <span class="price">$45.97</span>                                    </span>

                            </div>

                        </div>
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/thor-force-knee-guard.html')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/thor-force-knee-guard.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/22/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/22/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear
                     "></div>
            </div>

        </li>
        <li class="item">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-motocross-quadrant-belt.html" title="Thor Motocross Quadrant Belt" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/210x/9df78eab33525d08d6e5fb8d27136e95/t/h/thor_motocross_quadrant_belt_2.png" alt="Thor Motocross Quadrant Belt" /></a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/snow/thor-motocross-quadrant-belt.html" title="Thor Motocross Quadrant Belt">Thor Motocross Quadrant Belt</a></h2>
                            <div class="desc std">
                                We have a <strong>wonderful support</strong> service which is available for you <strong>7 days in week</strong>, 24 hours a day. All you have to do is just submit a <i>ticket</i> or call us and you’ll receive <strong>professional support</strong> immediately. Of course motorcycling is not just a <i>hobby</i> or <i>business</i>. This is a <i>life style</i> with its own unique <i>philosophy</i> and original world view.                                <!-- <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-motocross-quadrant-belt.html" title="Thor Motocross Quadrant Belt" class="link-learn">Learn More</a> -->
                            </div>



                            <div class="price-box">
                                <span class="regular-price" id="product-price-23">
                                    <span class="price">$26.96</span>                                    </span>

                            </div>

                        </div>
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/product/23/form_key/UxSkQNhTh6WRGOX1/')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/thor-motocross-quadrant-belt.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/23/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/23/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear
                     "></div>
            </div>

        </li>
        <li class="item last">
            <div class="hover-class">
                <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-mx-enemy-goggles.html" title="Thor Mx Enemy Goggles" class="product-image"><img src="http://livedemo00.template-help.com/magento_47672/media/catalog/product/cache/1/small_image/210x/9df78eab33525d08d6e5fb8d27136e95/t/h/thor_mx_enemy_goggles_2.png" alt="Thor Mx Enemy Goggles" /></a>
                <div class="product-shop">
                    <div class="f-fix">
                        <div class="wrapper-hover">
                            <h2 class="product-name"><a href="http://livedemo00.template-help.com/magento_47672/snow/thor-mx-enemy-goggles.html" title="Thor Mx Enemy Goggles">Thor Mx Enemy Goggles</a></h2>
                            <div class="desc std">
                                The reason of <i>our success</i> is obvious and really simple – we take care about each and every one of our clients and treat then with same respect and attention as we did <strong>10 years</strong> ago. Of course it doesn’t matter whether you are rookie with your first <strong>Yamaha</strong>, or an experienced <strong>road</strong> wolf with a solid <i>reputation</i> in the biker community.                                 <!-- <a href="http://livedemo00.template-help.com/magento_47672/snow/thor-mx-enemy-goggles.html" title="Thor Mx Enemy Goggles" class="link-learn">Learn More</a> -->
                            </div>



                            <div class="price-box">
                                <span class="regular-price" id="product-price-24">
                                    <span class="price">$31.28</span>                                    </span>

                            </div>

                        </div>
                        <button type="button" title="Add to Cart" class="button btn-cart" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/checkout/cart/add/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/product/24/form_key/UxSkQNhTh6WRGOX1/')"><span><span>Add to Cart</span></span></button>
                        <button type="button" title="Details" class="button btn-cart details" onclick="setLocation('http://livedemo00.template-help.com/magento_47672/snow/thor-mx-enemy-goggles.html')"><span><span>Details</span></span></button>
                        <ul class="add-to-links">
                            <li><a title="Add to Wishlist" href="http://livedemo00.template-help.com/magento_47672/wishlist/index/add/product/24/form_key/UxSkQNhTh6WRGOX1/" class="link-wishlist tooltips">Add to Wishlist</a></li>
                            <li><span class="separator">|</span> <a title="Add to Compare" href="http://livedemo00.template-help.com/magento_47672/catalog/product_compare/add/product/24/uenc/aHR0cDovL2xpdmVkZW1vMDAudGVtcGxhdGUtaGVscC5jb20vbWFnZW50b180NzY3Mi9zbm93Lmh0bWw_bW9kZT1saXN0/" class="link-compare tooltips">Add to Compare</a></li>
                        </ul>
                    </div>
                </div>
                <div class="label-product">             
                </div>
                <div class="clear
                     "></div>
            </div>

        </li>
    * 
    */?>
    </ol>
    <script type="text/javascript">decorateList('products-list', 'none-recursive')</script>
    
    <?include 'toolbar.php';?>
</div>

<?//=trace($arResult)?>