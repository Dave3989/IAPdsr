<?php

function IAP_Create_Customer($iapCustomer, $iapBDChg = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapCustomer['cust_company'] = $_REQUEST['CoId'];

	$iapCN = IAP_Split_Name($iapCustomer['cust_name']);
	$iapCustomer['cust_last_name'] = trim($iapCN['lname']." ".$iapCN['suffix']);
	$iapCustomer['cust_first_name'] = $iapCN['fname'];

	if ($iapCustomer['cust_email'] == "") {
		$iapCustomer['cust_newsletter'] = "N";
	}


	if ($_REQUEST['NameEmailChg'] == "Y") {
		$iapCustomer['cust_newsletter_add_date'] = "0000-00-00";
	}

	if ($_REQUEST['FollowUpChg'] == "Y") {
		if ($iapCustomer['cust_followup_consultant'] == "Y"
		or 	$iapCustomer['cust_followup_party'] == "Y") {
			if ($iapCustomer['cust_followup_set'] == "0000-00-00") {
				$iapCustomer['cust_followup_set'] = date('Y-m-d');
			}
		} else {
			$iapCustomer['cust_followup_set'] = "0000-00-00";
		}
	}

	if ($_REQUEST['UserData']['AddBirthdays'] == "Y"
	and $_REQUEST['BDChg'] == "Y") {
		if (!empty($iapCustomer['cust_birthday_event'])) {
			$iapRet = IAP_Delete_Row(array("event_id" => $iapCustomer['cust_birthday_event']), "iapcal");
			if ($iapRet < 0) {
	        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar event row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
			}
			$iapRet = IAP_Delete_Row(array("cr_id" => $iapCustomer['cust_birthday_event']), "iapcrep");
			if ($iapRet < 0) {
	        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
			}
		}
		if (!empty($iapCustomer['cust_birthday'])) {
			$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
			if ($eeEvents < 0) {
		        echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		        exit;
			}
			$eeEvent = (array) $eeEvents[0];
			$eeEvent['event_account'] = $_REQUEST['CoId'];
			$eeEvent['event_title'] = $iapCustomer['cust_name']."'s Birthday";
			$dsc = $eeEvent['event_title']."\n";
			if (!empty($iapCustomer['cust_street'])) {
				$dsc = $dsc.$iapCustomer['cust_street']."\n";
			}
			if (!empty($iapCustomer['cust_city'])
			or  !empty($iapCustomer['cust_state'])
			or  !empty($iapCustomer['cust_zip'])) {
				$dsc = $dsc.$iapCustomer['cust_city'].",".$iapCustomer['cust_state']." ".$iapCustomer['cust_zip']."\n";
			}
			if (!empty($iapCustomer['cust_phone'])) {
				$dsc = $dsc."Phone: ".$iapCustomer['cust_phone']."\n";
			}
			if (!empty($iapCustomer['cust_email'])) {
				$dsc = $dsc."Email: ".$iapCustomer['cust_email']."\n";
			}
			$eeEvent['event_desc'] = $dsc;
			$d = $iapCustomer['cust_birthday']."/".date("Y");
			$dstr = strtotime($d);
			$eeEvent['event_begin'] = date("Y-m-d", $dstr);
			$eeEvent['event_end'] = $eeEvent['event_begin'];
			$eeEvent['event_btime'] = "00:00";
			$eeEvent['event_etime'] = "00:00";
			$eeEvent['event_recur'] = "Y";
			$eeEvent['event_allday'] = "Y";
			$eeEvent['event_author'] = $eeUID;
			$es = $eeEvent['status'];
			$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
			if ($eeRepeats < 0) {
				echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}
			$eeRepeat = (array) $eeRepeats[0];
			$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
			$eeEvent = array_merge($eeEvent, $eeRepeat);
			$eeEvent['status'] = $es;
			$eeEvent['cr_type'] = "A";								// Repeat annually
			$eeEvent['cr_annual_option'] = "a1";					// Repeat on a particular mm/dd
			$eeEvent['cr_annual_month1A'] = date("m", $dstr);
			$eeEvent['cr_annual_dom'] = date("d", $dstr);
			$eeEvent['cr_annual_occurs'] = 1;						// Repeat every year
			$eeEvent['cr_until_date'] = "2099-12-31";				// Repeat forever
			$eeEvent['cr_until_count'] = 0;

			require_once(ABSPATH."Ajax/IAPCalendar/IAPWriteEvent.php");
			$eeEvent['event_id'] = FCWriteEvent($eeEvent, "Y");
			if ($eeEvent['event_id'] < 0) {
				echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}
			$iapCustomer['cust_birthday_event'] = $eeEvent['event_id'];
		}
	}

	if ($_REQUEST['UserData']['AddBirthdays'] == "Y"
	and $_REQUEST['AnChg'] == "Y") {
		if (!empty($iapCustomer['cust_anniverary_event'])) {
			$iapRet = IAP_Delete_Row(array("event_id" => $iapCustomer['cust_anniverary_event']), "iapcal");
			if ($iapRet < 0) {
	        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar event row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
			}
			$iapRet = IAP_Delete_Row(array("cr_id" => $iapCustomer['cust_anniverary_event']), "iapcrep");
			if ($iapRet < 0) {
	        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
			}
		}
		if (!empty($iapCustomer['cust_anniverary'])) {
			$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
			if ($eeEvents < 0) {
		        echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		        exit;
			}
			$eeEvent = (array) $eeEvents[0];
			$eeEvent['event_account'] = $_REQUEST['CoId'];
			$eeEvent['event_title'] = $iapCustomer['cust_name']."'s Anniverary";
			$dsc = $eeEvent['event_title']."\n";
			if (!empty($iapCustomer['cust_street'])) {
				$dsc = $dsc.$iapCustomer['cust_street']."\n";
			}
			if (!empty($iapCustomer['cust_city'])
			or  !empty($iapCustomer['cust_state'])
			or  !empty($iapCustomer['cust_zip'])) {
				$dsc = $dsc.$iapCustomer['cust_city'].",".$iapCustomer['cust_state']." ".$iapCustomer['cust_zip']."\n";
			}
			if (!empty($iapCustomer['cust_phone'])) {
				$dsc = $dsc."Phone: ".$iapCustomer['cust_phone']."\n";
			}
			if (!empty($iapCustomer['cust_email'])) {
				$dsc = $dsc."Email: ".$iapCustomer['cust_email']."\n";
			}
			$eeEvent['event_desc'] = $dsc;
			$d = $iapCustomer['cust_anniverary']."/".date("Y");
			$dstr = strtotime($d);
			$eeEvent['event_begin'] = date("Y-m-d", $dstr);
			$eeEvent['event_end'] = $eeEvent['event_begin'];
			$eeEvent['event_btime'] = "00:00";
			$eeEvent['event_etime'] = "00:00";
			$eeEvent['event_recur'] = "Y";
			$eeEvent['event_allday'] = "Y";
			$eeEvent['event_author'] = $eeUID;
			$es = $eeEvent['status'];
			$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
			if ($eeRepeats < 0) {
				echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}
			$eeRepeat = (array) $eeRepeats[0];
			$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
			$eeEvent = array_merge($eeEvent, $eeRepeat);
			$eeEvent['status'] = $es;
			$eeEvent['cr_type'] = "A";								// Repeat annually
			$eeEvent['cr_annual_option'] = "a1";					// Repeat on a particular mm/dd
			$eeEvent['cr_annual_month1A'] = date("m", $dstr);
			$eeEvent['cr_annual_dom'] = date("d", $dstr);
			$eeEvent['cr_annual_occurs'] = 1;						// Repeat every year
			$eeEvent['cr_until_date'] = "2099-12-31";				// Repeat forever
			$eeEvent['cr_until_count'] = 0;

			require_once(ABSPATH."Ajax/IAPCalendar/IAPWriteEvent.php");
			$eeEvent['event_id'] = FCWriteEvent($eeEvent, "Y");
			if ($eeEvent['event_id'] < 0) {
				echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}
			$iapCustomer['cust_anniverary_event'] = $eeEvent['event_id'];
		}
	}

	$iapCustomer['cust_changed'] = date("Y-m-d");
	$iapCustomer['cust_changed_by'] = $_REQUEST['IAPUID']; 
	$iapRet = IAP_Update_Data($iapCustomer, "cust");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR updating customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapCustomer['status'] == "NEW") {
		$iapCustomer['cust_no'] = $iapRet;
	}

	return($iapCustomer);
}
?>