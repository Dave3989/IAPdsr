<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>In AboutMe <br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("429") < 0) {
	return;
};

if ($_REQUEST['action'] == 'p429retA') {

	$iapProfile = (array) IAP_Get_Savearea("IAP429PR", $_REQUEST['IAPUID']);
//	if (!empty($iapProfile)) {
//		IAP_Remove_Savearea("IAP429PR", $_REQUEST['IAPUID']);
//	}

	$iapOrigAction = $_REQUEST['action'];

	$iapPageError = 0;
	$iapChanged = "N";
	$iapBDChg = "N";
	$iapHelpChg = "N";

	require_once("IAPValidators.php");

	if (isset($_REQUEST['pname'])) {
		$iapRet = IAP_Validate_Nonblank($iapProfile['pro_name'], $_REQUEST['pname']);
		if ($iapRet['Changed'] == "Y") {
			$iapProfile['pro_name'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Customer Name cannot be blank!</span><br>";
			$iapPageError = 1;
		}
	} elseif (empty($iapProfile['pro_name'])) {
		echo "<span class=iapError>Customer Name cannot be blank!</span><br>";
		$iapPageError = 1;
	}

	if (isset($_REQUEST['pbirth'])) {
		if (empty($_REQUEST['pbirth'])) {
			if (!empty($iapProfile['pro_birthday'])) {
				$iapProfile['pro_birthday'] = "";			
			     	$iapBDChg = "Y";
			     	$iapChanged = "Y";
			}
		} else {
			$iapBD = str_replace("-", "/", $_REQUEST['pbirth']."/1960");
			$iapRet = IAP_Validate_Date($iapProfile['pro_birthday'], $iapBD, "Y");
	        if ($iapRet['Changed'] == "Y") {
			$iapProfile['pro_birthday'] = $_REQUEST['pbirth'];
			$iapBDChg = "Y";
			$iapChanged = "Y";
	        }
	        if ($iapRet['Error'] == "1") {
			$iapProfile['pro_birthday'] = "";
			$iapBDChg = "Y";
			$iapChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Birthday is incorrectly formatted. Must be mm/dd</span><br>";
				$iapPageError = 1;
			}
		}
	}
	if (isset($_REQUEST['pstreet'])
	and	$iapProfile['pro_street'] != $_REQUEST['pstreet']) {
		$iapProfile['pro_street'] = $_REQUEST['pstreet'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['pcity'])
	and $iapProfile['pro_city'] != $_REQUEST['pcity']) {
		$iapProfile['pro_city'] = $_REQUEST['pcity'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['pstate'])
	and $iapProfile['pro_state'] != $_REQUEST['pstate']) {
		$iapProfile['pro_state'] = $_REQUEST['pstate'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['pzip'])
	and $iapProfile['pro_zip'] != $_REQUEST['pzip']) {
		$iapProfile['pro_zip'] = $_REQUEST['pzip'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['pemail'])
	and $iapProfile['pro_email'] != $_REQUEST['pemail']) {
		$iapProfile['pro_email'] = $_REQUEST['pemail'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['phphone'])) {
	 $iapRet = IAP_Validate_Phone($iapProfile['pro_home_phone'], $_REQUEST['phphone']);
	 if ($iapRet['Changed'] == "Y") {
	     $iapProfile['pro_home_phone'] = $iapRet['Value'];
	     $iapChanged = "Y";
	 }
	 if ($iapRet['Error'] == "1"
	and !(empty($iapProfile['pro_home_phone']))) {
		$iapProfile['pro_home_phone'] = "";
		$iapChanged = "Y";
	} elseif ($iapRet['Error'] == "2") {
		echo "<span class=iapError>Home Phone improperly formatted!</span><br>";
		$iapPageError = 1;
		}
	}
	if (isset($_REQUEST['pcphone'])) {
		$iapRet = IAP_Validate_Phone($iapProfile['pro_cell_phone'], $_REQUEST['pcphone']);
		if ($iapRet['Changed'] == "Y") {
			$iapProfile['pro_cell_phone'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1"
		and !(empty($iapProfile['pro_cell_phone']))) {
			$iapProfile['pro_cell_phone'] = "";
			$iapChanged = "Y";
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Cell Phone improperly formatted!</span><br>";
			$iapPageError = 1;
		}
	}
	if (isset($_REQUEST['pHelpLvl'])) {
		if ($_REQUEST['pHelpLvl'] != $iapProfile['pro_help_level']) {
			$iapProfile['pro_help_level'] = $_REQUEST['pHelpLvl'];
			$iapHelpChg = "Y";
			$iapChanged = "Y";
		}
	}

	if ($iapPageError == 0
	and $iapChanged == "Y") {
		$iapCN = explode(" ", $iapProfile['pro_name']);
		if (count($iapCN) > 1) {
// is last jr, etc
			$ln = array_pop($iapCN);
			$ln = strtolower($ln);
			if ($ln == "jr"
			or  $ln == "jr."
			or  $ln == "sr"
			or  $ln == "sr.") {
				$ln2 = array_pop($iapCN);
				$ln2 = strtolower($ln2);
				$ln = $ln2." ".$ln;
			}
			$iapProfile['pro_last_name'] = $ln;
			$iapProfile['pro_first_name'] = implode(" ", $iapCN);
		}

		if ($iapBDChg == "Y") {
			if (!empty($iapProfile['pro_birthday_event'])) {
				$iapRet = IAP_Delete_Row(array("event_id" => $iapProfile['pro_birthday_event']), "iapcal");
				if ($iapRet < 0) {
		        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar event row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
				}
				$iapRet = IAP_Delete_Row(array("cr_id" => $iapProfile['pro_birthday_event']), "iapcrep");
				if ($iapRet < 0) {
		        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
				}
			}
			if (!empty($iapProfile['pro_birthday'])) {
				$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
				if ($eeEvents < 0) {
					echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
					exit;
				}
				$eeEvent = (array) $eeEvents[0];
				$eeEvent['event_account'] = 0;		// System Administrator's Calendar
				$eeEvent['event_title'] = $iapProfile['pro_name']."'s Birthday";
				$dsc = $eeEvent['event_title']."\n";
				if (!empty($iapProfile['pro_street'])) {
					$dsc = $dsc.$iapProfile['pro_street']."\n";
				}
				if (!empty($iapProfile['pro_city'])
				or  !empty($iapProfile['pro_state'])
				or  !empty($iapProfile['pro_zip'])) {
					$dsc = $dsc.$iapProfile['pro_city'].",".$iapProfile['pro_state']." ".$iapProfile['pro_zip']."\n";
				}
				if (!empty($iapProfile['pro_home_phone'])) {
					$dsc = $dsc."Home Phone: ".$iapProfile['pro_home_phone']."\n";
				}
				if (!empty($iapProfile['pro_cell_phone'])) {
					$dsc = $dsc."Cell Phone: ".$iapProfile['pro_cell_phone']."\n";
				}
				if (!empty($iapProfile['pro_email'])) {
					$dsc = $dsc."Email: ".$iapProfile['pro_email']."\n";
				}
				$eeEvent['event_desc'] = $dsc;
				$d = $iapProfile['pro_birthday']."/".date("Y");
				$dstr = strtotime($d);
				$eeEvent['event_begin'] = date("Y-m-d", $dstr);
				$eeEvent['event_end'] = $eeEvent['event_begin'];
				$eeEvent['event_btime'] = "00:00";
				$eeEvent['event_etime'] = "00:00";
				$eeEvent['event_recur'] = "Y";
				$eeEvent['event_allday'] = "Y";
				$eeEvent['event_author'] = $eeUID;
				$es = $eeEvent['status'];
				$eeRepeats = IAP_Build_New_Row(array("table" => "iapcrep"));
				if ($eeRepeats < 0) {
					echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(2). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
					exit;
				}
				$eeRepeat = (array) $eeRepeats[0];
				$eeRepeat['repeatstatus'] = $eeRepeat['status']; 
				$eeEvent = array_merge($eeEvent, $eeRepeat);
				$eeEvent['status'] = $es;
				$eeEvent['cr_type'] = "A";								// Repeat annually
				$eeEvent['cr_annual_option'] = "a1";						// Repeat on a particular mm/dd
				$eeEvent['cr_annual_month1A'] = date("m", $dstr);
				$eeEvent['cr_annual_dom'] = date("d", $dstr);
				$eeEvent['cr_annual_occurs'] = 1;						// Repeat every year
				$eeEvent['cr_until_date'] = "2099-12-31";					// Repeat forever
				$eeEvent['cr_until_count'] = 0;

				require_once(ABSPATH."Ajax/IAPCalendar/IAPWriteEvent.php");
				$eeEvent['event_id'] = FCWriteEvent($eeEvent, "Y");
				if ($eeEvent['event_id'] < 0) {
					echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
					exit;
				}
				$iapProfile['pro_birthday_event'] = $eeEvent['event_id'];
			}
		}

		$iapProfile['pro_changed'] = date("Y-m-d");
		$iapProfile['pro_changed_by'] = $_REQUEST['IAPUID']; 
		$iapRet = IAP_Update_Data($iapProfile, "prof");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapUserData['Id'] = $iapProfile['pro_no'];
		$iapUserData['FirstName'] = $iapProfile['pro_first_name'];
		$iapUserData['LastName'] = $iapProfile['pro_last_name'];
		$iapUserData['NickName'] = $iapProfile['pro_nickname'];
		if (empty($iapUserData['NickName'])) {
			$iapUserData['DisplayName'] = $iapProfile['pro_name'];
		} else {
			$iapUserData['DisplayName'] = $iapProfile['pro_nickname'];
		}
		$iapUserData['HelpLevel'] = $iapProfile['pro_help_level'];

		if ($iapHelpChg == "Y") {
			IAP_Delete_PartKey(array("hl_client" => $_REQUEST['IAPUID']), "iaphlvl");		
		}

		echo "<br><span class=iapSuccess>Your profile was successfully updated.</span><br><br>";
	}

	$iapRet = IAP_Update_Savearea("IAP429PR", $iapProfile, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

} else {

	if (IAP_Remove_Savearea("IAP429PR") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the catalog item savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapProfile = IAP_Get_Profile();
	if ($iapProfile < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Error retreiving your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapProfile['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
/*
		$iapProfile['pro_company'] = $_REQUEST['CoId'];
		$iapProfile['pro_no'] = $_REQUEST['UserData']['Id'];
		$iapProfile['pro_first_name'] = $_REQUEST['UserData']['FirstName'];
		$iapProfile['pro_last_name'] = $_REQUEST['UserData']['LastName'];
		$iapProfile['pro_name'] = $_REQUEST['UserData']['FirstName']." ".$_REQUEST['UserData']['LastName'];
		$iapProfile['pro_nickname'] = $_REQUEST['UserData']['NickName'];
		$iapProfile['pro_street'] = "";
		$iapProfile['pro_city'] = "";
		$iapProfile['pro_state'] = "";
		$iapProfile['pro_zip'] = "";
		$iapProfile['pro_home_phone'] = "";
		$iapProfile['pro_cell_phone'] = "";
		$iapProfile['pro_email'] = "";
		$iapProfile['pro_changed'] = "";
		$iapProfile['pro_changed_by'] = "";
		$iapRet = IAP_Update_Row($iapProfile, "prof");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapProfile['status'] = "EXISTING";
*/
	}

	$iapRet = IAP_Create_Savearea("IAP429PR", $iapProfile, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
}

$iapReadOnly = IAP_Format_Heading("About Me Profile");

if ($_REQUEST['IAPUID'] == 9) {
	echo "<span class=iapError style='font-size:110%;'>The Profile cannot be changed for the Demo user.</span><br><br>";
	$iapReadOnly = "readonly";
}

$h = IAP_Do_Help(3, 429, 1); // level 3, page 429, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";	
}
?>

<p style='text-indent:50px; width:100%'>

<form name='idetform' action='?action=p429retA&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
<tr><td style='width:5%'></td><td style='width:7%'>&nbsp;</td><td style='width:6%'>&nbsp;</td><td style="width:86%">&nbsp;</td></tr>

<tr>
<td style='width:5%'></td><td colspan='2'><span class='iapFormLabel'>Your Name:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="1" size="50" maxlength="50" name="pname" id="pname" value="<?php echo $iapProfile['pro_name']; ?>" autofocus>
</td></tr>

<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>

<tr><td style='width:5%'></td><td colspan="2"><span class='iapFormLabel'>Address:</span></td><td style='width: 86%;'></td></tr>

<tr>
<td style='width:5%'></td><td style='width:7%'></td>
<td style='width:6%'><span class='iapFormLabel'>Street:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="2" maxlength="50" size="50" name="pstreet" id="pstreet" value="<?php echo $iapProfile['pro_street']; ?>">
</td></tr>
<tr>
<td style='width:5%'></td><td style='width:7%'></td>
<td style='width:6%'><span class='iapFormLabel'>City:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="3" maxlength="40" size="40" name="pcity" id="pcity" value="<?php echo $iapProfile['pro_city']; ?>">
</td></tr>
<tr>
<td style='width:5%'></td><td style='width:7%'></td>
<td style='width:6%'><span class='iapFormLabel'>State:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="4" maxlength="2" size="2" name="pstate" id="pstate" value="<?php echo $iapProfile['pro_state']; ?>">
</td></tr>
<tr>
<td style='width:5%'></td><td style='width:7%'></td>
<td style='width:6%'><span class='iapFormLabel'>Zip:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="5" maxlength="10" size="10" name="pzip" id="pzip" value="<?php echo $iapProfile['pro_zip']; ?>">
</td></tr>

<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>

<tr>
<td style='width:5%'></td><td colspan='2'><span class='iapFormLabel'>Email:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="6" type="email" maxlength="100" size="50" name="pemail" id="pemail" value="<?php echo $iapProfile['pro_email']; ?>">
</td></tr>

<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>

<tr>
<td style='width:5%'></td><td colspan='2'><span class='iapFormLabel'>Home Phone:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="7" maxlength="15" size="15" name="phphone" id="phphone" value="<?php echo $iapProfile['pro_home_phone']; ?>">
</td></tr>

<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>

<tr>
<td style='width:5%'></td><td colspan='2'><span class='iapFormLabel'>Cell Phone:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="8" maxlength="15" size="15" name="pcphone" id="pcphone" value="<?php echo $iapProfile['pro_cell_phone']; ?>">
</td></tr>

<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>

<tr>
<td style='width:5%'></td><td colspan='2'><span class='iapFormLabel'>Birthday:</span></td>
<td style='width: 86%;'>
	<input <?php echo $iapReadOnly; ?> tabindex="9" maxlength="5" size="5" name="pbirth" id="pbirth" placeholder="mm/dd" value="<?php if (!empty($iapProfile['pro_birthday'])) { echo date("m/d",strtotime($iapProfile['pro_birthday']."/1960")); } ?>">

&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 429, 1); ?> <!-- level 1, page 429, section 1 -->

</td></tr>

</table>

<?php
	$h = IAP_Do_Help(3, 429, 2); // level 3, page 429, section 2
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";	
	}

	echo "<table style='text-align: left;' border='1' cellpadding='2' cellspacing='2' height='20px'>";
	echo "<tr><td style='width:5%'></td><td style='width:7%'>&nbsp;</td><td style='width:6%'>&nbsp;</td><td style='width:86%'>&nbsp;</td></tr>";
	echo "<tr><td style='width:5%'></td><td colspan='3'><span class='iapFormLabel'>What level of help would you like: </span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 429, 2); // <!-- level 1, page 429, section 2 -->

	echo "</td></tr>";
	echo "<tr><td style='width:5%'></td><td colspan='2'></td><td width='82%'>";
	$d = "";
	if ($iapReadOnly == "readonly") {
		$d = "disabled ";
	}
	echo "<select ".$d."class='iapFormField' tabindex='19' name='pHelpLvl' id='pHelpLvl' size='1'>";

	echo "<option value='3'";
	if ($iapProfile['pro_help_level'] == 3) {
		echo " selected";
	}
	echo ">Walk Me Through.</option>";
	echo "<option value='1'";
	if ($iapProfile['pro_help_level'] == 1) {
		echo " selected";
	}
	echo ">I'll Pick What I Need.</option>";
	echo "<option value='0'";
	if ($iapProfile['pro_help_level'] == 0) {
		echo " selected";
	}
	echo ">I've Got This. No Help Needed.</option>";
	echo "</select>";
	echo "</td></tr>";

	echo "<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td style='width:5%'></td><td colspan='3'>&nbsp;</td></tr>";

	if ($iapReadOnly != "readonly") {
		echo "<tr><td style='width:5%'></td><td width='3%'></td><td width='6%'></td>";
		echo "<td width='86%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' class=iapButton tabindex='20' name='BISubmit' value='Submit'></td></tr></table>";
	}
?>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
</form>
</p>
