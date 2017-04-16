<?
use Bitrix\Im as IM;
use Bitrix\Main\UrlPreview as UrlPreview;

class CIMMessageParam
{
	public static function Set($messageId, $params = Array())
	{
		$messageId = intval($messageId);
		if(!(is_array($params) || is_null($params)) || $messageId <= 0)
			return false;

		if (is_null($params) || count($params) <= 0)
		{
			return self::DeleteAll($messageId);
		}

		$default = self::GetDefault();

		$arToDelete = array();
		foreach ($params as $key => $val)
		{
			if (isset($default[$key]) && $default[$key] == $val)
			{
				$arToDelete[$key] = array(
					"=MESSAGE_ID" => $messageId,
					"=PARAM_NAME" => $key,
				);
			}
		}

		$arToInsert = array();
		foreach($params as $k1 => $v1)
		{
			$name = substr(trim($k1), 0, 100);
			if(strlen($name))
			{
				if(is_object($v1) && $v1 instanceof CIMMessageParamAttach)
				{
					$v1 = array($v1);
				}
				else if(is_array($v1) && \Bitrix\Main\Type\Collection::isAssociative($v1))
				{
					$v1 = array($v1);
				}
				else if (!is_array($v1))
				{
					$v1 = array($v1);
				}
				
				if (empty($v1))
				{
					$arToDelete[$name] = array(
						"=MESSAGE_ID" => $messageId,
						"=PARAM_NAME" => $name,
					);
				}
				else
				{
					foreach($v1 as $v2)
					{
						if (is_array($v2))
						{
							$value = \Bitrix\Main\Web\Json::encode($v2);
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => isset($v2['ID'])? $v2['ID']: time(),
									"PARAM_JSON" => $value,
								);
							}
						}
						else if(is_object($v2) && $v2 instanceof CIMMessageParamAttach)
						{
							$value = $v2->GetJSON();
							$valueArray = $v2->GetArray();
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => $valueArray['ID'],
									"PARAM_JSON" => $value,
								);
							}
						}
						else
						{
							$value = substr(trim($v2), 0, 100);
							if(strlen($value))
							{
								$key = md5($name.$value);
								$arToInsert[$key] = array(
									"MESSAGE_ID" => $messageId,
									"PARAM_NAME" => $name,
									"PARAM_VALUE" => $value,
								);
							}
						}
					}
				}
			}
		}

		if(!empty($arToInsert))
		{
			$messageParameters = IM\MessageParamTable::getList(array(
				'select' => array('ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'),
				'filter' => array(
					'=MESSAGE_ID' => $messageId,
				),
			));
			while($ar = $messageParameters->fetch())
			{
				if (strlen($ar['PARAM_JSON']))
				{
					$key = md5($ar["PARAM_NAME"].$ar["PARAM_JSON"]);
				}
				else
				{
					$key = md5($ar["PARAM_NAME"].$ar["PARAM_VALUE"]);
				}
				if(array_key_exists($key, $arToInsert))
				{
					unset($arToInsert[$key]);
				}
				else if (isset($params[$ar["PARAM_NAME"]]))
				{
					IM\MessageParamTable::delete($ar['ID']);
				}
			}
		}

		foreach($arToInsert as $parameterInfo)
		{
			IM\MessageParamTable::add($parameterInfo);
		}

		foreach($arToDelete as $filter)
		{
			$messageParameters = IM\MessageParamTable::getList(array(
				'select' => array('ID'),
				'filter' => $filter,
			));
			while ($parameterInfo = $messageParameters->fetch())
			{
				IM\MessageParamTable::delete($parameterInfo['ID']);
			}
		}

		return true;
	}

	public static function SendPull($messageId)
	{
		global $DB;

		if (!CModule::IncludeModule('pull'))
			return false;

		$messageId = intval($messageId);

		$sql = "
			SELECT C.ID CHAT_ID, C.TYPE MESSAGE_TYPE, M.AUTHOR_ID
			FROM b_im_message M INNER JOIN b_im_chat C ON M.CHAT_ID = C.ID
			WHERE M.ID = ".$messageId."
		";
		$messageData = $DB->Query($sql)->Fetch();
		if (!$messageData)
			return false;

		$arPullMessage = Array(
			'id' => $messageId,
			'type' => $messageData['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE? 'private': 'chat',
		);

		$relations = CIMMessenger::GetRelationById($messageId);

		if ($messageData['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$arFields['FROM_USER_ID'] = $messageData['AUTHOR_ID'];
			foreach ($relations as $rel)
			{
				if ($rel['USER_ID'] != $messageData['AUTHOR_ID'])
					$arFields['TO_USER_ID'] = $rel['USER_ID'];
			}

			$arPullMessage['fromUserId'] = $arFields['FROM_USER_ID'];
			$arPullMessage['toUserId'] = $arFields['TO_USER_ID'];
		}
		else
		{
			$arPullMessage['chatId'] = $messageData['CHAT_ID'];
			$arPullMessage['senderId'] = $messageData['AUTHOR_ID'];
		}

		$arMessages[$messageId] = Array();
		$params = CIMMessageParam::Get(Array($messageId));
		foreach ($params as $mid => $param)
		{
			$arMessages[$mid]['params'] = $param;
			if (isset($arMessages[$mid]['params']['URL_ID']))
				unset($arMessages[$mid]['params']['URL_ID']);
		}
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
		$arPullMessage['params'] = CIMMessenger::PrepareParamsForPull($arMessages[$messageId]['params']);

		CPullStack::AddByUsers(array_keys($relations), Array(
			'module_id' => 'im',
			'command' => 'messageParamsUpdate',
			'params' => $arPullMessage,
		));

		if ($messageData['MESSAGE_TYPE'] == IM_MESSAGE_OPEN)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$messageData['CHAT_ID'], Array(
				'module_id' => 'im',
				'command' => 'messageParamsUpdate',
				'params' => $arPullMessage,
			));
		}

		return true;
	}

	public static function DeleteAll($messageId)
	{
		$messageId = intval($messageId);
		if ($messageId <= 0)
			return false;

		$messageParameters = IM\MessageParamTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=MESSAGE_ID' => $messageId,
			),
		));
		while ($parameterInfo = $messageParameters->fetch())
		{
			IM\MessageParamTable::delete($parameterInfo['ID']);
		}

		return true;
	}

	public static function Get($messageId, $params = false)
	{
		$arResult = array();
		if (is_array($messageId))
		{
			if (!empty($messageId))
			{
				foreach ($messageId as $key => $value)
				{
					$messageId[$key] = intval($value);
					$arResult[$messageId[$key]] = Array();
				}
			}
			else
			{
				return $arResult;
			}
		}
		else
		{
			$messageId = intval($messageId);
			if ($messageId <= 0)
			{
				return false;
			}
			$arResult[$messageId] = Array();
		}

		$filter = array(
			'=MESSAGE_ID' => $messageId,
		);
		if ($params && strlen($params) > 0)
		{
			$filter['=PARAM_NAME'] = $params;
		}
		$messageParameters = IM\MessageParamTable::getList(array(
			'select' => array('ID', 'MESSAGE_ID', 'PARAM_NAME', 'PARAM_VALUE', 'PARAM_JSON'),
			'filter' => $filter,
		));
		while($ar = $messageParameters->fetch())
		{
			if (strlen($ar["PARAM_JSON"]))
			{
				$value = \Bitrix\Main\Web\Json::decode($ar["PARAM_JSON"]);
			}
			else
			{
				$value = $ar["PARAM_VALUE"];
			}
			$arResult[$ar["MESSAGE_ID"]][$ar["PARAM_NAME"]][] = $value;
		}

		if (is_array($messageId))
		{
			foreach ($messageId as $key)
			{
				$arResult[$key] = self::PrepareValues($arResult[$key]);
			}
		}
		else
		{
			$arResult = self::PrepareValues($arResult[$messageId]);
		}

		return $arResult;
	}

	public static function GetMessageIdByParam($paramName, $paramValue)
	{
		$arResult = Array();
		if (strlen($paramName) <= 0 || strlen($paramValue) <= 0)
		{
			return $arResult;
		}

		$messageParameters = IM\MessageParamTable::getList(array(
			'select' => array('MESSAGE_ID'),
			'filter' => array(
				'=PARAM_NAME' => $paramName,
				'=PARAM_VALUE' => $paramValue,
			),
		));
		while($ar = $messageParameters->fetch())
		{
			$arResult[] = $ar["MESSAGE_ID"];
		}

		return $arResult;
	}

	public static function PrepareValues($values)
	{
		$arValues = Array();

		$arDefault = self::GetDefault();
		foreach($values as $key => $value)
		{
			if (in_array($key, Array('IS_DELETED', 'IS_EDITED', 'CAN_ANSWER')))
			{
				$arValues[$key] = in_array($value[0], Array('Y', 'N'))? $value[0]: $arDefault[$key];
			}
			else if ($key == 'FILE_ID' || $key == 'LIKE' || $key == 'URL_ID')
			{
				if (is_array($value) && !empty($value))
				{
					foreach ($value as $k => $v)
					{
						$arValues[$key][$k] = intval($v);
					}
				}
				else if (!is_array($value) && intval($value) > 0)
				{
					$arValues[$key] = intval($value);
				}
				else
				{
					$arValues[$key] = $arDefault[$key];
				}
			}
			else if ($key == 'ATTACH')
			{
				$arValues[$key] = isset($value)? $value: $arDefault[$key];
			}
			else if (isset($arDefault[$key]))
			{
				$arValues[$key] = $value;
			}
		}

		foreach($arDefault as $key => $value)
		{
			if (!isset($arValues[$key]))
			{
				$arValues[$key] = $value;
			}
		}

		return $arValues;
	}

	public static function GetDefault()
	{
		$arDefault = Array(
			'LIKE' => Array(),
			'FILE_ID' => Array(),
			'URL_ID' => Array(),
			'ATTACH' => Array(),
			'IS_DELETED' => 'N',
			'IS_EDITED' => 'N',
			'CAN_ANSWER' => 'N',
		);

		return $arDefault;
	}
}


class CIMMessageParamAttach
{
	const NORMAL = "#aac337";
	const ATTENTION = "#e8a441";
	const PROBLEM = "#df532d";
	const CHAT = "CHAT";

	private $result = Array();

	function __construct($id = null, $color = null)
	{
		$this->result['ID'] = $id? $id: time();
		$this->result['BLOCKS'] = Array();

		if ($color != self::CHAT)
		{
			if (!$color || !preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $color))
			{
				$color = Bitrix\Im\Color::getRandomColor();
			}
			$this->result['COLOR'] = $color;
		}
	}

	public function AddUser($params)
	{
		$add = Array();
		if (!isset($params['NAME']) || strlen(trim($params['NAME'])) <= 0)
			return false;

		$add['NAME'] = htmlspecialcharsbx(trim($params['NAME']));

		if (isset($params['NETWORK_ID']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(substr($params['NETWORK_ID'], 0,1)).intval(substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0)
		{
			$add['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0)
		{
			$add['CHAT_ID'] = intval($params['CHAT_ID']);
		}
		else if (isset($params['LINK']) && preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			$add['LINK'] = htmlspecialcharsbx($params['LINK']);
		}

		if (isset($params['AVATAR']) && preg_match('#^(?:/|https?://)#', $params['AVATAR']))
		{
			$add['AVATAR'] = htmlspecialcharsbx($params['AVATAR']);
		}
		$add['AVATAR_TYPE'] = isset($params['AVATAR_TYPE']) && $params['AVATAR_TYPE'] == 'CHAT'? 'CHAT': 'USER';

		$this->result['BLOCKS'][]['USER'] = Array($add);

		return true;
	}

	public function AddChat($params)
	{
		$params['AVATAR_TYPE'] = 'CHAT';
		return $this->AddUser($params);
	}

	public function AddLink($params)
	{
		$add = Array();

		if (isset($params['NETWORK_ID']) && isset($params['NAME']))
		{
			$add['NETWORK_ID'] = htmlspecialcharsbx(substr($params['NETWORK_ID'], 0,1)).intval(substr($params['NETWORK_ID'], 1));
		}
		else if (isset($params['USER_ID']) && intval($params['USER_ID']) > 0 && isset($params['NAME']))
		{
			$add['USER_ID'] = intval($params['USER_ID']);
		}
		else if (isset($params['CHAT_ID']) && intval($params['CHAT_ID']) > 0 && isset($params['NAME']))
		{
			$add['CHAT_ID'] = intval($params['CHAT_ID']);
		}
		else if (!isset($params['LINK']) || isset($params['LINK']) && !preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			return false;
		}

		if (isset($params['NAME']))
		{
			$add['NAME'] = htmlspecialcharsbx(trim($params['NAME']));
		}
		if (isset($params['LINK']))
		{
			$add['LINK'] = htmlspecialcharsbx($params['LINK']);
		}

		if (isset($params['DESC']))
		{
			$params['DESC'] = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', trim($params['DESC'])));
			$add['DESC'] = str_replace('#BR#', '<br/>', $params['DESC']);
		}

		if (isset($params['HTML']))
		{
			$sanitizer = new CBXSanitizer();
			$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
			$sanitizer->ApplyHtmlSpecChars(false);

			$add['HTML'] = $sanitizer->SanitizeHtml($params['HTML']);
		}
		else if (isset($params['PREVIEW']) && preg_match('#^(?:/|https?://)#', $params['PREVIEW']))
		{
			$add['PREVIEW'] = htmlspecialcharsbx($params['PREVIEW']);
		}

		$this->result['BLOCKS'][]['LINK'] = Array($add);

		return true;
	}

	public function AddHtml($html)
	{
		if (!isset($html))
			return false;

		$sanitizer = new CBXSanitizer();
		$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
		$sanitizer->ApplyHtmlSpecChars(false);

		$html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);

		$this->result['BLOCKS'][]['HTML'] = $sanitizer->SanitizeHtml($html);

		return true;
	}

	public function AddMessage($message)
	{
		$message = trim($message);
		if (strlen($message) <= 0)
			return false;

		$message = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', $message));
		$message = str_replace('#BR#', '<br/>', $message);

		$this->result['BLOCKS'][]['MESSAGE'] = $message;

		return true;
	}

	public function AddGrid($params)
	{
		$add = Array();

		foreach ($params as $grid)
		{
			$result = Array();

			if (!isset($grid['NAME']) || strlen(trim($grid['NAME'])) <= 0)
				continue;

			if (!isset($grid['VALUE']) || strlen(trim($grid['VALUE'])) <= 0)
				continue;

			if (isset($grid['DISPLAY']) && in_array($grid['DISPLAY'], Array('BLOCK', 'ROW', 'LINE', 'COLUMN')))
			{
				if ($grid['DISPLAY'] == 'COLUMN')
				{
					$grid['DISPLAY'] = 'ROW';
				}
				$result['DISPLAY'] = $grid['DISPLAY'];
			}
			else
			{
				$result['DISPLAY'] = 'BLOCK';
			}

			$result['NAME'] = htmlspecialcharsbx(trim($grid['NAME']));

			$grid['VALUE'] = htmlspecialcharsbx(str_replace(Array('<br>', '<br/>', '<br />'), '#BR#', trim($grid['VALUE'])));
			$result['VALUE'] = str_replace('#BR#', '<br/>', $grid['VALUE']);

			if (preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $grid['COLOR']))
			{
				$result['COLOR'] = $grid['COLOR'];
			}
			if (isset($grid['WIDTH']) && intval($grid['WIDTH']) > 0)
			{
				$result['WIDTH'] = intval($grid['WIDTH']);
			}
			if (isset($grid['USER_ID']) && intval($grid['USER_ID']) > 0)
			{
				$result['USER_ID'] = intval($grid['USER_ID']);
			}
			if (isset($grid['CHAT_ID']) && intval($grid['CHAT_ID']) > 0)
			{
				$result['CHAT_ID'] = intval($grid['CHAT_ID']);
			}
			if (isset($grid['LINK']) && preg_match('#^(?:/|https?://)#', $grid['LINK']))
			{
				$result['LINK'] = htmlspecialcharsbx($grid['LINK']);
			}

			$add[] = $result;
		}
		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['GRID'] = $add;

		return true;
	}

	public function AddImages($params)
	{
		$add = Array();

		foreach ($params as $images)
		{
			$result = Array();

			if (!isset($images['LINK']) || isset($images['LINK']) && !preg_match('#^(?:/|https?://)#', $images['LINK']))
				continue;

			if (isset($images['NAME']) && strlen(trim($images['NAME'])) > 0)
			{
				$result['NAME'] = htmlspecialcharsbx(trim($images['NAME']));
			}

			$result['LINK'] = htmlspecialcharsbx($images['LINK']);

			if (isset($images['PREVIEW']) && preg_match('#^(?:/|https?://)#', $images['PREVIEW']))
			{
				$result['PREVIEW'] = htmlspecialcharsbx($images['PREVIEW']);
			}

			$add[] = $result;
		}

		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['IMAGE'] = $add;

		return true;
	}

	public function AddFiles($params)
	{
		$add = Array();

		foreach ($params as $files)
		{
			$result = Array();

			if (!isset($files['LINK']) || isset($files['LINK']) && !preg_match('#^(?:/|https?://)#', $files['LINK']))
				continue;

			$result['LINK'] = htmlspecialcharsbx($files['LINK']);

			if (isset($files['NAME']) && strlen(trim($files['NAME'])) > 0)
			{
				$result['NAME'] = htmlspecialcharsbx(trim($files['NAME']));
			}

			if (isset($files['SIZE']) && intval($files['SIZE']) > 0)
			{
				$result['SIZE'] = intval($files['SIZE']);
			}

			$add[] = $result;
		}

		if (empty($add))
			return false;

		$this->result['BLOCKS'][]['FILE'] = $add;

		return true;
	}

	public function AddDelimiter($params)
	{
		$add = Array();

		$add['SIZE'] = isset($params['SIZE'])? intval($params['SIZE']): 0;
		if ($add['SIZE'] <= 0)
		{
			$add['SIZE'] = 200;
		}

		if (preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/D', $params['COLOR']))
		{
			$add['COLOR'] = $params['COLOR'];
		}
		else
		{
			$add['COLOR'] = '#c6c6c6';
		}

		$this->result['BLOCKS'][]['DELIMITER'] = $add;
	}

	public function IsEmpty()
	{
		return empty($this->result['BLOCKS']);
	}
	public function GetId()
	{
		return $this->result['ID'];
	}

	public function GetArray()
	{
		return $this->result;
	}

	public function GetJSON()
	{
		return \Bitrix\Main\Web\Json::encode($this->result);
	}
}

class CIMMessageLink
{
	private $result = false;
	private $message = "";
	private $attach = Array();
	private $urlId = Array();

	public function prepareInsert($text)
	{
		$parser = new CTextParser();
		$parser->allow = array("ANCHOR" => "Y", "NL2BR" => "N", "HTML" => "Y", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "N", "VIDEO" => "N", "TABLE" => "N", "ALIGN" => "N");
		$this->message = $parser->convertText($text);

		$this->message = preg_replace_callback('#<a\s+href="(?P<URL>[^"]+?)".+?>(?P<TEXT>.+?)</a>#', array($this, "replaceLinkToObject"), $this->message);

		return $this->result();
	}
	
	private function replaceLinkToObject($params)
	{
		$params['URL'] = htmlspecialcharsback($params['URL']);

		if ($linkParam = UrlPreview\UrlPreview::getMetadataAndHtmlByUrl($params['URL']))
		{
			$this->attach[$linkParam['ID']] = self::formatAttach($linkParam);
			$this->urlId[$linkParam['ID']] = $linkParam['ID'];
		}

		$this->result = true;

		return '[URL='.$params['URL'].']'.$params['TEXT'].'[/URL]';
	}
	
	public static function prepareShow($arMessages, $params)
	{
		$arUrl = Array();
		foreach ($params as $messageId => $param)
		{
			if (isset($param['URL_ID']))
			{
				foreach ($param['URL_ID'] as $urlId)
				{
					$urlId = intval($urlId);
					if ($urlId > 0)
					{
						$arUrl[$urlId] = $urlId;
					}
				}
			}
		}

		if (!empty($arUrl))
		{
			$arAttachUrl = self::getAttachments($arUrl, true);
			if (!empty($arAttachUrl))
			{
				foreach ($params as $messageId => $param)
				{
					foreach ($param['URL_ID'] as $urlId)
					{
						if (isset($arAttachUrl[$urlId]))
						{
							$arMessages[$messageId]['params']['ATTACH'][] = $arAttachUrl[$urlId];
						}
					}
				}
			}
		}

		return $arMessages;
	}

	public static function getAttachments($id, $typeArray = false)
	{
		$attachArray = Array();

		if (is_array($id))
		{
			foreach ($id as $key => $value)
			{
				$id[$key] = intval($value);
			}
		}
		else
		{
			$id = array(intval($id));
		}

		$params = UrlPreview\UrlPreview::getMetadataAndHtmlByIds($id);
		foreach ($params as $id => $linkParam)
		{
			if ($attach = self::formatAttach($linkParam))
			{
				$attachArray[$id] = $typeArray? $attach->GetArray(): $attach;
			}
		}

		return $attachArray;
	}

	public static function formatAttach($linkParam)
	{
		$attach = null;
		if ($linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_STATIC)
		{
			if (intval($linkParam['IMAGE_ID']) > 0)
			{
				$image = CFile::ResizeImageGet(
					$linkParam['IMAGE_ID'],
					array('width' => 450, 'height' => 120),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false,
					false,
					true
				);
				$linkParam['IMAGE_ID'] = empty($image['src'])? '': $image['src'];
			}
			else if (strlen($linkParam['IMAGE']) > 0)
			{
				$linkParam['IMAGE_ID'] = $linkParam['IMAGE'];
			}
			else
			{
				$linkParam['IMAGE_ID'] = '';
			}

			$attach = new CIMMessageParamAttach($linkParam['ID'], CIMMessageParamAttach::CHAT);
			$attach->AddLink(Array(
				"NAME" => $linkParam['TITLE'],
				"DESC" => $linkParam['DESCRIPTION'],
				"LINK" => $linkParam['URL'],
				"PREVIEW" => $linkParam['IMAGE_ID']
			));
		}
		else if (false && $linkParam['TYPE'] == UrlPreview\UrlMetadataTable::TYPE_DYNAMIC) // TODO think about dynamic content, CSS & JS issue
		{
			$attach = new CIMMessageParamAttach($linkParam['ID'], CIMMessageParamAttach::CHAT);
			$attach->AddHtml($linkParam['HTML']);
		}
		return $attach;
	}

	public function result()
	{
		return Array(
			'RESULT' => $this->result, 
			'MESSAGE' => $this->message, 
			'URL_ID' => array_values($this->urlId),
			'ATTACH' => array_values($this->attach),
		);
	}
}

?>