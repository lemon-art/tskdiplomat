<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach ($arResult["ITEMS"] as $gkey => $arGroup):
	foreach ($arGroup as $key => $Item):
		$Item["VALUE"] = $Item["PRICE"]*$Item["QUANTITY"];
		$Item["PRINT_VALUE"] = number_format($Item["VALUE"],2,"."," ");
		$Item["VALUE_FORMATED"] = CurrencyFormat($Item["VALUE"],$Item["CURRENCY"]);
		$arResult["ITEMS"][$gkey][$key] = $Item;
	endforeach;
endforeach;	
?>