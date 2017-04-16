<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//trace($arResult);
//delayed function must return a string
if(empty($arResult))
	return "";
$strReturn ='<div class="breadcrumbs">'.PHP_EOL;	
$strReturn .= '<ul>'.PHP_EOL;

$num_items = count($arResult);
for($index = 0, $itemSize = $num_items; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if($index == 0){
                $strReturn .= '<li class="home"><a href="/" title="'.$title.'">'.$title.'</a><span>></span></li>'.PHP_EOL;
        }elseif($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
		$strReturn .= '<li><a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">'.$title.'</a><span>></span></li>'.PHP_EOL;
	else
		$strReturn .= '<li><strong>'.$title.'</strong></li>'.PHP_EOL;
}

$strReturn .= '</ul>'.PHP_EOL;
$strReturn .= '</div>'.PHP_EOL;

return $strReturn;
?>