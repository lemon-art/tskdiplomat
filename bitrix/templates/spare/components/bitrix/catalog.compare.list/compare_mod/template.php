<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?php if(count($arResult)>0):?>
<?php $q=count($arResult); ?>
<a href="/catalog/compare" class="comp-count">К сравнению <strong><?=$q;?></strong> тов.</a>
<?php else: ?>
<span class="comp-count">У Вас нет товаров для сравнения</span>
<?php endif;?>