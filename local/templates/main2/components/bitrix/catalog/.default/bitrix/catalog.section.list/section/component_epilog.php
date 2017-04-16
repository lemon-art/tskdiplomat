<?php

        
    $s = '<div class="category-description">';
    if(is_array($arResult['SECTION']['PICTURE'])){
        
    $s .= '<div class="category-image">
            <img src="'.$arResult['SECTION']['PICTURE']['src'].'" alt="'.$arResult['SECTION']['PICTURE']['ALT'].'" />
           </div>';
    }
    if(1 == 0 && strlen($arResult['SECTION']['DESCRIPTION']) > 300){
    $s .='<div class="cat-text">
            <div class="cat-text-container">
                '.$arResult['SECTION']['DESCRIPTION'].'
            </div>
            <span class="cat-text-opener">
                <span class="cat-open">Показать больше <i class="fa fa-chevron-down"></i></span>
                <span class="cat-close">Показать меньше <i class="fa fa-chevron-up"></i></span>
            </span>
        </div>';
    }else{
        $s .= $arResult['SECTION']['DESCRIPTION'];
    }    
    $s .= '</div>';

$GLOBALS['SECTION_DESCRIPTION'] = $s;

?>