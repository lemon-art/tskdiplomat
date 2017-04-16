<?
/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin\Blocks;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

Bitrix\Main\Loader::includeModule('sale');
$moduleId = "sale";

$result = new \Bitrix\Sale\Result();
$order = null;
Loc::loadMessages(__FILE__);
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$arUserGroups = $USER->GetUserGroupArray();
$boolLocked = false;

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

/** @var \Bitrix\Sale\Order $order */
if(!isset($_REQUEST["ID"]) || intval($_REQUEST["ID"]) <= 0)
	LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

$ID = intval($_REQUEST["ID"]);
$intLockUserID = 0;
$strLockTime = '';

if(isset($_REQUEST['dontsave']) && $_REQUEST['dontsave'] == 'Y')
{
	if (!\Bitrix\Sale\Order::isLocked($ID))
		\Bitrix\Sale\Order::unlock($ID);

	\Bitrix\Sale\DiscountCouponsManager::clearByOrder($ID);
	LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

//load order

$boolLocked = \Bitrix\Sale\Order::isLocked($ID);

if ($boolLocked)
{
	$r = \Bitrix\Sale\Order::getLockedStatus($ID);
	if ($r->isSuccess())
	{
		$lockResult = $r->getData();

		if (array_key_exists('LOCKED_BY', $lockResult)
			&& intval($lockResult['LOCKED_BY']) > 0)
		{
			$intLockUserID = intval($lockResult['LOCKED_BY']);
		}

		if (array_key_exists('DATE_LOCK', $lockResult)
			&& $lockResult['DATE_LOCK'] instanceof \Bitrix\Main\Type\DateTime)
		{
			$strLockTime = $lockResult['DATE_LOCK']->toString();
		}
	}

	$strLockUser = $intLockUserID;
	$strLockUserInfo = $intLockUserID;

	$userIterator = \Bitrix\Main\UserTable::getList(array(
		'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
		'filter' => array('=ID' => $intLockUserID)
	));
	if ($arOneUser = $userIterator->fetch())
	{
		$strLockUser = CUser::FormatName($strNameFormat, $arOneUser);
		$strLockUserInfo = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$intLockUserID.'">'.$strLockUser.'</a>';
	}
	unset($arOneUser, $userIterator);

	$result->addError( new \Bitrix\Main\Entity\EntityError(
		GetMessage(
			'SOE_ORDER_LOCKED2',
			array(
				'#ID#' => $strLockUserInfo,
				'#DATE#' => $strLockTime,
			)
		)
	));
}

if(!($order = Bitrix\Sale\Order::load($_REQUEST["ID"])))
	LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	
$userId = isset($_POST["USER_ID"]) ? intval($_POST["USER_ID"]) : $order->getUserId();

OrderEdit::initCouponsData(
	$userId,
	$ID,
	isset($_POST["OLD_USER_ID"]) ? intval($_POST["USER_ID"]) : $userId
);

if(!$boolLocked)
	\Bitrix\Sale\Order::lock($ID);

$isSavingOperation = $_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["apply"]) || isset($_POST["save"]));
$isRefreshDataAndSaveOperation = ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["refresh_data_and_save"]) && $_POST["refresh_data_and_save"] == "Y");
$isNeedFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation && !$isRefreshDataAndSaveOperation;

//save order params
if (($isSavingOperation || $isNeedFieldsRestore || $isRefreshDataAndSaveOperation)
	&& $saleModulePermissions >= "U"
	&& check_bitrix_sessid()
	&& $result->isSuccess()
)
{
	if($isSavingOperation || $isRefreshDataAndSaveOperation)
		$order = OrderEdit::editOrderByFormData($_POST, $order, $USER->GetID(), true, $_FILES, $result);

	if($isRefreshDataAndSaveOperation)
	{
		/** @var \Bitrix\Sale\Basket $basket */
		if (!($basket = $order->getBasket()))
			throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');

		$res = $basket->refreshData(array('PRICE', 'QUANTITY', 'COUPONS'));

		if(!$res->isSuccess())
			$result->addErrors($res->getErrors());
	}

	if(($isSavingOperation || $isRefreshDataAndSaveOperation ) && $result->isSuccess())
	{
		if($order)
		{
			$res = OrderEdit::saveCoupons($order->getUserId(), $_POST);

			if(!$res)
				$result->addError(new \Bitrix\Main\Entity\EntityError("Can't save coupons!"));

			$discount = $order->getDiscount();
			$res = $discount->calculate();
			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());

			$res = $order->save();

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
			else
			{
				if(isset($_POST["BUYER_PROFILE_ID"]))
				{
					$profResult = OrderEdit::saveProfileData(intval($_POST["BUYER_PROFILE_ID"]), $order, $_POST, true);

					if(!$profResult->isSuccess())
						$result->addErrors($profResult->getErrors());
				}

				if(isset($_POST["save"]))
					LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
				else
					LocalRedirect("/bitrix/admin/sale_order_edit.php?lang=".LANGUAGE_ID."&ID=".$order->getId().GetFilterParams("filter_", false));
			}
		}
		else
		{
			$result->addError(new \Bitrix\Main\Entity\EntityError("Can't update order!"));
		}
	}
}

CUtil::InitJSCore();

$APPLICATION->SetTitle(
	Loc::getMessage(
		"NEWO_TITLE_EDIT",
		array(
			"#ID#" => $order->getId(),
			"#NUM#" => strlen($order->getField('ACCOUNT_NUMBER')) > 0 ? $order->getField('ACCOUNT_NUMBER') : $order->getId(),
			"#DATE#" => $order->getDateInsert()->toString()
		)
	)
);
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* context menu */
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SOE_TO_LIST"),
	"TITLE"=> Loc::getMessage("SOE_TO_LIST_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_edit.php?lang=".LANGUAGE_ID."&dontsave=Y&ID=".$ID.GetFilterParams("filter_")
);

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SOE_ORDER_VIEW"),
	"TITLE"=> Loc::getMessage("SOE_ORDER_VIEW_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_view.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
);

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SOE_ORDER_REFRESH"),
	"TITLE"=> Loc::getMessage("SOE_ORDER_REFRESH_TITLE"),
	"LINK" => "javascript:if(confirm('".GetMessageJS("SOE_ORDER_REFRESH_CONFIRM")."')) BX.Sale.Admin.OrderEditPage.onRefreshOrderDataAndSave();"
);

$arSysLangs = array();
$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
while ($arLang = $db_lang->Fetch())
	$arSysLangs[] = $arLang["LID"];

$arReports = array();
$dirs = array(
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/",
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/"

);
foreach ($dirs as $dir)
{
	if (file_exists($dir))
	{
		if ($handle = opendir($dir))
		{
			while (($file = readdir($handle)) !== false)
			{
				$file_contents = '';
				if ($file == "." || $file == ".." || $file == ".access.php")
					continue;
				if (is_file($dir.$file) && ToUpper(substr($file, -4)) == ".PHP")
				{
					$rep_title = $file;
					if ($dir == $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/")
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file))
							$file_contents = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file);
					}

					if (empty($file_contents))
						$file_contents = file_get_contents($dir.$file);

					$rep_langs = "";
					$arMatches = array();
					if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
					{
						$arMatches[3] = Trim($arMatches[3]);
						if (strlen($arMatches[3]) > 0) $rep_title = $arMatches[3];
						$arMatches[2] = Trim($arMatches[2]);
						if (strlen($arMatches[2]) > 0) $rep_langs = $arMatches[2];
					}
					if (strlen($rep_langs) > 0)
					{
						$bContinue = true;
						foreach ($arSysLangs as $sysLang)
						{
							if (strpos($rep_langs, $sysLang) !== false)
							{
								$bContinue = false;
								break;
							}
						}

						if ($bContinue)
							continue;
					}

					$arReports[] = array(
						"TEXT" => $rep_title,
						"ONCLICK" => "window.open('/bitrix/admin/sale_order_print_new.php?&ORDER_ID=".$ID."&doc=".substr($file, 0, strlen($file) - 4)."&".bitrix_sessid_get()."', '_blank');"
					);
				}
			}
		}
		closedir($handle);
	}
}

$aMenu[] = array(
	"TEXT" => Loc::getMessage("NEWO_TO_PRINT"),
	"TITLE"=> Loc::getMessage("NEWO_TO_PRINT_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
	"MENU" => $arReports
);

$aMenu[] = array(
	"TEXT" => Loc::getMessage("NEWO_ORDER_DELETE"),
	"TITLE"=> Loc::getMessage("NEWO_ORDER_DELETE_TITLE"),
	"LINK" => "javascript:if(confirm('".GetMessageJS("NEWO_CONFIRM_DEL_MESSAGE")."')) window.location='sale_order.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'"
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

//prepare blocks order
$defaultBlocksOrder = array(
	"statusorder",
	"buyer",
	"delivery",
	"payment",
	"additional",
	"basket"
);

$fastNavItems = array();

foreach($defaultBlocksOrder as $item)
	$fastNavItems[$item] = Loc::getMessage("SALE_BLOCK_TITLE_".toUpper($item));

// errors
if(!$result->isSuccess() && !$isNeedFieldsRestore)
{
	$message = "";

	foreach($result->getErrors() as $error)
		$message .= $error->getMessage()."<br>\n";

	CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => $message,
		"HTML" => true
	));
}

$formId = "sale_order_edit";
$basketPrefix = "sale_order_basket";

$orderBasket = new Blocks\OrderBasket($order,"BX.Sale.Admin.OrderBasketObj", $basketPrefix);

echo OrderEdit::getScripts($order, $formId);
echo Blocks\OrderInfo::getScripts();
echo Blocks\OrderBuyer::getScripts();
echo Blocks\OrderPayment::getScripts();
echo Blocks\OrderAdditional::getScripts();
echo Blocks\OrderStatus::getScripts($order, $USER->GetID());
echo Blocks\OrderFinanceInfo::getScripts();
echo Blocks\OrderShipment::getScripts();
echo $orderBasket->getScripts();

// navigation
echo OrderEdit::getFastNavigationHtml($fastNavItems);

// yellow block with brief
echo Blocks\OrderInfo::getView($order, $orderBasket);

// Problem block
if($order->getField("MARKED") == "Y" )
	echo OrderEdit::getProblemBlockHtml($order->getField("REASON_MARKED"));

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
	array("DIV" => "tab_analysis", "TAB" => Loc::getMessage("SALE_TAB_ANALYSIS"), "TITLE" => Loc::getMessage("SALE_TAB_ANALYSIS"))
);

?><form method="POST" action="<?=$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ID=".$ID."&".$urlForm.GetFilterParams("filter_", false)?>" name="sale_order_edit_form" id="sale_order_edit_form" enctype="multipart/form-data"><?

$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->Begin();

//TAB order --
$tabControl->BeginNextTab();
$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);
?>
<tr><td>
	<input type="hidden" id="ID" name="ID" value="<?=$ID?>">
	<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($order->getSiteId())?>">
	<input type="hidden" id="OLD_USER_ID" name="OLD_USER_ID" value="<?=$order->getUserId()?>">
	<input type="hidden" name="BASKET_PREFIX" value="<?=$basketPrefix?>">
	<?=bitrix_sessid_post()?>
	<div style="position: relative; vertical-align: top">
		<?$tabControl->DraggableBlocksStart();?>
		<?
		foreach ($blocksOrder as $blockCode)
		{
			echo '<a id="'.$blockCode.'"></a>';
			$tabControl->DraggableBlockBegin(Loc::getMessage("SALE_BLOCK_TITLE_".toUpper($blockCode)), $blockCode);

			switch ($blockCode)
			{
				case "statusorder":
					echo Blocks\OrderStatus::getEdit($order, $USER, false, false);
					break;
				case "buyer":
					echo Blocks\OrderBuyer::getEdit($order);
					break;
				case "delivery":
					$shipments = $order->getShipmentCollection();
					$index = 0;

					/** @var \Bitrix\Sale\Shipment  $shipment*/
					foreach ($shipments as $shipment)
					{
						if (!$shipment->isSystem())
							echo Blocks\OrderShipment::getView($shipment, ++$index, 'edit');
					}

					break;
				case "payment":
					$payments = $order->getPaymentCollection();
					$index = 0;

					foreach ($payments as $payment)
						echo Blocks\OrderPayment::getView($payment, ++$index, 'edit');
					break;
				case "additional":
					echo Blocks\OrderAdditional::getEdit($order, $formId."_form", 'ORDER');
					break;
				case "basket":
					echo $orderBasket->getEdit();
					echo '<div style="display: none;">'.$orderBasket->settingsDialog->getHtml().'</div>';
					break;
			}
			$tabControl->DraggableBlockEnd();
		}
		?>
	</div>
</td></tr>

<?
$tabControl->EndTab();
//--TAB order

//TAB analysis --
$tabControl->BeginNextTab();
?>
<tr>
	<td>
		<div style="position:relative; vertical-align:top">
			<?
			echo Blocks\OrderAnalysis::getView($order, $orderBasket);
			?>
		</div>
	</td>
</tr>
<?
$tabControl->EndTab();
//-- TAB analysis

$tabControl->Buttons(
	array(
		"back_url" => "/bitrix/admin/sale_order_edit.php?lang=".LANGUAGE_ID."&dontsave=Y&ID=".$ID.GetFilterParams("filter_"))
);

$tabControl->End();
?>

</form>
<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<?if(!$result->isSuccess() || $isNeedFieldsRestore):?>
	<script type="text/javascript">
		BX.ready( function(){
			BX.Sale.Admin.OrderEditPage.restoreFormData(
				<?=CUtil::PhpToJSObject(OrderEdit::restoreFieldsNames(
						array_diff_key($_POST, array("USER_ID" => true))
					));
				?>
			);
		});
	</script>
<?endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");