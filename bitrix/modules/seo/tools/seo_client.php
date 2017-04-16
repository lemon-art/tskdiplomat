<?php
/*
This is callback page for Dropbox OAuth 2.0 authentication.
Dropbox redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Dropbox.
*/
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("socialservices") && CSocServAuthManager::CheckUniqueKey())
{
	if(isset($_REQUEST["authresult"]))
	{
		\Bitrix\Seo\Service::clearLocalAuth();
?>
<script type="text/javascript">
	opener.location.reload();
	window.close();
</script>
<?
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");