<?php


function IAP_Refund_Sale($iapSale) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapSaleDet = IAP_Get_SaleDet($iapSale['sale_id']);
	if ($iaSaleDet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	require_once(ABSPATH."MyPages/IAPProcessLot.php");
	foreach($iapSaleDet as $iapSD) {
		if ($iapSale['sale_type'] != "F"
		and $iapSale['sale_type'] != "O"
		and $iapSale['sale_type'] != "W") {

			if($_REQUEST['debugme'] == "Y") {
				echo "<span class=iapWarning>IAPSalesRefund calling BackOut_Sale_Lot.<br>";
			}

			$iapRet = IAP_BackOut_Sale_Lot($iapSD, "Y");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR backing out sales detail lots [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}

		$iapRet = IAP_Delete_Row($iapSD, "sdtl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR deleting sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<span class=iapSuccess>...Sales Detail for item ".$iapCtlg['cat_item_code']." was successfully deleted.</span><br>";

		if ($iapSale['sale_type'] != "F"
		and $iapSale['sale_type'] != "O"
		and $iapSale['sale_type'] != "W") {
			$iapCtlg = IAP_Get_Catalog($iapSD['saledet_item_code'], $iapSale['sale_date']);
			if ($iapCtlg < 0
			or $iapCtlg['status'] == "NEW") {
				echo "<span class=iapError>IAP INTERNAL ERROR retreiving the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
// ---  Inventory balance are not adjusted for these types
			$iapCtlg['inv_on_hand'] = $iapCtlg['inv_on_hand'] - $iapSD['saledet_quantity'];
			$iapRet = IAP_Update_Data($iapCtlg, "inv");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating the inventory [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			echo "<span class=iapSuccess>...Item ".$iapCtlg['cat_item_code']." on hand was successfully updated.</span><br>";
		}
	}

	$iapRet = IAP_Delete_Row($iapSale, "sale");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR deleting the sales record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	echo "<span class=iapSuccess>...Sale record was successfully deleted.</span><br>";

	$iapPE = IAP_Get_PartyEvent_By_Id($iapSale['sale_peid']);
	if ($iapPE['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	// ------------------------------------------------
	// Update PE's sales figures and write it again 
	// ------------------------------------------------
	$iapPE['pe_sales_cnt'] = $iapPE['pe_sales_cnt'] - 1;
	$iapPE['pe_net_sales'] = $iapPE['pe_net_sales'] - $iapSale['sale_net'];
	$iapPE['pe_cost_of_items'] = $iapPE['pe_cost_of_items'] - $iapSale['sale_item_cost']; // will be 0 for F, O, W
	$iapPE['pe_profit'] = $iapPE['pe_profit'] - $iapSale['sale_profit']; // will be 0 for F, O, W
	$iapPE['pe_shipping'] = $iapPE['pe_shipping'] - $iapSale['sale_shipping'];
	$iapPE['pe_sales_tax'] = $iapPE['pe_sales_tax'] - $iapSale['sale_sales_tax'];
	$iapPE['pe_total_sales'] = $iapPE['pe_total_sales'] - $iapSale['sale_total_amt'];
	$iapPE['pe_changed'] = date("Y-m-d");
	$iapPE['pe_changed_by'] = 0;
	$iapRet = IAP_Update_Data($iapPE, "parev");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR updating the  party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	echo "<span class=iapSuccess>...Party/Event record was successfully updated.</span><br>";


	$iapJrnl = IAP_Get_Journal_By_Detail("S".$iapSale['sale_type'], $iapSale['sale_id']);
	if ($iapJrnl < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR checking the journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($iapJrnl['status'] == "NEW") {
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_date'] = $iapSale['sale_date'];
		$iapJrnl['jrnl_type'] = "S".$iapSale['sale_type'];
		$iapJrnl['jrnl_description'] = "Sale on ".date("m/d/Y", strtotime($iapSale['sale_date']));
		switch($iapSale['sale_type']) {
			case "E":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." at ".$iapPE['pe_sponsor'];
				break;
			case "F":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." on Facebook";
				break;
			case "I":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." as Sale To Individual";
				break;
			case "O":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." as Some Other Type of Sale";
				break;
			case "P":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." at party #".$iapPE['pe_party_no'];
				break;
			case "W":
				$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." from Website";
				break;
		}
		$iapJrnl['jrnl_net'] = $iapSale['sale_net'];
		$iapJrnl['jrnl_amount'] = $iapSale['sale_total_amt'];
		$iapJrnl['jrnl_tax'] = $iapSale['sale_sales_tax'];
		$iapJrnl['jrnl_shipping'] = $iapSale['sale_shipping'];
		$iapJrnl['jrnl_mileage'] = $iapSale['sale_mileage'];
		$iapJrnl['jrnl_expenses'] = $iapSale['sale_expenses'];
		$iapJrnl['jrnl_exp_explain'] = $iapSale['sale_exp_explained'];
		$iapJrnl['jrnl_profit'] = $iapSale['sale_profit'];
		$iapJrnl['jrnl_vendor'] = $iapPE['pe_sponsor'];
		$iapJrnl['jrnl_cost'] = $iapSale['sale_item_cost'];
		$iapJrnl['jrnl_profit'] = $iapSale['sale_profit'];
		$iapJrnl['jrnl_comment'] = $iapSale['sale_comment'];
		$iapJrnl['jrnl_detail_key'] = $iapSale['sale_id'];
	}
	$iapJrnl['jrnl_description'] = $iapJrnl['jrnl_description']." (REFUNDED on ".date("m/d/Y", strtotime("now"))." by user ".strval($_REQUEST['IAPUID']).")";
	$iapJrnl['jrnl_changed'] = date("Y-m-d");
	$iapJrnl['jrnl_changed_by'] = 0;
	$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR updating journal record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($iapJrnl['status'] == "NEW") {
		echo "<span class=iapSuccess>...Journal was successfully added.</span><br>";
	} else {
		echo "<span class=iapSuccess>...Journal was successfully updated.</span><br>";
	}
	echo "<br><span class=iapSuccess>Sale Successfully refunded!</span><br><br>";

	return(0);
}
?>