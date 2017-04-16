<?
if(!CModule::IncludeModule('rest'))
	return;

class CIMRestService extends IRestService
{
	public static function OnRestServiceBuildDescription()
	{
		return array(
			'im' => array(
				'im.chat.add' => array('CIMRestService', 'chatCreate'),
				'im.chat.delete' => array('CIMRestService', 'chatDelete'),

				'im.chat.list.open' => array('CIMRestService', 'chatListOpen'),
				'im.chat.list.chat' => array('CIMRestService', 'chatListChat'),

				'im.chat.setOwner' => array('CIMRestService', 'chatSetOwner'),
				'im.chat.updateColor' => array('CIMRestService', 'chatUpdateColor'),
				'im.chat.updateTitle' => array('CIMRestService', 'chatUpdateTitle'),
				'im.chat.updateAvatar' => array('CIMRestService', 'chatUpdateAvatar'),
				'im.chat.leave' => array('CIMRestService', 'chatUserDelete'),

				'im.chat.user.add' => array('CIMRestService', 'chatUserAdd'),
				'im.chat.user.delete' => array('CIMRestService', 'chatUserDelete'),
				'im.chat.user.list' => array('CIMRestService', 'chatUserList'),

				'im.chat.sendTyping' => array('CIMRestService', 'chatSendTyping'),

				'im.message.add' => array('CIMRestService', 'messageAdd'),
				'im.message.delete' => array('CIMRestService', 'messageDelete'),
				'im.message.update' => array('CIMRestService', 'messageUpdate'),
				'im.message.like' => array('CIMRestService', 'messageLike'),

				'im.notify' => array('CIMRestService', 'notifyAdd'),
				'im.notify.personal.add' => array('CIMRestService', 'notifyAdd'),
				'im.notify.system.add' => array('CIMRestService', 'notifyAdd'),
				'im.notify.delete' => array('CIMRestService', 'notifyDelete'),

				'im.bot.register' => array('CIMRestService', 'notImplemented'),
				'im.bot.unregister' => array('CIMRestService', 'notImplemented'),
				'im.bot.update' => array('CIMRestService', 'notImplemented'),
				'im.bot.slash.add' => array('CIMRestService', 'notImplemented'),
				'im.bot.slash.remove' => array('CIMRestService', 'notImplemented'),
				'im.bot.keyboard.show' => array('CIMRestService', 'notImplemented'),
				'im.bot.keyboard.hide' => array('CIMRestService', 'notImplemented'),
			),
		);
	}

	public static function chatCreate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!is_array($arParams['USERS']) || empty($arParams['USERS']))
		{
			throw new Bitrix\Rest\RestException("Please select users before creating a new chat.", "USERS_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['AVATAR']))
		{
			$arParams['AVATAR'] = CRestUtil::saveFile($arParams['AVATAR']);
			if (!$arParams['AVATAR'] || strpos($arParams['AVATAR']['type'], "image/") !== 0)
			{
				$arParams['AVATAR'] = 0;
			}
			else
			{
				$arParams['AVATAR'] = CFile::saveFile($arParams['AVATAR'], 'im');
			}
		}
		else
		{
			$arParams['AVATAR'] = 0;
		}


		$add = Array(
			'TYPE' => $arParams['TYPE'] == 'OPEN'? IM_MESSAGE_OPEN: IM_MESSAGE_CHAT,
			'USERS' => $arParams['USERS'],
		);
		if ($arParams['AVATAR'] > 0)
		{
			$add['AVATAR_ID'] = $arParams['AVATAR'];
		}
		if (isset($arParams['COLOR']))
		{
			$add['COLOR'] = $arParams['COLOR'];
		}
		if (isset($arParams['MESSAGE']))
		{
			$add['MESSAGE'] = $arParams['MESSAGE'];
		}
		if (isset($arParams['TITLE']))
		{
			$add['TITLE'] = $arParams['TITLE'];
		}
		if (isset($arParams['DESCRIPTION']))
		{
			$add['DESCRIPTION'] = $arParams['DESCRIPTION'];
		}

		$CIMChat = new CIMChat();
		$chatId = $CIMChat->Add($add);
		if (!$chatId)
		{
			throw new Bitrix\Rest\RestException("Chat can't be created.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $chatId;
	}

	public static function chatDelete($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		throw new Bitrix\Rest\RestException("Method isn't implemented yet.", "NOT_IMPLEMENTED", CRestServer::STATUS_NOT_FOUND);
	}

	public static function chatListOpen($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		throw new Bitrix\Rest\RestException("Method isn't implemented yet.", "NOT_IMPLEMENTED", CRestServer::STATUS_NOT_FOUND);
	}

	public static function chatListChat($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		throw new Bitrix\Rest\RestException("Method isn't implemented yet.", "NOT_IMPLEMENTED", CRestServer::STATUS_NOT_FOUND);
	}

	public static function chatSetOwner($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['USER_ID'] = intval($arParams['USER_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty.", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chat = new CIMChat();
		$result = $chat->SetOwner($arParams['CHAT_ID'], $arParams['USER_ID']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Change owner can only owner or user isn't member in chat.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateColor($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Bitrix\Im\Color::isSafeColor($arParams['COLOR']))
		{
			throw new Bitrix\Rest\RestException("This color currently unavailable.", "WRONG_COLOR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new CIMChat();
		$result = $chat->SetColor($arParams['CHAT_ID'], $arParams['COLOR']);

		if (!$result)
		{
			throw new Bitrix\Rest\RestException("This color currently set or chat isn't exists.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateTitle($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['TITLE'] = trim($arParams['TITLE']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}
		if (empty($arParams['TITLE']))
		{
			throw new Bitrix\Rest\RestException("Title can't be empty.", "TITLE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}


		$chat = new CIMChat();
		$result = $chat->Rename($arParams['CHAT_ID'], $arParams['TITLE']);

		if (!$result)
		{
			throw new Bitrix\Rest\RestException("This title currently set or chat isn't exists.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateAvatar($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams['AVATAR'] = CRestUtil::saveFile($arParams['AVATAR']);
		if (!$arParams['AVATAR'] || strpos($arParams['AVATAR']['type'], "image/") !== 0)
		{
			throw new Bitrix\Rest\RestException("Avatar incorrect.", "AVATAR_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['AVATAR'] = CFile::saveFile($arParams['AVATAR'], 'im');

		$result = CIMDisk::UpdateAvatarId($arParams['CHAT_ID'], $arParams['AVATAR']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Chat isn't exists.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$CIMChat = new CIMChat();
		$result = $CIMChat->AddUser($arParams['CHAT_ID'], $arParams['USERS'], $arParams['HIDE_HISTORY'] != "N");
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("You don't have access or user already member in chat.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserDelete($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['USER_ID'] = intval($arParams['USER_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if ($server->getMethod() == "im.chat.user.delete" && $arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty.", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$CIMChat = new CIMChat();
		$result = $CIMChat->DeleteUser($arParams['CHAT_ID'], $arParams['USER_ID'] > 0? $arParams['USER_ID']: $USER->GetID());
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("You don't have access or user isn't member in chat.", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserList($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arChat = CIMChat::GetChatData(array(
			'ID' => $arParams['CHAT_ID'],
			'USE_CACHE' => 'Y',
			'USER_ID' => $USER->GetId()
		));

		return isset($arChat['userInChat'][$arParams['CHAT_ID']])? $arChat['userInChat'][$arParams['CHAT_ID']]: Array();
	}

	public static function chatSendTyping($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = CIMMessenger::StartWriting('chat'.$arParams['CHAT_ID']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		return true;
	}

	public static function messageAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		if (strlen($arParams['MESSAGE']) <= 0)
		{
			throw new Bitrix\Rest\RestException("Message can't be empty.", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['FROM_USER_ID'] = $USER->GetId();
		if (isset($arParams['USER_ID']))
		{
			$arParams['USER_ID'] = intval($arParams['USER_ID']);
			if ($arParams['USER_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("User ID can't be empty.", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (!Bitrix\Im\User::getInstance($arParams['USER_ID'])->isExists())
			{
				throw new Bitrix\Rest\RestException("User not found", "USER_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
			}

			$arMessageFields = Array(
				"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE,
				"FROM_USER_ID" => $arParams['FROM_USER_ID'],
				"TO_USER_ID" => $arParams['USER_ID'],
				"MESSAGE" 	 => $arParams['MESSAGE'],
			);
		}
		else if (isset($arParams['CHAT_ID']))
		{
			$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
			if ($arParams['CHAT_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("Chat ID can't be empty.", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
			if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($arParams['FROM_USER_ID']))
			{
				throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}

			if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
			{
				$result = \CBitrix24App::getList(array(), array('APP_ID' => $server->getAppId()));
				$result = $result->fetch();
				$moduleName = isset($result['APP_NAME'])? $result['APP_NAME']: $result['CODE'];

				$arParams['MESSAGE'] = "[b]".$moduleName."[/b]\n".$arParams['MESSAGE'];
			}
			else
			{
				$arRelation = CIMChat::GetRelationById($arParams['CHAT_ID']);
				if (!isset($arRelation[$arParams['FROM_USER_ID']]))
				{
					throw new Bitrix\Rest\RestException("You don't have access or user isn't member in chat.", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}

			$arMessageFields = Array(
				"MESSAGE_TYPE" => IM_MESSAGE_CHAT,
				"FROM_USER_ID" => $arParams['FROM_USER_ID'],
				"TO_CHAT_ID" => $arParams['CHAT_ID'],
				"MESSAGE" 	 => $arParams['MESSAGE'],
			);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['ATTACH'] = self::prepareAttach($arParams['ATTACH']);
		if ($arParams['ATTACH'])
		{
			$arMessageFields['ATTACH'] = $arParams['ATTACH'];
		}
		if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
		{
			$arMessageFields['SYSTEM'] = 'Y';
		}
		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}

		$id = CIMMessenger::Add($arMessageFields);
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $id;

	}

	public static function messageDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			$res = CIMMessenger::Delete($arParams['ID']);
			if (!$res)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access.", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function messageUpdate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			if (isset($arParams['ATTACH']))
			{
				$message = CIMMessenger::CheckPossibilityUpdateMessage($arParams['ID']);
				if (!$message)
				{
					throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access.", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
				}

				if (empty($arParams['ATTACH']))
				{
					CIMMessageParam::Set($arParams['ID'], Array('IS_EDITED' => 'Y', 'ATTACH' => Array()));
				}
				else
				{
					$arParams['ATTACH'] = self::prepareAttach($arParams['ATTACH']);
					if ($arParams['ATTACH'])
					{
						CIMMessageParam::Set($arParams['ID'], Array('IS_EDITED' => 'Y', 'ATTACH' => $arParams['ATTACH']));
					}
				}
			}

			if (isset($arParams['MESSAGE']))
			{
				$urlPreview = isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == "N"? false: true;

				$res = CIMMessenger::Update($arParams['ID'], $arParams['MESSAGE'], $urlPreview);
				if (!$res)
				{
					throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access.", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
				}
			}
			else
			{
				CIMMessageParam::SendPull($arParams['ID']);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function messageLike($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['ACTION'] = strtolower($arParams['ACTION']);
		if (!in_array($arParams['ACTION'], Array('auto', 'plus', 'minus')))
		{
			$arParams['ACTION'] = 'auto';
		}

		$result = CIMMessenger::Like($arParams['ID'], $arParams['ACTION']);
		if ($result === false)
		{
			throw new Bitrix\Rest\RestException("Action completed without changes.", "WITHOUT_CHANGES", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function notifyAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['TO']))
		{
			$arParams['USER_ID'] = $arParams['TO'];
		}
		$arParams['USER_ID'] = intval($arParams['USER_ID']);
		if ($arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty.", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($server->getMethod() == "im.notify.personal.add")
		{
			$arParams['TYPE'] = 'USER';
		}
		else if ($server->getMethod() == "im.notify.system.add")
		{
			$arParams['TYPE'] = 'SYSTEM';
		}
		else if (!isset($arParams['TYPE']) || !in_array($arParams['TYPE'], Array('USER', 'SYSTEM')))
		{
			$arParams['TYPE'] = 'USER';
		}

		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		if (strlen($arParams['MESSAGE']) <= 0)
		{
			throw new Bitrix\Rest\RestException("Message can't be empty.", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageOut = "";
		$arParams['MESSAGE_OUT'] = trim($arParams['MESSAGE_OUT']);
		if ($arParams['TYPE'] == 'SYSTEM')
		{
			$result = \CBitrix24App::getList(array(), array('APP_ID' => $server->getAppId()));
			$result = $result->fetch();
			$moduleName = isset($result['APP_NAME'])? $result['APP_NAME']: $result['CODE'];

			$fromUserId = 0;
			$notifyType = IM_NOTIFY_SYSTEM;
			$message = $moduleName."#BR#".$arParams['MESSAGE'];
			if (!empty($arParams['MESSAGE_OUT']))
			{
				$messageOut = $moduleName."#BR#".$arParams['MESSAGE_OUT'];
			}
		}
		else
		{
			$fromUserId = $USER->GetID();
			$notifyType = IM_NOTIFY_FROM;
			$message = $arParams['MESSAGE'];
			if (!empty($arParams['MESSAGE_OUT']))
			{
				$messageOut = $arParams['MESSAGE_OUT'];
			}
		}

		$arMessageFields = array(
			"TO_USER_ID" => $arParams['USER_ID'],
			"FROM_USER_ID" => $fromUserId,
			"NOTIFY_TYPE" => $notifyType,
			"NOTIFY_MODULE" => "rest",
			"NOTIFY_EVENT" => "rest_notify",
			"NOTIFY_MESSAGE" => $message,
			"NOTIFY_MESSAGE_OUT" => $messageOut,
		);
		if (!empty($arParams['TAG']))
		{
			$appKey = substr(md5($server->getAppId()), 0, 5);
			$arMessageFields['NOTIFY_TAG'] = 'MP|'.$appKey.'|'.$arParams['TAG'];
		}
		if (!empty($arParams['SUB_TAG']))
		{
			$appKey = substr(md5($server->getAppId()), 0, 5);
			$arMessageFields['NOTIFY_SUB_TAG'] = 'MP|'.$appKey.'|'.$arParams['SUB_TAG'];
		}

		$arParams['ATTACH'] = self::prepareAttach($arParams['ATTACH']);
		if ($arParams['ATTACH'])
		{
			$arMessageFields['ATTACH'] = $arParams['ATTACH'];
		}

		return CIMNotify::Add($arMessageFields);
	}

	public static function notifyDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			$CIMNotify = new CIMNotify();
			$result = $CIMNotify->DeleteWithCheck($arParams['ID']);
		}
		else if (!empty($arParams['TAG']))
		{
			$appKey = substr(md5($server->getAppId()), 0, 5);
			$result = CIMNotify::DeleteByTag('MP|'.$appKey.'|'.$arParams['TAG']);
		}
		else if (!empty($arParams['SUB_TAG']))
		{
			$appKey = substr(md5($server->getAppId()), 0, 5);
			$result = CIMNotify::DeleteBySubTag('MP|'.$appKey.'|'.$arParams['SUB_TAG']);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params.", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $result;
	}

	public static function notImplemented($arParams, $n, CRestServer $server)
	{
		throw new Bitrix\Rest\RestException("Method isn't implemented yet.", "NOT_IMPLEMENTED", CRestServer::STATUS_NOT_FOUND);
	}

	private static function prepareAttach($array)
	{
		if (!$array)
			return false;

		if (!is_array($array))
			return false;

		if (isset($array[0]['BLOCKS']))
		{
			$array = $array[0];
		}

		$id = null;
		$color = null;
		$attach = null;

		if (isset($array['BLOCKS']))
		{
			$blocks = $array['BLOCKS'];

			if (isset($array['ID']))
			{
				$id = $array['ID'];
			}
			if (isset($array['COLOR']))
			{
				$color = $array['COLOR'];
			}
		}
		else
		{
			$blocks = $array;
		}

		$attach = new CIMMessageParamAttach($id, $color);
		foreach ($blocks as $data)
		{
			if (isset($data['USER']))
			{
				if (is_array($data['USER']) && !\Bitrix\Main\Type\Collection::isAssociative($data['USER']))
				{
					foreach ($data['USER'] as $dataItem)
					{
						$attach->AddUser($dataItem);
					}
				}
				else
				{
					$attach->AddUser($data['USER']);
				}
			}
			else if (isset($data['LINK']))
			{
				if (is_array($data['LINK']) && !\Bitrix\Main\Type\Collection::isAssociative($data['LINK']))
				{
					foreach ($data['LINK'] as $dataItem)
					{
						$attach->AddLink($dataItem);
					}
				}
				else
				{
					$attach->AddLink($data['LINK']);
				}
			}
			else if (isset($data['MESSAGE']))
			{
				$attach->AddMessage($data['MESSAGE']);
			}
			else if (isset($data['GRID']))
			{
				$attach->AddGrid($data['GRID']);
			}
			else if (isset($data['IMAGE']))
			{
				if (is_array($data['IMAGE']) && \Bitrix\Main\Type\Collection::isAssociative($data['IMAGE']))
				{
					$data['IMAGE'] = Array($data['IMAGE']);
				}
				$attach->AddImages($data['IMAGE']);
			}
			else if (isset($data['FILE']))
			{
				if (is_array($data['FILE']) && \Bitrix\Main\Type\Collection::isAssociative($data['FILE']))
				{
					$data['FILE'] = Array($data['FILE']);
				}
				$attach->AddFiles($data['FILE']);
			}
			else if (isset($data['DELIMITER']))
			{
				$attach->AddDelimiter($data['DELIMITER']);
			}
		}

		return $attach->IsEmpty()? false: $attach;
	}
}
?>