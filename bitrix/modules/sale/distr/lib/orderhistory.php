<?php


namespace Bitrix\Sale;


use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class OrderHistory
{
	protected static $pool = array();
	protected static $poolFields = array();

	const SALE_ORDER_HISTORY_UPDATE = 'UPDATE';

	const SALE_ORDER_HISTORY_RECORD_TYPE_ACTION = 'ACTION';
	const SALE_ORDER_HISTORY_RECORD_TYPE_FIELD = 'FIELD';

	protected function __construct()
	{

	}

	/**
	 * @param string $entity
	 * @param int $orderId
	 * @param string $field
	 * @param null|string $oldValue
	 * @param null|string $value
	 * @param int $id
	 * @param array $fields
	 */
	public static function addField($entity, $orderId, $field, $oldValue = null, $value = null, $id = null, array $fields = array())
	{
		if ($field == "ID")
			return;
		static::$pool[$entity][$orderId][$id][$field] = array(
			'RECORD_TYPE' => static::SALE_ORDER_HISTORY_RECORD_TYPE_FIELD,
			'ENTITY' => $entity,
			'ORDER_ID' => $orderId,
			'ID' => $id,
			'NAME' => $field,
			'OLD_VALUE' => $oldValue,
			'VALUE' => $value,
			'DATA' => $fields
		);
	}

	/**
	 * @param $entity
	 * @param $orderId
	 * @param $type
	 * @param null $id
	 * @param array $fields
	 */
	public static function addAction($entity, $orderId, $type, $id = null, array $fields = array())
	{
		static::$pool[$entity][$orderId][$id][$type] = array(
			'RECORD_TYPE' => static::SALE_ORDER_HISTORY_RECORD_TYPE_ACTION,
			'ENTITY' => $entity,
			'ID' => $id,
			'TYPE' => $type,
			'DATA' => $fields
		);
	}

	/**
	 * @param $entity
	 * @param $orderId
	 * @param null|int $id
	 * @return bool
	 */
	public static function collectEntityFields($entity, $orderId, $id = null)
	{
		$oldFields = array();
		$fields = array();

		if (!$poolEntity = static::getPoolByEntity($entity, $orderId))
		{
			return false;
		}

		if ($id !== null)
		{
			$found = false;
			foreach ($poolEntity as $entityId => $fieldValue)
			{
				if ($entityId == $id)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
				return false;
		}





		foreach ($poolEntity as $entityId => $fieldValue)
		{
			if ($id !== null && $entityId != $id)
				continue;

			foreach ($fieldValue as $data)
			{
				if ($data['RECORD_TYPE'] == static::SALE_ORDER_HISTORY_RECORD_TYPE_ACTION)
				{
					static::addRecord($entity, $orderId, $data['TYPE'], $data['ID'], $data['DATA']);
					unset(static::$pool[$entity][$orderId][$data['ID']][$data['TYPE']]);

					continue;
				}

				$value = $data['VALUE'];
				$oldValue = $data['OLD_VALUE'];

				if (static::isDate($value))
					$value = static::convertDateField($value);

				if (static::isDate($oldValue))
					$oldValue = static::convertDateField($oldValue);

				$oldFields[$data['NAME']] = $oldValue;
				$fields[$data['NAME']] = $value;

				if (!empty($data['DATA']) && is_array($data['DATA']))
				{
					if (!empty($data['DATA']['PRODUCT_ID']))
						$fields['~PRODUCT_ID'] = $data['DATA']['PRODUCT_ID'];

					if (!empty($data['DATA']['NAME']))
						$fields['~NAME'] = $data['DATA']['NAME'];

				}
			}

			unset(static::$pool[$entity][$orderId][$entityId]);
		}

		\CSaleOrderChange::AddRecordsByFields($orderId, $oldFields, $fields, array(), $entity, $id);

		unset(static::$pool[$entity][$orderId]);
	}

	/**
	 * @param $entity
	 * @param $orderId
	 * @return bool|array
	 */
	protected static function getPoolByEntity($entity, $orderId)
	{
		if (empty(static::$pool[$entity])
			|| empty(static::$pool[$entity][$orderId])
			|| !is_array(static::$pool[$entity][$orderId]))
		{
			return false;
		}

		return static::$pool[$entity][$orderId];
	}

	/**
	 * @param $entity
	 * @param $orderId
	 * @param $type
	 * @param null $id
	 * @param array $data
	 */
	protected static function addRecord($entity, $orderId, $type, $id = null, array $data = array())
	{
		\CSaleOrderChange::AddRecord($orderId, $type, $data, $entity, $id);
	}

	/**
	 * @param $value
	 * @return bool
	 */
	private static function isDate($value)
	{
		return ($value instanceof DateTime) || ($value instanceof Date);
	}

	/**
	 * @param $value
	 * @return string
	 */
	private static function convertDateField($value)
	{
		if (($value instanceof DateTime)
			|| ($value instanceof Date))
		{
			return $value->toString();
		}

		return $value;
	}

}