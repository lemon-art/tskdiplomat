<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
global $curPage;
global $pageLayOut;
?>
                            <?
                            if ($curPage == SITE_DIR."index.php"):
                                include("includes/footbanners.php");
                            endif;
                            ?>
                        </div> <!-- padding-s -->
                    </div> <!-- col main -->
<?    switch ($pageLayOut):
        case 'col2-left':?>
		<div class="col-left sidebar span3">
<?$APPLICATION->IncludeComponent(
	"bitrix:main.include", 
	".default", 
	array(
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "inc",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_MODE" => "html",
		"EDIT_TEMPLATE" => "sect_inc.php",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							Array(
								"AREA_FILE_SHOW" => "page", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "page_inc.php" 
							)
						);?>
                </div><!-- col-left sidebar -->
		</div><!--row-->
            </div><!--main-->
        </div><!--span NN -->
            <?break;
        case 'col2-right':?>
                                    <div class="col-right sidebar span3">
                                       		<?/*$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							Array(
								"AREA_FILE_SHOW" => "sect", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "sect_inc.php" 
							)
						);?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							Array(
								"AREA_FILE_SHOW" => "page", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "page_inc.php" 
							)
						);*/?>
                                    </div><!-- col-right -->
							</div><!-- row-->
						</div><!--main-->
					</div><!--span NN -->                                    
            <?break;
        case 'col3':?>
				<div class="col-left sidebar span3">
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							Array(
								"AREA_FILE_SHOW" => "sect", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "sect_inc.php" 
							)
						);?>

                                </div> <!-- col-left -->
				</div>
				</div>
				<div class="col-right sidebar span3">

						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							Array(
								"AREA_FILE_SHOW" => "page", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "page_inc.php" 
							)
						);?>
                                </div>
							</div><!-- row-->
						</div><!--main-->
					</div><!--span NN -->
            <?break;
            case 'catalog':
                break;
        default:?>
                </div><!--main-->
            </div><!--spanNN-->   
    <?endswitch;?>
        
        </div> <!--//row-->
    </div> <!-- container -->
</div> <!--main-container-->
                            
<!-- footer -->                            
                            <div class="footer-container">
                                <div class="container">
                                    <div class="row">
                                        <div class="span12">
                                            <div class="footer">
                                                <p id="back-top"><a href="#top"><span></span></a> </p>
                                                <div class="footer-cols-wrapper">
                                                    <div class="footer-col">
                                                        <h4><?=GetMessage('TPL_FOOTMT_ABOUT_SHOP')?></h4>
                                                        <div class="footer-col-content">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "bl", array(
				"ROOT_MENU_TYPE" => "foot1",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"CACHE_SELECTED_ITEMS" => "N",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "3",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>                                           
                                                        </div>
                                                    </div>
                                                    <div class="footer-col middle">
                                                        <h4><?=GetMessage('TPL_FOOTMT_ABOUT_BUY')?></h4>
                                                        <div class="footer-col-content">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "bl", array(
				"ROOT_MENU_TYPE" => "foot2",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"CACHE_SELECTED_ITEMS" => "N",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "3",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>   
                                                        </div>
                                                    </div>
                                                    <div class="footer-col">
                                                        <h4><?=GetMessage('TPL_FOOTMT_ACCAUNT')?></h4>
                                                        <div class="footer-col-content">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "bl", array(
				"ROOT_MENU_TYPE" => "foot3",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"CACHE_SELECTED_ITEMS" => "N",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "3",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?> 
                                                        </div>
                                                    </div>
                                                    <div class="footer-col last">
                                                        <h4><?=GetMessage('TPL_FOOTMT_CONTACTS')?></h4>
                                                        <div class="footer-col-content">
                                                            <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/contacts.php"), false);?>

                                                        </div>
                                                    </div> </div>
                                                <div class="bottom_block">
                                                    <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_name.php"), false);?>
                                                    <div class="clear"></div>
                                                    <div class="paypal-logo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                    //
                    $arPhpVars = array(
                        'COMPARE_ITEMS' => array(),
                        'WISHLIST_ITEMS' => array(),
                        'BASKET_ITEMS' => array()
                    );
                    
                    if(count($_SESSION['BASKET_ITEMS']) > 0){
                        $arPhpVars['BASKET_ITEMS'] = $_SESSION['BASKET_ITEMS'];
                    }
                    if(count($_SESSION['WISHLIST_ITEMS']) > 0){
                        $arPhpVars['WISHLIST_ITEMS'] = $_SESSION['WISHLIST_ITEMS'];
                    }
                    if(count($_SESSION['CATALOG_COMPARE_LIST']) > 0)
                        foreach ($_SESSION['CATALOG_COMPARE_LIST'] as $iBlockItems){
                            if(!empty($iBlockItems['ITEMS'])){
                                $arPhpVars['COMPARE_ITEMS'] = array_merge($arPhpVars['COMPARE_ITEMS'], array_keys($iBlockItems['ITEMS']));
                        }
                    }
                    
                    if(count($arPhpVars) > 0){?>
                        <script>
                           var templVars = <?=CUtil::PhpToJSObject($arPhpVars)?>;
                           console.log(templVars);
                        </script>    
                    <?
                    }
                    ?>
                        <?//=trace($_SESSION);?>
                    <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/counters.php"), false);?>
                </body>
                </html>
