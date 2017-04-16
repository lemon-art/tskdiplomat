<?
namespace Bitrix\Sale\AdminPage;

/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Sale;
use Bitrix\Sale\Result;
use Bitrix\Sale\Provider;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\UserMessageException;
use Bitrix\Main\ArgumentNullException;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER;
$arResult = array();
$result = new \Bitrix\Main\Entity\Result();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(!isset($_REQUEST["action"]))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "REQUEST[action] not defined!"));
}
elseif($saleModulePermissions == "D" || !check_bitrix_sessid())
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "Access denied!"));
}
elseif(!\Bitrix\Main\Loader::includeModule('sale'))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "Error! Can't include module \"Sale\"!"));
}
else
{
	$processor = new AjaxProcessor($USER->GetID(), $_REQUEST);
	$result = $processor->processRequest();
}

if($result->isSuccess())
{
	$arResult["RESULT"] = "OK";
}
else
{
	$arResult["RESULT"] = "ERROR";
	$arResult["ERROR"] = implode("\n", $result->getErrorMessages());
	$arResult["ERRORS"] = array();

	foreach($result->getErrorMessages() as $error)
		$arResult["ERRORS"][] = $error;
}

$data = $result->getData();

if(is_array($data))
	$arResult = array_merge($arResult, $result->getData());

$arResult = AjaxProcessor::convertEncodingArray($arResult, SITE_CHARSET, 'UTF-8');

Header('Content-Type: application/json');

echo json_encode($arResult);
\CMain::FinalActions();
die();

/**
 * Class AjaxProcessor
 * @package Bitrix\Sale\AdminPage
 * Class helper for processing ajax requests
 */
class AjaxProcessor
{
	protected $userId;
	/** @var \Bitrix\Sale\Result $result*/
	protected $result;
	protected $request;
	/** @var \Bitrix\Sale\Order $order  */
	protected $order = null;
	protected $formDataChanged = false;

	public function __construct($userId, array $request)
	{
		$this->userId = $userId;
		$this->result = new Result();
		$this->request = $request;
	}

	/**
	 * @return Result
	 * @throws SystemException
	 */
	public function processRequest()
	{
		if(!isset($this->request['action']))
			throw new SystemException("Undefined \"action\"");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

		global $APPLICATION;

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$this->request = $APPLICATION->ConvertCharsetArray($this->request, 'utf-8', SITE_CHARSET);

		try
		{
			call_user_func(
				array($this, $this->request['action']."Action")
			);

			/* Caution!
			 * You must update $this->request by fresh data,
			 * or you will refresh and receive old data!
			 */
			if(
				isset($this->request["refreshOrderData"])
				&& $this->request["refreshOrderData"] == "Y"
				&& $this->request['action'] != "refreshOrderData"
			)
				$this->refreshOrderDataAction();
		}
		catch(UserMessageException $e)
		{
			$this->addResultError($e->getMessage());
		}

		return $this->result;
	}

	/**
	 * @param $message
	 */
	public function addResultError($message)
	{
		$this->result->addError(new EntityError($message));
	}

	protected function addResultData($dataKey, $data)
	{
		if(strlen($dataKey) <= 0)
			$this->result->addData($data);
		else
			$this->result->addData(array($dataKey => $data));
	}

	/* * * * * * requests actions handlers * * * * * * * * */

	protected function addProductToBasketAction()
	{
		if(!$this->request["formData"]) throw new ArgumentNullException("formatData");
		if(!$this->request["quantity"]) throw new ArgumentNullException("quantity");
		if(!$this->request["productId"]) throw new ArgumentNullException("productId");

		$productId = isset($this->request['productId']) ? intval($this->request['productId']) : 0;
		$quantity = isset($this->request['quantity']) ? intval($this->request['quantity']) : 1;
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();

		$alreadyInBasketCode = "";
		$productParams = array();

		if(isset($this->request["formData"]["PRODUCT"]) && is_array($this->request["formData"]["PRODUCT"]))
		{
			foreach($this->request["formData"]["PRODUCT"] as $basketCode => &$params)
			{
				if(!isset($params["MODULE"]) || $params["MODULE"] != "catalog")
					continue;

				if(!isset($params["OFFER_ID"]) || $params["OFFER_ID"] != $productId)
					continue;

				$params["QUANTITY"] += $quantity;
				$alreadyInBasketCode = $basketCode;
				$productParams = $params;
				break;
			}
		}

		if(empty($productParams))
		{
			$productParams = Admin\Blocks\OrderBasket::getProductDetails(
				$productId,
				$quantity,
				!empty($this->request["formData"]["USER_ID"]) ? $this->request["formData"]["USER_ID"] : \CSaleUser::GetAnonymousUserID(),
				isset($this->request["formData"]["SITE_ID"]) ? $this->request["formData"]["SITE_ID"] : SITE_ID,
				$columns
			);
		}

		if(
			isset($this->request["replaceBasketCode"])
			&& strlen($this->request["replaceBasketCode"]) > 0
			&& isset($this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]])
		)
		{
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]] = $productParams;

			if(strlen($alreadyInBasketCode) > 0)
				unset($this->request["formData"]["PRODUCT"][$alreadyInBasketCode]);
		}
		elseif(strlen($alreadyInBasketCode) <= 0)
		{
			$this->request["formData"]["PRODUCT"]["new"] = $productParams;
		}

		$this->formDataChanged = true;
	}

	protected function cancelOrderAction()
	{
		global $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$canceled = isset($this->request['canceled']) ? $this->request['canceled'] : "N";
		$comment = isset($this->request['comment']) ? trim($this->request['comment']) : "";

		if(!\CSaleOrder::CanUserCancelOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException("Insufficient rights to cancel order");

		/** @var  \Bitrix\Sale\Order $saleOrder*/
		if(!$saleOrder = \Bitrix\Sale\Order::load($orderId))
			throw new UserMessageException("Can't load order with id: ".$orderId);

		$state = $saleOrder->getField("CANCELED");

		if($state != $canceled)
			throw new UserMessageException($state == "Y" ? "Order already canceled." : "Order not canceled yet.");

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("CANCELED", $canceled == "Y" ? "N" : "Y");

		if(!$res->isSuccess())
			throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

		$saleOrder->setField("REASON_CANCELED", $canceled == "N" ? $comment : "");

		if(!$res = $saleOrder->save())
			throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

		$canceled = $saleOrder->getField("CANCELED");
		$this->addResultData("CANCELED", $canceled);

		if($canceled == "Y")
		{
			$userInfo = Admin\Blocks\OrderStatus::getUserInfo($saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("DATE_CANCELED", $saleOrder->getField("DATE_CANCELED")->toString());
			$this->addResultData("EMP_CANCELED_ID", $saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("EMP_CANCELED_NAME", $userInfo["NAME"]." (".$userInfo["LOGIN"].")");
		}
	}

	protected function saveStatusAction()
	{
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['statusId']) || strlen($this->request['statusId']) <= 0)
			throw new SystemException("Wrong status id!");


		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($this->request['orderId']);

		if (!$order)
			throw new UserMessageException("Can't load order with id: \"".$this->request['orderId']."\"");

		$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
			$this->userId,
			\Bitrix\Sale\OrderStatus::getInitialStatus()
		);

		if(array_key_exists($this->request['statusId'], $statusesList))
		{
			$res = $order->setField("STATUS_ID", $this->request['statusId']);

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

			$res = $order->save();

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));
		}
	}

	protected function getOrderFieldsAction()
	{
		if(!isset($this->request['demandFields']) || !array($this->request['demandFields']) || empty($this->request['demandFields']))
			throw new SystemException("Demand fields is empty!");

		$this->addResultData(
			"RESULT_FIELDS",
			$this->getDemandedFields(
				$this->request['demandFields'],
				$this->request['givenFields']
			)
		);
	}

	protected function refreshOrderDataAction()
	{
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$additional = isset($this->request["additional"]) ? $this->request["additional"] : array();
		$shipment = null;
		$opResults = new Result();

		//use data from form, don't refresh data from provider
		Admin\OrderEdit::$isTrustProductFormData = true;
		$order = $this->getOrder($formData, $opResults);
		$isStartField = $order->isStartField();

		if($order->getId() > 0)
			$order = Admin\OrderEdit::editOrderByFormData($formData, $order, $this->userId, false, array(), $opResults);

		if($order->getId() <= 0)
		{
			if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
			{
				$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
				$res->getErrorMessages();
				$data = $res->getData();
				/** @var \Bitrix\Sale\Shipment $shipment */
				$shipment = array_shift($data['SHIPMENT']);
			}

			if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
			{
				$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT'], true);
				$res->getErrorMessages();
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $order->hasMeaningfulField();

			/** @var Result $r */
			$r = $order->doFinalAction($hasMeaningfulFields);
		}

		$result['PAYABLE'] = $order->getPrice() - $order->getSumPaid();
		$result["BASE_PRICE"] = Admin\Blocks\OrderBasket::getBasePrice($order);

		$data = $this->result->getData();
		if ($shipment)
		{
			if ($shipment->getField('CUSTOM_PRICE_DELIVERY') == 'Y')
				$result["CUSTOM_PRICE"] = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);

			if (!isset($data['SHIPMENT_DATA']['DELIVERY_SERVICE_LIST']))
			{
				$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList();
				foreach ($deliveryService as $i => $dlService)
				{
					if($dlService['ID'] <= 0)
						continue;

					if (!Sale\Delivery\Services\Manager::checkServiceRestriction($dlService['ID'], $shipment, '\Bitrix\Sale\Delivery\Restrictions\BySite'))
						unset($deliveryService[$i]);
				}
				$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
				$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);
				if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
				{
					foreach ($deliveryService as $delivery)
					{
						if ($shipment->getDeliveryId() == $delivery['ID'] && $delivery['RESTRICTED'])
							$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					}
				}
			}
			if (!isset($data['SHIPMENT_DATA']['PROFILES']))
			{
				if ($shipment->getDeliveryId())
				{
					$service = Sale\Delivery\Services\Manager::getService($shipment->getDeliveryId());
					$parentService = $service->getParentService();
					if ($parentService && $parentService->canHasProfiles())
					{
						$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($parentService->getId());
						$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
						$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles);
						if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
						{
							foreach ($profiles as $profile)
							{
								if ($shipment->getDeliveryId() == $profile['ID'] && !$profile['RESTRICTED'])
									$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
							}
						}
					}
				}
			}
		}

		$paySystemList = Admin\Blocks\OrderPayment::getPaySystemList($order);
		$result['PAY_SYSTEM_LIST'] = Admin\Blocks\OrderPayment::makePaymentSelectHtmlBody($paySystemList);
		$orderBasket = new Admin\Blocks\OrderBasket($order,"", $this->request["formData"]["BASKET_PREFIX"]);
		$basketPrepareParams = array();

		if((
			!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_ADD")
			||$this->request["action"] == "addProductToBasket"
		)
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = false;
		}
		else
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = true;
		}

		$result["BASKET"] = $orderBasket->prepareData($basketPrepareParams);

		// collect info about changed fields
		if($basketPrepareParams["SKIP_SKU_INFO"] && !empty($formData["PRODUCT"]) && is_array($formData["PRODUCT"]))
		{
			//prices
			$result["BASKET"]["PRICES_UPDATED"] = array();
			$errors = array();
			$PRECISE = 0.005;

			foreach($formData["PRODUCT"] as $basketCode => $itemParams)
			{
				if($basketCode == "new")
					continue;

				if(!isset($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) || !isset($itemParams["PRICE"]))
				{
					$errors[] = "Product price with basket code \"".$basketCode."\" not found.";
					continue;
				}

				if(abs(floatval($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) - floatval($itemParams["PRICE"])) >= $PRECISE)
					$result["BASKET"]["PRICES_UPDATED"][$basketCode] = $result["BASKET"]["ITEMS"][$basketCode]["PRICE"];
			}

			if(!empty($errors))
				$this->addResultData("ERROR_PRICE_COMPARING", $errors);

		}

		$resData = $opResults->getData();

		if(!empty($resData["NEW_ITEM_BASKET_CODE"]))
			$result["BASKET"]["NEW_ITEM_BASKET_CODE"] = $resData["NEW_ITEM_BASKET_CODE"];

		$result['RELATED_PROPS'] = Admin\Blocks\OrderBuyer::getRelPropData($order);
		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);

		if ($order->getBasket())
			$result['BASE_PRICE_DELIVERY'] = $result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['BASE_PRICE'];
		else
			$result['BASE_PRICE_DELIVERY'] = $order->getDeliveryPrice();

		$result['BASE_PRICE_DELIVERY'] = roundEx($result['BASE_PRICE_DELIVERY'], SALE_VALUE_PRECISION);
		$result['DELIVERY_PRICE_DISCOUNT'] = roundEx($result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['PRICE'], SALE_VALUE_PRECISION);
		$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order, false);
		$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);
		$result["DELIVERY_DISCOUNT"] = $result["TOTAL_PRICES"]["DELIVERY_DISCOUNT"];

		$result = array_merge($result, $order->getFieldValues());

		if(!isset($result["PRICE"]))
			$result["PRICE"] = 0;

		/* DEMANDED */
		if(isset($additional["demanded"]) && is_array($additional["demanded"]))
		{
			if(isset($additional["given"]) && is_array($additional["given"]))
				$result=array_merge($result, $additional["given"]);

			$demanded = $this->getDemandedFields($additional["demanded"], $result, $order);
			$result = array_merge($result, $demanded);
		}

		/* * */
		if(!$opResults->isSuccess())
		{
			foreach($opResults->getErrors() as $error)
			{
				if($error->getCode() == "CATALOG_QUANTITY_NOT_ENOGH"
					|| $error->getCode() == "SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY"
					|| $error->getCode() == "CATALOG_NO_QUANTITY_PRODUCT"
			)
					$this->addResultError($error->getMessage());
			}
		}

		$this->addResultData("ORDER_DATA", $result);
	}

	protected function changeResponsibleUserAction()
	{
		if(!isset($this->request['user_id']) || intval($this->request['user_id']) <= 0)
			throw new ArgumentNullException("user_id");

		global $USER;
		$dateResponsible = new \Bitrix\Main\Type\DateTime();
		$userInformation = $USER->GetByID($this->request['user_id'])->Fetch();
		$this->addResultData(
			"RESPONSIBLE",
			array(
				htmlspecialcharsbx($userInformation['NAME']),
				htmlspecialcharsbx($userInformation['LAST_NAME'])
			)
		);

		$this->addResultData(
			"EMP_RESPONSIBLE",
			array(
				htmlspecialcharsbx($USER->GetFirstName()),
				htmlspecialcharsbx($USER->GetLastName())
			)
		);
		$this->addResultData("DATE_RESPONSIBLE", $dateResponsible->toString());
	}

	protected function updatePaymentStatusAction()
	{
		global $USER;

		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new ArgumentNullException("orderId");

		if(!isset($this->request['paymentId']) || intval($this->request['paymentId']) <= 0)
			throw new ArgumentNullException("paymentId");

		$fields = array();
		$orderStatusId = '';
		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($this->request['orderId']);
		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $order->getPaymentCollection()->getItemById($this->request['paymentId']);

		if ($this->request['method'] == 'save')
		{
			if ($payment->getField('IS_RETURN') == 'Y')
			{
				$res = $payment->setReturn('N');
				if (!$res->isSuccess())
					$this->addResultError(join("\n", $res->getErrorMessages()));
			}
			else
			{
				$res = $payment->setPaid('Y');
				if (!$res->isSuccess())
					$this->addResultError(join("\n", $res->getErrorMessages()));
			}

			foreach ($this->request['data'] as $key => $value)
			{
				$newKey = substr($key, 0, strripos($key, '_'));
				if (strpos($newKey, 'PAY_VOUCHER') !== false)
					$fields[$newKey] = $value;

				if ($newKey == 'ORDER_STATUS_ID')
					$orderStatusId = $value;
			}
			$fields['PAY_VOUCHER_DATE'] = new \Bitrix\Main\Type\Date($fields['PAY_VOUCHER_DATE']);
		}
		else
		{
			foreach ($this->request['data'] as $key => $value)
			{
				$newKey = substr($key, 0, strripos($key, '_'));
				if (strpos($newKey, 'PAY_RETURN') !== false)
					$fields[$newKey] = $value;
			}

			if (isset($fields['PAY_RETURN_OPERATION_ID']) && $fields['PAY_RETURN_OPERATION_ID'] == 'RETURN')
			{
				$res = $payment->setReturn('Y');
				if (!$res->isSuccess())
					$this->addResultError(join("\n", $res->getErrorMessages()));
				unset($fields['PAY_RETURN_OPERATION_ID']);
			}
			else
			{
				$res = $payment->setPaid('N');
				if (!$res->isSuccess())
					$this->addResultError(join("\n", $res->getErrorMessages()));
			}
			$fields['PAY_RETURN_DATE'] = new \Bitrix\Main\Type\Date($fields['PAY_RETURN_DATE']);
		}

		$saveResult = $payment->setFields($fields);
		if ($saveResult->isSuccess())
		{
			if (!empty($orderStatusId))
			{
				if ($USER && $USER->isAuthorized())
					$statusesList = Sale\OrderStatus::getAllowedUserStatuses($USER->getID(), $order->getField('STATUS_ID'));
				else
					$statusesList = Sale\OrderStatus::getAllStatuses();

				if ($order->getField('STATUS_ID') != $orderStatusId && array_key_exists($orderStatusId, $statusesList))
				{
					$res = $order->setField('STATUS_ID', $orderStatusId);
					if (!$res->isSuccess())
					{
						$this->addResultError(join("\n", $res->getErrorMessages()));
						return;
					}
				}
			}

			$result = $order->save();
			if ($result->isSuccess())
			{
				$preparedData = \Bitrix\Sale\Helpers\Admin\Blocks\OrderFinanceInfo::prepareData($order);
				$preparedData["PAYMENT_PAID_".$payment->getId()] = $payment->isPaid() ? "Y" : "N";

				$this->addResultData(
					"RESULT",
					$preparedData
				);
			}
			else
				$this->addResultError(join("\n", $result->getErrorMessages()));
		}
		else
		{
			$this->addResultError(join("\n", $saveResult->getErrorMessages()));
		}
	}

	protected function deletePaymentAction()
	{
		$orderId = $this->request['orderId'];
		$paymentId = $this->request['paymentId'];

		if ($orderId <= 0 || $paymentId <=0)
			throw new ArgumentNullException("paymentId or orderId");

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException('Order with ID='.$orderId.' does not exist');

		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($paymentId);

		if (!$payment)
			throw new UserMessageException('Payment with ID='.$paymentId.' does not exist');

		$delResult = $payment->delete();

		if ($delResult->isSuccess())
		{
			$result = $order->save();
			if ($result->isSuccess())
				$this->addResultData("RESULT", "OK");
			else
				throw new UserMessageException(join("\n", $result->getErrorMessages()));
		}
		else
		{
			throw new UserMessageException(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function deleteShipmentAction()
	{
		$orderId = $this->request['order_id'];
		$shipmentId = $this->request['shipment_id'];

		if ($orderId <= 0 || $shipmentId <= 0)
			throw new UserMessageException('Error');

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException('Order with ID='.$orderId.' does not exist');

		$shipmentCollection = $order->getShipmentCollection();
		$shipmentItem = $shipmentCollection->getItemById($shipmentId);

		if (!$shipmentItem)
			throw new UserMessageException('Shipment with ID='.$shipmentId.' does not exist');

		$delResult = $shipmentItem->delete();

		if ($delResult->isSuccess())
		{
			$saveResult = $order->save();
			if ($saveResult->isSuccess())
			{
				$result["DELIVERY_PRICE"] = $shipmentCollection->getBasePriceDelivery();
				$result["DELIVERY_PRICE_DISCOUNT"] = $shipmentCollection->getPriceDelivery();
				$result['PRICE'] = $order->getPrice();
				$result['PAYABLE'] = $result['PRICE'] - $order->getSumPaid();

				$orderBasket = new Admin\Blocks\OrderBasket($order);
				$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);

				$this->addResultData("RESULT", $result);
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
		else
		{
			$this->addResultError(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function saveBasketVisibleColumnsAction()
	{
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$idPrefix = isset($this->request['idPrefix']) ? $this->request['idPrefix'] : "";

		if(\CUserOptions::SetOption($idPrefix."order_basket_table", "table_columns", array("columns" => implode(",", $columns))))
			$this->addResultData("RESULT", "OK");
		else
			$this->addResultError("Can't save columns!");
	}

	protected function updateShipmentStatusAction()
	{
		$shipmentId = $this->request['shipmentId'];
		$orderId = $this->request['orderId'];
		$field = $this->request['field'];
		$newStatus = $this->request['status'];

		$order = \Bitrix\Sale\Order::load($orderId);
		$shipment = $order->getShipmentCollection()->getItemById($shipmentId);

		$setResult = $shipment->setField($field, $newStatus);

		if ($setResult->isSuccess())
		{
			$saveResult = $order->save();
			if (!$saveResult->isSuccess())
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
		}
		else
		{
			$serResultMessage = $setResult->getErrorMessages();
			if (!empty($serResultMessage))
				$this->addResultError(join("\n", $serResultMessage));
			else
				$this->addResultError(Loc::getMessage('SALE_OA_SHIPMENT_STATUS_ERROR'));
		}

		if($shipment)
			$this->addResultData("RESULT", array("SHIPMENT_STATUS_".$shipment->getId() => $shipment->getField('STATUS_ID')));
	}

	protected function createNewPaymentAction()
	{
		$formData = $this->request['formData'];
		$index = $this->request['index'];

		$order = $this->getOrder($formData);

		if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
		{
			$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
			$res->getErrorMessages();
		}

		if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
		{
			$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT']);
			$res->getErrorMessages();
		}

		$payment = $order->getPaymentCollection()->createItem();
		$this->addResultData("PAYMENT", \Bitrix\Sale\Helpers\Admin\Blocks\OrderPayment::getEdit($payment, $index));
	}

	protected function getProductEditDialogHtmlAction()
	{
		$currency = isset($this->request['currency']) ? $this->request['currency'] : array();
		$objName = isset($this->request['objName']) ? $this->request['objName'] : "";
		$this->addResultData(
			'DIALOG_CONTENT',
			Admin\Blocks\OrderBasket::getProductEditDialogHtml(
				$currency,
				$objName
			)
		);
	}

	protected function changeDeliveryServiceAction()
	{
		$result = array();
		$profiles = array();
		$index = $this->request['index'];
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];
		$deliveryId = $formData['SHIPMENT'][$index]['DELIVERY_ID'];

		$order = $this->getOrder($formData);

		/** @var  \Bitrix\Sale\Delivery\Services\Base $service */
		$service = Sale\Delivery\Services\Manager::getService($deliveryId);
		if ($service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($deliveryId);
			if (!isset($formData['SHIPMENT'][$index]['PROFILE']))
			{
				reset($profiles);
				$initProfile = current($profiles);
				$formData['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
				$this->request["formData"]['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
			}
		}

		$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
		$data = $res->getData();
		$shipment = array_shift($data['SHIPMENT']);

		if ($service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
			$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles, $index, $shipment->getDeliveryId());

			foreach ($profiles as $profile)
			{
				if ($formData['SHIPMENT'][$index]['PROFILE'] == $profile['ID'] && !$profile['RESTRICTED'])
				{
					$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					break;
				}
			}
		}

		$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList($shipment);
		foreach ($deliveryService as $i => $dlService)
		{
			if($dlService['ID'] <= 0)
				continue;

			if (!Sale\Delivery\Services\Manager::checkServiceRestriction($dlService['ID'], $shipment, '\Bitrix\Sale\Delivery\Restrictions\BySite'))
				unset($deliveryService[$i]);
		}
		$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
		$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);
		foreach ($deliveryService as $delivery)
		{
			if ($deliveryId == $delivery['ID'] && $delivery['RESTRICTED'])
			{
				$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
				break;
			}
		}

		$storeMap = Admin\Blocks\OrderShipment::getMap($shipment->getDeliveryId(), $index);
		if ($storeMap)
			$result['MAP'] = $storeMap;

		$extraServiceManager = new \Bitrix\Sale\Delivery\ExtraServices\Manager($deliveryId);
		$deliveryExtraService = $shipment->getExtraServices();
		if ($deliveryExtraService)
			$extraServiceManager->setValues($deliveryExtraService);

		$extraService = $extraServiceManager->getItems();
		if ($extraService)
			$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, $index);

		$deliveryPrice = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);
		if ($shipment->getField('CUSTOM_PRICE_DELIVERY') == 'Y')
			$result["CUSTOM_PRICE"] = $deliveryPrice;
		else
			$this->request['formData']['SHIPMENT'][$index]['PRICE_DELIVERY'] = $deliveryPrice;

		$this->addResultData("SHIPMENT_DATA", $result);

		$this->formDataChanged = true;
	}

	protected function getDefaultDeliveryPriceAction()
	{
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];

		$order = $this->getOrder($formData);

		$result = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);

		$data = $result->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);
		$deliveryPrice = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);

		$this->addResultData(
			"RESULT",
			array(
				"CUSTOM_PRICE" => $deliveryPrice
			)
		);
	}

	protected function checkProductBarcodeAction()
	{
		if(!\Bitrix\Main\Loader::includeModule("catalog"))
			throw new UserMessageException("ERROR");
		$basketItem = null;
		$result = false;

		$barcode = $this->request['barcode'];
		$basketId = $this->request['basketId'];
		$orderId = $this->request['orderId'];
		$storeId = $this->request['storeId'];

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			$basket = $order->getBasket();
			if ($basket)
				$basketItem = $basket->getItemById($basketId);
		}

		if ($basketItem)
		{
			$params = array(
				'BARCODE' => $barcode,
				'STORE_ID' => $storeId
			);
			$result = Provider::checkProductBarcode($basketItem, $params);
		}

		if ($result)
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function deleteCouponAction()
	{
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(Sale\DiscountCouponsManager::delete($this->request["coupon"]))
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function addCouponsAction()
	{
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(strlen($this->request["coupon"]) > 0)
		{
			$coupons = explode(",", $this->request["coupon"]);

			if(is_array($coupons) && count($coupons) > 0)
				foreach($coupons as $coupon)
					if(strlen($coupon) > 0)
						Sale\DiscountCouponsManager::add($coupon);
		}

		$this->addResultData('RESULT', 'OK');
	}

	protected function getProductIdByBarcodeAction()
	{
		\Bitrix\Main\Loader::includeModule('catalog');

		$barcode = $this->request['barcode'];

		if(strlen($barcode) > 0)
		{
			$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
			$arBarCode = $rsBarCode->Fetch();
		}

		$this->addResultData(
			'RESULT',
			array(
				"PRODUCT_ID" => isset($arBarCode["PRODUCT_ID"]) ? intval($arBarCode["PRODUCT_ID"]) : 0
			)
		);
	}

	/* * * * * * * accessory methods * * * * * * * */

	protected function getDemandedFields(array $demandedFields, array $incomingFields, \Bitrix\Sale\Order $order = null)
	{
		$result = array();
		$userId = isset($incomingFields["USER_ID"]) && intval($incomingFields["USER_ID"]) > 0 ? intval($incomingFields["USER_ID"])  : 0;
		$currency = isset($incomingFields["CURRENCY"]) ? trim($incomingFields["CURRENCY"]) : "";
		$personTypeId = isset($incomingFields['PERSON_TYPE_ID']) ? intval($incomingFields['PERSON_TYPE_ID']) : 0;
		$orderId = isset($incomingFields["ID"]) ? intval($incomingFields["ID"]) : 0;

		if($order === null && intval($orderId) > 0)
			$order = \Bitrix\Sale\Order::load($orderId);

		foreach($demandedFields as $demandedField)
		{
			switch($demandedField)
			{
				case "BUYER_USER_NAME":

					$result["BUYER_USER_NAME"] = intval($userId) > 0 ? \Bitrix\Sale\Helpers\Admin\OrderEdit::getUserName(intval($userId)) : "";
					break;

				case "PROPERTIES":

					$profileId = isset($incomingFields["BUYER_PROFILE_ID"]) ? intval($incomingFields["BUYER_PROFILE_ID"]) : 0;
					$result["PROPERTIES"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getProfileParams($userId, $profileId);
					break;

				case "BUYER_PROFILES_LIST":

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$result["BUYER_PROFILES_LIST"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerProfilesList($userId, $personTypeId);
					break;

				case "BUYER_PROFILES_DATA":

					$result["BUYER_PROFILES_DATA"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($userId);
					break;

				case "BUYER_BUDGET":
					$res = \CSaleUserAccount::getList(
						array(),
						array(
							'USER_ID' => $userId,
							'CURRENCY' => $currency,
							'LOCKED' => 'N'
						),
						false,
						false,
						array(
							'CURRENT_BUDGET'
						)
					);

					if($userAccount = $res->Fetch())
						$result["BUYER_BUDGET"] = $userAccount['CURRENT_BUDGET'];
					else
						$result["BUYER_BUDGET"] = 0;

					break;
				case "PROPERTIES_ARRAY":

					if(!$order)
						throw new \Bitrix\Main\SystemException("Can't init order");

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$order->setPersonTypeId($personTypeId);

					$result["PROPERTIES_ARRAY"] = $order->getPropertyCollection()->getArray();
					break;

				case "PRODUCT":
					$result["PRODUCT"] = array();
					break;

				case "COUPONS":
					if(!$userId)
						throw new \Bitrix\Main\ArgumentNullException("userId");

					$result["COUPONS"] = Admin\OrderEdit::getCouponsData();

					break;

				case "COUPONS_LIST":

					$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order);

					break;

				default:
					throw new \Bitrix\Main\SystemException("Field: \"".$demandedField."\" is unknown!");
			}
		}

		return $result;
	}

	/**
	 * @param $formData
	 * @return Sale\Order
	 * @throws ArgumentNullException
	 * @throws UserMessageException
	 */
	protected function getOrder(array $formData, Result &$result = null)
	{
		if(!isset($formData["ID"]))
			$formData["ID"] = 0;
		else
			$formData["ID"] = intval($formData["ID"]);

		if($this->order !== null  && !$this->formDataChanged && $this->order->getId() == $formData["ID"])
			return $this->order;

		if(!$result)
			$result = new Result();

		Admin\OrderEdit::initCouponsData(
			(intval($formData["USER_ID"]) > 0 ? intval($formData["USER_ID"]) : 0),
			(intval($formData["ID"]) > 0 ?  intval($formData["ID"]) : 0),
			(intval($formData["OLD_USER_ID"]) > 0 ?  intval($formData["OLD_USER_ID"]) : 0)
		);

		if($formData["ID"] > 0)
		{
			$this->order = Sale\Order::load($formData["ID"]);

			if(!$this->order)
				throw new UserMessageException("Can't load order with id:\"".$formData["ID"]."\"!");
		}
		else
		{
			$this->order = Admin\OrderEdit::createOrderFromForm($formData, $this->userId, false, array(), $result);

			if(!$this->order)
				throw new UserMessageException("Can't create order!");
		}

		$this->formDataChanged = false;
		return $this->order;
	}

	public static function convertEncodingArray($arData, $charsetFrom, $charsetTo, &$errorMessage = "")
	{
		if (!is_array($arData))
		{
			if (is_string($arData))
			{
				$arData = Encoding::convertEncoding($arData, $charsetFrom, $charsetTo, $errorMessage);
			}
		}
		else
		{
			foreach ($arData as $key => $value)
			{
				$s = '';

				$newKey = Encoding::convertEncoding($key, $charsetFrom, $charsetTo, $s);
				$arData[$newKey] = Encoding::convertEncodingArray($value, $charsetFrom, $charsetTo, $s);

				if($newKey != $key)
					unset($arData[$key]);

				if($s!=='')
				{
					$errorMessage .= ($errorMessage == "" ? "" : "\n").$s;
				}
			}
		}

		return $arData;
	}

	protected function updatePaySystemInfoAction()
	{
		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException("Incorrect order ID!");

		if ($this->request["paymentId"])
			$paymentId = $this->request["paymentId"];
		else
			throw new UserMessageException("Incorrect payment ID!");

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $order->getPaymentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			if ($payment)
			{
				$psResultFile = '';
				$psParams = Admin\Blocks\OrderPayment::getPaySystemParams($payment->getPaymentSystemId(), $order->getPersonTypeId());

				$psActionPath = $_SERVER["DOCUMENT_ROOT"].$psParams["ACTION_FILE"];

				$psActionPath = str_replace("\\", "/", $psActionPath);
				while (substr($psActionPath, strlen($psActionPath) - 1, 1) == "/")
					$psActionPath = substr($psActionPath, 0, strlen($psActionPath) - 1);

				if (file_exists($psActionPath) && is_dir($psActionPath))
				{
					if (file_exists($psActionPath."/result.php") && is_file($psActionPath."/result.php"))
						$psResultFile = $psActionPath."/result.php";
				}
				elseif (strlen($psParams["RESULT_FILE"]) > 0)
				{
					if (file_exists($_SERVER["DOCUMENT_ROOT"].$psParams["RESULT_FILE"])
						&& is_file($_SERVER["DOCUMENT_ROOT"].$psParams["RESULT_FILE"])
					)
					{
						$psResultFile = $_SERVER["DOCUMENT_ROOT"].$psParams["RESULT_FILE"];
					}
				}

				if (strlen($psResultFile) > 0)
				{
					\CSalePaySystemAction::InitParamArrays($order->getFieldValues(), $orderId, $psParams["PARAMS"], array(), $payment->getFieldValues());

					try
					{
						if (!include($psResultFile))
							$this->addResultError(GetMessage("SALE_OA_ERROR_PAY_SYSTEM_INFO"));
					}
					catch(SystemException $e)
					{
						$this->addResultError($e->getMessage());
					}
				}
			}
		}
	}
}