<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$ID = IntVal($_REQUEST["ID"]);

if (CModule::IncludeModule("sale")):
	if ($ID > 0)
		$GLOBALS["APPLICATION"]->SetTitle(str_replace("#ID#", $ID, GetMessage("STPC_TITLE_UPDATE")));
	else
		$GLOBALS["APPLICATION"]->SetTitle(GetMessage("STPC_TITLE_ADD"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_LIST = Trim($PATH_TO_LIST);
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = "cc_list.php";

$PATH_TO_SELF = Trim($PATH_TO_SELF);
if (strlen($PATH_TO_SELF) <= 0)
	$PATH_TO_SELF = "cc_detail.php";


$errorMessage = "";
$bInitVars = false;

if ($_SERVER["REQUEST_METHOD"]=="POST"
	&& (strlen($_POST["save"]) > 0 || strlen($_POST["apply"]) > 0))
{
	if ($ID > 0)
	{
		$dbUserCards = CSaleUserCards::GetList(
				array(),
				array(
						"ID" => $ID,
						"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
					),
				false,
				false,
				array("ID")
			);
		if (!($arUserCards = $dbUserCards->Fetch()))
		{
			$errorMessage .= GetMessage("STPC_NO_CARD").". ";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		$PAY_SYSTEM_ACTION_ID = IntVal($_REQUEST["PAY_SYSTEM_ACTION_ID"]);
		if ($PAY_SYSTEM_ACTION_ID <= 0)
			$errorMessage .= GetMessage("STPC_EMPTY_PAY_SYS").". ";

		$CARD_TYPE = Trim($_REQUEST["CARD_TYPE"]);
		$CARD_TYPE = ToUpper($CARD_TYPE);
		if (strlen($CARD_TYPE) <= 0)
			$errorMessage .= GetMessage("STPC_EMPTY_CARD_TYPE").". ";

		$CARD_NUM = preg_replace("/[\D]+/", "", $_REQUEST["CARD_NUM"]);
		if (strlen($CARD_NUM) <= 0)
		{
			$errorMessage .= GetMessage("STPC_EMPTY_CARDNUM").". ";
		}
		else
		{
			$cardType = CSaleUserCards::IdentifyCardType($CARD_NUM);
			if ($cardType != $CARD_TYPE)
				$errorMessage .= GetMessage("STPC_WRONG_CARDNUM").". ";
		}

		$CARD_EXP_MONTH = IntVal($_REQUEST["CARD_EXP_MONTH"]);
		if ($CARD_EXP_MONTH < 1 || $CARD_EXP_MONTH > 12)
			$errorMessage .= GetMessage("STPC_WRONG_MONTH").". ";

		$CARD_EXP_YEAR = IntVal($_REQUEST["CARD_EXP_YEAR"]);
		if ($CARD_EXP_YEAR < 2000 || $CARD_EXP_YEAR > 2100)
			$errorMessage .= GetMessage("STPC_WRONG_YEAR").". ";

		$CARD_CODE = Trim($_REQUEST["CARD_CODE"]);
	}

	if (strlen($errorMessage) <= 0)
	{
		$SUM_MIN = str_replace(",", ".", $_REQUEST["SUM_MIN"]);
		$SUM_MIN = DoubleVal($SUM_MIN);
		$SUM_MAX = str_replace(",", ".", $_REQUEST["SUM_MAX"]);
		$SUM_MAX = DoubleVal($SUM_MAX);
		$ACTIVE = (($_REQUEST["ACTIVE"] == "Y") ? "Y" : "N");
		$SORT = ((IntVal($_REQUEST["SORT"]) > 0) ? IntVal($_REQUEST["SORT"]) : 100);
		$CURRENCY = Trim($_REQUEST["CURRENCY"]);
		$SUM_CURRENCY = Trim($_REQUEST["SUM_CURRENCY"]);

		if (($SUM_MIN > 0 || $SUM_MAX > 0) && strlen($SUM_CURRENCY) <= 0)
			$errorMessage .= GetMessage("STPC_EMPTY_BCURRENCY").". ";
	}

	if (strlen($errorMessage) <= 0)
	{
		$arFields = array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"ACTIVE" => $ACTIVE,
				"SORT" => $SORT,
				"PAY_SYSTEM_ACTION_ID" => $PAY_SYSTEM_ACTION_ID,
				"CURRENCY" => ((strlen($CURRENCY) > 0) ? $CURRENCY : False),
				"CARD_TYPE" => $CARD_TYPE,
				"CARD_NUM" => CSaleUserCards::CryptData($CARD_NUM, "E"),
				"CARD_EXP_MONTH" => $CARD_EXP_MONTH,
				"CARD_EXP_YEAR" => $CARD_EXP_YEAR,
				"CARD_CODE" => $CARD_CODE,
				"SUM_MIN" => (($SUM_MIN > 0) ? $SUM_MIN : False),
				"SUM_MAX" => (($SUM_MAX > 0) ? $SUM_MAX : False),
				"SUM_CURRENCY" => ((strlen($SUM_CURRENCY) > 0) ? $SUM_CURRENCY : False)
			);

		if ($ID > 0)
		{
			$res = CSaleUserCards::Update($ID, $arFields);
		}
		else
		{
			$ID = CSaleUserCards::Add($arFields);
			$res = ($ID > 0);
		}

		if (!$res)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("STPC_ERROR_SAVING_CARD").". ";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (strlen($_POST["save"]) > 0)
			LocalRedirect($PATH_TO_LIST);
	}
	else
	{
		$bVarsFromForm = true;
	}
}

$dbUserCards = CSaleUserCards::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "USER_ID", "ACTIVE", "SORT", "PAY_SYSTEM_ACTION_ID", "CURRENCY", "CARD_TYPE", "CARD_NUM", "CARD_CODE", "CARD_EXP_MONTH", "CARD_EXP_YEAR", "DESCRIPTION", "SUM_MIN", "SUM_MAX", "SUM_CURRENCY", "TIMESTAMP_X", "LAST_STATUS", "LAST_STATUS_CODE", "LAST_STATUS_DESCRIPTION", "LAST_STATUS_MESSAGE", "LAST_SUM", "LAST_CURRENCY", "LAST_DATE")
	);
if ($arUserCards = $dbUserCards->Fetch())
{
	while (list($key, $val) = each($arUserCards))
		${"str_".$key} = htmlspecialcharsbx($val);

	$str_CARD_NUM = CSaleUserCards::CryptData($str_CARD_NUM, "D");
}
else
{
	$ID = 0;
	$str_ACTIVE = "Y";
	$str_SORT = 100;
}

if ($bVarsFromForm)
{
	$arCardFields = &$DB->GetTableFieldsList("b_sale_user_cards");
	$countCardField = count($arCardFields);
	for ($i = 0; $i < $countCardField; $i++)
		if (array_key_exists($arCardFields[$i], $_REQUEST))
			${"str_".$arCardFields[$i]} = htmlspecialcharsbx($_REQUEST[$arCardFields[$i]]);
}
?>

<?= ShowError($errorMessage); ?>

<a name="tb"></a>
<font class="text">
<a href="<?= htmlspecialcharsbx($PATH_TO_LIST) ?>"><?= GetMessage("STPC_TO_LIST") ?></a>
</font>
<br><br>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>">
<input type="hidden" name="ID" value="<?= $ID ?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="tableborder"><tr><td>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<?if ($ID > 0):?>
		<tr>
			<td valign="top" align="right" class="tablebody"><font class="tablefieldtext">ID:</font></td>
			<td valign="top" class="tablebody"><font class="tablebodytext"><?= $ID ?></font></td>
		</tr>
		<tr>
			<td valign="top" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_TIMESTAMP")?></font></td>
			<td valign="top" class="tablebody"><font class="tablebodytext"><?= $str_TIMESTAMP_X ?></font></td>
		</tr>
	<?endif;?>
	<tr>
		<td valign="top" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_ACTIV")?></font></td>
		<td valign="top" class="tablebody">
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" class="tablebody" align="right"><font class="tablefieldtext"><?echo GetMessage("STPC_SORT")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<input type="text" name="SORT" size="10" class="typeinput" maxlength="20" value="<?= $str_SORT ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_PAY_SYSTEM")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<select name="PAY_SYSTEM_ACTION_ID" class="typeselect">
				<?
				$dbPaySysActions = CSalePaySystemAction::GetList(
						array("PERSON_TYPE_ID" => "ASC", "NAME" => "ASC", "PT_NAME" => "ASC", "PS_NAME" => "ASC"),
						array(
								"PS_LID" => SITE_ID,
								"HAVE_ACTION" => "Y"
							),
						false,
						false,
						array("*")
					);
				while ($arPaySysActions = $dbPaySysActions->Fetch())
				{
					?><option value="<?= $arPaySysActions["ID"] ?>"<?if (IntVal($str_PAY_SYSTEM_ACTION_ID) == IntVal($arPaySysActions["ID"])) echo " selected";?>><?= htmlspecialcharsEx($arPaySysActions["PS_NAME"]." - ".$arPaySysActions["PT_NAME"]) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_CURRENCY")?></font></td>
		<td valign="top" class="tablebody">
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, GetMessage("STPC_ANY"), false, "", "class='typeselect'")?>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_TYPE")?></font></td>
		<td valign="top" class="tablebody">
			<select name="CARD_TYPE" class="typeselect">
				<option value="VISA"<?if ($str_CARD_TYPE == "VISA") echo " selected";?>>Visa</option>
				<option value="MASTERCARD"<?if ($str_CARD_TYPE == "MASTERCARD") echo " selected";?>>MasterCard</option>
				<option value="AMEX"<?if ($str_CARD_TYPE == "AMEX") echo " selected";?>>Amex</option>
				<option value="DINERS"<?if ($str_CARD_TYPE == "DINERS") echo " selected";?>>Diners</option>
				<option value="DISCOVER"<?if ($str_CARD_TYPE == "DISCOVER") echo " selected";?>>Discover</option>
				<option value="JCB"<?if ($str_CARD_TYPE == "JCB") echo " selected";?>>JCB</option>
				<option value="ENROUTE"<?if ($str_CARD_TYPE == "ENROUTE") echo " selected";?>>Enroute</option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" class="tablebody" align="right"><font class="tablefieldtext"><?echo GetMessage("STPC_CNUM")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<input type="text" name="CARD_NUM" size="30" class="typeinput" maxlength="30" value="<?= $str_CARD_NUM ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_CEXP")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<select name="CARD_EXP_MONTH" class="typeselect">
				<?
				for ($i = 1; $i <= 12; $i++)
				{
					?><option value="<?= $i ?>"<?if (IntVal($str_CARD_EXP_MONTH) == $i) echo " selected";?>><?= ((strlen($i) < 2) ? "0".$i : $i) ?></option><?
				}
				?>
			</select>
			<select name="CARD_EXP_YEAR" class="typeselect">
				<?
				for ($i = 2005; $i <= 2100; $i++)
				{
					?><option value="<?= $i ?>"<?if (IntVal($str_CARD_EXP_YEAR) == $i) echo " selected";?>><?= $i ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" align="right" class="tablebody"><font class="tablefieldtext">CVC2:</font></td>
		<td valign="top" width="50%" class="tablebody">
			<input type="text" name="CARD_CODE" size="10" class="typeinput" maxlength="10" value="<?= $str_CARD_CODE ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_MIN_SUM")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<input type="text" name="SUM_MIN" size="10" class="typeinput" maxlength="10" value="<?= ((DoubleVal($str_SUM_MIN) > 0) ? roundEx($str_SUM_MIN, SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_MAX_SUM")?></font></td>
		<td valign="top" width="50%" class="tablebody">
			<input type="text" name="SUM_MAX" size="10" class="typeinput" maxlength="10" value="<?= ((DoubleVal($str_SUM_MAX) > 0) ? roundEx($str_SUM_MAX, SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STPC_SUM_CURR")?></font></td>
		<td valign="top" class="tablebody">
			<?echo CCurrency::SelectBox("SUM_CURRENCY", $str_SUM_CURRENCY, "", false, "", "class='typeselect'")?>
		</td>
	</tr>
</table>
</td></tr></table>

<br>
<div align="left">
	<input type="submit" name="save" value="<?= GetMessage("STPC_SAVE") ?>" class="inputbuttonflat">
	&nbsp;
	<input type="submit" name="apply" value="<?= GetMessage("STPC_APPLY") ?>" class="inputbuttonflat">
	&nbsp;
	<input type="reset" value="<?= GetMessage("STPC_CANCEL") ?>" class="inputbuttonflat">
</div>
</form>

<?
//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPC_NO_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>