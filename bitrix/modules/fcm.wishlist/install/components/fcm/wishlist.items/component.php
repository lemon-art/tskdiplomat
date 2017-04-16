<?

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if (!isset($arParams["CACHE_TIME"])) {
    $arParams["CACHE_TIME"] = 3600;
}

$arParams["WISHLIST_URL"] = trim($arParams["WISHLIST_URL"]);
if ($arParams["WISHLIST_URL"] === '')
    $arParams["WISHLIST_URL"] = "/personal/wishlist.php";

$arParams["ACTION_VARIABLE"] = trim($arParams["ACTION_VARIABLE"]);
if ($arParams["ACTION_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
    $arParams["ACTION_VARIABLE"] = "action";

$arParams["PRODUCT_ID_VARIABLE"] = trim($arParams["PRODUCT_ID_VARIABLE"]);
if ($arParams["PRODUCT_ID_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
    $arParams["PRODUCT_ID_VARIABLE"] = "id";

CPageOption::SetOptionString("main", "nav_page_in_session", "N");


if (!isset($arParams["ITEMS_LIMIT"])) {
    $arParams["ITEMS_LIMIT"] = 10;
}


if ($arParams["ITEMS_LIMIT"] > 0) {
    $arNavParams = array(
        "nPageSize" => $arParams["ITEMS_LIMIT"],
    );
}

global $USER;
//TODO только авторизованным или SALE_FUSER
if (Loader::includeModule("sale")) {
    $UserID = CSaleBasket::GetBasketUserID();
    $UserTYPE = 'S';
} elseif ($USER->IzAuthorized()) {
    $UserID = $USER->GetID();
    $UserTYPE = 'M';
} else {
    ShowError(GetMessage('ERROR_CANT_USER_IDENTIFY'));
    return;
}
/* * ***********************************************************************
  Processing of the action link
 * *********************************************************************** */
$strError = '';
$successfull = true;

if (isset($_REQUEST[$arParams["ACTION_VARIABLE"]]) && isset($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]])) {
    
    $action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
    $productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
    $addByAjax = isset($_REQUEST['ajax_wishlist']) && 'Y' == $_REQUEST['ajax_wishlist'];
    
    if (!Loader::includeModule("fcm.wishlist") || !Loader::includeModule("iblock")) {
        
                $strError = GetMessage("WISHLIST_MODULES_NOT_LOADET");
                $successfull = false;
            }
    echo 'module loadet';    
    if($successfull){
        

    $rs = CFcmWishlist::GetList(array('ELEMENT_ID' => 'ASC'), array('USER_ID' => $UserID, 'USER_TYPE' => $UserTYPE));

        while ($ar = $rs->Fetch()) {
            $arItems[$ar['ID']] = $ar['ELEMENT_ID'];
        }
        
    //trace($arItems);
    
        //ADD TO WISHLIST

        if ($action == "ADD" && $productID > 0) {
            
            $arFields = array(
                'USER_ID' => $UserID,
                'TYPE' => $UserTYPE,
                'SITE_ID' => SITE_ID,
            );

                //TODO validate of available IBlocks
                $rsProduct = CIBlockElement::GetByID($productID);

                if ($arProduct = $rsProduct->GetNext()) {
                    $arFields['IBLOCK_ID'] = intval($arProduct['IBLOCK_ID']);
                    $arFields['ELEMENT_ID'] = intval($arProduct['ID']);
                    unset($arProduct);
                } else {
                    $strError = GetMessage('WISHLIST_PRODUCT_NOT_FOUND');
                    $successfull = false;
                }
                
                if ($successfull && !in_array($productID, $arItems)) {
                    if (!$ID = CFcmWishlist::Add($arFields)) {

                        if ($ex = $APPLICATION->GetException())
                            $strError = $ex->GetString();
                        else
                            $strError = GetMessage("WISHLIST_ERROR2WISHLIST");
                        $successfull = false;
                    }else {
                        $arItems[$ID] = $productID;
                    }
                }
            }
            
        
        //DELETE from WISHLIST
        if ($action == "DEL" && $productID > 0) {
                foreach ($arItems as $key => $val) {
                    if ($val == $productID) {

                        if (!CFcmWishlist::Delete($key)) {
                            if ($ex = $APPLICATION->GetException())
                                $strError = $ex->GetString();
                            else
                                $strError = GetMessage("WISHLIST_ERRORDELETE");
                            $successfull = false;
                        }
                        unset($arItems[$key]);
                    }
                }
        }
//clear cache
        if ($successfull) {

            $this->ClearResultCache(array($UserID, $UserTYPE, $arNavigation));
            //update wishlist in session
            if ($arParams['USE_SESSION'] == 'Y') {
                $_SESSION['WISHLIST_ITEMS'] = $arItems;
            }
        }
        
        //trace($_SESSION['WISHLIST_ITEMS']);
//redirect        
        if ($addByAjax) {
            if ($successfull) {
                $addResult = array('STATUS' => 'OK', 'MESSAGE' => GetMessage('WISHLIST_SUCCESSFUL_' . $action));
            } else {
                $addResult = array('STATUS' => 'ERROR', 'MESSAGE' => $strError);
            }
            $APPLICATION->RestartBuffer();
            echo \Bitrix\Main\Web\Json::encode($addResult);
            die();
        } else {
            if ($successfull) {
                $pathRedirect = (
                        $action == "ADD" ? $arParams["WISHLIST_URL"] : $APPLICATION->GetCurPageParam("", array(
                                    $arParams["PRODUCT_ID_VARIABLE"],
                                    $arParams["ACTION_VARIABLE"],
                                ))
                        );
                LocalRedirect($pathRedirect);
            }
        }
    }
}
    if (!$successfull) {
        ShowError($strError);
        return;
    }
//END PROCESS LINK

    if ($this->StartResultCache(false, array($UserID, $UserTYPE, $arNavigation))) {

        if (!CModule::IncludeModule("fcm.wishlist")) {
            $this->AbortResultCache();
            ShowError("IBLOCK_MODULE_NOT_INSTALLED");
            return false;
        }
        $arFilter = array(
            'USER_ID' => $UserID,
            'USER_TYPE' => $UserTYPE,
            'SITE_ID' => SITE_ID
        );
        //trace($arFilter);

        $rs = CFcmWishlist::GetList(array('ELEMENT_ID' => 'ASC'), $arFilter);
        while ($ar = $rs->Fetch()) {
            $arResult['WISHLIST_ITEMS'][] = $ar;
            $arResult['ITEM_IDS'][] = $ar['ELEMENT_ID'];
        }

        $this->SetResultCacheKeys(array(
            "WISHLIST_ITEMS",
            "ITEM_IDS",
            "LIST_PAGE_URL",
            "~LIST_PAGE_URL",
        ));

        global $arWishlistItemsFilter;

        // trace($arResult['ITEM_IDS']);

        $arWishlistItemsFilter['ID'] = $arResult['ITEM_IDS'];

        $this->IncludeComponentTemplate();
    }
?>