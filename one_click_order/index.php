<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(CModule::IncludeModule("catalog")){
    $ID = intval($_GET['product_id']);
    $ar_res = CCatalogProduct::GetByIDEx($ID);
    if (is_array($ar_res)){
        global $USER;            
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();            
        ?>
        <div class="modal_close"></div>
        <div class="one_click_caption">Быстрая покупка</div>
        <div class="product_link_wr">Товар: <a class="product_link" href="<?=$ar_res['DETAIL_PAGE_URL']?>"><?=$ar_res['NAME']?></a>
        </div>
        <form id="one_click_form" onsubmit="return false;" name="one_click_form" action="/one_click_order/handler.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $ID ?>">
            <span>Имя:</span> <input type="text" name="fio" value="<?=$USER->GetFullName()?>" ><br/><br/>
            <span>Телефон:</span> <input type="text" name="phone" value="<?=$arUser['PERSONAL_PHONE']?>"><br/><br/>
            <span>E-mail:</span> <input type="text" name="email" value="<?=$USER->GetEmail()?>"><br/><br/>
            <input type="submit" value="Отправить" onclick="yaCounter22073026.reachGoal('OTPRAVIT'); return true;">        
        </form>
    <?
    }
}
?>