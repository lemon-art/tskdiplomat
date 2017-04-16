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

if(count($arResult['ITEMS']) > 0){
?>
<div class="row mordokniga">
    <?foreach ($arResult['ITEMS'] as $arItem){?>
    <div class="col-md-6">
         <div class="morda-item"> 
        <img 
            class="morda" 
            src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" 
            title="<?=$arItem['PREVIEW_PICTURE']['TITLE']?>" 
            alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>" 
            />
        <h3><?=$arItem['NAME']?></h3>
        <p>
            <?=$arItem['DISPLAY_PROPERTIES']['POSITION']['DISPLAY_VALUE']?>
            <br />
            <b>тел.: <?=$arItem['DISPLAY_PROPERTIES']['PHONE']['DISPLAY_VALUE']?></b> 
            <br />
            <b><?=$arItem['DISPLAY_PROPERTIES']['EMAIL']['DISPLAY_VALUE']?></b> 
        </p>    
         </div>
    </div>
    <?}?>
</div>    
<?}?>    

<?//trace($arResult)?>