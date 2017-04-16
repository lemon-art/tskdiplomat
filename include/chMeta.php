<?
if(isset($_GET['PAGEN_1']) and $_GET['PAGEN_1']!=1)
{
	$title = $APPLICATION->GetPageProperty('title');
	if(strpos($title, 'TSK DIPLOMAT')!==FALSE)
		$title = str_replace('страница '.$_GET['PAGEN_1'].' TSK DIPLOMAT', 'TSK DIPLOMAT', $title);
	if(strpos($title, 'ТСК ДИПЛОМАТ')!==FALSE)
		$title = str_replace('ТСК ДИПЛОМАТ', 'страница '.$_GET['PAGEN_1'].' - ТСК ДИПЛОМАТ', $title);
	else
		$title .= ' - страница '.$_GET['PAGEN_1'];
	$APPLICATION->SetPageProperty('title', $title);
	$APPLICATION->SetPageProperty('keywords', '');
	$APPLICATION->SetPageProperty('description', '');
}
?>