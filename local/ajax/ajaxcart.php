<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
  <?
if ((CModule::IncludeModule("sale"))&&(CModule::IncludeModule("iblock")))
{
    $arBasketItems = array();

    $dbBasketItems = CSaleBasket::GetList(
        array(),
        array(
             "FUSER_ID" => CSaleBasket::GetBasketUserID(),
            "LID" => SITE_ID,
             "ORDER_ID" => "NULL"
        ),
        false,
        false,
        array("*")
    );

    $i = 0;

    while ($arItems = $dbBasketItems->Fetch())
    {
       $sum = $sum+$arItems["QUANTITY"]*$arItems['PRICE'];
	   $res = CIBlockElement::GetByID($arItems["PRODUCT_ID"]);
       if($ar_res = $res->GetNext()) {
          $url=$ar_res['DETAIL_PAGE_URL'];
		  $arFile = CFile::GetFileArray($ar_res["PREVIEW_PICTURE"]);

			$img=CFile::ResizeImageGet(
            $arFile,
            array("width" => 50, "height" => 50),
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
            false
        );
	   }  
	   $i++;
	   ?>
                        	<div class="header-cart-item clearfix">
                            	<div class="header-cart-img">
                                	<a href="<?=$url;?>"><img src="<?=$img["src"];?>" alt=""></a>
                                </div>
                                <div class="header-cart-text">
                                	<a href="<?=$url;?>"><?=$arItems["NAME"]?></a>
                                    <span class="header-cart-price"><?=$arItems["QUANTITY"]*$arItems['PRICE']?> <span>руб.</span></span>
                                </div>
                            </div>
    <?}}?>                  <?if($i>0):?>      
                            <?if($i>5):?><a href="<?=$arParams["PATH_TO_BASKET"]?>" class="all">Показать все</a><?endif;?>
                            <div class="header-cart-total">Итого: <span><?=$sum;?> руб.</span></div>
                            <a href="/personal/cart/" class="btn btn-orange header-cart-btn">Оформить заказ</a>
							<?else:?>
							<div class="header-cart-total">Корзина пуста</span></div>
							<?endif;?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");		
?>