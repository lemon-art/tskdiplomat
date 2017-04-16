<?
namespace Sotbit\Seometa;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc; 
use Bitrix\Main\Loader;
use Bitrix\Main\Type;
Loc::loadMessages(__FILE__);
require_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sotbit.seometa/classes/general/seometa_sitemap.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/iblock/classes/general/iblocksection.php';
class ConditionTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sotbit_seometa';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new Entity\StringField('NAME', array(
				'required' => true,
				'title' => Loc::getMessage('SEOMETA_NAME'),
			)),
			new Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SEOMETA_ACTIVE'),
			)),
			new Entity\IntegerField('SORT', array(
				'required' => true,
				'title' => Loc::getMessage('SEOMETA_SORT'),
			)),
			new Entity\DatetimeField('DATE_CHANGE', array(
				'title' => Loc::getMessage('SEOMETA_DATE_CHANGE'),
			)),
			new Entity\TextField('SITES', array(
				'title' => Loc::getMessage('SEOMETA_SITES'),
			)),
			new Entity\StringField('TYPE_OF_CONDITION', array(
				'title' => Loc::getMessage('SEOMETA_TYPE_OF_CONDITION'),
			)),   
            new Entity\StringField('FILTER_TYPE', array(
                'title' => Loc::getMessage('SEOMETA_TYPE_OF_FILTER_TYPE'),
            )),   
			new Entity\StringField('TYPE_OF_INFOBLOCK', array(
				'title' => Loc::getMessage('SEOMETA_TYPE_OF_INFOBLOCK'),
			)),
			new Entity\StringField('INFOBLOCK', array(
				'title' => Loc::getMessage('SEOMETA_INFOBLOCK'),
			)),
			new Entity\StringField('SECTIONS', array(
				'title' => Loc::getMessage('SEOMETA_SECTIONS'),
			)),
			new Entity\TextField('RULE', array(
				'title' => Loc::getMessage('SEOMETA_RULE'),
			)),
			new Entity\StringField('META', array(
				'title' => Loc::getMessage('SEOMETA_META'),
			)),
			new Entity\BooleanField('NO_INDEX', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SEOMETA_NO_INDEX'),
			)),
			new Entity\BooleanField('STRONG', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SEOMETA_STRONG'),
			)),
			new Entity\FloatField('PRIORITY', array(
					'title' => Loc::getMessage('SEOMETA_PRIORITY'),
			)),
			new Entity\FloatField('CHANGEFREQ', array(
					'title' => Loc::getMessage('SEOMETA_CHANGEFREQ'),
			)),
			new Entity\IntegerField('CATEGORY_ID', array(
					'required' => true,
					'title' => Loc::getMessage('SEOMETA_CATEGORY_ID'),
			)),    
		);
	} 
    
    public static function generateUrlForCondition($id){
      if($id==0)
        return array();  
      SeometaUrlTable::deleteByConditionId($id);
      $arCondition = self::getById($id)->fetch();
      $FilterType = $arCondition['FILTER_TYPE'];
      if ($FilterType == 'bitrix_chpu')
      {
        $MASK = "#SECTION_URL#filter/#FILTER_PARAMS#apply/";
      } elseif ($FilterType == 'bitrix_not_chpu') {
        $MASK = "#SECTION_URL#?set_filter=y#FILTER_PARAMS#";
      } elseif ($FilterType == 'misshop_chpu') {
        $MASK = "#SECTION_URL#filter/#FILTER_PARAMS#apply/";
      }
      $res = array(); 
      
      Loader::includeModule('iblock');
      
       $ConditionSections = unserialize( $arCondition['SECTIONS'] );
        if (!is_array( $ConditionSections ) || count( $ConditionSections ) < 1) // If dont check sections
        {
            $ConditionSections = array();
            $rsSections = \CIBlockSection::GetList( array(
                    'SORT' => 'ASC'
            ), array(
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $arCondition['INFOBLOCK']
            ), false, array(
                    'ID'
            ) );
            while ( $arSection = $rsSections->GetNext() )
            {
                $ConditionSections[] = $arSection['ID'];
            }
        }                                              
        $Rule = unserialize( $arCondition['RULE'] );
        $template = unserialize($arCondition['META']);
        $template = $template['TEMPLATE_NEW_URL'];
        preg_match("/{(.*?)}/",$template,$template_prop);
        $template = str_replace($template_prop[0],'#PROPERTIES#',$template);   
        $template1 = $template;
        $template_prop = trim($template_prop[0],'{');
        $template_prop = trim($template_prop,'}');
        $template_prop = explode(':',$template_prop);
        $arCond = explode(':', $Rule['CHILDREN'][0]["CLASS_ID"]);
        if(!$arCond)
            $arCond = explode(':', $Rule['CHILDREN']["CLASS_ID"]);
        $IdProperty = $arCond[2]; 
        $IdIblock = $arCond[1];
        $property = \CIBlockProperty::GetByID($IdProperty,$IdIblock)->fetch();      
        if(isset($Rule['CHILDREN']['DATA']['value']) && empty($Rule['CHILDREN']['DATA']['value'])&&($property['PROPERTY_TYPE']=='E' || $property['PROPERTY_TYPE']=='L') || (isset($Rule['CHILDREN'][0]['DATA']['value']) && empty($Rule['CHILDREN'][0]['DATA']['value'])&&($property['PROPERTY_TYPE']=='E' || $property['PROPERTY_TYPE']=='L')))
            return;                                      

        $Pages = \CSeoMetaSitemap::ParseArray( $Rule, $ConditionSections );    
        // Get sections path
        $rsSections = \CIBlockSection::GetList( array(
                    'ID' => 'ASC'
                ), array(
                    'ID' => $ConditionSections
                ), false, array(
                    'ID',
                ), 
                false );
        $i = 0;
        while ( $arSection = $rsSections->Fetch() )
        {
            $CondVals['SECTION'][] = $arSection;
        }
        \CSeoMetaSitemap::SetListOfProps($FilterType);
        $CondVals['PAGES'] = \CSeoMetaSitemap::SortPagesCodes( $Pages );                  
        foreach ( $CondVals['SECTION'] as $Section )
        {
            $SectionUrl="";
            $nav = \CIBlockSection::GetNavChain(false,$Section['ID']);
            while($arSectionPath = $nav->GetNext()){ 
                $SectionUrl=$arSectionPath['SECTION_PAGE_URL'];
            }  
            
            $section = \CIBlockSection::getById($Section['ID'])->fetch();
            $sectname = $section['NAME'];   
            $template = $template1;    
            $template = str_replace('#SECTION_ID#',$section['ID'],$template);
            $template = str_replace('#SECTION_CODE#',$section['CODE'],$template);             
            
            foreach ( $CondVals['PAGES'] as $Page )
            {   
            
                $new_url_template = $template;               
                                                                                               
                $FilterParams = '';                         
                                
                $name = $sectname;
                $props_templ = array();
                $cond_properties = array();   
                foreach ( $Page as $CondKey => $CondValProps )
                {                            
                    $prop_url = '';                            
                    if(isset($CondValProps['CODE']) && !is_null($CondValProps['CODE'])) {
                        $prop = \CIBlockProperty::GetList( array(
                                    "SORT" => "ASC",
                                    'ID' => 'ASC'
                                    ), array(
                                    "IBLOCK_ID" => $arCondition['INFOBLOCK'],
                                    "CODE"=>$CondValProps['CODE'],
                                    "ACTIVE" => "Y"
                                ))->fetch();  
                        $CondValProps['PROPERTY_ID'] = $prop['ID'];    
                        $name .= ' '.strtolower($prop['NAME']);
                        $prop_url = str_replace('#PROPERTY_CODE#',$prop['CODE'],$template_prop[0]);                             
                        $prop_url = str_replace('#PROPERTY_ID#',$prop['ID'],$prop_url);    
                    }                                               
                    if ($CondKey != "PRICES")
                    {
                        $k = 1;
                        if ($FilterType == 'misshop_chpu')
                        {
                            if(isset($CondValProps['CODE']) && !is_null($CondValProps['CODE'])){
                                $key = $CondValProps['CODE'];
                                $FilterParams .= strtolower( $CondValProps['CODE'] ) . '-';                               
                            } else {
                                $key = $CondKey;
                                $FilterParams .=$CondKey. '-';    
                            }                      
                            $CntCondValProps = count( $CondValProps['MISSSHOP'][1] );
                            $values = array();    
                            foreach ( $CondValProps['MISSSHOP'][1] as $PropVal )
                            {                        
                                if ($k == $CntCondValProps)
                                {
                                    $FilterParams .= $PropVal;                       
                                    $values[] = $PropVal;                    
                                }
                                else
                                {
                                    $FilterParams .= $PropVal . '-or-';                 
                                    $values[] = $PropVal;                                 
                                }                      
                                ++$k;
                            }    
                            $cond_properties[$key] = $values;
                            $values = implode($template_prop[2],$values);       
                            $prop_url = str_replace('#PROPERTY_VALUE#',$values,$prop_url);  
                            $FilterParams .= '/';
                        }
                        elseif ($FilterType == 'bitrix_chpu')
                        {
                            if(isset($CondValProps['CODE']) && !is_null($CondValProps['CODE'])){
                                $key = $CondValProps['CODE'];
                                $FilterParams .= strtolower( $CondValProps['CODE'] ) . '-is-';                              
                            } else {
                                $key = $CondKey;
                                $FilterParams .=$CondKey. '-is-';                                          
                            }                               
                            $CntCondValProps = count( $CondValProps['MISSSHOP'][0] );
                            $values = array(); 
                            foreach ( $CondValProps['BITRIX'][1] as $PropVal )
                            {
                                if ($k == $CntCondValProps)
                                {
                                    $FilterParams .= strtolower($PropVal);                      
                                    $values[] = $PropVal;                                          
                                }
                                else
                                {
                                    $FilterParams .= $PropVal . '-or-';                     
                                    $values[] = $PropVal;                                        
                                }            
                                ++$k;
                            }    
                            $cond_properties[$key] = $values;       
                            $values = implode($template_prop[2],$values);
                            $prop_url = str_replace('#PROPERTY_VALUE#',$values,$prop_url);  
                            $FilterParams .= '/';
                        }
                        elseif ($FilterType == 'bitrix_not_chpu')
                        {
                            $values = array();
                            foreach ( $CondValProps['BITRIX'][0] as $PropVal )
                            {
                                $FilterParams .= '&arrFilter_'.$CondValProps['PROPERTY_ID'].'_'.strtolower($PropVal).'=Y';   
                                $values[] = $PropVal;                                                          
                            }   
                            $cond_properties[$CondValProps['PROPERTY_ID']] = $values;                                      
                            $values = implode($template_prop[1],$values);
                            $prop_url = str_replace('#PROPERTY_VALUE#',$values,$prop_url);
                        }
                    }
                    else
                    {
                        $prices = '';
                        if ($FilterType == 'misshop_chpu')
                        {
                            foreach ( $CondValProps as $PriceCode => $PriceProps )
                            {
                                $ValMin = "";
                                $ValMax = "";
                                foreach ( $PriceProps['TYPE'] as $j => $Type )
                                {
                                    if ($Type == 'MIN')
                                        $ValMin = "-from-" . $PriceProps['VALUE'][$j];
                                    if ($Type == 'MAX')
                                        $ValMax = "-to-" . $PriceProps['VALUE'][$j];
                                }
                                $cond_properties['PRICE'][$PriceCode]['FROM'] = $ValMin;
                                $cond_properties['PRICE'][$PriceCode]['TO'] = $ValMax;
                                $prices .= "price" . $ValMin . $ValMax;
                                $FilterParams .= "price" . $PriceProps['ID'][0] . $ValMin . $ValMax .= "/";    
                            }
                        }
                        elseif ($FilterType == 'bitrix_chpu')
                        {
                            foreach ( $CondValProps as $PriceCode => $PriceProps )
                            {
                                $ValMin = "";
                                $ValMax = "";
                                foreach ( $PriceProps['TYPE'] as $j => $Type )
                                {
                                    if ($Type == 'MIN')
                                        $ValMin = "-from-" . $PriceProps['VALUE'][$j];
                                        if ($Type == 'MAX')
                                            $ValMax = "-to-" . $PriceProps['VALUE'][$j];
                                }
                                $cond_properties['PRICE'][$PriceCode]['FROM'] = $ValMin;
                                $cond_properties['PRICE'][$PriceCode]['TO'] = $ValMax;
                                $prices .= "price" . $ValMin . $ValMax;
                                $FilterParams .= "price-" . strtolower($PriceCode) . $ValMin . $ValMax .= "/";   
                            }
                        }
                        elseif ($FilterType == 'bitrix_not_chpu')
                        {
                            foreach ( $CondValProps as $PriceCode => $PriceProps )
                            {
                                $ValMin = "";
                                $ValMax = "";
                                foreach ( $PriceProps['TYPE'] as $j => $Type )
                                {
                                    if ($Type == 'MIN')
                                        $ValMin = "_MIN=" . $PriceProps['VALUE'][$j];
                                        if ($Type == 'MAX')
                                            $ValMax = "_MAX=" . $PriceProps['VALUE'][$j];
                                }
                                if(isset($ValMin)&&$ValMin!="")
                                {
                                    $FilterParams .= "&arrFilter_P" . $PriceProps['ID'][0] . $ValMin;  
                                }
                                if(isset($ValMax)&&$ValMax!="")
                                {
                                    $FilterParams .= "&arrFilter_P" . $PriceProps['ID'][0] . $ValMax;   
                                }
                                $prices .= "price" . $ValMin . $ValMax;
                                $cond_properties['PRICE'][$PriceCode]['FROM'] = $ValMin;
                                $cond_properties['PRICE'][$PriceCode]['TO'] = $ValMax;
                            }
                        }
                    }              
                    $props_templ[] = $prop_url;                             
                }                       
                $LOC = str_replace( '#SECTION_URL#', $SectionUrl, $MASK );
                                   
                $LOC = str_replace( '#FILTER_PARAMS#', $FilterParams, $LOC );
                         
                $LOC = $SiteUrl.$LOC;                                    
                
                if(substr($LOC, 0, 4)!='http')
                {
                    $LOC=$SiteUrl.$LOC;                             
                }                                       
                $prop_url = implode($template_prop[1],$props_templ);     
                $new_url_template = str_replace('#PROPERTIES#',$prop_url,$new_url_template);    
                $new_url_template = str_replace('#PRICES#',$prices,$new_url_template);   
                
                $arFilter = array(
                    'ACTIVE' => 'Y',        
                    'INCLUDE_SUBSECTIONS' => 'Y',        
                    'IBLOCK_ID' => $arCondition['INFOBLOCK'],
                    'SECTION_ID' => $section['ID'], 
                );
                foreach($cond_properties as $code => $vals){
                    if($code!='PRICE'){      
                        if(intval($code))
                            $pr = \CIBlockProperty::GetList(array(), array('ID'=>$code))->fetch();
                        else
                            $pr = \CIBlockProperty::GetList(array(), array('CODE'=>$code))->fetch();
                        if($pr['PROPERTY_TYPE']!='L' && $pr['PROPERTY_TYPE']!='E')
                            $arFilter['PROPERTY_'.$pr['ID']] = $vals;
                        else 
                            $arFilter['PROPERTY_'.$pr['ID'].'_VALUE'] = $vals;
                    } else {
                        foreach($vals as $price_code => $price)
                            if(isset($price['FROM']) && $price['FROM']!=='')
                                $arFilter['>=CATALOG_PRICE_'.$price_code] = $price['FROM'];
                            if(isset($price['TO']) && $price['TO']!=='')
                                $arFilter['<=CATALOG_PRICE_'.$price_code] = $price['TO'];
                    }
                }                        
                $count = 0;
                $count = \CIBlockElement::GetList(array(),$arFilter)->SelectedRowsCount();       
                     
                $res1['real_url'] = $LOC;
                $res1['new_url'] = strtolower($new_url_template);  
                $res1['section_id'] = $section['ID'];
                $res1['name'] = $name;
                $res1['properties'] = $cond_properties;
                $res1['product_count'] = $count;
                $res[] = $res1;       
            }
        }
        $result = array();
        foreach($res as $url){
            $chpu = SeometaUrlTable::getByRealUrlGenerate($url['real_url']);          
            if($chpu) {
                $result[$chpu['ID']] = $chpu;    
            } else {
                $chpu['CONDITION_ID'] = $id;
                $chpu['REAL_URL'] = $url['real_url'];
                $chpu['ACTIVE'] = 'N';
                $chpu['NAME'] = $url['name'];
                $chpu['NEW_URL'] = $url['new_url'];
                $chpu['CATEGORY_ID'] = 0;
                $chpu['DATE_CHANGE'] = new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' );    
                $chpu['iblock_id'] = $arCondition['INFOBLOCK'];            
                $chpu['section_id'] = $url['section_id'];            
                $chpu['PROPERTIES'] = serialize($url['properties']);            
                $chpu['PRODUCT_COUNT'] = $url['product_count'];            
                $new_id = SeometaUrlTable::add($chpu);
                if ($new_id->isSuccess()){
                    $new_id = $new_id->getId();
                    $result[$new_id] = $chpu;
                } else {                                     
                }                
            }
        }                                                         
        return $result;      
    }
}
