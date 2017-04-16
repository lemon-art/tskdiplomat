<?
if (! defined ( "B_PROLOG_INCLUDED" ) || B_PROLOG_INCLUDED !== true)
	die ();
use Bitrix\Main\Loader;
use Bitrix\Main\Server;
use Sotbit\Seometa\SeometaUrlTable;
use Sotbit\Seometa\SeometaStatisticsTable;

class CSeoMetaEvents
{
	protected static $lAdmin;
	private static $i = 1;
	function OnInit()
	{
		return array (
				"TABSET" => "seometa",
				"GetTabs" => array (
						"CSeoMetaEvents",
						"GetTabs" 
				),
				"ShowTab" => array (
						"CSeoMetaEvents",
						"ShowTab" 
				),
				"Action" => array (
						"CSeoMetaEvents",
						"Action" 
				),
				"Check" => array (
						"CSeoMetaEvents",
						"Check" 
				) 
		);
	}
	function Action($arArgs)
	{
		return true;
	}
	function Check($arArgs)
	{
		return true;
	}
	function GetTabs($arArgs)
	{
		$arTabs = array (
				array (
						"DIV" => "url-mode",
						"TAB" => GetMessage ( 'seometa_title' ),
						"ICON" => "sale",
						"TITLE" => GetMessage ( 'seometa_list' ),
						"SORT" => 5 
				) 
		);
		return $arTabs;
	}
	function ShowTab($divName, $arArgs, $bVarsFromForm)
	{
		if ($divName == "url-mode")
		{
			define ( 'B_ADMIN_SUBCONDITIONS', 1 );
			define ( 'B_ADMIN_SUBCONDITIONS_LIST', false );
			?><tr id="tr_COUPONS">
	<td colspan="2"><?
			require ($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sotbit.seometa/admin/templates/sub_list.php');
			?></td>
</tr><?
		}
	}
	
    function PageStart()
	{
		global $APPLICATION;
		$context = Bitrix\Main\Context::getCurrent ();
		$server = $context->getServer ();
		$server_array = $server->toArray ();             
        $instance = Sotbit\Seometa\SeometaUrlTable::getByNewUrl( $context->getRequest()->getRequestUri() );
        if(!$instance)
            $instance = Sotbit\Seometa\SeometaUrlTable::getByNewUrl( $APPLICATION->GetCurPage());
		if (!$instance)
		{
			$instance = Sotbit\Seometa\SeometaUrlTable::getByRealUrl( $context->getRequest ()
				->getRequestUri () );                                                                      
			if ($instance && CSeoMetaEvents::$i)
			{
				CSeoMetaEvents::$i = 0;             
				LocalRedirect ( $instance['NEW_URL'], false, '301 Moved Permanently' );
			}
		}                        
		if ($instance && ($instance['NEW_URL'] != $instance['REAL_URL']))
		{
			$_SERVER['REQUEST_URI'] = $instance['REAL_URL'];
            $paramsUri = explode('?',$instance['REAL_URL']);
            $paramsUri = explode('&',$paramsUri[1]);  
            foreach($paramsUri as $p){
                $arr = explode('=',$p);
                $_REQUEST[$arr[0]] = $arr[1];
            }
			$server_array['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
			$server->set ( $server_array );
			$context->initialize ( new Bitrix\Main\HttpRequest ( $server, array (), array (), array (), $_COOKIE ), $context->getResponse (), $server );
			$APPLICATION->reinitPath ();
			CSeoMetaEvents::$i = 0;
		}
	}
    
    /* 
    * It is necessary to include processing of outdated events in settings of an e-commerce shop
    */
    function OrderAdd($ID, $arFields){   
        global $APPLICATION;                                            
        $cookie = $APPLICATION->get_cookie("sotbit_seometa_statistic");                                            
        echo $cookie; 
        if(!empty($cookie) && $cookie==bitrix_sessid()&&SeometaStatisticsTable::getBySessId($cookie)){  
            $stat = SeometaStatisticsTable::getBySessId($cookie); 
            $stat['ORDER_ID'] = intval($ID);
            SeometaStatisticsTable::update($stat['ID'],$stat);
            
        }                                
    }
}  