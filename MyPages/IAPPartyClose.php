<?php

function iap_do_pclose($iapPClose) {

	$iapPClose['TotalSales'] = $iapPClose['pc_customer_sales'];
	if ($iapPClose['pc_add_hostess'] == "Y") {
		$iapPClose['TotalSales'] = $iapPClose['TotalSales'] + $iapPClose['pc_hostess_purchases'];
	}
	$iapPClose['pc_award_amount'] = $iapPClose['TotalSales'] * $iapPClose['pc_award_percentage'];

	if ($iapPClose['PrevSales'] == $iapPClose['TotalSales']) {
		$iapPClose['PrevEqNow'] = "Y";
	} else {
		$iapPClose['PrevEqNow'] = "N";
	}
	$iapPClose['PrevSales'] = $iapPClose['TotalSales'];
///////
	$iapPClose['PrevEqNow'] == "Y";
//////

	$iapPClose['pc_changed'] = date("Y-m-d");
	$iapPClose['pc_changed_by'] = $_REQUEST['IAPUID']; 
	$iapRet = IAP_Update_Data($iapPClose, "parcl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR updating party close record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapPClose['status'] = "EXISTING";

	return($iapPClose);
}


$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Party Close with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("482") < 0) {
	return;
};

if ($_REQUEST['action'] == 'selected') {

	IAP_Remove_Savearea("IAP482PC", $_REQUEST['IAPUID']);
/* --------- Nothing should enter here. Code from IAPPartyEvents.php
	if (!empty($_REQUEST['party'])) {
		$pekey = $_REQUEST['party'];
	} elseif (!empty($_REQUEST['peid'])) {
		$pekey = $_REQUEST['peid'];
	} else {
		echo "<span class=iapError>IAP INTERNAL ERROR: Nothing passed. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;		
	}
	$iapParty = IAP_Get_PartyEvent_By_Id($pekey);
	if ($iapParty < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapParty['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapOrigAction = $_REQUEST['action'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key IAP482.<br>";
	}

	$iapRet = IAP_Create_Savearea("IAP482PC", $iapParty, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//	$DivSelect = "none";
	$DivShow = "block";
*/
} elseif ($_REQUEST['action'] == 'p482retA') {

	if ($_REQUEST['PEIDChosen'] != 0) {
		$iapParty = IAP_Get_PartyEvent_By_Id($_REQUEST['PEIDChosen']);
		if ($iapParty < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected party.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapParty['status'] = "EXISTING";
		$iapPClose = IAP_Get_Party_Closes($_REQUEST['PEIDChosen']);
		if ($iapPClose < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive close record for the selected party.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapPClose['status'] == "NEW") {
			$iapPClose['pc_company'] = $_REQUEST['CoId'];
			$iapPClose['pc_pe_id'] = $iapParty['pe_id'];
			$iapPClose['pc_hostess'] = $iapParty['pe_party_hostess'];
		}
	}

	$iapSales = IAP_Get_Sale_By_PE($iapParty['pe_id']);
	if ($iapSales < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales for party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapHostessSales = 0;
	$iapOtherSales = 0;
	if (count($iapSales) > 0) {
		foreach($iapSales as $iapS) {
			if ($iapS['sale_customer'] == $iapPClose['pc_hostess']) {
				$iapHostessSales = $iapHostessSales + $iapS['sale_net'];
			} else {
				$iapOtherSales = $iapOtherSales + $iapS['sale_net'];
			}
		}
	}
	if ($iapPClose['pc_hostess_purchases'] == $iapHostessSales
	and $iapPClose['pc_customer_sales'] == $iapOtherSales) {
		$iapPClose['PrevEqNow'] = "Y";		
	} else {
		$iapPClose['pc_hostess_purchases'] = $iapHostessSales;
		$iapPClose['pc_customer_sales'] = $iapOtherSales;
		$iapPClose['PrevEqNow'] = "N";		
		if ($iapPClose['status'] != "NEW") {
			$iapPClose = iap_do_pclose($iapPClose);
		}
	}

	$iapRet = IAP_Create_Savearea("IAP482PC", $iapPClose, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}

	$iapCloseOK = "N";
	$DivShow = "block";

} elseif ($_REQUEST['action'] == 'p482retB') {

// get party

	$iapPClose = IAP_Get_Savearea("IAP482PC", $_REQUEST['IAPUID']);
	if (empty($iapPClose)) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}

	$iapParty = IAP_Get_PartyEvent_By_Id($iapPClose['pc_pe_id']);
	if ($iapParty < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected party.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($_REQUEST['peclear'] == 'Clear Form') {
		$iapPClose['pc_close_date'] = "0000-00-00";
		$iapPClose['pc_customer_sales'] = 0;
		$iapPClose['pc_hostess_purchases'] = 0;
		$iapPClose['pc_add_hostess'] = "N";
		$iapPClose['pc_award_percentage'] = 0;
		$iapPClose['pc_award_amount'] = 0;
		$iapPClose['pc_comments'] = "";
		$iapPClose['pc_complete'] = "N";
		$iapPClose['pc_changed'] = date("Y-m-d");
		$iapPClose['pc_changed_by'] = $_REQUEST['IAPUID']; 
		$iapRet = IAP_Update_Data($iapPClose, "parcl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating party close record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

//		$DivSelect = "block";
		$DivShow = "none";
		$iapCloseOK = "N";

	} else {

		$iapPageError = 0;
		$iapChanged = "N";
		$iapCloseOK = "N";

		if (isset($_REQUEST['pcdate'])) {
			$Ret = LHC_Validate_Date($iapPClose['pc_close_date'],$_REQUEST['pcdate']);
			if ($Ret['Changed'] == "Y"){
				$iapPClose['pc_close_date'] = $Ret['Value'];
				$iapChanged = "Y";
			}
			if ($Ret['Error'] == 1) {
				echo "<span class=iapError>A valid Close Date must be entered!</span>";
				$iapPageError = 1;
			} elseif ($Ret['Error'] == 2) {
				echo "<span class=iapError>The entered Close Date is invalid!</span>";
				$iapPageError = 1;
			}
		} elseif ($iapPClose['pc_close_date'] == "0000-00-00") {
			echo "<span class=iapError>A valid Close Date must be entered!</span>";
			$iapPageError = 1;
		}

		if (isset($_REQUEST['pcpcnt'])) {
			$iapRet = LHC_Validate_Nonblank($iapPClose['pc_award_percentage'], $_REQUEST['pcpcnt'], "Y");
			if ($iapRet['Changed'] == "Y") {
			    $iapPClose['pc_award_percentage'] = $iapRet['Value'] / 100;
			    $iapChanged = "Y";
			}
			if ($Ret['Error'] == 1) {
				echo "<span class=iapError>A valid Close Percentage must be entered!</span>";
				$iapPageError = 1;
			} elseif ($Ret['Error'] == 2) {
				echo "<span class=iapError>The entered Close Percentage is invalid!</span>";
				$iapPageError = 1;
			}
		} elseif ($iapPClose['pc_award_percentage'] == 0) {
			echo "<span class=iapError>A valid Close Percentage must be entered!</span>";
			$iapPageError = 1;
		}

		if (isset($_REQUEST['pcaddhostess'])) {
			$iapPClose['pc_add_hostess'] = "Y";
		} else {
			$iapPClose['pc_add_hostess'] = "N";
		}


		if (isset($_REQUEST['pccomplete'])) {
			$iapPClose['pc_complete'] = "Y";
		} else {
			$iapPClose['pc_complete'] = "N";
		}

		if ($_REQUEST['pccomments'] != $iapPClose['pc_comments']) {
			$iapPClose['pc_comments'] = $_REQUEST['pccomments'];
			$iapChanged = "Y";
		}

		if ($iapPageError == 0) {
			$iapPClose = iap_do_pclose($iapPClose);
			$iapCloseOK = "Y";
		}

		if (IAP_Update_Savearea("IAP482PC", $iapPClose, $_REQUEST['IAPUID']) < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update the party record savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;		
		}

		$iapOrigAction = $_REQUEST['origaction'];

//		$DivSelect = "none";
		$DivShow = "block";	
	}

	$iapSales = IAP_Get_Sale_By_PE($iapPClose['pc_pe_id']);
	if ($iapSales < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales for party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapHostessSales = 0;
	$iapOtherSales = 0;
	foreach($iapSales as $iapS) {
		if ($iapS['sale_customer'] == $iapPClose['pc_hostess']) {
			$iapHostessSales = $iapHostessSales + $iapS['sale_net'];
		} else {
			$iapOtherSales = $iapOtherSales + $iapS['sale_net'];
		}
	}
	$iapPClose['pc_hostess_purchases'] = $iapHostessSales;
	$iapPClose['pc_customer_sales'] = $iapOtherSales;

} else {

//	$DivSelect = "block";
	$DivShow = "none";
	$iapCloseOK = "N";
}

$iapSelEna = "readonly";

$iapPar = IAP_Get_PE_List("N", "Y");		// Do not get closed parties and ONLY parties
if ($iapPar < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties/events. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
$iapPList = "";
$c = "";
if ($iapPar != NULL) {
	foreach ($iapPar as $iapP) {
		$p = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
		$iapPList = $iapPList.$c.'{"label": "'.$p.'", "id": "'.strval($iapP['pe_id']).'"}';
		$c = ",";
	}
}

$iapReadOnly = IAP_Format_Heading("Party Close Out");

$h = IAP_Do_Help(3, 482, 1); // level 3, page 482, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='pcchoose'>
<p style='text-indent:50px; width:100%'>
<form name='pcselform' id='pcselform' action='?action=p482retA&origaction=initial' method='POST' onsubmit='return cNoSubmit();'>

<?php
	if (empty($iapPList)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "No parties on file. Nothing to close. Choose another function.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<span class=iapFormLabel style='padding-left: 40px;'>";
	echo "<label for='pPCList' id='pPEListLbl'>Select a Party: </label>";
	echo "<input id='pPCList' size='50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 482, 1); 	//   <!-- level 1, page 482, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the green Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='pGoClicked()'>";
/*
<picture>
    <source srcset="MyImages/SmallGoGreen.png" media="(max-width: 400px)">
    <source srcset="MyImages/GoGreen.png">
    <img src="MyImages/GoGreen.png" alt="GO!" style="width:auto;"> // style="width:304px;height:228px;">
</picture>
*/

	echo "<br><span id=pselerror class=iapError style='display:none;'>Could not find the selected party. <br>Please, make sure to choose the full description from the list.</span>";

	echo "<br><span class=iapFormLabel style='padding-left: 40px;'>".$iapMsg."</span>";
?>
<input type="hidden" name="PEIDChosen" id="PEIDChosen" value=0>

</form>
</p>
</div>

<div id='pcdetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='text-indent:50px; width:100%'>

<form name='pcdetform' id='pcdetform' action='?action=p482retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>

<table style="width:100%;">
<tr><td style="width:30%;">&nbsp;</td><td style="width:15%;">&nbsp;</td><td style="width:55%;">&nbsp;
<?php
	if ($iapPClose['pc_award_percentage'] > 0
	and $iapPClose['PrevEqNow'] == "Y") {	// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
?>
			<a href="MyReports/IAPCloseRpt.php?action=selected&co=<?php echo strval($_REQUEST['CoId']); ?>&pe=<?php echo strval($iapParty['pe_id']); ?>" target='_blank'>
				<img src='MyImages/Print.jpg' alt="-([Print])-" height="30" width="60">
			</a>
<?php
	}
?>
</td></tr>

<tr>
<td style="width:30%;"><span class='iapFormLabel'>Party Number:</span></td>
<td style="width:15%;"><?php echo $iapParty['pe_party_no']; ?></td>
<td style="width:55%;"></td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><span class='iapFormLabel'>Hostess:</span></td>
<td colspan="2"><?php echo $iapParty['pe_sponsor']; ?></td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<?php
if ($iapPClose['pc_close_date'] != "0000-00-00") {
	$dt = date("m/d/Y", strtotime($iapPClose['pc_close_date']));
} else {
	$dt = date("m/d/Y", strtotime("now"));
}
?>
<tr>
<td style="width:30%;"><label for='pcdate' class='iapFormLabel'>Close Date: </label></td>
<td style="width:15%;">
	<input <?php echo $iapReadOnly; ?> tabindex='4' placeholder='mm/dd/yyyy' maxlength='12' size='12' name='pcdate' id='pcdate' value='<?php echo $dt; ?>' autofocus>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td style="width:55%;"></td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><label class='iapFormLabel'>Sales To Customers: </label></td>
<td style="width:15%; text-align:right;">
	<?php echo number_format($iapPClose['pc_customer_sales'], 2, '.', ''); ?></td>
<td style="width:55%;">&nbsp;&nbsp;&nbsp;Sales are listed below.</td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><label class='iapFormLabel'>Hostess Purchases: </label></td>
<td style="width:15%; text-align:right;">
	<?php echo number_format($iapPClose['pc_hostess_purchases'], 2, '.', ''); ?></td>
<td style="width:55%;">&nbsp;&nbsp;&nbsp;Sales are listed below.</td>
</tr>

<tr>
<td style="width:30%;"><label for='pcaddhostess' class='iapFormLabel'>Include Hostess Purchases In Total: </label></td>
<td style="width:15%; text-align:center;">
	<input type='checkbox' name=pcaddhostess id=pcaddhostess<?php if ($iapPClose['pc_add_hostess'] == "Y") { echo " checked"; } ?>>
<?php
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 482, 2); 	//   <!-- level 1, page 482, section 2 -->
?>
 </td>
<td style="width:55%;">Check for Yes</td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><label for='pcpcnt' class='iapFormLabel'>Award Percent: </label></td>
<td style="width:15%; text-align:right;">
	<input <?php echo $iapReadOnly; ?> style="text-align:right;" tabindex='5' maxlength='15' size='15' name='pcpcnt' id='pcpcnt' step='0.01' value='<?php echo number_format($iapPClose['pc_award_percentage'] * 100, 2, '.', ''); ?>'></td>
<td style="width:55%;"><?php
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 482, 3); 	//   <!-- level 1, page 482, section 3 -->
?>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr><td colspan="3">
<?php
	if ($iapPClose['pc_award_percentage'] > 0) {
		$UsableSales = $iapPClose['pc_customer_sales'];
		if ($iapPClose['pc_add_hostess'] == "Y") {
			$UsableSales = $UsableSales + $iapPClose['pc_hostess_purchases'];
		}
?>
	<table><tr><td style='width:5%'></td><td style='width:25%'></td><td style='width:15%'></td><td style='width:55%'></td></tr>
	<tr><td colspan="4"><span class='iapFormLabel' style='text-decoration:underline;'>Calculation of Hostess Award</span></td></tr>
	<tr>
	<td style='width:5%'></td>
	<td style='width:25%'><span class='iapFormLabel'>Compensable Sales:</span></td>
	<td style="width:15%; text-align:right;">
		<?php echo number_format($UsableSales, 2, '.', ''); ?></td>
	<td style="width:55%;"></td>
	</tr>
	<tr>
	<td style='width:5%'></td>
	<td style='width:25%'><span class='iapFormLabel'>Award Amount:</span></td>
	<td style="width:15%; text-align:right;">
		<?php echo number_format($iapPClose['pc_award_amount'], 2, '.', ''); ?></td>
	<td style="width:55%;"></td>
	</tr>

<?php
	if ($iapPClose['pc_add_hostess'] == "N") {
?>
		<tr>
		<td style='width:5%'></td>
		<td style='width:25%'><span class='iapFormLabel'>Hostess Purchases:</span></td>
		<td style="width:15%; text-align:right;">
			<?php echo number_format($iapPClose['pc_hostess_purchases'], 2, '.', ''); ?></td>
		<td style="width:55%;"></td>
		</tr>
		<tr>
		<td style='width:5%'></td>
		<td colspan='3'>These Hostess Purchases were not included in Compensable Sales above.
		</td></tr>
		<tr>
		<td style='width:5%'></td>
		<td colspan='3'>As such those pruchases can be used as compensation for the hostess.
		</td></tr>
<?php
	}
?>
	</table>
<?php
	}
?>

</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><span class='iapFormLabel'>Check To Close Party: </span></td>
<td style="width:15%;">
	<input type='checkbox' name=pccomplete id=pccomplete<?php if ($iapPClose['pc_complete'] == "Y") { echo " checked"; } ?>></td>
<td style="width:55%;">Party will remain open unless checked.</td>
</tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width:30%;"><span class='iapFormLabel'>Comments:</span></td>
<td colspan="2">
	<textarea name='pccomments' id='pccomments' tabindex="26" cols='40' rows='6' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapPClose['pc_comments']; ?></textarea></td>
</tr>

<tr style='line-height:200%;'><td style="width:30%;"> </td><td style="width:15%;"></td><td style="width:55%;"></tr>
<tr style='line-height:200%;'><td style="width:30%;"> </td><td style="width:15%;">

<?php
	if ($iapReadOnly != "readonly") {
?>
	<tr>
	<td colspan='2'>
		<table>
		<tr>
		<td style="width: 19%;"></td>
		<td style="width: 81%;">
			<input class=iapButton tabindex='30' type='submit' name='pesubmit' id='pesubmit' value='Submit'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=iapButton tabindex='31' type='submit' name='peclear' id='peclear' value='Clear Form'>
		</td></tr></table>
	</td><td style="width:55%;"></td>
	</tr>

<?php
	}
?>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<?php
	if ($iapParty['pe_sales_cnt'] == 0) {
		$iapSDsply = "No sales are recorded for this party";
	}
	echo "<tr>";
	echo "<td colspan='3' id='pesaletottitle'><span style='font-size:110%; text-decoration:underline;'>Sales For This Party</span>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 482, 4); // level 1, page 482, section 4
	echo "</td></tr>";
?>
	<tr><td colspan='3'>
		<table id=pesalestbl style='width:100%;'>
		<tr style='width:100%;'>
		<td style='width:5%;'></td>
		<td style='width:20%; text-align:left;'><span style='text-decoration:underline;'>Customer</span></td>
		<td style='width:10%; text-align:right;'><span style='text-decoration:underline;'>Net Sales</span></td>
		<td style='width:9%; text-align:right;'><span style='text-decoration:underline;'>Shipping</span></td>
		<td style='width:10%; text-align:right;'><span style='text-decoration:underline;'>Tax</span></td>
		<td style='width:10%; text-align:right;'><span style='text-decoration:underline;'>Total</span></td>
		<td style='width:10%; text-align:right;'><span style='text-decoration:underline;'>Items</span></td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr><td colspan='8'>&nbsp;</td></tr>
	<?php
		if ($iapPClose['pc_customer_sales'] > 0) {

			echo "<tr><td colspan='8'><span style='font-size:110%;'>&nbsp;&nbsp;&nbsp;Sales To Customers</span></td></tr>";

			$iapNetSales = 0;
			foreach($iapSales as $iapS) {
				if ($iapS['sale_customer'] != $iapPClose['pc_hostess']) {
	?>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'><a href='?page_id=291&action=selected&sale=<?php echo $iapS['sale_id']; ?>'><?php echo $iapS['cust_name']; ?></a></td>
		<td style='width:10%; text-align:right;' id="pesnet"><?php echo number_format($iapS['sale_net'], 2, '.', ',') ?></td>
		<td style='width:9%; text-align:right;' id="pesship"><?php echo number_format($iapS['sale_shipping'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pestax"><?php echo number_format($iapS['sale_sales_tax'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pestotal"><?php echo number_format($iapS['sale_total_amt'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pesitems"><?php echo number_format($iapS['sale_items'], 0, '.', ','); ?></td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
	<?php
					$iapNetSales = $iapNetSales + $iapS['sale_net'];
				}
			}
	?>
		<tr>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:20%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'>Total Net Sales</td>
		<td style='width:10%; text-align:right'><span id='petnet'> <?php echo number_format($iapNetSales, 2, '.', ','); ?></span></td>
		<td style='width:9%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:26%;'>&nbsp;</td>
		</tr>
		<tr>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:20%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>

<?php
		}
		if ($iapPClose['pc_hostess_purchases'] > 0) {

			echo "<tr><td colspan='8'><span style='font-size:110%;'>&nbsp;&nbsp;&nbsp;Sales To The Hostess</span></td></tr>";

			$iapNetSales = 0;
			foreach($iapSales as $iapS) {
				if ($iapS['sale_customer'] == $iapPClose['pc_hostess']) {
	?>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'><a href='?page_id=291&action=selected&sale=<?php echo $iapS['sale_id']; ?>'><?php echo $iapS['cust_name']; ?></a></td>
		<td style='width:10%; text-align:right;' id="pesnet"><?php echo number_format($iapS['sale_net'], 2, '.', ',') ?></td>
		<td style='width:9%; text-align:right;' id="pesship"><?php echo number_format($iapS['sale_shipping'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pestax"><?php echo number_format($iapS['sale_sales_tax'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pestotal"><?php echo number_format($iapS['sale_total_amt'], 2, '.', ','); ?></td>
		<td style='width:10%; text-align:right;' id="pesitems"><?php echo number_format($iapS['sale_items'], 0, '.', ','); ?></td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
	<?php
					$iapNetSales = $iapNetSales + $iapS['sale_net'];
				}
			}
	?>
		<tr>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:20%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:26%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'>Total Net Sales</td>
		<td style='width:10%; text-align:right'><span id='petnet'> <?php echo number_format($iapNetSales, 2, '.', ','); ?></span></td>
		<td style='width:9%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:10%;'>&nbsp;</td>
		<td style='width:26%;'>&nbsp;</td>
		</tr>

<?php
		}
?>

		</table>
	</td></tr>
</table>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="PEID" id="PEID" value=0>

</form>
</p>
</div>

<script type="text/javascript">
<?php
require_once($_REQUEST['LHCPath']."IAP/MyJS/JSPrtyCls.min.js");
?>
var pParties = [<?php echo $iapPList; ?>];
</script>