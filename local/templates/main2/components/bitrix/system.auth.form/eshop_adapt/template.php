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

CJSCore::Init(array("popup", "ls"));

?>
<div class="bx_login_block">
	<span id="login-line">
	<?
	$frame = $this->createFrame("login-line", false)->begin();
		if ($arResult["FORM_TYPE"] == "login")
		{
		?>
			<a class="bx_login_top_inline_link" href="javascript:void(0)<?//=$arResult["AUTH_URL"]?>" onclick="openAuthorizePopup()"><?=GetMessage("AUTH_LOGIN")?></a>
			<?if($arResult["NEW_USER_REGISTRATION"] == "Y"):?>
			<a class="bx_login_top_inline_link" href="<?=$arResult["AUTH_REGISTER_URL"]?>" ><?=GetMessage("AUTH_REGISTER")?></a>
			<?endif;?>

			<script>
				BX.localStorage.remove("eshop_user_name");
			</script>
		<?
		}
		else
		{
			$name = trim($USER->GetFullName());
			if (strlen($name) <= 0)
				$name = $USER->GetLogin();
		?>
			<a class="bx_login_top_inline_link" href="<?=$arResult['PROFILE_URL']?>"><?=htmlspecialcharsEx($name);?></a>
			<a class="bx_login_top_inline_link" href="<?=$APPLICATION->GetCurPageParam("logout=yes", Array("logout"))?>" onclick="BX.localStorage.remove('eshop_user_name')"><?=GetMessage("AUTH_LOGOUT")?></a>

			<script>
				BX.localStorage.set("eshop_user_name", "<?=CUtil::JSEscape($name)?>", 604800);
			</script>
		<?
		}
	$frame->beginStub();
		?>
		<span id="login_stat_line">
			<a class="bx_login_top_inline_link" href="javascript:void(0)<?//=$arResult["AUTH_URL"]?>" onclick="openAuthorizePopup()"><?=GetMessage("AUTH_LOGIN")?></a>
			<?if($arResult["NEW_USER_REGISTRATION"] == "Y"):?>
				<a class="bx_login_top_inline_link" href="<?=$arResult["AUTH_REGISTER_URL"]?>" ><?=GetMessage("AUTH_REGISTER")?></a>
			<?endif;?>
		</span>

		<script>
			var userName = BX.localStorage.get("eshop_user_name");

			if (userName)
			{
				setTimeout(function(){
					BX("login_stat_line").innerHTML = "";
					BX("login_stat_line").appendChild(BX.create("a",
						{
							text:userName,
							attrs:{className:"bx_login_top_inline_link", href:"<?=$arResult['PROFILE_URL']?>"}
						}
					));
					BX("login_stat_line").appendChild(BX.create("a",
						{
							html:"<?=GetMessage("AUTH_LOGOUT")?>",
							attrs:{className:"bx_login_top_inline_link", href:"<?=$APPLICATION->GetCurPageParam("logout=yes", Array("logout"))?>"}
						}
					));
				}, 1);
			}
		</script>
	<?
	$frame->end();
	?>
	</span>
</div>


<div id="bx_auth_popup_form" style="display:none;" class="bx_login_popup_form">
	<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "eshop_adapt_auth",
		array(
			"BACKURL" => $arResult["BACKURL"],
			"AUTH_FORGOT_PASSWORD_URL" => $arResult["AUTH_FORGOT_PASSWORD_URL"],
		),
		false
	);
	?>
</div>

<script>
	function openAuthorizePopup()
	{
		var authPopup = BX.PopupWindowManager.create("AuthorizePopup", null, {
			autoHide: true,
			//	zIndex: 0,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : true,
			draggable: {restrict:true},
			closeByEsc: true,
			closeIcon: { right : "12px", top : "10px"},
			content: '<div style="width:400px;height:400px; text-align: center;"><span style="position:absolute;left:50%; top:50%"><img src="<?=$this->GetFolder()?>/images/wait.gif"/></span></div>',
			events: {
				onAfterPopupShow: function()
				{
					this.setContent(BX("bx_auth_popup_form"));
				}
			}
		});

		authPopup.show();
	}
</script>
