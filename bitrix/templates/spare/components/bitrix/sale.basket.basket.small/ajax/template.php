<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult["ITEMS"] as $Item):
		$arProducts[] = array(
				"id"	   =>$Item["ID"],
				"link"	   =>'http://'.SITE_SERVER_NAME.$Item["DETAIL_PAGE_URL"],
				"quantity" =>$Item["QUANTITY"],
				"id_image" =>"",
				"priceByLine"=>$Item["PRICE"],
				"name" =>$Item["NAME"],//: "SP NGDL H4",
				"price" =>$Item["PRICE"],//: "$549.00",
				"price_float"=>$Item["PRICE"],//: "549",
				"idCombination" =>0,
				"idAddressDelivery"=>0,
				"hasAttributes" => false,
				"hasCustomizedDatas"=> false,
				"customizedDatas"=>array()
		);
endforeach;


$arResult["JSON_OUT"] = array(
	"products" => $arProducts,
    "discounts" => Array(),
    "shippingCost" => '$7.00',
    "shippingCostFloat" => '7',
    "wrappingCost" => '$0.00',
    "nbTotalProducts" => count($arResult["ITEMS"]),
    "total" => '$810.20',
    "productTotal" => "$803.20",
    "hasError" => ""
);


if($s = json_encode($arResult["JSON_OUT"])):
	$s = str_replace("\/","/",$s);
	echo $s;
else:
	echo "json error".json_last_error();die();
endif;
?>	
<?/* $ar = json_decode('
{
"products": [
{
"id": 1,
"link": "http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=1&controller=product&id_lang=1",
"quantity": 1,
"id_image": "http://livedemo00.template-help.com/prestashop_42844/img/p/1/1-small-cart.jpg",
"priceByLine": "$549.00",
"name": "SP NGDL H4",
"price": "$549.00",
"price_float": "549",
"idCombination": 0,
"idAddressDelivery": 0,
"hasAttributes": false,
"hasCustomizedDatas": false,
"customizedDatas":[
]
}, {
"id": 2,
"link": "http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=2&controller=product&id_lang=1",
"quantity": 1,
"id_image": "http://livedemo00.template-help.com/prestashop_42844/img/p/5/5-small-cart.jpg",
"priceByLine": "$215.00",
"name": "UUC7 shift knob",
"price": "$215.00",
"price_float": "215",
"idCombination": 0,
"idAddressDelivery": 0,
"hasAttributes": false,
"hasCustomizedDatas": false,
"customizedDatas":[
]
}, {
"id": 3,
"link": "http://livedemo00.template-help.com/prestashop_42844/index.php?id_product=3&controller=product&id_lang=1",
"quantity": 1,
"id_image": "http://livedemo00.template-help.com/prestashop_42844/img/p/8/8-small-cart.jpg",
"priceByLine": "$39.20",
"name": "Castrol Edge...",
"price": "$39.20",
"price_float": "39.2",
"idCombination": 0,
"idAddressDelivery": 0,
"hasAttributes": false,
"hasCustomizedDatas": false,
"customizedDatas":[
]
}],
"discounts": [
],
"shippingCost": "$7.00",
"shippingCostFloat": "7",
"wrappingCost": "$0.00",
"nbTotalProducts": "3",
"total": "$810.20",
"productTotal": "$803.20",
"hasError" : false
}
');*/?>
<?//="<pre>".print_r($arResult,1)."</pre>"?>
<?/*if ($arResult["READY"]=="Y" || $arResult["DELAY"]=="Y" || $arResult["NOTAVAIL"]=="Y" || $arResult["SUBSCRIBE"]=="Y"):?>
	<table class="sale_basket_small">
	<?if ($arResult["READY"]=="Y"):?>
		<tr>
			<td align="center"><?= GetMessage("TSBS_READY") ?></td>
		</tr>
		<?
		foreach ($arResult["ITEMS"] as $v)
		{
			if ($v["DELAY"]=="N" && $v["CAN_BUY"]=="Y")
			{
				?>
				<tr>
					<td><li>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								<a href="<?echo $v["DETAIL_PAGE_URL"] ?>">
							<?endif;?>
							<b><?echo $v["NAME"]?></b>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								</a>
							<?endif;?>
							<br />
							<?= GetMessage("TSBS_PRICE") ?>&nbsp;<b><?echo $v["PRICE_FORMATED"]?></b><br />
							<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $v["QUANTITY"]?>
					</li></td>
				</tr>
				<?
			}
		}
		?>
		<?if (strlen($arParams["PATH_TO_BASKET"])>0):?>
			<tr>
				<td align="center">
					<form method="get" action="<?=$arParams["PATH_TO_BASKET"]?>">
						<input type="submit" value="<?= GetMessage("TSBS_2BASKET") ?>">
					</form>
				</td>
			</tr>
		<?endif;?>
		<?if (strlen($arParams["PATH_TO_ORDER"])>0):?>
			<tr>
				<td align="center">
					<form method="get" action="<?= $arParams["PATH_TO_ORDER"] ?>">
						<input type="submit" value="<?= GetMessage("TSBS_2ORDER") ?>">
					</form>
				</td>
			</tr>
		<?endif;?>
	<?endif;?>
	<?if ($arResult["DELAY"]=="Y"):?>
		<tr>
			<td align="center"><?= GetMessage("TSBS_DELAY") ?></td>
		</tr>
		<tr>
		<?
		foreach ($arResult["ITEMS"] as $v)
		{
			if ($v["DELAY"]=="Y" && $v["CAN_BUY"]=="Y")
			{
				?>
				<tr>
					<td>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								<a href="<?echo $v["DETAIL_PAGE_URL"] ?>">
							<?endif;?>
							<b><?echo $v["NAME"]?></b>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								</a>
							<?endif;?>
							<br />
							<?= GetMessage("TSBS_PRICE") ?>&nbsp;<b><?echo $v["PRICE_FORMATED"]?></b><br />
							<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $v["QUANTITY"]?>
					</td>
				</tr>
				<?
			}
		}
		?>
		<?if (strlen($arParams["PATH_TO_BASKET"])>0):?>
			<tr>
				<td>
					<form method="get" action="<?=$arParams["PATH_TO_BASKET"]?>">
						<input type="submit" value="<?= GetMessage("TSBS_2BASKET") ?>">
					</form>
				</td>
			</tr>
		<?endif;?>
	<?endif;?>
	
	<?if ($arResult["SUBSCRIBE"]=="Y"):?>
		<tr>
			<td align="center"><?= GetMessage("TSBS_SUBSCRIBE") ?></td>
		</tr>
		<?
		foreach ($arResult["ITEMS"] as $v)
		{
			if ($v["CAN_BUY"]=="N" && $v["SUBSCRIBE"]=="Y")
			{
				?>
				<tr>
					<td>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								<a href="<?echo $v["DETAIL_PAGE_URL"] ?>">
							<?endif;?>
							<b><?echo $v["NAME"]?></b>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								</a>
							<?endif;?>
					</td>
				</tr>
				</tr>
				<?
			}
		}
		?>
	<?endif;?>
	
	<?if ($arResult["NOTAVAIL"]=="Y"):?>
		<tr>
			<td align="center"><?= GetMessage("TSBS_UNAVAIL") ?></td>
		</tr>
		<?
		foreach ($arResult["ITEMS"] as $v)
		{
			if ($v["CAN_BUY"]=="N" && $v["SUBSCRIBE"]=="N")
			{
				?>
				<tr>
					<td>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								<a href="<?echo $v["DETAIL_PAGE_URL"] ?>">
							<?endif;?>
							<b><?echo $v["NAME"]?></b>
							<?if (strlen($v["DETAIL_PAGE_URL"])>0):?>
								</a>
							<?endif;?>
							<br />
							<?= GetMessage("TSBS_PRICE") ?>&nbsp;<b><?echo $v["PRICE_FORMATED"]?></b><br />
							<?= GetMessage("TSBS_QUANTITY") ?>&nbsp;<?echo $v["QUANTITY"]?>
					</td>
				</tr>
				</tr>
				<?
			}
		}
		?>
	<?endif;?>
	</table>
<?endif;*/?>
