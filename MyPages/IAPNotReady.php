<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Not Ready with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

$iapReadOnly = IAP_Format_Heading("Future Function");

?>
<td style="width: 13%;"></td><td style="width: 87%;"><span class='iapFormLabel'>This function is part of a future update.</span></td>
<td colspan="2"></td>
<td style="width: 13%;"></td><td style="width: 87%;"><span class='iapFormLabel'>At this time we do not have a date when the this function will be available. If you have a need for this function, please let us know by completing a support ticket in the Support menu above.</span></td>
<td colspan="2"></td>
<td style="width: 13%;"></td><td style="width: 87%;"><span class='iapFormLabel'>Thank you for your understanding and patience.</span></td>