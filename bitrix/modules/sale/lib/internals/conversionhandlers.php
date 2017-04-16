<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Conversion\Config;
use Bitrix\Conversion\DayContext;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @internal */
final class ConversionHandlers
{
	static public function onGetCounterTypes()
	{
		return array(
			'sale_cart_add_day' => array('MODULE' => 'sale', 'NAME' => 'Added to cart', 'GROUP' => 'day'),
			'sale_cart_sum_add' => array('MODULE' => 'sale', 'NAME' => 'Sum added to cart'),
			//'sale_cart_sum_rem' => array('MODULE' => 'sale', 'GROUP' => 'sale_cart_sum', 'NAME' => 'Sum removed from cart'),
			'sale_order_add_day' => array('MODULE' => 'sale', 'NAME' => 'Placed orders', 'GROUP' => 'day'),
			'sale_order_sum_add' => array('MODULE' => 'sale', 'NAME' => 'Sum placed orders'),
			//'sale_order_sum_rem' => array('MODULE' => 'sale', 'GROUP' => 'sale_order_sum', 'NAME' => 'Sum removed orders'),
			'sale_payment_add_day' => array('MODULE' => 'sale', 'NAME' => 'Payments a day', 'GROUP' => 'day'),
			'sale_payment_sum_add' => array('MODULE' => 'sale', 'NAME' => 'Sum added payments'),
			//'sale_payment_sum_rem' => array('MODULE' => 'sale', 'GROUP' => 'sale_payment_sum', 'NAME' => 'Sum removed payments'),
		);
	}

	static public function onGetRateTypes()
	{
		$scale = array(0.5, 1, 1.5, 2, 5);

		$format = array(
			'SUM' => function ($value, $format = null)
			{
				return Config::formatToBaseCurrency($value, $format);
			},
		);

		$units = array('SUM' => Config::getBaseCurrencyUnit()); // TODO deprecated

		return array(
			'sale_payment' => array(
				'NAME'      => Loc::getMessage('SALE_CONVERSION_RATE_PAYMENT_NAME'),
				'SCALE'     => $scale,
				'FORMAT'    => $format,
				'UNITS'     => $units,
				'MODULE'    => 'sale',
				'SORT'      => 1100,
				'COUNTERS'  => array('conversion_visit_day', 'sale_payment_add_day', 'sale_payment_sum_add'),
				'CALCULATE' => function (array $counters)
				{
					$denominator = $counters['conversion_visit_day'] ?: 0;
					$numerator   = $counters['sale_payment_add_day'] ?: 0;
					$sum         = $counters['sale_payment_sum_add'] ?: 0;

					return array(
						'DENOMINATOR' => $denominator,
						'NUMERATOR'   => $numerator,
						'RATE'        => $denominator ? $numerator / $denominator : 0,
						'SUM'         => $sum,
					);
				},
			),

			'sale_order' => array(
				'NAME'      => Loc::getMessage('SALE_CONVERSION_RATE_ORDER_NAME'),
				'SCALE'     => $scale,
				'FORMAT'    => $format,
				'UNITS'     => $units,
				'MODULE'    => 'sale',
				'SORT'      => 1200,
				'COUNTERS'  => array('conversion_visit_day', 'sale_order_add_day', 'sale_order_sum_add'),
				'CALCULATE' => function (array $counters)
				{
					$denominator = $counters['conversion_visit_day'] ?: 0;
					$numerator   = $counters['sale_order_add_day'  ] ?: 0;
					$sum         = $counters['sale_order_sum_add'  ] ?: 0;

					return array(
						'DENOMINATOR' => $denominator,
						'NUMERATOR'   => $numerator,
						'RATE'        => $denominator ? $numerator / $denominator : 0,
						'SUM'         => $sum,
					);
				},
			),

			'sale_cart' => array(
				'NAME'      => Loc::getMessage('SALE_CONVERSION_RATE_CART_NAME'),
				'SCALE'     => $scale,
				'FORMAT'    => $format,
				'UNITS'     => $units,
				'MODULE'    => 'sale',
				'SORT'      => 1300,
				'COUNTERS'  => array('conversion_visit_day', 'sale_cart_add_day', 'sale_cart_sum_add'),
				'CALCULATE' => function (array $counters)
				{
					$denominator = $counters['conversion_visit_day'] ?: 0;
					$numerator   = $counters['sale_cart_add_day'   ] ?: 0;
					$sum         = $counters['sale_cart_sum_add'   ] ?: 0;

					return array(
						'DENOMINATOR' => $denominator,
						'NUMERATOR'   => $numerator,
						'RATE'        => $denominator ? $numerator / $denominator : 0,
						'SUM'         => $sum,
					);
				},
			),
		);
	}

	static public function onGenerateInitialData(Date $from, Date $to)
	{
		$data = array();

		// 1. Payments

		$result = \CSaleOrder::GetList(
			array(),
			array(
				'PAYED'        => 'Y',
				'CANCELED'     => 'N',
				'>=DATE_PAYED' => $from,
				'<=DATE_PAYED' => $to,
			),
			false,
			false,
			array('LID', 'DATE_PAYED', 'PRICE', 'CURRENCY')
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_PAYED']);
			$sum = Config::convertToBaseCurrency($row['PRICE'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_payment_add_day'] += 1;
				$counters['sale_payment_sum_add'] += $sum;
			}
			else
			{
				$counters = array(
					'sale_payment_add_day' => 1,
					'sale_payment_sum_add' => $sum,
				);
			}
		}

		// 2. Orders

		$result = \CSaleOrder::GetList(
			array(),
			array(
				'CANCELED'      => 'N',
				'>=DATE_INSERT' => $from,
				'<=DATE_INSERT' => $to,
			),
			false,
			false,
			array('LID', 'DATE_INSERT', 'PRICE', 'CURRENCY')
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_INSERT']);
			$sum = Config::convertToBaseCurrency($row['PRICE'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_order_add_day'] += 1;
				$counters['sale_order_sum_add'] += $sum;
			}
			else
			{
				$counters = array(
					'sale_order_add_day' => 1,
					'sale_order_sum_add' => $sum,
				);
			}
		}

		// 3. Cart

		$result = \CSaleBasket::GetList(
			array(),
			array(
				'>=DATE_INSERT' => $from,
				'<=DATE_INSERT' => $to,
			),
			false,
			false,
			array('LID', 'DATE_INSERT', 'PRICE', 'CURRENCY', 'QUANTITY')
		);

		while ($row = $result->Fetch())
		{
			$day = new DateTime($row['DATE_INSERT']);
			$sum = Config::convertToBaseCurrency($row['PRICE'] * $row['QUANTITY'], $row['CURRENCY']);

			if ($counters =& $data[$row['LID']][$day->format('Y-m-d')])
			{
				$counters['sale_cart_add_day'] += 1;
				$counters['sale_cart_sum_add'] += $sum;
			}
			else
			{
				$counters = array(
					'sale_cart_add_day' => 1,
					'sale_cart_sum_add' => $sum,
				);
			}
		}

		// Result

		unset($counters);

		$result = array();

		foreach ($data as $siteId => $dayCounters)
		{
			$result []= array(
				'ATTRIBUTES'   => array('conversion_site' => $siteId),
				'DAY_COUNTERS' => $dayCounters,
			);
		}

		return $result;
	}

	// Cart Counters

	static private $cartSum;

	static public function onBeforeBasketAdd(array $fields)
	{
		if (Loader::includeModule('conversion'))
		{
			if ($row = \CSaleBasket::GetList(
				array(),
				array(
					'LID'        => $fields['LID'],
					'FUSER_ID'   => $fields['FUSER_ID'],
					'PRODUCT_ID' => $fields['PRODUCT_ID'],
					'ORDER_ID'   => 'NULL',
				),
				false,
				false,
				array('PRICE', 'QUANTITY')
			)->Fetch())
			{
				self::$cartSum = $row['PRICE'] * $row['QUANTITY'];
			}
			else
			{
				self::$cartSum = 0;
			}
		}
	}

	static public function onBasketAdd($id, array $fields)
	{
		if (Loader::includeModule('conversion'))
		{
			$sum = $fields['PRICE'] * $fields['QUANTITY'];

			if ($sum > self::$cartSum)
			{
				$context = DayContext::getInstance();
				$context->addDayCounter     ('sale_cart_add_day', 1);
				$context->addCurrencyCounter('sale_cart_sum_add', $sum - self::$cartSum, $fields['CURRENCY']);
			}
		}
	}

	// Order Counters

	static public function onOrderAdd($id, array $fields)
	{
		if (Loader::includeModule('conversion'))
		{
			$context = DayContext::getInstance();
			$context->addDayCounter     ('sale_order_add_day', 1);
			$context->addCurrencyCounter('sale_order_sum_add', $fields['PRICE'], $fields['CURRENCY']);
			$context->attachEntityItem  ('sale_order', $id);
		}
	}

	// Payment Counters

	static public function onSalePayOrder($id, $paid)
	{
		if (Loader::includeModule('conversion') && ($row = \CSaleOrder::GetById($id)))
		{
			if ($paid == 'Y')
			{
				$context = DayContext::getEntityItemInstance('sale_order', $id);

				if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					$context->addCounter    ('sale_payment_add_day', 1);
				}
				else
				{
					$context->addDayCounter ('sale_payment_add_day', 1);
				}

				$context->addCurrencyCounter('sale_payment_sum_add', $row['PRICE'], $row['CURRENCY']);
			}
		}
	}
}
