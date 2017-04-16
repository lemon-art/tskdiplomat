<?php

namespace Bitrix\Sale\Compatible;

use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

class EventCompatibility extends Sale\Compatible\Internals\EntityCompatibility
{
	// Events old kernel, which will be called in a new kernel
	const EVENT_COMPATIBILITY_ON_ORDER_PAID = "OnSalePayOrder";
	const EVENT_COMPATIBILITY_ON_ORDER_PAID_SEND_EMAIL = "OnOrderPaySendEmail";
	const EVENT_COMPATIBILITY_ON_ORDER_CANCEL_SEND_EMAIL = "OnOrderCancelSendEmail";
	const EVENT_COMPATIBILITY_ON_BEFORE_ORDER_DELETE = "OnBeforeOrderDelete";
	const EVENT_COMPATIBILITY_ON_ORDER_DELETED = "OnOrderDelete";
	const EVENT_COMPATIBILITY_ON_SHIPMENT_DELIVER = "OnSaleDeliveryOrder";
	const EVENT_COMPATIBILITY_ON_ORDER_SAVE = "OnOrderSave";
	const EVENT_COMPATIBILITY_ON_ORDER_ADD = "OnOrderAdd";

	const EVENT_COMPATIBILITY_ON_BEFORE_BASKET_ITEM_ADD = "OnBeforeBasketAdd";
	const EVENT_COMPATIBILITY_ON_BEFORE_BASKET_ITEM_UPDATE = "OnBeforeBasketUpdate";

	const EVENT_COMPATIBILITY_ON_BEFORE_ORDER_CANCELED = "OnSaleBeforeCancelOrder";
	const EVENT_COMPATIBILITY_ON_ORDER_CANCELED = "OnSaleCancelOrder";



	/**
	 * @param string $event
	 * @return bool
	 */
	public static function getEventListUsed($event)
	{
		return GetModuleEvents("sale", $event, true);
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSalePayOrder(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_ORDER_PAY_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_ORDER_PAY_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();
		$value = $order->getField('PAYED');
		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_PAID, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $value));
		}

		if ($value == "Y")
		{
			$orderFields = null;

			/** @var Sale\Result $r */
			$r = OrderCompatibility::getOrderFields($order);
			if ($r->isSuccess())
			{
				if ($resultOrderFieldsData = $r->getData())
				{
					if (!empty($resultOrderFieldsData['ORDER_FIELDS']) && is_array($resultOrderFieldsData['ORDER_FIELDS']))
					{
						$orderFields = $resultOrderFieldsData['ORDER_FIELDS'];
					}
				}
			}

			\CSaleMobileOrderPush::send("ORDER_PAYED", array("ORDER" => $orderFields));

			if (Main\Loader::includeModule("statistic"))
			{
				\CStatEvent::AddByEvents("eStore", "order_paid", $id, "", $order->getField("STAT_GID"), $order->getPrice(), $order->getCurrency());
			}
		}
		else
		{
			if (Main\Loader::includeModule("statistic"))
			{
				\CStatEvent::AddByEvents("eStore", "order_chargeback", $id, "", $order->getField("STAT_GID"), $order->getPrice(), $order->getCurrency(), "Y");
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleOrderPaidSendMail(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_ORDER_PAY_SEND_EMAIL_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_ORDER_PAY_SEND_EMAIL_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();
		$value = $order->getField('PAYED');

		if ($value == "Y")
		{
			$userEmail = "";
			/** @var Sale\PropertyValueCollection $propertyCollection */
			if ($propertyCollection = $order->getPropertyCollection())
			{
				if ($propUserEmail = $propertyCollection->getUserEmail())
					$userEmail = $propUserEmail->getValue();
			}

			if(strval($userEmail) == '')
			{
				$resUser = \CUser::GetByID($order->getUserId());
				if($userData = $resUser->Fetch())
					$userEmail = $userData["EMAIL"];
			}

			$fields = Array(
				"ORDER_ID" => $order->getField("ACCOUNT_NUMBER"),
				"ORDER_DATE" => $order->getDateInsert()->toString(),
				"EMAIL" => $userEmail,
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"])
			);

			$eventName = "SALE_ORDER_PAID";
			$send = true;

			foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_PAID_SEND_EMAIL, true) as $oldEvent)
			{
				if (ExecuteModuleEventEx($oldEvent, array($id, &$eventName, &$fields)) === false)
				{
					$send = false;
				}
			}

			if($send)
			{
				$event = new \CEvent;
				$event->Send($eventName, $order->getField('LID'), $fields, "N");
			}

		}
		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleOrderCancelSendEmail(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_ORDER_CANCEL_SEND_EMAIL_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_ORDER_CANCEL_SEND_EMAIL_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();
		$value = $order->getField('CANCELED');
		$description = $order->getField('REASON_CANCELED');

		if ($value == "Y")
		{
			$userEmail = "";
			/** @var Sale\PropertyValueCollection $propertyCollection */
			if ($propertyCollection = $order->getPropertyCollection())
			{
				if ($propUserEmail = $propertyCollection->getUserEmail())
					$userEmail = $propUserEmail->getValue();
			}

			if(strval($userEmail) == '')
			{
				$resUser = \CUser::GetByID($order->getUserId());
				if($userData = $resUser->Fetch())
					$userEmail = $userData["EMAIL"];
			}

			$fields = Array(
				"ORDER_ID" => $order->getField("ACCOUNT_NUMBER"),
				"ORDER_DATE" => $order->getDateInsert()->toString(),
				"EMAIL" => $userEmail,
				"ORDER_CANCEL_DESCRIPTION" => $description,
				"SALE_EMAIL" => Main\Config\Option::get("sale", "order_email", "order@".$_SERVER["SERVER_NAME"])
			);

			$eventName = "SALE_ORDER_CANCEL";
			$send = true;

			foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_CANCEL_SEND_EMAIL, true) as $oldEvent)
			{
				if (ExecuteModuleEventEx($oldEvent, array($id, &$eventName, &$fields)) === false)
				{
					$send = false;
				}
			}

			if($send)
			{
				$event = new \CEvent;
				$event->Send($eventName, $order->getField('LID'), $fields, "N");
			}


			$orderFields = null;

			/** @var Sale\Result $r */
			$r = OrderCompatibility::getOrderFields($order);
			if ($r->isSuccess())
			{
				if ($resultOrderFieldsData = $r->getData())
				{
					if (!empty($resultOrderFieldsData['ORDER_FIELDS']) && is_array($resultOrderFieldsData['ORDER_FIELDS']))
					{
						$orderFields = $resultOrderFieldsData['ORDER_FIELDS'];
					}
				}
			}

			\CSaleMobileOrderPush::send("ORDER_CANCELED", array("ORDER" => $orderFields));

			if (Main\Loader::includeModule("statistic"))
			{
				\CStatEvent::AddByEvents("eStore", "order_cancel", $id, "", $order->getField("STAT_GID"));
			}

		}
		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onBeforeOrderDelete(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_BEFORE_ORDER_DELETE_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_BEFORE_ORDER_DELETE_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_BEFORE_ORDER_DELETE, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($id)) === false)
			{
				return new Main\EventResult(
					Main\EventResult::SUCCESS,
					array('RETURN' => false),
					'sale');
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onOrderDelete(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage(''), 'SALE_EVENT_COMPATIBILITY_ORDER_DELETE_WRONG_ORDER'),
				'sale'
			);
		}

		$deleted = $parameters[1];
		$id = $order->getId();

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_BEFORE_ORDER_DELETE, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $deleted));
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 * @throws Main\ObjectNotFoundException
	 */
	public static function onSaleDeliveryOrder(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Shipment $shipment */
		$shipment = $parameters[0];
		if (!$shipment instanceof Sale\Shipment)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_DELIVER_ORDER_WRONG_SHIPMENT'), 'SALE_EVENT_COMPATIBILITY_DELIVER_ORDER_WRONG_SHIPMENT'),
				'sale'
			);
		}

		/** @var Sale\ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $shipment->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Sale\Order $order */
		if (!$order = $shipmentCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$id = $order->getId();

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_SHIPMENT_DELIVER, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $shipment->getField('ALLOW_DELIVERY')));
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onOrderSave(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_ORDER_SAVE_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_ORDER_SAVE_WRONG_ORDER'),
				'sale'
			);
		}

		$isNew = $parameters[1];
		$id = $order->getId();

		$fields = null;
		$orderFields = null;

		/** @var Sale\Result $resultOrderFields */
		$resultOrderFields = OrderCompatibility::getOrderFields($order);
		if ($resultOrderFields->isSuccess())
		{
			if ($orderFieldsResultData = $resultOrderFields->getData())
			{
				if (!empty($orderFieldsResultData['FIELDS']) && is_array($orderFieldsResultData['FIELDS']))
				{
					$fields = $orderFieldsResultData['FIELDS'];
				}
				if (!empty($orderFieldsResultData['ORDER_FIELDS']) && is_array($orderFieldsResultData['ORDER_FIELDS']))
				{
					$orderFields = $orderFieldsResultData['ORDER_FIELDS'];
				}
			}
		}

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_SAVE, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $fields, $orderFields, $isNew));
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onOrderAdd(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		$isNew = $parameters[1];
		if (!$isNew)
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_ORDER_ADD_WRONG_ORDER'), 'SALE_EVENT_COMPATIBILITY_ORDER_ADD_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();

		$fields = null;
		$orderFields = null;

		/** @var Sale\Result $resultOrderFields */
		$resultOrderFields = OrderCompatibility::getOrderFields($order);
		if ($resultOrderFields->isSuccess())
		{
			if ($orderFieldsResultData = $resultOrderFields->getData())
			{
				if (!empty($orderFieldsResultData['ORDER_FIELDS']) && is_array($orderFieldsResultData['ORDER_FIELDS']))
				{
					$orderFields = $orderFieldsResultData['ORDER_FIELDS'];
				}
			}
		}

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_ADD, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $orderFields));
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onSaleBeforeCancelOrder(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage(''), 'SALE_EVENT_COMPATIBILITY_BEFORE_ORDER_DELETE_WRONG_ORDER'),
				'sale'
			);
		}

		$id = $order->getId();
		$value = $order->getField('CANCELED');;

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_BEFORE_ORDER_CANCELED, true) as $oldEvent)
		{
			if (ExecuteModuleEventEx($oldEvent, array($id, $value)) === false)
			{
				return new Main\EventResult(
					Main\EventResult::SUCCESS,
					array('RETURN' => false),
					'sale');
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onSaleCancelOrder(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\Order $order */
		$order = $parameters[0];
		if (!$order instanceof Sale\Order)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage(''), 'SALE_EVENT_COMPATIBILITY_ORDER_DELETE_WRONG_ORDER'),
				'sale'
			);
		}

		$canceled = $order->getField('CANCELED');
		$id = $order->getId();
		$description = $order->getField('REASON_CANCELED');

		foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_ORDER_CANCELED, true) as $oldEvent)
		{
			ExecuteModuleEventEx($oldEvent, array($id, $canceled, $description));
			$order->setField('REASON_CANCELED', $description);
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onBasketItemChange(Main\Event $event)
	{
		$parameters = $event->getParameters();

		/** @var Sale\BasketItem $basketItem */
		$basketItem = $parameters[0];
		if (!$basketItem instanceof Sale\BasketItem)
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_COMPATIBILITY_BASKET_ITEM_CHANGE_WRONG_BASKET'), 'SALE_EVENT_COMPATIBILITY_BASKET_ITEM_CHANGE_WRONG_BASKET'),
				'sale'
			);
		}

		$currentBasketFields = $basketFields = BasketCompatibility::convertBasketItemToArray($basketItem);

		if ($basketItem->getId() == 0)
		{
			foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_BEFORE_BASKET_ITEM_ADD, true) as $oldEvent)
			{
				if (ExecuteModuleEventEx($oldEvent, Array(&$basketFields))===false)
				{
					continue;
				}
			}
		}
		else
		{
			foreach(GetModuleEvents("sale", static::EVENT_COMPATIBILITY_ON_BEFORE_BASKET_ITEM_UPDATE, true) as $oldEvent)
			{
				if (ExecuteModuleEventEx($oldEvent, array($basketItem->getId(), &$basketFields)) === false)
				{
					continue;
				}
			}
		}

		foreach ($currentBasketFields as $key => $value)
		{
			if (isset($basketFields[$key]) && $basketFields[$key] != $value)
			{
				$basketItem->setFieldNoDemand($key, $basketFields[$key]);
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 *
	 */
	public static function registerEvents()
	{
		$eventManager = Main\EventManager::getInstance();

		$eventManager->registerEventHandler('sale', 'OnSaleOrderPaid', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSalePayOrder');

		$eventManager->registerEventHandler('sale', 'OnSaleBeforeOrderDelete', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBeforeOrderDelete');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderDeleted', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderDelete');

		$eventManager->registerEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleDeliveryOrder');

		$eventManager->registerEventHandler('sale', 'OnSaleBeforeOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleBeforeCancelOrder');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleCancelOrder');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderPaidSendMail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderPaidSendMail');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderCancelSendEmail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderCancelSendEmail');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderAdd');

		$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderSave');

		$eventManager->registerEventHandler('sale', 'OnSaleBasketItemBeforeSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBasketItemChange');

	}

	/**
	 *
	 */
	public static function unRegisterEvents()
	{
		$eventManager = Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaid', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSalePayOrder');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleBeforeOrderDelete', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBeforeOrderDelete');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderDeleted', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderDelete');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleDeliveryOrder');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleBeforeOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleBeforeCancelOrder');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleCancelOrder');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaidSendMail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderPaidSendMail');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderCancelSendEmail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderCancelSendEmail');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderSave');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderAdd');

		$eventManager->unRegisterEventHandler('sale', 'OnSaleBasketItemBeforeSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBasketItemChange');
	}

}
