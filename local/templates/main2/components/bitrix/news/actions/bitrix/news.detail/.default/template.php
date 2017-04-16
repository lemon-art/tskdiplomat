<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="action-detail">
<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
        <div class="row">
            <h1><?=$arResult["NAME"]?></h1>
        </div>
<?endif;?>

	<section class="row action">
			<article class="row action_item">
                                <?/*if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
                			<div class="row">
                                                <div class="date pull-left"><span class="figure-calendar"></span><?=$arResult["DISPLAY_ACTIVE_FROM"]?> Рі. РїРѕ <?=date('d.m.Y',  strtotime($arResult["DATE_ACTIVE_TO"]))?> Рі.</div>
                                        </div>
                                <?endif;*/?>
				<?if(is_array($arResult["DETAIL_PICTURE"])):?>
                                    <div class="row">
        					<div class="img"><img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt=""></div>
                                    </div>
				<?endif;?>
                                <div class="action-content">
	<?if(strlen($arResult["DETAIL_TEXT"])>0):?>
		<?echo $arResult["DETAIL_TEXT"];?>
	<?else:?>
		<?echo $arResult["PREVIEW_TEXT"];?>
	<?endif?>
                                 </div>
	
<br />

	<?/*foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>

		<?=$arProperty["NAME"]?>:&nbsp;
		<?if(is_array($arProperty["DISPLAY_VALUE"])):?>
			<?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
		<?else:?>
			<?=$arProperty["DISPLAY_VALUE"];?>
		<?endif?>
		<br />
	<?endforeach;*/?>
			</article>
	</section>
<?if(is_array($arResult['PROPERTIES']['OFFERS']['VALUE']) && count($arResult['PROPERTIES']['OFFERS']['VALUE']) > 0):?>
    
<div class="row"> 
<h2>Товвары в акции</h2>
</div>
<section class="row action-offers category">
<?php
//$APPLICATION->AddChainItem($arResult['NAME']);
$GLOBALS['arrActionFilter'] = array('ID' => array_values($arResult['PROPERTIES']['OFFERS']['VALUE'])); 
  
//if($_REQUEST["D"]=="2") echo "<pre>".print_r($GLOBALS['arrActionFilter'] ,1)."</pre>";
  
  $APPLICATION->IncludeComponent("bitrix:catalog.section", ".default", array(
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "9",
	"SECTION_ID" => "",
	"SECTION_CODE" => "",
	"SECTION_USER_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_SORT_FIELD2" => "id",
	"ELEMENT_SORT_ORDER2" => "desc",
	"FILTER_NAME" => "arrActionFilter",
	"INCLUDE_SUBSECTIONS" => "Y",
	"SHOW_ALL_WO_SECTION" => "Y",
	"HIDE_NOT_AVAILABLE" => "N",
	"PAGE_ELEMENT_COUNT" => "100",
	"LINE_ELEMENT_COUNT" => "3",
	"PROPERTY_CODE" => array(
		0 => "ARTICLE",
		1 => "",
	),
	"OFFERS_LIMIT" => "5",
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"BASKET_URL" => "/personal/basket.php",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "Y",
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"BROWSER_TITLE" => "-",
	"ADD_SECTIONS_CHAIN" => "N",
	"DISPLAY_COMPARE" => "N",
	"SET_TITLE" => "N",
	"SET_STATUS_404" => "N",
	"CACHE_FILTER" => "Y",
	"PRICE_CODE" => array(
		0 => "BASE",
		1 => "OLDSALE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRODUCT_PROPERTIES" => array(
	),
	"USE_PRODUCT_QUANTITY" => "N",
	"CONVERT_CURRENCY" => "N",
	"PAGER_TEMPLATE" => ".default",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "N",
	"PAGER_TITLE" => "РўРѕРІР°СЂС‹",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
	"PAGER_SHOW_ALL" => "N",
	"SHOW_ACTIONSCORNER" => "Y",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);
  
//if($_REQUEST["D"]=="2") echo "<pre>".print_r($arResult,1)."</pre>";
  
        ?>
</section>
    
<?endif;?>    

</div>


<?
//if($_REQUEST["D"]=="Y") echo "<pre>".print_r($arParams,1)."</pre>";
//if($_REQUEST["D"]=="Y") echo "<pre>".print_r($arResult,1)."</pre>";
?>