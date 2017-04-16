<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
   <div class="toolbar">
        <?if($toolbarTOP && $arParams['DISPLAY_TOP_PAGER'] == 'Y'){?>
            <? echo $arResult["NAV_STRING"]; ?>
        <?}?>
        <?if($toolbarBOTTOM && $arParams['DISPLAY_BOTTOM_PAGER'] == 'Y'){?>
            <? echo $arResult["NAV_STRING"]; ?>
        <?}?>
        <div class="sorter">
            <p class="view-mode">
                <label>Показать как:</label>
                <?
    switch ($arParams['VIEW_MODE']):
    case 'list':?>
                <a href="<?=$APPLICATION->GetCurPageParam("view=grid",array("view"))?>" title="Плитка" class="grid">Плитка</a>&nbsp;
                <strong title="Список" class="list">Список</strong>&nbsp;
    <?
    break;
    default:?>
                <strong title="Плитка" class="grid">Плитка</strong>&nbsp;
                <a href="<?=$APPLICATION->GetCurPageParam("view=list",array("view"))?>" title="Список" class="list">Список</a>&nbsp;
    <?endswitch;?>
            </p>

            <div class="sort-by">
                <div class="right">
                    <?if($arParams["ELEMENT_SORT_ORDER"] == 'asc'){?>
                     <a class="icon-arrow-up" href="<?=$APPLICATION->GetCurPageParam("dir=desc",array("dir"))?>" title="По убыванию"></a>
                    <?}else{?> 
                     <a class="icon-arrow-down" href="<?=$APPLICATION->GetCurPageParam("dir=asc",array("dir"))?>" title="По возрастанию"></a>
                    <?}?>
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