<?php
// TODO Need to find way to get DST value

// The logic here ia a copy of FCGetEvents in the Ajax directory wrapped in a function for calling from PHP
// 		for some reason js did not work to have the jason call outside the function 

function IAP_ee_Error($IAPMsgText, $IAPDisplay = "Y") {

	global $IAPDebug;

	if ($IAPDisplay == "Y") {
		echo "<span style='color:red; font-size:110%'>".$IAPMsgText."</span>";		
	}
	if ($IAPDebug == "Y") {
		$fdebug = fopen(ABSPATH."IAPDebug.txt", "a");
		fwrite($fdebug, "HPShowEvent".date("Y/m/d H:i:s").$IAPMsgText.chr(10).chr(13));
		fclose($fdebug);
	}
}

//------------------
// Program start
//------------------

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__LINE__.")";

$_REQUEST['debugme'] = "N";

global $IAPDebug;
$IAPDebug = "N";


$eePath = str_replace("\\", "/", dirname(__FILE__));
$eeP = explode("/", $eePath);
for ($i = 0; $i < count($eeP); $i++) {
	$ln = array_pop($eeP);
	$lnsm = strtolower($ln);
	if (strpos($lnsm, "iap") !== FALSE
	or strpos($lnsm, "itsaparty") !== FALSE) {
		array_push($eeP, $ln);
		$eePath = implode("/", $eeP);
		break;
	}
}

if (!defined('ABSPATH')) {
	define('ABSPATH', $eePath.'/');
}

IAP_ee_Error("URL=".$eeP, "N");

$f = ABSPATH.'IAPDBServices.php';
if (!file_exists($f)) {			
	IAP_ee_Error("-10|Cannot find ".$f);
	return(FALSE);
}
include_once(ABSPATH.'IAPDBServices.php');

IAP_ee_Error("/Got DBServices", "N");

$s = "SELECT iap_calendar.*, iap_cal_repeating.* from iap_calendar ".
	 "left join iap_cal_repeating on iap_cal_repeating.cr_id = iap_calendar.event_id ".
	 "where iap_calendar.event_id = '".$_REQUEST['eid']."'";

IAP_ee_Error("/SQL=".$s, "N");

$ret = iapProcessMySQL("select", $s);
if ($ret['retcode'] < 0) {
	return(false);
}

IAP_ee_Error("/Did query", "N");

if ($ret['numrows'] != 1) {
	IAP_ee_Error("-14|NO Events Found");
	return(FALSE);
}

if ($IAPDebug == "Y") {
	IAP_ee_Error("/Processing array");
}

$e = $ret['data'];
$event = $e[0];

$erows = 4;
//echo "<div style='background-color:whitesmoke;border-style:solid;border-width:2px;border-color:darkgray;height:25px;'>";
echo "<span style='font-size:125%;vertical-align:middle;'><center>".$event['event_title']."</center></span></div><span style='font-size:105%;'><br>";
$edrows = floor(strlen($event['event_desc']) / 100);
$edrows = $edrows + (count(explode("\n", $event['event_desc'])));
$erows = $erows + (int) $edrows;
if ($edrows == 1) {
	$erows = $erows + 2;
}
echo "<table>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";
	echo "<textarea cols='100' rows='".$edrows."' id='eedesc' name='eedesc' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>".$event['event_desc']."</textarea>";
echo "</td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'> </td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";
	echo "Start:&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['event_begin']));
	if ($event['event_allday'] != "Y") {
		echo "&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['event_btime']));
	}
echo "</td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";
	echo "End:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['event_end']));
	if ($event['event_allday'] != "Y") {
		echo "&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['event_etime']));
	}
echo "</td><td style='width:25%;'></tr>";

if ($event['event_allday'] == "Y") {
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>This is an all day event</td><td style='width:25%;'></tr>";
}

if ($event['event_loc_name'] != ""
or  $event['event_loc_street'] != ""
or  $event['event_loc_city'] != ""
or  $event['event_loc_state'] != ""
or  $event['event_loc_zip'] != "") {
	$l = trim($event['event_loc_name'])."|".trim($event['event_loc_street'])."|".trim($event['event_loc_city']).", ".trim($event['event_loc_state'])." ".trim($event['event_loc_zip']);
	$l = str_replace("||", "|", $l);
	if ($l == ",") {	// If only the comma added between city and state get rid of it.
		$l = NULL;
	}
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>Location:</td><td style='width:25%;'></tr>";
	$loc = explode("|", $l);
	$lflds = count($loc);
	$i=0;
	for ($i=0; $i<$lflds; $i++) {
		if ($loc[$i]) {
			$erows = $erows + 1;
			echo "<tr><td style='width:20%;'></td><td style='width:55%;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$loc[$i]."</td><td style='width:25%;'></tr>";
		}
	}
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

if ($event['event_link']){
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>Web Site:&nbsp;<a href='".$event['event_link']."' target='_blank'>".$event['event_link']."</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

if ($event['event_peid']) {
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
	$iapPELink = ABSPATH."MyPages/IAPPartyEvent.php?action=selected/peid=".strval($event['event_peid']);
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'><a href='".$iapPELink."'>Click here to view the Party/Event details.</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
}

if ($event['event_recur'] == "Y") {
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>This event repeats</td><td style='width:25%;'></tr>";

	$repdays = array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
	$repwks = array("", "1st", "2nd", "3rd", "4th", "Last");
	$repmths = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$repmsg = "Every";
	$intvmsg = "";
	if ($event['cr_interval'] > 1) {
		switch($event['cr_interval'].substring(strlen($event['cr_interval']) - 2)) {
			case "1":
				$intvmsg = " ".$event['cr_interval']."st ";
				break;
			case "2":
				$intvmsg = " ".$event['cr_interval']."nd ";
				break;
			case "3":
				$intvmsg = " ".$event['cr_interval']."rd ";
				break;
			default:
				$intvmsg = " ".$event['cr_interval']."th ";
				break;
		}
	}
	$repmsg = $repmsg.$intvmsg;
	switch($event['cr_type']) {
		case "D":	
			switch($event['cr_daily_option']) {
				case "d1":
					$repmsg = $repmsg." day";
					break;
				case "d2":
					$repmsg = $repmsg." weekday";
					break;
			}
			break;
		case "W":
			switch($event['cr_weekly_option']) {
				case "w1":									
					$repmsg = $repmsg." ".$repdays[$event['cr_weekly_dow']]." of every week";
					break;
			}
			break;
		case "M":
			switch($event['cr_monthly_option']) {
				case "m1":
					$repmsg = $repmsg." day ".$event['cr_monthly_daynum']." of every month";									
					break;
				case "m2":									
					$repmsg = $repmsg." ".$repdays[$event['cr_monthly_dow']]." of the ".$repwks[$event['cr_monthly_wknum']]." week of every month";
					break;
			}
			break;
		case "A":
			switch($event['cr_annual_option']) {
				case "a1":
					$repmsg = $repmsg." ".$repmths[$event['cr_annual_month1A']]." ".$event['cr_annual_dom']." of every year";
					break;
				case "a2":									
					$repmsg = $repmsg." day ".$repdays[$event['cr_annual_dow']]." of the ".$repwks[$event['cr_annual_wknum']]." week of ".$repmths[$event['cr_annual_month2C']]+" of every month";
					break;
				case "a3":
					$repmsg = $repmsg." day ".$event['cr_annual_daynum']." of every year";				
					break;
			}
			break;
	}
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'><span style='padding-left:15px;'>".$repmsg.".</span></td><td style='width:25%;'></tr>";

	$erows = $erows + 1;
	if ($event['cr_until_date'] != "2099-12-31") {
		$du_yr = substr($event['cr_until_date'], 0, 4);
		$du_mo = intval(substr($event['cr_until_date'], 5, 2));
		$du_da = substr($event['cr_until_date'], 8, 2);
		echo "<tr><td style='width:20%;'></td><td style='width:55%;'><span style='padding-left:25px;'>Until ".$repmths[$du_mo]." ".$du_da.", ".$du_yr.".</span></td><td style='width:25%;'></tr>";
	}
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

if ($event['event_loc_street'] != ""
or ($event['event_loc_city'] != ""
and $event['event_loc_state'] != "")
or ($event['event_loc_zip'] != "")) {
	$erows = $erows + 2;
	$eelloc = $event['event_loc_street']." ".$event['event_loc_city'].", ".$event['event_loc_state']." ".$event['event_loc_zip'];
	$eelloc = str_replace("  ", " ", $eelloc);
	$eelloc = str_replace(" ", "+", $eelloc);
	$eellocurl = "https://www.google.com/maps/place/".$eelloc;
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'><a href='".$eellocurl."' style='text-align:center;' target='_blank'>See on a map</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'>(id=".$event['event_id'].")<td style='width:55%;'></td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'><center><input type='submit' value='Close' onclick='self.close();' /></center></td><td style='width:25%;'></tr>";

echo "</table></body></html>";

?>