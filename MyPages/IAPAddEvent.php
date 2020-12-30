<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

require_once(ABSPATH. 'IAPServices.php');
if (IAP_Program_Start("168", "N") < 0) {
	return;
}
$eeCoID = $_REQUEST['CoId'];
$eeUID = $_REQUEST['IAPUID'];

if ($_REQUEST['action'] != "168ret") {
	IAP_Remove_Savearea("IAP168AE", $eeUID);
}

$eeEvent = IAP_Get_Savearea("IAP168AE", $eeUID);
if (!($eeEvent)) {

    $_REQUEST['origaction'] = $_REQUEST['action'];

	if ($_REQUEST['action'] == "NEW") {
		$eeEvents = IAP_Build_New_Row(array('table' => "iapcal"));
		if ($eeEvents < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeEvent = (array) $eeEvents[0];
   	    $eeEvent['ev_account'] = $_REQUEST['CoId'];
   	    $eeEvent['ev_begin'] = date("Y-m-d", current_time("timestamp", 0));
   	    $eeEvent['ev_end'] = $eeEvent['ev_begin'];
		$eeEvent['ev_btime'] = "00:00";
		$eeEvent['ev_etime'] = "00:00";
		$eeEvent['ev_recur'] = "S";
		$eeEvent['ev_allday'] = "N";
		$eeEvent['ev_author'] = $eeUID;
		$es = $eeEvent['status'];
		$eeRepeats = IAP_Build_New_Row(array('table' => "iapcrep"));
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeRepeat = (array) $eeRepeats[0];
		$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
		$eeEvent = array_merge($eeEvent, $eeRepeat);
		$eeEvent['status'] = $es;
	} else {
		$eeEvent = IAP_Get_Event_By_Id($_REQUEST['eventid'], "N");
		if ($eeEvent < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot retreive the selected event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		if ($eeEvent == NULL) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot retreive the selected event - event not found. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$es = $eeEvent['status'];
		$eeRepeats = IAP_Get_Repeating($_REQUEST['eventid']);
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot retreive the selected event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
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

	$eeRet = IAP_Create_Savearea("IAP168AE", $eeEvent, $eeUID);
    if ($eeRet < 0) {
        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot create savearea for calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;    
	}
    $eePgError = 99; // Fake error
} else {
    if ($eeEvent < 0) {
        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot retrieve savearea for calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
	}

    if ($_GET['action'] <> "168ret") {
        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot complete your request because of a program error. [FATAL]<br />Please notify Support and provide this reference of /Action is invalid-reentry/".basename(__FILE__)."/".__LINE__."</span><br />";
        exit;
    }
	if ($_REQUEST['deleteevent'] == "Delete") {
		$eeRet = IAP_Delete_Row($eeEvent, "iapcal");
		if ($eeRet < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot delete the event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		if ($eeEvent['ev_recur'] == "Y") {
			$eeRet = IAP_Delete_Row($eeEvent, "iapcrep");
			if ($eeRet < 0) {
	    	    echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot delete the event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        	exit;
			}
		}

		$eeId = $eeEvent['ev_id'];
		$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
		if ($eeEvents < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot build a new event because of a database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeEvent = (array) $eeEvents[0];
   	    $eeEvent['ev_account'] = $_REQUEST['CoId'];
   	    $eeEvent['ev_begin'] = date("Y-m-d", current_time("timestamp", 0));
   	    $eeEvent['ev_end'] = $eeEvent['ev_begin'];
		$eeEvent['ev_btime'] = "00:00";
		$eeEvent['ev_etime'] = "00:00";
		$eeEvent['ev_recur'] = "S";
		$eeEvent['ev_allday'] = "N";
		$eeEvent['ev_author'] = $eeUID;
		$es = $eeEvent['status'];

		$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	        exit;
		}
		$eeRepeat = (array) $eeRepeats[0];
		$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
		$eeEvent = array_merge($eeEvent, $eeRepeat);
		$eeEvent['status'] = $es;

		$eeUpdateMsg = "Event ".strval($eeId)." Successfully Deleted";
		if (IAP_Update_Savearea("IAP168AE", $eeEvent, $eeUID) < 0) {
            echo "<span style='color:red;'><strong>IAP INTERNAL ERROR Cannot update the calendar savearea. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br />";
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


		require_once($_REQUEST['IAPPath']."IAPValidators.php");


		if ($eeUID == 1) {
			if ($_REQUEST['eeevtype'] == "sysevent") {
				$eeCoID = 0;
			}
		}

		if (isset($_REQUEST['eetitle'])) {
			$t = stripslashes($_REQUEST['eetitle']); 
		    if ($eeEvent['ev_title'] != $t) {
			    $eeEvent['ev_title'] = $t;
		        $eeChanged = "Y";
			}
		}
	    if (empty($eeEvent['ev_title'])) {
	        $TiError = 1;
	        $PageError = 1;
	    }

		if (isset($_REQUEST['eedesc'])) {
			$d = stripslashes($_REQUEST['eedesc']); 
			if ($eeEvent['ev_desc'] != $d) {
			    $eeEvent['ev_desc'] = $d;
		        $eeChanged = "Y";
			}
		}
	    if (empty($eeEvent['ev_desc'])) {
	        $DeError = 1;
	        $PageError = 1;
	    }
	
		if ($_REQUEST['eeallday'] == "on") {
			$eeEvent['ev_allday'] = "Y";
		} else {
			$eeEvent['ev_allday'] = "N";
		}

		$eeRet = IAP_Validate_Date($eeEvent['ev_begin'], $_REQUEST['eebegdate'], "N");
	    $eeEvent['ev_begin'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $BDError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $BDError = 2;
	        $PageError = 1;
		} elseif (strtotime($eeEvent['ev_begin']) < strtotime(date("Y/m/d")." 00:00:01")) {
	        $BDError = 3;
		}

		$eeRet = IAP_Validate_Date($eeEvent['ev_end'], $_REQUEST['eeenddate'], "N");
	    $eeEvent['ev_end'] = $eeRet['Value'];
	    if ($eeRet['Changed'] == "Y") {
	        $eeChanged = "Y";
	    }
	    if ($eeRet['Error'] == "1") {
	        $EDError = 1;
	        $PageError = 1;
	    } elseif ($eeRet['Error'] == "2") {
	        $EDError = 2;
	        $PageError = 1;
		} elseif (strtotime($eeEvent['ev_end']) < strtotime($eeEvent['ev_begin'])) {
	        $EDError = "3";
	        $PageError = 1;
		}

		$eeRet = IAP_Validate_Time($eeEvent['ev_btime'],$_REQUEST['eebegtime'], "N");
	    $eeEvent['ev_btime'] = $eeRet['Value'];
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

		$eeRet = IAP_Validate_Time($eeEvent['ev_etime'],$_REQUEST['eeendtime'], "N");
	    $eeEvent['ev_etime'] = $eeRet['Value'];
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
			if ($eeEvent['ev_etime'] < $eeEvent['ev_btime']) {
	        	$ETError = 3;
	        	$PageError = 1;
			}
		}

		if (isset($_REQUEST['eelink'])) {
			if ($eeEvent['ev_link'] != $_REQUEST['eelink']) {
				$eeEvent['ev_link'] = $_REQUEST['eelink'];
		        $eeChanged = "Y";
			}
		}
		if (!empty($eeEvent['ev_link'])) {
			if (!parse_url($eeEvent['ev_link'])) {
		    	$LiError = 1;
		    	$PageError = 1;
			}
		}

		if (isset($_REQUEST['eelname'])) {
			if ($eeEvent['ev_loc_name'] != $_REQUEST['eelname']) {
				$eeEvent['ev_loc_name'] = $_REQUEST['eelname'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelstrt'])) {
			if ($eeEvent['ev_loc_street'] != $_REQUEST['eelstrt']) {
				$eeEvent['ev_loc_street'] = $_REQUEST['eelstrt'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelcity'])) {
			if ($eeEvent['ev_loc_city'] != $_REQUEST['eelcity']) {
				$eeEvent['ev_loc_city'] = $_REQUEST['eelcity'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelstate'])) {
			if ($eeEvent['ev_loc_state'] != $_REQUEST['eelstate']) {
				$eeEvent['ev_loc_state'] = $_REQUEST['eelstate'];
		        $eeChanged = "Y";
			}
		}

		if (isset($_REQUEST['eelzip'])) {
			if ($eeEvent['ev_loc_zip'] != $_REQUEST['eelzip']) {
				$eeEvent['ev_loc_zip'] = $_REQUEST['eelzip'];
		        $eeChanged = "Y";
			}
		}

// ------------- Repeating fields here -------------------- 
		if ($_REQUEST['eerecur'] != "on") {
			$eeEvent['ev_recur'] = "N";
			$eeEvent['cr_type'] = NULL;
		} else {
			$eeEvent['ev_recur'] = "Y";
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
					$eeRet = IAP_Validate_Nonblank($eeEvent['cr_daily_occurs'], $_REQUEST['eeday_occ'], "Y");
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
					$eeRet = IAP_Validate_Nonblank($eeEvent['cr_weekly_occurs'], $_REQUEST['eewk_occ'], "Y");
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
							$eeRet = IAP_Validate_Nonblank($eeEvent['cr_monthly_daynum'], $_REQUEST['eemo_1A'], "Y");
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
					$eeRet = IAP_Validate_Nonblank($eeEvent['cr_monthly_occurs'], $_REQUEST['eemth_occ'], "Y");
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
					$eeRet = IAP_Validate_Nonblank($eeEvent['cr_annual_occurs'], $_REQUEST['eeann_occ'], "Y");
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
					$eeRet = IAP_Validate_Nonblank($eeEvent['cr_until_count'], $_REQUEST['eerecocc'], "N");
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
					$eeRet = IAP_Validate_Date($eeEvent['cr_until_date'], $_REQUEST['eerecdate'], "N");
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
					} elseif (strtotime($eeEvent['cr_until_date']) < strtotime($eeEvent['ev_end'])) {
				        $UDError = "3";
					}
					$eeEvent['cr_until_count'] = 0;
					break;
			}
		}

	    if ($PageError == 0) {
	    	$eeEvent['ev_account'] = $eeCoID;

			require_once($_REQUEST['IAPPath']."Ajax/IAPCalendar/IAPWriteEvent.php");
	    	if ($eeEvent['ev_id'] < 0) {
	        	echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
	    	}
			$eeEvent['ev_id'] = FCWriteEvent($eeEvent, "Y");

/*
			if ($eeEvent['ev_allday'] == "Y") {
				$eeEvent['ev_btime'] = "00:00:00";
				$eeEvent['ev_etime'] = "23:59:59";
			}
			$eeEvent['ev_start_timestamp'] = strtotime($eeEvent['ev_begin']." ".$eeEvent['ev_btime']);

	$h1 = date("Y-m-d H:i T-I (Z)", $eeEvent['ev_start_timestamp']);

			$eeEvent['ev_end_timestamp'] = strtotime($eeEvent['ev_end']." ".$eeEvent['ev_etime']);

	$h2 = date("Y-m-d H:i T-I (Z)", $eeEvent['ev_end_timestamp']);

			$eeEvent['ev_changed'] = date("Y-m-d");
			$eeEvent['ev_changed_by'] = $_REQUEST['IAPUID'];
			$eeRet = IAP_Update_Data($eeEvent, "iapcal");
	    	if ($eeRet < 0) {
	        	echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error updating calendar. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	            exit;
	    	}
// Returns new event_id if insert.
			if ($eeEvent['status'] == "NEW") {
				$eeEvent['ev_id'] = $eeRet;
			}
			if ($eeEvent['status'] == "NEW") {
				$eeUpdateMsg = "Event Successfully Added";
			} else {
				$eeUpdateMsg = "Event Successfully Updated";
			}
// See if event repeats
			if ($eeEvent['ev_recur'] != "Y") {
				IAP_Delete_Row($eeEvent, "iapcrep");
			} else {
// Build new repeating record 
				$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
				if ($eeRepeats < 0) {
			        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot create repeating record because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			        exit;
				}
				$eeRepeat = (array) $eeRepeats[0];
				$eeRepeat['status'] = $eeEvent['repeatstatus'];
				$eeRepeat['cr_id'] = $eeEvent['ev_id'];
// Enter fields to rebuild display
				$eeRepeat['cr_type'] = $eeEvent['cr_type'];

				$eeOccBase = 0;
				$eeOccDay = 86400;

				switch($eeRepeat['cr_type']) {
					case "D":
						$eeRepeat['cr_daily_option'] = $eeEvent['cr_daily_option'];
						$eeRepeat['cr_daily_occurs'] = $eeEvent['cr_daily_occurs'];
						$eeRepeat['cr_jan'] = "Y";
						$eeRepeat['cr_feb'] = "Y";
						$eeRepeat['cr_mar'] = "Y";
						$eeRepeat['cr_apr'] = "Y";
						$eeRepeat['cr_may'] = "Y";
						$eeRepeat['cr_jun'] = "Y";
						$eeRepeat['cr_jul'] = "Y";
						$eeRepeat['cr_aug'] = "Y";
						$eeRepeat['cr_sep'] = "Y";
						$eeRepeat['cr_oct'] = "Y";
						$eeRepeat['cr_nov'] = "Y";
						$eeRepeat['cr_dec'] = "Y";
						$eeRepeat['cr_wk1'] = "Y";
						$eeRepeat['cr_wk2'] = "Y";
						$eeRepeat['cr_wk3'] = "Y";
						$eeRepeat['cr_wk4'] = "Y";
						$eeRepeat['cr_wk5'] = "Y";
						$eeRepeat['cr_mon'] = "Y";
						$eeRepeat['cr_tue'] = "Y";
						$eeRepeat['cr_wed'] = "Y";
						$eeRepeat['cr_thu'] = "Y";
						$eeRepeat['cr_fri'] = "Y";
						if ($eeRepeat['cr_daily_option'] == "d1") {
							$eeRepeat['cr_sun'] = "Y";
							$eeRepeat['cr_sat'] = "Y";
						}
						$eeRepeat['cr_interval'] = $eeEvent['cr_daily_occurs'];
						$eeOccInterval = $eeOccDay;
						break;
					case "W":
						$eeRepeat['cr_weekly_option'] = $eeEvent['cr_weekly_option'];
						$eeRepeat['cr_weekly_dow'] = $eeEvent['cr_weekly_dow'];
						$eeRepeat['cr_weekly_occurs'] = $eeEvent['cr_weekly_occurs'];
						$eeRepeat['cr_jan'] = "Y";
						$eeRepeat['cr_feb'] = "Y";
						$eeRepeat['cr_mar'] = "Y";
						$eeRepeat['cr_apr'] = "Y";
						$eeRepeat['cr_may'] = "Y";
						$eeRepeat['cr_jun'] = "Y";
						$eeRepeat['cr_jul'] = "Y";
						$eeRepeat['cr_aug'] = "Y";
						$eeRepeat['cr_sep'] = "Y";
						$eeRepeat['cr_oct'] = "Y";
						$eeRepeat['cr_nov'] = "Y";
						$eeRepeat['cr_dec'] = "Y";
						$eeRepeat['cr_wk1'] = "Y";
						$eeRepeat['cr_wk2'] = "Y";
						$eeRepeat['cr_wk3'] = "Y";
						$eeRepeat['cr_wk4'] = "Y";
						$eeRepeat['cr_wk5'] = "Y";
						if ($eeRepeat['cr_weekly_option'] == "w1") {
							if ($eeRepeat['cr_weekly_dow'] == 1) 
								$eeRepeat['cr_sun'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 2) 
								$eeRepeat['cr_mon'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 3) 
								$eeRepeat['cr_tue'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 4) 
								$eeRepeat['cr_wed'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 5) 
								$eeRepeat['cr_thu'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 6) 
								$eeRepeat['cr_fri'] = "Y";
							if ($eeRepeat['cr_weekly_dow'] == 7) 
								$eeRepeat['cr_sat'] = "Y";
						}
						$eeRepeat['cr_interval'] = $eeEvent['cr_weekly_occurs'];
						$eeOccInterval = $eeOccDay * 7;
						break;
					case "M":
						$eeRepeat['cr_monthly_option'] = $eeEvent['cr_monthly_option'];
						$eeRepeat['cr_monthly_daynum'] = $eeEvent['cr_monthly_daynum'];
						$eeRepeat['cr_monthly_wknum'] = $eeEvent['cr_monthly_wknum'];
						$eeRepeat['cr_monthly_dow'] = $eeEvent['cr_monthly_dow'];
						$eeRepeat['cr_monthly_occurs'] = $eeEvent['cr_monthly_occurs'];
						if ($eeRepeat['cr_monthly_option'] == "m1") {
							$eeRepeat['cr_day'] = strval($eeRepeat['cr_monthly_daynum']);
							$y = "D";
						} else {
							$y = "Y";
						}
						$eeRepeat['cr_jan'] = $y;
						$eeRepeat['cr_feb'] = $y;
						$eeRepeat['cr_mar'] = $y;
						$eeRepeat['cr_apr'] = $y;
						$eeRepeat['cr_may'] = $y;
						$eeRepeat['cr_jun'] = $y;
						$eeRepeat['cr_jul'] = $y;
						$eeRepeat['cr_aug'] = $y;
						$eeRepeat['cr_sep'] = $y;
						$eeRepeat['cr_oct'] = $y;
						$eeRepeat['cr_nov'] = $y;
						$eeRepeat['cr_dec'] = $y;
						if ($eeRepeat['cr_monthly_option'] == "m2") {
							if ($eeRepeat['cr_monthly_wknum'] == 1) 
								$eeRepeat['cr_wk1'] = "Y";
							if ($eeRepeat['cr_monthly_wknum'] == 2) 
								$eeRepeat['cr_wk2'] = "Y";
							if ($eeRepeat['cr_monthly_wknum'] == 3) 
								$eeRepeat['cr_wk3'] = "Y";
							if ($eeRepeat['cr_monthly_wknum'] == 4) 
								$eeRepeat['cr_wk4'] = "Y";
							if ($eeRepeat['cr_monthly_wknum'] == 5) 
								$eeRepeat['cr_wk5'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 1) 
								$eeRepeat['cr_sun'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 2) 
								$eeRepeat['cr_mon'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 3) 
								$eeRepeat['cr_tue'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 4) 
								$eeRepeat['cr_wed'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 5) 
								$eeRepeat['cr_thu'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 6) 
								$eeRepeat['cr_fri'] = "Y";
							if ($eeRepeat['cr_monthly_dow'] == 7) 
								$eeRepeat['cr_sat'] = "Y";
						}
						$eeRepeat['cr_interval'] = $eeEvent['cr_monthly_occurs'];
						$eeOccInterval = $eeOccDay * 30;
						break;
					case "A":
						$eeRepeat['cr_annual_option'] = $eeEvent['cr_annual_option'];
						$eeRepeat['cr_annual_month1A'] = $eeEvent['cr_annual_month1A'];
						$eeRepeat['cr_annual_dom'] = $eeEvent['cr_annual_dom'];
						$eeRepeat['cr_annual_wknum'] = $eeEvent['cr_annual_wknum'];
						$eeRepeat['cr_annual_month2C'] = $eeEvent['cr_annual_month2C'];
						$eeRepeat['cr_annual_dow'] = $eeEvent['cr_annual_dow'];
						$eeRepeat['cr_annual_daynum'] = $eeEvent['cr_annual_daynum'];
						$eeRepeat['cr_annual_occurs'] = $eeEvent['cr_annual_occurs'];
						if ($eeRepeat['cr_annual_option'] == "a3") {
							$eeRepeat['cr_day'] = strval($eeRepeat['cr_annual_daynum']);
						} else {
							if ($eeRepeat['cr_annual_option'] == "a1") {
								$eeRepeat['cr_day'] = strval($eeRepeat['cr_annual_dom']);
								$y = "D";
							} else {
								$y = "Y";
							}
							$m1A = strval($eeRepeat['cr_annual_month1A']);
							$m2C = strval($eeRepeat['cr_annual_month2C']);
							if ($m1A == 1
							or  $m2C == 1)
								$eeRepeat['cr_jan'] = $y;
							if ($m1A == 2
							or  $m2C == 2) 
								$eeRepeat['cr_feb'] = $y;
							if ($m1A == 3
							or  $m2C == 3) 
								$eeRepeat['cr_mar'] = $y;
							if ($m1A == 4
							or  $m2C == 4) 
								$eeRepeat['cr_apr'] = $y;
							if ($m1A == 5
							or  $m2C == 5) 
								$eeRepeat['cr_may'] = $y;
							if ($m1A == 6
							or  $m2C == 6) 
								$eeRepeat['cr_jun'] = $y;
							if ($m1A == 7
							or  $m2C == 7) 
								$eeRepeat['cr_jul'] = $y;
							if ($m1A == 8
							or  $m2C == 8) 
								$eeRepeat['cr_aug'] = $y;
							if ($m1A == 9
							or  $m2C == 9) 
								$eeRepeat['cr_sep'] = $y;
							if ($m1A == 10
							or  $m2C == 10) 
								$eeRepeat['cr_oct'] = $y;
							if ($m1A == 11
							or  $m2C == 11) 
								$eeRepeat['cr_nov'] = $y;
							if ($m1A == 12
							or  $m2C == 12)
								$eeRepeat['cr_dec'] = $y;
							if ($eeRepeat['cr_annual_option'] == "a2") {
								if ($eeRepeat['cr_annual_wknum'] == 1) 
									$eeRepeat['cr_wk1'] = "Y";
								if ($eeRepeat['cr_annual_wknum'] == 2) 
									$eeRepeat['cr_wk2'] = "Y";
								if ($eeRepeat['cr_annual_wknum'] == 3) 
									$eeRepeat['cr_wk3'] = "Y";
								if ($eeRepeat['cr_annual_wknum'] == 4) 
									$eeRepeat['cr_wk4'] = "Y";
								if ($eeRepeat['cr_annual_wknum'] == 5) 
									$eeRepeat['cr_wk5'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 1) 
									$eeRepeat['cr_sun'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 2) 
									$eeRepeat['cr_mon'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 3) 
									$eeRepeat['cr_tue'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 4) 
									$eeRepeat['cr_wed'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 5) 
									$eeRepeat['cr_thu'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 6) 
									$eeRepeat['cr_fri'] = "Y";
								if ($eeRepeat['cr_annual_dow'] == 7) 
									$eeRepeat['cr_sat'] = "Y"; 					
							}
						}
						$eeRepeat['cr_interval'] = $eeEvent['cr_annual_occurs'];
						$eeOccInterval = $eeOccDay + 365;
						break;
				}
				$eeRepeat['cr_until_date'] = $eeEvent['cr_until_date'];
				$eeRepeat['cr_until_count'] = $eeEvent['cr_until_count'];
				$eeRepeat['status'] = $eeEvent['repeatstatus'];
				$eeRet = IAP_Update_Data($eeRepeat, "iapcrep");
		    	if ($eeRet < 0) {
		        	echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
		    	}
			}
*/
		}
		$eeEvent['status'] = "EXIST";   		// get rid of NEW after update
		$eeEvent['repeatstatus'] = "EXIST";   	// get rid of NEW after update

		if (IAP_Update_Savearea("IAP168AE", $eeEvent, $eeUID) < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR Cannot update the calendar savearea. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br />";
	        exit;
	    }

		$eePgError = 91;
	}
}

?>

<style>
#recuropt {
	padding-left:50px;
}
.entry-content table {
	border-bottom: none;
	margin: 0;	
}

</style>

<?php

	$iapReadOnly = IAP_Format_Heading("Calendar Event Maintenance");

	echo "<table class=iapTable>";
	if ($BDError == 3) {
        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><font color='#FFD633'><strong>WARNING: Beginning Date is less than today?</span></td></tr>";
        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'> </td></tr>";
	}

	if ($PageError <> 0
	and $PageError < 90) {

		echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Errors were detected. Please make corrections</span></td></tr>";
		echo "<tr></tr>";
		if ($TiError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Title cannot be blank!</span></td></tr>";	
		}
		if ($DeError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Description cannot be blank!</span></td></tr>";
		}
		if ($BDError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Beginning Date must be entered!</span></td></tr>";
	    }
		if ($BDError == 2) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Beginning Date is invalid!</span></td></tr>";
		}
		if ($EDError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Date must be entered!</span></td></tr>";
	    }
		if ($EDError == 2) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Date is invalid!</span></td></tr>";
		}
		if ($EDError == 3) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Date is less than the Beginning Date!</span></td></tr>";
		}
		if ($BTError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Beginning Time must be entered!</span></td></tr>";
	    }
		if ($BTError == 2) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Beginning Time is invalid!</span></td></tr>";
		}
		if ($ETError == 1) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Time must be entered!</span></td></tr>";
	    }
		if ($ETError == 2) {
	        echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Time is invalid!</span></td></tr>";
	    }
		if ($ETError == 3) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Ending Time is less than Beginning Time!</span></td></tr>";
		}
		if ($LiError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Website is invalid!</span></td></tr>";
	    }
		if ($DOptError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Daily Option not selected!</span></td></tr>";
	    }
		if ($DOError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Daily Repeat Occurs is invalid!</span></td></tr>";
	    }
		if ($WOptError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Weekly Option not selected!</span></td></tr>";
	    }
		if ($WOError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Weekly Repeat Occurs is invalid!</span></td></tr>";
	    }
		if ($MOptError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Monthly Option not selected!</span></td></tr>";
	    }
		if ($MDError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Day of Monthly is invalid!</span></td></tr>";
	    }
		if ($MOError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Monthly Repeat Occurs is invalid!</span></td></tr>";
	    }
		if ($AOptError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Monthly Option not selected!</span></td></tr>";
	    }
		if ($AOError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Annual Repeat Occurs is invalid!</span></td></tr>";
	    }
		if ($UIError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Until Count cannot be blank!</span></td></tr>";
	    }
		if ($UIError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>Until Count is invalid!</span></td></tr>";
	    }
		if ($UDError == 1) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>End After Date cannot be blank!</span></td></tr>";
	    }
		if ($UDError == 2) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>End After Date is invalid!</span></td></tr>";
	    }
		if ($UDError == 3) {
			echo "<tr><td style='width: 25%;'></td><td style='width: 75%;'><span class=iapError>End After Date cannot be less than Ending Date!</span></td></tr>";
	    }

	    $UIError = 0;
	}

	if ($eePgError == 91) {
	    echo "<br /><center><span style='color:darkgreen; font-size:115%;'><strong>".$eeUpdateMsg."</strong></span></center><br />";
	}
	echo "</table>";

	echo "<form action='?action=168ret&origaction=".$_REQUEST['origaction']."' method='POST'>";
	echo "<input type=hidden name='eeorigparms' id='eeorigparms' value='a=".$_REQUEST['a'].",r=".$_REQUEST['r'].",v=".$_REQUEST['v'].",c=".$_REQUEST['c']."' />";

	echo "<br><table class=iapTable>";

	if ($_REQUEST['IAPUID'] == 1) {
		echo "<tr><td style='width: 25%;'><span class='iapFormLabel'>Application/System:</span></td><td style='width: 75%;'>";
			echo "<input type='radio' name='eeevtype' id='eeapplic' value='appevent'";
			if ($eeEvent['ev_account'] != 0) {
				echo " checked";
			}
			echo ">Application";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input type='radio' name='eeevtype' id='eesystem' value='sysevent'";
			if ($eeEvent['ev_account'] == 0) {
				echo " checked";
			}
			echo ">System</td>";
		echo "</tr>";
		echo "<tr><td style='width: 25%; line-height: 2;'> </td><td style='width: 75%;'></td></tr>";
	}

	$t = str_replace("'", "&apos;", $eeEvent['ev_title']);

	echo "<tr><td style='width: 25%;'><span class='iapFormLabel'>Title:</span></td><td style='width: 75%;'><input id='eetitle' name='eetitle' type='text' size='52' maxlength='50' value='".$t."' /><br></td></tr>";
	echo "<tr><td style='width: 25%;'><span class='iapFormLabel'></span></td><td style='width: 75%;'></td></tr>";
	echo "<tr><td style='width: 25%;' valign='top'><span class='iapFormLabel'>Description:</span></td><td style='width: 75%;'><textarea cols='50' rows='5' id='eedesc' name='eedesc'>".$eeEvent['ev_desc']."</textarea><br></td></tr>";

	if ($eeEvent['ev_allday'] == "Y") {
		$d = "none";
		$a = " checked";
	} else {
		$d = "block";
		$a = "";
	}
	echo "<tr><td style='width: 25%;'><span class='iapFormLabel'>All Day Event:</span></td><td style='width: 75%;'><input type='checkbox' id='eeallday' name='eeallday' onclick=\"return toggleTimes(this);\"".$a." /></td></tr>";
	echo "<tr><td style='width: 25%; line-height: 2;'> </td><td style='width: 75%;'></td></tr>";

	echo "<tr><td colspan='2'>";
		echo "<table class=iapTable>";
		echo "<tr>";
		echo "<td style='width: 25%;'><span class='iapFormLabel'>Begin Date:</td>";
		echo "<td style='width: 15%;'><script>DateInput('eebegdate', true, 'YYYY-MON-DD', '".$eeEvent['ev_begin']."')</script></td>";
		echo "<td style='width: 5%;'></td>";
		echo "<td style='width: 20%;'><span class='iapFormLabel'><div id='eebegtimelabel' style='display:".$d.";'>Begin Time:</div></td>";
		echo "<td style='width: 35%;'><div id='eebegtimeinput' style='display:".$d.";'><input id='eebegtime' name='eebegtime' value='".date("h:i a",strtotime($eeEvent['ev_btime']))."' size='8' maxlength='8' /></div></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td style='width: 25%;'><span class='iapFormLabel'>End Date:</td>";
		echo "<td style='width: 15%;'><script>DateInput('eeenddate', true, 'YYYY-MON-DD', '".$eeEvent['ev_end']."')</script></td>";
		echo "<td style='width: 5%;'></td>";
		echo "<td style='width: 20%;'><span class='iapFormLabel'><div id='eeendtimelabel' style='display:".$d.";'>End Time:</div></td>";
		echo "<td style='width: 35%;'><div id='eeendtimeinput' style='display:".$d.";'><input id='eeendtime' name='eeendtime' value='".date("h:i a",strtotime($eeEvent['ev_etime']))."' size='8' maxlength='8' /></div></td>";
		echo "</table>";

	echo "</td></tr>";
	echo "<tr><td style='width: 25%;'><span class='iapFormLabel'>Website:</span></td><td style='width: 75%;'><input id='eelink' name='eelink' type='text' size='50' maxlength='50' value='".$eeEvent['ev_link']."' /><br></td></tr>";
	echo "<tr><td style='width: 25%; line-height: 2;'> </td><td style='width: 75%;'></td></tr>";
	echo "<tr><td colspan='2'>Location</td>";
	echo "<tr><td colspan='2'>";	
		echo "<table class=iapTable>";

		echo "<tr><td style='width: 5%;'></td><td style='width: 20%;'><span class='iapFormLabel'>Name:</span></td><td colspan='7'><input id='eelname' name='eelname' type='text' size='50' maxlength='50' value='".$eeEvent['ev_loc_name']."' /></td></tr>";

		echo "<tr><td style='width: 5%;'></td><td style='width: 20%;'><span class='iapFormLabel'>Street:</span></td><td colspan='7'><input id='eelstrt' name='eelstrt' type='text' size='50' maxlength='50' value='".$eeEvent['ev_loc_street']."' /></td></tr>";
		echo "</table>";

		echo "<table class=iapTable><tr>";

		echo "<td style='width: 5%;'></td>";
		echo "<td style='width: 20%;'><span class='iapFormLabel'>City:</td>";
		echo "<td style='width: 25%;'><input id='eelcity' name='eelcity' type='text' size='30' maxlength='30' value='".$eeEvent['ev_loc_city']."' /></td>";
		echo "<td style='width: 3%;'></td>";
		echo "<td style='width: 5%;'>State:</td>";
		echo "<td style='width: 7%;'><input id='eelstate' name='eelstate' type='text' size='2' maxlength='2' value='".$eeEvent['ev_loc_state']."' /></td>";
		echo "<td style='width: 2%;'></td>";
		echo "<td style='width: 4%;'>Zip:</td>";
		echo "<td style='width: 34%;'><span class='iapFormLabel'><input id='eelzip' name='eelzip' type='text' size='10' maxlength='10' value='".$eeEvent['ev_loc_zip']."' /></td>";

		echo "</tr></table>";
	echo "</td></tr>";

	if ($eeEvent['ev_recur'] == "Y") {
		$r = "block ";
		$t = " checked";
	} else {
		$r = "none ";
		$t = "";
	}

	echo "<tr><td colspan='2'><input type='checkbox' id='eerecur' name='eerecur' onclick=\"return toggleMe('eeRecursOpts')\" ".$t."/>&nbsp;&nbsp;&nbsp;Check If This Is A Repeating Event.</td></tr>";
	echo "<tr><td style='width: 25%; line-height: 2;'> </td><td style='width: 75%;'></td></tr>";
	echo "</table>";

// Building options for repeating terms
	$RecWks = "<option value='0'> Week</option>".
			  "<option value='1'> 1st</option>".
			  "<option value='2'> 2nd</option>".
			  "<option value='3'> 3rd</option>".
			  "<option value='4'> 4th</option>".
			  "<option value='5'> Last</option>";
	$RecDays = "<option value='0'> DayOfWeek</option>".
			   "<option value='1'> Sunday</option>".
	 		   "<option value='2'> Monday</option>".
			   "<option value='3'> Tuesday</option>".
			   "<option value='4'> Wednesday</option>".
			   "<option value='5'> Thursday</option>".
			   "<option value='6'> Friday</option>".
			   "<option value='7'> Saturday</option>";
	$RecMths = "<option value='0'> Month </option>".
			   "<option value='1'> January</option>".
			   "<option value='2'> February</option>".
			   "<option value='3'> March</option>".
			   "<option value='4'> April</option>".
			   "<option value='5'> May</option>".
			   "<option value='6'> June</option>".
			   "<option value='7'> July</option>".
			   "<option value='8'> August</option>".
			   "<option value='9'> September</option>".
			   "<option value='10'> October</option>".
			   "<option value='11'> November</option>".
			   "<option value='12'> December</option>";
	$RecMoDays = "<option value='0'> Day</option>".
				 "<option value='1'> 01</option>".
				 "<option value='2'> 02</option>".
				 "<option value='3'> 03</option>".
				 "<option value='4'> 04</option>".
				 "<option value='5'> 05</option>".
				 "<option value='6'> 06</option>".
				 "<option value='7'> 07</option>".
				 "<option value='8'> 08</option>".
				 "<option value='9'> 09</option>".
				 "<option value='10'> 10</option>".
				 "<option value='11'> 11</option>".
				 "<option value='12'> 12</option>".
				 "<option value='13'> 13</option>".
				 "<option value='14'> 14</option>".
				 "<option value='15'> 15</option>".
				 "<option value='16'> 16</option>".
				 "<option value='17'> 17</option>".
				 "<option value='18'> 18</option>".
				 "<option value='19'> 19</option>".
				 "<option value='20'> 20</option>".
				 "<option value='21'> 21</option>".
				 "<option value='22'> 22</option>".
				 "<option value='23'> 23</option>".
				 "<option value='24'> 24</option>".
				 "<option value='25'> 25</option>".
				 "<option value='26'> 26</option>".
				 "<option value='27'> 27</option>".
				 "<option value='28'> 28</option>".
				 "<option value='29'> 29</option>".
				 "<option value='30'> 30</option>".
				 "<option value='31'> 31</option>";

	$s = strtotime($eeEvent['ev_begin']);
	$RecDayWk = date("w", $s);
	$RecDayMo = date("j", $s);
	$RecDayYr = date("z", $s);

	echo "<div id='eeRecursOpts' style='display:".$r."'>";
	echo "<table class=iapTable>";
	echo "<tr></td><td width='25%'></td><td width='75%'></td></tr>";
	echo "<tr><td colspan='2'>Repetition Criteria</td></tr>";

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
	echo "<tr></td><td colspan='2'>";
		echo "<table class=iapTable><tr><td style='width: 5%;'></td><td width='15%' valign='top'>";
		echo "<div id='eeRecurOptions'>";
			echo "<input id='eerecuropt_1' name='eerecuropt' type='radio' value='D'".$optd.
				 " onclick=\"return toggleOpts('d')\" /> Daily<br /><br />";

			echo "<input id='eerecuropt_2' name='eerecuropt' type='radio' value='W'".$optw.
				 " onclick=\"return toggleOpts('w')\" /> Weekly<br /><br />";

			echo "<input id='eerecoropt_3' name='eerecuropt' type='radio' value='M'".$optm.
				 " onclick=\"return toggleOpts('m')\" /> Monthly<br /><br />";

			echo "<input id='eerecoropt_4' name='eerecuropt' type='radio' value='A'".$opta.
				 " onclick=\"return toggleOpts('a')\" /> Annual<br /><br />";


// show examples


		echo "</div></td><td style='width: 5%;'></td><td width='75%' valign='top'>";

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
				echo " Every weekday (Monday through Friday, No weekends).<br /><br />";
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
				echo " On ".
					 "<select name='eewk_1A'>".$wd1A."</select> every week.<br /><br />";
/*
			echo "<input id='eeweeklyopt_2' name='eeweeklyopt' type='radio' value='w2'".$w2." />";
				echo " Another option<br /><br /><br />";

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
				echo " On day ".
					 "<input type='text' name='eemo_1A' size=2 maxlength=2 value='".$eeEvent['cr_monthly_daynum']."' />".
					 " of every month<br/><br />";

			echo "<input id='eemonthlyopt_2' name='eemonthlyopt' type='radio' value='m2'".$m2." />";
				echo " On the ".
					 "<select name='eemo_2A'>".$mw2A."</select>".
					 "<select name='eemo_2B'>".$md2B."</select> of every month<br /><br />";
/*
			echo "<input id='eemonthlyopt_3' name='eemonthlyopt' type='radio' value='m3'".$m3." />";
				echo " On the last day of month<br /><br /><br />";

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
				echo " On ".
					 "<select name='eean_1A' id='eean_1A' onchange='return load_days_in_mth(this.form.eean_1A.value, \"eean_1B\"'>".
					 $am1A.
					 "</option></select>".
					 " day ".
					 "<select name='eean_1B'>".$ad1B."</option></select><br /><br />";

			echo "<input id='eeannualopt_2' name='eeannualopt' type='radio' value='a2'".$a2." />";
				echo " On the ".
					 "<select name='eean_2A'>".$aw2A."</select> ".
					 " week on ".
					 "<select name='eean_2B'>".$ad2B."</select>".
					 " of ".
					 "<select name='eean_2C'>".$am2C."</select><br /><br />";
	
			echo "<input id='eeannualopt_3' name='eeannualopt' type='radio' value='a3'".$a3." />";
				echo " On day ".
					 "<input type='text' name='eean_3A' size=3 maxlength=3 value='".$eeEvent['cr_annual_daynum'].
					 "' /> of every year.<br /><br />";
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
				$u3date = $eeEvent['ev_end'];
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
			echo "<br />Occurs Until:<br />";
			echo "<input type='radio' name='eerecend' value='n'".$u1." /> No End<br /><br />";

/*
			echo "<input type='radio' name='eerecend' value='o'".$u2." />";
				echo "<input type='text' name='eerecocc' id='eerecocc' size=4 maxlength=4 value='".$eeEvent['cr_until_count']."' /> times.<br /><br />";
*/

			echo "<table class=iapTable><tr><td width='20%'><input type='radio' name='eerecend' value='a'".$u3." /> End After </td>".
				 "<td width='20%'> <script>DateInput('eerecdate', true, 'YYYY-MON-DD', '".$u3date."')</script></td><td width='60%'> </td></tr></table><br /><br />";
		echo "</div>";
		echo "</td></tr></table>";

	echo "</td></tr>";
	echo "<tr><td style='width: 5%;'></td><td width='20%'></td><td width='75%'></td></tr>";
	echo "<tr><td style='width: 5%;'></td><td width='20%'></td><td width='75%'></td></tr>";
	echo "</table></div>";

	echo "</table><br />";
	echo "<center><input type='submit' name='submitevent' value='Submit' />";
	if ($eeEvent['status'] != "NEW") {
		echo "<span style='padding-left:50px;'><input type='submit' name='deleteevent' value='Delete' />";
	}
	echo "</center>";

	echo "<input type='hidden' name='IAPA' id='IAPA' value='".$_REQUEST['CoId']."' />";

?>
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

</script>
