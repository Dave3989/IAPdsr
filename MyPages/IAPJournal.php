<?php

// TODO Put out Success message

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Journal with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("154") < 0) {
	return;
};

if ($_REQUEST['action'] == 'jrnletA') {

// ------------------------------------------------------------------
// This is never executed -- handled by jscript at end of program  --
// ------------------------------------------------------------------


	$DivSelect = "none";
	$DivShow = "block";

} elseif ($_REQUEST['action'] == 'jrnlretB') {

// get journal

	$iapJrnl = (array) IAP_Get_Savearea("IAP154JR", $_REQUEST['IAPUID']);
	if (empty($iapJrnl)) {
	    echo "<span class=iajError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}

	if (!(empty($_REQUEST['JJRNLID']))) {
		$iapJId = $_REQUEST['JJRNLID'];
		$iapJrnl = IAP_Get_Journal($iapJId);
		if ($iapJrnl < 0) {
			echo "<span class=iajError>IAP INTERNAL ERROR: Cannot retreive selected journal from the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl['status'] = "EXISTING";
	}

	$iapPageError = 0;
	$iapChanged = "N";
	require_once("IAPValidators.php");

	$Ret = IAP_Validate_Date($iapJrnl['jrnl_date'],$_REQUEST['jdate']);
	if ($Ret['Changed'] == "Y"){
		$iapJrnl['jrnl_date'] = $Ret['Value'];
		$iapChanged = "Y";
	}
	if ($Ret['Error'] == 1) {
		echo "<span class=iajError>A valid Date must be entered!</span>";
		$iapPageError = 1;
	} elseif ($Ret['Error'] == 2) {
		echo "<span class=iajError>The entered Date is invalid!</span>";
		$iapPageError = 1;
	}

    if (isset($_REQUEST['jdesc'])) {
        $iapRet = IAP_Validate_Nonblank($iapJrnl['jrnl_description'], $_REQUEST['jdesc']);
        if ($iapRet['Changed'] == "Y") {
            $iapJrnl['jrnl_description'] = $iapRet['Value'];
            $iapChanged = "Y";
        }
        if ($iapRet['Error'] == "1") {
            echo "<span class=iajError>Description cannot be blank!</span><br>";
            $iapPageError = 1;
        }
    } elseif (empty($iapJrnl['jrnl_desc'])) {
        echo "<span class=iajError>Description cannot be blank!</span><br>";
		$iapPageError = 1;
	}

    if (isset($_REQUEST['jtype'])
	and $iapJrnl['jrnl_type'] != $_REQUEST['jtype']) {
		$iapJrnl['jrnl_type'] = $_REQUEST['jtype'];
		$iapChanged = "Y";
	}

/*
		<option value="IP">Item Pricing<?php if ($iapJrnl['jrnl_type'] == "IP") { echo " selected"; } ?></option>
		<option value="PI">Purchase Inventory<?php if ($iapJrnl['jrnl_type'] == "PI") { echo " selected"; } ?></option>
		<option value="PX">Product Exchange<?php if ($iapJrnl['jrnl_type'] == "PX") { echo " selected"; } ?></option>
		<option value="SE">Sales At Event<?php if ($iapJrnl['jrnl_type'] == "SE") { echo " selected"; } ?></option>
		<option value="SO">Sale On-Line<?php if ($iapJrnl['jrnl_type'] == "SO") { echo " selected"; } ?></option>
		<option value="SP">Sales At A Party<?php if ($iapJrnl['jrnl_type'] == "SP") { echo " selected"; } ?></option>
		<option value="ME">Miscellanous Expenses<?php if ($iapJrnl['jrnl_type'] == "ME") { echo " selected"; } ?></option>
		<option value="MI">Miscellanous Information<?php if ($iapJrnl['jrnl_type'] == "MI") { echo " selected"; } ?></option>
		<option value="WE">Website and Comuter Expenses<?php if ($iapJrnl['jrnl_type'] == "WE") { echo " selected"; } ?></option>
*/

	switch($iapJrnl['jrnl_type']) {
		case "CB":	// Commission/Bonus
			if (isset($_REQUEST['jcbamt'])
			and $iapJrnl['jrnl_amount'] != $_REQUEST['jcbamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jcbamt'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jcbprft'])
			and $iapJrnl['jrnl_profit'] != $_REQUEST['jcbprft']) {
				$iapJrnl['jrnl_profit'] = $_REQUEST['jcbprft'];
				$iapChanged = "Y";
			}
			break;
		case "OI":	// Owner Investment
			if (isset($_REQUEST['joiamt'])
			and $iapJrnl['jrnl_amount'] != $_REQUEST['joiamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['joiamt'];
				$iapChanged = "Y";
			}
			break;
		case "PS":	// Purchase Supplies
			if (isset($_REQUEST['jpsamt'])
			and $iapJrnl['jrnl_vendor'] != $_REQUEST['jpsvend']) {
				$iapJrnl['jrnl_vendor'] = $_REQUEST['jpsvend'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jpsamt'])
			and $iapJrnl['jrnl_amount'] != $_REQUEST['jpsamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jpsamt'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jpsship'])
			and $iapJrnl['jrnl_shipping'] != $_REQUEST['jpsship']) {
				$iapJrnl['jrnl_shipping'] = $_REQUEST['jpsship'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jpstax'])
			and $iapJrnl['jrnl_tax'] != $_REQUEST['jpstax']) {
				$iapJrnl['jrnl_tax'] = $_REQUEST['jpstax'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jpsmiles'])
			and $iapJrnl['jrnl_mileage'] != $_REQUEST['jpsmiles']) {
				$iapJrnl['jrnl_mileage'] = $_REQUEST['jpsmiles'];
				$iapChanged = "Y";
			}
			break;
		case "ME":	// Miscellaneous Expense
			if (isset($_REQUEST['jmeamt'])
			and $iapJrnl['jrnl_amount'] != $_REQUEST['jmeamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jmeamt'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jmeship'])
			and $iapJrnl['jrnl_shipping'] != $_REQUEST['jmeship']) {
				$iapJrnl['jrnl_shipping'] = $_REQUEST['jmeship'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jmetax'])
			and $iapJrnl['jrnl_tax'] != $_REQUEST['jmetax']) {
				$iapJrnl['jrnl_tax'] = $_REQUEST['jmetax'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jmemiles'])
			and $iapJrnl['jrnl_mileage'] != $_REQUEST['jmemiles']) {
				$iapJrnl['jrnl_mileage'] = $_REQUEST['jmemiles'];
				$iapChanged = "Y";
			}
			break;
		case "WE":	// Website Expense
			if (isset($_REQUEST['jwevend'])
			and $iapJrnl['jrnl_vendor'] != $_REQUEST['jwevend']) {
				$iapJrnl['jrnl_vendor'] = $_REQUEST['jwevend'];
				$iapChanged = "Y";
			}

			if (isset($_REQUEST['jweamt'])
			and $iapJrnl['jrnl_amount'] != $_REQUEST['jweamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jweamt'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jweship'])
			and $iapJrnl['jrnl_shipping'] != $_REQUEST['jweship']) {
				$iapJrnl['jrnl_shipping'] = $_REQUEST['jweship'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jwetax'])
			and $iapJrnl['jrnl_tax'] != $_REQUEST['jwetax']) {
				$iapJrnl['jrnl_tax'] = $_REQUEST['jwetax'];
				$iapChanged = "Y";
			}
			if (isset($_REQUEST['jwemiles'])
			and $iapJrnl['jrnl_mileage'] != $_REQUEST['jwemiles']) {
				$iapJrnl['jrnl_mileage'] = $_REQUEST['jwemiles'];
				$iapChanged = "Y";
			}
			break;
		}

	$iapJrnl['jrnl_comment'] = $_REQUEST['jcomment'];

	if ($iapPageError == 0
	and $iapChanged == "Y") {
		if ($iapJrnl['jrnl_type'] == "CB") {
			$iapJrnl['jrnl_net'] = $iapJrnl['jrnl_amount'] - $iapJrnl['jrnl_profit'];
		}
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iajError>IAP INTERNAL ERROR updating journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl['status'] == "EXISTING";
	}

	$iapRet = IAP_Update_Savearea("IAP154JR", $iapJrnl, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iajError>IAP INTERNAL ERROR: Cannot update savearea for journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapOrigAction = $_REQUEST['origaction'];

	$DivSelect = "none";
	$DivShow = "block";	

} else {

	if (IAP_Remove_Savearea("IAP154JR") < 0) {
		echo "<span class=iajError>IAP INTERNAL ERROR: Cannot remove the journal savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapJ = (array) IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapJ[0];

	$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
	$iapJrnl['jrnl_id'] = 0;
	$iapJrnl['jrnl_date'] = "2010-01-01";
	$iapJrnl['jrnl_description'] = "";
	$iapJrnl['jrnl_type'] = "";
	$iapJrnl['jrnl_amount'] = 0;
	$iapJrnl['jrnl_tax'] = 0;
	$iapJrnl['jrnl_shipping'] = 0;
	$iapJrnl['jrnl_mileage'] = 0;
	$iapJrnl['jrnl_expenses'] = 0;
	$iapJrnl['jrnl_exp_explain'] = 0;
	$iapJrnl['jrnl_vendor'] = "";
	$iapJrnl['jrnl_item_code'] = "";
	$iapJrnl['jrnl_cost'] = 0;
	$iapJrnl['jrnl_units'] = 0;
	$iapJrnl['jrnl_price'] = 0;
	$iapJrnl['jrnl_cat_code'] = 0;
	$iapJrnl['jrnl_comment'] = "";
	$iapJrnl['jrnl_detail_key'] = "";
	$iapJrnl['jrnl_changed'] = "2010-01-01";
	$iapJrnl['jrnl_changed_by'] = 0;

	$iapRet = IAP_Create_Savearea("IAP154JR", $iapJrnl, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iajError>IAP INTERNAL ERROR: Cannot create savearea for journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";
$jJrnls = "";
$c = "";
$iapJrnls = IAP_Get_Journal_List();
if ($iapJrnls < 0) {
    echo "<span class=iajError>iap INTERNAL ERROR: Cannot retrieve journals. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapJrnls != NULL) {
	foreach ($iapJrnls as $iapJ) {
		$j = date('m/d/Y', strtotime($iapJ['jrnl_date']))." ".str_replace('"', "'", $iapJ['jrnl_description']);
		$jJrnls = $jJrnls.$c.'{"label": "'.$j.'", "jrnlid": "'.strval($iapJ['jrnl_id']).'"}';
		$c = ',';
	}
	$iapSelEna = "";
}

$jcommdisp = "none";
$jiprcdisp = "none";
$joinvdisp = "none";
$jpinvdisp = "none";
$jpsupdisp = "none";
$jpxchdisp = "none";
$jsalesdsp = "none";
$jsales2dsp = "none";
$jmexpdisp = "none";
$jminfdisp = "none";
switch($iapJrnl['jrnl_type']) {
	case "CB":
		$jcommdisp = "block";
		break;
	case "IP":
		$jiprcdisp = "block";
		break;
	case "OI":
		$joinvdisp = "block";
		break;
	case "PI":
		$jpinvdisp = "block";
		break;
	case "PS":
		$jpsupdisp = "block";
		break;
	case "PX":
		$jpxchdisp = "block";
		break;
	case "SE":
	case "SI":
	case "SO":
	case "SP":
	case "SW":
		if ($iapJrnl['jrnl_type'] == "SE" 
		or  $iapJrnl['jrnl_type'] == "SP") {
			$jsalesdsp = "block";
		}
		$jsales2dsp = "block";
		break;
//	case "SX":
//		$jsxfrdisp = "block";
//		break;
	case "ME":
		$jmexpdisp = "block";
		break;
	case "MI":
		$jminfdisp = "block";
		break;
	case "WE":
		$jwexpdisp = "block";
		break;
}

$iapReadOnly = IAP_Format_Heading("Activity Journal");

$h = IAP_Do_Help(3, 154, 1); // level 3, page 154, section 1
if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
}

?>

<div id='jchoose'>

<form name='jselform' action='?action=jrnlretA&origaction=initial' method='POST'>
<?php
	if (empty($jJrnls)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "No Journals on file. Click on ADD.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<span class=iapFormLabel style='padding-left: 40px;'>";
	echo "<label for='jselect'>Select a journal:&nbsp;</label>";
	echo "<input id='jselect' size='50' maxlength='100'></span>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 154, 1); 
//		Help Narative	<!-- level 1, page 154, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='jGoClicked()'>";

	echo "<br><span id=jerror class=iajError style='display:none;'>The journal was not found. Retry or click Add.</span>";

	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapFormLabel style='padding-left: 50px;'>";
		echo "<input type='button' class=iapButton name='jadd' id='jadd' value='Add A New Journal Entry' onclick='jAddClicked()' />";
	}
	echo "<br>".$iapMsg."</span>";
?>
</form>

</div>
<div id='jdetail' style='display:<?php echo $DivShow; ?>;'>

<?php
	echo "<div id=jlink style='display:none; width:100%'>";

	switch($iapJrnl['jrnl_type']) {
		case "PI":					// Purchase Inventory
			$iapMod = "208";
			break;
		case "SE":					// Sale Functions
		case "SI": 
		case "SO": 
		case "SP": 
		case "SW":
			$iapMod = "291";
			break;
		default:
			$iapMod = "154";
	}
	echo "<table style='width:100%'><tr><td style='width: 5%;'></td><td style='width: 15%;'></td><td style='width:80%;'>";
	echo "<span class=iapFormLabel><a href='?page_id=".$iapMod."&action=selected&key=".$iapJrnl['jrnl_detail_key']."' id=jlinkref>Click Here To View The Detail For This Entry.</a></span>";
	echo "</td></tr></table>";
	echo "</div>";
?>

<form name='jdetform' action='?action=jrnlretB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Date:</span></td>
<td style="width: 80%;">
	<input tabindex='1' placeholder='mm/dd/yyyy' maxlength='15' size='15' name='jdate' id='jdate' value='<?php echo date('m/d/Y', strtotime($iapJrnl['jrnl_date'])); ?>' autofocus>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr><td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Description:</span></td>
<td style="width: 80%;">
	<input <?php echo $iapReadOnly; ?> tabindex="2" size="50" maxlength="100" name="jdesc" id="jdesc" value="<?php echo $iapJrnl['jrnl_description']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 154, 3);  //		Help Narative	<!-- level 1, page 154, section 3 --> ?>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>

<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Journal Type:</span></td>
<td style="width: 80%;">
	<select <?php echo $iapReadOnly; ?> tabindex="3" size="1" name="jtype" id="jtype" onchange='jSetOptionDiv()'>
		<option value="--"<?php if ($iapJrnl['jrnl_type'] == "") { echo " selected"; } ?>>--- Select A Type ---</option>
		<option value="CB"<?php if ($iapJrnl['jrnl_type'] == "CB") { echo " selected"; } ?>>Commission/Bonus</option>
		<option value="IP"<?php if ($iapJrnl['jrnl_type'] == "IP") { echo " selected"; } ?>>*Item Pricing</option>
		<option value="OI"<?php if ($iapJrnl['jrnl_type'] == "OI") { echo " selected"; } ?>>Owner Investment</option>
		<option value="PI"<?php if ($iapJrnl['jrnl_type'] == "PI") { echo " selected"; } ?>>*Purchase Inventory</option>
		<option value="PS"<?php if ($iapJrnl['jrnl_type'] == "PS") { echo " selected"; } ?>>Purchase Supplies</option>
		<option value="PX"<?php if ($iapJrnl['jrnl_type'] == "PX") { echo " selected"; } ?>>Product Exchange</option>
		<option value="SE"<?php if ($iapJrnl['jrnl_type'] == "SE") { echo " selected"; } ?>>*Sales At Event</option>
		<option value="SP"<?php if ($iapJrnl['jrnl_type'] == "SP") { echo " selected"; } ?>>*Sales At A Party</option>
		<option value="SI"<?php if ($iapJrnl['jrnl_type'] == "SI") { echo " selected"; } ?>>*Sale To An Individual</option>
		<option value="SW"<?php if ($iapJrnl['jrnl_type'] == "SW") { echo " selected"; } ?>>*Sale On-Line</option>
		<option value="SO"<?php if ($iapJrnl['jrnl_type'] == "SO") { echo " selected"; } ?>>*Sales - Other Types</option>
		<option value="ME"<?php if ($iapJrnl['jrnl_type'] == "ME") { echo " selected"; } ?>>Miscellanous Expense</option>
		<option value="MI"<?php if ($iapJrnl['jrnl_type'] == "MI") { echo " selected"; } ?>>Miscellanous Information</option>
		<option value="WE"<?php if ($iapJrnl['jrnl_type'] == "WE") { echo " selected"; } ?>>Website and Comuter Expenses</option>
	</select>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 154, 4);  //		Help Narative	<!-- level 1, page 154, section 4 --> ?>

</td></tr>
<tr><td style="width: 5%;"></td><td style="width: 15%;"></td><td style="width: 80%;"><span class=iapFormLabel>Selections with an * are view only.</span></td> </tr>
</table>

<!-- The Following Are Input Here -->

	<div id=jcomm style="display:<?php echo $jcommdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="10" size="10" maxlength="10" name="jcbamt" id="jcbamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
			&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 154, 2);  //		Help Narative	<!-- level 1, page 154, section 2 --> ?>
		</td></tr>
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"></td>
		<td style="width: 80%;">Enter the total compensation amount including any retail profit.
		</td></tr>

<!-- Retail profit from sales on company website -->

		<tr><td colspan="3">&nbsp;</td></tr>
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Profit From Online Sales:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="11" size="10" maxlength="10" name="jcbprft" id="jcbprft"  value="<?php echo number_format((float) $iapJrnl['jrnl_profit'], 2, '.', ','); ?>">
		</td></tr>
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"></td>
		<td style="width: 80%;">Do not subtract the retail profit from the total amount above. I will do this.
		</td></tr>

		</table>
	</div>

	<div id=jowninv style="display:<?php echo $joinvdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount Invested:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="15" size="10" maxlength="10" name="joiamt" id="joiamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		</table>
	</div>

	<div id=jpursup style="display:<?php echo $jpsupdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Purchased From:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapReadOnly; ?> tabindex="20" size="50" maxlength="35" name="jpsvend" id="jpsvend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Purchase Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="21" size="10" maxlength="10" name="jpsamt" id="jpsamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		
		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="22" size="10" maxlength="10" name="jpsship" id="jpsship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapReadOnly; ?> tabindex="23" size="10" maxlength="10" name="jpstax" id="jpstax" align="right" step="0.1" value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Mileage:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="24" size="10" maxlength="10" name="jpsmiles" id="jpsmiles" align="right" step="0.1" value=<?php echo $iapJrnl['jrnl_mileage']; ?>>
		</td></tr>
		</table>
	</div>

	<div id=jmiscexp style="display:<?php echo $jmexpdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="30" size="10" maxlength="10" name="jmeamt" id="jmeamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		
		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="31" size="10" maxlength="10" name="jmeship" id="jmeship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapReadOnly; ?> tabindex="32" size="10" maxlength="10" name="jmetax" id="jmetax" align="right" step="0.1" value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Mileage:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="33" size="10" maxlength="10" name="jmemiles" id="jmemiles" align="right" step="0.1" value=<?php echo $iapJrnl['jrnl_mileage']; ?>>
		</td></tr>

		</table>
	</div>

	<div id=jwebexp style="display:<?php echo $jmexpdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Purchased From:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapReadOnly; ?> tabindex="20" size="50" maxlength="35" name="jwevend" id="jwevend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="30" size="10" maxlength="10" name="jweamt" id="jweamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		
		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="31" size="10" maxlength="10" name="jweship" id="jweship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapReadOnly; ?> tabindex="32" size="10" maxlength="10" name="jwetax" id="jwetax" align="right" step="0.1" value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Mileage:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapReadOnly; ?> tabindex="33" size="10" maxlength="10" name="jwemiles" id="jwemiles" align="right" step="0.1" value=<?php echo $iapJrnl['jrnl_mileage']; ?>>
		</td></tr>

		</table>
	</div>


<!-- The Following Are For Review Only Here -->

	<div id=jitemprc style="display:<?php echo $jiprcdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Item:</span></td>
		<td style="width: 80%;">
			<input tabindex="35" size="50" maxlength="50" name="jipitem" id="jipitem" value="<?php echo $iapJrnl['jrnl_item_code']; ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="36" size="10" maxlength="10" name="jipcost" id="jipcost"  value="<?php echo number_format((float) $iapJrnl['jrnl_cost'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Units:</span></td>
		<td style="width: 80%;">
			<input tabindex="37" size="50" maxlength="35" name="jipunits" id="jipunits" value="<?php echo $iapJrnl['jrnl_item_units']; ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Price:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="38" size="10" maxlength="10" name="jipprice" id="jipprice"  value="<?php echo number_format((float) $iapJrnl['jrnl_price'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Category:</span></td>
		<td style="width: 80%;">
			<input tabindex="39" size="50" maxlength="35" name="jipcat" id="jipcat" value="<?php echo $iapJrnl['jrnl_category']; ?>" readonly="readonly">
		</td></tr>
		</table>
	</div>

	<div id=jpurinv style="display:<?php echo $jpinvdisp; ?>;">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Vendor:</span></td>
		<td style="width: 80%;">
			<input tabindex="45" size="50" maxlength="35" name="jpivend" id="jpivend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Net Cost:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="46" size="10" maxlength="10" name="jpinet" id="jpinet"  value="<?php echo number_format((float) $iapJrnl['jrnl_net'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="47" size="10" maxlength="10" name="jpiship" id="jpiship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="48" size="10" maxlength="10" name="jpitax" id="jpitax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Total Paid:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="46" size="10" maxlength="10" name="jpiamt" id="jpiamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>
		</table>
	</div>

	<div id=jprodxchg style="display:<?php echo $jpxchdisp; ?>">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Vendor:</span></td>
		<td style="width: 80%;">
			<input tabindex="50" size="50" maxlength="35" name="jpxvend" id="jpxvend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="51" size="10" maxlength="10" name="jpxcost" id="jpxcost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="52" size="10" maxlength="10" name="jpxship" id="jpxship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="53" size="10" maxlength="10" name="jpxtax" id="jpxtax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>
		</table>
	</div>

	<div id=jsales style="display:<?php echo $jsalesdsp; ?>">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel" id="jsvendlbl">Sponsor:</span></td>
		<td style="width: 80%;">
			<input tabindex="50" size="50" maxlength="35" name="jsvend" id="jsvend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" readonly="readonly">
		</td></tr>
		</table>
	</div>

	<div id=jsales2 style="display:<?php echo $jsales2dsp; ?>">
		<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Net Sale Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="55" size="10" maxlength="10" name="jsnet" id="jsnet" value="<?php echo number_format((float) $iapJrnl['jrnl_net'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel" id="jsnet">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="55" size="10" maxlength="10" name="jscost" id="jscost" value="<?php echo number_format((float) $iapJrnl['jrnl_cost'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Profit On Sale:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="57" size="10" maxlength="10" name="jsprft" id="jsprft" value="<?php echo number_format((float) $iapJrnl['jrnl_profit'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="56" size="10" maxlength="10" name="jsship" id="jsship" value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>

		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="57" size="10" maxlength="10" name="jstax" id="jstax" value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Total Sale Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input tabindex="55" size="10" maxlength="10" name="jstotal" id="jstotal" value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" readonly="readonly">
		</td></tr>
		</table>
	</div>

<table style="text-align: left;width:100%;line-height:150%" border="1" cellpadding="2" cellspacing="2">
<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Comment:</span></td>
<td style="width: 80%;">
<textarea tabindex="70" name='jcomment' id='jcomment' cols='51' rows='5' wrap='soft' <?php echo $iapReadOnly; ?>><?php echo $iapJrnl['jrnl_comment']; ?></textarea>
</td></tr>

<tr><td colspan="3">&nbsp;</td></tr>
<tr><td colspan="3">&nbsp;</td></tr>

<tr><td style="width: 5%;"></td><td style="width: 15%;"></td><td style="width: 80%;">

<?php
	$d = "block";
	if ($iapJrnl['jrnl_type'] == "IP" 
	or  $iapJrnl['jrnl_type'] == "PI" 
	or  $iapJrnl['jrnl_type'] == "PX" 
	or  $iapJrnl['jrnl_type'] == "SE" 
	or  $iapJrnl['jrnl_type'] == "SI" 
	or  $iapJrnl['jrnl_type'] == "SO" 
	or  $iapJrnl['jrnl_type'] == "SP" 
	or  $iapJrnl['jrnl_type'] == "SW"
	or  $iapReadOnly == "readonly") {
		$d = "none";
	}
	echo "<input tabindex='75' class=iapButton style='diplay:".$d."' type='submit' id='csubmit' name='csubmit' value='Submit'>";
?>

</td></tr>
</table>

<br><br><br>

<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="">
<input type="hidden" name="JUPDATETYPE" id="JUPDATETYPE" value="">
<input type="hidden" name="JJRNLDATE" id="JJRNLDATE" value="">
<input type="hidden" name="JJRNLID" id="JJRNLID" value="">

</form>
</div>

<script type="text/javascript">
<?php
//require_once("MyJS/JSJrnl.min.js");
  require_once("MyJS/NonJSMin/JSJrnl.js");
?>
var jJList = [<?php echo $jJrnls; ?>];
</script>