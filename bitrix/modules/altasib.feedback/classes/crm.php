<?
/**
 * Company developer: ALTASIB
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * Copyright (c) 2006-2015 ALTASIB
 */

class AltasibFeedbackCRM
{
	function AddLead($arFields)
	{
		$common_crm = COption::GetOptionString('altasib.feedback', 'ALX_COMMON_CRM');

		if($common_crm == 'Y')
		{
			$server = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_SERVER');
			$path = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_PATH');
			$arFields['AUTH'] = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_HASH');
		}
		else
		{
			$server = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_SERVER_'.SITE_ID);
			$path = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_PATH_'.SITE_ID);
			$arFields['AUTH'] = COption::GetOptionString('altasib.feedback', 'ALX_FEEDBACK_HASH_'.SITE_ID);
		}

		return self::MakeRequest($server.$path, $arFields);
	}

	function parse_url_if_valid($url)
	{
		$arUrl = parse_url($url);

		$ret = null;

		if (!array_key_exists("scheme", $arUrl)
			|| !in_array($arUrl["scheme"], array("http", "https")))
				$arUrl["scheme"] = "http";

		if (array_key_exists("host", $arUrl) && !empty($arUrl["host"]))
			$ret = sprintf("%s://%s%s", $arUrl["scheme"], $arUrl["host"], $arUrl["path"]);

		else if (preg_match("/^\w+\.[\w\.]+(\/.*)?$/", $arUrl["path"]))
			$ret = sprintf("%s://%s", $arUrl["scheme"], $arUrl["path"]);

		if ($ret && empty($ret["query"]))
			$ret .= sprintf("?%s", $arUrl["query"]);

		return $ret;
	}

	function MakeRequest($url, $arParams)
	{
		global $APPLICATION;

		foreach($arParams as $key => $value)
			$arParams[$key] = $APPLICATION->ConvertCharset($value, LANG_CHARSET, 'UTF-8');

		if (!empty($url))
		{
			$url = self::parse_url_if_valid($url);

			if (!$url)
			{
				$result['error']=301;
			}
			else
			{
				$obHTTP = new CHTTP();

				$result = $obHTTP->Post($url, $arParams);

				$result = $APPLICATION->ConvertCharset($result, 'UTF-8', LANG_CHARSET);
				$result = CUtil::JsObjectToPhp($result);

				if($obHTTP->status==301 || $obHTTP->status==0)
				{
					$result=array();
					$result['error']=301;
				}
			}
		}
		else
			$result['error']=301;

		if (!is_array($result))
		{
			$result['error']=301;
		}
		return $result;
	}
}
?>