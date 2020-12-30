<?php

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Codes with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

$_REQUEST['sec_use_application'] = "Y";
require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("154") < 0) {
	return;
};

if ($_REQUEST['action'] == 'p154retA') {

// ------------------------------------------------------------------
// This is never executed -- handled by jscript at end of program  --
// ------------------------------------------------------------------


	$DivSelect = "none";
	$DivShow = "block";

} elseif ($_REQUEST['action'] == 'p154retB') {

// get code

	$iapJrnl = (array) IAP_Get_Savearea("IAP154JR", $_REQUEST['IAPUID']);
	if (empty($iapJrnl)) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}

	if (!(empty($_REQUEST['jjrnlid']))) {
		$iapJId = $_REQUEST['jjrnlid'];
		$iapJrnl = IAP_Get_Codes($iapJId);
		if ($iapJrnl < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected code from the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl['status'] = "EXISTING";
	}

	$iapPageError = 0;
	$iapChanged = "N";

    if (isset($_REQUEST['jdesc'])) {
        $iapRet = LHC_Validate_Nonblank($iapJrnl['jrnl_description'], $_REQUEST['jdesc']);
        if ($iapRet['Changed'] == "Y") {
            $iapJrnl['jrnl_description'] = $iapRet['Value'];
            $iapChanged = "Y";
        }
        if ($iapRet['Error'] == "Y") {
            echo "<span class=iapError>Description cannot be blank!</span><br>";
            $iapPageError = 1;
        }
    } elseif (empty($iapJrnl['jrnl_desc'])) {
        echo "<span class=iapError>Description cannot be blank!</span><br>";
		$iapPageError = 1;
	}

    if (isset($_REQUEST['jtype'])
	and $iapJrnl['jrnl_type'] != $_REQUEST['jtype']) {
		$iapJrnl['jrnl_type'] = $_REQUEST['jtype'];
		$iapChanged = "Y";
	}

	switch($iapJrnl['jrnl_type']) {
		case "CB":
		    if (isset($_REQUEST['jcbamt'])
	    	and $iapJrnl['jrnl_amount'] != $_REQUEST['jcbamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jcbamt'];
        		$iapChanged = "Y";
			}
			break;
		case "OI":
		    if (isset($_REQUEST['joiamt'])
	    	and $iapJrnl['jrnl_amount'] != $_REQUEST['joiamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['joiamt'];
        		$iapChanged = "Y";
			}
			break;
		case "PS":
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
			break;
		case "ME":
		    if (isset($_REQUEST['jmeamt'])
	    	and $iapJrnl['jrnl_amount'] != $_REQUEST['jmeamt']) {
				$iapJrnl['jrnl_amount'] = $_REQUEST['jmeamt'];
        		$iapChanged = "Y";
			}
			break;		
	}

	if ($iapPageError == 0
	and $iapChanged == "Y") {
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating code [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl['status'] == "EXISTING";
	}

	$iapOrigAction = $_REQUEST['origaction'];

	$DivSelect = "none";
	$DivShow = "block";	

} else {

	if (IAP_Remove_Savearea("IAP154JR") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the code savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapJ = (array) IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapJ[0];
	$iapRet = IAP_Create_Savearea("IAP154JR", $iapJrnl, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for code [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "disabled";
$iapOpts = "";
$iapJrnls = IAP_Get_Codes_List();
if ($iapJrnls < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve codes. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapJrnls != NULL) {
	$iapOpts = "<option value='---'>Select A Codes Entry...</option>";
	foreach ($iapJrnls as $iapJ) {
		$iapOpts = $iapOpts."<option value='".$iapJ['jrnl_id']."'";
		if ($iapJ['jrnl_id'] == $iapJrnl['jrnl_id']) {
			$iapOpts = $iapOpts." selected";
		}
		$iapOpts = $iapOpts.">".$iapJ['jrnl_description']." ".date('m/d/Y', strtotime($iapJ['jrnl_date']))."</option>";
	}
	$iapSelEna = "";
}

// --------------------------------------------- //
// --- Check Subscription Status --------------- //
	$iapDisable = "";
	if ($_REQUEST['UserData']['Mode'] == "expired") {
		echo "<span class=iapError>Error - Your current subscription has expired. You can view your data but make no changes.</span><br><br>";
		$iapDisable = "disabled";
	} elseif ($_REQUEST['UserData']['Mode'] == "warn") {
		echo "<span class=iapWarning>WARNING - Your current subscription will expire on ".date("m/d/Y", strtotime($_REQUEST['UserData']['Expires']))."</span><br><br>";
	}
// --------------------------------------------- //

$iapHeading = $_REQUEST['UserData']['DisplayName'];
if (substr_compare($iapHeading, "s", -1, 1) == 0) {
	$iapHeading = $iapHeading."'&nbsp;&nbsp;Codes";
} else {
	$iapHeading = $iapHeading."'s&nbsp;&nbsp;Codes";
}
echo "<table><tr><td width='15%'><span style='font-size:1px;'> </span></td><td width='50%'></td><td width='35%'></td></tr>";
echo "<tr><td colspan='2' class='iapFormHead'>".$iapHeading."</td><td width='35%'></td></tr></table>";

?>

<div id='jchoose'>
<p style='text-indent:50px; width:100%'>
<form name='jselform' action='?action=p154retA&origaction=initial' method='POST'>
<span class=iapFormLabel>
<select name=jselect id=jselect size='1' onchange='jSelectClicked()' autofocus><?php echo $iapOpts; ?></select>
<?php
	if ($iapDisable != "disabled") {
		echo "&nbsp;&nbsp;&nbsp;<input type='button' class=iapButton name='jadd' id='jadd' value='Add A New Codes Entry' onclick='jAddClicked()' />";
	}
?>
</span>
<br>
</form>
</p>
<br>
</div>
<div id='jdetail' style='display:<?php echo $DivShow; ?>;'>

<p style='text-indent:50px; width:100%'>


<?php
	echo "<div id=jlink style='display:none; width:100%'>";

	switch($iapJrnl['jrnl_type']) {
		case "PI":					// Purchase Inventory
			$iapMod = "208";
			break;
		default:
			$iapMod = "154";
	}
	echo "<table><tr><td style='width: 5%;'></td><td style='width: 15%;'></td><td style='width:80%;'>";
	echo "<span class=iapFormLabel><a href='?page_id=".$iapMod."&action=selected&key=".$iapJrnl['jrnl_detail_key']."' id=jlinkref>Click Here To View The Detail For This Entry.</a></span>";
	echo "</td></tr></table>";
	echo "</div>";
?>

<form name='jdetform' action='?action=p154retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">
<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Description:</span></td>
<td style="width: 80%;">
	<input <?php echo $iapDisable; ?> tabindex="1" size="35" maxlength="50" name="jdesc" id="jdesc" value="<?php echo $iapJrnl['jrnl_description']; ?>" autofocus>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Codes Type:</span></td>
<td style="width: 80%;">
	<select <?php echo $iapDisable; ?> tabindex="2" size="1" name="jtype" id="jtype" onchange='jSetOptionDiv()'>
		<option value="--">--- Select A Type ---</option>
		<option value="CB">Commission/Bonus<?php if ($iapJrnl['jrnl_type'] == "CB") { echo " selected"; } ?></option>
		<option value="OI">Owner Investment<?php if ($iapJrnl['jrnl_type'] == "OI") { echo " selected"; } ?></option>
		<option value="PI">Purchase Inventory<?php if ($iapJrnl['jrnl_type'] == "PI") { echo " selected"; } ?></option>
		<option value="PS">Purchase Supplies<?php if ($iapJrnl['jrnl_type'] == "PS") { echo " selected"; } ?></option>
		<option value="PX">Product Exchange<?php if ($iapJrnl['jrnl_type'] == "PX") { echo " selected"; } ?></option>
		<option value="SE">Sales At Event<?php if ($iapJrnl['jrnl_type'] == "SE") { echo " selected"; } ?></option>
		<option value="SO">Sale On-Line<?php if ($iapJrnl['jrnl_type'] == "SO") { echo " selected"; } ?></option>
		<option value="SP">Sales At A Party<?php if ($iapJrnl['jrnl_type'] == "SP") { echo " selected"; } ?></option>
		<option value="ME">Miscellanous Expense<?php if ($iapJrnl['jrnl_type'] == "ME") { echo " selected"; } ?></option>
	</select>
</td></tr>
</table>
<!-- The Following Are Input Here -->
	<div id=jcomm style="display:none;">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jcbamt" id="jcbamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		</table>
	</div>

	<div id=jowninv style="display:none;">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount Invested:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="joiamt" id="joiamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		</table>
	</div>

	<div id=jpursup style="display:none;">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Purchased From:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapDisable; ?> tabindex="1" size="50" maxlength="35" name="jpsvend" id="jpsvend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Purchase Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpsamt" id="jpsamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpsship" id="jpsship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>">
		</td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpstax" id="jpstax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>">
		</td></tr>
		</table>
	</div>

	<div id=jmiscexp style="display:none;">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Amount:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jmeamt" id="jmeamt"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>">
		</td></tr>
		</table>
	</div>


<!-- The Following Are For Review Only Here -->

	<div id=jpurinv style="display:none;">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Vendor:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapDisable; ?> tabindex="1" size="50" maxlength="35" name="jpivend" id="jpivend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpicost" id="jpicost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpiship" id="jpiship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpitax" id="jpitax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		</table>
	</div>

	<div id=jprodxchg style="display:none">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Vendor:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapDisable; ?> tabindex="1" size="50" maxlength="35" name="jpxvend" id="jpxvend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" disabled="disabled">
		</td></tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpxcost" id="jpxcost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpxship" id="jpxship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jpxtax" id="jpxtax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		</table>
	</div>

	<div id=jsevent style="display:none">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsecost" id="jsecost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jseship" id="jseship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsetax" id="jsetax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		</table>
	</div>

	<div id=jsonline style="display:none">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Vendor:</span></td>
		<td style="width: 80%;">
			<input <?php echo $iapDisable; ?> tabindex="1" size="50" maxlength="35" name="jsovend" id="jsovend" value="<?php echo $iapJrnl['jrnl_vendor']; ?>" disabled="disabled">
		</td></tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsocost" id="jsocost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsoship" id="jsoship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsotax" id="jsotax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		</table>
	</div>

	<div id=jsparty style="display:none">
		<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Cost Of inventory:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jspcost" id="jspcost"  value="<?php echo number_format((float) $iapJrnl['jrnl_amount'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Shipping:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jspship" id="jspship"  value="<?php echo number_format((float) $iapJrnl['jrnl_shipping'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>

		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
		<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Sales Tax:</span></td>
		<td style="width: 80%; align:right;">
			<input <?php echo $iapDisable; ?> tabindex="3" size="10" maxlength="10" name="jsptax" id="jsptax"  value="<?php echo number_format((float) $iapJrnl['jrnl_tax'], 2, '.', ','); ?>" disabled="disabled">
		</td></tr>
		</table>
	</div>

<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 5%;"></td><td style="width: 15%;"><span class="iapFormLabel">Comment:</span></td>
<td style="width: 80%;">
<textarea name='jcomment' id='jcomment' cols='51' rows='5' wrap='soft' <?php echo $iapDisable; ?>><?php echo $iapJrnl['jrnl_comment']; ?></textarea>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td style="width: 5%;"></td><td style="width: 15%;"></td><td style="width: 80%;">
<?php
	if ($iapDisable != "disabled") {
		echo "<input tabindex='52' type='submit' name='csubmit' value='Submit'>";
	}
?></td></tr>
</table>

<br><br><br>

<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" maxlength="10" size="10" name="jupdatetype" id="jupdatetype" value="">
<input type="hidden" maxlength="10" size="10" name="jjrnldate" id="jjrnldate" value="">
<input type="hidden" maxlength="10" size="10" name="jjrnlid" id="jjrnlid" value="">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="">

</form>
</p>
</div>

<!-- format( "#,###0.00", 3.141592) -->
<script src="<?php echo $_REQUEST['LHCUrl']; ?>Ajax/format.min.js" type="text/javascript"></script>

<script type="text/javascript">

function jsetOnOff(iapFld, iapSet) {
	var iapValue = document.getElementById(iapFld).value;
	var iapVParts = iapValue.split("-");
	var iapRealValue = iapVParts[1];
	if (iapRealValue == "Y") {
		document.getElementById(iapSet).style.display="block"; 
	} else {
		document.getElementById(iapSet).style.display="none"; 
	}
}

function jturnOn(iapFld) { document.getElementById(iapFld).disabled = false; }

function jturnOff(iapFld) { document.getElementById(iapFld).disabled = true; }

function jAddClicked() {
	document.getElementById("jdetail").style.display="block"; 
	document.getElementById("jdesc").focus();
	jblankCodes();
}

function jSelectClicked() {
	if (document.getElementById("jselect").value == "---") {
		document.getElementById("jdetail").style.display="none";
		return;	
	}
	document.getElementById("jdetail").style.display="block"; 
	jgetCodes();
}

function jblankCodes() {
	document.getElementById("jdesc").value = "";
	document.getElementById("jtype").value = "";
	document.getElementById("jtype").disabled=false;
	jSetOptionDiv()

	document.getElementById("jcbamt").value=""; 
	document.getElementById("joiamt").value=""; 
	document.getElementById("jpivend").value=""; 
	document.getElementById("jpicost").value=""; 
	document.getElementById("jpiship").value=""; 
	document.getElementById("jpitax").value=""; 
	document.getElementById("jpsvend").value=""; 
	document.getElementById("jpsamt").value=""; 
	document.getElementById("jpsship").value=""; 
	document.getElementById("jpstax").value=""; 
//	document.getElementById("jpxdir").value=""; 
	document.getElementById("jpxamt").value=""; 
	document.getElementById("jpxship").value=""; 
	document.getElementById("jpxtax").value=""; 
	document.getElementById("jsovend").value=""; 
	document.getElementById("jsoamt").value=""; 
	document.getElementById("jsoship").value=""; 
	document.getElementById("jsotax").value=""; 
	document.getElementById("jsevend").value=""; 
	document.getElementById("jseamt").value=""; 
	document.getElementById("jseship").value=""; 
	document.getElementById("jsetax").value=""; 
	document.getElementById("jsevend").value=""; 
	document.getElementById("jseamt").value=""; 
	document.getElementById("jseship").value=""; 
	document.getElementById("jsetax").value=""; 
	document.getElementById("jmeamt").value=""; 
	document.getElementById("jcomment").value = "";

	document.getElementById("jupdatetype").value = "NEW";
	document.getElementById("jjrnldate").value = "";
	document.getElementById("jjrnlid").value = "";
	return true;	
}

function jgetCodes() {
	var urlin = document.URL;
	var urllc = urlin.toLowerCase();
	if (urllc.indexOf("litehausconsulting") >= 0) {
		var urldomain = "litehausconsulting";
	} else {
		var urldomain = "itsapartydsr";
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
	}

	//	Mozilla version
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	//	IE version
	else if (window.ActiveXObject) {
		xhr = new ActiveXObject("Microsoft.XMLHTTP");
	}

	var accountFld = document.getElementById("LHCA").value;
	var jrnlIdFld = document.getElementById("jselect").value;
	iapGetType = encodeURIComponent("J#");
	iapOrg = encodeURIComponent(accountFld);
	iapJId = encodeURIComponent(jrnlIdFld);

	xhr.open("POST",iappath+"/Ajax/iapGetDB.php");
	xhr.setRequestHeader(
		'Content-Type',
		'application/x-www-form-urlencoded; charset=UTF-8');
	xhr.send(iapGetType+"|"+iapOrg+"|"+iapJId);
	xhr.onreadystatechange=function() {
		if (xhr.readyState==4) {
			var code_rec = xhr.responseText;
			if (code_rec == -1) {
				document.getElementById("jstatus").value = "ERROR";
			} else if (code_rec == 0) {
				document.getElementById("jstatus").value = "NEW";
				iblankCodes();
			} else {
				var code_object = eval ("(" + code_rec + ")"); 

				document.getElementById("jdesc").value = code_object.jrnl_description;
				document.getElementById("jtype").value = code_object.jrnl_type;
				jSetOptionDiv();
				document.getElementById("jtype").disabled = true;

				var iapAmt = format("#,###0.00", code_object.jrnl_amount);
				var iapShip = format("#,###0.00", code_object.jrnl_shipping);
				var iapTax = format("#,###0.00", code_object.jrnl_tax);

				switch(code_object.jrnl_type) {
					case "CB":
						document.getElementById("jcbamt").value = iapAmt;
					    break;
					case "ME":
						document.getElementById("jmeamt").value = iapAmt;
					    break;
					case "OI":
						document.getElementById("joiamt").value = iapAmt;
					    break;
					case "PI":
						document.getElementById("jpivend").value = code_object.jrnl_vendor;
						document.getElementById("jpicost").value = iapAmt;
						document.getElementById("jpiship").value = iapShip;
						document.getElementById("jpitax").value = iapTax;
						document.getElementById("jcomment").disabled = true;

						if (code_object.jrnl_detail_key != "") {
							document.getElementById("jlink").style.display="block"; 
							var link = "?page_id=208&action=selected&key=" + code_object.jrnl_detail_key;
							document.getElementById("jlinkref").href = link; 
						} else {
							document.getElementById("jlink").style.display="none"; 
						}
					    break;
					case "PS":
						document.getElementById("jpsvend").value = code_object.jrnl_vendor;
						document.getElementById("jpsamt").value = iapAmt;
						document.getElementById("jpsship").value = iapShip;
						document.getElementById("jpstax").value = iapTax;
					    break;
					case "PX":
						document.getElementById("jpxdir").value = code_object.jrnl_direction;
						document.getElementById("jpxamt").value = iapAmt;
						document.getElementById("jpxship").value = iapShip;
						document.getElementById("jpxtax").value = iapTax;
					    break;
					case "SE":
						document.getElementById("jseamt").value = iapAmt;
						document.getElementById("jseship").value = iapShip;
						document.getElementById("jsetax").value = iapTax;
					    break;
					case "SO":
						document.getElementById("jsovend").value = code_object.jrnl_vendor;
						document.getElementById("jsoamt").value = iapAmt;
						document.getElementById("jsoship").value = iapShip;
						document.getElementById("jsotax").value = iapTax;
					    break;
					case "SP":
						document.getElementById("jspamt").value = iapAmt;
						document.getElementById("jspship").value = iapship;
						document.getElementById("jsptax").value = iapTax;
					    break;
				}
				document.getElementById("jcomment").value = code_object.jrnl_comment;

				document.getElementById("jupdatetype").value = "EXISTING";
				document.getElementById("jjrnldate").value = code_object.jrnl_date;
				document.getElementById("jjrnlid").value = code_object.jrnl_id;
			}
			return true;
		}
	}
}

function jSetOptionDiv() {
	document.getElementById("jcomm").style.display = "none"; 
	document.getElementById("jmiscexp").style.display="none"; 
	document.getElementById("jowninv").style.display="none"; 
	document.getElementById("jpurinv").style.display="none"; 
	document.getElementById("jpursup").style.display="none"; 
	document.getElementById("jprodxchg").style.display="none"; 
	document.getElementById("jsevent").style.display="none"; 
	document.getElementById("jsonline").style.display="none"; 
	document.getElementById("jsparty").style.display="none"; 

	var jOption = document.getElementById("jtype").value;
	switch(jOption) {
		case "":
			break;
		case "CB":
			document.getElementById("jcomm").style.display = "block"; 
			document.getElementById("jcomment").disabled = false;
			break;
		case "ME":
			document.getElementById("jmiscexp").style.display = "block"; 
			document.getElementById("jcomment").disabled = false;
			break;
		case "OI":
			document.getElementById("jowninv").style.display = "block"; 
			document.getElementById("jcomment").disabled = false;
			break;
		case "PI":
			document.getElementById("jpurinv").style.display = "block"; 
			document.getElementById("jcomment").disabled = true;
			break;
		case "PS":
			document.getElementById("jpursup").style.display = "block"; 
			document.getElementById("jcomment").disabled = false;
			break;
		case "PX":
			document.getElementById("jprodxchg").style.display = "block"; 
			document.getElementById("jcomment").disabled = true;
			break;
		case "SE":
			document.getElementById("jsevent").style.display = "block"; 
			document.getElementById("jcomment").disabled = true;
			break;
		case "SO":
			document.getElementById("jsonline").style.display = "block"; 
			document.getElementById("jcomment").disabled = true;
			break;
		case "SP":
			document.getElementById("jsparty").style.display = "block"; 
			document.getElementById("jcomment").disabled = true;
			break;
	}
}

function jSetOption(iapId, iapTable, iapOption) {
	var iapCode = iapTable+"-"+iapOption;
	var iapSelect = document.getElementById(iapId);
	for(var i = 0; i < iapSelect.length; i++){
		if(iapSelect[i].value == iapCode){
			iapSelect[i].selected = true;
		} else {
			iapSelect[i].selected = false;
		}
	}
}

// From: www.somacon.com/p143.php
// set the radio button with the given value as being checked
// do nothing if there are no radio buttons
// if the given value does not exist, all the radio buttons
// are reset to unchecked
function setCheckedValue(radioObj, newValue) {
	if(!radioObj)
		return;
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		radioObj.checked = (radioObj.value == newValue.toString());
		return;
	}
	for(var i = 0; i < radioLength; i++) {
		radioObj[i].checked = false;
		if(radioObj[i].value == newValue.toString()) {
			radioObj[i].checked = true;
		}
	}
}

</script>