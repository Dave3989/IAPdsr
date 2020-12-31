<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In ExportCustomers with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("1562") < 0) {
	return;
};

if($_REQUEST['action'] == "ret1562A") {

	if (isset($_REQUEST['rfrchkbx'])) {
		$startFinRpt = "N";
		$FinRptArray = array();


		require_once($_REQUEST['IAPPath']."IAPValidators.php");
		if (isset($_REQUEST['rfrstarting'])) {
			$iapRet = IAP_Validate_Date($FinRptArray['startdate'], $_REQUEST['rfrstarting']);
			if ($iapRet['Changed'] == "Y") {
				$FinRptArray['startdate'] = $iapRet['Value'];
			} elseif ($iapRet['Error'] == 1) {
				$FinRptArray['startdate'] = "2010-01-01";
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapError>Starting Date is not valid!</span><br>";
			}
		}

		if (isset($_REQUEST['rfrending'])) {
			$iapRet = IAP_Validate_Date($FinRptArray['enddate'], $_REQUEST['rfrending']);
			if ($iapRet['Changed'] == "Y") {
				$FinRptArray['enddate'] = $iapRet['Value'];
			} elseif ($iapRet['Error'] == 1) {
				$FinRptArray['enddate'] = "2099-12-31";
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapError>Ending Date is not valid!</span><br>";
			}
		}

		if (isset($_REQUEST['rfrtype'])) {
			$FinRptArray['ByType'] = "Y";
		} else {
			$FinRptArray['ByType'] = "N";
		}

		if (isset($_REQUEST['rfrpgrp'])) {
			$FinRptArray['ByProd'] = "Y";
		} else {
			$FinRptArray['ByProd'] = "N";
		}
	}

	$startFinRpt = "Y";		
}


$iapReadOnly = IAP_Format_Heading("Reports");
?>

<form name='rselform' action='?action=ret1562A&origaction=initial' method='POST'>

<table style='width:100%'>
<tr><td style='width:5%;'></td><td style='width:95%;'></td></tr>
<tr><td style='width:5%;'></td><td style='width:95%;' class=iapFormLabel>Select The Report And Options To Use:<br></td></tr>

<tr><td style='width:5%;'></td><td style='width:95%;'>
<input type='checkbox' name='rfrchkbx' id='rfrchkbx' onclick='rfrChecked();'<?php if (is_array($FinRptArray)) { echo " checked"; } ?>>
 Financial Report
</td></tr>

</table>
<?php
if (is_array($FinRptArray)) {
	$d = "inline";
} else {
	$d = "none";
}
?>
<div id=rfrOptions style="display:<?php echo $d; ?>">
	<table style="width:100%;">

<?php
		if (isset($FinRptArray['startdate'])) {
			$sd = date("m/d/Y", strtotime($FinRptArray['startdate']));
		} else {
			$sd = "";
		}
?>
		<tr><td style="width:5%;"></td>
		<td style="width:95%;">
			<label for='rfrstarting' class='iapFormLabel'>Starting Date: </label>
			<input placeholder='mm/dd/yyyy' maxlength='15' size='15' name='rfrstarting' id='rfrstarting' 
				   value='<?php echo $sd; ?>'>
		</td></tr>

		<tr><td style="width:5%;">&nbsp;</td><td style="width:95%;">&nbsp;</td></tr>

<?php
		if (isset($FinRptArray['enddate'])) {
			$ed = date("m/d/Y", strtotime($FinRptArray['enddate']));
		} else {
			$ed = "";
		}
?>
		<tr><td style="width:5%;"></td>
		<td style="width:95%;">
			<label for='rfrending' class='iapFormLabel'>End Date: </label>
			<input placeholder='mm/dd/yyyy' maxlength='15' size='15' name='rfrending' id='rfrending' 
				   value='<?php echo $ed; ?>'>
		</td></tr>

		<tr><td style="width:5%;">&nbsp;</td><td style="width:95%;">&nbsp;</td></tr>

<?php
		if (isset($FinRptArray['ByType'])
		and $FinRptArray['ByType'] == "Y") {
			$t = " checked";
		} else {
			$t = "";
		}
?>
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">
			<input type="checkbox" name="rfrtype" id="rfrtype"<?php echo $t; ?>> Totals By Sale Type <br>
		</td></tr>

		<tr><td style="width:5%;">&nbsp;</td><td style="width:95%;">&nbsp;</td></tr>

<?php
		if (isset($FinRptArray['ByProd'])
		and $FinRptArray['ByProd'] == "Y") {
			$p = " checked";
		} else {
			$p = "";
		}
?>
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">
			<input type="checkbox" name="rfrpgrp" id="rfrpgrp"<?php echo $p; ?>> Total COGS By Product Group <br>
		</td></tr>

<?php
if ($startFinRpt == "Y") {
?>
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">
			<br>
			<button type='submit' class=iapButton name='rfrgenrpt' id='rfrgenrpt' onclick="genReport('F'); return false;">
			 Print Financial Report  
			</button>
			&nbsp;&nbsp;&nbsp;<span id="rfrprinting" style="display: none;">Your report is opening in another tab.</span>
		</td></tr>

		<tr><td style="width:5%;">&nbsp;</td><td style="width:95%;">&nbsp;</td></tr>
		<tr><td style="width:5%;">&nbsp;</td><td style="width:95%;">&nbsp;</td></tr>

<?php
		if (isset($FinRptArray['startdate'])) {
			$d1 = $FinRptArray['startdate'];
		} else {
			$d1 = "2010-01-01";
		}
		if (isset($FinRptArray['enddate'])) {
			$d2 = $FinRptArray['enddate'];
		} else {
			$d2 = "2099-12-31";
		}
?>
		<input type="hidden" name="rfrRptSDate" id="rfrRptSDate" value="<?php echo $d1; ?>">
		<input type="hidden" name="rfrRptEDate" id="rfrRptEDate" value="<?php echo $d2; ?>">
		<input type="hidden" name="rfrRptType" id="rfrRptType" value="<?php echo $FinRptArray['ByType']; ?>">
		<input type="hidden" name="rfrRptProd" id="rfrRptProd" value="<?php echo $FinRptArray['ByProd']; ?>">

<?php
}
?>

	</table>
</div>

<table style='width:100%'>
<tr><td style='width:5%;'></td><td style='width:95%;'>
<input type='checkbox' name='rtcchkbx' id='rtcchkbx' onclick='rtcChecked();'> Top Customers Report</td></tr>
</table>
<?php
if (is_array($TopCustArray)) {
	$d = "inline";
} else {
	$d = "none";
}
?>
<div id=rtcOptions style="display:<?php echo $d; ?>">
	<table style="width:100%;">
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">
			<input type="radio" name="rtcttdol" id="rtcttdol" value="t">  Top 10 Customers By Sales Dollars <br>
			<input type="radio" name="rtcthdol" id="rtcthdol" value="h">  Top 100 Customers By Sales Dollars <br>
			<input type="radio" name="rtcnodol" id="rtcnodol" value="x">  Do Not Show Customers By Sales Dollars <br>
		</td></tr>
		<tr><td style="width:7%;" </td><td style="width:93%;"></td></tr>
	</table>
</div>


<table style='width:100%'>
<tr><td style='width:5%;'></td><td style='width:95%;'>
<input type='checkbox' name='rtpchkbx' id='rtpchkbx' onclick='rtcChecked();'> Top Products Report</td></tr>
</table>
<?php
if (is_array($TopProdArray)) {
	$d = "inline";
} else {
	$d = "none";
}
?>
<div id=rtpOptions style="display:<?php echo $d; ?>">
	<table style="width:100%;">
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">

		<tr><td style="width:7%;" </td><td style="width:93%;"></td></tr>
	</table>
</div>


<table style='width:100%'>
<tr><td style='width:5%;'></td><td style='width:95%;'>
<input type='checkbox' name='ricchkbx' id='ricchkbx' onclick='ricChecked();'> Physical Inventory Checklist</td></tr>
</table>
<?php
if (is_array($TopInvChkLsArray)) {
	$d = "inline";
} else {
	$d = "none";
}
?>
<div id=ricOptions style="display:<?php echo $d; ?>">
	<table style="width:100%;">
		<tr><td style="width:7%;" </td><td style="width:93%;" class="iapFormLabel">

		<tr><td style="width:7%;" </td><td style="width:93%;"></td></tr>
	</table>
</div>


<button type='submit' class=iapButton name='rptsubmit' id='rptsubmit' tabindex='53'> Submit </button>


<input type="hidden" name="iapURL" id="iapURL" value="<?php echo $_REQUEST['IAPUrl']; ?>">

</form>


<script type="text/javascript">

function genReport($rptType) {
	if ($rptType == "F") {
		var finSDate = document.getElementById("rfrRptSDate").value;
		var finEDate = document.getElementById("rfrRptEDate").value;
 		var finType = document.getElementById("rfrRptType").value;
		var finProd = document.getElementById("rfrRptProd").value;
		var rptURL = "/MyReports/IAPFinanceRpt.php?action=print" + "&sd=" + finSDate + "&ed=" + finEDate + "&t=" + finType + "&p=" + finProd;
		document.getElementById("rfrprinting").style.display = "inline";
	} 
	var myURL = document.getElementById("iapURL").value;
	var fullURL = document.getElementById("iapURL").value + rptURL;
	window.open(fullURL, '_blank', 'location=no,menubar=no,resizable=no,scrollbar=no,titlebar=no,toolbar=no');
	eventWindow.focus()
	return false;
}

function rfrChecked() {
	document.getElementById("rfrstarting").value = "01/01/2010";
	document.getElementById("rfrending").value = "12/31/2099";
	document.getElementById("rfrtype").checked = false;
	document.getElementById("rfrpgrp").checked = false;
	if (document.getElementById("rfrchkbx").checked = true) {
		document.getElementById("rtcchkbx").checked = false;
		document.getElementById("rtpchkbx").checked = false;
		document.getElementById("ricchkbx").checked = false;
		document.getElementById("rfrOptions").style.display = "inline";
	} else {
		document.getElementById("rfrOptions").style.display = "none";
	}
}

function rtcChecked() {
	if (document.getElementById("rtcchkbx").checked = true) {
		document.getElementById("rfrchkbx").checked = false;
		document.getElementById("rtpchkbx").checked = false;
		document.getElementById("ricchkbx").checked = false;
		document.getElementById("rtcOptions").style.display = "inline";
	} else {
		document.getElementById("rtcOptions").style.display = "none";
	}
}

function rtpChecked() {
	if (document.getElementById("rtpchkbx").checked = true) {
		document.getElementById("rfrchkbx").checked = false;
		document.getElementById("rtcchkbx").checked = false;
		document.getElementById("ricchkbx").checked = false;
		document.getElementById("rtpOptions").style.display = "inline";
	} else {
		document.getElementById("rtpOptions").style.display = "none";
	}
}

function ricChecked() {
	if (document.getElementById("rtpchkbx").checked = true) {
		document.getElementById("rfrchkbx").checked = false;
		document.getElementById("rtcchkbx").checked = false;
		document.getElementById("rtpchkbx").checked = false;
		document.getElementById("ricOptions").style.display = "inline";
	} else {
		document.getElementById("ricOptions").style.display = "none";
	}
}


</script>