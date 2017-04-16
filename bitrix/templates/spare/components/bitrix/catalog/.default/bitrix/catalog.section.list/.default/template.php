<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["SECTIONS"]) > 0 ):?>
<div id="subcategories" class="clearfix">
<? if ( $arResult["SECTION"]["ID"] == "24") { ?>
<div class="h2style">В разделе:</div>
<style>
.smd a
{
    color: #fe8900;
    font-weight: bold;
    margin-right: 20px;
    font-size: 13px;
}
#center_column #subcategories ul li a span {
    display: block;
    padding: 10px 0 0 0;
    line-height: 14px;
    margin-left: -8px;
    float: left;
    margin-left: 1px;
    max-width: 65px;
}
#center_column #subcategories ul li strong {
    width: 12px;
    height: 12px;
    display: block;
    float: right;
    /* position: absolute; */
    /* bottom: 10px; */
    /* right: 10px; */
    margin-top: 10px;
	position: inherit;
}
#center_column #subcategories ul li a {
    width: 82px;
	height: 120px;
    display: block;
    padding: 20px;
    border-width: 1px;
    border-style: solid;
    text-decoration: none;
}
#center_column #subcategories ul li {
    float: left;
    margin: 20px 10px 20px 0;
    position: relative;
	height: 130px;
}

</style>
<?
function arr_t($art)
{
$arFilter = array( 
'IBLOCK_ID' => 3, 
'ACTIVE' => 'Y',
'SECTION_ID' => $art,
); 
$res = CIBlockElement::GetList(false, $arFilter, array('IBLOCK_ID')); 
if ($el = $res->Fetch()) 
return $el['CNT'];
}

	
?>
<ul class="neww">
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/shtukaturki_all/">Штукатурки (<?=arr_t(Array(98,473,121,495,127,537,441,115,116,387))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/shpatlevki_all/">Шпатлевки (<?=arr_t(Array(99,474,122,499,496,128,538,114,444))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/gruntovki_all/">Грунтовки  (<?=arr_t(Array(103,125,135,446,117))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/plitochnye_klei_all/">Плиточные клеи (<?=arr_t(Array(96,118,493,443))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/gidroizolyaciya_all/">Гидроизоляционные составы (<?=arr_t(Array(102,476,389,511,497,131,137,480,138,512))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/smesi_dlya_polov_all/">Смеси для устройста полов (<?=arr_t(Array(97,475,123,494,500,129,442,542))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/nalivnoy_pol_all/">Наливной пол (<?=arr_t(Array(113,539))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/kladochnye_rastvory_all/">Кладочные растворы (<?=arr_t(Array(101,477,388))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/montazhnye_smesi_all/">Монтажные смеси (<?=arr_t(Array(100,119,130,445))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/zatirki_all/">Затирки для швов (<?=arr_t(Array(120,316,317))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/kleevye_smesi_all/">Клеевые смеси (<?=arr_t(Array(472,126,540,541,107))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/germetiki_all/">Герметики (<?=arr_t(Array(112))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/prochie_materialy_all/">Прочие материалы (<?=arr_t(Array(447,111,498,124,105,510))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/cement_i_dobavki_all/">Цемент, спеццемен, огнеупорные добавки (<?=arr_t(Array(132,104))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/zimnie_smesi_all/">Зимние смеси (<?=arr_t(Array(481))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">
<a  class="bgcolor bordercolor" style="width:200px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="http://tskdiplomat.ru/catalog/remontnye_smesi_all/">Ремонтные смеси (<?=arr_t(Array(93))?>)</a></li>
<li style="height:auto; margin-top:10px; margin-bottom:1px;">&nbsp;</li>
</ul>
<div style="clear:both;">&nbsp;</div>
<? } ?>
<ul class="neww">
<?

$TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = $TOP_DEPTH;
$i=0;
$caption=false;

foreach($arResult["SECTIONS"] as $arSection):?>
        <? if ($arResult['SECTIONS'][$i]['ELEMENT_CNT'] != 0): ?>
        <?php if( $caption === false ){ ?><div class="h2style">В разделе:</div><?php } ?>
	<li id="<?=$this->GetEditAreaId($arSection['ID']);?>" style="height:auto; margin-top:10px; margin-bottom:1px;">
            <a  class="bgcolor bordercolor" style="width:202px; height:auto; border:none;background:none; padding:0; text-decoration:underline;" href="<?=$arSection["SECTION_PAGE_URL"]?>" title="<?=$arSection["NAME"]?>">
                <?=$arSection["NAME"]?> (<? echo $arResult['SECTIONS'][$i]['ELEMENT_CNT']; ?>)
            </a>	
        </li>
        <?php
            $caption = true;
            endif; 
        ?>
<?php $i++; ?>       
<?endforeach;?>	<? $i=0; ?>
</ul>
</div>
<?endif?>
<?//="<pre>".print_r($arResult,1)."</pre>";?>
<?/*
<div class="catalog-section-list">
<?
$TOP_DEPTH = $arResult["SECTION"]["DEPTH_LEVEL"];
$CURRENT_DEPTH = $TOP_DEPTH;

foreach($arResult["SECTIONS"] as $arSection):
	$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));
	if($CURRENT_DEPTH < $arSection["DEPTH_LEVEL"])
		echo "\n",str_repeat("\t", $arSection["DEPTH_LEVEL"]-$TOP_DEPTH),"<ul>";
	elseif($CURRENT_DEPTH == $arSection["DEPTH_LEVEL"])
		echo "</li>";
	else
	{
		while($CURRENT_DEPTH > $arSection["DEPTH_LEVEL"])
		{
			echo "</li>";
			echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</ul>","\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH-1);
			$CURRENT_DEPTH--;
		}
		echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</li>";
	}

	echo "\n",str_repeat("\t", $arSection["DEPTH_LEVEL"]-$TOP_DEPTH);
	?><li id="<?=$this->GetEditAreaId($arSection['ID']);?>"><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?><?if($arParams["COUNT_ELEMENTS"]):?>&nbsp;(<?=$arSection["ELEMENT_CNT"]?>)<?endif;?></a><?

	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
endforeach;

while($CURRENT_DEPTH > $TOP_DEPTH)
{
	echo "</li>";
	echo "\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH),"</ul>","\n",str_repeat("\t", $CURRENT_DEPTH-$TOP_DEPTH-1);
	$CURRENT_DEPTH--;
}
?>
 * */?>