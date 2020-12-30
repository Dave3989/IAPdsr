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

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__LINE__.")";

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

/*
$evList = "";
$evData = $ret['data'][0];
$evKeys = array_keys($evData);
$evKeys[] = "ev_location";	// concatenate address fields
$evKeys[] = "ev_address"; 	// concatenate address fields
$evC = " ";
$evKeyXlate = array(
	"ev_account" => "account",
	"ev_id" => "id",
	"ev_title" => "title",
	"ev_desc" => "description",
	"ev_begin" => "bdate",
	"ev_btime" => "btime",
	"ev_end" => "edate",
	"ev_etime" => "etime",
	"ev_allday" => "allDay", 
	"ev_link" => "web",
	"ev_peid" => "peid",
	"ev_recur" => "repeats",
// Special processing fields
	"ev_location" => "location",
	"ev_address" => "address",
// Repeating fields
	"cr_type" => "type",
	"cr_daily_option" => "daily_option",
	"cr_weekly_option" => "weekly_option",
	"cr_weekly_dow" => "weekly_dow",
	"cr_monthly_option" => "monthly_option",
	"cr_monthly_daynum" => "monthly_daynum",
	"cr_monthly_wknum" => "monthly_wknum",
	"cr_monthly_dow" => "monthly_dow",
	"cr_annual_option" => "annual_option",
	"cr_annual_month1A" => "annual_month1A",
	"cr_annual_dom" => "annual_dom",
	"cr_annual_wknum" => "annual_wknum",
	"cr_annual_month2C" => "annual_month2C",
	"cr_annual_daynum" => "annual_daynum",
	"cr_interval" => "interval",
	"cr_until_date" => "until_date");
for($i = 0; $i < count($evData); $i++) {
	$evK = $evKeys[$i];
	$evD = $evData[$evK];
	if ($evK == "ev_location"
	or $evK == "ev_address") {
		$evD = ev_loc_name."|".ev_loc_street."|".ev_loc_city."|".ev_loc_state."|".ev_loc_zip;
	}
	$evK2 = $evKeyXlate[$evK];
	if (!is_null($evK2)) {
		$evList = $evList.$evC.'{"'.$evK2.': "'.$evD.'"}';
		$evC = ",";
	}
}

$evOBJ = json_encode($evData, JSON_FORCE_OBJECT);
$evJList = json_encode($evList);
*/

$event = $ret['data'][0];

$evRows = array();

$evRows[] = " ";

$evRows[] = "<html><head><title>".$event['ev_title']."</title>";
$evRows[] = "</head><body><div style='background-color:whitesmoke;border-style:solid;border-width:2px;border-color:darkgray;height:25px;'>";
$evRows[] = "<span style='font-size:115%;vertical-align:middle; text-decoration:bold;'><center>".$event['ev_title']."</center></span>";

$evRows[] = " ";


// TODO find real line length so box is not overly big


$edrows = floor(strlen($event['ev_desc']) / 60);				// hypothetically each line will be 100 characters long
$edrows = $edrows + (count(explode("\n", $event['ev_desc'])));	// split a line if it contains a newline
$erows = $erows + (int) $edrows;								// save the rows as integers to get rid of decimal placez
if ($edrows == 1) {
	$erows = $erows + 2;										// add 2 for blanks at top and bottom
}
$evRows[] = "<textarea cols='60' rows='".
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
$evRows[] = "<span style='vertical-align:middle;'><center>".
			"<input type='submit' style='font:15px Georgia, serif; font-size:125%; background-color:lightgrey;' value=' Close ' onclick='self.close();'>".
			"</center></span>";

$evRows[] = " ";



?>

<!-- Generate page
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Event Details</title>
	</head>
	<body>
		<br><br>
		<table width="100%">
		<tr><td width="30%:> </td><td width="40%"> </td><td width="30%"> </td></tr>
		<tr><td width="30%:> </td><td width="40%">
			<span style='font-size:110%;vertical-align:middle;'>
			<fieldset style='border: 1px solid #000; top: 15px; right: 25px; bottom: 15px; left: 25px;'>
			<span style='font-size:100%'>
			<?php
				for($i = 0; $i < count($evRows); $i++) {
					echo $evRows[$i]."<br>";
				}
			?>
			</span>
			</fieldset>
			</span>
		</td><td width="30%"> </td></tr>
	</table>
	</body>
</html>

<script type="text/javascript">

	var event = eventJList;
	var erows = 4;
	var econtent = "<html><head><title>"+event.title+"</title>";
	econtent = econtent+"</head><body><div style='background-color:whitesmoke;border-style:solid;border-width:2px;border-color:darkgray;height:25px;'>";
	econtent = econtent+"<span style='font-size:125%;vertical-align:middle;'><center>"+event.title+"</center></span></div><span style='font-size:105%;'><br />";
	edrows = Math.floor(event.description.length / 50) + 1;
	edrows = edrows + event.description.split("\n").length - 1;
	erows = erows + edrows;
	if (edrows == 1) {
		erows = erows + 2;
	}
	econtent = econtent+"<textarea cols='50' rows='"+edrows+"' id='eedesc' name='eedesc' readonly style='outline:none; resize:none; overflow: auto; font-size:105%;'>"+event.description+"</textarea><br />";
	if (event.allDay) {
		erows = erows + 2;
		econtent = econtent+"<br />This is an all day event.<br />";
	}
	erows = erows + 2;
	var ds = moment(event.bdate+"T"+event.btime);
	econtent = econtent+"<br />Start:&nbsp;&nbsp;&nbsp;"+ds.format('dddd')+", "+ds.format('LL');
	if (event.allDay == false) {
		econtent = econtent+" at "+ds.format('h:mm A');
	}
	econtent = econtent+"<br />";
	erows = erows + 2;
	var de = moment(event.edate+"T"+event.etime);
	econtent = econtent+"<br />End:&nbsp;&nbsp;&nbsp;&nbsp;"+de.format('dddd')+", "+de.format('LL');
	if (event.allDay == false) {
		econtent = econtent+" at "+de.format('h:mm A');
	}
	econtent = econtent+"<br />";
	if (event.location) {
		erows = erows + 2;
		econtent = econtent+"<br />Location:<br />";
		var loc = event.location.split("|");
		var lflds = loc.length;
		var i=0;
		for (i=0; i<lflds; i++) {
			if (loc[i]) {
				erows = erows + 1;
				econtent = econtent+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+loc[i]+"<br />";
			}
		}
		erows = erows + 1;
		var a = event.address;
		a = a.replace(' ', '+')
		a = a.replace('|', ',');
		econtent = econtent+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='https://www.google.com/maps/place/"  + a + "' target='_blank'>See On The Map.</a><br>";
	};
	if (event.web){
		var eweb = event.web;
		eweb = eweb.toLowerCase();
		if (eweb.substr(0, 3) != "htt" ) {
			eweb = "http://" + event.web;
		}
		erows = erows + 2;
		econtent = econtent+"<br />Web Site:&nbsp;<a href='"+eweb+"' target='_blank'>"+event.web+"</a>";
	}
	if (event.peid){
		erows = erows + 3;
		var peId = event.peid;
		var peLink = "MyPages/IAPPartyEvent.php?action=selected/peid=" + peId.toString;
		econtent = econtent+"<br /><br><a href='"+peLink+"' target='_blank'>Click here to view the Party/Event details.</a><br>";

	}

	if (event.repeats == "Y") {
		econtent = econtent+"<br /><br />This event repeats";
		erows = erows + 2;

		var repdays=new Array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		var repwks=new Array("", "1st", "2nd", "3rd", "4th", "Last");
		var repmths=new Array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		var repmsg = "Every";
		var intvmsg = "";
		if (event.interval > 1) {
			switch(event.interval.substring(event.interval.length - 2)) {
				case "1":
					intvmsg = " "+event.interval+"st ";
					break;
				case "2":
					intvmsg = " "+event.interval+"nd ";
					break;
				case "3":
					intvmsg = " "+event.interval+"rd ";
					break;
				default:
					intvmsg = " "+event.interval+"th ";
					break;
			}
		}
		repmsg = repmsg+intvmsg;
		switch(event.type) {
			case "D":	
				switch(event.daily_option) {
					case "d1":
						repmsg = repmsg+" day";
						break;
					case "d2":
						repmsg = repmsg+" weekday";
						break;
				}
				break;
			case "W":
				switch(event.weekly_option) {
					case "w1":									
						repmsg = repmsg+" "+repdays[event.weekly_dow]+" of every week";
						break;
				}
				break;
			case "M":
				switch(event.monthly_option) {
					case "m1":
						repmsg = repmsg+" day "+event.monthly_daynum+" of every month";
						break;
					case "m2":									
						repmsg = repmsg+" "+repdays[event.monthly_dow]+" of the "+repwks[event.monthly_wknum]+" week of every month";
						break;
				}
				break;					
			case "A":
				switch(event.annual_option) {
					case "a1":
						repmsg = repmsg+" "+repmths[event.annual_month1A]+" "+event.annual_dom+" of every year";
						break;
					case "a2":
						repmsg = repmsg+" day "+repdays[event.annual_dow]+" of the "+repwks[event.annual_wknum]+" week of "+repmths[event.annual_month2C]+" of every month";
						break;
					case "a3":
						repmsg = repmsg+" day "+event.annual_daynum+" of every year";									
						break;
				}
				break;
		}
		econtent = econtent+"<br /><span style='padding-left:15px;'>"+repmsg+".</span>";
		erows = erows + 1;
		if (event.until_date != "2099-12-31") {
			var du_yr = event.until_date.substr(0, 4);
			var du_mo = parseInt(event.until_date.substr(5, 2));
			var du_da = event.until_date.substr(8, 2);
			econtent = econtent+"<br /><span style='padding-left:25px;'>Until "+repmths[du_mo]+" "+du_da+", "+du_yr+".</span>";
			erows = erows + 1;
		}
	}

	if (event.account != 0) {
		erows = erows + 3;
		econtent = econtent+"<br /><br /><br /></span>(id="+event.id+")";
	}
	erows = erows + 3;
	econtent = econtent+"<br /><center><input type='submit' value='Close' onclick='self.close();' /><br /></body></html>";
	erows = erows * 20;
		// Half the screen width - half popup width (450) - 10 for borders
	eleft = (window.screen.width/2) - 225 - 10;
		// Half the screen height - half the number of calculated rows - 50 for window dressing
	etop = (window.screen.height/2) - (erows / 2) - 50;	
	eventWindow=window.open('','_blank','width=450,height='+erows+',left='+eleft+',top='+etop+',location=no,menubar=no,resizable=no,scrollbar=no,titlebar=no,toolbar=no')
	eventWindow.document.write(econtent)
	eventWindow.focus()

	var eventObj = [<?php echo $evOBJ; ?>];
	var eventJList = [<?php echo $evJList; ?>];
</script>

-->