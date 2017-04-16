<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Sale\Delivery;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Delivery\ExtraServices;
use Bitrix\Currency;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');

/** @var  CMain $APPLICATION */
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSE_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;
$strError = "";
$fields = array();
$tabControlName = "tabControl";
$isItSavingProcess = ($_SERVER['REQUEST_METHOD'] == "POST" && (strlen($_POST["save"]) > 0 || strlen($_POST["apply"]) > 0)) ? true : false;
$isItReloadingProcess = ($_SERVER['REQUEST_METHOD'] == "POST" && (!isset($_POST["save"]) && !isset($_POST["apply"]))) ? true : false;
$isItViewProcess = $_SERVER['REQUEST_METHOD'] != "POST";

/*
 * Process form fields received via POST
 */
if (($isItReloadingProcess || $isItSavingProcess) && $saleModulePermissions == "W" && check_bitrix_sessid())
{
	if(isset($_POST["ID"]))             $fields["ID"] = intval($_POST["ID"]);
	if(isset($_POST["CODE"]))           $fields["CODE"] = trim($_POST["CODE"]);
	if(isset($_POST["SORT"]))           $fields["SORT"] = intval($_POST["SORT"]);
	if(isset($_POST["NAME"]))           $fields["NAME"] = trim($_POST["NAME"]);
	if(isset($_POST["CONFIG"]))         $fields["CONFIG"] = $_POST["CONFIG"];
	if(isset($_POST["CURRENCY"]))       $fields["CURRENCY"] = trim($_POST["CURRENCY"]);
	if(isset($_POST["PARENT_ID"]))      $fields["PARENT_ID"] = intval($_POST["PARENT_ID"]);
	if(isset($_POST["CLASS_NAME"]))     $fields["CLASS_NAME"] = trim($_POST["CLASS_NAME"]);
	if(isset($_POST["DESCRIPTION"]))    $fields["DESCRIPTION"] = trim($_POST["DESCRIPTION"]);

	if(isset($_POST["CHANGED_FIELDS"]) && is_array($_POST["CHANGED_FIELDS"]))
		$changedFields = $_POST["CHANGED_FIELDS"];
	else
		$changedFields = array();

	if(isset($_POST["ACTIVE"]) && $_POST["ACTIVE"] == "Y")
		$fields["ACTIVE"] = "Y";
	else
		$fields["ACTIVE"] = "N";

	if(array_key_exists("LOGOTIP", $_FILES) && $_FILES["LOGOTIP"]["error"] == 0)
	{
		$fields["LOGOTIP"] = $_FILES["LOGOTIP"];
		$fields["LOGOTIP"]["del"] = trim($_POST["LOGOTIP_del"]);
		$fields["LOGOTIP"]["MODULE_ID"] = "sale";
		CFile::SaveForDB($fields, "LOGOTIP", "sale/delivery/logotip");
	}
	elseif(isset($_POST["LOGOTIP_FILE_ID"]) && intval($_POST["LOGOTIP_FILE_ID"]))
	{
		$fields["LOGOTIP"] = intval($_POST["LOGOTIP_FILE_ID"]);
	}

	if ($isItSavingProcess)
	{
		if(strlen($fields["NAME"]) <=0 )
			$strError .= Loc::getMessage("SALE_DSE_ERROR_NO_NAME")."<br>";

		if(strlen($fields["CLASS_NAME"]) <=0 )
			$strError .= Loc::getMessage("SALE_DSE_ERROR_NO_CLASS_NAME")."<br>";

		if($strError == '')
		{
			try
			{
				$service = Services\Manager::createServiceObject($fields);
				$fields = $service->prepareFieldsForSaving($fields);
			}
			catch(\Bitrix\Main\SystemException $e)
			{
				$strError = $e->getMessage();
			}

			if($strError == '')
			{
				if(isset($fields["PARENT_ID"]) && $fields["PARENT_ID"] == "new" && strlen($_POST["GROUP_NAME"]) > 0)
				{
					$fields["PARENT_ID"] = Services\Manager::getGroupId($_POST["GROUP_NAME"]);

					if($fields["PARENT_ID"] <=0)
						$strError .= Loc::getMessage("SALE_DSE_ERROR_GROUP_SAVE")."<br>";
				}

				if ($ID > 0)
				{
					$res = \Bitrix\Sale\Delivery\Services\Table::update($ID, $fields);

					if ($res->isSuccess())
					{
						// update some fields in children if need
						if(!empty($changedFields))
						{
							$fieldsList = array();

							if(in_array('ACTIVE', $changedFields))
							{
								if($fields['ACTIVE'] == 'Y')
									$fieldsList['ACTIVE'] = 'Y';
								else
									$fieldsList['ACTIVE'] = 'N';
							}

							if(!empty($fieldsList))
							{
								\Bitrix\Sale\Delivery\Services\Table::setChildrenFieldsValues(
									$ID,
									$fieldsList
								);
							}
						}
					}
					else
					{
						$strError .= Loc::getMessage("SALE_DSE_ERROR_EDIT_DELIVERY")."<br>".implode("<br>",$res->getErrorMessages());
					}
				}
				else
				{
					$res = \Bitrix\Sale\Delivery\Services\Table::add($fields);

					if (!$res->isSuccess())
					{
						$strError .= Loc::getMessage("SALE_DSE_ERROR_ADD_DELIVERY")."<br>".implode("<br>",$res->getErrorMessages());
					}
					else
					{
						$ID = $res->getId();
						Services\Manager::onAfterAdd($ID, $fields);
					}
				}

				if($ID > 0)
				{
					//stores
					unset($res);
					if(isset($_POST["STORES_SHOW"]) && $_POST["STORES_SHOW"] == "Y" && isset($_POST["STORES"]["PARAMS"]["STORES"]))
					{
						$res = ExtraServices\Manager::saveStores(
							$ID,
							Bitrix\Sale\Delivery\ExtraServices\Store::getStoresIdsFromParams(
								$_POST["STORES"]["PARAMS"]
							)
						);
					}
					else
					{
						$storesFields = ExtraServices\Manager::getStoresFields($ID);

						if(!empty($storesFields))
							$res = Delivery\ExtraServices\Table::delete($storesFields["ID"]);
					}

					if($res && !$res->isSuccess())
						$strError .= implode("<br>\n", $res->getErrorMessages());

				}
			}
		}

		if(strlen($strError) <= 0)
		{
			if (strlen($_POST["apply"]) > 0)
			{
				LocalRedirect($APPLICATION->GetCurPageParam(
						"ID=".$ID,
						array("ID")).(isset($_REQUEST[$tabControlName."_active_tab"]) ? "&".$tabControlName."_active_tab=".$_REQUEST[$tabControlName."_active_tab"] : ""
					));
			}
			elseif(strlen($_POST["save"]) > 0)
			{
				LocalRedirect((isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "sale_delivery_service_list.php?lang=".LANG."&filter_group=".$fields["PARENT_ID"]));
			}
		}
	}
}

/*
 * If errors or !$_POST
 * Fill form fields by data from table
 */
if(empty($fields) && $ID <= 0)
{
	$fields["PARENT_ID"] = $_REQUEST["PARENT_ID"] ? $_REQUEST["PARENT_ID"] : 0;
	$fields["CLASS_NAME"] = $_REQUEST["CLASS_NAME"] ? $_REQUEST["CLASS_NAME"] : "";
	$fields["CURRENCY"] = COption::GetOptionString("sale", "default_currency", "RUB");
	$fields["RIGHTS"] = "YYY"; //Admin Manager Client

	if($fields["CLASS_NAME"] == '\Bitrix\Sale\Delivery\Services\Group')
		$fields["ACTIVE"] = "Y";
}

$serviceConfig = array();
$canHasProfiles = false;
$showRestrictions = true;
$showExtraServices = false;
$parentService = null;
$showFieldsList = \Bitrix\Sale\Delivery\Services\Table::getMap();

/* saving or updating Extra service & restrictions */
if($ID > 0 && ($_SERVER['REQUEST_METHOD'] != "POST" || $isItSavingProcess))
{
	$dbRes = \Bitrix\Sale\Delivery\Services\Table::getById($ID);

	if(!$fields = $dbRes->fetch())
		$strError .= str_replace("#ID#", $ID, Loc::getMessage("SALE_DSE_ERROR_ID"))."<br>";
}

/* If action is copying */
if($_REQUEST["action"] == "copy")
{
	$ID = 0;
	unset($fields["ID"]);
}
elseif($_REQUEST["action"] == "profile_delete")
{
	$idProf = isset($_REQUEST["ID_PROF"]) ? intval($_REQUEST["ID_PROF"]) : 0;

	if($idProf > 0)
	{
		$res = \Bitrix\Sale\Delivery\Services\Table::delete($idProf);

		if(!$res->isSuccess())
			$strError .= implode("<br>\n", $res->getErrorMessages())."<br>";
	}
	else
	{
		$strError .= Loc::getMessage("SALE_DSE_PROFILE_DEL_ERROR", array("#ID#" => $idProf))."<br>";
	}
}

/* Ask parent service witch class_names for children are allowed */
if(intval($fields["PARENT_ID"]) > 0)
{
	$parentService = Services\Manager::getService($fields["PARENT_ID"]);
	$classNamesList = $parentService->getChildrenClassNames();
}
else /* get all available */
{
	$classNamesList = Services\Manager::getHandlersClassNames();

	$classesToExclude = array(
		'\Bitrix\Sale\Delivery\Services\AutomaticProfile',
		'\Bitrix\Sale\Delivery\Services\Group'
	);

	foreach($classesToExclude as $class)
	{
		$key = array_search($class, $classNamesList);

		if($key !== false)
			unset($classNamesList[$key]);
	}
}

/* if we have only one class - let's fix it */
if(empty($fields["CLASS_NAME"]) && count($classNamesList) == 1)
	$fields["CLASS_NAME"] = current($classNamesList);

$service = null;

if(isset($fields["CLASS_NAME"]) && strlen($fields["CLASS_NAME"]) > 0)
{
	/* We must convert handler config from post as it was taken from database */
	if($isItSavingProcess && strlen($strError) > 0)
	{
		try
		{
			$service = Services\Manager::createServiceObject($fields);
			$fields = $service->prepareFieldsForSaving($fields);
		}
		catch(\Bitrix\Main\SystemException $e){}
	}

	$service = Services\Manager::createServiceObject($fields);
	$fields = $service->prepareFieldsForUsing($fields);

	if($service)
	{
		$serviceConfig = $service->getConfig();
		$showRestrictions = $service->whetherAdminRestrictionsShow();
		$showExtraServices = $service->whetherAdminExtraServicesShow();
		$showFieldsList = $service->getAdminFieldsList();
		$canHasProfiles = $service->canHasProfiles() && ($ID > 0);
	}
}

$serviceCurrency = $fields["CURRENCY"];
if(\Bitrix\Main\Loader::includeModule('currency'))
{
	$currencyList = Currency\CurrencyManager::getCurrencyList();
	if (isset($currencyList[$fields["CURRENCY"]]))
		$serviceCurrency = $currencyList[$fields["CURRENCY"]];
	unset($currencyList);
}

$aTabs = array(
	array(
		"DIV" => "edit_main",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_GENERAL"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_DELIVERY_GENERAL")
	)
);

/* from service config */
foreach($serviceConfig as $sectionKey => $serviceSection)
{
	$aTabs[] = array(
		"DIV" => "edit_".$sectionKey,
		"TAB" => $serviceSection["TITLE"],
		"ICON" => "sale",
		"TITLE" => $serviceSection["DESCRIPTION"]
	);
}

if($canHasProfiles)
{
	$aTabs[] = array(
		"DIV" => "edit_profiles",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_PROFILES"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_PROFILES_DESCR"),
	);
}

if($service && $showRestrictions && $ID > 0)
{
	$aTabs[] = array(
		"DIV" => "edit_restriction",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_RESTRICTIONS"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_RESTRICTIONS_DESCR")
	);
}

if($showExtraServices && $ID > 0)
{
	$aTabs[] = array(
		"DIV" => "edit_extraservices",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_EXTRA_SERVICES"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_EXTRA_SERVICES_DESCR"),
	);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

/* Profiles */
if($canHasProfiles)
{
	$sTableIDSubService = "tbl_sale_delivery_subservice";
	$oSortSubService = new CAdminSorting($sTableIDSubService);
	$lAdminSubServices = new CAdminList($sTableIDSubService, $oSortSubService);

	$dbSubServicesRes = \Bitrix\Sale\Delivery\Services\Table::getList(array(
		"filter" => array(
			"PARENT_ID" => $ID
		),
		"select" => array(
			"ID", "NAME", "ACTIVE", "LOGOTIP", "PARENT_ID"
		),
		"order" => isset($_REQUEST["by"]) && isset($_REQUEST["order"]) ? array($_REQUEST["by"] => $_REQUEST["order"]) : array("NAME" => "ASC")
	));

	$profilesList = new CAdminResult($dbSubServicesRes, $sTableIDSubService);
	$profilesList->NavStart();
	$lAdminSubServices->NavText($profilesList->GetNavPrint("PROFILES"));

	$profileHeader = array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"NAME", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_NAME"), "sort"=>"NAME", "default"=>true),
		array("id"=>"ACTIVE", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
		array("id"=>"LOGOTIP", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_LOGOTIP"), "sort"=>"LOGOTIP", "default"=>true)
	);

	$lAdminSubServices->AddHeaders($profileHeader);

	while ($profileParams = $profilesList->NavNext(true, "f_"))
	{
		$actUrl = "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID.'&'.$tabControl->ActiveTabParam()."&back_url=".urlencode($APPLICATION->GetCurPageParam());
		$row =& $lAdminSubServices->AddRow($f_ID, $profileParams, $actUrl, Loc::getMessage("SALE_DSE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');

		$row->AddField("ID", $f_ID);

		$logoHtml = intval($f_LOGOTIP) > 0 ? CFile::ShowImage(CFile::GetFileArray($f_LOGOTIP), 150, 150, "border=0", "", false) : "";
		$row->AddField("LOGOTIP", $logoHtml);
		$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? Loc::getMessage("SALE_DSE_YES") : Loc::getMessage("SALE_DSE_NO")));
		$row->AddField("CLASS_NAME", $f_CLASS_NAME);

		$arActions = Array();
		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_DSE_COPY"), "ACTION"=>$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&ID=".$f_ID."&action=copy&back_url=".urlencode($APPLICATION->GetCurPageParam())), "DEFAULT"=>true);
		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_DSE_EDIT_DESCR"), "ACTION"=>$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".urlencode($APPLICATION->GetCurPageParam())), "DEFAULT"=>true);
		if ($saleModulePermissions >= "W")
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_DSE_DELETE"), "ACTION"=>"if(confirm('".Loc::getMessage('SALE_DSE_CONFIRM_DEL_PROFILE_MESSAGE')."')) ".$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$fields["PARENT_ID"]."&ID=".$ID."&action=profile_delete&ID_PROF=".$f_ID));
		}

		$row->AddActions($arActions);
	}

	if ($saleModulePermissions == "W")
	{
		$aContext = array(
			array(
				"TEXT" => Loc::getMessage("SALE_DSE_ADD_NEW_PROFILE"),
				"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$ID."&back_url=".urlencode($APPLICATION->GetCurPageParam()),
				"TITLE" => Loc::getMessage("SALE_DSE_ADD_NEW_PROFILE_TITLE"),
				"ICON" => "btn_new"
			)
		);

		$lAdminSubServices->AddAdminContextMenu($aContext, false);
	}

	if($_REQUEST["table_id"]==$sTableIDSubService)
		$lAdminSubServices->CheckListMode();
}
/* profiles end */

Asset::getInstance()->addJs("/bitrix/js/sale/delivery.js");

if($parentService && get_class($parentService) != 'Bitrix\Sale\Delivery\Services\Group')
{
	if($ID > 0)
	{
		$sDocTitle = str_replace(
			array("#NAME#", "#PARENT_NAME#"),
			array($fields["NAME"], $parentService->name),
			Loc::getMessage("SALE_DSE_EDIT_RECORD_PROFILE")
		);
	}
	else
	{
		$sDocTitle = str_replace(
			"#PARENT_NAME#",
			$parentService->name,
			Loc::getMessage("SALE_DSE_NEW_RECORD_PROFILE")
		);
	}
}
else
{
	if($fields["CLASS_NAME"] == '\Bitrix\Sale\Delivery\Services\Group')
	{
		if($ID > 0)
			$sDocTitle = str_replace("#NAME#", $fields["NAME"], Loc::getMessage("SALE_DSE_EDIT_GROUP"));
		else
			$sDocTitle = Loc::getMessage("SALE_DSE_NEW_GROUP");
	}
	else
	{
		if($ID > 0)
			$sDocTitle = str_replace("#NAME#", $fields["NAME"], Loc::getMessage("SALE_DSE_EDIT_RECORD"));
		else
			$sDocTitle = Loc::getMessage("SALE_DSE_NEW_RECORD");
	}
}

$APPLICATION->SetTitle($sDocTitle);

if($service && $showRestrictions && $ID > 0)
{
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/delivery_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();
}
else
{
	$restrictionsHtml = "";
}

if($showExtraServices && $ID > 0)
{
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/delivery_eservice_list.php");
	$extraServicesHtml = ob_get_contents();
	ob_end_clean();
}
else
{
	$extraServicesHtml = "";
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Sale\Internals\Input\Manager::initJs();

?>
<script language="JavaScript">
	BX.message({
		SALE_DSE_GROUP_NAME: '<?=Loc::getMessage("SALE_DSE_GROUP_NAME")?>',
		SALE_DSE_GROUP_CREATE: '<?=Loc::getMessage("SALE_DSE_GROUP_CREATE")?>',
		SALE_DSE_GROUP_CREATE_G: '<?=Loc::getMessage("SALE_DSE_GROUP_CREATE_G")?>',
		SALE_RDL_RESTRICTION: '<?=Loc::getMessage("SALE_RDL_RESTRICTION")?>',
		SALE_RDL_SAVE: '<?=Loc::getMessage("SALE_RDL_SAVE")?>'
	});
</script>
<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SALE_DSE_2DLIST"),
		"LINK" => isset($_GET["back_url"]) ? $_GET["back_url"] : "/bitrix/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID."&filter_group=".$fields["PARENT_ID"],
		"ICON" => "btn_list"
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SALE_DSE_NEW_DELIVERY"),
		"LINK" => "/bitrix/admin/sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".$fields["PARENT_ID"],
		"ICON" => "btn_new"
	);

	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SALE_DSE_DELETE_DELIVERY"),
		"LINK" => "javascript:if(confirm('".Loc::getMessage("SALE_DSE_DELETE_DELIVERY_CONFIRM")."')) window.location='/bitrix/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID."&filter_group=".$fields["PARENT_ID"]."&ID=".$ID."&action=delete&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(strlen($strError)>0)
	CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>Loc::getMessage("SALE_DSE_ERROR"), "HTML"=>true));

?>
<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="form1" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?=$ID ?>">
<input type="hidden" name="CODE" value="<?=(isset($fields["CODE"]) ? $fields["CODE"] : "" )?>">
<input type="hidden" name="PARENT_ID" value="<?=(isset($fields["PARENT_ID"]) ? $fields["PARENT_ID"] : "0" )?>">
<?=bitrix_sessid_post()?>

<?foreach($fields as $fieldName => $fieldValue): /* if fields don't show let's make them hidden */?>
	<?if(!is_array($fieldValue) && strlen($fieldValue) > 0 && !array_key_exists($fieldName, $showFieldsList)):?>
		<input type="hidden" name="<?=$fieldName?>" value="<?=$fieldValue?>">
	<?endif;?>
<?endforeach;?>

<?
$tabControl->Begin();
/* General settings */
$tabControl->BeginNextTab();
	if($ID>0 && array_key_exists("ID", $showFieldsList)):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("NAME", $showFieldsList)):?>
		<tr class="adm-detail-required-field">
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_NAME")?>:</td>
			<td width="60%"><input type="text" name="NAME" value="<?=(isset($fields["NAME"]) ? htmlspecialcharsbx($fields["NAME"]) : "" )?>" size="40"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("CLASS_NAME", $showFieldsList)):?>
		<tr class="adm-detail-required-field">
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_CLASS_NAME")?>:</td>
			<td width="60%">
				<?if(count($classNamesList) > 1 && (strlen($fields["CLASS_NAME"]) <= 0 || intval($ID) <= 0)):?>
					<select name="CLASS_NAME" onchange="if(this.value == '') return; top.BX.showWait(); this.form.submit(); /*elements.apply.click();*/">
						<option value=""></option>
						<?foreach($classNamesList as $className):?>
							<option value="<?=$className?>" <?=(isset($fields["CLASS_NAME"]) && $className == $fields["CLASS_NAME"] ? " selected" : "" )?>><?=$className::getClassTitle()." [".$className."]"?></option>
						<?endforeach;?>
					</select>
				<?else:?>
					<?=$fields["CLASS_NAME"]::getClassTitle()?>
					<input type="hidden" name="CLASS_NAME" value="<?=$fields["CLASS_NAME"]?>">
				<?endif;?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("ACTIVE", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_ACTIVE")?>:</td>
			<td width="60%"><input type="checkbox" name="ACTIVE" value="Y" <?if ($fields["ACTIVE"]=="Y") echo "checked";?> onclick="BX.Sale.Delivery.createFlagFieldChanged('ACTIVE', this);"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("SORT", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_SORT")?>:</td>
			<td width="60%"><input type="text" name="SORT" value="<?=(isset($fields["SORT"]) ? $fields["SORT"] : "100" )?>" size="5"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("DESCRIPTION", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_DESCRIPTION")?>:</td>
			<td width="60%">
				<!--<textarea name="DESCRIPTION"><?=(isset($fields["DESCRIPTION"]) ? htmlspecialcharsbx($fields["DESCRIPTION"]) : "" )?></textarea>-->
				<?=wrapDescrLHE(
					'DESCRIPTION',
					isset($fields["DESCRIPTION"]) ? htmlspecialcharsbx($fields["DESCRIPTION"]) : '',
					'hndl_dscr');?>
				<script language="JavaScript">BX.Sale.Delivery.setLHEClass('bxlhe_frame_hndl_dscr'); </script>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("PARENT_ID", $showFieldsList)):?>
		<?if($parentService && get_class($parentService) != 'Bitrix\Sale\Delivery\Services\Group'):?>
			<tr>
				<td width="40%">
					<?=Loc::getMessage("SALE_DSE_FORM_PARENT_ID")?>
					:</td>
				<td width="60%">
					<a href="?LANG=<?=LANGUAGE_ID?>&PARENT_ID=<?=$parentService->parentId?>&ID=<?=$parentService->id?>"><?=htmlspecialcharsbx($parentService->name)?></a>
				</td>
			</tr>
		<?else:?>
			<tr>
				<td width="40%">
					<?=Loc::getMessage("SALE_DSE_FORM_GROUP_ID")?>
					:</td>
				<td width="60%">
					<?=\Bitrix\Sale\Delivery\Helper::getGroupChooseControl($fields["PARENT_ID"], "PARENT_ID")?> &nbsp;
					<a
						href="javascript:void(0);"
						style="border-bottom: 1px dashed; cursor: pointer; text-decoration: none;"
						onclick="BX.Sale.Delivery.createGroup();"
					>
						<?=Loc::getMessage("SALE_DSE_ADD")?>
					</a>
					<input type="hidden" name="GROUP_NAME" id="GROUP_NAME" value="">
				</td>
			</tr>
		<?endif;?>
	<?endif;?>

	<?if(array_key_exists("LOGOTIP", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_LOGO")?>:</td>
			<td width="60%">
				<div><input type="file" name="LOGOTIP"><input type="hidden" name="LOGOTIP_FILE_ID" value="<?=$fields["LOGOTIP"]?>"></div>
				<?if(isset($fields["LOGOTIP"]) && intval($fields["LOGOTIP"]) > 0):?>
					<br>
					<?
						$arLogotip = CFile::GetFileArray($fields["LOGOTIP"]);
						echo CFile::ShowImage($arLogotip, 150, 150, "border=0", "", false);
					?>
					<br />
					<div>
						<input type="checkbox" name="LOGOTIP_del" value="Y" id="LOGOTIP_del" >
						<label for="LOGOTIP_del"><?=Loc::getMessage("SALE_DSE_LOGOTIP_DEL");?></label>
					</div>
				<?endif;?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("CURRENCY", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_CURRENCY")?>:</td>
			<td width="60%">
				<?=CCurrency::SelectBox("CURRENCY", $fields["CURRENCY"], "", true, "");?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("STORES", $showFieldsList)):?>
		<?$stores = ExtraServices\Manager::getStoresFields($ID);?>
		<?$storeClassName = ExtraServices\Manager::STORE_PICKUP_CLASS;?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_STORES_SHOW")?>:</td>
			<td width="60%">
				<input type="checkbox" name="STORES_SHOW" value="Y" <?=!empty($stores) ? " checked" : ""?> onchange="BX.Sale.Delivery.toggleStores();">
			</td>
		</tr>
		<tr id="sale-admin-delivery-stores"<?=!empty($stores) ? '' : ' style="display: none;"'?>>
			<td width="40%">
				<?=Loc::getMessage("SALE_DSE_FORM_STORES")?>:
			</td>
			<td width="60%">
				<?=$storeClassName::getAdminParamsControl("STORES", $stores)?>
			</td>
		</tr>
	<?endif;?>

	<?if(is_array($serviceConfig) && !empty($serviceConfig)):?>
		<?foreach($serviceConfig as $sectionKey => $configSection):?>
			<?$tabControl->BeginNextTab();?>
			<?if(isset($configSection["ITEMS"]) && is_array($configSection["ITEMS"]) && !empty($configSection["ITEMS"])):?>
				<?foreach($configSection["ITEMS"] as $name => $params):?>
					<?if($params["TYPE"] == "DELIVERY_SECTION"):?>
						<tr class="heading">
							<td colspan="2"><?=$params["NAME"]?></td>
						</tr>
					<?else:?>
						<tr>
							<td width="40%"><?=$params["NAME"]?>:</td>
							<td width="60%">
								<?=\Bitrix\Sale\Internals\Input\Manager::getEditHtml("CONFIG[".$sectionKey."][".$name."]", $params)?>
							</td>
						</tr>
					<?endif;?>
				<?endforeach;?>
			<?endif;?>
		<?endforeach;?>
	<?endif;?>

	<?if($canHasProfiles):?>
		<?$tabControl->BeginNextTab();?>
		<tr>
			<td colspan="2">
				<?$lAdminSubServices->DisplayList(array("FIX_HEADER" => false, "FIX_FOOTER" => false));?>
			</td>
		</tr>
	<?endif;?>

	<?if(strlen($restrictionsHtml) > 0):?>
		<?$tabControl->BeginNextTab();?>
		<tr><td id="sale-delivery-restriction-container"><?=$restrictionsHtml?></td></tr>
	<?endif;?>

	<?if($service && $showExtraServices && $ID > 0):?>
		<?$tabControl->BeginNextTab();?>
		<tr><td><?=$extraServicesHtml?></td></tr>
	<?endif;

$tabControl->Buttons(
	array(
		"disabled" => ($saleModulePermissions < "W"),
		"back_url" => isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : ("/bitrix/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID)
	)
);

$tabControl->End();

?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

function wrapDescrLHE($inputName, $content = '', $divId = false)
{
	ob_start();
	$ar = array(
		'inputName' => $inputName,
		'height' => '160',
		'width' => '320',
		'content' => $content,
		'bResizable' => true,
		'bManualResize' => true,
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'bAutoResize' => true,
		'bSaveOnBlur' => true,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike',
			'CreateLink', 'DeleteLink',
			'Source', 'BackColor', 'ForeColor'
		)
	);

	if($divId)
		$ar['id'] = $divId;

	$LHE = new CLightHTMLEditor;
	$LHE->Show($ar);
	$sVal = ob_get_contents();
	ob_end_clean();

	return $sVal;
}