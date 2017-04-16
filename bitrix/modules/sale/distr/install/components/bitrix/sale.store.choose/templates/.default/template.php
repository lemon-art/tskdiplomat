<?
	use Bitrix\Main\Localization\Loc;
?>
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$rnd = "or".randString(4);?>
<table class="data">
	<tr>
		<td class="map">
			<div class="view_map">
				<?$APPLICATION->IncludeComponent(
					"bitrix:map.yandex.view",
					".default",
					Array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => $arResult["LOCATION"],
						"MAP_WIDTH" => 230,
						"MAP_HEIGHT" => 230,
						"CONTROLS" => array(0=>"TYPECONTROL"),
						"OPTIONS" => array("ENABLE_SCROLL_ZOOM", "ENABLE_DRAGGING"),
						"MAP_ID" => $rnd,
						"ONMAPREADY" => 'onMapReady'.$rnd
					)
				);
				?>
			</div>
		</td>
	</tr>
	</table>
			<script>
				var arDeliveryStores = <?=CUtil::PhpToJSObject($arResult["STORES"]);?>;
			</script>
			<?
				$menu = array();
			?>
			<div class="ora-storelist">
				<table id="store_table<?=$arParams["INDEX"]?>" class="store_table">
					<?
					$i = 1;
					$countCount = count($arResult["STORES"]);
					$arDefaultStore = array_shift(array_values($arResult["STORES"]));

					foreach ($arResult["STORES"] as $val)
					{
						$result = '';
						$checked = ($val["ID"] != $arParams["SELECTED_STORE"]) ? "style='display:none;'" : "";
						?>
						<tr class="store_row" id="row<?=$arParams["INDEX"]?>_<?=$val["ID"]?>" <?=$checked?>>
							<?
							if ($showImages)
							{
								?>
								<td class="image_cell">
									<div class="image">
										<?
										if (intval($val["IMAGE_ID"]) > 0):
											?>
											<a href="<?=$val["IMAGE_URL"]?>" target="_blank"><?=$val["IMAGE"]?></a>
										<?
										else:
											?>
											<img src="<?=$templateFolder?>/images/no_store.png" />
										<?
										endif;
										?>
									</div>
								</td>
							<?
							}
							?>
							<td class="<?=($countCount != $i)?"lilne":"last"?>">
								<label for="store<?=$arParams["INDEX"]?>_<?=$val["ID"]?>">
									<div class="adres"><?=htmlspecialcharsbx($val["ADDRESS"])?></div>
									<div class="phone"><?=htmlspecialcharsbx($val["PHONE"])?></div>
									<?
										$result .= '<span class="adres"><b>'.htmlspecialcharsbx($val["TITLE"]).':</b> '.htmlspecialcharsbx($val["ADDRESS"]).'</span>';
										$menu[] = array('TEXT' => $result, 'ONCLICK' => 'setChangeStore("'.$val["ID"].'", "'.$rnd.'");');
									?>
									<div class="full_store_info" id="full_store_info" onclick="showFullInfo(this);"><?=Loc::getMessage('SALE_SSC_ADD_INFO')?></div>
									<div style="display: none;">
										<div class="email"><a href="mailto:<?=htmlspecialcharsbx($val["EMAIL"])?>"><?=htmlspecialcharsbx($val["EMAIL"])?></a></div>
										<div class="shud"><?=htmlspecialcharsbx($val["SCHEDULE"])?></div>
										<div class="desc"><?=GetMessage('SALE_SSC_DESC');?>: <?=htmlspecialcharsbx($val["DESCRIPTION"])?></div>
									</div>
								</label>
							</td>
						</tr>
						<?
						$i++;
					}
					?>
				</table>
				<div class="block_change_store">
					<div><b><?=Loc::getMessage('SALE_SSC_STORE_EXPORT')?>:</b></div>
					<div id="store_name<?=$arParams["INDEX"]?>"><?=$arResult["STORES"][$arDefaultStore["ID"]]['TITLE']?></div>
					<span id="change_store<?=$arParams["INDEX"]?>" class="change_store"><?=Loc::getMessage('SALE_SSC_CHANGE')?></span>
				</div>
			</div>

<script>
	new BX.COpener({
		DIV: 'change_store<?=$arParams["INDEX"]?>',
		MENU: <?=CUtil::PhpToJSObject($menu);?>
	});
</script>
<input type="hidden" name="<?=$arParams["INPUT_NAME"]?>" id="<?=$arParams["INPUT_ID"]?>" value="<?=$arParams["SELECTED_STORE"]?>" />

<script type="text/javascript">

	function showFullInfo(obj)
	{
		var nextSibling = BX.findNextSibling(obj, {'tag' : 'div'});
		BX.toggle(nextSibling);
	}

	function setChangeStore(id, objName)
	{
		var store = arDeliveryStores[id];

		var tbl = BX('store_table<?=$arParams["INDEX"]?>');
		var children = BX.findChildren(tbl, {'tag' : 'tr'}, true);
		for (var i in children)
		{
			if (children[i].style.display != 'none')
				BX.hide(children[i]);
		}

		var obRow = BX("row<?=$arParams["INDEX"]?>_"+id);
		if (!!obRow)
			BX.show(obRow);

		var obStoreName = BX('store_name<?=$arParams["INDEX"]?>');
		if (obStoreName)
			BX.html(obStoreName, store['TITLE']);
		if (parseFloat(store["GPS_N"]) > 0 && parseFloat(store["GPS_S"]) > 0)
		{
			if (window.GLOBAL_arMapObjects[objName])
				window.GLOBAL_arMapObjects[objName].panTo([parseFloat(store["GPS_N"]), parseFloat(store["GPS_S"])], {flying: 1});
		}

		BX('<?=$arParams["INPUT_ID"]?>').value = id;
	}

	function onMapReady<?=$rnd;?>()
	{
		<? if ($arParams["SELECTED_STORE"] > 0) : ?>
			setChangeStore('<?=$arParams["SELECTED_STORE"];?>', '<?=$rnd?>');
		<?else:?>
			var keysStores = Object.keys(arDeliveryStores);
			var selectedStore = keysStores[0];
			setChangeStore(selectedStore, '<?=$rnd?>');
		<?endif;?>
	}
</script>