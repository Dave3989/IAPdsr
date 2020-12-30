<?php



/* To activate, this add the following to FCInclude.js where SUBMIT is for pop up
				econtent = econtent+"<a href='"+lhcpath+"/Ajax/LHCCalendar/FCEditEvent.php?LCHA="+document.getElementById("LHCA").value+"&eid="+event.id+"' target='_blank'><input type='submit' value='Edit' /></a></center></body></html>";
*/

echo "<br /><br /><br /><span style='color:red; font-size:120%;'>In LHCCalendar/FCEditEvent.php and should not be!</span><br /><br /><br />";
$eePath = str_replace("\\", "/", dirname(__FILE__));
$eePath = explode("/", $eePath);
for ($i = 0; $i < count($eePath); $i++) {
	$ln = array_pop($eePath);
	if (strpos(strtolower($ln), "litehausconsulting") !== FALSE
	or  strtolower($ln) == "lhc"
	or  strtolower($ln) == "lhcqa") {
		array_push($eePath, $ln);
		$eeP = implode("/", $eePath);
		define('ABSPATH', $eeP."/");
		break;
	}
}

$MyURLIn = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
$MyU = explode("/", $MyURLIn);
for ($i = 0; $i < count($MyU); $i++) {
	$ln = array_pop($MyU);
	if (strpos(strtolower($ln), "litehausconsulting") !== FALSE) {
		array_push($MyU, $ln);
		$MyURL = implode("/", $MyU);
		define('ABSURL', $MyURL."/");
		break;
	}
}

$MyURLIn = $_SERVER["HTTP_REFERER"];
$MyU = explode("/", $MyURLIn);
for ($i = 0; $i < count($MyU); $i++) {
	$ln = array_pop($MyU);
	if (strpos(strtolower($ln), "litehausconsulting") !== FALSE) {
		break;
	}
	$MyApp = $ln;
}
$_REQUEST['running_app'] = $MyApp;

if (!file_exists(ABSPATH.$MyApp.'/wp-config.php')) {
	echo "-10|Cannot find ".$f;
	exit;
}
include_once( ABSPATH.$MyApp.'/wp-config.php');
include_once( ABSPATH.$MyApp.'/wp-load.php');
if ($MyApp == "MAFP") {
// I'll have to do this for each app that uses the calendar
	require_once(ABSPATH. 'MAFP/MAFP_Services.php');
	program_init();
	if (MAFP_Program_Start("NOCHK") < 0) {
		echo "MAFP ERROR This program has to be started by another program. Ending.";
    	return;
	}
	$eeUID = $_REQUEST['MAFPUID'];
	$CSSUrl = "<link rel='stylesheet' href='".ABSURL."/wp-content/themes/FPRSlimauorange/style.css' type='text/css' media='screen' />";
	$LogoUrl = ABSURL."wp-content/themes/FPRSlimauorange/images/logo.gif";
} elseif ($MyApp == "MAEK") {
	require_once(ABSPATH. 'MAEK/MAEK_Services.php');
	program_init();
	if (MAEK_Program_Start("NOCHK") < 0) {
		echo "MAEK ERROR This program has to be started by another program. Ending.";
    	return;
	}
	$eeUID = $_REQUEST['MAEKUID'];
//	$CSSUrl = "<link rel='stylesheet' href='".ABSURL."/wp-content/themes/FPRSlimauorange/style.css' type='text/css' media='screen' />";
//	$LogoUrl = ABSURL."wp-content/themes/FPRSlimauorange/images/logo.gif";
}

if (isset($_REQUEST['eid'])) {
  	LHC_Remove_Savearea("FCEditEvent", $eeUID);
}

$eeEvent = (array) LHC_Get_Savearea("FCEditEvent", $eeUID);
if (!( $eeEvent )) {

	if (isset($_REQUEST['etype'])) {
		$requestType = $_REQUEST['etype'];
		if ($requestType != "edit"
		and $requestType != "delete") {
			return("-2|Invalid Request Type");
		}
	} else {
		return("-1|No Type Passed");
	}

    $_REQUEST['origaction'] = $requestType;

	if (isset($_REQUEST['eid'])) {
		$requestId = $_REQUEST['eid'];
		$eeEvent = LHC_Get_Event_By_Id($requestId);
		if ($eeEvent < 0) {
			return("-11|Cannot retreive the selected event");
		}
	} else {
		return("-1|No Id Passed");
	}


	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key EditEvent.<br />";
	}

    $eeRet = LHC_Create_Savearea("FCEditEvent", $eeEvent, $eeUID);
    if ($eeRet < 0) {
		return("-13|Cannot create savearea for calendar");
    }
    $eePgError = 99; // Fake error
} else {
    if ($eeEvent < 0) {
		return("-13|Cannot retrieve savearea for calendar");
        exit;
    }

    if ($_REQUEST['ret'] <> "eeRet") {
        echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot complete your request because of a program error. [FATAL]<br />Please notify Support and provide this reference of /Action is invalid-reentry/".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
    }
	
    $PageError = 0;
    $TiError = 0;
    $DeError = 0;
    $BDError = 0;
    $BTError = 0;
    $ETError = 0;
    $LiError = 0;
    $LNError = 0;
    $LSTError = 0;
    $LCError = 0;
    $LStError = 0;
    $LZError = 0;
    $RPError = 0;
    $ROError = 0;
    $RDError = 0;
    $REError = 0;
	$eeChanged = "N";

	if (isset($_REQUEST['eetitle'])) {
	    $eeEvent['ev_title'] = $_REQUEST['eetitle'];
	}
    if (empty($eeEvent['ev_title'])) {
        $TiError = 1;
        $PageError = 1;
    }

	if (isset($_REQUEST['eedesc'])) {
	    $eeEvent['ev_desc'] = $_REQUEST['eedesc'];
	}
    if (empty($eeEvent['ev_desc'])) {
        $DeError = 1;
        $PageError = 1;
    }

	if ($_REQUEST['eeallday'] == "checked") {
		$eeEvent['ev_allday'] = "Y";
	} else {
		$eeEvent['ev_allday'] = "N";
	}
	
/*
	$eeRet = LHC_Validate_Date($eeEvent['ev_begin'], $_REQUEST['eebegdate'], "N");
    $eeEvent['ev_begin'] = $eeRet['Value'];
    if ($eeRet['Changed'] == "Y") {
        $eeChanged = "Y";
    }
    if ($eePRet['Error'] == "1") {
        $BDError = 1;
        $PageError = 1;
    } elseif ($eePRet['Error'] == "2") {
        $BDError = 2;
        $PageError = 1;
	} elseif (strtotime($eeEvent['ev_begin']) < strtotime("now")) {
        $BDError = "W";
        $PageError = 1;
	}
	

	$eeRet = LHC_Validate_Date($eeEvent['ev_end'], $_REQUEST['eeenddate'], "N");
    $eeEvent['ev_end'] = $eeRet['Value'];
    if ($eeRet['Changed'] == "Y") {
        $eeChanged = "Y";
    }
    if ($eePRet['Error'] == "1") {
        $EDError = 1;
        $PageError = 1;
    } elseif ($eePRet['Error'] == "2") {
        $EDError = 2;
        $PageError = 1;
	} elseif (strtotime($eeEvent['ev_end']) < strtotime($eeEvent['ev_begin'])) {
        $EDError = "3";
        $PageError = 1;
	}
*/
	$eeRet = LHC_Validate_Time($eeEvent['ev_btime'],$_REQUEST['eebegtime'], "N");
    $eeEvent['ev_btime'] = $eeRet['Value'];
    if ($eeRet['Changed'] == "Y") {
        $eeChanged = "Y";
    }
    if ($eePRet['Error'] == "1") {
        $BTError = 1;
        $PageError = 1;
    } elseif ($eePRet['Error'] == "2") {
        $BTError = 2;
        $PageError = 1;
    }

	$eeRet = LHC_Validate_Time($eeEvent['ev_etime'],$_REQUEST['eeendtime'], "N");
    $eeEvent['ev_etime'] = $eeRet['Value'];
    if ($eeRet['Changed'] == "Y") {
        $eeChanged = "Y";
    }
    if ($eePRet['Error'] == "1") {
        $ETError = 1;
        $PageError = 1;
    } elseif ($eePRet['Error'] == "2") {
        $ETError = 2;
        $PageError = 1;
    }
	if ($BTError == 0
	and $ETError == 0) {
		if ($eeEvent['ev_etime'] < $eeEvent['ev_btime']) {
        	$ETError = 3;
        	$PageError = 1;
		}
	}

	if (isset($_REQUEST['eelink'])) {
		$eeEvent['ev_link'] = $_REQUEST['eelink'];
	}
	if (!empty($eeEvent['ev_link'])) {
		if (!parse_url($eeEvent['ev_link'])) {
	    	$LiError = 1;
	    	$PageError = 1;
		}
	}

	if (isset($_REQUEST['eelname'])) {
		$eeEvent['ev_loc_name'] = $_REQUEST['eename'];
	}

	if (isset($_REQUEST['eelstrt'])) {
		$eeEvent['ev_loc_street'] = $_REQUEST['eestrt'];
	}

	if (isset($_REQUEST['eelcity'])) {
		$eeEvent['ev_loc_city'] = $_REQUEST['eecity'];
	}

	if (isset($_REQUEST['eelstate'])
	and $_REQUEST['eelstate'] != "--") {
		$eeEvent['ev_loc_state'] = $_REQUEST['eestate'];
	}

	if (isset($_REQUEST['eelzip'])) {
		$eeEvent['ev_loc_zip'] = $_REQUEST['eezip'];
	}

// ------------- Will have to do recurring fields here -------------------- 

    if ($PageError == 0) {
		if ($eeEvent['ev_allday'] == "Y") {
			$eeEvent['ev_btime'] = "00:00:00";
			$eeEvent['ev_etime'] = "23:59:59";
		}
		$ds = date_parse($eeEvent['ev_begin']." ".$eeEvent['ev_btime']);
		$gs = gmdate("H, i, s, M, d, Y", mktime($ds['hour'], $ds['minute'], $ds['second'], $ds['month'], $ds['day'], $ds['year']));
		$gst = gmmktime($gs);
		$eeEvent['ev_start_timestamp'] = $gst;

		$de = date_parse($eeEvent['ev_end']." ".$eeEvent['ev_etime']);
		$ge = gmdate("H, i, s, M, d, Y", mktime($de['hour'], $de['minute'], $de['second'], $de['month'], $de['day'], $de['year']));
		$get = gmmktime($ge);
		$eeEvent['ev_end_timestamp'] = $gst;
		$eeRet = LHC_Update_Data($eeEvent, "cal");
    	if ($eeRet < 0) {
        	echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: Error updating calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
            exit;
    	}
		if ($eeEvent['status'] == "NEW") {
			$eeUpdateMsg = "Event Successfully Added";
	        $eeEvent['status'] = "UPDATE";   // get rid of NEW after update
		} else {
			$eeUpdateMsg = "Event Successfully Updated";
		}
        if (LHC_Remove_Savearea("FCEditEvent", $eeUID) < 0) {
            echo "<span style='color:red;'><strong>LHC INTERNAL ERROR Cannot update the calendar savearea. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br />";
            exit;
        }

		$eePgError = 91;
	}
}

$eeStOpts = "";
$eeAllStates = LHC_Get_All_States();
if ($eeAllStates < 0) {
    echo "<span style='color:red;'><strong>LHC INTERNAL ERROR Cannot get the states names because of database error [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</strong></span><br />";
    exit;
} else {
    foreach ($eeAllStates as $eeState) {
        $eeState = (array) $eeState;  // must cast as an array to work here!
        $eeStOpts = $eeStOpts."<option value='".$eeState['st_abbrev']."'";
        if ($eeState['st_abbrev'] == $eeUnit['unit_state']) {
            $eeStOpts = $eeStOpts." selected";
        }
        $eeStOpts = $eeStOpts.">".strtoupper($eeState['st_abbrev'])."</option>";
    }
}

/*
event_account			smallint(6)	// LHC
event_id				int(11)
event_begin				date
event_end				date
event_title				varchar(30)
event_desc				text
event_btime				time
event_etime				time
event_loc_name 			varchar(50)	// LHC
event_loc_street		varchar(50)	// LHC
event_loc_city			varchar(30)	// LHC
event_loc_state			char(2)		// LHC
event_loc_zip			varchar(10)	// LHC
event_recur				char(1)
event_repeats			int(3)
event_recur_period		char(1)		// LHC
event_recur_occur		tinyint(4)	// LHC
event_recur_sunday		char(1)		// LHC
event_recur_monday		char(1)		// LHC
event_recur_tuesday		char(1)		// LHC
event_recur_wednesday	char(1)		// LHC
event_recur_thursday	char(1)		// LHC
event_recur_friday		char(1)		// LHC
event_recur_saturday	char(1)		// LHC
event_recur_last_date	date		// LHC
event_author			smallint(6) 
event_category			bigint(20) 
event_link				text

*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Event Addition/Maintenance</title>
<?php echo $CSSUrl; ?>
<script type='text/javascript' src='<?php echo LHCURL; ?>Ajax/lhc_expcoll.js'></script>
<!--
/***********************************************
* Jason's Date Input Calendar- By Jason Moon http://calendar.moonscript.com/dateinput.cfm
* Script featured on and available at http://www.dynamicdrive.com
* Keep this notice intact for use.
***********************************************/
<script type="text/javascript" src="calendarDateInput.js"></script>
-->
<script type='text/javascript' src='<?php echo ABSURL; ?>calendarDateInput.js'></script>

<script type='text/javascript'>
function toggleTimes(cbName){
	if(cbName.checked){
		var t = "h";
	} else {
		var t = "d";
	}
	var e=document.getElementById("eebegtimelabel");
	if(e){
		if(t=="d"){
			e.style.display="block"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eebegtimeinput");
	if(e){
		if(t=="d"){
			e.style.display="block"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eeendtimelabel");
	if(e){
		if(t=="d"){
			e.style.display="block"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eeendtimeinput");
	if(e){
		if(t=="d"){
			e.style.display="block"
		} else {
			e.style.display="none"
		}
	}
}
function toggleOpts(t){
	var end="N"
	var e=document.getElementById("eedailyoptions");
	if(e){
		if(t=="d"){
			e.style.display="block"
			end="Y"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eeweeklyoptions");
	if(e){
		if(t=="w"){
			e.style.display="block"
			end="Y"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eemonthlyoptions");
	if(e){
		if(t=="m"){
			e.style.display="block"
			end="Y"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eeannualoptions");
	if(e){
		if(t=="a"){
			e.style.display="block"
			end="Y"
		} else {
			e.style.display="none"
		}
	}
	var e=document.getElementById("eeendoptions");
	if(e) {
		if(end=="Y"){
			e.style.display="block"
		} else {
			e.style.display="none"
		}
	}
}

function iAmDone() {
	window.opener.reloadCalendar();
	self.close();
	return false;	
}
</script>
</head>
<body>
<div id="wrap">
<div id="header"><h1><a href=#></a></h1></div>
<div class="left"> 
The following fields are required:
<br /><br /><em>
Title<br />
Description<br /></em>
<br />
The remainder of the information will show for the event if it is entered.
</div>
<div class="middle">

<?php
echo "<center><h2>".$_REQUEST['sec_orgname']."</h2>";
echo "<span style='font-size: 115%;'><strong><em>Add/Update An Event</em></strong></span></center><br />";

	if ($PageError <> 0
	and $PageError < 90) {

		echo "<table>";
		echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Errors were detected. Please make corrections</strong></span></td></tr>";
		echo "<tr></tr>";
		if ($TiError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Title cannot be blank!</strong></span></td></tr>";	
		}
		if ($DeError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Description cannot be blank!</strong></span></td></tr>";
		}
		if ($BDError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Beginning Date must be entered!</strong></span></td></tr>";
	    }
		if ($BDError == 2) {

	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Beginning Date is invalid!</strong></span></td></tr>";
		}
		if ($BDError == "W") {
	        echo "<tr><td width='50'></td><td width='600'><font color='#FFD633'><strong>WARNING: Beginning Date is less than today?</strong></span></td></tr>";
		}
		if ($EDError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date must be entered!</strong></span></td></tr>";
	    }
		if ($EDError == 2) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date is invalid!</strong></span></td></tr>";
		}
		if ($EDError == 3) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date is less yanh the Beginning Date!</strong></span></td></tr>";
		}
		if ($BTError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Beginning Time must be entered!</strong></span></td></tr>";
	    }
		if ($BTError == 2) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Beginning Time is invalid!</strong></span></td></tr>";
		}
		if ($ETError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Time must be entered!</strong></span></td></tr>";
	    }
		if ($ETError == 2) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Time is invalid!</strong></span></td></tr>";
	    }
		if ($ETError == 3) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Time is less than Beginning Time!</strong></span></td></tr>";
		}
		if ($LiError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Website is invalid!</strong></span></td></tr>";
	    }
	}

	if ($eePgError == 91) {
	    echo "<br /><center><span style='color:darkgreen; font-size:115%;'><strong>".$eeUpdateMsg."</strong></span></center><br />";
	}

	echo "<form action='?ret=eeRet' method='POST'>";
	echo "<input type=hidden name='eeorigparms' id='eeorigparms' value='a=".$_REQUEST['a'].",r=".$_REQUEST['r'].",v=".$_REQUEST['v'].",c=".$_REQUEST['c']."' />";
	echo "<table>";
	echo "<tr><td width='100'>Title:</td><td width='500'><input id='eetitle' name='eetitle' type='text' size='50' maxlength='50' value='".$eeEvent['ev_title']."' /></td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td width='100' valign='top'>Description:</td><td width='500'><textarea cols='50' rows='5' id='eedesc' name='eedesc'>".$eeEvent['eventdesc']."</textarea></td></tr>";

	if ($eeEvent['ev_allday'] == "Y") {
		$d = "none";
		$a = " checked";
	} else {
		$d = "block";
		$a = "";
	}
	echo "<tr><td width='100'>All Day Event:</td><td width='500'><input type='checkbox' id='eeallday' name='eeallday' onclick=\"return toggleTimes(this);\"".$a." /></td></tr>";

	echo "<tr><td colspan='2'>";
		echo "<table>";
//		echo "<tr><td width='100'>Begin Date:</td><td width='150'><script>DateInput('eebegdate', true, 'YYYY-MON-DD', '".$eeEvent['ev_begin']."')</script></td>";
		echo "<tr><td width='100'>Begin Date:</td><td width='150'>".date("D M j, Y ", strtotime($eeEvent['ev_begin']))."</td>";
		echo "<td width='20'></td>";

		echo "<td width='100'><div id='eebegtimelabel' style='display:".$d.";'>Begin Time:</div></td><td width='160'><div id='eebegtimeinput' style='display:".$d.";'><input id='eebegtime' name='eebegtime' value='".date("H:i",strtotime($eeEvent['ev_btime']))."' size='6' maxlength='6' /></div></td>";
		echo "<td width='20'></td></tr>";

//		echo "<tr><td width='100'>End Date:</td><td width='150'><script>DateInput('eeenddate', true, 'YYYY-MON-DD', '".$eeEvent['ev_end']."')</script></td>";
		echo "<tr><td width='100'>End Date:</td><td width='150'>".date("D M j, Y ", strtotime($eeEvent['ev_end']))."</td>";
		echo "<td width='20'></td>";
		echo "<td width='100'><div id='eeendtimelabel' style='display:".$d.";'>End Time:</div></td><td width='160'><div id='eeendtimeinput' style='display:".$d.";'><input id='eeendtime' name='eeendtime' value='".date("H:i",strtotime($eeEvent['ev_etime']))."' size='6' maxlength='6' /></div></td>";
		echo "<td width='20'></td></tr></table>";

	echo "</td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td width='100'>Website:</td><td width='500'><input id='eelink' name='eelink' type='text' size='50' maxlength='50' value='".$eeEvent['ev_link']."' /></td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td colspan='2'>Location</td>";
	echo "<tr><td colspan='2'>";	
		echo "<table><tr>";
		echo "<td width='20'></td><td width='80'>Name:</td><td width='500'><input id='eelname' name='eelname' type='text' size='50' maxlength='50' value='".$eeEvent['ev_loc_name']."' /></td></tr>";
		echo "<td width='20'></td><td width='80'>Street:</td><td width='500'><input id='eelstrt' name='eelstrt' type='text' size='50' maxlength='50' value='".$eeEvent['ev_loc_street']."' /></td></tr>";
		echo "</table>";
		echo "<table><tr>";
		echo "<td width='20'></td><td width='80'>City:</td><td width='200'><input id='eelcity' name='eelcity' type='text' size='30' maxlength='30' value='".$eeEvent['ev_loc_city']."' /></td>";
		echo "<td width='20'></td><td width='35'>State:</td><td width='50'><select name='eestate'><Option Value='--'>--".$eeStOpts."</select></td>";
		echo "<td width='20'></td><td width='35'>Zip:</td><td width='100'><input id='eelzip' name='eelzip' type='text' size='10' maxlength='10' value='".$eeEvent['ev_loc_zip']."' /></td>";
		echo "</tr></table>";
	echo "</td></tr>";

/*
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td width='100'><input type='checkbox' id='eerecur' name='eerecurdesc' onclick=\"return toggleMe('eeRecursOpts')\" />&nbsp;&nbsp;&nbsp;Check If This Is A Recurring Event.</td><td width='500'></td></tr></table>";

	if ($eeEvent['ev_recur'] == "Y" ) {
		$r = "block ";
	//	echo " checked";
	} else {
		$r = "none ";
	}

// Building options for recurring terms
	$RecWks = "<option value='1st'>1st</option>".
			  "<option value='2nd'>2nd</option>".
			  "<option value='3rd'>3rd</option>".
			  "<option value='4th'>4th</option>".
			  "<option value='last'>Last</option>";
	$RecDays = "<option value='sunday'>Sunday</option>".
	 		   "<option value='monday'>Monday</option>".
			   "<option value='tuesday'>Tuesday</option>".
			   "<option value='wednesday'>Wednesday</option>".
			   "<option value='thursday'>Thursday</option>".
			   "<option value='friday'>Friday</option>".
			   "<option value='saturday'>Saturday</option>";
	$RecMths = "<option value'jan'>January</option>".
			   "<option value'feb'>February</option>".
			   "<option value'mar'>March</option>".
			   "<option value'apr'>April</option>".
			   "<option value'may'>May</option>".
			   "<option value'jun'>June</option>".
			   "<option value'jul'>July</option>".
			   "<option value'aug'>August</option>".
			   "<option value'sep'>September</option>".
			   "<option value'oct'>October</option>".
			   "<option value'nov'>November</option>".
			   "<option value'dec'>december</option>";
	$RecDayWk = date("w", $eeEvent['ev_begin']);
	$RecDayMo = date("j", $eeEvent['ev_begin']);
	$RecDayYr = date("z", $eeEvent['ev_begin']);
	
	echo "<div id='eeRecursOpts' style='display:".$r."'>";
	echo "<table>";
	echo "<tr><td width='20'></td><td width='130'></td><td width='500'></td></tr>";
	echo "<tr><td width='20'></td><td colspan='2'>Reoccurence Criteria</td></tr>";
	
	echo "<tr><td colspan='2'></td><td width='500'>";
		echo "<table><tr><td wdith='100' valign='top'>";
		echo "<div id='eeRecurOptions'>";
			echo "<input id='eerecuropt_1' name='eerecuropt' type='radio' value='d' ".
				 "onclick=\"return toggleOpts('d')\" />".
				 "<label for='eerecuropt_1'>Daily</label><br />";
	
			echo "<input id='eerecuropt_2' name='eerecuropt' type='radio' value='w' ".
				 "onclick=\"return toggleOpts('w')\" />".
				 "<label for='eerecuropt_2'>Weekly</label><br />";

			echo "<input id='eerecoropt_3' name='eerecuropt' type='radio' value='m' ".
				 "onclick=\"return toggleOpts('m')\" />".
				 "<label for='eerecuropt_3'>Monthly</label><br />";
	
			echo "<input id='eerecoropt_4' name='eerecuropt' type='radio' value='a' ".
				 "onclick=\"return toggleOpts('a')\" />".
				 "<label for='eerecuropt_4'>Annual</label><br />";
	
		echo "</div></td><td width='25'></td><td width='525' valign='top'>";
	
// Daily options
		if ($eeEvent['ev_recur_period'] == "D") {
			$d = "block ";
		//	echo " checked";
		} else {
			$d = "none ";
		}
		echo "<div id='eedailyoptions' style='display:".$d."'>Daily Options<br />";
			echo "<input id='eedailyopt_1' name='eedailyopt' type='radio' value='d1' />";
				echo "<label for='eedailyopt_1'>Every ".
					 "<input type='text' name='eeday_1A' size=3 maxlength=3 value='".$eeEvent['eventARecurPeriods']."' />".
					 " day(s)</label><br />";
			echo "<input id='eedailyopt_2' name='eedailyopt' type='radio' value='d2' />";
				echo "<label for='eedailyopt_2'>Every weekday (no weekends)</label><br />";
		echo "</div>";
	
// Weekly options
		if ($eeEvent['ev_recur_period'] == "W") {
			$w = "block ";
		//	echo " checked";
		} else {
			$w = "none ";
		}
		echo "<div id='eeweeklyoptions' style='display:".$w."'>Weekly Options<br />";
			echo "<input id='eeweeklyopt_1' name='eeweeklyopt' type='radio' value='w1' />";
				echo "<label for='eeweeklyopt_1'>On ".
					 "<select name='eewk_1A>.$RecDays.</select> ".
					 " every ".
					 "<input type='text' name='eewk_1B' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods']."' />".
					 " week(s)</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other week, 3=every third week, etc...</span><br />";
			echo "<input id='eeweeklyopt_2' name='eeweeklyopt' type='radio' value='w3' />";
				echo "<label for='eeweeklyopt_2'>Another option</label><br />";
		echo "</div>";
	
// Monthly options
		if ($eeEvent['ev_recur_period'] == "M") {
			$m = "block ";
		//	echo " checked";
		} else {
			$m = "none ";
		}
		echo "<div id='eemonthlyoptions' style='display:".$m."'>Monthly Options<br />";
			echo "<input id='eemonthlyopt_1' name='eemonthlyopt' type='radio' value='m1' />";
				echo "<label for='eemonthlyopt_1'>On day ".
					 "<input type='text' name='eewk_1A' size=2 maxlength=2 value='".$RecDayMo."' />";
					 " of every month every ".
					 "<input type='text' name='eewk_1B' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods']."' />".
					 " months</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other month, 3=every third month, etc...</span><br />";
			echo "<input id='eemonthlyopt_2' name='eemonthlyopt' type='radio' value='m2' />";
				echo "<label for='eemonthlyopt_2'>On the ".
					 "<select name='eemo_2A'>".$RecWks."</select>".
					 "<select name='eemo_2B'>".$RecDays."</select>".
					 " of  every ".
					 "<input type='text' name='eemo_2C' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods']."' />".
					 " months</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other month, 3=every third month, etc...</span><br />";
			echo "<input id='eemonthlyopt_3' name='eemonthlyopt' type='radio' value='m3' />";
				echo "<label for='eemonthlyopt_3'>Another option</label><br />";
		echo "</div>";
	
// Annual options
		if ($eeEvent['ev_recur_period'] == "A") {
			$a = "block ";
		//	echo " checked";
		} else {
			$a = "none ";
		}
		echo "<div id='eeannualoptions' style='display:".$a."'>Anual Options<br />";
			echo "<input id='eeannualopt_1' name='eeannualopt' type='radio' value='a1' />";
				echo "<label for='eeannualopt_1'>On day ".
					 "<input type='text' name='eean_1A' size=2 maxlength=2 value='".$eeEvent['eventARecurCount']."' />".
					 " every ".
					 "<input type='text' name='eean_1B' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods']."' />".
					 " years.</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other year, 3=every third year, etc...</span><br />";
			echo "<input id='eeannualopt_2' name='eeannualopt' type='radio' value='a2' />";
				echo "<label for='eeannualopt_2'>On the ".
					 "{day #}".
					 " of ".
					 "<select name='eean_2A'>".$RecMths."</option></select>".
					 " every ".
					 "<input type='text' name='eean_2B' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods']."' />".
					 " years.</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other year, 3=every third year, etc...</span><br />";
	
			echo "<input id='eeannualopt_3' name='eeannualopt' type='radio' value='a3' />";
				echo "<label for='eeannualopt_3'>On the ".
					 "<select name='eean_3A'>".$RecWks."</select> ".
					 "<select name='eean_3B'>.$RecDays.</select> ".
					 " every ".
					 "<input type='text' name='eean_3C' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods'].
					 " years</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other year, 3=every third year, etc...</span><br />";
	
			echo "<input id='eeannualopt_4' name='eeannualopt' type='radio' value='a4' />";
				echo "<label for='eeannualopt_4'>On ".
					 "{month/day}".
					 " of every ".
					 "<input type='text' name='eean_4B' size=2 maxlength=2 value='".$eeEvent['eventARecurPeriods'].
					 " years</label><br />".
					 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other year, 3=every third year, etc...</span><br />";
	
		echo "</div><br /><br />";
	
// End of occurs options
		if ($eeEvent['ev_recur_period'] == "A" 
		or  $eeEvent['ev_recur_period'] == "D" 
		or  $eeEvent['ev_recur_period'] == "M"
		or  $eeEvent['ev_recur_period'] == "W") {
			$e = "block ";
		//	echo " checked";
		} else {
			$e = "none ";
		}
		echo "<div id='eeendoptions' style='display:".$e."'>";
			echo "<br /><br />Occurs Until:<br />";
			echo "<input type='radio' name='eerecend' value='n' />No End<br /><br />";
			echo "<input type='radio' name='eerecend' value='o' />";
				echo "<input type='text' name='eerecocc' id='eerecocc' size=4 maxlength=4 /> occurences<br /><br />";
			echo "<table><tr><td><input type='radio' name='eerecend' value='a' />After </td>".
				 "<td> <script>DateInput('enddate', true, 'YYYY-MON-DD', '".$eeEvent['ev_begin']."')</script></td></tr></table><br /><br />";
		echo "</div>";
		echo "</td></tr></table>";
	
	echo "</td></tr>";
	echo "<tr><td width='20'></td><td width='130'></td><td width='500'></td></tr>";
	echo "<tr><td colspan='2'></td><td width='500'>";
	
	echo "</td></tr>";
	echo "</table></div>";
*/

	echo "</table><br /><table>";
	echo "<tr><td width='200'><center><input type='submit' name='submitevent' value='Submit' /></center></td>";
	if ($eePgError == 91) {
		echo "<td width='50'></td><td width='200'><center><input type='submit' name='alldone' value='Close Window and Exit' onclick=\"iAmDone();\"' /></center></td>";
	}
	echo "</tr></table></div></body></html>";
?>