<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\ArgumentNullException;

class CSaleStoreChooseComponent extends CBitrixComponent
{
	public function checkParams($params)
	{
		if(!isset($params["INDEX"]))
			throw new ArgumentNullException('params["INDEX"]');

//		if(!isset($params["DELIVERY_ID"]) || intval($params["DELIVERY_ID"] <= 0))
//			throw new ArgumentNullException('params["DELIVERY_ID"]');

		if(!isset($params["STORES_LIST"]) || !is_array($params["STORES_LIST"]) || count($params["STORES_LIST"]) <= 0 )
			throw new ArgumentNullException('params["STORES_LIST"]');

		return true;
	}

	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if(!isset($this->arParams["WIDTH"]))
			$this->arParams["WIDTH"] = 400;

		if(!isset($this->arParams["HEIGHT"]))
			$this->arParams["HEIGHT"] = 400;

		if(!isset($this->arParams["SELECTED_STORE"]))
		{
			$this->arParams["SELECTED_STORE"] = 0;
			if (isset($params["STORES_LIST"]) && is_array($params["STORES_LIST"]))
			{
				reset($params["STORES_LIST"]);
				$this->arParams["SELECTED_STORE"] = key($params["STORES_LIST"]);
			}
		}

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		$stores = array();
		$arStoreLocation = array("yandex_scale" => 11, "PLACEMARKS" => array());

		foreach($this->arParams["STORES_LIST"] as $storeId => $storeParams)
		{
			$stores[$storeParams["ID"]] = $storeParams;

			if (intval($storeParams["IMAGE_ID"]) > 0)
			{
				$arImage = CFile::GetFileArray($storeParams["IMAGE_ID"]);
				$imgValue = CFile::ShowImage($arImage, 115, 115, "border=0", "", false);
				$stores[$storeParams["ID"]]["IMAGE"] = $imgValue;
				$stores[$storeParams["ID"]]["IMAGE_URL"] = $arImage["SRC"];
			}

			if (floatval($arStoreLocation["yandex_lat"]) <= 0)
				$arStoreLocation["yandex_lat"] = $storeParams["GPS_N"];

			if (floatval($arStoreLocation["yandex_lon"]) <= 0)
				$arStoreLocation["yandex_lon"] = $storeParams["GPS_S"];

			$arLocationTmp = array();
			$arLocationTmp["ID"] = $storeParams["ID"];
			if (strlen($storeParams["GPS_N"]) > 0)
				$arLocationTmp["LAT"] = $storeParams["GPS_N"];
			if (strlen($storeParams["GPS_S"]) > 0)
				$arLocationTmp["LON"] = $storeParams["GPS_S"];
			if (strlen($storeParams["TITLE"]) > 0)
				$arLocationTmp["TEXT"] = $storeParams["TITLE"]."\r\n".$storeParams["DESCRIPTION"];
			$arStoreLocation["PLACEMARKS"][] = $arLocationTmp;
		}
		$this->arResult["LOCATION"] = serialize($arStoreLocation);
		$this->arResult["SHOW_IMAGES"] = (isset($_REQUEST["showImages"]) && $_REQUEST["showImages"] == "Y");
		$this->arResult["STORES"] = $stores;
		$this->includeComponentTemplate();
	}
}