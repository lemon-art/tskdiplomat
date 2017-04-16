<?php

namespace Bitrix\Lists;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('bizproc'))
{
	return;
}

class BizprocDocumentLists extends \CIBlockDocument
{
	public static function getEntityName()
	{
		return Loc::getMessage('LISTS_BIZPROC_ENTITY_LISTS_NAME');
	}

	public function AddDocumentField($documentType, $fields)
	{
		$iblockId = intval(substr($documentType, strlen("iblock_")));
		if ($iblockId <= 0)
			throw new \CBPArgumentOutOfRangeException("documentType", $documentType);

		if (substr($fields["code"], 0, strlen("PROPERTY_")) == "PROPERTY_")
			$fields["code"] = substr($fields["code"], strlen("PROPERTY_"));

		$fieldsTemporary = array(
			"NAME" => $fields["name"],
			"ACTIVE" => "Y",
			"SORT" => $fields["sort"] ? $fields["sort"] : 900,
			"CODE" => $fields["code"],
			'MULTIPLE' => $fields['multiple'] == 'Y' || (string)$fields['multiple'] === '1' ? 'Y' : 'N',
			'IS_REQUIRED' => $fields['required'] == 'Y' || (string)$fields['required'] === '1' ? 'Y' : 'N',
			"IBLOCK_ID" => $iblockId,
			"FILTRABLE" => "Y",
			"SETTINGS" => $fields["settings"] ? $fields["settings"] : array("SHOW_ADD_FORM" => 'Y', "SHOW_EDIT_FORM"=>'Y'),
			"DEFAULT_VALUE" => $fields['DefaultValue']
		);

		if (strpos("0123456789", substr($fieldsTemporary["CODE"], 0, 1))!==false)
			$fieldsTemporary["CODE"] = self::generatePropertyCode($fields["name"], $fields["code"], $iblockId);

		if (array_key_exists("additional_type_info", $fields))
			$fieldsTemporary["LINK_IBLOCK_ID"] = intval($fields["additional_type_info"]);

		if (strstr($fields["type"], ":") !== false)
		{
			if($fields["type"] == "S:DiskFile")
			{
				$fieldsTemporary["TYPE"] = $fields["type"];
			}
			else
			{
				list($fieldsTemporary["TYPE"], $fieldsTemporary["USER_TYPE"]) = explode(":", $fields["type"], 2);
				if ($fields["type"] == "E:EList")
					$fieldsTemporary["LINK_IBLOCK_ID"] = $fields["options"];
			}
		}
		elseif ($fields["type"] == "user")
		{
			$fieldsTemporary["TYPE"] = "S:employee";
			$fieldsTemporary["USER_TYPE"]= "UserID";
		}
		elseif ($fields["type"] == "date")
		{
			$fieldsTemporary["TYPE"] = "S:Date";
			$fieldsTemporary["USER_TYPE"]= "Date";
		}
		elseif ($fields["type"] == "datetime")
		{
			$fieldsTemporary["TYPE"] = "S:DateTime";
			$fieldsTemporary["USER_TYPE"]= "DateTime";
		}
		elseif ($fields["type"] == "file")
		{
			$fieldsTemporary["TYPE"] = "F";
			$fieldsTemporary["USER_TYPE"]= "";
		}
		elseif ($fields["type"] == "select")
		{
			$fieldsTemporary["TYPE"] = "L";
			$fieldsTemporary["USER_TYPE"]= false;

			if (is_array($fields["options"]))
			{
				$i = 10;
				foreach ($fields["options"] as $k => $v)
				{
					$def = "N";
					if($fields['DefaultValue'] == $v)
						$def = "Y";
					$fieldsTemporary["VALUES"][] = array("XML_ID" => $k, "VALUE" => $v, "DEF" => $def, "SORT" => $i);
					$i = $i + 10;
				}
			}
			elseif (is_string($fields["options"]) && (strlen($fields["options"]) > 0))
			{
				$a = explode("\n", $fields["options"]);
				$i = 10;
				foreach ($a as $v)
				{
					$v = trim(trim($v), "\r\n");
					if (!$v)
						continue;
					$v1 = $v2 = $v;
					if (substr($v, 0, 1) == "[" && strpos($v, "]") !== false)
					{
						$v1 = substr($v, 1, strpos($v, "]") - 1);
						$v2 = trim(substr($v, strpos($v, "]") + 1));
					}
					$def = "N";
					if($fields['DefaultValue'] == $v2)
						$def = "Y";
					$fieldsTemporary["VALUES"][] = array("XML_ID" => $v1, "VALUE" => $v2, "DEF" => $def, "SORT" => $i);
					$i = $i + 10;
				}
			}
		}
		elseif($fields["type"] == "string")
		{
			$fieldsTemporary["TYPE"] = "S";

			if($fields["row_count"] && $fields["col_count"])
			{
				$fieldsTemporary["ROW_COUNT"] = $fields["row_count"];
				$fieldsTemporary["COL_COUNT"] = $fields["col_count"];
			}
			else
			{
				$fieldsTemporary["ROW_COUNT"] = 1;
				$fieldsTemporary["COL_COUNT"] = 30;
			}
		}
		elseif($fields["type"] == "text")
		{
			$fieldsTemporary["TYPE"] = "S";
			if($fields["row_count"] && $fields["col_count"])
			{
				$fieldsTemporary["ROW_COUNT"] = $fields["row_count"];
				$fieldsTemporary["COL_COUNT"] = $fields["col_count"];
			}
			else
			{
				$fieldsTemporary["ROW_COUNT"] = 4;
				$fieldsTemporary["COL_COUNT"] = 30;
			}
		}
		elseif($fields["type"] == "int" || $fields["type"] == "double")
		{
			$fieldsTemporary["TYPE"] = "N";
		}
		elseif($fields["type"] == "bool")
		{
			$fieldsTemporary["TYPE"] = "L";
			$fieldsTemporary["VALUES"][] = array(
				"XML_ID" => 'Y',
				"VALUE" => GetMessage("BPVDX_YES"),
				"DEF" => "N",
				"SORT" => 10
			);
			$fieldsTemporary["VALUES"][] = array(
				"XML_ID" => 'N',
				"VALUE" => GetMessage("BPVDX_NO"),
				"DEF" => "N",
				"SORT" => 20
			);
		}
		else
		{
			$fieldsTemporary["TYPE"] = $fields["type"];
			$fieldsTemporary["USER_TYPE"] = false;
		}

		$idField = false;
		$properties = \CIBlockProperty::getList(
			array(),
			array("IBLOCK_ID" => $fieldsTemporary["IBLOCK_ID"], "CODE" => $fieldsTemporary["CODE"])
		);
		if(!$properties->fetch())
		{
			$listObject = new \CList($iblockId);
			$idField = $listObject->addField($fieldsTemporary);
		}

		if($idField)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->clearByTag("lists_list_".$iblockId);
			if(!empty($fieldsTemporary["CODE"]))
			{
				$idField = substr($idField, 0, strlen("PROPERTY_")).$fieldsTemporary["CODE"];
			}
			return $idField;
		}
		return false;
	}

	public static function isFeatureEnabled($documentType, $feature)
	{
		return in_array($feature, array(\CBPDocumentService::FEATURE_MARK_MODIFIED_FIELDS));
	}
}