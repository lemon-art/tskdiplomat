<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$bAjaxSuccess	= false;
$arFields		= $_POST;
$arServer		= $_SERVER;

// Sending data from AJAX
if($arFields["AJAX_CALL"] == "Y" && $arFields["ASP-TR"] == "" && check_bitrix_sessid()) {
	$thisTimeMin	= top10getTimeMin($arFields);		// MIN TIME
	$thisTimeMax	= top10getTimeMax($arFields);		// MAX TIME
	$sPhone			= top10getPhoneNumber($arFields);	// Phone number
	$sName			= top10getName($arFields);			// User name
	$sMessage		= top10getCallbackMessage($thisTimeMin, $thisTimeMax, $sPhone, $sName);
	
	if($arParams["IBLOCK_SAVE"] === "Y") {
		top10saveCallbackToIBlock(
			$arParams,
			Array(
				"PHONE"		=> $sPhone,
				"MESSAGE"	=> $sMessage
			)
		);
	}
	
	if($arParams["EMAIL_SEND"] === "Y") {
		top10sendEmail(
			$arParams,
			Array("MESSAGE"	=> $sMessage)
		);
	}
	
	$bAjaxSuccess = true;
}

if(!CModule::IncludeModule("iblock")) {
    ShowError(GetMessage("IB_MODULE_NOT_INSTALLED"));
    return;
}

top10setTimesInResult($arResult, $arParams);

$this->IncludeComponentTemplate();

if($bAjaxSuccess) {
	echo '<script>top10_callback_set_popup();top10_callback_success();</script>';
}

function top10saveCallbackToIBlock($arParams, $arData) {
	if($arParams["IBLOCK_ID"] && CModule::IncludeModule("iblock")) {
		global $USER;
		$elCallback = new CIBlockElement;
		
		$arElementCallbackParam = Array(
			"MODIFIED_BY"		=> $USER->GetID(),
			"IBLOCK_SECTION_ID"	=> false,
			"IBLOCK_ID"			=> intval($arParams["IBLOCK_ID"]),
			"NAME"				=> $arData["PHONE"],
			"ACTIVE"			=> "Y",
			"PREVIEW_TEXT"		=> $arData["MESSAGE"],
		);
		
		$iCallbackId = $elCallback->Add($arElementCallbackParam);
		
		if($iCallbackId) {
			return $iCallbackId;
		} else {
			return "Error: ".$elCallback->LAST_ERROR;
		}
	} else {
		return false;
	}
}

function top10sendEmail($arParams, $arData) {
	if(strpos($arParams["EMAIL_TO"], "@") && $arParams["EMAIL_SUBJECT"]) {

		$arEventFields = Array(
			"EMAIL_TO"		=> $arParams["EMAIL_TO"],
			"EMAIL_SUBJECT"	=> $arParams["EMAIL_SUBJECT"],
			"MESSAGE"		=> $arData["MESSAGE"]
		);

		foreach($arParams["EMAIL_EVENT_TYPE"] as $sEventName) {
			CEvent::Send($sEventName, SITE_ID, $arEventFields);
		}

		return true;
	} else {
		return false;
	}
}

function top10getTimeMin($arPost) {
	if($arPost["TIME_MIN"]) {
		$sTimeMin	= htmlspecialcharsEx($arPost["TIME_MIN"]);
		
		$hours		= floor($sTimeMin / 60);
		$minutes	= $sTimeMin - ($hours * 60);

		if(strlen($hours) == 1)		{ $hours = '0'.$hours; }
		if(strlen($minutes) == 1)	{ $minutes = '0'.$minutes; }
		if($minutes == 0)			{ $minutes = '00'; }

		$thisTimeMin = $hours.':'.$minutes;
		
		return $thisTimeMin;
	} else {
		return false;
	}
}

function top10getTimeMax($arPost) {
	if($arPost["TIME_MAX"]) {
		$sTimeMax	= htmlspecialcharsEx($arPost["TIME_MAX"]);
	
		$hours		= floor($sTimeMax / 60);
		$minutes	= $sTimeMax - ($hours * 60);

		if(strlen($hours) == 1)		{ $hours = '0'.$hours; }
		if(strlen($minutes) == 1)	{ $minutes = '0'.$minutes; }
		if($minutes == 0)			{ $minutes = '00'; }

		$thisTimeMax = $hours.':'.$minutes;
		
		return $thisTimeMax;
	} else {
		return false;
	}
}

function top10getPhoneNumber($arPost) {
	if($arPost["PHONE"]) {
		$sPhone = htmlspecialcharsEx($arPost["PHONE"]);
		
		return $sPhone;
	} else {
		return false;
	}
}

function top10getName($arPost) {
	if($arPost["NAME"]) {
		$sName = htmlspecialcharsEx($arPost["NAME"]);
		
		return $sName;
	} else {
		return false;
	}
}

function top10getCallbackMessage($thisTimeMin, $thisTimeMax, $sPhone, $sName) {
	$message = str_replace(
		Array("{time_min}", "{time_max}", "{phone}", "{name}"),
		Array($thisTimeMin, $thisTimeMax, $sPhone, $sName),
		GetMessage("CALLBACK_MSG")
	);
	
	return $message;
}

function top10setTimesInResult(&$arResult, &$arParams) {
	// Time min to minutes
	$arTimeMatches = Array();
	if(preg_match("/(\d+)\:(\d+)/", $arParams["TIME_MIN"], $arTimeMatches)) {
		$arResult["TIME_MIN"] = $arTimeMatches[1] * 60 + $arTimeMatches[2];
	}

	// Time max to minutes
	$arTimeMatches = Array();
	if(preg_match("/(\d+)\:(\d+)/", $arParams["TIME_MAX"], $arTimeMatches)) {
		$arResult["TIME_MAX"] = $arTimeMatches[1] * 60 + $arTimeMatches[2];
	}

	// Time start to minutes
	$arTimeMatches = Array();
	if(preg_match("/(\d+)\:(\d+)/", $arParams["TIME_START"], $arTimeMatches)) {
		$arResult["TIME_START"] = $arTimeMatches[1] * 60 + $arTimeMatches[2];
	}

	// Time finish to minutes
	$arTimeMatches = Array();
	if(preg_match("/(\d+)\:(\d+)/", $arParams["TIME_FINISH"], $arTimeMatches)) {
		$arResult["TIME_FINISH"] = $arTimeMatches[1] * 60 + $arTimeMatches[2];
	}
}