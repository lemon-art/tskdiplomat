<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if ( !CModule::IncludeModule("bxmod.seo") ) {
    CAdminMessage::ShowMessage( GetMessage("BXMOD_SEO_CORE_ERROR_MESS") );
}

global $USER, $APPLICATION;

// Проверка прав доступа
$groupRight = $APPLICATION->GetGroupRight("bxmod_seo");
if ( $groupRight == "D" ) $APPLICATION->AuthForm( GetMessage("ACCESS_DENIED") );

IncludeModuleLangFile(__FILE__); 

// подключаем JQuery
CJSCore::Init(array("jquery"));
// подключаем JS
$APPLICATION->AddHeadScript('/bitrix/js/bxmod.seo/admin_core.js');

$sTableID = "bxmod_seo";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

// Фильтр выборки элементов
$arFilter = array();

// Получен ID родителя, значит выводим дерево
if ( isset($_GET['pid']) ) {
    $arFilter['PARENT_ID'] = intval($_GET['pid']);
}

// Групповое действие
if ( $arID = $lAdmin->GroupAction() )
{
    foreach ($arID as $ID)
    {
        if (strlen($ID) <= 0) continue;

        // Удаление элемента
        if ($_REQUEST['action'] == 'delete')
        {
            BxmodSeo::Delete( $ID );
        }
    }
}

// Формируем массив заголовков таблицы.
$lAdmin->AddHeaders(array(
    array("id"=>"ID", "content"=>"ID", "default"=>false),
    array("id"=>"ACTIVE", "content"=>GetMessage("BXMOD_SEO_CORE_HEAD_ACTIVE"), "default"=>true),
    array("id"=>"KEY", "content"=>GetMessage("BXMOD_SEO_CORE_HEAD_KEY"), "default"=>true),
    array("id"=>"SEO_TEXT", "content"=>GetMessage("BXMOD_SEO_CORE_HEAD_SEO_TEXT"), "default"=>true),
    array("id"=>"FIELDS", "content"=>GetMessage("BXMOD_SEO_CORE_HEAD_FIELDS"), "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

// Текущий ключ
$currentKey = Array();

// Добавление строки
function BxmodSeoFieldsAddRow( &$lAdmin, $arFilter, $depth = 0 ) {
    global $currentKey;
    
    // Получаем список элементов
    if ( isset( $arFilter['ID'] ) ) {
        $dbResultList = BxmodSeo::GetByID( $arFilter['ID'] );
        if ( !$dbResultList ) {
            LocalRedirect( "bxmod_seo_core.php?lang=" . urlencode(LANGUAGE_ID) );
        }
    } else {
        $pid = isset($arFilter['PARENT_ID']) ? intval( $arFilter['PARENT_ID'] ) : 0;
        $dbResultList = BxmodSeo::GetSubKeys( $pid );
    }
    
    if ( $dbResultList ) {
        // Преобразуем список элементов в экземпляр класса CAdminResult
        $dbResultList = new CAdminResult($dbResultList, $sTableID);
        // Инициализируем постраничную навигацию
        // $dbResultList->NavStart();
        // отправим вывод переключателя страниц в основной объект $lAdmin
        $lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SMILE_NAV")));
        
        while ($arItem = $dbResultList->GetNext())
        {
            if ( $depth > 0 ) {
                $hiddenCss = 'display: none; overflow: hidden;';
            }
            
            $hiddenCss = strlen( $hiddenCss ) > 0 ? '" style="' . $hiddenCss : '';
            
            $row =& $lAdmin->AddRow($arItem["ID"], $arItem, "bxmod_seo_edit.php?id=".$arItem["ID"]."&lang=".LANG, GetMessage("BXMOD_SEO_CORE_ACT_EDIT") . $hiddenCss . '" data-key-id="bxmodSeoKey' . $arItem["ID"] .'" data-parent-key="bxmodSeoKey' . $arItem["PARENT_ID"]);
            
            // Выбранный ключ
            if ( isset( $arFilter['ID'] ) ) {
                $currentKey = $arItem;
            }
            
            // Блок с ключем
            $collapseButton = '<td style="width: '. (50 + (25 * $depth)) .'px;">';
            $subKeys = BxmodSeo::GetSubKeys( $arItem["ID"] );
            if ( $subKeys->Fetch() ) {
                $collapseButton .= '<a href="#" class="adm-btn bxmodSeoCollapse" title="Развернуть">+</a>';
            }
            $collapseButton .= "</td>";
            
            $row->AddViewField("KEY", '<table><tr>'. $collapseButton .'<td><a href="bxmod_seo_edit.php?id='. $arItem["ID"] .'&lang='. LANG .'"><b>'. $arItem["KEY"] .'</b></a></td></tr></table>');
            
            // Блок с активностью
            $row->AddViewField("ACTIVE", $arItem["ACTIVE"] == "Y" ? GetMessage("BXMOD_SEO_CORE_ACTIVE_YES") : GetMessage("BXMOD_SEO_CORE_ACTIVE_NO"));
            
            // Блок с сеошным текстом
            $row->AddViewField("SEO_TEXT", '<div style="height: 80px; overflow: hidden; max-width: 400px;">' . strip_tags( htmlspecialchars_decode( $arItem["SEO_TEXT"], ENT_QUOTES ) ) . '</div>');
            
            // Блок с тегами
            $row->AddViewField("FIELDS", '<b>URL:</b> '. $arItem["URL"] .'<br><b>Title:</b> '. $arItem["TITLE"] .'<br><b>H1:</b> '. $arItem["H1"] .'<br><b>Keywords</b>: '. $arItem["META_KEYS"] .'<br><b>Description</b>: '. $arItem["META_DESC"]);
            
            // Контектное меню для строки элемента
            $arActions = Array(
                array("ICON"=>"add", "TEXT"=>GetMessage("BXMOD_SEO_CORE_ACT_ADD_CHILD"), "ACTION"=>$lAdmin->ActionRedirect("bxmod_seo_edit.php?pid=".$arItem["ID"]."&lang=".LANG."&".GetFilterParams("filter_")."")),
                array("ICON"=>"edit", "TEXT"=>GetMessage("BXMOD_SEO_CORE_ACT_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("bxmod_seo_edit.php?id=".$arItem["ID"]."&lang=".LANG."&".GetFilterParams("filter_")."")),
                array("SEPARATOR" => true),
                array("ICON"=>"delete", "TEXT"=>GetMessage("BXMOD_SEO_CORE_ACT_DELETE"), "ACTION"=>"if(confirm('". GetMessage("BXMOD_SEO_CORE_ACT_DEL_CONFIRM") ."')) ".$lAdmin->ActionDoGroup($arItem["ID"], "delete"))
            );
            
            $row->AddActions($arActions);
            
            BxmodSeoFieldsAddRow( $lAdmin, Array("PARENT_ID" => $arItem["ID"]), ($depth + 1));
        }
    }
}

// Добавляем строки в таблицу вывода
BxmodSeoFieldsAddRow( $lAdmin, Array("PARENT_ID" => 0) );

// Установка заголовка страницы
$APPLICATION->SetTitle( GetMessage("BXMOD_SEO_CORE_TITLE") );

// Список групповых операций, отображается под таблицей
$lAdmin->AddGroupActionTable(
    array(
        "delete" => true,
    )
);

// Кнопки в шапке таблицы (Добавить, Создать и т.п.)
$aContext = array(
    array(
        "TEXT" => GetMessage("BXMOD_SEO_CORE_BUTTON_ADD_NEW"),
        "LINK" => "bxmod_seo_edit.php?lang=" . LANG,
        "TITLE" => GetMessage("BXMOD_SEO_CORE_BUTTON_ADD_NEW_ALT"),
        "ICON" => "btn_new",
    ),
);

$lAdmin->AddAdminContextMenu($aContext);

// Вызывается для обработки альтернативных методов вывода (Ajax и т.п.)
$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Выводим таблицу
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>