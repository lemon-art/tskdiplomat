<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSFA_PD_STATE") ?>:</td>
	<td width="60%">
		<select name="target_state_name_1">
			<option value=""><?= GetMessage("BPSFA_PD_OTHER") ?></option>
			<?
			$fl = false;
			foreach ($arStates as $key => $value)
			{
				if ($key == $arCurrentValues["target_state_name"])
					$fl = true;
				?><option value="<?= htmlspecialcharsbx($key) ?>"<?= ($key == $arCurrentValues["target_state_name"]) ? " selected" : "" ?>><?= $value ?></option><?
			}
			?>
		</select><br />
		<input type="text" id="id_target_state_name" name="target_state_name" value="<?= !$fl ? htmlspecialcharsbx($arCurrentValues["target_state_name"]) : "" ?>">
		<input type="button" value="..." onclick="BPAShowSelector('id_target_state_name', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"></td>
	<td width="60%">
		<label>
			<input type="checkbox" value="Y" name="cancel_current_state" <?
				if (isset($arCurrentValues["cancel_current_state"]) && $arCurrentValues["cancel_current_state"] == 'Y') echo 'checked'?>/>
			<?= GetMessage("BPSSA_CANCEL_CURRENT_STATE") ?>
		</label>
	</td>
</tr>