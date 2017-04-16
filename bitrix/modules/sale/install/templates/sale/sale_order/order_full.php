<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);
if (CModule::IncludeModule("sale")):
//*******************************************************

$BASKET_PAGE = Trim($BASKET_PAGE);
if (strlen($BASKET_PAGE) <= 0)
	$BASKET_PAGE = $GLOBALS["BASKET_PAGE"];
if (strlen($BASKET_PAGE) <= 0)
	$BASKET_PAGE = "basket.php";

$ORDER_PAGE = Trim($ORDER_PAGE);
if (strlen($ORDER_PAGE) <= 0)
	$ORDER_PAGE = $GLOBALS["ORDER_PAGE"];
if (strlen($ORDER_PAGE) <= 0)
	$ORDER_PAGE = "order.php";

$PERSONAL_PAGE = Trim($PERSONAL_PAGE);
if (strlen($PERSONAL_PAGE) <= 0)
	$PERSONAL_PAGE = $GLOBALS["PERSONAL_PAGE"];
if (strlen($PERSONAL_PAGE) <= 0)
	$PERSONAL_PAGE = LANG_DIR."personal/";

$ALLOW_PAY_FROM_ACCOUNT = (($ALLOW_PAY_FROM_ACCOUNT == "N") ? "N" : "Y");

$CurrentStep = IntVal($_REQUEST["CurrentStep"]);
if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_REQUEST["backButton"]) > 0)
	$CurrentStep = $CurrentStep - 2;
if ($CurrentStep <= 0)
	$CurrentStep = 1;

$errorMessage = "";

/*******************************************************************************/
/*****************  ACTION  ****************************************************/
/*******************************************************************************/
if (!$GLOBALS["USER"]->IsAuthorized())
{
	if ($_REQUEST["do_authorize"] == "Y")
	{
		$USER_LOGIN = $_REQUEST["USER_LOGIN"];
		if (strlen($USER_LOGIN) <= 0)
			$errorMessage .= GetMessage("STOF_ERROR_AUTH_LOGIN").".<br>";

		$USER_PASSWORD = $_REQUEST["USER_PASSWORD"];

		if (strlen($errorMessage) <= 0)
		{
			$arAuthResult = $GLOBALS["USER"]->Login($USER_LOGIN, $USER_PASSWORD, "N");
			if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
				$errorMessage .= GetMessage("STOF_ERROR_AUTH").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br>" );
		}
	}
	elseif ($_REQUEST["do_register"] == "Y")
	{
		$NEW_GENERATE = (($_REQUEST["NEW_GENERATE"] == "Y") ? "Y" : "N");

		$NEW_NAME = $_REQUEST["NEW_NAME"];
		if (strlen($NEW_NAME) <= 0)
			$errorMessage .= GetMessage("STOF_ERROR_REG_NAME").".<br>";

		$NEW_LAST_NAME = $_REQUEST["NEW_LAST_NAME"];
		if (strlen($NEW_LAST_NAME) <= 0)
			$errorMessage .= GetMessage("STOF_ERROR_REG_LASTNAME").".<br>";

		$NEW_EMAIL = $_REQUEST["NEW_EMAIL"];
		if (strlen($NEW_EMAIL) <= 0)
			$errorMessage .= GetMessage("STOF_ERROR_REG_EMAIL").".<br>";
		elseif (!check_email($NEW_EMAIL))
			$errorMessage .= GetMessage("STOF_ERROR_REG_BAD_EMAIL").".<br>";

		if ($NEW_GENERATE == "Y")
		{
			$NEW_LOGIN = $NEW_EMAIL;

			$pos = strpos($NEW_LOGIN, "@");
			if ($pos !== false)
				$NEW_LOGIN = substr($NEW_LOGIN, 0, $pos);

			if (strlen($NEW_LOGIN) > 47)
				$NEW_LOGIN = substr($NEW_LOGIN, 0, 47);

			$dbUserLogin = CUser::GetByLogin($NEW_LOGIN);
			if ($arUserLogin = $dbUserLogin->Fetch())
			{
				$newLoginTmp = $NEW_LOGIN;
				$uind = 0;
				do
				{
					$uind++;
					if ($uind == 10)
					{
						$NEW_LOGIN = $NEW_EMAIL;
						$newLoginTmp = $NEW_LOGIN;
					}
					elseif ($uind > 10)
					{
						$NEW_LOGIN = "buyer".time().GetRandomCode(2);
						$newLoginTmp = $NEW_LOGIN;
						break;
					}
					else
					{
						$newLoginTmp = $NEW_LOGIN.$uind;
					}
					$dbUserLogin = CUser::GetByLogin($newLoginTmp);
				}
				while ($arUserLogin = $dbUserLogin->Fetch());

				$NEW_LOGIN = $newLoginTmp;
			}

			$NEW_PASSWORD = GetRandomCode(6);
			$NEW_PASSWORD_CONFIRM = $NEW_PASSWORD;
		}
		else
		{
			$NEW_LOGIN = $_REQUEST["NEW_LOGIN"];
			if (strlen($NEW_LOGIN) <= 0)
				$errorMessage .= GetMessage("STOF_ERROR_REG_FLAG").".<br>";

			$NEW_PASSWORD = $_REQUEST["NEW_PASSWORD"];
			if (strlen($NEW_PASSWORD) <= 0)
				$errorMessage .= GetMessage("STOF_ERROR_REG_FLAG1").".<br>";

			$NEW_PASSWORD_CONFIRM = $_REQUEST["NEW_PASSWORD_CONFIRM"];
			if (strlen($NEW_PASSWORD) > 0 && strlen($NEW_PASSWORD_CONFIRM) <= 0)
				$errorMessage .= GetMessage("STOF_ERROR_REG_FLAG1").".<br>";

			if (strlen($NEW_PASSWORD) > 0
				&& strlen($NEW_PASSWORD_CONFIRM) > 0
				&& $NEW_PASSWORD != $NEW_PASSWORD_CONFIRM)
				$errorMessage .= GetMessage("STOF_ERROR_REG_PASS").".<br>";
		}

		if (strlen($errorMessage) <= 0)
		{
			$arAuthResult = $GLOBALS["USER"]->Register($NEW_LOGIN, $NEW_NAME, $NEW_LAST_NAME, $NEW_PASSWORD, $NEW_PASSWORD_CONFIRM, $NEW_EMAIL, LANG, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
				$errorMessage .= GetMessage("STOF_ERROR_REG").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br>" );
			else
				if ($GLOBALS["USER"]->IsAuthorized())
					CUser::SendUserInfo($GLOBALS["USER"]->GetID(), SITE_ID, GetMessage("INFO_REQ"));
		}
	}
}
else
{
	$BASE_LANG_CURRENCY = CSaleLang::GetLangCurrency(SITE_ID);

	if ($CurrentStep > 0 && $CurrentStep < 7)
	{
		// <***************** BEFORE 1 STEP
		$ORDER_PRICE = 0;
		$ORDER_WEIGHT = 0;
		$bProductsInBasket = False;
		$arProductsInBasket = array();

		$dbBasketItems = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array(
						"FUSER_ID" => CSaleBasket::GetBasketUserID(),
						"LID" => SITE_ID,
						"ORDER_ID" => "NULL"
					),
				false,
				false,
				array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "WEIGHT", "NAME")
			);
		while ($arBasketItems = $dbBasketItems->Fetch())
		{
			if (strlen($arBasketItems["CALLBACK_FUNC"])>0)
			{
				CSaleBasket::UpdatePrice($arBasketItems["ID"], $arBasketItems["CALLBACK_FUNC"], $arBasketItems["MODULE"], $arBasketItems["PRODUCT_ID"], $arBasketItems["QUANTITY"]);
				$arBasketItems = CSaleBasket::GetByID($arBasketItems["ID"]);
			}

			if ($arBasketItems["DELAY"] == "N" && $arBasketItems["CAN_BUY"] == "Y")
			{
				$arProductsInBasket[] = $arBasketItems;
				$ORDER_PRICE += DoubleVal($arBasketItems["PRICE"]) * IntVal($arBasketItems["QUANTITY"]);
				$ORDER_WEIGHT += IntVal($arBasketItems["WEIGHT"]) * IntVal($arBasketItems["QUANTITY"]);
				$bProductsInBasket = True;
			}
		}

		if (!$bProductsInBasket)
		{
			LocalRedirect($BASKET_PAGE);
			$errorMessage .= GetMessage("SALE_BASKET_EMPTY");
		}

		if (strlen($errorMessage) <= 0 && $CurrentStep > 1)
		{
			// <***************** AFTER 1 STEP
			$PERSON_TYPE = IntVal($_REQUEST["PERSON_TYPE"]);
			if ($PERSON_TYPE <= 0)
				$errorMessage .= GetMessage("SALE_NO_PERS_TYPE")."<br>";

			if (($PERSON_TYPE > 0) && !($arPersType = CSalePersonType::GetByID($PERSON_TYPE)))
				$errorMessage .= GetMessage("SALE_PERS_TYPE_NOT_FOUND")."<br>";

			if (strlen($errorMessage) > 0)
				$CurrentStep = 1;
		}

		if (strlen($errorMessage) <= 0 && $CurrentStep > 2)
		{
			// <***************** AFTER 2 STEP
			foreach ($_REQUEST as $key => $value)
			{
				if (substr($key, 0, strlen("ORDER_PROP_"))=="ORDER_PROP_")
					$$key = $value;
			}

			$PROFILE_ID = IntVal($_REQUEST["PROFILE_ID"]);
			if ($PROFILE_ID > 0 && $GLOBALS["USER"]->IsAuthorized())
			{
				$dbUserProps = CSaleOrderUserPropsValue::GetList(
						array("SORT" => "ASC"),
						array("USER_PROPS_ID" => $PROFILE_ID),
						false,
						false,
						array("ID", "ORDER_PROPS_ID", "VALUE", "SORT")
					);
				while ($arUserProps = $dbUserProps->Fetch())
				{
					${"ORDER_PROP_".$arUserProps["ORDER_PROPS_ID"]} = $arUserProps["VALUE"];
					$_REQUEST["ORDER_PROP_".$arUserProps["ORDER_PROPS_ID"]] = $arUserProps["VALUE"];
				}
			}

			$dbOrderProps = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array("PERSON_TYPE_ID" => $PERSON_TYPE),
					false,
					false,
					array("ID", "NAME", "TYPE", "IS_LOCATION", "IS_LOCATION4TAX", "IS_PROFILE_NAME", "IS_PAYER", "IS_EMAIL", "REQUIED", "SORT")
				);
			while ($arOrderProps = $dbOrderProps->Fetch())
			{
				$bErrorField = False;
				$curVal = ${"ORDER_PROP_".$arOrderProps["ID"]};
				if ($arOrderProps["TYPE"]=="LOCATION" && ($arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_LOCATION4TAX"]=="Y"))
				{
					if ($arOrderProps["IS_LOCATION"]=="Y")
						$DELIVERY_LOCATION = IntVal($curVal);
					if ($arOrderProps["IS_LOCATION4TAX"]=="Y")
						$TAX_LOCATION = IntVal($curVal);

					if (IntVal($curVal)<=0) $bErrorField = True;
				}
				elseif ($arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_PAYER"]=="Y" || $arOrderProps["IS_EMAIL"]=="Y")
				{
					if ($arOrderProps["IS_PROFILE_NAME"]=="Y")
					{
						$PROFILE_NAME = Trim($curVal);
						if (strlen($PROFILE_NAME)<=0) $bErrorField = True;
					}
					if ($arOrderProps["IS_PAYER"]=="Y")
					{
						$PAYER_NAME = Trim($curVal);
						if (strlen($PAYER_NAME)<=0) $bErrorField = True;
					}
					if ($arOrderProps["IS_EMAIL"]=="Y")
					{
						$USER_EMAIL = Trim($curVal);
						if (strlen($USER_EMAIL)<=0 || !check_email($USER_EMAIL)) $bErrorField = True;
					}
				}
				elseif ($arOrderProps["REQUIED"]=="Y")
				{
					if ($arOrderProps["TYPE"]=="TEXT" || $arOrderProps["TYPE"]=="TEXTAREA" || $arOrderProps["TYPE"]=="RADIO" || $arOrderProps["TYPE"]=="SELECT")
					{
						if (strlen($curVal)<=0) $bErrorField = True;
					}
					elseif ($arOrderProps["TYPE"]=="LOCATION")
					{
						if (IntVal($curVal)<=0) $bErrorField = True;
					}
					elseif ($arOrderProps["TYPE"]=="MULTISELECT")
					{
						if (!is_array($curVal) || count($curVal)<=0) $bErrorField = True;
					}
				}
				if ($bErrorField)
					$errorMessage .= GetMessage("SALE_EMPTY_FIELD")." \"".$arOrderProps["NAME"]."\".<br>";
			}

			if (strlen($errorMessage) > 0)
				$CurrentStep = 2;
		}

		if (strlen($errorMessage) <= 0 && $CurrentStep > 3)
		{
			// <***************** AFTER 3 STEP
			$arTaxExempt = array();
			$arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();

			$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
			while ($arTaxExemptList = $dbTaxExemptList->Fetch())
			{
				if (!in_array(IntVal($arTaxExemptList["TAX_ID"]), $arTaxExempt))
				{
					$arTaxExempt[] = IntVal($arTaxExemptList["TAX_ID"]);
				}
			}

			// DELIVERY
			$DELIVERY_ID = IntVal($_REQUEST["DELIVERY_ID"]);
			$DELIVERY_PRICE = 0;
			if (($DELIVERY_ID > 0) && !($arDeliv = CSaleDelivery::GetByID($DELIVERY_ID)))
				$errorMessage .= GetMessage("SALE_DELIVERY_NOT_FOUND")."<br>";
			elseif (($DELIVERY_ID > 0) && $arDeliv)
				$DELIVERY_PRICE = roundEx(CCurrencyRates::ConvertCurrency($arDeliv["PRICE"], $arDeliv["CURRENCY"], $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION);

			if (strlen($errorMessage) > 0)
				$CurrentStep = 3;
		}

		if (strlen($errorMessage) <= 0 && $CurrentStep > 4)
		{
			// <***************** AFTER 4 STEP

			// PAY_SYSTEM
			$PAY_SYSTEM_ID = IntVal($_REQUEST["PAY_SYSTEM_ID"]);
			if ($PAY_SYSTEM_ID <= 0)
				$errorMessage .= GetMessage("SALE_NO_PAY_SYS")."<br>";
			if (($PAY_SYSTEM_ID > 0) && !($arPaySys = CSalePaySystem::GetByID($PAY_SYSTEM_ID, $PERSON_TYPE)))
				$errorMessage .= GetMessage("SALE_PAY_SYS_NOT_FOUND")."<br>";

			// DISCOUNT
			for ($i = 0; $i < count($arProductsInBasket); $i++)
				$arProductsInBasket[$i]["DISCOUNT_PRICE"] = DoubleVal($arProductsInBasket[$i]["PRICE"]);

			$dbDiscount = CSaleDiscount::GetList(
					array("SORT" => "ASC"),
					array(
							"LID" => SITE_ID,
							"ACTIVE" => "Y",
							"!>ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
							"!<ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
							"<=PRICE_FROM" => $ORDER_PRICE,
							">=PRICE_TO" => $ORDER_PRICE
						),
					false,
					false,
					array("*")
				);
			$DISCOUNT_PRICE = 0;
			$DISCOUNT_PERCENT = 0;
			$arDiscounts = array();
			if ($arDiscount = $dbDiscount->Fetch())
			{
				if ($arDiscount["DISCOUNT_TYPE"] == "P")
				{
					$DISCOUNT_PERCENT = $arDiscount["DISCOUNT_VALUE"];
					for ($bi = 0; $bi < count($arProductsInBasket); $bi++)
					{
						$curDiscount = roundEx(DoubleVal($arProductsInBasket[$bi]["PRICE"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
						$arDiscounts[IntVal($arProductsInBasket[$bi]["ID"])] = $curDiscount;
						$DISCOUNT_PRICE += $curDiscount * IntVal($arProductsInBasket[$bi]["QUANTITY"]);
						$arProductsInBasket[$bi]["DISCOUNT_PRICE"] = DoubleVal($arProductsInBasket[$bi]["PRICE"]) - $curDiscount;
					}
				}
				else
				{
					$DISCOUNT_PRICE = CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $BASE_LANG_CURRENCY);
					$DISCOUNT_PRICE = roundEx($DISCOUNT_PRICE, SALE_VALUE_PRECISION);
					$DISCOUNT_PRICE_tmp = 0;
					for ($bi = 0; $bi < count($arProductsInBasket); $bi++)
					{
						$curDiscount = roundEx(DoubleVal($arProductsInBasket[$bi]["PRICE"]) * $DISCOUNT_PRICE / $ORDER_PRICE, SALE_VALUE_PRECISION);
						$arDiscounts[IntVal($arProductsInBasket[$bi]["ID"])] = $curDiscount;
						$arProductsInBasket[$bi]["DISCOUNT_PRICE"] = DoubleVal($arProductsInBasket[$bi]["PRICE"]) - $curDiscount;
						$DISCOUNT_PRICE_tmp += $curDiscount * IntVal($arProductsInBasket[$bi]["QUANTITY"]);
					}
					$DISCOUNT_PRICE = $DISCOUNT_PRICE_tmp;
				}
			}

			$PAY_CURRENT_ACCOUNT = $_REQUEST["PAY_CURRENT_ACCOUNT"];
			if ($PAY_CURRENT_ACCOUNT != "Y")
				$PAY_CURRENT_ACCOUNT = "N";

			// TAX
			$TAX_EXEMPT = (($_REQUEST["TAX_EXEMPT"]=="Y") ? "Y" : "N");
			if ($TAX_EXEMPT == "N")
			{
				unset($arTaxExempt);
				$arTaxExempt = array();
			}

			$TAX_PRICE = 0;
			$arTaxList = array();
			$dbTaxRate = CSaleTaxRate::GetList(
					array("APPLY_ORDER"=>"ASC"),
					array(
							"LID" => SITE_ID,
							"PERSON_TYPE_ID" => $PERSON_TYPE,
							"ACTIVE" => "Y",
							"LOCATION" => IntVal($TAX_LOCATION)
						)
				);
			while ($arTaxRate = $dbTaxRate->Fetch())
			{
				if (!in_array(IntVal($arTaxRate["TAX_ID"]), $arTaxExempt))
				{
					$arTaxList[] = $arTaxRate;
				}
			}

			$arTaxSums = array();
			if (count($arTaxList) > 0)
			{
				for ($i = 0; $i < count($arProductsInBasket); $i++)
				{
					$TAX_PRICE_tmp = CSaleOrderTax::CountTaxes(
							$arProductsInBasket[$i]["DISCOUNT_PRICE"] * IntVal($arProductsInBasket[$i]["QUANTITY"]),
							$arTaxList,
							$BASE_LANG_CURRENCY
						);

					for ($j = 0; $j < count($arTaxList); $j++)
					{
						$arTaxList[$j]["VALUE_MONEY"] += $arTaxList[$j]["TAX_VAL"];
					}
				}

				for ($i = 0; $i < count($arTaxList); $i++)
				{
					$arTaxSums[$arTaxList[$i]["TAX_ID"]]["VALUE"] = $arTaxList[$i]["VALUE_MONEY"];
					$arTaxSums[$arTaxList[$i]["TAX_ID"]]["NAME"] = $arTaxList[$i]["NAME"];
					if ($arTaxList[$i]["IS_IN_PRICE"] != "Y")
					{
						$TAX_PRICE += $arTaxList[$i]["VALUE_MONEY"];
					}
				}
			}

			if (strlen($errorMessage) > 0)
				$CurrentStep = 4;
		}

		if (strlen($errorMessage) <= 0 && $CurrentStep > 5)
		{
			$ORDER_DESCRIPTION = Trim($_REQUEST["ORDER_DESCRIPTION"]);

			if (strlen($errorMessage) > 0)
				$CurrentStep = 5;

			if (strlen($errorMessage) <= 0)
			{
				$totalOrderPrice = $ORDER_PRICE + $DELIVERY_PRICE + $TAX_PRICE - $DISCOUNT_PRICE;

				$arFields = array(
						"LID" => SITE_ID,
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"PAYED" => "N",
						"CANCELED" => "N",
						"STATUS_ID" => "N",
						"PRICE" => $totalOrderPrice,
						"CURRENCY" => $BASE_LANG_CURRENCY,
						"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
						"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
						"PRICE_DELIVERY" => $DELIVERY_PRICE,
						"DELIVERY_ID" => ($DELIVERY_ID > 0 ? $DELIVERY_ID : false),
						"DISCOUNT_VALUE" => $DISCOUNT_PRICE,
						"TAX_VALUE" => $TAX_PRICE,
						"USER_DESCRIPTION" => $ORDER_DESCRIPTION
					);

				// add Guest ID
				if (CModule::IncludeModule("statistic"))
					$arFields["STAT_GID"] = CStatistic::GetEventParam();

				$affiliateID = CSaleAffiliate::GetAffiliate();
				if ($affiliateID > 0)
					$arFields["AFFILIATE_ID"] = $affiliateID;
				else
					$arFields["AFFILIATE_ID"] = false;

				$ORDER_ID = CSaleOrder::Add($arFields);
				$ORDER_ID = IntVal($ORDER_ID);

				if ($ORDER_ID <= 0)
					$errorMessage .= GetMessage("SALE_ERROR_ADD_ORDER")."<br>";
			}

			if (strlen($errorMessage) <= 0)
			{
				CSaleBasket::OrderBasket($ORDER_ID, CSaleBasket::GetBasketUserID(), SITE_ID, $arDiscounts);
			}

			$withdrawSum = 0.0;
			if (strlen($errorMessage) <= 0)
			{
				if ($PAY_CURRENT_ACCOUNT == "Y" && $ALLOW_PAY_FROM_ACCOUNT == "Y")
				{
					$withdrawSum = CSaleUserAccount::Withdraw(
							$GLOBALS["USER"]->GetID(),
							$totalOrderPrice,
							$BASE_LANG_CURRENCY,
							$ORDER_ID
						);

					if ($withdrawSum > 0)
					{
						$arFields = array(
								"SUM_PAID" => $withdrawSum,
								"USER_ID" => $GLOBALS["USER"]->GetID()
							);
						if ($withdrawSum == $totalOrderPrice)
							$arFields["PAY_SYSTEM_ID"] = false;

						CSaleOrder::Update($ORDER_ID, $arFields);

						if ($withdrawSum == $totalOrderPrice)
							CSaleOrder::PayOrder($ORDER_ID, "Y", False, False);
					}
				}
			}

			if (strlen($errorMessage) <= 0)
			{
				for ($i = 0; $i < count($arTaxList); $i++)
				{
					$arFields = array(
							"ORDER_ID" => $ORDER_ID,
							"TAX_NAME" => $arTaxList[$i]["NAME"],
							"IS_PERCENT" => $arTaxList[$i]["IS_PERCENT"],
							"VALUE" => ($arTaxList[$i]["IS_PERCENT"]=="Y") ? $arTaxList[$i]["VALUE"] : RoundEx(CCurrencyRates::ConvertCurrency($arTaxList[$i]["VALUE"], $arTaxList[$i]["CURRENCY"], $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION),
							"VALUE_MONEY" => $arTaxList[$i]["VALUE_MONEY"],
							"APPLY_ORDER" => $arTaxList[$i]["APPLY_ORDER"],
							"IS_IN_PRICE" => $arTaxList[$i]["IS_IN_PRICE"],
							"CODE" => $arTaxList[$i]["CODE"]
						);
					CSaleOrderTax::Add($arFields);
				}

				if ($PROFILE_ID <= 0)
				{
					if (strlen($PROFILE_NAME) <= 0)
						$PROFILE_NAME = GetMessage("SALE_PROFILE_NAME")." ".Date("Y-m-d");

					$arFields = array(
							"NAME" => $PROFILE_NAME,
							"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
							"PERSON_TYPE_ID" => $PERSON_TYPE
						);
					$USER_PROPS_ID = CSaleOrderUserProps::Add($arFields);
					$USER_PROPS_ID = IntVal($USER_PROPS_ID);
				}

				$dbOrderProperties = CSaleOrderProps::GetList(
						array("SORT" => "ASC"),
						array("PERSON_TYPE_ID" => $PERSON_TYPE),
						false,
						false,
						array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT")
					);
				while ($arOrderProperties = $dbOrderProperties->Fetch())
				{
					$curVal = ${"ORDER_PROP_".$arOrderProperties["ID"]};
					if ($arOrderProperties["TYPE"] == "MULTISELECT")
					{
						$curVal = "";
						for ($i = 0; $i < count(${"ORDER_PROP_".$arOrderProperties["ID"]}); $i++)
						{
							if ($i > 0)
								$curVal .= ",";
							$curVal .= ${"ORDER_PROP_".$arOrderProperties["ID"]}[$i];
						}
					}

					if (strlen($curVal) > 0)
					{
						$arFields = array(
								"ORDER_ID" => $ORDER_ID,
								"ORDER_PROPS_ID" => $arOrderProperties["ID"],
								"NAME" => $arOrderProperties["NAME"],
								"CODE" => $arOrderProperties["CODE"],
								"VALUE" => $curVal
							);
						CSaleOrderPropsValue::Add($arFields);

						if ($PROFILE_ID <= 0 && $arOrderProperties["USER_PROPS"] == "Y" && $USER_PROPS_ID > 0)
						{
							$arFields = array(
									"USER_PROPS_ID" => $USER_PROPS_ID,
									"ORDER_PROPS_ID" => $arOrderProperties["ID"],
									"NAME" => $arOrderProperties["NAME"],
									"VALUE" => $curVal
								);
							CSaleOrderUserPropsValue::Add($arFields);
						}
					}
				}
			}

			// mail message
			if (strlen($errorMessage) <= 0)
			{
				$event = new CEvent;

				$strOrderList = "";
				$dbBasketItems = CSaleBasket::GetList(
					array("NAME" => "ASC"),
					array("ORDER_ID" => $ORDER_ID),
					false,
					false,
					array("ID", "NAME", "QUANTITY")
				);
				while ($arBasketItems = $dbBasketItems->Fetch())
				{
					$strOrderList .= $arBasketItems["NAME"]." - ".$arBasketItems["QUANTITY"]." ".GetMessage("SALE_QUANTITY_UNIT");
					$strOrderList .= "\n";
				}

				$arFields = Array(
					"ORDER_ID" => $ORDER_ID,
					"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID))),
					"ORDER_USER" => ( (strlen($PAYER_NAME) > 0) ? $PAYER_NAME : $GLOBALS["USER"]->GetFullName() ),
					"PRICE" => SaleFormatCurrency($totalOrderPrice, $BASE_LANG_CURRENCY),
					"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
					"EMAIL" => $USER_EMAIL,
					"ORDER_LIST" => $strOrderList
				);
				$event->Send("SALE_NEW_ORDER", SITE_ID, $arFields, "N");

				CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $arFields["ORDER_ID"]));
			}

			if (strlen($errorMessage) <= 0)
			{
				if (strpos($ORDER_PAGE, "?") === False)
					LocalRedirect($ORDER_PAGE."?CurrentStep=7&ORDER_ID=".$ORDER_ID);
				else
					LocalRedirect($ORDER_PAGE."&CurrentStep=7&ORDER_ID=".$ORDER_ID);
			}

			if (strlen($errorMessage) > 0)
				$CurrentStep = 5;
		}
	}
}

/*******************************************************************************/
/*****************  BODY  ******************************************************/
/*******************************************************************************/
if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STOF_AUTH"));
	?>
	<?= ShowError($errorMessage) ?>
	<table border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td width="45%" valign="top">
				<font class="tableheadtext">
				<b><?echo GetMessage("STOF_2REG")?></b>
				</font>
			</td>
			<td width="10%">
				&nbsp;
			</td>
			<td width="45%" valign="top">
				<font class="tableheadtext">
				<b><?echo GetMessage("STOF_2NEW")?></b>
				</font>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<form method="post" action="<?= htmlspecialchars($ORDER_PAGE) ?>" name="order_auth_form">
						<tr>
							<td class="tablebody">
								<font class="tablebodytext">
								<?echo GetMessage("STOF_LOGIN_PROMT")?>
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								<?echo GetMessage("STOF_LOGIN")?> <font color="#FF0000">*</font><br>
								<input type="text" name="USER_LOGIN" maxlength="30" size="30" value="<?= ((strlen($USER_LOGIN) > 0) ? htmlspecialchars($USER_LOGIN) : htmlspecialchars(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"})) ?>" class="inputtext">&nbsp;&nbsp;&nbsp;
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								<?echo GetMessage("STOF_PASSWORD")?> <font color="#FF0000">*</font><br>
								<input type="password" name="USER_PASSWORD" maxlength="30" size="30" class="inputtext">&nbsp;&nbsp;&nbsp;
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								<a href="auth.php?forgot_password=yes&back_url=<?= urlencode($ORDER_PAGE); ?>"><?echo GetMessage("STOF_FORGET_PASSWORD")?></a>
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap align="center">
								<font class="tablebodytext">
								<input type="submit" value="<?echo GetMessage("STOF_NEXT_STEP")?>" class="inputbuttonflat">
								<input type="hidden" name="do_authorize" value="Y">
								</font>
							</td>
						</tr>
					</form>
				</table>
				</td></tr></table>
			</td>
			<td>
				&nbsp;
			</td>
			<td valign="top">
				<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<form method="post" action="<?= htmlspecialchars($ORDER_PAGE) ?>" name="order_reg_form">
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								<?echo GetMessage("STOF_NAME")?> <font color="#FF0000">*</font><br>
								<input type="text" name="NEW_NAME" size="40" value="<?= htmlspecialchars($NEW_NAME) ?>" class="inputtext">&nbsp;&nbsp;&nbsp;
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								<?echo GetMessage("STOF_LASTNAME")?> <font color="#FF0000">*</font><br>
								<input type="text" name="NEW_LAST_NAME" size="40" class="inputtext" value="<?= htmlspecialchars($NEW_LAST_NAME) ?>">&nbsp;&nbsp;&nbsp;
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<font class="tablebodytext">
								E-Mail <font color="#FF0000">*</font><br>
								<input type="text" name="NEW_EMAIL" size="40" class="inputtext" value="<?= htmlspecialchars($NEW_EMAIL) ?>">&nbsp;&nbsp;&nbsp;
								</font>
							</td>
						</tr>
						<tr>
							<td class="tablebody" nowrap>
								<script language="JavaScript">
								<!--
								function ChangeGenerate(val)
								{
									document.order_reg_form.NEW_LOGIN.disabled = val;
									document.order_reg_form.NEW_PASSWORD.disabled = val;
									document.order_reg_form.NEW_PASSWORD_CONFIRM.disabled = val;
									var obj = document.getElementById("tr_login");
									obj.disabled = val;
									obj = document.getElementById("tr_pass");
									obj.disabled = val;
									obj = document.getElementById("tr_pass_conf");
									obj.disabled = val;

									try{document.order_reg_form.NEW_LOGIN.focus();}catch(e){}
								}
								//-->
								</script>
								<table>
									<tr>
										<td width="0%">
											<input type="radio" id="NEW_GENERATE_N" class="inputradio" name="NEW_GENERATE" value="N" OnClick="ChangeGenerate(false)"<?if ($NEW_GENERATE == "N") echo " checked";?>>
										</td>
										<td>
											<font class="tablebodytext">
											<label for="NEW_GENERATE_N"><?echo GetMessage("STOF_MY_PASSWORD")?></label>
											</font>
										</td>
									</tr>
									<tr id="tr_login">
										<td width="0%">&nbsp;&nbsp;&nbsp;</td>
										<td>
											<font class="tablebodytext">
											<?echo GetMessage("STOF_LOGIN")?> <font color="#FF0000">*</font><br>
											<input type="text" name="NEW_LOGIN" size="30" class="inputtext" value="<?= htmlspecialchars($NEW_LOGIN) ?>">
											</font>
										</td>
									</tr>
									<tr id="tr_pass">
										<td width="0%">&nbsp;&nbsp;&nbsp;</td>
										<td>
											<font class="tablebodytext">
											<?echo GetMessage("STOF_PASSWORD")?> <font color="#FF0000">*</font><br>
											<input type="password" name="NEW_PASSWORD" size="30" class="inputtext">
											</font>
										</td>
									</tr>
									<tr id="tr_pass_conf">
										<td width="0%">&nbsp;&nbsp;&nbsp;</td>
										<td>
											<font class="tablebodytext">
											<?echo GetMessage("STOF_RE_PASSWORD")?> <font color="#FF0000">*</font><br>
											<input type="password" name="NEW_PASSWORD_CONFIRM" size="30" class="inputtext">
											</font>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="tablebody">
								<font class="tablebodytext">
								<input type="radio" id="NEW_GENERATE_Y" class="inputradio" name="NEW_GENERATE" value="Y" OnClick="ChangeGenerate(true)"<?if ($NEW_GENERATE != "N") echo " checked";?>> <label for="NEW_GENERATE_Y"><?echo GetMessage("STOF_SYS_PASSWORD")?></label>
								</font>
								<script language="JavaScript">
								<!--
								ChangeGenerate(<?= (($NEW_GENERATE != "N") ? "true" : "false") ?>);
								//-->
								</script>
							</td>
						</tr>

						<?
						/* CAPTCHA */
						if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
						{
							?>
							<tr>
								<td class="tablebody"><br>
									<font class="tableheadtext"><b><?=GetMessage("CAPTCHA_REGF_TITLE")?></b></font>
								</td>
							</tr>
							<tr>
								<td class="tablebody">
									<?
									$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
									?>
									<input type="hidden" name="captcha_sid" value="<?= htmlspecialchars($capCode) ?>">
									<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialchars($capCode) ?>" width="180" height="40">
								</td>
							</tr>
							<tr valign="middle">
								<td class="tablebody">
									<font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("CAPTCHA_REGF_PROMT")?>:</font><br>
									<input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext">
								</td>
							</tr>
							<?
						}
						/* CAPTCHA */
						?>

						<tr>
							<td class="tablebody" align="center">
								<font class="tablebodytext">
								<input type="submit" value="<?echo GetMessage("STOF_NEXT_STEP")?>" class="inputbuttonflat">
								<input type="hidden" name="do_register" value="Y">
								</font>
							</td>
						</tr>
					</form>
				</table>
				</td></tr></table>
			</td>
		</tr>
	</table>

	<font class="tablebodytext">
	<br><br>
	<?echo GetMessage("STOF_REQUIED_FIELDS_NOTE")?><br><br>
	<?echo GetMessage("STOF_EMAIL_NOTE")?><br><br>
	<?echo GetMessage("STOF_PRIVATE_NOTES")?>
	</font>
	<?
}
else
{
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STOF_MAKING_ORDER"));

	$SKIP_FIRST_STEP = (($_REQUEST["SKIP_FIRST_STEP"] == "Y") ? "Y" : "N");
	if ($CurrentStep == 1)
	{
		$SKIP_FIRST_STEP = "N";
		$numPersonTypes = 0;
		$curOnePersonType = 0;

		$dbPersonTypesList = CSalePersonType::GetList(
				array("SORT" => "ASC"),
				array("LID" => SITE_ID)
			);
		while ($arPersonTypesList = $dbPersonTypesList->Fetch())
		{
			$numPersonTypes++;
			if ($numPersonTypes >= 2)
				break;

			if ($curOnePersonType <= 0)
				$curOnePersonType = IntVal($arPersonTypesList["ID"]);
		}

		if ($numPersonTypes < 2)
		{
			$SKIP_FIRST_STEP = "Y";
			$CurrentStep = 2;
			$PERSON_TYPE = $curOnePersonType;
		}
	}


	$SKIP_THIRD_STEP = (($_REQUEST["SKIP_THIRD_STEP"] == "Y") ? "Y" : "N");
	if ($CurrentStep < 3)
	{
		if (strlen($_REQUEST["SKIP_THIRD_STEP"]) <= 0 && IntVal($PERSON_TYPE) > 0)
		{
			$SKIP_THIRD_STEP = "N";

			$dbOrderProps = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(
							"PERSON_TYPE_ID" => $PERSON_TYPE,
							"IS_LOCATION" => "Y"
						),
					false,
					false,
					array("ID", "SORT")
				);
			if (!($arOrderProps = $dbOrderProps->Fetch()))
				$SKIP_THIRD_STEP = "Y";
		}
	}
	elseif ($CurrentStep == 3)
	{
		if (IntVal($DELIVERY_LOCATION) > 0)
		{
			$numDelivery = 0;
			$curOneDelivery = 0;

			$dbDelivery = CSaleDelivery::GetList(
					array(),
					array(
							"LID" => SITE_ID,
							"+<=WEIGHT_FROM" => $ORDER_WEIGHT,
							"+>=WEIGHT_TO" => $ORDER_WEIGHT,
							"+<=ORDER_PRICE_FROM" => $ORDER_PRICE,
							"+>=ORDER_PRICE_TO" => $ORDER_PRICE,
							"ACTIVE" => "Y",
							"LOCATION" => $DELIVERY_LOCATION
						)
				);
			while ($arDelivery = $dbDelivery->Fetch())
			{
				$numDelivery++;
				if ($numDelivery >= 2)
					break;

				if ($curOneDelivery <= 0)
					$curOneDelivery = $arDelivery["ID"];
			}

			if ($numDelivery < 2)
			{
				$SKIP_THIRD_STEP = "Y";
				$CurrentStep = 4;
				$DELIVERY_ID = $curOneDelivery;
			}
		}
		else
		{
			$SKIP_THIRD_STEP = "Y";
			$CurrentStep = 4;
		}
	}


/*
	$SKIP_THIRD_STEP = (($_REQUEST["SKIP_THIRD_STEP"] == "Y") ? "Y" : "N");
echo "SKIP_THIRD_STEP1=".$SKIP_THIRD_STEP.";<br>";
echo "CurrentStep1=".$CurrentStep.";<br>";
	if ($CurrentStep < 3)
	{
		$SKIP_THIRD_STEP = "N";
echo "SKIP_THIRD_STEP2=".$SKIP_THIRD_STEP.";<br>";
	}
	elseif ($CurrentStep == 3)
	{
		$SKIP_THIRD_STEP = "N";
echo "DELIVERY_LOCATION=".$DELIVERY_LOCATION.";<br>";
		if (IntVal($DELIVERY_LOCATION) > 0)
		{
			$numDelivery = 0;
			$curOneDelivery = 0;

			$dbDelivery = CSaleDelivery::GetList(
					array(),
					array(
							"LID" => SITE_ID,
							"WEIGHT" => $ORDER_WEIGHT,
							"ORDER_PRICE" => $ORDER_PRICE,
							"ACTIVE" => "Y",
							"LOCATION" => $DELIVERY_LOCATION
						)
				);
			while ($arDelivery = $dbDelivery->Fetch())
			{
				$numDelivery++;
				if ($numDelivery >= 2)
					break;

				if ($curOneDelivery <= 0)
					$curOneDelivery = $arDelivery["ID"];
			}

			if ($numDelivery < 2)
			{
				$SKIP_THIRD_STEP = "Y";
				$CurrentStep = 4;
				$DELIVERY_ID = $curOneDelivery;
			}
		}
		else
		{
			$SKIP_THIRD_STEP = "Y";
			$CurrentStep = 4;
		}
echo "SKIP_THIRD_STEP2=".$SKIP_THIRD_STEP.";<br>";
	}
echo "CurrentStep2=".$CurrentStep.";<br>";
*/
	?>


	<?if ($CurrentStep < 6):?>
		<form method="post" action="<?= htmlspecialchars($ORDER_PAGE) ?>" name="order_form">
	<?endif;?>

	<table width="100%" border="0">
	<tr><td>

		<?if ($CurrentStep < 6):?>
			<font class="tablebodytext">
			<?
			$arMenuLine = array(
					0 => GetMessage("STOF_PERSON_TYPE"),
					1 => GetMessage("STOF_MAKING"),
					2 => GetMessage("STOF_DELIVERY"),
					3 => GetMessage("STOF_PAYMENT"),
					4 => GetMessage("STOF_CONFIRM")
				);
			for ($i = 0; $i < count($arMenuLine); $i++)
			{
				if ($SKIP_FIRST_STEP == "Y" && $i == 0)
					continue;
				if ($SKIP_THIRD_STEP == "Y" && $i == 2)
					continue;
				if ($i > 0 && $SKIP_FIRST_STEP != "Y" || $i > 1 && $SKIP_FIRST_STEP == "Y")
					echo " &gt; ";

				if ($CurrentStep > $i + 1)
					echo "<a href=\"#\" OnClick=\"document.order_form.CurrentStep.value='".($i + 1)."'; document.order_form.submit();\">".$arMenuLine[$i]."</a>";
				elseif ($CurrentStep == $i + 1)
					echo "<b>".$arMenuLine[$i]."</b>";
				else
					echo $arMenuLine[$i];
			}
			?>
			</font>
		<?endif;?>

	</td></tr>
	<tr><td>

		<br>
		<?= ShowError($errorMessage); ?>

		<?
		//------------------ STEP 1 ----------------------------------------------
		if ($CurrentStep == 1):
		//------------------------------------------------------------------------
		if(CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_1";
			$event3 = "";

			if (is_array($arProductsInBasket))
			{
				foreach($arProductsInBasket as $ar_prod)
				{
					$event3 .= $ar_prod["PRODUCT_ID"].", ";
				}
			}
			$e = $event1."/".$event2."/".$event3;

			if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"])))
			{
				CStatistic::Set_Event($event1, $event2, $event3);
				$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}


		?>
			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
					<td valign="top" width="5%" rowspan="3">&nbsp;</td>
					<td valign="top" width="35%" rowspan="3">
						<font class="tablebodytext">
						<?echo GetMessage("STOF_PROC_DIFFERS")?><br><br>
						<?echo GetMessage("STOF_PRIVATE_NOTES")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%">
						<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td class="tablebody">
									<font class="tablebodytext">
									<?echo GetMessage("STOF_SELECT_PERS_TYPE")?><br><br>
									<?
									$dbPersonType = CSalePersonType::GetList(
											array("SORT" => "ASC"),
											array("LID" => SITE_ID)
										);
									$bFirst = True;
									while ($arPersonType = $dbPersonType->Fetch())
									{
										?><input type="radio" class="inputradio" id="PERSON_TYPE_<?= $arPersonType["ID"] ?>" name="PERSON_TYPE" value="<?= $arPersonType["ID"] ?>" <?if (IntVal($PERSON_TYPE) == IntVal($arPersonType["ID"]) || IntVal($PERSON_TYPE) <= 0 && $bFirst) echo "checked";?>> <label for="PERSON_TYPE_<?= $arPersonType["ID"] ?>"><?= $arPersonType["NAME"] ?></label><br><?
										$bFirst = False;
									}
									?>
									</font>
								</td>
							</tr>
						</table>
						</td></tr></table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%" align="right">
						<!--<input type="reset" name="resetButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CANCEL_BUTTON")?>">!-->
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
				</tr>
			</table>

		<?
		//------------------ STEP 2 ----------------------------------------------
		elseif ($CurrentStep == 2):
		//------------------------------------------------------------------------
		if(CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_2";
			$event3 = "";

			foreach($arProductsInBasket as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // проверим не было ли такого события в сессии
			{
					CStatistic::Set_Event($event1, $event2, $event3);
					$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
		?>

			<?
			function PrintPropsForm($PERSON_TYPE, $USER_PROPS = "Y", $PRINT_TITLE = "")
			{
				$bPropsPrinted = False;

				$PERSON_TYPE = IntVal($PERSON_TYPE);
				$USER_PROPS = (($USER_PROPS == "Y") ? "Y" : "N");
				$PRINT_TITLE = Trim($PRINT_TITLE);

				$propertyGroupID = -1;

				$dbProperties = CSaleOrderProps::GetList(
						array(
								"GROUP_SORT" => "ASC",
								"PROPS_GROUP_ID" => "ASC",
								"SORT" => "ASC",
								"NAME" => "ASC"
							),
						array(
								"PERSON_TYPE_ID" => $PERSON_TYPE,
								"USER_PROPS" => $USER_PROPS
							),
						false,
						false,
						array("ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "GROUP_NAME", "GROUP_SORT", "SORT")
					);
				if ($arProperties = $dbProperties->Fetch())
				{
					if (strlen($PRINT_TITLE) > 0)
					{
						?>
						<font class="tabletitletext"><b><?= $PRINT_TITLE ?></b></font><br><br>
						<?
					}
					$bPropsPrinted = True;
					?>
					<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<?
					do
					{
						if (IntVal($arProperties["PROPS_GROUP_ID"]) != $propertyGroupID)
						{
							?>
							<tr>
								<td colspan="2" align="center" class="tablebody">
									<font class="tableheadtext"><b><?= $arProperties["GROUP_NAME"] ?></b></font>
								</td>
							</tr>
							<?
							$propertyGroupID = IntVal($arProperties["PROPS_GROUP_ID"]);
						}
						?>
						<tr id="tr_prop_field_<?= $arProperties["ID"] ?>">
							<td align="right" valign="top" class="tablebody">
								<font class="tablebodytext"><?= $arProperties["NAME"] ?>:<?
								if ($arProperties["REQUIED"]=="Y" || $arProperties["IS_EMAIL"]=="Y" || $arProperties["IS_PROFILE_NAME"]=="Y" || $arProperties["IS_LOCATION"]=="Y" || $arProperties["IS_LOCATION4TAX"]=="Y" || $arProperties["IS_PAYER"]=="Y")
								{
									?><font class="starrequired">*</font><?
								}
								?></font>
							</td>
							<td align="left" class="tablebody">
								<font class="tablebodytext">
								<?
								$curVal = $_REQUEST["ORDER_PROP_".$arProperties["ID"]];
								?>
								<?
								if ($arProperties["TYPE"] == "CHECKBOX")
								{
									echo '<input type="checkbox" class="inputcheckbox" ';
									echo 'name="ORDER_PROP_'.$arProperties["ID"].'" value="Y"';
									if ($curVal=="Y" || !isset($curVal) && $arProperties["DEFAULT_VALUE"]=="Y")
										echo " checked";
									echo '>';
								}
								elseif ($arProperties["TYPE"] == "TEXT")
								{
									$showValue = (isset($curVal) ? $curVal : $arProperties["DEFAULT_VALUE"]);
									if (strlen($showValue) <= 0)
									{
										if ($arProperties["IS_EMAIL"] == "Y")
											$showValue = $GLOBALS["USER"]->GetEmail();
										elseif ($arProperties["IS_PAYER"] == "Y")
											$showValue = $GLOBALS["USER"]->GetFullName();
									}
									echo '<input type="text" class="inputtext" maxlength="250" ';
									echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 30).'" ';
									echo 'value="'.htmlspecialchars($showValue).'" ';
									echo 'name="ORDER_PROP_'.$arProperties["ID"].'">';
								}
								elseif ($arProperties["TYPE"] == "SELECT")
								{
									echo '<select name="ORDER_PROP_'.$arProperties["ID"].'" ';
									echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1).'" ';
									echo 'class="inputselect">';
									$dbVariants = CSaleOrderPropsVariant::GetList(
											array("SORT" => "ASC"),
											array("ORDER_PROPS_ID" => $arProperties["ID"]),
											false,
											false,
											array("*")
										);
									while ($arVariants = $dbVariants->Fetch())
									{
										echo '<option value="'.htmlspecialchars($arVariants["VALUE"]).'"';
										if ($arVariants["VALUE"] == $curVal || !isset($curVal) && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"])
											echo " selected";
										echo '>'.htmlspecialcharsEx($arVariants["NAME"]).'</option>';
									}
									echo '</select>';
								}
								elseif ($arProperties["TYPE"] == "MULTISELECT")
								{
									echo '<select multiple name="ORDER_PROP_'.$arProperties["ID"].'[]" ';
									echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 5).'" ';
									echo 'class="inputselect">';
									$arCurVal = array();
									for ($i = 0; $i < count($curVal); $i++)
										$arCurVal[$i] = Trim($curVal[$i]);
									$arDefVal = Split(",", $arProperties["DEFAULT_VALUE"]);
									for ($i = 0; $i < count($arDefVal); $i++)
										$arDefVal[$i] = Trim($arDefVal[$i]);

									$dbVariants = CSaleOrderPropsVariant::GetList(
											array("SORT" => "ASC"),
											array("ORDER_PROPS_ID" => $arProperties["ID"]),
											false,
											false,
											array("*")
										);
									while ($arVariants = $dbVariants->Fetch())
									{
										echo '<option value="'.htmlspecialchars($arVariants["VALUE"]).'"';
										if (in_array($arVariants["VALUE"], $arCurVal) || !isset($curVal) && in_array($arVariants["VALUE"], $arDefVal))
											echo " selected";
										echo '>'.htmlspecialcharsEx($arVariants["NAME"]).'</option>';
									}
									echo '</select>';
								}
								elseif ($arProperties["TYPE"] == "TEXTAREA")
								{
									echo '<textarea class="inputtextarea" ';
									echo 'rows="'.((IntVal($arProperties["SIZE2"]) > 0) ? $arProperties["SIZE2"] : 4).'" ';
									echo 'cols="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 40).'" ';
									echo 'name="ORDER_PROP_'.$arProperties["ID"].'">';
									echo ((isset($curVal)) ? htmlspecialchars($curVal) : htmlspecialchars($arProperties["DEFAULT_VALUE"]));
									echo '</textarea>';
								}
								elseif ($arProperties["TYPE"] == "LOCATION")
								{
									echo '<select name="ORDER_PROP_'.$arProperties["ID"].'" ';
									echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1).'" ';
									echo 'class="inputselect">';
									$dbVariants = CSaleLocation::GetList(
											array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
											array("LID" => LANGUAGE_ID),
											false,
											false,
											array("ID", "COUNTRY_NAME", "CITY_NAME", "SORT", "COUNTRY_NAME_LANG", "CITY_NAME_LANG")
										);
									while ($arVariants = $dbVariants->Fetch())
									{
										echo '<option value="'.htmlspecialchars($arVariants["ID"]).'"';
										if (IntVal($arVariants["ID"]) == IntVal($curVal) || !isset($curVal) && IntVal($arVariants["ID"]) == IntVal($arProperties["DEFAULT_VALUE"]))
											echo " selected";
										echo '>'.htmlspecialchars($arVariants["COUNTRY_NAME"].((strlen($arVariants["CITY_NAME"]) > 0) ? " - " : "").$arVariants["CITY_NAME"]).'</option>';
									}
									echo '</select>';
								}
								elseif ($arProperties["TYPE"] == "RADIO")
								{
									$dbVariants = CSaleOrderPropsVariant::GetList(
											array("SORT" => "ASC"),
											array("ORDER_PROPS_ID" => $arProperties["ID"]),
											false,
											false,
											array("*")
										);
									while ($arVariants = $dbVariants->Fetch())
									{
										echo '<input type="radio" class="inputradio" ';
										echo 'name="ORDER_PROP_'.$arProperties["ID"].'" ';
										echo 'id="ID_ORDER_PROP_'.$arProperties["ID"].'_'.$arVariants["ID"].'" ';
										echo 'value="'.htmlspecialchars($arVariants["VALUE"]).'"';
										if ($arVariants["VALUE"] == $curVal || !isset($curVal) && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"])
											echo " checked";
										echo '> <label for="ID_ORDER_PROP_'.$arProperties["ID"].'_'.$arVariants["ID"].'">'.htmlspecialcharsEx($arVariants["NAME"]).'</label><br>';
									}
								}

								if (strlen($arProperties["DESCRIPTION"]) > 0)
								{
									?><br><small><?echo $arProperties["DESCRIPTION"] ?></small><?
								}
								?>
								</font>
							</td>
						</tr>
						<?
					}
					while ($arProperties = $dbProperties->Fetch());
					?>
					</table>
					</td></tr></table>
					<?
				}

				return $bPropsPrinted;
			}	// end function PrintPropsForm($PERSON_TYPE, $USER_PROPS = "Y")
			?>

			<br>
			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
					<td valign="top" width="5%" rowspan="3">&nbsp;</td>
					<td valign="top" width="35%" rowspan="3">
						<font class="tablebodytext">
						<?echo GetMessage("STOF_CORRECT_NOTE")?><br><br>
						<?echo GetMessage("STOF_PRIVATE_NOTES")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%">
						<?
						$bPropsPrinted = PrintPropsForm($PERSON_TYPE, "N", GetMessage("SALE_INFO2ORDER"));

						$bFillProfileFields = False;
						$bFirstProfile = True;

						$dbUserProfiles = CSaleOrderUserProps::GetList(
								array("DATE_UPDATE" => "DESC"),
								array(
										"PERSON_TYPE_ID" => $PERSON_TYPE,
										"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
									)
							);
						if ($arUserProfiles = $dbUserProfiles->Fetch())
						{
							$bFillProfileFields = True;

							if ($bPropsPrinted)
								echo "<br><br>";
							?>
							<font class="tabletitletext"><b><?echo GetMessage("STOF_PROFILES")?></b></font><br><br>

							<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td class="tablebody" colspan="2">
										<font class="tablebodytext">
										<?= GetMessage("SALE_PROFILES_PROMT")?>:
										</font>

										<script language="JavaScript">
										function SetContact(enabled)
										{
											var obj;
											<?
											$dbOrderProps = CSaleOrderProps::GetList(
													array("SORT" => "ASC"),
													array(
															"PERSON_TYPE_ID" => $PERSON_TYPE,
															"USER_PROPS" => "Y"
														),
													false,
													false,
													array()
												);
											while ($arOrderProps = $dbOrderProps->Fetch())
											{
												?>
												if (document.order_form.ORDER_PROP_<?= $arOrderProps["ID"] ?>)
												{
													if ('['+document.order_form.ORDER_PROP_<?= $arOrderProps["ID"] ?>.type+']' == "[undefined]")
														document.order_form.ORDER_PROP_<?= $arOrderProps["ID"] ?>[document.order_form.ORDER_PROP_<?= $arOrderProps["ID"] ?>.length - 1].disabled = !enabled;
													else
														document.order_form.ORDER_PROP_<?= $arOrderProps["ID"] ?>.disabled = !enabled;
												}
												obj = document.getElementById("tr_prop_field_<?= $arOrderProps["ID"] ?>");
												obj.disabled = !enabled;
												<?
											}
											?>
										}
										</script>
									</td>
								</tr>
								<?
								do
								{
									?>
									<tr>
										<td class="tablebody" valign="top" width="0%">
											<input type="radio" class="inputradio" name="PROFILE_ID" id="ID_PROFILE_ID_<?= $arUserProfiles["ID"] ?>" value="<?= $arUserProfiles["ID"];?>" <?if (IntVal($PROFILE_ID)==IntVal($arUserProfiles["ID"]) || !isset($PROFILE_ID) && $bFirstProfile) echo "checked";?> onClick="SetContact(false)">
										</td>
										<td class="tablebody" valign="top" width="100%">
											<font class="tablebodytext">
											<label for="ID_PROFILE_ID_<?= $arUserProfiles["ID"] ?>">
											<b><?= htmlspecialcharsEx($arUserProfiles["NAME"]) ?></b><br><?
											$bFirstProfile = False;
											$dbUserPropsValues = CSaleOrderUserPropsValue::GetList(
													array("SORT" => "ASC"),
													array("USER_PROPS_ID" => $arUserProfiles["ID"]),
													false,
													false,
													array("VALUE", "PROP_TYPE", "VARIANT_NAME", "SORT")
												);
											while ($arUserPropsValues = $dbUserPropsValues->Fetch())
											{
												$valueTmp = "";

												if ($arUserPropsValues["PROP_TYPE"] == "SELECT"
													|| $arUserPropsValues["PROP_TYPE"] == "MULTISELECT"
													|| $arUserPropsValues["PROP_TYPE"] == "RADIO")
												{
													$valueTmp = $arUserPropsValues["VARIANT_NAME"];
												}
												elseif ($arUserPropsValues["PROP_TYPE"] == "LOCATION")
												{
													if ($arLocation = CSaleLocation::GetByID($arUserPropsValues["VALUE"], LANGUAGE_ID))
													{
														$valueTmp = $arLocation["COUNTRY_NAME"];
														if (strlen($arLocation["COUNTRY_NAME"]) > 0
															&& strlen($arLocation["CITY_NAME"]) > 0)
														{
															$valueTmp .= " - ";
														}
														$valueTmp .= $arLocation["CITY_NAME"];
													}
												}
												else
													$valueTmp = $arUserPropsValues["VALUE"];

												if (strlen($valueTmp) > 0)
													echo $valueTmp."<br>";
											}
											?>
											</label>
											</font>
										</td>
									</tr>
									<?
								}
								while ($arUserProfiles = $dbUserProfiles->Fetch());
								?>
								<tr>
									<td class="tablebody" width="0%">
										<input type="radio" class="inputradio" name="PROFILE_ID" id="ID_PROFILE_ID_0" value="0" <?if (isset($PROFILE_ID) && IntVal($PROFILE_ID)==0 || !isset($PROFILE_ID) && $bFirstProfile) echo "checked";?> onClick="SetContact(true)">
									</td>
									<td class="tablebody" width="100%">
										<font class="tablebodytext">
										<b><label for="ID_PROFILE_ID_0"><?echo GetMessage("SALE_NEW_PROFILE")?></label></b><br>
										</font>
									</td>
								</tr>
							</table>
							</td></tr></table>
							<?
						}
						else
						{
							?><input type="hidden" name="PROFILE_ID" value="0"><?
						}
						?>

						<br><br>
						<?
						PrintPropsForm($PERSON_TYPE, "Y", GetMessage("SALE_NEW_PROFILE_TITLE"));

						if ($bFillProfileFields)
						{
							?>
							<script language="JavaScript">
								SetContact(<?echo (isset($PROFILE_ID) && IntVal($PROFILE_ID)==0 || !isset($PROFILE_ID) && $bFirstProfile)?"true":"false";?>);
							</script>
							<?
						}
						?>

					</td>
				</tr>
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="backButton" class="inputbuttonflat" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
						<!--<input type="reset" name="resetButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CANCEL_BUTTON")?>">!-->
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
				</tr>
			</table>

		<?
		//------------------ STEP 3 ----------------------------------------------
		elseif ($CurrentStep == 3):
		//------------------------------------------------------------------------
		if(CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_3";
			$event3 = "";

			foreach($arProductsInBasket as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // проверим не было ли такого события в сессии
			{
					CStatistic::Set_Event($event1, $event2, $event3);
					$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
		?>

			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
					<td valign="top" width="5%" rowspan="3">&nbsp;</td>
					<td valign="top" width="35%" rowspan="3">
						<font class="tablebodytext">
						<?echo GetMessage("STOF_DELIVERY_NOTES")?><br><br>
						<?echo GetMessage("STOF_PRIVATE_NOTES")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%">
						<font class="tabletitletext"><b><?echo GetMessage("STOF_DELIVERY_PROMT")?></b></font><br><br>

						<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td class="tablebody" colspan="2">
									<font class="tablebodytext">
									<?echo GetMessage("STOF_SELECT_DELIVERY")?><br><br>
									</font>
								</td>
							</tr>
							<?
							$dbDelivery = CSaleDelivery::GetList(
										array("SORT"=>"ASC", "NAME"=>"ASC"),
										array(
												"LID" => SITE_ID,
												"+<=WEIGHT_FROM" => $ORDER_WEIGHT,
												"+>=WEIGHT_TO" => $ORDER_WEIGHT,
												"+<=ORDER_PRICE_FROM" => $ORDER_PRICE,
												"+>=ORDER_PRICE_TO" => $ORDER_PRICE,
												"ACTIVE" => "Y",
												"LOCATION" => $DELIVERY_LOCATION
											)
								);
							$bFirst = True;
							while ($arDelivery = $dbDelivery->Fetch())
							{
								?>
								<tr>
									<td class="tablebody" valign="top" width="0%">
										<input type="radio" class="inputradio" id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" name="DELIVERY_ID" value="<?= $arDelivery["ID"] ?>"<?if (IntVal($DELIVERY_ID) == IntVal($arDelivery["ID"]) || IntVal($DELIVERY_ID) <= 0 && $bFirst) echo " checked";?>>
									</td>
									<td class="tablebody" valign="top" width="100%">
										<font class="tablebodytext">
										<label for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>">
										<b><?= $arDelivery["NAME"] ?></b><br><?
										$bFirst = False;
										if (IntVal($arDelivery["PERIOD_FROM"]) > 0 || IntVal($arDelivery["PERIOD_TO"]) > 0)
										{
											echo GetMessage("SALE_DELIV_PERIOD");
											if (IntVal($arDelivery["PERIOD_FROM"]) > 0)
												echo " ".GetMessage("SALE_FROM")." ".IntVal($arDelivery["PERIOD_FROM"]);
											if (IntVal($arDelivery["PERIOD_TO"]) > 0)
												echo " ".GetMessage("SALE_TO")." ".IntVal($arDelivery["PERIOD_TO"]);
											if ($arDelivery["PERIOD_TYPE"] == "H")
												echo " ".GetMessage("SALE_PER_HOUR")." ";
											elseif ($arDelivery["PERIOD_TYPE"]=="M")
												echo " ".GetMessage("SALE_PER_MONTH")." ";
											else
												echo " ".GetMessage("SALE_PER_DAY")." ";
											echo "<br>";
										}
										echo GetMessage("SALE_DELIV_PRICE")." ".SaleFormatCurrency($arDelivery["PRICE"], $arDelivery["CURRENCY"])."<br>";
										if (strlen($arDelivery["DESCRIPTION"])>0)
										{
											echo $arDelivery["DESCRIPTION"]."<br>";
										}
										?>
										</label>
										</font>
									</td>
								</tr>
								<?
							}
							?>
						</table>
						</td></tr></table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="backButton" class="inputbuttonflat" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
						<!--<input type="reset" name="resetButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CANCEL_BUTTON")?>">!-->
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
				</tr>
			</table>

		<?
		//------------------ STEP 4 ----------------------------------------------
		elseif ($CurrentStep == 4):
		//------------------------------------------------------------------------
		if(CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_4";
			$event3 = "";

			foreach($arProductsInBasket as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // проверим не было ли такого события в сессии
			{
					CStatistic::Set_Event($event1, $event2, $event3);
					$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
		?>

			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
					<td valign="top" width="5%" rowspan="3">&nbsp;</td>
					<td valign="top" width="35%" rowspan="3">
						<font class="tablebodytext">
						<?echo GetMessage("STOF_PRIVATE_NOTES")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%">
						<font class="tabletitletext"><b><?echo GetMessage("STOF_PAYMENT_WAY")?></b></font><br><br>

						<?
						if ($GLOBALS["USER"]->IsAuthorized() && $ALLOW_PAY_FROM_ACCOUNT == "Y")
						{
							$dbUserAccount = CSaleUserAccount::GetList(
									array(),
									array(
											"USER_ID" => $GLOBALS["USER"]->GetID(),
											"CURRENCY" => $BASE_LANG_CURRENCY
										)
								);
							if ($arUserAccount = $dbUserAccount->Fetch())
							{
								if ($arUserAccount["CURRENT_BUDGET"] > 0)
								{
									?>
									<font class="tablebodytext">
									<input type="checkbox" class="inputradio" name="PAY_CURRENT_ACCOUNT" value="Y" checked> <b><?echo GetMessage("STOF_PAY_FROM_ACCOUNT")?></b><br>
									<?echo GetMessage("STOF_ACCOUNT_HINT1")?> <b><?= SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $BASE_LANG_CURRENCY) ?></b><?echo GetMessage("STOF_ACCOUNT_HINT2")?>
									</font><br><br>
									<?
								}
							}
						}
						?>

						<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td class="tablebody" colspan="2">
									<font class="tablebodytext">
									<?echo GetMessage("STOF_PAYMENT_HINT")?><br><br>
									</font>
								</td>
							</tr>
							<?

							$dbPaySystem = CSalePaySystem::GetList(
										array("SORT" => "ASC", "PSA_NAME" => "ASC"),
										array(
												"LID" => SITE_ID,
												"CURRENCY" => $BASE_LANG_CURRENCY,
												"ACTIVE" => "Y",
												"PERSON_TYPE_ID" => $PERSON_TYPE,
												"PSA_HAVE_PAYMENT" => "Y"
											)
								);
							$bFirst = True;
							while ($arPaySystem = $dbPaySystem->Fetch())
							{
								?>
								<tr>
									<td class="tablebody" valign="top" width="0%">
										<input type="radio" class="inputradio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>"<?if (IntVal($PAY_SYSTEM_ID) == IntVal($arPaySystem["ID"]) || IntVal($PAY_SYSTEM_ID) <= 0 && $bFirst) echo " checked";?>>
									</td>
									<td class="tablebody" valign="top" width="100%">
										<font class="tablebodytext">
										<label for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>">
										<b><?= $arPaySystem["PSA_NAME"] ?></b><br><?
										$bFirst = False;
										if (strlen($arPaySystem["DESCRIPTION"])>0)
										{
											echo $arPaySystem["DESCRIPTION"]."<br>";
										}
										?>
										</label>
										</font>
									</td>
								</tr>
								<?
							}
							?>
						</table>
						</td></tr></table>

						<?
						$bHaveTaxExempts = False;
						if ($GLOBALS["USER"]->IsAuthorized())
						{
							if (is_array($arTaxExempt) && count($arTaxExempt)>0)
							{
								$dbTaxRateList = CSaleTaxRate::GetList(
										array("APPLY_ORDER" => "ASC"),
										array(
											"LID" => SITE_ID,
											"PERSON_TYPE_ID" => $PERSON_TYPE,
											"IS_IN_PRICE" => "N",
											"ACTIVE" => "Y",
											"LOCATION" => IntVal($TAX_LOCATION)
										)
									);
								while ($arTaxRateList = $dbTaxRateList->Fetch())
								{
									if (in_array(IntVal($arTaxRateList["TAX_ID"]), $arTaxExempt))
									{
										$bHaveTaxExempts = True;
										break;
									}
								}
							}
						}

						if ($bHaveTaxExempts)
						{
							?>
							<br>
							<font class="tablebodytext">
							<input type="checkbox" class="inputradio" name="TAX_EXEMPT" value="Y" checked> <b><?echo GetMessage("STOF_TAX_EX")?></b><br>
							<?echo GetMessage("STOF_TAX_EX_PROMT")?>
							</font><br><br>
							<?
						}
						?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="backButton" class="inputbuttonflat" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
						<!--<input type="reset" name="resetButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CANCEL_BUTTON")?>">!-->
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
					</td>
				</tr>
			</table>

		<?
		//------------------ STEP 5 ----------------------------------------------
		elseif ($CurrentStep == 5):
		//------------------------------------------------------------------------
		if(CModule::IncludeModule("statistic"))
		{
			$event1 = "eStore";
			$event2 = "Step4_5";
			$event3 = "";

			foreach($arProductsInBasket as $ar_prod)
			{
				$event3 .= $ar_prod["PRODUCT_ID"].", ";
			}
			$e = $event1."/".$event2."/".$event3;

			if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // проверим не было ли такого события в сессии
			{
					CStatistic::Set_Event($event1, $event2, $event3);
					$_SESSION["ORDER_EVENTS"][] = $e;
			}
		}
		?>

			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONFIRM")?>">
					</td>
					<td valign="top" width="5%" rowspan="3">&nbsp;</td>
					<td valign="top" width="35%" rowspan="3">
						<font class="tablebodytext">
						<?echo GetMessage("STOF_CORRECT_PROMT_NOTE")?><br><br>
						<?echo GetMessage("STOF_CONFIRM_NOTE")?><br><br>
						<?echo GetMessage("STOF_CORRECT_ADDRESS_NOTE")?><br><br>
						<?echo GetMessage("STOF_PRIVATE_NOTES")?>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%">
						<?
						$propertyGroupID = -1;

						$dbProperties = CSaleOrderProps::GetList(
								array(
										"GROUP_SORT" => "ASC",
										"PROPS_GROUP_ID" => "ASC",
										"SORT" => "ASC",
										"NAME" => "ASC"
									),
								array(
										"PERSON_TYPE_ID" => $PERSON_TYPE
									),
								false,
								false,
								array("ID", "NAME", "TYPE", "PROPS_GROUP_ID", "GROUP_NAME", "GROUP_SORT", "SORT")
							);
						if ($arProperties = $dbProperties->Fetch())
						{
							?>
							<font class="tabletitletext"><b><?echo GetMessage("STOF_ORDER_PARAMS")?></b></font><br><br>

							<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<?
								do
								{
									if (IntVal($arProperties["PROPS_GROUP_ID"]) != $propertyGroupID)
									{
										?>
										<tr>
											<td colspan="2" align="center" class="tablebody">
												<font class="tableheadtext"><b><?= $arProperties["GROUP_NAME"] ?></b></font>
											</td>
										</tr>
										<?
										$propertyGroupID = IntVal($arProperties["PROPS_GROUP_ID"]);
									}
									?>
									<tr>
										<td class="tablebody" width="50%" align="right" valign="top">
											<font class="tablebodytext"><?= $arProperties["NAME"] ?>:</font>
										</td>
										<td class="tablebody" width="50%" align="left">
											<font class="tablebodytext">
											<?
											$curVal = ${"ORDER_PROP_".$arProperties["ID"]};
											if ($arProperties["TYPE"] == "CHECKBOX")
											{
												if ($curVal == "Y")
													echo GetMessage("SALE_YES");
												else
													echo GetMessage("SALE_NO");
											}
											elseif ($arProperties["TYPE"] == "TEXT" || $arProperties["TYPE"] == "TEXTAREA")
											{
												echo htmlspecialchars($curVal);
											}
											elseif ($arProperties["TYPE"] == "SELECT" || $arProperties["TYPE"] == "RADIO")
											{
												$arVal = CSaleOrderPropsVariant::GetByValue($arProperties["ID"], $curVal);
												echo htmlspecialchars($arVal["NAME"]);
											}
											elseif ($arProperties["TYPE"] == "MULTISELECT")
											{
												for ($i = 0; $i < count($curVal); $i++)
												{
													$arVal = CSaleOrderPropsVariant::GetByValue($arProperties["ID"], $curVal[$i]);
													if ($i > 0)
														echo ", ";
													echo htmlspecialchars($arVal["NAME"]);
												}
											}
											elseif ($arProperties["TYPE"] == "LOCATION")
											{
												$arVal = CSaleLocation::GetByID($curVal, LANGUAGE_ID);
												echo htmlspecialchars($arVal["COUNTRY_NAME"]);
												if (strlen($arVal["COUNTRY_NAME"]) > 0 && strlen($arVal["CITY_NAME"]) > 0)
													echo " - ";
												echo htmlspecialchars($arVal["CITY_NAME"]);
											}
											?>
											</font>
										</td>
									</tr>
									<?
								}
								while ($arProperties = $dbProperties->Fetch());
								?>
							</table>
							</td></tr></table>
							<?
						}
						?>


						<br><br>
						<font class="tabletitletext"><b><?echo GetMessage("STOF_PAY_DELIV")?></b></font><br><br>

						<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td class="tablebody" width="50%" align="right">
									<font class="tablebodytext"><?= GetMessage("SALE_DELIV_SUBTITLE")?>:</font>
								</td>
								<td class="tablebody" width="50%" align="left">
									<font class="tablebodytext">
									<?
									if ((IntVal($DELIVERY_ID) > 0) && ($arDeliv = CSaleDelivery::GetByID($DELIVERY_ID)))
									{
										echo $arDeliv["NAME"];
									}
									elseif (IntVal($DELIVERY_ID)>0)
									{
										?><font class="errortext"><?echo GetMessage("SALE_ERROR_DELIVERY")?></font><?
									}
									else
									{
										?><?echo GetMessage("SALE_NO_DELIVERY")?><?
									}
									?>
									</font>
								</td>
							</tr>
							<tr>
								<td class="tablebody" width="50%" align="right">
									<font class="tablebodytext"><?= GetMessage("SALE_PAY_SUBTITLE")?>:</font>
								</td>
								<td class="tablebody" width="50%" align="left">
									<font class="tablebodytext">
									<?
									if ((IntVal($PAY_SYSTEM_ID) > 0) && ($arPaySys = CSalePaySystem::GetByID($PAY_SYSTEM_ID, $PERSON_TYPE)))
									{
										echo $arPaySys["PSA_NAME"];
									}
									elseif (IntVal($PAY_SYSTEM_ID) > 0)
									{
										?><font class="errortext"><?echo GetMessage("SALE_ERROR_PAY_SYS")?></font><?
									}
									else
									{
										echo GetMessage("STOF_NOT_SET");
									}
									?>
									</font>
								</td>
							</tr>
						</table>
						</td></tr></table>


						<br><br>
						<font class="tabletitletext"><b><?= GetMessage("SALE_ORDER_CONTENT")?></b></font><br><br>

						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
						<table cellpadding="2" cellspacing="1" border="0" width="100%">
							<tr>
								<td class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_NAME")?></font></td>
								<td class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_PRICETYPE")?></font></td>
								<td class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_QUANTITY")?></font></td>
								<td class="tablehead"><font class="tableheadtext"><?echo GetMessage("SALE_CONTENT_PRICE")?></font></td>
							</tr>
							<?
							$dbBasketItems = CSaleBasket::GetList(
									array("NAME" => "ASC"),
									array(
											"FUSER_ID" => CSaleBasket::GetBasketUserID(),
											"LID" => SITE_ID,
											"ORDER_ID" => "NULL"
										)
								);
							while ($arBasketItems = $dbBasketItems->Fetch())
							{
								if (strlen($arBasketItems["CALLBACK_FUNC"]) > 0)
								{
									CSaleBasket::UpdatePrice($arBasketItems["ID"], $arBasketItems["CALLBACK_FUNC"], $arBasketItems["MODULE"], $arBasketItems["PRODUCT_ID"], $arBasketItems["QUANTITY"]);
									$arBasketItems = CSaleBasket::GetByID($arBasketItems["ID"]);
								}
								if ($arBasketItems["DELAY"] == "N" && $arBasketItems["CAN_BUY"] == "Y")
								{
									?>
									<tr>
										<td class="tablebody"><font class="tablebodytext"><?= htmlspecialcharsEx($arBasketItems["NAME"]) ?></font></td>
										<td class="tablebody"><font class="tablebodytext"><?= htmlspecialcharsEx($arBasketItems["NOTES"]) ?></font></td>
										<td class="tablebody"><font class="tablebodytext"><?= htmlspecialcharsEx($arBasketItems["QUANTITY"]) ?></font></td>
										<td class="tablebody" align="right"><font class="tablebodytext"><?= SaleFormatCurrency($arBasketItems["PRICE"], $arBasketItems["CURRENCY"]) ?></font></td>
									</tr>
									<?
								}
							}
							?>
							<tr>
								<td class="tablebody" align="right"><font class="tablebodytext"><b><?echo GetMessage("SALE_CONTENT_PR_PRICE")?>:</b></font></td>
								<td class="tablebody" align="right" colspan="3"><font class="tablebodytext"><?echo SaleFormatCurrency($ORDER_PRICE, $BASE_LANG_CURRENCY) ?></font></td>
							</tr>
							<tr>
								<td class="tablebody" align="right"><font class="tablebodytext"><b><?echo GetMessage("SALE_CONTENT_DISCOUNT")?>:</b></font></td>
								<td class="tablebody" align="right" colspan="3"><font class="tablebodytext"><?echo SaleFormatCurrency($DISCOUNT_PRICE, $BASE_LANG_CURRENCY) ?>
									<?if (DoubleVal($DISCOUNT_PERCENT)>0):?>
										(<?echo DoubleVal($DISCOUNT_PERCENT)."%";?>)
									<?endif;?>
								</font></td>
							</tr>
							<?
							if (is_array($arTaxList) && count($arTaxList)>0):
								foreach ($arTaxList as $key => $val):
									?>
									<tr>
										<td class="tablebody" align="right">
											<font class="tablebodytext">
											<?
											echo $val["NAME"];
											if ($val["IS_IN_PRICE"]=="Y")
											{
												echo " (".(($val["IS_PERCENT"]=="Y")?"".DoubleVal($val["VALUE"])."%, ":"").GetMessage("SALE_TAX_INPRICE").")";
											}
											elseif ($val["IS_PERCENT"]=="Y")
											{
												echo " (".DoubleVal($val["VALUE"])."%)";
											}
											?>:
											</font>
										</td>
										<td class="tablebody" align="right" colspan="3">
											<font class="tablebodytext"><?= SaleFormatCurrency($val["VALUE_MONEY"], $BASE_LANG_CURRENCY) ?></font>
										</td>
									</tr>
									<?
								endforeach;
							endif;
							?>
							<tr>
								<td class="tablebody" align="right">
									<font class="tablebodytext"><b><?echo GetMessage("SALE_CONTENT_DELIVERY")?>:</b></font>
								</td>
								<td class="tablebody" align="right" colspan="3">
									<font class="tablebodytext"><?= SaleFormatCurrency($DELIVERY_PRICE, $BASE_LANG_CURRENCY) ?></font>
								</td>
							</tr>
							<tr>
								<td class="tablebody" align="right">
									<font class="tablebodytext"><b><?= GetMessage("SALE_CONTENT_ITOG")?>:</b></font>
								</td>
								<td class="tablebody" align="right" colspan="3">
									<font class="tablebodytext"><b><?= SaleFormatCurrency(($ORDER_PRICE + $DELIVERY_PRICE + $TAX_PRICE - $DISCOUNT_PRICE), $BASE_LANG_CURRENCY) ?></b></font>
								</td>
							</tr>
							<?
							if ($GLOBALS["USER"]->IsAuthorized() && $PAY_CURRENT_ACCOUNT == "Y")
							{
								$dbUserAccount = CSaleUserAccount::GetList(
										array(),
										array(
												"USER_ID" => $GLOBALS["USER"]->GetID(),
												"CURRENCY" => $BASE_LANG_CURRENCY
											)
									);
								if ($arUserAccount = $dbUserAccount->Fetch())
								{
									if ($arUserAccount["CURRENT_BUDGET"] > 0)
									{
										?>
										<tr>
											<td class="tablebody" align="right">
												<font class="tablebodytext"><b><?echo GetMessage("STOF_PAY_FROM_ACCOUNT1")?></b></font>
											</td>
											<td class="tablebody" align="right" colspan="3">
												<font class="tablebodytext">
												<?
												$orderTotalSum = $ORDER_PRICE + $DELIVERY_PRICE + $TAX_PRICE - $DISCOUNT_PRICE;
												echo SaleFormatCurrency(
														(($arUserAccount["CURRENT_BUDGET"] >= $orderTotalSum) ? $orderTotalSum : $arUserAccount["CURRENT_BUDGET"]),
														$BASE_LANG_CURRENCY
													);
												?>
												</font>
											</td>
										</tr>
										<?
									}
								}
							}
							?>
						</table>
						</td></tr></table>


						<br><br>
						<font class="tabletitletext"><b><?= GetMessage("SALE_ADDIT_INFO")?></b></font><br><br>

						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableborder"><tr><td>
						<table cellpadding="2" cellspacing="1" border="0" width="100%">
							<tr>
								<td class="tablebody" width="50%" align="right" valign="top">
									<font class="tablebodytext"><?= GetMessage("SALE_ADDIT_INFO_PROMT")?></font>
								</td>
								<td class="tablebody" width="50%" align="left" valign="top">
									<font class="tablebodytext"><textarea rows="4" cols="40" name="ORDER_DESCRIPTION" class="inputtextarea"><?= htmlspecialchars($ORDER_DESCRIPTION) ?></textarea></font>
								</td>
							</tr>
						</table>
						</td></tr></table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="60%" align="right">
						<input type="submit" name="backButton" class="inputbuttonflat" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
						<!--<input type="reset" name="resetButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CANCEL_BUTTON")?>">!-->
						<input type="submit" name="contButton" class="inputbuttonflat" value="<?= GetMessage("SALE_CONFIRM")?>">
					</td>
				</tr>
			</table>

		<?
		//------------------ STEP 6 ----------------------------------------------
		elseif ($CurrentStep == 6):
		//------------------------------------------------------------------------
		?>

		<?
		//------------------ STEP 7 ----------------------------------------------
		elseif ($CurrentStep > 6):
		//------------------------------------------------------------------------
		$ORDER_ID = IntVal($_REQUEST["ORDER_ID"]);

		?>

			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td valign="top" width="60%">
						<?
						$dbOrder = CSaleOrder::GetList(
								array("DATE_UPDATE" => "DESC"),
								array(
										"LID" => SITE_ID,
										"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
										"ID" => $ORDER_ID
									)
							);
						if ($arOrder = $dbOrder->Fetch())
						{
							if(CModule::IncludeModule("statistic"))
							{
								$event1 = "eStore";
								$event2 = "order_confirm";
								$event3 = $ORDER_ID;

								$e = $event1."/".$event2."/".$event3;

								if(!is_array($_SESSION["ORDER_EVENTS"]) || (is_array($_SESSION["ORDER_EVENTS"]) && !in_array($e, $_SESSION["ORDER_EVENTS"]))) // проверим не было ли такого события в сессии
								{
										CStatistic::Set_Event($event1, $event2, $event3);
										$_SESSION["ORDER_EVENTS"][] = $e;
								}
							}
							?>
							<font class="tabletitletext"><b><?echo GetMessage("STOF_ORDER_CREATED")?></b></font><br><br>

							<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td class="tablebody">
										<font class="tablebodytext">
										<?
										$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
										if (is_array($arDateInsert) && count($arDateInsert) > 0)
											$onlyDate = $arDateInsert[0];
										else
											$onlyDate = $arOrder["DATE_INSERT"];
										?>
										<?= str_replace("#ORDER_DATE#", $onlyDate, str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STOF_ORDER_CREATED_DESCR"))); ?><br><br>
										<?= str_replace("#LINK#", htmlspecialchars($PERSONAL_PAGE), GetMessage("STOF_ORDER_VIEW")) ?>
										</font>
									</td>
								</tr>
							</table>
							</td></tr></table>


							<?
							if (IntVal($arOrder["PAY_SYSTEM_ID"]) > 0)
							{
								$dbPaySysAction = CSalePaySystemAction::GetList(
										array(),
										array(
												"PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
												"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"]
											),
										false,
										false,
										array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS")
									);
								if ($arPaySysAction = $dbPaySysAction->Fetch())
								{
									?>
									<br><br>
									<font class="tabletitletext"><b><?echo GetMessage("STOF_ORDER_PAY_ACTION")?></b></font><br><br>

									<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td class="tablebody">
												<font class="tablebodytext">
												<?echo GetMessage("STOF_ORDER_PAY_ACTION1")?> <?= $arPaySysAction["NAME"] ?>
												</font>
											</td>
										</tr>
										<?
										if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
										{
											?>
											<tr>
												<td class="tablebody">
													<font class="tablebodytext">
													<?
													if ($arPaySysAction["NEW_WINDOW"] == "Y")
													{
														?>
														<script language="JavaScript">
															window.open('payment.php?ORDER_ID=<?= $ORDER_ID ?>');
														</script>
														<?= GetMessage(str_replace("#LINK#", "payment.php?ORDER_ID=".$ORDER_ID, GetMessage("STOF_ORDER_PAY_WIN"))) ?>
														<?
													}
													else
													{
														$GLOBALS["SALE_INPUT_PARAMS"] = array();

														$dbUser = CUser::GetByID($arOrder["USER_ID"]);
														if ($arUser = $dbUser->Fetch())
															$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;

														$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = $arOrder;
														$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] = DoubleVal($arOrder["PRICE"]) - DoubleVal($arOrder["SUM_PAID"]);

														$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
														if (is_array($arDateInsert) && count($arDateInsert) > 0)
															$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arDateInsert[0];
														else
															$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arOrder["DATE_INSERT"];


														$arCurOrderProps = array();
														$dbOrderPropVals = CSaleOrderPropsValue::GetList(
																array(),
																array("ORDER_ID" => $ORDER_ID),
																false,
																false,
																array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE")
															);
														while ($arOrderPropVals = $dbOrderPropVals->Fetch())
														{
															$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
																	$arOrderPropVals["ORDER_PROPS_ID"],
																	$arOrderPropVals["CODE"],
																	$arOrderPropVals["PROP_TYPE"],
																	$arOrderPropVals["VALUE"],
																	LANGUAGE_ID
																);
															foreach ($arCurOrderPropsTmp as $key => $value)
															{
																$arCurOrderProps[$key] = $value;
															}
														}

														$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;

														$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);

														$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

														$pathToAction = str_replace("\\", "/", $pathToAction);
														while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
															$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

														if (file_exists($pathToAction))
														{
															if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php"))
																$pathToAction .= "/payment.php";

															include($pathToAction);
														}
													}
													?>
													</font>
												</td>
											</tr>
											<?
										}
										?>
									</table>
									</td></tr></table>
									<?
								}
							}
						}
						else
						{
							?>
							<font class="tabletitletext"><b><?echo GetMessage("STOF_ERROR_ORDER_CREATE")?></b></font><br><br>

							<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td class="tablebody">
										<font class="tablebodytext">
										<?= str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STOF_NO_ORDER")); ?>
										<?echo GetMessage("STOF_CONTACT_ADMIN")?>
										</font>
									</td>
								</tr>
							</table>
							</td></tr></table>
							<?
						}
						?>
					</td>
					<td valign="top" width="5%">&nbsp;</td>
					<td valign="top" width="35%">
						<font class="tablebodytext">
						<?= str_replace("#LINK#", htmlspecialchars($PERSONAL_PAGE), GetMessage("STOF_ORDER_VIEW")) ?><br><br>
						<?= str_replace("#LINK#", htmlspecialchars($PERSONAL_PAGE), GetMessage("STOF_ANNUL_NOTES")) ?><br><br>
						<?= str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STOF_ORDER_ID_NOTES")) ?>
						</font>
					</td>
				</tr>
			</table>

		<?
		//------------------------------------------------------------------------
		endif;
		//------------------------------------------------------------------------
		?>
	</td></tr>
	</table>

	<?if ($CurrentStep > 0 && $CurrentStep < 7):?>
		<input type="hidden" name="ORDER_PRICE" value="<?= DoubleVal($ORDER_PRICE) ?>">
		<input type="hidden" name="ORDER_WEIGHT" value="<?= DoubleVal($ORDER_WEIGHT) ?>">
		<input type="hidden" name="SKIP_FIRST_STEP" value="<?= htmlspecialchars($SKIP_FIRST_STEP) ?>">
		<input type="hidden" name="SKIP_THIRD_STEP" value="<?= htmlspecialchars($SKIP_THIRD_STEP) ?>">
	<?endif?>

	<?if ($CurrentStep > 1 && $CurrentStep < 7):?>
		<input type="hidden" name="PERSON_TYPE" value="<?= IntVal($PERSON_TYPE) ?>">
	<?endif?>

	<?if ($CurrentStep > 2 && $CurrentStep < 7):?>
		<input type="hidden" name="PROFILE_ID" value="<?= IntVal($PROFILE_ID) ?>">
		<input type="hidden" name="DELIVERY_LOCATION" value="<?= IntVal($DELIVERY_LOCATION) ?>">
		<?
		$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array("PERSON_TYPE_ID" => $PERSON_TYPE),
				false,
				false,
				array("ID", "TYPE", "SORT")
			);
		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			if ($arOrderProps["TYPE"] == "MULTISELECT")
			{
				if (count(${"ORDER_PROP_".$arOrderProps["ID"]}) > 0)
				{
					for ($i = 0; $i < count(${"ORDER_PROP_".$arOrderProps["ID"]}); $i++)
					{
						?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>[]" value="<?= htmlspecialchars(${"ORDER_PROP_".$arOrderProps["ID"]}[$i]) ?>"><?
					}
				}
				else
				{
					?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>[]" value=""><?
				}
			}
			else
			{
				?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>" value="<?= htmlspecialchars(${"ORDER_PROP_".$arOrderProps["ID"]}) ?>"><?
			}
		}
		?>
	<?endif?>

	<?if ($CurrentStep > 3 && $CurrentStep < 7):?>
		<input type="hidden" name="DELIVERY_ID" value="<?= IntVal($DELIVERY_ID) ?>">
	<?endif?>

	<?if ($CurrentStep > 4 && $CurrentStep < 7):?>
		<input type="hidden" name="TAX_EXEMPT" value="<?= htmlspecialchars($TAX_EXEMPT) ?>">
		<input type="hidden" name="PAY_SYSTEM_ID" value="<?= IntVal($PAY_SYSTEM_ID) ?>">
		<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="<?= htmlspecialchars($PAY_CURRENT_ACCOUNT) ?>">
	<?endif?>

	<?if ($CurrentStep < 7):?>
		<input type="hidden" name="CurrentStep" value="<?= ($CurrentStep + 1) ?>">
	<?endif?>

	<?if ($CurrentStep < 6):?>
		</form>
	<?endif;?>
	<?
}
?>

<?
//*******************************************************
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>
