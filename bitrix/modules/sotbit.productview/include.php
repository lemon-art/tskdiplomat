<?
IncludeModuleLangFile(__FILE__);
Class CSotbitProductView
{
    public function getDemo()
    {
        $module_id = "sotbit.productview";
        $sotbit_DEMO = CModule::IncludeModuleEx($module_id);
        //$sotbit_DEMO = 3; 
        if($sotbit_DEMO==3)
        {   
            echo GetMessage("SBT_PRODUCTVIEW_DEMO");
            return false;    
        }
        else return true;       
    }
    public function GetProductElements($arView=array(), $PRODUCT_ID)
    {
        $arViewID = array();
        if(!empty($arView) && self::getDemo() && $PRODUCT_ID)
        {
            foreach($arView as $v)
            {
                $arFuser[$v["FUSER_ID"]][$v["PRODUCT_ID"]] = $v["PRODUCT_ID"];
            }
            foreach($arFuser as $fID=>$arProductID)
            {
                if(isset($arProductID))
                {
                    $arViewID = array_merge($arViewID, $arProductID);
                }
            }
            
            $arVewFilter["IBLOCK_LID"] = SITE_ID;
            $arVewFilter["IBLOCK_ACTIVE"] = "Y";
            $arVewFilter["ACTIVE_DATE"] = "Y";
            $arVewFilter["ACTIVE"] = "Y";
            $arVewFilter["CHECK_PERMISSIONS"] = "Y";
            $arVewFilter["=ID"] = array_unique($arViewID);
            $arVewFilter["!ID"] = $PRODUCT_ID;
            if(empty($arViewID)) return $arViewID;
            else return $arVewFilter;
        }
        return $arViewID;
    }
}
?>
