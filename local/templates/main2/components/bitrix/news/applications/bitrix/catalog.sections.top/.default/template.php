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
<div class="applications-sections-top">
<?foreach($arResult["SECTIONS"] as $arSection):?>
    <div class="col-md-12 applications-section">
        <div class="application-section-title">
            <a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?></a>
        </div>
        <ul class="application-section-items">
		<?
		foreach($arSection["ITEMS"] as $arElement):
		?>
		<?
		$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCST_ELEMENT_DELETE_CONFIRM')));
		?>
		<li>
                    <?if(is_array($arElement["PREVIEW_PICTURE"])):?>
                            <a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img
                                            border="0"
                                            src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>"
                                            width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>"
                                            height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>"
                                            alt="<?=$arElement["PREVIEW_PICTURE"]["ALT"]?>"
                                            title="<?=$arElement["PREVIEW_PICTURE"]["TITLE"]?>"
                                            /></a><br />
                    <?elseif(is_array($arElement["DETAIL_PICTURE"])):?>
                            <a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img
                                            border="0"
                                            src="<?=$arElement["DETAIL_PICTURE"]["SRC"]?>"
                                            width="<?=$arElement["DETAIL_PICTURE"]["WIDTH"]?>"
                                            height="<?=$arElement["DETAIL_PICTURE"]["HEIGHT"]?>"
                                            alt="<?=$arElement["DETAIL_PICTURE"]["ALT"]?>"
                                            title="<?=$arElement["DETAIL_PICTURE"]["TITLE"]?>"
                                            /></a><br />
                    <?endif?>
                    <a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a>
                </li>        
        	<?
		endforeach; // foreach($arResult["ITEMS"] as $arElement):
		?>
	</ul>
    </div>    
<?endforeach?>
</div>
