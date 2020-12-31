<?php

function IAP_Apply_Purchase_Set($iapPurDet, $iapPO) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In Apply_Set.<br>";
	}

	$iapSet = IAP_Get_CSet($iapPurDet['purdet_item_source'], $iapPurDet['purdet_item']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP DATABASE ERROR retrieving set [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapSet == NULL) {
		echo "<span class=iapError>IAP INTERNAL ERROR set record not found [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;		
	}

	require_once(ABSPATH."MyPages/IAPProcessLot.php");
	foreach($iapSet as $iapSetPart) {
		$total_quantity = $iapPurDet['purdet_quantity'] * $iapSetPart['set_part_quantity'];
		$total_cost = $iapPurDet['purdet_cost'] * $iapSetPart['set_buy_percent'];
		$total_cost = round($total_cost, 2);
		$total_ext_cost = $total_cost * $total_quantity;

		$iapCtlg = IAP_Get_Catalog($iapSetPart['set_part_item']);
		if ($iapCtlg < 0
		or $iapCtlg['status'] == "NEW") {
			echo "<span class=iapError>IAP INTERNAL ERROR retreiving item ".$iapSet['set_item_code']." from catalog. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
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
		$q = $total_quantity * $iapCtlg['prc_units'];
		$iapInv['inv_on_hand'] = $iapInv['inv_on_hand'] + $q;
		$iapInv['inv_changed'] = date("Y-m-d");
		$iapInv['inv_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapInv, "inv");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating inventory for item ".$iapCtlg['inv_item_code'].". [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapP = (array) IAP_Build_New_Row(array("table" => "pdtl"));
		$iapPDtl = $iapP[0];
		$iapPDtl['purdet_company'] = $_REQUEST['CoId'];
		$iapPDtl['purdet_purid'] = $iapPurDet['purdet_purid'];
		$iapPDtl['purdet_item'] = strtoupper($iapSetPart['set_part_item']);
		$iapPDtl['purdet_item_source'] = $iapPurDet['purdet_item_source'];;
		$iapPDtl['purdet_from_set'] = "Y";
		$iapPDtl['purdet_desc'] = $iapCtlg['cat_description'];
		$iapPDtl['purdet_quantity'] = $total_quantity;
		$iapPDtl['purdet_cost'] = $total_cost;
		$iapPDtl['purdet_ext_cost'] = $total_ext_cost;
		$iapPDtl['purdet_date'] = $iapPurDet['purdet_date'];

// ----------------
// Update lot table
// ----------------
		$iapRet = IAP_Add_Purchase_Lot($iapPDtl, $iapInv['inv_on_hand'], $iapPO);

// ----------------------
// Write New Purchase Detail
// ----------------------
		$iapRet = IAP_Update_Data($iapPDtl, "pdtl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapPDtl['purdet_seq'] = $iapRet;
		$iapPDtl['status'] = "EXISTING";

		echo "<span class=iapSuccess>...Successfully added item ".$iapSetPart['set_part_item']." of set ".$iapSetPart['set_item_code']." for unit cost of ".number_format($iapPDtl['purdet_cost'], 2, '.', ',')."</span><br>";
		wp_ob_end_flush_all();
		flush();


	}
}
?>