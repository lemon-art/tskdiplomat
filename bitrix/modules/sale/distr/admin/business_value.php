<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$readOnly = $APPLICATION->GetGroupRight('sale') < 'W';

if ($readOnly)
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/include.php');

use	Bitrix\Sale\BusinessValue,
	Bitrix\Sale\Internals\Input,
	Bitrix\Sale\Internals\OrderPropsTable,
	Bitrix\Sale\Internals\BusinessValueTable,
	Bitrix\Sale\Internals\BusinessValueCodeTable,
	Bitrix\Sale\Internals\BusinessValueGroupTable,
	Bitrix\Sale\Internals\BusinessValueParentTable,
	Bitrix\Sale\Internals\BusinessValuePersonDomainTable,
	Bitrix\Sale\Internals\PersonTypeTable,
	Bitrix\Sale\Internals\CompanyTable,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$errors = array();

$domains = array(
	BusinessValue::COMMON_DOMAIN     => Loc::getMessage('BIZVAL_DOMAIN_COMMON'    ),
	BusinessValue::INDIVIDUAL_DOMAIN => Loc::getMessage('BIZVAL_DOMAIN_INDIVIDUAL'),
	BusinessValue::ENTITY_DOMAIN     => Loc::getMessage('BIZVAL_DOMAIN_ENTITY'    ),
);

// load person types

$persons = array(/* ID => NAME (LID) */);

$result = PersonTypeTable::getList(array(
	'select'  => array('ID', 'NAME', 'LID'),
	'order'   => array('LID', 'SORT', 'NAME'),
));

while ($row = $result->fetch())
	$persons[$row['ID']] = htmlspecialcharsbx($row['NAME'].' ('.$row['LID'].')');

$personsWithCommon = array(BusinessValue::COMMON_PERSON_ID => Loc::getMessage('BIZVAL_DOMAIN_COMMON')) + $persons;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 1. PERSON DOMAINS ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// load person domains

$personsDomains = array(/* PERSON_TYPE_ID => DOMAIN */);

$result = BusinessValuePersonDomainTable::getList(array(
	'select' => array('PERSON_TYPE_ID', 'DOMAIN'),
));

while ($row = $result->fetch())
	$personsDomains[$row['PERSON_TYPE_ID']] = $row['DOMAIN'];

// person domain input

$personDomainInput = array('TYPE' => 'ENUM', 'OPTIONS' => array(
	''                               => Loc::getMessage('BIZVAL_DOMAIN_NONE'      ),
	BusinessValue::INDIVIDUAL_DOMAIN => Loc::getMessage('BIZVAL_DOMAIN_INDIVIDUAL'),
	BusinessValue::ENTITY_DOMAIN     => Loc::getMessage('BIZVAL_DOMAIN_ENTITY'    ),
));

// post person domains

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($postedPersonsDomains = $_POST['PERSON_DOMAIN']) && is_array($postedPersonsDomains))
{
	// sanitize & validate persons domains

	foreach ($postedPersonsDomains as $personId => $personDomain)
	{
		if ($persons[$personId])
		{
			if ($error = Input\Manager::getError($personDomainInput, $personDomain))
				$errors['PERSON_DOMAIN'][$personId]['DOMAIN'] = $error;
		}
		else
		{
			unset ($postedPersonsDomains[$personId]);
		}
	}

	// save persons domains

	if (! $errors && ! $readOnly && check_bitrix_sessid() && ($_POST['save'] || $_POST['apply']))
	{
		foreach ($postedPersonsDomains as $personId => $postedPersonDomain)
		{
			$savedPersonDomain = $personsDomains[$personId];

			if ($postedPersonDomain != $savedPersonDomain)
			{
				if ($savedPersonDomain)
				{
					$deletePersonDomainResult = BusinessValuePersonDomainTable::delete(array(
						'PERSON_TYPE_ID' => $personId,
						'DOMAIN'         => $savedPersonDomain,
					));

					if ($deletePersonDomainResult->isSuccess())
					{
						$result = BusinessValueTable::getList(array(
							'select'  => array('ID', 'CODE_ID'),
							'filter'  => array(
								'=PERSON_TYPE_ID' => $personId,
								'=CODE.DOMAIN'    => $savedPersonDomain,
							),
						));

						while ($row = $result->fetch())
							BusinessValueTable::delete($row['ID']); // TODO errors

						unset($personsDomains[$personId]);
					}
					else
					{
						$errors['PERSON_DOMAIN'][$personId]['DELETE'] = $deletePersonDomainResult->getErrorMessages();
					}
				}

				if ($postedPersonDomain)
				{
					$addPersonDomainResult = BusinessValuePersonDomainTable::add(array(
						'PERSON_TYPE_ID' => $personId,
						'DOMAIN'         => $postedPersonDomain,
					));

					if ($addPersonDomainResult->isSuccess())
					{
						$personsDomains[$personId] = $postedPersonDomain;
					}
					else
					{
						$errors['PERSON_DOMAIN'][$personId]['ADD'] = $addPersonDomainResult->getErrorMessages();
					}
				}
			}
		}
	}
}

// post processing

$personsDomainsWithCommon = array(BusinessValue::COMMON_PERSON_ID => BusinessValue::COMMON_DOMAIN) + $personsDomains;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 2. BUSINESS VALUES //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// filter

$filterCodeId = $filterParentId = $filterEntity = $filterItem = null;

if ($_GET['set_filter'])
{
	$filterCodeId   = $_GET['FILTER_CODE_ID'  ];
	$filterParentId = $_GET['FILTER_PARENT_ID'];
	$filterEntity   = $_GET['FILTER_ENTITY'   ];
	$filterItem     = $_GET['FILTER_ITEM'     ];
}

// LOAD PARENTS & TRANSLATIONS /////////////////////////////////////////////////////////////////////////////////////////

$parents = array(/* ID => Loc || NAME */);

$result = BusinessValueParentTable::getList(array(
	'select'  => array('ID', 'NAME', 'LANG_SRC'),
));

while ($row = $result->fetch())
{
	$parentId = $row['ID'];
	$parentName = $row['NAME'];

	if ($message = Loc::getMessage("BIZVAL_PARENT_$parentName"))
		$parentName = $message;

	$parents[$parentId] = $parentName;

	if (($parentLangSrc = $row['LANG_SRC']) && (! $filterParentId || $filterParentId == $parentId))
		Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].$parentLangSrc);
}

if (! $parents[$filterParentId])
	$filterParentId = null;

// LOAD GROUPS /////////////////////////////////////////////////////////////////////////////////////////////////////////

$groups = array(/* ID => NAME */);

$result = BusinessValueGroupTable::getList(array(
	'select'  => array('ID', 'NAME'),
	'order'   => array('SORT'),
//	array('=Bitrix\Sale\Internals\BusinessValueCodeTable:GROUP.Bitrix\Sale\Internals\BusinessValueCodeParentTable:CODE.PARENT_ID' => $filterParentId)
));

while ($row = $result->fetch())
{
	$groupName = $row['NAME'];

	if ($message = Loc::getMessage("BIZVAL_GROUP_$groupName"))
		$groupName = $message;

	$groups[$row['ID']] = $groupName;
}

// LOAD CODES & MAPPINGS ///////////////////////////////////////////////////////////////////////////////////////////////

$codes = array(/* ID => ... */);

$activeCodeDomains = array(/* DOMAIN => true */);

$filter = array('=DOMAIN' => $personsDomainsWithCommon);

if ($filterCodeId)
	$filter['=ID'] = $filterCodeId;

if ($filterParentId)
	$filter['=PARENT_ID'] = $filterParentId;

if ($filterEntity)
{
	$filter['=MAP_ENTITY'] = $filterEntity;
	$filter['=MAP_ITEM'  ] = $filterItem  ;
}

$result = BusinessValueCodeTable::getList(array(
	'select' => array(
		'ID', 'NAME', 'DOMAIN', 'GROUP_ID',
		'PARENT_ID'     => 'Bitrix\Sale\Internals\BusinessValueCodeParentTable:CODE.PARENT_ID',
		'MAP_ID'        => 'Bitrix\Sale\Internals\BusinessValueTable:lCODE.ID',
		'MAP_ENTITY'    => 'Bitrix\Sale\Internals\BusinessValueTable:lCODE.ENTITY',
		'MAP_ITEM'      => 'Bitrix\Sale\Internals\BusinessValueTable:lCODE.ITEM',
		'MAP_PERSON_ID' => 'Bitrix\Sale\Internals\BusinessValueTable:lCODE.PERSON_TYPE_ID',
	),
	'filter' => $filter,
	'order'  => array('SORT'),
));

while ($row = $result->fetch())
{
	$parentId = $row['PARENT_ID'];
	$mapId    = $row['MAP_ID'   ];

	if ($code = &$codes[$row['ID']])
	{
		if (! $codeParent = &$code['PARENTS'][$parentId])
			$codeParent = $parents[$parentId];

		if ($mapId && ! ($codeMap = &$code[$row['MAP_PERSON_ID']]))
		{
			$codeMap = array(
				'ID'     => $mapId,
				'ENTITY' => $row['MAP_ENTITY'],
				'ITEM'   => $row['MAP_ITEM'],
			);
		}
	}
	else
	{
		$codeName   = $row['NAME'];
		$codeDomain = $row['DOMAIN'];
		if ($message = Loc::getMessage("BIZVAL_CODE_$codeName"))
			$codeName = $message;

		$code = array(
			'NAME'     => $codeName,
			'DOMAIN'   => $codeDomain,
			'GROUP_ID' => $row['GROUP_ID'],
			'PARENTS'  => array($parentId => $parents[$parentId]),
		);

		if ($mapId)
		{
			$code[$row['MAP_PERSON_ID']] = array(
				'ID'     => $mapId,
				'ENTITY' => $row['MAP_ENTITY'],
				'ITEM'   => $row['MAP_ITEM'],
			);
		}

		$activeCodeDomains[$codeDomain] = true;
	}
}
unset($code, $codeParent, $codeMap);

// LOAD ORDER PROPERTIES ///////////////////////////////////////////////////////////////////////////////////////////////

$propertyOptions = array(/* [PERSON_TYPE_ID][PROPERTY ID] => NAME */);

$result = OrderPropsTable::getList(array(
	'select' => array('ID', 'NAME', 'PERSON_TYPE_ID'),
	'filter' => array('=PERSON_TYPE_ID' => array_keys($personsDomains)),
	'order'  => array('PERSON_TYPE_ID', 'SORT'),
));

while ($row = $result->fetch())
	$propertyOptions[$row['PERSON_TYPE_ID']][$row['ID']] = $row['NAME'];

$propertyOptionsByName = array(/* [PERSON TYPE NAME][PROPERTY ID] => NAME */);

foreach ($propertyOptions as $personId => $options)
	$propertyOptionsByName[$persons[$personId]] = $options;

// INPUTS //////////////////////////////////////////////////////////////////////////////////////////////////////////////

$codeInputOptions = array(
	''                                          => Loc::getMessage('BIZVAL_PAGE_FILTER_ALL'),
	Loc::getMessage('BIZVAL_DOMAIN_COMMON'    ) => array(),
	Loc::getMessage('BIZVAL_DOMAIN_INDIVIDUAL') => array(),
	Loc::getMessage('BIZVAL_DOMAIN_ENTITY'    ) => array(),
);

$result = BusinessValueCodeTable::getList(array('select' => array('ID', 'NAME', 'DOMAIN')));

while ($row = $result->fetch())
{
	$codeName   = $row['NAME'];
	if ($message = Loc::getMessage("BIZVAL_CODE_$codeName"))
		$codeName = $message;
	$codeInputOptions[$domains[$row['DOMAIN']]][$row['ID']] = $codeName;
}

$codeInput = array('TYPE' => 'ENUM', 'OPTIONS' => $codeInputOptions);

// other

$parentInput = array('TYPE' => 'ENUM', 'OPTIONS' => array('' => Loc::getMessage('BIZVAL_PAGE_FILTER_ALL')) + $parents);

$personEntityInput = array('TYPE' => 'ENUM', 'OPTIONS' => array(
	'VALUE'    => Loc::getMessage('BIZVAL_ENTITY_OTHER'   ),
	'USER'     => Loc::getMessage('BIZVAL_ENTITY_USER'    ),
	'ORDER'    => Loc::getMessage('BIZVAL_ENTITY_ORDER'   ),
	'PROPERTY' => Loc::getMessage('BIZVAL_ENTITY_PROPERTY'),
	'PAYMENT'  => Loc::getMessage('BIZVAL_ENTITY_PAYMENT' ),
	'SHIPMENT' => Loc::getMessage('BIZVAL_ENTITY_SHIPMENT'),
	'COMPANY'  => Loc::getMessage('BIZVAL_ENTITY_COMPANY' ),
));

$commonEntityInput = $personEntityInput;
unset($commonEntityInput['OPTIONS']['PROPERTY']);

$itemInputs = array(
	'' => array('TYPE' => 'STRING', 'DISABLED' => 'Y', 'VALUE' => Loc::getMessage('BIZVAL_PAGE_FILTER_ALL')),
	'VALUE' => array('TYPE' => 'STRING', 'MAXLENGTH' => 255),
	'USER' => array('TYPE' => 'ENUM', 'OPTIONS' => array(
		Loc::getMessage('BIZVAL_GROUP_CLIENT') => array(
			'ID'                  => Loc::getMessage('BIZVAL_CODE_CLIENT_USER_ID'),
			'LOGIN'               => Loc::getMessage('BIZVAL_CODE_CLIENT_USER_LOGIN'),
			'NAME'                => Loc::getMessage('BIZVAL_CODE_CLIENT_FIRST_NAME'),
			'SECOND_NAME'         => Loc::getMessage('BIZVAL_CODE_CLIENT_SECOND_NAME'),
			'LAST_NAME'           => Loc::getMessage('BIZVAL_CODE_CLIENT_LAST_NAME'),
			'EMAIL'               => Loc::getMessage('BIZVAL_CODE_CLIENT_EMAIL'),
			'LID'                 => Loc::getMessage('BIZVAL_CODE_CLIENT_USER_SITE_ID'),
			'PERSONAL_PROFESSION' => Loc::getMessage('BIZVAL_CODE_CLIENT_PROFESSION'),
			'PERSONAL_WWW'        => Loc::getMessage('BIZVAL_CODE_CLIENT_WEBSITE'),
			'PERSONAL_ICQ'        => Loc::getMessage('BIZVAL_CODE_CLIENT_ICQ'),
			'PERSONAL_GENDER'     => Loc::getMessage('BIZVAL_CODE_CLIENT_SEX'),
			'PERSONAL_FAX'        => Loc::getMessage('BIZVAL_CODE_CLIENT_FAX'),
			'PERSONAL_MOBILE'     => Loc::getMessage('BIZVAL_CODE_CLIENT_PHONE'),
			'PERSONAL_STREET'     => Loc::getMessage('BIZVAL_CODE_CLIENT_ADDRESS'),
			'PERSONAL_MAILBOX'    => Loc::getMessage('BIZVAL_CODE_CLIENT_POSTAL_ADDRESS'),
			'PERSONAL_CITY'       => Loc::getMessage('BIZVAL_CODE_CLIENT_CITY'),
			'PERSONAL_STATE'      => Loc::getMessage('BIZVAL_CODE_CLIENT_REGION'),
			'PERSONAL_ZIP'        => Loc::getMessage('BIZVAL_CODE_CLIENT_ZIP'),
			'PERSONAL_COUNTRY'    => Loc::getMessage('BIZVAL_CODE_CLIENT_COUNTRY'),
		),
		Loc::getMessage('BIZVAL_GROUP_CLIENT_COMPANY') => array(
			'WORK_COMPANY'    => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_NAME'),
			'WORK_DEPARTMENT' => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_DEPARTMENT'),
			'WORK_POSITION'   => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_POSITION'),
			'WORK_WWW'        => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_WEBSITE'),
			'WORK_PHONE'      => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_PHONE'),
			'WORK_FAX'        => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_FAX'),
			'WORK_STREET'     => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_ADDRESS'),
			'WORK_MAILBOX'    => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_POSTAL_ADDRESS'),
			'WORK_CITY'       => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_CITY'),
			'WORK_STATE'      => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_REGION'),
			'WORK_ZIP'        => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_ZIP'),
			'WORK_COUNTRY'    => Loc::getMessage('BIZVAL_CODE_CLIENT_COMPANY_COUNTRY'),
		),
	)),
	'ORDER' => array('TYPE' => 'ENUM', 'OPTIONS' => array(
		'ID'               => Loc::getMessage('BIZVAL_CODE_ORDER_ID'),
		'ACCOUNT_NUMBER'   => Loc::getMessage('BIZVAL_CODE_ORDER_NUMBER'),
		'DATE_INSERT'      => Loc::getMessage('BIZVAL_CODE_ORDER_DATETIME'),
		'DATE_INSERT_DATE' => Loc::getMessage('BIZVAL_CODE_ORDER_DATE'),
		'DATE_PAY_BEFORE'  => Loc::getMessage('BIZVAL_CODE_ORDER_PAY_BEFORE'),
		'SHOULD_PAY'       => Loc::getMessage('BIZVAL_CODE_ORDER_PRICE'),
		'CURRENCY'         => Loc::getMessage('BIZVAL_CODE_ORDER_CURRENCY'),
		'PRICE'            => Loc::getMessage('BIZVAL_CODE_ORDER_SUM'),
		'LID'              => Loc::getMessage('BIZVAL_CODE_ORDER_SITE_ID'),
		'PRICE_DELIVERY'   => Loc::getMessage('BIZVAL_CODE_ORDER_PRICE_DELIV'),
		'DISCOUNT_VALUE'   => Loc::getMessage('BIZVAL_CODE_ORDER_DESCOUNT'),
		'USER_ID'          => Loc::getMessage('BIZVAL_CODE_ORDER_USER_ID'),
		'PAY_SYSTEM_ID'    => Loc::getMessage('BIZVAL_CODE_ORDER_PAY_SYSTEM_ID'),
		'DELIVERY_ID'      => Loc::getMessage('BIZVAL_CODE_ORDER_DELIVERY_ID'),
		'TAX_VALUE'        => Loc::getMessage('BIZVAL_CODE_ORDER_TAX'),
	)),
	'PROPERTY' => array('TYPE' => 'ENUM'),
	'PAYMENT' => array('TYPE' => 'ENUM', 'OPTIONS' => array(
		'ID'                    => Loc::getMessage('BIZVAL_CODE_PAYMENT_ID'),
		'PAID'                  => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAID'),
		'DATE_PAID'             => Loc::getMessage('BIZVAL_CODE_PAYMENT_DATE_PAID'),
		'EMP_PAID_ID'           => Loc::getMessage('BIZVAL_CODE_PAYMENT_EMP_PAID_ID'),
		'PAY_SYSTEM_ID'         => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_SYSTEM_ID'),
		'PS_STATUS'             => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_STATUS'),
		'PS_STATUS_CODE'        => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_STATUS_CODE'),
		'PS_STATUS_DESCRIPTION' => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_STATUS_DESCRIPTION'),
		'PS_STATUS_MESSAGE'     => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_STATUS_MESSAGE'),
		'PS_SUM'                => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_SUM'),
		'PS_CURRENCY'           => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_CURRENCY'),
		'PS_RESPONSE_DATE'      => Loc::getMessage('BIZVAL_CODE_PAYMENT_PS_RESPONSE_DATE'),
		'PAY_VOUCHER_NUM'       => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_VOUCHER_NUM'),
		'PAY_VOUCHER_DATE'      => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_VOUCHER_DATE'),
		'DATE_PAY_BEFORE'       => Loc::getMessage('BIZVAL_CODE_PAYMENT_DATE_PAY_BEFORE'),
		'DATE_BILL'             => Loc::getMessage('BIZVAL_CODE_PAYMENT_DATE_BILL'),
		'XML_ID'                => Loc::getMessage('BIZVAL_CODE_PAYMENT_XML_ID'),
		'SUM'                   => Loc::getMessage('BIZVAL_CODE_PAYMENT_SUM'),
		'CURRENCY'              => Loc::getMessage('BIZVAL_CODE_PAYMENT_CURRENCY'),
		'PAY_SYSTEM_NAME'       => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_SYSTEM_NAME'),
		'COMPANY_ID'            => Loc::getMessage('BIZVAL_CODE_PAYMENT_COMPANY_ID'),
		'PAY_RETURN_NUM'        => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_RETURN_NUM'),
		'PAY_RETURN_DATE'       => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_RETURN_DATE'),
		'PAY_RETURN_COMMENT'    => Loc::getMessage('BIZVAL_CODE_PAYMENT_PAY_RETURN_COMMENT'),
		'RESPONSIBLE_ID'        => Loc::getMessage('BIZVAL_CODE_PAYMENT_RESPONSIBLE_ID'),
		'EMP_RESPONSIBLE_ID'    => Loc::getMessage('BIZVAL_CODE_PAYMENT_EMP_RESPONSIBLE_ID'),
		'DATE_RESPONSIBLE_ID'   => Loc::getMessage('BIZVAL_CODE_PAYMENT_DATE_RESPONSIBLE_ID'),
		'COMPANY_BY'            => Loc::getMessage('BIZVAL_CODE_PAYMENT_COMPANY_BY'),
	)),
	'SHIPMENT' => array('TYPE' => 'ENUM', 'OPTIONS' => array(
		'STATUS_ID'             => Loc::getMessage('BIZVAL_CODE_SHIPMENT_STATUS_ID'),
		'PRICE_DELIVERY'        => Loc::getMessage('BIZVAL_CODE_SHIPMENT_PRICE_DELIVERY'),
		'ALLOW_DELIVERY'        => Loc::getMessage('BIZVAL_CODE_SHIPMENT_ALLOW_DELIVERY'),
		'DATE_ALLOW_DELIVERY'   => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DATE_ALLOW_DELIVERY'),
		'EMP_ALLOW_DELIVERY_ID' => Loc::getMessage('BIZVAL_CODE_SHIPMENT_EMP_ALLOW_DELIVERY_ID'),
		'DEDUCTED'              => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DEDUCTED'),
		'DATE_DEDUCTED'         => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DATE_DEDUCTED'),
		'EMP_DEDUCTED_ID'       => Loc::getMessage('BIZVAL_CODE_SHIPMENT_EMP_DEDUCTED_ID'),
		'REASON_UNDO_DEDUCTED'  => Loc::getMessage('BIZVAL_CODE_SHIPMENT_REASON_UNDO_DEDUCTED'),
		'DELIVERY_ID'           => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DELIVERY_ID'),
		'DELIVERY_DOC_NUM'      => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DELIVERY_DOC_NUM'),
		'DELIVERY_DOC_DATE'     => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DELIVERY_DOC_DATE'),
		'TRACKING_NUMBER'       => Loc::getMessage('BIZVAL_CODE_SHIPMENT_TRACKING_NUMBER'),
		'XML_ID'                => Loc::getMessage('BIZVAL_CODE_SHIPMENT_XML_ID'),
		'PARAMETERS'            => Loc::getMessage('BIZVAL_CODE_SHIPMENT_PARAMETERS'),
		'DELIVERY_NAME'         => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DELIVERY_NAME'),
		'COMPANY_ID'            => Loc::getMessage('BIZVAL_CODE_SHIPMENT_COMPANY_ID'),
		'MARKED'                => Loc::getMessage('BIZVAL_CODE_SHIPMENT_MARKED'),
		'DATE_MARKED'           => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DATE_MARKED'),
		'EMP_MARKED_ID'         => Loc::getMessage('BIZVAL_CODE_SHIPMENT_EMP_MARKED_ID'),
		'REASON_MARKED'         => Loc::getMessage('BIZVAL_CODE_SHIPMENT_REASON_MARKED'),
		'CANCELED'              => Loc::getMessage('BIZVAL_CODE_SHIPMENT_CANCELED'),
		'DATE_CANCELED'         => Loc::getMessage('BIZVAL_CODE_SHIPMENT_DATE_CANCELED'),
		'EMP_CANCELED_ID'       => Loc::getMessage('BIZVAL_CODE_SHIPMENT_EMP_CANCELED_ID'),
	)),
	'COMPANY' => array('TYPE' => 'ENUM'),
);

$companyOptions = array(
	Loc::getMessage('BIZVAL_GROUP_COMPANY_ENTITY') => array(
		'ID'          => Loc::getMessage('BIZVAL_CODE_COMPANY_ID'),
		'NAME'        => Loc::getMessage('BIZVAL_CODE_COMPANY_NAME'),
		'LOCATION_ID' => Loc::getMessage('BIZVAL_CODE_COMPANY_LOCATION'),
		'CODE'        => 'CODE',
		'XML_ID'      => 'XML_ID',
		'ACTIVE'      => 'ACTIVE',
		'DATE_CREATE' => 'DATE_CREATE',
		'DATE_MODIFY' => 'DATE_MODIFY',
		'CREATED_BY'  => 'CREATED_BY',
		'MODIFIED_BY' => 'MODIFIED_BY',
	),
);

$companyUFOptions = array();

$result = $USER_FIELD_MANAGER->GetUserFields(CompanyTable::getUfId(), null, LANGUAGE_ID);

foreach ($result as $name => $row)
	$companyUFOptions[$name] = ($str = $row['EDIT_FORM_LABEL']) ? $str : $name;

$companyOptions[Loc::getMessage('BIZVAL_GROUP_COMPANY_UF')] = $companyUFOptions;

$itemInputs['COMPANY']['OPTIONS'] = $companyOptions;

// POST MAPS ///////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($postedCodes = $_POST['MAP']) && is_array($postedCodes))
{
	// sanitize & validate maps

	foreach ($postedCodes as $codeId => &$maps)
	{
		if ($code = $codes[$codeId])
		{
			$codeDomain = $code['DOMAIN'];

			foreach ($maps as $personId => $map)
			{
				if (($personDomain = $personsDomainsWithCommon[$personId]) && $activeCodeDomains[$personDomain] &&
					($codeDomain == BusinessValue::COMMON_DOMAIN || $codeDomain == $personDomain))
				{
					if ($personId == BusinessValue::COMMON_PERSON_ID)
					{
						$entityInput = $commonEntityInput;
						$itemInputs['PROPERTY']['OPTIONS'] = array();
					}
					else
					{
						$entityInput = $personEntityInput;
						$itemInputs['PROPERTY']['OPTIONS'] = $propertyOptions[$personId];
					}

					if (! $map['DELETE'])
					{
						$mapEntity = $map['ENTITY'];

						if ($error = Input\Manager::getError($entityInput, $mapEntity))
							$errors['MAP'][$personId][$codeId]['ENTITY'] = $error;

						if ($error = Input\Manager::getError($itemInputs[$mapEntity], $map['ITEM']))
							$errors['MAP'][$personId][$codeId]['ITEM'] = $error;
					}
				}
				else
				{
					unset($maps[$personId]);
				}
			}
		}
		else
		{
			unset($postedCodes[$codeId]);
		}
	}
	unset($maps);

	// save maps

	if (! $errors && ! $readOnly && check_bitrix_sessid() && ($_POST['save'] || $_POST['apply']))
	{
		foreach ($postedCodes as $codeId => &$postedCode)
		{
			$code = &$codes[$codeId];

			foreach ($postedCode as $personId => &$postedMap)
			{
				if (! $map = &$code[$personId])
					$map = array();

				if ($postedMap['DELETE'])
				{
					if ($mapId = $map['ID'])
					{
						$result = BusinessValueTable::delete($mapId);

						if ($result->isSuccess())
							unset($code[$personId]);
						else
							$errors['MAP'][$personId][$codeId]['DATABASE'] = $result->getErrorMessages();
					}

					unset($postedCode[$personId]);
				}
				else
				{
					$data = array(
						'CODE_ID'        => $codeId,
						'PERSON_TYPE_ID' => $personId,
						'ENTITY'         => $postedMap['ENTITY'],
						'ITEM'           => $postedMap['ITEM'],
					);

					if ($mapId = $map['ID'])
					{
						if ($postedMap['ENTITY'] == $map['ENTITY'] && $postedMap['ITEM'] == $map['ITEM'])
							continue;
						else
							$result = BusinessValueTable::update($mapId, $data);
					}
					else
					{
						if ($map)
							continue;
						else
							$result = BusinessValueTable::add($data);
					}

					if ($result->isSuccess())
					{
						$map['ID'    ] = $result->getId();
						$map['ENTITY'] = $postedMap['ENTITY'];
						$map['ITEM'  ] = $postedMap['ITEM'];
					}
					else
					{
						$errors['MAP'][$personId][$codeId]['DATABASE'] = $result->getErrorMessages();
					}
				}
			}
		}
		unset($postedCode, $code, $postedMap, $map);
	}
}
else
{
	$postedCodes = array();
}

// VIEW ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(Loc::getMessage('BIZVAL_PAGE_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if ($errors)
{
	$message = new CAdminMessage(Loc::getMessage('BIZVAL_PAGE_ERRORS'));
	echo $message->Show();
}

// FILTER

$filterControl = new CAdminFilter('bizvalFilterControl',
	array(
		Loc::getMessage('BIZVAL_PAGE_PARENT'),
		Loc::getMessage('BIZVAL_PAGE_CODE'),
		Loc::getMessage('BIZVAL_PAGE_ENTITY'),
	)
);

$entityInput = $personEntityInput;
$entityInput['OPTIONS'] = array('' => Loc::getMessage('BIZVAL_PAGE_FILTER_ALL')) + $entityInput['OPTIONS'];
$entityInput['ONCHANGE'] = 'bizvalChangeEntityInput(this)';
$itemInputs['PROPERTY']['OPTIONS'] = $propertyOptionsByName;

?>
<form name="bizvalFilter" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
	<?$filterControl->Begin()?>
	<tr>
		<td><?=Loc::getMessage('BIZVAL_PAGE_PARENT')?>:</td>
		<td><?=Input\Manager::getEditHtml('FILTER_PARENT_ID', $parentInput, $filterParentId)?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BIZVAL_PAGE_CODE')?>:</td>
		<td><?=Input\Manager::getEditHtml('FILTER_CODE_ID', $codeInput, $filterCodeId)?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BIZVAL_PAGE_ENTITY')?>:</td>
		<td>
			<?=Input\Manager::getEditHtml('FILTER_ENTITY', $entityInput, $filterEntity)?>
			<?=Input\Manager::getEditHtml('FILTER_ITEM', $itemInputs[$filterEntity], $filterItem)?>
		</td>
	</tr>
	<?

	$filterControl->Buttons(
		array(
//			"table_id" => $sTableID,
			'url' => $APPLICATION->GetCurPage(),
			'form' => 'bizvalFilter'
		)
	);

	$filterControl->End();

	?>
</form>
<?

// TABS

$tabControlTabs = array();

foreach ($personsDomainsWithCommon as $personId => $personDomain)
{
	if (! $activeCodeDomains[$personDomain])
		continue;

	$tabControlTabs []= array(
		'DIV'          => 'mapTab'.$personId,
		'TAB'          => $personsWithCommon[$personId],
		'TITLE'        => $personsWithCommon[$personId],
		'SHOW_WRAP'    => 'N',
		'IS_DRAGGABLE' => 'Y'
	);
}

$tabControlTabs []= array('DIV' => 'domainTab', 'TAB' => Loc::getMessage('BIZVAL_PAGE_PTYPES'), 'TITLE' => Loc::getMessage('BIZVAL_PAGE_DOMAIN'));

?><form method="POST" action="<?=$APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.GetFilterParams('filter_', false)?>" name="bizvalTabs_form" id="bizvalTabs_form"><?
$tabControl = new CAdminTabControlDrag('bizvalTabs', $tabControlTabs, 'sale', false, true);
$tabControl->Begin();

foreach ($personsDomainsWithCommon as $personId => $savedPersonDomain)
{
	if (! $activeCodeDomains[$savedPersonDomain])
		continue;

	//TAB PERSON CODE MAP --
	$tabControl->BeginNextTab();
	$customFieldId = 'MAP_'.$personId;

	if ($personId == BusinessValue::COMMON_PERSON_ID)
	{
		$entityInput = $commonEntityInput;
		$itemInputs['PROPERTY']['OPTIONS'] = array();
	}
	else
	{
		$entityInput = $personEntityInput;
		$itemInputs['PROPERTY']['OPTIONS'] = $propertyOptions[$personId];
	}

	$entityInput['ONCHANGE'] = "bizvalChangeEntityInput(this, '$personId')";

	?>
	<tr>
		<td>
			<div style="position:relative; vertical-align:top">
				<?

				$tabControl->DraggableBlocksStart();

				foreach ($groups as $groupId => $groupName)
				{
					ob_start();
					$groupItemCount = 0;
					$tabControl->DraggableBlockBegin($groupName);

					?>
					<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
						<tbody>
							<?

							foreach ($codes as $codeId => $code)
							{
								if ($code['GROUP_ID'] != $groupId)
									continue;

								$codeDomain = $code['DOMAIN'];

								if ($codeDomain != BusinessValue::COMMON_DOMAIN && $codeDomain != $savedPersonDomain)
									continue;

								++$groupItemCount;

								$deleted = false;

								if (! $map = $postedCodes[$codeId][$personId])
								{
									if (! $map = $code[$personId])
									{
										if ($personId != BusinessValue::COMMON_PERSON_ID)
										{
											if ($codeDomain == BusinessValue::COMMON_DOMAIN)
												$deleted = true;

											$map = $code[BusinessValue::COMMON_PERSON_ID];
										}

										if (! $map)
											$map = array('ENTITY' => 'VALUE');
									}
								}

								$mapEntity = $map['ENTITY'];

//								$inputName = "MAP[$personId][$codeId]";
								$inputName = "MAP[$codeId][$personId]";

								$error = isset($errors['MAP'][$personId][$codeId])
									? $errors['MAP'][$personId][$codeId]
									: array();

								?>
								<tr>
									<td class="adm-detail-content-cell-l" width="40%">
										<?=htmlspecialcharsbx($code['NAME'])?>
										<img src="/bitrix/js/main/core/images/hint.gif"
										     style="cursor: help;"
										     title="<?=htmlspecialcharsbx(implode(', ', $code['PARENTS']))?>">
										<?if ($error['DATABASE']):?>
											<div style="color:#ff5454"><?=htmlspecialcharsbx(implode('<br>', $error['DATABASE']))?></div>
										<?endif?>
									</td>
									<td class="adm-detail-content-cell-r" width="50%">
										<span><?

											echo Input\Manager::getEditHtml($inputName.'[ENTITY]'
												, $deleted ? $entityInput + array('DISABLED' => 'Y') : $entityInput
												, $mapEntity
											);

											if ($error['ENTITY'])
												echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['ENTITY'])).'</div>';

											?>
										</span><span><?

											echo Input\Manager::getEditHtml($inputName.'[ITEM]'
												, $deleted ? $itemInputs[$mapEntity] + array('DISABLED' => 'Y') : $itemInputs[$mapEntity]
												, $map['ITEM']);

											if ($error['ITEM'])
												echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['ITEM'])).'</div>';

											?>
										</span>
									</td>
									<td width="10%">
										<?if ($personId && $codeDomain == BusinessValue::COMMON_DOMAIN):?>
											<label>
												<?=Loc::getMessage('BIZVAL_PAGE_DELETE')?>
												<input type="checkbox" name="<?=$inputName.'[DELETE]'?>"<?=$deleted ? ' checked' : ''?> onclick="bizvalToggleDeleteMap(this)">
											</label>
										<?endif?>
									</td>
								</tr>
								<?
							}

							?>
						</tbody>
					</table>
					<?

					$tabControl->DraggableBlockEnd();

					$groupItemCount ? ob_end_flush() : ob_end_clean();
				}

				?>
			</div>
		</td>
	</tr>
	<?

	//-- TAB PERSON CODE MAP
}

//TAB PERSONS DOMAINS --
$tabControl->BeginNextTab();

?>
	<tr>
		<td>
			<?=bitrix_sessid_post()?>
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<?

					foreach ($persons as $personId => $personName)
					{
						$error = isset($errors['PERSON_DOMAIN'][$personId])
							? $errors['PERSON_DOMAIN'][$personId]
							: array();

						?>
						<tr>
							<td class="adm-detail-content-cell-l" width="40%">
								<?

								echo $personName;

								if ($error['ADD'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['ADD'])).'</div>';

								if ($error['DELETE'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['DELETE'])).'</div>';

								?>
							</td>
							<td class="adm-detail-content-cell-r">
								<?

								echo Input\Manager::getEditHtml("PERSON_DOMAIN[$personId]", $personDomainInput, $personsDomains[$personId]);

								if ($error['DOMAIN'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['DOMAIN'])).'</div>';

								?>
							</td>
						</tr>
						<?
					}

					?>
				</tbody>
			</table>
		</td>
	</tr>
<?

//-- TAB PERSONS DOMAINS

$tabControl->Buttons(array(
	'disabled' => $readOnly,
	'back_url' => '/bitrix/admin/sale_business_value.php?lang='.LANGUAGE_ID.GetFilterParams('filter_'),
));

$tabControl->End();

?>
</form>
<script>

	function bizvalToggleDeleteMap(checkbox)
	{
		'use strict';
		var	td = checkbox.parentNode.parentNode.parentNode.childNodes[3],
			entity = td.childNodes[1].firstChild,
			item = td.childNodes[2].firstChild;

		if (checkbox.checked)
		{
			entity.disabled = true;
			item.disabled = true;
		}
		else
		{
			entity.disabled = false;
			item.disabled = false;
		}
	}

	function bizvalChangeEntityInput(selector, personId)
	{
		'use strict';
		var	entity = selector.options[selector.selectedIndex].value,
			itemContainer = selector.parentNode.nextSibling,
			name = itemContainer.firstChild.name,
			allProperties = '<?

				$propertyInput = $itemInputs['PROPERTY'];
				$propertyInput['OPTIONS'] = $propertyOptionsByName;

				echo CUtil::JSEscape(Input\Manager::getEditHtml('', $propertyInput));

			?>',
			properties = {
				<?

				$propertyInput = $itemInputs['PROPERTY'];
				foreach ($propertyOptions as $personId => $options)
				{
					$propertyInput['OPTIONS'] = $options;
					echo "'$personId':'".CUtil::JSEscape(Input\Manager::getEditHtml('', $propertyInput))."',\n";
				}

				?>
				ie8:'sucks'
			},
			items = {
				<?

				foreach (array_diff_key($itemInputs, array('PROPERTY'=>1)) as $entity => $input)
					echo "'$entity':'".CUtil::JSEscape(Input\Manager::getEditHtml('', $input))."',\n";

				?>
				ie8:'sucks'
			};

		itemContainer.innerHTML = (personId !== '<?=BusinessValue::COMMON_PERSON_ID?>' && entity === 'PROPERTY')
			? (typeof personId === 'undefined' ? allProperties : properties[personId])
			: items[entity];

		itemContainer.firstChild.name = name;
	}

</script>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');