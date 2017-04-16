<?
if($_REQUEST["ajax"]=="Y"):

	$AJAX_MODE = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	if (!CModule::IncludeModule("sale"))
		{
			ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
			return;
		}
//****************************************************************
$arParams["ACTION_VARIABLE"]=trim($arParams["ACTION_VARIABLE"]);
if(strlen($arParams["ACTION_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";

$arParams["PRODUCT_ID_VARIABLE"]=trim($arParams["PRODUCT_ID_VARIABLE"]);
if(strlen($arParams["PRODUCT_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";

$arParams["PRODUCT_QUANTITY_VARIABLE"]=trim($arParams["PRODUCT_QUANTITY_VARIABLE"]);
if(strlen($arParams["PRODUCT_QUANTITY_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_QUANTITY_VARIABLE"]))
	$arParams["PRODUCT_QUANTITY_VARIABLE"] = "qty";

$arParams["PRODUCT_PROPS_VARIABLE"]=trim($arParams["PRODUCT_PROPS_VARIABLE"]);
if(strlen($arParams["PRODUCT_PROPS_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_PROPS_VARIABLE"]))
	$arParams["PRODUCT_PROPS_VARIABLE"] = "prop";
	
//массив инфоблоков из которых разрешено добавлять в корзину 	
$arParams["IBLOCK_IDS"]	= array(3);
/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
$todo = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
$todo .="1 ";
	if(array_key_exists($arParams["ACTION_VARIABLE"]."BUY", $_REQUEST))
		$action = "BUY";
	elseif(array_key_exists($arParams["ACTION_VARIABLE"]."ADD2BASKET", $_REQUEST))
		$action = "ADD2BASKET";
	else
		$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);

	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if(($action == "ADD2BASKET" || $action == "BUY" || $action == "SUBSCRIBE_PRODUCT") && $productID > 0)
	{
		if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			//if($arParams["USE_PRODUCT_QUANTITY"])
			$QUANTITY = intval($_REQUEST[$arParams["PRODUCT_QUANTITY_VARIABLE"]]);
			if($QUANTITY <= 1)
				$QUANTITY = 1;

			$product_properties = array();

			$rsItems = CIBlockElement::GetList(array(), array('ID' => $productID), false, false, array('ID', 'IBLOCK_ID'));
			if ($arItem = $rsItems->Fetch())
			{
	$todo .="2 ";
				$arItem['IBLOCK_ID'] = intval($arItem['IBLOCK_ID']);
				if (in_array($arItem['IBLOCK_ID'], $arParams["IBLOCK_IDS"]))
				{
	$todo .="3 ";
					
					if (!empty($arParams["PRODUCT_PROPERTIES"]))
					{
						if (
							array_key_exists($arParams["PRODUCT_PROPS_VARIABLE"], $_REQUEST)
							&& is_array($_REQUEST[$arParams["PRODUCT_PROPS_VARIABLE"]])
						)
						{
							$product_properties = CIBlockPriceTools::CheckProductProperties(
								$arParams["IBLOCK_ID"],
								$productID,
								$arParams["PRODUCT_PROPERTIES"],
								$_REQUEST[$arParams["PRODUCT_PROPS_VARIABLE"]]
							);
							if (!is_array($product_properties))
								$strError = GetMessage("CATALOG_ERROR2BASKET").".";
						}
						else
						{
							$strError = GetMessage("CATALOG_ERROR2BASKET").".";
						}
					}
				}
				else
				{
	$todo .="4 ";
					
					if (!empty($arParams["OFFERS_CART_PROPERTIES"]))
					{
						$product_properties = CIBlockPriceTools::GetOfferProperties(
							$productID,
							$arParams["IBLOCK_ID"],
							$arParams["OFFERS_CART_PROPERTIES"]
						);
					}
				}
			}
			else
			{
	$todo .="5 ";
				$strError = GetMessage('CATALOG_PRODUCT_NOT_FOUND').".";
			}

			$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
			$arNotify = unserialize($notifyOption);

			if ($action == "SUBSCRIBE_PRODUCT" && $arNotify[SITE_ID]['use'] == 'Y')
			{
				$arRewriteFields["SUBSCRIBE"] = "Y";
				$arRewriteFields["CAN_BUY"] = "N";
			}
			$NEW = Add2BasketByProductID($productID, $QUANTITY, $arRewriteFields, $product_properties);
			if(!$strError && $NEW)
			{
	$todo .="6 ";
//echo "<br/>NEW:".$NEW;
//echo "<br/>ProductID:".$productID;
//echo "<br/>QUANTITY:".$QUANTITY;
//echo "<br/>arRewriteFields:".$arRewriteFields;
//echo "<br/>product_properties:<pre>".print_r($product_properties,1)."</pre>";
				
				//if($action == "BUY")
				//	LocalRedirect($arParams["BASKET_URL"]);
				//else
					//die();
					//LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
	$todo .="7 ";
				
				if($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = "Произошла ошибка при добавлении в корзину.";
			}
		}
	}
        die();
}
if(strlen($strError)>0)
{
	ShowError($strError);
	return;
}
//****************************************************************	
//удаление из корзины	
	if(strlen($_REQUEST["action"]) > 0)
	{
		$id = IntVal($_REQUEST["id"]);
		if($id > 0)
		{
			$dbBasketItems = CSaleBasket::GetList(
					array(),
					array(
							"FUSER_ID" => CSaleBasket::GetBasketUserID(),
							"LID" => SITE_ID,
							"ORDER_ID" => "NULL",
							"ID" => $id,
						),
					false,
					false,
					array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "CURRENCY")
				);
			if($arBasket = $dbBasketItems->Fetch())
			{
				if($_REQUEST["action"] == "delete")
				{
					CSaleBasket::Delete($arBasket["ID"]);
				}
				elseif($_REQUEST["action"] == "shelve" )
				{
					if ($arBasket["DELAY"] == "N" && $arBasket["CAN_BUY"] == "Y")
						CSaleBasket::Update($arBasket["ID"], Array("DELAY" => "Y"));
				}
				elseif($_REQUEST["action"] == "add")
				{
					if ($arBasket["DELAY"] == "Y" && $arBasket["CAN_BUY"] == "Y")
						CSaleBasket::Update($arBasket["ID"], Array("DELAY" => "N"));
				}
				elseif($_REQUEST["action"] == "inc")
				{
					$arBasket["QUANTITY"]++;
					CSaleBasket::Update($arBasket["ID"], $arBasket);
				}
			}
		}
	};

else:
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
	$APPLICATION->SetTitle("Корзина AJAX");
endif;	

//JSON ответ содержимое корзины

$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", "ajax", Array(
	"PATH_TO_BASKET" => "/personal/basket.php",
	"PATH_TO_ORDER" => "/personal/order.php",	// Страница оформления заказа
	),
	false
);

//echo "<br/>todo:".$todo;
//echo "<br/>Action:".$action;
//echo "<br/>ProductID:".$productID;
//echo "<pre>".print_r($_REQUEST,1)."</pre>";
//echo "<pre>".print_r($arParams,1)."</pre>";
//echo "<pre>".print_r($arResult,1)."</pre>";

if($AJAX_MODE):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
else:	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
endif;	
?>