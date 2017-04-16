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
<div class="row factory-detail">
    <?if(strlen($arResult['PREVIEW_PICTURE']['SRC']) > 0){?>
    <div class="col-md-2 col-sm-4">
        <img class="factory-logo" 
             src="<?=$arResult['PREVIEW_PICTURE']['SRC']?>" 
             alt="<?=$arResult['PREVIEW_PICTURE']['ALT']?>" 
             title="<?=$arResult['PREVIEW_PICTURE']['TITLE']?>" 
             
             />
    </div>
    <div class="col-md-10 col-sm-8">
    <?}else{?>
    <div class="col-md-12 col-sm-12">
    <?}?>
        <table class="props">
        <?foreach ($arResult['DISPLAY_PROPERTIES'] as $arProperty):?>
            <tr>
                <td class="proptitle">
                    <span><span><?=$arProperty['NAME']?></span></span>
                </td>
                <td class="propvalue">
                    <?if(is_array($arProperty['DISPLAY_VALUE'])):?>
                        <?=implode(', ',$arProperty['DISPLAY_VALUE'])?>
                    <?else:?>
                        <?=$arProperty['DISPLAY_VALUE']?>
                    <?endif;?>
                </td>
            </tr>
        <?endforeach;?>
        </table>
    </div> 
   
</div>
<div class="row factory-detail">
    <div class="col-md-12">
        <div class="cat-text">
            <div class="cat-text-container">
                <?=$arResult['DETAIL_TEXT'];?>
            </div>
            <span class="cat-text-opener">
                <span class="cat-open">Показать больше <i class="fa fa-chevron-down"></i></span>
                <span class="cat-close">Показать меньше <i class="fa fa-chevron-up"></i></span>
            </span>
        </div>
        <hr/>
    </div>
    
</div>

<?//trace($arParams)?>
<?//trace($arResult)?>    