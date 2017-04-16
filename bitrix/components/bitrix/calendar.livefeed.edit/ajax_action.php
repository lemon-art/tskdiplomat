<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (check_bitrix_sessid() && CModule::IncludeModule("calendar"))
{
	if (isset($_REQUEST['bx_event_calendar_check_meeting_room']) && $_REQUEST['bx_event_calendar_check_meeting_room'] === 'Y')
	{
		$check = false;
		$settings = CCalendar::GetSettings();
		$from = CCalendar::Date(CCalendar::Timestamp($_REQUEST['from']));
		$to = CCalendar::Date(CCalendar::Timestamp($_REQUEST['to']));
		$loc_new = CCalendar::ParseLocation(trim($_REQUEST['location']));

		$params = array(
			'dateFrom' => $from,
			'dateTo' => $to,
			'regularity' => 'NONE',
			'members' => false,
		);

		if ($loc_new['mrid'] == $settings['vr_iblock_id'])
		{
			$params['VMiblockId'] = $settings['vr_iblock_id'];
			$check = CCalendar::CheckVideoRoom($params);
		}
		else
		{
			$params['RMiblockId'] = $settings['rm_iblock_id'];
			$params['mrid'] = $loc_new['mrid'];
			$params['mrevid_old'] = 0;

			$check = CCalendar::CheckMeetingRoom($params);
		}
		?><script>top.BXCRES_Check = <?= CUtil::PhpToJSObject($check)?>;</script><?
	}
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>