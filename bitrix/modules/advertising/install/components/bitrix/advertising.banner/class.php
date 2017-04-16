<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!\Bitrix\Main\Loader::includeModule('advertising'))
	return;

Loc::loadMessages(__FILE__);

class AdvertisingBanner extends \CBitrixComponent
{
	protected $obCache;
	protected $cache_id;
	protected $cache_path;
	protected $templateCachedData;
	protected $arBanners;
	protected $arVars;
	protected $templateProps;
	protected $templateFiles;
	protected $bannerIndex = 0;

	public function onPrepareComponentParams($params)
	{
		$params["TYPE"] = (isset($params["TYPE"]) ? trim($params["TYPE"]) : "");

		if($params["NOINDEX"] <> "Y")
			$params["NOINDEX"] = "N";

		if ($params["CACHE_TYPE"] == "Y" || ($params["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
			$params["CACHE_TIME"] = intval($params["CACHE_TIME"]);
		else
			$params["CACHE_TIME"] = 0;

		if (isset($params['QUANTITY']) && intval($params['QUANTITY']) > 0)
			$params['QUANTITY'] =  intval($params['QUANTITY']);
		else
			$params['QUANTITY'] = 1;

		$params['BANNER_ID'] = intval($params["BANNER_ID"]);

		return $params;
	}

	protected function setCache()
	{
		global $USER;
		$this->obCache = new CPHPCache;
		$this->cache_id = SITE_ID."|advertising.banner|".serialize($this->arParams)."|".$USER->GetGroups();
		$this->cache_path = "/".SITE_ID.$this->GetRelativePath();
		return $this->obCache->StartDataCache($this->arParams["CACHE_TIME"], $this->cache_id, $this->cache_path);
	}

	protected function loadBanners()
	{
		global $APPLICATION;

		$this->arBanners = CAdvBanner::GetRandomArray($this->arParams["TYPE"], $this->arParams['QUANTITY']);
		$this->arResult["BANNERS"] = array();
		if (is_array($this->arBanners) && count($this->arBanners) > 0)
		{
			foreach ($this->arBanners as $key => $banner)
			{
				if ($banner["AD_TYPE"] == 'template')
				{
					$templateProps = unserialize($banner["TEMPLATE"]);
					$templateFiles = unserialize($banner["TEMPLATE_FILES"]);

					if (empty($this->arResult['SIZE']))
					{
						$this->templateFiles = $templateFiles;
						$this->arResult['SIZE'] = $this->getBannerSize();
					}
					if (count($templateProps['PROPS']) > 1)
					{
						if (count($this->arResult["BANNERS"]) == 0)
						{
							foreach ($templateProps['PROPS'] as $k => $v)
							{
								ob_start();

								$APPLICATION->IncludeComponent(
									'bitrix:advertising.banner.view',
									$templateProps['NAME'],
									array(
										'PROPS' => $v,
										'FILES'	=> $templateFiles[$k],
										'EXT_MODE' => $templateProps['MODE'],
										'INDEX' => $this->bannerIndex,
										'HEIGHT' => $this->arParams['HEIGHT'],
										'CASUAL_PROPERTIES' => array(
											'TYPE' => $banner['AD_TYPE']
										)
									),
									null,
									array('HIDE_ICONS' => 'Y')
								);

								$strReturn = CAdvBanner::PrepareHTML(ob_get_contents(), $banner);
								$strReturn = CAdvBanner::ReplaceURL($strReturn, $banner);
								ob_end_clean();

								$this->arResult["BANNERS"][] = $strReturn;
								$this->bannerIndex++;
							}

							$this->arResult["BANNERS_PROPERTIES"][$key] = $banner;
							break;
						}
						else
							continue;
					}
					else
					{
						ob_start();

						$APPLICATION->IncludeComponent(
							'bitrix:advertising.banner.view',
							$templateProps['NAME'],
							array(
								'PROPS' => $templateProps['PROPS'][0],
								'FILES'	=> $templateFiles[0],
								'EXT_MODE' => $templateProps['MODE'],
								'INDEX' => $this->bannerIndex,
								'HEIGHT' => $this->arParams['HEIGHT'],
								'CASUAL_PROPERTIES' => array(
									'TYPE' => $banner['AD_TYPE']
								)
							),
							null,
							array('HIDE_ICONS' => 'Y')
						);

						$strReturn = CAdvBanner::PrepareHTML(ob_get_contents(), $banner);
						$strReturn = CAdvBanner::ReplaceURL($strReturn, $banner);
						ob_end_clean();
						$this->arResult["BANNERS"][$key] = $strReturn;
						$this->arResult["BANNERS_PROPERTIES"][$key] = $banner;
						$this->bannerIndex++;
					}
				}
				else
				{
					$templateExists = false;
					if (strlen($this->arParams['DEFAULT_TEMPLATE']) > 0)
					{
						$arTemplates = CComponentUtil::GetTemplatesList('bitrix:advertising.banner.view');
						if (is_array($arTemplates) && !empty($arTemplates))
						{
							foreach ($arTemplates as $template)
							{
								if ($this->arParams['DEFAULT_TEMPLATE'] == $template['NAME'])
								{
									$templateExists = true;
									break;
								}
							}
						}
					}

					if ($banner["AD_TYPE"] == 'image' && $templateExists)
					{
						$strReturn = CAdvBanner::GetHTML($banner, ($this->arParams["NOINDEX"] == "Y"));
						ob_start();

						$APPLICATION->IncludeComponent(
							'bitrix:advertising.banner.view',
							$this->arParams['DEFAULT_TEMPLATE'],
							array(
								'PROPS' => array(),
								'FILES'	=> array(),
								'EXT_MODE' => 'N',
								'HEIGHT' => $this->arParams['HEIGHT'],
								'CASUAL_PROPERTIES' => array(
									'TYPE' => $banner['AD_TYPE'],
									'IMG' => $banner['IMAGE_ID'],
									'ALT' => $banner['IMAGE_ALT'],
									'URL' => $banner['URL'],
									'URL_TARGET' => $banner['URL_TARGET']
								)
							),
							null,
							array('HIDE_ICONS' => 'Y')
						);

						$strReturn = ob_get_contents();
						ob_end_clean();
					}
					else
						$strReturn = CAdvBanner::GetHTML($banner, ($this->arParams["NOINDEX"] == "Y"));

					$this->arResult["BANNERS"][$key] = $strReturn;
					$this->arResult["BANNERS_PROPERTIES"][$key] = $banner;
					$this->bannerIndex++;
				}
			}
		}
	}

	protected function loadPreview()
	{
		global $APPLICATION;

		if ($banner = CAdvBanner::GetByID($this->arParams["BANNER_ID"])->Fetch())
		{
			if ($banner["AD_TYPE"] == 'template')
			{
				$this->templateProps = unserialize($banner["TEMPLATE"]);
				$this->templateFiles = unserialize($banner["TEMPLATE_FILES"]);

				foreach ($this->templateProps['PROPS'] as $k => $v)
				{
					ob_start();

					$APPLICATION->IncludeComponent(
						'bitrix:advertising.banner.view',
						$this->templateProps['NAME'],
						array(
							'PROPS' => $v,
							'FILES'	=> $this->templateFiles[$k],
							'EXT_MODE' => $this->templateProps['MODE'],
							'INDEX'	=> $this->bannerIndex,
							'PREVIEW' => $this->arParams['PREVIEW'],
							'CASUAL_PROPERTIES' => array(
								'TYPE' => $banner['AD_TYPE']
							)
						),
						null,
						array('HIDE_ICONS' => 'Y')
					);

					$strReturn = CAdvBanner::PrepareHTML(ob_get_contents(), $banner);
					ob_end_clean();

					$this->arResult["BANNERS"][] = $strReturn;
					$this->bannerIndex++;
				}
				$this->arResult["BANNERS_PROPERTIES"][] = $banner;
			}

			$this->arResult['SIZE'] = $this->getBannerSize();
		}
		else
		{
			$this->arResult = array();
		}
	}

	protected function getBannerSize()
	{
		if (is_array($this->templateFiles))
		{
			foreach ($this->templateFiles as $tfk => $tfv)
			{
				foreach ($tfv as $name => $id)
				{
					if ($id !== 'null')
					{
						$file = CFile::GetFileArray($id);
						return array('WIDTH' => $file['WIDTH'], 'HEIGHT' => $file['HEIGHT']);
					}
				}
			}
		}
		else
			return array();
	}

	public function executeComponent()
	{
		global $USER;
		global $APPLICATION;

		$this->arResult = Array(
			"ID" => randString(5),
			"BANNER" => "",
			"BANNER_PROPERTIES" => Array(),
		);
		if ($this->setCache())
		{
			if ($this->arParams['PREVIEW'] == 'Y')
				$this->loadPreview();
			else
				$this->loadBanners();

			if (is_array($this->arResult['BANNERS']))
			{
				foreach ($this->arResult['BANNERS'] as $bk => $bv)
				{
					if (isset($this->arResult['BANNER'][0]))
						$this->arResult['BANNER'] .= '<br />';
					$this->arResult['BANNER'] .= $bv;
				}
			}

			$this->arResult['BANNER_PROPERTIES'] = (count($this->arResult['BANNERS_PROPERTIES']) > 0) ? $this->arResult['BANNERS_PROPERTIES'][0] : array();

			$this->includeComponentTemplate();

			$this->templateCachedData = $this->GetTemplateCachedData();

			$this->obCache->EndDataCache(
				Array(
					"arResult" => $this->arResult,
					"templateCachedData" => $this->templateCachedData
				)
			);
		}
		else
		{
			$this->arVars = $this->obCache->GetVars();
			$this->arResult = $this->arVars["arResult"];
			CBitrixComponentTemplate::ApplyCachedData($this->arVars["templateCachedData"]);
		}

		if ($this->arParams['PREVIEW'] != 'Y')
		{
			if (is_array($this->arResult['BANNERS_PROPERTIES']) && !empty($this->arResult['BANNERS_PROPERTIES']))
			{
				foreach ($this->arResult['BANNERS_PROPERTIES'] as $banner)
				{
					CAdvBanner::FixShow($banner);

					if ($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas())
					{
						if(($arIcons = CAdvBanner::GetEditIcons($banner, $this->arParams["TYPE"], $this->getIncludeAreaIcons())) !== false)
							$this->AddIncludeAreaIcons($arIcons);
					}
				}
			}
		}
	}
}