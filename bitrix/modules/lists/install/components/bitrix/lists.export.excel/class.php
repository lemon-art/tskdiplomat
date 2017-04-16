<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListExportExcelComponent extends CBitrixComponent
{
	protected $listsPerm;
	protected $arIBlock = array();

	/* Processing of input parameter */
	public function onPrepareComponentParams($params)
	{
		$this->arIBlock = CIBlock::GetArrayByID($params["IBLOCK_ID"]);
		$this->arResult["IBLOCK"] = htmlspecialcharsex($this->arIBlock);
		$this->arResult["IBLOCK_ID"] = $this->arIBlock["ID"];
		$this->arResult["GRID_ID"] = "lists_list_elements_".$this->arResult["IBLOCK_ID"];
		$this->arResult["ANY_SECTION"] = isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0;
		$this->arResult["SECTIONS"] = array();
		$this->arResult["SECTION_ID"] = false;
		$this->arResult["LIST_SECTIONS"] = array();
		if (isset($_GET["list_section_id"]))
			$sectionId = intval($_GET["list_section_id"]);
		else
			$sectionId = intval($params["SECTION_ID"]);

		$rsSections = CIBlockSection::GetList(
			array("left_margin" => "asc"),
			array(
				"IBLOCK_ID"         => $this->arIBlock["ID"],
				"GLOBAL_ACTIVE"     => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);
		while ($arSection = $rsSections->GetNext())
		{
			$this->arResult["SECTIONS"][$arSection["ID"]] = array(
				"ID"   => $arSection["ID"],
				"NAME" => $arSection["NAME"]
			);
			if ($arSection["ID"] == $sectionId)
			{
				$this->arResult["SECTION"] = $arSection;
				$this->arResult["SECTION_ID"] = $arSection["ID"];
			}
			$this->arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
		}

		$this->arResult["IS_SOCNET_GROUP_CLOSED"] = false;
		if (
			intval($params["~SOCNET_GROUP_ID"]) > 0
			&& CModule::IncludeModule("socialnetwork")
		)
		{
			$arSonetGroup = CSocNetGroup::GetByID(intval($params["~SOCNET_GROUP_ID"]));
			if (
				is_array($arSonetGroup)
				&& $arSonetGroup["CLOSED"] == "Y"
				&& !CSocNetUser::IsCurrentUserModuleAdmin()
				&& (
					$arSonetGroup["OWNER_ID"] != $GLOBALS["USER"]->GetID()
					|| COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y"
				)
			)
			{
				$this->arResult["IS_SOCNET_GROUP_CLOSED"] = true;
			}
		}

		return $params;
	}

	/* Start Component */
	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$this->setFrameMode(false);

		if (!CModule::IncludeModule('lists'))
		{
			ShowError(Loc::getMessage("CC_BLL_MODULE_NOT_INSTALLED"));

			return;
		}

		$this->arResult["BIZPROC"] = (bool)CModule::includeModule("bizproc");
		$this->arResult["DISK"] = (bool)CModule::includeModule("disk");

		$this->listsPerm = CListPermissions::CheckAccess(
			$USER,
			$this->arParams["~IBLOCK_TYPE_ID"],
			$this->arResult["IBLOCK_ID"],
			$this->arParams["~SOCNET_GROUP_ID"]
		);
		if($this->listsPerm < 0)
		{
			switch($this->listsPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					ShowError(GetMessage("CC_BLL_WRONG_IBLOCK_TYPE"));
					return;
				case CListPermissions::WRONG_IBLOCK:
					ShowError(GetMessage("CC_BLL_WRONG_IBLOCK"));
					return;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					ShowError(GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED"));
					return;
				default:
					ShowError(GetMessage("CC_BLL_UNKNOWN_ERROR"));
					return;
			}
		}
		elseif(
			$this->listsPerm < CListPermissions::CAN_READ
			&& !(
				CIBlockRights::UserHasRightTo($this->arResult["IBLOCK_ID"], $this->arResult["IBLOCK_ID"], "element_read")
				|| CIBlockSectionRights::UserHasRightTo($this->arResult["IBLOCK_ID"], $this->arResult["SECTION_ID"], "section_element_bind")
			)
		)
		{
			ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
			return;
		}

		if(!(
			!$this->arResult["IS_SOCNET_GROUP_CLOSED"]
			&& (
				$this->listsPerm > CListPermissions::CAN_READ
				|| CIBlockSectionRights::UserHasRightTo($this->arResult["IBLOCK_ID"], $this->arResult["SECTION_ID"], "element_read")
				|| CIBlockSectionRights::UserHasRightTo($this->arResult["IBLOCK_ID"], $this->arResult["SECTION_ID"], "section_element_bind")
			)
		))
		{
			ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
			return;
		}

		$this->createDataExcel();

		$APPLICATION->RestartBuffer();
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: filename=list_".$this->arIBlock["ID"].".xls");
		$this->IncludeComponentTemplate();
		$r = $APPLICATION->EndBufferContentMan();
		echo $r;
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
		die();
	}

	/* Create an dataArray to excel */
	protected function createDataExcel()
	{
		$obList = new CList($this->arIBlock["ID"]);
		$gridOptions = new CGridOptions($this->arResult["GRID_ID"]);
		$gridColumns = $gridOptions->GetVisibleColumns();
		$gridSort = $gridOptions->GetSorting(array("sort" => array("name" => "asc")));

		$this->arResult["ELEMENTS_HEADERS"] = array();
		$arSelect = array("ID", "IBLOCK_ID");
		$arProperties = array();

		$this->arResult["FIELDS"] = $arListFields = $obList->GetFields();
		foreach ($arListFields as $fieldId => $arField)
		{
			if (!count($gridColumns) || in_array($fieldId, $gridColumns))
			{
				if (substr($fieldId, 0, 9) == "PROPERTY_")
					$arProperties[] = $fieldId;
				else
					$arSelect[] = $fieldId;
			}

			if ($fieldId == "CREATED_BY")
				$arSelect[] = "CREATED_USER_NAME";

			if ($fieldId == "MODIFIED_BY")
				$arSelect[] = "USER_NAME";

			$this->arResult["ELEMENTS_HEADERS"][$fieldId] = $arField["NAME"];
		}

		if (!count($gridColumns) || in_array("IBLOCK_SECTION_ID", $gridColumns))
		{
			$arSelect[] = "IBLOCK_SECTION_ID";
		}
		$this->arResult["ELEMENTS_HEADERS"]["IBLOCK_SECTION_ID"] = Loc::getMessage("CC_BLL_COLUMN_SECTION");

		/* FILTER */
		$sections = array();
		foreach ($this->arResult["LIST_SECTIONS"] as $id => $name)
			$sections[$id] = $name;

		$this->arResult["FILTER"] = array(
			array(
				"id" => "list_section_id",
				"type" => "list",
				"items" => $sections,
				"filtered" => $this->arResult["SECTION_ID"] !== false,
				"filter_value" => $this->arResult["SECTION_ID"],
				"value" => $this->arResult["SECTION_ID"],
			),
		);

		$i = 1;
		$arFilterable = array();
		$arCustomFilter = array();
		$arDateFilter = array();
		foreach ($arListFields as $fieldId => $arField)
		{
			if (
				$arField["TYPE"] == "ACTIVE_FROM"
				|| $arField["TYPE"] == "ACTIVE_TO"
			)
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => "DATE_".$fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "date",
				);
				$arFilterable["DATE_".$fieldId] = "";
				$arDateFilter["DATE_".$fieldId] = true;
			}
			elseif (
				$arField["TYPE"] == "DATE_CREATE"
				|| $arField["TYPE"] == "TIMESTAMP_X"
			)
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "date",
				);
				$arFilterable[$fieldId] = "";
				$arDateFilter[$fieldId] = true;
			}
			elseif (is_array($arField["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicFilterHTML", $arField["PROPERTY_USER_TYPE"]))
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "custom",
					"fieldsType" => $arField["TYPE"],
					"enable_settings" => false,
					"value" => call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"], array(
						$arField,
						array(
							"VALUE" => $fieldId,
							"FORM_NAME" => "filter_".$this->arResult["GRID_ID"],
							"GRID_ID" => $this->arResult["GRID_ID"],
						),
					)),
				);
				$arFilterable[$fieldId] = "";
				if(array_key_exists("AddFilterFields", $arField["PROPERTY_USER_TYPE"]))
					$arCustomFilter[$fieldId] = array(
						"callback" => $arField["PROPERTY_USER_TYPE"]["AddFilterFields"],
						"filter" => &$this->arResult["FILTER"][$i],
					);
			}
			elseif ($arField["TYPE"] == "SORT" || $arField["TYPE"] == "N")
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "number",
				);
				$arFilterable[$fieldId] = "";
			}
			elseif ($arField["TYPE"] == "G")
			{
				$items = array();
				$prop_secs = CIBlockSection::GetList(array("left_margin" => "asc"), array("IBLOCK_ID" => $arField["LINK_IBLOCK_ID"]));
				while ($ar_sec = $prop_secs->Fetch())
					$items[$ar_sec["ID"]] = str_repeat(". ", $ar_sec["DEPTH_LEVEL"] - 1).$ar_sec["NAME"];

				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "list",
					"items" => $items,
					"params" => array("size" => 5, "multiple" => "multiple"),
					"valign" => "top",
				);
				$arFilterable[$fieldId] = "";
			}
			elseif ($arField["TYPE"] == "E")
			{
				//Should be handled in template
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "E",
					"value" => $arField,
				);
				$arFilterable[$fieldId] = "";
			}
			elseif ($arField["TYPE"] == "L")
			{
				$items = array();
				$propEnums = CIBlockProperty::GetPropertyEnum($arField["ID"]);
				while ($arEnum = $propEnums->Fetch())
					$items[$arEnum["ID"]] = $arEnum["VALUE"];

				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"type" => "list",
					"items" => $items,
					"params" => array("size" => 5, "multiple" => "multiple"),
					"valign" => "top",
				);
				$arFilterable[$fieldId] = "";
			}
			elseif (in_array($arField["TYPE"], array("S", "S:HTML", "NAME", "DETAIL_TEXT", "PREVIEW_TEXT")))
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
				);
				$arFilterable[$fieldId] = "?";
			}
			else
			{
				$this->arResult["FILTER"][$i] = array(
					"id" => $fieldId,
					"name" => htmlspecialcharsex($arField["NAME"]),
					"fieldsType" => $arField["TYPE"]
				);
				$arFilterable[$fieldId] = "";
			}

			$i++;
		}

		$arFilter = array();
		$gridFilter = $gridOptions->GetFilter($this->arResult["FILTER"]);
		foreach ($gridFilter as $key => $value)
		{
			if (substr($key, -5) == "_from")
			{
				$op = ">=";
				$newKey = substr($key, 0, -5);
			}
			elseif (substr($key, -3) == "_to")
			{
				$op = "<=";
				$newKey = substr($key, 0, -3);
				if (array_key_exists($newKey, $arDateFilter))
				{
					if (!preg_match("/\\d\\d:\\d\\d:\\d\\d\$/", $value))
						$value .= " 23:59:59";
				}
			}
			else
			{
				$op = "";
				$newKey = $key;
			}
			if (array_key_exists($newKey, $arFilterable))
			{
				if ($op == "")
					$op = $arFilterable[$newKey];
				$arFilter[$op.$newKey] = $value;
			}
		}

		foreach($arCustomFilter as $fieldId => $arCallback)
		{
			$filtered = false;
			call_user_func_array($arCallback["callback"], array(
				$arListFields[$fieldId],
				array(
					"VALUE" => $fieldId,
					"GRID_ID" => $this->arResult["GRID_ID"],
				),
				&$arFilter,
				&$filtered,
			));
		}

		$arFilter["IBLOCK_ID"] = $this->arIBlock["ID"];
		$arFilter["CHECK_PERMISSIONS"] = ($this->listsPerm >= CListPermissions::CAN_READ ? "N": "Y");
		if (!$this->arResult["ANY_SECTION"])
			$arFilter["SECTION_ID"] = $this->arResult["SECTION_ID"];

		$rsElements = CIBlockElement::GetList(
			$gridSort["sort"], $arFilter, false, false, $arSelect
		);

		$this->arResult["EXCEL_COLUMN_NAME"] = array();
		$this->arResult["EXCEL_CELL_VALUE"] = array();
		$count = 0;

		$comments = false;
		if(in_array("COMMENTS", $gridColumns) && CModule::includeModule("forum"))
		{
			$comments = true;
		}

		while ($obElement = $rsElements->GetNextElement())
		{
			$data = $obElement->GetFields();
			$propertyArray = $obElement->GetProperties();
			if (!empty($arProperties))
			{
				foreach ($propertyArray as $arProp)
				{
					$fieldId = "PROPERTY_".$arProp["ID"];
					if (in_array($fieldId, $arProperties))
					{
						$arField = $this->arResult["FIELDS"][$fieldId];

						if (is_array($arField["PROPERTY_USER_TYPE"]) && is_array($arField["PROPERTY_USER_TYPE"]["GetPublicViewHTML"]))
						{
							if($arProp["USER_TYPE"] == "map_yandex")
							{
								$data[$fieldId] = !empty($arProp["VALUE"]) ? $arProp["VALUE"] : '';
								continue;
							}
							elseif($arProp["USER_TYPE"] == "DiskFile")
							{
								if(!empty($arProp["VALUE"]) && $this->arResult["DISK"])
								{
									$listValue = current($arProp["VALUE"]);
									if(!is_array($listValue))
										$listValue = $arProp["VALUE"];

									$number = 0;
									$countFiles = count($listValue);
									foreach($listValue as $idAttached)
									{
										$number++;
										list($type, $realId) = Bitrix\Disk\Uf\FileUserType::detectType($idAttached);
										if($type == Bitrix\Disk\Uf\FileUserType::TYPE_ALREADY_ATTACHED)
										{
											$attachedModel = Bitrix\Disk\AttachedObject::loadById($realId);
											if(!$attachedModel)
											{
												continue;
											}
											$fileModel = Bitrix\Disk\File::loadById($attachedModel->getObjectId(), array('STORAGE'));
											if(!$fileModel)
											{
												continue;
											}
											$data[$fieldId] .= $fileModel->getName();
											$data[$fieldId] .= ($countFiles != $number) ? ', ' : '';
										}
									}
								}
								continue;
							}

							if(is_array($arProp["~VALUE"]))
							{
								foreach($arProp["~VALUE"] as $propValue)
								{
									$data[$fieldId][] = call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicViewHTML"], array(
										$arField,
										array("VALUE" => $propValue),
										array(),
									));
								}
							}
							else
							{
								$data[$fieldId] = call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicViewHTML"], array(
									$arField,
									array("VALUE" => $arProp["~VALUE"]),
									array(),
								));
							}
						}
						elseif ($arField["PROPERTY_TYPE"] == "E")
						{
							if(empty($arProp['VALUE']))
							{
								continue;
							}

							if(!is_array($arProp['VALUE']))
							{
								$arProp['VALUE'] = array($arProp['VALUE']);
							}

							$elementQuery = CIBlockElement::getList(
								array(),
								array("=ID" => $arProp['VALUE']),
								false,
								false,
								array("NAME")
							);
							while($element = $elementQuery->fetch())
							{
								$data[$fieldId][] = $element['NAME'];
							}
						}
						elseif ($arField["PROPERTY_TYPE"] == "G")
						{
							if(empty($arProp['VALUE']))
							{
								continue;
							}

							if(!is_array($arProp['VALUE']))
							{
								$arProp['VALUE'] = array($arProp['VALUE']);
							}

							$sectionQuery = CIBlockSection::getList(array(), array("=ID" => $arProp['VALUE']));
							while($section = $sectionQuery->fetch())
							{
								$data[$fieldId][] = $section['NAME'];
							}
						}
						elseif ($arField["PROPERTY_TYPE"] == "L")
						{
							$data[$fieldId] = htmlspecialcharsex($arProp["VALUE_ENUM"]);
						}
						elseif ($arField["PROPERTY_TYPE"] == "F")
						{
							$files = is_array($arProp["VALUE"]) ? $arProp["VALUE"] : array($arProp["VALUE"]);
							$number = 1;
							$countFiles = count($files);
							foreach ($files as $file)
							{
								$value = CFile::MakeFileArray($file);
								$data[$fieldId] .= $value["name"];
								$data[$fieldId] .= ($countFiles != $number) ? ', ' : '';
								$number++;
							}
						}
						else
						{
							$data[$fieldId] = htmlspecialcharsex($arProp["VALUE"]);
						}
					}
				}
				if (!empty($data["IBLOCK_SECTION_ID"]))
				{
					if (array_key_exists($data["IBLOCK_SECTION_ID"], $this->arResult["SECTIONS"]))
					{
						$data["IBLOCK_SECTION_ID"] = $this->arResult["SECTIONS"][$data["IBLOCK_SECTION_ID"]]["NAME"];
					}
				}
				if(in_array("BIZPROC", $gridColumns))
					$data["BIZPROC"] = $this->getArrayBizproc($data);
			}

			if($comments)
			{
				$countComments = $this->getCommentsProcess($data["ID"]);
			}

			if (isset($data["CREATED_BY"]))
				$data["CREATED_BY"] = "[".$data["CREATED_BY"]."] ".$data["CREATED_USER_NAME"];

			if (isset($data["MODIFIED_BY"]))
				$data["MODIFIED_BY"] = "[".$data["MODIFIED_BY"]."] ".$data["USER_NAME"];
			if (isset($data["ACTIVE_FROM"]))
				$data['ACTIVE_FROM'] = FormatDateFromDB($data['ACTIVE_FROM']);
			if (isset($data["ACTIVE_TO"]))
				$data['ACTIVE_TO'] = FormatDateFromDB($data['ACTIVE_TO']);
			if (isset($data["DATE_CREATE"]))
				$data['DATE_CREATE'] = FormatDateFromDB($data['DATE_CREATE']);
			if (isset($data["TIMESTAMP_X"]))
				$data['TIMESTAMP_X'] = FormatDateFromDB($data['TIMESTAMP_X']);

			foreach ($gridColumns as $position => $id)
			{
				if($id == "COMMENTS")
				{
					if($comments)
					{
						$data[$id] = $countComments;
					}
					else
					{
						continue;
					}
				}

				$this->arResult["EXCEL_CELL_VALUE"][$count][$position] = is_array($data[$id]) ? implode('/', $data[$id]) : $data[$id];
				$this->arResult["EXCEL_COLUMN_NAME"][$position] = $this->arResult["ELEMENTS_HEADERS"][$id];
			}
			$count++;
		}
	}

	/* Data business process */
	protected function getArrayBizproc($data = array())
	{
		if(!$this->arResult["BIZPROC"])
		{
			return '';
		}

		$currentUserId = $GLOBALS["USER"]->GetID();

		$html = "";

		if ($this->arResult["IBLOCK"]["BIZPROC"] == "Y")
		{
			$this->arResult["ELEMENTS_HEADERS"]["BIZPROC"] = Loc::getMessage("CC_BLL_COLUMN_BIZPROC");

			$arDocumentStates = CBPDocument::GetDocumentStates(
				BizProcDocument::generateDocumentComplexType($this->arParams["IBLOCK_TYPE_ID"], $this->arResult["IBLOCK_ID"]),
				BizProcDocument::getDocumentComplexId($this->arParams["IBLOCK_TYPE_ID"], $data["ID"])
			);

			$userGroups = $GLOBALS["USER"]->GetUserGroupArray();
			if ($data["~CREATED_BY"] == $currentUserId)
				$userGroups[] = "Author";

			$ii = 0;
			foreach ($arDocumentStates as $workflowId => $workflowState)
			{
				if (strlen($workflowState["TEMPLATE_NAME"]) > 0)
					$html .= "".$workflowState["TEMPLATE_NAME"].":\r\n";
				else
					$html .= "".(++$ii).":\r\n";

				$html .= "".(strlen($workflowState["STATE_TITLE"]) > 0 ? $workflowState["STATE_TITLE"] : $workflowState["STATE_NAME"])."\r\n";
			}
		}

		return $html;
	}

	protected function getCommentsProcess($elementId)
	{
		$countComments = 0;

		$this->arResult["ELEMENTS_HEADERS"]["COMMENTS"] = Loc::getMessage("CC_BLL_COMMENTS");

		if(!$this->arResult["BIZPROC"] || !$elementId)
		{
			return $countComments;
		}

		$documentStates = CBPDocument::GetDocumentStates(
			BizProcDocument::generateDocumentComplexType($this->arParams["IBLOCK_TYPE_ID"], $this->arResult["IBLOCK_ID"]),
			BizProcDocument::getDocumentComplexId($this->arParams["IBLOCK_TYPE_ID"], $elementId)
		);

		if(!empty($documentStates))
		{
			$state = current($documentStates);
		}
		else
		{
			return $countComments;
		}

		$query = CForumTopic::getList(array(), array("@XML_ID" => 'WF_'.$state["ID"]));
		while ($row = $query->fetch())
		{
			$countComments = $row["POSTS"];
		}

		return $countComments;
	}
}