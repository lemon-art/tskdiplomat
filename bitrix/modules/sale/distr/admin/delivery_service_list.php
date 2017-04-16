<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

/** @var  CMain $APPLICATION */
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSL_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/delivery.js");
$sTableID = "tbl_sale_delivery_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_name",
	"filter_active",
	"filter_class_name",
	"filter_group"
);

$groupId = !empty($filter_group) ? $filter_group : -1;

$filter = array();
if(strlen($filter_name) > 0) $filter["%NAME"] = Trim($filter_name);
if(strlen($filter_active) > 0) $filter["=ACTIVE"] = Trim($filter_active);
if(intval($filter_group) > 0) $filter["=PARENT_ID"] = intval($filter_group);

if(strlen($filter_class_name) > 0)
	$filter["=CLASS_NAME"] = Trim($filter_class_name);
else
	$filter['!=CLASS_NAME'] = array(
		'\Bitrix\Sale\Delivery\Services\AutomaticProfile',
		'\Bitrix\Sale\Delivery\Services\Group'
	);

$lAdmin->InitFilter($arFilterFields);

$filter_group = $groupId;

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = \Bitrix\Sale\Delivery\Services\Table::GetList( array(
			'sort' => array($by => $order),
			'filter' => $filter,
			'select' => array("ID")
			)
		);

		while ($arResult = $dbResultList->fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$res = \Bitrix\Sale\Delivery\Services\Table::delete($ID);

				if (!$res->isSuccess())
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("SALE_SDL_ERROR_DELETE"), $ID);
				}

				break;

			case "activate":
			case "deactivate":
				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				$res = \Bitrix\Sale\Delivery\Services\Table::update($ID, $arFields);

				if (!$res->isSuccess())
				{
					if ($errors = $res->getErrors())
						$lAdmin->AddGroupError(implode("<br>/n", $errors), $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("SALE_SDL_ERROR_UPDATE"), $ID);
				}
				else
				{
					\Bitrix\Sale\Delivery\Services\Table::setChildrenFieldsValues(
						$ID,
						$arFields
					);
				}

				break;
		}
	}
}

$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList(array(
	'order' => array($by => $order),
	'filter' => $filter
	)
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_SDL_PRLIST")));
$lAdmin->AddHeaders(array(
	array("id"=>"NAME", "content"=>Loc::getMessage("SALE_SDL_NAME"),  "sort"=>"NAME", "default" => true),
	array("id"=>"GROUP_NAME", "content"=>Loc::getMessage("SALE_SDL_GROUP_NAME"),  "sort"=>"PARENT.NAME", "default"=>true),
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"LOGOTIP", "content"=>Loc::getMessage("SALE_SDL_LOGOTIP"), "sort"=>"", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage("SALE_SDL_DESCRIPTION"),  "sort"=>"", "default" => false),
	array("id"=>"SORT", "content"=>Loc::getMessage("SALE_SDL_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"ACTIVE", "content"=>Loc::getMessage("SALE_SDL_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"CLASS_NAME", "content"=>Loc::getMessage("SALE_SDL_CLASS_NAME"),  "sort"=>"CLASS_NAME", "default"=>false)
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$backUrl = urlencode($APPLICATION->GetCurPageParam("", array("mode")));

while ($service = $dbResultList->NavNext(true, "f_"))
{
	if(is_callable($service["CLASS_NAME"].'::canHasChildren') && $service["CLASS_NAME"]::canHasChildren()) //has children
	{
		$actUrl = "sale_delivery_service_list.php?lang=".LANG."&filter_group=".$f_ID;
		$row =& $lAdmin->AddRow($f_ID, $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-submenu-item-link-icon adm-list-table-icon sale_section_icon"></span>'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');
	}
	else //has no children
	{
		$actUrl = "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".$backUrl;
		$row =& $lAdmin->AddRow($f_ID, $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');
	}

	$row->AddField("ID", $f_ID);

	$logoHtml = intval($f_LOGOTIP) > 0 ? CFile::ShowImage(CFile::GetFileArray($f_LOGOTIP), 150, 150, "border=0", "", false) : "";
	$row->AddField("LOGOTIP", $logoHtml);
	$row->AddField("DESCRIPTION", $f_DESCRIPTION);
	$row->AddField("SORT", $f_SORT);
	$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? Loc::getMessage("SALE_SDL_YES") : Loc::getMessage("SALE_SDL_NO")));
	$row->AddField("CLASS_NAME", (is_callable($f_CLASS_NAME."::getClassTitle") ? $f_CLASS_NAME::getClassTitle() : "")." [".$f_CLASS_NAME."]");

	$groupNameHtml = "";

	if($f_PARENT_ID > 0)
	{
		$res = \Bitrix\Sale\Delivery\Services\Table::getById($f_PARENT_ID);

		if($group = $res->fetch())
			$groupNameHtml = '<a href="sale_delivery_service_edit.php?lang='.LANG.'&PARENT_ID='.$group["PARENT_ID"].'&ID='.$group["ID"]."&back_url=".$backUrl.'">'.htmlspecialcharsbx($group["NAME"]).'</a>';
	}

	$row->AddField("GROUP_NAME", $groupNameHtml);

	$arActions = Array();
	$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("SALE_SDL_COPY_DESCR"), "ACTION"=>'BX.Sale.Delivery.showGroupsDialog("sale_delivery_service_edit.php?lang='.LANG.'&ID='.$f_ID.'&action=copy","'.$f_PARENT_ID."&back_url=".$backUrl.'");', "DEFAULT"=>true);
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_SDL_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".$backUrl), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_SDL_DELETE_DESCR"), "ACTION"=>"if(confirm('".Loc::getMessage('SALE_SDL_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "PARENT_ID=".$f_PARENT_ID));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array();

	$aContext[] = array(
		"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW"),
		"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_ALT"),
		"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0).
			(isset($filter["=CLASS_NAME"]) ? "&CLASS_NAME=".urlencode($filter["=CLASS_NAME"]) : "")."&back_url=".$backUrl,
		"ICON" => "btn_new"
	);

/*
		array(
			"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW_GROUP"),
			"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$filter_group."&CLASS_NAME=".urlencode('\Bitrix\Sale\Delivery\Services\Group'),
			"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_GROUP_ALT")
		),
*/

	if(isset($filter["=CLASS_NAME"]) && $filter["=CLASS_NAME"] == '\Bitrix\Sale\Delivery\Services\Group')
	{
		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_TO_LIST"),
			"LINK" => isset($_GET["back_url"]) ? $_GET["back_url"] : "/bitrix/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID.
			(!empty($filter_group) ? "&filter_group=".intval($filter_group) : ""),
			"TITLE" => Loc::getMessage("SALE_SDL_TO_LIST_ALT"),
		);
	}
	else
	{
		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_MANAGE_GROUP"),
			"LINK" => $APPLICATION->GetCurPageParam(
				"filter_class_name=".urlencode('\Bitrix\Sale\Delivery\Services\Group').
					"&backurl=".urlencode($APPLICATION->GetCurPageParam()),
				array("filter_class_name", "filter_group")
			),
			"TITLE" => Loc::getMessage("SALE_SDL_MANAGE_GROUP_ALT")
		);
	}

	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("SALE_SDL_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<script language="JavaScript">
	BX.message({
		SALE_DSE_CHOOSE_GROUP_TITLE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_TITLE")?>',
		SALE_DSE_CHOOSE_GROUP_HEAD: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_HEAD")?>',
		SALE_DSE_CHOOSE_GROUP_SAVE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_SAVE")?>'
	});
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPageParam()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		Loc::getMessage("SALE_SDL_FILTER_NAME"),
		Loc::getMessage("SALE_SDL_FILTER_ACTIVE"),
		Loc::getMessage("SALE_SDL_FILTER_CLASS_NAME"),
		Loc::getMessage("SALE_SDL_FILTER_GROUP")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_NAME")?>:</td>
		<td>
			<input type="text" name="filter_name" value="<?=htmlspecialcharsbx($filter_name)?>">
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=Loc::getMessage("SALE_SDL_ALL")?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=Loc::getMessage("SALE_SDL_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=Loc::getMessage("SALE_SDL_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_CLASS_NAME")?>:</td>
		<td>
			<select name="filter_class_name">
				<option value=""></option>
				<?foreach(\Bitrix\Sale\Delivery\Services\Manager::getHandlersClassNames() as $className):?>
					<?if(is_callable($className."::getClassTitle")):?>
						<option value="<?=htmlspecialcharsbx($className)?>" <?=(isset($filter["=CLASS_NAME"]) && $className == $filter["=CLASS_NAME"] ? " selected" : "" )?>><?=htmlspecialcharsbx($className::getClassTitle())?></option>
					<?endif;?>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_GROUP")?>:</td>
		<td>
			<?=\Bitrix\Sale\Delivery\Helper::getGroupChooseControl(
				$filter_group,
				"filter_group",
				"",
				true
			)?>
		</td>
	</tr>
	<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPageParam(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");