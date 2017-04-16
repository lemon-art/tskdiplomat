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

$PERSON_TYPE = IntVal($PERSON_TYPE);
$FIRST_STEP_GROUP = IntVal($FIRST_STEP_GROUP);
$SECOND_STEP_GROUP = IntVal($SECOND_STEP_GROUP);
$SHIPPING_BILLING_SAME = Trim($SHIPPING_BILLING_SAME);
$ALLOW_PAY_FROM_ACCOUNT = (($ALLOW_PAY_FROM_ACCOUNT == "N") ? "N" : "Y");

$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STO_TITLE"));

$CurrentStep = IntVal($_REQUEST["CurrentStep"]);
if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_REQUEST["backButton"]) > 0)
	$CurrentStep = $CurrentStep - 2;
if ($CurrentStep <= 0)
	$CurrentStep = 1;

$errorMessage = "";
$warningMessage = "";

/*******************************************************************************/
/*****************  ACTION  ****************************************************/
/*******************************************************************************/
$BASE_LANG_CURRENCY = CSaleLang::GetLangCurrency(SITE_ID);

if ($CurrentStep > 0 && $CurrentStep < 4)
{
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
		LocalRedirect($BasketPage);
		$errorMessage .= GetMessage("STO_EMPTY_BASKET").".<br>";
	}

	$PERSON_TYPE = IntVal($PERSON_TYPE);
	if ($PERSON_TYPE <= 0)
	{
		$dbPersonType = CSalePersonType::GetList(
				array("SORT" => "ASC"),
				array("LID" => SITE_ID)
			);
		if ($arPersonType = $dbPersonType->Fetch())
		{
			$PERSON_TYPE = IntVal($arPersonType["ID"]);
		}
	}

	$PROFILE_ID = IntVal($PROFILE_ID);
	if ($PROFILE_ID <= 0)
	{
		if ($GLOBALS["USER"]->IsAuthorized())
		{
			$dbUserProfile = CSaleOrderUserProps::GetList(
					array("DATE_UPDATE" => "DESC"),
					array(
							"USER_ID" => $GLOBALS["USER"]->GetID(),
							"PERSON_TYPE_ID" => $PERSON_TYPE
						)
				);
			if ($arUserProfile = $dbUserProfile->Fetch())
			{
				$PROFILE_ID = IntVal($arUserProfile["ID"]);
			}
		}
	}

	if (strlen($errorMessage) <= 0 && $CurrentStep > 1)
	{
		// <***************** AFTER 1 STEP
		$arCurOrderProps = array();

		$dbProperties = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"PROPS_GROUP_ID" => $FIRST_STEP_GROUP
					)
			);
		while ($arProperties = $dbProperties->Fetch())
		{
			$bErrorField = False;
			$curVal = $_REQUEST["ORDER_PROP_".$arProperties["ID"]];
			if ($arProperties["TYPE"] == "LOCATION" && ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y"))
			{
				if ($arProperties["IS_LOCATION"] == "Y")
					$DELIVERY_LOCATION = IntVal($curVal);
				if ($arProperties["IS_LOCATION4TAX"] == "Y")
					$TAX_LOCATION = IntVal($curVal);

				if (IntVal($curVal) <= 0)
					$bErrorField = True;
			}
			elseif ($arProperties["IS_PROFILE_NAME"] == "Y" || $arProperties["IS_PAYER"] == "Y" || $arProperties["IS_EMAIL"] == "Y")
			{
				if ($arProperties["IS_PROFILE_NAME"] == "Y")
				{
					$PROFILE_NAME = Trim($curVal);
					if (strlen($PROFILE_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arProperties["IS_PAYER"] == "Y")
				{
					$PAYER_NAME = Trim($curVal);
					if (strlen($PAYER_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arProperties["IS_EMAIL"] == "Y")
				{
					$USER_EMAIL = Trim($curVal);
					if (strlen($USER_EMAIL) <= 0 || !check_email($USER_EMAIL))
						$bErrorField = True;
				}
			}
			elseif ($arProperties["REQUIED"] == "Y")
			{
				if ($arProperties["TYPE"] == "TEXT" || $arProperties["TYPE"] == "TEXTAREA" || $arProperties["TYPE"] == "RADIO" || $arProperties["TYPE"] == "SELECT")
				{
					if (strlen($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arProperties["TYPE"] == "LOCATION")
				{
					if (IntVal($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arProperties["TYPE"] == "MULTISELECT")
				{
					if (!is_array($curVal) || count($curVal) <= 0)
						$bErrorField = True;
				}
			}
			if ($bErrorField)
				$errorMessage .= str_replace("#FIELD#", $arProperties["NAME"], GetMessage("STO_EMPTY_FIELD")).".<br>";

			$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
					$arProperties["ID"],
					$arProperties["CODE"],
					$arProperties["TYPE"],
					$curVal,
					LANGUAGE_ID
				);
			foreach ($arCurOrderPropsTmp as $key => $value)
			{
				$arCurOrderProps[$key] = $value;
			}
		}

		$arTaxExempt = array();
		if ($GLOBALS["USER"]->IsAuthorized())
		{
			$arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();

			$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
			while ($arTaxExemptList = $dbTaxExemptList->Fetch())
			{
				if (!in_array(IntVal($arTaxExemptList["TAX_ID"]), $arTaxExempt))
				{
					$arTaxExempt[] = IntVal($arTaxExemptList["TAX_ID"]);
				}
			}
		}

		// PAY SYSTEM
		$PAY_SYSTEM_ID = IntVal($_REQUEST["PAY_SYSTEM_ID"]);
		if ($PAY_SYSTEM_ID <= 0)
		{
			$errorMessage .= GetMessage("STO_SELECT_PS");
		}
		else
		{
			if ($arPaySys = CSalePaySystem::GetByID($PAY_SYSTEM_ID, $PERSON_TYPE))
				$PAY_SYSTEM_ACTION_ID = IntVal($arPaySys["PSA_ID"]);
			else
				$errorMessage .= GetMessage("STO_WRONG_PS");
		}

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
					)
			);
		$DISCOUNT_PRICE = 0;
		$DISCOUNT_PERCENT = 0;
		$arDiscounts = array();
		if ($arDiscount = $dbDiscount->Fetch())
		{
			if ($arDiscount["DISCOUNT_TYPE"] == "P")
			{
				$DISCOUNT_PERCENT = $arDiscount["DISCOUNT_VALUE"];
				for ($i = 0; $i < count($arProductsInBasket); $i++)
				{
					$curDiscount = roundEx(DoubleVal($arProductsInBasket[$i]["PRICE"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
					$arDiscounts[IntVal($arProductsInBasket[$i]["ID"])] = $curDiscount;
					$DISCOUNT_PRICE += $curDiscount * IntVal($arProductsInBasket[$i]["QUANTITY"]);
					$arProductsInBasket[$i]["DISCOUNT_PRICE"] = DoubleVal($arProductsInBasket[$i]["PRICE"]) - $curDiscount;
				}
			}
			else
			{
				$DISCOUNT_PRICE = CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $BASE_LANG_CURRENCY);
				$DISCOUNT_PRICE = roundEx($DISCOUNT_PRICE, SALE_VALUE_PRECISION);
				$DISCOUNT_PRICE_tmp = 0;
				for ($i = 0; $i < count($arProductsInBasket); $i++)
				{
					$curDiscount = roundEx(DoubleVal($arProductsInBasket[$i]["PRICE"]) * $DISCOUNT_PRICE / $ORDER_PRICE, SALE_VALUE_PRECISION);
					$arDiscounts[IntVal($arProductsInBasket[$i]["ID"])] = $curDiscount;
					$arProductsInBasket[$i]["DISCOUNT_PRICE"] = DoubleVal($arProductsInBasket[$i]["PRICE"]) - $curDiscount;
					$DISCOUNT_PRICE_tmp += $curDiscount * IntVal($arProductsInBasket[$i]["QUANTITY"]);
				}
				$DISCOUNT_PRICE = $DISCOUNT_PRICE_tmp;
			}
		}

		// TAX
		$TAX_EXEMPT = (($_REQUEST["TAX_EXEMPT"]=="Y") ? "Y" : "N");
		$TAX_EXEMPT = "Y";	// Always tax exemption
		if ($TAX_EXEMPT == "N")
		{
			unset($arTaxExempt);
			$arTaxExempt = array();
		}

		$TAX_PRICE = 0;
		$arTaxList = array();
		$dbTaxRate = CSaleTaxRate::GetList(
				array("APPLY_ORDER" => "ASC"),
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

		$PAY_CURRENT_ACCOUNT = $_REQUEST["PAY_CURRENT_ACCOUNT"];
		if ($PAY_CURRENT_ACCOUNT != "Y")
			$PAY_CURRENT_ACCOUNT = "N";

		if (strlen($errorMessage) > 0)
			$CurrentStep = 1;
	}

	if (strlen($errorMessage) <= 0 && $CurrentStep > 2)
	{
		// <***************** AFTER 2 STEP
		$dbProperties = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"PROPS_GROUP_ID" => $SECOND_STEP_GROUP
					)
			);
		while ($arProperties = $dbProperties->Fetch())
		{
			$bErrorField = False;
			$curVal = $_REQUEST["ORDER_PROP_".$arProperties["ID"]];

			if ($arProperties["TYPE"] == "LOCATION" && ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y"))
			{
				if ($arProperties["IS_LOCATION"] == "Y")
					$DELIVERY_LOCATION = IntVal($curVal);
				if ($arProperties["IS_LOCATION4TAX"] == "Y")
					$TAX_LOCATION = IntVal($curVal);

				if (IntVal($curVal) <= 0)
					$bErrorField = True;
			}
			elseif ($arProperties["IS_PROFILE_NAME"] == "Y" || $arProperties["IS_PAYER"] == "Y" || $arProperties["IS_EMAIL"] == "Y")
			{
				if ($arProperties["IS_PROFILE_NAME"] == "Y")
				{
					$PROFILE_NAME = Trim($curVal);
					if (strlen($PROFILE_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arProperties["IS_PAYER"] == "Y")
				{
					$PAYER_NAME = Trim($curVal);
					if (strlen($PAYER_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arProperties["IS_EMAIL"] == "Y")
				{
					$USER_EMAIL = Trim($curVal);
					if (strlen($USER_EMAIL) <= 0 || !check_email($USER_EMAIL))
						$bErrorField = True;
				}
			}
			elseif ($arProperties["REQUIED"] == "Y")
			{
				if ($arProperties["TYPE"] == "TEXT" || $arProperties["TYPE"] == "TEXTAREA" || $arProperties["TYPE"] == "RADIO" || $arProperties["TYPE"] == "SELECT")
				{
					if (strlen($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arProperties["TYPE"] == "LOCATION")
				{
					if (IntVal($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arProperties["TYPE"] == "MULTISELECT")
				{
					if (!is_array($curVal) || count($curVal) <= 0)
						$bErrorField = True;
				}
			}
			if ($bErrorField)
				$errorMessage .= str_replace("#FIELD#", $arProperties["NAME"], GetMessage("STO_EMPTY_FIELD")).".<br>";

			$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
					$arProperties["ID"],
					$arProperties["CODE"],
					$arProperties["TYPE"],
					$curVal,
					LANGUAGE_ID
				);
			foreach ($arCurOrderPropsTmp as $key => $value)
			{
				$arCurOrderProps[$key] = $value;
			}
		}


		$totalOrderPrice = roundEx($ORDER_PRICE + $TAX_PRICE - $DISCOUNT_PRICE, SALE_VALUE_PRECISION);
		$userShouldPay = $totalOrderPrice;

		if ($GLOBALS["USER"]->IsAuthorized())
		{
			if ($PAY_CURRENT_ACCOUNT == "Y" && $ALLOW_PAY_FROM_ACCOUNT == "Y")
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
						if ($userShouldPay > $arUserAccount["CURRENT_BUDGET"])
							$userShouldPay = roundEx($userShouldPay - DoubleVal($arUserAccount["CURRENT_BUDGET"]), SALE_VALUE_PRECISION);
						else
							$userShouldPay = 0;
					}
				}
			}
		}


		$arPaySysResult = array(
				"PS_STATUS" => false,
				"PS_STATUS_CODE" => false,
				"PS_STATUS_DESCRIPTION" => false,
				"PS_STATUS_MESSAGE" => false,
				"PS_SUM" => false,
				"PS_CURRENCY" => false,
				"PS_RESPONSE_DATE" => false
			);

		if ($userShouldPay > 0)
		{
			$strPaySysError = "";
			$strPaySysWarning = "";

			$dbPaySysAction = CSalePaySystemAction::GetList(
					array(),
					array(
							"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
							"PERSON_TYPE_ID" => $PERSON_TYPE
						),
					false,
					false,
					array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS")
				);
			if ($arPaySysAction = $dbPaySysAction->Fetch())
			{
				if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
				{
					$GLOBALS["SALE_INPUT_PARAMS"] = array();

					if ($GLOBALS["USER"]->IsAuthorized())
					{
						$dbUser = CUser::GetByID($GLOBALS["USER"]->GetID());
						if ($arUser = $dbUser->Fetch())
							$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;
					}

					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = array(
							"ID" => 0,
							"LID" => SITE_ID,
							"PERSON_TYPE_ID" => $PERSON_TYPE,
							"CANCELED" => "N",
							"STATUS_ID" => "N",
							"DATE_STATUS" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
							"PRICE_DELIVERY" => 0.0,
							"PRICE" => $totalOrderPrice,
							"CURRENCY" => $BASE_LANG_CURRENCY,
							"DISCOUNT_VALUE" => $DISCOUNT_PRICE,
							"SUM_PAID" => 0.0,
							"USER_ID" => (($GLOBALS["USER"]->IsAuthorized()) ? $GLOBALS["USER"]->GetID() : 0),
							"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
							"DELIVERY_ID" => 0,
							"DATE_INSERT" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
							"DATE_UPDATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
							"TAX_VALUE" => $TAX_PRICE
						);
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] = $userShouldPay;
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID)));

					$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;

					$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);

					$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

					$pathToAction = str_replace("\\", "/", $pathToAction);
					while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
						$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

					if (file_exists($pathToAction) && is_dir($pathToAction))
						$pathToAction .= "/pre_payment.php";

					if (file_exists($pathToAction))
					{
						CSalePaySystemAction::IncludePrePaySystem($pathToAction, True, $arPaySysResult, $strPaySysError, $strPaySysWarning, $BASE_LANG_CURRENCY, $ORDER_PRICE, $TAX_PRICE, $DISCOUNT_PRICE, 0.0);
					}
				}
			}

			if (strlen($strPaySysError) > 0)
				$errorMessage .= $strPaySysError;

			if (strlen($strPaySysWarning) > 0)
				$warningMessage .= $strPaySysWarning;
		}

		if (strlen($errorMessage) > 0)
			$CurrentStep = 2;
	}

	if (strlen($errorMessage) <= 0 && $CurrentStep > 2)
	{
		if (!$USER->IsAuthorized())
		{
			if (strlen($USER_EMAIL) > 0)
			{
				$NEW_LOGIN = $USER_EMAIL;

				$pos = strpos($USER_EMAIL, "@");
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
							$NEW_LOGIN = $USER_EMAIL;
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
			}
			else
			{
				$NEW_LOGIN = "buyer".time().GetRandomCode(2);
			}
			$NEW_PASSWORD = GetRandomCode(6);
			$NEW_CONFIRM_PASSWORD = $NEW_PASSWORD;

			if (strlen($USER_EMAIL) <= 0)
				$USER_EMAIL = COption::GetOptionString("sale", "default_email", "admin@".$SERVER_NAME);

			$NEW_NAME = "";
			$NEW_LAST_NAME = "";
			$arPayerName = explode(" ", $PAYER_NAME);
			if (count($arPayerName) > 0)
				$NEW_NAME = $arPayerName[0];
			if (count($arPayerName) > 1)
				$NEW_LAST_NAME = $arPayerName[1];

			$arAuthResult = $GLOBALS["USER"]->Register($NEW_LOGIN, $NEW_NAME, $NEW_LAST_NAME, $NEW_PASSWORD, $NEW_CONFIRM_PASSWORD, $USER_EMAIL, SITE_ID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
				$errorMessage .= GetMessage("STO_ERROR_REG_USER").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br>" );
		}

		if (strlen($errorMessage) <= 0)
		{
			$arFields = array(
					"LID" => SITE_ID,
					"PERSON_TYPE_ID" => $PERSON_TYPE,
					"PAYED" => "N",
					"CANCELED" => "N",
					"STATUS_ID" => "N",
					"PRICE" => $totalOrderPrice,
					"CURRENCY" => $BASE_LANG_CURRENCY,
					"USER_ID" => IntVal($USER->GetID()),
					"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
					"PRICE_DELIVERY" => 0,
					"DELIVERY_ID" => false,
					"DISCOUNT_VALUE" => $DISCOUNT_PRICE,
					"TAX_VALUE" => $TAX_PRICE,
					"PS_STATUS" => $arPaySysResult["PS_STATUS"],
					"PS_STATUS_CODE" => $arPaySysResult["PS_STATUS_CODE"],
					"PS_STATUS_DESCRIPTION" => $arPaySysResult["PS_STATUS_DESCRIPTION"],
					"PS_STATUS_MESSAGE" => $arPaySysResult["PS_STATUS_MESSAGE"],
					"PS_SUM" => $arPaySysResult["PS_SUM"],
					"PS_CURRENCY" => $arPaySysResult["PS_CURRENCY"],
					"PS_RESPONSE_DATE" => $arPaySysResult["PS_RESPONSE_DATE"]
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

			if ($ORDER_ID<=0)
				$errorMessage .= GetMessage("STO_ERROR_SAVING_ORDER").".<br>";
		}

		if (strlen($errorMessage)<=0)
		{
			CSaleBasket::OrderBasket($ORDER_ID, CSaleBasket::GetBasketUserID(), SITE_ID, $arDiscounts);
		}


		$fullOrderPayment = 0.0;
		if (strlen($errorMessage) <= 0)
		{
			if ($PAY_CURRENT_ACCOUNT == "Y" && $ALLOW_PAY_FROM_ACCOUNT == "Y")
			{
				$withdrawSum = roundEx($totalOrderPrice - $userShouldPay, SALE_VALUE_PRECISION);

				$bSuccessPayment = CSaleUserAccount::Pay(
						$GLOBALS["USER"]->GetID(),
						$withdrawSum,
						$BASE_LANG_CURRENCY,
						$ORDER_ID,
						False
					);

				if ($bSuccessPayment)
				{
					$arFields = array(
							"SUM_PAID" => $withdrawSum,
							"USER_ID" => $GLOBALS["USER"]->GetID()
						);
					if ($userShouldPay == 0)
						$arFields["PAY_SYSTEM_ID"] = false;

					CSaleOrder::Update($ORDER_ID, $arFields);
					$fullOrderPayment += $withdrawSum;
				}
			}

			if ($arPaySysResult["PS_STATUS"] == "Y" && $arPaySysResult["PS_CURRENCY"] == $BASE_LANG_CURRENCY)
			{
				$fullOrderPayment += DoubleVal($arPaySysResult["PS_SUM"]);
			}

			if ($fullOrderPayment == $totalOrderPrice)
			{
				CSaleOrder::PayOrder($ORDER_ID, "Y", False, False);
			}

			if ($arPaySysResult["PS_STATUS"] == "Y")
			{
				if (CSaleUserCards::CheckPassword())
				{
					$arFields = array(
							"USER_ID" => $GLOBALS["USER"]->GetID(),
							"ACTIVE" => "Y",
							"SORT" => "100",
							"PAY_SYSTEM_ACTION_ID" => $PAY_SYSTEM_ACTION_ID,
							"CURRENCY" => $BASE_LANG_CURRENCY,
							"CARD_TYPE" => (($arPaySysResult["USER_CARD_TYPE"] && strlen($arPaySysResult["USER_CARD_TYPE"]) > 0) ? $arPaySysResult["USER_CARD_TYPE"] : CSaleUserCards::IdentifyCardType($arPaySysResult["USER_CARD_NUM"])),
							"CARD_NUM" => CSaleUserCards::CryptData($arPaySysResult["USER_CARD_NUM"], "E"),
							"CARD_EXP_MONTH" => $arPaySysResult["USER_CARD_EXP_MONTH"],
							"CARD_EXP_YEAR" => $arPaySysResult["USER_CARD_EXP_YEAR"],
							"DESCRIPTION" => False,
							"CARD_CODE" => $arPaySysResult["USER_CARD_CODE"],
							"SUM_MIN" => False,
							"SUM_MAX" => False,
							"SUM_CURRENCY" => False
						);

					$UserCardID = CSaleUserCards::Add($arFields);
				}
			}
		}

		if (strlen($errorMessage)<=0)
		{
			for ($i = 0; $i < count($arTaxList); $i++)
			{
				$arFields = array(
						"ORDER_ID" => $ORDER_ID,
						"TAX_NAME" => $arTaxList[$i]["NAME"],
						"IS_PERCENT" => $arTaxList[$i]["IS_PERCENT"],
						"VALUE" => ($arTaxList[$i]["IS_PERCENT"]=="Y") ? $arTaxList[$i]["VALUE"] : RoundEx(CCurrencyRates::ConvertCurrency($arTaxList[$i]["VALUE"], $arTaxList[$it]["CURRENCY"], $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION),
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
					$PROFILE_NAME = str_replace("#DATE#", Date("Y-m-d"), GetMessage("STO_PROFILE_TITLE"));

				$arFields = array(
						"NAME" => $PROFILE_NAME,
						"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
						"PERSON_TYPE_ID" => $PERSON_TYPE
					);
				$PROFILE_ID = CSaleOrderUserProps::Add($arFields);
				$PROFILE_ID = IntVal($PROFILE_ID);
			}
			else
			{
				CSaleOrderUserPropsValue::DeleteAll($PROFILE_ID);
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
				$curVal = $_REQUEST["ORDER_PROP_".$arOrderProperties["ID"]];
				if ($arOrderProperties["TYPE"] == "MULTISELECT")
				{
					$curVal = "";
					for ($i = 0; $i < count($_REQUEST["ORDER_PROP_".$arOrderProperties["ID"]]); $i++)
					{
						if ($i > 0)
							$curVal .= ",";
						$curVal .= $_REQUEST["ORDER_PROP_".$arOrderProperties["ID"]][$i];
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

					if ($arOrderProperties["USER_PROPS"]=="Y" && $PROFILE_ID > 0)
					{
						$arFields = array(
								"USER_PROPS_ID" => $PROFILE_ID,
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
				$strOrderList .= $arBasketItems["NAME"]." - ".$arBasketItems["QUANTITY"]." ".GetMessage("STO_SHT").".";
				$strOrderList .= "\n";
			}

			$arFields = Array(
				"ORDER_ID" => $ORDER_ID,
				"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", LANG))),
				"ORDER_USER" => ( (strlen($PAYER_NAME)>0) ? $PAYER_NAME : $GLOBALS["USER"]->GetFullName() ),
				"PRICE" => SaleFormatCurrency(($ORDER_PRICE + $TAX_PRICE - $DISCOUNT_PRICE), $BASE_LANG_CURRENCY),
				"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
				"EMAIL" => $USER_EMAIL,
				"ORDER_LIST" => $strOrderList
			);
			$event->Send("SALE_NEW_ORDER", SITE_ID, $arFields, "N");

			CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $arFields["ORDER_ID"]));
		}

		if (strlen($errorMessage) <= 0)
		{
			$_SESSION["ORDER_".$ORDER_ID."_WARNING"] = $warningMessage;
			if (strpos($ORDER_PAGE, "?") === False)
				LocalRedirect($ORDER_PAGE."?CurrentStep=4&ORDER_ID=".$ORDER_ID);
			else
				LocalRedirect($ORDER_PAGE."&CurrentStep=4&ORDER_ID=".$ORDER_ID);
		}

		if (strlen($errorMessage) > 0)
			$CurrentStep = 2;
	}
}


// ShowPropertiesForm ==>
function ShowPropertiesForm($personType, $arPropsGroup, $bInit, $profileID, $printTitle = "")
{
	$personType = IntVal($personType);
	if (!is_array($arPropsGroup))
		$arPropsGroup = array(IntVal($arPropsGroup));
	$bInit = ($bInit ? True : False);
	$profileID = IntVal($profileID);
	$printTitle = Trim($printTitle);

	if ($bInit && ($profileID > 0))
	{
		$dbUserPropsVal = CSaleOrderUserPropsValue::GetList(
				array("SORT" => "ASC"),
				array("USER_PROPS_ID" => $profileID),
				false,
				false,
				array("ORDER_PROPS_ID", "VALUE", "SORT")
			);
		while ($arUserPropsVal = $dbUserPropsVal->Fetch())
		{
			${"DEF_ORDER_PROP_".$arUserPropsVal["ORDER_PROPS_ID"]} = $arUserPropsVal["VALUE"];
		}
	}

	$propertyGroupID = -1;

	$dbProperties = CSaleOrderProps::GetList(
			array(
					"SORT" => "ASC",
					"NAME" => "ASC"
				),
			array(
					"PERSON_TYPE_ID" => $personType,
					"PROPS_GROUP_ID" => $arPropsGroup
				),
			false,
			false,
			array("ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "GROUP_NAME", "SORT")
		);
	if ($arProperties = $dbProperties->Fetch())
	{
		if (strlen($printTitle) > 0)
		{
			?>
			<br>
			<font class="tabletitletext"><b><?= $printTitle ?></b></font><br><br>
			<?
		}
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
			<tr>
				<td align="right" valign="top" width="40%" class="tablebody">
					<font class="tablebodytext"><?= $arProperties["NAME"] ?>:<?
					if ($arProperties["REQUIED"]=="Y" || $arProperties["IS_EMAIL"]=="Y" || $arProperties["IS_PROFILE_NAME"]=="Y" || $arProperties["IS_LOCATION"]=="Y" || $arProperties["IS_LOCATION4TAX"]=="Y" || $arProperties["IS_PAYER"]=="Y")
					{
						?><font class="starrequired">*</font><?
					}
					?></font>
				</td>
				<td align="left" class="tablebody" width="60%">
					<font class="tablebodytext">
					<?
					$curVal = $_REQUEST["ORDER_PROP_".$arProperties["ID"]];
					if ($bInit && strlen($curVal) <= 0)
						$curVal = ${"DEF_ORDER_PROP_".$arProperties["ID"]};

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
}
// <== ShowPropertiesForm
?>

<?
if (is_array($APPLICATION->arAuthResult) && isset($APPLICATION->arAuthResult["TYPE"]) && ($APPLICATION->arAuthResult["TYPE"]=="ERROR"))
{
	echo ShowError($APPLICATION->arAuthResult["MESSAGE"]);
}
?>


<?
//------------------ STEP 1 ----------------------------------------------
if ($CurrentStep == 1):
//------------------------------------------------------------------------
if(CModule::IncludeModule("statistic"))
{
	$event1 = "eStore";
	$event2 = "Step2_1";

	foreach($arProductsInBasket as $ar_prod)
	{
		$event3 .= $ar_prod["PRODUCT_ID"].", ";
	}
	$e = $event1."/".$event2."/".$event3;

	if(!in_array($e, $_SESSION["ORDER_EVENTS"])) // проверим не было ли такого события в сессии
	{
			CStatistic::Set_Event($event1, $event2, $event3);
			$_SESSION["ORDER_EVENTS"][] = $e;
	}
}

?>

	<?= ShowError($errorMessage); ?>

	<table width="100%">
	<tr><td>

		<font class="tabletitletext"><b><?echo GetMessage("STO_AUTH")?></b></font><br><br>
		<table border="0" cellspacing="0" cellpadding="1" width="100%"><tr><td class="tableborder">
		<table border="0" cellpadding="3" cellspacing="0" width="100%">
			<form method="post" action="<?= htmlspecialchars($ORDER_PAGE.(($s=DeleteParam(array("logout", "login"))) == "" ? "?login=yes":"?$s&login=yes")); ?>" name="bform">
			<?if ($USER->IsAuthorized()):?>
				<tr valign="middle">
					<td class="tablebody" align="right" nowrap width="20%">
						<font class="tablebodytext"><?echo GetMessage("STO_CUR_USER")?></font>
					</td>
					<td class="tablebody" align="left" width="50%">
						<font class="tablebodytext">
						[<?= $USER->GetLogin();?>] <?= $USER->GetFullName();?>
						</font>
					</td>
					<td class="tablebody" align="left" nowrap width="50%">
						<font class="tablebodytext">
						<a href="<?= htmlspecialchars($ORDER_PAGE)."?logout=yes"; ?>"><?echo GetMessage("STO_LOGOUT")?></a>
						</font>
					</td>
				</tr>
			<?else:?>
				<tr valign="middle">
					<td class="tablebody" align="right" nowrap>
						<font class="tablebodytext"><?echo GetMessage("STO_LOGIN")?></font>
					</td>
					<td class="tablebody" align="left" nowrap>
						<input maxlength="20" name="USER_LOGIN" size="15" value="<?= htmlspecialchars(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"}) ?>" class="inputtext">
					</td>
					<td class="tablebody" align="right" nowrap>
						<font class="tablebodytext"><?echo GetMessage("STO_PASSWORD")?></font>
					</td>
					<td class="tablebody" align="left" nowrap>
						<input maxlength="50" name="USER_PASSWORD" size="15" type="password" class="inputtext">
					</td>
					<td class="tablebody" align="right" nowrap>
						<input type="hidden" name="AUTH_FORM" value="Y">
						<input type="hidden" name="TYPE" value="AUTH">
						<input type="submit" name="Login" value="<?echo GetMessage("STO_LOG_IN")?>" class="inputbuttonflat">
					</td>
					<td class="tablebody" align="right" nowrap>
						<font class="tablebodytext">
							<a href="auth.php?forgot_password=yes&back_url=<?= urlencode($ORDER_PAGE);?>"><?echo GetMessage("STO_FORGOT_PAS")?></a>
						</font>
					</td>
				</tr>
			<?endif;?>
			</form>
		</table>
		</td></tr></table>

	</td></tr>

	<form method="post" action="<?= htmlspecialchars($ORDER_PAGE) ?>" name="bform">

	<tr><td>

		<?
		ShowPropertiesForm($PERSON_TYPE, $FIRST_STEP_GROUP, (strlen($errorMessage)<=0), $PROFILE_ID, (($GLOBALS["USER"]->IsAuthorized()) ? GetMessage("STO_ORDER_PARAMS") : GetMessage("STO_NEW_BUYER")));
		?>

		<?
		/* CAPTCHA */
		if (!$USER->IsAuthorized() && COption::GetOptionString("main", "captcha_registration", "N") == "Y")
		{
			?>
			<br>
			<font class="tabletitletext"><b><?echo GetMessage("CAPTCHA_REGF_TITLE")?></b></font><br><br>

			<table border="0" cellspacing="0" cellpadding="1" width="100%"><tr><td class="tableborder">
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
				<tr>
					<td class="tablebody" valign="top" align="right" width="40%" class="tablebody">
						&nbsp;
					</td>
					<td class="tablebody">
						<?
						$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
						?>
						<input type="hidden" name="captcha_sid" value="<?= htmlspecialchars($capCode) ?>">
						<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialchars($capCode) ?>" width="180" height="40">
					</td>
				</tr>
				<tr valign="middle">
					<td class="tablebody" valign="top" align="right" width="40%" class="tablebody">
						<font class="starrequired">*</font><font class="tablebodytext"><?=GetMessage("CAPTCHA_REGF_PROMT")?>:</font>
					</td>
					<td class="tablebody">
						<input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext">
					</td>
				</tr>
			</table>
			</td></tr></table>
			<?
		}
		/* CAPTCHA */
		?>

		<br>
		<font class="tabletitletext"><b><?echo GetMessage("STO_PAYMENT")?></b></font><br><br>
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
					<input type="checkbox" class="inputradio" name="PAY_CURRENT_ACCOUNT" value="Y" checked> <b><?echo GetMessage("STO_PAY_FROM_ACCOUNT")?></b><br>
					<?echo GetMessage("STO_YOU_HAVE1")?> <b><?= SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $BASE_LANG_CURRENCY) ?></b><?echo GetMessage("STO_YOU_HAVE2")?>
					</font><br><br>
					<?
				}
			}
		}
		?>
		<table border="0" cellspacing="0" cellpadding="1" width="100%"><tr><td class="tableborder">
		<table border="0" cellpadding="3" cellspacing="0" width="100%">
			<tr>
				<td class="tablebody" valign="top" align="right" width="40%" class="tablebody">
					<font class="tablebodytext"><?echo GetMessage("STO_PAYMENT_WAY")?></font><font class="starrequired">*</font>
				</td>
				<td class="tablebody" valign="top" width="60%" class="tablebody">
					<font class="tablebodytext">
					<select name="PAY_SYSTEM_ID" class="inputselect">
						<option value=""><?echo GetMessage("STO_PLEASE_SELECT")?></option>
						<?
						$db_ptype = CSalePaySystem::GetList(
								array(
										"SORT" => "ASC",
										"PSA_NAME" => "ASC"
									),
								array(
										"LID" => LANG,
										"CURRENCY" => $BASE_LANG_CURRENCY,
										"ACTIVE" => "Y",
										"PERSON_TYPE_ID" => $PERSON_TYPE,
										"PSA_HAVE_PREPAY" => "Y"
									)
							);
						while ($ptype = $db_ptype->Fetch())
						{
							?><option value="<?echo $ptype["ID"] ?>"<?if (IntVal($_REQUEST["PAY_SYSTEM_ID"]) == IntVal($ptype["ID"])) echo " selected";?>><?echo $ptype["PSA_NAME"] ?></option><?
						}
						?>
					</select>
					</font>
				</td>
			</tr>
		</table>
		</td></tr></table>

		<br>
		<input type="submit" name="contButton" value="<?echo GetMessage("STO_NEXT_STEP")?>" class="inputbuttonflat">
		<input type="hidden" name="CurrentStep" value="2">
		<input type="hidden" name="PROFILE_ID" value="<?= IntVal($PROFILE_ID) ?>">

	</td></tr>

	</form>

	</table>

<?
//------------------ STEP 2 ----------------------------------------------
elseif ($CurrentStep == 2):
//------------------------------------------------------------------------
if(CModule::IncludeModule("statistic"))
{
	$event1 = "eStore";
	$event2 = "Step2_2";

	foreach($arProductsInBasket as $ar_prod)
	{
		$event3 .= $ar_prod["PRODUCT_ID"].", ";
	}
	$e = $event1."/".$event2."/".$event3;

	if(!in_array($e, $_SESSION["ORDER_EVENTS"])) // проверим не было ли такого события в сессии
	{
			CStatistic::Set_Event($event1, $event2, $event3);
			$_SESSION["ORDER_EVENTS"][] = $e;
	}
}

?>

	<table width="100%">

	<form method="post" action="<?= htmlspecialchars($ORDER_PAGE) ?>" name="bform">

	<tr><td>

		<?= ShowError($errorMessage); ?>
		<?= ShowError($warningMessage, "oktext"); ?>

		<?
		$bDoPayAction = False;

		$dbPaySysAction = CSalePaySystemAction::GetList(
				array(),
				array(
						"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
						"PERSON_TYPE_ID" => $PERSON_TYPE
					),
				false,
				false,
				array("NAME", "ACTION_FILE", "NEW_WINDOW", "PARAMS")
			);
		if ($arPaySysAction = $dbPaySysAction->Fetch())
		{
			?>
			<font class="tabletitletext"><b><?echo GetMessage("STO_PAYMENT1")?> <?= $arPaySysAction["NAME"] ?></b></font><br><br>
			<?
			if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
			{
				$GLOBALS["SALE_INPUT_PARAMS"] = array();

				if ($GLOBALS["USER"]->IsAuthorized())
				{
					$dbUser = CUser::GetByID($GLOBALS["USER"]->GetID());
					if ($arUser = $dbUser->Fetch())
						$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;
				}

				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = array(
						"ID" => 0,
						"LID" => SITE_ID,
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"CANCELED" => "N",
						"STATUS_ID" => "N",
						"DATE_STATUS" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
						"CURRENCY" => $BASE_LANG_CURRENCY,
						"USER_ID" => (($GLOBALS["USER"]->IsAuthorized()) ? $GLOBALS["USER"]->GetID() : 0),
						"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
						"DATE_INSERT" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
						"DATE_UPDATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)))
					);
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("SHORT", SITE_ID)));

				$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;

				$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);

				$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

				$pathToAction = str_replace("\\", "/", $pathToAction);
				while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
					$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

				if (file_exists($pathToAction) && is_dir($pathToAction))
					$pathToAction .= "/pre_payment.php";

				if (file_exists($pathToAction))
				{
					?>
					<table border="0" cellspacing="0" cellpadding="1" width="100%"><tr><td class="tableborder">
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr>
							<td class="tablebody" valign="top" class="tablebody">
								<font class="tablebodytext">
								<?
								CSalePaySystemAction::IncludePrePaySystem($pathToAction, False, $arPaySysResult, $strPaySysError, $strPaySysWarning, $BASE_LANG_CURRENCY, 0.0, 0.0, 0.0, 0.0);
								?>
								</font>
							</td>
						</tr>
					</table>
					</td></tr></table>
					<?
				}
			}
		}
		?>


		<?
		$bShowBillingAddressForm = True;
		$dbSkipFormProp = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"CODE" => $SHIPPING_BILLING_SAME
					)
			);
		if ($arSkipFormProp = $dbSkipFormProp->Fetch())
			$bShowBillingAddressForm = ($_REQUEST["ORDER_PROP_".$arSkipFormProp["ID"]] != "Y");

		if ($bShowBillingAddressForm)
		{
			ShowPropertiesForm($PERSON_TYPE, $SECOND_STEP_GROUP, (strlen($errorMessage)<=0), $PROFILE_ID, GetMessage("STO_ORDER_PARAMS"));
		}
		else
		{
			$arPropertiesList = array();

			$dbProperties = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array("PERSON_TYPE_ID" => $PERSON_TYPE),
					false,
					false,
					array("ID", "CODE", "SORT")
				);
			while ($arProperties = $dbProperties->Fetch())
			{
				$arPropertiesList[(strlen($arProperties["CODE"]) > 0) ? $arProperties["CODE"] : $arProperties["ID"]] = IntVal($arProperties["ID"]);
			}

			$dbProperties = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(
							"PERSON_TYPE_ID" => $PERSON_TYPE,
							"PROPS_GROUP_ID" => $SECOND_STEP_GROUP
						)
				);
			while ($arProperties = $dbProperties->Fetch())
			{
				$curCode = substr($arProperties["CODE"], 0, strlen($arProperties["CODE"]) - strlen("_billing"));

				if ($arProperties["TYPE"] == "MULTISELECT")
				{
					$curVal = array();
					if (array_key_exists("ORDER_PROP_".$arPropertiesList[$curCode], $_REQUEST))
						$curVal = $_REQUEST["ORDER_PROP_".$arPropertiesList[$curCode]];

					if (count($curVal) > 0)
					{
						for ($i = 0; $i < count($curVal); $i++)
						{
							?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>[]" value="<?= htmlspecialchars($curVal[$i]) ?>"><?
						}
					}
					else
					{
						?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>[]" value=""><?
					}
				}
				else
				{
					$curVal = "";
					if (array_key_exists("ORDER_PROP_".$arPropertiesList[$curCode], $_REQUEST))
						$curVal = $_REQUEST["ORDER_PROP_".$arPropertiesList[$curCode]];

					?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>" value="<?echo htmlspecialchars($curVal) ?>"><?
				}

				$db_res = CSaleOrderProps::GetList(
						array($by="SORT"), ($order="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE, "CODE"=>substr($props["CODE"], 0, strlen($props["CODE"])-strlen("_billing"))));
				if ($ar_res = $db_res->Fetch())
				{
				}
			}
		}
		?>

		<br>
		<input type="submit" name="contButton" value="<?echo GetMessage("STO_MAKE_ORDER")?>" class="inputbuttonflat">
		<input type="submit" name="backButton" value="<?echo GetMessage("STO_PRIOR_STEP")?>" class="inputbuttonflat">
		<input type="hidden" name="CurrentStep" value="3">
		<input type="hidden" name="PROFILE_ID" value="<?= IntVal($PROFILE_ID) ?>">
		<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="<?= htmlspecialchars($PAY_CURRENT_ACCOUNT) ?>">

		<?
		$dbProperties = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $PERSON_TYPE,
						"PROPS_GROUP_ID" => $FIRST_STEP_GROUP
					)
			);
		while ($arProperties = $dbProperties->Fetch())
		{
			if ($arProperties["TYPE"] == "MULTISELECT")
			{
				if (count($_REQUEST["ORDER_PROP_".$arProperties["ID"]]) > 0)
				{
					for ($i = 0; $i < count($_REQUEST["ORDER_PROP_".$arProperties["ID"]]); $i++)
					{
						?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>[]" value="<?= htmlspecialchars($_REQUEST["ORDER_PROP_".$arProperties["ID"]][$i]) ?>"><?
					}
				}
				else
				{
					?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>[]" value=""><?
				}
			}
			else
			{
				?><input type="hidden" name="ORDER_PROP_<?= $arProperties["ID"] ?>" value="<?= htmlspecialchars($_REQUEST["ORDER_PROP_".$arProperties["ID"]]) ?>"><?
			}
		}
		?>
		<input type="hidden" name="PAY_SYSTEM_ID" value="<?= IntVal($PAY_SYSTEM_ID) ?>">

	</td></tr>

	</form>

	</table>

<?
//------------------ STEP > 2 --------------------------------------------
elseif ($CurrentStep > 2):
//------------------------------------------------------------------------
$ORDER_ID = IntVal($_REQUEST["ORDER_ID"]);

if(CModule::IncludeModule("statistic"))
{
	$event1 = "eStore";
	$event2 = "order_confirm";
	$event3 .= $ORDER_ID;

	$e = $event1."/".$event2."/".$event3;

	if(!in_array($e, $_SESSION["ORDER_EVENTS"])) // проверим не было ли такого события в сессии
	{
			CStatistic::Set_Event($event1, $event2, $event3);
			$_SESSION["ORDER_EVENTS"][] = $e;
	}
}

?>

	<?= ShowError($_SESSION["ORDER_".$ORDER_ID."_WARNING"], "oktext"); ?>

	<table width="100%">
	<tr><td>

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
			?>
			<font class="tabletitletext"><b><?echo GetMessage("STO_ORDER_CREATED")?></b></font><br><br>

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
						<?= str_replace("#ORDER_DATE#", $onlyDate, str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STO_ORDER_CREATED_DESCR"))); ?><br><br>
						<?= str_replace("#LINK#", htmlspecialchars($strLink2PersonalFolder), GetMessage("STO_ORDER_HINT")) ?><br><br>
						<?= str_replace("#LINK#", htmlspecialchars($strLink2PersonalFolder), GetMessage("STO_ORDER_HINT_ANN")) ?><br><br>
						<?= str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STO_ORDER_HINT2")) ?>
						</font>
					</td>
				</tr>
			</table>
			</td></tr></table>
			<?
		}
		else
		{
			?>
			<font class="tabletitletext"><b><?echo GetMessage("STO_ERROR_CREATING")?></b></font><br><br>

			<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td class="tablebody">
						<font class="tablebodytext">
						<?= str_replace("#ORDER_ID#", $ORDER_ID, GetMessage("STO_NO_ORDER")); ?>
						<?echo GetMessage("STO_CONTACT_ADMIN")?>
						</font>
					</td>
				</tr>
			</table>
			</td></tr></table>
			<?
		}
		?>

	</td></tr>
	</table>
<?
//------------------ END -------------------------------------------------
endif;
//------------------------------------------------------------------------
?>


<?
//*******************************************************
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>
