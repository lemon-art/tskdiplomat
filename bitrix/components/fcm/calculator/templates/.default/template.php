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
?>
<?if(count($arResult['ITEMS']) > 0):?>
    <!--/b__form/-->
        <?if(strlen($arParams['TITLE']) > 0):?>
            <h2 class="title"><?=$arParams['TITLE']?></h2>
        <?endif;?>
    <div class="calculator user_form">
            <form action="#" class="e__form form-horizontal" method="post" id="calc" onsubmit="return false;" role="form">
            <div class="ef__row control-group">
                <label class="control-label" for="opt1">Расходуемый материал</label>
                <div class="controls">
                <select name="opt1" id="opt1">
                    <?foreach ($arResult['ITEMS'] as $arItem):?>
                        <option
                            <?if($arItem['PROPERTY_CALC_WEIGHT_VALUE']):?>
                                data-weight = "<?=str_replace(',', '.',$arItem['~PROPERTY_CALC_WEIGHT_VALUE'])?>"                            
                            <?endif;?>
                            <?if($arItem['PROPERTY_CALC_MIN_THINK_VALUE']):?>
                                data-mint = "<?=str_replace(',', '.',$arItem['~PROPERTY_CALC_MIN_THINK_VALUE'])?>"                            
                            <?endif;?>
                            <?if($arItem['PROPERTY_CALC_MAX_THINK_VALUE']):?>
                                data-maxt = "<?=str_replace(',', '.',$arItem['~PROPERTY_CALC_MAX_THINK_VALUE'])?>"                            
                            <?endif;?>
                            <?if($arItem['PROPERTY_CALC_CONSUM_VALUE']):?>
                                data-rashod = "<?=  str_replace(',', '.',$arItem['~PROPERTY_CALC_CONSUM_VALUE'])?>" 
                            <?endif;?>
                            <?switch ($arItem['PROPERTY_CALC_FORMULA_ENUM_VALUE']['XML_ID']):
                                case 'UNWEIGHT':?> 
                                        data-unweight-l = "true"
                                    <?break;
                                case 'FORMULA1':?> 
                                        data-formula = "1"
                                    <?break;
                                case 'FORMULA2':?> 
                                        data-formula = "2"
                                    <?break;
                                case 'SIMPLE':
                                default:    
                            endswitch;?>
                            value="<?=$arItem['ID']?>"><?=$arItem['~NAME']?></option>
                    <?endforeach;?>
                </select>
                </div>    
            </div>

                <div class="ef__row formula1  control-group">
                    <label class="control-label" for="inp1">Толщина слоя наносимого материала, мм </label>
                <div class="controls">
                    <input id="inp1" type="text" name="F" value="" class="__fix_w190 input-text" />
                </div>
                </div>
            
            <div class="ef__row formula1  control-group">
                <label class="control-label" for="inp2">Отделываемая площадь, м<sup>2</sup></label>
                <div class="controls">
                <input id="inp2" type="text" name="S" value="" class="__fix_w190 input-text" />
                </div>
            </div>

            <div class="ef__row formula2  control-group">
                <label class="control-label" >Размер кирпича/блока(мм):</label>
                <div class="controls">
                
                <select name="opt2_1" id="opt2_1">
                    <option value="7.197789" selected>380x250x219</option>
                    <option value="20.66667">250x120x60</option>
                    <option value="5.66667">600x250x200</option>
                </select> 
                </div>
            </div>
            <div class="ef__row formula2  control-group">
		<label class="control-label" for="inp6">Количество кирпичей/блоков, м<sup>3</sup></label>
                <div class="controls">
                <input id="inp6" type="text" name="v" value="" class="__fix_w190 input-text" />
                </div>
            </div>

            <div class="ef__row formula2  control-group">
                <label class="control-label" for="inp7">Ширина шва, мм</label>
                <div class="controls">
                
                <input id="inp7" type="text" name="d" value="" class="__fix_w190 input-text" />
                </div>
            </div>

            <div class="buttons-set">
                <button class="button btn" type="submit" name="submit" value="рассчитать">
                    <span>
                        <span>Рассчитать</span>
                    </span>
                </button>    
            </div>
            <div class="control-group">
                <div class="controls">
            <div id='result' class="ef__row e_result">
            </div> 
                </div>
            </div>
        </form>

    </div>
    <?endif?>
    <?//trace($arResult)?>