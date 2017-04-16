<?php

namespace Bitrix\Sale\Delivery\ExtraServices;

use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class String extends Base
{
	public function __construct($id, array $structure, $value = null, array $additionalParams = array())
	{
		$structure["PARAMS"]["ONCHANGE"] = 'BX.onCustomEvent("onDeliveryExtraServiceValueChange", [{"id" : "'.$id.'", "value": this.value, "price": this.value*parseFloat("'.$structure["PARAMS"]["PRICE"].'")}]);';
		parent::__construct($id, $structure, $value);
		$this->params["TYPE"] = "STRING";

		if(isset($structure["PARAMS"]["PRICE"]))
			$this->params["PRICE"] = $structure["PARAMS"]["PRICE"];
	}

	public function getClassTitle()
	{
		return Loc::getMessage("DELIVERY_EXTRA_SERVICE_STRING_TITLE");
	}


	public function setValue($value)
	{
		$this->value = intval($value);
	}

	public function getCost()
	{
		if(!isset($this->params["PRICE"]))
			throw new SystemException("Service id: ".$this->id." doesn't have field PRICE"); // todo ?

		return floatval($this->params["PRICE"])*floatval($this->value);
	}

	public static function getAdminParamsName()
	{
		return Loc::getMessage("DELIVERY_EXTRA_SERVICE_STRING_PRICE");
	}

	public static function getAdminParamsControl($name, array $params = array(), $currency = "")
	{
		return '<input type="text" name="'.$name.'[PARAMS][PRICE]" value="'.$params["PARAMS"]["PRICE"].'">'.(strlen($currency) > 0 ? " (".$currency.")" : "");
	}
}
