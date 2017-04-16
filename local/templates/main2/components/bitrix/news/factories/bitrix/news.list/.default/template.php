<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="factories-list">
    <?if(count($arResult['SECTIONS']) > 0){?>
        <?foreach ($arResult['SECTIONS'] as $arSection){?>
            <?if(count($arSection['PRODUCERS']) > 0){?>
                <?if(strlen($arSection['NAME']) > 0){?>
                    <h2><?=$arSection['NAME']?></h2>
                <?}else{?>
                    <h2>Прочие категории</h2>
                <?}?>
                <table class="factories-table">
                    <tr>
                        <th></th>    
                        <th  class="hidden-xs">Производитель</th>
                    
                    <?
                    if(count($arSection['ITEMS']) > 0){
                        foreach($arSection['ITEMS'] as $k => $v){?>
                        <th  class="hidden-xs"><?=$arResult['ITEMS'][$v]['NAME'];?></th>
                    <?  }
                    }?>
                    </tr>
                    <?foreach ($arResult['PRODUCERS'] as $producerId => $arProducer){
                        if(array_key_exists($producerId, $arSection['PRODUCERS'])){
                    ?>
                    <tr>
                        <td>
                            <?if(!empty($arProducer['PREVIEW_PICTURE'])):?>
                                <a href="<?=$arProducer['DETAIL_PAGE_URL']?>" >
                                    <img class="logo" src="<?=$arProducer['PREVIEW_PICTURE']['SRC']?>" alt="Логотип <?=$arProducer['NAME']?>"/>
                                </a>    
                            <?else:?>
                                &nbsp;
                            <?endif;?>
                            <a  class="visible-xs-block" href="<?=$arProducer['DETAIL_PAGE_URL']?>" >
                                <?=$arProducer['NAME']?>
                            </a>    
                        </td>
                        <td class="hidden-xs">
                            <a href="<?=$arProducer['DETAIL_PAGE_URL']?>" >
                                <?=$arProducer['NAME']?>
                            </a>    
                        </td>
                        <td class="visible-xs products">
                            <ul class="">
                        <?foreach ($arSection['ITEMS'] as $k => $v) {
                                if (in_array($v,$arSection['PRODUCERS'][$producerId])) {?>
                                <li>
                                    <?=$arResult['ITEMS'][$v]['NAME']?>
                                </li>    
                                    <?}
                            }?>
                            </ul>            
                        </td>
                        
                                        <?
                                            foreach ($arSection['ITEMS'] as $k => $v) {?>
                                                <td class="hidden-xs">
                                                    <?if (in_array($v,$arSection['PRODUCERS'][$producerId])) {?>
                                                        <i  title="<?=$arResult['ITEMS'][$v]['NAME']?>" class="fa fa-check"></i>
                                                    <?} else {?>
                                                        &nbsp;
                                                    <?}?>
                                                </td>
                                            <?}?>
                    </tr>
                    <?}
                    
                    }?>
                </table>
            <?}?>
        <?}?>
    <?}?>
</div>
<?if($_REQUEST['D'] == 'Y')trace($arResult);?>