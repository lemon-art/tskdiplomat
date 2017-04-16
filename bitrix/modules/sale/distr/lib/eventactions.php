<?php
namespace Bitrix\Sale;

class EventActions
{
	const ADD = "ADD";
	const UPDATE = "UPDATE";
	const DELETE = "DELETE";

	// Events new kernel
	const EVENT_ON_ORDER_PAID = "OnSaleOrderPaid";
	const EVENT_ON_PAYMENT_PAID = "OnSalePaymentPaid";
	const EVENT_ON_BEFORE_ORDER_DELETE = "OnSaleBeforeOrderDelete";
	const EVENT_ON_ORDER_DELETED = "OnSaleOrderDeleted";
	const EVENT_ON_ORDER_BEFORE_SAVED = "OnSaleOrderBeforeSaved";
	const EVENT_ON_ORDER_SAVED = "OnSaleOrderSaved";
	const EVENT_ON_SHIPMENT_DELIVER = "OnSaleShipmentDelivery";

	const EVENT_ON_BEFORE_ORDER_CANCELED = "OnSaleBeforeOrderCanceled";
	const EVENT_ON_ORDER_CANCELED = "OnSaleOrderCanceled";

	const EVENT_ON_ORDER_PAID_SEND_MAIL = "OnSaleOrderPaidSendMail";
	const EVENT_ON_ORDER_CANCELED_SEND_MAIL = "OnSaleOrderCancelSendEmail";

	const EVENT_ON_BASKET_BEFORE_SAVED = "OnSaleBasketBeforeSaved";
	const EVENT_ON_BASKET_ITEM_BEFORE_SAVED = "OnSaleBasketItemBeforeSaved";
	const EVENT_ON_BASKET_SAVED = "OnSaleBasketSaved";

}
