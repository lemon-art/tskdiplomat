<?
require_once ($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');
use Bitrix\Main\Localization\Loc;
use Sotbit\Seometa\SitemapTable;
use Sotbit\Seometa\ConditionTable;
use Sotbit\Seometa\SeometaUrlTable;
use Bitrix\Main\Loader;
Loc::loadMessages( __FILE__ );

if (!$USER->CanDoOperation( 'sotbit.seometa' ))
{
    $APPLICATION->AuthForm( Loc::getMessage( "ACCESS_DENIED" ) );
}

Loader::includeModule( 'sotbit.seometa' );
$ID = intval( $_REQUEST['ID'] );
$arSitemap = null;
if ($ID > 0)
{
    $dbSitemap = SitemapTable::getById( $ID );
    $arSitemap = $dbSitemap->fetch();
}

if (!is_array( $arSitemap ))
{
    require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITEMAP_NOT_FOUND" ) );
    require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}
else
{
    $arSitemap['SETTINGS'] = unserialize( $arSitemap['SETTINGS'] );
}
$arSites = array();
$rsSites = CSite::GetById( $arSitemap['SITE_ID'] );
$arSite = $rsSites->Fetch();
$SiteUrl = "";
if (isset( $arSitemap['SETTINGS']['PROTO'] ) && $arSitemap['SETTINGS']['PROTO'] == 1)
{
    $SiteUrl .= 'https://';
}
elseif (isset( $arSitemap['SETTINGS']['PROTO'] ) && $arSitemap['SETTINGS']['PROTO'] == 0)
{
    $SiteUrl .= 'http://';
}
else
{
    require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND" ) );
    require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

if (isset( $arSitemap['SETTINGS']['DOMAIN'] ) && !empty( $arSitemap['SETTINGS']['DOMAIN'] ))
    $SiteUrl .= $arSitemap['SETTINGS']['DOMAIN'];
else
{
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND" ) );
}
if (isset( $arSitemap['SETTINGS']['FILENAME_INDEX'] ) && !empty( $arSitemap['SETTINGS']['FILENAME_INDEX'] ))
    $SiteMapUrl = $SiteUrl . '/' . $arSitemap['SETTINGS']['FILENAME_INDEX'];
else
{
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND" ) );
}
if (isset( $arSitemap['SETTINGS']['FILTER_TYPE'] ) && !is_null( $arSitemap['SETTINGS']['FILTER_TYPE'] ))
{
    $FilterType = key( $arSitemap['SETTINGS']['FILTER_TYPE'] );
    $FilterCHPU = $arSitemap['SETTINGS']['FILTER_TYPE'][$FilterType];
    $MASK = '';
    if ($FilterType == 'BITRIX' && $FilterCHPU)
    {
        $MASK = "#SECTION_URL#filter/#FILTER_PARAMS#apply/";
    }
    elseif ($FilterType == 'BITRIX' && !$FilterCHPU)
    {
        $MASK = "#SECTION_URL#?set_filter=y#FILTER_PARAMS#";
    }
    elseif ($FilterType == 'MISSSHOP' && $FilterCHPU)
    {
        $MASK = "#SECTION_URL#filter/#FILTER_PARAMS#apply/";
    }
}
else
{
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITEMAP_FILTER_TYPE_NOT_FOUND" ) );
}
if (file_exists( $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . $arSitemap['SETTINGS']['FILENAME_INDEX'] ))
{
    $FoundSeoMetaSitemap = false;
    $xml = simplexml_load_file( $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . 'sitemap.xml' );

    foreach ( $xml->sitemap as $sitemap ) // search if exist seometa sitemap in main sitemap
    {
        if (isset( $sitemap->loc ) && $sitemap->loc == $SiteUrl . '/sitemap_seometa_' . $ID . '.xml')
            $FoundSeoMetaSitemap = true;
    }
    if (!$FoundSeoMetaSitemap) // IF seometa sitemap not found add main sitemap
    {
        $NewSitemap = $xml->addChild( "sitemap" );
        $NewSitemap->addChild( "loc", $SiteUrl . '/sitemap_seometa_' . $ID . '.xml' );
        $NewSitemap->addChild( "lastmod", (isset($arSitemap['DATE_RUN']) && !empty($arSitemap['DATE_RUN']))?str_replace( ' ', 'T', date( 'Y-m-d H:i:sP', strtotime( $arSitemap['DATE_RUN'] ) ) ):str_replace( ' ', 'T', date( 'Y-m-d H:i:sP', strtotime( $arSitemap['TIMESTAMP_CHANGE'] ) ) ) );
        file_put_contents( $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . 'sitemap.xml', $xml->asXML() );
    }
    // START GENERATE XML ARRAY
    $rsCondition = ConditionTable::getList( array(
            'select' => array(
                    'ID',
                    'DATE_CHANGE',
                    'INFOBLOCK',
                    'STRONG',
                    'NO_INDEX',
                    'RULE',
                    'SITES',
                    'SECTIONS',
                    'PRIORITY',
                    'CHANGEFREQ',   
            ),
            'filter' => array(
                    'ACTIVE' => 'Y'
            ),
            'order' => array(
                    'ID' => 'asc'
            )
    ) );
    $CONDS = array(); // Array for XML content
    while ( $arCondition = $rsCondition->Fetch() )
    {
        $ConditionSites = unserialize( $arCondition['SITES'] );
        if (!in_array( $arSitemap['SITE_ID'], $ConditionSites )) // If condition not with this site
            continue;

        if($arCondition['NO_INDEX']=='Y')
            continue;


        $ConditionSections = unserialize( $arCondition['SECTIONS'] );
        if (!is_array( $ConditionSections ) || count( $ConditionSections ) < 1) // If dont check sections
        {
            $ConditionSections = array();
            $rsSections = CIBlockSection::GetList( array(
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

        if ($arCondition['STRONG'] != 'Y') // If condition not strong
            continue;


            // Get values
        $Rule = unserialize( $arCondition['RULE'] );


        if(isset($Rule['CHILDREN']['DATA']['value']) && empty($Rule['CHILDREN']['DATA']['value']) || (isset($Rule['CHILDREN'][0]['DATA']['value']) && empty($Rule['CHILDREN'][0]['DATA']['value'])))
            continue;

        $Pages = CSeoMetaSitemap::ParseArray( $Rule, $ConditionSections );

        CSeoMetaSitemap::SetListOfProps( $FilterType );
        $CONDS[$arCondition['ID']]['PAGES'] = CSeoMetaSitemap::SortPagesCodes( $Pages );
        $CONDS[$arCondition['ID']]['DATE_CHANGE'] = $arCondition['DATE_CHANGE'];
        $CONDS[$arCondition['ID']]['CHANGEFREQ'] = $arCondition['CHANGEFREQ']; 
        $CONDS[$arCondition['ID']]['PRIORITY'] = $arCondition['PRIORITY'];  
        $CONDS[$arCondition['ID']]['INFOBLOCK'] = $arCondition['INFOBLOCK'];       
        // Get sections path
        $rsSections = CIBlockSection::GetList( array(
                'ID' => 'ASC'
        ), array(
                'ID' => $ConditionSections
        ), false, array(
                'ID',
        ), false );
        $i = 0;
        while ( $arSection = $rsSections->Fetch() )
        {
            $CONDS[$arCondition['ID']]['SECTION'][] = $arSection;
        }
    }              
    
    $chpuAll = SeometaUrlTable::getAll();
                         
    // START GENERATE SITEMAP
    $urlset = new SimpleXMLElement("<urlset></urlset>");
    $urlset->addAttribute( "xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9" );

    foreach ( $CONDS as $IdCond => $CondVals )
    {
        foreach ( $CondVals['SECTION'] as $Section )
        {
            $SectionUrl="";
            $nav = CIBlockSection::GetNavChain(false,$Section['ID']);
            while($arSectionPath = $nav->GetNext()){
                $SectionUrl=$arSectionPath['SECTION_PAGE_URL'];
            }  

            foreach ( $CondVals['PAGES'] as $Page )
            {
                //$SectionUrl = str_replace( '#SITE_DIR#', $SiteUrl, $Section['SECTION_PAGE_URL'] );
                //$SectionUrl = str_replace( '#SECTION_CODE#', $Section['CODE'], $SectionUrl );       

                $FilterParams = '';

                $cond_properties = array();   
                foreach ( $Page as $CondKey => $CondValProps )
                {
                   // print_r($CondValProps);
                    if ($CondKey != "PRICES")
                    {
                        $k = 1;
                        if ($FilterType == 'MISSSHOP' && $FilterCHPU)
                        {
                            if(isset($CondValProps['CODE']) && !is_null($CondValProps['CODE'])) {
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
                            $FilterParams .= '/';
                        }
                        elseif ($FilterType == 'BITRIX' && $FilterCHPU)
                        {
                            if(isset($CondValProps['CODE']) && !is_null($CondValProps['CODE'])) {
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
                            $FilterParams .= '/';
                            $cond_properties[$key] = $values;   
                        }
                        elseif ($FilterType == 'BITRIX' && !$FilterCHPU)
                        {
                            $values = array();
                            foreach ( $CondValProps['BITRIX'][0] as $PropVal )
                            {
                                $FilterParams .= '&arrFilter_'.$CondValProps['PROPERTY_ID'].'_'.strtolower($PropVal).'=Y';  
                                $values[] = $PropVal;  
                            }
                            $cond_properties[$CondValProps['PROPERTY_ID']] = $values;  
                        }
                    }
                    else
                    {
                        if ($FilterType == 'MISSSHOP' && $FilterCHPU)
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
                                $FilterParams .= "price" . $PriceProps['ID'][0] . $ValMin . $ValMax .= "/";
                            }
                        }
                        elseif ($FilterType == 'BITRIX' && $FilterCHPU)
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
                                $FilterParams .= "price-" . strtolower($PriceCode) . $ValMin . $ValMax .= "/";
                            }
                        }
                        elseif ($FilterType == 'BITRIX' && !$FilterCHPU)
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
                                $cond_properties['PRICE'][$PriceCode]['FROM'] = $ValMin;
                                $cond_properties['PRICE'][$PriceCode]['TO'] = $ValMax;
                            }
                        }
                    }
                } 
                
                
                $arFilter = array(
                    'ACTIVE' => 'Y',        
                    'INCLUDE_SUBSECTIONS' => 'Y',      
                    'IBLOCK_ID' => $CondVals['INFOBLOCK'],
                    'SECTION_ID' => $Section['ID'], 
                );               
                
                   
                $LOC = str_replace( '#SECTION_URL#', $SectionUrl, $MASK );
                                   
                $LOC = str_replace( '#FILTER_PARAMS#', $FilterParams, $LOC );
                
                $url = SeometaUrlTable::getByRealUrl($LOC);
                if(!empty($url)&&isset($chpuAll[$url['ID']])){
                    $LOC = str_replace($url['REAL_URL'], $url['NEW_URL'], $LOC );  
                    unset($chpuAll[$url['ID']]);
                }
                
                
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
                
                if($count <= 0)
                    continue;      
                         
                $LOC = $SiteUrl.$LOC;
                
                if(substr($LOC, 0, 4)!='http')
                {
                    $LOC=$SiteUrl.$LOC;
                }                  
                $url = $urlset->addChild( "url" );
                $loc = $url->addChild( "loc", htmlentities($LOC) );
                $lastmod = $url->addChild( "lastmod", str_replace( ' ', 'T', date( 'Y-m-d H:i:sP', strtotime( $CondVals['DATE_CHANGE'] ) ) ) );
                if(isset($CondVals['CHANGEFREQ']) && !is_null($CondVals['CHANGEFREQ']))
                    $changefreq = $url->addChild( "changefreq", $CondVals['CHANGEFREQ'] );
                if(isset($CondVals['PRIORITY']) && !is_null($CondVals['PRIORITY']))
                    $priority = $url->addChild( "priority", $CondVals['PRIORITY']);
            }
        }
    }
      
    foreach($chpuAll as $chpu){             
        $LOC = $SiteUrl.$chpu['NEW_URL'];
        if(substr($LOC, 0, 4)!='http'){
            $LOC=$SiteUrl.$LOC;
        }
        
       $url = $urlset->addChild( "url" );
       $loc = $url->addChild( "loc", htmlentities($LOC) );
       $lastmod = $url->addChild( "lastmod", str_replace( ' ', 'T', date( 'Y-m-d H:i:sP', strtotime( $chpu['DATE_CHANGE'] ) ) ) ); 
    }        
    
    $urlset->asXML( $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . 'sitemap_seometa_' . $ID . '.xml' );
    SitemapTable::update($ID, array('DATE_RUN' => new Bitrix\Main\Type\DateTime()));
    ?>
    <script>
    top.BX.finishSitemap();
    </script>
    <?
}
else
{
    ShowError( Loc::getMessage( "SEO_META_ERROR_SITE_SITEMAP_NOT_FOUND" ) . ' ' . $arSite['ABS_DOC_ROOT'] . $arSite['DIR'] . $arSitemap['SETTINGS']['FILENAME_INDEX'] );
}
?>