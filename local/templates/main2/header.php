<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
IncludeTemplateLangFile(__FILE__);
CUtil::InitJSCore();
global $curPage;
$curPage = $APPLICATION->GetCurPage(true);
/*
 * Columns layout of page. must value
 * col1 - 1 column
 * col2-right - 2 column, right sidebar
 * col2-left - 2 column, left sidebar
 * col3 - 3 column
 */
global $pageLayOut;
$pageLayOut = $APPLICATION->GetProperty('PAGE_LAYOUT');

    switch ($pageLayOut):
        case 'catalog':
        case 'col2-left':
                $mainContainerClass = 'col2-left-layout';
            break;
        case 'col2-right':
                $mainContainerClass = 'col2-right-layout';
            break;
        case 'col3':
                $mainContainerClass = 'col3-layout';
            break;
        default:
                $mainContainerClass = 'col1-layout';
    endswitch;                


    CUtil::InitJSCore();
    
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/bootstrap/css/bootstrap.min.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/styles.css");

    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/font-awesome.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/extra_style.css");
    //$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/grid_1170.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/responsive.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/superfish.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/widgets.css");
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/ecommerceteam/cloud-zoom.css");

//    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/print.css"); needet media
    

    
    $APPLICATION->AddHeadScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
    
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/superfish.js");
    
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scripts.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.jcarousel.min.js");
    
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/prototype/prototype.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/lib/ccard.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/prototype/validation.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scriptaculous/builder.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scriptaculous/effects.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scriptaculous/dragdrop.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scriptaculous/controls.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scriptaculous/slider.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/varien/js.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/varien/form.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/mage/translate.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/mage/cookies.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/ecommerceteam/cloud-zoom.1.0.2.min.js");
    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/bootstrap/js/bootstrap.min.js");    

    $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/fancybox/jquery.fancybox.pack.js');
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/js/fancybox/jquery.fancybox.css');
    
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="yandex-verification" content="e4e4066f5dd220dd" />  
		<title><?$APPLICATION->ShowTitle()?></title>
        
        <link rel="shortcut icon" type="image/x-icon" href="<?= SITE_TEMPLATE_PATH ?>/favicon.ico" />
        <?
        $APPLICATION->ShowHead();
        echo '<meta http-equiv="Content-Type" content="text/html; charset=' . LANG_CHARSET . '"' . (true ? ' /' : '') . '>' . "\n";
        $APPLICATION->ShowMeta("robots", false, true);
      
       
        //$APPLICATION->ShowCSS(true, true);
        ?>
        <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/css/print.css" media="print"/>
		<?/*
			if (CModule::IncludeModule("iblock")){
			$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");//IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
			$arFilter = Array("IBLOCK_ID"=>12, "NAME"=>  $APPLICATION->GetCurPage(), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
			$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
			while($ob = $res->GetNextElement()){ 
			 $arFields = $ob->GetFields();  

			 $arProps = $ob->GetProperties();
			//print_r($arProps);
				if($arProps['TITLE']['VALUE']){
			/ *	$APPLICATION->SetPageProperty('title', $arProps['TITLE']['VALUE']);
				$APPLICATION->SetPageProperty('description', $arProps['DESCRIPTION']['VALUE']);
				$APPLICATION->SetPageProperty('keywords', $arProps['KEYWORDS']['VALUE']);* /
				echo '<title>'.$arProps['TITLE']['VALUE'].'</title>'."\n";
				echo '<meta name="description" content="'.$arProps['DESCRIPTION']['VALUE'].'"/>'."\n";
				echo '<meta name="keywords" content="'.$arProps['KEYWORDS']['VALUE'].'"/>'."\n";
				}
				else{echo '<title>'.$APPLICATION->ShowTitle().'</title>';
						$APPLICATION->ShowMeta("description", false, true);
						$APPLICATION->ShowMeta("keywords", false, true);
				}
			}
			
			}
			
		*/?>

		
                <link rel="icon" href="<?= SITE_TEMPLATE_PATH ?>/favicon.ico" type="image/x-icon"/>
                <link rel="shortcut icon" href="<?= SITE_TEMPLATE_PATH ?>/favicon.ico" type="image/x-icon"/>
                <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,300,400italic,600,600italic,700italic,700,800,800italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'/>
                <!--[if lt IE 7]>
                <script type="text/javascript">
                //<![CDATA[
                    var BLANK_URL = '<?= SITE_TEMPLATE_PATH ?>/js/blank.html';
                    var BLANK_IMG = '<?= SITE_TEMPLATE_PATH ?>/js/spacer.gif';
                //]]>
                </script>
                <![endif]-->
                <!--[if lt IE 9]>
                <div style=' clear: both; text-align:center; position: relative;'>
                 <a  rel="nofollow" href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." /></a>
                </div>
                <![endif]-->
                <!--[if lt IE 9]>
                        <style>
                        body {
                                min-width: 960px !important;
                        }
                        </style>
                <![endif]-->
                

                <!--[if lt IE 8]>
                <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/css/styles-ie.css" media="all" />
                <![endif]-->
                <!--[if lt IE 7]>
                <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH ?>/js/lib/ds-sleight.js"></script>
                <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH ?>/skin/frontend/base/default/js/ie6.js"></script>
                <![endif]-->
      <?          
        //$APPLICATION->ShowHeadStrings();
        //$APPLICATION->ShowHeadScripts();
      ?>  
                </head>
 
<body>
<?$APPLICATION->ShowPanel(); ?>
    <div class="body_wrap">
                    <div class="container">
                        <noscript>
                            <div class="global-site-notice noscript">
                                <div class="notice-inner">
                                    <p>
                                        <strong>Кажется, в вашем браузере отключён JavaScript. </strong><br/>
                                        Вы должны включить JavaScript в вашем браузере, чтобы использовать функциональные возможности этого сайта.<br/>
                                        <strong>JavaScript seems to be disabled in your browser.</strong><br/>
                                        You must have JavaScript enabled in your browser to utilize the functionality of this website. 
                                    </p>
                                </div>
                            </div>
                        </noscript>
                        <div class="row">
                            <div class="header col-md-12">
                                    <div class="header-buttons">
                                        <div class="header-button top-login">
                                            <ul class="links">
                                                <li>
                                                    <a href="/auth/" title="Log In" class="Login_link">Войти</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="header-button menu-list">
                                            <?
                                            $APPLICATION->IncludeComponent(
                                                    "bitrix:menu", "quickaccess", array(
                                                "ROOT_MENU_TYPE" => "quickaccess",
                                                "MENU_CACHE_TYPE" => "Y",
                                                "MENU_CACHE_TIME" => "36000000",
                                                "MENU_CACHE_USE_GROUPS" => "Y",
                                                "MENU_CACHE_GET_VARS" => array(
                                                ),
                                                "MAX_LEVEL" => "1",
                                                "USE_EXT" => "N",
                                                "ALLOW_MULTI_SELECT" => "N",
                                                "COMPONENT_TEMPLATE" => "quickaccess",
                                                "CHILD_MENU_TYPE" => "left",
                                                "DELAY" => "N"
                                                    ), false
                                            );
                                            ?>                                                          
                                            <a href="#"></a>
                                            <ul class="links">
                                                <li>
                                                    <a href="/customer/account/" title="Мой счет">Мой счет</a>
                                                </li>
                                                <li><a href="/checkout/cart/" title="Корзина" class="top-link-cart">Корзина</a></li>
                                                <li><a href="/checkout/" title="Заказы" class="top-link-checkout">Заказы</a></li>
                                                <li>
                                                    <a href="/wishlist/" title="Избранное">Избранное</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="header-button top-auth">
                                            <?
                                            global $USER;
                                            if ($USER->IsAuthorized()) {
                                                ?>
                                                <a  class="sign-out" href="/?logout=yes" title="Выйти">Выход</a>
                                            <? } else { ?>
                                                <a class="sign-in" href="/auth/" title="Войти на сайт">Войти</a>
                                            <? } ?>
                                        </div>
                                    <div class="header-aboutmenu">
                                            <?
                                            $APPLICATION->IncludeComponent(
                                                "bitrix:menu", "quickaccess", array(
                                                "ROOT_MENU_TYPE" => "about",
                                                "MENU_CACHE_TYPE" => "Y",
                                                "MENU_CACHE_TIME" => "36000000",
                                                "MENU_CACHE_USE_GROUPS" => "Y",
                                                "MENU_CACHE_GET_VARS" => array(
                                                ),
                                                "MAX_LEVEL" => "1",
                                                "USE_EXT" => "N",
                                                "ALLOW_MULTI_SELECT" => "N",
                                                "COMPONENT_TEMPLATE" => "quickaccess",
                                                "CHILD_MENU_TYPE" => "",
                                                "DELAY" => "N"
                                                    ), false
                                            );
                                            ?>                                                            
                                    </div> 
                                    </div>
                                    <?//TODO вставить название?>
                                    <div class="logo">
                                        <strong></strong>
                                        <a href="/" title="">
                                            <img src="<?= SITE_TEMPLATE_PATH ?>/images/logo.png" alt=""/>
                                        </a>
                                    </div>
                                    <div class="clear head_clear"></div>
                                    <div class="row-2">
                                        <div class="header-links">

                                            <?
                                            $APPLICATION->IncludeComponent(
                                                    "bitrix:menu", "quickaccess", array(
                                                "ROOT_MENU_TYPE" => "quickaccess",
                                                "MENU_CACHE_TYPE" => "Y",
                                                "MENU_CACHE_TIME" => "36000000",
                                                "MENU_CACHE_USE_GROUPS" => "Y",
                                                "MENU_CACHE_GET_VARS" => array(
                                                ),
                                                "MAX_LEVEL" => "1",
                                                "USE_EXT" => "N",
                                                "ALLOW_MULTI_SELECT" => "N",
                                                "COMPONENT_TEMPLATE" => "quickaccess",
                                                "CHILD_MENU_TYPE" => "left",
                                                "DELAY" => "N"
                                                    ), false
                                            );
                                            ?>                                                            
                                        </div>
                                        <div class="header_phone">
                                            <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/phones2.php"), false);?>
                                        </div> 
                                        <div class="clear"></div>
                                        <!--cart-->
										<div class="block-cart-header">
											<?
											$APPLICATION->IncludeComponent(
													"bitrix:sale.basket.basket.line", ".default", array(
												"COMPONENT_TEMPLATE" => ".default",
												"PATH_TO_BASKET" => SITE_DIR . "personal/cart/",
												"SHOW_NUM_PRODUCTS" => "Y",
												"SHOW_TOTAL_PRICE" => "Y",
												"SHOW_EMPTY_VALUES" => "Y",
												"SHOW_PERSONAL_LINK" => "N",
												"PATH_TO_PERSONAL" => SITE_DIR . "personal/",
												"SHOW_AUTHOR" => "N",
												"PATH_TO_REGISTER" => SITE_DIR . "login/",
												"PATH_TO_PROFILE" => SITE_DIR . "personal/",
												"SHOW_PRODUCTS" => "Y",
												"SHOW_DELAY" => "N",
												"SHOW_NOTAVAIL" => "N",
												"SHOW_SUBSCRIBE" => "N",
												"SHOW_IMAGE" => "Y",
												"SHOW_PRICE" => "Y",
												"SHOW_SUMMARY" => "Y",
												"PATH_TO_ORDER" => SITE_DIR . "personal/order/make/",
												"POSITION_FIXED" => "N",
													), false
											);
											?>
										</div>
                                        <!--//cart-->

                                        <form id="search_mini_form" action="/search/" method="get">
                                            <div class="form-search">
                                                <label for="search">Поиск:</label>
                                                <input id="search" type="text" name="q" value="" class="input-text" maxlength="128"/>
                                                <button type="submit" title="Посик по сайту"><i class="icon-search"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                    <form id="search_mini_form" action="/search/" method="get">
                                        <div class="form-search">
                                            <label for="search">Поиск:</label>
                                            <input id="search" type="text" name="q" value="" class="input-text" maxlength="128"/>
                                            <button type="submit" title="Поиск по сайту" class="button"><i class="icon-search"></i></button>
                                        </div>
                                    </form>
                            </div>
                        </div>
                        <div class="row nav-container">
                            <div class="col-lg-12 col-sm-12">
                                            <div id="menu-icon">Каталог</div>
<?
                                            $APPLICATION->IncludeComponent(
                                                "bitrix:menu", "sf", array(
												"ROOT_MENU_TYPE" => "top",
												"MENU_CACHE_TYPE" => "Y",
												"MENU_CACHE_TIME" => "36000000",
												"MENU_CACHE_USE_GROUPS" => "N",
												"MENU_CACHE_GET_VARS" => array(
												),
												"MAX_LEVEL" => "2",
												"USE_EXT" => "Y",
												"ALLOW_MULTI_SELECT" => "N",
												"COMPONENT_TEMPLATE" => "sf",
												"CHILD_MENU_TYPE" => "left",
												"DELAY" => "N",
                                                "MENU_CLASS" => 'sf-menu',
                                                "MENU_ID" => 'nav',
                                                    ), false
);
                                            ?>    
                                            <?
                                            $APPLICATION->IncludeComponent(
                                                "bitrix:menu", "sf", array(
                                                "ROOT_MENU_TYPE" => "top",
                                                "MENU_CACHE_TYPE" => "Y",
                                                "MENU_CACHE_TIME" => "36000000",
                                                "MENU_CACHE_USE_GROUPS" => "N",
                                                "MENU_CACHE_GET_VARS" => array(
                                                ),
                                                "MAX_LEVEL" => "2",
                                                "USE_EXT" => "Y",
                                                "ALLOW_MULTI_SELECT" => "N",
                                                "COMPONENT_TEMPLATE" => "sf",
                                                "CHILD_MENU_TYPE" => "left",
                                                "DELAY" => "N",
                                                "MENU_CLASS" => 'sf-menu-phone',
                                                "MENU_ID" => '',
                                                    ), false
                                            );
                                            ?>                                         
                                        </div>
                        </div>
                            <?
                            if ($curPage == SITE_DIR . "index.php"):
                                include("includes/mainslider.php");
                            endif;
                            ?>
                            <div class="row main-container <?=$mainContainerClass;// $APPLICATION->GetProperty('PAGE_LAYOUT');?>">
                                        <div class="col-lg-12 col-sm-12">
                                            <div class="main">
                               <?$APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default", array(
						"START_FROM" => "0",
						"PATH" => "",
						"SITE_ID" => "-"
					),
					false,
					Array('HIDE_ICONS' => 'Y')
				);?>
                                                
                            <?switch ($pageLayOut):
                                    case 'col2-left':?>
                                                <?//col2 left layout?>
							<div class="row">
                                                            <div class="col-left col-lg-3 col-sm-3">
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
                                                            </div>
                                                            <div class="col-main col-lg-9 col-sm-9">
                                                                        <div class="padding-s">
                                        <?break;
                                    case 'col2-right':?>
                                                <?//col2 right layout?>
                                                        <div class="row">
                                                            <div class="col-main col-lg-9 col-sm-9">
                                                                        <div class="padding-s">
                                        <?break;
                                    case 'col3':?>
                                                <?//col3 layout?>
							<div class="row">
                                                            <div class="col-wrapper col-lg-9 col-sm-9">
                                                		<div class="row">
                                                                    <div class="col-main col-lg-6 col-sm-6">
                                                                        <div class="padding-s">
                                        <?break;
                                    case 'catalog':?>
                                                <?//same catalog layout?>
                                        <?break;
                                    default:
                                            //col1 layout?>
							<div class="col-main">
                                                            <div class="padding-s">
                                <?endswitch;?>                

<!-- Page layout <?=$pageLayOut?> --> 


                         