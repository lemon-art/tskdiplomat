<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$ALX = "FID".$arParams["FORM_ID"];

if($arParams['ALX_CHECK_NAME_LINK']=='Y')
{
	if($_SERVER["REQUEST_METHOD"]=="POST")
	{
		if($_POST["OPEN_POPUP_".$ALX] || $_POST["FEEDBACK_FORM_".$ALX])
		{
			CUtil::JSPostUnescape();

			$APPLICATION->RestartBuffer();

			$arResult["FANCYBOX_".$ALX]='Y';

			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
		}
		elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return;
		}
	}
}

if(is_array($arParams["PROPERTY_FIELDS_REQUIRED"]))
{
	foreach($arParams["PROPERTY_FIELDS_REQUIRED"] as $k => $v)
		$arParams["PROPERTY_FIELDS_REQUIRED"][$k] = $v."_".$ALX;
}

$arResult["FORM_ERRORS"] = Array();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IB_MODULE_NOT_INSTALLED"));
	return;
}

if(!CModule::IncludeModule("altasib.feedback"))
{
	ShowError(GetMessage("ALX_FEEDBACK_NOT_INSTALLED"));
	return;
}
$res = CIBlock::GetProperties($arParams["IBLOCK_ID"],
	array(),
	array("PROPERTY_TYPE" => "F")
);

while($res_arr = $res->Fetch())
{
	$arTypeFile[$res_arr["CODE"]]["FILE_TYPE"] = $res_arr["FILE_TYPE"];
	$arTypeFile[$res_arr["CODE"]]["NAME"] = $res_arr["NAME"];
}

$codeFileFields = count($_POST["codeFileFields"]);

if(is_array($_FILES["myFile"]["name"]))
{
	foreach($_FILES["myFile"]["name"] as $k => $value)
	{
		$codeID = trim(htmlspecialcharsEx($_POST["codeFileFields"][$k]));
		$code = str_replace("_".$ALX, "", trim(htmlspecialcharsEx($_POST["codeFileFields"][$k])));

		$arParamTypeFile = array();
		$arParamTypeFileTrim = array();
		if(!empty($arTypeFile[$code]["FILE_TYPE"]))
		{
			$arParamTypeFile = explode(",", $arTypeFile[$code]["FILE_TYPE"]);
			foreach($arParamTypeFile as $v)
				$arParamTypeFileTrim[] = trim($v);
		}

		$params = Array(
			"max_len" => "200",
			"change_case" => "L",
			"replace_space" => "_",
			"replace_other" => ".",
			"delete_repeat_replace" => "true",
		);

		if(!is_array($_FILES["myFile"]["name"][$k]))
		{
			$filename = $_FILES["myFile"]["name"][$k];
			$arFileName = explode(".", $filename);

			if((in_array($arFileName[count($arFileName)-1], $arParamTypeFileTrim) || empty($arTypeFile[$code]["FILE_TYPE"])) && !empty($_FILES["myFile"]["tmp_name"][$k]))
			{
				$file_array = Array();
				$_FILES["myFile"]["name"][$k] = CUtil::translit($_FILES["myFile"]["name"][$k], "ru", $params);
				$file_array["name"] = $_FILES["myFile"]["name"][$k];
				$file_array["size"] = $_FILES["myFile"]["size"][$k];
				$file_array["tmp_name"] = $_FILES["myFile"]["tmp_name"][$k];
				$file_array["type"] = $_FILES["myFile"]["type"][$k];
				$file_array["description"] = "";
				$PROPS[$code] = $file_array;
			}
			elseif(!empty($_FILES["myFile"]["tmp_name"][$k]))
				$errorFile[$codeID] .= GetMessage("ALX_FIELD1").$arTypeFile[$code]["NAME"].'". '.GetMessage("DISABLE_FILE").' '.$k;
			elseif(in_array($k, $arParams["PROPERTY_FIELDS_REQUIRED"]) && $_FILES["myFile"]["error"][$k] == 4)
				$errorFile[$codeID] .= GetMessage("ALX_FIELD1").$arTypeFile[$code]["NAME"].'". '.GetMessage("EMPTY_FILE");
		}
		else
		{
			$n = 0;
			foreach($_FILES["myFile"]["name"][$k] as $mk => $mval)
			{
				$filename = $_FILES["myFile"]["name"][$k][$mk];
				$arFileName = explode(".", $filename);

				if((in_array($arFileName[count($arFileName)-1], $arParamTypeFileTrim) || empty($arTypeFile[$code]["FILE_TYPE"])) && !empty($_FILES["myFile"]["tmp_name"][$k][$mk]))
				{
					$file_array = Array();
					$_FILES["myFile"]["name"][$k][$mk] = CUtil::translit($_FILES["myFile"]["name"][$k][$mk], "ru", $params);
					$file_array["name"] = $_FILES["myFile"]["name"][$k][$mk];
					$file_array["size"] = $_FILES["myFile"]["size"][$k][$mk];
					$file_array["tmp_name"] = $_FILES["myFile"]["tmp_name"][$k][$mk];
					$file_array["type"] = $_FILES["myFile"]["type"][$k][$mk];
					$file_array["description"] = "";
					$PROPS[$code]["n".$n++] = $file_array;
				}
				elseif(!empty($_FILES["myFile"]["tmp_name"][$k][$mk]))
					$errorFile[$codeID] .= GetMessage("ALX_FIELD1").$arTypeFile[$code]["NAME"].'". '.GetMessage("DISABLE_FILE").' '.$k.' '.$mk;
				elseif(in_array($k, $arParams["PROPERTY_FIELDS_REQUIRED"]) && $_FILES["myFile"]["error"][$k][$mk] == 4)
					$errorFile[$codeID] .= GetMessage("ALX_FIELD1").$arTypeFile[$code]["NAME"].'". '.GetMessage("EMPTY_FILE");
			}
		}
	}
}

$arParams["IBLOCK_TYPE"] = trim(htmlspecialcharsEx($arParams["IBLOCK_TYPE"]));
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["EVENT_TYPE"] = trim(htmlspecialcharsEx($arParams["EVENT_TYPE"]));
$arParams["ACTIVE_ELEMENT"] = trim(htmlspecialcharsEx($arParams["ACTIVE_ELEMENT"]));
$arParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"] == "Y" && !$USER->IsAuthorized();
$arParams["ADD_LEAD"] = $arParams["ADD_LEAD"] == "Y" ? "Y" : "";

if(strlen($arParams["EVENT_TYPE"]) <= 0)
	$arParams["EVENT_TYPE"] = "ALX_FEEDBACK_FORM";

// parameters name of property fields saving name, email and phone for auto-complete
$arAutompleteParams = array(
	"PROPS_AUTOCOMPLETE_NAME",
	"PROPS_AUTOCOMPLETE_EMAIL",
	"PROPS_AUTOCOMPLETE_PERSONAL_PHONE"
);
if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["FEEDBACK_FORM_".$ALX] && check_bitrix_sessid())
{
	$arFields = $_POST["FIELDS"];

	if(!is_array($arFields))
		$arFields = Array();

	$arResult["POST"] = Array();
	$arFieldsName = Array();

	$rsProp = CIBlockProperty::GetList(Array(), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
	while($arrProp = $rsProp->Fetch())
		$arFieldsName[$arrProp["CODE"]."_".$ALX] = $arrProp;

	foreach($arParams["PROPERTY_FIELDS_REQUIRED"] as $k => $v)
	{
		if(is_array($_POST["codeFileFields"]))
		{
			if(!array_key_exists($v, $arFields) && !in_array($v, $_POST["codeFileFields"]) && $v != "FEEDBACK_TEXT_".$ALX)
				$arResult["FORM_ERRORS"]["EMPTY_FIELD"][$v] = GetMessage("ALX_FIELD1").$arFieldsName[$v]["NAME"].GetMessage("ALX_FIELD2");
		}
		else
		{
			if(!array_key_exists($v, $arFields) && $v != "FEEDBACK_TEXT_".$ALX)
				$arResult["FORM_ERRORS"]["EMPTY_FIELD"][$v] = GetMessage("ALX_FIELD1").$arFieldsName[$v]["NAME"].GetMessage("ALX_FIELD2");
		}
	}
	foreach($arFields as $k => $v)
	{
		$k = htmlspecialcharsEx($k);
		$k = str_replace("_".$ALX, "", $k);
		if($k != "myFile")
		{
			if(is_array($v))
			{
				$PROPS[$k] = $v;
			}
			elseif(strlen(trim(htmlspecialcharsEx($v))) <= 0)
			{
				if(in_array($k."_".$ALX, $arParams["PROPERTY_FIELDS_REQUIRED"]))
					$arResult["FORM_ERRORS"]["EMPTY_FIELD"][$k."_".$ALX] = GetMessage("ALX_FIELD1").$arFieldsName[$k."_".$ALX]["NAME"].GetMessage("ALX_FIELD2");
			}
			else
			{
				if($k == "EMAIL")
				{
					$v = trim(htmlspecialcharsEx($v));
					if(check_email($v))
						$PROPS[$k] = $v;
					else
						$arResult["FORM_ERRORS"]["EMPTY_FIELD"][$k."_".$ALX] = GetMessage("INCORRECT_MAIL");
				}
				else
				{
					if($arFieldsName[$k."_".$ALX]["USER_TYPE"] == "HTML")
						$PROPS[$k] = array(
							"VALUE" => array(
								"TEXT" => trim(htmlspecialcharsEx($v)),
								"TYPE" => "TEXT"
							)
						);
					else
						$PROPS[$k] = trim(htmlspecialcharsEx($v));
				}
			}
		}
		else
		{
			foreach($arFields["myFile"] as $kMyFile => $vMyFile)
				if(is_array($errorFile))
					if(array_key_exists($kMyFile, $errorFile))
						$arResult["FORM_ERRORS"]["EMPTY_FIELD"][$kMyFile] = $errorFile["$kMyFile"];
		}
	}

	$arResult["FEEDBACK_TEXT"] = trim(htmlspecialcharsEx($_POST["FEEDBACK_TEXT_".$ALX]));
	if(strlen($arResult["FEEDBACK_TEXT"]) <= 0)
		if(in_array("FEEDBACK_TEXT_".$ALX, $arParams["PROPERTY_FIELDS_REQUIRED"]))
			$arResult["FORM_ERRORS"]["EMPTY_FIELD"]["FEEDBACK_TEXT_".$ALX] = GetMessage("ALX_FIELD1").GetMessage("ALX_CP_EVENT_TEXT_MESSAGE").GetMessage("ALX_FIELD2");

	if(count($PROPS) == 0 && empty($arResult["FEEDBACK_TEXT"]) && count($arResult["FORM_ERRORS"]["EMPTY_FIELD"]) == 0)
		$arResult["FORM_ERRORS"]["EMPTY_FIELD"]["ALL_EMPTY"] = GetMessage("ALX_ERROR_ALL_EMPTY");

	$PROPS["USERIP"] = $_SERVER["REMOTE_ADDR"];

	if($arParams["USE_CAPTCHA"])
	{
		if($arParams["CAPTCHA_TYPE"] == "recaptcha") // Google reCAPTCHA
		{
			if(COption::GetOptionString('altasib.feedback', 'ALX_COMMON_CRM') == "Y")
			{
				$site_key = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SITE_KEY');
				$server_key = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SECRET_KEY');
			}
			else
			{
				$site_key = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SITE_KEY_'.SITE_ID);
				$server_key = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SECRET_KEY_'.SITE_ID);
			}

			$strResponse = $_POST["g-recaptcha-response"];
			$user_ip = $_SERVER["REMOTE_ADDR"];
			if (!empty($_SERVER["HTTP_X_REAL_IP"]))
				$user_ip = $_SERVER["HTTP_X_REAL_IP"];

			$strUrl = "https://www.google.com/recaptcha/api/siteverify?secret=".$server_key."&response=".$strResponse."&remoteip=".$user_ip;

			if (!function_exists('curl_init'))
			{
				if(!$text = file_get_contents($strUrl))
					$arResult["FORM_ERRORS"]["CAPTCHA_WORD"]["ALX_CP_WRONG_CAPTCHA"] = GetMessage("ALX_CP_WRONG_RECAPTCHA_NOT");
			}
			else
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $strUrl);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);
				$text = curl_exec($ch);
				$errno = curl_errno($ch);
				$errstr = curl_error($ch);
				curl_close($ch);

				if ($errno)
					$arResult["FORM_ERRORS"]["CAPTCHA_WORD"]["ALX_CP_WRONG_CAPTCHA"] .= GetMessage("ALX_CP_WRONG_RECAPTCHA_NOT");
			}
			$answers = json_decode($text, true);
			if(!$answers["success"])
			{
				$strCaptchaErr = '';
				foreach($answers["error-codes"] as $err)
				{
					if($err == 'missing-input-response')
						$strCaptchaErr .= GetMessage("ALX_CP_WRONG_RECAPTCHA_MIR");
					elseif($err == 'invalid-input-response')
						$strCaptchaErr .= GetMessage("ALX_CP_WRONG_RECAPTCHA_IIR");
					else
						$strCaptchaErr .= GetMessage("ALX_CP_WRONG_RECAPTCHA_ALL");
				}
				$arResult["FORM_ERRORS"]["CAPTCHA_WORD"]["ALX_CP_WRONG_CAPTCHA"] .= $strCaptchaErr;
			}
		}
		else // system CAPTCHA
		{
			$captcha_sid = $_POST['captcha_sid'];
			$captcha_word = $_POST['captcha_word'];
			if(!$APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid))
				$arResult["FORM_ERRORS"]["CAPTCHA_WORD"]["ALX_CP_WRONG_CAPTCHA"] = GetMessage("ALX_CP_WRONG_CAPTCHA");
		}
	}

	if(count($arResult["FORM_ERRORS"]) <= 0)
	{
		$arMessForm = array();
		$_POST["type_question_".$ALX] = trim(htmlspecialcharsEx($_POST["type_question_".$ALX]));

		// add element
		$arElementFields = Array(
			"IBLOCK_ID"				=> $arParams["IBLOCK_ID"],
			"IBLOCK_SECTION_ID"		=> $_POST["type_question_".$ALX],
			"ACTIVE"				=> $arParams["ACTIVE_ELEMENT"],
			"PREVIEW_TEXT"			=> $arResult["FEEDBACK_TEXT"],
			"PROPERTY_VALUES"		=> $PROPS,
		);

		if(!empty($arParams["SECTION_MAIL".$_POST["type_question_".$ALX]]))
		{
			$emailTo = trim($arParams["SECTION_MAIL".$_POST["type_question_".$ALX]]);
			$emailTo .= ", ".trim($arParams["SECTION_MAIL_ALL"]);
		}
		else
		{
			$emailTo = trim($arParams["SECTION_MAIL_ALL"]);
		}

		if ($arParams["ACTIVE_ELEMENT"] == "Y")
			$arElementFields["ACTIVE"] == "Y";

		if ($arParams['NAME_ELEMENT'] == "ALX_DATE")
		{
			$arElementFields["NAME"] = ConvertTimeStamp();
		}
		elseif ($arParams['NAME_ELEMENT'] == "ALX_TEXT")
		{
			$arElementFields["NAME"] = $arResult["FEEDBACK_TEXT"];
		}
		elseif (!empty($PROPS[$arParams['NAME_ELEMENT']]))
		{
			if(!is_array($PROPS[$arParams['NAME_ELEMENT']]))
				$arElementFields["NAME"] = $PROPS[$arParams['NAME_ELEMENT']];
			else
				$arElementFields["NAME"] = $PROPS[$arParams['NAME_ELEMENT']]["VALUE"]["TEXT"];
		}
		else
		{
			$arElementFields["NAME"] = ConvertTimeStamp();
		}

		$el = new CIBlockElement;

		if(!$ID = $el->Add($arElementFields))
		{
			ShowError($el->LAST_ERROR);
		}
		else
		{
			$strMessageForm = "";
			$strMessCategoty = "";
			$_POST["type_question_name_".$ALX] = trim(htmlspecialcharsEx($_POST["type_question_name_".$ALX]));
			if(!empty($_POST["type_question_name_".$ALX]))
				$strMessCategoty = GetMessage("ALX_TREATMENT_CATEGORY").": ".$_POST["type_question_name_".$ALX]."\n\n";

			$dbProps = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $ID, array("sort" => "asc"));

			/* ---- props ---- */

			$arMessFormProps = Array();

			while($arProps = $dbProps->GetNext())
			{
				if(!empty($arProps["VALUE"]))
				{
					$arMessFormProps[$arProps["ID"]]["SYSTEM"] = $arProps;
					$value = false;

					if($arProps["PROPERTY_TYPE"] == "L")
						$value = $arProps["VALUE_ENUM"];
					elseif($arProps["PROPERTY_TYPE"] == "F")
						$value = "http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arProps["VALUE"]);
					elseif($arProps["USER_TYPE"] == "HTML")
						$value = $arProps["VALUE"]["TEXT"];
					else
						$value = $arProps["VALUE"];

					if($value)
					{
						$arMessFormProps[$arProps["ID"]]["VALUE"][] = $value;
					}
				}

				// save name, email and phone in session
				foreach($arAutompleteParams as $param)
				{
					if(is_array($arParams[$param]) && !empty($arParams[$param]))
					{
						if(in_array($arProps["CODE"], $arParams[$param]))
						{
							$_SESSION["ALTASIB_FB_".$arProps["CODE"]] = htmlspecialcharsbx($arProps["VALUE"]);
							break;
						}
					}
				}
			}

			foreach($arMessFormProps as $arMFProp)
			{
				$arMessForm[$arMFProp["SYSTEM"]["ID"]] = $arMFProp["SYSTEM"]["NAME"].": ";

				if($arMFProp["SYSTEM"]["PROPERTY_TYPE"] == "L")
				{
					if(isset($arMFProp["VALUE"]) && is_array($arMFProp["VALUE"]))
						$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= implode(", ", $arMFProp["VALUE"]);
					else
						$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= ", ".$arMFProp["SYSTEM"]["VALUE_ENUM"];

				}
				elseif($arMFProp["SYSTEM"]["PROPERTY_TYPE"] == "E")
				{
					foreach($arMFProp["VALUE"] as $keyElem=>$iElementId)
					{
						$resElement = CIBlockElement::GetByID($iElementId);
						if($arElement = $resElement->GetNext())
						{
							$arMFProp["VALUE"][$keyElem] = $arElement["NAME"].' (#'.$arElement["ID"].')';
						}
					}

					$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= implode(", ", $arMFProp["VALUE"]);

				}
				elseif($arMFProp["SYSTEM"]["PROPERTY_TYPE"] == "F")
				{
					if(is_array($arMFProp["VALUE"]) && !empty($arMFProp["VALUE"]))
					{
						foreach($arMFProp["VALUE"] as $FlProp)
							$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= "\n".$FlProp;
						$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= "\n";
					}
					else
						$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= "\n"."http://".$_SERVER["SERVER_NAME"].CFile::GetPath($arMFProp["SYSTEM"]["VALUE"])."\n";
				}
				elseif($arMFProp["SYSTEM"]["USER_TYPE"] == "HTML")
				{
					$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= "\n".$arMFProp["SYSTEM"]["VALUE"]["TEXT"];

				}
				else
				{
					$arMessForm[$arMFProp["SYSTEM"]["ID"]] .= implode(", ", $arMFProp["VALUE"]);
				}
			}
			/* ---- end props ---- */

			foreach($arMessForm as $k => $v)
				$strMessageForm .= $v."\n";
			// create EventType for admin
			$rsET = CEventType::GetList(Array("TYPE_ID" => $arParams["EVENT_TYPE"]));
			if(!$arET = $rsET->Fetch())
			{
				$et = new CEventType;
				$eventID = $et->Add(array(
					"LID"		=> LANGUAGE_ID,
					"EVENT_NAME"	=> trim(htmlspecialcharsEx($arParams["EVENT_TYPE"])),
					"NAME"			=> GetMessage("ALX_CP_EVENT_NAME"),
					"DESCRIPTION"	=> GetMessage("ALX_CP_EVENT_DESCRIPTION")
				));
				$emess = new CEventMessage;
				$arMessage = Array(
					"ACTIVE"		=>	"Y",
					"LID"			=>	SITE_ID,
					"EVENT_NAME"	=>	trim(htmlspecialcharsEx($arParams["EVENT_TYPE"])),
					"EMAIL_FROM"	=>	"#EMAIL_FROM#",
					"EMAIL_TO"		=>	"#DEFAULT_EMAIL_FROM#, #SECTION_EMAIL_TO#",
					"SUBJECT"		=>	GetMessage("ALX_CP_EVENT_MESSAGE_SUBJECT"),
					"BODY_TYPE"		=>	"text",
					"BCC"			=>	"#BCC#",
					"MESSAGE"		=>	GetMessage("ALX_CP_EVENT_MESSAGE"),
				);

				if(!$emess->Add($arMessage))
					ShowError($emess->LAST_ERROR);
			}
			$strMessage = $strMessCategoty;
			if(!empty($arResult["FEEDBACK_TEXT"]))
			{
				$strMessage.= GetMessage("ALX_CP_EVENT_TEXT_MESSAGE").":";
				$strMessage.= "\n";
				$strMessage.= $arResult["FEEDBACK_TEXT"];
				$strMessage.= "\n";
				$strMessage.= "\n";
			}
			$strMessage.= $strMessageForm;
			$strMessage.= "------------------------------------------";

			if($arParams["SHOW_MESSAGE_LINK"] == "Y")
			{
				$strEditUrl = GetMessage("ALX_CP_IBLOCK_ELEMENT_EDIT",
					Array("#ID#" => $ID, "#IBLOCK_TYPE#" => trim(htmlspecialcharsEx($arParams["IBLOCK_TYPE"])), "#LID#" => LANGUAGE_ID, "#IBLOCK_ID#" => intval($arParams["IBLOCK_ID"])));
				$strMessLink = GetMessage("ALX_CP_EVENT_MESSAGE_LINK",
					Array("#SERVER_NAME#" => $_SERVER["SERVER_NAME"], "#EDIT_URL#" => $strEditUrl));
				$strMessage .= "\n\n".$strMessLink;
			}

			if(!empty($_POST["type_question_name_".$ALX]))
				$strMessCategoty = "(".$_POST["type_question_name_".$ALX].")";
			$arEventSend = Array(
				"SECTION_EMAIL_TO"		=> $emailTo,
				"TEXT_MESSAGE"			=> $strMessage,
				"BCC"					=> trim($arParams["BBC_MAIL"]),
				"CATEGORY"				=> $strMessCategoty
			);

			if($arParams["USERMAIL_FROM"] == "Y" && check_email($_POST["FIELDS"]["EMAIL_".$ALX]))
				$arEventSend["EMAIL_FROM"] = $_POST["FIELDS"]["EMAIL_".$ALX];
			else
				$arEventSend["EMAIL_FROM"] = COption::GetOptionString("main", "email_from");

			CEvent::SendImmediate($arParams["EVENT_TYPE"], SITE_ID, $arEventSend);
			// create EventType for user
			$mail = trim(htmlspecialcharsEx($_POST["FIELDS"]["EMAIL_".$ALX]));

			if ($arParams["SEND_MAIL"] == "Y" && !empty($mail))
			{
				$rsET = CEventType::GetList(Array("TYPE_ID" => "ALX_FEEDBACK_FORM_SEND_MAIL"));

				if(!$arET = $rsET->Fetch())
				{
					$et = new CEventType;
					$eventID = $et->Add(array(
						"LID"			=> LANGUAGE_ID,
						"EVENT_NAME"	=> "ALX_FEEDBACK_FORM_SEND_MAIL",
						"NAME"			=> GetMessage("ALX_CP_SEND_MAIL"),
						"DESCRIPTION"	=> ""
					));
					$emess = new CEventMessage;
					$arMessage = Array(
						"ACTIVE"		=>	"Y",
						"LID"			=>	SITE_ID,
						"EVENT_NAME"	=>	"ALX_FEEDBACK_FORM_SEND_MAIL",
						"EMAIL_FROM"	=>	"#DEFAULT_EMAIL_FROM#",
						"EMAIL_TO"		=>	"#EMAIL#",
						"SUBJECT"		=>	GetMessage("ALX_CP_EVENT_MESSAGE_SUBJECT"),
						"BODY_TYPE"		=>	"text",
						"BCC"			=>	"#BCC#",
						"MESSAGE"		=>	GetMessage("ALX_SEND_USER_MESSAGE")
					);
					$altasib_flag_error=false;

					if(!$emess->Add($arMessage))
					{
						ShowError($emess->LAST_ERROR);

						$altasib_flag_error=true;
					}
				}
				if($altasib_flag_error==false)
				{
					$arEventSend = Array(
						"TEXT_MESSAGE"	=>	GetMessage("ALX_SEND_USER_MESSAGE_TEXT"),
						"CATEGORY"		=>	$strMessCategoty,
						"EMAIL"			=>	$mail
					);
					CEvent::SendImmediate("ALX_FEEDBACK_FORM_SEND_MAIL", SITE_ID, $arEventSend);
				}
			}
			$arResult["success_".$ALX]="yes";

			if($arParams["ADD_LEAD"] == "Y")
			{
				$arLeadFields = array();
				foreach($arParams as $key => $value)
				{
					if(stripos($key, 'LEAD_') === 0 && $value)
						$arLeadFields[substr($key, 5)] = $PROPS[$value];
				}
				$arLeadFields['SOURCE_ID'] = 'WEB';
				$arLeadFields['COMMENTS'] = $arElementFields['PREVIEW_TEXT'];
				AltasibFeedbackCRM::AddLead($arLeadFields);
			}

			// saving in cookies submit of form - if popup
			if($arParams['ALX_LOAD_PAGE']=='Y' && $arParams['ALX_CHECK_NAME_LINK']=='Y')
			{
				$APPLICATION->set_cookie("ALTASIB_FDB_SEND_".$ALX, "Y", time()+2592000); // 60*60*24*30
			}
		}
		if($arParams['ALX_CHECK_NAME_LINK']!='Y')
		{
			if($arParams['LOCAL_REDIRECT_ENABLE'] == 'Y')
			{
				LocalRedirect(trim(htmlspecialcharsEx($arParams['LOCAL_REDIRECT_URL'])));
			}
			else
			{
				LocalRedirect($APPLICATION->GetCurPageParam("success_".$ALX."=yes", array("success")));
			}
		}
	}
}

$arResult["FIELDS"] = Array();
$rsProp = CIBlockProperty::GetList(Array("SORT" => "ASC"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
while($arrProp = $rsProp->Fetch())
{
	if(!in_array($arrProp["CODE"], $arParams["PROPERTY_FIELDS"]))
		continue;

	$arField = Array(
		"CODE"	=>	$arrProp["CODE"] ."_".$ALX,
		"NAME"	=>	$arrProp["NAME"],
		"TYPE"	=>	$arrProp["PROPERTY_TYPE"],
		"HINT"	=>	$arrProp["HINT"],
		"DEFAULT_VALUE"	=>	$arrProp["DEFAULT_VALUE"],
		"USER_TYPE"	=>	$arrProp["USER_TYPE"]
	);

	if(isset($arrProp["USER_TYPE_SETTINGS"]))
		$arField["USER_TYPE_SETTINGS"] = $arrProp["USER_TYPE_SETTINGS"];

	if($arrProp["PROPERTY_TYPE"] == "L")
	{
		$arField["LIST_TYPE"] = $arrProp["LIST_TYPE"];
		$arField["MULTIPLE"] = $arrProp["MULTIPLE"];

		$db_enum_list = CIBlockProperty::GetPropertyEnum($arrProp["ID"]);
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arField["ENUM"][] = $ar_enum_list;
		}
	}

	if($arrProp["PROPERTY_TYPE"] == "F")
	{
		$arField["MULTIPLE"] = $arrProp["MULTIPLE"];
		$arField["MULTIPLE_CNT"] = $arrProp["MULTIPLE_CNT"];
	}

	if(in_array($arField["CODE"], $arParams["PROPERTY_FIELDS_REQUIRED"]))
		$arField["REQUIRED"] = "Y";

	if($arrProp["CODE"] == "CITY")
		if(CModule::IncludeModule("altasib.geoip"))
		{
			$arGeoIP = ALX_GeoIP::GetAddr();
			$arField["DEFAULT_VALUE"] = $arGeoIP["city"];
		}

	if($arrProp["PROPERTY_TYPE"] == "E")
	{
		$arField["PROPERTY"] = $arrProp;

		if(isset($arrProp["LINK_IBLOCK_ID"]))
		{
			$resElement = CIBlockElement::GetList(
				Array("SORT" => "ASC"),
				Array(
					"IBLOCK_ID"	=> $arrProp["LINK_IBLOCK_ID"],
					"ACTIVE"	=> "Y"
				),
				false,
				false,
				Array("IBLOCK_ID", "ID", "ACTIVE", "NAME")
			);

			while($arElement = $resElement->Fetch())
			{
				$arField["LINKED_ELEMENTS"][] = $arElement;
			}
		}
	}

	// autocomplete the form fields from personal or session
	if($USER->IsAuthorized())
	{
		if(is_array($arParams["PROPS_AUTOCOMPLETE_NAME"])
			&& !empty($arParams["PROPS_AUTOCOMPLETE_NAME"]))
		{
			if(in_array($arrProp["CODE"], $arParams["PROPS_AUTOCOMPLETE_NAME"]))
				$arField["AUTOCOMPLETE_VALUE"] = $USER->GetFormattedName(false);
		}
		if(is_array($arParams["PROPS_AUTOCOMPLETE_EMAIL"])
			&& !empty($arParams["PROPS_AUTOCOMPLETE_EMAIL"]))
		{
			if(in_array($arrProp["CODE"], $arParams["PROPS_AUTOCOMPLETE_EMAIL"]))
				$arField["AUTOCOMPLETE_VALUE"] = htmlspecialcharsbx($USER->GetEmail());
		}
		if(is_array($arParams["PROPS_AUTOCOMPLETE_PERSONAL_PHONE"])
			&& !empty($arParams["PROPS_AUTOCOMPLETE_PERSONAL_PHONE"]))
		{
			if(in_array($arrProp["CODE"], $arParams["PROPS_AUTOCOMPLETE_PERSONAL_PHONE"]))
			{
				if($arUser = CUser::GetByID($USER->GetID())->Fetch())
				{
					$arField["AUTOCOMPLETE_VALUE"] = $arUser["PERSONAL_PHONE"];
				}
			}
		}
	}
	else
	{
		// save name, email and phone in session
		foreach($arAutompleteParams as $param)
		{
			if(is_array($arParams[$param]) && !empty($arParams[$param]))
			{
				if(in_array($arrProp["CODE"], $arParams[$param]))
				{
					if(strlen($_SESSION["ALTASIB_FB_".$arrProp["CODE"]]) > 0)
					{
						$arField["AUTOCOMPLETE_VALUE"] = htmlspecialcharsbx($_SESSION["ALTASIB_FB_".$arrProp["CODE"]]);
						break;
					}
				}
			}
		}
	}

	$arResult["FIELDS"][] = $arField;
}
$arResult["TYPE_QUESTION"] = Array();
$arFilter = Array(
	"ACTIVE"=>"Y",
	"IBLOCK_ID"=>$arParams["IBLOCK_ID"]
);
$arSection = CIBlockSection::GetList(Array("SORT"=>"ASC"), $arFilter, true);
while($v = $arSection->GetNext())
	$arResult["TYPE_QUESTION"][] = $v;

if($arParams["USE_CAPTCHA"] == "Y" && $arParams["CAPTCHA_TYPE"] == "recaptcha")
{
	$common_crm = COption::GetOptionString('altasib.feedback', 'ALX_COMMON_CRM');
	if($common_crm == "Y")
		$arResult["SITE_KEY"] = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SITE_KEY');
	else
		$arResult["SITE_KEY"] = COption::GetOptionString('altasib.feedback', 'ALX_RECAPTCHA_SITE_KEY_'.SITE_ID);
}

$this->IncludeComponentTemplate();

if($arParams['ALX_CHECK_NAME_LINK']=='Y')
{
	if($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST["OPEN_POPUP_".$ALX] || $_POST["FEEDBACK_FORM_".$ALX]))
		die();
}
?>