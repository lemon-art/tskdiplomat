<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}?>
<?if(strlen($arResult['NAV_STRING']) > 0){?>
<div class="row">
	<div class="col-md-12 products_list_top_nav">
		<?echo $arResult['NAV_STRING'];?>
	</div>
</div>
<?}?>
<?foreach ($arResult['SECTIONS'] as $arSection){?>
<h3><?=$arSection['NAME']?></h3>

<div class="clearfix">
    <table class="producs">
        <?
            $columnsCnt = count($arSection['UF_LISTVIEW']['COLUMNS']) + count($arSection['UF_LISTVIEW']['PRICES']) +2;
        ?>
        <tr class="table_header">
        <th></th>    
        <th class="hidden-xs">Название</th>
        <th class="props-td-xs">Характеристики</th>
        <?foreach($arSection['UF_LISTVIEW']['COLUMNS'] as $propColumn){?>
        <th class="props-td">
            <?if(strlen($propColumn['TITLE'] )> 0){?>
                <?=$propColumn['TITLE']?>
            <?}else{?>
                <?=$arResult['IBLOCK_PROPERTIES'][$propColumn['PROPERTY_CODE']]['NAME'];?>
            <?}?>
        </th>
        <?}?>
        <?foreach($arSection['UF_LISTVIEW']['PRICES'] as $priceColumn){?>
        <th  class="hidden-xs">
            <?if(strlen($priceColumn['TITLE']) > 0){?>
                <?=$priceColumn['TITLE']?>
            <?}else{?>
                <?=$arResult['PRICES'][$priceColumn['CODE']]['TITLE'];?>
            <?}?>
        </th>
        <?}?>
        </tr>
        <?foreach ($arSection['ITEMS'] as $key => $id):
            $arItem = $arResult['ITEMS'][$id];
            ?>
        <tr<?=($arItem['SPEC']=='Y')?' class="spec"':''?>>
            <td class="img">
                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
                <img 
                    src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" 
                    title="<?=$arItem['PREVIEW_PICTURE']['TITLE']?>" 
                    alt="<?=$arItem['PREVIEW_PICTURE']['ALT']?>" 
                    width="<?=$arItem['PREVIEW_PICTURE']['WIDTH']?>" 
                    height="<?=$arItem['PREVIEW_PICTURE']['HEIGHT']?>" 
                    />
                </a>
            </td>
            <td class="name hidden-xs">
                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
                    <?=$arItem['NAME']?>
                </a>
                <?/*
                <br/>    
                    <span class="quick-watch" href="<?=$arItem['DETAIL_PAGE_URL']?>" data-popup='<?=json_encode($arItem['jsData'], JSON_FORCE_OBJECT)?>'>
                        <span>Быстрый просмотр</span>
                    </span>
                 * 
                 */?>
            </td>
            <td class="props-td-xs">
                <div class="visible-xs-block">
                    <a href="<?=$arItem['DETAIL_PAGE_URL']?>" title="<?=$arItem['NAME']?>">
                        <b><?=$arItem['NAME']?></b><br/>
                    </a>                    
                </div>
        <?foreach($arSection['UF_LISTVIEW']['COLUMNS'] as $propColumn){?>
            <?if(!empty($arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])){?>
                <?if(strlen($propColumn['TITLE'] )> 0){?>
                    <?=$propColumn['TITLE']?>:
                <?}else{?>
                    <?=$arResult['IBLOCK_PROPERTIES'][$propColumn['PROPERTY_CODE']]['NAME'];?>:
                <?}?>    
                <?if(is_array($arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])){?>
                    <b><?=implode(', ', $arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])?></b>
                <?}else{?>
                    <b><?=$arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE']?></b>
                <?}?>
                <br/>
            <?}?>
        <?}?>
                <div class="visible-xs-block">        
                    <?foreach($arSection['UF_LISTVIEW']['PRICES'] as $priceColumn){?>
                        <?if(!empty($arItem['PRICES'][$priceColumn['PRICE_ID']])){?>
                                <?if(strlen($priceColumn['TITLE']) > 0){?>
                                    <?=$priceColumn['TITLE']?>:
                                <?}else{?>
                                    <?=$arResult['PRICES'][$priceColumn['CODE']]['TITLE'];?>:
                                <?}?>
                            <span class="price rouble"><?=$arItem['PRICES'][$priceColumn['PRICE_ID']]['PRINT_VALUE']?></span><br/>
                        <?}?>
                    <?}?>                   
                </div>
            </td>
            
        <?foreach($arSection['UF_LISTVIEW']['COLUMNS'] as $propColumn){?>
        <td class="props-td">
            <?if(!empty($arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])){?>
                <?if(is_array($arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])){?>
                    <?=implode(', ', $arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE'])?>
                <?}else{?>
                    <?=$arItem['DISPLAY_PROPERTIES'][$propColumn['PROPERTY_ID']]['DISPLAY_VALUE']?>
                <?}?>
            <?}else{?>
                &nbsp;
            <?}?>
        </td>
        <?}?>
        <?foreach($arSection['UF_LISTVIEW']['PRICES'] as $priceColumn){?>
        <td class="price-cell hidden-xs">
            <?if(!empty($arItem['PRICES'][$priceColumn['PRICE_ID']])){?>
                <span class="price rouble"><?=$arItem['PRICES'][$priceColumn['PRICE_ID']]['PRINT_VALUE']?></span>
            <?}else{?>
                &nbsp;
            <?}?>
        </td>
        <?}?>            
        </tr>
        <?endforeach;// arResult ITEMS?>
    </table>
</div>   
<?} //foreach sections ?>
<?if(strlen($arResult['NAV_STRING']) > 0){?>
<div class="row">
	<div class="col-md-12 products_list_bottom_nav">
		<?echo $arResult['NAV_STRING'];?>
	</div>
</div>
<?}?>
    <!-- Modal -->
<div id="quickViewModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body clearfix quick-view-dialog">
              <div class="col-md-7">
                  <h3 id="qw_title" class="product-title"></h3>
                  <img id="qw_image" src=""/>
                  <br/>
                  <table id="qw_attributes" class="props">
                      <tr id="qwa_articul"><td class="proptitle"><span>Артикул</span></td><td class="propvalue"></td></tr>
                      <tr id="qwa_supplier"><td class="proptitle"><span>Поставщик </span></td><td class="propvalue"></td></tr>
                      <tr id="qwa_destination"><td class="proptitle"><span>Назначение </span></td><td class="propvalue"></td></tr>
                      <tr id="qwa_notation"><td class="proptitle"><span>Примечание </span></td><td class="propvalue"></td></tr>
                  </table>
                  <span class="props_table_title">Внешний вид</span>
                  <table id="qw_properties" class="props">
                  </table>
              </div>
              <div class="col-md-5">
				<form class="qw-calc-form" action="/echo" method="post">
					<div class="wrapper">
						<div class="message">
                                                    <div class="calc-message"></div>
                                                </div>
						<div class="calc">
							<div class="calc__title">Количество</div>
							<div class="calc__buttons clearfix">
                                                            <a href="#" class="js-minus js-button calc__multi button"></a>
								<input id="qw_qty" type="text" class="qty calc__input js-calc-count" value="1" />
                                                            <a href="#" class="js-plus js-button calc__multi calc__multi--right button"></a>
							</div>
                                                        <span class="calc__title">Цена</span>
							<ul id="qw_select" class="calc_select clearfix">
							</ul>
							<div class="calc_total clearfix">
                                                            <span class="calc__title">Итог</span><span id="qw_total" class="price rouble">0,0</span>
							</div>
							<div class="calc__place calc__place--color-center">
                                                            <?if(strlen($arResult['CITY']['PROPERTIES']['DELIVERY_PRICE_REMARK']['VALUE']) > 0){?>
                                                                <?=$arResult['CITY']['PROPERTIES']['DELIVERY_PRICE_REMARK']['VALUE']?>
                                                            <?}else{?>
                                                                 Цена действительна для <?=$arResult['CITY']['PROPERTIES']['GEN_NAME']['VALUE']?>
                                                            <?}?>
                                                        </div>         
							<div id="qw_addbutton" class="calc__button js-button js-add-basket button" data-element="">В корзину</div>
							<div class="calc__place">Цена может отличаться от заявленной. Мы уточним, если это так</div>
							<div class="calc__place"></div>
						</div>
					</div>
				</form>
                  
              </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default left" data-dismiss="modal">Подробнее</button>
        <button type="button" class="btn btn-default right" data-dismiss="modal">Закрыть</button>
      </div>
    </div>

  </div>
</div>
  <?//($_REQUEST['D']=='Y')?trace($arResult):'';?>  
  <?//($_REQUEST['P']=='Y')?trace($arParams):'';?>  
