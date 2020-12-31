<?php

// TODO process items marked as supply by category and not as inventory nor add to lots

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Purchases with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("208") < 0) {
	return;
};

require_once(ABSPATH."MyPages/IAPExpenseRtns.php");

if ($_REQUEST['action'] == "selected") {

	$iapOrigAction = $_REQUEST['action'];

	if (IAP_Remove_Savearea("IAP208PU") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the purchase savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapPId = $_REQUEST['pur'];
	$iapPurchase = IAP_Get_Purchase($iapPId);
	if ($iapPurchase < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapPurDtl = IAP_Get_PurDet($iapPurchase['pur_id']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving prior purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapItems = array();
	foreach($iapPurDtl as $iapPD) {
		$iapColumns[0] = $iapPD['purdet_item'];
		$iapColumns[1] = $iapPD['purdet_desc'];
		$iapColumns[2] = $iapPD['purdet_quantity'];
		$iapColumns[3] = $iapPD['purdet_cost'];
		$iapColumns[4] = $iapPD['purdet_ext_cost'];
		$iapColumns[5] = $iapPD['purdet_item_source'];
		$iapItems[] = implode("~", $iapColumns);
	}
	$iapPurchase['purdtl'] = $iapItems;

	$iapExps = IAP_Get_Expenses("P", $iapPurchase['pur_id']);
	if ($iapExps < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive expenses for ".$expMod."-".$expId.". [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapPurchase['expenses'] = $iapExps;

	$iapRet = IAP_Create_Savearea("IAP208PU", $iapPurchase, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "block";	

} elseif ($_REQUEST['action'] == 'p208retB') {

	$iapOrigAction = $_REQUEST['action'];

	if ($_REQUEST['PUPDATETYPE'] == "NEW") {

// Destroy any existing savearea and create a new one
		if (IAP_Remove_Savearea("IAP208PU") < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the purchase savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapP = (array) IAP_Build_New_Row(array("table" => "pur"));
		$iapPurchase = $iapP[0];
		$iapPurchase['pur_type'] = "I";
		$iapPurchase['purdtl'] = array();
		$iapPurchase['expenses'] = array();
		$iapRet = IAP_Create_Savearea("IAP208PU", $iapPurchase, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {

// get purchase --- save area has purdtl that was originally entered
		$iapPurchase = (array) IAP_Get_Savearea("IAP208PU", $_REQUEST['IAPUID']);
		if (is_null($iapPurchase)) {
		    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		    return;
		}
		if (!(empty($_REQUEST['POID']))) {
			$iapPId = $_REQUEST['POID'];
			$iapPurchase = IAP_Get_Purchase($iapPId);
			if ($iapPurchase < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapPurchase['status'] = "EXISTING";
		}
	}

	$iapPageError = 0;
	$iapChanged = "N";

	require_once("IAPValidators.php");

	if (isset($_REQUEST['pPurDate'])) {
		$iapRet = IAP_Validate_Date($iapPurchase['pur_date'], $_REQUEST['pPurDate']);
		if ($iapRet['Changed'] == "Y") {
		    $iapPurchase['pur_date'] = $iapRet['Value'];
		    $iapChanged = "Y";
		}
		if ($iapRet['Error'] == 1) {
		    echo "<span class=iapError>Purchase Date cannot be blank!</span><br>";
		    $iapPageError = 1;
		}
	} elseif (empty($iapPurchase['pur_date'])) {
		echo "<span class=iapError>Purchase Date cannot be blank!</span><br>";
		$iapPageError = 1;
	} else {
		$LastYear = strtotime("now - 1 year");
		$ThisDate = strtotime($_REQUEST['pPurDate']);
		if ($ThisDate < $LastYear) {
		    echo "<span class=iapError>Purchase Date more than one year old!</span><br>";
		    $iapPageError = 1;			
		}
	}

	if (isset($_REQUEST['pPurVendor'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_vendor'], $_REQUEST['pPurVendor']);
		if ($iapRet['Changed'] == "Y") {
		    $iapPurchase['pur_vendor'] = $iapRet['Value'];
		    $iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
		    echo "<span class=iapError>Purchased From cannot be blank!</span><br>";
		    $iapPageError = 1;
		}
	} elseif (empty($iapPurchase['pur_vendor'])) {
		echo "<span class=iapError>Purchased From cannot be blank!</span><br>";
		$iapPageError = 1;
	}

	if (isset($_REQUEST['pPurOrder'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_order'], $_REQUEST['pPurOrder']);
		if ($iapRet['Changed'] == "Y") {
		    $iapPurchase['pur_order'] = $iapRet['Value'];
		    $iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
		    echo "<span class=iapError>Order Number cannot be blank!</span><br>";
		    $iapPageError = 1;
		}
	} elseif (empty($iapPurchase['pur_order'])) {
		echo "<span class=iapError>Order Number cannot be blank!</span><br>";
		$iapPageError = 1;
	}

	if ($_REQUEST['pPurType'] == "I"
	or $_REQUEST['pPurType'] == "S") {
		if ($iapPurchase['pur_type'] != $_REQUEST['pPurType']) {
			$iapPurchase['pur_type'] = $_REQUEST['pPurType'];
			$iapChanged = "Y";
		}
	} else {
	    echo "<span class=iapError>Purchase Type is invalid!</span><br>";
	    $iapPageError = 1;
	}

	if (isset($_REQUEST['pPurMiles'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_miles'], $_REQUEST['pPurMiles'], "Y");
		if ($iapRet['Changed'] == "Y") {
			$iapPurchase['pur_miles'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			$iapPurchase['pur_miles'] = 0;
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Miles value must be numeric if entered!</span><br>";
			$iapPageError = 1;
		}
	}

	$expRet = IAP_Validate_Expenses($iapPurchase['expenses']);
	$iapPurchase['expenses'] = $expRet['Table'];
	if ($expRet['Error'] != "N") {
		$iapPageError = 1;
	}


/*
	if () {
		

	} else {
		if (isset($_REQUEST['pPurExp'])) {
			$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_expenses'], $_REQUEST['pPurExp'], "Y");
			if ($iapRet['Changed'] == "Y") {
				$iapPurchase['pur_expenses'] = $iapRet['Value'];
				$iapChanged = "Y";
			}
			if ($iapRet['Error'] == "1") {
				$iapPurchase['pur_expenses'] = 0;
			} elseif ($iapRet['Error'] == "2") {
				echo "<span class=iapError>Expenses value must be numeric if entered!</span><br>";
				$iapPageError = 1;
			}
		}

		if (isset($_REQUEST['pExpExp'])) {
			if ($iapPurchase['pur_exp_explained'] != $_REQUEST['pExpExp']) {
				$iapPurchase['pur_exp_explained'] = $_REQUEST['pExpExp'];
				$iapChanged = "Y";
			}
		}
	}
*/

	if (isset($_REQUEST['pComment'])) {
		if ($iapPurchase['pur_comment'] != $_REQUEST['pComment']) {
			$iapPurchase['pur_comment'] = $_REQUEST['pComment'];
			$iapChanged = "Y";
		}
	}

	if (isset($_REQUEST['pPurNet'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_net'], $_REQUEST['pPurNet'], "Y");
		if ($iapRet['Changed'] == "Y") {
			$iapPurchase['pur_net'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			echo "<span class=iapError>Net Purchase value is not valid!</span><br>";
			$iapPageError = 1;
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Net Purchase value must be numeric!</span><br>";
			$iapPageError = 1;
		}
	}

	if (isset($_REQUEST['pShipping'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_shipping'], $_REQUEST['pShipping'], "Y");
		if ($iapRet['Changed'] == "Y") {
			$iapPurchase['pur_shipping'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			$iapPurchase['pur_shipping'] = 0;
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Shipping value must be numeric if entered!</span><br>";
			$iapPageError = 1;
		}
	}

	if (isset($_REQUEST['pSalesTax'])) {
		$iapRet = IAP_Validate_Nonblank($iapPurchase['pur_tax'], $_REQUEST['pSalesTax'], "Y");
		if ($iapRet['Changed'] == "Y") {
			$iapPurchase['pur_tax'] = $iapRet['Value'];
			$iapChanged = "Y";
		}
		if ($iapRet['Error'] == "1") {
			$iapPurchase['pur_tax'] = 0;
		} elseif ($iapRet['Error'] == "2") {
			echo "<span class=iapError>Sales Tax value must be numeric if entered!</span><br>";
			$iapPageError = 1;
		}
	}

	$iapData = $_REQUEST['IAPDATA'];
	$iapItems = explode("|", $iapData);
	$iapNewData = $_REQUEST['PNEWITEMINFO'];
	$iapNewItems = explode("|", $iapNewData); 
	$iapNewPurDtl = array();

	$i = 0;
	$p = 0;

// if maintain po and add item get no match error because items coming from existing po are not in the newitem array.

	if (!(set_time_limit(90))) {
		echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	foreach($iapItems as $iapI) {
		$i++;
		if (empty($iapI)) {
			continue;
		}
		$iapColumns = explode("~", $iapI);
		$iapItemCode = strtoupper($iapColumns[0]);
		$iapItemDesc = ucwords($iapColumns[1]);
		$iapItemQty = $iapColumns[2];
		$iapItemCost = $iapColumns[3];
		$iapItemValue = $iapColumns[4];
		$iapItemSource = $iapColumns[5];
		if (empty($iapItemCode)) {
			echo "<span class=iapError>Item Code cannot be blank in row ".$i."!</span><br>";
	    	$iapPageError = 1;
		}
		if (empty($iapItemDesc)) {
			echo "<span class=iapError>Item Description cannot be blank in row ".$i."; item ".$iapItemCode."!</span><br>";
	    	$iapPageError = 1;
		}
		if (empty($iapItemQty)) {
			echo "<span class=iapError>Item Quantity cannot be zero in row ".$i."; item ".$iapItemCode."!</span><br>";
	    	$iapPageError = 1;
		}

		$iapN = $iapNewItems[$p];
		$iapNewCols = explode("~", $iapN);
		$iapNewItem = strtoupper($iapNewCols[0]);
		$iapNewStatus = $iapNewCols[1];
		$iapNewSource = $iapNewCols[5];
		if ($iapNewItem == $iapItemCode) {
			$iapFnd = "Y";
			$iapNewItems[$p] = "";
		} else {
			$iapFnd = "N";
			$q = 0;
			$r = count($iapNewItems);
			while($iapFnd == "N") {
				if ($q == $r) {
					break;
				}
				if (!empty($iapNewItems[$q])) {
					$iapN = $iapNewItems[$q];
					$iapNewCols = explode("~", $iapN);
					$iapNewItem = strtoupper($iapNewCols[0]);
					if ($iapNewItem == $iapItemCode) {
						$iapNewItems[$q] = "";
						$iapFnd = "Y";
						break;
					}
				}
				$q++;
			}
			$p++;
		}
		if ($iapFnd == "N") {
			echo "<span class=iapError>IAP INTERNAL ERROR Matching items to new items for item #".$p." - ".$iapItemCode." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapNewStatus == "EXISTING") {
			$iapNewUnits = 0;
			$iapNewPrice = 0;
			$iapNewCat = 0;
			$iapNewSource = $iapItemSource;
		} else {
			$iapNewUnits = $iapNewCols[2];
			$iapNewPrice = $iapNewCols[3];
			$iapNewCat = $iapNewCols[4];
			$iapNewSource = $iapNewCols[5];
		}

		$iapPD = array('PDItemCode' => $iapItemCode, 'PDDesc' => $iapItemDesc, 'PDQty' => $iapItemQty, "PDCost" => $iapItemCost, "PDValue" => $iapItemValue, "PDSrc" => $iapItemSource, "PDUnits" => $iapNewUnits, "PDPrice" => $iapNewPrice, "PDCat" => $iapNewCat, "PDStatus" => $iapNewStatus, "PDNSrc" => $iapNewSource);

		$iapNewPurDtl[] = $iapPD;
		$iapNewUsed[$p] = "Y";
		$p++;
	}

	if ($iapPageError == 0
	and $iapChanged == "Y") {

		echo "<span class=iapSuccess>All fields are valid. Beginning update.</span><br>";
		wp_ob_end_flush_all();
		flush();

		$iapPurchase['pur_company'] = $_REQUEST['CoId'];
		if (empty($iapPurchase['pur_miles'])) {
			$iapPurchase['pur_miles'] = 0;
		}
		if (empty($iapPurchase['pur_expenses'])) {
			$iapPurchase['pur_expenses'] = 0;
		}
		if (empty($iapPurchase['pur_shipping'])) {
			$iapPurchase['pur_shipping'] = 0;
		}
		if (empty($iapPurchase['pur_tax'])) {
			$iapPurchase['pur_tax'] = 0;
		}
		$iapPurchase['pur_items'] = count($iapNewPurDtl);
		$iapPurchase['pur_changed'] = date("Y-m-d");
		$iapPurchase['pur_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapPurchase, "pur");
		if ($iapRet < 0) {
 			echo "<span class=iapError>IAP INTERNAL ERROR writing purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapPurchase['status'] == "NEW") {
			$iapPurchase['pur_id'] = $iapRet;
			$iapPurDtl = (array) IAP_Build_New_Row(array("table" => "pdtl"));
			$iapPurDtl[0]['purdet_company'] = $_REQUEST['CoId'];
			$iapPurDtl[0]['purdet_purid'] = $iapPurchase['pur_id'];
			$iapPurDtl[0]['purdet_item'] = "";
			$iapPurDtl[0]['RowStatus'] = "REM";
			$u = "added";
		} else {
			$iapPurDtl = IAP_Get_PurDet($iapPurchase['pur_id']);
			if ($iapPurDtl < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retreiving prior purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$u = "updated";
		}

		$iapRet = IAP_Write_Expenses("P", $iapPurchase['pur_id'], $iapPurchase['expenses']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR retreiving prior purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}


		echo "<span class=iapSuccess>Successfully ".$u." the purchase record.</span><br>";
		wp_ob_end_flush_all();
		flush();

		if (!(set_time_limit(120))) {
			echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
		}

// -------------------------
// Write new purchase detail
// -------------------------
		require_once(ABSPATH."MyPages/IAPProcessLot.php");
		$iapWasNewItem = "N";
		foreach($iapNewPurDtl as $iapNPD) {
			$iapPDFound = "N";
			$i = 0;
			for($i = 0; $i < count($iapPurDtl); $i++) {
				if (!isset($iapPurDtl[$i]['RowStatus'])) {
					if (strtoupper($iapPurDtl[$i]['purdet_item']) == strtoupper($iapNPD['PDItemCode'])
					and $iapPurDtl[$i]['purdet_quantity'] == $iapNPD['PDQty']) {
						$iapPurDtl[$i]['RowStatus'] = "OK";
						$iapPDFound = "Y";
						break;
					}
				}
			}
			if ($iapPDFound == "N") {
				$iapP = (array) IAP_Build_New_Row(array("table" => "pdtl"));
				$iapPDtl = $iapP[0];
				$iapPDtl['purdet_company'] = $_REQUEST['CoId'];
				$iapPDtl['purdet_purid'] = $iapPurchase['pur_id'];
				$iapPDtl['purdet_item'] = strtoupper($iapNPD['PDItemCode']);
				if ($iapNPD['PDStatus'] == "NEW") {
					$iapPDtl['purdet_item_source'] = 0;
				} else {
					$iapPDtl['purdet_item_source'] = $iapNPD['PDSrc'];
				}
				$iapPDtl['purdet_from_set'] = "N";
				$iapPDtl['purdet_desc'] = $iapNPD['PDDesc'];
				$iapPDtl['purdet_quantity'] = $iapNPD['PDQty'];
				$iapPDtl['purdet_cost'] = $iapNPD['PDCost'];
				$iapPDtl['purdet_ext_cost'] = $iapNPD['PDCost'] * $iapNPD['PDQty'];
				$iapPDtl['purdet_date'] = $iapPurchase['pur_date'];

// -----------------
// Add any new items
// -----------------
				if ($iapNPD['PDStatus'] == "NEW") {
					echo "<span class=iapWarning>NEW ITEM, ".$iapNPD['PDItemCode'].", being added to your catalog. It may require additional information in the Catalog function.</span><br>";
					wp_ob_end_flush_all();
					flush();

					$iapC = IAP_Build_New_Row(array("table" => "ctlg"));
					$iapI = IAP_Build_New_Row(array("table" => "inv"));
					$iapP = IAP_Build_New_Row(array("table" => "prc"));
					$iapCtlg = array_merge($iapC[0], $iapI[0], $iapP[0]);

					$iapCtlg['cat_company'] = $_REQUEST['CoId'];
					$iapCtlg['cat_item_code'] = $iapNPD['PDItemCode'];
					$iapCtlg['cat_description'] = $iapNPD['PDDesc'];
					$iapCtlg['cat_supplier'] = $iapPurchase['pur_vendor'];
					$iapCtlg['cat_active'] = "Y";
					$iapCtlg['cat_set'] = "N";
					$iapCtlg['cat_changed'] = date("Y-m-d");
					$iapCtlg['cat_changed_by'] = $_REQUEST['IAPUID'];
					$iapCtlg['inv_on_hand'] = 0;
					$iapCtlg['inv_min_onhand'] = 0;
					$iapCtlg['prc_effective'] = date("Ymd");
					$iapCtlg['prc_cost'] = $iapNPD['PDCost'];
					$iapCtlg['prc_units'] = $iapNPD['PDUnits'];
					$iapCtlg['prc_price'] = $iapNPD['PDPrice'];
					$iapCtlg['prc_cat_code'] = $iapNPD['PDCat'];
					require_once(ABSPATH."MyPages/IAPCreateCat.php");
					IAP_Create_Cat($iapCtlg, "Y", "Y", "Y");

					$iapCtlg['status'] = "EXISTING";
					$iapWasNewItem = "Y";
					echo "<span class=iapSuccess>Successfully added item ".$iapPurDtl['purdet_item'].".</span><br>";
					wp_ob_end_flush_all();
					flush();
				} else {
					$iapCtlg = IAP_Get_Catalog($iapNPD['PDItemCode']);
					if ($iapCtlg < 0
					or $iapCtlg['status'] == "NEW") {
						echo "<span class=iapError>IAP INTERNAL ERROR retreiving item ".$iapNPD['PDItemCode']." from catalog. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
				}
				if ($iapCtlg['cat_set'] != "Y" 
				and $iapCtlg['code_inv_type'] == "I") {
					$iapInv = IAP_Get_Inventory($iapCtlg['cat_item_code']);
					if ($iapInv < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR retrieving inventory item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					if ($iapInv['status'] == "NEW") {
						$iapInv['inv_company'] = $_REQUEST['CoId'];
						$iapInv['inv_item_code'] = $iapCtlg['cat_item_code'];
						$iapInv['inv_on_order'] = 0;
						$iapInv['inv_min_onhand'] = 0;
					}
					$q = $iapNPD['PDQty'] * $iapCtlg['prc_units'];
					$iapInv['inv_on_hand'] = $iapInv['inv_on_hand'] + ($iapNPD['PDQty'] * $iapCtlg['prc_units']);
					$iapInv['inv_changed'] = date("Y-m-d");
					$iapInv['inv_changed_by'] = $_REQUEST['IAPUID'];
					$iapRet = IAP_Update_Data($iapInv, "inv");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR updating inventory for item ".$iapCtlg['inv_item_code'].". [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					echo "<span class=iapSuccess>Successfully updated on-hand for item ".$iapNPD['PDItemCode'].".</span><br>";
					wp_ob_end_flush_all();
					flush();

// ----------------
// Update lot table
// ----------------
					$iapRet = IAP_Add_Purchase_Lot($iapPDtl, $iapInv['inv_on_hand'], $iapPurchase['pur_order']);
				}

// ----------------------
// Write New Purchase Detail
// ----------------------
				if ($iapCtlg['cat_set'] == "Y") {
					$iapPDtl['purdet_from_set'] = "S";  // Say Set item part has S
				}
				$iapRet = IAP_Update_Data($iapPDtl, "pdtl");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR writing purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapPDtl['purdet_seq'] = $iapRet;
				$iapPDtl['status'] = "EXISTING";

				if ($iapCtlg['cat_set'] == "Y") {
					require_once(ABSPATH."MyPages/IAPApplySet.php");
					IAP_Apply_Purchase_Set($iapPDtl, $iapPurchase['pur_order']);
				}
			}
		}

// --------------------------------
// Done finding New Purchase Detail
// Delete those no longer used. 
// --------------------------------
		foreach($iapPurDtl as $iapPD) {
			if (!isset($iapPD['RowStatus'])) {
				$iapPD['QtyDiff'] = $iapPD['purdet_quantity'] * -1;
				$iapRet = IAP_Update_Purchase_Lot($iapPD, $iapPurchase['pur_order']);
				$iapRet = IAP_Delete_Row($iapPD, "pdtl");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR deleting row from purchase detail table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}		
			}
		}

		$iapJrnl = IAP_Get_Journal_By_Detail("P".$iapPurchase['pur_type'], $iapPurchase['pur_id']);
		if ($iapJrnl < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR retreiving journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_date'] = $iapPurchase['pur_date'];
		if ($iapPurchase['pur_type'] == "I") {
			$t = "Inventory";
		} else {
			$t = "Supplies";
		}
		$iapJrnl['jrnl_description'] = "Purchase of ".$t;
		$iapJrnl['jrnl_type'] = "P".$iapPurchase['pur_type'];
		$iapJrnl['jrnl_amount'] = $iapPurchase['pur_net'] + $iapPurchase['pur_shipping'] + $iapPurchase['pur_tax'];
		$iapJrnl['jrnl_net'] = $iapPurchase['pur_net'];
		$iapJrnl['jrnl_tax'] = $iapPurchase['pur_tax'];
		$iapJrnl['jrnl_shipping'] = $iapPurchase['pur_shipping'];
		$iapJrnl['jrnl_mileage'] = $iapPurchase['pur_mileage'];

//------		$iapJrnl['jrnl_expenses'] = $iapPurchase['pur_expenses'];

		$iapJrnl['jrnl_exp_explain'] = $iapPurchase['pur_exp_explained'];
		$iapJrnl['jrnl_vendor'] = $iapPurchase['pur_vendor'];
		$iapJrnl['jrnl_comment'] = $iapPurchase['pur_comment'];
		$iapJrnl['jrnl_detail_key'] = $iapPurchase['pur_id'];
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapJrnl['status'] == "NEW") {
			echo "<span class=iapSuccess>Journal was successfully added.</span><br><br>";
			wp_ob_end_flush_all();
			flush();
		} else {
			echo "<span class=iapSuccess>Journal was successfully updated.</span><br>";
			wp_ob_end_flush_all();
			flush();
		}

		if ($iapPurchase['status'] == "NEW") {
			$iapU = "added";
			$iapPurchase['status'] == "EXISTING";
		} else {
			$iapU = "updated";
		}
		echo "<span class=iapSuccess style='font-weight:bold;'>Purchase was successfully ".$iapU.".</span><br><br>";

		if ($iapWasNewItem == "Y") {
			echo "<br><br>";			
		}
	}
	$iapPurchase['purdtl'] = $iapItems;

	$iapRet = IAP_Update_Savearea("IAP208PU", $iapPurchase, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapOrigAction = $_REQUEST['origaction'];

	$DivShow = "block";	

} else {

	$iapOrigAction = "NEW";

	if (IAP_Remove_Savearea("IAP208PU") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the purchase savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapP = (array) IAP_Build_New_Row(array("table" => "pur"));
	$iapPurchase = $iapP[0];
	$iapPurchase['pur_type'] = "I";
	$iapPurchase['purdtl'] = array();
	$iapRet = IAP_Create_Savearea("IAP208PU", $iapPurchase, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for purchase [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";

$iapPurchases = IAP_Get_Purchase_List();
if ($iapPurchases < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve purchases. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
$pPurs = "";
//$pPurIds = "";
$c = "";
if (!empty($iapPurchases)) {
	foreach($iapPurchases as $iapP) {
		$v = str_replace("(", "- ", $iapP['pur_vendor']);
		$v = str_replace(")", " ", $v);
		$p = $iapP['pur_order']." on ".date("m/d/Y", strtotime($iapP['pur_date']))." from ".$v;
		$pPurs = $pPurs.$c.'{"label": "'.$p.'", "id": "'.strval($iapP['pur_id']).'"}';
		$c = ',';
	}
}

$iapSuppList = IAP_Build_CoSupp_Array();
if ($iapSuppList < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve suppliers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
$pCoSupps = "";
$c = "";
if (!empty($iapSuppList)) {
	foreach($iapSuppList as $iapS) {
		if ($iapS['SId'] > 0) {
			$pCoSupps = $pCoSupps.$c.'{"label": "'.$iapS['SName'].'", "id": "'.strval($iapS['SId']).'"}';
			$c = ',';
		}		
	}	
}

require_once("IAPGetItemLists.php");
$iapItemLists = IAP_Get_Item_Lists();
$iapItemList = $iapItemLists[0];
$iapDescList = $iapItemLists[1];

$iapCats = IAP_Get_Codes();
if ($iapCats < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve categories. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
if ($iapCats != NULL) {
	$iapCOpts = "";
	foreach ($iapCats as $iapC) {
		$iapCOpts = $iapCOpts."<option value='".$iapC['code_code']."'>".$iapC['code_value']."</option>";
	}
}

$iapExps = IAP_Get_Expense_Codes();
if ($iapExps < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve expenses codes. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
$expSel = "";
$expVals = "[";
foreach($iapExps as $expCd) {
	$expSel = $expSel."<option value='".$expCd['expcd_type']."'>".$expCd['expcd_value']."</option>";
	$expVals = $expVals."['".$expCd['expcd_type']."','".$expCd['expcd_value']."','".$expCd['expcd_more_info']."'],";
}
$expSel = $expSel."</select>";
$expVals = substr($expVals,0,strlen($expVals)-1)."]";

$iapReadOnly = IAP_Format_Heading("Purchase Entry/Edit");

$h = IAP_Do_Help(3, 208, 1); // level 3, page 208, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='pchoose'  style='display:block;'>
<form name='pselform' action='?action=p208retA&origaction=initial' method='POST' onsubmit='return pNoSubmit();'>
<?php
	if (empty($pPurs)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "No purchases on file. Click on ADD.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<span class=iapFormLabel style='padding-left: 40px;'>";
	echo "<label for='pPurList'>Select a purchase: </label>";
	echo "<input id='pPurList' size=50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 208, 1); //		Help Narative	<!-- level 1, page 208, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='pGoClicked()'>";
	echo "<br><span class=iapError id=pError style='display:none;'>The purchase was not found. Retry or click Add.</span>";

	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapFormLabel style='padding-left: 50px;'>";
		echo "<input class=iapButton type='button' name='pAdd' id='pAdd' value='Add A New Purchase' onclick='pAddClicked()'>";
		echo "</span>";
	}

	echo "<br><span class=iapFormLabel style='padding-left: 40px;'>".$iapMsg."</span>";
?>
</form>
</div>
<div id='pdetail' style='display:<?php echo $DivShow; ?>;'>

<p style='text-indent:50px; width:100%'>

<form name='purform' action='?action=p208retB&origaction=<?php echo $iapOrigAction; ?>' method='POST' onkeypress='stopEnterSubmitting(window.event)'>

<?php
$tbindx = 1;
$d3 = date("m/d/Y", strtotime($iapPurchase['pur_date']));
?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label class=iapFormLabel>Date of Purchase:</label>&nbsp;
	<input <?php echo $iapReadOnly; ?> placeholder="mm/dd/yyyy" maxlength="15" size="15" 
		   tabindex="<?php echo strval($tbindx++); ?>" name="pPurDate" id="pPurDate" value="<?php echo $d3; ?>">
	<br>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label class=iapFormLabel>Purchase Type: </label>
	   <input <?php echo $iapReadOnly; ?> tabindex="<?php echo strval($tbindx++); ?>" name="pPurType" value="I" 
	   	      type="radio"<?php if ($iapPurchase['pur_type'] == "I") echo " checked"; ?>> For Inventory
	   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	   <input <?php echo $iapReadOnly; ?> tabindex="<?php echo strval($tbindx++); ?>" name="pPurType" value="S" 
	   		  type="radio"<?php if ($iapPurchase['pur_type'] == "S") echo " checked"; ?>> For Supplies
	   &nbsp;&nbsp;&nbsp;
	   <?php echo IAP_Do_Help(1, 208, 2);  //		Help Narative	<!-- level 1, page 208, section 2 --> ?>
	<br>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label class=iapFormLabel>Purchase From:</label>&nbsp;
	<input <?php echo $iapReadOnly; ?> maxlength="50" size="30" tabindex="<?php echo strval($tbindx++); ?>" 
		   name="pPurVendor" id="pPurVendor" value="<?php echo $iapPurchase['pur_vendor']; ?>">
	&nbsp;&nbsp; 

	<label class=iapFormLabel>Order Number:</label>&nbsp;
	<input <?php echo $iapReadOnly; ?> type='text' maxlength="20" size="20" tabindex="<?php echo strval($tbindx++); ?>"			   name="pPurOrder" id="pPurOrder" value="<?php echo $iapPurchase['pur_order']; ?>" onblur="pCheckPO()">
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span id="pOrdNoExists" style="display:none; color:brown;">Warning: The entered Order Number is already in the file!</span><br>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class=iapFormLabel>Expenses Related To This Purchase</span>
		&nbsp;&nbsp;&nbsp;
		<?php echo IAP_Do_Help(1, 208, 3);  //  Help Narative	<!-- level 1, page 208, section 3 --> ?>
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label class=iapFormLabel>Mileage:</label>&nbsp;
	<input <?php echo $iapReadOnly; ?> maxlength="10" size="10" tabindex="<?php echo strval($tbindx++); ?>" 
		   name="pPurMiles" id="pPurMiles" align="right" step="0.1" value=<?php echo $iapPurchase['pur_miles']; ?>>
	&nbsp;&nbsp;
	<br>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	echo IAP_Format_Expenses($tbindx, $iapReadOnly, $iapPurchase['pur_expenses'], $iapPurchase['pur_exp_explained'],
							 $iapPurchase['expenses']);
?>

	<br><br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label class=iapFormLabel id=pCommLbl>Comments:</label>
	<textarea name='pComment' id='pComment' cols='60' rows='4' wrap='soft' 
			  tabindex="<?php echo strval($tbindx++); ?>" 
			  style="text-indent: 15;" <?php echo $iapReadOnly; ?>>
		<?php echo $iapPurchase['pur_comment']; ?>
	</textarea>

<?php
	if ($iapReadOnly != "readonly") {
?>
		<br>
		<div id="UsePriceDiv" style="text-align: left; display: none;">
			<br>
			<label class=iapFormLabel id=pCommLbl>Use Selling Price For Unit Cost?:</label>
			<input type="checkbox" id="pUsePrc" tabindex="<?php echo strval($tbindx++); ?>" 
				   onclick="pSetUsePrice()">	
		</div>

		<br>
		&nbsp;&nbsp;<span style="text-decoration: underline;">Enter Item Purchased</span>
			&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 208, 4);  //	Help Narative	<!-- level 1, page 208, section 4 -->  ?>
			&nbsp;&nbsp;&nbsp;<span class=iapFormLabel>Start typing an item code or description for a list.</span>
		<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<span id=pItemError class=iapError> </span>
		<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="pItemCd" class=iapFormLabel id=pItemCdLbl>Item Code:</label>&nbsp;
		<input tabindex='<?php echo strval($tbindx++); ?>' size='50' name='pItemCd' id='pItemCd' 
			   list='iapICD' onfocus='pItemFocus()' />&nbsp;&nbsp;&nbsp;
		<button class=iapButton style="margin:0 0 5px;" 
		        tabindex="<?php echo strval($tbindx++); ?>" name='pAddNewItem' id='pAddNewItem'
		        onclick='pNIClicked(); return false;'>Add A New Item</button>
		<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="pItemDesc" class=iapFormLabel id=pItemDescLbl>Description:</label>&nbsp;
		<input tabindex='<?php echo strval($tbindx++); ?>' maxlength='100' size='50' name='pItemDesc' 
			   id='pItemDesc' onfocus='pItemFocus()' />
		<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label class=iapFormLabel id=pItemQtyLbl>Quantity:</label>&nbsp;
		<input type='number' maxlength="10" size="10" tabindex="<?php echo strval($tbindx++); ?>" 
			   name="pItemQty" id="pItemQty" step="1" <?php echo $iapReadOnly; ?> >
		&nbsp;&nbsp;
		<label class=iapFormLabel id="pItemCostLbl">Unit Cost:</label>&nbsp;
		<input type='number' maxlength="10" size="10" tabindex="<?php echo strval($tbindx++); ?>" 
			   name="pItemCost" id="pItemCost" step="0.01" <?php echo $iapReadOnly; ?>>
		<br>

		<div id="iapNewItem" style="text-align: left; display: none;">
			<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class=iapFormLabel id="pNewItemHead" style='font-weight: bold;'>New Item! Enter Information Below.</label>
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class=iapFormLabel id=pNewUnitsLbl>Saleable Units In Package:</label>&nbsp;
			<input type='number' maxlength="10" size="10" tabindex="<?php echo strval($tbindx++); ?>" 
			       name="pNewUnits" id="pNewUnits" step="1" <?php echo $iapReadOnly; ?>>
			&nbsp;&nbsp;
			<label class=iapFormLabel id=pNewPriceLbl>Sale Price:</label>&nbsp;
			<input type='number' maxlength="10" size="10" tabindex="<?php echo strval($tbindx++); ?>" 
			       name="pNewPrice" id="pNewPrice" step="0.01"> <?php echo $iapReadOnly; ?>
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class=iapFormLabel id=pNewCatLbl>Category:</label>&nbsp;
			<select tabindex="<?php echo strval($tbindx++); ?>" name=pNewCat id=pNewCat size='1' 
			        <?php echo $iapReadOnly; ?>>
			<option value='---'>Select A Category</option><?php echo $iapCOpts; ?></select>
		</div>

		<div style="width:100%; vertical-align:middle; text-align:center;">
			<input class=iapButton type='button' tabindex='<?php echo strval($tbindx++); ?>' 
			       name='pRecItem' id='pRecItem' value='Record This Item' onclick='pRecordItem(); return false;'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=iapButton type='button' tabindex='<?php echo strval($tbindx++); ?>' 
				   name='pClearItem' id='pClearItem' value='Clear This Item&apos;s Data' 
				   onclick='pClrItemData(); return false;'>
		    <br><span class=iapWarning>(WARNING: Do NOT use the Submit button until all items have been recorded)</span>
		</div>

<?php
	}
?>

<br>
<label class=iapFormLabel>Net Cost:</label>&nbsp;
<input readonly="readonly" maxlength="15" size="15" tabindex="<?php echo strval($tbindx++); ?>" 
	   name="pPurNet" id="pPurNet" align="right" step="0.01" value=<?php echo $iapPurchase['pur_net']; ?>>
&nbsp;&nbsp;
<label class=iapFormLabel>Shipping Cost: </label>&nbsp;
<input <?php echo $iapReadOnly; ?> type=number maxlength="12" size="12" 
	   tabindex="<?php echo strval($tbindx++); ?>" name="pShipping" id="pShipping" step="0.01" 
	   value=<?php echo $iapPurchase['pur_shipping']; ?>>
&nbsp;&nbsp; 
<label class=iapFormLabel>Sales Tax:</label>&nbsp;
<input <?php echo $iapReadOnly; ?> type=number maxlength="12" size="12" 
	   tabindex="<?php echo strval($tbindx++); ?>" name="pSalesTax" id="pSalesTax" step="0.01" 
	   value=<?php echo $iapPurchase['pur_tax']; ?>>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 208, 5);  //		Help Narative	<!-- level 1, page 208, section 5 -->  ?>
<br>

<br><br>
<fieldset style='border: 1px solid #000; top: 5px; right: 5px; bottom: 5px; left: 5px;'>
&nbsp;&nbsp;<span style="text-decoration: underline;">Items Received</span>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 208, 6);  //		Help Narative	<!-- level 1, page 208, section 6 -->  ?>
&nbsp;&nbsp;&nbsp;<span class=iapWarning>(Don't forget to click on <span style='font-weight:bold;'>Submit</span> below when all items have been recorded!)</span><br>
<br>
<table id='iapReceived' class=iapTable><tbody class=iapTBody>
<tr>
<td width='3%'></td>
<td width='2%'></td>
<td width='13%' class=iapFormLabel><span style='text-decoration: underline;'>Item Code</span></td>
<td width='55%' class=iapFormLabel><span style='text-decoration: underline;'>Description</span></td>
<td width='5%' class=iapFormLabel><span style='text-decoration: underline;text-align:right;'>Qty</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;text-align:right;'>Cost</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;text-align:right;'>Value</span></td>
<td width='2%'></td>
</tr>

<?php
$iapItemInfo = "";
$iapItems = $iapPurchase['purdtl'];
$pRows = 0;
foreach($iapItems as $iapI) {
	if (empty($iapI)) {
		continue;
	}
	$pRows = $pRows + 1;
	$iapColumns = explode("~", $iapI);
	echo "<tr><td width='3%'><input type='hidden' id='recrow".$pRows."' value='E'></td>";
	echo "<td width='2%'><img src='".$_REQUEST['IAPUrl']."/MyImages/Icons/DeleteRedSM.png' onclick='pDelReceived(".$pRows."); return(false);'>&nbsp;&nbsp;</td>";
	echo "<td width='13%' class=iapFormLabel>".$iapColumns[0]."</td>";
	echo "<td width='55%' class=iapFormLabel>".$iapColumns[1]."</td>";
	echo "<td width='5%' class=iapFormLabel style='text-align:right;'>".number_format($iapColumns[2], 0, '.', ',')."</td>";
	echo "<td width='10%' class=iapFormLabel style='text-align:right;'>".number_format($iapColumns[3], 2, '.', ',')."</td>";
	$iapTotValue = $iapColumns[2]*$iapColumns[3];
	echo "<td width='10%' class=iapFormLabel style='text-align:right;'>".number_format($iapTotValue, 2, '.', ',')."</td>";
	echo "<td width='2%'><input type='hidden' value='".$iapColumns[5]."'></td></tr>";

	$iapItemInfo = $iapItemInfo.$iapColumns[0]."~Existing~~~|";
//	document.getElementById("PNEWITEMINFO").value = pNewData + pItemCd + "~" + pStatus + "~" + pNewUnits + "~" + pNewPrice + "~" + pNewCat + "|";

}
?>

</tbody></table>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/Icons/Delete_IconSM.png'><span style='vertical-align: middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clicking on this symbol next to a row removes the row</span><br>
</fieldset>

<br><br>

<table><tr><td style='width:100%; text-align:center;'>

<?php
if ($iapReadOnly != "readonly") {
	echo "<input class='iapButton' tabindex='".strval($tbindx++)."' type='submit' name='pSubmit' id='pSubmit' 
				 value='Submit' onclick='return pSendForm();'>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input class='iapButton' tabindex='".strval($tbindx++)."' type='button' name='pClear' id='pClear' 
				 value='Clear' onclick='pClearForm(); return false;'>";

}
?>
</td></tr></table>

<br><br><br>

<input type='hidden' name='LHCA' id='LHCA' value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type='hidden' name='IAPDATA' id='IAPDATA' value="">
<input type="hidden" name="PUPDATETYPE" id="PUPDATETYPE" value="">
<input type="hidden" name="PTHISITEMSOURCE" id="PTHISITEMSOURCE" value="">
<input type="hidden" name="PTHISITEMSTATUS" id="PTHISITEMSTATUS" value="">
<input type="hidden" name="POID" id="POID" value="">
<input type="hidden" name="PNEWITEMINFO" id="PNEWITEMINFO" value="<?php echo $iapItemInfo; ?>">
<input type="hidden" name="PSUPPID" id="PSUPPID" value="">
<input type="hidden" name="PUSEPRICE" id="PUSEPRICE" value="">
<input type="hidden" name="PIAPURL" id="PIAPURL" value="<?php echo $_REQUEST['IAPUrl']; ?>">
<input type="hidden" name="EXPCNT" id="EXPCNT" value="0">

</form>
</p></div>

<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSPurc.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSPurc.min.js");
?>
var expValArray = <?php echo $expVals; ?>;
var expSelText =  "<?php echo $expSel; ?>";

var pPrchList = [<?php echo $pPurs; ?>];
var pSplrList = [<?php echo $pCoSupps; ?>]
var pItemCodes = [<?php echo $iapItemList; ?>];
var pItemDescs = [<?php echo $iapDescList; ?>];
</script>