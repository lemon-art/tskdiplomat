<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2016 Bitrix
 *
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDataBase $DB
 *
TABS unused yet
SHOW_FORM_TAG true|false
FORM_ID
THEME_GRID_ID
~DATA - obsolete
MAX_FILE_SIZE unused yet
RESTRICTED_MODE true|false
BUTTONS
DATE_TIME_FORMAT - for datetimePicker
DATE_FORMAT - for datetimePicker
TIME_FORMAT - for datetimePicker
*/


if (empty($arParams["DATE_TIME_FORMAT"]) ||  $arParams["DATE_TIME_FORMAT"] == "FULL")
	$arParams["DATE_TIME_FORMAT"]= $DB->DateFormatToPHP(FORMAT_DATETIME);
$arParams["DATE_TIME_FORMAT"] = preg_replace('/[\/.,\s:][s]/', '', $arParams["DATE_TIME_FORMAT"]);

if (!$arParams["TIME_FORMAT"])
	$arParams["TIME_FORMAT"] = preg_replace(array('/[dDjlFmMnYyo]/', '/^[\/.,\s]+/', '/[\/.,\s]+$/'), "", $arParams["DATE_TIME_FORMAT"]);
if (!$arParams["DATE_FORMAT"])
	$arParams["DATE_FORMAT"] = trim(str_replace($arParams["TIME_FORMAT"], "", $arParams["DATE_TIME_FORMAT"]));

$arParams["DATE_TIME_FORMAT"] = array(
	"tomorrow" => "tomorrow, ".$arParams["TIME_FORMAT"],
	"today" => "today, ".$arParams["TIME_FORMAT"],
	"yesterday" => "yesterday, ".$arParams["TIME_FORMAT"],
	"" => $arParams["DATE_TIME_FORMAT"]
);
$arParams["DATE_FORMAT"] = array(
	"tommorow" => "tommorow",
	"today" => "today",
	"yesterday" => "yesterday",
	"" => $arParams["DATE_FORMAT"]
);

CJSCore::GetCoreMessages();
global $APPLICATION;
$APPLICATION->SetPageProperty('BodyClass', 'mobile-grid-field-form');
$APPLICATION->SetAdditionalCSS($templateFolder."/style_add.css");
CUtil::InitJSCore(array('ajax', 'date'));
$userUrl = str_replace("//", "/", "/".SITE_DIR."mobile/users/?user_id=#ID#");
$groupUrl = str_replace("//", "/", "/".SITE_DIR."mobile/log/?group_id=#ID#");

?>
<div class="bx-interface-form mobile-grid <?if ($arParams["RESTRICTED_MODE"]) echo "mobile-grid-modified";?>">
<?if($arParams["SHOW_FORM_TAG"]):?>
<form name="<?=$arParams["FORM_ID"]?>" id="<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
<?=bitrix_sessid_post();?>
<input type="hidden" id="<?=$arParams["FORM_ID"]?>_active_tab" name="<?=$arParams["FORM_ID"]?>_active_tab" value="<?=htmlspecialcharsbx($arResult["SELECTED_TAB"])?>">
<?endif;
?><div class="bx-tabs"><?
$i = 0;
$jsObjects = array();
foreach($arResult["TABS"] as $tab)
{
//	$bSelected = ($tab["id"] == $arResult["SELECTED_TAB"]);
	$bWasRequired = false;
	$sections = array();
?>
	<div id="tab_<?=$tab["id"]?>"><?
	/*?>
		<div
			title="<?=htmlspecialcharsbx($tab["title"])?>"
			id="tab_cont_<?=$tab["id"]?>"
			class="bx-tab-container<?=($bSelected? "-selected":"")?>">
			<?=htmlspecialcharsbx($tab["name"])?>
		</div><?*/ // Our design is not support this 13.11.2015 ?>
		<div
			id="inner_tab_<?=$tab["id"]?>"
			class="bx-edit-tab-inner"<?if($tab["id"] <> $arResult["SELECTED_TAB"]) echo ' style="display:none;"'?>>
			<? if($tab["icon"] <> ""): ?> <div class="bx-icon <?=htmlspecialcharsbx($tab["icon"])?>"></div> <? endif; ?>
			<?/*?><div class="bx-form-title"><?=htmlspecialcharsbx($tab["title"])?></div><?*/ // Our design is not support this 13.11.2015 ?>
			<div style="height: 100%;">
				<div class="bx-edit-table <?=(isset($tab["class"]) ? $tab['class'] : '')?>" id="<?=$tab["id"]?>_edit_table"><?
					foreach($tab["fields"] as $field)
					{
						if(!is_array($field))
							continue;

						if ($arParams["RESTRICTED_MODE"] && empty($field["value"]) && $field["value"] !== "0")
							continue;

						$style = '';
						if(isset($field["show"]))
						{
							if($field["show"] == "N")
							{
								$style = "display:none;";
							}
						}

						if ($field["type"] == 'section')
						{
							if (!empty($field["id"]))
							{
								$expanded = ($field["expanded"] == "Y" || $field["expanded"] == true);
								$jsObjects[] = $sections[] = $field["id"];

								?><div class="mobile-grid-field <?= htmlspecialcharsbx($field['class']) ?> mobile-grid-field-<?if($expanded): ?>expanded<? else: ?>collapsed<? endif;?>-head" id="<?=$field["id"]?>" bx-type="section" <?if(!empty($style)): ?> style="<?= $style ?>"<? endif ?>><?
									if(array_key_exists("name", $field))
									{
										?><span class="mobile-grid-field-title"><?=htmlspecialcharsEx($field["name"])?></span><?
									}
								?><div class="mobile-grid-field-select"><?
										?><a href="javascript:void(0);"><?= htmlspecialcharsbx($field["value"]) ?></a><?
									?></div><?
								?></div><?
								?><div class="mobile-grid-field mobile-grid-field-<?if($expanded): ?>expanded<? else: ?>collapsed<? endif;?>-body" id="section_<?=$field["id"]?>_body"<?if(!empty($style)): ?> style="<?= $style ?>"<? endif ?>><?
							}
							else
							{
								?><div class="mobile-grid-field<?if(array_key_exists("class", $field)): ?> <?= htmlspecialcharsbx($field['class']) ?><? endif ?>"<?
								if(!empty($style)): ?> style="<?= $style ?>"<? endif ?>><?
								?><span class="mobile-grid-field-head" onclick=""><?= htmlspecialcharsbx($field["name"]) ?></span><?
								?></div><?
							}
						}
						else
						{
							if (!empty($sections))
							{
								while (($section = end($sections)) && $section)
								{
									if ($field["section"] == $section)
									{
										break;
									}
									else
									{
										?></div><?
										array_pop($sections);
									}
								}
							}

							$val = (isset($field["value"]) ? $field["value"] : $arParams["~DATA"][$field["id"]]);

							//default attributes
							if(!is_array($field["params"]))
								$field["params"] = array();
							if($field["type"] == '' || $field["type"] == 'text')
							{
								if($field["params"]["size"] == '')
									$field["params"]["size"] = "30";
							}
							elseif($field["type"] == 'textarea')
							{
								if($field["params"]["cols"] == '')
									$field["params"]["cols"] = "40";
								if($field["params"]["rows"] == '')
									$field["params"]["rows"] = "3";
							}
							elseif($field["type"] == 'date')
							{
								if($field["params"]["size"] == '')
									$field["params"]["size"] = "10";
							}

							$params = '';
							if(is_array($field["params"]) && $field["type"] <> 'file')
							{
								foreach($field["params"] as $p => $v)
									$params .= ' ' . $p . '="' . $v . '"';
							}
							$field["~id"] = "bx_".preg_replace("/[^a-z0-9_-]/i", "_", $field["id"]);

							$bWasRequired = ($bWasRequired ? : $field["required"]);
							$className = "";
							$html = "";
							switch($field["type"])
							{
								case 'custom':
									$className = "custom";
									$html = $val;
								break;
								case 'text':
									$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
									$val = htmlspecialcharsbx($val);
									$className = "text";
									if ($arParams["RESTRICTED_MODE"])
									{
										$html = "<input type='hidden' bx-type='text' placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />".
										"<span id=\"{$field["~id"]}_target\" class=\"text\">".($val==""?"<span class='placeholder'>".$placeholder."</span>" : $val)."</span>";
									}
									else
									{
										$html = "<input class='mobile-grid-field-data' type='text' placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />";
									}
									$jsObjects[] = $field["~id"];
									break;
								case 'number':
									$className = "number";
									$valFrom = $field["item"]["from"];
									$valTo = $field["item"]["to"];
									$html = "<input type='text' name='".$field["id"]."_from' id=\"{$field["~id"]}\" value='".htmlspecialcharsbx($valFrom)."'> ...
									<input type='text' name='".$field["id"]."_to' value='".htmlspecialcharsbx($valTo)."'>";
									break;
								case 'textarea':
									$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
									$val = htmlspecialcharsbx($val);
									$className = "textarea";
									if ($arParams["RESTRICTED_MODE"])
									{
										$html = "<input type='hidden' bx-type='textarea' placeholder=\"{$placeholder}\" name=\"{$field["id"]}\" id=\"{$field["~id"]}\" $params value=\"$val\" />".
										"<span id=\"{$field["~id"]}_target\">".($val==""?"<span class='placeholder'>".$placeholder."</span>" : $val)."</span>";
									}
									else
									{
										$html = "<textarea name=\"{$field["id"]}\" id=\"{$field["~id"]}\" placeholder=\"$placeholder\" $params>$val</textarea>";
									}
									$jsObjects[] = $field["~id"];
									break;
								case 'select-group':
								case 'select-user':
									$url = ($field["type"] == 'select-user' ? $userUrl : $groupUrl);
									$className = "select-user";
									$jsObjects[] = $field["~id"];
									ob_start();
									$html = '';

									if (is_array($field["item"]))
									{
										$item = array_change_key_case($field["item"], CASE_LOWER);
										$html .= "<option value=\"{$item["id"]}\" selected>{$item["id"]}</option>";

										?><div class="mobile-grid-field-select-user-item-outer"><div class="mobile-grid-field-select-user-item"><?
											if ($field["canDrop"] !== false):
												?><del id="<?=$field["~id"]?>_del_<?=$item["id"]?>"></del><?
											endif;
											?>
											<div class="avatar"<?if(!empty($item["avatar"])):?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<?endif;?>></div>
											<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle : true});"><?=htmlspecialcharsbx($item["name"])?></span>
										</div></div><?
									}
									$users = ob_get_clean();
									$html = "<select name=\"{$field["id"]}\" bx-type=\"{$field["type"]}\" bx-can-drop=\"".($field["canDrop"] === false ? "false" : "")."\" id=\"{$field["~id"]}\"{$params}\">".$html."</select>".
										"<div id=\"{$field["~id"]}_target\" class=\"mobile-grid-field-select-user-container\">".$users."</div>".
										"<a class=\"mobile-grid-button select-user add\" id=\"{$field["~id"]}_select\" href=\"#\">".GetMessage("interface_form_change")."</a>";
									break;
								case 'group':
								case 'user':
									$url = ($field["type"] == 'user' ? $userUrl : $groupUrl);
									$className = "select-user";

									ob_start();
									if (is_array($field["item"]))
									{
										$item = array_change_key_case($field["item"], CASE_LOWER);
										?><div class="mobile-grid-field-select-user-item-outer"><div class="mobile-grid-field-select-user-item">
											<div class="avatar"<?if(!empty($item["avatar"])):?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<?endif;?>></div>
											<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle : true});"><?=htmlspecialcharsbx($item["name"])?></span>
										</div></div><?
									}
									$users = ob_get_clean();
									$html = "<div class=\"mobile-grid-field-select-user-container\">".$users."</div>";
									break;
								case 'select-groups':
								case 'select-users':
									$field["type"] = ($field["type"] == "select-users" ? "select-user" : "select-group");
									$url = ($field["type"] == 'select-user' ? $userUrl : $groupUrl);
									$className = "select-user";
									$jsObjects[] = $field["~id"];
									$html = '';
									ob_start();
									if ($field["items"])
									{
										$val = is_array($val) ? $val : array($val);
										foreach($field["items"] as $item)
										{
											$item = array_change_key_case($item, CASE_LOWER);
											if (!in_array($item["id"], $val))
												continue;
											$html .= "<option value=\"{$item["id"]}\" selected>{$item["id"]}</option>";
											?><div class="mobile-grid-field-select-user-item"><?
												if ($field["canDrop"] !== false):
													?><del id="<?=$field["~id"]?>_del_<?=$item["id"]?>"></del><?
												elseif (is_array($field["menu"])):
													?><i class="mobile-grid-menu" id="<?=$field["~id"]?>_menu_<?=$item["id"]?>"></i><?
												endif;
												?>
												<div class="avatar"<?if (!empty($item["avatar"])): ?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<? endif; ?>></div>
												<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle:true});"><?=htmlspecialcharsbx($item["name"])?></span>
											</div><?
										}
									}
									$users = ob_get_clean();

									$html = "<select name=\"{$field["id"]}\" bx-type=\"{$field["type"]}\" bx-can-drop=\"".($field["canDrop"] === false ? "false" : "")."\" id=\"{$field["~id"]}\" multiple {$params}\">".$html."</select>".
										"<div id=\"{$field["~id"]}_target\" class=\"mobile-grid-field-select-user-container\">".$users."</div>".
										"<a class=\"mobile-grid-button select-user add\" id=\"{$field["~id"]}_select\" href=\"#\">".GetMessage("interface_form_add")."</a>";
									break;
								case 'groups':
								case 'users':
									$field["type"] = ($field["type"] == "users" ? "user" : "group");
									$url = ($field["type"] == 'select-user' ? $userUrl : $groupUrl);
									$className = "select-user";
									$html = '';
									ob_start();
									if ($field["items"])
									{
										foreach($field["items"] as $item)
										{
											$item = array_change_key_case($item, CASE_LOWER);
											?><div class="mobile-grid-field-select-user-item">
												<div class="avatar"<?if (!empty($item["avatar"])): ?> style="background-image:url('<?=htmlspecialcharsbx($item["avatar"])?>')"<? endif; ?>></div>
												<span onclick="BXMobileApp.PageManager.loadPageBlank({url: '<?=str_replace("#ID#", $item["id"], $url)?>',bx24ModernStyle:true});"><?=htmlspecialcharsbx($item["name"])?></span>
											</div><?
										}
									}
									$users = ob_get_clean();

									$html = "<div class=\"mobile-grid-field-select-user-container\">".$users."</div>";
									break;
								case 'list':
								case 'select':
									$className = "select";
									if (is_array($field["items"]))
									{
										if(!is_array($val))
											$val = array($val);
										$items = array();

										$html = "<select name=\"{$field["id"]}\" id=\"{$field["~id"]}\"{$params}>";
										foreach($field["items"] as $k => $v):
											$items[$k] = $v;
											$s = (in_array($k, $val) ? " selected" : "");
											$k = htmlspecialcharsbx($k);
											$v = htmlspecialcharsbx($v);
											$html .= "<option value=\"{$k}\" {$s}>$v</option>";
										endforeach;
										$html .= "</select>";

										$selected = array_intersect(array_keys($field["items"]), $val);
										if (is_array($field["params"]) && array_key_exists("multiple", $field["params"]))
										{
											$html .= "<span id=\"{$field["~id"]}_target\">";
											foreach ($selected as $k)
											{
												$v = htmlspecialcharsbx($items[$k]);
												$html .= "<a href=\"javascript:void();\">$v</a>"; // TODO we need to decide how should it looks
											}
											$html .= "</span>".
												"<a class=\"mobile-grid-button select-change\" href=\"#\" id=\"{$field["~id"]}_select\">".GetMessage("interface_form_change")."</a>";
										}
										else
										{
											if (!$selected)
											{
												$html .= "<a href=\"#\" id=\"{$field["~id"]}_select\"><span style=\"/*color:grey*/\">".GetMessage("interface_form_select")."</span></a>";
											}
											else
											{
												$k = reset($selected);
												$v = htmlspecialcharsbx($items[$k]);
												$html .= "<a href='#' id=\"{$field["~id"]}_select\">$v</a>";
											}
										}
										$jsObjects[] = $field["~id"];
									}
									break;
								case 'checkbox':
									$className = "checkbox";
									$items = (is_array($field["items"]) ? $field["items"] : array("Y" => "Y"));
									$val = (is_array($val) ? $val : array($val));
									foreach($items as $k => $v)
									{
										$i++;
										$k = htmlspecialcharsbx($k);
										$v = htmlspecialcharsbx($v);
										$checked = (in_array($k, $val) ? ' checked' : '');
										$html .= "<label for=\"{$field["~id"]}{$i}\">".
												"<input type=\"checkbox\" id=\"{$field["~id"]}{$i}\" name=\"{$field["id"]}\" value=\"{$k}\" {$checked}{$params} />".
												"<span>{$v}</span>".
											"</label>";
										$jsObjects[] = $field["~id"].$i;
									}
									break;
								case 'radio':
									$className = "radio";
									$items = (is_array($field["items"]) ? $field["items"] : array("Y" => "Y"));
									$val = (is_array($val) ? $val : array($val));
									foreach($items as $k => $v)
									{
										$i++;
										$k = htmlspecialcharsbx($k);
										$v = htmlspecialcharsbx($v);
										$checked = (in_array($k, $val) ? ' checked' : '');
										$html .= "<label for=\"{$field["~id"]}{$i}\">".
												"<input type=\"radio\" id=\"{$field["~id"]}{$i}\" name=\"{$field["id"]}\" value=\"{$k}\" {$checked}{$params} />".
												"<span>{$v}</span>".
											"</label>";
										$jsObjects[] = $field["~id"].$i;
									}
									break;
								case 'diskview':
								case 'disk':
									$val = is_array($val) ? $val : array();
									$className = "file";
									ob_start();
									$APPLICATION->IncludeComponent("bitrix:system.field.edit", "disk_file",
										array(
											"arUserField" => $val,
											"MOBILE" => "Y",
											"CAN_EDIT" => ($field["type"] == "disk"),
											"formId" => $arParams["FORM_ID"]
										),
										$component,
										array("HIDE_ICONS" => "Y")
									);
									if ($field["type"] == "disk")
									{
										?><input type="hidden" id="<?=$field["~id"]?>" value="<?=$val["FIELD_NAME"]?>" bx-type="disk_file" /><?
										$jsObjects[] = $field["~id"];
									}
									$html = ob_get_clean();
									break;
								case 'file':
									ob_start();
									$arDefParams = array("iMaxW" => 150, "iMaxH" => 150, "sParams" => "border=0", "strImageUrl" => "", "bPopup" => true, "sPopupTitle" => false, "size" => 20);
									foreach($arDefParams as $k => $v)
										if(!array_key_exists($k, $field["params"]))
											$field["params"][$k] = $v;

									echo CFile::InputFile($field["id"], $field["params"]["size"], $val);
									if($val <> '')
										echo '<br>' . CFile::ShowImage($val, $field["params"]["iMaxW"], $field["params"]["iMaxH"], $field["params"]["sParams"], $field["params"]["strImageUrl"], $field["params"]["bPopup"], $field["params"]["sPopupTitle"]);
									break;
								case 'datetime':
								case 'date':
								case 'time':
									$className = "date";
									$placeholder = htmlspecialcharsbx($field["placeholder"] ?: $field["name"]);
									$html = "<input type='hidden' bx-type=\"{$field["type"]}\" id=\"{$field["~id"]}\" name=\"{$field["id"]}\" {$params} value=\"{$val}\" />";

									$format = ($field["type"] == "datetime" ? $arParams["DATE_TIME_FORMAT"] : ($field["type"] == "date" ? $arParams["DATE_FORMAT"] : $arParams["TIME_FORMAT"]));

								//	if ($val)
								//		$val = FormatDate($format, MakeTimeStamp($val));

									$html .= "<span placeholder=\"{$placeholder}\" id=\"{$field["~id"]}_container\">".($val? $val : $placeholder)."</span>";

									if ($field["canDrop"] !== false)
										$html .= '<del id="'.$field["~id"].'_del" '.($val ? '' :'style="display:none"').'></del>';

									$jsObjects[] = $field["~id"];
									break;
								default:
									$className = "label";
									$html = $val;
								break;
							}

							?><div class="mobile-grid-field <?= htmlspecialcharsbx($field['class']) ?>"<?if(!empty($style)): ?> style="<?= $style ?>"<? endif;
							if(!empty($field['fieldId'])): ?> id="<?= $field['fieldId'] ?>"<? endif ?>><?
								if(array_key_exists("name", $field))
								{
									?><span class="mobile-grid-field-title <?= ($field["required"] ? "bx-field-required" : "") ?>" <?
									if($field["title"] <> '') echo ' title="' . htmlspecialcharsEx($field["title"]) . '"'?>><?
									?><?if(strlen($field["name"])):?><?=htmlspecialcharsEx($field["name"])?><? endif ?>
									</span><?
								}
								?><div class="mobile-grid-field-<?=$className?>"><?=preg_replace("/[\t\n]+/i", "", $html)?></div><?
							?></div><?
						}
					}
?>
				</div>
			</div>
		</div>
	</div>
<?
}
?></div><?
if (isset($arParams["BUTTONS"]) && is_string($arParams["BUTTONS"]) && strtolower($arParams["BUTTONS"]) == "app")
{

}
else if (isset($arParams["BUTTONS"]))
{
?>
	<div class="mobile-grid-button-panel" id="buttons_<?=$arParams["FORM_ID"]?>">
	<?if($arParams["~BUTTONS"]["standard_buttons"] !== false):?>
		<a href="#" id="submit_<?=$arParams["FORM_ID"]?>"><?=GetMessage("interface_form_save")?></a>
		<a href="#" id="cancel_<?=$arParams["FORM_ID"]?>"><?=GetMessage("interface_form_cancel")?></a>
	<?endif?>
	<?=$arParams["~BUTTONS"]["custom_html"]?>
	</div>
<?
}
if($arParams["SHOW_FORM_TAG"]):?>
</form>
<?endif;?>
<script>
BX.message({
	interface_form_select : '<?=GetMessageJS("interface_form_select")?>',
	interface_form_save : '<?=GetMessageJS("interface_form_save")?>',
	interface_form_cancel : '<?=GetMessageJS("interface_form_cancel")?>',
	interface_form_user_url : '<?=CUtil::JSEscape($userUrl)?>',
	interface_form_group_url : '<?=CUtil::JSEscape($groupUrl)?>'
});
BX.ready(function() {
	new BX.Mobile.Grid.Form(<?=CUtil::PhpToJSObject(array(
		"gridId" => $arParams["THEME_GRID_ID"],
		"formId" => $arParams["FORM_ID"],
		"restrictedMode" => $arParams["RESTRICTED_MODE"],
		"customElements" => $jsObjects,
		"buttons" => (isset($arParams["BUTTONS"]) && is_string($arParams["BUTTONS"]) ? strtolower($arParams["BUTTONS"]) : "none"),
		"format" => (isset($arParams["~DATE_TIME_FORMAT"]) ? array("datetime" => $arParams["~DATE_TIME_FORMAT"]) : array()) +
			(isset($arParams["~DATE_FORMAT"]) ? array("date" => $arParams["~DATE_FORMAT"]) : array()) +
			(isset($arParams["~TIME_FORMAT"]) ? array("time" => $arParams["~TIME_FORMAT"]) : array())
	))?>);
});
</script>
</div>
