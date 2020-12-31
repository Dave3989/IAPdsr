<?php

function iapFormatEvent($ev1) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In FormatEvent.<br>";
	}

	$ev1 = (array) $ev1;
	if ($ev1['ev_allday'] == "Y") {
		$ad = TRUE;
		$st = "00:00";
		$et = "23:59";
	} else {
		$ad = FALSE;
		$st = $ev1['ev_btime'];
		$et = $ev1['ev_etime'];
	}

	$FCTZParm = $_GET['LHCTimeZone'];
//	$dtz = date_default_timezone_set($FCTZParm);
	$dtz = date_default_timezone_set("America/New_York");

	$o = date("P", strtotime($ev1['ev_begin']." ".$ev1['ev_btime']));	 
	if ($o === FALSE) {

// DST does not work so I am setting it myself for testing
		$dst = date("I");	// DST
		if ($dst == 1) {
			$o = "-04:00";
		} else {
			$o = "-05:00";
		}
	}

	$sd = $ev1['ev_begin']."T".$st.$o;
	$ed = $ev1['ev_end']."T".$et.$o;

//		$ev1['ev_title'] = str_replace("'", "\'", $ev1['ev_title']);
//		$ev1['ev_desc'] = str_replace("'", "\'", $ev1['ev_desc']);

	if ($ev1['ev_loc_name'] != ""
	or  $ev1['ev_loc_street'] != ""
	or  $ev1['ev_loc_city'] != ""
	or  $ev1['ev_loc_state'] != ""
	or  $ev1['ev_loc_zip'] != "") {
		$l = trim($ev1['ev_loc_name'])."|".trim($ev1['ev_loc_street'])."|".trim($ev1['ev_loc_city']).", ".trim($ev1['ev_loc_state'])." ".trim($ev1['ev_loc_zip']);
		$a = trim($ev1['ev_loc_street'])."|".trim($ev1['ev_loc_city']).", ".trim($ev1['ev_loc_state'])." ".trim($ev1['ev_loc_zip']);
	} else {
		$l = NULL;
	}
	$l = str_replace("||", "|", $l);
	if ($l == ",") {	// If only the comma added between city and state exists, get rid of it.
		$l = NULL;
	}
	$a = str_replace("||", "|", $a);
	if ($a == ",") {	// If only the comma added between city and state exists, get rid of it.
		$a = NULL;
	}

	if ($ev1['ev_recur'] == "Y") {
		if ($ev1['cr_until_count'] > 0) {
			$until_msg = "for ".number_format((float) $ev1['cr_until_count'], 0, '.', ',')." times";
		} elseif ($ev1['cr_until_date'] == "2099-12-31") {
			$until_msg = "with no end";
		} else {
			$until_msg = "ending on ".date('F j, Y', strtotime($ev1['cr_until_date']));
		}
	} else {
		$until_msg = "";
	}
	
	if ($ev1['ev_account'] == 0) {
		$bg = "#FFFF8C";
		$br = "yellow";
		$tx = "black";
	} else {
		$bg = "#FFC04D";
		$br = "orange";
		$tx = "black";
	}

	$event = array("id" => $ev1['ev_id'],
		  "title" => $ev1['ev_title'],
		  "start" => $sd,
		  "end" => $ed,
	  	  "bdate" => $ev1['ev_begin'],
	  	  "btime" => $st,
	  	  "edate" => $ev1['ev_end'],
	  	  "etime" => $et,
	  	  "description" => $ev1['ev_desc'],
		  "allDay" => $ad,
		  "location" => $l,
		  "address" => $a,
		  "account" => $ev1['ev_account'],
		  "web" => $ev1['ev_link'],
		  "repeats" => $ev1['ev_recur'],
		  "type" => $ev1['cr_type'],
		  "daily_option" => $ev1['cr_daily_option'],
		  "weekly_option" => $ev1['cr_weekly_option'],
		  "weekly_dow" => $ev1['cr_weekly_dow'],
		  "monthly_option" => $ev1['cr_monthly_option'],
		  "monthly_daynum" => $ev1['cr_monthly_daynum'],
		  "monthly_wknum" => $ev1['cr_monthly_wknum'],
		  "monthly_dow" => $ev1['cr_monthly_dow'],
		  "annual_option" => $ev1['cr_annual_option'],
		  "annual_month1A" => $ev1['cr_annual_month1A'],
		  "annual_dom" => $ev1['cr_annual_dom'],
		  "annual_wknum" => $ev1['cr_annual_wknum'],
		  "annual_month2C" => $ev1['cr_annual_month2C'],
		  "annual_dow" => $ev1['cr_annual_dow'],
		  "annual_daynum" => $ev1['cr_annual_daynum'],
		  "interval" => $ev1['cr_interval'],
		  "until_count" => $ev1['cr_until_count'], 
		  "until_date" => $ev1['cr_until_date'],
		  "until_msg" => $until_msg,
		  "FROM" => "LHCEE",
		  "backgroundColor" => $bg,
		  "borderColor" => $br,
		  "textColor" => $tx
	);
	return($event);
};
?>