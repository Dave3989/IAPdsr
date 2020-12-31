<?php

/*
Plugin Name: IAP Add Headers
Plugin URI: https://Litehaus Consulting/MyPlugins
Description: Add IAP Headers to themes
Author: Dave/Litehaus Consulting
Version: 0.1
Author URI: http://LitehausConsulting.com
*/

add_action('wp_head', 'iapAddHeaders');
function iapAddHeaders() {

	if (is_admin()) {
		return;
	}

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$IAPUrl = $_REQUEST['IAPUrl'];
	$IAPUrl = $_REQUEST['IAPUrl'];

?>

<!-- Expand/Collaspe  -->
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/lhc_expcoll.js"></script>

<!-- Moment           -->
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/moment.min.js"></script>

<!-- Format("#,###0.00", 3.141592) -->
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/format.min.js"></script>
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/number_format.js"></script>

<!-- DateInput        -->
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/calendarDateInput.js"></script>

<!-- jQuery           -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<!-- jQuery Ui         -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- Page Loading Spinner -->
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/MyScripts/Spinner.js"></script>

<!-- FullCalendar     -->
<link rel='stylesheet' type='text/css' href='<?php echo $IAPUrl; ?>/Ajax/IAPCalendar/fullcalendar2/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo $IAPUrl; ?>/Ajax/IAPCalendar/fullcalendar2/fullcalendar.print.css' media='print' />
<link rel='stylesheet' type='text/css' href='<?php echo $IAPUrl; ?>/Ajax/IAPCalendar/FCStyle.css' />
<script type='text/javascript' src='<?php echo $IAPUrl; ?>/Ajax/IAPCalendar/fullcalendar2/fullcalendar.js'></script>
<script type='text/javascript' src='<?php echo $IAPUrl; ?>/Ajax/IAPCalendar/fullcalendar2/gcal.js'></script>

<!-- Tool Tip         -->
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $IAPUrl; ?>/Ajax/tooltipster-master/css/tooltipster.css" />
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $IAPUrl; ?>/Ajax/tooltipster-master/css/themes/tooltipster-shadow.css" />
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/tooltipster-master/js/jquery.tooltipster.min.js"></script>

<!-- Time Picker -->
<link rel="stylesheet" media="all" type="text/css" href="<?php echo $IAPUrl; ?>/Ajax/JQuery-timepicker/jquery.timepicker.css" />
<script type="text/javascript" src="<?php echo $IAPUrl; ?>/Ajax/JQuery-timepicker/jquery.timepicker.js"></script>

<!-- My Checks -->
<script type="text/javascript">

$(document).ready(function() {	
	$('.tooltip').tooltipster({
		theme: 'tooltipster-shadow',
		position: 'top-right',
		maxWidth: 300
	});
})

function iapPrepCall(myProgram, myType, myKey, myGoodFun) {
	var urlin = document.URL;
	var urllc = urlin.toLowerCase();
	if (urllc.indexOf("litehausconsulting") >= 0) {
		var urldomain = "iap";
	} else if (urllc.indexOf("iapqa") >= 0) {
		var urldomain = "iapqa";
	} else {
		var urldomain = "iapdsr";
	}
	var uarray = urlin.split("/");
	var ln = "";
	for (i = 0; i <= uarray.length; i++) {
		ln = uarray.pop();
		var lnsm = ln.toLowerCase();
		if (lnsm.indexOf(urldomain) >= 0) {
			uarray.push(ln);
			var iappath = uarray.join("/");
			break;
		}
	}
	var accountFld = document.getElementById("LHCA").value;
	var urlFld = iappath+myProgram+".php";
	var iapCallResponse = iapCall(urlFld, myType, accountFld, myKey, myGoodFun);
	return iapCallResponse;
}

function iapCall(myURL, myType, myOrg, myKey, myGoodFun) {

	$.ajax({
		type: 'POST',
		url: encodeURI(myURL),
		data: {iapType: myType, iapOrg: myOrg, iapKey: myKey},
		dataType: 'json',
		async: false,
		error: function(xhr){
// error(xhr,status,error)
			var retStatus = xhr.status;
			var retStatusText = xhr.statusText;
			if (retStatusText == "OK") {
				var funReturn = myGoodFun(retMsg);
				return funReturn;
			}
			var retErr = retStatus + " " + retStatusText;
			var retMsg = "IAP INTERNAL ERROR: A server call failed [FATAL]. Attempt to call " + myURL + " failed with error " + retErr + ". Please notify Support!";
			var funReturn = "ERROR: " + retErr;
			alert(retMsg);
			return funReturn;
        	},
		success: function (callResponse) {
// success(result,status,xhr)
			var funReturn = myGoodFun(callResponse);
			return funReturn;
		}
    });
}

// ------------------------------------------------------------------------------
// Functions to prevent enter key from submitting form
// ------------------------------------------------------------------------------
// --- With this function 
// ---   onsubmit='return iapNoSubmit();' added to the form element
function iapNoSubmit() {
	return false;
}

// --- With this function the tab key must be used between fields
// ---   onkeypress='stopEnterSubmitting(window.event)' added to the form element
function stopEnterSubmitting(e) {
    if (e.keyCode == 13) {
        var src = e.srcElement || e.target;
        if (src.tagName.toLowerCase() != "textarea") {
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false;
            }
        }
    }
}

// ------------------------------------------------------------------------------

// -------------
// Binary search
// -------------
//Copyright 2009 Nicholas C. Zakas. All rights reserved.
//MIT-Licensed, see source file
function binarySearch(items, value) {

    var startIndex  = 0,
        stopIndex   = items.length - 1,
        middle      = Math.floor((stopIndex + startIndex)/2);

    while(items[middle] != value && startIndex < stopIndex){

        //adjust search area
        if (value < items[middle]){
            stopIndex = middle - 1;
        } else if (value > items[middle]){
            startIndex = middle + 1;
        }

        //recalculate middle
        middle = Math.floor((stopIndex + startIndex)/2);
    }

    //make sure it's the right value
    return (items[middle] != value) ? -1 : middle;
}
// -------------

// --------------------
// PHP Empty equivalent
// --------------------
function empty(e) {
	switch(e) {
		case "":
		case 0:
		case "0":
		case "undefined":
		case null:
		case false:
		case typeof this == "undefined":
			return true;
		default : return false;
	}
}
// --------------------

// --------------------------------
// Script for IAPSideBarHelp.php
// --------------------------------
function procHelpChoice() {
<?php
	if (isset($_REQUEST['page_id'])) {
		global $current_user;
		get_currentuserinfo();
		$iapCurrentUser = (array) $current_user;
		$uid = $iapCurrentUser['ID'];
?>
		var hPage = <?php echo $_REQUEST['page_id']; ?>;
		var hOption = document.getElementById('PgHelpLvl').value;
		phcShowDesc(hOption);
		document.getElementById('PgHelpLvl').value = hOption;
		document.getElementById('HELPLVL').value = hOption;

		var hUserId = <?php echo $uid; ?>;
		var helpRec = hUserId +"|" + hPage + "|" + hOption;
		iapPrepCall("Ajax/iapPutDB", "H", helpRec, hProcHelp);

// redo page?

<?php
	}
?>

}

function hProcHelp(hRec) {
	var a = 1;
}

function phcShowDesc(phcChoice) {
	document.getElementById('phl0').style.display = "none";
	document.getElementById('phl1').style.display = "none";
	document.getElementById('phl2').style.display = "none";
	document.getElementById('phl3').style.display = "none";
	var phcDesc = "phl" + phcChoice.toString();
	document.getElementById(phcDesc).style.display = "inline";
}
// --------------------------------

</script>

<?php
}
?>