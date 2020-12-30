<?php

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

//------------------//
//  Program start   //
//------------------//

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

$_REQUEST['debugme'] = "N";

global $IAPDebug;
$IAPDebug = "N";
$eeDisplayMsgs = "N";

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

$evRows = array();

$evRows[] = "<!DOCTYPE html>";
$evRows[] = '<html><head><meta charset="utf-8">';
$evRows[] = "<title>".$event['ev_title']."</title>";
$evRows[] = "</head><body>";
$evRows[] = "<textarea cols='50' rows='1' id='eetitle' name='eetitle' readonly ".
			"style='background-color:whitesmoke; border-style:solid;border-width:2px;border-color:darkgray; resize:none; font-size:125%;  ".
			" text-decoration:bold; text-align:center;'>".$event['ev_title']."</textarea>";
$evRows[] = " ";
$evRows[] = " ";


// TODO find real line length so box is not overly big


$edrows = floor(strlen($event['ev_desc']) / 50);				// hypothetically each line will be 50 characters long
$edrows = $edrows + (count(explode("\n", $event['ev_desc'])));	// split a line if it contains a newline
$erows = $erows + (int) $edrows;								// save the rows as integers to get rid of decimal placez
if ($edrows == 1) {
	$erows = $erows + 2;										// add 2 for blanks at top and bottom
}
$evRows[] = "<textarea cols='50' rows='".
			$edrows.
		 	"' id='eedesc' name='eedesc' wrap='soft' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>".
		 	"\n".$event['ev_desc']."\n".
		 	"</textarea>";
$evRows[] = " ";

$sd = "Start:&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['ev_begin']));
if ($event['ev_allday'] != "Y") {
	$sd = $sd."&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['ev_btime']));
}
$evRows[] = $sd;

$ed = "End:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".date("l F d, Y ", strtotime($event['ev_end']));
if ($event['ev_allday'] != "Y") {
	$ed = $ed."&nbsp;&nbsp;&nbsp;".date(" h:i a",strtotime($event['ev_etime']));
}

if ($event['ev_allday'] == "Y") {
	$edrows++;
	$evRows[] = "This is an all day event";
}
if ($event['ev_loc_street'] != ""
or ($event['ev_loc_city'] != ""
and $event['ev_loc_state'] != "")
or ($event['ev_loc_zip'] != "")) {
	$edrows = $edrows + 7;		// 5 for generating address and 2 maps link
	$evRows[] = "Location:";
	$l = trim($event['ev_loc_name'])."|".trim($event['ev_loc_street'])."|".trim($event['ev_loc_city']).", ".trim($event['ev_loc_state'])." ".trim($event['ev_loc_zip']);
	$l = str_replace("||", "|", $l);
	if ($l == ",") {	// If only the comma added between city and state get rid of it.
		$l = NULL;
	}
	$loc = explode("|", $l);
	$lflds = count($loc);
	$i=0;
	for ($i=0; $i<$lflds; $i++) {
		if ($loc[$i]) {
			$erows = $erows + 1;
			$evRows[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$loc[$i];
		}
	}
// generate map link 
	$eelloc = $event['ev_loc_street']." ".$event['ev_loc_city'].", ".$event['ev_loc_state']." ".$event['ev_loc_zip'];
	$eelloc = str_replace("  ", " ", $eelloc);
	$eelloc = str_replace(" ", "+", $eelloc);
	$eellocurl = "https://www.google.com/maps/place/".$eelloc;
	$evRows[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$eellocurl."' style='text-align:center;' target='_blank'>View map</a>";
	$evRows[] = " ";
}

if ($event['ev_link']){
	$erows = $erows + 2;
	$evRows[] = "Web Site:&nbsp;<a href='".$event['ev_link']."' target='_blank'>".$event['ev_link']."</a>";
	$evRows[] = " ";
}

if ($event['ev_peid']){
	$erows = $erows + 4;
	$evRows[] = " ";
	$evRows[] = " ";
	$iapPELink = ABSPATH."MyPages/IAPPartyEvent.php?action=selected/peid=".strval($event['ev_peid']);
	$evRows[] = "<a href='".$iapPELink."'>Click here to view the Party/Event details.</a>";
	$evRows[] = " ";
}

if ($event['ev_recur'] == "Y") {
	$erows = $erows + 4;
	$evRows[] = "This event repeats ";
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
	$evRows[] = "<span style='padding-left:15px;'>".$repmsg.".</span>";

	$erows = $erows + 1;
	if ($event['cr_until_date'] != "2099-12-31") {
		$edrows++;
		$du_yr = substr($event['cr_until_date'], 0, 4);
		$du_mo = intval(substr($event['cr_until_date'], 5, 2));
		$du_da = substr($event['cr_until_date'], 8, 2);
		$evRows[] = "<span style='padding-left:25px;'>Until ".$repmths[$du_mo]." ".$du_da.", ".$du_yr.".</span>";
	}
	$evRows[] = " ";
}

$evRows[] = " ";
$evRows[] = " ";
$evRows[] = "(id=".$event['ev_id'].")";

$evRows[] = " ";
$evRows[] = " ";
$evRows[] = "<span style='vertical-align:middle; text-indent:100px'>".
			"<input type='submit' style='font:15px Georgia, serif; font-size:115%; background-color:lightgrey;' value=' Close '".
			" onclick='self.close();'>".
			"</span>";
$evRows[] = "</body></html> ";

for($i = 0; $i < count($evRows); $i++) {
	echo $evRows[$i]."<br>";
}

?>
