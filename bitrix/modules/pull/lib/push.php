<?php
namespace Bitrix\Pull;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class PushTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> DEVICE_TYPE string(50) optional
 * <li> APP_ID string(50) optional
 * <li> UNIQUE_HASH string(50) optional
 * <li> DEVICE_ID string(255) optional
 * <li> DEVICE_NAME string(50) optional
 * <li> DEVICE_TOKEN string(255) mandatory
 * <li> DATE_CREATE datetime mandatory
 * <li> DATE_AUTH datetime optional
 * <li> USER reference to {@link \Bitrix\User\UserTable}
 * </ul>
 *
 * @package Bitrix\Pull
 **/

class PushTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_pull_push';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PUSH_ENTITY_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('PUSH_ENTITY_USER_ID_FIELD'),
			),
			'DEVICE_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeviceType'),
				'title' => Loc::getMessage('PUSH_ENTITY_DEVICE_TYPE_FIELD'),
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAppId'),
				'title' => Loc::getMessage('PUSH_ENTITY_APP_ID_FIELD'),
			),
			'UNIQUE_HASH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateUniqueHash'),
				'title' => Loc::getMessage('PUSH_ENTITY_UNIQUE_HASH_FIELD'),
			),
			'DEVICE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeviceId'),
				'title' => Loc::getMessage('PUSH_ENTITY_DEVICE_ID_FIELD'),
			),
			'DEVICE_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeviceName'),
				'title' => Loc::getMessage('PUSH_ENTITY_DEVICE_NAME_FIELD'),
			),
			'DEVICE_TOKEN' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateDeviceToken'),
				'title' => Loc::getMessage('PUSH_ENTITY_DEVICE_TOKEN_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('PUSH_ENTITY_DATE_CREATE_FIELD'),
			),
			'DATE_AUTH' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('PUSH_ENTITY_DATE_AUTH_FIELD'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\User\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for DEVICE_TYPE field.
	 *
	 * @return array
	 */
	public static function validateDeviceType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateAppId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for UNIQUE_HASH field.
	 *
	 * @return array
	 */
	public static function validateUniqueHash()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for DEVICE_ID field.
	 *
	 * @return array
	 */
	public static function validateDeviceId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for DEVICE_NAME field.
	 *
	 * @return array
	 */
	public static function validateDeviceName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for DEVICE_TOKEN field.
	 *
	 * @return array
	 */
	public static function validateDeviceToken()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}