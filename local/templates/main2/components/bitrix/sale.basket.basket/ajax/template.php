<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $Item):
		$arProducts[] = array(
				"id"	   =>$Item["ID"],
				"link"	   =>$Item["DETAIL_PAGE_URL"],
				"quantity" =>$Item["QUANTITY"],
				"id_image" =>"",
				"priceByLine"=>$Item["VALUE"],
				"name" =>substr($Item["NAME"],0,30)."...",//: "SP NGDL H4",
				"price" =>$Item["VALUE_FORMATED"],//: "$549.00",
				"price_float"=>$Item["VALUE"],//: "549",
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

echo \Bitrix\Main\Web\Json::encode($arResult["JSON_OUT"]);
?>
<?=trace($arResult,1);?>