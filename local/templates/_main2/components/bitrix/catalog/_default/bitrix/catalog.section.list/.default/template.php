<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
if (count($arResult["SECTIONS"]) > 0):
    ?>
            <?
            $TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
            $CURRENT_DEPTH = $TOP_DEPTH;

            foreach ($arResult["SECTIONS"] as $arSection):

                if ($arSection['DEPTH_LEVEL'] > $TOP_DEPTH + 1)
                    continue;
                ?>

                    
                <div class="page-title category-title">
                    <h1>
                        <a href="<?= $arSection["SECTION_PAGE_URL"] ?>" title="<?= $arSection["NAME"] ?>">
                            <?=$arSection['NAME']?>
                        </a>	
                    </h1>    
                </div>
   
                            <? if (count($arSection['SUBSECTIONS']) > 0) { ?>
                <div class="row categories clearfix">
                    <div class="span12">
                            <ul class="thumbnails">
                                <?
                                foreach ($arSection['SUBSECTIONS'] as $sub) {
                                    $arSub = $arResult['SECTIONS'][$sub];
                                    ?>
                                    <li class="span3">
                                        <a  class="thumbnail" href="<?= $arSub["SECTION_PAGE_URL"] ?>" title="<?= $arSub["NAME"] ?>">
                                            <img class="bordercolor" src="<?= $arSub["DISPLAY_PICTURE"]["src"] ?>" alt="<?= $arSub["NAME"] ?>">
                                            <h3><?= $arSub["NAME"] ?></h3>
                                        </a>	
                                    </li>
                                <? } ?>
                            </ul>
                    </div>
                </div>
                                <? } ?>
                        <?/*
                        <ul class="category-more">
                            <li>
                                <?= $arSection['ELEMENT_CNT'] ?> товаров
                            </li>
                            <li>
                                <a  class="more" href="<?= $arSection["SECTION_PAGE_URL"] ?>" title="<?= $arSection["NAME"] ?>">
                                    в раздел
                                </a>
                            </li> 

                        </ul>
                         * 
                         */?>
    <? endforeach; ?>	
<? endif ?>
<?// trace($arResult) ?>
