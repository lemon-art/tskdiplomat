<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$frame = $this->createFrame()->begin("");
if(!empty($arResult['BANNERS'])){
    ?>
<div id="bannerCarousel" class="section-top-banner carousel slide" data-ride="carousel">
      <!-- Indicators -->
  <ol class="carousel-indicators">
    <?foreach ($arResult['BANNERS'] as $key => $Banner){?>
    <li data-target="#bannerCarousel" data-slide-to="<?=$key?>" <?=($key==0)?' class="active"':''?>></li>
    <?}?>
  </ol>
      
    <div class="carousel-inner" role="listbox">
    <?foreach ($arResult['BANNERS'] as $key => $Banner){?>
        <div class="item<?=($key == 0)?" active":""?>">
            <?=$Banner;?>
        </div>    
    <?}?>
    </div>    
</div>        
<?    
}
$frame->end();
?>

<!--
<?//trace($arResult)?>
-->