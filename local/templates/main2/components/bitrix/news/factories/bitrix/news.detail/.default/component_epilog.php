<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)die();

//trace($arResult['SECTIONS']);
$curSection = false;
if(isset($_REQUEST['SECTION']))
    $curSection = htmlspecialchars($_REQUEST['SECTION']);

$this->initComponentTemplate();
$this->__template->SetViewTarget('factory_sections');?>
<div class="sidebar-block">
    <div class="catalog-section-list open-levels">
        
<ul>
    <li<?=(!$curSection)?' class="active"':''?> >
        <a href="<?=$APPLICATION->GetCurPageParam('',array('SECTION'))?>">Все категории</a>
    </li>
    <?foreach ($arResult['SECTIONS'] as $arSection){
        if($arSection['DEPTH_LEVEL'] > 1) continue;
        ?>
    <li<?=($curSection == $arSection['CODE'])?' class="active"':''?> <?/*if($arSection['UF_BACKGROUND_IMG']):?>style="background: #fff url(<?=CFile::GetPath($arSection['UF_BACKGROUND_IMG']);?>) right bottom no-repeat;"<?endif*/?>>
        <a href="<?=$APPLICATION->GetCurPageParam('SECTION='.$arSection['CODE'],array('SECTION'))?>">
            <?=$arSection['NAME']?>
        </a>
        <?if(count($arSection['SECTIONS']) > 0){?>
            <ul>
            <?foreach($arSection['SECTIONS'] as $id){
                $arSubSection = $arResult['SECTIONS'][$id];
                ?>
                <li<?=($curSection == $arSubSection['CODE'])?' class="active"':''?>>
                    <a href="<?=$APPLICATION->GetCurPageParam('SECTION='.$arSubSection['CODE'],array('SECTION'))?>">
                        <?=$arSubSection['NAME']?>
                    </a>
                </li>
            <?}?>
            </ul>    
        <?}?>
    </li>
    <?}?>
</ul>
    </div>
</div>
<?$this->__template->EndViewTarget();?> 
<?
$APPLICATION->AddHeadScript("https://api-maps.yandex.ru/2.1/?load=package.standard&lang=ru_RU");

//ShemaOrg
foreach ($arResult['OG_PROPERTIES'] as $code => $value){
    $APPLICATION->SetPageProperty($code,$value);
} 
?>