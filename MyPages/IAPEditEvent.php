<?php

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

	require_once(ABSPATH. 'IAPServices.php');
	if (IAP_Program_Start("173", "N", "N", "N") < 0) {
		return;
	}
	$eeUID = $_REQUEST['IAPUID'];

	if ($_REQUEST['action'] != "173ret") {
		IAP_Remove_Savearea("IAP173EE", $eeUID);
	}

	$Err  = 0;
	if ($_REQUEST['action'] == "173ret") {
		if (empty($_REQUEST['eventid'])) {
			$Err = 1;
		} else {
			$Event = IAP_Get_Event_By_Id($_REQUEST['eventid']);
			if ($Event < 0) {
		    	echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot retreive the selected event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /Action is invalid-reentry/".basename(__FILE__)."/".__LINE__."</span><br />";
		    	exit;
		 	}
			if ($Event == NULL) {
		    	$Err = 2;
			} else {
		 		$_REQUEST['action'] = "selected";
				require_once("MyPages/IAPAddEvent.php");
		    	return;
			}
		}
	} elseif ($_REQUEST['action'] == "eeEret") {
		require_once($_REQUEST['LHCPath']."Ajax/LHCCalendar/FCAddEvent.php");
		return;
	}

	$iapReadOnly = IAP_Format_Heading("Edit a Calendar Event");

	if ($Err != 0) {
		echo "<span style='text-align:left; padding-left:30px; font-size:110%; color:red;'><strong>Please correct the following error</strong></span><br />";
		if ($Err == 1) {
			echo "<span style='padding-left:50px; font-size:110%; color:red;'>No Id was entered.</span><br />";
		} elseif ($Err == 2) {
			echo "<span style='padding-left:50px; font-size:110%; color:red;'>The entered Id could not be found.</span><br />";
		}
		echo "<br />";
	}
	echo "<form action='?action=173ret' method='POST'>";
	echo "<span style='padding-left:30px; font-size:110%;'>Enter the Id of the event to be edited: <input type='text' name='eventid' size='7' /><br /><br /></span>";
	echo "<center><input type='submit' value='Submit' /></center><br /><br />";
	echo "<span style='font-size:110%;'>The Id of the event can be found on the pop-up that appears by clicking on the event in Our Calendar.</span><br />"; 
	echo "<input type='hidden' name='LHCA' id='LHCA' value='".$_REQUEST['CoId']."'>";

?>