<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $Item):
		$arProducts[] = array(
				"id"	   =>$Item["ID"],
				"link"	   =>'http://'.SITE_SERVER_NAME.$Item["DETAIL_PAGE_URL"],
				"quantity" =>$Item["QUANTITY"],
				"id_image" =>"",
				"priceByLine"=>$Item["PRICE"],
				"name" =>substr($Item["NAME"],0,30)."...",//: "SP NGDL H4",
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
    "shippingCost" => '',
    "shippingCostFloat" => '',
    "wrappingCost" => '',
    "nbTotalProducts" => count($arResult["ITEMS"]["AnDelCanBuy"]),
    "total" => $arResult["allSum_FORMATED"],
    "productTotal" => $arResult["allSum_FORMATED"],
    "hasError" => ""
);


if($s = json_encode($arResult["JSON_OUT"])):
	$s = str_replace("\/","/",$s);
	echo $s;
else:
	echo "json error".json_last_error();die();
endif;?>
<?//if($_REQUEST["D"] == "Y") echo "<pre>".print_r($arResult,1)."</pre>";?>