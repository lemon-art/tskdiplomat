<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$ID = IntVal($_REQUEST["ID"]);

if (CModule::IncludeModule("sale")):
	$GLOBALS["APPLICATION"]->SetTitle(str_replace("#ID#", $ID, GetMessage("STPOD_TITLE")));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_LIST = Trim($PATH_TO_LIST);
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = $GLOBALS["PATH_TO_LIST"];
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = "index.php";

$PATH_TO_CANCEL = Trim($PATH_TO_CANCEL);
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = $GLOBALS["PATH_TO_CANCEL"];
if (strlen($PATH_TO_CANCEL) <= 0)
	$PATH_TO_CANCEL = "order_cancel.php";

$PATH_TO_PAYMENT = Trim($PATH_TO_PAYMENT);
if (strlen($PATH_TO_PAYMENT) <= 0)
	$PATH_TO_PAYMENT = $GLOBALS["PATH_TO_PAYMENT"];
if (strlen($PATH_TO_PAYMENT) <= 0)
	$PATH_TO_PAYMENT = "payment.php";

if ($ID <= 0)
	LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false)."&SHOW_TYPE=".$_REQUEST["SHOW_TYPE"]);

$errorMessage = "";

$dbOrder = CSaleOrder::GetList(
		array("ID" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
			)
	);

if (!($arOrder = $dbOrder->Fetch()))
	LocalRedirect($PATH_TO_LIST."?".GetFilterParams("filter_", false)."&SHOW_TYPE=".$_REQUEST["SHOW_TYPE"]);
?>
<font class="text"><a name="tb"></a>
<a href="<?= htmlspecialchars($PATH_TO_LIST) ?>?<?= GetFilterParams("filter_", false) ?>&SHOW_TYPE=<?= $_REQUEST["SHOW_TYPE"] ?>" class="navchain"><?= GetMessage("SALE_RECORDS_LIST") ?></a>
<br><br></font>

<table border="0" cellspacing="0" cellpadding="1" width="100%" class="tableborder"><tr><td>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr>
		<td valign="middle" colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("STPOD_ORDER_NO")?> <?= $ID ?> <?echo GetMessage("STPOD_FROM")?> <?= $arOrder["DATE_INSERT"] ?></b></font>
		</td>
	</tr>
	<!--
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("STPOD_DATE_UPDATE")?></font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext"><?= $arOrder["DATE_UPDATE"] ?></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?= GetMessage("STPOD_ORDER_SITE'") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo "[".$arOrder["LID"]."] ";
			$dbSite = CLang::GetByID($arOrder["LID"]);
			if ($arSite = $dbSite->Fetch())
				echo $arSite["NAME"];
			?>
			</font>
		</td>
	</tr>
	//-->
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("STPOD_ORDER_STATUS")?></font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			$arCurrentStatus = CSaleStatus::GetByID($arOrder["STATUS_ID"]);
			echo $arCurrentStatus["NAME"].GetMessage("STPOD_ORDER_FROM").$arOrder["DATE_STATUS"].")";
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?= GetMessage("P_ORDER_PRICE") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext"><?
				echo "<b>".SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"])."</b>";
				if (DoubleVal($arOrder["SUM_PAID"]) > 0)
					echo GetMessage("STPOD_ALREADY_PAID").SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"])."</b>)";
			?></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?= GetMessage("P_ORDER_CANCELED") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo (($arOrder["CANCELED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
			if ($arOrder["CANCELED"] == "Y")
			{
				echo GetMessage("STPOD_ORDER_FROM").$arOrder["DATE_CANCELED"].")";
				if (strlen($arOrder["REASON_CANCELED"]) > 0)
					echo "<br>".$arOrder["REASON_CANCELED"];
			}
			elseif ($arOrder["CANCELED"] != "Y" && $arOrder["STATUS_ID"] != "F" && $arOrder["PAYED"] != "Y")
			{
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="<?= htmlspecialchars($PATH_TO_CANCEL) ?>?ID=<?= $ID ?>&CANCEL=Y&lang=<?= LANG ?>&<?echo GetFilterParams("filter_", false) ?>&SHOW_TYPE=<?= $_REQUEST["SHOW_TYPE"] ?>"><?= GetMessage("SALE_CANCEL_ORDER") ?> &gt;&gt;</a>
				<?
			}
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2" class="tablebody">
			<img src="/bitrix/images/1.gif" width="1" height="8">
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("STPOD_ACCOUNT_DATA")?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?= GetMessage("STPOD_ACCOUNT") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo "[".$arOrder["USER_ID"]."] ";
			$dbUser = CUser::GetByID($arOrder["USER_ID"]);
			if ($arUser = $dbUser->Fetch())
				echo htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]);
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?= GetMessage("STPOD_LOGIN") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext"><?= htmlspecialcharsEx($arUser["LOGIN"]); ?></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("STPOD_EMAIL")?></font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext"><a href="mailto:<?= htmlspecialcharsEx($arUser["EMAIL"]); ?>"><?= htmlspecialcharsEx($arUser["EMAIL"]); ?></a></font>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" colspan="2" class="tablebody">
			<img src="/bitrix/images/1.gif" width="1" height="8">
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("P_ORDER_USER")?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("P_ORDER_PERS_TYPE") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo "[".$arOrder["PERSON_TYPE_ID"]."] ";
			$arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]);
			echo $arPersonType["NAME"]." (".$arPersonType["LID"].")";
			?>
			</font>
		</td>
	</tr>
	<?
	$dbOrderProps = CSaleOrderPropsValue::GetOrderProps($ID);
	$iGroup = -1;
	while ($arOrderProps = $dbOrderProps->Fetch())
	{
		if ($iGroup != IntVal($arOrderProps["PROPS_GROUP_ID"]))
		{
			?>
			<tr>
				<td colspan="2" align="center" class="tablebody">
					<b><font class="tablefieldtext"><?= $arOrderProps["GROUP_NAME"];?></font></b>
				</td>
			</tr>
			<?
			$iGroup = IntVal($arOrderProps["PROPS_GROUP_ID"]);
		}

		?>
		<tr>
			<td width="40%" align="right" valign="top" class="tablebody">
				<font class="tablefieldtext"><?echo $arOrderProps["NAME"] ?>:</font>
			</td>
			<td width="60%" align="left" class="tablebody">
				<font class="tablebodytext">
				<?
				if ($arOrderProps["TYPE"] == "CHECKBOX")
				{
					if ($arOrderProps["VALUE"] == "Y")
						echo GetMessage("SALE_YES");
					else
						echo GetMessage("SALE_NO");
				}
				elseif ($arOrderProps["TYPE"] == "TEXT" || $arOrderProps["TYPE"] == "TEXTAREA")
				{
					echo htmlspecialcharsEx($arOrderProps["VALUE"]);
				}
				elseif ($arOrderProps["TYPE"] == "SELECT" || $arOrderProps["TYPE"] == "RADIO")
				{
					$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $arOrderProps["VALUE"]);
					echo htmlspecialcharsEx($arVal["NAME"]);
				}
				elseif ($arOrderProps["TYPE"] == "MULTISELECT")
				{
					$curVal = split(",", $arOrderProps["VALUE"]);
					for ($i = 0; $i < count($curVal); $i++)
					{
						$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $curVal[$i]);
						if ($i > 0)
							echo ", ";
						echo htmlspecialcharsEx($arVal["NAME"]);
					}
				}
				elseif ($arOrderProps["TYPE"] == "LOCATION")
				{
					$arVal = CSaleLocation::GetByID($arOrderProps["VALUE"], LANG);
					echo htmlspecialcharsEx($arVal["COUNTRY_NAME"].((strlen($arVal["COUNTRY_NAME"])<=0 || strlen($arVal["CITY_NAME"])<=0) ? "" : " - ").$arVal["CITY_NAME"]);
				}
				?>
				</font>
			</td>
		</tr>
		<?
	}
	if ($iGroup >= 0 && strlen($arOrder["USER_DESCRIPTION"]) > 0)
	{
		?>
		<tr>
			<td valign="top" align="right" colspan="2" class="tablebody">
				<img src="/bitrix/images/1.gif" width="1" height="8">
			</td>
		</tr>
		<?
	}
	if (strlen($arOrder["USER_DESCRIPTION"]) > 0)
	{
		?>
		<tr>
			<td valign="top" align="right" width="40%" class="tablebody">
				<font class="tablefieldtext"><?echo GetMessage("P_ORDER_USER_COMMENT") ?>:</font>
			</td>
			<td valign="top" align="left" width="60%" class="tablebody">
				<font class="tablebodytext"><?echo $arOrder["USER_DESCRIPTION"] ?></font>
			</td>
		</tr>
		<?
	}	
	?>
	<tr>
		<td valign="top" align="right" colspan="2" class="tablebody">
			<img src="/bitrix/images/1.gif" width="1" height="8">
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("P_ORDER_PAYMENT")?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("P_ORDER_PAY_SYSTEM") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			if (IntVal($arOrder["PAY_SYSTEM_ID"]) > 0)
			{
				$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
				echo "[".$arPaySys["ID"]."] ".$arPaySys["NAME"]." (".$arPaySys["LID"].")";
			}
			else
			{
				?><?echo GetMessage("STPOD_NONE")?><?
			}
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("P_ORDER_PAYED") ?>:</font>
		</td>
		<td valign="top" align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo (($arOrder["PAYED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
			if ($arOrder["PAYED"] == "Y")
				echo GetMessage("STPOD_ORDER_FROM").$arOrder["DATE_PAYED"].")";
			?>
			</font>
		</td>
	</tr>
	<?
	if ($arOrder["PAYED"] != "Y" && $arOrder["CANCELED"] != "Y")
	{
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
				if (strlen($arPaySysAction["ACTION_FILE"]) > 0)
				{
					?>
					<tr>
						<td valign="top" colspan="2" class="tablebody" align="center">
							<font class="tablebodytext">
							<?
							if ($arPaySysAction["NEW_WINDOW"] == "Y")
							{
								?>
								<a href="<?= htmlspecialchars($PATH_TO_PAYMENT) ?>?ORDER_ID=<?= $ID ?>" target="_blank"><?= GetMessage("SALE_REPEAT_PAY") ?></a>
								<?
							}
							else
							{
								$ORDER_ID = $ID;

								CSalePaySystemAction::InitParamArrays($arOrder, $ID, $arPaySysAction["PARAMS"]);

								$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

								$pathToAction = str_replace("\\", "/", $pathToAction);
								while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
									$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);

								if (file_exists($pathToAction))
								{
									if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php"))
										$pathToAction .= "/payment.php";

									try
									{
										include($pathToAction);
									}
									catch(\Bitrix\Main\SystemException $e)
									{
										if($e->getCode() == CSalePaySystemAction::GET_PARAM_VALUE)
											$message = GetMessage("SOA_TEMPL_ORDER_PS_ERROR");
										else
											$message = $e->getMessage();

										ShowError($message);
									}
								}
							}
							?>
							</font>
						</td>
					</tr>
					<?
				}				
			}
		}
	}
	?>
	<tr>
		<td align="right" colspan="2" class="tablebody">
			<img src="/bitrix/images/1.gif" width="1" height="8">
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?= GetMessage("P_ORDER_DELIVERY")?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("P_ORDER_DELIVERY") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			if (IntVal($arOrder["DELIVERY_ID"]) > 0)
			{
				$arDelivery = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
				echo "[".$arDelivery["ID"]."] ".$arDelivery["NAME"]." (".$arDelivery["LID"].")";
			}
			else
			{
				?><?echo GetMessage("STPOD_NONE")?><?
			}
			?>
			</font>
		</td>
	</tr>
	<!--
	<tr>
		<td align="right" width="40%" class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("P_ORDER_ALLOW_DELIVERY") ?>:</font>
		</td>
		<td align="left" width="60%" class="tablebody">
			<font class="tablebodytext">
			<?
			echo (($arOrder["ALLOW_DELIVERY"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
			if ($arOrder["ALLOW_DELIVERY"] == "Y")
				echo GetMessage("STPOD_ORDER_FROM").$arOrder["DATE_ALLOW_DELIVERY"].")";
			?>
			</font>
		</td>
	</tr>
	//-->
	<tr>
		<td valign="top" align="right" colspan="2" class="tablebody">
			<img src="/bitrix/images/1.gif" width="1" height="8">
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("P_ORDER_BASKET")?></b></font>
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="2" align="center" class="tablebody">
			<?
			$dbBasket = CSaleBasket::GetList(
					array("NAME" => "ASC"),
					array("ORDER_ID" => $ID),
					false,
					false,
					array("ID", "DETAIL_PAGE_URL", "NAME", "NOTES", "QUANTITY", "PRICE", "CURRENCY")
				);
			?>
			<table cellpadding="0" cellspacing="1" border="0" width="100%"><tr><td class="tableborder">
			<table cellpadding="3" cellspacing="1" border="0" width="100%">
				<tr>
					<td class="tablehead">
						<font class="tableheadtext"><?= GetMessage("STPOD_NAME") ?></font>
					</td>
					<td class="tablehead">
						<font class="tableheadtext"><?= GetMessage("STPOD_PROPS") ?></font>
					</td>
					<td class="tablehead">
						<font class="tableheadtext"><?= GetMessage("STPOD_PRICETYPE") ?></font>
					</td>
					<td class="tablehead">
						<font class="tableheadtext"><?= GetMessage("STPOD_QUANTITY") ?></font>
					</td>
					<td class="tablehead">
						<font class="tableheadtext"><?= GetMessage("STPOD_PRICE") ?></font>
					</td>
				</tr>
				<?
				while ($arBasket = $dbBasket->Fetch())
				{
					?>
					<tr>
						<td valign="top" class="tablebody">
							<font class="tablebodytext">
							<?
							if (strlen($arBasket["DETAIL_PAGE_URL"])>0)
								echo "<a href=\"".$arBasket["DETAIL_PAGE_URL"]."\">";
							echo htmlspecialcharsEx($arBasket["NAME"]);
							if (strlen($arBasket["DETAIL_PAGE_URL"])>0)
								echo "</a>";
							?>
							</font>
						</td>
						<td valign="top" class="tablebody">
							<?
							$dbBasketProps = CSaleBasket::GetPropsList(
									array("SORT" => "ASC"),
									array(
											"BASKET_ID" => $arBasket["ID"],
											"!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")
										),
									false,
									false,
									array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
							);
							if ($arBasketProps = $dbBasketProps->Fetch())
							{
								?>
								<table border="0" cellpadding="3" cellspacing="1">
								<?
								do
								{
									?>
									<tr>
										<td valign="top"><font class="tableheadtext"><?echo htmlspecialcharsEx($arBasketProps["NAME"]);?>:</font></td>
										<td valign="top"><font class="tablebodytext"><?echo htmlspecialcharsEx($arBasketProps["VALUE"]);?></font></td>
									</tr>
									<?
								}
								while ($arBasketProps = $dbBasketProps->Fetch());
								?>
								</table>
								<?
							}
							?>
						</td>
						<td valign="top" class="tablebody">
							<font class="tablebodytext"><?echo $arBasket["NOTES"] ?></font>
						</td>
						<td valign="top" class="tablebody">
							<font class="tablebodytext"><?echo $arBasket["QUANTITY"] ?></font>
						</td>
						<td align="right" valign="top" class="tablebody">
							<font class="tablebodytext"><?echo SaleFormatCurrency($arBasket["PRICE"], $arBasket["CURRENCY"]) ?></font>
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<td align="right" class="tablebody">
						<font class="tablebodytext"><b><?= GetMessage("STPOD_DISCOUNT") ?>:</b></font>
					</td>
					<td align="right" colspan="4" class="tablebody">
						<font class="tablebodytext"><?echo SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"]) ?></font>
					</td>
				</tr>
				<?
				$dbTaxList = CSaleOrderTax::GetList(
						array("APPLY_ORDER" => "ASC"),
						array("ORDER_ID" => $ID)
					);
				while ($arTaxList = $dbTaxList->Fetch())
				{
					?>
					<tr>
						<td align="right" class="tablebody">
							<font class="tablebodytext"><?
							echo htmlspecialcharsEx($arTaxList["TAX_NAME"]); 
							if ($arTaxList["IS_IN_PRICE"]=="Y")
								echo " (".(($arTaxList["IS_PERCENT"]=="Y") ? "".DoubleVal($arTaxList["VALUE"])."%, " : "").GetMessage("SALE_TAX_INPRICE").")";
							elseif ($arTaxList["IS_PERCENT"]=="Y")
								echo " (".DoubleVal($arTaxList["VALUE"])."%)";
							?>:</font>
						</td>
						<td align="right" colspan="4" class="tablebody">
							<font class="tablebodytext"><?echo SaleFormatCurrency($arTaxList["VALUE_MONEY"], $arOrder["CURRENCY"]) ?></font>
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<td align="right" class="tablebody">
						<font class="tablebodytext"><b><?= GetMessage("STPOD_TAX") ?>:</b></font>
					</td>
					<td align="right" colspan="4" class="tablebody">
						<font class="tablebodytext"><?echo SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"]) ?></font>
					</td>
				</tr>
				<tr>
					<td align="right" class="tablebody">
						<font class="tablebodytext"><b><?echo GetMessage("STPOD_DELIVERY")?>:</b></font>
					</td>
					<td align="right" colspan="4" class="tablebody">
						<font class="tablebodytext"><?echo SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]) ?></font>
					</td>
				</tr>
				<tr>
					<td align="right" class="tablebody">
						<font class="tablebodytext"><b><?echo GetMessage("STPOD_ITOG")?>:</b></font>
					</td>
					<td align="right" colspan="4" class="tablebody">
						<font class="tablebodytext"><?echo SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]) ?></font>
					</td>
				</tr>
			</table>
			</td></tr></table>
		</td>
	</tr>

</table>
</td></tr></table>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPOD_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("SALE_NO_MODULE_X") ?></b></font>
	<?
endif;
?>