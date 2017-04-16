<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Loader;
use Bitrix\Iblock\Template;
use Bitrix\Main\Config\Option;  
use Sotbit\Seometa\SeometaUrlTable;  
use Sotbit\Seometa\SeometaStatisticsTable;  
if(!Loader::includeModule('sotbit.seometa') || !Loader::includeModule('iblock'))
{
	return false;
}             
global $sotbitSeoMetaTitle;//Meta title
global $sotbitSeoMetaKeywords;//Meta keywords
global $sotbitSeoMetaDescription;//Meta description
global $sotbitFilterResult;//Filter result
global $sotbitSeoMetaH1;//for set h1
global $sotbitSeoMetaBottomDesc;//for set bottom description
global $sotbitSeoMetaTopDesc;//for set top description
global $sotbitSeoMetaAddDesc;//for set additional description
global $sotbitSeoMetaBreadcrumbLink;
global $sotbitSeoMetaBreadcrumbTitle;
global ${$arParams['FILTER_NAME']};       

if(Option::get("sotbit.seometa", "NO_INDEX_".SITE_ID,"N")!="N")
{
	$APPLICATION->SetPageProperty("robots", 'noindex, nofollow');
}

$url = SeometaUrlTable::getByRealUrl($APPLICATION->GetCurPage()); 
if(!$url)
    $url = SeometaUrlTable::getByRealUrl(preg_replace('/index.php$/','',$APPLICATION->GetCurPage()));
if(!empty($url) && !empty($url['NEW_URL'])){
    $APPLICATION->SetCurPage($url['NEW_URL']);    
}  

CSeoMeta::SetFilterResult($sotbitFilterResult,$arParams['SECTION_ID']);//filter result for class
CSeoMeta::AddAdditionalFilterResults(${$arParams['FILTER_NAME']});
CSeoMeta::FilterCheck();

if($this->StartResultCache(($arParams["CACHE_TIME"]? $arParams["CACHE_TIME"]: false), ($arParams["CACHE_GROUPS"]? $USER->GetGroups(): false)))
{                           
	$arResult=CSeoMeta::getRules($arParams);//list of conditions for current section
	$this->endResultCache();
}
$COND=array();
foreach($arResult as $key=>$condition){//get conditions and metatags
    $condition_id = $condition['ID'];       
	$COND[$key]['RULES']=unserialize($condition['RULE']);
	$COND[$key]['META']=unserialize($condition['META']);      
    $COND[$key]['ID']=$condition['ID'];
	$COND[$key]['NO_INDEX']=$condition['NO_INDEX'];
	$COND[$key]['STRONG']=$condition['STRONG'];
}   
$issetCondition = false;
foreach($COND as $rule)//get metatags if condition true
{
	$results[]=CSeoMeta::SetMetaCondition($rule,$arParams['SECTION_ID'],$condition['INFOBLOCK']);                     
	foreach($results as $result)//set metatags
	{             
		//INDEX
		if(isset($result['NO_INDEX']) && $result['NO_INDEX']=='Y')
		{
			$APPLICATION->SetPageProperty("robots", 'noindex, nofollow');
		}
		if(isset($result['NO_INDEX']) && $result['NO_INDEX']=='N')
		{
			$APPLICATION->SetPageProperty("robots", 'index, follow');   
		}

		$sku = new \Bitrix\Iblock\Template\Entity\Section($arParams['SECTION_ID']);
                                              
		if(isset($result['TITLE']) && !empty($result['TITLE']))
		{
			$sotbitSeoMetaTitle=\Bitrix\Iblock\Template\Engine::process( $sku, $result['TITLE'] );
			$APPLICATION->SetPageProperty("title", $sotbitSeoMetaTitle);
            $issetCondition = true;
		}
		if(isset($result['KEYWORDS']) && !empty($result['KEYWORDS']))
		{
			$sotbitSeoMetaKeywords=\Bitrix\Iblock\Template\Engine::process( $sku, $result['KEYWORDS'] );
			$APPLICATION->SetPageProperty("keywords", $sotbitSeoMetaKeywords);
            $issetCondition = true;
		}
		if(isset($result['DESCRIPTION']) && !empty($result['DESCRIPTION']))
		{
			$sotbitSeoMetaDescription=\Bitrix\Iblock\Template\Engine::process( $sku, $result['DESCRIPTION'] );
			$APPLICATION->SetPageProperty("description", $sotbitSeoMetaDescription);
            $issetCondition = true;
		}
		if(isset($result['PAGE_TITLE']) && !empty($result['PAGE_TITLE']))
		{
			$sotbitSeoMetaH1=\Bitrix\Iblock\Template\Engine::process( $sku, $result['PAGE_TITLE'] );
			if(isset($sotbitSeoMetaH1) && !empty($sotbitSeoMetaH1))
				$arResult['ELEMENT_H1']=$sotbitSeoMetaH1;
			$APPLICATION->SetTitle($sotbitSeoMetaH1);
            $issetCondition = true;
		}
		if(isset($result['BREADCRUMB_TITLE']) && !empty($result['BREADCRUMB_TITLE']))
		{
			$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
			$url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
			$url .= $_SERVER["REQUEST_URI"];
			$sotbitSeoMetaBreadcrumbLink=$url;
			$sotbitSeoMetaBreadcrumbTitle=\Bitrix\Iblock\Template\Engine::process( $sku, $result['BREADCRUMB_TITLE'] );
			if(isset($sotbitSeoMetaBreadcrumbLink) && !empty($sotbitSeoMetaBreadcrumbLink))
			{
				$arResult['BREADCRUMB_TITLE']=$sotbitSeoMetaBreadcrumbTitle;
				$arResult['BREADCRUMB_LINK']=$url;
			}
            $issetCondition = true;
		}
		if(isset($result['ELEMENT_TOP_DESC']) && !empty($result['ELEMENT_TOP_DESC']))
		{
			$sotbitSeoMetaTopDesc=\Bitrix\Iblock\Template\Engine::process( $sku, html_entity_decode($result['ELEMENT_TOP_DESC'] ));
			if(isset($sotbitSeoMetaTopDesc) && !empty($sotbitSeoMetaTopDesc))
				$arResult['ELEMENT_TOP_DESC']=$sotbitSeoMetaTopDesc;
            $issetCondition = true;
		}
		if(isset($result['ELEMENT_BOTTOM_DESC']) && !empty($result['ELEMENT_BOTTOM_DESC']))
		{
			$sotbitSeoMetaBottomDesc=\Bitrix\Iblock\Template\Engine::process( $sku, html_entity_decode($result['ELEMENT_BOTTOM_DESC'] ));
			if(isset($sotbitSeoMetaBottomDesc) && !empty($sotbitSeoMetaBottomDesc))
				$arResult['ELEMENT_BOTTOM_DESC']=$sotbitSeoMetaBottomDesc;
            $issetCondition = true;
		}
		if(isset($result['ELEMENT_ADD_DESC']) && !empty($result['ELEMENT_ADD_DESC']))
		{
			$sotbitSeoMetaAddDesc=\Bitrix\Iblock\Template\Engine::process( $sku, html_entity_decode($result['ELEMENT_ADD_DESC'] ));
			if(isset($sotbitSeoMetaAddDesc) && !empty($sotbitSeoMetaAddDesc))
				$arResult['ELEMENT_ADD_DESC']=$sotbitSeoMetaAddDesc;
            $issetCondition = true;
		}
	}
}  
                                                                            
if($issetCondition){ 
    CJSCore::Init(array("jquery"));                   
    $APPLICATION->AddHeadScript("/bitrix/components/sotbit/seo.meta/js/stat.js");                    
}      
$this->IncludeComponentTemplate();            
?>