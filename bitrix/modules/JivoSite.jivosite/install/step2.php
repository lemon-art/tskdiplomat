<?

	
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/JivoSite.jivosite/config.php');

	IncludeModuleLangFile(__FILE__);
	
	function do_post_request($url, $data, $optional_headers = null)
	{
		$d = http_build_query($data);
		
		$params = array('http' => array(
					  'method' => 'POST',
					  'content' => $d
					));
		
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		
		$ctx = stream_context_create($params);
		
		$fp = @fopen($url, 'rb', false, $ctx);
		
		if (!$fp) {
			throw new Exception(GetMessage("JS_ERR_CONN")." $url $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		
		if ($response === false) {
			throw new Exception(GetMessage("JS_ERR_CONN")." $url $php_errormsg");
		}
		return $response;
	}
	
	
	try{
		$authToken = uniqid ();
		$siteUrl = "http://".COption::GetOptionString("main", "server_name");
		
		if (!$email)
			throw new Exception(GetMessage("JS_NO_EMAIL"));
		
		if (!$password)
			throw new Exception(GetMessage("JS_NO_PW"));
		
		if (!$userDisplayName)
			throw new Exception(GetMessage("JS_NO_NAME"));
		
		$res = do_post_request("https://".JIVO_BASE_URL."/integration/install",
			array(
				"partnerId" => "bitrix",
				"partnerPassword" => "bitrix",
				"siteUrl" => $siteUrl,
				"email" => $email,
				"userPassword" => $password,
				"userDisplayName" => $userDisplayName,
				"authToken" => $authToken
			)
		);
		
		if (strstr($res,"Error:") != FALSE || !is_numeric ($res)){
			throw new Exception($res);
		} 

		COption::SetOptionString("JivoSite.jivosite", "widget_id", $res);
		COption::SetOptionString("JivoSite.jivosite", "auth_token", $authToken);
		
		RegisterModule("JivoSite.jivosite");
		RegisterModuleDependences("main", "OnPageStart", "JivoSite.jivosite", "JivoSiteClass", "addScriptTag", "0");
		
		echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
		
		$token = COption::GetOptionString("JivoSite.jivosite", "auth_token");

		
		?>
			<?= GetMessage("JS_INSTALL_1"); ?>
			<?= GetMessage("JS_INSTALL_SETTINGS"); ?>
			
		
			<form action='https://<?= JIVO_BASE_URL ?>/integration/login' target='_blank'>
				<input type='hidden' name='token' value='<?= $authToken ?>'>
				<input type='hidden' name='partner' value='bitrix'>
				<input style='font-size: 18px; padding: 8px;' type='submit' value='<?= GetMessage("JS_GOTO_ADMIN"); ?>'>
			</form>
		
			<?= GetMessage("JS_AFTER_REG"); ?>
			
			<p><a href='<?=$APPLICATION->GetCurPage()?>'>&laquo; <?= GetMessage("JS_BACK_TO_MODULES") ?> </a></p>
		<?
				
	}catch(Exception $e){
		
		$errTxt =  $e->getMessage();
				
		echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$errTxt, "HTML"=>false));
		
		$APPLICATION->IncludeAdminFile(
				GetMessage("JS_STEP_1"), 
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/JivoSite.jivosite/install/step1.php"
			);
		
	}


?>
	
