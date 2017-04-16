<?php
/* MySQL */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fcm.wishlist/classes/general/wishlist.php");
class CFcmWishlist extends CAllFcmWishlist{
    
        function Add($arFields = array()){
            
            global $DB;
            
            if(!self::CheckFields()){
                return false;
            }
            
/***************** Event onBeforeVoteAdd ***************************/
		foreach (GetModuleEvents("fcm.wishlist", "onBeforeWishlistElementAdd", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
            
                $arFields["~TIMESTAMP_X"] = $DB->GetNowFunction();
                
                $ID = $DB->Add("b_fcm_wishlist", $arFields);

/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("fcm.wishlist", "onAfterWishlistElementAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
        }
    
        function GetList($arOrder = array('ID' => 'ASC'), $arFilter = array()){
		global $DB;
		$err_mess = (CFcmWishlist::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		foreach ($arFilter as $key => $val)
		{
			if (empty($val) || (is_string($val) && $val === "NOT_REF")): 
				continue;
			endif;
			$key = strtoupper($key);
			switch($key)
			{
				case "ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.ID", $val, $match);
					break;
				case "USER_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.USER_ID", $val, $match);
					break;
				case "USER_TYPE":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.USER_TYPE", $val, $match);
					break;
				case "ELEMENT_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.ELEMENT_ID", $val, $match);
					break;
				case "IBLOCK_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.IBLOCK_ID", $val, $match);
					break;
				case "SITE_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.SITE_ID", $val, $match);
					break;
				case "ACTIVE":
					$arSqlSearch[] = "V.ACTIVE = '".($val == "Y" ? "Y" : "N")."'";
					break;
			}
		}
                /* prepare sort fields */
		$arSqlOrder = Array();
		if(is_array($arOrder))
		{
			foreach($arOrder as $by=>$order)
			{
				$by = strtolower($by);
				$order = strtolower($order);
				if ($order!="asc")
					$order = "desc";

				if ($by == "id") $arSqlOrder[$by] = " V.ID ".$order." ";
				elseif ($by == "user_id") $arSqlOrder[$by] = " V.USER_ID ".$order." ";
				elseif ($by == "iblock_id") $arSqlOrder[$by] = " V.IBLOCK_ID ".$order." ";
				elseif ($by == "element_id") $arSqlOrder[$by] = " V.ELEMENT_ID ".$order." ";
				elseif ($by == "active") $arSqlOrder[$by] = " V.ACTIVE ".$order." ";
				elseif ($by == "timestamp_x") $arSqlOrder[$by] = " V.TIMESTAMP_X ".$order." ";
				elseif ($by == "site_id") $arSqlOrder[$by] = " V.SITE_ID ".$order." ";
				else
				{
					$by = "timestamp_x";
					$arSqlOrder[$by] = " V.TIMESTAMP_X ".$order." ";
				}
			}
		}

		if(count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);
		else
			$strSqlOrder = "";                
                
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT V.* 
				,".$DB->DateToCharFunction("V.TIMESTAMP_X")." TIMESTAMP_X
			FROM 
                            b_fcm_wishlist V ".
                        "WHERE ".
                        $strSqlSearch." ".
			$strSqlOrder;
                //trace($strSql);
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
                
		return $res;
            
        }
        
        function Delete($ID = false){
            if(intval($ID) <= 0){
                return false;
            }
            
            global $DB;
            
            $strSql = "DELETE FROM b_fcm_wishlist WHERE ID = ".intval($ID)." LIMIT 1";
                //trace($strSql);
            
/***************** Event onBefore ***************************/
		foreach (GetModuleEvents("fcm.wishlist", "onBeforeWishlistElementDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
/***************** /Event ******************************************/            
            
            if(!$DB->Query($strSql, false, $err_mess.__LINE__)){
                return false;
            }
/***************** Event onAfter ***************************/
            foreach(GetModuleEvents("fcm.wishlist", "OnAfterWishlistElementDelete", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($ID));
/***************** /Event ******************************************/            

            
            return true;
        }
}