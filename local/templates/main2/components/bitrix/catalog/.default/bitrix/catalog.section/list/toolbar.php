<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
   <div class="toolbar">
        <?if($arParams['DISPLAY_TOP_PAGER'] == 'Y'){?>
            <? echo $arResult["NAV_STRING"]; ?>
        <?}?>
        <div class="sorter">
            <p class="view-mode">
                <label>Показать как:</label>
                <strong title="Плитка" class="grid">Плитка</strong>&nbsp;
                <a href="<?=$APPLICATION->GetCurPageParam("view=list",array("view"))?>" title="Список" class="list">Список</a>&nbsp;
            </p>

            <div class="sort-by">
                <div class="right">
                     <a class="icon-arrow-up" href="<?=$APPLICATION->GetCurPageParam("dir=desc",array("dir"))?>" title="По убыванию"></a>
                </div>
                <label>Сортировать:</label>
                <select onchange="setLocation(this.value)">
                    <?foreach($arParams['AVAILABLE_SORTS'] as $key => $item):?>
                        <option value="<?=$APPLICATION->GetCurPageParam("order=".$key,array("order"))?>" 
                               <?=($arParams["ELEMENT_SORT_FIELD"] == $item)? ' selected="selected"':''?>>
                            <?=GetMessage('SECTION_NAVBAR_SORT_'.$key)?>
                        </option>
                    <?endforeach;?>
                </select>
            </div>
        </div>        
    </div><!--toolbar-->