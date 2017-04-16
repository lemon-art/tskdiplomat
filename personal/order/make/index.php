<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.ajax", 
	"visual", 
	array(
		"PAY_FROM_ACCOUNT" => "Y",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"COUNT_DELIVERY_TAX" => "N",
		"ALLOW_AUTO_REGISTER" => "Y",
		"SEND_NEW_USER_NOTIFY" => "Y",
		"DELIVERY_NO_AJAX" => "N",
		"DELIVERY_NO_SESSION" => "N",
		"TEMPLATE_LOCATION" => "popup",
		"DELIVERY_TO_PAYSYSTEM" => "d2p",
		"USE_PREPAYMENT" => "N",
		"PROP_3" => array(
		),
		"PROP_4" => array(
		),
		"ALLOW_NEW_PROFILE" => "Y",
		"SHOW_PAYMENT_SERVICES_NAMES" => "Y",
		"SHOW_STORES_IMAGES" => "N",
		"PATH_TO_BASKET" => "/personal/cart/",
		"PATH_TO_PERSONAL" => "/personal/order/",
		"PATH_TO_PAYMENT" => "/personal/order/payment/",
		"PATH_TO_AUTH" => "/auth/",
		"SET_TITLE" => "Y",
		"PRODUCT_COLUMNS" => array(
		),
		"DISABLE_BASKET_REDIRECT" => "N",
		"DISPLAY_IMG_WIDTH" => "90",
		"DISPLAY_IMG_HEIGHT" => "90",
		"COMPONENT_TEMPLATE" => "visual"
	),
	false
);
?>
<script type="text/javascript">
<?

if (ereg('^\/personal\/order\/make\/(.*)',$_SERVER['REQUEST_URI'],$url)) {
	if (isset($_GET['ORDER_ID'])) {
		echo "ga('require', 'ecommerce');
		";
		$order = $_GET['ORDER_ID'];
		$query = 'SELECT * FROM b_sale_order WHERE ID = '.$order;
		$sql = mysql_query($query);
		$row = mysql_fetch_assoc($sql);
		$order_id = $order;
		$price = $row['PRICE'];
		
		$query = 'SELECT * FROM b_sale_basket WHERE ORDER_ID = '.$order;
		$sql = mysql_query($query);
		$shipping = 0;
		$return ='';
		while($row = mysql_fetch_assoc($sql)) {
			$item['order_id'] = $order;
			$item['sku'] = $row['PRODUCT_ID'];
			$item['name'] = $row['NAME'];
			$item['price'] = $row['PRICE'];
			$quanity = explode('.',$row['QUANTITY']);
			$item['count'] = $quanity[0];

$return .= "ga('ecommerce:addItem', {
  'id': '{$item['order_id']}',                     // Transaction ID. Required.
  'name': '{$item['name']}',    // Product name. Required.
  'sku': '{$item['sku']}',                 // SKU/code.
  'category': '',         // Category or variation.
  'price': '{$item['price']}',                 // Unit price.
  'quantity': '{$item['count']}'                   // Quantity.
});
";

			$shipping += $item['count'];
		}
		
echo "ga('ecommerce:addTransaction', {
  'id': '{$order_id}', 
  'affiliation': 'tskdiplomat.ru',   // Affiliation or store name.
  'revenue': '{$price}',               // Grand Total.
  'shipping': '{$shipping}',                  // Shipping.
  'tax': ''                     // Tax.
});
".$return."
ga('ecommerce:send');
";
	}
}
?>
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>