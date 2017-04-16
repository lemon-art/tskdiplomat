<?

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ListsElementPreviewComponent extends \CBitrixComponent
{
	protected function prepareData()
	{
		$elementIblock = CIBlock::GetArrayByID((int)$this->arParams["listId"]);
		$this->arResult['ENTITY_NAME'] = $elementIblock['ELEMENT_NAME'];
		$this->arResult["FIELDS"] = $this->getElementFields($this->arParams['listId'], $this->arParams['elementId']);
		foreach($this->arResult['FIELDS'] as $fieldId => &$field)
		{
			if($field['TYPE'] == 'NAME')
			{
				$this->arResult['ENTITY_TITLE'] = $field['VALUE'];
			}

			$field['HTML'] = $this->renderField($field);

			if($field['SETTINGS']['SHOW_FIELD_PREVIEW'] !== 'Y')
			{
				unset($this->arResult['FIELDS'][$fieldId]);
				continue;
			}
		}
	}

	/**
	 * @param array $field
	 * @return string|false
	 */
	protected function renderField(array $field)
	{
		$result = false;

		if( isset($field['PROPERTY_USER_TYPE']['USER_TYPE'])
			&&method_exists($this, 'renderFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE']))
		{
			$renderMethodName = 'renderFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE'];
			$result = $this->$renderMethodName($field);
		}
		else if(isset($field['PROPERTY_USER_TYPE']['GetPublicViewHTML']))
		{
			if($field['MULTIPLE'] === 'Y' && is_array($field['VALUE']))
			{
				$results = array();
				foreach($field['VALUE'] as $value)
				{
					$fieldParam = array('VALUE' => $value);
					$results[] = call_user_func_array($field["PROPERTY_USER_TYPE"]['GetPublicViewHTML'], array($field, $fieldParam, array()));
				}
				$result = implode('<br>', $results);
			}
			else
			{
				$result = call_user_func_array($field["PROPERTY_USER_TYPE"]['GetPublicViewHTML'], array($field, $field, array()));
			}
		}
		else if($field['PROPERTY_TYPE'] != '')
		{
			$renderMethodName = 'renderFieldByType'.$field['PROPERTY_TYPE'];
			if(method_exists($this, $renderMethodName))
			{
				$result = $this->$renderMethodName($field);
			}
		}
		else if($field['TYPE'] != '')
		{
			$renderMethodName = 'renderFieldByField'.str_replace('_', '', $field['TYPE']);
			if(method_exists($this, $renderMethodName))
			{
				$result = $this->$renderMethodName($field);
			}
			else
			{
				$result = $this->renderDefaultField($field);
			}
		}

		return $result;
	}

	protected function renderFieldByTypeS(array $field)
	{
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = nl2br(htmlspecialcharsbx($value));
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = nl2br(htmlspecialcharsbx($field['VALUE']));
		}
		return $result;
	}

	protected function renderFieldByTypeN(array $field)
	{
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = (float)$value;
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = (float)$field['VALUE'];
		}
		return $result;
	}

	protected function renderFieldByTypeL(array $field)
	{
		$items = array("0" => GetMessage("CT_BLEE_NO_VALUE"));
		$listElements = \CIBlockProperty::GetPropertyEnum($field["ID"]);
		while($listElement = $listElements->Fetch())
			$items[$listElement["ID"]] = htmlspecialcharsbx($listElement["VALUE"]);
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = $items[$value];
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = $items[$field['VALUE']];
		}
		return $result;
	}

	protected function renderFieldByTypeF(array $field)
	{
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$file = new CListFile(0, 0, 0, 0, $value);
				$fileControl = new CListFileControl($file, null);
				$results[] = $fileControl->GetHTML(array('download_text' => GetMessage("CT_BLEE_DOWNLOAD"), 'show_input' => false));
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$file = new CListFile(0, 0, 0, 0, $field["VALUE"]);
			$fileControl = new CListFileControl($file, null);
			$result = $fileControl->GetHTML(array('download_text' => GetMessage("CT_BLEE_DOWNLOAD"), 'show_input' => false));
		}

		return $result;
	}

	protected function renderFieldByTypeG(array $field)
	{
		$items = array();
		$sections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$field["LINK_IBLOCK_ID"]));
		while($section = $sections->GetNext())
			$items[$section["ID"]] = htmlspecialcharsbx(str_repeat(" . ", $section["DEPTH_LEVEL"]).$section["~NAME"]);

		$result = '';

		if(
			$field['MULTIPLE'] == 'Y'
			&& is_array($field['VALUE'])
		)
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = $items[$value];
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = $items[$field['VALUE']];
		}
		return $result;
	}

	protected function renderFieldByTypeE(array $field)
	{
		if($field["IS_REQUIRED"]=="Y")
			$items = array();
		else
			$items = array("" => GetMessage("CT_BLEE_NO_VALUE"));

		$elements = CIBlockElement::GetList(array("NAME"=>"ASC"), array("IBLOCK_ID"=>$field["LINK_IBLOCK_ID"]), false, false, array("ID", "NAME"));
		while($element = $elements->Fetch())
			$items[$element["ID"]] = htmlspecialcharsbx($element["NAME"]);

		$result = '';

		if(
			$field['MULTIPLE'] == 'Y'
			&& is_array($field['VALUE'])
		)
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = $items[$value];
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = $items[$field['VALUE']];
		}

		return $result;
	}

	protected function renderFieldByUserTypeElist(array $field)
	{
		$result = array();
		$field['LINK_IBLOCK_ID'] = intval($field['LINK_IBLOCK_ID']);
		$urlTemplate = CList::getUrlByIblockId($field['LINK_IBLOCK_ID']);
		$filter = array();
		$filter["ACTIVE"] = "Y";
		$filter["ACTIVE_DATE"] = "Y";
		$filter["CHECK_PERMISSIONS"] = "Y";
		if ($field['LINK_IBLOCK_ID'] > 0)
			$filter['IBLOCK_ID'] = $field['LINK_IBLOCK_ID'];

		if(!is_array($field['VALUE']) || count($field['VALUE']) === 0)
			return Loc::getMessage('CT_BLEE_NO_VALUE');

		foreach($field['VALUE'] as $value)
			$filter['ID'][] = $value;

		$rsElements = CIBlockElement::GetList(array(), $filter, false, false, array("*"));
		while($element = $rsElements->GetNext(true,false))
		{
			$elementUrl = str_replace(
				array('#section_id#', '#element_id#'),
				array('0', $element['ID']),
				$urlTemplate
			);
			$result[] = '<a href="'.htmlspecialcharsbx($elementUrl).'">'.htmlspecialcharsbx($element["NAME"]).'</a>';
		}
		return implode('<br>', $result);
	}

	protected function renderFieldByUserTypeDiskFile(array $field)
	{
		$fieldValue = array('VALUE' => explode(',', $field['VALUE'][0]));
		return call_user_func_array($field["PROPERTY_USER_TYPE"]['GetPublicViewHTML'], array($field, $fieldValue, array()));
	}

	protected function renderFieldByFieldPreviewPicture(array $field)
	{
		return $this->renderFieldByTypeF($field);
	}

	protected function renderFieldByFieldDetailPicture(array $field)
	{
		return $this->renderFieldByTypeF($field);
	}

	protected function renderFieldByFieldActiveFrom(array $field)
	{
		return $this->renderDateField($field);
	}

	protected function renderFieldByFieldActiveTo(array $field)
	{
		return $this->renderDateField($field);
	}

	protected function renderFieldByFieldDateCreate(array $field)
	{
		return $this->renderDateField($field);
	}

	protected function renderFieldByFieldTimestampX(array $field)
	{
		return $this->renderDateField($field);
	}

	protected function renderFieldByFieldCreatedBy(array $field)
	{
		$user = new CUser();
		$userId = (int)$field['VALUE'];
		$userDetails = $user->GetByID($userId)->fetch();
		$result = null;

		if(is_array($userDetails))
		{
			$siteNameFormat = CSite::GetNameFormat(false);
			$formattedUsersName = CUser::FormatName($siteNameFormat, $userDetails, true, true);

			$result = htmlspecialcharsbx($formattedUsersName);
		}

		return $result;
	}

	protected function renderFieldByFieldModifiedBy(array $field)
	{
		return $this->renderFieldByFieldCreatedBy($field);
	}

	protected function renderFieldByFieldDetailText(array $field)
	{
		return nl2br(htmlspecialcharsbx($field['VALUE']));
	}

	protected function renderFieldByFieldPreviewText(array $field)
	{
		return nl2br(htmlspecialcharsbx($field['VALUE']));
	}

	protected function renderDateField(array $field)
	{
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = FormatDateFromDB($value, 'SHORT');
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = FormatDateFromDB($field['VALUE'], 'SHORT');
		}
		return $result;
	}

	protected function renderDefaultField(array $field)
	{
		if($field['MULTIPLE'] == 'Y')
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = htmlspecialcharsbx($value);
			}
			$result = implode('<br>', $results);
		}
		else
		{
			$result = htmlspecialcharsbx($field['VALUE']);
		}
		return $result;
	}

	protected function getElementFields($iblockId, $elementId)
	{
		$totalResult = array();
		$list = new CList($iblockId);
		$listFields = $list->getFields();

		foreach ($listFields as $fieldId => $field)
		{
			$totalResult[$fieldId] = $field;
		}

		$elementQuery = CIBlockElement::getList(
			array(),
			array("IBLOCK_ID" => $iblockId, "=ID" => $elementId),
			false,
			false,
			array('*')
		);
		if(is_a($elementQuery, 'CIBlockResult'))
		{
			if ($elementObject = $elementQuery->getNextElement())
			{
				$elementNewData = $elementObject->getFields();
				if(is_array($elementNewData))
				{
					foreach($elementNewData as $fieldId => $fieldValue)
					{
						if(!$list->is_field($fieldId))
							continue;

						if(isset($totalResult[$fieldId]["NAME"]))
						{
							$totalResult[$fieldId]["VALUE"] = $fieldValue;
						}
					}
				}
			}
		}

		if ($elementObject)
		{
			$query = \CIblockElement::getPropertyValues($iblockId, array('ID' => $elementId));
			if($propertyValues = $query->fetch())
			{
				foreach($propertyValues as $id => $values)
				{
					if($id == "IBLOCK_ELEMENT_ID")
						continue;
					$fieldId = "PROPERTY_".$id;
					$totalResult[$fieldId]["VALUE"] = $values;
				}
			}
		}
		else
		{
			$totalResult = array();
		}

		return $totalResult;
	}

	public function executeComponent()
	{
		$this->prepareData();
		if(is_array($this->arResult['FIELDS']) && count($this->arResult['FIELDS']) > 0)
		{
			$this->includeComponentTemplate();
		}
	}
}