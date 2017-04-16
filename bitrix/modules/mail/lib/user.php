<?php

namespace Bitrix\Mail;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

class User
{

	/**
	 * Creates mail user
	 *
	 * @param array $fields User fields.
	 * @return int|false
	 */
	public static function create($fields)
	{
		$user = new \CUser;

		$userFields = array(
			'LOGIN'            => $fields["EMAIL"],
			'EMAIL'            => $fields["EMAIL"],
			'NAME'             => (!empty($fields["NAME"]) ? $fields["NAME"] : ''),
			'LAST_NAME'        => (!empty($fields["LAST_NAME"]) ? $fields["LAST_NAME"] : ''),
			'EXTERNAL_AUTH_ID' => 'email'
		);

		$mailGroup = self::getMailUserGroup();
		if (!empty($mailGroup))
		{
			$userFields["GROUP_ID"] = $mailGroup;
		}
		$result = $user->add($userFields);

		return $result;
	}

	/**
	 * Runs user login
	 *
	 * @return void
	 */
	public static function login()
	{
		$eventManager = Main\EventManager::getInstance();
		$handler = $eventManager->addEventHandlerCompatible('main', 'OnUserLoginExternal', array('\Bitrix\Mail\User', 'onLoginExternal'));

		global $USER;
		$USER->login(null, null, 'Y');

		$eventManager->removeEventHandler('main', 'OnUserLoginExternal', $handler);
	}

	/**
	 * Returns mail user ID
	 *
	 * @param array &$params Auth params.
	 * @return int|false
	 */
	public static function onLoginExternal(&$params)
	{
		$context = Main\Application::getInstance()->getContext();
		$request = $context->getRequest();

		if ($token = $request->get('token') ?: $request->getCookie('MAIL_AUTH_TOKEN'))
		{
			$userRelation = UserRelationsTable::getList(array(
				'select' => array('USER_ID'),
				'filter' => array(
					'=TOKEN'                 => $token,
					'=USER.EXTERNAL_AUTH_ID' => 'email',
					'USER.ACTIVE'            => 'Y'
				)
			))->fetch();

			if ($userRelation)
			{
				$context->getResponse()->addCookie(new Main\Web\Cookie('MAIL_AUTH_TOKEN', $token));

				return $userRelation['USER_ID'];
			}
		}

		return false;
	}

	/**
	 * Returns User-Entity unique email and entry point URL
	 *
	 * @param string $siteId Site ID.
	 * @param int $userId User ID.
	 * @param string $entityType Entity type ID.
	 * @param int $entityId Entity ID.
	 * @param string $entityLink Entity URL.
	 * @param string $backurl Back URL.
	 * @return array|false
	 */
	public static function getReplyTo($siteId, $userId, $entityType, $entityId, $entityLink = null, $backurl = null)
	{
		$filter = array(
			'=SITE_ID'     => $siteId,
			'=USER_ID'     => $userId,
			'=ENTITY_TYPE' => $entityType,
			'=ENTITY_ID'   => $entityId
		);
		$userRelation = UserRelationsTable::getList(array('filter' => $filter))->fetch();

		if (empty($userRelation))
		{
			$filter['=SITE_ID'] = null;
			$userRelation = UserRelationsTable::getList(array('filter' => $filter))->fetch();
		}

		if (empty($userRelation))
		{
			if (empty($entityLink))
				return false;

			$userRelation = array(
				'SITE_ID'     => $siteId,
				'TOKEN'       => base_convert(md5(time().Main\Security\Random::getBytes(6)), 16, 36),
				'USER_ID'     => $userId,
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID'   => $entityId,
				'ENTITY_LINK' => $entityLink,
				'BACKURL'     => $backurl
			);

			if (!UserRelationsTable::add($userRelation)->isSuccess())
				return false;
		}

		$site    = Main\SiteTable::getByPrimary($siteId)->fetch();
		$context = Main\Application::getInstance()->getContext();
		$server  = $context->getServer();
		$request = $context->getRequest();

		$scheme = $request->isHttps() ? 'https' : 'http';
		$domain = $site['SERVER_NAME'] ?: \COption::getOptionString('main', 'server_name', '');
		$port   = in_array($server->getServerPort(), array(80, 443)) ? '' : ':'.$server->getServerPort();
		$path   = ltrim(trim($site['DIR'], '/') . '/pub/entry.php', '/');

		$replyTo = sprintf('rpl%s@%s', $userRelation['TOKEN'], $domain);
		$backUrl = sprintf('%s://%s%s/%s#%s', $scheme, $domain, $port, $path, $userRelation['TOKEN']);

		return array($replyTo, $backUrl);
	}

	/**
	 * Returns Site-User-Entity unique email
	 *
	 * @param string $siteId Site ID.
	 * @param int $userId User ID.
	 * @param string $entityType Entity type ID.
	 * @return array|false
	 */
	public static function getForwardTo($siteId, $userId, $entityType)
	{
		$cache = new \CPHPCache();

		if ($cache->initCache(365*24*3600, sprintf('forward_%s_%s_%s', $siteId, $userId, $entityType), '/mail'))
		{
			$forwardTo = $cache->getVars();
		}
		else
		{
			$userRelation = UserRelationsTable::getList(array(
				'filter' => array(
					'=SITE_ID'     => $siteId,
					'=USER_ID'     => $userId,
					'=ENTITY_TYPE' => $entityType,
					'=ENTITY_ID'   => null
				)
			))->fetch();

			if (empty($userRelation))
			{
				$userRelation = array(
					'SITE_ID'     => $siteId,
					'TOKEN'       => base_convert(md5(time().Main\Security\Random::getBytes(6)), 16, 36),
					'USER_ID'     => $userId,
					'ENTITY_TYPE' => $entityType
				);

				if (!UserRelationsTable::add($userRelation)->isSuccess())
					return false;

				// for dav addressbook modification label
				$user = new \CUser;
				$user->update($userId, array());
			}

			$site   = Main\SiteTable::getByPrimary($siteId)->fetch();
			$domain = $site['SERVER_NAME'] ?: \COption::getOptionString('main', 'server_name', '');

			$forwardTo = sprintf('fwd%s@%s', $userRelation['TOKEN'], $domain);

			$cache->startDataCache();
			$cache->endDataCache($forwardTo);
		}

		return array($forwardTo);
	}

	/**
	 * Sends email related events
	 *
	 * @param string $to Recipient email.
	 * @param array $message Message.
	 * @return bool
	 */
	public static function onEmailReceived($to, $message)
	{
		if (preg_match('/^(?<type>rpl|fwd)(?<token>[a-z0-9]+)@(?<domain>.+)/i', $to, $matches))
		{
			$type  = $matches['type'];
			$token = $matches['token'];

			if ($userRelation = UserRelationsTable::getByPrimary($token)->fetch())
			{
				$message['secret'] = $token;

				switch ($type)
				{
					case 'rpl':
						$eventId = sprintf('onReplyReceived%s', $userRelation['ENTITY_TYPE']);
						$content = Message::parseReply($message);
						break;
					case 'fwd':
						$eventId = sprintf('onForwardReceived%s', $userRelation['ENTITY_TYPE']);
						$content = Message::parseForward($message);
						break;
				}

				if (empty($content) && empty($message['files']))
					return false;

				$event = new Main\Event(
					'mail', $eventId,
					array(
						'site_id'     => $userRelation['SITE_ID'],
						'entity_id'   => $userRelation['ENTITY_ID'],
						'from'        => $userRelation['USER_ID'],
						'subject'     => $message['subject'],
						'content'     => $content,
						'attachments' => $message['files']
					)
				);
				$event->send();

				if ($event->getResults())
					return true;
			}
		}

		return false;
	}

	/**
	 * Returns email users group
	 *
	 * @return array
	 */
	public static function getMailUserGroup()
	{
		$res = array();
		$mailInvitedGroup = Main\Config\Option::get("mail", "mail_invited_group", false);
		if ($mailInvitedGroup)
		{
			$res[] = intval($mailInvitedGroup);
		}
		return $res;
	}

}
