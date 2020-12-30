<?php

function FCWriteEvent($eeEvent, $eeReturnId = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";




// add this to insert and update db text functions

	$eeEvent['ev_title'] = str_replace("'", "''", $eeEvent['ev_title']);
	$eeEvent['ev_desc'] = str_replace("'", "''", $eeEvent['ev_desc']);




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
        return($eeRet);
}
// Returns new event_id if insert.
	if ($eeEvent['status'] == "NEW") {
		$eeEvent['ev_id'] = $eeRet;
	}

// See if event repeats
	if ($eeEvent['ev_recur'] != "Y") {
		IAP_Delete_Row($eeEvent, "iapcrep");
	} else {
// Build new repeating record 
		$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
		if ($eeRepeats < 0) {
	        echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: I cannot create repeating record because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
            return($eeRepeats);
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
            return($eeRet);
    	}
	}

	if ($eeReturnId == "Y") {
		$eeUpdateMsg = strval($eeEvent['ev_id']);
	} else {
		$eeUpdateMsg = "Event ".strval($eeEvent['ev_id'])." Successfully ";
		if ($eeEvent['status'] == "NEW") {
			$eeUpdateMsg = $eeUpdateMsg."Added";
		} else {
			$eeUpdateMsg = $eeUpdateMsg."Updated";
		}
	}
	return($eeUpdateMsg);
}

?>