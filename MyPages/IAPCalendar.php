<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Calendar<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once("IAPServices.php");
if (iap_Program_Start("166") < 0) {
	return;
};

// $iapReadOnly = IAP_Format_Heading("Sales Entry/Edit");
$a = 1;

$h = IAP_Do_Help(3, 166, 1); // level 3, page 166, section 1
if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
}
?>

<div id='loading' style='display:none;'><strong>loading...</strong></div>
<div id='calendar_comment' style='display:block;'>
<span class=iapFormLabel>Click on an event to view its details.</span>
</div>
<div id='calendar' style='display:block;'></div>
<hr />
<table><tr>
<td width='20'> </td>
<td width='200'><span style='color:black; background-color:#FFC04D; font-size:115%;'>&nbsp;Your Events&nbsp;</span></td>
<td width='200'><span style='color:black; background-color:#FFFF8C; font-size:115%;'>&nbsp;System Events&nbsp;</span></td>
<td width='200'><span style='color:black; background-color:lightblue; font-size:115%;'>&nbsp;US Holidays&nbsp;</span></td>
</tr></table>

<form name='purform' action='#' method='POST'>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="">
<input type='hidden' name='IAPCALEVENTS' id='IAPCALEVENTS' value='<?php echo $_REQUEST['IAPUrl']; ?>/Ajax/IAPCalendar/IAPGetEvents.php?LHCA='>
<input type='hidden' name='IAPUSERGOOGLE' id='IAPUSERGOOGLE' value='<?php echo $_REQUEST['UserData']['GoogleCal']; ?>'>

</form>

<script type="text/javascript">
<?php
require_once("MyJS/NonJSMin/JSCalendar.js");
//require_once("MyJS/JSCalendar.min.js");
?>

</script>