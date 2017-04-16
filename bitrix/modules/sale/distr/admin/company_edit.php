<?
use \Bitrix\Sale\Internals\CompanyTable;
use \Bitrix\Main\Application;
use Bitrix\Sale\Location\Admin\DefaultSiteHelper as Helper;
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

global $USER_FIELD_MANAGER, $USER;
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");


$lang    = Application::getInstance()->getContext()->getLanguage();
$conn    = Application::getConnection();
$request = Application::getInstance()->getContext()->getRequest();
$id      = intval($request->get("ID"));
$company = array();

$errorMessage = '';

if ($request->isPost() && strlen($request->getPost("update")) > 0 && check_bitrix_sessid() && $saleModulePermissions == 'W')
{
	$name        = $request->getPost('NAME');
	$locationId  = $request->getPost('LOCATION_ID');
	$code        = $request->getPost('CODE');
	$active      = ($request->getPost('ACTIVE') !== null) ? 'Y' : 'N';
	$location    = $request->getPost('ADDRESS');

	if (empty($name))
		$errorMessage .= GetMessage('ERROR_NO_NAME')."\n";
	if (empty($locationId))
		$errorMessage .= GetMessage('ERROR_NO_LOCATION_ID')."\n";

	if (empty($errorMessage))
	{
		$uFields = array();
		$USER_FIELD_MANAGER->EditFormAddFields(CompanyTable::getUfId(), $uFields);

		$fields = array(
			'NAME'          => $name,
			'LOCATION_ID'   => $locationId,
			'ADDRESS'      => $location,
			'CODE'          => $code,
			'ACTIVE'        => $active
		);
		$fields = array_merge($fields, $uFields);

		$result = null;
		$conn->startTransaction();
		if ($id > 0)
		{
			$fields['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
			$fields['MODIFIED_BY'] = $USER->GetID();
			$result = CompanyTable::update($id, $fields);
		}
		else
		{
			$fields['XML_ID'] = $request->getPost('XML_ID');
			$fields['DATE_CREATE'] = new \Bitrix\Main\Type\DateTime();
			$fields['CREATED_BY'] = $USER->GetID();
			$result = CompanyTable::add($fields);
		}

		if ($result && $result->isSuccess())
		{
			$conn->commitTransaction();

			$id = $result->getId();
			if (strlen($request->getPost("apply")) == 0)
				LocalRedirect("/bitrix/admin/sale_company.php?lang=".$lang."&".GetFilterParams("filter_", false));
			else
				LocalRedirect("/bitrix/admin/sale_company_edit.php?lang=".$lang."&ID=".$id."&".GetFilterParams("filter_", false));
		}
		else
		{
			$conn->rollbackTransaction();
			$errorMessage .= join("\n", $result->getErrorMessages());
		}
	}
}

if ($id > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $id, GetMessage("COMPANY_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($id > 0)
{
	$select = array('*', 'CREATED', 'MODIFIED');
	$filter = array(
		'ID' => $id
	);

	$fields = $USER_FIELD_MANAGER->GetUserFields(CompanyTable::getUfId());
	foreach ($fields as $field)
		$select[] = $field['FIELD_NAME'];

	$params = array(
		'select' => $select,
		'filter' => $filter
	);

	$res = CompanyTable::getList($params);
	$company = $res->fetch();
	
}
if (!empty($errorMessage))
	CAdminMessage::ShowMessage($errorMessage);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("COMPANY_TAB"), "ICON" => "catalog", "TITLE" => GetMessage("COMPANY_TAB_DESCR")),
);
$tabControl = new CAdminForm("company_edit", $aTabs);
$tabControl->BeginPrologContent();
echo $USER_FIELD_MANAGER->ShowScript();
$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
echo bitrix_sessid_post();
?>
<input type="hidden" name="update" value="Y">
<input type="hidden" name="lang" value="<?=$lang;?>">
<input type="hidden" name="ID" value="<?=$id;?>">
<?
$tabControl->EndEpilogContent();
$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang));

$tabControl->BeginNextFormTab();

$fields = ($request->isPost()) ? $_POST : $company;

$tabControl->AddViewField("ID", "ID", $company['ID']);
$tabControl->ShowUserFieldsWithReadyData(CompanyTable::getUfId(), $company, false, 'ID');
if ($id > 0)
{
	$createdBy = htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_LAST_NAME']).' '.htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_NAME']);
	$modifiedBy = htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_LAST_NAME']).' '.htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_NAME']);
	$tabControl->AddViewField('DATE_CREATE', GetMessage("COMPANY_DATE_CREATE"), $company['DATE_CREATE']);
	$tabControl->AddViewField('DATE_MODIFY', GetMessage("COMPANY_DATE_MODIFY"), $company['DATE_MODIFY']);
	$tabControl->AddViewField('CREATED_BY', GetMessage("COMPANY_CREATED_BY"), $createdBy);
	if (strlen(trim($modifiedBy)) > 0)
		$tabControl->AddViewField('MODIFIED_BY', GetMessage("COMPANY_MODIFIED_BY"), $modifiedBy);
}
$tabControl->ShowUserFieldsWithReadyData(CompanyTable::getUfId(), $fields, false, 'ID');
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("COMPANY_ACTIVE"), false, 'Y', $fields['ACTIVE'] == 'Y');
$tabControl->AddEditField("NAME", GetMessage("COMPANY_NAME"), true, array(), htmlspecialcharsbx($fields['NAME']));

$tabControl->BeginCustomField('LOCATIONS', GetMessage("COMPANY_LOCATION_ID"));
if ($saleModulePermissions >= 'W')
{?>
	<tr>
		<td><strong><?=GetMessage("COMPANY_LOCATION_ID");?></strong></td>
		<td>
			<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".\Bitrix\Sale\Location\Admin\LocationHelper::getWidgetAppearance(), "", array(
					"ID" => "",
					"CODE" => $fields['LOCATION_ID'],
					"INPUT_NAME" => "LOCATION_ID",
					"PROVIDE_LINK_BY" => "code",
					"SHOW_ADMIN_CONTROLS" => 'Y',
					"SELECT_WHEN_SINGLE" => 'N',
					"FILTER_BY_SITE" => 'Y',
					"FILTER_SITE_ID" => Application::getInstance()->getContext()->getSite(),
					"SHOW_DEFAULT_LOCATIONS" => 'N',
					"SEARCH_BY_PRIMARY" => 'Y'
				),
				false
			);?>
		</td>
	</tr>
<?
}
else
{
	$res = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode(
		$fields['LOCATION_ID'],
		array(
			'select' => array('CHAIN' => 'NAME.NAME'),
			'filter' => array('NAME.LANGUAGE_ID' => $lang)
		)
	);
	$path = array();
	while($item = $res->fetch())
	    $path[] = $item['CHAIN'];

	$path = implode(', ', array_reverse($path));
?>
	<tr>
		<td><?=GetMessage("COMPANY_LOCATION");?></td>
		<td><?=$path;?></td>
	</tr>
<?}
$tabControl->EndCustomField('LOCATIONS', '');

$tabControl->AddEditField("ADDRESS", GetMessage("COMPANY_LOCATION"), false, array(), htmlspecialcharsbx($fields['ADDRESS']));
$tabControl->AddEditField("CODE", GetMessage("COMPANY_CODE"), false, array(), htmlspecialcharsbx($fields['CODE']));
if ($id > 0)
	$tabControl->AddViewField("XML_ID", GetMessage("COMPANY_XML_ID"), htmlspecialcharsbx($fields['XML_ID']));
else
	$tabControl->AddEditField("XML_ID", GetMessage("COMPANY_XML_ID"), false, array(), htmlspecialcharsbx($fields['XML_ID']));
$tabControl->Buttons(array(
	"disabled" => ($saleModulePermissions < 'W'),
	"back_url" => "sale_company.php?lang=".$lang
));

$tabControl->Show();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
