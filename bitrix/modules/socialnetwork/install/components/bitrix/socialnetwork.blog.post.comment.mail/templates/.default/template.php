<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table cellpadding="0" cellspacing="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;width: 693px;">
	<tr>
		<td valign="top" width="50" style="border-collapse: collapse;border-spacing: 0;vertical-align: top;padding: 0 17px 0 0;width: 50px;"><?
			$src = $arResult["AUTHORS"][$arResult["POST"]["AUTHOR_ID"]]["AVATAR_URL"];
			?><img height="50" width="50" src="<?=$src?>" alt="user" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;display: block;">
		</td>
		<td valign="top" style="border-collapse: collapse;border-spacing: 0;text-align: left;vertical-align: top;">
			<table cellpadding="0" cellspacing="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 14px;width: 100%;">
				<tr>
					<td align="left" valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 3px 0 8px;text-align: left;">
						<span style="color: #2067b0;font-size: 14px;font-weight: bold;vertical-align: top;"><?
							?><?=$arResult["AUTHORS"][$arResult["POST"]["AUTHOR_ID"]]["NAME_FORMATTED"]?><?
						?></span><?

						if (!empty($arResult["DESTINATIONS"]))
						{
							$src = "/bitrix/components/bitrix/socialnetwork.blog.post.comment.mail/templates/.default/images/arrow.gif";
							?><img height="16" width="20" src="<?=$src?>" alt="&rarr;" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline;font-size: 19px;vertical-align: top;line-height: 15px;"><?
							?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?
								$i = 0;
								foreach ($arResult["DESTINATIONS"] as $destinationName)
								{
									if ($i > 0)
									{
										?>, <?
									}
									?><span style="color: #7f7f7f;font-size: 14px;vertical-align: top;"><?=($destinationName == "#ALL#" ? GetMessage("BLOG_DESTINATION_ALL") : $destinationName)?></span><?
									$i++;
								}
							?></span><?
						}

					?></td>
				</tr>
				<tr>
					<td style="border-collapse: collapse;border-spacing: 0;">
						<table cellspacing="0" cellpadding="0" border="0" align="left" width="80%" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;width: 80%;">
							<tr>
								<td valign="top" style="border-collapse: collapse;border-spacing: 0;padding: 13px 0 14px;vertical-align: top;" width="0%"><?
									$src = "/bitrix/components/bitrix/socialnetwork.blog.post.comment.mail/templates/.default/images/post-icon.gif";
									?><img width="30" height="29" alt="" src="<?=$src?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;vertical-align: top;"><?
								?></td>
								<td valign="middle" style="border-collapse: collapse;border-spacing: 0;padding: 9px 0 14px 16px;vertical-align: top;" width="100%">
									<a href="<?=$arResult["POST_URL"]?>" style="color: #0067a3;border-bottom: 1px solid #0067a3;font-size: 14px;line-height: 18px;text-decoration: none;">
										<!--[if gte mso 9]>
										<span style="text-decoration: underline;">
										<![endif]-->
										<?=$arResult["POST"]["TITLE_FORMATTED"]?>
										<!--[if gte mso 9]>
										</span>
										<![endif]-->
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr><?

				if (!empty($arResult["COMMENTS"]))
				{
					?><tr>
						<td valign="top" align="left" style="border-collapse: collapse;border-spacing: 0;padding: 0 0 36px;">
							<table cellspacing="0" cellpadding="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;background-color: #f5f7f8;width: 100%;">
								<tr>
									<td border="0" valign="top" align="center" bgcolor="#ffffff" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 10px;padding: 0;vertical-align: top;width: 39px;border: none;background-color: #ffffff;height: 10px;text-align: center;"><?
										$src = "/bitrix/components/bitrix/socialnetwork.blog.post.comment.mail/templates/.default/images/comments-corner.gif";
										?><img src="<?=$src?>" alt="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;display: inline-block;vertical-align: top;">
									</td>
									<td border="0" bgcolor="#ffffff" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #000000;font-size: 13px;line-height: 20px;padding: 0;border: none;background-color: #ffffff;height: 10px;"></td>
								</tr><?

								if (
									$arParams["COMMENTS_COUNT"] > 0
									&& $arResult["COMMENTS_ALL_COUNT"] > $arParams["COMMENTS_COUNT"]
								)
								{
									?><tr>
										<td valign="middle" colspan="2" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 0 0 0 16px;vertical-align: middle;width: 39px;color: #2067b0;height: 38px;font-size: 12px;">
											<a href="<?=$arResult["POST_URL"]?>" style="color: #146cc5;"><?=GetMessage('SBPCM_TEMPLATE_MORE_COMMENTS', array("#NUM#" => ($arResult["COMMENTS_ALL_COUNT"] - $arParams["COMMENTS_COUNT"])))?></a>
										</td>
									</tr><?
								}

								$i = 0;
								foreach ($arResult["COMMENTS"] as $arComment)
								{
									$strBgColor = (
										$i == (count($arResult["COMMENTS"]) - 1)
											? 'background-color:#fffcee;'
											: ''
									)

									?>
									<tr>
										<td valign="top" align="center" style="<?=$strBgColor?>border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 12px 15px 12px 19px;vertical-align: top;width: 39px;">
											<img height="39" width="39" src="<?=$arResult["AUTHORS"][$arComment["AUTHOR_ID"]]["AVATAR_COMMENT_URL"]?>" alt="<?=$arResult["AUTHORS"][$arComment["AUTHOR_ID"]]["NAME_FORMATTED"]?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;height: 39px;width: 39px;">
										</td>
										<td valign="top" style="<?=$strBgColor?>border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #000000;font-size: 13px;line-height: 20px;padding: 12px 15px 15px 0;">
											<span style="color: #0b66c3;font-size: 13px;font-weight: bold;line-height: 16px;vertical-align: top;"><?=$arResult["AUTHORS"][$arComment["AUTHOR_ID"]]["NAME_FORMATTED"]?></span>
											&nbsp;
											<span style="color: #999999;font-size: 11px;line-height: 16px;vertical-align: top;"><?
												?><?=$arComment["DATE_CREATE_FORMATTED"]?><?

												if (false)
												{
													?>&nbsp;
													<a href="<?=$arResult["POST_URL"]?>" style="color: #80868e;"><?=GetMessage('SBPCM_TEMPLATE_LIKE')?></a>
													&nbsp;
													<a href="<?=$arResult["POST_URL"]?>" style="color: #80868e;"><?=GetMessage('SBPCM_TEMPLATE_REPLY')?></a><?

												}
											?></span>
											<br>
											<?=$arComment["POST_TEXT_FORMATTED"]?><?

											if (!empty($arComment["ATTACHMENTS"]))
											{
												?><br>
												<table cellspacing="0" cellpadding="0" border="0" align="left" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 13px;">
													<tr>
														<td valign="top" style="border-collapse: collapse;border-spacing: 0;color: #000000;padding: 5px 5px 0 0;vertical-align: top;"><?=GetMessage('SBPCM_TEMPLATE_FILES')?></td>
														<td valign="top" style="border-collapse: collapse;border-spacing: 0; padding: 5px 0 0 0;"><?
															$j = 0;
															foreach($arComment["ATTACHMENTS"] as $attachment)
															{
																if ($j > 0)
																{
																	?><br/><?
																}
																?><a href="<?=$attachment["URL"]?>" style="color: #146cc5;"><?=$attachment["NAME"]?> (<?=$attachment["SIZE"]?>)</a><?
																$j++;
															}
															?></td>
													</tr>
												</table>
												<?
											}

										?></td>
									</tr>
									<?
									$i++;
								}
								?>
								<tr>
									<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;line-height: 0;padding: 12px 15px 12px 19px;vertical-align: top;width: 39px;height: 39px;"><?
										$src = "/bitrix/components/bitrix/socialnetwork.blog.post.comment.mail/templates/.default/images/userpic.gif";
										?><img height="39" width="39" src="<?=$src?>" alt="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border-radius: 50%;height: 39px;width: 39px;">
									</td>
									<td style="border-collapse: collapse;border-spacing: 0;border-bottom: 2px solid #FFFFFF;color: #80868e;font-size: 13px;line-height: 20px;padding: 12px 15px 15px 0;height: 39px;">
										<a href="<?=$arResult["POST_URL"]?>" style="color: #80868e;"><?=GetMessage('SBPCM_TEMPLATE_ADD_COMMENT_LINK')?></a>
									</td>
								</tr>
							</table>
						</td>
					</tr><?
				}
				?><tr>
					<td valign="top" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #edeef0;padding: 33px 0 20px;">
						<table cellspacing="0" cellpadding="0" border="0" align="center" style="border-collapse: collapse;mso-table-lspace: 0pt;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
							<tr>
								<td style="border-collapse: collapse;border-spacing: 0;background-color: #44c8f2;padding: 0;">
									<a target="_blank" href="<?=$arResult["POST_URL"]?>" style="color: #ffffff;background-color: #44c8f2;border: 8px solid #44c8f2;border-radius: 2px;display: block;font-family: Helvetica, Arial, sans-serif;font-size: 12px;font-weight: bold;padding: 4px;text-transform: uppercase;text-decoration: none;"><?=GetMessage('SBPCM_TEMPLATE_ADD_COMMENT_BUTTON')?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="border-collapse: collapse;border-spacing: 0;color: #8b959d;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size: 11px;text-align: center;padding: 14px 0 0;">
						<?=GetMessage('SBPCM_TEMPLATE_DESCRIPTION', array("#BR#" => '<br/>'))?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>