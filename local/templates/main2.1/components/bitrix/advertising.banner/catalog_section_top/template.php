<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$frame = $this->createFrame()->begin("");
if(!empty($arResult['BANNER'])){
    ?>
<div class="section-top-banner">
    <?=$arResult["BANNER"];?>
</div>        
<?    
}
$frame->end();
?>