<?php

namespace Bitrix\Sale\Delivery\ExtraServices;

use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Checkbox extends Base
{
	public function __construct($id, array $structure, $value = null, array $additionalParams = array())
	{
		$structure["PARAMS"]["ONCHANGE"] = 'BX.onCustomEvent("onDeliveryExtraServiceValueChange", [{"id" : "'.$id.'", "value": this.checked, "price": this.checked ? "'.$structure["PARAMS"]["PRICE"].'" : "0"}]);';
		parent::__construct($id, $structure, $value, $additionalParams);
		$this->params["TYPE"] = "Y/N";

		if(isset($structure["PARAMS"]["PRICE"]))
			$this->params["PRICE"] = $structure["PARAMS"]["PRICE"];
	}

	public function getClassTitle()
	{
		return Loc::getMessage("DELIVERY_EXTRA_SERVICE_CHECKBOX_TITLE");
	}

	public function getCost()
	{
		if(!isset($this->params["PRICE"]))
			throw new SystemException("Service id: ".$this->id." doesn't have field PRICE"); // todo ?

		if($this->value == "Y")
			$result = $this->params["PRICE"];
		else
			$result = 0;

		return $result;
	}

	public static function getAdminParamsName()
	{
		return Loc::getMessage("DELIVERY_EXTRA_SERVICE_CHECKBOX_PRICE");
	}

	public static function getAdminParamsControl($name, array $params, $currency = "")
	{
		return \Bitrix\Sale\Internals\Input\Manager::getEditHtml(
			$name."[PARAMS][PRICE]",
			array(
				"TYPE" => "NUMBER"
			),
			$params["PARAMS"]["PRICE"]
		).(strlen($currency) > 0 ? " (".$currency.")" : "");
	}
}
