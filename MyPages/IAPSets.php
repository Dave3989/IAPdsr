<?php

// foreach invalid in group item box when returning after completed item 
// TODO multiple of an item
// TODO part description not showing


$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";


if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Sets with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("141") < 0) {
	return;
}

if (isset($_REQUEST['action'])
and $_REQUEST['action'] == "p826ret") {
	$iapSetItem = strtoupper($_REQUEST['setitem']);
	$iapSetDesc = ucwords($_REQUEST['setdesc']);
	$iapSetSupp = $_REQUEST['SUPPID'];
	$iapSetCost = $_REQUEST['setcost'];
	$iapSetPrice = $_REQUEST['setprice'];

	if ($_REQUEST['ADDSETITEM'] == "Y") {
		if ($_REQUEST['snewitemsupp'] == "CO") {
			$iapX = IAP_Build_New_Row(array("table" => "ctlg"));
			$iapC = $iapX[0];
			$iapC['cat_company'] = $_REQUEST['CoId'];
			$iapX = IAP_Build_New_Row(array("table" => "prc"));
			$iapP = $iapX[0];
			$iapP['prc_company'] = $_REQUEST['CoId'];
		} else {
			$iapX = IAP_Build_New_Row(array("table" => "supcat"));
			$iapC = $iapX[0];
			$iapC['cat_supplier_id'] = $_REQUEST['snewitemsupp'];
			$iapX = IAP_Build_New_Row(array("table" => "supprc"));
			$iapP = $iapX[0];
			$iapP['prc_supplier_id'] = $_REQUEST['snewitemsupp'];
		}
		$iapX = IAP_Build_New_Row(array("table" => "inv"));
		$iapI = $iapX[0];

		$iapC['cat_item_code'] = $iapSetItem;
		$iapC['cat_description'] = $iapSetDesc;
		$iapC['cat_supplier'] = "";
		$iapC['cat_active'] = "Y";
		$iapC['cat_set'] = "Y";
		$iapC['cat_changed'] = date("Y-m-d");
		$iapC['cat_changed_by'] = $_REQUEST['IAPUID'];
		if ($_REQUEST['snewitemsupp'] == "CO") {
			$iapRet = IAP_Update_Data($iapC, "ctlg");
		} else {
			$iapRet = IAP_Update_Data($iapC, "supcat");
		}
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Adding set item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapI['inv_company'] = $_REQUEST['CoId'];
		$iapI['inv_item_code'] = $iapSetItem;
		$iapI['inv_on_order'] = 0;
		$iapI['inv_on_hand'] = 0;
		$iapI['inv_min_onhand'] = 0;
		$iapI['inv_changed'] = date("Y-m-d");
		$iapI['inv_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapI, "inv");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Adding set item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapP['prc_company'] = $_REQUEST['CoId'];
		$iapP['prc_item_code'] = $iapSetItem;
		$iapP['prc_effective_until'] = '2099-12-31';
		$iapP['prc_effective'] = "2010-01-01";
		$iapP['prc_cost_unit'] = $iapSetCost;
		$iapP['prc_units'] = $_REQUEST['snewitemunits'];
		$iapP['prc_cost'] = $iapP['prc_cost_unit'] * $iapP['prc_units'];
		$iapP['prc_price'] = $iapSetPrice;
		$iapP['prc_cat_code'] = "cat027";
		$iapP['prc_prev_cost'] = 0;
		$iapP['prc_prev_units'] = 0;
		$iapP['prc_prev_cost_units'] = 0;
		$iapP['prc_prev_price'] = 0;
		$iapP['prc_changed'] = date("Y-m-d");
		$iapP['prc_changed_by'] = $_REQUEST['IAPUID'];
		if ($_REQUEST['snewitemsupp'] == "CO") {
			$iapRet = IAP_Update_Data($iapP, "prc");
		} else {
			$iapRet = IAP_Update_Data($iapP, "supprc");
		}
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Adding set price [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		echo "<span class=iapSuccess>Set item ".$iapSetItem." added.</span><br>";
	}

	$iapGrpCost = $_REQUEST['grpcost'];
	$iapGrpPrice = $_REQUEST['grpprice'];
	if ($iapSetCost <= 0) {
		$iapSetCost = $iapGrpCost;
	}
	$iapCostPercent = $iapGrpCost / $iapSetCost;
	$iapCostPercent = round($iapCostPercent, 4);
	if ($iapSetPrice <= 0) {
		$iapSetPrice = $iapGrpPrice;
	}
	$iapPricePercent = $iapGrpPrice / $iapSetPrice;
	$iapPricePercent = round($iapPricePercent, 4);
	$iapData = $_REQUEST['IAPDATA'];
	$iapItems = explode("|", $iapData);
	foreach($iapItems as $I) {
		$iapGroup = explode("~",$I);
		$iapGrpItem = $iapGroup[0];
		$iapGrpIDesc = $iapGroup[1];
		$iapGrpIQty = $iapGroup[2];
		$iapGrpICost = $iapGroup[3];
		$iapGrpICostExt = $iapGroup[4];
		$iapGrpICostExt = str_replace("~", "", $iapGrpICostExt);
// item percent = this item book cost / total group book cost
		$iapGrpIBuy = round($iapGrpICostExt / $iapGrpCost, 4);
		$iapGrpIPrice = $iapGroup[5];
		$iapGrpIPriceExt = $iapGroup[6];
		$iapGrpIPriceExt = str_replace("~", "", $iapGrpIPriceExt);
// item percent = this item book price / total group book price
		$iapGrpISell = round($iapGrpIPriceExt / $iapGrpPrice, 4);
		if ($_REQUEST['snewitemsupp'] == "CO") {
			$iapS = (array) IAP_Build_New_Row(array("table" => "cset"));
			$iapSet = $iapS[0];
			$iapSet['set_company'] = $_REQUEST['CoId'];		
		} else {
			$iapS = (array) IAP_Build_New_Row(array("table" => "supset"));
			$iapSet = $iapS[0];
			$iapSet['set_supplier'] = $_REQUEST['SUPPID'];		
		}

		$iapSet['set_item_code'] = $iapSetItem;
		$iapSet['set_part_item'] = $iapGrpItem;
		$iapSet['set_part_description'] = $iapGrpDesc;
		$iapSet['set_part_quantity'] = $iapGrpIQty;
		$iapSet['set_buy_percent'] = $iapGrpIBuy;
		$iapSet['set_sell_percent'] = $iapGrpISell;
		$iapSet['set_changed'] = date("Y-m-d");
		$iapSet['set_changed_by'] = $_REQUEST['IAPUID']; 
		if ($_REQUEST['snewitemsupp'] == "CO") {
			$iapRet = IAP_Update_Data($iapSet, "cset");
		} else {
			$iapRet = IAP_Update_Data($iapSet, "supset");
		}
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Adding group item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<span class=iapSuccess>Group Item ".$iapGrpItem." Added with buy percent of ".$iapGrpIBuy." and sell percent of ".$iapGrpISell."</span><br>";
	}
	$iapMasterItem = IAP_Get_Catalog_Only($iapSetItem, $_REQUEST['SUPPID']);
	$iapMasterItem['cat_set'] = "Y";
	$iapRet = IAP_Update_Data($iapMasterItem, "supcat");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR Updating item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	echo "<span class=iapSuccess>Update complete for set ".$iapSetItem.".</span><br><br>";

} else {
	$iapOrigAction = "NEW";

	if (IAP_Remove_Savearea("IAP826CS") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the Sale savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapSetItems = array();
	$iapSet = array("setItem" => "", "setDesc" => "", "setCost" => 0, "setGrpValue" => 0, "setGroup" => $iapSetItems);

	$iapRet = IAP_Create_Savearea("IAP826CS", $iapSale, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "inline";
	$DivShow = "none";
}

require_once("IAPGetItemLists.php");
$iapItemLists = IAP_Get_Item_Lists();
$sCodes = $iapItemLists[0];
$sDescs = $iapItemLists[1];

$iapSuppliers = IAP_Get_Supplier_List();
if ($iapSuppliers < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve supplierss. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
if ($iapSuppliers != NULL) {
	$iapSuppOpts = "";
	foreach($iapSuppliers as $iapS) {
		$iapSNm = str_replace('"', '', $iapS['supp_name']);
		$iapSuppOpts = $iapSuppOpts."<option value='".$iapS['supp_id']."'>".$iapSNm."</option>";
	}
	$c = "";
	foreach($iapSuppliers as $iapS) {
		$iapSuppList = $iapSuppList.$c.'{"label": "'.$iapSNm.'", "sname": "'.$iapSNm.'", "cd": "'.$iapS['supp_id'].'"}';
		$c = ",";
	}
}

$iapReadOnly = IAP_Format_Heading("Set Creation");

?>

<p style='text-indent:50px; width:100%'><br>

<form name='isetform' action='?action=p826ret' method='POST' onkeypress='stopEnterSubmitting(window.event)'>

<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">

<tr><td style="width:3%;"> </td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>
<tr><td style="width:3%;"> </td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>

<tr>
	<td style="width:3%;"></td><td style="width:12%;">Set Item Code:</td>
	<td style="width: 85%;">
		<input tabindex='1' type='text' id=setitem name='setitem' size=40 maxlength=50 value='<?php echo $iapSet['set_item']; ?>' autofocus>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type='button' class=iapButton name='addsetitem' id='addsetitem' value='Add As A New Item' onclick='sAddSetItem(); return false;' />
	</td>
</tr>

<tr><td style="width:3%;"></td><td style="width:12%;">
	<label for="setdesc" class=iapFormLabel id=setdesclbl>Description:</label></td>
	<td style="width:85%;">
		<input readonly  tabindex='2' maxlength='100' size='50' name='setdesc' id='setdesc' value=<?php echo $iapSet['set_desc']; ?>>
	</td>
</tr>

<tr><td style="width:3%;"></td><td style="width:12%;"><span class=iapFormLabel>Set Cost:</span></td>
	<td style="width:85%;">
		<input readonly tabindex='3' style="text-align:right;" maxlength="15" size="15" name="setcost" id="setcost" 
			value="<?php echo number_format($iapSet['set_set_cost'], 2, '.', ''); ?>"> 
		&nbsp&nbsp&nbsp&nbsp&nbsp
		<span class=iapFormLabel>Set Price:</span>
		<input readonly tabindex='4' style="text-align:right;" maxlength="15" size="15" name="setprice" id="setprice" 
			value="<?php echo number_format($iapSet['set_set_price'], 2, '.', ''); ?>"> 
</td></tr>
</table>

<div id="iapNewItem" style="text-align: left; display: none;">
	<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
	<tr><td style="width:3%;"></td>
	<td style="width:12%;"><label class=iapFormLabel id=snewitemunitslbl>Units In Package:</label></td>
	<td style="width:85%;">
		<input  tabindex='5' type='number' maxlength="10" size="10" name="snewitemunits" id="snewitemunits" step="1">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label class=iapFormLabel id=snewitemsuplbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Supplier:</label>&nbsp;
		<select tabindex="6" name="snewitemsupp" id="snewitemsups" size='1' onblur="sSaveSetSupp();">
			<option value='---'>Select A Supplier</option>
			<option value='CO'>My Company</option>
			<?php echo $iapSuppOpts; ?>
		</select>
	</td></tr>
	</table>
</div>


<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">

<tr><td style="width:3%;">&nbsp;</td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>
<tr><td colspan="2"></td>
	<td style="width:85%;">
		<span id=sitemerror class=iapError> </span>
	</td>
</tr>

<tr><td style="width:3%;">&nbsp;</td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>
<tr><td style="width:3%;">&nbsp;</td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>

<tr><td colspan="3">
	<span class=iapFormLabel style="font-weight:bold; text-decoration:underline;">Enter the Items Making Up This Set</span>
</td></tr>

<tr><td style="width:3%;">&nbsp;</td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>
<tr><td style="width:3%;">&nbsp;</td><td style="width:12%;"> </td><td style="width: 85%;"></td></tr>

<tr><td style="width:3%;"></td><td style="width:12%;">
	<label for="sgrpitem" class=iapFormLabel id=sgrpitemlbl>Item Code:</label></td>
	<td style="width:85%;">
		<input tabindex='10' size='50' name='sgrpitem' id='sgrpitem'>
	</td>
</tr>

<tr><td style="width:3%;"></td><td style="width:12%;">
	<label for="sgrpdesc" class=iapFormLabel id=sgrpdesclbl>Description:</label></td>
	<td style="width:85%;">
		<input tabindex='11' maxlength='100' size='50' name='sgrpdesc' id='sgrpdesc'>
	</td>
</tr>

<tr><td style="width:3%;"></td><td style="width:12%;">
	<label class=iapFormLabel id=sgrpqtylbl>Quantity in Set:</label></td>
	<td style="width:85%;">
		<input tabindex='12' type='number' tabindex="29" maxlength="10" size="10" name="sgrpqty" id="sgrpqty">
	</td>
</tr>

<tr><td colspan="3" style="text-align:center;">
	<span class=iapFormLabel>
	<input class=iapButton type='button' tabindex='13' name='sRecItem' id='sRecItem' value='Record This Item' onclick='sRecordItem(); return false;'></span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input class=iapButton type='button' tabindex='14' name='sClearItem' id='sClearItem' value='Clear This Item&apos;s Data' onclick='sClrGrpData(); return false;'>
</td></tr>

<tr><td style="width:3%;"></td><td style="width:12%;"></td><td style="width:85%;"></td></tr>
<tr><td style="width:3%;"></td><td style="width:12%;"></td><td style="width:85%;"></td></tr>

<tr><td colspan="3" style="text-align:center;">
<span class=iapWarning>(WARNING: Do NOT use the Submit button until all items have been recorded)</span>
</td></tr>

<tr><td style="width:3%;"></td><td style="width:12%;"></td><td style="width:85%;"></td></tr>
<tr><td style="width:3%;"></td><td style="width:12%;"></td><td style="width:85%;"></td></tr>

<tr><td style="width:3%;"></td><td style="width:12%;"><span class=iapFormLabel>Group Cost:</span>
	</td><td style="width:85%;">
		<input readonly style="text-align:right;" maxlength="15" size="15" name="grpcost" id="grpcost" 
			value="<?php echo number_format($iapSet['set_part_cost'], 2, '.', ''); ?>"> 
		&nbsp&nbsp&nbsp&nbsp&nbsp
		<span class=iapFormLabel>Group Price:</span>
		<input readonly style="text-align:right;" maxlength="15" size="15" name="grpprice" id="grpprice" 
			value="<?php echo number_format($iapSet['set_part_price'], 2, '.', ''); ?>"> 
</td></tr>
</table>

<br><br>

<fieldset style='border: 1px solid #000; top: 5px; right: 5px; bottom: 5px; left: 5px;'>
&nbsp;&nbsp;<span style="text-decoration: underline;">Items in Group</span>
&nbsp;&nbsp;&nbsp;<span class=iapWarning>(Don't Forget To Click on Submit When All Items Have Been Recorded!)</span><br>
<br>

<table id='iapGroupTbl' class=iapTable>
<tr>
<td width='1%'></td>
<td width='1%'></td>
<td width='2%'></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Item Code</span></td>
<td width='40%' class=iapFormLabel><span style='text-decoration: underline;'>Description</span></td>
<td width='5%' class=iapFormLabel><span style='text-decoration: underline;'>Qty</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Bk Cost</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Ext Cost</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Bk Price</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Ext Price</span></td>
<td width='1%'></td>
</tr>

<?php
	$iapItems = $iapSet['setGroup'];
	$sRows = 0;
	foreach($iapItems as $iapI) {
		$sRows = $sRows + 1;
		$iapColumns = explode("~", $iapI);
		echo "<tr id='GroupItem<?php echo strval($sRows); ?>'>";
		echo "<td width='1'><span style='color:darkgreen;>v</span>";
		echo "<td width='1%'></td>";
		echo "<td width='2%'><img src='".$_REQUEST['IAPUrl']."/MyImages/Icons/DeleteRedSM.png' onclick='sDelItem(".$sRows."); return(false);'>&nbsp;&nbsp;</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[0]."</td>";
		echo "<td width='40%' class=iapFormLabel>".$iapColumns[1]."</td>";
		echo "<td width='5%' class=iapFormLabel>".$iapColumns[2]."</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[3]."</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[4]."</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[5]."</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[6]."</td>";
		echo "<td width='1%'><input type='hidden' id='recsid".strval($sRows)."' value='|".strval($iapColumns[7])."|'></td>";
		echo "</tr>";
	}
?>
</table>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/Icons/Delete_IconSM.png'><span style='vertical-align: middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clicking on this symbol next to a row removes the row</span><br>
</fieldset>
<br><br>


<span style='text-align:center;'>
<input class=iapButton tabindex='20' type='submit' name='ssubmit' id='ssubmit' value='Submit' onclick='return sSendForm();'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<button class=iapButton tabindex='21' name='sclear' id='sclear' onclick='sClearForm(); return(false);'>Clear</button>
</span>




<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type="hidden" name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name='IAPDATA' id="IAPDATA" value="">
<input type="hidden" name="SUPDATETYPE" id="SUPDATETYPE" value="">
<input type="hidden" name="SNEWITEMINFO" id="SNEWITEMINFO" value="">
<input type="hidden" name="STHISITEMSTATUS" id="STHISITEMSTATUS" value="">
<input type="hidden" name="STHISITEMSOURCE" id="STHISITEMSOURCE" value="">
<input type="hidden" name="SUPPID" id="SUPPID" value="">
<input type="hidden" name="SETITEMCD" id="SETITEMCD" value="">
<input type="hidden" name="ADDSETITEM" id="ADDSETITEM" value="N">
<input type="hidden" name="SETCOST" id="SETCOST" value="">
<input type="hidden" name="SETPRICE" id="SETPRICE" value="">
<input type="hidden" name="GROUPCOST" id="GROUPCOST" value="">
<input type="hidden" name="GROUPPRICE" id="GROUPPRICE" value="">
<input type="hidden" name="SIAPURL" id="SIAPURL" value="<?php echo $_REQUEST['IAPUrl']; ?>">

</form>
</p>

<script src="<?php echo $_REQUEST['LHCUrl']; ?>Ajax/number_format.js" type="text/javascript"></script>

<script type="text/javascript">
<?php

require_once($_REQUEST['LHCPath']."IAP/MyJS/NonJSMin/JSSetMaint.js");
// require_once($_REQUEST['LHCPath']."IAP/MyJS/JSSetMaint.min.js");
?>

var sItemList = [<?php echo $sCodes; ?>];
var sDescList = [<?php echo $sDescs; ?>];
</script>
