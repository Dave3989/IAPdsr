<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Customer Maintenance with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("134") < 0) {
	return;
};

if ($_REQUEST['action'] == 'selected') {
	if (empty($_REQUEST['custno'])) {
		echo "<span class=iapError>IAP INTERNAL ERROR: No customer information provided by Application Home. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	$iapCNo = $_REQUEST['custno'];
	$iapCustomer = IAP_Get_Customer_By_No($iapCNo);
	if ($iapCustomer < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapCustomer['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: The customer information supplied by Application Home is invalid. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	$iapRet = IAP_Create_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//	$DivSelect = "none";
	$DivShow = "block";	

} elseif ($_REQUEST['action'] == 'p134retA') {

// ------------------------------------------------------------------
// This is never executed -- handled by jscript at end of program  --
// ------------------------------------------------------------------

	$iapCustomer = (array) IAP_Get_Savearea("IAP134CM", $_REQUEST['IAPUID']);
	if (!empty($iapCustomer)) {
		IAP_Remove_Savearea("IAP134CM", $_REQUEST['IAPUID']);
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "......savearea does not exist so build it.<br>";
	}

	if (empty($_REQUEST['custnames'])) {
		$iapC = (array) IAP_Build_New_Row(array("table" => "cust"));
		$iapCustomer = $iapC[0];
		$_REQUEST['action'] = "NEW";
	} else {
		$iapCustomer = (array) IAP_Get_All_Customers();
		$_REQUEST['action'] = "EXISTING";
	}
	$iapOrigAction = $_REQUEST['action'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key IAP134.<br>";
	}

	$iapRet = IAP_Create_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "none";
	$DivShow = "block";
	
} elseif ($_REQUEST['action'] == 'p134retB') {

// get customer 

	if ($_REQUEST['CSTATUS'] == "NEW") {
		IAP_Remove_Savearea("IAP134CM", $_REQUEST['IAPUID']);
		$iapC = (array) IAP_Build_New_Row(array("table" => "cust"));
		$iapCustomer = $iapC[0];
		if ($iapCustomer < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR creating customer savearea [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapRet = IAP_Create_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {
		$iapCustomer = (array) IAP_Get_Savearea("IAP134CM", $_REQUEST['IAPUID']);
		if (empty($iapCustomer)) {
		    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		    return;
		}
		if (!(empty($_REQUEST['CCUSTNO']))) {
			$iapCNo = $_REQUEST['CCUSTNO'];
			$iapCustomer = IAP_Get_Customer_By_No($iapCNo);
			if ($iapCustomer < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapCustomer['status'] = "EXISTING";
		}
	}

	$iapPageError = 0;
	$_REQUEST['FollowUpChg'] = "N";
	$_REQUEST['NameEmailChg'] = "N";
	$iapChanged = "N";

	require_once("IAPValidators.php");

    if (isset($_REQUEST['cname'])) {
        $iapRet = IAP_Validate_Nonblank($iapCustomer['cust_name'], ucwords($_REQUEST['cname']));
        if ($iapRet['Changed'] == "Y") {
            $iapCustomer['cust_name'] = $iapRet['Value'];
            $iapChanged = "Y";
			$_REQUEST['NameEmailChg'] = "Y";
        }
        if ($iapRet['Error'] == "1") {
            echo "<span class=iapError>Customer Name cannot be blank!</span><br>";
            $iapPageError = 1;
        }
    } elseif (empty($iapCustomer['cust_name'])) {
        echo "<span class=iapError>Customer Name cannot be blank!</span><br>";
		$iapPageError = 1;
	}
	if (isset($_REQUEST['cstreet'])
	and	$iapCustomer['cust_street'] != ucwords($_REQUEST['cstreet'])) {
		$iapCustomer['cust_street'] = ucwords($_REQUEST['cstreet']);
	        $iapChanged = "Y";
	}
	if (isset($_REQUEST['ccity'])
	and $iapCustomer['cust_city'] != ucwords($_REQUEST['ccity'])) {
		$iapCustomer['cust_city'] = ucwords($_REQUEST['ccity']);
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['cstate'])
	and $iapCustomer['cust_state'] != ucwords($_REQUEST['cstate'])) {
		$iapCustomer['cust_state'] = ucwords($_REQUEST['cstate']);
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['czip'])
	and $iapCustomer['cust_zip'] != $_REQUEST['czip']) {
		$iapCustomer['cust_zip'] = $_REQUEST['czip'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['cphone'])) {
		$iapRet = IAP_Validate_Phone($iapCustomer['cust_phone'], $_REQUEST['cphone']);
		if ($iapRet['Changed'] == "Y") {
			$iapCustomer['cust_phone'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1"
		and !(empty($iapCustomer['cust_phone']))) {
			$iapCustomer['cust_phone'] = "";
			$iapChanged = "Y";
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Cell Phone improperly formatted!</span><br>";
			$iapPageError = 1;
		}
	}
	if (isset($_REQUEST['cemail'])
	and $iapCustomer['cust_email'] != strtolower($_REQUEST['cemail'])) {
		$iapCustomer['cust_email'] = strtolower($_REQUEST['cemail']);
		$iapCustomer['cust_newsletter_add_date'] = "0000-00-00";
		$iapChanged = "Y";
		$_REQUEST['NameEmailChg'] = "Y";
	}
	if (isset($_REQUEST['cfacebk'])
	and strtolower($iapCustomer['cust_facebook']) != strtolower($_REQUEST['cfacebk'])) {
		$iapCustomer['cust_facebook'] = $_REQUEST['cfacebk'];
		$iapChanged = "Y";
	}
	if (isset($_REQUEST['cnewsltr'])) {
		if ($iapCustomer['cust_newsletter'] != "Y") {
			$iapCustomer['cust_newsletter'] = "Y";
			$iapChanged = "Y";
		}
	} elseif ($iapCustomer['cust_newsletter'] == "Y") {
			$iapCustomer['cust_newsletter'] = "N";
			$iapChanged = "Y";
	}
	if (isset($_REQUEST['cfollowcons'])) {
		if ($iapCustomer['cust_followup_consultant'] != "Y") {
			$iapCustomer['cust_followup_consultant'] = "Y";
			$iapChanged = "Y";
			$iapFollowUpChg = "y";
			$_REQUEST['FollowUpChg'] = "Y";
		}
	} elseif ($iapCustomer['cust_followup_consultant'] == "Y") {
			$iapCustomer['cust_followup_consultant'] = "N";
			$iapChanged = "Y";
			$iapFollowUpChg = "Y";
			$_REQUEST['FollowUpChg'] = "Y";
	}
	if (isset($_REQUEST['cfollowparty'])) {
		if ($iapCustomer['cust_followup_party'] != "Y") {
			$iapCustomer['cust_followup_party'] = "Y";
			$iapChanged = "Y";
		}
	} elseif ($iapCustomer['cust_followup_party'] == "Y") {
			$iapCustomer['cust_followup_party'] = "N";
			$iapChanged = "Y";
	}
	if (isset($_REQUEST['cmetdate'])) {
		if (empty($_REQUEST['cmetdate'])) {
			if (!empty($iapCustomer['cust_met_date'])) {
				$iapCustomer['cust_met_date'] = "0000-00-00";			
				$iapCustomer['cust_met_peid'] = 0;
   				$iapChanged = "Y";
   			}
		} else {
			$iapRet = IAP_Validate_Date($iapCustomer['cust_met_date'], $_REQUEST['cmetdate'], "Y");
			if ($iapRet['Changed'] == "Y") {
	    		$iapCustomer['cust_met_date'] = $iapRet['Value'];
				$iapCustomer['cust_met_peid'] = $_REQUEST['CSELPE'];
	    		$iapChanged = "Y";
			}
			if ($iapRet['Error'] == "1") {
	            $iapCustomer['cust_met_at'] = "0000-00-00";
				$iapCustomer['cust_met_peid'] = 0;
	            $iapChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Met Date is incorrectly formatted.</span><br>";
				$iapPageError = 1;
			}
		}
	}
	if (isset($_REQUEST['cmetat'])) {
		if ($_REQUEST['cmetat'] != $iapCustomer['cust_met_at']) {
			$iapCustomer['cust_met_at'] = $_REQUEST['cmetat'];
			if ($_REQUEST['CSELPE'] > 0) {
				$iapCustomer['cust_met_peid'] = $_REQUEST['CSELPE'];
			}
			$iapChanged = "Y";
		}
	}
	$iapCustomer['cust_met_type'] = $_REQUEST['CTYPE'];

	$_REQUEST['BDChg'] = "N";
	if (isset($_REQUEST['cbirth'])) {
		if (empty($_REQUEST['cbirth'])) {
			if (!empty($iapCustomer['cust_birthday'])) {
				$iapCustomer['cust_birthday'] = "";			
            	$_REQUEST['BDChg'] = "Y";
            	$iapChanged = "Y";
            }
		} else {
			$iapBD = str_replace("-", "/", $_REQUEST['cbirth']."/1960");
			$iapRet = IAP_Validate_Date($iapCustomer['cust_birthday'], $iapBD, "Y");
	        if ($iapRet['Changed'] == "Y") {
	            $iapCustomer['cust_birthday'] = $_REQUEST['cbirth'];
	            $_REQUEST['BDChg'] = "Y";
	            $iapChanged = "Y";
	        }
	        if ($iapRet['Error'] == "1") {
	            $iapCustomer['cust_birthday'] = "";
	            $_REQUEST['BDChg'] = "Y";
	            $iapChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Birthday is incorrectly formatted. Must be mm/dd</span><br>";
				$iapPageError = 1;
			}
		}
	}
	$_REQUEST['AnChg'] = "N";
	if (isset($_REQUEST['canniv'])) {
		if (empty($_REQUEST['canniv'])) {
			if (!empty($iapCustomer['cust_anniversary'])) {
				$iapCustomer['cust_anniversary'] = "";			
            	$_REQUEST['AnChg'] = "Y";
            	$iapChanged = "Y";
            }
		} else {
			$iapAn = str_replace("-", "/", $_REQUEST['canniv']."/1960");
			$iapRet = IAP_Validate_Date($iapCustomer['cust_anniversary'], $iapAn, "Y");
	        if ($iapRet['Changed'] == "Y") {
	            $iapCustomer['cust_anniversary'] = $_REQUEST['canniv'];
	            $_REQUEST['AnChg'] = "Y";
	            $iapChanged = "Y";
	        }
	        if ($iapRet['Error'] == "1") {
	            $iapCustomer['cust_anniversary'] = "";
	            $_REQUEST['AnChg'] = "Y";
	            $iapChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Anniversary is incorrectly formatted. Must be mm/dd</span><br>";
				$iapPageError = 1;
			}
		}
	}
	if (isset($_REQUEST['cnotes'])) {
		if ($_REQUEST['cnotes'] != $iapCustomer['cust_notes']) {
			$iapCustomer['cust_notes'] = $_REQUEST['cnotes'];
			$iapChanged = "Y";
		}
	}

	if ($iapPageError == 0
	and $iapChanged == "Y") {
		require_once(ABSPATH."MyPages/IAPCreateCust.php");
		$iapCustomer = IAP_Create_Customer($iapCustomer);
		if ($iapCustomer['status'] == "NEW") {
			$iapCustomer['status'] = "EXISTING";
			$iapU = "added";
		} else {
			$iapU = "updated";
		}
		echo "<br><span class=iapSuccess>Customer ".$iapCustomer['cust_name']." was successfully ".$iapU.".</span><br><br>";
	}

	$iapRet = IAP_Update_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapOrigAction = $_REQUEST['origaction'];

	$DivSelect = "block";
	$DivShow = "block";	

} else {

	if (IAP_Remove_Savearea("IAP134CM") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the customer savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapC = (array) IAP_Build_New_Row(array("table" => "cust"));
	$iapCustomer = $iapC[0];
	$iapCustomer['cust_newsletter'] = "N";
	$iapCustomer['cust_followup_consultant'] = "N";
	$iapCustomer['cust_followup_party'] = "N";
	$iapCustomer['cust_met_peid'] = 0;
	$iapRet = IAP_Create_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";

$iapCusts = iap_Get_Customer_List("N");
if ($iapCusts < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapCusts != NULL) {
	$iapCustList = "";
	$c = "";
	foreach($iapCusts as $iapC) {
		$iapCNm = str_replace('"', '', $iapC['cust_name']);
		$iapCustList = $iapCustList.$c.'"'.$iapCNm.'"';
		$c = ",";
	}
	$iapSelEna = "";
}

$iapCusts = iap_Get_Customer_List("E");
if ($iapCusts < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapCusts != NULL) {
	$iapEmailList = "";
	$c = "";
	foreach($iapCusts as $iapC) {
		$iapEmailList = $iapEmailList.$c.'"'.$iapC['cust_email'].'"';
		$c = ",";
	}
	$iapSelEna = "";
}

$iapCusts = iap_Get_Customer_List("P");
if ($iapCusts < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapCusts != NULL) {
	$iapPhoneList = "";
	$c = "";
	foreach($iapCusts as $iapC) {
		$iapPhoneList = $iapPhoneList.$c.'"'.$iapC['cust_phone'].'"';
		$c = ",";
	}
	$iapSelEna = "";
}

/*
$iapCities = iap_Get_Customer_Cities();
if ($iapCities < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customer cities. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapCities != NULL) {
	$iapCityOpts = "<datalist id='iapCities'>";
	foreach($iapCities as $iapC) {
		if (!(empty($iapC))) {
			$iapCityOpts = $iapCityOpts."<option value='".ucwords(strtolower($iapC['cust_city']))."'>";
		}
	}
	$iapCityOpts = $iapCityOpts."</datalist>";
	echo $iapCityOpts;
}
*/

$iapCustomer['pe_selector'] = "";
$iapCustomer['pe_type'] = "N";
$iapPar = IAP_Get_PE_List("N");		// Do not get closed parties
if ($iapPar < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
if ($iapPar != NULL) {
	$cParties = "";
	$cEvents = "";
	$cE = "";
	$cP = "";
	$iapCustomer['pe_type'] = "N";
	foreach($iapPar as $iapP) {
		$sponsor = trim($iapP['pe_sponsor']);
		$sponsor = str_replace('.', '', $sponsor);
		$sponsor = str_replace(',', '', $sponsor);
		$sponsor = str_replace("'", "", $sponsor);
		$sponsor = str_replace('-', '', $sponsor);
		if ($iapP['pe_type'] == "P") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".$sponsor;
			$cParties = $cParties.$cP.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'"}';
			$cP = ",";
			if ($iapCustomer['cust_met_peid'] == $iapP['pe_id']) {
				$iapCustomer['pe_selector'] = $peText;
				$iapCustomer['pe_type'] = "P";
			}
		} elseif ($iapP['pe_type'] == "E") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." event at ".$sponsor;
			$cEvents = $cEvents.$cE.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'"}';
			$cE = ",";
			if ($iapCustomer['cust_met_peid'] == $iapP['pe_id']) {
				$iapCustomer['pe_selector'] = $peText;
				$iapCustomer['pe_type'] = "E";
			}
		}
	}
}


$iapSales = array();
if (!empty($iapCustomer['cust_no'])) {
	if ($iapCustomer['status'] != "NEW") {
		$iapSales = IAP_Get_SaleDet_For_Cust($iapCustomer['cust_no']);
		if ($iapSales < 0) {
		    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve sales detail for customer. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		if (!is_null($iapSales)) {
			for($i=0; $i<count($iapSales); $i++) {
				$iapS = $iapSales[$i];
				if (empty($iapS['CO_description'])) {
					$iapS['cat_description'] = $iapS['SUPP_description'];
					$iapS['cat_item_code'] = $iapS['SUPP_item_code'];
				} else {
					$iapS['cat_description'] = $iapS['CO_description'];
					$iapS['cat_item_code'] = $iapS['CO_item_code'];
				}
				$iapSales[$i] = $iapS;
			}
		}
	}
}

$iapReadOnly = IAP_Format_Heading("Customers");

$h = IAP_Do_Help(3, 134, 1); // level 3, page 134, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='cchoose' style='display:<?php echo $DivSelect; ?>;'>
<p style='text-indent:50px; width:100%'>
<form name='cselform' action='?action=p134retA&origaction=initial' method='POST' onsubmit='return iapNoSubmit();' onkeypress='stopEnterSubmitting(window.event)'>

	<span class=iapFormLabel style="padding-left: 40px;">
	<label for="cCustList">&nbsp;&nbsp;&nbsp;Select a customer by name: </label>
	<input type="text" id="cCustList" size="35" maxlength="50">&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 1); ?> <!-- level 1, page 134, section 1 -->
	<br>
	<label for="cEmailList">Select a customer by email address: </label>
	<input type="email"" id="cEmailList" size="35" maxlength="100">
	</span>
	<br>
	<label for="cPhoneList">&nbsp;Select a customer by phone number: </label>
	<input type="tel" id="cPhoneList" size="15" maxlength="20" placeholder="xxx-xxx-xxxx" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
	</span>
	<br>
	<span class=iapSuccess style="padding-left: 50px;">&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<!--
 	&nbsp;<a href=# onclick='cGoClicked()' name='iapGo' id='iapGo'><img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;border:0;' title='iapGo'></a>
-->

 	&nbsp;<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/LHCGoGreen.jpg' id=iapGo style='width:25px;height:25px;vertical-align:bottom;border-style:none;border:0;' title='iapGo' onclick='cGoClicked()'>
	<br><br>
	<span class=iapError id="cError" style="padding-left:40px; display:none;">Select a customer by name, email or phone OR click on Add a New Customer!</span>

<?php
	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapFormLabel style='padding-left: 60px;'>";
		echo "<input type='button' class=iapButton name='cadd' id='cadd' value='Add A New Customer' onclick='cAddClicked()' />";
	}
?>
</form>
</p>
</div>
<div id='cdetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='text-indent:50px; width:100%'>

<form name='cdetform' action='?action=p134retB&origaction=<?php echo $iapOrigAction; ?>' method='POST' onkeypress='stopEnterSubmitting(window.event)'>
<table><tbody>

<tr><td style='width:5%'>&nbsp;</td><td style='width:14%'>&nbsp;</td><td style='width:81%'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Name:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="1" size="50" maxlength="50" name="cname" id="cname" value="<?php echo $iapCustomer['cust_name']; ?>" autofocus>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr><td colspan="3"><span class='iapFormLabel'>Address:</span></td></tr>

<tr>
<td style="width: 5%"></td>
<td style="width: 14%;"><span class='iapFormLabel'>Street:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="2" maxlength="50" size="50" name="cstreet" id="cstreet" value="<?php echo $iapCustomer['cust_street']; ?>">
</td></tr>
<tr>
<td style="width: 5%"></td>
<td style="width: 14%;"><span class='iapFormLabel'>City, State, Zip:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="3" maxlength="40" size="40" name="ccity" id="ccity" value="<?php echo $iapCustomer['cust_city']; ?>">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="4" maxlength="2" size="2" name="cstate" id="cstate" value="<?php echo $iapCustomer['cust_state']; ?>">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="5" maxlength="10" size="10" name="czip" id="czip" value="<?php echo $iapCustomer['cust_zip']; ?>">
</td></tr>

<?php
$a = trim($iapCustomer['cust_street'])."|".trim($iapCustomer['cust_city']).", ".trim($iapCustomer['cust_state'])." ".trim($iapCustomer['cust_zip']);
$a = str_replace("||", "|", $a);
if ($a == ",") {	// If only the comma added between city and state exists, get rid of it.
	$a = NULL;
	$d = "none";
}
if ($a !== NULL) {
	$a = str_replace(" ", "+", $a);
	$a = str_replace("|", ",", $a);
	$d = "inline";
}
?>
<td style="width: 5%"></td>
<td style='width: 14%;'></td>
<td style='width: 81%;'
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style='display:<php echo $d; ?>' id='cmap'>
<a id='cmapa' href='https://www.google.com/maps/place/<?php echo $a; ?>' target='_blank'>See On The Map.</a>
</span>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Phone:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="6" maxlength="15" size="15" name="cphone" id="cphone" placeholder="xxx-xxx-xxxx" value="<?php echo $iapCustomer['cust_phone']; ?>">
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Email:</span></td>
<td style="width: 81%;">
	<input type="email" <?php echo $iapReadOnly; ?> tabindex="7" maxlength="100" size="50" name="cemail" id="cemail" value="<?php echo $iapCustomer['cust_email']; ?>">
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Facebook:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="8" maxlength="100" size="50" name="cfacebk" id="cfacebk" value="<?php if ($iapCustomer['cust_facebook'] > "") { echo $iapCustomer['cust_facebook']; } ?>">
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Birthday:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="9" maxlength="5" size="5" name="cbirth" id="cbirth" placeholder="mm/dd" value="<?php if (!empty($iapCustomer['cust_birthday'])) { echo date("m/d",strtotime($iapCustomer['cust_birthday']."/1960")); } ?>">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Anniversary:</span>
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="10" maxlength="5" size="5" name="canniv" id="canniv" placeholder="mm/dd" value="<?php if (!empty($iapCustomer['cust_anniversary'])) { echo date("m/d",strtotime($iapCustomer['cust_anniversary']."/1960")); } ?>">
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 2); ?> <!-- level 1, page 134, section 2 -->

</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Newsletter:</span></td>
<td style="width: 81%;">
	<input type="checkbox" <?php echo $iapReadOnly; ?> tabindex="11" name="cnewsltr" id="cnewsltr" value="cnewsyes"<?php if ($iapCustomer['cust_newsletter'] == "Y") { echo " checked"; } ?>> Check to send newsletter.
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 5); ?> <!-- level 1, page 134, section 5 -->
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='3'><span class='iapFormLabel' >Where and when did you meet this customer:</span></td>
</tr>
<tr>
<td colspan='2'></td>
<td style="width: 81%;">
	<input type="radio" name="stype" id="stypeparty" value="P" tabindex="12" onchange='sSetType("P")'
		<?php if ($iapCustomer['pe_type'] == "P") { echo " checked"; } ?>
	>At a Party&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeevent" value="E" tabindex="12" onchange='sSetType("E")'
		<?php if ($iapCustomer['pe_type'] == "E") { echo " checked"; } ?>
	>Event&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeneither" value="N" tabindex="12" onchange='sSetType("N")'
		<?php if ($iapCustomer['pe_type'] == "N") { echo " checked"; } ?>
	>Neither&nbsp;&nbsp;&nbsp;

<!-- may use other types in the future such as Other
	<input type="text" type="radio" name="stype" id="stypefacebk" value="F" tabindex="13" onchange='sSetType("F")'
		<!--php if ($iapCustomer['pe_type'] == "F") { echo " checked"; } ?>
	>Facebook*&nbsp;&nbsp;&nbsp;
	<input type="text" type="radio" name="stype" id="stypeweb" value="W" tabindex="13" onchange='sSetType("W")'
		<!-- php if ($iapSale['sale_type'] == "W") { echo " checked"; } ?>
	>Website*&nbsp;&nbsp;&nbsp;
	<input type="text" type="radio" name="stype" id="stypeother" value="O" tabindex="13" onchange='sSetType("O")'
		<!--php if ($iapSale['sale_type'] == "O") { echo " checked"; } ?>
	>Other*
-->
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 8);//		Help Narative	<!-- level 1, page 134, section 8 --> ?>

</td></tr>
<tr><td colspan='2'></td><td style="width:81%;">
<span id=cparError class=iapError style='display:none;'>The party was not found. Retry or click Add.</span>
<span id=cevtError class=iapError style='display:none;'>The event was not found. Retry or click Add.</span>
</td></tr>

<?php

	if ($iapCustomer['pe_type'] == "P") {
		$pl ="Select a Party:";
		$ps = " value='".$iapCustomer['pe_selector']."'";
		$p = "inline";
		$n = "inline";
		$e = "none";
	} elseif ($iapCustomer['pe_type'] == "E") {
		$pl ="Select an Event:";
		$es = " value='".$iapCustomer['pe_selector']."'";
		$e = "inline";
		$n = "inline";
		$p = "none";
	} else {
		$pl =" ";
		$n = "none";
		$e = "none";
		$p = "none";
	}
?>
<tr style='vertical-align:top;'>
<td style='width: 5%'></td>
<td style='width: 14%;'>
<span class='iapFormLabel' id='cpelabel' style='display:<?php echo $n; ?>'><?php echo $pl; ?></span>
</td>
<td style='width:81%;'>
<input type="text" style='display:<?php echo $iapReadOnly." ".$p; ?>' tabindex='13' name='cpeparty' id='cpeparty'  size='50'".$ps.">
<input type="text" style='display:<?php echo $iapReadOnly." ".$e; ?>' tabindex='13' name='cpeevent' id='cpeevent'  size='50'".$es.">

</td></tr>


<tr>
<td style="width: 5%"></td>
<td style="width: 14%;"><span class='iapFormLabel'>Met On:</span></td>
<td style="width: 81%;">
	<input type="text" <?php echo $iapReadOnly; ?> tabindex="14" maxlength="10" size="10" name="cmetdate" id="cmetdate" placeholder="mm/dd/yyyy" value="<?php if ($iapCustomer['cust_met_date'] != "0000-00-00") { echo date("m/d/Y",strtotime($iapCustomer['cust_met_date'])); } ?>">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;At:&nbsp;&nbsp;
	<textarea <?php echo $iapReadOnly; ?> tabindex="15" rows="4" cols="25" name="cmetat" id="cmetat" maxlength="100"><?php echo $iapCustomer['cust_met_at']; ?></textarea>&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 6); ?> <!-- level 1, page 134, section 6 -->
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Follow Up:</span></td>
<td style="width: 81%;">
	Possible Consultant:
	<input  type="checkbox" <?php echo $iapReadOnly; ?> tabindex="16" name="cfollowcons" id="cfollowcons" value="cflwcns"
	<?php if ($iapCustomer['cust_followup_consultant'] == "Y") { echo " checked"; } ?>>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
	Possible Party:
	<input  type="checkbox" <?php echo $iapReadOnly; ?> tabindex="17" name="cfollowparty" id="cfollowparty" value="cflwpar"
	<?php if ($iapCustomer['cust_followup_party'] == "Y") { echo " checked"; } ?>>&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 134, 7); ?> <!-- level 1, page 134, section 7 -->
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapFormLabel'>Notes:</span></td>
<td style="width: 81%;">
	<textarea <?php echo $iapReadOnly; ?> tabindex="18" rows="10" cols="50" name="cnotes" id="cnotes" maxlength="500"><?php echo $iapCustomer['cust_notes']; ?></textarea> 

</td></tr>

<tr style='line-height:200%;'><td colspan='2'></td><td style="width: 81%;"></td></tr>

<tr style='line-height:200%;'><td colspan='2'></td><td style="width: 81%;">
<?php
	if ($iapReadOnly != "readonly") {
		echo "<input type='submit' tabindex='20' name='csubmit' value='Submit'>";
	}
?>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>
<tr><td colspan="3">&nbsp;</td></tr>
</tbody></table>

<?php
	if (!empty($iapSales) 
	and count($iapSales) > 0) {
		$iapSDsply = "block";
	} else {
		$iapSDsply = "none";
	}
?>

<table id=csalestbl style='display:<?php echo $iapSDsply; ?>; width:100%;'>
	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'></td>
	<td style='width:40%;'></td>
	<td style='width:5%;'></td>
	<td style='width:10%;'></td>
	<td style='width:12%;'></td>
	<td style='width:12%;'></td>
	<td style='width:8%;'></td>
	</tr>

	<tr>
	<td colspan='8' id='csaletbltitle'><span style='font-size:110%; text-decoration:underline;'>Items Purchased By This Customer</span>
	</td></tr>

	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%; text-align:left;'><span style='text-decoration:underline;'>Date</span>
		<?php echo IAP_Do_Help(1, 134, 3); // level 1, page 134, section 3 ?>
	</td>
	<td style='width:40%; text-align:center;'><span style='text-decoration:underline;'>Where Purchased</span></td>
	<td colspan="5"></td>
	</tr>

	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%; text-align:center;'><span style='text-decoration:underline;'>Item Code</span>
		<?php echo IAP_Do_Help(1, 134, 4); // level 1, page 134, section 4 ?>
	</td>
	<td style='width:40%; text-align:center;'><span style='text-decoration:underline;'>Description</span></td>
	<td style='width:5%; text-align:center;'><span style='text-decoration:underline;'>Qty</span></td>
	<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Price</span></td>
	<td style='width:12%; text-align:center;'><span style='text-decoration:underline;'>Total Price</span></td>
	<td style='width:12%; text-align:center;'><span style='text-decoration:underline;'>Total Cost</span></td>
	<td style='width:8%;'></td>
	</tr>

<?php
	$sSubtotCost = 0;
	$sSubtotPrice = 0;
	$sTotPrice = 0;
	$sTotCost = 0;
	$sLastKey = "";

	if (!empty($iapSales)) {
		foreach($iapSales as $iapS) {
			$iapSKey = date("m/d/Y", strtotime($iapS['sale_date'])).$iapS['pe_sponsor'].strval($iapS['saledet_sid']);
			if ($iapSKey != $sLastKey) {
				if ($sLastKey != "") {
?>

	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'></td>
	<td style='width:40%;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total This Sale</td>
	<td style='width:5%;'></td>
	<td style='width:10%;'></td>
	<td style='width:12%; text-align:right'><span id=stotprice></span><?php echo number_format($sSubtotPrice, 2, '.', ','); ?></span></td>
	<td style='width:12%; text-align:right'><span id=stotcost></span><?php echo number_format($sSubtotCost, 2, '.', ','); ?></span></td>
	<td style='width:8%;'></td>
	</tr>

<?php
					$sSubtotCost = 0;
					$sSubtotPrice = 0;
				}

/*
E = Event - need an event address (selectable or add)
P = Party - need a party number (selectable or add)
I = Individual - need name&address

F = Facebook (SALE DOES NOT REDUCE INVENTORY)
O = Other (SALE DOES NOT REDUCE INVENTORY)
W = Website - need number from online (SALE DOES NOT REDUCE INVENTORY)
X = Exchange - processed in other program 
*/
				$iapPELink = "N";	
				switch($iapS['pe_type']) {
					case "E":
						$iapSType = "Event";
						$iapPELink = "Y";
						break;
					case "P":
						$iapSType = "Party ".$iapS['pe_party_no'];
						$iapPELink = "Y";
						break;
					case "I":
						$iapSType = "Sale To Individual";
						if (!empty($iapS['pe_party_no'])) {
							$iapPELink = "Y";
							$iapSType = $iapSType." For Party ".$iapS['pe_party_no'];
						}
						break;
					case "F":
						$iapSType = "Facebook Party - Party Number ".$iapS['pe_party_no'];
						$iapPELink = "Y";
						break;
					case "W":
						$iapSType = "Sale From Website";
						if (!empty($iapS['pe_party_no'])) {
							$iapPELink = "Y";
							$iapSType = $iapSType." For Party ".$iapS['pe_party_no'];
						}
						break;
					case "X":
						$iapSType = "Exchange";
						break;
					case "O":
						$iapSType = "Other Type Sale";
						break;
					default:
						$iapSType = "Unknown Type";
				}
?>
	<tr><td colspan='8'>&nbsp;</td></tr>
	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'>
<?php
				if ($iapPELink == "Y") {
					echo "<a href='?page_id=356&action=selected&peid=".$iapS['sale_peid']."'>";
				}
				echo date("m/d/Y", strtotime($iapS['sale_date']));
				if ($iapPELink == "Y") {
					echo "</a>";
				}
?>
	</td>
	<td style='width:40%;'><?php echo $iapS['pe_sponsor']; ?></td>
	<td colspan="5"><?php echo $iapSType; ?></a></td>
	</tr>
<?php
				$sLastKey = $iapSKey;
			}
?>
	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'>&nbsp;&nbsp;<a href='?page_id=141&action=selected&item=<?php echo $iapS['cat_item_code']; ?>'><?php echo $iapS['cat_item_code']; ?></a></td>
	<td style='width:40%; padding-left:10px;'><?php echo $iapS['cat_description']; ?></a></td>
	<td style='width:5%; text-align:right;'><?php echo number_format($iapS['saledet_quantity'], 0, '.', ','); ?></td>
	<td style='width:10%; text-align:right;'><?php echo number_format($iapS['saledet_price'], 2, '.', ','); ?></td>
	<td style='width:12%; text-align:right;'><?php echo number_format($iapS['saledet_total_price'], 2, '.', ','); ?></td>
	<td style='width:12%; text-align:right;'><?php echo number_format($iapS['saledet_total_cost'], 2, '.', ','); ?></td>
	<td style='width:8%;'></td>
	</tr>

<?php
			$sSubtotPrice = $sSubtotPrice + $iapS['saledet_total_price'];
			$sSubtotCost = $sSubtotCost + $iapS['saledet_total_cost'];
			$sTotPrice = $sTotPrice + $iapS['saledet_total_price'];
			$sTotCost = $sTotCost + $iapS['saledet_total_cost'];
		}
?>
	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'></td>
	<td style='width:40%;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total This Sale</td>
	<td style='width:5%;'></td>
	<td style='width:10%;'></td>
	<td style='width:12%; text-align:right'><span id=stotprice></span><?php echo number_format($sSubtotPrice, 2, '.', ','); ?></span></td>
	<td style='width:12%; text-align:right'><span id=stotcost></span><?php echo number_format($sSubtotCost, 2, '.', ','); ?></span></td>
	<td style='width:8%;'></td>
	</tr>
	<tr>
	<td style='width:2%;'>&nbsp;</td>
	<td style='width:11%;'>&nbsp;</td>
	<td style='width:40%;'>&nbsp;</td>
	<td style='width:5%;'>&nbsp;</td>
	<td style='width:10%;'>&nbsp;</td>
	<td style='width:12%;'>&nbsp;</td>
	<td style='width:12%;'>&nbsp;</td>
	<td style='width:8%;'>&nbsp;</td>
	</tr>
	<tr>
	<td style='width:2%;'></td>
	<td style='width:11%;'></td>
	<td style='width:40%;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total For Customer</td>
	<td style='width:5%;'></td>
	<td style='width:10%;'></td>
	<td style='width:12%; text-align:right'><span id=stotprice></span><?php echo number_format($sTotPrice, 2, '.', ','); ?></span></td>
	<td style='width:12%; text-align:right'><span id=stotcost></span><?php echo number_format($sTotCost, 2, '.', ','); ?></span></td>
	<td style='width:8%;'></td>
	</tr>
<?php
	}
?>
</table>


<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="CUPDATETYPE" id="CUPDATETYPE" value="">
<input type="hidden" name="CCUSTNO" id="CCUSTNO" value="">
<input type="hidden" name="CSTATUS" id="CSTATUS" value="">
<input type="hidden" name="CTYPE" id="CTYPE" value="None">
<input type="hidden" name="CSELPE" id="CSELPE" value="">

</form>
</p>
</div>

<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSCustMaint.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSCustMaint.min.js");
?>

var acCustomers = [<?php echo $iapCustList; ?>];
var acEmails = [<?php echo $iapEmailList; ?>];
var acPhones = [<?php echo $iapPhoneList; ?>];
var cPList = [<?php echo $cParties; ?>];
var cEList = [<?php echo $cEvents; ?>];
</script>
