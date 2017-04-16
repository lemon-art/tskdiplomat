<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Date
 * @package Bitrix\Bizproc\BaseType
 */
class Date extends Base
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::DATE;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return null|mixed
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();
		switch ($type)
		{
			case FieldType::DOUBLE:
			case FieldType::INT:
				$value = $value? (int)strtotime($value) : 0;
				break;
			case FieldType::DATE:
			case FieldType::DATETIME:
			case FieldType::STRING:
			case FieldType::TEXT:
				$value = (string) $value;
				if ($value)
				{
					if ($type == FieldType::DATE)
						$format = \FORMAT_DATE;
					elseif ($type == FieldType::DATETIME)
						$format = \FORMAT_DATETIME;
					else
						$format = static::getType() == FieldType::DATE ? \FORMAT_DATE : \FORMAT_DATETIME;

					if (\CheckDateTime($value, $format))
					{
						$value = date(Type\Date::convertFormatToPhp($format), \MakeTimeStamp($value, $format));
					}
					else
					{
						$value = date(Type\Date::convertFormatToPhp($format), strtotime($value));
					}
				}
				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param mixed $value
	 * @param bool $allowSelection
	 * @param int $renderMode
	 * @return string
	 */
	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		$name = static::generateControlName($field);
		$renderResult = '';

		if ($renderMode & FieldType::RENDER_MODE_ADMIN)
		{
			require_once(Loader::getLocal('/modules/main/interface/init_admin.php'));
			$renderResult = \CAdminCalendar::calendarDate($name, $value, 19, static::getType() == FieldType::DATETIME);
		}
		else
		{
			ob_start();
			global $APPLICATION;

			$APPLICATION->includeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'SHOW_INPUT' => 'Y',
					'FORM_NAME' => $field['Form'],
					'INPUT_NAME' => $name,
					'INPUT_VALUE' => $value,
					'SHOW_TIME' => static::getType() == FieldType::DATETIME ? 'Y' : 'N'
				),
				false,
				array('HIDE_ICONS' => 'Y')
			);

			$renderResult = ob_get_contents();
			ob_end_clean();
		}

		return $renderResult;
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param array $request
	 * @return null|string
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);

		if ($value !== null && is_string($value) && strlen($value) > 0)
		{
			$format = static::getType() == FieldType::DATETIME ? \FORMAT_DATETIME : \FORMAT_DATE;
			if(!\CheckDateTime($value, $format))
			{
				$value = null;
				static::addError(array(
					'code' => 'ErrorValue',
					'message' => Loc::getMessage('BPDT_DATE_INVALID'),
					'parameter' => static::generateControlName($field),
				));
			}
			else
				$value = \ConvertDateTime($value, $format);
		}
		else
		{
			$value = null;
		}

		return $value;
	}
}