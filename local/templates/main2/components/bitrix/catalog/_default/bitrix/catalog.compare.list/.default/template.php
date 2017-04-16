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

$itemCount = count($arResult);
$isAjax = (isset($_REQUEST["ajax_action"]) && $_REQUEST["ajax_action"] == "Y");
$idCompareCount = 'compareList'.$this->randString();
$obCompare = 'ob'.$idCompareCount;
$idCompareTable = $idCompareCount.'_tbl';
$idCompareRow = $idCompareCount.'_row_';
$idCompareAll = $idCompareCount.'_count';
$mainClass = 'bx_catalog-compare-list';
if ($arParams['POSITION_FIXED'] == 'Y')
{
	$mainClass .= ' fix '.($arParams['POSITION'][0] == 'bottom' ? 'bottom' : 'top').' '.($arParams['POSITION'][1] == 'right' ? 'right' : 'left');
}
$style = ($itemCount == 0 ? ' style="display: none;"' : '');
?><div id="<? echo $idCompareCount; ?>" class="block block-list block-compare <? echo $mainClass; ?> "<? echo $style; ?>><?
unset($style, $mainClass);
if ($isAjax)
{
	$APPLICATION->RestartBuffer();
}
$frame = $this->createFrame($idCompareCount)->begin('');
?>
    <div class="block-title">
        <strong><span><?=GetMessage('CATALOG_COMPARE_TITLE')?>           
                <small>(<span id="<? echo $idCompareAll; ?>"><? echo $itemCount; ?></span>)</small>
                    </span></strong>
    </div>
    
    <div class="bx_catalog_compare_count"><?
?></div><?
if (!empty($arResult))
{
?><div class="bx_catalog_compare_form">
<table id="<? echo $idCompareTable; ?>" class="compare-items">
<tbody><?
	foreach($arResult as $arElement)
	{
		?><tr id="<? echo $idCompareRow.$arElement['PARENT_ID']; ?>">
			<td><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img src="<?=$arElement['IMAGE']['SRC']?>" alt="<?=$arElement["NAME"]?>"/></a></td>
                        <td><a class="product-name" href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></td>
			<td><noindex><a class="btn-remove" href="javascript:void(0);"  data-id="<? echo $arElement['PARENT_ID']; ?>" rel="nofollow"><?=GetMessage("CATALOG_DELETE")?></a></noindex></td>
		</tr><?
	}
?>
</tbody>
</table>
    <div class="actions">
            <button type="button" title="<? echo GetMessage('CP_BCCL_TPL_MESS_COMPARE_PAGE'); ?>" class="button" onclick="setLocation('<? echo $arParams["COMPARE_URL"]; ?>')">
                <span>
                    <span>
                        <? echo GetMessage('CP_BCCL_TPL_MESS_COMPARE_PAGE'); ?>
                    </span>
                </span>
            </button>
        <?/*/TODO
        <a href="" onclick="return confirm(<?=  GetMessage('CATALOG_DELETE_ALL_CONFIRM')?>);"><?=  GetMessage('CATALOG_DELETE_ALL')?></a>
         **/?> 
  
    </div>
<?/*
    <p class="compare-redirect">
        <a href="<? echo $arParams["COMPARE_URL"]; ?>"><? echo GetMessage('CP_BCCL_TPL_MESS_COMPARE_PAGE'); ?></a>
    </p>
 * 
 */?>
</div><?
}
$frame->end();
if ($isAjax)
{
	die();
}
$currentPath = CHTTP::urlDeleteParams(
	$APPLICATION->GetCurPageParam(),
	array(
		$arParams['PRODUCT_ID_VARIABLE'],
		$arParams['ACTION_VARIABLE'],
		'ajax_action'
	),
	array("delete_system_params" => true)
);

$jsParams = array(
	'VISUAL' => array(
		'ID' => $idCompareCount,
	),
	'AJAX' => array(
		'url' => $currentPath,
		'params' => array(
			'ajax_action' => 'Y'
		),
		'templates' => array(
			'delete' => (strpos($currentPath, '?') === false ? '?' : '&').$arParams['ACTION_VARIABLE'].'=DELETE_FROM_COMPARE_LIST&'.$arParams['PRODUCT_ID_VARIABLE'].'='
		)
	),
	'POSITION' => array(
		'fixed' => $arParams['POSITION_FIXED'] == 'Y',
		'align' => array(
			'vertical' => $arParams['POSITION'][0],
			'horizontal' => $arParams['POSITION'][1]
		)
	)
);
?></div>
<script type="text/javascript">
var <? echo $obCompare; ?> = new JCCatalogCompareList(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>)
</script>

<?//trace($arResult)?>