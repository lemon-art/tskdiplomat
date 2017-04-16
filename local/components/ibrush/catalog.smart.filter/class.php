<?
use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.smart.filter");
class CBitrixCatalogSmartFilter2 extends CBitrixCatalogSmartFilter
{

	public function searchValue($item, $lookupValue)
	{
		global $APPLICATION;
		foreach($item as $itemId => $arValue)
		{
			$arValue['UPPER'] = str_replace('&QUOT;', '"', $arValue['UPPER']);

			if (strtoupper($lookupValue) === $arValue['UPPER'] && preg_match("/[А-Яа-я]/", $lookupValue)){
				if (strtoupper(htmlspecialchars($lookupValue)) !== strtoupper($arValue["URL_ID"])){
					header("Location: " . str_replace(htmlspecialchars($lookupValue), $arValue["URL_ID"], $APPLICATION->GetCurPage()));
				}
			}
			if ($lookupValue === $arValue["URL_ID"] ) {
				return $itemId;
			}
		}
		return $lookupValue;//false

//		foreach($item as $itemId => $arValue)
//		{
//			if ($lookupValue === $arValue["URL_ID"])
//				return $itemId;
//		}
//		return false;
	}

	public function fillItemValues(&$resultItem, $arProperty, $flag = null)
	{
		static $cache = array();

		if(is_array($arProperty))
		{
			if(isset($arProperty["PRICE"]))
			{
				return null;
			}
			$key = $arProperty["VALUE"];
			$PROPERTY_TYPE = $arProperty["PROPERTY_TYPE"];
			$PROPERTY_USER_TYPE = $arProperty["USER_TYPE"];
			$PROPERTY_ID = $arProperty["ID"];
		}
		else
		{
			$key = $arProperty;
			$PROPERTY_TYPE = $resultItem["PROPERTY_TYPE"];
			$PROPERTY_USER_TYPE = $resultItem["USER_TYPE"];
			$PROPERTY_ID = $resultItem["ID"];
			$arProperty = $resultItem;
		}

		if($PROPERTY_TYPE == "F")
		{
			return null;
		}
		elseif($PROPERTY_TYPE == "N")
		{
			$convertKey = (float)$key;
			if (strlen($key) <= 0)
			{
				return null;
			}

			if (
				!isset($resultItem["VALUES"]["MIN"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
				|| doubleval($resultItem["VALUES"]["MIN"]["VALUE"]) > $convertKey
			)
				$resultItem["VALUES"]["MIN"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

			if (
				!isset($resultItem["VALUES"]["MAX"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
				|| doubleval($resultItem["VALUES"]["MAX"]["VALUE"]) < $convertKey
			)
				$resultItem["VALUES"]["MAX"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

			return null;
		}
		elseif($arProperty["DISPLAY_TYPE"] == "U")
		{
			$date = substr($key, 0, 10);
			if (!$date)
			{
				return null;
			}
			$timestamp = MakeTimeStamp($date, "YYYY-MM-DD");
			if (!$timestamp)
			{
				return null;
			}

			if (
				!isset($resultItem["VALUES"]["MIN"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
				|| $resultItem["VALUES"]["MIN"]["VALUE"] > $timestamp
			)
				$resultItem["VALUES"]["MIN"]["VALUE"] = $timestamp;

			if (
				!isset($resultItem["VALUES"]["MAX"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
				|| $resultItem["VALUES"]["MAX"]["VALUE"] < $timestamp
			)
				$resultItem["VALUES"]["MAX"]["VALUE"] = $timestamp;

			return null;
		}
		elseif($PROPERTY_TYPE == "E" && $key <= 0)
		{
			return null;
		}
		elseif($PROPERTY_TYPE == "G" && $key <= 0)
		{
			return null;
		}
		elseif(strlen($key) <= 0)
		{
			return null;
		}

		$arUserType = array();
		if($PROPERTY_USER_TYPE != "")
		{
			$arUserType = CIBlockProperty::GetUserType($PROPERTY_USER_TYPE);
			if(isset($arUserType["GetExtendedValue"]))
				$PROPERTY_TYPE = "Ux";
			elseif(isset($arUserType["GetPublicViewHTML"]))
				$PROPERTY_TYPE = "U";
		}

		if ($PROPERTY_USER_TYPE === "DateTime")
		{
			$key = call_user_func_array(
				$arUserType["GetPublicViewHTML"],
				array(
					$arProperty,
					array("VALUE" => $key),
					array("MODE" => "SIMPLE_TEXT", "DATETIME_FORMAT" => "SHORT"),
				)
			);
			$PROPERTY_TYPE = "S";
		}

		$htmlKey = htmlspecialcharsbx($key);
		if (isset($resultItem["VALUES"][$htmlKey]))
		{
			return $htmlKey;
		}

		$file_id = null;
		$url_id = null;

		switch($PROPERTY_TYPE)
		{
			case "L":
				$enum = CIBlockPropertyEnum::GetByID($key);
				if ($enum)
				{
					$value = $enum["VALUE"];
					$sort  = $enum["SORT"];
					$url_id = toLower($enum["XML_ID"]);
				}
				else
				{
					return null;
				}
				break;
			case "E":
				if(!isset($cache[$PROPERTY_TYPE][$key]))
				{
					$arLinkFilter = array (
						"ID" => $key,
						"ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
					);
					$rsLink = CIBlockElement::GetList(array(), $arLinkFilter, false, false, array("ID","IBLOCK_ID","NAME","SORT","CODE"));
					$cache[$PROPERTY_TYPE][$key] = $rsLink->Fetch();
				}

				$value = $cache[$PROPERTY_TYPE][$key]["NAME"];
				$sort = $cache[$PROPERTY_TYPE][$key]["SORT"];
				if ($cache[$PROPERTY_TYPE][$key]["CODE"])
					$url_id = toLower($cache[$PROPERTY_TYPE][$key]["CODE"]);
				else
					$url_id = toLower($value);
				break;
			case "G":
				if(!isset($cache[$PROPERTY_TYPE][$key]))
				{
					$arLinkFilter = array (
						"ID" => $key,
						"GLOBAL_ACTIVE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
					);
					$rsLink = CIBlockSection::GetList(array(), $arLinkFilter, false, array("ID","IBLOCK_ID","NAME","LEFT_MARGIN","DEPTH_LEVEL","CODE"));
					$cache[$PROPERTY_TYPE][$key] = $rsLink->Fetch();
					$cache[$PROPERTY_TYPE][$key]['DEPTH_NAME'] = str_repeat(".", $cache[$PROPERTY_TYPE][$key]["DEPTH_LEVEL"]).$cache[$PROPERTY_TYPE][$key]["NAME"];
				}

				$value = $cache[$PROPERTY_TYPE][$key]['DEPTH_NAME'];
				$sort = $cache[$PROPERTY_TYPE][$key]["LEFT_MARGIN"];
				if ($cache[$PROPERTY_TYPE][$key]["CODE"])
					$url_id = toLower($cache[$PROPERTY_TYPE][$key]["CODE"]);
				else
					$url_id = toLower($value);
				break;
			case "U":
				if(!isset($cache[$PROPERTY_ID]))
					$cache[$PROPERTY_ID] = array();

				if(!isset($cache[$PROPERTY_ID][$key]))
				{
					$cache[$PROPERTY_ID][$key] = call_user_func_array(
						$arUserType["GetPublicViewHTML"],
						array(
							$arProperty,
							array("VALUE" => $key),
							array("MODE" => "SIMPLE_TEXT"),
						)
					);
				}

				$value = $cache[$PROPERTY_ID][$key];
				$sort = 0;

				if( preg_match("/[А-Яа-я]/", $value) ) {
					$url_id = toLower($key);
				} else {
					$url_id = toLower($value);
				}
				//$url_id = toLower($key);//$url_id = toLower($value);
				break;
			case "Ux":
				if(!isset($cache[$PROPERTY_ID]))
					$cache[$PROPERTY_ID] = array();

				if(!isset($cache[$PROPERTY_ID][$key]))
				{
					$cache[$PROPERTY_ID][$key] = call_user_func_array(
						$arUserType["GetExtendedValue"],
						array(
							$arProperty,
							array("VALUE" => $key),
						)
					);
				}

				$value = $cache[$PROPERTY_ID][$key]['VALUE'];
				$file_id = $cache[$PROPERTY_ID][$key]['FILE_ID'];
				$sort = (isset($cache[$PROPERTY_ID][$key]['SORT']) ? $cache[$PROPERTY_ID][$key]['SORT'] : 0);
				$url_id = toLower($cache[$PROPERTY_ID][$key]['UF_XML_ID']);
				break;
			default:
				$value = $key;
				$sort = 0;
				$url_id = toLower($value);
				break;
		}

		$keyCrc = abs(crc32($htmlKey));
		$safeValue = htmlspecialcharsex($value);
		$sort = (int)$sort;

		$filterPropertyID = $this->SAFE_FILTER_NAME.'_'.$PROPERTY_ID;
		$filterPropertyIDKey = $filterPropertyID.'_'.$keyCrc;
		$resultItem["VALUES"][$htmlKey] = array(
			"CONTROL_ID" => $filterPropertyIDKey,
			"CONTROL_NAME" => $filterPropertyIDKey,
			"CONTROL_NAME_ALT" => $filterPropertyID,
			"HTML_VALUE_ALT" => $keyCrc,
			"HTML_VALUE" => "Y",
			"VALUE" => $safeValue,
			"SORT" => $sort,
			"UPPER" => ToUpper($safeValue),
			"FLAG" => $flag,
		);

		if ($file_id)
		{
			$resultItem["VALUES"][$htmlKey]['FILE'] = CFile::GetFileArray($file_id);
		}

		if ($url_id)
		{
			$resultItem["VALUES"][$htmlKey]['URL_ID'] = $url_id;
		}

		return $htmlKey;
	}
}