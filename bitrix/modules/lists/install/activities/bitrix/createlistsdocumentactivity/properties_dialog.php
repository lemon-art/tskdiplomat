<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$runtime = CBPRuntime::GetRuntime();
?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?=GetMessage('BPCLDA_PD_DT')?>:</span></td>
	<td width="60%">
		<select name="lists_document_type" onchange="BPCLDA_changeDocumentType(this.value)">
			<option value=""><?=GetMessage('BPCLDA_PD_CHOOSE_DT')?></option>
			<optgroup label="<?=GetMessage('BPCLDA_PD_LISTS')?>">
			<?
			$iterator = CIBlock::GetList(array('SORT'=>'ASC', 'NAME' => 'ASC'), array(
				'ACTIVE' => 'Y',
				'TYPE' => 'lists',
				'CHECK_PERMISSIONS' => 'N',
			));
			while ($row = $iterator->fetch()):
				$value = 'lists@Bitrix\Lists\BizprocDocumentLists@iblock_'.$row['ID'];
				$selected = ($value == $documentType);
			?>
				<option value="<?=$value?>" <?=$selected?'selected':''?>>[<?=$row['LID']?>] <?=htmlspecialcharsbx($row['NAME'])?></option>
			<? endwhile;?>
			</optgroup>
			<optgroup label="<?=GetMessage('BPCLDA_PD_PROCESSES')?>">
			<?
			$iterator = CIBlock::GetList(array('SORT'=>'ASC', 'NAME' => 'ASC'), array(
				'ACTIVE' => 'Y',
				'TYPE' => COption::getOptionString("lists", "livefeed_iblock_type_id", 'bitrix_processes'),
				'CHECK_PERMISSIONS' => 'N',
			));
			while ($row = $iterator->fetch()):
				$value = 'lists@BizprocDocument@iblock_'.$row['ID'];
				$selected = ($value == $documentType);
			?>
			<option value="<?=$value?>" <?=$selected?'selected':''?>>[<?=$row['LID']?>] <?=htmlspecialcharsbx($row['NAME'])?></option>
			<? endwhile;?>
			</optgroup>
		</select>
	</td>
</tr>
<tbody id="lists_document_fields">
<?=$documentFieldsRender?>
</tbody>

<script>

	var BPCLDA_changeDocumentType = function(documentType)
	{
		var container = BX('lists_document_fields');
		container.innerHTML = '';

		if (!documentType)
			return;

		BX.ajax.post(
			'/bitrix/tools/bizproc_activity_ajax.php',
			{
				'site_id': BX.message('SITE_ID'),
				'sessid' : BX.bitrix_sessid(),
				'document_type' : <?=Cutil::PhpToJSObject($paramDocumentType)?>,
				'activity': 'CreateListsDocumentActivity',
				'lists_document_type': documentType,
				'form_name': <?=Cutil::PhpToJSObject($formName)?>,
				'content_type': 'html'
			},
			function(response)
			{
				if (response)
					container.innerHTML = response;
			}
		);
	};
</script>


