<?
if (!defined( "B_PROLOG_INCLUDED" ) || B_PROLOG_INCLUDED !== true)
	die();
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
class CSeoMetaSitemap extends CCSeoMeta
{
	private static $AllPropsForSort = array();
	private static $AllPropsForSortByIblock = array();
	public function ParseArray($Condition, $ConditionSections)
	{
		$result = self::PrepareConditions( $Condition['CHILDREN'], $ConditionSections );                  

		// Generate pages array
		$Pages = array();
		if ($Condition['DATA']['All'] == 'AND' && $Condition['DATA']['True']) // AND
		{
			foreach ( $result as $k => $CondPages )
			{
				foreach ( $CondPages as $PropID => $Prop )
				{
					$PagesCnt = count( $Pages );
					if (isset( $Prop['ALL'] ) && is_array( $Prop['ALL'] )) // if not value
					{
						if ($PagesCnt > 0)
						{
							$TmpPages = $Pages;
							$Pages = array();
							foreach ( $TmpPages as $Pagex )
							{
								foreach ( $Prop['ALL']['MISSSHOP'][1] as $i => $AllProps )
								{
                                    
									$Cnt = count( $Pages );
									$Pages[$Cnt] = $Pagex;
									$Pages[$Cnt][$PropID]['MISSSHOP'][1] = $Prop['ALL']['MISSSHOP'][1][$i];
									$Pages[$Cnt][$PropID]['MISSSHOP'][0] = $Prop['ALL']['MISSSHOP'][0][$i];
									$Pages[$Cnt][$PropID]['BITRIX'][0] = $Prop['ALL']['BITRIX'][0][$i];
									$Pages[$Cnt][$PropID]['BITRIX'][1] = $Prop['ALL']['BITRIX'][1][$i];
									$Pages[$Cnt][$PropID]['CODE']=$Prop['CODE'];
								}
							}
						}
						else
						{
							$k = 0;
							foreach ( $Prop['ALL']['MISSSHOP'][1] as $j => $AllProps )
							{
								$Pages[$k][$PropID]['MISSSHOP'][1] = $AllProps;
								$Pages[$k][$PropID]['MISSSHOP'][0] = $Prop['ALL']['MISSSHOP'][0][$j];
								$Pages[$k][$PropID]['BITRIX'][0] = $Prop['ALL']['BITRIX'][0][$j];
								$Pages[$k][$PropID]['BITRIX'][1] = $Prop['ALL']['BITRIX'][1][$j];
								$Pages[$k][$PropID]['CODE'] = $Prop['CODE'];
								++$k;
							}
						}
					}
					elseif ($PropID == "PRICE")
					{
						if ($PagesCnt > 0)
						{
							foreach ( $Pages as &$Page )
							{
								$Page['PRICES'][key( $Prop )]['TYPE'][] = $Prop[key( $Prop )]['TYPE'][0];
								$Page['PRICES'][key( $Prop )]['ID'][] = $Prop[key( $Prop )]['ID'][0];
								$Page['PRICES'][key( $Prop )]['VALUE'][] = $Prop[key( $Prop )]['VALUE'][0];
							}
						}
						else
						{
							$Pages[0]['PRICES'] = $Prop;
						}
					}
					else
					{
						if ($PagesCnt > 0)
						{
							foreach ( $Pages as &$Page )
							{
								$Page[$PropID]['MISSSHOP'][1][] = $Prop['MISSSHOP'][1][0];
								$Page[$PropID]['MISSSHOP'][0][] = $Prop['MISSSHOP'][0][0];
								$Page[$PropID]['BITRIX'][0][] = $Prop['BITRIX'][0][0];
								$Page[$PropID]['BITRIX'][1][] = $Prop['BITRIX'][1][0];
								$Page[$PropID]['CODE'] = $Prop['CODE'];
							}
						}
						else
						{
							$Pages[0][$PropID]['MISSSHOP'][1][] = $Prop['MISSSHOP'][1][0];
							$Pages[0][$PropID]['MISSSHOP'][0][] = $Prop['MISSSHOP'][0][0];
							$Pages[0][$PropID]['BITRIX'][0][] = $Prop['BITRIX'][0][0];
							$Pages[0][$PropID]['BITRIX'][1][] = $Prop['BITRIX'][1][0];
							$Pages[0][$PropID]['CODE'] = $Prop['CODE'];
						}
					}
				}
			}
		}
		elseif ($Condition['DATA']['All'] == 'OR' && $Condition['DATA']['True']) // OR
		{
			$Pages = $result;
		}




		return $Pages;
	}
	public function PrepareConditions($conditions, $ConditionSections)
	{
		$return = array();
		$i = 0;
		$PropVals = array();
		foreach ( $conditions as $condition )
		{                                  
			if (strpos( $condition['CLASS_ID'], 'FilterPrice' ) === false)
			{
				if (isset( $condition['CHILDREN'] )) // If it is group of condition
				{            
					$tmp=self::ParseArray( $condition, $ConditionSections );

					$PropVals[$i] = $tmp[0];
					unset($tmp);
					continue;
				}
				else
				{                         
					$arCond = explode( ':', $condition['CLASS_ID'] );
					$IdIblock = $arCond[1];
					$IdProperty = $arCond[2];
					$PropVals[$i] = self::GetPropVal( $condition['DATA']['value'], $IdProperty, $IdIblock, $condition['DATA']['logic'], $ConditionSections );
				}
			}
			else // If filter price
			{    
				$arCond = explode( 'FilterPrice', str_replace( 'CondIB', '', $condition['CLASS_ID'] ) );
				$PropVals[$i] = self::GetPropVal( $condition['DATA']['value'], 0, 0, $condition['DATA']['logic'], $ConditionSections, $arCond[0], $arCond[1] );
			}
			++$i;
		}
		return $PropVals;
	}
	public function GetValuesIfEmptyValue($IdIblock, $IdProperty, $ConditionSections)
	{
		// All products - need for empty values
		$return = array();
		$CatalogResult = CCatalogSKU::GetInfoByProductIBlock( $IdIblock );
		if (!is_array( $CatalogResult ))
		{
			$OffersResult = CCatalogSKU::GetInfoByOfferIBlock( $IdIblock );
		}
		if ($IdIblock == $CatalogResult['PRODUCT_IBLOCK_ID']) // If property of product
		{
			$res = CIBlockElement::GetList( Array(), Array(
					"IBLOCK_ID" => $IdIblock,
					"ACTIVE" => "Y",
					"SECTION_ID" => $ConditionSections,
					"INCLUDE_SUBSECTIONS" => "Y"
			), false, false, array(
					'PROPERTY_' . $IdProperty
			) );

			while ( $ob = $res->GetNextElement() )
			{
				$arFields = $ob->GetFields();
				$return[] = $arFields;
			}
		}
		elseif ($IdIblock == $OffersResult['IBLOCK_ID']) // If property of offer
		{
			$res = CIBlockElement::GetList( Array(), Array(
					"IBLOCK_ID" => $IdIblock,
					"ACTIVE" => "Y"
			), false, false, array(
					'ID',
					'PROPERTY_' . $IdProperty
			) );
			$Offers = array();
			$OffersIds = array();
			while ( $ob = $res->GetNextElement() )
			{
				$arFields = $ob->GetFields();
				if (!in_array( $arFields['PROPERTY_' . $IdProperty . '_VALUE'], $Offers ) && !is_null( $arFields['PROPERTY_' . $IdProperty . '_VALUE'] ))
				{
					$OffersIds[] = $arFields['ID'];
					$Offers[$arFields['ID']]['VALUE'] = $arFields['PROPERTY_' . $IdProperty . '_VALUE'];
				}
			}
			// Find products for offers
			$ProductsOffers = CCatalogSKU::getProductList( $OffersIds, $IdIblock );
			$Products = array();
			foreach ( $ProductsOffers as $OfferKey => $Prod )
			{
				$Offers[$OfferKey]['PROD'] = $Prod['ID'];
				if (!in_array( $Prod['ID'], $Products ) && !is_null( $Prod['ID'] ))
				{
					$Products[] = $Prod['ID'];
				}
			}
			// Find in section
			$NeedPropducts = array();
			$res = CIBlockElement::GetList( Array(), Array(
					"ID" => $Products,
					"IBLOCK_ID" => $OffersResult['PRODUCT_IBLOCK_ID'],
					"ACTIVE" => "Y",
					"SECTION_ID" => $ConditionSections,
					"INCLUDE_SUBSECTIONS" => "Y"
			), false, false, array(
					'ID'
			) );
			while ( $ob = $res->GetNextElement() )
			{
				$arFields = $ob->GetFields();
				if (!in_array( $arFields['ID'], $NeedPropducts ) && !is_null( $arFields['ID'] ))
					$NeedPropducts[] = $arFields['ID'];
			}
			foreach ( $Offers as $IdOffer => $Val )
			{
				if (!in_array( $Val['PROD'], $NeedPropducts ))
					unset( $Offers[$IdProd] );
				elseif (!in_array( $Val['VALUE'], $return ))
					$return[] = $Val['VALUE'];
			}
		}
		return $return;
	}
	public function SetListOfProps($FilterType)
	{
		$TmpAllProps = array();
		foreach ( self::$AllPropsForSortByIblock as $IdIblock => $Props )
		{
			if ($IdIblock != "PRICES")
			{
				$CatalogResult = CCatalogSKU::GetInfoByProductIBlock( $IdIblock );
				if (is_array( $CatalogResult ))
				{
					foreach ( $Props as $Prop )
						$TmpAllProps['PRODUCT'][] = $Prop;
				}
				else
				{
					foreach ( $Props as $Prop )
						$TmpAllProps['OFFERS'][] = $Prop;
				}
			}
			else
			{
				$TmpAllProps['PRICES'] = $Props;
			}
		}
		self::$AllPropsForSort = array();
		if ($FilterType == "BITRIX")
		{
			if (isset( $TmpAllProps['PRICES'] ))
				self::$AllPropsForSort[]["ID"] = "PRICES";
		}
		if (isset( $TmpAllProps['PRODUCT'] ))
			foreach ( $TmpAllProps['PRODUCT'] as $Prop )
				if (!in_array( $Prop, self::$AllPropsForSort ))
					self::$AllPropsForSort[] = $Prop;
		if (isset( $TmpAllProps['OFFERS'] ))
			foreach ( $TmpAllProps['OFFERS'] as $Prop )
				if (!in_array( $Prop, self::$AllPropsForSort ))
					self::$AllPropsForSort[] = $Prop;
		if ($FilterType == "MISSSHOP")
		{
			if (isset( $TmpAllProps['PRICES'] ))
			{
				self::$AllPropsForSort[]["ID"] = "PRICES";
			}
		}
		unset( $TmpAllProps );
	}
	public function SortPagesCodes($Pages)
	{
		$return = array();
		foreach ( self::$AllPropsForSort as $Prop )
		{
			foreach ( $Pages as $i => $Page )
				if (isset( $Page[$Prop['ID']] ))
					$return[$i][$Prop['ID']] = $Page[$Prop['ID']];
		}
		return $return;
	}
	private function GetListOfPropsByIblock($IdIblock)
	{
		$rsProps = CIBlockProperty::GetList( array(
				"SORT" => "ASC",
				'ID' => 'ASC'
		), array(
				"IBLOCK_ID" => $IdIblock,
				"ACTIVE" => "Y"
		) );
		while ( $arProp = $rsProps->Fetch() )
		{
			self::$AllPropsForSortByIblock[$IdIblock][] = $arProp;
		}
	}
	private function GetPropVal($ConditionValue, $IdProperty, $IdIblock, $Logic, $ConditionSections, $PriceType = 0, $PriceCode = '')
	{
		$return = array();
		// All Props need for sort
		if (!isset( self::$AllPropsForSortByIblock[$IdIblock] ) && $IdIblock > 0)
		{
			self::GetListOfPropsByIblock( $IdIblock );
		}
		// Get values from conditions
		if ($Logic == 'Equal')
		{
			if ($IdProperty > 0 && $IdIblock > 0)
			{
				$resProperty = CIBlockProperty::GetByID( $IdProperty, $IdIblock, false );
				if ($arProperty = $resProperty->GetNext())
				{
					if ($arProperty['PROPERTY_TYPE'] == 'L') // list
					{
						$return = self::GetValueOfListProp( $ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $arProperty['CODE'] );
					}
					elseif ($arProperty['PROPERTY_TYPE'] == 'S' && $arProperty['USER_TYPE'] == 'directory')
					{
						$return = self::GetValueOfHLProp( $ConditionValue, $IdIblock, $IdProperty, $ConditionSections, (!empty($arProperty['CODE']))?$arProperty['CODE']: $arProperty['ID'] );
					}
					elseif ($arProperty['PROPERTY_TYPE'] == 'E') // link
					{                               
						$return = self::GetValueOfLinkProp( $ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $arProperty['CODE'],$arProperty['LINK_IBLOCK_ID'] );
					}
					else
					{
						$return = self::GetValueOfTextProp( $ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $arProperty['CODE'] );
					}
				}
			}
			elseif (($PriceType == 'Min' || $PriceType == 'Max') && $PriceCode != "")
			{
				$res = CCatalogGroup::GetListEx( array(), array(
						'=NAME' => $PriceCode
				), false, false, array(
						'ID'
				) );
				if ($group = $res->Fetch())
				{
					$priceID = $group['ID'];
					if (!is_null( $ConditionValue ))
					{
						if ($PriceType == 'Min')
						{
							$return["PRICE"][$PriceCode]['TYPE'][] = "MIN";
						}
						elseif ($PriceType == 'Max')
						{
							$return["PRICE"][$PriceCode]['TYPE'][] = "MAX";
						}
						$return["PRICE"][$PriceCode]['ID'][] = $priceID;
						$return["PRICE"][$PriceCode]['VALUE'][] = $ConditionValue;
						self::$AllPropsForSortByIblock["PRICES"][$PriceCode][] = $return["PRICE"][$PriceCode];
					}
				}
			}
		}
		return $return;
	}
	private function GetValueOfListProp($ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $PropertyCode)
	{                               
		$return = array();
		if ($ConditionValue == "")
		{
			$ListVals = array();
			$ListVals = self::GetValuesIfEmptyValue( $IdIblock, $IdProperty, $ConditionSections );    
			if (count( $ListVals['MISSSHOP'][1] ) == count( $ListVals['MISSSHOP'][0] ) && count( $ListVals['MISSSHOP'][1] ) == count( $ListVals['BITRIX'][0] ) && count( $ListVals['MISSSHOP'][1] ) == count( $ListVals['BITRIX'][1] ))
			{
				// Get all values of property - need for xml_id
				$AllProps = array();
				$property_enums = CIBlockPropertyEnum::GetList( Array(
						"SORT" => "ASC"
				), Array(
						"IBLOCK_ID" => $IdIblock,
						"PROPERTY_ID" => $IdProperty
				) );
				while ( $enum_fields = $property_enums->GetNext() )
				{
					$AllProps[$enum_fields['ID']] = $enum_fields['XML_ID'];
				}
				//
				$arAllProps['MISSSHOP'][1] = array();
				foreach ( $ListVals as $ListVal )
				{
					if (!in_array( CUtil::translit( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'], 'ru' ), $arAllProps['MISSSHOP'][1] ) && !is_null( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'] ))
					{
						$arAllProps['MISSSHOP'][1][] = CUtil::translit( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'], 'ru' );
						$arAllProps['MISSSHOP'][0][] = "";
						$arAllProps['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $ListVal['PROPERTY_' . $IdProperty . '_ENUM_ID'] ) ) );
						$arAllProps['BITRIX'][1][] = $AllProps[$ListVal['PROPERTY_' . $IdProperty . '_ENUM_ID']];
					}
				}
				// All possible values
				$AllVals['MISSSHOP'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][1] );
				$AllVals['MISSSHOP'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][0] );
				$AllVals['BITRIX'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][0] );
				$AllVals['BITRIX'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][1] );
				if (count( $AllVals['MISSSHOP'][1] ) > 0)
					foreach ( $AllVals['MISSSHOP'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][1][] = $AllVal;
					}
				if (count( $AllVals['MISSSHOP'][0] ) > 0)
					foreach ( $AllVals['MISSSHOP'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][0] ) > 0)
					foreach ( $AllVals['BITRIX'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][1] ) > 0)
					foreach ( $AllVals['BITRIX'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][1][] = $AllVal;
					}
			}
		}
		else
		{
			$property_enums = CIBlockPropertyEnum::GetList( Array(
					"SORT" => "ASC"
			), Array(
					"IBLOCK_ID" => $IdIblock,
					"PROPERTY_ID" => $IdProperty,
					"ID" => $ConditionValue
			) );

			if ($enum_fields = $property_enums->GetNext())
			{
				$return[$IdProperty]['MISSSHOP'][1][] = CUtil::translit( $enum_fields['VALUE'], 'ru' );
				$return[$IdProperty]['MISSSHOP'][0][] = "";
				$return[$IdProperty]['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $enum_fields['ID'] ) ) );
				$return[$IdProperty]['BITRIX'][1][] = $enum_fields['XML_ID'];
			}
		}
		$return[$IdProperty]['CODE']=$PropertyCode;       
		return $return;
	}
	private function GetValueOfLinkProp($ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $PropertyCode,$LinkIblock)
	{
		$return = array();

		if(isset($ConditionValue) && !empty($ConditionValue))
		{
			$res = CIBlockElement::GetByID($ConditionValue);
			if($ar_res = $res->GetNext())
			{
				$return[$IdProperty]['MISSSHOP'][1][] = CUtil::translit( $ar_res['NAME'], 'ru' );
				$return[$IdProperty]['MISSSHOP'][0][] = "";
				$return[$IdProperty]['BITRIX'][0][] = CUtil::translit( $ar_res['NAME'], 'ru' );
				$return[$IdProperty]['BITRIX'][1][] = CUtil::translit( $ar_res['NAME'], 'ru' );
			}
		}

		$return[$IdProperty]['CODE']=$PropertyCode;
		return $return;
	}
	private function GetValueOfHLProp($ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $PropertyCode)
	{
		$return = array();
		if ($ConditionValue == "")
		{
			$ListVals = array();
			$ListVals = self::GetValuesIfEmptyValue( $IdIblock, $IdProperty, $ConditionSections );

			$arAllProps['MISSSHOP'][1] = array();
			$res = CIBlockProperty::GetByID( $IdProperty, $IdIblock, false );
			if ($ar_res = $res->GetNext())
			{
				$highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList( array(
						"filter" => array(
								'TABLE_NAME' => $ar_res['USER_TYPE_SETTINGS']['TABLE_NAME']
						)
				) );
				while ( $HLBlock = $highBlock->Fetch() )
				{
					$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $HLBlock );
					$main_query = new Entity\Query( $entity );
					$main_query->setSelect( array(
							"*"
					) );
					$main_query->setFilter( array(
							'=UF_XML_ID' => $ListVals
					) );
					$result = $main_query->exec();
					$result = new CDBResult( $result );
					while ( $row = $result->Fetch() )
					{
						$arAllProps['MISSSHOP'][1][] = CUtil::translit( $row['UF_NAME'], 'ru' );
						$arAllProps['MISSSHOP'][0][] = "";
						$arAllProps['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $row['UF_XML_ID'] ) ) );
						$arAllProps['BITRIX'][1][] = $row['UF_XML_ID'];
					}
				}
			}
			if (count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['MISSSHOP'][0] ) && count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['BITRIX'][0] ) && count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['BITRIX'][1] ))
			{
				$AllVals['MISSSHOP'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][1] );
				$AllVals['MISSSHOP'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][0] );
				$AllVals['BITRIX'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][0] );
				$AllVals['BITRIX'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][1] );
				if (count( $AllVals['MISSSHOP'][1] ) > 0)
					foreach ( $AllVals['MISSSHOP'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][1][] = $AllVal;
					}
				if (count( $AllVals['MISSSHOP'][0] ) > 0)
					foreach ( $AllVals['MISSSHOP'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][0] ) > 0)
					foreach ( $AllVals['BITRIX'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][1] ) > 0)
					foreach ( $AllVals['BITRIX'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][1][] = $AllVal;
					}
			}
		}
		else
		{
			$res = CIBlockProperty::GetByID( $IdProperty, $IdIblock, false );
			if ($ar_res = $res->GetNext())
			{
				$highBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList( array(
						"filter" => array(
								'TABLE_NAME' => $ar_res['USER_TYPE_SETTINGS']['TABLE_NAME']
						)
				) );
				while ( $HLBlock = $highBlock->Fetch() )
				{
					$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $HLBlock );
					$main_query = new Entity\Query( $entity );
					$main_query->setSelect( array(
							"*"
					) );
					$main_query->setFilter(
							array("LOGIC" => "OR",
							array("=UF_NAME" => $ConditionValue),
							array("=UF_XML_ID" => $ConditionValue)));
					$result = $main_query->exec();
					$result = new CDBResult( $result );
					while ( $row = $result->Fetch() )
					{
						$return[$IdProperty]['MISSSHOP'][1][] = CUtil::translit( $ConditionValue, 'ru' );
						$return[$IdProperty]['MISSSHOP'][0][] = "";
						$return[$IdProperty]['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $row['UF_XML_ID'] ) ) );
						$return[$IdProperty]['BITRIX'][1][] = $row['UF_XML_ID'];
					}
				}
			}
		}
		$return[$IdProperty]['CODE']=$PropertyCode;
		return $return;
	}
	private function GetValueOfTextProp($ConditionValue, $IdIblock, $IdProperty, $ConditionSections, $PropertyCode)
	{
		$return = array();
		if ($ConditionValue == "")
		{
			$ListVals = array();
			$ListVals = self::GetValuesIfEmptyValue( $IdIblock, $IdProperty, $ConditionSections );
			$arAllProps = array();
			foreach ( $ListVals as $ListVal )
			{
				if (!is_null( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'] ))
				{
					$arAllProps['MISSSHOP'][1][] = CUtil::translit( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'], 'ru' );
					$arAllProps['MISSSHOP'][0][] = "";
					$arAllProps['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'] ) ) );
					$arAllProps['BITRIX'][1][] = str_replace( '%', '%25', urlencode( $ListVal['PROPERTY_' . $IdProperty . '_VALUE'] ) );
				}
			}
			$AllVals = array();
			if (count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['MISSSHOP'][0] ) && count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['BITRIX'][0] ) && count( $arAllProps['MISSSHOP'][1] ) == count( $arAllProps['BITRIX'][1] ))
			{
				$AllVals['MISSSHOP'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][1] );
				$AllVals['MISSSHOP'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['MISSSHOP'][0] );
				$AllVals['BITRIX'][0] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][0] );
				$AllVals['BITRIX'][1] = parent::AllCombinationsOfArrayElements( $arAllProps['BITRIX'][1] );
				if (count( $AllVals['MISSSHOP'][1] ) > 0)
					foreach ( $AllVals['MISSSHOP'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][1][] = $AllVal;
					}
				if (count( $AllVals['MISSSHOP'][0] ) > 0)
					foreach ( $AllVals['MISSSHOP'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['MISSSHOP'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][0] ) > 0)
					foreach ( $AllVals['BITRIX'][0] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][0][] = $AllVal;
					}
				if (count( $AllVals['BITRIX'][1] ) > 0)
					foreach ( $AllVals['BITRIX'][1] as $AllVal )
					{
						$return[$IdProperty]['ALL']['BITRIX'][1][] = $AllVal;
					}
			}
		}
		else
		{
			$return[$IdProperty]['MISSSHOP'][1][] = CUtil::translit( $ConditionValue, 'ru' );
			$return[$IdProperty]['MISSSHOP'][0][] = "";
			$return[$IdProperty]['BITRIX'][0][] = abs( crc32( htmlspecialcharsbx( $ConditionValue ) ) );
			$return[$IdProperty]['BITRIX'][1][] = str_replace( '%', '%25', urlencode( strtolower($ConditionValue) ) );
		}
		$return[$IdProperty]['CODE']=$PropertyCode;
		return $return;
	}
}