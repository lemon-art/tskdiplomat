<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
	$APPLICATION->AuthForm("");
elseif (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CAjax::Init();
	CUtil::InitJSCore(array("ajax", "window", "tooltip", "popup"));
	$ajax_page = $APPLICATION->GetCurPageParam("", array("bxajaxid", "logout"));
	$log_content_id = "sonet_log_content_".RandString(8);
	$event_cnt = 0;

	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		array(
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		),
		false,
		array("HIDE_ICONS" => "Y")
	);

	?><div id="<?=$log_content_id?>" class="feed-wrap"><?

	if (IsModuleInstalled('forum'))
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			"spoiler",
			Array(
				"TITLE" => "",
				"TEXT" => "",
				"RETURN" => "Y"
			),
			null,
			array("HIDE_ICONS" => "Y")
		);

	?><script>
		var logAjaxMode = false;
		var nodeTmp1Cap = false;
		var nodeTmp2Cap = false;

		BX.message({
			sonetLGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log/ajax.php')?>',
			sonetLSetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log/ajax.php')?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>',
			sonetLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
			sonetLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
			sonetLNoSubscriptions: '<?=GetMessageJS("SONET_C30_NO_SUBSCRIPTIONS")?>',
			sonetLInherited: '<?=GetMessageJS("SONET_C30_INHERITED")?>',
			sonetLDialogClose: '<?=GetMessageJS("SONET_C30_DIALOG_CLOSE_BUTTON")?>',
			sonetLDialogSubmit: '<?=GetMessageJS("SONET_C30_DIALOG_SUBMIT_BUTTON")?>',
			sonetLDialogCancel: '<?=GetMessageJS("SONET_C30_DIALOG_CANCEL_BUTTON")?>',
			sonetLDialogUT_Y: '<?=GetMessageJS("SONET_C30_DIALOG_UT_BUTTON_Y")?>',
			sonetLDialogUT_N: '<?=GetMessageJS("SONET_C30_DIALOG_UT_BUTTON_N")?>',
			sonetLTransportTitle: '<?=GetMessageJS("SONET_C30_DIALOG_TRANSPORT_TITLE")?>',
			sonetLTransportUnsubscribe: '<?=GetMessageJS("SONET_C30_DIALOG_TRANSPORT_UNSUBSCRIBE")?>',
			sonetLVisibleTitle_Y: '<?=GetMessageJS("SONET_C30_DIALOG_VISIBLE_TITLE_Y")?>',
			sonetLVisibleTitle_N: '<?=GetMessageJS("SONET_C30_DIALOG_VISIBLE_TITLE_N")?>',
			sonetLTransportTitle_M: '<?=GetMessageJS("SONET_C30_DIALOG_TRANSPORT_M")?>',
			sonetLTransportTitle_X: '<?=GetMessageJS("SONET_C30_DIALOG_TRANSPORT_X")?>',
			sonetLPathToUser: '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
			sonetLPathToGroup: '<?=CUtil::JSEscape($arParams["PATH_TO_GROUP"])?>',
			sonetLPathToDepartment: '<?=CUtil::JSEscape($arParams["PATH_TO_CONPANY_DEPARTMENT"])?>',
			sonetLPathToSmile: '<?=CUtil::JSEscape($arParams["PATH_TO_SMILE"])?>',
			sonetLShowRating: '<?=CUtil::JSEscape($arParams["SHOW_RATING"])?>',
			sonetLTextLikeY: '<?=CUtil::JSEscape(COption::GetOptionString("main", "rating_text_like_y", GetMessageJS("SONET_C30_TEXT_LIKE_Y")))?>',
			sonetLTextLikeN: '<?=CUtil::JSEscape(COption::GetOptionString("main", "rating_text_like_n", GetMessageJS("SONET_C30_TEXT_LIKE_N")))?>',
			sonetLTextLikeD: '<?=CUtil::JSEscape(COption::GetOptionString("main", "rating_text_like_d", GetMessageJS("SONET_C30_TEXT_LIKE_D")))?>',
			sonetLTextPlus: '<?=GetMessageJS("SONET_C30_TEXT_PLUS")?>',
			sonetLTextMinus: '<?=GetMessageJS("SONET_C30_TEXT_MINUS")?>',
			sonetLTextCancel: '<?=GetMessageJS("SONET_C30_TEXT_CANCEL")?>',
			sonetLTextAvailable: '<?=GetMessageJS("SONET_C30_TEXT_AVAILABLE")?>',
			sonetLTextDenied: '<?=GetMessageJS("SONET_C30_TEXT_DENIED")?>',
			sonetLTextRatingY: '<?=GetMessageJS("SONET_C30_TEXT_RATING_YES")?>',
			sonetLTextRatingN: '<?=GetMessageJS("SONET_C30_TEXT_RATING_NO")?>',
			sonetLPathToUserBlogPost: '<?=CUtil::JSEscape($arParams["PATH_TO_USER_BLOG_POST"])?>',
			sonetLPathToGroupBlogPost: '<?=CUtil::JSEscape($arParams["PATH_TO_GROUP_BLOG_POST"])?>',
			sonetLPathToUserMicroblogPost: '<?=CUtil::JSEscape($arParams["PATH_TO_USER_MICROBLOG_POST"])?>',
			sonetLPathToGroupMicroblogPost: '<?=CUtil::JSEscape($arParams["PATH_TO_GROUP_MICROBLOG_POST"])?>',
			sonetLForumID: '<?=intval($arParams["FORUM_ID"])?>',
			sonetLNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
			sonetLDateTimeFormat: '<?=CUtil::JSEscape($arParams["DATE_TIME_FORMAT"])?>',
			sonetLShowLogin: '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
			sonetLRatingType: '<?=CUtil::JSEscape($arParams["RATING_TYPE"])?>',
			sonetLCurrentUserID: '<?=intval($GLOBALS["USER"]->GetID())?>',
			sonetLAvatarSize: '<?=CUtil::JSEscape($arParams["AVATAR_SIZE"])?>',
			sonetLAvatarSizeComment: '<?=CUtil::JSEscape($arParams["AVATAR_SIZE_COMMENT"])?>',
			sonetLBlogAllowPostCode: '<?=CUtil::JSEscape($arParams["BLOG_ALLOW_POST_CODE"])?>',
			sonetLMessageShow: '<?=GetMessageJS("SONET_C30_T_MESSAGE_SHOW")?>',
			sonetLMessageHide: '<?=GetMessageJS("SONET_C30_T_MESSAGE_HIDE")?>',
			sonetLMoreWait: '<?=GetMessageJS("SONET_C30_T_MORE_WAIT")?>',
			sonetLCounterText1: '<?=GetMessageJS("SONET_C30_COUNTER_TEXT_1")?>',
			sonetLMenuTransportTitle: '<?=GetMessageJS("SONET_C30_MENU_TITLE_TRANSPORT")?>',
			sonetLMenuVisibleTitle: '<?=GetMessageJS("SONET_C30_MENU_TITLE_VISIBLE")?>',
			sonetLMenuFavoritesTitleY: '<?=GetMessageJS("SONET_C30_MENU_TITLE_FAVORITES_Y")?>',
			sonetLMenuFavoritesTitleN: '<?=GetMessageJS("SONET_C30_MENU_TITLE_FAVORITES_N")?>',
			sonetLDestinationLimit: '<?=intval($arParams["DESTINATION_LIMIT_SHOW"])?>',
			sonetLCounterType: '<?=CUtil::JSEscape($arResult["COUNTER_TYPE"])?>',
			sonetLIsB24: '<?=(SITE_TEMPLATE_ID == "bitrix24" ? "Y" : "N")?>'
			<?
			if (strlen($arParams["CONTAINER_ID"]) > 0):
				?>, sonetLContainerExternal: '<?=CUtil::JSEscape($arParams["CONTAINER_ID"])?>'<?
			endif;

			if ($arParams["USE_FOLLOW"] == "Y"):
				?>
				, sonetLFollowY: '<?=GetMessageJS("SONET_LOG_T_FOLLOW_Y")?>'
				, sonetLFollowN: '<?=GetMessageJS("SONET_LOG_T_FOLLOW_N")?>'
				<?
			endif;
			?>
		});
		<?if ($arResult["AJAX_CALL"] && $arParams["SHOW_RATING"] == "Y"):?>
			<?if ($arParams["RATING_TYPE"] == "like"):?>
				BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/<?=$arParams["RATING_TYPE"]?>/popup.css');
			<?endif;?>
			BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/<?=$arParams["RATING_TYPE"]?>/style.css');
		<?endif;?>

		<?
		if (
			$arParams["LOG_ID"] <= 0
			&& $arResult["bReload"]
		):
			?>
			if (typeof __logOnReload === 'function')
			{
				BX.ready(function(){
					__logOnReload(<?=intval($arResult["LOG_COUNTER"])?>);
				});
			}
			<?
		endif;

		if (
			$arParams["LOG_ID"] <= 0
			&& (!$arResult["AJAX_CALL"] || $arResult["bReload"])
		):
			?>
			BX.browser.addGlobalClass();
			BX.ready(function(){
				BX.onCustomEvent(window, 'onSonetLogCounterClear', [BX.message('sonetLCounterType')]);
				<?
				if (!$arResult["AJAX_CALL"]):
					?>
					BX.addCustomEvent(window, "onImUpdateCounter", BX.proxy(function(arCount){ __logChangeCounterArray(arCount); }, this));
					<?
				endif;
				?>
			});
			<?
		endif;
		?>
		BX.ready(function(){
			<?
			if ($arParams["LOG_ID"] <= 0)
			{
				?>
	//			__logExpandAdjust('<?=$log_content_id?>');
				BX.addCustomEvent(window, "onAjaxInsertToNode", function() { BX.ajax.Setup({denyShowWait: true}, true); });

				BX.bind(BX('sonet_log_counter_2_container'), 'click', sonetLClearContainerExternalNew);
				BX.bind(BX('sonet_log_counter_2_container'), 'click', __logOnAjaxInsertToNode);

				BX.bind(BX('sonet_log_more_container'), 'click', sonetLClearContainerExternalMore);
				BX.bind(BX('sonet_log_more_container'), 'click', __logOnAjaxInsertToNode);
				<?
			}
			?>
			if (BX('sonet_log_comment_text'))
				BX('sonet_log_comment_text').onkeydown = BX.eventCancelBubble;
		});

	</script>
	<?
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	unset($arResult["ActiveFeatures"]["all"]);
	if (
		!$arResult["AJAX_CALL"]
		&& $arParams["SHOW_EVENT_ID_FILTER"] == "Y"
		&& !empty($arResult["ActiveFeatures"])
	)
	{
		?><?
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:socialnetwork.log.filter",
				".default",
				array(
					"arParams" => $arParams,
					"arResult" => $arResult
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
		?><?
	}

	if (!$arResult["AJAX_CALL"])
	{
		if (IsModuleInstalled('tasks'))
		{
			?><?
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:tasks.iframe.popup",
				".default",
				array(
					"ON_TASK_ADDED" => "BX.DoNothing",
					"ON_TASK_CHANGED" => "BX.DoNothing",
					"ON_TASK_DELETED" => "BX.DoNothing",
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
			?><?
		}

		if(
			$arParams["LOG_ID"] <= 0
			&& IntVal($arResult["MICROBLOG_USER_ID"]) > 0
		)
		{
			?><div id="sonet_log_microblog_container" style="padding-bottom: 10px;"><span id="slog-mb-hide" style="display:none;"><div onclick="WriteMicroblog(false)"></div></span>
				<div id="microblog-link" class="feed-add-post-title" onclick="WriteMicroblog(true)"><?=GetMessage("SONET_C30_mb_show_new")?></div>
				<div id="microblog-form" style="display:none;"><?
					$arBlogComponentParams = Array(
						"ID" => "new",
						"PATH_TO_BLOG" => $APPLICATION->GetCurPageParam(),
						"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
						"PATH_TO_GROUP_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
						"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
						"SET_TITLE" => "N",
						"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
						"USER_ID" => $USER->GetID(),
						"SET_NAV_CHAIN" => "N",
						"USE_SOCNET" => "Y",
						"MICROBLOG" => "Y",
						"USE_CUT" => $arParams["BLOG_USE_CUT"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					);

					if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
						$arBlogComponentParams["SOCNET_GROUP_ID"] = $arParams["GROUP_ID"];
					elseif ($arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $USER->GetID() != $arParams["CURRENT_USER_ID"])
						$arBlogComponentParams["SOCNET_USER_ID"] = $arParams["CURRENT_USER_ID"];

					?><?
					$APPLICATION->IncludeComponent(
						"bitrix:socialnetwork.blog.post.edit",
						"",
						$arBlogComponentParams,
						$component,
						array("HIDE_ICONS" => "Y")
					);
				?></div>
			</div><?
		}

		if ($arParams["USE_RSS"] == "Y")
		{
			?><div style="float: right;"><?

			$APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.log.rss.link",
				"",
				Array(
					"PATH_TO_RSS" => $arParams["~PATH_TO_LOG_RSS"],
					"PATH_TO_RSS_MASK" => $arParams["~PATH_TO_LOG_RSS_MASK"],
					"ENTITY_TYPE" => $arParams["ENTITY_TYPE"],
					"ENTITY_ID" => ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]),
					"EVENT_ID" => $arParams["EVENT_ID"]
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);
			?></div><?
		}
	}

	if (
		$arParams["LOG_ID"] <= 0
		&& !$arResult["AJAX_CALL"]
		&& array_key_exists("CONTAINER_ID", $arParams)
		&& strlen(trim($arParams["CONTAINER_ID"])) > 0
	)
	{
		?><a href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("RELOAD=Y", array(
			"flt_created_by_id",
			"flt_group_id",
			"flt_date_datesel",
			"flt_date_days",
			"flt_date_from",
			"flt_date_to",
			"flt_date_to",
			"flt_show_hidden",
			"skip_subscribe",
			"preset_filter_id",
			"sessid",
			"bxajaxid"
		), false)?>" id="sonet_log_counter_2_container" class="feed-new-message-informer"><span class="feed-new-message-inf-text"><?=GetMessage("SONET_C30_COUNTER_TEXT_1")?><span class="feed-new-message-informer-counter" id="sonet_log_counter_2"></span><span class="feed-new-message-icon"></span></span><span class="feed-new-message-inf-text feed-new-message-inf-text-waiting" style="display: none;"><span class="feed-new-message-wait-icon"></span><?=GetMessage("SONET_C30_T_MORE_WAIT")?></span></a>
		<?
	}

	if ($arResult["EventsNew"] && is_array($arResult["EventsNew"]) && count($arResult["EventsNew"]) > 0)
	{
		?><div id="sonet_log_items" class="show-hidden-<?=($arResult["SHOW_HIDDEN"] ? "Y" : "N")?>"><?
		foreach ($arResult["EventsNew"] as $date => $arEvents)
		{
			foreach ($arEvents as $arEvent)
			{
				if (!empty($arEvent["EVENT"]))
				{
					$event_cnt++;
					$ind = RandString(8);

					$is_unread = (
						$arParams["SHOW_UNREAD"] == "Y"
						&& ($arResult["COUNTER_TYPE"] == "**" || $arResult["COUNTER_TYPE"] == "blog_post")
						&& $arEvent["EVENT"]["USER_ID"] != $GLOBALS["USER"]->GetID() 
						&& intval($arResult["LAST_LOG_TS"]) > 0 
						&& (MakeTimeStamp($arEvent["EVENT"]["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"]
					);
					$is_hidden = (array_key_exists("VISIBLE", $arEvent) && $arEvent["VISIBLE"] == "N");

					if (
						array_key_exists("URL", $arEvent["EVENT_FORMATTED"])
						&& strlen($arEvent["EVENT_FORMATTED"]["URL"]) > 0
					)
						$url = $arEvent["EVENT_FORMATTED"]["URL"];
					elseif (
						array_key_exists("URL", $arEvent["EVENT"])
						&& strlen($arEvent["EVENT"]["URL"]) > 0
					)
						$url = $arEvent["EVENT"]["URL"];
					else
						$url = "";

					if(in_array($arEvent["EVENT"]["EVENT_ID"], Array("blog_post", "blog_post_micro", "blog_comment", "blog_comment_micro")))
					{
						?><div id="sonet_log_day_item_<?=$ind?>"><?

						$arAditMenu = array();

						if ($GLOBALS["USER"]->IsAuthorized())
						{
							$arAditMenu["1"] = Array(
								"text" => (array_key_exists("FAVORITES", $arEvent) && $arEvent["FAVORITES"] == "Y" ? "sonetLMenuFavoritesTitleY" : "sonetLMenuFavoritesTitleN"),
								"onclick" => "function(e) { __logChangeFavorites('".$arEvent["EVENT"]["ID"]."'); return BX.PreventDefault(e);}",
							);
							$arAditMenu["4"] = Array(
								"text" => "sonetLMenuTransportTitle",
								"onclick" => "function(e) { __logShowTransportDialog('".$ind."', '".$arEvent["EVENT"]["ENTITY_TYPE"]."', '".$arEvent["EVENT"]["ENTITY_ID"]."', '".$arEvent["EVENT"]["EVENT_ID"]."', ".($arEvent["EVENT"]["EVENT_ID_FULLSET"] ? "'".$arEvent["EVENT"]["EVENT_ID_FULLSET"]."'" : "false").", '".$arEvent["EVENT"]["USER_ID"]."'); this.popupWindow.close();}",
							);
						}

						if ($GLOBALS["USER"]->IsAuthorized() && $arParams["SUBSCRIBE_ONLY"] == "Y")
							$arAditMenu["5"] = Array(
								"text" => "sonetLMenuVisibleTitle",
								"onclick" => "function(e) { __logShowVisibleDialog('".$ind."', '".$arEvent["EVENT"]["ENTITY_TYPE"]."', '".$arEvent["EVENT"]["ENTITY_ID"]."', '".$arEvent["EVENT"]["EVENT_ID"]."', ".($arEvent["EVENT"]["EVENT_ID_FULLSET"] ? "'".$arEvent["EVENT"]["EVENT_ID_FULLSET"]."'" : "false").", '".$arEvent["EVENT"]["USER_ID"]."', '".$arEvent["VISIBLE"]."'); this.popupWindow.close(); return BX.PreventDefault(e);}",
							);

						$arComponentParams = Array(
							"PATH_TO_BLOG" => $arParams["PATH_TO_USER_BLOG"],
							"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
							"PATH_TO_BLOG_CATEGORY" => $arParams["PATH_TO_USER_BLOG_CATEGORY"],
							"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"],
							"PATH_TO_USER" => $arParams["PATH_TO_USER"],
							"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
							"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
							"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
							"SET_NAV_CHAIN" => "N",
							"SET_TITLE" => "N",
							"POST_PROPERTY" => $arParams["POST_PROPERTY"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"USER_ID" => $arEvent["EVENT"]["USER_ID"],
							"ENTITY_TYPE" => $arEvent["EVENT"]["ENTITY_TYPE"],
							"ENTITY_ID" => $arEvent["EVENT"]["ENTITY_ID"],
							"EVENT_ID" => $arEvent["EVENT"]["EVENT_ID"],
							"EVENT_ID_FULLSET" => $arEvent["EVENT"]["EVENT_ID_FULLSET"],
							"IND" => $ind,
							"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
							"SONET_GROUP_ID" => $arParams["GROUP_ID"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
							"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
							"USE_SHARE" => $arParams["USE_SHARE"],
							"SHARE_HIDE" => $arParams["SHARE_HIDE"],
							"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"],
							"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"],
							"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
							"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
							"SHOW_RATING" => $arParams["SHOW_RATING"],
							"RATING_TYPE" => $arParams["RATING_TYPE"],
							"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
							"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
							"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
							"ID" => $arEvent["EVENT"]["SOURCE_ID"],
							"LOG_ID" => $arEvent["EVENT"]["ID"],
							"FROM_LOG" => "Y",
							"ADIT_MENU" => $arAditMenu,
							"IS_UNREAD" => $is_unread,
							"MARK_NEW_COMMENTS" => ($GLOBALS["USER"]->IsAuthorized() && $arResult["COUNTER_TYPE"] == "**") ? "Y" : "N",
							"IS_HIDDEN" => $is_hidden,
							"LAST_LOG_TS" => ($arResult["LAST_LOG_TS"]+$arResult["TZ_OFFSET"]), 
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
							"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
							"USE_CUT" => $arParams["BLOG_USE_CUT"],
							"AVATAR_SIZE" => $arParams["AVATAR_SIZE"],
							"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
						);

						if ($arParams["USE_FOLLOW"] == "Y")
							$arComponentParams["FOLLOW"] = $arEvent["EVENT"]["FOLLOW"];

						if (
							strlen($arEvent["EVENT"]["RATING_TYPE_ID"])>0
							&& $arEvent["EVENT"]["RATING_ENTITY_ID"] > 0
							&& $arParams["SHOW_RATING"] == "Y"
						)
						{
							$arComponentParams["RATING_ENTITY_ID"] = $arEvent["EVENT"]["RATING_ENTITY_ID"];
							$arComponentParams["RATING_USER_VOTE_VALUE"] = $arEvent["EVENT"]["RATING_USER_VOTE_VALUE"];
							$arComponentParams["RATING_TOTAL_VOTES"] = $arEvent["EVENT"]["RATING_TOTAL_VOTES"];
							$arComponentParams["RATING_TOTAL_POSITIVE_VOTES"] = $arEvent["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"];
							$arComponentParams["RATING_TOTAL_NEGATIVE_VOTES"] = $arEvent["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"];
							$arComponentParams["RATING_TOTAL_VALUE"] = $arEvent["EVENT"]["RATING_TOTAL_VALUE"];
						}

						$arComponentParams["DESTINATION_CODE"] = $arEvent["EVENT_FORMATTED"]["DESTINATION_CODE"];

						$APPLICATION->IncludeComponent(
							"bitrix:socialnetwork.blog.post",
							"",
							$arComponentParams,
							$component
						);
						?></div><?
					}
					else
					{
						?>
						<div class="feed-post-block<?=($is_unread ? " feed-post-block-new" : "")?><?=(array_key_exists("EVENT_FORMATTED", $arEvent) && array_key_exists("STYLE", $arEvent["EVENT_FORMATTED"]) && strlen($arEvent["EVENT_FORMATTED"]["STYLE"]) > 0 ? " feed-".$arEvent["EVENT_FORMATTED"]["STYLE"] : "")?>">
							<div id="sonet_log_day_item_<?=$ind?>" class="feed-post-cont-wrap<?=($is_hidden ? " feed-hidden-post" : "")?><?
							if (
								array_key_exists("USER_ID", $arEvent["EVENT"])
								&& intval($arEvent["EVENT"]["USER_ID"]) > 0
							)
							{
								?> sonet-log-item-createdby-<?=intval($arEvent["EVENT"]["USER_ID"])?><?
							}
							if (
								array_key_exists("ENTITY_TYPE", $arEvent["EVENT"])
								&& strlen($arEvent["EVENT"]["ENTITY_TYPE"]) > 0
								&& array_key_exists("ENTITY_ID", $arEvent["EVENT"])
								&& intval($arEvent["EVENT"]["ENTITY_ID"]) > 0
							)
							{
								?> sonet-log-item-where-<?=$arEvent["EVENT"]["ENTITY_TYPE"]?>-<?=intval($arEvent["EVENT"]["ENTITY_ID"])?>-all <?
								if (
									array_key_exists("EVENT_ID", $arEvent["EVENT"])
									&& strlen($arEvent["EVENT"]["EVENT_ID"]) > 0
								)
								{
									?> sonet-log-item-where-<?=$arEvent["EVENT"]["ENTITY_TYPE"]?>-<?=intval($arEvent["EVENT"]["ENTITY_ID"])?>-<?=str_replace("_", '-', $arEvent["EVENT"]["EVENT_ID"])?><?

									if (
										array_key_exists("EVENT_ID_FULLSET", $arEvent["EVENT"])
										&& strlen($arEvent["EVENT"]["EVENT_ID_FULLSET"]) > 0
									)
									{
										?> sonet-log-item-where-<?=$arEvent["EVENT"]["ENTITY_TYPE"]?>-<?=intval($arEvent["EVENT"]["ENTITY_ID"])?>-<?=str_replace("_", '-', $arEvent["EVENT"]["EVENT_ID_FULLSET"])?> <?
									}
								}
							}

							?>">
								<div class="feed-user-avatar"<?=(strlen($arEvent["AVATAR_SRC"]) > 0 ? " style=\"background:url('".$arEvent["AVATAR_SRC"]."') no-repeat center;\"" : "")?>></div>
								<div class="feed-post-title-block"><?
									$strDestination = "";
									if (
										array_key_exists("DESTINATION", $arEvent["EVENT_FORMATTED"])
										&& is_array($arEvent["EVENT_FORMATTED"]["DESTINATION"])
										&& count($arEvent["EVENT_FORMATTED"]["DESTINATION"]) > 0
									)
									{
										if (in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_groups", "system_friends")))
										{
											$strDestination .= '<div class="feed-post-item">';

											if (
												array_key_exists("TITLE_24", $arEvent["EVENT_FORMATTED"])
												&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24"]) > 0
											)
												$strDestination .= '<div class="feed-add-post-destination-title">'.$arEvent["EVENT_FORMATTED"]["TITLE_24"].'<span class="feed-add-post-destination-icon"></span></div>';

											foreach($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
											{
												if (strlen($arDestination["URL"]) > 0)
													$strDestination .= '<a target="_self" href="'.$arDestination["URL"].'" class="feed-add-post-destination feed-add-post-destination-'.$arDestination["STYLE"].'"><span class="feed-add-post-destination-text">'.$arDestination["TITLE"].'</span></a>';
												else
													$strDestination .= '<span class="feed-add-post-destination feed-add-post-destination-'.$arDestination["STYLE"].'"><span class="feed-add-post-destination-text">'.$arDestination["TITLE"].'</span></span>';
											}
											$strDestination .= '</div>';
										}
										else
										{
											$strDestination .= ' <span class="feed-add-post-destination-icon"></span> ';
											$i = 0;
											foreach($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
											{
												if ($i > 0)
													$strDestination .= ', ';

												if (strlen($arDestination["URL"]) > 0)
													$strDestination .= '<a class="feed-add-post-destination-new" href="'.$arDestination["URL"].'">'.$arDestination["TITLE"].'</a>';
												else
													$strDestination .= '<span class="feed-add-post-destination-new">'.$arDestination["TITLE"].'</span>';
												$i++;
											}
											if (intval($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"]) > 0)
											{
												if (
													($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"] % 100) > 10
													&& ($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"] % 100) < 20
												)
													$suffix = 5;
												else
													$suffix = $arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"] % 10;

												$strDestination .= '<a class="feed-post-link-new" onclick="__logShowHiddenDestination('.$arEvent["EVENT"]["ID"].', this)" href="javascript:void(0)">'.str_replace("#COUNT#", $arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"], GetMessage("SONET_C30_DESTINATION_MORE_".$suffix)).'</a>';
											}
										}
									}

									$strCreatedBy = "";
									if (
										array_key_exists("CREATED_BY", $arEvent)
										&& is_array($arEvent["CREATED_BY"])
									)
									{
										if (
											array_key_exists("TOOLTIP_FIELDS", $arEvent["CREATED_BY"])
											&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
										)
										{
											$anchor_id = RandString(8);
											$strCreatedBy .= '<a class="feed-post-user-name" id="anchor_'.$anchor_id.'" href="'.str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]).'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
											$strCreatedBy .= '<script type="text/javascript">';
											$strCreatedBy .= 'BX.tooltip('.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"].', "anchor_'.$anchor_id.'", "'.CUtil::JSEscape($ajax_page).'");';
											$strCreatedBy .= '</script>';
										}
										elseif (
											array_key_exists("FORMATTED", $arEvent["CREATED_BY"])
											&& strlen($arEvent["CREATED_BY"]["FORMATTED"]) > 0
										)
										{
											$strCreatedBy .= '<span class="feed-post-user-name">'.$arEvent["CREATED_BY"]["FORMATTED"].'</span>';
										}
									}
									elseif (
										in_array($arEvent["EVENT"]["EVENT_ID"], array("data", "news"))
										&& array_key_exists("ENTITY", $arEvent)
									)
									{
										if (
											array_key_exists("TOOLTIP_FIELDS", $arEvent["ENTITY"])
											&& is_array($arEvent["ENTITY"]["TOOLTIP_FIELDS"])
										)
										{
											$anchor_id = RandString(8);
											$strCreatedBy .= '<a class="feed-post-user-name" id="anchor_'.$anchor_id.'" href="'.str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]).'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
											$strCreatedBy .= '<script type="text/javascript">';
											$strCreatedBy .= 'BX.tooltip('.$arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"].', "anchor_'.$anchor_id.'", "'.CUtil::JSEscape($ajax_page).'");';
											$strCreatedBy .= '</script>';
										}
										elseif (
											array_key_exists("FORMATTED", $arEvent["ENTITY"])
											&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
										)
										{
											if (array_key_exists("URL", $arEvent["ENTITY"]["FORMATTED"]) && strlen($arEvent["ENTITY"]["FORMATTED"]["URL"]) > 0)
												$strCreatedBy .= '<a href="'.$arEvent["ENTITY"]["FORMATTED"]["URL"].'" class="feed-post-user-name">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</a>';
											else
												$strCreatedBy .= '<span class="feed-post-user-name">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</span>';
										}
									}
									elseif (
										in_array($arEvent["EVENT"]["EVENT_ID"], array("system"))
										&& array_key_exists("ENTITY", $arEvent)
										&& array_key_exists("FORMATTED", $arEvent["ENTITY"])
										&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
									)
									{
										if (array_key_exists("URL", $arEvent["ENTITY"]["FORMATTED"]) && strlen($arEvent["ENTITY"]["FORMATTED"]["URL"]) > 0)
											$strCreatedBy .= '<a href="'.$arEvent["ENTITY"]["FORMATTED"]["URL"].'" class="feed-post-user-name">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</a>';
										else
											$strCreatedBy .= '<span class="feed-post-user-name">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</span>';
									}

									?><?=(strlen($strCreatedBy) > 0 ? $strCreatedBy : "")?><?
									?><?=$strDestination?><?

									if (
										array_key_exists("EVENT_FORMATTED", $arEvent)
										&&
										(
											(array_key_exists("TITLE_24", $arEvent["EVENT_FORMATTED"]) && strlen($arEvent["EVENT_FORMATTED"]["TITLE_24"]) > 0)
											|| (array_key_exists("TITLE_24_2", $arEvent["EVENT_FORMATTED"]) && strlen($arEvent["EVENT_FORMATTED"]["TITLE_24_2"]) > 0)
										)

									)
									{
										if (
											array_key_exists("TITLE_24", $arEvent["EVENT_FORMATTED"])
											&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24"]) > 0
										)
										{
											?><div class="feed-post-item"><?
											if (in_array($arEvent["EVENT"]["EVENT_ID"], array("photo")))
											{
												?><div class="feed-add-post-destination-title"><span class="feed-add-post-files-title feed-add-post-p"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></span></div><?
											}
											elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("timeman_entry")))
											{
												?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=$arEvent['ENTITY']['FORMATTED']['URL']?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_TIMEMAN")?><span class="feed-work-time-icon"></span></a></div><?
											}
											elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("report")))
											{
												?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=$arEvent['ENTITY']['FORMATTED']['URL']?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_REPORTS")?><span class="feed-work-time-icon"></span></a></div><?
											}
											elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("tasks")))
											{
												?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><span class="feed-work-time"><?=GetMessage("SONET_C30_MENU_ENTRY_TASKS")?><span class="feed-work-time-icon"></span></span></div><?
											}
											elseif (!in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_groups", "system_friends")))
											{
												?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></div><?
											}
											?></div><?
										}

										if (
											(
												!array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
												|| !$arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
											)
											&& array_key_exists("TITLE_24_2", $arEvent["EVENT_FORMATTED"])
											&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24_2"]) > 0
										)
										{
											if (strlen($url) > 0)
											{
												?><div class="feed-post-title"><a href="<?=$url?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a></div><?
											}
											else
											{
												?><div class="feed-post-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
											}
										}
									}

								?></div><? // title

								// body

								if (
									array_key_exists("EVENT_FORMATTED", $arEvent)
									&& array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
									&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
								)
								{
									?><div class="feed-info-block"><?

										if (
											array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
											&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
											&& array_key_exists("TITLE_24_2", $arEvent["EVENT_FORMATTED"])
											&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24_2"]) > 0
										)
										{
											if (strlen($url) > 0)
											{
												?><a href="<?=$url?>" class="feed-post-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a><?
											}
											else
											{
												?><div class="feed-post-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
											}
										}

										?><div class="feed-post-text-block"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?><?if($arEvent["EVENT_FORMATTED"]["URL"]):?><a href="<?=$arEvent["EVENT_FORMATTED"]["URL"]?>"><?=GetMessage("SONET_C30_MORE_IMPORTANT")?></a><?endif?><i class="feed-info-block-icon"></i></div><?
									?></div><?
								}
								elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("files", "commondocs")))
								{
									?><div class="feed-post-item feed-post-add-files">
										<div class="feed-add-post-files-title feed-add-post-f"><?=$arEvent["EVENT_FORMATTED"]["MESSAGE_TITLE_24"]?></div><?
										$file_ext = GetFileExtension($arEvent["EVENT"]["TITLE"]);
										?><div class="feed-files-cont">
											<span class="feed-com-file-wrap">
												<span class="feed-com-file-icon feed-file-icon-<?=$file_ext?>"></span><?
												if (
													array_key_exists("URL", $arEvent["EVENT"])
													&& strlen($arEvent["EVENT"]["URL"]) > 0
												)
												{
													?><span class="feed-com-file-name"><a href="<?=$arEvent["EVENT"]["URL"]?>"><?=$arEvent["EVENT"]["TITLE"]?></a></span><?
												}
												else
												{
													?><span class="feed-com-file-name"><?=$arEvent["EVENT"]["TITLE"]?></span><?
												}
												?><span class="feed-com-size"></span>
											</span>
										</div>
									</div><?
								}
								elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("photo", "photo_photo")))
								{
									?><div class="feed-post-item"><?

										$arPhotoItems = array();
										$photo_section_id = false;
										if ($arEvent["EVENT"]["EVENT_ID"] == "photo")
										{
											$photo_section_id = $arEvent["EVENT"]["SOURCE_ID"];
											if (strlen($arEvent["EVENT"]["PARAMS"]) > 0)
											{
												$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]));
												if (
													$arEventParams
													&& is_array($arEventParams)
													&& array_key_exists("arItems", $arEventParams)
													&& is_array($arEventParams["arItems"])
												)
													$arPhotoItems = $arEventParams["arItems"];
											}
										}
										elseif ($arEvent["EVENT"]["EVENT_ID"] == "photo_photo")
										{
											if (intval($arEvent["EVENT"]["SOURCE_ID"]) > 0)
												$arPhotoItems = array($arEvent["EVENT"]["SOURCE_ID"]);

											if (strlen($arEvent["EVENT"]["PARAMS"]) > 0)
											{
												$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]));
												if (
													$arEventParams
													&& is_array($arEventParams)
													&& array_key_exists("SECTION_ID", $arEventParams)
													&& intval($arEventParams["SECTION_ID"]) > 0
												)
													$photo_section_id = $arEventParams["SECTION_ID"];
											}
										}

										if (strlen($arEvent["EVENT"]["PARAMS"]) > 0)
										{
											$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]));

											$photo_iblock_type = $arEventParams["IBLOCK_TYPE"];
											$photo_iblock_id = $arEventParams["IBLOCK_ID"];

											if (is_array($arEventParams) && array_key_exists("ALIAS", $arEventParams))
												$alias = $arEventParams["ALIAS"];
										else
												$alias = false;

											if ($arEvent["EVENT"]["EVENT_ID"] == "photo")
											{
												$photo_detail_url = $arEventParams["DETAIL_URL"];
												if ($photo_detail_url && IsModuleInstalled("extranet") && $arEvent["EVENT"]["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
													$photo_detail_url = str_replace("#GROUPS_PATH#", $arResult["WORKGROUPS_PAGE"], $photo_detail_url);
											}
											elseif ($arEvent["EVENT"]["EVENT_ID"] == "photo_photo")
												$photo_detail_url = $arEvent["EVENT"]["URL"];

											if (!$photo_detail_url)
												$photo_detail_url = $arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_ELEMENT"];

											if (
												strlen($photo_iblock_type) > 0
												&& intval($photo_iblock_id) > 0
												&& intval($photo_section_id) > 0
												&& count($arPhotoItems) > 0
											)
											{
												?><?$APPLICATION->IncludeComponent(
													"bitrix:photogallery.detail.list.ex",
													"",
													Array(
														"IBLOCK_TYPE" => $photo_iblock_type,
														"IBLOCK_ID" => $photo_iblock_id,
														"SHOWN_PHOTOS" => (count($arPhotoItems) > $arParams["PHOTO_COUNT"]
															? array_slice($arPhotoItems, 0, $arParams["PHOTO_COUNT"])
															: $arPhotoItems
														),
														"DRAG_SORT" => "N",
														"MORE_PHOTO_NAV" => "N",

														"THUMBNAIL_SIZE" => $arParams["PHOTO_THUMBNAIL_SIZE"],
														"SHOW_CONTROLS" => "Y",
														"USE_RATING" => ($arParams["PHOTO_USE_RATING"] == "Y" || $arParams["SHOW_RATING"] == "Y" ? "Y" : "N"),
														"SHOW_RATING" => $arParams["SHOW_RATING"],
														"SHOW_SHOWS" => "N",
														"SHOW_COMMENTS" => "Y",
														"MAX_VOTE" => $arParams["PHOTO_MAX_VOTE"],
														"VOTE_NAMES" => isset($arParams["PHOTO_VOTE_NAMES"])? $arParams["PHOTO_VOTE_NAMES"]: Array(),
														"DISPLAY_AS_RATING" => $arParams["SHOW_RATING"] == "Y"? "rating_main": isset($arParams["PHOTO_DISPLAY_AS_RATING"])? $arParams["PHOTO_DISPLAY_AS_RATING"]: "rating",
														"RATING_MAIN_TYPE" => $arParams["SHOW_RATING"] == "Y"? $arParams["RATING_TYPE"]: "",

														"BEHAVIOUR" => "SIMPLE",
														"SET_TITLE" => "N",
														"CACHE_TYPE" => "A",
														"CACHE_TIME" => $arParams["CACHE_TIME"],
														"CACHE_NOTES" => "",
														"SECTION_ID" => $photo_section_id,
														"ELEMENT_LAST_TYPE"	=> "none",
														"ELEMENT_SORT_FIELD" => "ID",
														"ELEMENT_SORT_ORDER" => "asc",
														"ELEMENT_SORT_FIELD1" => "",
														"ELEMENT_SORT_ORDER1" => "asc",
														"PROPERTY_CODE" => array(),

														"INDEX_URL" => CComponentEngine::MakePathFromTemplate(
															$arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO"],
															array(
																"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
																"group_id" => $arEvent["EVENT"]["ENTITY_ID"]
															)
														),
														"DETAIL_URL" => CComponentEngine::MakePathFromTemplate(
															$photo_detail_url,
															array(
																"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
																"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
															)
														),
														"GALLERY_URL" => "",
														"SECTION_URL" => CComponentEngine::MakePathFromTemplate(
															$arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_SECTION"],
															array(
																"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
																"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
																"section_id" => ($arEvent["EVENT"]["EVENT_ID"] == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])
															)
														),
														"PATH_TO_USER" => $arParams["PATH_TO_USER"],
														"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
														"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],

														"USE_PERMISSIONS" => "N",
														"GROUP_PERMISSIONS" => array(),
														"PAGE_ELEMENTS" => $arParams["PHOTO_COUNT"],
														"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
														"SET_STATUS_404" => "N",
														"ADDITIONAL_SIGHTS" => array(),
														"PICTURES_SIGHT" => "real",
														"USE_COMMENTS" => $arParams["PHOTO_USE_COMMENTS"],
														"COMMENTS_TYPE" => ($arParams["PHOTO_COMMENTS_TYPE"] == "blog" ? "blog" : "forum"),
														"FORUM_ID" => $arParams["PHOTO_FORUM_ID"],
														"BLOG_URL" => $arParams["PHOTO_BLOG_URL"],
														"USE_CAPTCHA" => $arParams["PHOTO_USE_CAPTCHA"],
														"SHOW_LINK_TO_FORUM" => "N",
														"IS_SOCNET" => "Y",
														"USER_ALIAS" => ($alias ? $alias : ($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "group" : "user")."_".$arEvent["EVENT"]["ENTITY_ID"]),
														//these two params below used to set action url and unique id - for any ajax actions
														"~UNIQUE_COMPONENT_ID" => 'bxfg_ucid_from_req_'.$photo_iblock_id.'_'.($arEvent["EVENT"]["EVENT_ID"] == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])."_".$arEvent["EVENT"]["ID"],
														"ACTION_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_SECTION"], array("user_id" => $arEvent["EVENT"]["ENTITY_ID"],"group_id" => $arEvent["EVENT"]["ENTITY_ID"],"section_id" => ($arEvent["EVENT"]["EVENT_ID"] == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"]))),
													),
													$component,
													array(
														"HIDE_ICONS" => "Y"
													)
												);?><?
											}
										}

									?></div><?
								}
								elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("tasks")))
								{
									?><div class="feed-post-info-block-wrap"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div><?
								}
								elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("timeman_entry", "report")))
								{
									?><div class="feed-post-text-block"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div><?
								}
								elseif (!in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_groups", "system_friends")) && strlen($arEvent["EVENT_FORMATTED"]["MESSAGE"]) > 0) // all other events
								{
									?><div class="feed-post-text-block">
										<div class="feed-post-text-block-inner"><div class="feed-post-text-block-inner-inner"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div></div>
										<div class="feed-post-text-more" onclick="__logEventExpand(this); return false;"><div class="feed-post-text-more-but"></div></div>
									</div><?
								}

								?><div class="feed-post-informers"><?
									if (
										array_key_exists("HAS_COMMENTS", $arEvent)
										&& $arEvent["HAS_COMMENTS"] == "Y"
										&& array_key_exists("CAN_ADD_COMMENTS", $arEvent)
										&& $arEvent["CAN_ADD_COMMENTS"] == "Y"
									)
									{
										$bHasComments = true;
										?><span class="feed-inform-comments"><?=(intval($arEvent["COMMENTS_COUNT"]) > 0 ? "<a href=\"".$url."\">".GetMessage("SONET_C30_COMMENTS")."</a>" : "<a href=\"javascript:void(0);\" onclick=\"BX('feed_comments_block_".$arEvent["EVENT"]["TMP_ID"]."').style.display = 'block'; return __logShowCommentForm('".$arEvent["EVENT"]["TMP_ID"]."')\">".GetMessage("SONET_C30_COMMENT_ADD")."</a>")?></span><?
									}
									else
										$bHasComments = false;

									if (
										strlen($arEvent["EVENT"]["RATING_TYPE_ID"])>0
										&& $arEvent["EVENT"]["RATING_ENTITY_ID"] > 0
										&& $arParams["SHOW_RATING"] == "Y"
									)
									{
										?><span class="feed-inform-ilike"><?
										$APPLICATION->IncludeComponent(
											"bitrix:rating.vote", $arParams["RATING_TYPE"],
											Array(
												"ENTITY_TYPE_ID" => $arEvent["EVENT"]["RATING_TYPE_ID"],
												"ENTITY_ID" => $arEvent["EVENT"]["RATING_ENTITY_ID"],
												"OWNER_ID" => $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
												"USER_VOTE" => $arEvent["EVENT"]["RATING_USER_VOTE_VALUE"],
												"USER_HAS_VOTED" => $arEvent["EVENT"]["RATING_USER_VOTE_VALUE"] == 0? 'N': 'Y',
												"TOTAL_VOTES" => $arEvent["EVENT"]["RATING_TOTAL_VOTES"],
												"TOTAL_POSITIVE_VOTES" => $arEvent["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"],
												"TOTAL_NEGATIVE_VOTES" => $arEvent["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"],
												"TOTAL_VALUE" => $arEvent["EVENT"]["RATING_TOTAL_VALUE"],
												"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"]
											),
											$component,
											array("HIDE_ICONS" => "Y")
										);
										?></span><?
									}

									if (
										$bHasComments 
										&& array_key_exists("FOLLOW", $arEvent["EVENT"])
									)
									{
										?><span class="feed-inform-follow" data-follow="<?=($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N")?>" id="log_entry_follow_<?=intval($arEvent["EVENT"]["ID"])?>" onclick="__logSetFollow(<?=$arEvent["EVENT"]["ID"]?>)"><a href="javascript:void(0);"><?=GetMessage("SONET_LOG_T_FOLLOW_".($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N"))?></a></span><?
									}

									?><span class="feed-post-time-wrap"><?
										if (strlen($url) > 0)
											echo '<a href="'.$url.'">';

										if (
											array_key_exists("EVENT_FORMATTED", $arEvent)
											&& array_key_exists("DATETIME_FORMATTED", $arEvent["EVENT_FORMATTED"])
											&& strlen($arEvent["EVENT_FORMATTED"]["DATETIME_FORMATTED"]) > 0
										)
											echo '<span class="feed-time">'.$arEvent["EVENT_FORMATTED"]["DATETIME_FORMATTED"].'</span>';
										elseif (
											array_key_exists("DATETIME_FORMATTED", $arEvent)
											&& strlen($arEvent["DATETIME_FORMATTED"]) > 0
										)
											echo '<span class="feed-time">'.$arEvent["DATETIME_FORMATTED"].'</span>';
										elseif ($arEvent["LOG_DATE_DAY"] == ConvertTimeStamp())
											echo '<span class="feed-time">'.$arEvent["LOG_TIME_FORMAT"].'</span>';
										else
											echo '<span class="feed-time">'.$arEvent["LOG_DATE_DAY"]." ".$arEvent["LOG_TIME_FORMAT"].'</span>';

										if (strlen($url) > 0)
											echo '</a>';

									?></span>
								</div><?

							?></div><? // cont_wrap

							if (
								array_key_exists("HAS_COMMENTS", $arEvent)
								&& $arEvent["HAS_COMMENTS"] == "Y"
							)
							{
								?><div class="feed-comments-block" id="feed_comments_block_<?=$arEvent["EVENT"]["TMP_ID"]?>" style="display: <?=(intval($arEvent["COMMENTS_COUNT"]) > 0 ? "block" : "none")?>"><?
									if (
										(count($arEvent["COMMENTS"]) > 0 && intval($arEvent["COMMENTS_COUNT"]) > count($arEvent["COMMENTS"]))
										|| (count($arEvent["COMMENTS"]) == 0 && intval($arEvent["COMMENTS_COUNT"]) > 0 && intval($arParams["CREATED_BY_ID"]) > 0)
									)
									{
										?><div class="feed-com-header">
											<div class="feed-com-all"><a href="javascript:void(0);" onclick="__logComments(<?=$arEvent["EVENT"]["TMP_ID"]?>, <?=intval($arResult["LAST_LOG_TS"] + $arResult["TZ_OFFSET"])?>, <?=($arEvent["EVENT"]["FOLLOW"] != "N" ? "true" : "false")?>);"><span class="feed-com-all-text"><?=GetMessage("SONET_C30_ALL_COMMENTS")?></span><span class="feed-comments-all-count"> (<?=intval($arEvent["COMMENTS_COUNT"])?>)</span><span class="feed-comments-all-hide" style="display: none;"><?=GetMessage("SONET_C30_ALL_COMMENTS_HIDE")?></span></a><i></i></div>
										</div>
										<div class="feed-comments-limited"><div class="feed-comments-limited-inner"><?
									}

									if (array_key_exists("COMMENTS", $arEvent) && count($arEvent["COMMENTS"]) > 0)
									{
										foreach($arEvent["COMMENTS"] as $arComment)
										{
											$ind_comment = RandString(8);
											?><div class="feed-com-block<?
												echo (
													array_key_exists("VISIBLE", $arComment) && $arComment["VISIBLE"] == "N" 
														? " feed-hidden-post" 
														: (
															$GLOBALS["USER"]->IsAuthorized()
															&& (
																$arEvent["EVENT"]["FOLLOW"] != "N" 
//																|| intval($arParams["GROUP_ID"]) > 0
															)
															&& $arComment["EVENT"]["USER_ID"] != $GLOBALS["USER"]->GetID() 
															&& intval($arResult["LAST_LOG_TS"]) > 0 
															&& (MakeTimeStamp($arComment["EVENT"]["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"] 
															&& ($arResult["COUNTER_TYPE"] == "**" || $arResult["COUNTER_TYPE"] == "blog_post") 
																? " feed-com-block-new" 
																: ""
														)
												)?><?
											if (
												array_key_exists("USER_ID", $arComment["EVENT"])
												&& intval($arComment["EVENT"]["USER_ID"]) > 0
											)
											{
												?> sonet-log-comment-createdby-<?=$arComment["EVENT"]["USER_ID"]?><?
											}
											?>" id="sonet_log_comment_<?=$ind_comment?>">
												<div class="feed-com-avatar"<?=(strlen($arComment["AVATAR_SRC"]) > 0 ? " style=\"background:url('".$arComment["AVATAR_SRC"]."') no-repeat center #FFFFFF;\"" : "")?>></div><?

												if (
													array_key_exists("TOOLTIP_FIELDS", $arComment["CREATED_BY"])
													&& is_array($arComment["CREATED_BY"]["TOOLTIP_FIELDS"])
												)
												{
													$anchor_id = RandString(8);
													?><a class="feed-com-name" id="anchor_<?=$anchor_id?>" href="<?=str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"], $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"])?>"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arComment["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false))?></a><?
													?><script type="text/javascript">
														BX.tooltip(<?=$arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"]?>, "anchor_<?=$anchor_id?>", "<?=CUtil::JSEscape($ajax_page)?>");
													</script><?
												}
												elseif (
													array_key_exists("FORMATTED", $arComment["CREATED_BY"])
													&& strlen($arComment["CREATED_BY"]["FORMATTED"]) > 0
												)
													echo '<span class="feed-com-name">'.$arComment["CREATED_BY"]["FORMATTED"].'</span>';

												?><div class="feed-com-informers"><?
													echo (
														array_key_exists("EVENT_FORMATTED", $arComment)
														&& array_key_exists("DATETIME", $arComment["EVENT_FORMATTED"])
														&& strlen($arComment["EVENT_FORMATTED"]["DATETIME"]) > 0
															? '<span class="feed-time">'.$arComment["EVENT_FORMATTED"]["DATETIME"].'</span>'
															: ($arComment["LOG_DATE_DAY"] == ConvertTimeStamp() ? '<span class="feed-time">'.$arComment["LOG_TIME_FORMAT"]."</span>" : '<span class="feed-date">'.$arComment["LOG_DATE_DAY"]." ".$arComment["LOG_TIME_FORMAT"].'</span>')
													);

													if (
														strlen($arComment["EVENT"]["RATING_TYPE_ID"]) > 0
														&& $arComment["EVENT"]["RATING_ENTITY_ID"] > 0
														&& $arParams["SHOW_RATING"] == "Y"
													)
													{
														?><span class="sonet-log-comment-like rating_vote_text"><?
														$APPLICATION->IncludeComponent(
															"bitrix:rating.vote", $arParams["RATING_TYPE"],
															Array(
																"ENTITY_TYPE_ID" => $arComment["EVENT"]["RATING_TYPE_ID"],
																"ENTITY_ID" => $arComment["EVENT"]["RATING_ENTITY_ID"],
																"OWNER_ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
																"USER_VOTE" => $arComment["EVENT"]["RATING_USER_VOTE_VALUE"],
																"USER_HAS_VOTED" => $arComment["EVENT"]["RATING_USER_VOTE_VALUE"] == 0? 'N': 'Y',
																"TOTAL_VOTES" => $arComment["EVENT"]["RATING_TOTAL_VOTES"],
																"TOTAL_POSITIVE_VOTES" => $arComment["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"],
																"TOTAL_NEGATIVE_VOTES" => $arComment["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"],
																"TOTAL_VALUE" => $arComment["EVENT"]["RATING_TOTAL_VALUE"],
																"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"]
															),
															$component,
															array("HIDE_ICONS" => "Y")
														);
														?></span><?
													}
												?></div>
												<div class="feed-com-text">
													<div class="feed-com-text-inner"><div class="feed-com-text-inner-inner"><?
														$message = (array_key_exists("EVENT_FORMATTED", $arComment) && array_key_exists("MESSAGE", $arComment["EVENT_FORMATTED"]) ? $arComment["EVENT_FORMATTED"]["MESSAGE"] : $arComment["EVENT"]["MESSAGE"]);
														if (strlen($message) > 0)
															echo CSocNetTextParser::closetags(htmlspecialcharsback($message));
													?></div></div>
													<div class="feed-post-text-more" onclick="__logCommentExpand(this);"><div class="feed-post-text-more-but"><div class="feed-post-text-more-left"></div><div class="feed-post-text-more-right"></div></div></div>
												</div>
												<?
												if ($arParams["SUBSCRIBE_ONLY"] == "Y")
												{
													?><div class="feed-com-block-menu">
														<div class="feed-com-block-menu-but" onclick="__logShowCommentMenu(this, '<?=$ind_comment?>', '', false, '<?=$arComment["EVENT"]["EVENT_ID"] ?>', false, '<?=$arComment["EVENT"]["USER_ID"] ?>', '<?=$arComment["VISIBLE"]?>');" onmousedown="BX.addClass(this, 'feed-com-block-menu-act')" onmouseup="BX.removeClass(this, 'feed-com-block-menu-act')"></div>
													</div><?
												}
											?></div><?
										}
									}

									if (
										(count($arEvent["COMMENTS"]) > 0 && intval($arEvent["COMMENTS_COUNT"]) > count($arEvent["COMMENTS"]))
										|| (count($arEvent["COMMENTS"]) == 0 && intval($arEvent["COMMENTS_COUNT"]) > 0 && intval($arParams["CREATED_BY_ID"]) > 0)
									)
									{
										?></div></div>
										<div class="feed-comments-full" style="display:none"><div class="feed-comments-full-inner"></div></div><?
									}

									if (
										array_key_exists("HAS_COMMENTS", $arEvent)
										&& $arEvent["HAS_COMMENTS"] == "Y"
										&& array_key_exists("CAN_ADD_COMMENTS", $arEvent)
										&& $arEvent["CAN_ADD_COMMENTS"] == "Y"
									)
									{
										?><div class="feed-com-footer" onclick="return __logShowCommentForm('<?=$arEvent["EVENT"]["TMP_ID"]?>')"><?=GetMessage("SONET_C30_COMMENT_ADD")?></div>
										<div class="sonet-log-comment-form-place" id="sonet_log_comment_form_place_<?=$arEvent["EVENT"]["ID"]?>"></div><?
									}
									?><div class="feed-com-corner"></div>
								</div><?
							}

							if ($GLOBALS["USER"]->IsAuthorized())
							{
								?><div class="feed-post-menu-wrap">
									<div class="feed-post-menu-but" onclick="__logShowPostMenu(this, '<?=$ind?>', '<?=$arEvent["EVENT"]["ENTITY_TYPE"] ?>', <?=$arEvent["EVENT"]["ENTITY_ID"] ?>, '<?=$arEvent["EVENT"]["EVENT_ID"] ?>', <?=($arEvent["EVENT"]["EVENT_ID_FULLSET"] ? "'".$arEvent["EVENT"]["EVENT_ID_FULLSET"]."'" : "false")?>, '<?=$arEvent["EVENT"]["USER_ID"] ?>', '<?=$arEvent["VISIBLE"]?>', '<?=$arEvent["EVENT"]["ID"] ?>', <?=(array_key_exists("FAVORITES", $arEvent) && $arEvent["FAVORITES"] == "Y" ? "true" : "false")?>, <?=($arParams["SUBSCRIBE_ONLY"] == "Y" ? "true" : "false")?>);" onmouseup="BX.removeClass(this,'feed-post-menu-but-active')" onmousedown="BX.addClass(this,'feed-post-menu-but-active')"></div>
								</div><?
							}
						?></div><?
					}
				}
			}
		}
		?></div><?
		if (
			$arParams["AJAX_MODE"] == "Y"
			&& array_key_exists("CONTAINER_ID", $arParams)
			&& strlen(trim($arParams["CONTAINER_ID"])) > 0
		)
		{
			?>
			<script type="text/javascript">
				top.BX.bind(top, 'load', function() {
					<?
					if (!$arResult["AJAX_CALL"])
					{
						?>
						if (BX('sonet_log_filter_container'))
							__logMoveBody('sonet_log_filter_container', '<?=$arParams["CONTAINER_ID"]?>');
						__logMoveBody('sonet_log_microblog_container', '<?=$arParams["CONTAINER_ID"]?>');
						__logMoveBody('sonet_log_counter_2_container', '<?=$arParams["CONTAINER_ID"]?>');
						<?
					}
					?>
					__logMoveBody('<?=$log_content_id?>', '<?=$arParams["CONTAINER_ID"]?>');
				});
			</script>
			<?
		}

		if (
			$arParams["AJAX_MODE"] != "Y"
			&& $arParams["SHOW_NAV_STRING"] != "N"
			&& StrLen($arResult["NAV_STRING"]) > 0
		)
		{
			?><div class="sonet-log-nav"><?
			echo $arResult["NAV_STRING"];
			?></div><?
		}
	}

	?></div>

	<div id="sonet_log_comment_form_container" style="display: none;">
	<form method="POST" onsubmit="return false;" action="" id="sonet_log_comment_form">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="sonet_log_comment_logid" id="sonet_log_comment_logid" value="">
		<textarea id="sonet_log_comment_text" cols="35" rows="4"></textarea>
		<script type="text/javascript">
			var CommentFormWidth = 0;
			var CommentFormColsDefault = 0;
			var CommentFormRowsDefault = 0;
			var CommentFormSymbolWidth = 6.6;
			BX.bind(BX('sonet_log_comment_text', true), "keyup", BX.delegate(__logCommentFormAutogrow, this));
		</script>
		<div><input onclick="__logCommentAdd(); return false;" id="sonet_log_post_comment_button" type="submit" value="<?=GetMessage("SONET_C30_COMMENT_SUBMIT")?>" name="add_comment"></div>
	</form>
	</div><?
	// sonet_log_content

}

$next_page = ($arResult["PAGE_ISDESC"] ? $arResult["PAGE_NUMBER"]-1 : $arResult["PAGE_NUMBER"]+1);

if (
	$arParams["AJAX_MODE"] == "Y"
	&& $arParams["SHOW_NAV_STRING"] != "N"
	&& is_array($arResult["EventsNew"])
	&& $event_cnt > 0
	&& (
		$event_cnt >= intval($arParams["PAGE_SIZE"])
		|| $arResult["SHOW_MORE_LINK"]
	)
	&& (
		$arResult["PAGE_ISDESC"] && $next_page > 0
		||
		!$arResult["PAGE_ISDESC"] && $next_page <= $arResult["PAGE_NAVCOUNT"]
	)
)
{
	$strParams = "PAGEN_".$arResult["PAGE_NAVNUM"]."=".$next_page;
	if (!$arResult["AJAX_CALL"])
		$strParams .= "&ts=".$arResult["LAST_LOG_TS"];

	?><a href="<?=$APPLICATION->GetCurPageParam($strParams, array("PAGEN_".$arResult["PAGE_NAVNUM"], "RELOAD"));?>" id="sonet_log_more_container" class="feed-new-message-informer feed-new-message-inf-bottom"><span class="feed-new-message-inf-text"><?=GetMessage("SONET_C30_MORE")?><span class="feed-new-message-icon"></span></span><span class="feed-new-message-inf-text feed-new-message-inf-text-waiting" style="display: none;"><span class="feed-new-message-wait-icon"></span><?=GetMessage("SONET_C30_T_MORE_WAIT")?></span></a><?
}
?>