<?php

function iapCoEdit() {

	if ($_REQUEST['mod'] != "BI") {

		if ($_REQUEST['debugme'] == "Y") echo ">>>In IAPCoEdit.<br>";

		$iapReadOnly = "";
		$iapHeading = $_REQUEST['UserData']['DisplayName'];
		if ($iapHeading == ""
		or 	$_REQUEST['iap1st'] == "Y") {
			$_REQUEST['UserData']['HelpLevel'] = 3;
			if ($iapHeading != "") {
				$iapHeading = $iapHeading.", ";			
			}
			$iapHeading = $iapHeading."Please Set Up Your Business Information";
			echo "<table><tr><td width='15%'><span style='font-size:1px;'> </span></td><td width='40%'></td><td width='45%'></td></tr>";
			echo "<tr><td colspan='3' class='iapFormHead'>".$iapHeading."</td></table>";
		} else {
			$iapReadOnly = IAP_Format_Heading("Business Information");
		}

		if (IAP_Remove_Savearea("IAP079BI") < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot clean up the savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapPageError = "N";
		$iapCoChanged = "N";
		$iapCoSuppChanged = "N";

		$iapCompany = IAP_Get_Company($_REQUEST['CoId']);
		if ($iapCompany < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapCompany['Suppliers'] = array();
		$iapCoSuppliers = IAP_Get_Co_Suppliers();
		if ($iapCoSuppliers < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company suppliers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		foreach($iapCoSuppliers as $s) {
			$iapCompany['Suppliers'][] = $s['cs_supplier'];
		}
			
		if (IAP_Create_Savearea("IAP079BI", $iapCompany) < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create the profile savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

	} else {

		$iapCompany = (array) IAP_Get_Savearea("IAP079BI");
		if (!($iapCompany)) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company save area [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		if ($_REQUEST['debugme'] == "Y") {
			echo "......Company not set so build it.<br>";
		}

		$iapPageError = "N";
		$iapCoChanged = "N";
		$iapCoSuppChanged = "N";

		require_once("IAPValidators.php");

		if (isset($_REQUEST['BICoName'])) {
			$iapRet = IAP_Validate_Nonblank($iapCompany['co_name'], trim($_REQUEST['BICoName']));
			if ($iapRet['Changed'] == "Y") {
				$iapRet['Value'] = str_replace("'", "&apos;", $iapRet['Value']);
				$iapCompany['co_name'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			}
			if ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Company Name cannot be blank!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (isset($_REQUEST['BIMailStrt'])) {
			$iapRet = IAP_Validate_Nonblank($iapCompany['co_mail_street'], trim($_REQUEST['BIMailStrt']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_mail_street'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Mailing Street cannot be blank!</span><br>";
				$iapPageError = "Y";
			}
		}

		if ($_REQUEST['BIMailStrt2'] != "" 
		and $_REQUEST['BIMailStrt2'] != $iapCompany['co_mail_street2']) {
			$iapCompany['co_mail_street2'] = $_REQUEST['BIMailStrt2'];
			$iapCoChanged = "Y";
		}

		if (isset($_REQUEST['BIMailCity'])) {
			$iapRet = IAP_Validate_Nonblank($iapCompany['co_mail_city'], trim($_REQUEST['BIMailCity']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_mail_city'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Mailing City cannot be blank!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (isset($_REQUEST['BIMailState'])) {
			$iapRet = IAP_Validate_Nonblank($iapCompany['co_mail_state'], trim($_REQUEST['BIMailState']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_mail_state'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Mailing State cannot be blank!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (isset($_REQUEST['BIMailZip'])) {
			$iapRet = IAP_Validate_Nonblank($iapCompany['co_mail_zip'], trim($_REQUEST['BIMailZip']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_mail_zip'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			}elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Shiping Zip Code cannot be blank!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (!isset($_REQUEST['BIShipSame'])) {
			if (isset($_REQUEST['BIShipStrt'])) {
				$iapRet = IAP_Validate_Nonblank($iapCompany['co_ship_street'], trim($_REQUEST['BIShipStrt']));
				if ($iapRet['Changed'] == "Y") {
					$iapCompany['co_ship_street'] = $iapRet['Value'];
					$iapCoChanged = "Y";
				} elseif ($iapRet['Error'] == "1") {
					echo "<span class=iapError>Ship To Street cannot be blank!</span><br>";
					$iapPageError = "Y";
				}
			}
			if ($_REQUEST['BIShipStrt2'] != "" 
			and $_REQUEST['BIShipStrt2'] != $iapCompany['co_ship_street2']) {
				$iapCompany['co_ship_street2'] = $_REQUEST['BIShipStrt2'];
				$iapCoChanged = "Y";
			}
			if (isset($_REQUEST['BIShipCity'])) {
				$iapRet = IAP_Validate_Nonblank($iapCompany['co_ship_city'], trim($_REQUEST['BIShipCity']));
				if ($iapRet['Changed'] == "Y") {
					$iapCompany['co_ship_city'] = $iapRet['Value'];
					$iapCoChanged = "Y";
				} elseif ($iapRet['Error'] == "1") {
					echo "<span class=iapError>Ship To City cannot be blank!</span><br>";
					$iapPageError = "Y";
				}
			}
			if (isset($_REQUEST['BIShipState'])) {
				$iapRet = IAP_Validate_Nonblank($iapCompany['co_ship_state'], trim($_REQUEST['BIShipState']));
				if ($iapRet['Changed'] == "Y") {
					$iapCompany['co_ship_state'] = $iapRet['Value'];
					$iapCoChanged = "Y";
				} elseif ($iapRet['Error'] == "1") {
					echo "<span class=iapError>Ship To State cannot be blank!</span><br>";
					$iapPageError = "Y";
				}
			}
			if (isset($_REQUEST['BIShipZip'])) {
				$iapRet = IAP_Validate_Nonblank($iapCompany['co_ship_zip'], trim($_REQUEST['BIShipZip']));
				if ($iapRet['Changed'] == "Y") {
					$iapCompany['co_ship_zip'] = $iapRet['Value'];
					$iapCoChanged = "Y";
				} elseif ($iapRet['Error'] == "1") {
					echo "<span class=iapError>Ship To Zip Code cannot be blank!</span><br>";
					$iapPageError = "Y";
				}
			}
		}

		if (isset($_REQUEST['BIPhone'])) {
			$iapRet = IAP_Validate_Phone($iapCompany['co_phone'], trim($_REQUEST['BIPhone']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_phone'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == '1') {
				$iapCompany['co_phone'] = trim($_REQUEST['BIPhone']);
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Phone Number is not in a valid format!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (isset($_REQUEST['BIEmail'])) {
			$iapRet = IAP_Validate_Email($iapCompany['co_email'], trim($_REQUEST['BIEmail']));
			if ($iapRet['Changed'] == "Y") {
				$iapCompany['co_email'] = $iapRet['Value'];
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == '1') {
				$iapCompany['co_email'] = trim($_REQUEST['BIEmail']);
				$iapCoChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Email address must be active!</span><br>";
				$iapPageError = "Y";
			}
		}

		if (isset($_REQUEST['BIBday'])) {
			if ($_REQUEST['BIBday'] != "Y"
			and $_REQUEST['BIBday'] != "N") {
				echo "<span class=iapError>Add Birthdays must be either Y or N!</span><br>";
				$iapPageError = "Y";
			} else {
				if ($_REQUEST['BIBday'] != $iapCompany['co_add_birthdays']) {
					$iapCompany['co_add_birthdays'] = $_REQUEST['BIBday'];
					$iapCoSuppChanged = "Y";
				}
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
				if ($iapCoChanged == "Y") {
					if ($_REQUEST['iap1st'] == "Y") {
						$iapCompany['co_setup'] = date("Y-m-d");
						$iapCompany['co_license_renewal'] = date("Y-m-d",strtotime("+2 months"));
					}
					$iapRet = IAP_Update_Data($iapCompany, "comp");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update company information due to database error [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					if ($iapCompany['status'] == "NEW") {
						$iapCompany['co_id'] = $iapRet;
						$iapCompany['status'] = "EXISTING";
						$iapRet = IAP_Update_Data(array('cu_company' => $iapCompany['co_id'], 'cu_user' => $_REQUEST['UserData']['Id'], 'status' => "NEW"), "cous");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update company information due to database error [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
					}
					$_REQUEST['CoId'] = $iapCompany['co_id'];
					$_REQUEST['CoName'] = $iapCompany['co_name'];
					$_REQUEST['CoZip'] = $iapCompany['co_mail_zip'];
					$_REQUEST['Suppliers'] = $iapCompany['Suppliers'];
					$_REQUEST['AddBdays'] = $iapCompany['co_add_birthdays'];
					$_REQUEST['Expires'] = $iapCompany['co_license_renewal'];

					$iapUserData = $_REQUEST['UserData'];
					$iapUserData['CompanyId'] = $_REQUEST['CoId'];
					$iapUserData['CompanyName'] = $_REQUEST['CoName'];
					$iapUserData['DisplayName'] = $_REQUEST['CoName'];
					$iapUserData['Suppliers'] = $_REQUEST['Suppliers'];
					$iapUserData['AddBirthdays'] = $_REQUEST['AddBdays'];
					$_REQUEST['UserData'] = $iapUserData;

					if ($_REQUEST['iap1st'] == "Y") {
						$_REQUEST['back2co'] = "Y";
						require_once("MyPages/IAPAppHome.php");
						$_REQUEST['back2co'] = "N";
						$_REQUEST['mod'] = "IN";
						IAP_Finish_Home();
						return;
					}
				}
			}

// Update CoSupp if changes
			if ($iapCoSuppChanged == "Y") {
				$s = "DELETE FROM iap_co_suppliers WHERE cs_company = ".$_REQUEST['CoId'];
				$iapRet = iapProcessMySQL("delete", $s);
				if ($iapRet['retcode'] < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating suppliers for company [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$CoSuppliers = $iapCompany['Suppliers'];
				foreach($CoSuppliers as $cS) {
					if ($cS != 0) {
						$iapCS = IAP_Build_New_Row(array("table" => "cosup"));
						$iapCoSupp = $iapCS[0];
						$iapCoSupp['cs_company'] = $_REQUEST['CoId'];
						$iapCoSupp['cs_supplier'] = $cS;
						$iapCoSupp['cs_changed'] = date("Y-m-d");
						$iapCoSupp['cs_changed_by'] = $_REQUEST['IAPUID'];
						$iapRet = IAP_Update_Data($iapCoSupp, "cosup");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update company suppliers due to database error [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
					}					
				}
			}
		}
		if (IAP_Update_Savearea("IAP079BI", $iapCompany) < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update the savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
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
		foreach($iapSuppliers as $iapS) {
			$iapSNm = str_replace('"', '', $iapS['supp_name']);
			$cS = "<input type='checkbox' id='csupp".strval($iapS['supp_id'])."' name='csupps[]' value='csupp".strval($iapS['supp_id'])."'";
			foreach($iapCompany['Suppliers'] as $s) {
				if ($s == $iapS['supp_id']) {
					$cS = $cS." checked";
					break;
				}
			}
			$cS = $cS.">".$iapSNm;
			$cSuppliers[] = $cS;
		}
		$iapSelEna = "";
	}

	$iapHelpLvl = $_REQUEST['HelpLevel'];

	if ($_REQUEST['IAPUID'] == 9) {
		echo "<span class=iapError style='font-size:110%;'>Company Information cannot be changed for the Demo business.</span><br><br>";
		$iapReadOnly = "readonly";
	}

	if ($iapPageError == "Y") {
		echo "<br><span class=iapError>Errors were found. Correct the errors and resubmit</span><br><br>";
	}

	echo "<form method='post' action='?mod=BI&iap1st=".$_REQUEST['iap1st']."' name='iapCompany'>";
	echo "<table style='width:100%'>";

	$h = IAP_Do_Help(3, 79, 1); // level 3, page 79, section 1
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
	}

	if ($_REQUEST['iap1st'] != "Y") {
		echo "<table style='width:100%'>";
		echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
		echo "<tr><td width='5%'></td><td colspan='2' text-align='center'><span class=iapSuccess style='font-weight:bold'>Thank you for being a valued client since ".date("m/d/Y", strtotime($iapCompany['co_setup']))."</span></td></tr>";
	}
	echo "<tr><td width='5%'></td><td colspan='2' text-align='center'><span class=iapSuccess style='font-weight:bold'>Your current subscription will expire on ".date("m/d/Y", strtotime($iapCompany['co_license_renewal']))."</span></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td colspan='2'><span class='iapFormLabel'>What do you call the business: </span></td>";

	echo "<td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='1' name='BICoName' value='".$iapCompany['co_name']."' autofocus></td></tr>";
	echo "<tr><td width='5%'></td><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NOTE: If you enter an astrophe in the Company Name it may show with a backslash. I am Working on this!</td></tr>";


	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";

	echo "<tr><td colspan='3'><span class='iapFormLabel'>What is the business mailing address:</span></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>Street:</span></td>";
	echo "<td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='2' name='BIMailStrt' value='".$iapCompany['co_mail_street']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'></td>";
	echo "<td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='3' name='BIMailStrt2' value='".$iapCompany['co_mail_street2']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>City: </span></td>";
	echo "<td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='30' size='30' tabindex='4' name='BIMailCity' value='".$iapCompany['co_mail_city']."'>";
	echo "<span class='iapFormLabel'> State: </span><input ".$iapReadOnly." class='iapFormField' maxlength='2' size='2' tabindex='5' name='BIMailState' value='".$iapCompany['co_mail_state']."'>";
	echo "<span class='iapFormLabel'> Zip: </span><input ".$iapReadOnly." class='iapFormField' maxlength='10' size='10' tabindex='6' name='BIMailZip' value='".$iapCompany['co_mail_zip']."'></td></tr>";


	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td colspan='3'><span class='iapFormLabel'>What is the business shipping address:</span></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>Same as mailing adress</span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' tabindex='7' name='BIShipSame' type='checkbox'>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 79, 1);  	// level 1, page 79, section 1
	echo "</td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>Street:</span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='8' name='BIShipStrt' value='".$iapCompany['co_ship_street']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='9' name='BIShipStrt2' value='".$iapCompany['co_ship_street2']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>City: </span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='30' size='30' tabindex='10' name='BIShipCity' value='".$iapCompany['co_ship_city']."'>";
	echo "<span class='iapFormLabel'> State: </span><input ".$iapReadOnly." class='iapFormField' maxlength='2' size='2' tabindex='11' name='BIShipState' value='".$iapCompany['co_ship_state']."'>";
	echo "<span class='iapFormLabel'> Zip: </span><input ".$iapReadOnly." class='iapFormField' maxlength='10' size='10' tabindex='12' name='BIShipZip' value='".$iapCompany['co_mail_zip']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>What is the business phone number: </span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='13' name='BIPhone' value='".$iapCompany['co_phone']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>What is the business email: </span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='50' size='50' tabindex='14' name='BIEmail' value='".$iapCompany['co_email']."'></td></tr>";

	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>Should birthdays be added calendar: </span></td><td width='70%'><input ".$iapReadOnly." class='iapFormField' maxlength='2' size='2' tabindex='15' name='BIBdays' value='".$iapCompany['co_add_birthdays']."'>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 79, 2);  	// level 1, page 79, section 2
	echo "</td></tr>";

	echo "</table>";

	$h = IAP_Do_Help(3, 79, 2); // level 3, page 79, section 2 - first time after license acquisition
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
	}

	echo "<table style='width:100%'><tr><td width='5%'></td><td width='25%'></td><td width='70%'></td></tr>";
	echo "<tr><td width='5%'></td><td colspan='2'><span class='iapFormLabel'>Check Your Direct Sales Organization(s): </span>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 79, 4);  	// level 1, page 79, section 4
	echo "</td></tr>";
	echo "<tr><td width='5%'></td><td colspan='2'>&nbsp;&nbsp;&nbsp;<span class=iapWarning style='font-size:80%'>Please contact Support if your organization is not listed.</span></td></tr>";

	foreach ($cSuppliers as $iapS) {
		echo "<tr><td width='5%'></td><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$iapS."</td></tr>";
	}

	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";

	if ($iapReadOnly != "readonly") {
		echo "<tr><td width='5%'></td><td width='25%'></td>";
		echo "<td width='70%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' class=iapButton tabindex='20' name='BISubmit' value='Submit'></td></tr>";
	}
	echo "<tr><td colspan='3'><input type='hidden' name='LHCA' id='LHCA' value='".$_REQUEST['CoId']."'></td></tr>";
	echo "<tr><td colspan='3'><input type='hidden' name='IAPMODE' id='IAPMODE' value='".$_REQUEST['UserData']['Mode']."'></td></tr>";
	echo "<tr><td colspan='3'><input type='hidden' name='IAPDL' id='IAPDL' value=''></td></tr>";
	echo "</table></form>";

}

function iapCoGet($iapCoId) {

if ($_REQUEST['debugme'] == "Y") echo ">>>In IAPCoGet with Company Id of ".strval($iapCoId).".<br>";

	$iapCompany = IAP_Get_Company($iapCoId);
	if ($iapCompany < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$_REQUEST['CoId'] = $iapCompany['co_id'];
	$_REQUEST['CoName'] = $iapCompany['co_name'];
	$_REQUEST['CoZip'] = $iapCompany['co_mail_zip'];
	$_REQUEST['AddBdays'] = $iapCompany['co_add_birthdays'];
	$_REQUEST['CoSetUp'] = $iapCompany['co_setup'];
	$_REQUEST['Expires'] = $iapCompany['co_license_renewal'];

	$iapCompany['Suppliers'] = array();
	$iapCoSuppliers = IAP_Get_Co_Suppliers();
	if ($iapCoSuppliers < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company suppliers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	foreach($iapCoSuppliers as $s) {
		if ($c['status'] != "NEW") {
			$iapCompany['Suppliers'][] = $s['cs_supplier'];			
		}
	}
	$_REQUEST['Suppliers'] = $iapCompany['Suppliers'];
	return;
}

function iapCoSelect($iapCos, $iapFrom) {

if ($_REQUEST['debugme'] == "Y") echo ">>>In IAPCoSelect.<br>";

	echo "<table><tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td colspan='3' class='iapFormHead'>".$_REQUEST['UserData']['DisplayName']."</td></tr>";
	echo "<tr><td colspan='3' class='iapFormHead'>Your Business</td></tr></table>";

	if ($_REQUEST['mod'] == "CS") {
		$_REQUEST['co_id'] = $_REQUEST['IAPCoSel'];
		iapCoGet();
		return;
	}


// TODO check how to change this


	echo "<form method='post' action='?page_id=".$iapFrom."&mod=CS' name='iapCompany'>";
	echo "<table>";
	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td width='5%'></td><td width='25%'></td><td width='70%'><br></td></tr>";
	echo "<tr><td width='5%'></td><td width='25%'><span class='iapFormLabel'>Select A Business:</td><td width='70%'><select name='IAPCoSel' size='1'>";
	foreach($iapCos as $iapCo) {
		$iapCompany = IAP_Get_Company($iapCo);
		if ($iapCompany < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<option value='".strval($iapCompany['co_id'])."'>".$iapCompany['co_name']."</option>";
	}
	echo "</input></td></tr>";
	echo "<tr><td colspan='3'><input ".$iapReadOnly." type='hidden' name='LHCA' id='LHCA' value='".$_REQUEST['CoId']."'></td></tr>";
	echo "<tr><td colspan='3'><input ".$iapReadOnly." type='hidden' name='LHCA' id='LHCA' value='".$_REQUEST['CoId']."'></td></tr>";
	echo "<tr><td colspan='3'><input ".$iapReadOnly." type='hidden' name='IAPDL' id='IAPDL' value=''></td></tr>";
	echo "</table></form>";
}

// ------------------------------------- 
// Entry point when selected from menu
//

// coming back in with mod cs see above

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['mod'] == "IN") {
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In IAPCompany.<br>";
}

if ($_REQUEST['mod'] != "IN"
and $_REQUEST['mod'] != "BI") {
	require_once(ABSPATH."IAPServices.php");
	if (iap_Program_Start("79") < 0) {
		return;
	}
};

if ($_REQUEST['mod'] == "BI") {
	iapCoEdit();
	return;
}

if (IAP_Remove_Savearea("IAP079BI") < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the business savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

if ($_REQUEST['debugme'] == "Y") echo ">>> Doing Get CoUser.<br>";

$IAPCos = IAP_Get_CoUser();
if ($IAPCos < 0) {
	echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot retrieve the companies for user [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	return;	
}

if ($IAPCos[0]['status'] == "NEW") {
	echo "<span style=iapError>No company was found for you. Please add one!</span><br>";
	iapCoEdit();
} else {
	if (count($IAPCos) == 1) {
		$_REQUEST['co_id'] = $IAPCos[0]['cu_company'];
		iapCoEdit();
	} else {
		iapCoSelect($IAPCos, "79");
	}
}

return;

?>