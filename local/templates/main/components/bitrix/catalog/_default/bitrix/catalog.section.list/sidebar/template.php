<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>
<?
if (count($arResult["SECTIONS"]) > 0):
    ?>
<div class="block block-layered-nav first">
    <div class="block-title">
        <strong><span>Каталог</span></strong>
        <span class="toggle"></span></div>
        <div class="block-content">
            <?
            $TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
            $CURRENT_DEPTH = $TOP_DEPTH;

            foreach ($arResult["SECTIONS"] as $arSection):

                if ($arSection['DEPTH_LEVEL'] > $TOP_DEPTH + 1)
                    continue;
                ?>
            <dl id="narrow-by-list2">
            <dt class="last odd"><?=$arSection['NAME']?></dt>
                <dd class="last odd">
   
            <? if (count($arSection['SUBSECTIONS']) > 0) { ?>
                            <ol>
                                <?
                                foreach ($arSection['SUBSECTIONS'] as $sub) {
                                    $arSub = $arResult['SECTIONS'][$sub];
                                    ?>
                                    <li>
                                        <a href="<?= $arSub["SECTION_PAGE_URL"] ?>" title="<?= $arSub["NAME"] ?>">
                                            <?= $arSub["NAME"] ?>
                                        </a>	
                                    </li>
                                <? } ?>
                            </ol>
            <? } ?>
                </dd>
            </dl>
    <? endforeach; ?>	
            
        <script type="text/javascript">decorateDataList('narrow-by-list2')</script>
    </div>
</div>
<? endif ?>
<?// trace($arResult) ?>
