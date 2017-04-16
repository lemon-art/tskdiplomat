<?php
namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Event;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/* Inputs for deliveries */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

/**
 * Class Base (abstract)
 * Base class for delivery services
 * @package Bitrix\Sale\Delivery
 */
abstract class Base
{
	public $name = "";
	public $description = "";
	public $parentId = 0;

	public $id = 0;

	public $sort = 100;
	public $logotip = 0;

	public $countPriceImmediately = false;

	protected $extraServices = array();
	protected $code = "";
	protected $active = false;
	protected $config = array();
	protected $currency = "";

	const EVENT_ON_CALCULATE = "onSaleDeliveryServiceCalculate";

	/**
	 * Constructor
	 * @param array $initParams Delivery service params
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct(array $initParams)
	{
		$initParams = $this->prepareFieldsForUsing($initParams);

		if(isset($initParams["PARENT_ID"]))
			$this->parentId = $initParams["PARENT_ID"];
		else
			$this->parentId = 0;

		if(!isset($initParams["ACTIVE"]))
			$initParams["ACTIVE"] = "N";

		if(!isset($initParams["NAME"]))
			$initParams["NAME"] = "";

		if(!isset($initParams["CONFIG"]))
			$initParams["CONFIG"] = array();

		if(!is_array($initParams["CONFIG"]))
			throw new \Bitrix\Main\ArgumentTypeException("CONFIG", "array");
/*
		if(isset($initParams["ID"]) && intval($initParams["ID"]) <=0 )
			throw new \Bitrix\Main\ArgumentTypeException("ID", "int");
*/
		$this->active = $initParams["ACTIVE"];
		$this->name = $initParams["NAME"];
		$this->config = $initParams["CONFIG"];

		if(isset($initParams["ID"]) )
			$this->id = $initParams["ID"];

		if(isset($initParams["DESCRIPTION"]))
			$this->description = $initParams["DESCRIPTION"];

		if(isset($initParams["CODE"]))
			$this->code = $initParams["CODE"];

		if(isset($initParams["SORT"]))
			$this->sort = $initParams["SORT"];

		if(isset($initParams["LOGOTIP"]))
			$this->logotip = $initParams["LOGOTIP"];

		if(isset($initParams["CURRENCY"]))
			$this->currency = $initParams["CURRENCY"];

		if(isset($initParams["EXTRA_SERVICES"]))
			$this->extraServices = new \Bitrix\Sale\Delivery\ExtraServices\Manager($initParams["EXTRA_SERVICES"]);
		elseif($this->id > 0)
			$this->extraServices = new \Bitrix\Sale\Delivery\ExtraServices\Manager($this->id);
		else
			$this->extraServices = new \Bitrix\Sale\Delivery\ExtraServices\Manager(array());
	}

	/**
	 * Calculates delivery price
	 * @param \Bitrix\Sale\Shipment $shipment.
	 * @return \Bitrix\Sale\Delivery\CalculationResult
	 */
	public function calculate(\Bitrix\Sale\Shipment $shipment = null) // null for compability with old configurable services
	{
		if($shipment && !$shipment->getCollection())
			return false;

		$result = $this->calculateConcrete($shipment);

		if($shipment)
		{
			$this->extraServices->setValues(
				$shipment->getExtraServices()
			);

			$extraServicePrice = $this->extraServices->getTotalCost();

			if(floatval($extraServicePrice) > 0)
				$result->setExtraServicesPrice($extraServicePrice);
		}

		$eventParams = array(
			"RESULT" => $result,
			"SHIPMENT" => $shipment
		);

		$event = new Event('sale', self::EVENT_ON_CALCULATE, $eventParams);
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as &$eventResult)
			{
				if ($eventResult->getType() != EventResult::SUCCESS)
					continue;

				$params = $eventResult->getParameters();

				if(isset($params["RESULT"]))
					$result = $params["RESULT"];
			}
		}

		return $result;
	}

	public function getExtraServices()
	{
		return $this->extraServices;
	}

	public function getCurrency()
	{
		return $this->currency;
	}

	protected static function calculateShipmentPrice(\Bitrix\Sale\Shipment $shipment)
	{
		$result = 0;

		foreach($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			/** @var  \Bitrix\Sale\BasketItem $basketItem */
			$basketItem = $shipmentItem->getBasketItem();
			$result += $basketItem->getPrice();
		}

		return $result;
	}

	/**
	 * Returns class name
	 * @return string
	 */
	public static function getClassTitle()
	{
		return "";
	}

	/**
	 * Returns class description
	 * @return string
	 */
	public static function getClassDescription()
	{
		return "";
	}

	/**
	 * @param \Bitrix\Sale\Shipment $shipment.
	 * @return \Bitrix\Sale\Delivery\CalculationResult
	 */
	abstract protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment);

	/**
	 * Prepares config array for saving
	 * @param array $config array config to prepare
	 * @return array
	 */
	public function prepareFieldsForSaving(array $fields)
	{
		$strError = "";

		$structure = $fields["CLASS_NAME"]::getConfigStructure();
		foreach($structure as $key1 => $rParams)
		{
			foreach($rParams["ITEMS"] as $key2 => $iParams)
			{
				if($iParams["TYPE"] == "DELIVERY_SECTION")
					continue;

				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($iParams, $fields["CONFIG"][$key1][$key2]);

				if(!empty($errors))
				{
					$strError .= Loc::getMessage("SALE_DLVR_BASE_FIELD")." \"".$iParams["NAME"]."\": ".implode("<br>\n", $errors)."<br>\n";
				}
			}
		}

		if($strError != "")
			throw new SystemException($strError);

		return $fields;
	}

	/**
	 * Returns service configuration (only structure without values)
	 * @return array
	 * @throws \Exception
	 */
	protected function getConfigStructure()
	{
		return array();
	}

	/**
	 * @param array $confStructure The structure of configuration
	 * @param array $confValues The configuration's values
	 * @return array glued config with values
	 */
	protected function glueValuesToConfig(array $confStructure, $confValues = array())
	{
		if(!is_array($confValues))
			$confValues = array();

		if(isset($confStructure["ITEMS"]) && is_array($confStructure["ITEMS"]))
		{
			$confStructure["ITEMS"] = $this->glueValuesToConfig($confStructure["ITEMS"], $confValues);
		}
		else
		{
			foreach($confStructure as $itemKey => $itemParams)
			{
				if(isset($confStructure[$itemKey]["VALUE"]))
					continue;

				if(isset($itemParams["ITEMS"]) && is_array($itemParams["ITEMS"]))
					$confStructure[$itemKey]["ITEMS"] = $this->glueValuesToConfig($itemParams["ITEMS"], $confValues[$itemKey]);
				elseif(isset($confValues[$itemKey]))
					$confStructure[$itemKey]["VALUE"] = $confValues[$itemKey];
				elseif(!isset($itemParams["VALUE"]) && isset($itemParams["DEFAULT"]))
					$confStructure[$itemKey]["VALUE"] = $itemParams["DEFAULT"];
			}
		}

		return $confStructure;
	}

	/**
	 * @return array Base Config structure with values
	 */
	public function getConfig()
	{
		$configStructure = $this->getConfigStructure();

		if(!is_array($configStructure))
			throw new SystemException ("Method getConfigStructure() must return an array!");

		foreach($configStructure as $key => $configSection)
			$configStructure[$key] = $this->glueValuesToConfig($configSection, isset($this->config[$key]) ? $this->config[$key] : array());

		return $configStructure;
	}

	/**
	 * @return array Fields witch user will see on delivery admin page
	 */
	public static function getAdminFieldsList()
	{
		return Table::getMap();
	}

	/**
	 * @return bool Show or not restrictions on admin page
	 * For example lib/delivery/services/group.php: we must hide it on public page always, and nobody can cancel this.
	 */
	public static function whetherAdminRestrictionsShow()
	{
		return true;
	}

	/**
	 * @return bool Can this services has children.
	 */
	public static function canHasChildren()
	{
		return false;
	}

	/**
	 * @return bool Can this services has profiles.
	 */
	public static function canHasProfiles()
	{
		return false;
	}

	/**
	 * @return array profiles handlers class names
	 */
	public static function getChildrenClassNames()
	{
		$classNamesList = Manager::getHandlersClassNames();
		unset($classNamesList[array_search('\Bitrix\Sale\Delivery\Services\Group', $classNamesList)]);
		unset($classNamesList[array_search('\Bitrix\Sale\Delivery\Services\AutomaticProfile', $classNamesList)]);
		return $classNamesList;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	public function getLogotip()
	{
		return $this->logotip;
	}

	public function getLogotipPath()
	{
		$logo = $this->getLogotip();
		return intval($logo) > 0 ? \CFile::GetPath($logo) : "";
	}

	public function getParentService()
	{
		return null;
	}

	public function prepareFieldsForUsing(array $fields)
	{
		return $fields;
	}

	public function getEmbeddedExtraServicesList()
	{
		return array();

		/*
		exapmple for concrete handlers
		return array(
			"ZAPALECH" => array(
				"NAME" => "extra service name",
				"SORT" => 50,
				"RIGHTS" => "YYY",
				"ACTIVE" => "Y",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\ExtraServices\Checkbox',
				"DESCRIPTION" => "Extra service description",
				"PARAMS" => array("PRICE" => 2000)
			)
		);
		*/
	}

	/**
	* @return bool If admin could edit extra services
	*/
	public static function whetherAdminExtraServicesShow()
	{
		return false;
	}

	/**
	 * @param $serviceId
	 * @param array $fields
	 * @return bool
	 */
	public static function onAfterAdd($serviceId, array $fields = array())
	{
		return true;
	}

	/**
	 * @param Shipment $shipment
	 * @return bool
	 */
	public function isCompatible(Shipment $shipment)
	{
		return true;
	}
}