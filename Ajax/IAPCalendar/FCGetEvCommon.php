<?php

function FCGetMain() {

	$evReg = eeGetRegular();
	if ($evReg === FALSE) {
		$evReg = array();
	} 

	$evRep = eeGetRepeats();
	if ($evRep === FALSE) {
		$evRep = array();
	}
	$evAll = array_merge($evReg, $evRep);

	$events = array();
	foreach($evAll as $ev1) {
		
		$ev1 = (array) $ev1;
		if ($ev1['event_allday'] == "Y") {
			$ad = TRUE;
			$st = "00:00";
			$et = "23:59";
		} else {
			$ad = FALSE;
			$st = $ev1['event_btime'];
			$et = $ev1['event_etime'];
		}


		$FCTZParm = $_GET['LHCTimeZone'];
//	$dtz = date_default_timezone_set($FCTZParm);
		$dtz = date_default_timezone_set("America/New_York");

		$o = date("P", strtotime($ev1['event_begin']." ".$ev1['event_btime']));	 
		if ($o === FALSE) {

	// DST does not work so I am setting it myself for testing
			$dst = date("I");	// DST
			if ($dst == 1) {
				$o = "-04:00";
			} else {
				$o = "-05:00";
			}
		}

		$sd = $ev1['event_begin']."T".$st.$o;
		$ed = $ev1['event_end']."T".$et.$o;

		if ($ev1['event_loc_name'] != ""
		or  $ev1['event_loc_street'] != ""
		or  $ev1['event_loc_city'] != ""
		or  $ev1['event_loc_state'] != ""
		or  $ev1['event_loc_zip'] != "") {
			$l = trim($ev1['event_loc_name'])."|".trim($ev1['event_loc_street'])."|".trim($ev1['event_loc_city']).", ".trim($ev1['event_loc_state'])." ".trim($ev1[event_loc_zip]);
		} else {
			$l = NULL;
		}
		$l = str_replace("||", "|", $l);
		if ($l == ",") {	// If only the comma added between city and state exists, get rid of it.
			$l = NULL;
		}

		if ($ev1['event_recur'] == "Y") {
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
		
		if ($ev1['event_account'] == 0) {
			$bg = "#FFFF8C";
			$br = "yellow";
			$tx = "black";
		} else {
			$bg = "#FFC04D";
			$br = "orange";
			$tx = "black";
		}

		array_push($events,
			array("id" => $ev1[event_id],
				  "title" => $ev1['event_title'],
				  "start" => $sd,
				  "end" => $ed,
			  	  "bdate" => $ev1['event_begin'],
			  	  "btime" => $st,
			  	  "edate" => $ev1['event_end'],
			  	  "etime" => $et,
			  	  "description" => $ev1['event_desc'],
				  "allDay" => $ad,
				  "location" => $l,
				  "account" => $ev1['event_account'],
				  "web" => $ev1['event_link'],
				  "repeats" => $ev1['event_recur'],
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
			)
		);
	}

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		foreach($events as $E) {
			fwrite($fdebug, "Ajax/FCGetEvents ".
							date("Y/m/d H:i:s").
							"/ID=".$E['id'].
							"/TITLE=".$E['title'].
							"/START=".date("Ymd",$E['start']).
							"/END=".date("Ymd",$E['end']).
							"/ALLDAY=".$E['allDay'].
							"/ACCOUNT=".$E['account'].
							chr(10).chr(13)
				  );
		}
		fclose($fdebug);
	}
	return ($events);
}


function eeGetRegular() {

	$FCStParm = $_GET['start'];
	$FCEnParm = $_GET['end'];


	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Starting with FCStParm of ".$FCStParm."/FCEnParm of ".$FCEnParm.chr(10).chr(13));
		fclose($fdebug);
	}

	$FC_Start = date("Y-m-d", $FCStParm);
	$FC_End = date("Y-m-d", $FCEnParm);

	$FC_Next = date("Y-m", strtotime($FC_End." +1 day"))."-01";
	$FC_Today = date("Y-m");

	$FC_Acct = $_GET['LCHA'];
	$FC_Accts = "";
	if (is_array($FC_Acct)) {
		foreach($FC_Acct as $a) {
			$FC_Accts = $FC_Accts."' OR event_account ='".strval($a);
		}
	} else {
		if ($FC_Acct != 0) {
			$FC_Accts = "' OR event_account ='".strval($FC_Acct);
		}
	}

	$s = "SELECT lhc_calendar.*, lhc_cal_repeating.* from lhc_calendar ".
		 "left join lhc_cal_repeating on lhc_cal_repeating.cr_id = lhc_calendar.event_id ".
		 "where (event_account = '0".$FC_Accts.
	 							  "') AND ((event_begin >= '".$FC_Start.
								  "' AND event_begin <= '".$FC_End.
								  "') OR (event_begin < '".$FC_Start.
								  "' AND event_end >= '".$FC_Start.
								  "') OR (event_begin < '".$FC_Next.
								  "' AND event_end > '".$FC_End.
								  "')) AND event_recur = 'N'".
		 " order by event_account, event_begin";



	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents".date("Y/m/d H:i:s")."/SQL=".$s.chr(10).chr(13));
		fclose($fdebug);
	}

	$ret = (array) eeGetDB($s);
	if ($ret['retcode'] < 0) {
		return(false);
	}

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents".date("Y/m/d H:i:s")."/Did query".chr(10).chr(13));
		fclose($fdebug);
	}

	if ($ret['numrows'] == 0) {
		if ($MyDebug == "Y"){
			$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
			fwrite($fdebug, "Ajax/FCGetEvents".date("Y/m/d H:i:s")."-14|NO Events Found\n");
			fclose($fdebug);
		}

		return(false);
	}

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents".date("Y/m/d H:i:s")."/Processing array".chr(10).chr(13));
		fclose($fdebug);
	}

	$evs = $ret['data'];

	if (empty($evs)) {
		return(FALSE);
	} else {
		return($evs);
	}
}

function eeGetRepeats() {

	$MTbl = array("", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
	$WTbl = array("", "1st", "2nd", "3rd", "4th", "Last");
	$DTbl = array("", "sun", "mon", "tue", "wed", "thu", "fri", "sat");

	$FCStParm = strtotime(date("Y-m-d", $_GET['start'])." 00:00:00");
	$FCEnParm = strtotime(date("Y-m-d", $_GET['end'])." 00:00:00");
	$FC_Acct = $_GET['LCHA'];
	$FC_Accts = "";
	if (is_array($FC_Acct)) {
		foreach($FC_Acct as $a) {
			$FC_Accts = $FC_Accts."' OR event_account ='".strval($a);
		}
	} else {
		if ($FC_Acct != 0) {
			$FC_Accts = "' OR event_account ='".strval($FC_Acct);
		}
	}

	$FC_Date = date("Y-m-d", $FCStParm);

	$evr = array();
	$eePossibles = array();
	$TestMth = strtotime(date("Y-m-", $FCStParm).date("d", $FCEnParm));
	while($TestMth <= $FCEnParm) {
		$eeMth = date("n", $TestMth);
		$eeMonth = $MTbl[$eeMth];
		$MthFld = "cr_".$eeMonth;

		$s = "SELECT lhc_cal_repeating.*, lhc_calendar.* from lhc_cal_repeating ".
			 "JOIN lhc_calendar on lhc_calendar.event_id = lhc_cal_repeating.cr_id ".
			 "where (".$MthFld."='D' OR ".$MthFld."='Y')".
					" AND (event_account = '0".$FC_Accts."')".
					" AND cr_until_date > '".$FC_Date."';";

		if ($MyDebug == "Y") {
			$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
			fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /SQL=".$s.chr(10).chr(13));
			fclose($fdebug);
		}

		$ret = (array) eeGetDB($s);
		if ($ret['retcode'] < 0) {
			return(false);
		}

		if ($MyDebug == "Y") {
			$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
			fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Did query".chr(10).chr(13));
			fclose($fdebug);
		}

		$ev = $ret['numrows'];
		$evs = $ret['data'];

		if ($MyDebug == "Y") {
			$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
			fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Processing array".chr(10).chr(13));
			fclose($fdebug);
		}

		if ($ev > 0) {
			foreach($evs as $ev1) {
				$ev1['ThisMonth'] = $eeMth;
				$ev1['OrigStartTS'] = strtotime(date("Y-m-d", $ev1['event_start_timestamp'])." 00:00:00");
				switch($ev1['cr_type']) {
					case "D":
						break;
					case "W":
						break;
					case "M":
						if ($ev1['cr_monthly_option'] == "m2") {
							eeRecalculateDate($ev1, $eeMth, $ev1['cr_monthly_wknum'], $ev1['cr_monthly_dow']);
						}
						break;
					case "A":
						if ($ev1['cr_annual_option'] == "a2"
						and $ev1['cr_annual_month2C'] == $eeMth) {
							eeRecalculateDate($ev1, $eeMth, $ev1['cr_annual_wknum'], $ev1['cr_annual_dow']);
						}
						break;
				}

				$eePossibles[] = $ev1;
			}
		}
		$TestMth = strtotime("next month", $TestMth);
	}
	if (count($eePossibles) > 0) {

		$eeThis = $FCStParm;
		while($eeThis <= $FCEnParm) {

			$DebugDate = date("Y-m-d", $eeThis);
			if ($MyDebug == "Y") {
				$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
				fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Processing date ".$DebugDate.(10).chr(13));
				fclose($fdebug);
			}

$t7 = date("d", $eeThis)." ".date("w", $eeThis);

			$eeWeek = ceil(abs((date("d", $eeThis))) / 7);
			if (date("d", $eeThis) != 1) {
				$eeWeek + 1;
			}
			$eeWeekFld = "cr_wk".$eeWeek;
	
			$eeDay = strtolower(date("D", $eeThis));
			$eeDayFld = "cr_".$eeDay;

			foreach($eePossibles as $eePossible) {
				$use = "N";

$t7 = date("Y-m-d H:i:s", $eeThis)." ".date("Y-m-d H:i:s", $eePossible['OrigStartTS']); //  eethis=1345089600   origstart=1345114800
				if ($eeThis >= $eePossible['OrigStartTS']) {

$t4 = date("n", $eeThis)." ".$eePossible['ThisMonth'];
$t5 = $eePossible[$m2]." ".$eePossible['cr_day']." ".date("j", $eeThis);
$t6 = $eePossible[$eeWeekFld]." ".$eePossible[$eeDayFld];

					if (date("n", $eeThis) == $eePossible['ThisMonth']) {
						$m1 = $MTbl[date("n", $eeThis)];
						$m2 = "cr_".$m1;
						if ($eePossible[$m2] == "D"
						  and $eePossible['cr_day'] == date("j", $eeThis)) {
							$use = "Y";
						} elseif ($eePossible[$eeWeekFld] == "Y"
						  and $eePossible[$eeDayFld] == "Y"){
							$use = "Y";
						}
						if ($use == "Y"){
							$eeThisStart = mktime(0, 0, 0, $eePossible['ThisMonth'], date("j", $eeThis), date("Y", $eeThis));
							if ($eeThisStart >= strtotime(date("Y-m-d", $eePossible['event_start_timestamp'])." 00:00:00")
							and ($eePossible['cr_until_date'] == "2099-12-31"
							or $eeThisStart <= strtotime($eePossible['cr_until_date']." 00:00:00"))) {
								$GoodEvent = $eePossible;
								$dur = $eePossible['event_end_timestamp'] - $eePossible['event_start_timestamp'];
								$GoodEvent['event_start_timestamp'] = $eeThisStart;
								$GoodEvent['event_end_timestamp'] = $GoodEvent['event_start_timestamp'] + $dur;
								$GoodEvent['event_begin'] = date("Y-m-d", $GoodEvent['event_start_timestamp']);
								$GoodEvent['event_end'] = date("Y-m-d", $GoodEvent['event_end_timestamp']);
								$evr[] = $GoodEvent;
							}
						}
					}
				}
			}
			$eeThis = strtotime("+1 day", $eeThis);			
		}
	}

//////////// Check for A3 repeats
	
	$s = "SELECT lhc_cal_repeating.*, lhc_calendar.* from lhc_cal_repeating ".
		 "JOIN lhc_calendar on lhc_calendar.event_id = lhc_cal_repeating.cr_id ".
		 "where cr_type = 'A' AND cr_annual_option = 'a3'".
			" AND (event_account = '0".$FC_Accts."')".
			" AND cr_until_date > '".$FC_Date."';";

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /SQL=".$s.chr(10).chr(13));
		fclose($fdebug);
	}

	$ret = (array) eeGetDB($s);
	if ($ret['retcode'] < 0) {
		return(false);
	}

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Did query".chr(10).chr(13));
		fclose($fdebug);
	}

	$ev = $ret['numrows'];
	$evs = $ret['data'];

	if ($ev > 0) {
	
		if ($MyDebug == "Y") {
			$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
			fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Processing array".chr(10).chr(13));
			fclose($fdebug);
		}

		$sd = date('z', $FCStParm) + 1;
		$ed = date('z', $FCEnParm) + 1;

		foreach($evs as $ev1) {
			if ($ev1['cr_day'] < $sd) {
				$evY = date("Y", $FCEnParm);
			} else {
				$evY = date("Y", $FCStParm);
			}
			$yd1 = mktime(0, 0, 0, 12, 31, $evY - 1);
			$d1 = date("Y-m-d", $yd1);
//			$yd2 = $yd1 + ($ev1['cr_day'] * 86400);
			$yd2 = strtotime("+".$ev1['cr_day']." days", $yd1);
			$d2 = date("Y-m-d", $yd2);
			if ($yd2 >= $FCStParm 
			and $yd2 <= $FCEnParm) {
				$eeThisStart = mktime(0, 0, 0, date('n', $yd2), date("j", $yd2), date("Y", $yd2));
				if ($ev1['cr_until_date'] == "2099-12-31"
				or $eeThisStart <= strtotime($ev1['cr_until_date'])) {
					$GoodEvent = $ev1;
					$dur = $ev1['event_end_timestamp'] - $ev1['event_start_timestamp'];
					$GoodEvent['event_start_timestamp'] = $eeThisStart;
					$GoodEvent['event_end_timestamp'] = $GoodEvent['event_start_timestamp'] + $dur;
					$GoodEvent['event_begin'] = date("Y-m-d", $GoodEvent['event_start_timestamp']);
					$GoodEvent['event_end'] = date("Y-m-d", $GoodEvent['event_end_timestamp']);
					$evr[] = $GoodEvent;
				}
			}
		}
	}


	if (count($evr) == 0) {
		return(FALSE);
	}
	return($evr);
}

function eeRecalculateDate(&$ev1, $eeMth, $eeWkNum, $eeDow) {

	$WksTbl = array("", "first", "second", "third", "fourth", "fifth");
	$DaysTbl = array("", "sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");

	$rdbegymd = date("Y", $ev1['event_start_timestamp'])."-".$eeMth."-01";
	$rdbegts = strtotime($rdbegymd);
$t1 = date("w", $rdbegts);
	if ( $eeWkNum == 1
	and date("w", $rdbegts) + 1 == $ev1['cr_monthly_dow']) {
		$rdrealts = $rdbegts;	
	} else {
		$rdwk = $WksTbl[$eeWkNum];
		$rdday = $DaysTbl[$eeDow];
$t2 = $rdwk." ".$rdday;
		$rdrealts = strtotime($rdwk." ".$rdday, $rdbegts);
	}
$t3 = date("Y-m-d", $rdrealts);
	$tsdiff = $ev1['event_end_timestamp'] - $ev1['event_start_timestamp'];
	$ev1['event_start_timestamp'] = $rdrealts;
	$ev1['event_end_timestamp'] = $rdrealts + $tsdiff;
	$ev1['event_begin'] = date("Y-m-d", $ev1['event_start_timestamp']);
	$ev1['event_end'] = date("Y-m-d", $ev1['event_end_timestamp']);

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." M2 calculated date is ".$rdrealymd.chr(10).chr(13));
		fclose($fdebug);
	}

	return;
}

function eeGetDB($eeSQL) {
	global $LHCDBConn;

	if (empty($LHCDBConn)) {
		$f = ABSPATH."LHCDataConfig.php";
		require_once(ABSPATH."LHCDataConfig.php");
	}

	$eeRes = $LHCDBConn->query($eeSQL);

	if ($LHCDBConn->errno != 0) {
		$eeErr = array('retcode' => -301,
						'retmsg' => "SQL Error",
						'mserrno' => $LHCDBConn->errno,
						'mserrmsg' => $LHCDBConn->error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		LHC_MySQL_Error($eeErr);
		return($eeErr);
	}

	$r = $eeRes->num_rows;
  	$d = array();
	if ($r > 0) {
		$eeRes->data_seek(0);
		while ($row = $eeRes->fetch_assoc()) {
			$d[] = $row;
		}
		$eeRes->free();
	}

	return(array('retcode' => 0,
			 	 'retmsg' => "Good return",
				 'numrows' => $r,
				 'mserrno' => NULL,
				 'mserrmsg' => NULL,
				 'data' => $d));
}

/*
id - String/Integer. Optional
Uniquely identifies the given event. Different instances of repeating events should all have the same id.

title - String. Required.
The text on an event's element

allDay - true or false. Optional.
Whether an event occurs at a specific time-of-day. This property affects whether an event's time is shown. Also, in the agenda views, determines if it is displayed in the "all-day" section.
Don't include quotes around your true/false. This value is not a string!
When specifying Event Objects for events or eventSources, omitting this property will make it inherit from allDayDefault, which is normally true.

start - Date. Required.
The date/time an event begins.
When specifying Event Objects for events or eventSources, you may specify a string in IETF format (ex: "Wed, 18 Oct 2009 13:00:00 EST"), a string in ISO8601 format (ex: "2009-11-05T13:15:30Z") or a UNIX timestamp.

end - Date. Optional.
The date/time an event ends.
As with start, you may specify it in IETF, ISO8601, or UNIX timestamp format.

If an event is all-day...
the end date is inclusive. This means an event with start Nov 10 and end Nov 12 will span 3 days on the calendar.

If an event is NOT all-day...
the end date is exclusive. This is only a gotcha when your end has time 00:00. It means your event ends on midnight, and it will not span through the next day.

url - String. Optional.
A URL that will be visited when this event is clicked by the user. For more information on controlling this behavior, see the eventClick callback.

className - String/Array. Optional.
A CSS class (or array of classes) that will be attached to this event's element.

editable - true or false. Optional.
Overrides the master editable option for this single event.

source - Event Source Object. Automatically populated.
A reference to the event source that this event came from.
New options have been added in version 1.5 to change an event's colors:

color - Sets an event's background and border color just like the calendar-wide eventColor option.

backgroundColor - Sets an event's background color just like the calendar-wide eventBackgroundColor option.

borderColor - Sets an event's border color just like the the calendar-wide eventBorderColor option.

textColor - Sets an event's text color just like the calendar-wide eventTextColor option.

*/

?>