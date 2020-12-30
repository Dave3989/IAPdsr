<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>In GiftCert <br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("514") < 0) {
	return;
};

if ($_REQUEST['action'] == 'p514retA') {


	$DivSelect = "none";
	$DivShow = "block";
	
} elseif ($_REQUEST['action'] == 'p514retB') {

	$iapGiftCert = (array) IAP_Get_Savearea("IAP514GC", $_REQUEST['IAPUID']);
	if (!empty($iapGiftCert)) {
		IAP_Remove_Savearea("IAP514GC", $_REQUEST['IAPUID']);
	}

	$iapCust = $iapGiftCert['gcCustomer'];
	$iapPE = $iapGiftCert['gcPE'];





	$iapOrigAction = $_REQUEST['action'];

	$iapPageError = 0;
	$iapChanged = "N";

    if (isset($_REQUEST['gbuyer'])) {
        $iapRet = LHC_Validate_Nonblank($iapGiftCert['gc_purchaser'], $_REQUEST['gbuyer']);
        if ($iapRet['Changed'] == "Y") {
            $iapGiftCert['gc_purchaser'] = $iapRet['Value'];
            $iapChanged = "Y";
			$iapGiftCert['gc_purchaser_custno'] = $_REQUEST['GBUYERNO'];
        }
        if ($iapRet['Error'] == "Y") {
            echo "<span class=iapError>Purchaser Name cannot be blank!</span><br>";
            $iapPageError = 1;
        }
    } elseif (empty($iapGiftCert['gc_purchaser'])) {
        echo "<span class=iapError>Purchaser Name cannot be blank!</span><br>";
		$iapPageError = 1;
	}

    if (isset($_REQUEST['gfor'])) {
        $iapRet = LHC_Validate_Nonblank($iapGiftCert['gc_for'], $_REQUEST['gfor']);
        if ($iapRet['Changed'] == "Y") {
            $iapGiftCert['gc_for'] = $iapRet['Value'];
            $iapChanged = "Y";
            $iapGiftCert['gc_for_custno'] = $_REQUEST['GFORNO'];
        }
        if ($iapRet['Error'] == "Y") {
            echo "<span class=iapError>Customer Name cannot be blank!</span><br>";
            $iapPageError = 1;
        }
    } elseif (empty($iapGiftCert['gc_for'])) {
        echo "<span class=iapError>Purchased For Name cannot be blank!</span><br>";
		$iapPageError = 1;
	}

	$iapBDChg = "N";
	if (isset($_REQUEST['gbirth'])) {
		if (empty($_REQUEST['gbirth'])) {
			if (!empty($iapGiftCert['cust_birthday'])) {
				$iapGiftCert['cust_birthday'] = "";			
            	$iapBDChg = "Y";
            	$iapChanged = "Y";
            }
		} else {
			$iapBD = str_replace("-", "/", $_REQUEST['gbirth']."/1960");
			$iapRet = LHC_Validate_Date($iapGiftCert['cust_birthday'], $iapBD, "Y");
	        if ($iapRet['Changed'] == "Y") {
	            $iapGiftCert['cust_birthday'] = $_REQUEST['gbirth'];
	            $iapBDChg = "Y";
	            $iapChanged = "Y";
	        }
	        if ($iapRet['Error'] == "1") {
	            $iapGiftCert['cust_birthday'] = "";
	            $iapBDChg = "Y";
	            $iapChanged = "Y";
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Birthday is incorrectly formatted. Must be mm/dd</span><br>";
				$iapPageError = 1;
			}
		}
	}
    if (isset($_REQUEST['gstreet'])
	and	$iapGiftCert['cust_street'] != $_REQUEST['gstreet']) {
		$iapGiftCert['cust_street'] = $_REQUEST['gstreet'];
        $iapChanged = "Y";
	}
    if (isset($_REQUEST['gcity'])
    and $iapGiftCert['cust_city'] != $_REQUEST['gcity']) {
		$iapGiftCert['cust_city'] = $_REQUEST['gcity'];
        $iapChanged = "Y";
	}
    if (isset($_REQUEST['gstate'])
    and $iapGiftCert['cust_state'] != $_REQUEST['gstate']) {
		$iapGiftCert['cust_state'] = $_REQUEST['gstate'];
        $iapChanged = "Y";
	}
    if (isset($_REQUEST['gzip'])
    and $iapGiftCert['cust_zip'] != $_REQUEST['gzip']) {
		$iapGiftCert['cust_zip'] = $_REQUEST['gzip'];
        $iapChanged = "Y";
	}
    if (isset($_REQUEST['gemail'])
    and $iapGiftCert['cust_email'] != $_REQUEST['gemail']) {
		$iapGiftCert['cust_email'] = $_REQUEST['gemail'];
        $iapChanged = "Y";
	}
    if (isset($_REQUEST['gphone'])) {
        $iapRet = LHC_Validate_Phone($iapGiftCert['cust_phone'], $_REQUEST['gphone']);
        if ($iapRet['Changed'] == "Y") {
            $iapGiftCert['cust_cell_phone'] = $iapRet['Value'];
            $iapChanged = "Y";
        }
        if ($iapRet['Error'] == "1"
        and !(empty($iapGiftCert['cust_cell_phone']))) {
            $iapGiftCert['cust_cell_phone'] = "";
            $iapChanged = "Y";
        } elseif ($iapRet['Error'] == "2") {
	        echo "<span class=iapError>Cell Phone improperly formatted!</span><br>";
			$iapPageError = 1;
		}
    }

	if ($iapPageError == 0
	and $iapChanged == "Y") {
		$iapGiftCert['cust_company'] = $_REQUEST['CoId'];





		$iapCN = IAP_Split_Name($iapCust['cust_name']);
		$iapCust['cust_last_name'] = trim($iapCN['lname']." ".$iapCN['suffix']);
		$iapCust['cust_first_name'] = $iapCN['fname'];







		if ($iapBDChg == "Y") {
			if (!empty($iapGiftCert['cust_birthday_event'])) {
				$iapRet = IAP_Delete_Row(array("event_id" => $iapGiftCert['cust_birthday_event']), "iapcal");
				if ($iapRet < 0) {
		        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar event row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
				}
				$iapRet = IAP_Delete_Row(array("cr_id" => $iapGiftCert['cust_birthday_event']), "iapcrep");
				if ($iapRet < 0) {
		        	echo "<span style='color:red;'><strong>INTERNAL ERROR: Error removing calendar repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
				}
			}
			if (!empty($iapGiftCert['cust_birthday'])) {
				$eeEvents = IAP_Build_New_Row(array("table" => "iapcal"));
				if ($eeEvents < 0) {
			        echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: I cannot build a new event because of a database error(1). [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			        exit;
				}
				$eeEvent = (array) $eeEvents[0];
		   	    $eeEvent['event_account'] = 0;		// System Administrator's Calendar
			    $eeEvent['event_title'] = $iapGiftCert['gc_purchaser']."'s Birthday";
			    $dsc = $eeEvent['event_title']."\n";
			    if (!empty($iapGiftCert['cust_street'])) {
					$dsc = $dsc.$iapGiftCert['cust_street']."\n";
				}
			    if (!empty($iapGiftCert['cust_city'])
			    or  !empty($iapGiftCert['cust_state'])
			    or  !empty($iapGiftCert['cust_zip'])) {
					$dsc = $dsc.$iapGiftCert['cust_city'].",".$iapGiftCert['cust_state']." ".$iapGiftCert['cust_zip']."\n";
				}
			    if (!empty($iapGiftCert['cust_home_phone'])) {
					$dsc = $dsc."Home Phone: ".$iapGiftCert['cust_home_phone']."\n";
				}
			    if (!empty($iapGiftCert['cust_cell_phone'])) {
					$dsc = $dsc."Cell Phone: ".$iapGiftCert['cust_cell_phone']."\n";
				}
			    if (!empty($iapGiftCert['cust_email'])) {
					$dsc = $dsc."Email: ".$iapGiftCert['cust_email']."\n";
				}
			    $eeEvent['event_desc'] = $dsc;
			    $d = $iapGiftCert['cust_birthday']."/".date("Y");
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
				$eeEvent['cr_annual_option'] = "a1";					// Repeat on a particular mm/dd
				$eeEvent['cr_annual_month1A'] = date("m", $dstr);
				$eeEvent['cr_annual_dom'] = date("d", $dstr);
				$eeEvent['cr_annual_occurs'] = 1;						// Repeat every year
				$eeEvent['cr_until_date'] = "2099-12-31";				// Repeat forever
				$eeEvent['cr_until_count'] = 0;
	
				require_once(ABSPATH."Ajax/IAPCalendar/IAPWriteEvent.php");
				$eeEvent['event_id'] = FCWriteEvent($eeEvent, "Y");
		    	if ($eeEvent['event_id'] < 0) {
		        	echo "<span style='color:red;'><strong>".$eeApp." INTERNAL ERROR: Error updating repeating row. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		            exit;
		    	}
				$iapGiftCert['cust_birthday_event'] = $eeEvent['event_id'];
			}
		}

		$iapGiftCert['cust_changed'] = date("Y-m-d");
		$iapGiftCert['cust_changed_by'] = $_REQUEST['IAPUID']; 
		$iapRet = IAP_Update_Data($iapGiftCert, "prof");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapGiftCert['status'] == "NEW") {
			$iapGiftCert['cust_no'] = $iapRet;
			$iapGiftCert['status'] == "EXISTING";
			$iapU = "added";
		} else {
			$iapU = "updated";
		}
		echo "<br><span class=iapSuccess>Your profile was successfully ".$iapU.".</span><br><br>";
	}

	$iapRet = IAP_Update_Savearea("IAP514GC", $iapGiftCert, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "block";	
} else {

	if (IAP_Remove_Savearea("IAP514GC") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the catalog item savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapGC = (array) IAP_Build_New_Row(array("table" => "gftcrt"));
	$iapGiftCert = $iapGC[0];
	$iapGiftCert['gc_date_issued'] = date("m/d/Y");
	$iapGiftCert['gc_comment'] = "This Gift Certificate is to be redeemed for product from the Independant Consultant ".
								 "mentioned above ONLY. This Gift Certificate has NO monetary value.";
	$iapC = (array) IAP_Build_New_Row(array("table" => "cust"));
	$iapCust = $iapC[0];
	$iapGiftCert['gcCustomer'] = $iapCust;
	$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
	$iapPE = $iapP[0];
	$iapPE['pe_type'] = "P";
	$iapPE['pe_selector'] = "";
	$iapGiftCert['gcPE'] = $iapPE;

	$iapRet = IAP_Create_Savearea("IAP514GC", $iapGiftCert, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";

$iapCust = $iapGiftCert['gcCustomer'];
$iapPE = $iapGiftCert['gcPE'];

$iapGCList = IAP_Get_GiftCert_List();
if ($iapGCList < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve gift certificates. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
$gGCs = "";
$c = "";
if ($iapGCList != NULL) {
	foreach ($iapGCList as $iapG) {
		$iapCNm = str_replace('"', '', $iapG['cust_name']);
		$g = "Certificate Number ".strval($iapG['gc_id'])." on ".date("m/d/Y", strtotime($iapG['gc_date_issued']))." For ".$iapCNm;
		$iapGiftCerts = $iapGiftCerts.$c.'{"label": "'.$g.'", "certid": "'.strval($iapG['gc_id']).'"}';
		$c = ',';
	}
}

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

$iapGiftCert['pe_selector'] = "";
$iapPar = IAP_Get_PE_List();
if ($iapPar < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
if ($iapPar != NULL) {
	$gParties = "";
	$gEvents = "";
	$cE = "";
	$cP = "";
	foreach($iapPar as $iapP) {
		if ($iapP['pe_type'] == "P") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
			$gParties = $gParties.$cP.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'"}';
			$cP = ",";
		} elseif ($iapP['pe_type'] == "E") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." event at ".trim($iapP['pe_sponsor']);
			$gEvents = $gEvents.$cE.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'"}';
			$cE = ",";
		}
		if ($iapGiftCert['sale_peid'] == $iapP['pe_id']) {
			$iapGiftCert['pe_selector'] = $peText;
		}
	}
}

$iapReadOnly = IAP_Format_Heading("Gift Certificates");

$h = IAP_Do_Help(3, 514, 1); // level 3, page 514, section 1
if ($h != "") {
	echo $h;
}

?>

<div id='gchoose' >
<form name='gselform' action='?action=p514retA&origaction=initial' method='POST'>
<?php
	if (empty($gGiftCerts)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = 'No Gift Certificates on file. Click "Add A New Gift Certificate".';
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<span class=iapformLabel style='padding-left: 40px;'>";
	echo "<label for='gGiftCertList'>Select a gift certificate: </label>";
	echo "<input id='gGiftCertList' size='50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 514, 1);	//		Help Narative	<!-- level 1, page 514, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='gGoClicked()'>";

/*
<picture>
    <source srcset="MyImages/SmallGoGreen.png" media="(max-width: 400px)">
    <source srcset="MyImages/GoGreen.png">
    <img src="MyImages/GoGreen.png" alt="GO!" style="width:auto;"> // style="width:304px;height:228px;">
</picture>
*/

	echo "<br><span class=iapError id=gError style='display:none;'>The gift certificate was not found. Retry or click Add.</span>";

	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapformLabel style='padding-left: 50px;'>";
		echo "<input type='button' class=iapButton name='gAdd' id='gAdd' value='Add A New Gift Certificate' onclick='gAddClicked()' /></span>";
	}
	if (!empty($iapMsg)) {
		echo "<br><span class='iapformLabel iapWarning' style='padding-left:50px; text-decoration:bold;'>".$iapMsg."</span>";		
	}
?>
</form>
</div>

<div id='gdetail' style='display:<?php echo $DivShow; ?>;'>
<hr>

<p style='text-indent:50px; width:100%'>

<form name='gdetform' action='?action=p514retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">

<tr><td style='width:5%'>&nbsp;</td><td style='width:12%'>&nbsp;</td><td style="width:83%">&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Person Purchasing:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="1" size="50" maxlength="50" name="gbuyer" id="gbuyer" value="<?php echo $iapGiftCert['gc_purchaser']; ?>" autofocus>
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Person For:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="1" size="50" maxlength="50" name="gfor" id="gfor" value="<?php echo $iapGiftCert['gc_for']; ?>">
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 514, 1); ?> <!-- level 1, page 514, section 1 -->
</td></tr>

<tr><td style='width:5%'>&nbsp;</td>
<td colspan='2'>
<span id=gNoCust class=iapError style='display:none;'><br>The customer was not found. Please complete the address, email, and/or phone fields below.</span>
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr><td colspan="3"><span class='iapformLabel'>Address of Person Being Purchased For</span></td></td></tr>

<tr>
<td style='width:5%'></td>
<td style='width:12%'><span class='iapformLabel'>Street:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="2" maxlength="50" size="50" name="gstreet" id="gstreet" value="<?php echo $iapGiftCert['cust_street']; ?>">
</td></tr>
<tr>
<td style='width:5%'></td>
<td style='width:12%'><span class='iapformLabel'>City, State, Zip:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="3" maxlength="40" size="40" name="gcity" id="gcity" value="<?php echo $iapGiftCert['cust_city']; ?>">
	<input <?php echo $iapReadOnly; ?> tabindex="4" maxlength="2" size="2" name="gstate" id="gstate" value="<?php echo $iapGiftCert['cust_state']; ?>">
	<input <?php echo $iapReadOnly; ?> tabindex="5" maxlength="10" size="10" name="gzip" id="gzip" value="<?php echo $iapGiftCert['cust_zip']; ?>">
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Email:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="6" type="email" maxlength="100" size="50" name="gemail" id="gemail" value="<?php echo $iapGiftCert['cust_email']; ?>">
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Phone:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="8" maxlength="15" size="15" name="gphone" id="gphone" value="<?php echo $iapGiftCert['cust_phone']; ?>">
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Birthday:</span></td>
<td style='width:83%'>
	<input <?php echo $iapReadOnly; ?> tabindex="9" maxlength="5" size="5" name="gbirth" id="gbirth" placeholder="mm/dd" value="<?php if (!empty($iapGiftCert['cust_birthday'])) { echo date("m/d",strtotime($iapGiftCert['cust_birthday']."/1960")); } ?>">
</td></tr>

<tr><td colspan='3'>&nbsp;</td></tr>

<tr>
<td colspan='2'><span class='iapformLabel'>Purchased At:</span></td>
<td style="width:83%;">
	<input type="radio" name="gtype" id="gtypeparty" value="P" tabindex="13" onchange='gSetType("P")'
		<?php if ($iapPE['pe_type'] == "P") { echo " checked"; } ?>
	>Party&nbsp;&nbsp;&nbsp;
	<input type="radio" name="gtype" id="gtypeevent" value="E" tabindex="13" onchange='gSetType("E")'
		<?php if ($iapPE['pe_type'] == "E") { echo " checked"; } ?>
	>Event&nbsp;&nbsp;&nbsp;
	<input type="radio" name="gtype" id="gtypeother" value="O" tabindex="13" onchange='gSetType("O")'
		<?php if ($iapPE['pe_type'] != "P" and $iapPE['pe_type'] != "E") { echo " checked"; } ?>
	>Other
	<br>
</td></tr>
</table>

	<div id=gnonpediv1 style="display:none;">
<?php
if (!empty($iapGiftCert['gc_date_issued'])
and $iapGiftCert['gc_date_issued'] != "0000-00-00") {
	$d3 = date("m/d/Y", strtotime($iapGiftCert['gc_date_issued']));
} else {
	$d3 = date("m/d/Y");
}
?>
		<table>
		<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
		<tr><td colspan='2'><span class='iapFormLabel'>Date of Sale:</span></td>
			<td style="width:83%;">
				<input <?php echo $iapReadOnly; ?> tabindex="14" maxlength="15" size="15" name="gissued" id="gissued" placeholder="mm/dd/yyyy" value="<?php echo $d3; ?>" onchange='gChangedNonPE()'>
		</td></tr>
		<tr><td colspan='2'></td><td style="width:83%;">-- OR --</td></tr>
		</table>
	</div>

<table>
<tr><td colspan='2'></td><td style="width:83%;">
<span id=parError class=iapError style='display:none;'>The party was not found. Retry or click Add.</span>
<span id=pevtError class=iapError style='display:none;'>The event was not found. Retry or click Add.</span>
</td></tr>

<?php
	$p = "none";
	if ($iapGiftCert['pe_type'] != "E") {
		$ps = " value='".$iapGiftCert['pe_selector']."'";
		$p = "block";
	}
	$e = "none";
	if ($iapGiftCert['pe_type'] == "E") {
		$es = " value='".$iapGiftCert['pe_selector']."'";
		$e = "block";
	}
	$s = $iapPE['pe_sponsor'];

	echo "<tr><td colspan='2'><label class='iapFormLabel' id='gpelabel' style='vertical-align:top;'>Select a Party:</label></td>";
	echo "<td style='width:83%;'>";
	echo "<input ".$iapReadOnly." style='display:".$p.";' type='text' tabindex='15' name='gpeparty' id='gpeparty'  size='50'".$ps.">";
	echo "<input ".$iapReadOnly." style='display:".$e.";' type='text' tabindex='15' name='gpeevent' id='gpeevent'  size='50'".$es.">";
	echo "</td></tr>";
	echo "<tr><td colspan='2'></td><td style='width:83%;'>";
	if ($iapReadOnly != "readonly") {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class=iapButton name='gAddPE' id='gAddPE' type='button' onclick='gAddPE()'>New Party</button>";
	}
	echo "</td></tr>";
?>

</table>

	<?php
		if ($iapGiftCert['newpe'] == "Y") {
			$p = "block;";
		} else {
			$p = "none;";
		}
	?>
	<div id=iapNewPE style="display:<?php echo $p; ?>">
		<table>
		<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
		<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span class='iapFormLabel'>Please provide the following information regarding this new <span id='gnewpecmt1'>party</span>. This new <span id='gnewpecmt2'>party</span> will need to be editted later to enter any additional information.</span>
		</td></tr>
		<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='gnewpedatelbl'>Date:</label></td>
			<td style="width:83%;"><input tabindex="16" type="text" name="gnewpedate" id="gnewpedate" size="15" placeholder="mm/dd/yyyy" <?php echo date("Y-m-d", strtotime($iapGiftCert['gc_date_issued'])); ?> onchange="gnewpeDateChg();">
		</td></tr>
		<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='gnewpenamelbl'>Hostess:</label</td>
			<td style="width:83%;"><input tabindex="17" type="text" name="gnewpename" id="gnewpename" size="50">
		</td></tr>
		<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Street:</span></td>
			<td style="width:83%;"><input tabindex="18" type="text" name="gnewpestrt" id="gnewpestrt" size="50">
		</td></tr>
		<tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>City, State, Zip:</td>
			<td style="width:83%;">
				<input tabindex="19" type="text" name="gnewpecity" id="gnewpecity" size="30">
				<input tabindex="20" type="text" name="gnewpestate" id="gnewpestate" size="3">
				<input tabindex="21" type="text" name="gnewpezip" id="gnewpezip" size="10">
		</td></tr>
		</table>
	</div>

	<div id=gnonpediv2 style="display:none;">
		<table>
		<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
		<tr><td colspan='2'><label class=iapFormLabel>Mileage:</label></td>
			<td style="width:83%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='23' maxlength="7" size="7" name="gmileage" id="gmileage" step="0.01" value="<?php echo $iapGiftCert['gc_mileage']; ?>" />
		</td></tr>
		<tr><td colspan='2'><label class=iapFormLabel>Other Expense:</label></td>
			<td style="width:83%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='24' maxlength="7" size="7" name="gotherexp" id="gotherexp" step="0.01" value="<?php echo $iapGiftCert['gc_other_exp']; ?>"" />
		</td></tr>
		<tr><td colspan='2'><label class=iapFormLabel>Explain Other Expenses:</label></td>
			<td style="width:83%;">
				<textarea name='gexpexplain' id='gexpexplain' tabindex="25" cols='50' rows='4' wrap='soft' <?php echo $iapReadOnly; ?>><?php echo $iapGiftCert['gc_exp_explained']; ?></textarea>
		</td></tr>
		</table>
	</div>

<table>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

<tr><td colspan='2'><span class=iapformLabel>Face Amount:</span>
	</td><td style="width:83%;">
		<input <?php echo $iapReadOnly; ?>  style="text-align:right;" maxlength="15" size="15" name="gamount" id="gamount" value="<?php echo number_format($iapGiftCert['gc_amount'], 2, '.', ''); ?>"> 	
</td></tr>

<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

<tr><td colspan='2'><span class=iapformLabel>Payment Method:</span></td>
	<td style="width:83%;">
		<input <?php echo $iapReadOnly; ?> tabindex='37' name="gpayment" id="gpaycash" value="gpaycash" type="radio" onchange="gPayChg();">Cash
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input <?php echo $iapReadOnly; ?> name="gpayment" id="gpaycredit" value="gpaycredit" type="radio" onchange="gPayChg();">Debit/Credit Card 
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input <?php echo $iapReadOnly; ?> name="gpayment" id="gpaycheck" value="gpaycheck" type="radio" onchange="gPayChg();">Check 
		&nbsp;&nbsp;Check Number:&nbsp;
		<input tabindex='38' maxlength='10' size='10' name='gpaychkno' id='gpaychkno' value='<?php echo $iapGiftCert['gc_check_number']; ?>' />

</td></tr>

<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

<tr><td colspan='2'><label class=iapformLabel id=gcommlbl>Comments To Print:</label></td>
	<td style="width:83%;"><textarea name='gcomment' id='gcomment' tabindex='41' cols='50' rows='5' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapGiftCert['gc_comment']; ?></textarea>
</td></tr>

<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

<tr><td colspan='2'><label class=iapformLabel id=gcommlbl>Your Notes:</label></td>
	<td style="width:83%;"><textarea name='gnotes' id='gnotes' tabindex='41' cols='50' rows='5' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapGiftCert['gc_notes']; ?></textarea>
</td></tr>

<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

<?php
echo "<tr><td colspan='3' style='text-align:center;'>";
if ($iapReadOnly != "readonly") {
	echo "<button class=iapButton name='gsubmit' id='gsubmit' tabindex='50' onclick='gSendForm(); return(true);'>Submit</button>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<button class=iapButton name='gclear' id='gclear' tabindex='51' onclick='gClearForm(); return(false);'>Clear</button>";
}
echo "</td></tr>";
?>

<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>
<tr><td colspan='2'></td><td style="width:83%;">&nbsp;</td></tr>

</table>

<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="GUPDATETYPE" id="GUPDATETYPE" value="">
<input type="hidden" name="GBUYERNO" id="GBUYERNO" value="">
<input type="hidden" name="GFORNO" id="GFORNO" value="">
<input type="hidden" name="GSTATUS" id="GSTATUS" value="">
<input type="hidden" name="GUPDATETYPE" id="GUPDATETYPE" value="">
<input type="hidden" name="GNAMEID" id="GNAMEID" value="">
<input type="hidden" name="GSTATUS" id="GSTATUS" value="">
<input type="hidden" name="GDATE" id="GDATE" value="">
<input type="hidden" name="GTYPE" id="GTYPE" value="Party">
<input type="hidden" name="GTAXOVERRIDE" id="GTAXOVERRIDE" value="<?php echo $iapGiftCert['sale_tax_override']; ?>">
<input type="hidden" name="GNEWCUST" id="GNEWCUST" value="">
<input type="hidden" name="GNEWPE" id="GNEWPE" value="">
<input type="hidden" name="GPEID" id="GPEID" value="">
<input type="hidden" name="GNEWITEMINFO" id="GNEWITEMINFO" value="">
<input type="hidden" name="GTHISITEMSTATUS" id="GTHISITEMSTATUS" value="">

</form>
</p>
</div>


<script type="text/javascript">

$(function() {

	var agGiftCerts = [<?php echo $iapGiftCerts; ?>];
	$("#gGiftCertList").autocomplete({
		source: agGiftCerts,
		minLength: 0,
		change: function(gEvent, gId) { 
						ggetGiftCert("gId");
					}

	});
	var acCustomers = [<?php echo $iapCustList; ?>];
	$("#gbuyer").autocomplete({
		source: acCustomers,
		minLength: 0,
		change: function(gEvent, cBuy) { 
						ggetBuyer();
					}
	});
	$("#gfor").autocomplete({
		source: acCustomers,
		minLength: 0,
		change: function(gEvent, gFor) { 
						ggetFor();
					}
	});

	var gPList = [<?php echo $gParties; ?>];
	$("#gpeparty").autocomplete({
		source: gPList,
		minLength: 0,
		change: function(gEvent, gParty) {
						gSelectP();
					}
	});

	var gEList = [<?php echo $gEvents; ?>];
	$("#gpeevent").autocomplete({
		source: gEList,
		minLength: 0,
		change: function(gEvent, gEvt) {
						gSelectE();
					}
	});

});

function gGoClicked() {
	
}

function ggetGiftCert(gcId) {
	var typeFld = "G#";
	iapPrepCall("/Ajax/iapGetDB", typeFld, gcId, gProcGftCrt);
	document.getElementById("gGiftCertList").value="";

	document.getElementById("gError").style.display = "none";
	document.getElementById("gdetail").style.display="block";
	document.getElementById("gdate").focus(); 											
	return;
}

function gProcGiftCrt(gGiftCrt) {
	if (gGiftCrt == 0) {
		
	} else {
		
	}
}

function gAddClicked() {
	document.getElementById("gbuyer").value = "";
	gblankFor();
	
	document.getElementById("GSTATUS").value = "NEW";
//	document.getElementById("gchoose").style.display="none"; 
	document.getElementById("gdetail").style.display="block";
	document.getElementById("gbuyer").focus();
}

function gblankGiftCert() {
//	document.getElementById("gissued").value = 
}


// ---------------------------------------------------------------------------------
//
// Buyer/For Functions
//
// ---------------------------------------------------------------------------------
function ggetBuyer(gBuyer) {
	gNm = document.getElementById("gbuyer").value;
	if (gNm == "") {
		document.getElementById("GBUYERNO").value = 0;
		document.getElementById("gfor").focus();
		return;
	}
	var typeFld = "CN";
	iapPrepCall("/Ajax/iapGetDB", typeFld, gNm, gProcBuyer);
	document.getElementById("gfor").focus();
	return;
}

function gProcBuyer(gBuyer) {
	if (gBuyer == 0) {
		document.getElementById("GBUYERNO").value = 0;
	} else {
		document.getElementById("GBUYERNO").value = cBuyer.cust_no;
		document.getElementById("gbuyer").value = cBuyer.cust_name;
	}	
}

function ggetFor(gFor) {
	gNm = document.getElementById("gfor").value;
	if (gNm == "") {
		gblankFor();
		document.getElementById("GFORNO").value = 0;
		document.getElementById("gNoCust").style.display="block"; 
		document.getElementById("gfor").focus();
		return;
	}
	var typeFld = "CN";
	iapPrepCall("/Ajax/iapGetDB", typeFld, gNm, gProgFor);
	document.getElementById("gNoCust").style.display = "none";
	document.getElementById("gstreet").focus();
	return;
}

function gProgFor(gFor) {
	if (gFor == 0) {
		gblankFor();
		document.getElementById("gNoCust").style.display="block"; 
	} else {
		document.getElementById("GSTATUS").value = "EXISTING";
		document.getElementById("gfor").value = gFor.cust_name;
		document.getElementById("gstreet").value = gFor.cust_street;
		document.getElementById("gcity").value = gFor.cust_city;
		document.getElementById("gstate").value = gFor.cust_state;
		document.getElementById("gzip").value = gFor.cust_zip;
		document.getElementById("gemail").value = gFor.cust_email;
		if (empty(gFor.cust_phone)) {
			document.getElementById("gphone").value = "";
		} else {
			document.getElementById("gphone").value = gFor.cust_phone;
		}
		if (empty(gFor.cust_birthday)) {
			document.getElementById("gbirth").value = "";
		} else {
			document.getElementById("gbirth").value = moment(gFor.cust_birthday).format("MM/DD");			
		}
		document.getElementById("GFORNO").value = gFor.cust_no;
	}
	return true;
}

function gblankFor() {
	document.getElementById("gfor").value = "";
	document.getElementById("gfor").focus();
	document.getElementById("gbirth").value = "";
	document.getElementById("gstreet").value = "";
	document.getElementById("gcity").value = "";
	document.getElementById("gstate").value = "";
	document.getElementById("gzip").value = "";
	document.getElementById("gphone").value = "";
	document.getElementById("gemail").value = "";
	document.getElementById("gbirth").value = "";
	document.getElementById("GFORNO").value = 0;
}

// ---------------------------------------------------------------------------------
//
// Type Functions
//
// ---------------------------------------------------------------------------------

function gSetType(gTypeChosen) {
	switch(gTypeChosen) {
		case "P":
		 	document.getElementById("gnewpenamelbl").innerHTML = "Hostess:";
		 	document.getElementById("GTYPE").value = "Party";
		 	document.getElementById("gAddPE").innerHTML = "New Party";
		 	document.getElementById("gnewpecmt1").innerHTML = "party";
		 	document.getElementById("gnewpecmt2").innerHTML = "party";
			gTurnPOn();
			break;
		case "E":
		 	document.getElementById("gnewpenamelbl").innerHTML = "Sponsor:";
		 	document.getElementById("GTYPE").value = "Event";
		 	document.getElementById("gAddPE").innerHTML = "New Event";
		 	document.getElementById("gnewpecmt1").innerHTML = "event";
		 	document.getElementById("gnewpecmt2").innerHTML = "event";
			gTurnEOn();
			break;
		case "O":
		 	document.getElementById("gAddPE").innerHTML = " ";
		 	document.getElementById("GTYPE").value = "Other";
			gTurnPEOff();
			break;
	}
}

function gTurnPOn() {
	gClearNewPE();
	document.getElementById("iapNewPE").style.display="none";
	document.getElementById("gnonpediv1").style.display="none";
	document.getElementById("gnonpediv2").style.display="none";
	document.getElementById("gpelabel").innerHTML = "Select a Party: ";
	document.getElementById("GDATE").value = document.getElementById("gpeparty").value;
 	document.getElementById("gpeevent").style.display = "none";
 	document.getElementById("gpeparty").style.display = "block";
}

function gTurnEOn() {
	gClearNewPE();
	document.getElementById("iapNewPE").style.display="none";
	document.getElementById("gnonpediv1").style.display="none";
	document.getElementById("gnonpediv2").style.display="none";
	document.getElementById("gpelabel").innerHTML = "Select an Event: ";
	document.getElementById("GDATE").value = document.getElementById("gpeevent").value;
 	document.getElementById("gpeparty").style.display = "none";
 	document.getElementById("gpeevent").style.display = "block";
}

function gTurnPEOff() {
	gClearNewPE();
	document.getElementById("iapNewPE").style.display="none";
 	document.getElementById("gnonpediv1").style.display="block";
	document.getElementById("gnonpediv2").style.display="block";
	document.getElementById("gpelabel").innerHTML = "Select a Party: ";
	document.getElementById("GDATE").value = document.getElementById("gissued").value;
 	document.getElementById("gpeevent").style.display = "none";
 	document.getElementById("gpeparty").style.display = "block";
}


// ---------------------------------------------------------------------------------
//
// Party/Event Functions
//
// ---------------------------------------------------------------------------------

function gClearNewPE() {
	document.getElementById("iapNewPE").style.display="none";
	document.getElementById("gnewpename").value = "";
	document.getElementById("gnewpedate").value = "";
	document.getElementById("gnewpestrt").value = "";
	document.getElementById("gnewpecity").value = "";
	document.getElementById("gnewpestate").value = "";
	document.getElementById("gnewpezip").value = "";
}

function gAddPE() {
	gClearNewPE();
	document.getElementById("GNEWPE").value = "Y";
	document.getElementById("GPEID").value = "";
	document.getElementById("iapNewPE").style.display="block";
	document.getElementById("gnewpedate").focus();
}

function gSelectP() {
//	"Party ".$iapP['pe_party_no']." on ".date("m/d/Y", strtotime($iapP['pe_date']))." for ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);

	var gParty = document.getElementById("gpeparty").value;
	var gPs = gParty.split(" ");
	document.getElementById("GDATE").value = gPs[0];
	document.getElementById("GNEWPE").value = "N";
	document.getElementById("gnonpediv2").style.display="none";
	return;
}

function gSelectE() {
//	"Event on ".date("m/d/Y", strtotime($iapP['pe_date']))." at ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))."event at ".trim($iapP['pe_sponsor']);

	var gEvent = document.getElementById("gpeevent").value;
	var gEs = gEvent.split(" ");
	document.getElementById("GDATE").value = gEs[0];
	document.getElementById("GNEWPE").value = "N";
	document.getElementById("gnonpediv2").style.display="none";
	return;
}

function gNewPEDateChg() {
	var gNewPEDate = document.getElementById("gnewpedate").value;
	if (gNewPEDate.trim() == "") {
		return;
	}
	document.getElementById("GDATE").value = gNewPEDate;
	return;
}

function gChangedNonPE() {
	var gNonPEDate = document.getElementById("gsaledate").value;
	if (gNonPEDate.trim() == "") {
		return;
	}
	gClearNewPE();
	document.getElementById("GDATE").value = gNonPEDate;
	document.getElementById("GPEID").value = "";
	return;
}


// ---------------------------------------------------------------------------------
//
// Payment Fields Change Function
//
// ---------------------------------------------------------------------------------

function gPayChg() {
/*
	if (document.getElementById("gpaycash").checked == true) {
		document.getElementById("gccexp").value = 0;
	} else if (document.getElementById("gpaycredit").checked == true) {
		sCCChg();
	}
*/
}


// -----------------------------------------------------------------------------------
//
// Form Functions
//
// -----------------------------------------------------------------------------------

function sClearForm() {
	document.getElementById("gbuyer").value = "";
	document.getElementById("gbuyer").readOnly = false;
	sClearNewCust();
	document.getElementById("gtypeparty").checked = true;
	document.getElementById("gpeparty").value = "";
	document.getElementById("gpeevent").value = "";
	sClearNewPE();
	document.getElementById("gsaledate").value = "";
	document.getElementById("gpaycash").checked = true;
	document.getElementById('spaychkno').value = "";
	document.getElementById("gcomment").value = "";
	document.getElementById("gnetsale").value = "";
	document.getElementById("gtotalsale").value = "";
	document.getElementById("gprtrec").style.display="none";
	document.getElementById("GDATE").value = "";
	document.getElementById("GTAXOVERRIDE").value = "N";
	document.getElementById("GTYPE").value = "Party";
	document.getElementById("GNEWCUST").value = "N";
	document.getElementById("GPEID").value = "";
	document.getElementById("GNEWPE").value = "N";
	document.getElementById("GNEWITEMINFO").value = "";
	document.getElementById("GTHISITEMSTATUS").value = "";
	return(false);
}

function sSendForm() {
/*
	var sData = "";
	var sTable = document.getElementById('iapSold');
	var sFirst = "Y";
	for (var r = 1, n = sTable.rows.length; r < n; r++) {
    	if (sFirst == "Y") {
			sFirst = "N";
		} else {
			sData = sData + "|";
		}
	    for (var c = 2, m = sTable.rows[r].cells.length; c < m; c++) {
			sData = sData + sTable.rows[r].cells[c].innerHTML + "~";
	    }
	}
	document.getElementById("IAPDATA").value = sData;
*/
	return(true);
}


// -----------------------------------------------------------------------------------
//
// Error Functions
//
// -----------------------------------------------------------------------------------

function sGenerateError(sErrMsg) {

	if (sErrMsg == "<RESET>") {
		document.getElementById("gitemerror").innerHTML = " ";
		return;
	}
	var sExistingMsg = document.getElementById("gitemerror").innerHTML;
	var sBreak = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	document.getElementById("gitemerror").innerHTML = sExistingMsg + sBreak + sErrMsg;
}

</script>