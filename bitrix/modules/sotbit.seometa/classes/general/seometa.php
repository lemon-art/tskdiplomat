<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Sotbit\Seometa\ConditionTable;
use Sotbit\Seometa\SeoMeta;
use Bitrix\Main\Loader;
class CSeoMeta extends Bitrix\Iblock\Template\Functions\FunctionBase
{
	protected static $FilterResult=array();
	private static $Checker=array();
	private static $CheckerRule=array();
	private static $UserFields=array();

	public function SetFilterResult($FilterResult,$Section)
	{
		self::$FilterResult=$FilterResult;
		self::$FilterResult['PARAMS_SECTION']['ID']=$Section;

	}
	public function AddAdditionalFilterResults($FilterAdditionalResult)
	{
		foreach($FilterAdditionalResult as $key=>$value)
		{
			if(stripos($key,'PROPERTY_')===0 || stripos($key,'=PROPERTY_')===0 || stripos($key,'>=PROPERTY_')===0 || stripos($key,'<=PROPERTY_')===0)
			{
				if(is_array($value))
				{
					$Xvalue=$value;
				}
				else
				{
					$Xvalue=array($value);
				}
				/*if(is_array($value))
				{
					$Xvalue=$value[0];
				}
				else
				{
					$Xvalue=$value;
				}*/
				if(stripos($key,'PROPERTY_')===0)
					$key=str_replace('PROPERTY_','',$key);
				elseif (stripos ( $key, '=PROPERTY_' ) === 0)
					$key = str_replace ( '=PROPERTY_', '', $key );
				elseif (stripos ( $key, '>=PROPERTY_' ) === 0)
					$key = str_replace ( '>=PROPERTY_', '', $key );
				elseif (stripos ( $key, '<=PROPERTY_' ) === 0)
					$key = str_replace ( '<=PROPERTY_', '', $key );

				if(is_numeric($key))
					$Filter=array('ID'=>$key);
				else
					$Filter=array('CODE'=>$key);

				$property_enums = CIBlockProperty::GetList(Array("ID"=>"ASC", "SORT"=>"ASC"), $Filter);
				while($enum_fields = $property_enums->GetNext())
				{   
					$bool=true;
					if(isset(self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES']))
						foreach(self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'] as $i=>$value)
						{
							if(isset($value["FACET_VALUE"]) && ($value["FACET_VALUE"]=$Xvalue[0]))
								$bool=false;
						}
					if($enum_fields['PROPERTY_TYPE']=='L')//if list
					{
						$ListValues=array();
						$property_enums = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC"), Array('ID'=>$Xvalue,"PROPERTY_ID"=>$enum_field['ID']));
						while($property_fields = $property_enums->GetNext())
						{
							$ListValues[$property_fields['ID']]=$property_fields['VALUE'];
						}
					} elseif ($enum_fields["PROPERTY_TYPE"] === "E"){ 
                        $ListValues=array(); 
                        $arLinkFilter = array ( 
                            "ID" => $Xvalue, 
                            'IBLOCK_ID' => $enum_fields['LINK_IBLOCK_ID'] 
                        ); 

                        $rsLink = CIBlockElement::GetList(Array("SORT"=>"ASC"),$arLinkFilter,false,false,array("ID", "NAME")); 

                        while($elementFields = $rsLink->GetNext()){ 
                            $ListValues[$elementFields['ID']]=$elementFields['NAME']; 
                        } 
                    }
                    
					if($bool)
					{
						foreach($Xvalue as $XXvalue)
						{
							if(!isset(self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'][$XXvalue]['CHECKED']))
								self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'][$XXvalue]['CHECKED']=1;
							if(!isset(self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'][$XXvalue]['VALUE']))
								self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'][$XXvalue]['VALUE']=$XXvalue;
							if(!isset(self::$FilterResult['ITEMS'][$enum_fields['ID']]['CODE']))
								self::$FilterResult['ITEMS'][$enum_fields['ID']]['CODE']=$enum_fields['CODE'];
							if(isset($ListValues[$XXvalue]))
							{
								self::$FilterResult['ITEMS'][$enum_fields['ID']]['VALUES'][$XXvalue]['LIST_VALUE']=$ListValues[$XXvalue];
								unset($ListValues[$XXvalue]);
							}
						}
					}
					else
					{

					}
				}
			}
			elseif(is_array($value))
			{
				self::AddAdditionalFilterResults($value);
			}
		}
	}
	public function FilterCheck()
	{
		$FilterResult=self::$FilterResult;
		self::$Checker=array();    
		foreach($FilterResult['ITEMS'] as $key=>$param)
		{
			foreach($param['VALUES'] as $key_val=>$param_val)
			{
				if(isset($param_val['CHECKED']) && $param_val['CHECKED']==1)
				{
					if(isset($param['ID']) && !empty($param['ID']))
						self::$Checker[$param['ID']][$key_val]=1;
					else
						self::$Checker[$key][$key_val]=1;
				}
			}
		}                          
	}
    
	public function getRules($arParams)
	{
		$rows=array();
			$filter=array(
				'=ACTIVE'=>'Y',
				'=TYPE_OF_CONDITION'=>'filter',
			);
			$order=array('SORT'=>'desc');
			$result=ConditionTable::getList(array(                                                 
                'select' => array('ID','INFOBLOCK','SITES','SECTIONS','RULE','META','NO_INDEX','STRONG'),
				'filter' => $filter,
				'order' => $order,
			));
			while ($row = $result->fetch())
			{
				$sites=unserialize($row['SITES']);
				$sections=unserialize($row['SECTIONS']);
				if((!isset($sections) || !is_array($sections) || in_array($arParams["SECTION_ID"],$sections)) && in_array(SITE_ID,$sites))
				{
					unset($row['SITES']);
					unset($row['SECTIONS']);
					$rows[] = $row;
				}
			}
		return $rows;
	}
    
	public function SetMetaCondition($rule,$SectionId,$IblockId)
	{                           
        
		$meta=array();
		self::$CheckerRule=self::$Checker;      
		$result=self::ParseArray($rule['RULES']);     
		//strong rule
		if($rule['STRONG']=='Y' && isset(self::$CheckerRule))
			foreach(self::$CheckerRule as $key=>$param)
			{
				if(in_array(1,$param))
				{
					$result=0;break;
				}
		}
		if($result==1)
		{                     
			$meta['TITLE']=self::UserFields($rule['META']['ELEMENT_TITLE'],$SectionId,$IblockId);
			$meta['KEYWORDS']=self::UserFields($rule['META']['ELEMENT_KEYWORDS'],$SectionId,$IblockId);
			$meta['DESCRIPTION']=self::UserFields($rule['META']['ELEMENT_DESCRIPTION'],$SectionId,$IblockId);
			$meta['PAGE_TITLE']=self::UserFields($rule['META']['ELEMENT_PAGE_TITLE'],$SectionId,$IblockId);
			$meta['BREADCRUMB_TITLE']=self::UserFields($rule['META']['ELEMENT_BREADCRUMB_TITLE'],$SectionId,$IblockId);
			$meta['ELEMENT_BOTTOM_DESC']=self::UserFields($rule['META']['ELEMENT_BOTTOM_DESC'],$SectionId,$IblockId);
			$meta['ELEMENT_TOP_DESC']=self::UserFields($rule['META']['ELEMENT_TOP_DESC'],$SectionId,$IblockId);
			$meta['ELEMENT_ADD_DESC']=self::UserFields($rule['META']['ELEMENT_ADD_DESC'],$SectionId,$IblockId);           
                          
			if($rule['NO_INDEX']=='Y')
			{
				$meta['NO_INDEX']='Y';
			}
			elseif($rule['NO_INDEX']=='N')
			{
				$meta['NO_INDEX']='N';
			}
		}
		return $meta;
	}
	private function ParseArray($array)
	{

		$result=self::PrepareConditions($array['CHILDREN']); 

		if(isset($array['DATA']['All']) && isset($array['DATA']['True']) && $array['DATA']['All']=='AND' && $array['DATA']['True']=='True')
			$return=self::ANDConditions($result);
		if(isset($array['DATA']['All']) && isset($array['DATA']['True']) && $array['DATA']['All']=='OR' && $array['DATA']['True']=='True')
			$return=self::ORConditions($result);
		if(isset($array['DATA']['All']) && isset($array['DATA']['True']) && $array['DATA']['All']=='AND' && $array['DATA']['True']=='False')
			$return=self::ANDFalseConditions($result);
		if(isset($array['DATA']['All']) && isset($array['DATA']['True']) && $array['DATA']['All']=='OR' && $array['DATA']['True']=='False')
			$return=self::ORFalseConditions($result);
		return $return;
	}
	private function PrepareConditions($conditions)
	{
		$MassCond=array();
		$return = 0;         
		foreach($conditions as $condition)
		{                        
			$type=0;
			if(isset($condition['CLASS_ID']) && $condition['CLASS_ID']=='CondGroup')
				array_push($MassCond,self::ParseArray($condition));
			$idsSection=explode(':',$condition['CLASS_ID']);
			$idSections=$idsSection[count($idsSection)-1];   
			$idCondition=$condition['DATA']['value'];
			$Types=explode('Price',$condition['CLASS_ID']);
			if($Types[0]=='CondIBMax')// MAX_PRICE
				$type='MAX:VALUE:'.$Types[1];
			if($Types[0]=='CondIBMin')
				$type='MIN:VALUE:'.$Types[1];
			if($Types[0]=='CondIBMaxFilter')// MAX_PRICE
			{
				if(!isset(self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MAX']['HTML_VALUE']))
					self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MAX']['HTML_VALUE']=self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MAX']['VALUE'];
				$type='MAX:HTML_VALUE:'.$Types[1];
			}
			if($Types[0]=='CondIBMinFilter')
			{
				if(!isset(self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MIN']['HTML_VALUE']))
					self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MIN']['HTML_VALUE']=self::$FilterResult['ITEMS'][$Types[1]]['VALUES']['MIN']['VALUE'];
				$type='MIN:HTML_VALUE:'.$Types[1];
			}


			//if [SORT] -> [ID]
			$FilterResult=self::$FilterResult;
			$idSection=-1;
			foreach($FilterResult['ITEMS'] as $key=>$val)
			{
				if(isset($val['ID']) && $val['ID']==$idSections)
				{
					$idSection=$key;
				}
				elseif(!isset($val['ID']))
				{
					$idSection=$idSections;
				}
			}
			if($idSection==-1)
				$idSection=$idSections;

			if($idSections=='CondIBSection')// if section
				$idSection='SECTION_ID';


			if($condition['DATA']['logic']=='Equal')
				array_push($MassCond, self::CheckElementsEqual($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='Not')
				array_push($MassCond, self::CheckElementsNot($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='Contain')
				array_push($MassCond, self::CheckElementsContain($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='NotCont')
				array_push($MassCond, self::CheckElementsNotCont($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='Great')
				array_push($MassCond, self::CheckElementsGreat($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='Less')
				array_push($MassCond, self::CheckElementsLess($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='EqGr')
				array_push($MassCond, self::CheckElementsEqGr($idSection,$idCondition,$type));
			if($condition['DATA']['logic']=='EqLs')
				array_push($MassCond, self::CheckElementsEqLs($idSection,$idCondition,$type));
		}                  
		return $MassCond;
	}
	private function ANDConditions($conditions)
	{                                   
		$return=0;
		if(in_array(0,$conditions))
			$return =0;
		else
			$return =1;
		return $return;
	}
	private function ORConditions($conditions)
	{
		$return=0;
		if(in_array(1,$conditions))
			$return =1;
		else
			$return =0;
		return $return;
	}
	private function ANDFalseConditions($conditions)
	{
		$return=0;
		foreach($conditions as $key=>$condition)
			if($key==0)
				if($condition==1)
					$return=1;
				else
					{$return=0;break;}
			else
				if($condition==0)
					$return=1;
				else
					{$return=0;break;}
		return $return;
	}
	private function ORFalseConditions($conditions)
	{                     
		$return=0;
		foreach($conditions as $key=>$condition)
			if($key==0)
				if($condition==1)
					{$return=1;break;}
				else
					$return=0;
			else
				if($condition==0)
					{$return=1;break;}
				else
					$return=0;
		return $return;
	}
	private function CheckElementsEqual($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type===0)
		{   
			if($idCondition=='' && isset($FilterResult['ITEMS'][$idSection]['VALUES']) && is_array($FilterResult['ITEMS'][$idSection]['VALUES']))
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					if(isset(self::$CheckerRule[$idSection]))
						foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
						{
							self::$CheckerRule[$idSection][$key_check]=0;
							}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			else
			{
				if(isset($FilterResult['ITEMS'][$idSection]['VALUES']) && is_array($FilterResult['ITEMS'][$idSection]['VALUES']))
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param){      
					if((is_int($idCondition) && $key==$idCondition) || $param['VALUE']==$idCondition || (isset($param['FACET_VALUE']) && $param['FACET_VALUE']==$idCondition) || ($idSection=='SECTION_ID' && $param['CONTROL_NAME_SEF']==$idCondition))
					{       
						if($key==='MIN' || $key==='MAX')
							continue;                   
						self::$CheckerRule[$idSection][$key]=0;                                                                                            
						if(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$key]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$key]['CHECKED']==1)
							return 1;
						else
							return 0;
						break;
					}
				}
			}
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && !empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]==$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function CheckElementsNot($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		$Check=0;
		if($type===0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=0;break;}
					else
						$return=1;
				}
			}
			else
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					if(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$key]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$key]['CHECKED']==1)
					{
						self::$CheckerRule[$idSection][$key]=0;
						$Check=1;
						if((is_int($idCondition) && $key==$idCondition) || $param['VALUE']==$idCondition || ($idSection=='SECTION_ID' && $param['CONTROL_NAME_SEF']==$idCondition))
							return 0;
					}
				}
				if($Check==1)
					return 1;
				else
					return 0;
			}
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(!isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) || empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]!=$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function CheckElementsContain($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type==0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			else
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					if(stripos($param['VALUE'],$idCondition)!==false && isset($param['CHECKED']) && $param['CHECKED']==1)
					{
						self::$CheckerRule[$idSection][$key]=0;$return=1;
					}
					elseif(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=0;break;}
				}
			}
		}
		return $return;
	}
	private function CheckElementsNotCont($idSection,$idCondition,$type=0)
	{
		$return=0;
		$CheckedElements='';
		$FilterResult=self::$FilterResult;
		if($type==0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			else
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
					{
						self::$CheckerRule[$idSection][$key]=0;
						$CheckedElements.=$param['VALUE'];
					}
				}
				if(stripos($CheckedElements,$idCondition)!==false || $CheckedElements=='')
					$return=0;
				else
					$return=1;
			}
		}
		return $return;
	}
	private function CheckElementsGreat($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type===0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			elseif(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']==1)
				{self::$CheckerRule[$idSection][$idCondition]=0;$return=1;}
			else
				$return=0;
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && !empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]>$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function CheckElementsLess($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type===0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			elseif(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']==1)
				{self::$CheckerRule[$idSection][$idCondition]=0;$return=1;}
			else
				$return=0;
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && !empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]<$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function CheckElementsEqGr($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type===0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			elseif(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']==1)
				{self::$CheckerRule[$idSection][$idCondition]=0;$return=1;}
			else
				$return=0;
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && !empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]>=$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function CheckElementsEqLs($idSection,$idCondition,$type=0)
	{
		$return=0;
		$FilterResult=self::$FilterResult;
		if($type===0)
		{
			if($idCondition=='')
			{
				foreach($FilterResult['ITEMS'][$idSection]['VALUES'] as $key=>$param)
				{
					foreach(self::$CheckerRule[$idSection] as $key_check=>$param_check)
					{
						self::$CheckerRule[$idSection][$key_check]=0;
					}
					if(isset($param['CHECKED']) && $param['CHECKED']==1)
						{$return=1;break;}
					else
						$return=0;
				}
			}
			elseif(isset($FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']) && $FilterResult['ITEMS'][$idSection]['VALUES'][$idCondition]['CHECKED']==1)
				{self::$CheckerRule[$idSection][$idCondition]=0;$return=1;}
			else
				$return=0;
		}
		else
		{
			$types=explode(':',$type);
			if($idCondition=='')
			{
				if(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && !empty($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]))
					$return=1;
				else
					$return=0;
			}
			elseif(isset($FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]) && $FilterResult['ITEMS'][$types[2]]['VALUES'][$types[0]][$types[1]]<=$idCondition)
				$return=1;
			else
				$return=0;
		}
		return $return;
	}
	private function UserFields($str,$SectionID,$IblockId)
	{
		preg_match_all('/\#(.+)\#/U', $str, $matches);
		if(isset($matches[0]) && is_array($matches[0]) && count($matches[0])>0)
		{
			$NeedFields=array();
			foreach($matches[0] as $UserField)
			{
				if (!array_key_exists($UserField, self::$UserFields)) {
					$NeedFields[]=str_replace('#', '', $UserField);
				}
			}
			if(count($NeedFields)>0)
			{
				$ar_result=CIBlockSection::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$IblockId, "ID"=>$SectionID),false, $NeedFields);
				if($res=$ar_result->GetNext()){
					foreach($NeedFields as $NeedField)
					{
						if(isset($res[$NeedField]))
							self::$UserFields['#'.$NeedField.'#']=$res[$NeedField];
					}
				}
			}
		}
		if(count(self::$UserFields)>0)
			$str=str_replace(array_keys(self::$UserFields), array_values(self::$UserFields), $str);

		return $str;
	}
}