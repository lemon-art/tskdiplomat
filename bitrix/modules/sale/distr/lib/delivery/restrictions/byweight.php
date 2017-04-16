<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class ByWeight
 * Restricts delivery by weight
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByWeight extends Base
{
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_WEIGHT_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_WEIGHT_DESCRIPT");
	}

	public function check($weight, array $restrictionParams, $deliveryId = 0)
	{
		$result = true;

		if($weight > 0)
		{
			if(intval($restrictionParams["MIN_WEIGHT"]) > 0  && $weight < intval($restrictionParams["MIN_WEIGHT"]))
				$result = false;
			elseif(intval($restrictionParams["MAX_WEIGHT"]) > 0 && $weight > $restrictionParams["MAX_WEIGHT"])
				$result = false;
		}

		return $result;
	}

	public function checkByShipment(\Bitrix\Sale\Shipment $shipment, array $restrictionParams, $deliveryId = 0)
	{
		if(empty($restrictionParams))
			return true;

		$weight = $shipment->getWeight();
		return $this->check($weight, $restrictionParams, $deliveryId);
	}

	public static function getParamsStructure()
	{
		return array(
			"MIN_WEIGHT" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => "0",
				'MIN' => 0,
				'LABEL' => Loc::getMessage("SALE_DLVR_RSTR_BY_WEIGHT_MIN_WEIGHT")
			),
			"MAX_WEIGHT" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => "0",
				'MIN' => 0,
				'LABEL' => Loc::getMessage("SALE_DLVR_RSTR_BY_WEIGHT_MAX_WEIGHT")
			)
		);
	}
} 