<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $USER, $APPLICATION;

if ( !CModule::IncludeModule("bxmod.seo") ) {
    CAdminMessage::ShowMessage( GetMessage("BXMOD_SEO_CORE_ERROR_MESS") );
}

// Проверка прав доступа
$groupRight = $APPLICATION->GetGroupRight("bxmod_seo");
if ( $groupRight == "D" ) $APPLICATION->AuthForm( GetMessage("ACCESS_DENIED") );

IncludeModuleLangFile(__FILE__);

$aTabs = array(
    0 => array("DIV" => "edit1", "TAB" => GetMessage("BXMOD_SEO_EDIT_TAB_SEO"), "ICON" => "", "TITLE" => GetMessage("BXMOD_SEO_EDIT_PASTE_SEO_CONTENT"))
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$fields = Array(
    0 => Array (
        Array (
            "ID" => "ACTIVE",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_ACTIVE"),
            "FIELD" => 'checkbox',
            "DEFAULT" => "Y",
        ),
        Array (
            "ID" => "KEY",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_KEY"),
            "FIELD" => 'text',
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "URL",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_URL"),
            "FIELD" => 'text',
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "PARENT_ID",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_PARENT"),
            "FIELD" => 'select',
            "VALUES" => Array(
                "0" => GetMessage("BXMOD_SEO_EDIT_FIELD_PARENT_CORE")
            ),
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "SORT",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_SORT"),
            "FIELD" => 'text',
            "DEFAULT" => "10",
        ),
        Array (
            "ID" => "TITLE",
            "NAME" => "<b>Title</b>",
            "FIELD" => 'text',
            "DEFAULT" => "",
            "HEADING" => GetMessage("BXMOD_SEO_EDIT_FIELD_TITLE")
        ),
        Array (
            "ID" => "H1",
            "NAME" => "<b>H1</b>",
            "FIELD" => 'text',
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "META_KEYS",
            "NAME" => "<b>Keywords</b>",
            "FIELD" => 'textarea',
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "META_DESC",
            "NAME" => "<b>Description</b>",
            "FIELD" => 'textarea',
            "DEFAULT" => "",
        ),
        Array (
            "ID" => "SEO_TEXT",
            "NAME" => GetMessage("BXMOD_SEO_EDIT_FIELD_SEO_TEXT"),
            "FIELD" => 'full_textarea',
            "DEFAULT" => "",
            "HEADING" => GetMessage("BXMOD_SEO_EDIT_FIELD_SEO_TEXT_HEAD")
        )
    )
);

// Редактирование
if ( isset( $_GET["id"] ) && $res = BxmodSeo::GetByID( $_GET["id"] ) ) {
    $ITEM = $res->Fetch();
    
    foreach ( $fields AS $key => $val ) {
        foreach ( $val AS $k => $v ) {
            $fields[$key][$k]["VALUE"] = $ITEM[ $v["ID"] ];
        }
    }
    
    // Добавляем кнопки в хлебные крошки
    $chainKeys = BxmodSeo::GetChainKeys( $ITEM["ID"] );
    for( $i = 0; $i < count( $chainKeys ); $i++ ) {
        if ( ($i + 1) >= count( $chainKeys ) ) break;
        $link =  "bxmod_seo_core.php?pid={$chainKeys[$i]["ID"]}&lang=" . LANG;
        $adminChain->AddItem(array(
            "TEXT" => $chainKeys[$i]["KEY"],
            "LINK" => $link,
        ));
    }
    
    $APPLICATION->SetTitle(GetMessage("BXMOD_SEO_EDIT_EDIT") . ": {$ITEM["KEY"]}");
} elseif ( isset( $_GET["pid"] ) ) {
    $fields[0][3]["VALUE"] = intval( $_GET["pid"] );
    
    // Добавляем кнопки в хлебные крошки
    $chainKeys = BxmodSeo::GetChainKeys( intval( $_GET["pid"] ) );
    for( $i = 0; $i < count( $chainKeys ); $i++ ) {
        $link =  "bxmod_seo_core.php?pid={$chainKeys[$i]["ID"]}&lang=" . LANG;
        $adminChain->AddItem(array(
            "TEXT" => $chainKeys[$i]["KEY"],
            "LINK" => $link,
        ));
    }
    
    $APPLICATION->SetTitle(GetMessage("BXMOD_SEO_EDIT_CREATE"));
}

// Сбор дерева фраз
function BuildTree ( $pid, $level = 0 ) {
    global $ITEM;
    
    $result = Array();
    $res = BxmodSeo::GetSubKeys( $pid );
    while ( $arRes = $res->Fetch() ) {
        if ( isset( $ITEM["ID"] ) && $ITEM["ID"] == $arRes["ID"] ) {
            continue;
        }
        $result[$arRes["ID"]] = str_repeat("&mdash;", $level) ." ". $arRes["KEY"];
        $sub = BuildTree ( $arRes["ID"], ($level + 1) );
        if ( !empty( $sub ) ) {
            $result = $result + $sub;
        }
    }
    return $result;
}

$fields[0][3]["VALUES"] = $fields[0][3]["VALUES"] + BuildTree ( 0 );

// Сохранение
if( isset( $_POST["Apply"] ) && check_bitrix_sessid() )
{
    // Редактирование
    if ( isset( $ITEM["ID"] ) )
    {
        $result = BxmodSeo::Edit( $ITEM["ID"], $_POST );
    }
    // Создание нового
    else
    {
        $result = BxmodSeo::Add( $_POST );
    }
    
    // Если сохранить не удалось, то выводим ошибку
    if ( $result !== true ) {
        die( $result );
    }
    
    if ( intval( $_POST["PARENT_ID"] ) > 0 ) {
        LocalRedirect( "bxmod_seo_core.php?lang=" . urlencode(LANGUAGE_ID) );
    } else {
        LocalRedirect( "bxmod_seo_core.php?lang=" . urlencode(LANGUAGE_ID) );
    }
} elseif( isset( $_POST["Cancel"] ) && check_bitrix_sessid() ) {
    LocalRedirect( "bxmod_seo_core.php?lang=" . urlencode(LANGUAGE_ID) );
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&lang=<?=urlencode(LANGUAGE_ID)?><?=isset( $ITEM["ID"] ) ? "&id=" . $ITEM["ID"] : ""?>">
<?
$tabControl->Begin();

foreach ( $aTabs AS $key=>$tab ):
    $tabControl->BeginNextTab();
    foreach ( $fields[ $key ] AS $field ):
?>
        <?if( $field["HEADING"] ):?>
        <tr class="heading">
            <td colspan="2"><?=$field["HEADING"]?></td>
        </tr>
        <?endif?>
        <?if( $field["FIELD"] == "full_textarea" ):?>
            <tr valign="top">
                <td width="100%" colspan="2" class="field-name">
                    <?CFileMan::AddHTMLEditorFrame( "SEO_TEXT", $field["VALUE"], "TEXT_TYPE", "html", array( 'height' => 350, 'width' => '100%' ), "N", 0, "", "", SITE_ID, true, false );?>
                </td>
            </tr>
        <?else:?>
            <tr valign="top">
                <td width="50%" class="field-name">
                    <?=$field["NAME"]?>:
                </td>
                <td width="50%" style="padding-left: 7px;">
                    <?if( $field["FIELD"] == "checkbox" ):?>
                        <?$checked = $field["VALUE"] == "Y" ? ' checked="checked"' : ''?>
                        <input type="checkbox" name="<?=$field["ID"]?>" value="Y" <?=$checked?>>
                    <?elseif( $field["FIELD"] == "text" ):?>
                        <input type="text" name="<?=$field["ID"]?>" value="<?=$field["VALUE"]?>" style="width: 350px;">
                    <?elseif( $field["FIELD"] == "select" ):?>
                        <select style="width: 350px;" name="<?=$field["ID"]?>">
                            <?foreach( $field["VALUES"] AS $k=>$v ):?>
                                <?$selected = $field["VALUE"] == $k ? ' selected="selected" ' : ''?>
                                <option value="<?=$k?>" <?=$selected?>><?=$v?></option>
                            <?endforeach?>
                        </select>
                    <?elseif( $field["FIELD"] == "textarea" ):?>
                        <textarea rows="6" style="width: 350px;" name="<?=$field["ID"]?>"><?=$field["VALUE"]?></textarea>
                    <?endif?>
                </td>
            </tr>
        <?endif?>
        <?if( $field["MESSAGE"] ):?>
        <tr>
            <td align="center" colspan="2">
                <div class="adm-info-message-wrap" align="center">
                    <div class="adm-info-message"><?=$field["MESSAGE"]?></div>
                </div>
            </td>
        </tr>
        <?endif?>
<?
    endforeach;
endforeach;
$tabControl->Buttons();?>
    <input type="hidden" name="siteTabControl_active_tab" value="<?=htmlspecialcharsbx($_REQUEST["siteTabControl_active_tab"])?>">
    <input type="submit" name="Apply" class="adm-btn-save" title="<?=GetMessage("BXMOD_SEO_EDIT_BUTTON_SAVE")?>" value="<?=GetMessage("BXMOD_SEO_EDIT_BUTTON_SAVE")?>">
    <input type="submit" name="Cancel" title="<?=GetMessage("BXMOD_SEO_EDIT_BUTTON_CANCEL")?>" value="Отмена">
    <?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>