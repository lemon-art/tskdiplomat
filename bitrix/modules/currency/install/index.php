<?
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\SiteTable;

Loc::loadMessages(__FILE__);

class currency extends CModule
{
	var $MODULE_ID = 'currency';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $errors = false;

	function currency()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (!empty($arModuleVersion['VERSION']))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = CURRENCY_VERSION;
			$this->MODULE_VERSION_DATE = CURRENCY_VERSION_DATE;
		}

		$this->MODULE_NAME = Loc::getMessage("CURRENCY_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("CURRENCY_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();
		$GLOBALS["errors"] = $this->errors;

		$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = (int)$step;
		if ($step<2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep1.php");
		}
		elseif ($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep2.php");
		}
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;

		$this->errors = false;

		if (!$DB->Query("SELECT COUNT(CURRENCY) FROM b_catalog_currency", true)):
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/install.sql");
		endif;

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		ModuleManager::registerModule('currency');
		self::installCurrencies();

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		if (Loader::includeModule('currency'))
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/uninstall.sql");
			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode('', $this->errors));
				return false;
			}
		}
		CAgent::RemoveModuleAgents('currency');
		ModuleManager::unRegisterModule('currency');

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/currency", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/currency/install/tools", $_SERVER['DOCUMENT_ROOT']."/bitrix/tools", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/currency/");
		DeleteDirFilesEx("/bitrix/images/currency/");
		DeleteDirFilesEx("/bitrix/js/currency/");
		DeleteDirFilesEx("/bitrix/tools/currency/"); // scripts

		return true;
	}

	protected function installCurrencies()
	{
		if (!Loader::includeModule('currency'))
			return;

		$bitrix24Path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bitrix24/';
		$bitrix24 = file_exists($bitrix24Path) && is_dir($bitrix24Path);
		unset($bitrix24Path);

		$currencyIterator = \Bitrix\Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY'),
			'limit' => 1
		));
		$currency = $currencyIterator->fetch();
		if (!empty($currency))
			return;

		$languageID = '';
		$siteIterator = SiteTable::getList(array(
			'select' => array('LID', 'LANGUAGE_ID'),
			'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
		));
		if ($site = $siteIterator->fetch())
			$languageID = (string)$site['LANGUAGE_ID'];
		unset($site, $siteIterator);

		if ($languageID == '')
			$languageID = 'en';

		$currencyList = array();
		$currencySetID = '';
		switch ($languageID)
		{
			case 'ua':
			case 'de':
			case 'en':
			case 'la':
			case 'tc':
			case 'sc':
			case 'in':
				$currencySetID = $languageID;
				break;
			case 'ru':
				if (!$bitrix24)
				{
					$languageIterator = LanguageTable::getList(array(
						'select' => array('ID'),
						'filter' => array('=ID' => 'kz', '=ACTIVE' => 'Y')
					));
					if ($existLanguage = $languageIterator->fetch())
						$currencySetID = $existLanguage['ID'];

					if ($currencySetID == '')
					{
						$languageIterator = LanguageTable::getList(array(
							'select' => array('ID'),
							'filter' => array('=ID' => 'ua', '=ACTIVE' => 'Y')
						));
						if ($existLanguage = $languageIterator->fetch())
							$currencySetID = $existLanguage['ID'];
					}
					unset($existLanguage, $languageIterator);
				}
				if ($currencySetID == '')
					$currencySetID = $languageID;
				break;
			default:
				$currencySetID = 'en';
				break;
		}
		$datetimeEntity = new Main\DB\SqlExpression(Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction());
		switch ($currencySetID)
		{
			case 'kz':
				$addCurrency = array(
					array('CURRENCY' => 'KZT', 'NUMCODE' => '398', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 4.40, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.40),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 283.17, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 283.17),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 310.78, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 310.78)
				);
				break;
			case 'ua':
				$addCurrency = array(
					array('CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 3.61, 'AMOUNT_CNT' => 10, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.361),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 2322.93, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 23.2293),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 2548.19, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 25.4819)
				);
				break;
			case 'ru':
				$addCurrency = array(
					array('CURRENCY' => 'RUB', 'NUMCODE' => '643', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 64.36, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 64.36),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 70.60, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 70.60),
					array('CURRENCY' => 'UAH', 'NUMCODE' => '980', 'AMOUNT' => 27.69, 'AMOUNT_CNT' => 10, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 2.769),
					array('CURRENCY' => 'BYR', 'NUMCODE' => '974', 'AMOUNT' => 36.90, 'AMOUNT_CNT' => 10000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.00369)
				);
				break;
			case 'de':
				$addCurrency = array(
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 0.91, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.91),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 14.35, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.1435),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 23.21, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2321),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 13.97, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01397)
				);
				break;
			case 'tc':
			case 'sc':
				$addCurrency = array(
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 6.36, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.36),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 6.97, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 6.97),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1.61, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.61),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 9.74, 'AMOUNT_CNT' => 100, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.09737)
				);
				break;
			case 'in':
				$addCurrency = array(
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 65.31, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 65.31),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 71.56, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 71.56),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 10.27, 'AMOUNT_CNT' => 1, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 10.27),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 16.56, 'AMOUNT_CNT' => 1, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 16.56)
				);
				break;
			case 'br':
				$addCurrency = array(
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 3.90, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 3.90),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 4.29, 'AMOUNT_CNT' => 1, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 4.29),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 61.44, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.6144),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 5.99, 'AMOUNT_CNT' => 100, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.0599),
				);
				break;
			default:
			case 'en':
			case 'la':
				$addCurrency = array(
					array('CURRENCY' => 'USD', 'NUMCODE' => '840', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100, 'BASE' => 'Y', 'CURRENT_BASE_RATE' => 1),
					array('CURRENCY' => 'EUR', 'NUMCODE' => '978', 'AMOUNT' => 1.10, 'AMOUNT_CNT' => 1, 'SORT' => 200, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 1.10),
					array('CURRENCY' => 'CNY', 'NUMCODE' => '156', 'AMOUNT' => 15.73, 'AMOUNT_CNT' => 100, 'SORT' => 300, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.1573),
					array('CURRENCY' => 'BRL', 'NUMCODE' => '986', 'AMOUNT' => 25.35, 'AMOUNT_CNT' => 100, 'SORT' => 400, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.2535),
					array('CURRENCY' => 'INR', 'NUMCODE' => '356', 'AMOUNT' => 15.31, 'AMOUNT_CNT' => 1000, 'SORT' => 500, 'BASE' => 'N', 'CURRENT_BASE_RATE' => 0.01531)
				);
				break;
		}
		foreach ($addCurrency as &$fields)
		{
			$fields['CREATED_BY'] = null;
			$fields['MODIFIED_BY'] = null;
			$fields['DATE_CREATE'] = $datetimeEntity;
			$fields['DATE_UPDATE'] = $datetimeEntity;
			$currencyResult = \Bitrix\Currency\CurrencyTable::add($fields);
			if ($currencyResult->isSuccess())
				$currencyList[] = $fields['CURRENCY'];
		}
		unset($currencyResult, $fields);

		if (!empty($currencyList))
		{
			Option::set('currency', 'installed_currencies', implode(',', $currencyList), '');
			$languageIterator = LanguageTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($existLanguage = $languageIterator->fetch())
			{
				$messList = Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/currency/install_lang.php', $existLanguage['ID']);
				foreach($currencyList as &$oneCurrency)
				{
					$fields = array(
						'LID' => $existLanguage['ID'],
						'CURRENCY' => $oneCurrency,
						'THOUSANDS_SEP' => false,
						'DECIMALS' => 2,
						'HIDE_ZERO' => 'Y',
						'FORMAT_STRING' => $messList['CUR_INSTALL_'.$oneCurrency.'_FORMAT_STRING'],
						'FULL_NAME' => $messList['CUR_INSTALL_'.$oneCurrency.'_FULL_NAME'],
						'DEC_POINT' => $messList['CUR_INSTALL_'.$oneCurrency.'_DEC_POINT'],
						'THOUSANDS_VARIANT' => $messList['CUR_INSTALL_'.$oneCurrency.'_THOUSANDS_SEP'],
						'CREATED_BY' => null,
						'MODIFIED_BY' => null,
						'DATE_CREATE' => $datetimeEntity,
						'TIMESTAMP_X' => $datetimeEntity
					);
					$resultCurrencyLang = \Bitrix\Currency\CurrencyLangTable::add($fields);
					unset($resultCurrencyLang);
				}
				unset($oneCurrency, $messList);
			}
			unset($existLanguage, $languageIterator);
			if (!$bitrix24)
			{
				$checkDate = Main\Type\DateTime::createFromTimestamp(strtotime('tomorrow 00:01:00'));;
				CAgent::AddAgent('\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();', 'currency', 'Y', 86400, '', 'Y', $checkDate->toString(), 100, false, true);
				unset($checkDate);
			}
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		}
		unset($datetimeEntity);
	}
}