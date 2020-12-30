<?php

function IAP_UpldCust_Initial() {
	echo "<form enctype='multipart/form-data' action='?mod=UC&step=1' method='post'>";

	$h = IAP_Do_Help(4, 103, 1); // level 4 (Always display), page 103, section 1
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
	}

	echo "<table style='width:100%'>";
	echo "<tr><td width='5%'></td><td width='95%'></td></tr>";
	echo "<tr><td width='5%'></td><td width='95%'>Select the local file to begin the upload then click Upload.</td></tr>";
	echo "<tr><td width='5%'></td><td width='95%'><span class=iapFormLabel>File name to import:</span></td.</tr>";
	echo "<tr><td width='5%'></td><td width='95%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input size='50' type='file' name='filename'></td.</tr>";
	echo "<tr><td width='5%'></td><td width='95%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit' value='Upload'></td></tr>";
	echo "</table></form>";
}

function IAP_UpldCust_Get_File($iapSave) {

	if ($_FILES['filename']['error'] != 0){
		echo "<span class=iapError>An error was detected - try again!</span><br><br>";
		IAP_Remove_Savearea("IAP103UC");
		return(-1);
	}
	if ($_FILES['filename']['type'] != "text/csv"
	and $_FILES['filename']['type'] != "text/comma-separated-values"
	and $_FILES['filename']['type'] != "application/vnd.ms-excel"
	and $_FILES['filename']['type'] != "text/plain") {
		echo "<span class=iapError>File must be saved as a CSV file!</span><br><br>";
		IAP_Remove_Savearea("IAP103UC");
		return(-2);
	}

// Copy file to TempFiles dir
	if (!copy($_FILES['filename']['tmp_name'], "TempFiles/CustomerUpload.csv")) {
	    	echo "<span class=iapError>Copy of tempfile failed...<br><br>";
		IAP_Remove_Savearea("IAP103UC");
	   	return(-2);
	}

//Get Column Names
	$handle = fopen("TempFiles/CustomerUpload.csv", "r");
	$iapColNames = fgetcsv($handle, 1000, ",", '"');

	fclose($handle);
	if ($iapColNames === FALSE) {
		echo "<span class=iapError>The CSV file is empty.</span><br><br>";
		IAP_Remove_Savearea("IAP103UC");
		return(-3);
	}

	$iapCustCols = $iapSave['custcols'];

	echo "<form enctype='multipart/form-data' action='?mod=UC&step=2' method='post'>";
	$h = IAP_Do_Help(4, 103, 2); // level 4 (Always display), page 103, section 2
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";	
	} else {
		echo "<table style='width:100%'><tr><td width='5%'></td><td width='95%'></td></tr>";
		echo "<tr><td width='5%'></td><td width='95%'>";
		echo "<strong>Step 1 Complete - File Uploaded. You are ready for step 2.</strong></td></tr>";
		echo "</table>";
	}

	echo "<table><tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	echo "<tr><td width='16%' class='iapFormTitle'>Map</td><td width='42%' class='iapFormTitle'>Database Column</td><td width='42%' class='iapFormTitle'>Your CSV Column</td></tr>";
	echo "<tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	$i = 0;
	$iapStop = FALSE;
	while($iapStop === FALSE) {
		echo "<tr><td width='16%'>";
		If ($i < count($iapCustCols)) {
			echo "<input type='text' name='colno".strval($i)."' size='3' maxlength='3' class='iapFormTitle'";
			if ($i == 0) {
				echo "  autofocus";
			}
			echo ">";
		}
		echo "</td><td width='42%'>";
		If ($i < count($iapCustCols)) {
			echo $iapCustCols[$i];
		}
		echo "</td><td width='42%'>";
		If ($i < count($iapColNames)) {
			echo strval($i + 1)." - ".$iapColNames[$i];
		}
		echo "</td></tr>";
		$i = $i + 1;
		if ($i >= count($iapCustCols)
		and $i >= count($iapColNames)) {
			$iapStop = TRUE;
		}
	}
	echo "<tr><td colspan='3'><input class=iapButton type='submit' name='submit' value='Map Columns'></td></tr>";
	echo "</table></form><br><br>";

//	$iapSave = array("step" => "2", "custcols" => $iapImportCols, "realcols" => $iapRealCols, "cols" => $iapColNames);
	$iapSave['step'] = "2";
	$iapSave['cols'] = $iapColNames;
	$iapRet = IAP_Update_Savearea("IAP103UC", $iapSave);
	if ($iapRet < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    return(-1);
	}
}

function IAP_UpldCust_Map_Cols($iapSave) {

	if ($_REQUEST['iapUserData']['AddBirthdays'] == "Y") {
		require("Ajax/IAPCalendar/IAPWriteEvent.php");
	}

	$iapCustCols = $iapSave['custcols'];
	$iapColNames = $iapSave['cols'];
	if (isset($iapSave['map'])) {
		$iapMapping = $iapSave['map'];
	} else {
		$iapMapping = array();		
	}
	$iapError = array();
	$iapNextStep = 3;
	$i = -1;
	while($i < count($iapCustCols)) {
		$i = $i + 1;
		$iapError[$i] = "N";
		$iapCN = "colno".strval($i);
		$iapMapping[$i] = $_REQUEST[$iapCN];
		if ($iapMapping[$i] == "") {
			continue;
		}
		if (!(is_numeric($iapMapping[$i]))) {
			$iapSubCols = explode("+", $iapMapping[$i]);
			foreach($iapSubCols as $iapSC) {
				if (!(is_numeric($iapSC))
				or $iapSC > count($iapColNames)) {
					$iapError[$i] = "Y";
					$iapNextStep = "2";
				} elseif ($iapSC > count($iapColNames)) {
					$iapError[$i] = "Y";
					$iapNextStep = "2";
				}
			}
		} elseif ($iapMapping[$i] > count($iapColNames)) {
			$iapError[$i] = "Y";
			$iapNextStep = "2";
		}
	}
	echo "<form enctype='multipart/form-data' action='?mod=UC&step=".$iapNextStep."' method='post'>";
	$h = IAP_Do_Help(4, 103, 3); // level 4 (Always display), page 103, section 3
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";	
	} else {
		echo "<table style='width:100%'><tr><tr><td width='10%'></td><td width='95%'></td></tr>";
		echo "<tr><td width='5%'></td><td width='95%'>";
		echo "<strong>Step 2 Complete - Fields Mapped. You are ready for step 3.</strong></td></tr>";
		echo "/table>";
	}

	echo "<table><tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	echo "<tr><td width='16%' class='iapFormTitle'>Map</td><td width='42%' class='iapFormTitle'>Database Column</td><td width='42%' class='iapFormTitle'>Your CSV Column</td></tr>";
	echo "<tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	$i = 0;
	$iapStop = FALSE;
	while($iapStop === FALSE) {
		echo "<tr><td width='16%'>";
		if ($iapError[$i] == "Y") {
			echo "<span class=iapError>*</span>";
		}
		if ($iapNextStep == "2") {
			echo "<input type='text' name='colno".strval($i)."' size='3' maxlength='3' class='iapFormTitle' value='".$iapMapping[$i]."'>";
		} else {
			echo $iapMapping[$i];
		}
		echo "</td><td width='42%'>";
		If ($i < count($iapCustCols)) {
			echo $iapCustCols[$i];
		}
		echo "</td><td width='42%'>";
		If ($i < count($iapColNames)) {
			echo strval($i + 1)." - ".$iapColNames[$i];
		}
		echo "</td></tr>";
		$i = $i + 1;
		if ($i >= count($iapCustCols)
		and $i >= count($iapColNames)) {
			$iapStop = TRUE;
		}
	}
	if ($iapNextStep == "3") {
		echo "<tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
		echo "<tr><td colspan='3'><input class='iapFormField' name='clrcust' type='checkbox' autofocus> Check this box to delete existing customers.</td></tr>";
	} 

	echo "<tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	if ($_REQUEST['UserData']['AddBirthdays'] == "Y") {
		echo "<tr><td colspan='3'>New birthdays will be added to your calendar per your company setting.</td></tr>";
	} else {
		echo "<tr><td colspan='3'>New birthdays will NOT be added to your calendar per your company setting.</td></tr>";
	}

	echo "<tr><td width='16%'></td><td width='42%'></td><td width='42%'></td></tr>";
	echo "<tr><td colspan='3'><input type='submit' class=iapButton name='submit' value='Import Data'></td></tr>";

	echo "</table></form><br><br>";

	$iapSave['step'] = $iapNextStep;
	$iapsave['map'] = $iapMapping;
	$iapRet = IAP_Update_Savearea("IAP103UC", $iapSave);
	if ($iapRet < 0) {
		 echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		IAP_Remove_Savearea("IAP103UC");
		return(-1);
	}
}

function IAP_UpldCust_Import_Data($iapSave) {

	$iapJrnl = IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapPrices[0];
	$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
	$iapJrnl['jrnl_date'] = date("Y-m-d");
	$iapJrnl['jrnl_description'] = "Import of Customers Started";
	$iapJrnl['jrnl_type'] = "MI";
	$iapJrnl['jrnl_amount'] = 0;
	$iapJrnl['jrnl_tax'] = 0;
	$iapJrnl['jrnl_shipping'] = 0;
	$iapJrnl['jrnl_mileage'] = 0;
	$iapJrnl['jrnl_expenses'] = 0;
	$iapJrnl['jrnl_exp_explain'] = "";
	$iapJrnl['jrnl_vendor'] = "";
	$iapJrnl['jrnl_comment'] = "Beginning import of an external list of customers at ".date("m/d/Y h:m");
	$iapJrnl['jrnl_detail_key'] = "";
	$iapJrnl['jrnl_changed'] = date("Y-m-d");
	$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapCustFlds = $iapSave['realcols'];
	$iapCustCols = $iapSave['custcols'];
	$iapColNames = $iapSave['cols'];
	$iapMapping = $iapSave['map'];

	if (isset($_REQUEST['clrcust'])) {

		echo "<p>Removing current customers.</p>\n";
		wp_ob_end_flush_all();
		flush();

		IAP_Clear_Customers();
	}

	if ($_REQUEST['UserData']['AddBirthdays'] == "Y") {
		require_once(ABSPATH."Ajax/IAPCalendar/IAPWriteEvent.php");
	}

	if (!(set_time_limit(420))) {
		echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	echo "<p>Here we go!</p>\n";
	wp_ob_end_flush_all();
	flush();

	$handle = fopen("TempFiles/CustomerUpload.csv", "r");
	$x = fgetcsv($handle, 1000, ","); 	// bypass column names
	$iapRecsAdded = 0;
	while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$iapCustomer = IAP_Build_New_Row(array("table" => "cust"));
		$iapCustomer = $iapCustomer[0];
		$i = 0;
		while($i < count($iapCustFlds)) {
			$iapCustIndex = $iapCustFlds[$i];
			if (!(empty($iapMapping[$i]))) {
				$iapMap = $iapMapping[$i];
				if (!(is_numeric($iapMap))) {
					$iapFlds = explode("+", $iapMap);
				} else {
					$iapFlds = array($iapMap);
				}
				foreach($iapFlds as $iapFld) {
					$iapCustomer[$iapCustIndex] = $iapCustomer[$iapCustIndex].$data[$iapFld - 1]." ";
				}
				$iapCustomer[$iapCustIndex] = str_replace("'", "", $iapCustomer[$iapCustIndex]);
				$iapCustomer[$iapCustIndex] = rtrim($iapCustomer[$iapCustIndex]);
			}
			$i = $i + 1;
		}
		$iapCustomer['cust_company'] = $_REQUEST['CoId'];
		echo "Adding ".$iapCustomer['cust_name']."<br>";

		if ($_REQUEST['UserData']['AddBirthdays'] == "Y") {
			if (!empty($iapCustomer['cust_birthday'])) {
				$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
				if ($eeEvents < 0) {
			        echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			        exit;
				}
				$eeEvent = (array) $eeEvents[0];
				$eeEvent['event_account'] = $_REQUEST['CoId'];
				$eeEvent['event_title'] = $iapCustomer['cust_name']."'s Birthday";
				$eeEvent['event_begin'] = date("Y-m-d", strtotime($iapCustomer['cust__birthday']), 0);
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
				$eeEvent['cr_annual_month1A'] = date("m", $eeEvent['event_begin']);
				$eeEvent['cr_annual_dom'] = date("d", $eeEvent['event_begin']);
				$eeEvent['cr_annual_occurs'] = 1;						// Repeat every year
				$eeEvent['cr_until_date'] = "2099-12-31";				// Repeat forever
				$eeEvent['cr_until_count'] = 0;

				$eeEvent['event_id'] = FCWriteEvent($eeEvent, "Y");
		    	if ($eeEvent['event_id'] < 0) {
		        	echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
		    	}
				$iapCustomer['cust_birthday_event'] = $eeEvent['event_id'];
			}
		}
		$iapRecsAdded = $iapRecsAdded + 1;
	        $iapRet = IAP_Update_Data($iapCustomer, "cust");
	        if ($iapRet < 0) {
	            echo "<font color='red'><strong>MAFP INTERNAL ERROR Cannot add the customer record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
				IAP_Remove_Savearea("IAP103UC");
	            exit;
	        }
	} 
	fclose($handle);
	echo "Import Complete! ".strval($iapRecsAdded)." customers added.<br>";
	ob_flush();  // so messages display immediately!
	flush();

	$iapJrnl = IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapPrices[0];
	$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
	$iapJrnl['jrnl_date'] = date("Y-m-d");
	$iapJrnl['jrnl_description'] = "Import of Customers Complete";
	$iapJrnl['jrnl_type'] = "MI";
	$iapJrnl['jrnl_amount'] = 0;
	$iapJrnl['jrnl_tax'] = 0;
	$iapJrnl['jrnl_shipping'] = 0;
	$iapJrnl['jrnl_mileage'] = 0;
	$iapJrnl['jrnl_expenses'] = 0;
	$iapJrnl['jrnl_exp_explain'] = "";
	$iapJrnl['jrnl_vendor'] = "";
	$iapJrnl['jrnl_comment'] = "Import of an external list of customers ended at ".date("m/d/Y h:m")." with ".strval($iapRecsAdded)." customers added";
	$iapJrnl['jrnl_detail_key'] = "";
	$iapJrnl['jrnl_changed'] = date("Y-m-d");
	$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		IAP_Remove_Savearea("IAP103UC");
		exit;
	}

	return;
}


///// Program Start //////

if ($_REQUEST['page_id'] != "103") {
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In IAP ";
    if (isset($_REQUEST['applinfo'])) {
        echo " with applinfo of ".$_REQUEST['applinfo'];
    }
    echo ".<br />";
}

require_once(ABSPATH."IAPServices.php");
$ret = IAP_Program_Start("103", "N");

$iapSave = IAP_Get_Savearea("IAP103UC");
if ($iapSave < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this refrence of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapStep = $iapSave['step'];
$iapReadOnly = IAP_Format_Heading("Customer Import");

if ($_REQUEST['UserData']['Mode'] == "expired") {
	echo "You cannot import more customers because your license has expired.";
	return;
}

switch($iapStep) {
	case "1":
		$iapRet = IAP_UpldCust_Get_File($iapSave);
		if ($iapRet < 0) {
			return;
		}
		break;
	case "2":
		IAP_UpldCust_Map_Cols($iapSave);
		break;
	case "3":
		IAP_UpldCust_Import_Data($iapCustCols, $iapRealCols);
		$iapRet = IAP_Remove_Savearea("IAP103UC");
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}
		$_REQUEST['mod'] = "HP";
		require_once("MyPages/IAPAppHome.php"); // go back to App Home
		return;
		break;
	default:	// initial entry
		$iapRet = IAP_Remove_Savearea("IAP103UC");
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}

		$iapColSQL = "SHOW FULL COLUMNS FROM iap_customers";
		$iapRet = IAPProcessMySQL("select", $iapColSQL);
		if ($iapRet['retcode'] < 0) {
		    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customer columns. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		if ($iapRet['numrows'] == 0){
		    echo "<span class=iapError>iap INTERNAL ERROR: No customer columns found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		$iapCols = (array) $iapRet['data'];
		$iapImportCols = array();
		$iapRealCols = array();
		foreach($iapCols as $c) {
			if ($c['Comment'] != "-<(NOEXPORT)>-") {
				$iapImportCols[] = $c['Comment'];
				$iapRealCols[] = $c['Field'];
			}
		}
		$iapSave = array("step" => "1", "custcols" => $iapImportCols, "realcols" => $iapRealCols, "cols" => NULL);
		$iapRet = IAP_Create_Savearea("IAP103UC", $iapSave);
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}
		IAP_UpldCust_Initial();
		break;				
}

?>