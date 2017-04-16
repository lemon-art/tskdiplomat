<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent("bitrix:sale.viewed.product", "sidebar", array(
	"VIEWED_COUNT" => "5",
	"VIEWED_NAME" => "Y",
	"VIEWED_IMAGE" => "Y",
	"VIEWED_PRICE" => "Y",
	"VIEWED_CANBUY" => "Y",
	"VIEWED_CANBUSKET" => "Y",
	"VIEWED_IMG_HEIGHT" => "100",
	"VIEWED_IMG_WIDTH" => "100",
	"BASKET_URL" => "/site_cf/personal/basket.php",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"SET_TITLE" => "N"
	),
	false
);?>