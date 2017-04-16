<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
foreach ($arResult  as $key => $value) {
   if (preg_match('/\((\d+)\)/', $value['TEXT'], $matches)) {
      if (0 == $matches[1]) {
         unset($arResult[$key]);
      }
   }
}
?>

