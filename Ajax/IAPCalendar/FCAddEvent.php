<?php

$a = explode("/", $_SERVER["PHP_SELF"]);
array_pop($a);
$eePath = implode("/", $a);
$eeURL = $eePath."/";
$eeApp = array_pop($a);
$eeLHCPath = implode("/", $a);
$eeLHCURL = $eeLHCPath."/";
$eeAbspath = ABSPATH;

if ($eeApp == "MAFP") {
// I'll have to do this for each app that uses the calendar

 	if ($_REQUEST['action'] == "NEW"
	or  $_REQUEST['action'] == "607ret") {
		$eePage = "607";
		$eeFormAction = $eeLHCURL."Ajax/FCAddEvent.php";
		$eeFormRet = "607ret";
	} elseif ($_REQUEST['action'] == "selected"
		  or  $_REQUEST['action'] == "616Eret") {
		$eePage = "616";
		$eeFormAction = $eeLHCURL."Ajax/FCAddEvent.php";
		$eeFormRet = "616Eret";
	} else {
        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Invalid Action passed to FCAddEvent. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
	}
	require_once(ABSPATH. 'MAFP_Services.php');
	if (MAFP_Program_Start($eePage, "Y") < 0) {
		return;
	}
	$eeUID = $_REQUEST['MAFPUID'];

} elseif ($eeApp == "MAEK") {

 	if ($_REQUEST['action'] == "NEW"
	or  $_REQUEST['action'] == "aeret") {
		$eePage = "addevent";
		$eeFormAction = $eeLHCURL."Ajax/FCAddEvent.php";
		$eeFormRet = "aeret";
	} elseif ($_REQUEST['action'] == "selected"
		  or  $_REQUEST['action'] == "eeEret") {
		$eePage = "editevent";
		$eeFormAction = $eeLHCURL."Ajax/FCAddEvent.php";
		$eeFormRet = "eeEret";
	} else {
        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Invalid Action passed to FCAddEvent. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
	}
	require_once(ABSPATH. 'MAEK_Services.php');
	if (MAEK_Program_Start($eePage, "Y") < 0) {
		return;
	}
	$eeUID = $_REQUEST['MAEKUID'];
}

if ($_REQUEST['action'] != $eeFormRet) {
	$fun = $eeApp."_Remove_Savearea";
    $fun($_REQUEST['runningapp'].$eePage, $eeUID);
}

$fun = $eeApp."_Get_Savearea";
$eeEvent = (array) $fun($_REQUEST['runningapp'].$eePage, $eeUID);
if (!( $eeEvent )) {

    $_REQUEST['origaction'] = $_REQUEST['action'];

	if ($_REQUEST['action'] == "NEW") {
		$fun = $eeApp."_Build_New_Row";
		$eeEvents = (array) $fun(array(table => "cal"));
		if ($eeEvents < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeEvent = (array) $eeEvents[0];
   	    $eeEvent['event_account'] = $_REQUEST['sec_acct'];
   	    $eeEvent['event_begin'] = date("Y-m-d", current_time("timestamp", 0));
   	    $eeEvent['event_end'] = $eeEvent['event_begin'];
		$eeEvent['event_btime'] = "00:00";
		$eeEvent['event_etime'] = "00:00";
		$eeEvent['event_recur'] = "S";
		$eeEvent['event_allday'] = "N";
		$eeEvent['event_author'] = $_REQUEST['sec_client'];
		$es = $eeEvent['status'];
		$fun = $eeApp."_Build_New_Row";
		$eeRepeats = (array) $fun(array(table => "calrep"));
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeRepeat = (array) $eeRepeats[0];
		$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
		$eeEvent = array_merge($eeEvent, $eeRepeat);
		$eeEvent['status'] = $es;
	} else {
		$eeEvent = LHC_Get_Event_By_Id($_REQUEST['eventid'], "N");
		if ($eeEvent < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot retreive the selected event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		if ($eeEvent == NULL) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot retreive the selected event - event not found. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$es = $eeEvent['status'];
		$eeRepeats = LHC_Get_Repeating($_REQUEST['eventid']);
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot retreive the selected event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeRepeat = (array) $eeRepeats[0];
		$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
		$eeEvent = array_merge($eeEvent, $eeRepeat);
		$eeEvent['status'] = $es;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key EditEvent.<br />";
	}

	$fun = $eeApp."_Create_Savearea";
    $eeRet = $fun($_REQUEST['runningapp'].$eePage, $eeEvent, $eeUID);
    if ($eeRet < 0) {
        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot create savearea for calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;    
	}
    $eePgError = 99; // Fake error
} else {
    if ($eeEvent < 0) {
        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot retrieve savearea for calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
	}

    if ($_GET['action'] <> $eeFormRet) {
        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot complete your request because of a program error. [FATAL]<br />Please notify Support and provide this reference of /Action is invalid-reentry/".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
    }
	if ($_REQUEST['deleteevent'] == "Delete") {
		$fun = $eeApp."_Delete_Row";
		$eeRet = $fun($eeEvent, "cal");
		if ($eeRet < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot delete the event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		if ($eeEvent['event_recur'] == "Y") {
			$fun = $eeApp."_Delete_Row";
			$eeRet = $fun($eeEvent, "calrep");
			if ($eeRet < 0) {
	    	    echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot delete the event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        	exit;
			}
		}

		$fun = $eeApp."_Build_New_Row";
		$eeEvents = (array) $fun(array(table => "cal"));
		if ($eeEvents < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot build a new event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeEvent = (array) $eeEvents[0];
   	    $eeEvent['event_account'] = $_REQUEST['sec_acct'];
   	    $eeEvent['event_begin'] = date("Y-m-d", current_time("timestamp", 0));
   	    $eeEvent['event_end'] = $eeEvent['event_begin'];
		$eeEvent['event_btime'] = "00:00";
		$eeEvent['event_etime'] = "00:00";
		$eeEvent['event_recur'] = "S";
		$eeEvent['event_allday'] = "N";
		$eeEvent['event_author'] = $_REQUEST['sec_client'];
		$es = $eeEvent['status'];

		$fun = $eeApp."_Build_New_Row";
		$eeRepeats = (array) $fun(array(table => "calrep"));
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeRepeat = (array) $eeRepeats[0];
		$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
		$eeEvent = array_merge($eeEvent, $eeRepeat);
		$eeEvent['status'] = $es;

		$eeUpdateMsg = "Event Successfully Deleted";
		$fun = $eeApp."_Update_Savearea";
        if ($fun($_REQUEST['runningapp'].$eePage, $eeEvent, $eeUID) < 0) {
            echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR Cannot update the calendar savearea. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br />";
            exit;
        }
		$eePgError = 91;
	} else {		
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
	    $DOptError = 0;
	    $DOError = 0;
	    $WOptError = 0;
	    $WOError = 0;
	    $MOptError = 0;
	    $MDError = 0;
	    $MOError = 0;
	    $AOptError = 0;
	    $AOError = 0;
	    $UDError = 0;
	    $UIError = 0;
		$eeChanged = "N";

		if (isset($_REQUEST['eetitle'])) {
		    if ($eeEvent['event_title'] != $_REQUEST['eetitle']) {
			    $eeEvent['event_title'] = $_REQUEST['eetitle'];
		        $eeChanged = "Y";
			}
		}
	    if (empty($eeEvent['event_title'])) {
	        $TiError = 1;
	        $PageError = 1;
	    }

		if (isset($_REQUEST['eedesc'])) {
			if ($eeEvent['event_desc'] != $_REQUEST['eedesc']) {
			    $eeEvent['event_desc'] = $_REQUEST['eedesc'];
		        $eeChanged = "Y";
			}
		}
	    if (empty($eeEvent['event_desc'])) {
	        $DeError = 1;
	        $PageError = 1;
	    }
	
		if ($_REQUEST['eeallday'] == "on") {
			$eeEvent['event_allday'] = "Y";
		} else {
			$eeEvent['event_allday'] = "N";
		}

		$eeRet = LHC_Validate_Date($eeEvent['event_begin'], $_REQUEST['eebegdate'], "N");
	    $eeEvent['event_begin'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $BDError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $BDError = 2;
	        $PageError = 1;
		} elseif (strtotime($eeEvent['event_begin']) < strtotime(date("Y/m/d")." 00:00:01")) {
	        $BDError = 3;
		}

		$eeRet = LHC_Validate_Date($eeEvent['event_end'], $_REQUEST['eeenddate'], "N");
	    $eeEvent['event_end'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $EDError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $EDError = 2;
	        $PageError = 1;
		} elseif (strtotime($eeEvent['event_end']) < strtotime($eeEvent['event_begin'])) {
	        $EDError = "3";
	        $PageError = 1;
		}

		$eeRet = LHC_Validate_Time($eeEvent['event_btime'],$_REQUEST['eebegtime'], "N");
	    $eeEvent['event_btime'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $BTError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $BTError = 2;
	        $PageError = 1;
	    }

		$eeRet = LHC_Validate_Time($eeEvent['event_etime'],$_REQUEST['eeendtime'], "N");
	    $eeEvent['event_etime'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $ETError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $ETError = 2;
	        $PageError = 1;
	    }
		if ($BTError == 0
		and $ETError == 0) {
			if ($eeEvent['event_etime'] < $eeEvent['event_btime']) {
	        	$ETError = 3;
	        	$PageError = 1;
			}
		}

		if (isset($_REQUEST['eelink'])) {
			if ($eeEvent['event_link'] != $_REQUEST['eelink']) {
				$eeEvent['event_link'] = $_REQUEST['eelink'];
		        $eeChanged = "Y";
			}
		}
		if (!empty($eeEvent['event_link'])) {
			if (!parse_url($eeEvent['event_link'])) {
		    	$LiError = 1;
		    	$PageError = 1;
			}
		}

		if (isset($_REQUEST['eelname'])) {
			if ($eeEvent['event_loc_name'] != $_REQUEST['eelname']) {
				$eeEvent['event_loc_name'] = $_REQUEST['eelname'];
		        $eeChanged = "Y";
			}
		}
	
		if (isset($_REQUEST['eelstrt'])) {
			if ($eeEvent['event_loc_street'] != $_REQUEST['eelstrt']) {
				$eeEvent['event_loc_street'] = $_REQUEST['eelstrt'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelcity'])) {
			if ($eeEvent['event_loc_city'] != $_REQUEST['eelcity']) {
				$eeEvent['event_loc_city'] = $_REQUEST['eelcity'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelstate'])
		and $_REQUEST['eelstate'] != "--") {
			if ($eeEvent['event_loc_state'] != $_REQUEST['eelstate']) {
				$eeEvent['event_loc_state'] = $_REQUEST['eelstate'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelzip'])) {
			if ($eeEvent['event_loc_zip'] != $_REQUEST['eelzip']) {
				$eeEvent['event_loc_zip'] = $_REQUEST['eelzip'];
		        $eeChanged = "Y";
			}
		}

// ------------- Repeating fields here -------------------- 
		if ($_REQUEST['eerecur'] != "on") {
			$eeEvent['event_recur'] = "N";
			$eeEvent['cr_type'] = NULL;
		} else {
			$eeEvent['event_recur'] = "Y";
			if (isset($_REQUEST['eerecuropt'])) {
				$eeEvent['cr_type'] = $_REQUEST['eerecuropt'];
			}
			switch($eeEvent['cr_type']) {
				case "D":
					if (isset($_REQUEST['eedailyopt'])) {
						$eeEvent['cr_daily_option'] = $_REQUEST['eedailyopt'];
					}
					switch($eeEvent['cr_daily_option']) {
						case "d1":
						case "d2":
							break;
						default:
							$DOptError = 1;
							$PageError = 1;
					}
					$eeRet = LHC_Validate_Nonblank($eeEvent['cr_daily_occurs'], $_REQUEST['eeday_occ'], "Y");
					$eeEvent['cr_daily_occurs'] = $eeRet['Value'];
					if ($eeRet['Changed'] == "Y") {
						$eeChanged = "Y";
					}
					if ($eeRet['Error'] == "Y") {
						$eeEvent['cr_daily_occurs'] = 1;
					} elseif ($eeRet['Error'] == "2") {
						$DOError = 2;
						$PageError = 1;
					}
					break;

				case "W":
					if (isset($_REQUEST['eeweeklyopt'])) {
						$eeEvent['cr_weekly_option'] = $_REQUEST['eeweeklyopt'];
					}
					switch($eeEvent['cr_weekly_option']) {
						case "w1":
							$eeEvent['cr_weekly_dow'] = $_REQUEST['eewk_1A'];
							break;
						case "w2":
							break;
						default:
							$WOptError = 1;
							$PageError = 1;
					}
					$eeRet = LHC_Validate_Nonblank($eeEvent['cr_weekly_occurs'], $_REQUEST['eewk_occ'], "Y");
					$eeEvent['cr_weekly_occurs'] = $eeRet['Value'];
					if ($eeRet['Changed'] == "Y") {
						$eeChanged = "Y";
					}
					if ($eeRet['Error'] == "Y") {
						$eeEvent['cr_weekly_occurs'] = 1;
					} elseif ($eeRet['Error'] == "2") {
						$WOError = 2;
						$PageError = 1;
					}
					break;

				case "M":
					if (isset($_REQUEST['eemonthlyopt'])) {
						$eeEvent['cr_monthly_option'] = $_REQUEST['eemonthlyopt'];
					}
					switch($eeEvent['cr_monthly_option']) {
						case "m1":
							$eeRet = LHC_Validate_Nonblank($eeEvent['cr_monthly_daynum'], $_REQUEST['eemo_1A'], "Y");
							$eeEvent['cr_monthly_daynum'] = $eeRet['Value'];
							if ($eeRet['Changed'] == "Y") {
								$eeChanged = "Y";
							}
							if ($eeRet['Error'] == "Y") {
								$eeEvent['cr_monthly_daynum'] = 0;
							} elseif ($eeRet['Error'] == "2") {
								$MDError = 2;
								$PageError = 1;
							}
							break;
						case "m2":
							$eeEvent['cr_monthly_wknum'] = $_REQUEST['eemo_2A'];
							$eeEvent['cr_monthly_dow'] = $_REQUEST['eemo_2B'];
							break;
						case "m3":
							break;
						default:
							$MOptError = 1;
							$PageError = 1;
					}
					$eeRet = LHC_Validate_Nonblank($eeEvent['cr_monthly_occurs'], $_REQUEST['eemth_occ'], "Y");
					$eeEvent['cr_monthly_occurs'] = $eeRet['Value'];
					if ($eeRet['Changed'] == "Y") {
						$eeChanged = "Y";
					}
					if ($eeRet['Error'] == "Y") {
						$eeEvent['cr_monthly_occurs'] = 1;
					} elseif ($eeRet['Error'] == "2") {
						$MOError = 2;
						$PageError = 1;
					}
					break;

				case "A":
					if (isset($_REQUEST['eeannualopt'])) {
						$eeEvent['cr_annual_option'] = $_REQUEST['eeannualopt'];
					}
					switch($eeEvent['cr_annual_option']) {
						case "a1":
							$eeEvent['cr_annual_month1A'] = $_REQUEST['eean_1A'];
							$eeEvent['cr_annual_dom'] = $_REQUEST['eean_1B'];
							break;
						case "a2":
							$eeEvent['cr_annual_wknum'] = $_REQUEST['eean_2A'];
							$eeEvent['cr_annual_dow'] = $_REQUEST['eean_2B'];
							$eeEvent['cr_annual_month2C'] = $_REQUEST['eean_2C'];
							break;
						case "a3":
							$eeEvent['cr_annual_daynum'] = $_REQUEST['eean_3A'];
							break;
						case "a4":
							break;
						default:
							$AOptError = 1;
							$PageError = 1;
					}
					$eeRet = LHC_Validate_Nonblank($eeEvent['cr_annual_occurs'], $_REQUEST['eeann_occ'], "Y");
					$eeEvent['cr_annual_occurs'] = $eeRet['Value'];
					if ($eeRet['Changed'] == "Y") {
						$eeChanged = "Y";
					}
					if ($eeRet['Error'] == "Y") {
						$eeEvent['cr_annual_occurs'] = 1;
					} elseif ($eeRet['Error'] == "2") {
						$AOError = 2;
						$PageError = 1;
					}
			}

			switch($_REQUEST['eerecend']) {
				case "n":
					$eeEvent['cr_until_date'] = "2099-12-31";
					$eeEvent['cr_unitl_count'] = 0;
					break;
				case "o":
					$eeRet = LHC_Validate_Nonblank($eeEvent['cr_until_count'], $_REQUEST['eerecocc'], "N");
					$eeEvent['cr_until_count'] = $eeRet['Value'];
					if ($eeRet['Changed'] == "Y") {
						$eeChanged = "Y";
					}
					if ($eeRet['Error'] == "1") {
						$UIError = 1;
						$PageError = 1;
					} elseif ($eeRet['Error'] == "2") {
						$UIError = 2;
						$PageError = 1;
					} else {
						$eeEvent['cr_until_date'] = "2099-12-31";
					}
					break;
				case "a":
					$eeRet = LHC_Validate_Date($eeEvent['cr_until_date'], $_REQUEST['eerecdate'], "N");
				    $eeEvent['cr_until_date'] = $eeRet['Value'];
				    if ($eeRet['Changed'] == "Y") {
				        $eeChanged = "Y";
				    }
				    if ($eeRet['Error'] == "1") {
				        $UDError = 1;
				        $PageError = 1;
				    } elseif ($eeRet['Error'] == "2") {
				        $UDError = 2;
				        $PageError = 1;
					} elseif (strtotime($eeEvent['cr_until_date']) < strtotime($eeEvent['event_end'])) {
				        $UDError = "3";
					}
					$eeEvent['cr_until_count'] = 0;
					break;
			}
		}

	    if ($PageError == 0) {

			require_once($_REQUEST['LHCPath']."/Ajax/LHCCalendar/FCWriteEvent.php");
			$eeUpdateMsg = FCWriteEvent($eeApp, $eeEvent);
	    	if ($eeUpdateMsg < 0) {
	        	echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
	    	}

			$eeEvent['status'] = "EXIST";   		// get rid of NEW after update
			$eeEvent['repeatstatus'] = "EXIST";   	// get rid of NEW after update

			$fun = $eeApp."_Update_Savearea";
	        if ($fun($_REQUEST['runningapp'].$eePage, $eeEvent, $eeUID) < 0) {
	            echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR Cannot update the calendar savearea. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br />";
	            exit;
	        }

			$eePgError = 91;
		}
	}
}

$eeStOpts = "";
$eeAllStates = LHC_Get_All_States();
if ($eeAllStates < 0) {
    echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR Cannot get the states names because of database error [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</strong></span><br />";
    exit;
} else {
    foreach ($eeAllStates as $eeState) {
        $eeState = (array) $eeState;  // must cast as an array to work here!
        $eeStOpts = $eeStOpts."<option value='".$eeState['st_abbrev']."'";
        if ($eeState['st_abbrev'] == $eeEvent['event_loc_state']) {
            $eeStOpts = $eeStOpts." selected";
        }
        $eeStOpts = $eeStOpts.">".strtoupper($eeState['st_abbrev'])."</option>";
    }
}

?>

<style>
#recuropt {
	padding-left:50px;
}

</style>
<script type='text/javascript'>

function load_days_in_mth(eeYear, eeMonth, eeFld) {

	var eeToday = new Date();
	var eeDays = new Date(eeToday.getFullYear(), eeMonth, 0);
	var myselect=document.getElementById("eeFld");

	var i=0;
	var eeOpts;
	for (i=0; i <= eeDays; i++) {
		var s = i.toString();
		if (s == 0) {
			s = "--"
		} else if (s.length == 1) {
			s = "0".s;
		}
		myselect.options[myselect.length] = new Option(s, s);
	}
	return false;
} 

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

	$(function(){

// -- Date Picker w/ class  
		$('.getDate').datepicker({
	   		onClose: function(dateText, inst) {
	      		$(this).focus();
	   		}
		});
	});

</script>

<?php
if ($eeApp != "MAEK") {
	echo "<center><h2>".$_REQUEST['sec_orgname']."</h2>";
}
echo "<span style='font-size: 115%;'><strong><em>Add/Update An Event</em></strong></span></center><br />";

	echo "<table>";
	if ($BDError == 3) {
        echo "<tr><td width='50'></td><td width='600'><font color='#FFD633'><strong>WARNING: Beginning Date is less than today?</strong></span></td></tr>";
        echo "<tr><td width='50'></td><td width='600'> </td></tr>";
	}

	if ($PageError <> 0
	and $PageError < 90) {

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
		if ($EDError == 1) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date must be entered!</strong></span></td></tr>";
	    }
		if ($EDError == 2) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date is invalid!</strong></span></td></tr>";
		}
		if ($EDError == 3) {
	        echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Ending Date is less than the Beginning Date!</strong></span></td></tr>";
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
		if ($DOptError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Daily Option not selected!</strong></span></td></tr>";
	    }
		if ($DOError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Daily Repeat Occurs is invalid!</strong></span></td></tr>";
	    }
		if ($WOptError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Weekly Option not selected!</strong></span></td></tr>";
	    }
		if ($WOError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Weekly Repeat Occurs is invalid!</strong></span></td></tr>";
	    }
		if ($MOptError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Monthly Option not selected!</strong></span></td></tr>";
	    }
		if ($MDError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Day of Monthly is invalid!</strong></span></td></tr>";
	    }
		if ($MOError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Monthly Repeat Occurs is invalid!</strong></span></td></tr>";
	    }
		if ($AOptError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Monthly Option not selected!</strong></span></td></tr>";
	    }
		if ($AOError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Annual Repeat Occurs is invalid!</strong></span></td></tr>";
	    }
		if ($UIError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Until Count cannot be blank!</strong></span></td></tr>";
	    }
		if ($UIError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>Until Count is invalid!</strong></span></td></tr>";
	    }
		if ($UDError == 1) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>End After Date cannot be blank!</strong></span></td></tr>";
	    }
		if ($UDError == 2) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>End After Date is invalid!</strong></span></td></tr>";
	    }
		if ($UDError == 3) {
			echo "<tr><td width='50'></td><td width='600'><span style='color:red;'><strong>End After Date cannot be less than Ending Date!</strong></span></td></tr>";
	    }


	    $UIError = 0;


	}

	if ($eePgError == 91) {
	    echo "<br /><center><span style='color:darkgreen; font-size:115%;'><strong>".$eeUpdateMsg."</strong></span></center><br />";
	}

	echo "<form action='?page_id=".$eePage."&action=".$eeFormRet."&origaction=".$_REQUEST['origaction']."' method='POST'>";
	echo "<input type=hidden name='eeorigparms' id='eeorigparms' value='a=".$_REQUEST['a'].",r=".$_REQUEST['r'].",v=".$_REQUEST['v'].",c=".$_REQUEST['c']."' />";
	echo "<table>";
	echo "<tr><td width='100'>Title:</td><td width='500'><input id='eetitle' name='eetitle' type='text' size='50' maxlength='50' value='".$eeEvent['event_title']."' /></td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td width='100' valign='top'>Description:</td><td width='500'><textarea cols='50' rows='5' id='eedesc' name='eedesc'>".$eeEvent['event_desc']."</textarea></td></tr>";

	if ($eeEvent['event_allday'] == "Y") {
		$d = "none";
		$a = " checked";
	} else {
		$d = "block";
		$a = "";
	}
	echo "<tr><td width='100'>All Day Event:</td><td width='500'><input type='checkbox' id='eeallday' name='eeallday' onclick=\"return toggleTimes(this);\"".$a." /></td></tr>";

	echo "<tr><td colspan='2'>";
		echo "<table>";
		echo "<tr><td width='100'>Begin Date:</td><td width='150'><input type='text' name='eebegdate' class='getDate' value='".date("m/d/Y", strtotime($eeEvent['event_begin']))."' /></td>";
		echo "<td width='20'></td>";
		echo "<td width='100'><div id='eebegtimelabel' style='display:".$d.";'>Begin Time:</div></td><td width='160'><div id='eebegtimeinput' style='display:".$d.";'><input id='eebegtime' name='eebegtime' value='".date("H:i",strtotime($eeEvent['event_btime']))."' size='6' maxlength='6' /></div></td>";
		echo "<td width='20'></td></tr>";
		echo "<tr><td width='100'>End Date:</td><td width='150'><input type='text' name='eeenddate' class='getDate' value='".date("m/d/Y", strtotime($eeEvent['event_end']))."' /></td>";
		echo "<td width='20'></td>";
		echo "<td width='100'><div id='eeendtimelabel' style='display:".$d.";'>End Time:</div></td><td width='160'><div id='eeendtimeinput' style='display:".$d.";'><input id='eeendtime' name='eeendtime' value='".date("H:i",strtotime($eeEvent['event_etime']))."' size='6' maxlength='6' /></div></td>";
		echo "<td width='20'></td></tr></table>";

	echo "</td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td width='100'>Website:</td><td width='500'><input id='eelink' name='eelink' type='text' size='50' maxlength='50' value='".$eeEvent['event_link']."' /></td></tr>";
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td colspan='2'>Location</td>";
	echo "<tr><td colspan='2'>";	
		echo "<table>";
		echo "<tr><td width='20'></td><td width='80'>Name:</td><td width='500'><input id='eelname' name='eelname' type='text' size='50' maxlength='50' value='".$eeEvent['event_loc_name']."' /></td></tr>";
		echo "<tr><td width='20'></td><td width='80'>Street:</td><td width='500'><input id='eelstrt' name='eelstrt' type='text' size='50' maxlength='50' value='".$eeEvent['event_loc_street']."' /></td></tr>";
		echo "</table>";
		echo "<table><tr>";





		echo "<td width='20'></td><td width='80'>City:</td><td width='200'><input id='eelcity' name='eelcity' type='text' size='30' maxlength='30' value='".$eeEvent['event_loc_city']."' /></td>";
		echo "<td width='20'></td><td width='35'>State:</td><td width='50'><select name='eelstate'><Option Value='--'>--".$eeStOpts."</select></td>";
		echo "<td width='20'></td><td width='35'>Zip:</td><td width='100'><input id='eelzip' name='eelzip' type='text' size='10' maxlength='10' value='".$eeEvent['event_loc_zip']."' /></td>";
		echo "</tr></table>";
	echo "</td></tr>";

	if ($eeEvent['event_recur'] == "Y") {
		$r = "block ";
		$t = " checked";
	} else {
		$r = "none ";
		$t = "";
	}
	echo "<tr><td width='100'></td><td width='500'></td></tr>";
	echo "<tr><td colspan='2'><input type='checkbox' id='eerecur' name='eerecur' onclick=\"return toggleMe('eeRecursOpts')\" ".$t."/>&nbsp;&nbsp;&nbsp;Check If This Is A Repeating Event.</td></tr>";
	echo "</table>";

// Building options for repeating terms
	$RecWks = "<option value='0'>Week</option>".
			  "<option value='1'>1st</option>".
			  "<option value='2'>2nd</option>".
			  "<option value='3'>3rd</option>".
			  "<option value='4'>4th</option>".
			  "<option value='5'>Last</option>";
	$RecDays = "<option value='0'>DayOfWeek</option>".
			   "<option value='1'>Sunday</option>".
	 		   "<option value='2'>Monday</option>".
			   "<option value='3'>Tuesday</option>".
			   "<option value='4'>Wednesday</option>".
			   "<option value='5'>Thursday</option>".
			   "<option value='6'>Friday</option>".
			   "<option value='7'>Saturday</option>";
	$RecMths = "<option value='0'> Month </option>".
			   "<option value='1'>January</option>".
			   "<option value='2'>February</option>".
			   "<option value='3'>March</option>".
			   "<option value='4'>April</option>".
			   "<option value='5'>May</option>".
			   "<option value='6'>June</option>".
			   "<option value='7'>July</option>".
			   "<option value='8'>August</option>".
			   "<option value='9'>September</option>".
			   "<option value='10'>October</option>".
			   "<option value='11'>November</option>".
			   "<option value='12'>December</option>";
	$RecMoDays = "<option value='0'>Day</option>".
				 "<option value='1'>01</option>".
				 "<option value='2'>02</option>".
				 "<option value='3'>03</option>".
				 "<option value='4'>04</option>".
				 "<option value='5'>05</option>".
				 "<option value='6'>06</option>".
				 "<option value='7'>07</option>".
				 "<option value='8'>08</option>".
				 "<option value='9'>09</option>".
				 "<option value='10'>10</option>".
				 "<option value='11'>11</option>".
				 "<option value='12'>12</option>".
				 "<option value='13'>13</option>".
				 "<option value='14'>14</option>".
				 "<option value='15'>15</option>".
				 "<option value='16'>16</option>".
				 "<option value='17'>17</option>".
				 "<option value='18'>18</option>".
				 "<option value='19'>19</option>".
				 "<option value='20'>20</option>".
				 "<option value='21'>21</option>".
				 "<option value='22'>22</option>".
				 "<option value='23'>23</option>".
				 "<option value='24'>24</option>".
				 "<option value='25'>25</option>".
				 "<option value='26'>26</option>".
				 "<option value='27'>27</option>".
				 "<option value='28'>28</option>".
				 "<option value='29'>29</option>".
				 "<option value='30'>30</option>".
				 "<option value='31'>31</option>";

	$s = strtotime($eeEvent['event_begin']);
	$RecDayWk = date("w", $s);
	$RecDayMo = date("j", $s);
	$RecDayYr = date("z", $s);

	echo "<div id='eeRecursOpts' style='display:".$r."'>";
	echo "<table>";
	echo "<tr><td width='20'></td><td width='200'></td><td width='425'></td></tr>";
	echo "<tr><td width='20'></td><td colspan='2'>Repetition Criteria</td></tr>";

	$optd = "";
	$optw = "";
	$optm = "";
	$opta = "";
	switch($eeEvent['cr_type']) {
		case "D":
			$optd = " checked";
			break;
		case "W":
			$optw = " checked";
			break;
		case "M":
			$optm = " checked";
			break;
		case "A":
			$opta = " checked";
			break;
	}
	echo "<tr><td width='20'></td><td colspan='2'>";
		echo "<table><tr><td width='20'></td><td wdith='200' valign='top'>";
		echo "<div id='eeRecurOptions'>";
			echo "<input id='eerecuropt_1' name='eerecuropt' type='radio' value='D'".$optd.
				 " onclick=\"return toggleOpts('d')\" />Daily<br /><br />";

			echo "<input id='eerecuropt_2' name='eerecuropt' type='radio' value='W'".$optw.
				 " onclick=\"return toggleOpts('w')\" />Weekly<br /><br />";

			echo "<input id='eerecoropt_3' name='eerecuropt' type='radio' value='M'".$optm.
				 " onclick=\"return toggleOpts('m')\" />Monthly<br /><br />";

			echo "<input id='eerecoropt_4' name='eerecuropt' type='radio' value='A'".$opta.
				 " onclick=\"return toggleOpts('a')\" />Annual<br /><br />";


// TODO show examples






		echo "</div></td><td width='20'></td><td width='425' valign='top'>";

// Daily options
		if ($eeEvent['cr_type'] == "D") {
			$d = "block ";
		} else {
			$d = "none ";
		}
		echo "<div id='eedailyoptions' style='display:".$d."'>Daily Options<br />";
			$d1 = "";
			$d2 = "";
			if ($eeEvent['cr_daily_option'] == "d1") {
				$d1 = " checked";
			}
			if ($eeEvent['cr_daily_option'] == "d2") {
				$d2 = " checked";
			}
			echo "<input id='eedailyopt_1' name='eedailyopt' type='radio' value='d1'".$d1." />";
				echo " Every day.<br /><br />";

			echo "<input id='eedailyopt_3' name='eedailyopt' type='radio' value='d2'".$d2." />";
				echo " Every weekday (Monday through Friday, No weekends).<br /><br /><br />";
/*
			echo " Occurs every ".
				 "<input type='text' name='eeday_occ' size=2 maxlength=2 value='".$eeEvent['cr_daily_occurs']."' />".
				 " day(s)<br />".
				 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other day, 3=every third day, etc...</span><br />";
*/
		echo "</div>";

// Weekly options
		if ($eeEvent['cr_type'] == "W") {
			$w = "block ";
		} else {
			$w = "none ";
		}
		$wd1A = $RecDays;
		echo "<div id='eeweeklyoptions' style='display:".$w."'>Weekly Options<br />";
			$w1 = "";
			$w2 = "";
			$w3 = "";
			if ($eeEvent['cr_weekly_option'] == "w1") {
				$w1 = " checked";				
				$wd1A = str_replace("value='".strval($eeEvent['cr_weekly_dow']),"selected='selected' value='".strval($eeEvent['cr_weekly_dow']), $RecDays);
			}
			if ($eeEvent['cr_weekly_option'] == "w2") {
				$w2 = " checked";
			}
			if ($eeEvent['cr_weekly_option'] == "w3") {
				$w3 = " checked";
			}
			echo "<input id='eeweeklyopt_1' name='eeweeklyopt' type='radio' value='w1'".$w1." />";
				echo "On ".
					 "<select name='eewk_1A'>".$wd1A."</select> every week.<br /><br />";
/*
			echo "<input id='eeweeklyopt_2' name='eeweeklyopt' type='radio' value='w2'".$w2." />";
				echo "Another option<br /><br /><br />";

			echo " Occurs every ".
				 "<input type='text' name='eewk_occ' size=2 maxlength=2 value='".$eeEvent['cr_weekly_occurs']."' />".
				 " week(s)<br />".
				 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other week, 3=every third week, etc...</span><br />";
*/
		echo "</div>";

// Monthly options
		if ($eeEvent['cr_type'] == "M") {
			$m = "block ";
		} else {
			$m = "none ";
		}
		echo "<div id='eemonthlyoptions' style='display:".$m."'>Monthly Options<br />";
			$m1 = "";
			$m2 = "";
			$m3 = "";
			$mw2A = $RecWks;
			$md2B = $RecDays;
			if ($eeEvent['cr_monthly_option'] == "m1") {
				$m1 = " checked";
			}
			if ($eeEvent['cr_monthly_option'] == "m2") {
				$m2 = " checked";
				$mw2A = str_replace("value='".strval($eeEvent['cr_monthly_wknum']),"selected='selected' value='".strval($eeEvent['cr_monthly_wknum']), $RecWks);
				$md2B = str_replace("value='".strval($eeEvent['cr_monthly_dow']),"selected='selected' value='".strval($eeEvent['cr_monthly_dow']), $RecDays);
			}
			if ($eeEvent['cr_monthly_option'] == "m3") {
				$m3 = " checked";
			}

			echo "<input id='eemonthlyopt_1' name='eemonthlyopt' type='radio' value='m1'".$m1." />";
				echo "On day ".
					 "<input type='text' name='eemo_1A' size=2 maxlength=2 value='".$eeEvent['cr_monthly_daynum']."' />".
					 " of every month<br/><br />";

			echo "<input id='eemonthlyopt_2' name='eemonthlyopt' type='radio' value='m2'".$m2." />";
				echo "On the ".
					 "<select name='eemo_2A'>".$mw2A."</select>".
					 "<select name='eemo_2B'>".$md2B."</select> of every month<br /><br />";
/*
			echo "<input id='eemonthlyopt_3' name='eemonthlyopt' type='radio' value='m3'".$m3." />";
				echo "Another option<br /><br /><br />";

			echo " Occurs every ".
				 "<input type='text' name='eemth_occ' size=2 maxlength=2 value='".$eeEvent['cr_monthly_occurs']."' />".
				 " month(s)<br />".
				 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other month, 3=every third month, etc...</span><br />";
*/
		echo "</div>";
	
// Annual options
		if ($eeEvent['cr_type'] == "A") {
			$a = "block ";
		} else {
			$a = "none ";
		}
		echo "<div id='eeannualoptions' style='display:".$a."'>Annual Options<br />";
			$a1 = "";
			$a2 = "";
			$a3 = "";
			$am1A = $RecMths;
			$ad1B = $RecMoDays;
			$aw2A = $RecWks;
			$ad2B = $RecDays;
			$am2C = $RecMths;
 			if ($eeEvent['cr_annual_option'] == "a1") {
				$a1 = " checked";
				$am1A = str_replace("value='".strval($eeEvent['cr_annual_month1A']),"selected='selected' value='".strval($eeEvent['cr_annual_month1A']), $RecMths);
				$ad1B = str_replace("value='".strval($eeEvent['cr_annual_dom']),"selected='selected' value='".strval($eeEvent['cr_annual_dom']), $RecMoDays);
			}
			if ($eeEvent['cr_annual_option'] == "a2") {
				$a2 = " checked";
				$aw2A = str_replace("value='".strval($eeEvent['cr_annual_wknum']),"selected='selected' value='".strval($eeEvent['cr_annual_wknum']), $RecWks);
				$ad2B = str_replace("value='".strval($eeEvent['cr_annual_dow']),"selected='selected' value='".strval($eeEvent['cr_annual_dow']), $RecDays);
				$am2C = str_replace("value='".strval($eeEvent['cr_annual_month2C']),"selected='selected' value='".strval($eeEvent['cr_annual_month2C']), $RecMths);
			}
			if ($eeEvent['cr_annual_option'] == "a3") {
				$a3 = " checked";
			}
			echo "<input id='eeannualopt_1' name='eeannualopt' type='radio' value='a1'".$a1." />";
				echo "On ".
					 "<select name='eean_1A' id='eean_1A' onchange='return load_days_in_mth(this.form.eean_1A.value, \"eean_1B\"'>".
					 $am1A.
					 "</option></select>".
					 " day ".
					 "<select name='eean_1B'>".$ad1B."</option></select><br /><br />";

			echo "<input id='eeannualopt_2' name='eeannualopt' type='radio' value='a2'".$a2." />";
				echo "On the ".
					 "<select name='eean_2A'>".$aw2A."</select> ".
					 " week on ".
					 "<select name='eean_2B'>".$ad2B."</select>".
					 " of ".
					 "<select name='eean_2C'>".$am2C."</select><br /><br />";
	
			echo "<input id='eeannualopt_3' name='eeannualopt' type='radio' value='a3'".$a3." />";
				echo "On day ".
					 "<input type='text' name='eean_3A' size=3 maxlength=3 value='".$eeEvent['cr_annual_daynum'].
					 "' /> of every year.<br /><br /><br />";
/*
			echo " Occurs every ".
				 "<input type='text' name='eeann_occ' size=2 maxlength=2 value='".$eeEvent['cr_annual_occurs']."' />".
				 " year(s)<br />".
				 "<span style='font-size:80%; text-indent:20px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2=every other year, 3=every third year, etc...</span><br />";
*/
		echo "</div>";

// End of occurs options
		if ($eeEvent['cr_type'] == NULL) {
			$e = "none ";
		} else {
			$e = "block ";
		}
		echo "<div id='eeendoptions' style='display:".$e."'>";
			if ($eeEvent['cr_until_date'] == "2099-12-31") {
				$u1 = " checked";
				$u2 = "";
				$u3 = "";
				$u3date = $eeEvent['event_end'];
			} else {
				$u1 = "";
				if ($eeEvent['cr_until_count'] > 1) {
					$u2 = " checked";
					$u3 = "";
				} else {
					$u2 = "";
					$u3 = " checked";
				}
				$u3date = $eeEvent['cr_until_date'];
			}
			echo "<br /><br /><br />Occurs Until:<br />";
			echo "<input type='radio' name='eerecend' value='n'".$u1." />No End<br /><br />";

/*
			echo "<input type='radio' name='eerecend' value='o'".$u2." />";
				echo "<input type='text' name='eerecocc' id='eerecocc' size=4 maxlength=4 value='".$eeEvent['cr_until_count']."' /> times.<br /><br />";
*/

			echo "<table><tr><td><input type='radio' name='eerecend' value='a'".$u3." />End After </td><td><input type='text' name='eerecdate' class='getDate' value='".date("m/d/Y", strtotime($u3date))."' /></td></tr></table><br /><br />";
		echo "</div>";
		echo "</td></tr></table>";

	echo "</td></tr>";
	echo "<tr><td width='20'></td><td width='200'></td><td width='425'></td></tr>";
	echo "<tr><td width='20'></td><td width='200'></td><td width='425'></td></tr>";
	echo "</table></div>";

	echo "</table><br />";
	echo "<center><input type='submit' name='submitevent' value='Submit' />";
	if ($eeEvent['status'] != "NEW") {
		echo "<span style='padding-left:50px;'><input type='submit' name='deleteevent' value='Delete' />";
	}
	echo "</center>";


?>