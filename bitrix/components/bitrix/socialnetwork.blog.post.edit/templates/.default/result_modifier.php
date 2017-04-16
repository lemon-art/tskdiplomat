<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (
    $arParams["B_CALENDAR"]
    && empty($arResult["Post"])
    && !isset($arParams["DISPLAY"])
    && !$arResult["bExtranetUser"]
)
{
    $arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"] = $arResult["PostToShow"]["FEED_DESTINATION"];

    $arResult["DEST_SORT_CALENDAR"] = CSocNetLogDestination::GetDestinationSort(array(
        "DEST_CONTEXT" => "CALENDAR",
        "ALLOW_EMAIL_INVITATION" => false
    ));
    $arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST'] = array();
    CSocNetLogDestination::fillLastDestination($arResult["DEST_SORT_CALENDAR"], $arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']);

    if(!empty($arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']['USERS']))
    {
        foreach ($arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']['USERS'] as $value)
        {
            $arDestUser[] = str_replace('U', '', $value);
        }
    }

    $arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $arDestUser));
}
?>