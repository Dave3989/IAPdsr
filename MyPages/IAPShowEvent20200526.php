<?php
// TODO Need to find way to get DST value

// The logic here ia a copy of FCGetEvents in the Ajax directory wrapped in a function for calling from PHP
// 		for some reason js did not work to have the jason call outside the function 

function IAP_ee_Messages($IAPMsgText, $IAPDisplay = "Y") {

	global $IAPDebug;

	if ($IAPDisplay == "Y") {
		echo "<span style='font-size:110%'>".$IAPMsgText."</span><br>";
	}
	if ($IAPDebug == "Y") {
		$fdebug = fopen(ABSPATH."IAPDebug.txt", "a");
		fwrite($fdebug, "IAPShowEvent".date("Y/m/d H:i:s").$IAPMsgText.chr(10).chr(13));
		fclose($fdebug);
	}
}

//------------------
// Program start
//------------------

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

$_REQUEST['debugme'] = "N";

global $IAPDebug;
$IAPDebug = "N";
$eeDisplayMsgs = "N";

	$MySite = $_SERVER['HTTP_HOST'];
	switch($MySite) {
		case "iapdsr.com":
			define('DB_NAME', 'litehaus_iapdsr2');
		case "covepoint:8080":
		case "localhost:8080":
			define('DB_NAME', 'litehaus_iapdsr');
			
			break;
		default:
			echo "<span style='color:red; font-size:120%;'>WP-CONFIG: Unknown IAP server [".$MySite."]<br>";
			echo "In ".basename(__FILE__)."/".__LINE__."</span><br><br>";
			die;
	}


if ( ! defined( 'ABSPATH' ) ) {
	$vars = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
	$v = substr($vars, 0, strpos($vars, "/MyPages"))."/";
	define( 'ABSPATH', $v);
}
require_once(ABSPATH."IAPSetVars.php");

$eePath = ABSPATH;

IAP_ee_Messages("Path=".$eePath, $eeDisplayMsgs);

$f = ABSPATH.'IAPDBServices.php';
if (!file_exists($f)) {			
	IAP_ee_Messages("-10|Cannot find ".$f);
	die();
}
require_once(ABSPATH.'IAPDBServices.php');

IAP_ee_Messages("Got DBServices", $eeDisplayMsgs);

$s = "SELECT iap_calendar.*, iap_cal_repeating.* from iap_calendar ".
	 "left join iap_cal_repeating on iap_cal_repeating.cr_id = iap_calendar.ev_id ".
	 "where iap_calendar.ev_id = '".$_REQUEST['eid']."'";

IAP_ee_Messages("/SQL=".$s, $eeDisplayMsgs);

$ret = iapProcessMySQL("select", $s);
if ($ret['retcode'] < 0) {
	die();
}

IAP_ee_Messages("/Did query", $eeDisplayMsgs);

if ($ret['numrows'] == 0) {
	IAP_ee_Messages("-14|NO Events Found", "Y");
	return(FALSE);
}

if ($IAPDebug == "Y") {
	IAP_ee_Messages("/Processing array", $eeDisplayMsgs);
}

$event = $ret['data'][0];

$erows = 12;
$edrows = floor(strlen($event['ev_desc']) / 100);
$edrows = $edrows + (count(explode("\n", $event['ev_desc'])));
$erows = $erows + (int) $edrows;
if ($edrows == 1) {
	$erows = $erows + 2;
}
if ($event['ev_allday'] == "Y") {
	$edrows++;
}	
if ($event['ev_loc_street'] != ""
or ($event['ev_loc_city'] != ""
and $event['ev_loc_state'] != "")
or ($event['ev_loc_zip'] != "")) {
	$edrows = $edrows + 7;		// 5 for generating address and 2 maps link
}
if ($event['ev_link']){
	$erows = $erows + 2;
}
if ($event['ev_link']){
	$erows = $erows + 4;
}
if ($event['ev_recur'] == "Y") {
	$erows = $erows + 4;
	if ($event['cr_until_date'] != "2099-12-31") {
		$edrows++;
	}
}

echo "<span style='font-size:125%;vertical-align:middle;'><center>".$event['ev_title']."</center></span></div><span style='font-size:105%;'><br>";

echo "<table>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";

	echo "<textarea cols='100' rows='".$edrows.
		 "' id='eedesc' name='eedesc' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>".
		 $event['ev_desc'].
		 "</textarea>";
echo "</td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'> </td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";
	echo "Start:&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['ev_begin']));
	if ($event['ev_allday'] != "Y") {
		echo "&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['ev_btime']));
	}
echo "</td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'>";
	echo "End:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['ev_end']));
	if ($event['ev_allday'] != "Y") {
		echo "&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['ev_etime']));
	}
echo "</td><td style='width:25%;'></tr>";

if ($event['ev_allday'] == "Y") {
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>This is an all day event</td><td style='width:25%;'></tr>";
}

if ($event['ev_loc_name'] != ""
or  $event['ev_loc_street'] != ""
or  $event['ev_loc_city'] != ""
or  $event['ev_loc_state'] != ""
or  $event['ev_loc_zip'] != "") {
	$l = trim($event['ev_loc_name'])."|".trim($event['ev_loc_street'])."|".trim($event['ev_loc_city']).", ".trim($event['ev_loc_state'])." ".trim($event['ev_loc_zip']);
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

if ($event['ev_link']){
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'>Web Site:&nbsp;<a href='".$event['ev_link']."' target='_blank'>".$event['ev_link']."</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

if ($event['ev_peid']) {
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
	$iapPELink = ABSPATH."MyPages/IAPPartyEvent.php?action=selected/peid=".strval($event['ev_peid']);
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'><a href='".$iapPELink."'>Click here to view the Party/Event details.</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";	
}

if ($event['ev_recur'] == "Y") {
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

if ($event['ev_loc_street'] != ""
or ($event['ev_loc_city'] != ""
and $event['ev_loc_state'] != "")
or ($event['ev_loc_zip'] != "")) {
	$erows = $erows + 2;
	$eelloc = $event['ev_loc_street']." ".$event['ev_loc_city'].", ".$event['ev_loc_state']." ".$event['ev_loc_zip'];
	$eelloc = str_replace("  ", " ", $eelloc);
	$eelloc = str_replace(" ", "+", $eelloc);
	$eellocurl = "https://www.google.com/maps/place/".$eelloc;
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'><a href='".$eellocurl."' style='text-align:center;' target='_blank'>See on a map</a></td><td style='width:25%;'></tr>";
	echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
}

echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'>(id=".$event['ev_id'].")<td style='width:55%;'></td><td style='width:25%;'></tr>";

echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'></td><td style='width:25%;'></tr>";
echo "<tr><td style='width:20%;'></td><td style='width:55%;'><center><input type='submit' value='Close' onclick='self.close();' /></center></td><td style='width:25%;'></tr>";

echo "</table></body></html>";
//echo "</table></div></body></html>";

?>