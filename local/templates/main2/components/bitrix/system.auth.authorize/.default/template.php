<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="account-login">
	<?
	ShowMessage($arParams["~AUTH_RESULT"]);
	ShowMessage($arResult['ERROR_MESSAGE']);
	?>
    <div class="page-title">
        <h1><?=GetMessage('AUTH_TITLE');?></h1>
    </div>
    <form name="form_auth" method="post" target="_top" action="<?=SITE_DIR?>auth/<?//=$arResult["AUTH_URL"]?>" class="bx_auth_form"  id="login-form">
		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="AUTH" />
		<?if (strlen($arParams["BACKURL"]) > 0 || strlen($arResult["BACKURL"]) > 0):?>
		<input type="hidden" name="backurl" value="<?=($arParams["BACKURL"] ? $arParams["BACKURL"] : $arResult["BACKURL"])?>" />
		<?endif?>
		<?foreach ($arResult["POST"] as $key => $value):?>
		<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?endforeach?>  
                
        <div class="col2-set">
            <div class="wrapper">
                <div class="registered-users-wrapper">
                    <div class="col-2 registered-users">
                        <div class="content">
                            <h2><?=GetMessage('AUTH_REGISTERED_TITLE');?></h2>
                            <p><?=GetMessage('AUTH_REGISTERED_ONE');?></p>
                            <ul class="form-list">
                                <li>
                                    <label for="email" class="required"><em>*</em><?=GetMessage('AUTH_LOGIN');?></label>
                                    <div class="input-box">
                        		<input  id="login" class="input_text_style input-text required-entry" type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" />
                                    </div>
                                </li>
                                <li>
                                    <label for="pass" class="required"><em>*</em><?=GetMessage('AUTH_PASSWORD');?></label>
                                    <div class="input-box">
                                        <input  id="pass" class="input_text_style input-text required-entry validate-password" type="password" name="USER_PASSWORD" maxlength="255" />
		<?if($arResult["SECURE_AUTH"]):?>
			<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
			</span>
			<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
			</noscript>
			<script type="text/javascript">
				document.getElementById('bx_auth_secure').style.display = 'inline-block';
			</script>
		<?endif?>                                        
                                    </div>
                                </li>
                                <?if ($arResult["STORE_PASSWORD"] == "Y"):?>
                                <li>
                                    <span class="rememberme">
                                        <input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" checked/><?=GetMessage("AUTH_REMEMBER_ME")?>
                                    </span>
                                </li>    
                                <?endif?>
                            </ul>
                            <div id="window-overlay" class="window-overlay" style="display:none;"></div>
                            <div id="remember-me-popup" class="remember-me-popup" style="display:none;">
                                <div class="remember-me-popup-head">
                                    <h3><?=GetMessage('AUTH_WHATSIS');?></h3>
                                    <a href="#" class="remember-me-popup-close" title="<?=GetMessage('AUTH_REMEMBER_CLOSE');?>"><?=GetMessage('AUTH_REMEMBER_CLOSE');?></a>
                                </div>
                                <div class="remember-me-popup-body">
                                    <p><?=GetMessage('AUTH_REMEMBER_DESC');?></p>
                                    <div class="remember-me-popup-close-button a-right">
                                        <a href="#" class="remember-me-popup-close button" title="<?=GetMessage('AUTH_REMEMBER_CLOSE');?>"><span><?=GetMessage('AUTH_REMEMBER_CLOSE');?></span></a>
                                    </div>
                                </div>
                            </div>
                            <script type="text/javascript">
                            //<![CDATA[
                                function toggleRememberMepopup(event){
                                    if($('remember-me-popup')) {
                                        var viewportHeight = document.viewport.getHeight(),
                                                docHeight = $$('body')[0].getHeight(),
                                                height = docHeight > viewportHeight ? docHeight : viewportHeight;
                                        $('remember-me-popup').toggle();
                                        $('window-overlay').setStyle({height: height + 'px'}).toggle();
                                    }
                                    Event.stop(event);
                                }

                                document.observe("dom:loaded", function () {
                                    new Insertion.Bottom($$('body')[0], $('window-overlay'));
                                    new Insertion.Bottom($$('body')[0], $('remember-me-popup'));

                                    $$('.remember-me-popup-close').each(function (element) {
                                        Event.observe(element, 'click', toggleRememberMepopup);
                                    })
                                    $$('#remember-me-box a').each(function (element) {
                                        Event.observe(element, 'click', toggleRememberMepopup);
                                    });
                                });
                            //]]>
                            </script>
                            <p class="required">* <?=GetMessage('AUTH_REQUIRED');?></p>
		<?if($arResult["CAPTCHA_CODE"]):?>
			<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
			<?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:
			<input class="bx-auth-input" type="text" name="captcha_word" maxlength="50" value="" size="15" />
		<?endif;?>
		<span style="display:block;height:7px;"></span>

                            <div class="buttons-set">

		<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
		<noindex>
                        <a href="<?=$arParams["AUTH_FORGOT_PASSWORD_URL"] ? $arParams["AUTH_FORGOT_PASSWORD_URL"] : $arResult["AUTH_FORGOT_PASSWORD_URL"]?>" class="f-left" rel="nofollow" ><?=GetMessage('AUTH_FORGOT_PASSWORD_2');?></a>
		</noindex>
		<?endif?>                            
                                <button type="submit" class="button" title="<?=GetMessage('AUTH_AUTHORIZE');?>" name="Login" id="send2"><span><span><?=GetMessage('AUTH_AUTHORIZE');?></span></span></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="new-users-wrapper">
                    <div class="col-1 new-users">
                        <div class="content">
                            <h2><?=GetMessage('AUTH_NEW_TITLE');?></h2>
                            <p><?=GetMessage('AUTH_FIRST_ONE');?></p>
                            <div class="buttons-set">
                                <button type="button" title="<?=GetMessage('AUTH_REGISTER_TITLE');?>" class="button" onclick="window.location = '/auth/?register=yes';">
                                    <span>
                                        <span>
                                            <?=GetMessage('AUTH_REGISTER');?>
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        //<![CDATA[
        var dataForm = new VarienForm('login-form', true);
        //]]>
    </script>
<script type="text/javascript">
<?if (strlen($arResult["LAST_LOGIN"])>0):?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<?else:?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<?endif?>
</script>

</div>


<?/*


<div class="login_page bx_<?=$arResult["THEME"]?>">
	<?
	ShowMessage($arParams["~AUTH_RESULT"]);
	ShowMessage($arResult['ERROR_MESSAGE']);
	?>
	<?if($arResult["AUTH_SERVICES"]):?>
	<h2><?echo GetMessage("AUTH_TITLE")?></h2>
	<?endif?>
	<?if($arResult["AUTH_SERVICES"]):
		$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "",
			array(
				"AUTH_SERVICES"=>$arResult["AUTH_SERVICES"],
				"CURRENT_SERVICE"=>$arResult["CURRENT_SERVICE"],
				"AUTH_URL"=>$arResult["AUTH_URL"],
				"POST"=>$arResult["POST"],
				"SUFFIX" => "main",
			),
			$component,
			array("HIDE_ICONS"=>"Y")
		);
	endif;?>
	<form name="form_auth" method="post" target="_top" action="<?=SITE_DIR?>auth/<?//=$arResult["AUTH_URL"]?>" class="bx_auth_form">
		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="AUTH" />
		<?if (strlen($arParams["BACKURL"]) > 0 || strlen($arResult["BACKURL"]) > 0):?>
		<input type="hidden" name="backurl" value="<?=($arParams["BACKURL"] ? $arParams["BACKURL"] : $arResult["BACKURL"])?>" />
		<?endif?>
		<?foreach ($arResult["POST"] as $key => $value):?>
		<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?endforeach?>

		<strong><?=GetMessage("AUTH_LOGIN")?></strong><br>
		<input class="input_text_style" type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" />
                <br><br>
		<strong><?=GetMessage("AUTH_PASSWORD")?></strong><br>
		<input class="input_text_style" type="password" name="USER_PASSWORD" maxlength="255" /><br>
		<?if($arResult["SECURE_AUTH"]):?>
			<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
			</span>
			<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
			</noscript>
			<script type="text/javascript">
				document.getElementById('bx_auth_secure').style.display = 'inline-block';
			</script>
		<?endif?>

		<?if($arResult["CAPTCHA_CODE"]):?>
			<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
			<?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:
			<input class="bx-auth-input" type="text" name="captcha_word" maxlength="50" value="" size="15" />
		<?endif;?>
		<span style="display:block;height:7px;"></span>
		<?if ($arResult["STORE_PASSWORD"] == "Y"):?>
			<span class="rememberme"><input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" checked/><?=GetMessage("AUTH_REMEMBER_ME")?></span>
		<?endif?>

		<?if ($arParams["NOT_SHOW_LINKS"] != "Y"):?>
		<noindex>
			<span class="forgotpassword" style="padding-left:75px;"><a href="<?=$arParams["AUTH_FORGOT_PASSWORD_URL"] ? $arParams["AUTH_FORGOT_PASSWORD_URL"] : $arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></span>
		</noindex>
		<?endif?>
		<br><br><input type="submit" name="Login" class="bx_bt_button bx_big shadow" value="<?=GetMessage("AUTH_AUTHORIZE")?>" />
	</form>
</div>


*/?>