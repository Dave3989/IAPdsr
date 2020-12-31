<?php

function FCGetMain() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In FCGetMain.<br>";
	}

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
	require_once($_REQUEST['IAPPath']."Ajax/IAPCalendar/IAPFormatEvent.php");

	foreach($evAll as $ev1) {
		$formatted_ev1 = iapFormatEvent($ev1);
		array_push($events, $formatted_ev1);
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


	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In eeGetRegular.<br>";
	}



/*
SELECT iap_calendar.*, iap_cal_repeating.* from iap_calendar 

left join iap_cal_repeating 
   on iap_cal_repeating.cr_id = iap_calendar.ev_id 

where (ev_account = '0' OR ev_account ='5') 
   AND ((ev_begin >= '2015-05-31' 
      AND ev_begin <= '2015-07-05') 
   OR (ev_begin < '2015-05-31' 
      AND ev_end >= '2015-05-31') 
   OR (ev_begin < '2015-07-01' 
      AND ev_end > '2015-07-05')) 
   AND ev_recur = 'N' 

order by ev_account, ev_begin
*/


	$FCStParm = $_GET['start'];
	$FCEnParm = $_GET['end'];

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Starting with FCStParm of ".$FCStParm."/FCEnParm of ".$FCEnParm.chr(10).chr(13));
		fclose($fdebug);
	}

	$FC_Start = date("Y-m-d", strtotime($FCStParm));
	$FC_End = date("Y-m-d", strtotime($FCEnParm));

	$FC_Next = date("Y-m", strtotime($FC_End." +1 day"))."-01";
	$FC_Today = date("Y-m");

	$FC_Acct = $_GET['LHCA'];

/*
	$FC_Accts = "";
	if (is_array($FC_Acct)) {
		foreach($FC_Acct as $a) {
			$FC_Accts = $FC_Accts."' OR ev_account ='".strval($a);
		}
	} else {
		if ($FC_Acct != 0) {
			$FC_Accts = "' OR ev_account ='".strval($FC_Acct);
		}
	}
		 "where (ev_account = '0".$FC_Accts.
*/




	$s = "SELECT iap_calendar.*, iap_cal_repeating.* from iap_calendar ".

		 "left join iap_cal_repeating on iap_cal_repeating.cr_id = iap_calendar.ev_id ".
		 "where (ev_account = '".strval($FC_Acct).
	 							  "') AND ((ev_begin >= '".$FC_Start.
								  "' AND ev_begin <= '".$FC_End.
								  "') OR (ev_begin < '".$FC_Start.
								  "' AND ev_end >= '".$FC_Start.
								  "') OR (ev_begin < '".$FC_Next.
								  "' AND ev_end > '".$FC_End.
								  "')) AND ev_recur = 'N'".
		 " order by ev_account, ev_begin";

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

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In eeGetRepeats.<br>";
	}


	$MTbl = array("", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
	$WTbl = array("", "1st", "2nd", "3rd", "4th", "Last");
	$DTbl = array("", "sun", "mon", "tue", "wed", "thu", "fri", "sat");

	$FCStParm = strtotime(date("Y-m-d", strtotime($_GET['start']))." 00:00:00");
	$FCEnParm = strtotime(date("Y-m-d", strtotime($_GET['end']))." 00:00:00");
	$FC_Acct = $_GET['LHCA'];
	$FC_Accts = "";
	if (is_array($FC_Acct)) {
		foreach($FC_Acct as $a) {
			$FC_Accts = $FC_Accts."' OR ev_account ='".strval($a);
		}
	} else {
		if ($FC_Acct != 0) {
			$FC_Accts = "' OR ev_account ='".strval($FC_Acct);
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

		$s = "SELECT iap_cal_repeating.*, iap_calendar.* from iap_cal_repeating ".
			 "JOIN iap_calendar on iap_calendar.ev_id = iap_cal_repeating.cr_id ".
			 "where (".$MthFld."='D' OR ".$MthFld."='Y')".
					" AND (ev_account = '0".$FC_Accts."')".
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
				$ev1['OrigStartTS'] = strtotime(date("Y-m-d", $ev1['ev_start_timestamp'])." 00:00:00");
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

$t8 = date("d", $eeThis)." ".date("w", $eeThis);

			$eeWeek = ceil(abs((date("d", $eeThis))) / 7);
			if (date("d", $eeThis) != 1) {
				$eeWeek + 1;
			}
			$eeWeekFld = "cr_wk".$eeWeek;
	
			$eeDay = strtolower(date("D", $eeThis));
			$eeDayFld = "cr_".$eeDay;

			foreach($eePossibles as $eePossible) {
				$use = "N";

$t9 = strtotime($eePossible['ev_begin']." ".$eePossible['ev_btime']);
$t10 = strtotime($eePossible['ev_end']." ".$eePossible['ev_etime']);
$t7 = date("Y-m-d H:i:s", $eeThis)." ".date("Y-m-d H:i:s", $eePossible['OrigStartTS']);
$t11 = localtime($eePossible['OrigStartTS'],$is_associative = true);


if ($eePossible['ev_begin'] == "2015-06-22"
and date("Y-m-d", $eeThis) == "2015-06-22") {
	$a = 1;
}

				if ($eeThis >= $eePossible['OrigStartTS']) {
//					1434924000			1435010400
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
							if ($eeThisStart >= strtotime(date("Y-m-d", $eePossible['ev_start_timestamp'])." 00:00:00")
							and ($eePossible['cr_until_date'] == "2099-12-31"
							or $eeThisStart <= strtotime($eePossible['cr_until_date']." 00:00:00"))) {
								$GoodEvent = $eePossible;
								$dur = $eePossible['ev_end_timestamp'] - $eePossible['ev_start_timestamp'];
								$GoodEvent['ev_start_timestamp'] = $eeThisStart;
								$GoodEvent['ev_end_timestamp'] = $GoodEvent['ev_start_timestamp'] + $dur;
								$GoodEvent['ev_begin'] = date("Y-m-d", $GoodEvent['ev_start_timestamp']);
								$GoodEvent['ev_end'] = date("Y-m-d", $GoodEvent['ev_end_timestamp']);
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
	
	$s = "SELECT iap_cal_repeating.*, iap_calendar.* from iap_cal_repeating ".
		 "JOIN iap_calendar on iap_calendar.ev_id = iap_cal_repeating.cr_id ".
		 "where cr_type = 'A' AND cr_annual_option = 'a3'".
			" AND (ev_account = '0".$FC_Accts."')".
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
					$dur = $ev1['ev_end_timestamp'] - $ev1['ev_start_timestamp'];
					$GoodEvent['ev_start_timestamp'] = $eeThisStart;
					$GoodEvent['ev_end_timestamp'] = $GoodEvent['ev_start_timestamp'] + $dur;
					$GoodEvent['ev_begin'] = date("Y-m-d", $GoodEvent['ev_start_timestamp']);
					$GoodEvent['ev_end'] = date("Y-m-d", $GoodEvent['ev_end_timestamp']);
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

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$WksTbl = array("", "first", "second", "third", "fourth", "fifth");
	$DaysTbl = array("", "sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");

	$rdbegymd = date("Y", $ev1['ev_start_timestamp'])."-".$eeMth."-01";
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
	$tsdiff = $ev1['ev_end_timestamp'] - $ev1['ev_start_timestamp'];
	$ev1['ev_start_timestamp'] = $rdrealts;
	$ev1['ev_end_timestamp'] = $rdrealts + $tsdiff;
	$ev1['ev_begin'] = date("Y-m-d", $ev1['ev_start_timestamp']);
	$ev1['ev_end'] = date("Y-m-d", $ev1['ev_end_timestamp']);

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." M2 calculated date is ".$rdrealymd.chr(10).chr(13));
		fclose($fdebug);
	}

	return;
}

function eeGetDB($eeSQL) {
	GLOBAl $IAPDataConn;

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$dbsrv = $_REQUEST['IAPPath']."/IAPDBServices.php";
	require_once($dbsrv);
	$ret = IAPProcessMySQL("select", $eeSQL);

	if ($ret['mserrno'] != 0) {
		$eeErr = array('retcode' => -301,
						'retmsg' => "SQL Error",
						'sql' => $IAPSql,
						'mserrno' => $IAPDataConn->errno,
						'mserrmsg' => $IAPDataConn->error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		IAP_MySQL_Error($eeErr);
		return($eeErr);
	}


	return($ret);
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