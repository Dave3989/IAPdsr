<?php


$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";


if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Catalog Maintenance with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("141") < 0) {
	return;
}

if ($_REQUEST['action'] == 'selected') {

	IAP_Remove_Savearea("IAP141CM", $_REQUEST['IAPUID']);

	if ($_REQUEST['debugme'] == "Y") {
		echo "......savearea does not exist so build it.<br>";
	}

	if (empty($_REQUEST['item'])) {
		echo "<span class=iapError>IAP INTERNAL ERROR: No items passed. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapItem = IAP_Get_Catalog($_REQUEST['item']);
	if ($iapItem < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapItem['status'] == 'NEW') {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapOrigAction = $_REQUEST['action'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key IAP141.<br>";
	}

	$iapRet = IAP_Create_Savearea("IAP141CM", $iapItem, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//	$DivSelect = "none";
	$DivShow = "inline";
	
} elseif ($_REQUEST['action'] == 'p141retB') {
// Return from submit

// get catalog
	$iapItem = (array) IAP_Get_Savearea("IAP141CM", $_REQUEST['IAPUID']);
	if (empty($iapItem)) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}
	if (!(empty($_REQUEST['IITEMCD']))) {
		$iapICd = $_REQUEST['IITEMCD'];
		$iapItem = IAP_Get_Catalog($iapICd);
		if ($iapItem < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected item from the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
//		$iapItem['status'] = "EXISTING";
	}
	$iapItem['cat_hold_item'] = $iapItem['cat_item_code'];
	$iapItem['inv_hold_on_hand'] = $iapItem['inv_on_hand'];

	$iapPageError = 0;
	$iapChanged = "N";
	$iapEffChanged = "N";
	$iapItemChanged = "N";
	$iapOHChanged = "N";
	$iapPrcChanged = "N";

	if ($_REQUEST['iretire'] == 'Retire Item') {
		$iapItem['cat_active'] = "N";
		$iapItem['cat_changed'] = date("Y-m-d");
		$iapItem['cat_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapItem, "ctlg");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<br><span class=iapSuccess>Item ".$iapItem['item_code']." has been marked as retired.</span><br>";
	} else {

		require_once("IAPValidators.php");

		if (isset($_REQUEST['icode'])) {
			$iapRet = IAP_Validate_Nonblank($iapItem['cat_item_code'], $_REQUEST['icode']);
			 if ($iapRet['Changed'] == "Y") {
				$iapItem['cat_item_code'] = $iapRet['Value'];
				$iapItemChanged = "Y";
			 } elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>Item Code cannot be blank!</span><br>";
				$iapPageError = 1;
			 }
		} elseif (empty($iapItem['cat_item_code'])) {
			echo "<span class=iapError>Item Code cannot be blank!</span><br>";
			$iapPageError = 1;
		}
		if (isset($_REQUEST['idesc'])) {
			$iapRet = IAP_Validate_Nonblank($iapItem['cat_description'], $_REQUEST['idesc']);
			if ($iapRet['Changed'] == "Y") {
				$iapItem['cat_description'] = $iapRet['Value'];
				$iapItemChanged = "Y";
			} elseif ($iapRet['Error'] == "1") {
				echo "<span class=iapError>catalog item Name cannot be blank!</span><br>";
				$iapPageError = 1;
			}
		} elseif (empty($iapItem['cat_description'])) {
			echo "<span class=iapError>catalog item Name cannot be blank!</span><br>";
			$iapPageError = 1;
		}
		if (isset($_REQUEST['isupplier'])
		and	$iapItem['cat_supplier'] != $_REQUEST['isupplier']) {
			$iapItem['cat_supplier'] = $_REQUEST['isupplier'];
		        $iapItemChanged = "Y";
		}

// Check inventory fields
		$iapOHChanged = "N";

		if (isset($_REQUEST['ionhand'])
		and	$iapItem['inv_on_hand'] != $_REQUEST['ionhand']) {
			$iapItem['inv_on_hand'] = $_REQUEST['ionhand'];
		        $iapOHChanged = "Y";
		}
		if (isset($_REQUEST['iminonhand'])
		and	$iapItem['inv_min_onhand'] != $_REQUEST['iminonhand']) {
			$iapItem['inv_min_onhand'] = $_REQUEST['iminonhand'];
		        $iapOHChanged = "Y";
		}

// Check price fields
		$iapPriceChanged = "N";

// need some value
		if (isset($_REQUEST['iselcat'])
		and	$iapItem['prc_cat_code'] != $_REQUEST['iselcat']) {
			$iapItem['prc_cat_code'] = $_REQUEST['iselcat'];
		       $iapPriceChanged = "Y";
		}
		if (isset($_REQUEST['icost'])
		and $iapItem['prc_cost'] != $_REQUEST['icost']) {
			$iapItem['prc_cost'] = $_REQUEST['icost'];
		       $iapPriceChanged = "Y";
		}
		if (isset($_REQUEST['iunits'])
		and $iapItem['prc_units'] != $_REQUEST['iunits']) {
			$iapItem['prc_units'] = $_REQUEST['iunits'];
			$iapPriceChanged = "Y";
		}
		if (isset($_REQUEST['iprice'])
		and $iapItem['prc_price'] != $_REQUEST['iprice']) {
			$iapItem['prc_price'] = $_REQUEST['iprice'];
			$iapPriceChanged = "Y";
		}
		if ($iapPriceChanged == "Y") {	
			if ($iapItem['status'] != "NEW"
			and $iapItem['SOURCE'] != "COMPANY") {
				echo "<span class=iapError>Pricing cannot be changed for items in the supplier's catalog!</span><br>";
				echo "<span class=iapError>Price change ignored!</span><br>";
				$iapPriceChanged = "N";
			} else {
				if (isset($_REQUEST['ieffdt'])) {
					$iapRet = IAP_Validate_Date($iapItem['prc_effective'], $_REQUEST['ieffdt']);
					if ($iapRet['Changed'] == "Y") {
						$iapItem['prc_effective'] = $iapRet['Value'];
						$iapEffChanged = "Y";
					} elseif ($iapRet['Error'] == "1") {
						echo "<span class=iapError>Price Effective Date cannot be blank!</span><br>";
						$iapPageError = 1;
					} elseif ($iapRet['Error'] == "2") {
						echo "<span class=iapError>Price Effective Date is invalid!</span><br>";
						$iapPageError = 1;
					}
				} elseif (empty($iapItem['prc_effective'])) {
				        echo "<span class=iapError>Price Effective Date cannot be blank!</span><br>";
					$iapPageError = 1;
				} elseif ($iapItem['prc_effective'] < date("Y-m-d", strtotime($_REQUEST['IAPEFFDT']))) {
					if ($_REQUEST['IAPEFFTYPE'] == "T") {
						echo "<span class=iapError>Price Effective Date cannot be less than today's date!</span>";				
					} else {
						echo "<span class=iapError>Price Effective Date cannot be less than the date of the next price change on record.</span>";
					}
					$iapPageError = 1;
				}
			}
		}

		if ($iapPageError == 0) {
			require_once(ABSPATH."MyPages/IAPCreateCat.php");
			$iapRet = IAP_Create_Cat($iapItem, $iapItemChanged, $iapOHChanged, $iapPriceChanged);
			if ($iapItem['status'] == "NEW") {
				$iapItemChg = "added";			
			} else {
				$iapItemChg = "changed";
			}
			$iapOrigAction = "U";
			$iapItem['status'] = "EXISTING";
		}
	}

	$iapRet = IAP_Update_Savearea("IAP141CM", $iapItem, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($iapPageError == 0) {
		if ($iapItemChanged == "Y"
		or $iapOHChanged == "Y") {
			echo "<br><span class=iapSuccess style='font-weight:bold;'>Item ".$iapItem['item_code']." was successfully ".$iapItemChg.".</span><br>";
		}
		if ($iapPriceChanged == "Y") {
			echo "<span class=iapSuccess style='font-weight:bold;'>Pricing for item ".$iapItem['item_code']." was successfully ".$iapItemChg.".</span><br>";
		}
		if ($iapItemChanged == "Y"
		or $iapOHChanged == "Y"
		or $iapPriceChanged == "Y") {
			echo "<br>";
		}
	}

//	$DivSelect = "none";
	$DivShow = "inline";	

} else {

	if ($_REQUEST['origaction'] == "U") {
		echo "<span class=iapSuccess>Previous Update Successful!</span><br><br>";
	}

	if (IAP_Remove_Savearea("IAP141CM") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the catalog item savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapC = IAP_Build_New_Row(array("table" => "ctlg"));
	$iapI = IAP_Build_New_Row(array("table" => "inv"));
	$iapP = IAP_Build_New_Row(array("table" => "prc"));
	$iapP[0]['prc_effective'] = date("Ymd");
	$iapItem = array_merge($iapC[0], $iapI[0], $iapP[0]);

	$iapRet = IAP_Create_Savearea("IAP141CM", $iapItem, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapPageError = 2; // Just so retire msg doesn't show but not fix errors msg 

	$iapOrigAction = "N";

	$DivSelect = "inline";
	$DivShow = "none";
}

require_once("IAPGetItemLists.php");
$iapItemLists = IAP_Get_Item_Lists();
if (is_null($iapItemLists)) {
	$_REQUEST['CatsOK'] = "N";
	$iapItemList = " ";
	$iapDescList = " ";
	$iapPriceHistory = " ";

} else {
	$_REQUEST['CatsOK'] = "Y";
	$iapItemList = $iapItemLists[0];
	$iapDescList = $iapItemLists[1];
	$iapSelEna = "readonly";

	//	$iapLoDate = date('Y-m', strtotime('-18 months'))."-01";	// 18 months
	//	$iapLoDate = date('Y-m', strtotime('-24 months'))."-01";	// 24 months
		$iapLoDate = "2010-01-01";

	if ($iapItem['status'] != "NEW") {

		if (!(empty($iapItem['cat_item_code']))) {
	//		$iapPriceHistory = IAP_Get_Price_History($iapItem['cat_item_code'], $iap24mo);
			$iapPriceHistory = IAP_Get_Price_History($iapItem['cat_item_code'], $iapLoDate);
			if ($iapPriceHistory < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive price history for selected item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		} else {
			$iapPriceHistory = array();
		}

		if ($iapEffChanged == "Y") {
			$iapNextEff = $iapItem['prc_effective'];
		} else {
			$iapNextEff = date('Y-m-d');
			if ($iapPriceHistory[0]['prc_effective'] > $iapNextEff) {
				$iapLastEff = $iapPriceHistory[0]['prc_effective'];
				$iapNextEff = date('m/d/Y', strtotime($iapPriceHistory[0]['prc_effective']."+ 1 day"));
			}
		}
		$iapNextEff = date("m/d/Y", strtotime($iapNextEff));

	// Get purchases for the last X months
		if (!(empty($iapItem['cat_item_code']))) {
			$iapPurchaseHistory = IAP_Get_PurDet_For_Item($iapItem['cat_item_code'], $iapLoDate);
			if ($iapPurchaseHistory < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive purchase detail for selected item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapPurchaseHistory == NULL) {
				$iapPurchaseHistory[0]['purdet_date'] = $iapLoDate;
			}
	// Get sales for the last X months
			$iapSalesHistory = IAP_Get_SaleDet_For_Item($iapItem['cat_item_code'], $iapLoDate);
			if ($iapSalesHistory < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive sales detail for selected item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapSalesHistory == NULL) {
				$iapSalesHistory[0]['sale_date'] = $iapLoDate;
			}
		} else {
			$iapPurchaseHistory[0]['purdet_date'] = $iapLoDate;
			$iapSalesHistory[0]['sale_date'] = $iapLoDate;
		}

		$iP = 0;
		$iS = 0;
		$iapPSHistory = array();
		$iapDone = "N";
		while($iapDone == "N") {
			if ($iP == count($iapPurchaseHistory)) {
				$iapPurchaseHistory[$iP]['purdet_date'] = $iapLoDate;
			}
			if ($iS == count($iapSalesHistory)) {
				$iapSalesHistory[$iS]['sale_date'] = $iapLoDate;
			}
			if ($iapPurchaseHistory[$iP]['purdet_date'] == $iapLoDate
			and $iapSalesHistory[$iS]['sale_date'] == $iapLoDate) {
				$iapDone = "Y";
			} else {
				if ($iapPurchaseHistory[$iP]['purdet_date'] > $iapSalesHistory[$iS]['sale_date']) {
					$iapPSHistory[] = array("P", $iapPurchaseHistory[$iP]['purdet_purid'], $iapPurchaseHistory[$iP]['purdet_item'], $iapPurchaseHistory[$iP]['cat_description'], $iapPurchaseHistory[$iP]['purdet_date'], $iapPurchaseHistory[$iP]['purdet_quantity'], $iapPurchaseHistory[$iP]['purdet_cost'], $iapPurchaseHistory[$iP]['purdet_ext_cost'], $iapPurchaseHistory[$iP]['pur_vendor']."-".$iapPurchaseHistory[$iP]['pur_order'], 0, 0);
					$iP = $iP + 1;
				} elseif ($iapPurchaseHistory[$iP]['purdet_date'] < $iapSalesHistory[$iS]['sale_date']) {
					$iapPSHistory[] = array("S", $iapSalesHistory[$iS]['saledet_sid'], $iapSalesHistory[$iS]['saledet_item_code'], $iapSalesHistory[$iS]['cat_description'], $iapSalesHistory[$iS]['sale_date'], $iapSalesHistory[$iS]['saledet_quantity'], $iapSalesHistory[$iS]['saledet_lot_cost'], $iapSalesHistory[$iS]['saledet_total_cost'], $iapSalesHistory[$iS]['cust_name']."-".$iapSalesHistory[$iS]['pe_sponsor'], $iapSalesHistory[$iS]['saledet_price'], $iapSalesHistory[$iS]['saledet_total_price']);
					$iS = $iS + 1;
				} else {
					if ($iapPurchaseHistory[$iP]['purdet_item'] < $iapSalesHistory[$iS]['saledet_item_code']) {
					$iapPSHistory[] = array("P", $iapPurchaseHistory[$iP]['purdet_purid'], $iapPurchaseHistory[$iP]['purdet_item'], $iapPurchaseHistory[$iP]['cat_description'], $iapPurchaseHistory[$iP]['purdet_date'], $iapPurchaseHistory[$iP]['purdet_quantity'], $iapPurchaseHistory[$iP]['purdet_cost'], $iapPurchaseHistory[$iP]['purdet_ext_cost'], $iapPurchaseHistory[$iP]['pur_vendor']."-".$iapPurchaseHistory[$iP]['pur_order'], 0, 0);
						$iP = $iP + 1;
					} elseif ($iapPurchaseHistory[$iP]['purdet_item'] > $iapSalesHistory[$iS]['saledet_item_code']) {
					$iapPSHistory[] = array("S", $iapSalesHistory[$iS]['saledet_sid'], $iapSalesHistory[$iS]['saledet_item_code'], $iapSalesHistory[$iS]['cat_description'], $iapSalesHistory[$iS]['sale_date'], $iapSalesHistory[$iS]['saledet_quantity'], $iapSalesHistory[$iS]['saledet_lot_cost'], $iapSalesHistory[$iS]['saledet_total_cost'], $iapSalesHistory[$iS]['cust_name']."-".$iapSalesHistory[$iS]['pe_sponsor'], $iapSalesHistory[$iS]['saledet_price'], $iapSalesHistory[$iS]['saledet_total_price']);
						$iS = $iS + 1;
					} else {
					$iapPSHistory[] = array("P", $iapPurchaseHistory[$iP]['purdet_purid'], $iapPurchaseHistory[$iP]['purdet_item'], $iapPurchaseHistory[$iP]['cat_description'], $iapPurchaseHistory[$iP]['purdet_date'], $iapPurchaseHistory[$iP]['purdet_quantity'], $iapPurchaseHistory[$iP]['purdet_cost'], $iapPurchaseHistory[$iP]['purdet_ext_cost'], $iapPurchaseHistory[$iP]['pur_vendor']."-".$iapPurchaseHistory[$iP]['pur_order'], 0, 0);
						$iP = $iP + 1;
					$iapPSHistory[] = array("S", $iapSalesHistory[$iS]['saledet_sid'], $iapSalesHistory[$iS]['saledet_item_code'], $iapSalesHistory[$iS]['cat_description'], $iapSalesHistory[$iS]['sale_date'], $iapSalesHistory[$iS]['saledet_quantity'], $iapSalesHistory[$iS]['saledet_lot_cost'], $iapSalesHistory[$iS]['saledet_total_cost'], $iapSalesHistory[$iS]['cust_name']."-".$iapSalesHistory[$iS]['pe_sponsor'], $iapSalesHistory[$iS]['saledet_price'], $iapSalesHistory[$iS]['saledet_total_price']);
						$iS = $iS + 1;
					}
				}
			}
		}
	}
}

$iapCategories = IAP_Get_Codes();
if ($iapCategories < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve categories. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}

if ($iapCategories == NULL) {
	$iapCatOpts = "No Categories Have Been Provided!";
} else {
	$iapCatOpts = "<option value='---'>Select An Category For This Item...</option>";
	foreach ($iapCategories as $iapCat) {
		$iapCatOpts = $iapCatOpts."<option value='".$iapCat['code_code']."'";
		if ($iapCat['code_code'] == $iapItem['prc_cat_code']) {
			$iapCatOpts = $iapCatOpts." selected";
		}
		$iapCatOpts = $iapCatOpts.">".$iapCat['code_value']."</option>";
	}
}

// Check if item is obsolete aka not active
if ($iapItem['cat_active'] == "N") {
	$iapReadOnly = "readonly";
}

if ($iapPageError == 1) {
	echo "<span class=iapError style='font-weight:bold;'>Errors were detected. Please fix them and submit again.</span><br><br>";
}

$iapReadOnly = IAP_Format_Heading("Catalog of Items");

$h = IAP_Do_Help(3, 141, 1); // level 3, page 141, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='ichoose' style='display:<?php echo $DivSelect; ?>;' onfocus='getTabbableFields(this);'>
<p style='text-indent:50px; width:100%'>


<form name='iselform' action='?action=p141retA&origaction=initial' method='POST' onsubmit='return pNoSubmit();' onkeypress='stopEnterSubmitting(window.event)'>

<?php
	if ($_REQUEST['CatsOK'] == "N") {
		echo "<br><br><span style='padding-left:40px; font-weight:bold; color:red;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You have no items in your catalog.<br>";
		echo "<span style='padding-left:40px; font-weight:bold; color:red;'>Please, click Add A New Catalog Item or import catalog from another source.<br><br>";

	} else {
?>

<span class=iapFormLabel>
	<label for="iDescList">Select an item by its description: </label>
	<input name="iDescList" id="iDescList" size="50"  maxlength="100">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 1); ?> <!-- level 1, page 141, section 2 -->
	<br>
	<label for="iItemList">- OR - by its code: </label>
	<input name="iItemList" id="iItemList" size="50">
	<br>
	<span class=iapSuccess style="padding-left: 50px;">&nbsp;&nbsp;&nbsp;Then click the GO button to see the detail.</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 	&nbsp;<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/LHCGoGreen.jpg' id=iapGo style='width:25px;height:25px;vertical-align:bottom;border-style:none;border:0;' title='iapGo' onclick='iGoClicked()'>
	<br>
	<span class=iapError id="cError" style="padding-left:40px; display:none;">Item cannot be found. Retry or click Add.</span>
	<br>
	<span class=iapError style="display:none;" id="iSelError"> </span>
<?php
	}

	if ($iapReadOnly != "readonly") {
		if ($catsOK == "Y") {
			echo "<label for='iadd'> - OR - </label>";			
		}
		echo "<input type='button' class=iapButton name='iadd' id='iadd' value='Add A New Catalog Item' onclick='iAddClicked()' />";
	}
?>

</span>
</form>
</p>
</div>
<div id='idetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='text-indent:50px; width:100%'>

<form name='idetform' action='?action=p141retB&origaction=<?php echo $iapOrigAction; ?>' method='POST' onkeypress='stopEnterSubmitting(window.event)'>

<table style="text-align: left;" border="1" cellpadding="2" cellspacing="2" height="20px">
<tbody>

<tr style='line-height:150%;'><td style="width: 25%;"></td><td style="width: 75%;"></td></tr>

<?php
if ($iapItem['cat_active'] == "Y") {
	$iapActMsgDiv = "none";
} else {
	$iapActMsgDiv = "inline";
}
?>
<tr style='line-height:200%;'><td colspan="2">
	<div style='display:<?php echo $iapActMsgDiv; ?>;' id=iActMsgDiv>
		<span class=iapFormLabel,iapError style='text-align:center; font-size:115%;'>This item has been retired.</span>
	</div>
</td></tr>
<tr><td colspan="2"></td></tr>

<?php
$iapSuppReadOnly = "";
$iapICReadOnly = "";
$iapSuppMsgDiv = "none";
if ($iapReadOnly != " readonly") {
	if ($iapItem['SOURCE'] == "SUPPLIER") {
		$iapSuppReadOnly = " readonly";
		$i = $iapItem['cat_supplier_id'];
		$nm = $iapSuppliers[$i]['SName'];
		$iapSuppMsgDiv = "inline";
	} else {
		if ($iapItem['status'] != "NEW") {
			$iapICReadOnly = " readonly";
		}
		$iapSuppMsgDiv = "none";
	}
}
?>
<tr style='line-height:150%;'><td colspan="2">
	<div style='display:<?php echo $iapSuppMsgDiv; ?>;' id=iSuppMsgDiv>
		<span class=iapWarning,iapFormLabel style='line-height:200%; text-align:center; font-size:115%;' id=iSuppName>This item has been supplied by <span id=iSuppMsgName><?php echo $nm; ?></span>. Only the inventory balances can be updated</span><br><br>
	</div>
</td></tr>
<tr><td colspan="2"></td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Mfgr Item Code:</span></td>
<td style="width: 75%;">
<?php
	if ($iapItem['status'] == "NEW") {
		$iapFocus = "  autofocus";
	} else {
		$iapFocus = "";
	}
?>
	<input <?php echo $iapReadOnly.$iapSuppReadOnly.$iapICReadOnly; ?> tabindex="1" size="50" maxlength="50" name="icode" id="icode" value="<?php echo $iapItem['cat_item_code']; ?>"<?php echo $iapFocus; ?>>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 2); ?> <!-- level 1, page 141, section 2 -->

<?php
	if ($iapReadOnly == "readonly"
	or empty($iapItem['cat_image_file'])) {
		$d = "none";
	} else {
		$d ="inline";
	}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button class=iapButton id=iImgBtn style='display: <?php echo $d; ?>;' onclick='siShowImage()' value='Show Image' />
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Description:</span></td>
<td style="width: 75%;">
<?php
	if ($iapItem['status'] == "NEW") {
		$iapFocus = "";
	}
?>
	<input <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="2" size="60" maxlength="100" name="idesc" id="idesc" value="<?php echo $iapItem['cat_description']; ?>"<?php echo $iapFocus; ?>>
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Category:</span></td>
<td style="width: 75%;"><select <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="3" name=iselcat id=iselcat size='1'><?php echo $iapCatOpts; ?></select>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 3); ?> <!-- level 1, page 141, section 3 -->
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Supplier:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="4" maxlength="50" size="50" name="isupplier" id="isupplier" value="<?php echo $iapItem['cat_supplier']; ?>">
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Saleable Units On Hand:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly; ?> tabindex="5" maxlength="5" size="5" name="ionhand" id="ionhand" value="<?php echo $iapItem['inv_on_hand']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 4); ?> <!-- level 1, page 141, section 4 -->
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Minimum On Hand:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly; ?> tabindex="5" maxlength="5" size="5" name="iminonhand" id="iminonhand" value="<?php echo $iapItem['inv_min_onhand']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 5); ?> <!-- level 1, page 141, section 5 -->
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Cost To You:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="6" maxlength="10" size="10" name="icost" id="icost" value="<?php echo number_format((float) $iapItem['prc_cost'], 2, '.', ','); ?>" style="align:right;">

</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Units In Package:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="7" maxlength="4" size="4" name="iunits" id="iunits" value="<?php echo $iapItem['prc_units']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 8); ?> <!-- level 1, page 141, section 6 -->
</td></tr>

<tr style='line-height:150%;'>
<td style="width: 25%;"><span class='iapFormLabel'>Price To Customer:</span></td>
<td style="width: 75%;">
	<input <?php echo $iapReadOnly.$iapSuppReadOnly; ?> tabindex="8" maxlength="10" size="10" name="iprice" id="iprice" value="<?php echo number_format((float) $iapItem['prc_price'], 2, '.', ','); ?>" style="align:right;">
</td></tr>

<?php
if ($iapReadOnly == "readonly"
or $iapSuppReadOnly == "readonly") {
	$iapChg = "none";
} else {
	$iapChg = "inline";
}
?>
<div id=iEffDateDiv style="display:<?php echo $iapChg; ?>">
	<tr style='line-height:125%;'>
	<td colspan='2'><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;If the Category, Cost, Units, and/or Price was changed tell us when these changes went into effect.</span></td></tr>

	<tr style='line-height:150%;'>
	<td style="width: 25%;"><span class='iapFormLabel'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Price Effective Date:</span></td>
	<td style="width: 75%;">
		<input tabindex="9" maxlength="10" size="11" name="ieffdt" id="ieffdt" value="<?php echo $iapNextEff; ?>" style="align:right;">
		&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 141, 9); ?> <!-- level 1, page 141, section 6 -->
	</td></tr>
</div>

<tr style='line-height:150%;'><td colspan="2"></td></td></tr>

<tr style='line-height:200%;'><td style="width: 25%;"></td><td style="width: 75%;">
<?php
	if ($iapReadOnly != "readonly") {
		echo "<input tabindex='10' type='submit' name='isubmit' value='Submit'>";
		if ($iapItem['SOURCE'] == "SUPPLIER") {
			$d = "none";
		} elseif ($iapPageError == 0) {
			$d = "none";			
		} else {
			$d = "inline";
		}
		echo "<span style='display:".$d."'  name='iretirefill' id='iretirefill'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		echo "<input style='display:".$d."' tabindex='11' type='submit' name='iretire' id='iretire' value='Retire Item'>";
	}
?>

</td></tr>
</tbody>
</table>

<br>

<?php
if (isset($iapPriceHistory)
and count($iapPriceHistory) > 0) {
	$d = "block";
} else {
	$d = "none";
}
?>

<fieldset id='iapPrcFldset' style='width:100%; border: 1px double #000; top: 5px; right: 5px; bottom: 5px; left: 5px; display:<?php echo $d; ?>;'>
<legend style="width:25%; margin-right:0.5em; padding:0.5em 0.5em; border:1px; font-weight:bold;">
Cost and Price History
</legend>

<table id='iapPriceChgs' style="width:100%">
<tr>
<td width='3%'></td>
<td width='2%'></td>
<td width='3%'></td>
<td width='12%' class=iapTH>Effective&nbsp;&nbsp;<?php IAP_Do_Help(1, 141, 7); // level 1, page 141, section 7 ?></td>
<td width='12%' class=iapTH style='text-align: right;'>Your Cost</td>
<td width='12%' class=iapTH style='text-align: right;'>Units</td>
<td width='12%' class=iapTH style='text-align: right;'>Cost/Unit</td>
<td width='12%' class=iapTH style='text-align: right;'>Selling Price</td>
<td width='3%'></td>
<td width='27%' class=iapTH>Category</td>
<td width='2%'></td>
</tr>

<?php
$iRows = 0;
if ($_REQUEST['CatsOK'] == "N"
or  empty($iapPriceHistory)) {
	echo "<tr id='Item".strval($pRow)."' class='iapTD1'>";
	echo "<td colspan='11'><span name='irollback' id='irollback' style='display:none'> </span></td>";
	echo "</tr>";
} else {
	foreach($iapPriceHistory as $iapP) {
		echo "<tr id='Item".strval($pRow)."' class='iapTD1'>";
		echo "<td width='3%'> </td>";
		echo "<td id='Del".strval($pRow)."' class='iapTD1' width='2%'>";
		$d = "none";
		if ($iRows == 0) {
			if ($iapItem['SOURCE'] != "SUPPLIER"
			and count($iapPriceHistory) > 1 
			and $iapReadOnly != "readonly") {
				$d = "inline";
			}
		}
		echo "<span name='irollback' id='irollback' style='display:".$d."'><img src='MyImages/Icons/DeleteRedSM.png' onclick='pDelSelected(".$iRows."); return(false);'></span>";
		echo "&nbsp;&nbsp;</td>";
		echo "<td width='3%'></td>";
		echo "<td width='12%' style='text-align: right;'>".date("Y-m-d", strtotime($iapP['prc_effective']))."</td>";
		echo "<td width='12%' style='text-align: right;'>".number_format((float) $iapP['prc_cost'], 2, '.', ',')."</td>";
		echo "<td width='12%' style='text-align: right;'>".number_format((float) $iapP['prc_units'], 0, '.', ',')."</td>";
		echo "<td width='12%' style='text-align: right;'>".number_format((float) $iapP['prc_cost_unit'], 2, '.', ',')."</td>";
		echo "<td width='12%' style='text-align: right;'>".number_format((float) $iapP['prc_price'], 2, '.', ',')."</td>";
		echo "<td width='3%'></td>";
		echo "<td width='27%'>".$iapP['code_value']."</td>";
		echo "<td width='2%'></td>";
		echo "</tr>";
		$iRows = $iRows + 1;
	}
}
echo "</table>";

if ($iapItem['SOURCE'] != "SUPPLIER"
and isset($iapPriceHistory)
and count($iapPriceHistory) > 0 
and $iapReadOnly != "readonly") {
	$d = "inline";
} else {
	$d = "none";
}
echo "<span style='display:".$d."' id='irollbackfill'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
echo "<span style='display:".$d."' id='irollbackicon'><img src='".$_REQUEST['IAPUrl']."/MyImages/Icons/Delete_IconSM.png'></span><span style='vertical-align: middle; display:".$d."' id='irollbacktxt'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clicking on this symbol next to a row rolls back the price to the previous price!</span><br>";
?>

</fieldset>

<?php
	if (isset($iapPSHistory)
	and count($iapPSHistory) > 0) {
		$d = "block";
	} else {
		$d = "none";
	}
?>

<br><br>

<fieldset id='iapActFldset' style='width:100%; border: 1px double #000; top: 5px; right: 5px; bottom: 5px; left: 5px; display:<?php echo $d; ?>;'>
<legend style="width:25%; margin-right:0.5em; padding:0.5em 0.5em; border:1px; font-weight:bold;">
Purchases and Sales
</legend>
<span style="margin-left:0.5em;">Click on Purchase or Sale to view its detail.</span>
<table id=ihistory style='width:100%;'>

<tr><td colspan='8'>&nbsp;</td></tr>

<tr style='width:100%'>
<td style='width:1%;' class=iapTH> </td>
<td style='width:8%;' class=iapTH>Type&nbsp;&nbsp;<?php IAP_Do_Help(1, 141, 6); // level 1, page 141, section 6 ?></td>
<td style='width:12%;' class=iapTH>Date</td>
<td style='width:31%;' class=iapTH>Reference</td>
<td style='width:7%; text-align:right;' class=iapTH>Quantity</td>
<td style='width:10%; text-align:right;' class=iapTH>Cost/Unit</td>
<td style='width:10%; text-align:right;' class=iapTH>Total Cost</td>
<td style='width:10%; text-align:right;' class=iapTH>Price/Unit</td>
<td style='width:10%; text-align:right;' class=iapTH>Total Price</td>
<td style='width:1%;' class=iapTH> </td>
</tr>

<?php
	if (!empty($iapPSHistory)) {
		foreach($iapPSHistory as $iapH) {
			echo "<tr>";
			echo "<td style='width:1%;' class=iapTH> </td>";
			echo "<td style='width:8%;'>";
			if ($iapH[0] == "P") {
				echo "<a href='?page_id=208&action=selected&pur=".strval($iapH[1])."'>Purchase</a></td>";
			} else {
				echo "<a href='?page_id=291&action=selected&sale=".strval($iapH[1])."'>Sale</a></td>";
			}
			echo "<td style='width:12%;'>".date("m/d/Y", strtotime($iapH[4]))."</td>";
			echo "<td style='width:31%;'>".$iapH[8]."</td>";
			echo "<td style='width:7%; text-align:right;'>".number_format($iapH[5], 0, '.', ',')."</td>";
			echo "<td style='width:10%; text-align:right;'>".number_format($iapH[6], 2, '.', ',')."</td>";
			echo "<td style='width:10%; text-align:right;'>".number_format($iapH[7], 2, '.', ',')."</td>";
			echo "<td style='width:10%; text-align:right;'>".number_format($iapH[9], 2, '.', ',')."</td>";
			echo "<td style='width:10%; text-align:right;'>".number_format($iapH[10], 2, '.', ',')."</td>";
			echo "<td style='width:1%;' class=iapTH> </td>";
			echo "</tr>";
		}
	}
?>

</table>
</fieldset>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCS" id="LHCS" value="<?php echo  $_REQUEST['UserData']['Suppliers']; ?>">
<input type="hidden" name="IUPDATETYPE" id="IUPDATETYPE" value="">
<input type="hidden" name="IITEMCD" id="IITEMCD" value="">
<input type="hidden" name="IPRICEKEY" id="IPRICEKEY" value="">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type='hidden' name='IAPEFFDT' id='IAPEFFDT' value="">
<input type='hidden' name='IAPEFFTYPE' id='IAPEFFTYPE' value="">
<input type='hidden' name='IAP24mos' id='IAP24mos' value="<?php echo date('Y-m', strtotime('-24 months')).'-01'; ?>">

<input type='hidden' name='IAPSUPPID' id='IAPSUPPID' value="<?php echo strval($iapItem['cat_supplier_id']); ?>">
<input type='hidden' name='IAPCATIMG' id='IAPCATIMG' value="<?php echo $iapItem['cat_image_file']; ?>">
<input type='hidden' name='IAPLODATE' id='IAPLODATE' value="<?php echo $iapLoDate; ?>">
<input type='hidden' name='IAPPATH' id='IAPPATH' value="<?php echo $_REQUEST['IAPPath']; ?>">
<input type='hidden' name='IAPURL' id='IAPURL' value="<?php echo $_REQUEST['IAPUrl']; ?>">
<input type='hidden' name='IAPCATS' id='IAPCATS' value="<?php echo $_REQUEST['CatsOK']; ?>">
</form>
</p>
</div>


<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSShowImage.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSShowImage.min.js");
?>
</script>

<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSCatMaint.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSCatMaint.min.js");
?>

var iItemCodes = [<?php echo $iapItemList; ?>];
var iItemDescs = [<?php echo $iapDescList; ?>];
</script>