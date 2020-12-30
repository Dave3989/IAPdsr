<?php


if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In StoreTrial ";
    if (isset($_REQUEST['applinfo'])) {
        echo " with applinfo of ".$_REQUEST['applinfo'];
    }
    echo ".<br />";
}

if ($_REQUEST['action'] != "trialret") {
	if (IAP_Remove_Savearea("IAP274ST") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot clean up the savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		exit;
	}

	$iapCo = IAP_Build_New_Row(array("table" => "comp"));
	$iapCompany = $iapCo[0]; 
	$iapCompany['co_setup'] = date("Y-m-d");
	$iapCompany['co_renewal_date'] = date("Y-m-d",strtotime("+2 months"));
	$iapCompany['co_add_birthdays'] = "Y";
	$iapCompany['Suppliers'] = array();

	if (IAP_Create_Savearea("IAP274ST", $iapCompany) < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create the profile savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		exit;
	}
} else {
	$iapCompany = (array) IAP_Get_Savearea("IAP274ST");
	if ($iapCompany < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company save area [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		exit;
	}
	$PageError = "N";
	$PageUpdate = "N";
	$iapCoSuppChanged = "N";
	$iapSuccess = "";

	if (isset($_REQUEST['BICoName'])) {
		$iapRet = LHC_Validate_Nonblank($iapCompany['co_name'], trim($_REQUEST['BICoName']));
		if ($iapRet['Changed'] == "Y") {
			$iapRet['Value'] = str_replace("'", "&apos;", $iapRet['Value']);
			$iapCompany['co_name'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Company Name cannot be blank!</span><br />";
			$iapPageError = "Y";
		}
	}

	if (isset($_REQUEST['BIMailStrt'])) {
		$iapRet = LHC_Validate_Nonblank($iapCompany['co_mail_street'], trim($_REQUEST['BIMailStrt']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_mail_street'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Mailing Street cannot be blank!</span><br />";
			$iapPageError = "Y";
		}
	}

	if ($_REQUEST['BIMailStrt2'] != "" 
	and $_REQUEST['BIMailStrt2'] != $iapCompany['co_mail_street2']) {
		$iapCompany['co_mail_street2'] = $_REQUEST['BIMailStrt2'];
		$iapCoChanged = "Y";
	}

	if (isset($_REQUEST['BIMailCity'])) {
		$iapRet = LHC_Validate_Nonblank($iapCompany['co_mail_city'], trim($_REQUEST['BIMailCity']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_mail_city'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Mailing City cannot be blank!</span><br />";
			$iapPageError = "Y";
		}
	}

	if (isset($_REQUEST['BIMailState'])) {
		$iapRet = LHC_Validate_Nonblank($iapCompany['co_mail_state'], trim($_REQUEST['BIMailState']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_mail_state'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Mailing State cannot be blank!</span><br />";
			$iapPageError = "Y";
		}
	}

	if (isset($_REQUEST['BIMailZip'])) {
		$iapRet = LHC_Validate_Nonblank($iapCompany['co_mail_zip'], trim($_REQUEST['BIMailZip']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_mail_zip'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		}elseif ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Shiping Zip Code cannot be blank!</span><br />";
			$iapPageError = "Y";
		}
	}

	if (!isset($_REQUEST['BIShipSame'])) {
		if (isset($_REQUEST['BIShipStrt'])) {
			$iapRet = LHC_Validate_Nonblank($iapCompany['co_ship_street'], trim($_REQUEST['BIShipStrt']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_ship_street'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Ship To Street cannot be blank!</span><br />";
				$iapPageError = "Y";
			}
		}
		if ($_REQUEST['BIShipStrt2'] != "" 
		and $_REQUEST['BIShipStrt2'] != $iapCompany['co_ship_street2']) {
			$iapCompany['co_ship_street2'] = $_REQUEST['BIShipStrt2'];
			$iapCoChanged = "Y";
		}
		if (isset($_REQUEST['BIShipCity'])) {
			$iapRet = LHC_Validate_Nonblank($iapCompany['co_ship_city'], trim($_REQUEST['BIShipCity']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_ship_city'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Ship To City cannot be blank!</span><br />";
				$iapPageError = "Y";
			}
		}
		if (isset($_REQUEST['BIShipState'])) {
			$iapRet = LHC_Validate_Nonblank($iapCompany['co_ship_state'], trim($_REQUEST['BIShipState']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_ship_state'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Ship To State cannot be blank!</span><br />";
				$iapPageError = "Y";
			}
		}
		if (isset($_REQUEST['BIShipZip'])) {
			$iapRet = LHC_Validate_Nonblank($iapCompany['co_ship_zip'], trim($_REQUEST['BIShipZip']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_ship_zip'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Ship To Zip Code cannot be blank!</span><br />";
				$iapPageError = "Y";
			}
		}
	}

	if (isset($_REQUEST['BIPhone'])) {
		$iapRet = LHC_Validate_Phone($iapCompany['co_phone'], trim($_REQUEST['BIPhone']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_phone'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == '1') {
			$iapCompany['co_phone'] = trim($_REQUEST['BIPhone']);
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Phone Number is not in a valid format!</span><br />";
			$iapPageError = "Y";
		}
	}

	if (isset($_REQUEST['BIEmail'])) {
		$iapRet = LHC_Validate_Email($iapCompany['co_email'], trim($_REQUEST['BIEmail']));
		if ($iapRet['Changed'] == "Y") {
			$iapCompany['co_email'] = $iapRet['Value'];
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == '1') {
			$iapCompany['co_email'] = trim($_REQUEST['BIEmail']);
			$iapCoChanged = "Y";
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Email address must be active!</span><br />";
			$iapPageError = "Y";
		}
	}

// Process suppliers
	if (isset($_REQUEST['csupps'])) {
		$CoSupps = $iapCompany['Suppliers'];
		$InSupps = $_REQUEST['csupps'];
		foreach($InSupps as $is) {
			$sno = substr($is, 5);
			$fnd = "N";
			foreach($CoSupps as $cs) {
				if ($cs == $sno) {
					$fnd = "Y";
					break;
				}
			}
			if ($fnd == "N") {		// In not found so need to add
				$iapCompany['Suppliers'][] = $sno;
				$iapCoSuppChanged = "Y";
			}
		}
		$c = count($CoSupps);
		for($i = 0; $i < $c; $i++ ) {
			$fnd = "N";
			$cs = $CoSupps[$i];
			foreach($InSupps as $is) {
				$ino = substr($is, 5);
				if ($cs == $ino) {
					$fnd = "Y";
					break;
				}
			}
			if ($fnd == "N") {		// Co not found so need to delete
				$iapCompany['Suppliers'][$i] = 0;
				$iapCoSuppChanged = "Y";
			}
		}
	}

	if ($iapPageError != "Y") {
		if ($iapCoChanged == "Y") {
			if (isset($_REQUEST['BIShipSame'])) {
				$iapCompany['co_ship_street'] = $iapCompany['co_mail_street'];
				$iapCompany['co_ship_street2'] = $iapCompany['co_mail_street2'];
				$iapCompany['co_ship_city'] = $iapCompany['co_mail_city'];
				$iapCompany['co_ship_state'] = $iapCompany['co_mail_state'];
				$iapCompany['co_ship_zip'] = $iapCompany['co_mail_zip'];
			}

			$iapCompany['co_setup'] = date("Y-m-d");
			$iapCompany['co_license_renewal'] = date("Y-m-d",strtotime("+2 months"));
			$iapRet = IAP_Update_Data($iapCompany, "comp");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot add company information due to database error [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}

			$iapCompany['co_id'] = $iapRet;
			$iapCompany['status'] = "EXISTING";
			$iapRet = IAP_Update_Data(array('cu_company' => $iapCompany['co_id'], 'cu_user' => $_REQUEST['UserData']['Id'], 'status' => "NEW"), "cous");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update company information due to database error [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
				exit;
			}
		}

// Update CoSupp if changes
		if ($iapCoSuppChanged == "Y") {
			$CoSuppliers = $iapCompany['Suppliers'];
			foreach($CoSuppliers as $cS) {
				if ($cS != 0) {
					$iapCS = IAP_Build_New_Row(array("table" => "cosup"));
					$iapCoSupp = $iapCS[0];
					$iapCoSupp['cs_company'] = $iapCompany['co_id'];
					$iapCoSupp['cs_supplier'] = $cS;
					$iapCoSupp['cs_changed'] = date("Y-m-d");
					$iapCoSupp['cs_changed_by'] = $_REQUEST['IAPUID'];
					$iapRet = IAP_Update_Data($iapCoSupp, "cosup");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update company suppliers due to database error [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
						exit;
					}
				}					
			}
		}

// Build profile record
		$iapProfile['pro_no'] = $iapCurrentUser['ID'];
		$iapProfile['pro_first_name'] = get_user_meta($iapCurrentUser['ID'], 'first_name', true);
		$iapProfile['pro_last_name'] = get_user_meta($iapCurrentUser['ID'], 'last_name', true);
		$iapProfile['pro_name'] = $iapProfile['pro_first_name']." ".$iapProfile['pro_last_name'];
		$iapProfile['pro_nickname'] = get_user_meta($iapCurrentUser['ID'], 'nickname', true);
		$iapProfile['pro_street'] = "";
		$iapProfile['pro_city'] = "";
		$iapProfile['pro_state'] = "";
		$iapProfile['pro_zip'] = "";
		$iapProfile['pro_home_phone'] = "";
		$iapProfile['pro_cell_phone'] = "";
		$iapProfile['pro_email'] = "";
		$iapProfile['pro_google_calendar'] = "";
		$iapProfile['pro_help_level'] = 3;
		$iapProfile['pro_changed'] = date("Y-m-d", strtotime("now"));
		$iapProfile['pro_changed_by'] = $iapCurrentUser['ID'];
		$iapRet = IAP_Update_Data($iapProfile, "prof");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		if (IAP_Remove_Savearea("IAP274ST") < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot clean up the savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			exit;
		}

		$user_id = $_REQUEST['IAPUID'];
		update_user_meta($user_id, 'role', 'Contributor');
		if (get_user_meta($user_id, 'role', true) != 'Contributor') {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update your wp user role. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;			
		}

?>

		<span class=iapSuccess style='font-weight:bold; text-align:center;'>Your license has been created and will expire on <?php echo date("m/d/Y", strtotime($iapCompany['co_license_renewal'])); ?></span><br><br>

		So What's Next?<br>
		A link to the application should appear on the top menu. When you click on <span style='font-style: italic;'>Access It’s A Party App</span> You will want to:<br><br>
		&nbsp;&nbsp;&nbsp;1) Click on <span style='font-style: italic;'>About My Company</span> to check the information is correct an complete.<br><br>
		&nbsp;&nbsp;&nbsp;2) Click on <span style='font-style: italic;'>About Me</span> to complplete your profile information.<br><br>
		&nbsp;&nbsp;&nbsp;3) Check out <span style='font-style: italic;'>Quick Start Guide.</span><br><br>
		&nbsp;&nbsp;&nbsp;

		Go ahead. Click on the <span style='font-style: italic;'>Access It’s A Party App</span> menu item above. If it is not there click on <span style='font-style: italic;'>Home</span>. Contact Support if it still isn't there. <br><br> 

		<span class=iapSuccess style='font-weight:bold; text-align:center;'>Thank You For Registering It's A Party!</span><br>

<?php
		return;
	}
	if (IAP_Update_Savearea("IAP079BI", $iapCompany) < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update the savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		exit;
	}
}

$iapSuppliers = IAP_Get_Supplier_List();
if ($iapSuppliers < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve supplierss. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
if ($iapSuppliers != NULL) {
	$cSuppliers = array();
	$ti = 16;
	foreach($iapSuppliers as $iapS) {
		$iapSNm = str_replace('"', '', $iapS['supp_name']);
		$cSuppliers[] = "<input tabindex='".strval($ti)."' type='checkbox' id=csupp".strval($iapS['supp_id']).
			  " name=csupps[] value=csupp".strval($iapS['supp_id']).
			  ">".$iapSNm;
		$ti = $ti + 1;
	}
	$iapSelEna = "";
}

$iapHelpLvl = 3;

if ($iapPageError == "Y") {
	echo "<br><span class=iapError>Errors were found. Correct the errors and resubmit</span><br><br>";
}

?>

<form method='post' action='?page_id=274&action=trialret'>

<?php
$h = IAP_Do_Help(3, 79, 1); // level 3, page 79, section 1
if ($h != "") {
?>
	<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>
	<tr><td width='1%'></td><td width='80%'>
	<?php echo $h; ?>
	</td><td width='19%'></td></tr>
	</table>
<?php
}
?>

<table style='width:100%'>
<tr><td width='30%'></td><td width='70%'><br></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>What do you call the business: </span></td>
<td width='70%'><input class='iapFormField' maxlength='50' size='50' tabindex='1' name='BICoName' value='<?php echo $iapCompany['co_name']; ?>' autofocus></td></tr>

<tr><td width='30%'></td><td width='70%'><br></td></tr>

<tr><td colspan='2'><span class='iapFormLabel'>What is the business mailing address:</span></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Street:</span></td>
<td width='70%'><input class='iapFormField' maxlength='50' size='50' tabindex='2' name='BIMailStrt' value='<?php echo $iapCompany['co_mail_street']; ?>'></td></tr>

<tr><td width='30%'></td>
<td width='70%'><input class='iapFormField' maxlength='50' size='50' tabindex='3' name='BIMailStrt2' value='<?php echo $iapCompany['co_mail_street2']; ?>'></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;City, State Zip: </span></td>
<td width='70%'><input class='iapFormField' maxlength='30' size='30' tabindex='4' name='BIMailCity' value='<?php echo $iapCompany['co_mail_city']; ?>'>
&nbsp;&nbsp;&nbsp;<input class='iapFormField' maxlength='2' size='2' tabindex='5' name='BIMailState' value='<?php echo $iapCompany['co_mail_state']; ?>'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class='iapFormField' maxlength='10' size='10' tabindex='6' name='BIMailZip' value='<?php echo $iapCompany['co_mail_zip']; ?>'></td></tr>


<tr><td width='30%'></td><td width='70%'><br></td></tr>
<tr><td colspan='2'><span class='iapFormLabel'>What is the business shipping address:</span></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Same as mailing adress</span></td><td width='70%'><input class='iapFormField' tabindex='7' name='BIShipSame' type='checkbox'>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 79, 1);  	// level 1, page 79, section 1?>
</td></tr>

<tr><td width='30%'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Street:</span></td><td width='70%'><input class='iapFormField' maxlength='50' size='50' tabindex='8' name='BIShipStrt' value='<?php echo $iapCompany['co_ship_street']; ?>'></td></tr>

<tr><td width='30%'></td><td width='70%'><input class='iapFormField' maxlength='50' size='50' tabindex='9' name='BIShipStrt2' value='<?php echo $iapCompany['co_ship_street2']; ?>'></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;City, State Zip: </span></td><td width='70%'><input class='iapFormField' maxlength='30' size='30' tabindex='10' name='BIShipCity' value='<?php echo $iapCompany['co_ship_city']; ?>'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class='iapFormField' maxlength='2' size='2' tabindex='11' name='BIShipState' value='<?php echo $iapCompany['co_ship_state']; ?>'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class='iapFormField' maxlength='10' size='10' tabindex='12' name='BIShipZip' value='<?php echo $iapCompany['co_mail_zip']; ?>'></td></tr>

<tr><td width='30%'></td><td width='70%'><br></td></tr>
<tr><td width='30%'><span class='iapFormLabel'>What is the business phone number: </span></td><td width='70%'><input class='iapFormField' maxlength='20' size='20' tabindex='13' name='BIPhone' value='<?php echo $iapCompany['co_phone']; ?>'></td></tr>

<tr><td width='30%'></td><td width='70%'><br></td></tr>
<tr><td width='30%'><span class='iapFormLabel'>What is the business email: </span></td><td width='70%'><input class='iapFormField' maxlength='75' size='50' tabindex='14' name='BIEmail' value='<?php echo $iapCompany['co_email']; ?>'></td></tr>

<tr><td width='30%'></td><td width='70%'><br></td></tr>

<tr><td width='30%'><span class='iapFormLabel'>Should birthdays be added calendar: </span></td><td width='70%'><input class='iapFormField' maxlength='2' size='2' tabindex='15' name='BIBdays' value='<?php echo $iapCompany['co_add_birthdays']; ?>'>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 79, 2);  	// level 1, page 79, section 2?>
</td></tr>

</table>

<?php
$h = IAP_Do_Help(3, 79, 2); // level 3, page 79, section 2 - first time after license acquisition
if ($h != "") {
?>
	<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>
	<tr><td width='1%'></td><td width='80%'>
	<?php echo $h; ?>
	</td><td width='19%'></td></tr>
	</table>
<?php
}
?>

<table style='width:100%'><tr><td width='5%'></td><td width='25%'></td><td width='70%'></td></tr>
<tr><td width='30%'></td><td width='70%'></td></tr>
<tr><td colspan='2'><span class='iapFormLabel'>Check Your Direct Sales Organization(s): </span>&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 79, 4);  	// level 1, page 79, section 4?>
</td></tr>
<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;<span class=iapWarning style='font-size:80%'>Please contact Support if your organization is not listed.</span></td></tr>

<?php
foreach ($cSuppliers as $iapS) {
	echo "<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$iapS."></td></tr>";
}
?>

<tr><td width='30%'></td><td width='70%'>&nbsp;</td></tr>
<tr><td width='30%'></td><td width='70%'>&nbsp;</td></tr>

<tr><td width='30%'></td>
<td width='70%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type='submit' class=iapButton tabindex='20' name='BISubmit' value='Submit'>
</td></tr>

</table></form>

<?php return; ?>
