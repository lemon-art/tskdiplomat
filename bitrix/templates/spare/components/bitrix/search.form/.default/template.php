<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<form method="get" action="<?=$arResult["FORM_ACTION"]?>" id="searchbox">
			<input class="search_query ac_input" id="search_query_top" type="text" name="q" value="" size="15" maxlength="50"  autocomplete="off"/>
			<input class="search_submit" name="s" type="submit" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" />
</form>
<?/*
<input class="search_query ac_input" type="text" id="search_query_top" name="search_query" value="" autocomplete="off">
<a href="javascript:document.getElementById('searchbox').submit();">Поиск</a>

<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<table border="0" cellspacing="0" cellpadding="2" align="center">
		<tr>
			<td align="center"><?if($arParams["USE_SUGGEST"] === "Y"):?><?$APPLICATION->IncludeComponent(
				"bitrix:search.suggest.input",
				"",
				array(
					"NAME" => "q",
					"VALUE" => "",
					"INPUT_SIZE" => 15,
					"DROPDOWN_SIZE" => 10,
				),
				$component, array("HIDE_ICONS" => "Y")
			);?><?else:?>
			<?endif;?></td>
		</tr>
		<tr>
			<td align="right"></td>
		</tr>
	</table>
</form>
</div>
*/?>