<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);

if (!empty($arResult['ITEMS'])): 
if (count($arResult['ITEMS']) > 3) 
      {
        $carous='related-carousel';
        } else {  $carous='related-carousel-none';
  }
?>  
<div class="box-collateral box-up-sell  <?php echo $carous;?>">
    <h2><?=GetMessage('CATALOG_RECOMMENDED_PRODUCTS_BLOCK_TITLE')?></h2>
    <ul class="products-ups">
        <?foreach ($arResult['ITEMS'] as $arItem):?> 
        <li class="item">
            <div class="product-box">
                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>" class="product-image">
                    <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" /></a>
                <div class="noSwipe">
                    <h3 class="product-name">
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>"><?=$arItem['NAME']?></a>
                    </h3>

                    <div class="price-box">
                        <?if($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE'] ):?>
                            <span class="old-price">
                                <span class="price"><?=$arItem['MIN_PRICE']['PRINT_VALUE']?></span>                                    
                            </span><br/>
                            <span class="special-price">
                                <span class="price"><?=$arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE']?></span>                                    
                            </span>
                        <?else:?>
                            <span class="regular-price" id="product-price-<?=$arItem['ID']?>-upsell">
                                <span class="price"><?=$arItem['MIN_PRICE']['PRINT_VALUE']?></span>                                    
                            </span>
                        <?endif;?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </li>
        <?endforeach;?>
    </ul>
    <script type="text/javascript">decorateTable('upsell-product-table')</script>
</div>


<? endif ?>