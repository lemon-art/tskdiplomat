<?php

//define('NO_KEEP_STATISTIC', 'Y');
//define('NO_AGENT_STATISTIC','Y');
//define('NO_AGENT_CHECK', true);
//define('DisableEventsCheck', true);

define('NOT_CHECK_PERMISSIONS', true);
//define('BX_SECURITY_SESSION_READONLY', true);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

$context = Bitrix\Main\Application::getInstance()->getContext();

$request  = $context->getRequest();
$response = $context->getResponse();

try
{
	$hostname = null;
	$hostname = $request->getPostList()->getRaw('hostname');

	if (empty($hostname))
		throw new Bitrix\Main\ArgumentException();

	$message = null;
	$message = Bitrix\Main\Web\Json::decode($request->getPostList()->getRaw('message'));

	if (empty($message))
		throw new Bitrix\Main\ArgumentException();

	if (empty($message['rcpt_to']) || !is_array($message['rcpt_to']))
		throw new Bitrix\Main\ArgumentException();

	$message['files'] = array();
	if (!empty($message['attachments']) && is_array($message['attachments']))
	{
		$emptyFile = array(
			'name'     => '',
			'type'     => '',
			'tmp_name' => '',
			'error'    => UPLOAD_ERR_NO_FILE,
			'size'     => 0
		);

		$imageExts = array(
			'image/bmp'  => array('.bmp'),
			'image/gif'  => array('.gif'),
			'image/jpeg' => array('.jpeg', '.jpg', '.jpe'),
			'image/png'  => array('.png')
		);
		$jpegTypes = array('image/pjpeg', 'image/jpeg', 'image/jpg', 'image/jpe');

		foreach ($message['attachments'] as &$item)
		{
			$itemId = $item['uniqueId'];
			$fileId = md5($item['checksum'].$item['length']);

			$item['fileName'] = trim(trim(trim($item['fileName']), '.')) ?: $fileId;

			if ($item['contentType'])
			{
				if (in_array($item['contentType'], $jpegTypes))
					$item['contentType'] = 'image/jpeg';

				if (is_set($imageExts, $item['contentType']))
				{
					$extPos = strrpos($item['fileName'], '.');
					$ext    = substr($item['fileName'], $extPos);

					if ($extPos === false || !in_array($ext, $imageExts[$item['contentType']]))
						$item['fileName'] .= $imageExts[$item['contentType']][0];
				}
			}

			$message['files'][$fileId] = array_merge(
				empty($_FILES[$itemId]) ? $emptyFile : $_FILES[$itemId],
				array(
					'name' => $item['fileName'],
					'type' => $item['contentType']
				)
			);

			$item['uniqueId'] = $fileId;
		}
		unset($item);
	}

	CModule::includeModule('mail');

	$success = false;
	foreach ($message['rcpt_to'] as $recipient)
	{
		if (!empty($recipient['user']) && !empty($recipient['host']))
		{
			if (preg_match('/^no-?reply$/i', $recipient['user']))
				continue;

			if (strtolower($hostname) != strtolower($recipient['host']))
				continue;

			$to = sprintf('%s@%s', $recipient['user'], $recipient['host']);
			$success |= (bool) Bitrix\Mail\User::onEmailReceived($to, $message);
		}
	}

	$response->setStatus($success ? '204 No Content' : '400 Bad Request');
}
catch (Bitrix\Main\ArgumentException $e)
{
	$response->setStatus('400 Bad Request');
}
catch (Exception $e)
{
	$response->setStatus('500 Internal Server Error');
}

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
