<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<?
//калькулятор расхода
//global $USER;
//if($USER->IsAdmin()):

global $arCalcFilter;

$arCalcFilter = array(
    'SECTION_ID' => $arResult['ID'],
    'INCLUDE_SUBSECTIONS' => 'Y',
    '!PROPERTY_CALC_CONSUM' => false
);
//trace($arResult);
?>
<div class="calculator">
<?$APPLICATION->IncludeComponent(
	"fcm:calculator",
	"",
	array(
		"IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'],
		"IBLOCK_ID" => $arParams['IBLOCK_ID'],
		"FILTER_NAME" => "arCalcFilter",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "360000",
		"CACHE_GROUPS" => "Y",
                "TITLE" => 'Калькулятор расхода материала'
	),
	$component
);?>
</div>
<br/>
<?//endif;?>



<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<?endif;?>

<form class="sorting" method="get" action="">
	<div class="sort">
		Сортировать по:
		<select name="sort_price">
			<option value="">Нет</option>
			<option value="price" <?if($_GET['sort_price']=='price'){?>selected<?}?>>Цене</option>
		</select>
		<?if($_GET['sort_order']=='desc'){?>
			<a href="<?=$APPLICATION->GetCurPageParam('sort_order=asc', Array('sort_order'));?>"></a>
		<?}else{?>
			<a href="<?=$APPLICATION->GetCurPageParam('sort_order=desc', Array('sort_order'));?>" class="desc"></a>
		<?}?>
	</div>
	<div class="count">
		Показывать по:
		<select name="count">
			<option value="20">20</option>
			<option value="40" <?if($_GET['count']=='40'){?>selected<?}?>>40</option>
			<option value="80" <?if($_GET['count']=='80'){?>selected<?}?>>80</option>
		</select>
	</div>
	<script>

	</script>
</form>

<ul id="product_list" class="bordercolor list">
		<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
		<? 
		$res = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
		if($ar_res = $res->GetNext())
		{
			$cat_id = $ar_res['IBLOCK_SECTION_ID'];
			$res2 = CIBlockSection::GetByID($cat_id);
			if($ar_res2 = $res2->GetNext())
			{
			//	echo $ar_res2["SECTION_PAGE_URL"].$ar_res2["NAME"]; 
			}
		}
	//	echo $arElement["IBLOCK_SECTION_ID"];
		
		
		?>
		
		<?
		
		
		/*$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
		*/?>
<li class="ajax_block_product bordercolor">
<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="product_img_link"<?/* title="SP NGDL H4"*/?>>
	<?if(strlen($arElement["PREVIEW_PICTURE"]["SRC"]) > 0):?>
 		<img border="0" src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>"<?/* title="<?=$arElement["NAME"]?>"*/?> />
 	<?else:?>
 		<img border="0" src="/images/noimage.png" width="180" height="180" alt="Нет изображения" title="Нет изображения" />
 	<?endif;?>
</a>
<div class="center_block">
<div class="product_flags">
<?if($arElement["PROPERTIES"]["NEWPRODUCT"]["VALUE_XML_ID"] == "Y"):?>
	<span class="new">Новинка!</span>
<?endif?>
 
</div>

<a class="product_link" href="<?=$arElement["DETAIL_PAGE_URL"]?>"<?/* title="<?=$arElement["NAME"]?>"*/?>><?=$arElement["NAME"]?></a>
<?if($ar_res2["NAME"]):?>
<p style="    padding: 5px 0 0 0;">
<b>Бренд:</b> <a href="<? echo $ar_res2["SECTION_PAGE_URL"];?>"><? echo $ar_res2["NAME"];?></a>
</p>
<?endif;?>
<?if($arElement["PREVIEW_TEXT_TYPE"] == "text"):?>

<p class="product_desc">
<?/*<a class="product_descr" href="<?=$arElement["DETAIL_PAGE_URL"]?>" title="<?=$arElement["~PREVIEW_TEXT"]?>">*/?>
<?=$arElement["~PREVIEW_TEXT"]?>
<?/*</a>*/?>
<a style="display:block" href="<?=$arElement["DETAIL_PAGE_URL"]?>" title="Подробно">Подробно</a>
</p>
<?else:?>
<div class="product_desc">
<?=$arElement["~PREVIEW_TEXT"]?>
</div>
<a  style="display:block" href="<?=$arElement["DETAIL_PAGE_URL"]?>" title="Подробно">Подробно</a>
<?endif;?>
</div>
<div class="right_block bordercolor">
<?/*
<span class="on_sale">On sale!</span>
<span class="discount">discount</span>
*/?>
<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
	<?if($arPrice["CAN_ACCESS"]):?>
		<?/*<p><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;*/?>
			<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
				<span class="price old"><?=$arPrice["PRINT_VALUE"]?></span>
				<span class="price yourprice"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?>
                                    <?if($arElement['CATALOG_MEASURE'] > 0):?>
                                        <span class="measure">
                                            <br/>за <?=$arElement['CATALOG_MEASURE_RATIO']?>
                                            <?=$arElement['CATALOG_MEASURE_NAME']?>
                                        </span>
                                    <?endif?>
                                </span>
			<?else:?>
				<span class="price"><span style="font-size: 17px;">Цена:</span> <?=$arPrice["PRINT_VALUE"]?>
                                    <?if($arElement['CATALOG_MEASURE'] > 0):?>
                                        <span class="measure">
                                            <br/>за <?=$arElement['CATALOG_MEASURE_RATIO']?>
                                            <?=$arElement['CATALOG_MEASURE_NAME']?>
                                        </span>
                                    <?endif?>
                                </span>
			<?endif;?>
		<?/*</p>*/?>
	<?endif;?>
<?endforeach;?>

<?/*
<p class="compare checkbox">
	<input class="comparator" id="comparator_item_1" value="comparator_item_<?=$arElement["ID"]?>" type="checkbox">
	<label for="comparator_item_<?=$arElement["ID"]?>">В сравнение</label>
</p>
*/?>
   <?if($arElement['PROPERTIES']['TOORDER']['VALUE_XML_ID'] == 'TOORDER_YES'):?>
	<span class="availability bordercolor">На заказ</span>
    <?else:?>
        <?if($arElement["CAN_BUY"] == "Y"):?>
                <span class="availability bordercolor">В наличии</span>
        <?endif?>
    <?endif?>
<a class="ajax_add_to_cart_button exclusive" onclick="yaCounter22073026.reachGoal('buyFromSection');ga('send', 'event', 'buyFromSection', 'submit', 'Купить разводящая');" rel="ajax_id_product_<?=$arElement["ID"]?>" href="<?=$arElement["ADD_URL"]?>" title="В корзину">Купить</a>

</div>
<div style="clear:both"></div>
</li>
<?endforeach;?>
</ul>
<?
if (!isset($_GET['PAGEN_1']))
{
?>
<?if(strlen($arResult["DESCRIPTION"]) > 0):
	if(strlen($arResult["DESCRIPTION"]) !== strlen($arResult["DESCRIPTION_SHORT"])): ?>
		<div class="cat_desc bordercolor bgcolor" id="category_description_short" style="background-color:#fff;text-align:justify">
 			<?=$arResult["DESCRIPTION_SHORT"]?>...&nbsp;
 			<a href="#" onclick="$('#category_description_short').hide(); $('#category_description_full').show(); $(this).hide(); return false;" class="lnk_more">еще...</a>
 		</div>
		<div class="cat_desc bordercolor bgcolor" id="category_description_full"  style="display: none;background-color:#fff;text-align:justify"><?=$arResult["DESCRIPTION"]?></div>
	<?else:?>
		<div class="cat_desc bordercolor bgcolor" id="category_description_full" style="background-color:#fff;text-align:justify"><?=$arResult["DESCRIPTION"]?></div>
	<?endif?>
<?endif?>
<?
}
?>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
<?$APPLICATION->IncludeComponent(
	"bxmod:seo", 
	"seo_mod", 
	array(
		"BXMOD_SEO_SEO_PARTSTITLE" => "Другие разделы",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000"
	),
	false
);?>
<?//=($_REQUEST["D"]=="Y")?"<pre>".print_r($arResult,1)."</pre>":"";?>
<?/********************************************************************************************* /?>
<div class="catalog-section">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<table cellpadding="0" cellspacing="0" border="0">
		<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
		<?
		$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
		?>
		<?if($cell%$arParams["LINE_ELEMENT_COUNT"] == 0):?>
		<tr>
		<?endif;?>

		<td valign="top" width="<?=round(100/$arParams["LINE_ELEMENT_COUNT"])?>%" id="<?=$this->GetEditAreaId($arElement['ID']);?>">

			<table cellpadding="0" cellspacing="2" border="0">
				<tr>
					<?if(is_array($arElement["PREVIEW_PICTURE"])):?>
						<td valign="top">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a><br />
						</td>
					<?elseif(is_array($arElement["DETAIL_PICTURE"])):?>
						<td valign="top">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arElement["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arElement["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arElement["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a><br />
						</td>
					<?endif?>
					<td valign="top"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a><br />
						<?foreach($arElement["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
							<?=$arProperty["NAME"]?>:&nbsp;<?
								if(is_array($arProperty["DISPLAY_VALUE"]))
									echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
								else
									echo $arProperty["DISPLAY_VALUE"];?><br />
						<?endforeach?>
						<br />
						<?=$arElement["PREVIEW_TEXT"]?>
					</td>
				</tr>
			</table>
			<?if(is_array($arElement["OFFERS"]) && !empty($arElement["OFFERS"])):?>
				<?foreach($arElement["OFFERS"] as $arOffer):?>
					<?foreach($arParams["OFFERS_FIELD_CODE"] as $field_code):?>
						<small><?echo GetMessage("IBLOCK_FIELD_".$field_code)?>:&nbsp;<?
								echo $arOffer[$field_code];?></small><br />
					<?endforeach;?>
					<?foreach($arOffer["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
						<small><?=$arProperty["NAME"]?>:&nbsp;<?
							if(is_array($arProperty["DISPLAY_VALUE"]))
								echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
							else
								echo $arProperty["DISPLAY_VALUE"];?></small><br />
					<?endforeach?>
					<?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
						<?if($arPrice["CAN_ACCESS"]):?>
							<p><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
							<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
							<?else:?>
								<span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
							<?endif?>
							</p>
						<?endif;?>
					<?endforeach;?>
					<p>
					<?if($arParams["DISPLAY_COMPARE"]):?>
						<noindex>
						<a href="<?echo $arOffer["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_COMPARE")?></a>&nbsp;
						</noindex>
					<?endif?>
					<?if($arOffer["CAN_BUY"]):?>
						<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
							<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
							<table border="0" cellspacing="0" cellpadding="2">
								<tr valign="top">
									<td><?echo GetMessage("CT_BCS_QUANTITY")?>:</td>
									<td>
										<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
									</td>
								</tr>
							</table>
							<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
							<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arOffer["ID"]?>">
							<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
							<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CATALOG_ADD")?>">
							</form>
						<?else:?>
							<noindex>
							<a href="<?echo $arOffer["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
							&nbsp;<a href="<?echo $arOffer["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_ADD")?></a>
							</noindex>
						<?endif;?>
					<?elseif(count($arResult["PRICES"]) > 0):?>
						<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
						<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
							"NOTIFY_ID" => $arOffer['ID'],
							"NOTIFY_URL" => htmlspecialcharsback($arOffer["SUBSCRIBE_URL"]),
							"NOTIFY_USE_CAPTHA" => "N"
							),
							false
						);?>
					<?endif?>
					</p>
				<?endforeach;?>
			<?else:?>
				<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
					<?if($arPrice["CAN_ACCESS"]):?>
						<p><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
						<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
							<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
						<?else:?><span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span><?endif;?>
						</p>
					<?endif;?>
				<?endforeach;?>
				<?if(is_array($arElement["PRICE_MATRIX"])):?>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="data-table">
					<thead>
					<tr>
						<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
							<td valign="top" nowrap><?= GetMessage("CATALOG_QUANTITY") ?></td>
						<?endif?>
						<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
							<td valign="top" nowrap><?= $arType["NAME_LANG"] ?></td>
						<?endforeach?>
					</tr>
					</thead>
					<?foreach ($arElement["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
					<tr>
						<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) > 1 || count($arElement["PRICE_MATRIX"]["ROWS"]) == 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
							<th nowrap><?
								if (IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
									echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
								elseif (IntVal($arQuantity["QUANTITY_FROM"]) > 0)
									echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
								elseif (IntVal($arQuantity["QUANTITY_TO"]) > 0)
									echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
							?></th>
						<?endif?>
						<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
							<td><?
								if($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"]):?>
									<s><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])?></s><span class="catalog-price"><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]);?></span>
								<?else:?>
									<span class="catalog-price"><?=FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]);?></span>
								<?endif?>&nbsp;
							</td>
						<?endforeach?>
					</tr>
					<?endforeach?>
					</table><br />
				<?endif?>
				<?if($arParams["DISPLAY_COMPARE"]):?>
					<noindex>
					<a href="<?echo $arElement["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_COMPARE")?></a>&nbsp;
					</noindex>
				<?endif?>
				<?if($arElement["CAN_BUY"]):?>
					<?if($arParams["USE_PRODUCT_QUANTITY"] || count($arElement["PRODUCT_PROPERTIES"])):?>
						<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
						<table border="0" cellspacing="0" cellpadding="2">
						<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
							<tr valign="top">
								<td><?echo GetMessage("CT_BCS_QUANTITY")?>:</td>
								<td>
									<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
								</td>
							</tr>
						<?endif;?>
						<?foreach($arElement["PRODUCT_PROPERTIES"] as $pid => $product_property):?>
							<tr valign="top">
								<td><?echo $arElement["PROPERTIES"][$pid]["NAME"]?>:</td>
								<td>
								<?if(
									$arElement["PROPERTIES"][$pid]["PROPERTY_TYPE"] == "L"
									&& $arElement["PROPERTIES"][$pid]["LIST_TYPE"] == "C"
								):?>
									<?foreach($product_property["VALUES"] as $k => $v):?>
										<label><input type="radio" name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]" value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"checked"'?>><?echo $v?></label><br>
									<?endforeach;?>
								<?else:?>
									<select name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]">
										<?foreach($product_property["VALUES"] as $k => $v):?>
											<option value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"selected"'?>><?echo $v?></option>
										<?endforeach;?>
									</select>
								<?endif;?>
								</td>
							</tr>
						<?endforeach;?>
						</table>
						<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
						<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arElement["ID"]?>">
						<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
						<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CATALOG_ADD")?>">
						</form>
					<?else:?>
						<noindex>
						<a href="<?echo $arElement["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>&nbsp;<a href="<?echo $arElement["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_ADD")?></a>
						</noindex>
					<?endif;?>
				<?elseif((count($arResult["PRICES"]) > 0) || is_array($arElement["PRICE_MATRIX"])):?>
					<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
					<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
							"NOTIFY_ID" => $arElement['ID'],
							"NOTIFY_URL" => htmlspecialcharsback($arElement["SUBSCRIBE_URL"]),
							"NOTIFY_USE_CAPTHA" => "N"
							),
							false
						);?>
				<?endif?>
			<?endif?>
			&nbsp;
		</td>

		<?$cell++;
		if($cell%$arParams["LINE_ELEMENT_COUNT"] == 0):?>
			</tr>
		<?endif?>

		<?endforeach; // foreach($arResult["ITEMS"] as $arElement):?>

		<?if($cell%$arParams["LINE_ELEMENT_COUNT"] != 0):?>
			<?while(($cell++)%$arParams["LINE_ELEMENT_COUNT"] != 0):?>
				<td>&nbsp;</td>
			<?endwhile;?>
			</tr>
		<?endif?>

</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
<pre>
<?=print_r($arResult,1)?>
</pre>
<?/**/?>