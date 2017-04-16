<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
$APPLICATION->SetPageProperty("keywords", $arResult['NAME'] . " - купить цена интернет магазин");
$APPLICATION->SetPageProperty("title", $arResult['NAME'] . " - купить  в интернет-магазине TSK DIPLOMAT");
$APPLICATION->SetPageProperty("description", $arResult['NAME'] . " - продажа в интернет-магазине TSK DIPLOMAT. Купить " . $arResult['NAME'] . " по доступной цене.");
?>

<script>
    var jqZoomEnabled = true;
</script>
<div id="primary_block" class="clearfix">

    <div id="pb-right-column">

        <div id="image-block" class="bordercolor">
            <img 
                src="<?= $arResult["MORE_PHOTO"][0]["BIGIMAGE"]["SRC"] ?>" 
                class="jqzoom" 
                alt="<?= $arResult["MORE_PHOTO"][0]["SRC"] ?>" 
                id="bigpic" 
                height="<?= $arResult["MORE_PHOTO"][0]["BIGIMAGE"]["HEIGHT"] ?>" 
                width="<?= $arResult["MORE_PHOTO"][0]["BIGIMAGE"]["WIDTH"] ?>">
        </div>


        <div id="views_block">
            <span class="view_scroll_spacer">
                <a style="cursor: default; opacity: 0;" id="view_scroll_left" class="hidden" title="Other views" href="javascript:{}">Previous</a></span> 
            <div id="thumbs_list">
                <ul style="width: 358px;" id="thumbs_list_frame">

                    <?
                    $i = count($arResult["MORE_PHOTO"]);
                    foreach ($arResult["MORE_PHOTO"] as $key => $PHOTO):
                        $i--;
                        ?>
                        <li style="display: list-item;" id="thumbnail_<?= $key ?>" <?= ($i == 0) ? ' class="thumb_last"' : "" ?>>
                            <a 
                                href="<?= $PHOTO["BIGIMAGE"]["SRC"] ?>" 
                                rel="other-views" 
                                content="<?= $PHOTO["SRC"] ?>" 
                                class="thickbox bordercolor shown" 
                                title=""
                                >
                                <img 
                                    id="thumb_<?= $key ?>"
                                    border="0" 
                                    src="<?= $PHOTO["THUMBNAIL"]["SRC"] ?>" 
                                    width="<?= $PHOTO["THUMBNAIL"]["WIDTH"] ?>" 
                                    height="<?= $PHOTO["THUMBNAIL"]["HEIGHT"] ?>"
                                    alt="<?= $arResult["NAME"] ?>" 
                                    title="<?= $arResult["NAME"] ?>" />
                                <br />
                            </a>
                        </li>
                    <? endforeach ?>		
                </ul>
            </div>
            <a style="cursor: pointer; opacity: 1; display: block;" id="view_scroll_right" title="Other views" href="javascript:{}">Next</a> 
        </div>

        <p class="resetimg" style="display:none;">
            <span id="wrapResetImages" style="display: none;">
                <img src="<?= SITE_TEMPLATE_PATH ?>/images/cancel_11x13.gif" alt="Cancel" height="13" width="11"> 
                <a id="resetImages" href="<?= $arResult["DETAIL_PAGE_URL"] ?>" onclick="$('span#wrapResetImages').hide('slow');return (false);">
                    Показать все картинки
                </a>
            </span>
        </p>  

    </div>

    <div id="pb-left-column">
        <?
        echo '<!--****';
        print_r($arElement);
        echo '-->';
        ?>
        <div itemscope itemtype="http://schema.org/Product">
            <div itemprop="name"><h1><?= $arResult["NAME"] ?></h1></div> <!-- Смена вывода H1 -->
            <div id="buy_block" class="bordercolor">
                <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">	<!-- Изминения для микроразметки-->
                    <div class="price bordercolor">
                        <span class="our_price_display">
                            <?
                            foreach ($arResult["PRICES"] as $Price):
                                if ($Price["VALUE"] > $Price["DISCOUNT_VALUE"]):
                                    ?>
                                    <span id="default_price_display" class="pricedefault">
                                        <?= $Price["PRINT_VALUE"] ?>
                                    </span>
                                    <br />
                                    <span id="our_price_display" class="pricediscount">
                                        <?= $Price["PRINT_DISCOUNT_VALUE"] ?>
                                        <? if ($arResult['CATALOG_MEASURE'] > 0): ?>
                                            <span class="measure measureBx">
                                                &nbsp;/&nbsp; за <?= $arResult['CATALOG_MEASURE_RATIO'] ?> 
                                                <?= $arResult['CATALOG_MEASURE_NAME'] ?> 
                                            </span>
                                        <? endif ?>                    
                                    </span>
                                <? else: ?>
                                    <span id="our_price_display" class="pricecolor">
                                        <div><span style="font-size: 17px;">Цена:</span> <?= $Price["PRINT_VALUE"] ?></div> 	<!-- Div для цены-->
                                        <meta itemprop="price" content="<?= $Price["VALUE"] ?>"> <!-- Вывод мета информации-->
                                        <meta itemprop="priceCurrency" content="RUB">				 <!-- Вывод мета информации-->
                                        <? if ($arResult['CATALOG_MEASURE'] > 0): ?>
                                            <span class="measure measureBx">
                                                за <?= $arResult['CATALOG_MEASURE_RATIO'] ?> 
                                                <?= $arResult['CATALOG_MEASURE_NAME'] ?> 
                                            </span>
                                        <? endif ?>
                                    </span>
                                <? endif ?>
                            <? endforeach; ?>
                        </span>
                        <script>
                            $(function () {
                                $('#modal_window_wrapper').click(function (event) {
                                    if ($(event.target).closest("#modal_window").length)
                                        return;
                                    $("#modal_window_wrapper").hide();
                                    event.stopPropagation();
                                });
                            });
                            function setQuantity(arrow) {
                                var count = $('input[name="quantity"]').val();
                                if (arrow == 'up') {
                                    $('input[name="quantity"]').val(++count);
                                }
                                if (arrow == 'down') {
                                    if (count > 1) {
                                        $('input[name="quantity"]').val(--count);
                                    }
                                }
                            }

                            function order_handler()
                            {
                                $("#one_click_form").submit(function () {
                                    if (
                                            $('[name="fio"]').val() == '' ||
                                            $('[name="phone"]').val() == '' ||
                                            $('[name="email"]').val() == ''
                                            ) {
                                        alert('Заполните поля со звездочками.');
                                    } else {
                                        var str = $(this).serialize();
                                        $.post($(this).attr('action'), str, function (data) {
                                            if (data === 'ok') {
                                                alert('Заказ успешно оформлен');
                                                window.location.href = '';
                                            } else {
                                                alert(data);
                                            }
                                        });
                                    }
                                });
                            }

                            function OneclickForm(path) {
                                $("#modal_window").load(path, function () {
                                    var count = $('input[name="quantity"]').val();
                                    $("#one_click_form").append('<input name="quantity" type="hidden" value="'+count+'" />');
                                    order_handler();
                                    $("#modal_window_wrapper").show();
                                    $('.modal_close').click(function () {
                                        $("#modal_window_wrapper").hide();
                                    });
                            });
                        }
                        </script>
                        <form action="" method="get">
                            <input type="hidden" name="id" value="<?= $arResult['ID'] ?>">
                            <input type="hidden" name="action" value="ADD2BASKET">
                            <p id="add_to_cart">
                                <? /* <a  href="<?=$arResult["ADD_URL"]?>" onclick="yaCounter22073026.reachGoal('buyFromDetail');ga('send', 'event', 'buyFromDetail', 'submit', 'Купить в карточке товаров');">Купить</a> */ ?>
                                <input class="exclusive" id="add2cartbtn" value="<?php echo GetMessage("CATALOG_ADD_TO_BASKET"); ?>" type="submit" onclick="yaCounter22073026.reachGoal('buyFromDetail');
                                        ga('send', 'event', 'buyFromDetail', 'submit', 'Купить в карточке товаров');" />
                            </p>
                            <p id="quantity_control">
                                <a href="javascript:void(0);" class="plus" onclick="setQuantity('up');"><i class="fa fa-sort-asc"></i></a>
                                <a href="javascript:void(0);" class="minus" onclick="setQuantity('down');"><i class="fa fa-sort-desc"></i></a>
                            </p>
                            <p id="quantity_wanted_p">
                                <input name="quantity" id="quantity_wanted" class="text" value="1" size="2" maxlength="3" type="text">
                                <label>Количество:</label>
                            </p>
                            <div class="clear"></div>
                            <p id="buy_1_click">
                                <input value="Купить в 1 клик" type="button" class="exclusive" id="buy_1_clickbtn" onclick="OneclickForm('/one_click_order?product_id=<?= $arResult['ID'] ?>');" />
                            </p>
                        </form>
                    </div>
                    <div class="other_options bordercolor clearfix">
                        <div id="other_prices">
                            <div style="width:110%; float:left; margin-top:0; margin-bottom:10px; line-height:15px;" id="mydiv">
                                <? if ($_SERVER["REQUEST_URI"] == "/catalog/plasters/shtukaturka_fasadnaya_vyravnivayushchaya_zimnyaya_seriya/") { ?>
                                    <? if (!empty($arResult["PROPERTIES"]["MANUFACTURER"]['VALUE'])) { ?>
                                        <p><b>Тип:</b> <? echo $arResult["PROPERTIES"]["MANUFACTURER"]["VALUE"]; ?></p>
                                    <? } ?>
                                <? } else { ?>
                                    <p><b>Тип: </b><a style="color:#fe8900;" href="<?= $arResult["SECTION"]["SECTION_PAGE_URL"]; ?>">

                                            <?
                                            if ($_SERVER['REQUEST_URI'] == '/catalog/solvents/white_spirit_nefras_c4_155_200/' || $_SERVER['REQUEST_URI'] == '/catalog/foam_liquid_nails_sealants/solvent_hardened_foam/')
                                                echo 'Химический растворитель';
                                            else
                                                echo $arResult["SECTION"]["NAME"];
                                            ?>

                                        </a></p>
                                <? } ?>
                                <p><b>Категория: </b><a style="color:#fe8900;"href="<?= $arResult["SECTION"]["PATH"]["0"]["SECTION_PAGE_URL"]; ?>"><?= $arResult["SECTION"]["PATH"]["0"]["NAME"] ?></a></p>
                                <?php if ($arResult['PROPERTIES']['MANUFACTURER']['VALUE'] != '' && $arResult['PROPERTIES']['MAN_LINK']['VALUE'] != '') { ?>
                                    <p><b>Бренд: </b><a href="<?= $arResult['PROPERTIES']['MAN_LINK']['VALUE'] ?>" style="color:#fe8900;"><? echo $arResult['PROPERTIES']['MANUFACTURER']['VALUE']; ?></a></p>
                                <?php } ?>
                            </div>
                            <ul id="usefull_link_block" class="bordercolor">
                                <li id="left_share_fb">
                                    <a terget="_blanc" href="#" _href="https://www.facebook.com/sharer/sharer.php?u=http://tsk-diplomat.tw1.ru<? //=$arResult["DETAIL_PAGE_URL"]     ?>&amp;t=<?= $arResult["NAME"] ?>" class="js-new-window"
                                       onclick="
                                               window.open(
                                                       'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href),
                                                       'facebook-share-dialog',
                                                       'width=626,height=436');">
                                        Поделиться на Facebook
                                    </a>
                                </li>
                                <script text="javascript">

                                    $('document').ready(function () {
                                        $('#send_friend_button').fancybox({
                                            'hideOnContentClick': false
                                        });
                                        $('#sendEmail').click(function () {
                                            var datas = [];
                                            $('#fancybox-content').find('input').each(function (index) {
                                                var o = {};
                                                o.key = $(this).attr('name');
                                                o.value = $(this).val();
                                                if (o.value != '')
                                                    datas.push(o);
                                            });
                                            if (datas.length >= 3)
                                            {
                                                $.ajax({
                                                    url: "/sendtoafriend_ajax.php",
                                                    post: "POST",
                                                    data: {action: 'sendToMyFriend', secure_key: '42d7c4a4e1a92560c31a82c695b2d269', friend: JSON.stringify(datas)},
                                                    dataType: "json",
                                                    success: function (result) {
                                                        $.fancybox.close();
                                                    }
                                                });
                                            } else
                                                $('#send_friend_form_error').text("Заполните обязательные поля");
                                        });
                                    });
                                </script>
                                <li class="sendtofriend">
                                    <a id="send_friend_button" href="#send_friend_form">Послать другу</a>
                                </li>
                                <div style="display: none;">
                                    <div id="send_friend_form">
                                        <div class="h2title">Послать другу</div>
                                        <div class="product clearfix">
                                            <img src="<?= $arResult["MIDDLE_PICTURE"]["SRC"] ?>" alt="<?= $arResult["NAME"] ?>" height="<?= $arResult["MIDDLE_PICTURE"]["HEIGHT"] ?>" width="<?= $arResult["MIDDLE_PICTURE"]["WIDTH"] ?>">
                                            <div class="product_desc">
                                                <p class="product_name"><strong><?= $arResult["NAME"] ?></strong></p>
                                                <p><?= $arResult["DETAIL_TEXT"] ?></p>
                                            </div>
                                        </div>
                                        <div class="send_friend_form_content">
                                            <div id="send_friend_form_error"></div>
                                            <div class="form_container">
                                                <p class="intro_form">Получатель:</p>
                                                <p class="text">
                                                    <label for="friend_name">Имя друга:<sup class="required">*</sup> :</label>
                                                    <input id="friend_name" name="friend_name" type="text">
                                                </p>
                                                <p class="text">
                                                    <label for="friend_email">E-mail вашего друга:<sup class="required">*</sup> :</label>
                                                    <input id="friend_email" name="friend_email" type="text">
                                                </p>
                                                <p class="txt_required"><sup class="required">*</sup> Обязательные поля</p>
                                            </div>
                                            <p class="submit">
                                                <input id="id_product_comment_send" name="id_product" value="1" type="hidden">
                                                <a href="#" onclick="$.fancybox.close();">Отказаться</a>&nbsp;или&nbsp;
                                                <input id="sendEmail" class="button" name="sendEmail" value="Отправить" type="submit">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <li class="print"><a href="javascript:print();">Печать</a></li>
                            </ul>


                            <p id="reduction_percent" style="display:none;"><span id="reduction_percent_display"></span></p>
                            <p id="reduction_amount" style="display:none"><span id="reduction_amount_display"></span></p>
                            <p id="product_reference" style="display: none;">
                                <label for="product_reference">Reference: </label>
                                <span class="editable"></span>
                            </p>

                            <p id="minimal_quantity_wanted_p" style="display: none;">
                                This product is not sold individually. You must select at least <b id="minimal_quantity_label">1</b> quantity for this product.
                            </p>

                            <p id="availability_statut" style="display: none;">
                                <span id="availability_label">Availability:</span>
                                <span id="availability_value">
                                </span>
                            </p>
                            <? /*
                              <p id="pQuantityAvailable">
                              <span id="quantityAvailable">99</span>
                              <span style="display: none;" id="quantityAvailableTxt">item in stock</span>
                              <span id="quantityAvailableTxtMultiple">items in stock</span>
                              </p>
                             */ ?>
                        </div>
                        <div id="attributes">
                            <div>	<!-- Div для микроразметки-->
                                <? if ($arResult['PROPERTIES']['TOORDER']['VALUE_XML_ID'] == 'TOORDER_YES'): ?>
                                    <span class="on_sale">НА ЗАКАЗ!</span>
                                <? else: ?>
                                    <span class="on_sale">В НАЛИЧИИ!</span>
                                <? endif; ?>
                                <br><br><br><p style="float:right; class="buttons_bottom_block">
                                <noindex>
                                    <a class="button_compare" style="margin:0 0 5px;" href="?action=ADD_TO_COMPARE_LIST&id=<? echo $arResult['ID']?>" rel="nofollow">Сравнить</a>&nbsp;
                                </noindex>     
                            <br />
                            <a href="http://tskdiplomat.ru/about/delivery/" class="button">Доставка</a>
                            </p>

                        </div>		<!-- закрываем Div для микроразметки-->
                        <link itemprop="availability" href="http://schema.org/InStock">
                    </div>
                    <div class="clearblock"></div>
                </div>
            </div><!-- Нужно казкрыть Div !!!!!!!!!! -->

            <div class="clear"></div>
            <? /*
              <div id="short_description_block" class="bordercolor">
              <div id="short_description_content" class="rte align_justify">
              <p><?=$arResult["PREVIEW_TEXT"]?></p>
              </div>

              <p id="oosHook" style="display: none;">
              <script type="text/javascript">
              $(function(){
              $('a[href=#idTab5]').click(function(){
              $('*[id^="idTab"]').addClass('block_hidden_only_for_screen');
              $('div#idTab5').removeClass('block_hidden_only_for_screen');

              $('ul#more_info_tabs a[href^="#idTab"]').removeClass('selected');
              $('a[href="#idTab5"]').addClass('selected');
              });
              });
              </script>
              </p><div id="product_comments_block_extra" class="bordercolor">
              <ul>
              <!--
              <li><a class="open-comment-form" href="#new_comment_form">Оставить отзыв</a></li>
              -->
              </ul>
              </div>
             */ ?>

            <p></p>

            <!--[if !IE]> -->

            <!-- <![endif]-->
            <div class="clearblock"></div>
        </div>

        <p class="hidden">
            <input name="token" value="256513822170e218db4463e3d40d19dd" type="hidden">
            <input name="id_product" value="1" id="product_page_product_id" type="hidden">
            <input name="add" value="1" type="hidden">
            <input name="id_product_attribute" id="idCombination" value="" type="hidden">
        </p>
    </div>
</div>
</div>
<? /*
  <div id="quantityDiscount" class="bgcolor bordercolor">
  <h3>Скидка на количество</h3>
  <table class="std">
  <thead>
  <tr>
  <th>product</th>
  <th>from (qty)</th>
  <th>discount</th>
  </tr>
  </thead>
  <tbody>
  <tr id="quantityDiscount_0">
  <td>
  <?=$arResult["NAME"]?>
  </td>
  <td>10</td>
  <td>
  -30%
  </td>
  </tr>
  </tbody>
  </table>
  </div>
 */ ?>
<?
global $USER;
if ($USER->IsAdmin()):;
?>
<?$APPLICATION->IncludeComponent(
	"sotbit:product.view",
	"",
	Array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"ID" => $arResult['ID'],
		"CODE" => $arResult['CODE'],
		"DATE_FROM" => "30",
		"LIMIT" => "10000",
		"SORT_FIELD" => "sort",
		"SORT_ORDER" => "rand",
		"DETAIL_PROPS_ANALOG" => $arResult['SECTION']['IBLOCK_SECTION_ID'],
		"HIDE_NOT_AVAILABLE" => "N",
		"DETAIL_URL" => $arResult["DETAIL_PAGE_URL"],
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"ELEMENT_COUNT" => "5",
		"LINE_ELEMENT_COUNT" => "5",
		"PRICE_CODE" => array("BASE"),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CONVERT_CURRENCY" => "N",
                "LINKED_ELEMENTS_ARR" => $arResult["LOOK_LIKE"],
                "TYPE" => "PERELINKOVKA"
	)
);?>
<?$APPLICATION->IncludeComponent(
	"sotbit:product.view",
	"",
	Array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "3",
		"ID" => $arResult['ID'],
		"CODE" => $arResult['CODE'],
		"DATE_FROM" => "30",
		"LIMIT" => "10000",
		"SORT_FIELD" => "sort",
		"SORT_ORDER" => "rand",
		"DETAIL_PROPS_ANALOG" => $arResult['SECTION']['IBLOCK_SECTION_ID'],
		"HIDE_NOT_AVAILABLE" => "N",
		"DETAIL_URL" => $arResult["DETAIL_PAGE_URL"],
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"ELEMENT_COUNT" => "5",
		"LINE_ELEMENT_COUNT" => "5",
		"PRICE_CODE" => array("BASE"),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CONVERT_CURRENCY" => "N",
                "LINKED_ELEMENTS_ARR" => $arResult["SOPUT_PRODUCTS"],
                "TYPE" => "SOPUT"
	)
);?>
<? endif; ?> 
<div id="more_info_block">
    <ul id="more_info_tabs" class="idTabs idTabsShort ">
        <li><a class="selected" id="more_info_tab_more_info" href="#idTab1">Описание</a></li> 
        <!--<li><a id="more_info_tab_calculator" href="#idTab3">Калькулятор расхода</a></li> -->
        <? /*
          <li><a id="more_info_tab_data_sheet" href="#idTab2">Характеристики</a></li>
         */ ?>
        <? if (count($arResult["FILES"]) > 0): ?>
            <li><a id="more_info_tab_attachments" href="#idTab9">Документы</a></li> 
        <? endif ?>
        <!--<li><a href="#idTab4">Сопутствующие товары</a></li>-->
        <li><a href="#idTab5" class="idTabHrefShort">Отзывы</a></li>
        <!--<li><a href="#idTab6" class="idTabHrefShort">Аналогичные товары</a></li>-->
    </ul>
    <div id="more_info_sheets" class="bordercolor bgcolor">		
        <div id="idTab1">
            <div>
                <p>
                <div itemprop="description">	<!-- Div для микроразметки-->
                    <?= $arResult["DETAIL_TEXT"] ?>
                </div>		<!-- Закрываем Div для микроразметки-->
                </p>
            </div>
            <div class="h2style" style="padding-left:10px;" >Аналогичные товары</div>
            <div id="featured_products">
                <div class="block_content">

                    <?
                    /* echo '<!---*****';
                      print_r($arResult);
                      echo '--->'; */
//echo $arResult['SECTION']['IBLOCK_SECTION_ID'];
//echo $arResult["CATALOG_PRICE_1"]; 
                    $section = $arResult['SECTION']['IBLOCK_SECTION_ID'];

                    if (empty($section)) {
                        $section = $arResult['IBLOCK_SECTION_ID'];
                        //echo $section;
                    }

                    $min_cena = $arResult["CATALOG_PRICE_1"] - 150.00;
                    $max_cena = $arResult["CATALOG_PRICE_1"] + 150.00;
                    global $arrFilter;
                    $arrFilter = Array(
                        "IBLOCK_ID" => 3,
                        "SECTION_ID" => $section,
                        "!ID" => $arResult["ID"],
                        '>=CATALOG_PRICE_1' => $min_cena,
                        '<=CATALOG_PRICE_1' => $max_cena,
                    );


                    $APPLICATION->IncludeComponent("bitrix:catalog.section", "simlar-2", array(
                        "IBLOCK_TYPE" => "catalog",
                        "IBLOCK_ID" => "3",
                        "SECTION_ID" => $section,
                        "SECTION_CODE" => "",
                        "SECTION_USER_FIELDS" => array(
                            0 => "",
                            1 => "",
                        ),
                        "ELEMENT_SORT_FIELD" => "sort",
                        "ELEMENT_SORT_ORDER" => "asc",
                        "ELEMENT_SORT_FIELD2" => "id",
                        "ELEMENT_SORT_ORDER2" => "desc",
                        "FILTER_NAME" => "arrFilter",
                        "INCLUDE_SUBSECTIONS" => "Y",
                        "SHOW_ALL_WO_SECTION" => "N",
                        "HIDE_NOT_AVAILABLE" => "N",
                        "PAGE_ELEMENT_COUNT" => "3",
                        "LINE_ELEMENT_COUNT" => "3",
                        "PROPERTY_CODE" => array(
                            0 => "ARTNUMBER",
                            1 => "",
                        ),
                        "OFFERS_FIELD_CODE" => array(
                            0 => "PREVIEW_PICTURE",
                            1 => "",
                        ),
                        "OFFERS_PROPERTY_CODE" => array(
                            0 => "",
                            1 => "",
                        ),
                        "OFFERS_SORT_FIELD" => "sort",
                        "OFFERS_SORT_ORDER" => "asc",
                        "OFFERS_SORT_FIELD2" => "id",
                        "OFFERS_SORT_ORDER2" => "desc",
                        "OFFERS_LIMIT" => "5",
                        "SECTION_URL" => "",
                        "DETAIL_URL" => "",
                        "SECTION_ID_VARIABLE" => "SECTION_ID",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "AJAX_OPTION_HISTORY" => "N",
                        "CACHE_TYPE" => "A",
                        "CACHE_TIME" => "36000000",
                        "CACHE_GROUPS" => "Y",
                        "SET_META_KEYWORDS" => "N",
                        "META_KEYWORDS" => "-",
                        "SET_META_DESCRIPTION" => "Y",
                        "META_DESCRIPTION" => "-",
                        "BROWSER_TITLE" => "-",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "DISPLAY_COMPARE" => ($USER->IsAdmin() ? "Y" : "N"),
                        "SET_TITLE" => "N",
                        "SET_STATUS_404" => "N",
                        "CACHE_FILTER" => "N",
                        "PRICE_CODE" => array(
                            0 => "BASE",
                        ),
                        "USE_PRICE_COUNT" => "N",
                        "SHOW_PRICE_COUNT" => "1",
                        "PRICE_VAT_INCLUDE" => "Y",
                        "CONVERT_CURRENCY" => "N",
                        "BASKET_URL" => "/personal/basket.php",
                        "ACTION_VARIABLE" => "action",
                        "PRODUCT_ID_VARIABLE" => "id",
                        "USE_PRODUCT_QUANTITY" => "N",
                        "ADD_PROPERTIES_TO_BASKET" => "Y",
                        "PRODUCT_PROPS_VARIABLE" => "prop",
                        "PARTIAL_PRODUCT_PROPERTIES" => "N",
                        "PRODUCT_PROPERTIES" => "",
                        "OFFERS_CART_PROPERTIES" => "",
                        "PAGER_TEMPLATE" => ".default",
                        "DISPLAY_TOP_PAGER" => "N",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "PAGER_TITLE" => "Товары",
                        "PAGER_SHOW_ALWAYS" => "N",
                        "PAGER_DESC_NUMBERING" => "N",
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                        "PAGER_SHOW_ALL" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                        "TEMPLATE_THEME" => "blue",
                        "PRODUCT_DISPLAY_MODE" => "Y",
                        "ADD_PICT_PROP" => "-",
                        "LABEL_PROP" => "-",
                        "OFFER_ADD_PICT_PROP" => "-",
                        "OFFER_TREE_PROPS" => array(
                            0 => "-",
                            1 => "",
                        ),
                        "PRODUCT_SUBSCRIPTION" => "N",
                        "SHOW_DISCOUNT_PERCENT" => "N",
                        "SHOW_OLD_PRICE" => "N",
                        "MESS_BTN_BUY" => "Купить",
                        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                        "MESS_BTN_SUBSCRIBE" => "Подписаться",
                        "MESS_BTN_DETAIL" => "Подробнее",
                        "MESS_NOT_AVAILABLE" => "Нет в наличии"
                            ), false, array(
                        "ACTIVE_COMPONENT" => "Y"
                            )
                    );
                    ?>
                </div>
            </div>
        </div>
        <div id="idTab3" class="block_hidden_only_for_screen">
            <div>
                <?
//калькулятор расхода
                global $USER;
                if ($USER->IsAdmin()):

                    global $arCalcFilter;

                    $arCalcFilter = array(
                        'ID' => $arResult['ID'],
                        '!PROPERTY_CALC_CONSUM' => false
                    );
//trace($arResult);
                    ?>
                    <div class="calculator">
                        <?
                        $APPLICATION->IncludeComponent(
                                "fcm:calculator", "", array(
                            "IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'],
                            "IBLOCK_ID" => $arParams['IBLOCK_ID'],
                            "FILTER_NAME" => "arCalcFilter",
                            "CACHE_TYPE" => "A",
                            "CACHE_TIME" => "360000",
                            "CACHE_GROUPS" => "Y",
                            "TITLE" => 'Калькулятор расхода материала'
                                ), $component
                        );
                        ?>    
                    </div>    
                    <br/>
                <? endif; ?>
            </div>
        </div>            
        <? /* 		
          <ul id="idTab2" class="bullet block_hidden_only_for_screen">
          <?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
          <li>
          <span><?=$arProperty["NAME"]?></span>
          <?
          if(is_array($arProperty["DISPLAY_VALUE"])):
          echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
          elseif($pid=="MANUAL"):
          ?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
          else:
          echo $arProperty["DISPLAY_VALUE"];?>
          <?endif?>
          </li>
          <?endforeach?>
          </ul>
         */ ?>		

        <? if (count($arResult["FILES"]) > 0): ?>
            <ul id="idTab9" class="bullet block_hidden_only_for_screen">
                <? foreach ($arResult["FILES"] as $arFile): ?>
                    <li>
                        <? if (strlen($arFile["HTML"]) > 0): ?>
                            <?= $arFile["HTML"] ?>
                        <? else: ?>
                            <a href="<?= $arFile["SRC"] ?>">
                                <img src="<?= $arFile["THUMBNAIL"]["SRC"] ?>" alt="<?= $arFile["DESCRIPTION"] ?>"/>
                            </a>
                        <? endif; ?>	
                        <br>
                        <a href="<?= $arFile["SRC"] ?>">
                            <?= $arFile["ORIGINAL_NAME"] ?>
                            <?= $arFile["DESCRIPTION"] ?>
                        </a>
                        <br/>
                        <span class="filesize"><?= $arFile["~FILE_SIZE"] ?></span>
                    </li>
                <? endforeach; ?>
            </ul>
        <? endif ?>


        <ul class="block_hidden_only_for_screen" id="idTab4">
            <? foreach ($arResult["LINKED_ELEMENTS"] as $Item) { ?>
                <li class="ajax_block_product bordercolor first_item product_accessories_description">
                    <div class="accessories_desc">
                        <a href="<?= $Item["DETAIL_PAGE_URL"] ?>" title="<?= $Item["NAME"] ?>" class="accessory_image product_img_link bordercolor">
                            <img src="<?= $Item["THUMBNAIL_IMAGE"]["SRC"] ?>" alt="<?= $Item["NAME"] ?>" height="80" width="80">
                        </a>
                        <h5>
                            <a class="product_link" href="<?= $Item["DETAIL_PAGE_URL"] ?>"><?= $Item["NAME"] ?></a>
                        </h5>
                        <a class="product_descr" href="<?= $Item["DETAIL_PAGE_URL"] ?>" title="More">
                            <?= $Item["PREVIEW_TEXT"] ?>
                        </a>
                    </div>

                    <div class="accessories_price bordercolor">
                        <? foreach ($Item["PRICES"] as $Price): ?>
                            <span class="price"><?= $Price["PRINT_VALUE"] ?></span> 
                        <? endforeach; ?>	
                        <a class="exclusive button ajax_add_to_cart_button" href="<?= $Item["ADD_URL"] ?>" rel="ajax_id_product_3" title="В корзину">В корзину</a>
                    </div>
                </li>
            <? } ?>
        </ul>


        <div class="block_hidden_only_for_screen" id="idTab5">
            <div id="product_comments_block_tab">
                <div class="h2style">Отзывы</div>
                <? if ($arParams["USE_REVIEW"] == "Y" && IsModuleInstalled("forum")): ?>
                    <?
                    $APPLICATION->IncludeComponent(
                            "bitrix:forum.topic.reviews", "", Array(
                        "EDITOR_CODE_DEFAULT" => "Y",
                        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                        "MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
                        "USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
                        "PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
                        "FORUM_ID" => $arParams["FORUM_ID"],
                        "URL_TEMPLATES_READ" => $arParams["URL_TEMPLATES_READ"],
                        "SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"],
                        "ELEMENT_ID" => $arResult["ID"],
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "AJAX_POST" => $arParams["REVIEW_AJAX_POST"],
                        "POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"],
                        "URL_TEMPLATES_DETAIL" => $arParams["POST_FIRST_MESSAGE"] === "Y" ? $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"] : "",
                            ), $component
                    );
                    ?>
                <? endif ?>
                <? /* 				
                  <p class="align_center">
                  <a id="new_comment_tab_btn" class="open-comment-form" href="#new_comment_form">Оставьте свой отзыв !</a>
                  </p>
                  <div style="display: none;">
                  <div id="new_comment_form">
                  <h2 class="title">Написать отзыв</h2>
                  <div class="product clearfix">
                  <img src="<?=$arResult["MIDDLE_PICTURE"]["SRC"]?>" alt="<?=$arResult["NAME"]?>" height="<?=$arResult["MIDDLE_PICTURE"]["HEIGHT"]?>" width="<?=$arResult["MIDDLE_PICTURE"]["WIDTH"]?>"/>
                  <div class="product_desc">
                  <p class="product_name"><strong><?=$arResult["NAME"]?></strong></p>
                  <p><?=$arResult["DETAIL_TEXT"]?></p>
                  </div>
                  </div>
                  <div class="new_comment_form_content" style="z-index: 99999">
                  <h2>Написать отзыв</h2>
                  <?if($arParams["USE_REVIEW"]=="Y" && IsModuleInstalled("forum")):?>
                  <?$APPLICATION->IncludeComponent(
                  "bitrix:forum.topic.reviews",
                  "",
                  Array(
                  "EDITOR_CODE_DEFAULT" =>"Y",
                  "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                  "CACHE_TIME" => $arParams["CACHE_TIME"],
                  "MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
                  "USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
                  "PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
                  "FORUM_ID" => $arParams["FORUM_ID"],
                  "URL_TEMPLATES_READ" => $arParams["URL_TEMPLATES_READ"],
                  "SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"],
                  "ELEMENT_ID" => $arResult["ID"],
                  "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                  "AJAX_POST" => $arParams["REVIEW_AJAX_POST"],
                  "POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"],
                  "URL_TEMPLATES_DETAIL" => $arParams["POST_FIRST_MESSAGE"]==="Y"? $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"] :"",
                  ),
                  $component
                  );?>
                  <?endif?>
                  <div class="clearfix"></div>
                  </div>
                  </div><!-- new comment form-->
                  </div>
                 */ ?>
            </div>
        </div>
        <!--<div class="block_hidden_only_for_screen" id="idTab6">
        
        </div>-->
    </div><!--more_info_sheets-->
</div><!--more_info_block -->	
</div>


<? /*
  <ul class="idTabs">
  <li><a class="selected">Product customization</a></li>
  </ul>
  <div class="customization_block bgcolor bordercolor">
  <form method="post" action="/prestashop_42844/index.php?id_product=1&amp;controller=product&amp;id_lang=1" enctype="multipart/form-data" id="customizationForm" class="clearfix">
  <p class="infoCustomizable">
  After saving your customized product, remember to add it to your cart.
  </p>
  <div class="customizableProductsText">
  <h2>Text</h2>
  <ul id="text_fields">
  </ul>
  </div>
  <p id="customizedDatas">
  <input name="quantityBackup" id="quantityBackup" value="" type="hidden">
  <input name="submitCustomizedDatas" value="1" type="hidden">
  <input class="button" value="Save" onclick="javascript:saveCustomization()" type="button">
  <span id="ajax-loader" style="display:none">
  <img src="<?=SITE_TEMPLATE_PATH?>/images/loader.gif" alt="loader">
  </span>
  </p>
  </form>
  <p class="clear required"><sup>*</sup> required fields</p>
 */ ?>


<? if ($_REQUEST["D"] == "Y" && $USER->IsAdmin()): ?>
    <pre>
        <?= print_r($arParams, 1) ?>
        <?= print_r($arResult, 1) ?>
    </pre>
<? endif ?>


<? /*
  <div class="catalog-element">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
  <?if(is_array($arResult["PREVIEW_PICTURE"]) || is_array($arResult["DETAIL_PICTURE"])):?>
  <td width="0%" valign="top">
  <?if(is_array($arResult["PREVIEW_PICTURE"]) && is_array($arResult["DETAIL_PICTURE"])):?>
  <img border="0" src="<?=$arResult["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" id="image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>" style="display:block;cursor:pointer;cursor: hand;" OnClick="document.getElementById('image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>').style.display='none';document.getElementById('image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>').style.display='block'" />
  <img border="0" src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" id="image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>" style="display:none;cursor:pointer; cursor: hand;" OnClick="document.getElementById('image_<?=$arResult["DETAIL_PICTURE"]["ID"]?>').style.display='none';document.getElementById('image_<?=$arResult["PREVIEW_PICTURE"]["ID"]?>').style.display='block'" />
  <?elseif(is_array($arResult["DETAIL_PICTURE"])):?>
  <img border="0" src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
  <?elseif(is_array($arResult["PREVIEW_PICTURE"])):?>
  <img border="0" src="<?=$arResult["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
  <?endif?>
  <?if(count($arResult["MORE_PHOTO"])>0):?>
  <br /><a href="#more_photo"><?=GetMessage("CATALOG_MORE_PHOTO")?></a>
  <?endif;?>
  </td>
  <?endif;?>
  <td width="100%" valign="top">
  <?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
  <?=$arProperty["NAME"]?>:<b>&nbsp;<?
  if(is_array($arProperty["DISPLAY_VALUE"])):
  echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
  elseif($pid=="MANUAL"):
  ?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
  else:
  echo $arProperty["DISPLAY_VALUE"];?>
  <?endif?></b><br />
  <?endforeach?>
  </td>
  </tr>
  </table>
  <?if(is_array($arResult["OFFERS"]) && !empty($arResult["OFFERS"])):?>
  <?foreach($arResult["OFFERS"] as $arOffer):?>
  <?foreach($arParams["OFFERS_FIELD_CODE"] as $field_code):?>
  <small><?echo GetMessage("IBLOCK_FIELD_".$field_code)?>:&nbsp;<?
  echo $arOffer[$field_code];?></small><br />
  <?endforeach;?>
  <?foreach($arOffer["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
  <small><?=$arProperty["NAME"]?>:&nbsp;<?
  if(is_array($arProperty["DISPLAY_VALUE"]))
  echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
  else
  echo $arProperty["DISPLAY_VALUE"];?></small><br />
  <?endforeach?>
  <?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
  <?if($arPrice["CAN_ACCESS"]):?>
  <p><?=$arResult["CAT_PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
  <?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
  <s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
  <?else:?>
  <span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
  <?endif?>
  </p>
  <?endif;?>
  <?endforeach;?>
  <p>
  <?if($arParams["DISPLAY_COMPARE"]):?>
  <noindex>
  <a href="<?echo $arOffer["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_COMPARE")?></a>&nbsp;
  </noindex>
  <?endif?>
  <?if($arOffer["CAN_BUY"]):?>
  <?if($arParams["USE_PRODUCT_QUANTITY"]):?>
  <form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
  <table border="0" cellspacing="0" cellpadding="2">
  <tr valign="top">
  <td><?echo GetMessage("CT_BCE_QUANTITY")?>:</td>
  <td>
  <input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
  </td>
  </tr>
  </table>
  <input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
  <input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arOffer["ID"]?>">
  <input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
  <input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CT_BCE_CATALOG_ADD")?>">
  </form>
  <?else:?>
  <noindex>
  <a href="<?echo $arOffer["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
  &nbsp;<a href="<?echo $arOffer["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_ADD")?></a>
  </noindex>
  <?endif;?>
  <?elseif(count($arResult["CAT_PRICES"]) > 0):?>
  <?=GetMessage("CATALOG_NOT_AVAILABLE")?>
  <?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
  "NOTIFY_ID" => $arOffer['ID'],
  "NOTIFY_URL" => htmlspecialcharsback($arOffer["SUBSCRIBE_URL"]),
  "NOTIFY_USE_CAPTHA" => "N"
  ),
  false
  );?>
  <?endif?>
  </p>
  <?endforeach;?>
  <?else:?>
  <?foreach($arResult["PRICES"] as $code=>$arPrice):?>
  <?if($arPrice["CAN_ACCESS"]):?>
  <p><?=$arResult["CAT_PRICES"][$code]["TITLE"];?>&nbsp;
  <?if($arParams["PRICE_VAT_SHOW_VALUE"] && ($arPrice["VATRATE_VALUE"] > 0)):?>
  <?if($arParams["PRICE_VAT_INCLUDE"]):?>
  (<?echo GetMessage("CATALOG_PRICE_VAT")?>)
  <?else:?>
  (<?echo GetMessage("CATALOG_PRICE_NOVAT")?>)
  <?endif?>
  <?endif;?>:&nbsp;
  <?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
  <s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
  <?if($arParams["PRICE_VAT_SHOW_VALUE"]):?><br />
  <?=GetMessage("CATALOG_VAT")?>:&nbsp;&nbsp;<span class="catalog-vat catalog-price"><?=$arPrice["DISCOUNT_VATRATE_VALUE"] > 0 ? $arPrice["PRINT_DISCOUNT_VATRATE_VALUE"] : GetMessage("CATALOG_NO_VAT")?></span>
  <?endif;?>
  <?else:?>
  <span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
  <?if($arParams["PRICE_VAT_SHOW_VALUE"]):?><br />
  <?=GetMessage("CATALOG_VAT")?>:&nbsp;&nbsp;<span class="catalog-vat catalog-price"><?=$arPrice["VATRATE_VALUE"] > 0 ? $arPrice["PRINT_VATRATE_VALUE"] : GetMessage("CATALOG_NO_VAT")?></span>
  <?endif;?>
  <?endif?>
  </p>
  <?endif;?>
  <?endforeach;?>
  <?if(is_array($arResult["PRICE_MATRIX"])):?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%" class="data-table">
  <thead>
  <tr>
  <?if(count($arResult["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
  <td><?= GetMessage("CATALOG_QUANTITY") ?></td>
  <?endif;?>
  <?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
  <td><?= $arType["NAME_LANG"] ?></td>
  <?endforeach?>
  </tr>
  </thead>
  <?foreach ($arResult["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
  <tr>
  <?if(count($arResult["PRICE_MATRIX"]["ROWS"]) > 1 || count($arResult["PRICE_MATRIX"]["ROWS"]) == 1 && ($arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arResult["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
  <th nowrap>
  <?if(IntVal($arQuantity["QUANTITY_FROM"]) > 0 && IntVal($arQuantity["QUANTITY_TO"]) > 0)
  echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
  elseif(IntVal($arQuantity["QUANTITY_FROM"]) > 0)
  echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
  elseif(IntVal($arQuantity["QUANTITY_TO"]) > 0)
  echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
  ?>
  </th>
  <?endif;?>
  <?foreach($arResult["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
  <td>
  <?if($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"])
  echo '<s>'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s> <span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
  else
  echo '<span class="catalog-price">'.FormatCurrency($arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arResult["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
  ?>
  </td>
  <?endforeach?>
  </tr>
  <?endforeach?>
  </table>
  <?if($arParams["PRICE_VAT_SHOW_VALUE"]):?>
  <?if($arParams["PRICE_VAT_INCLUDE"]):?>
  <small><?=GetMessage('CATALOG_VAT_INCLUDED')?></small>
  <?else:?>
  <small><?=GetMessage('CATALOG_VAT_NOT_INCLUDED')?></small>
  <?endif?>
  <?endif;?><br />
  <?endif?>
  <?if($arResult["CAN_BUY"]):?>
  <?if($arParams["USE_PRODUCT_QUANTITY"] || count($arResult["PRODUCT_PROPERTIES"])):?>
  <form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
  <table border="0" cellspacing="0" cellpadding="2">
  <?if($arParams["USE_PRODUCT_QUANTITY"]):?>
  <tr valign="top">
  <td><?echo GetMessage("CT_BCE_QUANTITY")?>:</td>
  <td>
  <input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
  </td>
  </tr>
  <?endif;?>
  <?foreach($arResult["PRODUCT_PROPERTIES"] as $pid => $product_property):?>
  <tr valign="top">
  <td><?echo $arResult["PROPERTIES"][$pid]["NAME"]?>:</td>
  <td>
  <?if(
  $arResult["PROPERTIES"][$pid]["PROPERTY_TYPE"] == "L"
  && $arResult["PROPERTIES"][$pid]["LIST_TYPE"] == "C"
  ):?>
  <?foreach($product_property["VALUES"] as $k => $v):?>
  <label><input type="radio" name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]" value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"checked"'?>><?echo $v?></label><br>
  <?endforeach;?>
  <?else:?>
  <select name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]">
  <?foreach($product_property["VALUES"] as $k => $v):?>
  <option value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"selected"'?>><?echo $v?></option>
  <?endforeach;?>
  </select>
  <?endif;?>
  </td>
  </tr>
  <?endforeach;?>
  </table>
  <input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
  <input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arResult["ID"]?>">
  <input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
  <input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CATALOG_ADD_TO_BASKET")?>">
  </form>
  <?else:?>
  <noindex>
  <a href="<?echo $arResult["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
  &nbsp;<a href="<?echo $arResult["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_ADD_TO_BASKET")?></a>
  </noindex>
  <?endif;?>
  <?elseif((count($arResult["PRICES"]) > 0) || is_array($arResult["PRICE_MATRIX"])):?>
  <?=GetMessage("CATALOG_NOT_AVAILABLE")?>
  <?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
  "NOTIFY_ID" => $arResult['ID'],
  "NOTIFY_PRODUCT_ID" => $arParams['PRODUCT_ID_VARIABLE'],
  "NOTIFY_ACTION" => $arParams['ACTION_VARIABLE'],
  "NOTIFY_URL" => htmlspecialcharsback($arResult["SUBSCRIBE_URL"]),
  "NOTIFY_USE_CAPTHA" => "N"
  ),
  false
  );?>
  <?endif?>
  <?endif?>
  <br />
  <?if($arResult["DETAIL_TEXT"]):?>
  <br /><?=$arResult["DETAIL_TEXT"]?><br />
  <?elseif($arResult["PREVIEW_TEXT"]):?>
  <br /><?=$arResult["PREVIEW_TEXT"]?><br />
  <?endif;?>
  <?if(count($arResult["LINKED_ELEMENTS"])>0):?>
  <br /><b><?=$arResult["LINKED_ELEMENTS"][0]["IBLOCK_NAME"]?>:</b>
  <ul>
  <?foreach($arResult["LINKED_ELEMENTS"] as $arElement):?>
  <li><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></li>
  <?endforeach;?>
  </ul>
  <?endif?>
  <?
  // additional photos
  $LINE_ELEMENT_COUNT = 2; // number of elements in a row
  if(count($arResult["MORE_PHOTO"])>0):?>
  <a name="more_photo"></a>
  <?foreach($arResult["MORE_PHOTO"] as $PHOTO):?>
  <img border="0" src="<?=$PHOTO["SRC"]?>" width="<?=$PHOTO["WIDTH"]?>" height="<?=$PHOTO["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" /><br />
  <?endforeach?>
  <?endif?>
  <?if(is_array($arResult["SECTION"])):?>
  <br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
  <?endif?>
  </div>
 */ ?>

<?php
global $goods_chain_info;
if (is_array($arResult) && isset($arResult['NAME']) && isset($arResult['DETAIL_PAGE_URL'])) {
    $goods_chain_info = array(
        'NAME' => $arResult['NAME'],
        'DETAIL_PAGE_URL' => $arResult['DETAIL_PAGE_URL']
    );
}
?>
<? if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/promo.php')) include_once ($_SERVER['DOCUMENT_ROOT'] . '/promo.php'); ?>
