<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
global $APPLICATION;
if (!empty($arResult['ITEMS'])) {
    $i = 0;
    ?>

    <? if ($arParams['BLOCK_TITLE'] !== '-'): ?>
                            <div class="page-title">
                                <?if(strlen($arParams['BLOCK_TITLE_URL']) > 0){?>
                                <h2><a href="<?=$arParams['BLOCK_TITLE_URL']?>"><?= $arParams['BLOCK_TITLE'] ?></a></h2>
                                <?}else{?>
                                <h2><?= $arParams['BLOCK_TITLE'] ?></h2>
                                <?}?>
                            </div>
    <? endif; ?>
                        <ul class="products-grid row">
                            <?foreach ($arResult['ITEMS'] as $arItem): ?>   
                                <?if ($i > 1 && $i % $arParams['LINE_ELEMENT_COUNT'] == 0):?>
                                                            <!--<?=$i?>-->

                                        </ul><ul class="products-grid row">
                                <? endif ?>
                                <li class="item span3">
                                    <h3 class="product-name before-name">
                                        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" title="<?= $arItem['NAME'] ?>">
                                            <?= $arItem['NAME'] ?>
                                        </a>
                                    </h3>
                                    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" title="<?= $arItem['NAME'] ?>" class="product-image">
                                        <img src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $arItem['NAME'] ?>"/>
                                    </a>
                                    <div class="product-shop">
                                        <h3 class="product-name after-name">
                                            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" title="<?= $arItem['NAME'] ?>">
                                                    <?= $arItem['NAME'] ?>
                                            </a>
                                        </h3>
                                        <div class="desc_grid">
                                            <?=$arItem['~PREVIEW_TEXT']?>
                                        </div>                                        
                                        <div class="price-box">
                                            <?
                                            if (!empty($arItem['MIN_PRICE'])) {
                                                if ($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE']) {
                                                    ?>
                                                    <p class="old-price">
                                                        <span class="price">
                <?= $arItem['MIN_PRICE']['PRINT_VALUE']; ?>
                                                        </span>
                                                    </p><br/>
                                                    <p class="special-price">
                                                        <span class="price">
                <?= $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE']; ?>
                                                        </span>
                                                    </p>                            

            <? } else { ?>
                                                    <span class="regular-price">
                                                        <span class="price">
                <? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?>
                                                        </span> 
                                                    </span>
                                                <? }
                                            }
                                            ?>                                                                            

                                        </div>
                                        <div class="actions">
                                            <button type="button" title="<?= GetMessage('TPL_DETAIL_BUTTON_TITLE') ?>" class="button btn-cart" onclick="setLocation('<?= $arItem['DETAIL_PAGE_URL'] ?>')">
                                                <?= GetMessage('TPL_DETAIL_BUTTON_CAPTION') ?>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <?
                                $i++;
                            endforeach;
                            ?>                                                                
                        </ul>
<? } ?>    
