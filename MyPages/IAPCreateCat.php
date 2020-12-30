<?php

function IAP_Create_Cat($iapItem, $iapItemChanged, $iapOHChanged, $iapPriceChanged) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In Create_Cat.<br>";
	}

	if ($iapItemChanged == "Y") {
		if (!(empty($iapItem['cat_hold_item']))
		and $iapItem['cat_item_code'] != $iapItem['cat_hold_item']) {
			$iapRet = IAP_Delete_Row(array('cat_company' => $iapItem['cat_company'], 'cat_item_code' => $iapItem['cat_hold_item']), 'ctlg');
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR deleting catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapItem['status'] = "NEW";
		}
		if ($iapItem['status'] == "NEW") {
			$iapItem['cat_company'] = $_REQUEST['CoId'];
			$iapOHChanged = "Y";
			$iapPriceChanged = "Y";
		}
		$iapItem['cat_changed'] = date("Y-m-d");
		$iapItem['cat_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapItem, "ctlg");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapJ = IAP_Build_New_Row(array("table" => "jrnl"));
		$iapJrnl = $iapJ[0]; 
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_date'] = date("Y-m-d", strtotime("now"));
		if ($iapItem['status'] == "NEW") {
			$iapItemChg = "added";			
		} else {
			$iapItemChg = "changed";
		}
		$iapJrnl['jrnl_description'] = "Item ".$iapItem['cat_item_code']." ".$iapItemChg;
		$iapJrnl['jrnl_type'] = "MI";
		$iapJrnl['jrnl_amount'] = 0;
		$iapJrnl['jrnl_tax'] = 0;
		$iapJrnl['jrnl_shipping'] = 0;
		$iapJrnl['jrnl_mileage'] = 0;
		$iapJrnl['jrnl_expenses'] = 0;
		$iapJrnl['jrnl_exp_explain'] = 0;
		$iapJrnl['jrnl_vendor'] = 0;
		$iapJrnl['jrnl_item_code'] = $iapItem['cat_item_code'];
		$iapJrnl['jrnl_cost'] = 0;
		$iapJrnl['jrnl_units'] = 0;
		$iapJrnl['jrnl_price'] = 0;
		$iapJrnl['jrnl_cat_code'] = "";
		$iapJrnl['jrnl_on_hand'] = 0;
		$iapJrnl['jrnl_comment'] = "Item ".$iapItem['cat_item_code']." ".$t;
		$iapJrnl['jrnl_detail_key'] = "";
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}

	if ($iapOHChanged == "Y") {
		$iapInv = IAP_Get_Inventory($iapItem['cat_item_code']);
		if ($iapInv < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR retrieving inventory item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapInv['status'] == "NEW") {
			$iapInv['inv_company'] = $_REQUEST['CoId'];
			$iapInv['inv_item_code'] = $iapItem['cat_item_code'];
			$iapInv['inv_on_order'] = 0;
		}
		$iapInv['inv_on_hand'] = $iapItem['inv_on_hand'];
		$iapInv['inv_min_onhand'] = $iapItem['inv_min_onhand'];
		$iapInv['inv_changed'] = date("Y-m-d");
		$iapInv['inv_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapInv, "inv");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating inventory item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		if ($iapItem['status'] != "NEW") {
			$iapJ = IAP_Build_New_Row(array("table" => "jrnl"));
			$iapJrnl = $iapJ[0]; 
			$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
			$iapJrnl['jrnl_date'] = date("Y-m-d", strtotime("now"));
			$iapJrnl['jrnl_description'] = " On Hand Modified For Item ".$iapItem['cat_item_code'];
			$iapJrnl['jrnl_type'] = "MI";
			$iapJrnl['jrnl_amount'] = 0;
			$iapJrnl['jrnl_tax'] = 0;
			$iapJrnl['jrnl_shipping'] = 0;
			$iapJrnl['jrnl_mileage'] = 0;
			$iapJrnl['jrnl_expenses'] = 0;
			$iapJrnl['jrnl_exp_explain'] = 0;
			$iapJrnl['jrnl_vendor'] = 0;
			$iapJrnl['jrnl_item_code'] = $iapItem['cat_item_code'];
			$iapJrnl['jrnl_cost'] = 0;
			$iapJrnl['jrnl_units'] = 0;
			$iapJrnl['jrnl_price'] = 0;
			$iapJrnl['jrnl_cat_code'] = "";
			$iapJrnl['jrnl_on_hand'] = $iapItem['inv_on_hand'];
			$iapJrnl['jrnl_comment'] = "Item ".$iapItem['cat_item_code']." On Hand Was ".strval($iapItem['inv_hold_on_hand'])." Changed To ".strval($iapItem['inv_on_hand']);
			$iapJrnl['jrnl_detail_key'] = "";
			$iapJrnl['jrnl_changed'] = date("Y-m-d");
			$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
			$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}
	}

// price table update here
// add previous price
	if ($iapPriceChanged == "Y") {
		$iapP = IAP_Build_New_Row(array("table" => "prc"));
		$iapNewPriceRec = $iapP[0];
		$iapNewPriceRec['prc_company'] = $_REQUEST['CoId'];
		$iapNewPriceRec['prc_item_code'] = $iapItem['cat_item_code'];
		$iapNewPriceRec['prc_effective_until'] = '2099-12-31';
		$iapNewPriceRec['prc_cost'] = $iapItem['prc_cost'];
		$iapNewPriceRec['prc_units'] = $iapItem['prc_units'];
		if ($iapItem['prc_units'] == 0) {
			$iapNewPriceRec['prc_cost_unit'] = 0;
		} else {
			$iapNewPriceRec['prc_cost_unit'] = $iapItem['prc_cost'] / $iapItem['prc_units'];
		}
		$iapNewPriceRec['prc_price'] = $iapItem['prc_price'];
		$iapNewPriceRec['prc_cat_code'] = $iapItem['prc_cat_code'];
		$iapNewPriceRec['prc_changed'] = date("Y-m-d");
		$iapNewPriceRec['prc_changed_by'] = $_REQUEST['IAPUID'];

		if ($iapItem['status'] == "NEW") {
			$iapNewPriceRec['prc_effective'] = "2010-01-01";
			$iapNewPriceRec['prc_prev_cost'] = 0;
			$iapNewPriceRec['prc_prev_units'] = 0;
			$iapNewPriceRec['prc_prev_cost_units'] = 0;
			$iapNewPriceRec['prc_prev_price'] = 0;
			$iapNewPriceRec['prc_prev_cat_code'] = $iapItem['prc_cat_code'];
			$iapRet = IAP_Update_Data($iapNewPriceRec, "prc");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR inserting new price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		} else {
// Get existing 2099-12-31 record and save previous pricing.
// What if 12-31 rec is not the active rec
			$iapPTbl = IAP_Get_Price($iapItem['cat_item_code'], "CO", "Y");
			if ($iapPTbl < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
// if an effective date was entered use it else use today's date.
			if ($iapNewPriceRec['prc_effective'] == $iapPTbl['prc_effective']) {
				$iapNewPriceRec['prc_effective'] = date("Y-m-d");
			} else {
				$iapNewPriceRec['prc_effective'] = $iapItem['prc_effective'];
			}
			$iapNewPriceRec['prc_prev_cost'] = $iapPTbl['prc_cost'];
			$iapNewPriceRec['prc_prev_units'] = $iapPTbl['prc_units'];
			$iapNewPriceRec['prc_prev_cost_units'] = $iapPTbl['prc_cost_units'];
			$iapNewPriceRec['prc_prev_price'] = $iapPTbl['prc_price'];
			$iapNewPriceRec['prc_prev_cat_code'] = $iapPTbl['prc_cat_code'];
// Change Effective_Until to yesterday and add as a new record.
			if ($iapPTbl['status'] != "NEW") {
				$iapPTbl['prc_effective_until'] = date("Y-m-d", strtotime($iapNewPriceRec['prc_effective']." - 1 day"));
				$iapPTbl['prc_changed'] = date("Y-m-d");
				$iapPTbl['prc_changed_by'] = $_REQUEST['IAPUID'];
				$iapPTbl['status'] = "NEW";
				$iapRet = IAP_Update_Data($iapPTbl, "prc");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating previous price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}

// Now rewrite the 2099-12-31 record with the new pricing.
			$iapNewPriceRec['status'] = "EXISTING";
			$iapRet = IAP_Update_Data($iapNewPriceRec, "prc");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR inserting new price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}

		$iapJ = IAP_Build_New_Row(array("table" => "jrnl"));
		$iapJrnl = $iapJ[0]; 
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_date'] = date("Y-m-d", strtotime("now"));
		if ($iapItem['status'] == "NEW") {
			$iapJrnl['jrnl_description'] = "Pricing For New Item ".$iapNewPriceRec['prc_item_code'];
		} else {
			$iapJrnl['jrnl_description'] = "New Pricing For Item ".$iapNewPriceRec['prc_item_code']." Effective ".date("m/d/Y", strtotime($iapNewPriceRec['prc_effective']));
		}
		$iapJrnl['jrnl_type'] = "IP";
		$iapJrnl['jrnl_amount'] = 0;
		$iapJrnl['jrnl_tax'] = 0;
		$iapJrnl['jrnl_shipping'] = 0;
		$iapJrnl['jrnl_mileage'] = 0;
		$iapJrnl['jrnl_expenses'] = 0;
		$iapJrnl['jrnl_exp_explain'] = 0;
		$iapJrnl['jrnl_vendor'] = 0;
		$iapJrnl['jrnl_item_code'] = $iapNewPriceRec['prc_item_code'];
		$iapJrnl['jrnl_cost'] = $iapNewPriceRec['prc_cost'];
		$iapJrnl['jrnl_units'] = $iapNewPriceRec['prc_units'];
		$iapJrnl['jrnl_price'] = $iapNewPriceRec['prc_price'];
		$iapJrnl['jrnl_cat_code'] = $iapNewPriceRec['prc_cat_code'];
		$iapJrnl['jrnl_on_hand'] = 0;
		$iapJrnl['jrnl_comment'] = $iapJrnl['jrnl_description'];
		$iapJrnl['jrnl_detail_key'] = "";
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}
}
?>