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
    <?$toolbarTOP = true; include 'toolbar.php'; unset($toolbarTOP);?>
 
<?if($arParams['VIEW_MODE'] !== 'list'):?>    
    <ul class="products-grid row">
        
<?
$i = 0;
foreach ($arResult['ITEMS'] as $arItem):
    //echo '<!-- '.$i.'-->';
    ?>  
        <?if($i % ($arParams["LINE_ELEMENT_COUNT"]) == 0):?>
            </ul><ul  class="products-grid row"> 
        <?endif;?>
        <li class="item col-md-4 col-sm-6">
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
				<?if ($arResult["SHOW_PREVIEW_TEXT"] && $arItem['PREVIEW_TEXT']):?>
					<div class="desc_grid">
						  <?=$arItem['PREVIEW_TEXT']?>
					</div>
				<?endif;?>
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
                        		<? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?><?/*&#8381;*/?>
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
                            <button type="button" title="<?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?>" class="button btn-cart add2cart add2cart_<?=$arItem['ID']?>" data-id="<?=$arItem['ID']?>" rel="nofollow">
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
                            <button type="button" title="<?=GetMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET')?>" class="button btn-cart add2cart add2cart_<?=$arItem['ID']?>" data-id="<?=$arItem['ID']?>" rel="nofollow">
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
        
    </ul>
    <script type="text/javascript">decorateGeneric($$('ul.products-grid'), ['odd', 'even', 'first', 'last'])</script>
    <?$toolbarBOTTOM = true; include 'toolbar.php'; unset($toolbarBOTTOM);?>
</div>
<!--
<?trace($arParams)?>
<?global $arrCatalogFilter;trace($arrCatalogFilter)?>
-->
