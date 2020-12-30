<?php

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Party/Event Maintenance with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

$SaleSQL = "select * from iap_saledata_sales order by sale_peid, sale_customer";
$iapRet = IAPProcessMySQL("select", $SaleSQL);
if ($iapRet['retcode'] < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR cannot retrieve iap_saledata_sales. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
if ($iapRet['numrows'] == 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR no sales rows returned from iap_saledata_sales. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
$iapSaleRecs = $iapRet['data'];
foreach($iapSaleRecs as $iapSale) {

	if (!(set_time_limit(800))) {
		echo "<span style=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	$iapS2 = IAP_Get_Sale_By_PE_Cust($iapSale['sale_peid'], $iapSale['sale_customer']);
	if ($iapS2 < 0) {
	echo " <span class=iapError>IAP INTERNAL ERROR retreiving Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapS2 == NULL) {

		$iapPE = IAP_Get_PartyEvent_By_Id($iapSale['sale_peid']);
		if ($iapPE < 0) {
		echo " <span class=iapError>IAP INTERNAL ERROR caanot get PE for ".strval($iapSale['sale_peid'])."writing Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

// -------------------------------------------------------
// ... Add the sale record
// -------------------------------------------------------
		$pe = "...Adding";
		$pd = "added";

		echo $pe." sale record of type ".$iapSale['sale_type'].
		                  " for P/E Id ".strval($iapSale['sale_peid']).
		                  " on ".$iapSale['sale_date'].
		                  " customer ".$iapSale['sale_customer'].
		                  " .<br>";
		wp_ob_end_flush_all();
		flush();
		$iapSale['sale_changed'] = date("Y-m-d");
		$iapSale['sale_changed_by'] = $_REQUEST['IAPUID'];
		$iapSale['status'] = "NEW";
		$iapRet = IAP_Update_Data($iapSale, "sale");
		if ($iapRet < 0) {
			echo " <span class=iapError>IAP INTERNAL ERROR writing Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapSale['status'] == "NEW") {
			$iapSale['sale_id'] = $iapRet;
		}
		echo "<span class=iapSuccess>Sale was successfully ".$pd.".</span><br>";

		echo $pe." items sold detail and updating item's on-hand balance.<br>";
		wp_ob_end_flush_all();
		flush();
		$iapWasNewItem = "N";
		$iapSale['sale_item_cost'] = 0;
		$iapSale['sale_net'] = 0;
		$iapSale['sale_profit'] = 0;

		$SaleDetSQL = "select * from iap_saledata_saledet where saledet_peid = ".$iapSale['sale_peid']." and saledet_customer_no = ".$iapSale['sale_customer']." order by saledet_item_code";
		$iapRet = IAPProcessMySQL("select", $SaleDetSQL);
		if ($iapRet['retcode'] < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR cannot retrieve iap_saledata_saledet. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapRet['numrows'] == 0) {
			continue;
		}
		$iapSaleDetRecs = $iapRet['data'];
		foreach($iapSaleDetRecs as $iapSaleDet) {

			if (substr($iapSaleDet['saledet_item_code'],0,4) == "CUST") {
				$iapSaleDet['saledet_item_code'] = "111111";
			}

// ------------------------------------------------------------------
// ... Add/Update the sale detail records
// ------------------------------------------------------------------
			$iapSaleDet['saledet_sid'] = $iapSale['sale_id'];
			echo "... Processing item ".$iapSaleDet['saledet_item_code'].".<br>";
			wp_ob_end_flush_all();
			flush();
			$iapSaleDet['status'] = "NEW";
			$iapRet = IAP_Update_Data($iapSaleDet, "sdtl");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR adding item to the price table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapSaleDet['saledet_seq'] = $iapRet;
			$iapSaleDet['status'] = "EXISTING";

// ------------------------------------------------------------------
// ...... Get the item, add if new, update on hand
// ------------------------------------------------------------------
			$iapCtlg = IAP_Get_Catalog($iapSaleDet['saledet_item_code'], "N");
			if ($iapCtlg < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the catalog table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}

			if ($iapCtlg['status'] == "NEW") {
				$iapCtlg['cat_company'] = $_REQUEST['CoId'];
				$iapCtlg['cat_item_code'] = $iapSaleDet['saledet_item_code'];
				$iapCtlg['cat_description'] = $iapSaleDet['saledet_desc'];
				$iapCtlg['cat_supplier'] = $iapSale['sale_location'];
				$iapCtlg['cat_changed'] = date("Y-m-d");
				$iapCtlg['cat_changed_by'] = $_REQUEST['IAPUID'];
				$iapRet = IAP_Update_Data($iapCtlg, "ctlg");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating item in the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}

				$iapI = (array) IAP_Build_New_Row(array("table" => "inv"));
				$iapInv = $iapI[0];
				if ($iapInv < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR adding item to the inventory [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapInv['inv_company'] = $_REQUEST['CoId'];
				$iapInv['inv_item_code'] = $iapCtlg['cat_item_code'];
				$iapInv['inv_on_order'] = 0;
				$iapInv['inv_on_hand'] = 0;
				$iapInv['inv_min_onhand'] = 0;
				$iapInv['inv_changed'] = date("Y-m-d");
				$iapInv['inv_changed_by'] = $_REQUEST['IAPUID'];
				$iapRet = IAP_Update_Data($iapInv, "inv");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating inventory item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapCtlg = array_merge($iapCtlg, $iapInv);
				$iapCtlg['status'] = "EXISTING";

				echo "...... NEW ITEM, ".$iapCtlg['cat_item_code'].", being added to the catalog. It may require additional information.<br>";
				wp_ob_end_flush_all();
				flush();

				$iapWasNewItem = "Y";

// ------------------------------------------------------------------
// ...... Create price record for new item
// ------------------------------------------------------------------
				$iapNewData = $_REQUEST['snewiteminfo'];
				$iapItemData = explode("|", $iapNewData);
				$iapNewUnits = 0;
				$iapNewCost = 0;
				$iapPrc = (array) IAP_Build_New_Row(array("table" => "prc"));
				if ($iapPrc < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR adding item to the price table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapPrice = $iapPrc[0];
				$iapPrice['prc_company'] = $_REQUEST['CoId'];
				$iapPrice['prc_item_code'] = $iapSaleDet['saledet_item_code'];
				$iapPrice['prc_effective_until'] = "2099-12-31";
				$iapPrice['prc_effective'] = "2010-01-01";
				$iapPrice['prc_cost'] = 0;
				$iapPrice['prc_units'] = 0;
				$iapPrice['prc_cost_unit'] = 0;
				$iapPrice['prc_price'] = $iapSaleDet['saledet_price'];
				$iapPrice['prc_cat_code'] = "UNKNOWN";
				$iapPrice['prc_changed'] = date("Y-m-d");
				$iapPrice['prc_changed_by'] = $_REQUEST['IAPUID'];
				$iapRet = IAP_Update_Data($iapPrice, "prc");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR adding item to the price table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				echo "...... NEW PRicing, ".$iapCtlg['cat_item_code'].", MUST BE UPDATED. It WILL require additional information.<br>";
				wp_ob_end_flush_all();
				flush();
				$iapSaleDet['saledet_total_cost'] = $iapPrice['prc_cost_unit'] * $iapSaleDet['saledet_quantity'];
			} else {

// ------------------------------------------------
// Item exists so assign lots 
// ------------------------------------------------
				require_once(ABSPATH."MyPages/IAPProcessLot.php");
				$iapSaleDet = IAP_Apply_Lot_To_Sale($iapSaleDet);
			}

// ------------------------------------------------
// Update catalog on-hand and write it 
// ------------------------------------------------
			$iapCtlg['inv_on_hand'] = $iapCtlg['inv_on_hand'] - $iapSaleDet['saledet_quantity'];
			$iapRet = IAP_Update_Data($iapCtlg, "inv");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR adding item to the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}

// ------------------------------------------------
// Finish sales detail calculations and write it 
// ------------------------------------------------
			$iapSaleDet['saledet_total_price'] = $iapSaleDet['saledet_price'] * $iapSaleDet['saledet_quantity'];
			$iapSaleDet['saledet_total_profit'] = $iapSaleDet['saledet_total_price'] - $iapSaleDet['saledet_total_cost'];
			$iapRet = IAP_Update_Data($iapSaleDet, "sdtl");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR writing Sale detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			echo "<span class=iapSuccess>On-hand was successfully updated for item ".$iapSaleDet['saledet_item_code'].".</span><br>";
			wp_ob_end_flush_all();
			flush();

			$iapSale['sale_item_cost'] = $iapSale['sale_item_cost'] + $iapSaleDet['saledet_total_cost'];
			$iapSale['sale_net'] = $iapSale['sale_net'] + $iapSaleDet['saledet_total_price'];
			$iapSale['sale_profit'] = $iapSale['sale_profit'] + $iapSaleDet['saledet_total_profit'];
		}

// ------------------------------------------------
// Final sales data updated so write it again 
// ------------------------------------------------
		$iapSale['sale_changed'] = date("Y-m-d");
		$iapSale['sale_changed_by'] = $_REQUEST['IAPUID'];
		$iapSale['status'] = "EXISTING";
		$iapRet = IAP_Update_Data($iapSale, "sale");
		if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

// ------------------------------------------------
// Update PE's sales figures and write it again 
// ------------------------------------------------
		echo "...Updating Party/Event record for sale.</span><br>";
		wp_ob_end_flush_all();
		flush();

		$iapPE['pe_sales_cnt'] = $iapPE['pe_sales_cnt'] + 1;
		$iapPE['pe_cost_of_items'] = $iapPE['pe_cost_of_items'] + $iapSale['sale_item_cost'];
		$iapPE['pe_net_sales'] = $iapPE['pe_net_sales'] + $iapSale['sale_net'];
		$iapPE['pe_profit'] = $iapPE['pe_profit'] + $iapSale['sale_profit'];
		$iapPE['pe_changed'] = date("Y-m-d");
		$iapPE['pe_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapPE, "parev");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<span class=iapSuccess>Party/Event was successfully updated.</span><br>";
		wp_ob_end_flush_all();
		flush();

// ------------------------------------------------
// Journal the sale  
// ------------------------------------------------
		echo "...Adding Journal record for sale.</span><br>";
		wp_ob_end_flush_all();
		flush();

		$iapJ = (array) IAP_Build_New_Row(array("table" => "jrnl"));
		if ($iapJrnl < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR getting row for journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapJrnl = $iapJ[0];
		$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
		$iapJrnl['jrnl_date'] = $iapSale['sale_date'];
		if ($iapSale['sale_type'] == "P") {
			$t = "Party";
		} else {
			$t = "Event";
		}
		$iapJrnl['jrnl_description'] = $t." for ".$iapSale['sale_sponsor']." on ".date("m/d/Y", strtotime($iapSale['sale_date']));
		$iapJrnl['jrnl_type'] = "S".$iapSale['sale_type'];
		$iapJrnl['jrnl_amount'] = $iapSale['sale_net'];
		$iapJrnl['jrnl_tax'] = $iapSale['sale_sales_tax'];
		$iapJrnl['jrnl_shipping'] = $iapSale['sale_shipping'];
		$iapJrnl['jrnl_mileage'] = $iapSale['sale_mileage'];
		$iapJrnl['jrnl_expenses'] = $iapSale['sale_expenses'];
		$iapJrnl['jrnl_exp_explain'] = $iapSale['sale_exp_explained'];
		$iapJrnl['jrnl_vendor'] = $iapSale['sale_sponsor'];
		$iapJrnl['jrnl_cost'] = $iapSale['sale_item_cost'];
		$iapJrnl['jrnl_profit'] = $iapSale['sale_profit'];
		$iapJrnl['jrnl_comment'] = $iapSale['sale_comment'];
		$iapJrnl['jrnl_detail_key'] = $iapSale['sale_id'];
		$iapJrnl['jrnl_changed'] = date("Y-m-d");
		$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "<span class=iapSuccess>Journal was successfully added.</span><br>";
		wp_ob_end_flush_all();
		flush();
	}
}

echo "<span class=iapSuccess>Updating Complete.</span><br><hr><br>";

?>