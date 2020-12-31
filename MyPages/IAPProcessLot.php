<?php

function IAP_Add_Purchase_Lot($iapPurDtl, $iapCat_on_hand, $iapPur_order) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";


	if ($iapPurDtl['purdet_quantity'] > $iapCat_on_hand) {
		$iapPurDtl = IAP_Apply_Purchase_Lot($iapPurDtl, $iapCat_on_hand);
	}
	if ($iapPurDtl['purdet_quantity'] < 1) {
		return 0;
	}

	$iapLot = IAP_Get_Lot($iapPurDtl['purdet_item'], $iapPurDtl['purdet_date'], $iapPurDtl['purdet_cost']);
	if ($iapLot < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapLot['status'] == "NEW") {
		$iapLot['lot_company'] = $_REQUEST['CoId'];
		$iapLot['lot_item_code'] = $iapPurDtl['purdet_item'];
		$iapLot['lot_date'] = $iapPurDtl['purdet_date'];
		$iapLot['lot_cost'] = $iapPurDtl['purdet_cost'];
		$iapLot['lot_count'] = 0;
		$iapLot['lot_po'] = $iapPur_order;
	}
	$iapLot['lot_count'] = $iapLot['lot_count'] + $iapPurDtl['purdet_quantity'];
	$iapLot['lot_changed'] = date("Y-m-d");
	$iapLot['lot_changed_by'] = $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapLot, "plot");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR adding item to the price lot table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	return 0;
}

function IAP_Apply_Purchase_Lot($iapPurDtl, $iapCat_on_hand) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "sdtl";
	$iapPass['cols'] = "iap_sales_detail.*, iap_sales.sale_date";
	$iapPass['join'] = "JOIN iap_sales ON iap_sales.sale_id = iap_sales_detail.saledet_sid";
	$iapPass['where'] = "iap_sales_detail.saledet_company = ".$_REQUEST['CoId'].
						" AND iap_sales_detail.saledet_item_code = '".$iapPurDtl['purdet_item']."'".
						" AND iap_sales_detail.saledet_lot_date = '0000-00-00'".
						" AND iap_sales_detail.saledet_mult_lots_applied = 'N'";
	$iapPass['order'] = "iap_sales.sale_date";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapRet['numrows'] == 0) {
		return $iapPurDtl;
	}
	$iapSaleDet = $iapRet['data'];

	$c = $iapCat_on_hand;
	for($i1 = 0; $i1 < count($iapSaleDet); $i1++) {		// Determine which sales deatil recs need to be 
		$s = $iapSaleDet[$i1];							// satisfied to bring item's on hand to 0
		$c = $c + $s['saledet_quantity'];
		if ($c >= 0) {
			break;
		}
	}

	for ($i2 = $i1-1; $i2 > -1; $i2--) {
		$s = $iapSaleDet[$i2];

		$cdiff = $iapPurDtl['purdet_cost'] - $s['saledet_lot_cost'];
		if ($cdiff != 0) {
			$s['saledet_lot_cost'] = $iapPurDtl['purdet_cost'];
			$s['saledet_lot_date'] = $iapPurDtl['purdet_date'];
			$tc = $s['saledet_total_cost'];
			$s['saledet_total_cost'] = $iapPurDtl['purdet_cost'] * $s['saledet_quantity'];
			$tp = $s['saledet_total_profit'];
			$s['saledet_total_profit'] = $s['saledet_total_price'] - $s['saledet_total_cost'];
			$s['saledet_cost_from_book'] = 'N';
			$iapRet = IAP_Update_Data($s, "sdtl");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$tcdiff = $s['saledet_total_cost'] - $tc;
			$tpdiff = $s['saledet_total_profit'] - $tp;

// update sale record
			$sid = $s['saledet_sid'];
			$iapSale =  IAP_Get_Sale($sid);
			if ($iapSale < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retrieving sales record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapSale['sale_item_cost'] = $iapSale['sale_item_cost'] + $tcdiff;
			$iapSale['sale_profit'] = $iapSale['sale_profit'] + $tpdiff;
			$iapRet = IAP_Update_Data($iapSale, "sale");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating sales record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}

// update party/event record
			$peid = $iapSale['sale_peid'];
			$iapPE = IAP_Get_PartyEvent_By_Id($peid);
			if ($iapPE < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retrieving party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapPE['pe_cost_of_items'] = $iapPE['pe_cost_of_items'] + $tcdiff;
			$iapPE['pe_profit'] = $iapPE['pe_profit'] + $tpdiff;
			$iapRet = IAP_Update_Data($iapPE, "parev");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}
		$iapPurDtl['purdet_quantity'] = $iapPurDtl['purdet_quantity'] - $s['saledet_quantity'];
		if ($iapPurDtl['purdet_quantity'] < 0) {
			$iapPurDtl['purdet_quantity'] = 0;
		}
	}
	return $iapPurDtl;
}

function IAP_Update_Purchase_Lot($iapPurDtl, $iapPur_order) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapLot = IAP_Get_Lot($iapPurDtl['purdet_item'], $iapPurDtl['purdet_date'], $iapPurDtl['purdet_cost']);
	if ($iapLot < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($iapLot['status'] == "NEW") {
		$iapLot['lot_company'] = $_REQUEST['CoId'];
		$iapLot['lot_item_code'] = $iapPurDtl['purdet_item'];
		$iapLot['lot_date'] = $iapPurDtl['purdet_date'];
		$iapLot['lot_cost'] = $iapPurDtl['purdet_cost'];
		$iapLot['lot_count'] = 0;
		$iapLot['lot_po'] = $iapPur_order;
	}
	$iapLot['lot_count'] = $iapLot['lot_count'] + $iapPurDtl['QtyDiff'];
	if ($iapLot['lot_count'] < 1) {
		$iapRet = IAP_Delete_Row($iapLot, "plot");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR deleting row from the purchase lot table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}		
	} else {
		$iapLot['lot_changed'] = date("Y-m-d");
		$iapLot['lot_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapLot, "plot");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR adding item to the price lot table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}
	return 0;
}

function IAP_BackOut_Purchase_Lot($iapPurDet) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapLot = IAP_Get_Lot($iapPurDet['purdet_item'], $iapPurDet['purdet_date'], $iapPurDet['purdet_cost']);
	if ($iapLot < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapLot['lot_count'] = $iapLot['lot_count'] - $iapPurDet['purdet_quantity'];
	if ($iapLot['lot_count'] < 1) {
		$iapRet = IAP_Delete_Row($iapLot, "plot");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR removing item from the price lot table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {
		$iapLot['lot_changed'] = date("Y-m-d");
		$iapLot['lot_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapLot, "plot");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR removing item from the price lot table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}			
	}
}


function IAP_Apply_Lot_To_Sale($AL_SalesDetail, $AL_Price_Cost, $AL_Price_Effective) {

// TODO Update processing 
// 	 don't back it out rather check if need to add or release lot 
// UPDATE Processing
//   Sale had 2 items each with quantity 2
//   Added third item  don't change first two
//   What if remove item from sale
//   What if cancel sale

//* MODS --- 2/19/19 Modifying this function based on 2/18 repricing

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$AL_Quantity = $AL_SalesDetail['saledet_quantity'];
	$AL_Cost = $AL_SalesDetail['saledet_total_cost'];   // added after 2/18/19 repricing
	$AL_SalesDetail['saledet_cost_from_book'] = "N";
	$AL_SalesDetail['saledet_mult_lots_applied'] = "N";
	$iapLotsApplied = array(); 

	if($_REQUEST['debugme'] == "Y") {
		echo " --------------- Processing sales detail with item code of ".$AL_SalesDetail['saledet_item_code'].
			 " quantity = ".number_format($AL_SalesDetail['saledet_quantity'], 0, '.', '').
			 " cost = ".number_format($AL_SalesDetail['saledet_total_cost'], 2, '.', '')."<br>";
	}

	while($AL_Quantity > 0) {
		$iapLot = IAP_Get_1st_Lot($AL_SalesDetail['saledet_item_code']);
		if ($iapLot < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		if ($iapLot == NULL) {
//   No lots found
			echo "<span class=iapWarning>--- No inventory found to apply cost for item ".
			     $AL_SalesDetail['saledet_item_code'].
			     ". Applying  cost from catalog of "
			     .number_format($AL_Price_Cost, 2, '.', '').
			     ".</span><br>";
			wp_ob_end_flush_all();
			flush();

// TODO enhancement
/* -------------------------------------------------------------
  If lots have already been processed
  Price the remainder at book but not
  the whole thing.
    LIKE
		if ($TotalQty < $AL_SalesDetail['saledet_quantity']) {
			echo "<span class=iapError>--- Insufficent inventory found to apply lot to item ".
			     $AL_SalesDetail['saledet_item_code'].
				 " applying book price of ".number_format($AL_Price_Cost, 2, '.', '').
				 " for qunatity of ".$q.
				 "</span><br>";
			wp_ob_end_flush_all();
			flush();
			$q = $AL_SalesDetail['saledet_quantity'] - $TotalQty;
			$TotalQty = $TotalQty + $q;
			$c = $AL_Price_Cost * $q;
			$TotalCost = $TotalCost + $c;

---------------------------------------------------------------- */

			$AL_SalesDetail['saledet_cost_from_book'] = "Y";
			$AL_SalesDetail['saledet_lot_date'] = "0000-00-00";
			$AL_SalesDetail['saledet_lot_cost'] = 0;
			$AL_SalesDetail['saledet_lot_po'] = 0;
			break;
		} elseif ($iapLot['lot_count'] == 0) {
//   This lot has zero quantity
			echo "<span class=iapError>--- Lot with zero count found! - item code of ".$iapLot['lot_item_code'].
				 " Date of ".$iapLot['lot_date']." Cost of ".number_format($iapLot['lot_cost'], 2, '.', '').
				 " Quantity = ".number_format($iapLot['lot_count'], 0, '.', '')."</span><br>";
			$iapRet = IAP_Delete_Row($iapLot, "plot");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if($_REQUEST['debugme'] == "Y") {
				echo " --- <span class=iapWarning>Lot Deleted!</span><br>";
			}
		} else {
			if($_REQUEST['debugme'] == "Y") {
				echo "^^^^^^ Processing lot with item code of ".$iapLot['lot_item_code'].
					 " Date of ".$iapLot['lot_date']." Cost of ".number_format($iapLot['lot_cost'], 2, '.', '').
					 " Quantity of ".number_format($iapLot['lot_count'], 0, '.', '')."<br>";
			}

			if ($iapLot['lot_count'] < $AL_Quantity) {			
// Need more than this lot has
				if($_REQUEST['debugme'] == "Y") {
					if ($AL_SalesDetail['saledet_mult_lots_applied'] != "Y") {
						echo " --------------- Multiple lots needed<br>";
					}
				}
				$AL_SalesDetail['saledet_mult_lots_applied'] = "Y";
				if($_REQUEST['debugme'] == "Y") {
					echo " ^^^^^^^^^^^^^^^^^^ Writing assigned lot with sale id of ".$AL_SalesDetail['saledet_sid'].
						 " Date of ".$iapLot['lot_date'].
						 " Cost of ".number_format($iapLot['lot_cost'], 2, '.', '').
						 " Quantity of ".number_format($iapLot['lot_count'], 0, '.', '')."<br>";
				}

				$LA = (array) IAP_Build_New_Row(array("table" => "sal"));
				if ($LA < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR getting area for an assigned lot row. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$LotAsgn = $LA[0];
				$LotAsgn['salelot_company'] = $_REQUEST['CoId'];
				$LotAsgn['salelot_sid'] = $AL_SalesDetail['saledet_sid'];
				$LotAsgn['salelot_sdseq'] = $AL_SalesDetail['saledet_seq'];
				$LotAsgn['salelot_quantity'] = $iapLot['lot_count'];
				$LotAsgn['salelot_lot_cost'] = $iapLot['lot_cost'];
				$LotAsgn['salelot_lot_date'] = $iapLot['lot_date'];
				$LotAsgn['salelot_original_po'] = $iapLot['lot_po'];
				$iapRet = IAP_Update_Data($LotAsgn, "sal");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR adding assigned lot to ".
						 $AL_SalesDetail['saledet_item_code'].
						 " [FATAL]<br>Please notify Support and provide this reference of /"
						 .basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}

				$iapLotsApplied[] = array("lot_date" => $iapLot['lot_date'], 
										  "lot_cost" => $iapLot['lot_cost'], 
										  "lot_qty" => $iapLot['lot_count']);
				$AL_Quantity = $AL_Quantity - $iapLot['lot_count'];
				$iapRet = IAP_Delete_Row($iapLot, "plot");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				if($_REQUEST['debugme'] == "Y") {
					echo " ^^^^^^^^^^^^^^^^^^ <span class=iapWarning>Lot Deleted!</span><br>";
				}
				continue;
			} else {
				if ($AL_SalesDetail['saledet_mult_lots_applied'] == "N") {
					$AL_SalesDetail['saledet_cost_from_book'] = "N";
					$AL_SalesDetail['saledet_lot_date'] = $iapLot['lot_date'];
					$AL_SalesDetail['saledet_lot_cost'] = $iapLot['lot_cost'];
					$AL_SalesDetail['saledet_lot_po'] = $iapLot['lot_po'];
				} else {
					$LotsApplied[] = array("lot_date" => $iapLot['lot_date'], "lot_cost" => $iapLot['lot_cost'],
										   "lot_qty" => $AL_Quantity, "lot_po" => $iapLot['lot_po']);
				}
				if($_REQUEST['debugme'] == "Y") {
					echo " ^^^^^^^^^^^^^^^^^^ Writing assigned lot with sale id of ".$AL_SalesDetail['saledet_sid'].
						 " Date of ".$iapLot['lot_date'].
						 " Cost of ".number_format($iapLot['lot_cost'], 2, '.', '').
						 " Quantity of ".number_format($AL_Quantity, 0, '.', '')."<br>";
				}

				$iapLA = (array) IAP_Build_New_Row(array("table" => "sal"));
				if ($iapLA < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR getting area for an assigned lot row. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapLotAsgn = $iapLA[0];
				$iapLotAsgn['salelot_company'] = $_REQUEST['CoId'];
				$iapLotAsgn['salelot_sid'] = $AL_SalesDetail['saledet_sid'];
				$iapLotAsgn['salelot_sdseq'] = $AL_SalesDetail['saledet_seq'];
				$iapLotAsgn['salelot_item_code'] = $AL_SalesDetail['saledet_item_code'];
				$iapLotAsgn['salelot_quantity'] = $AL_Quantity;
				$iapLotAsgn['salelot_lot_cost'] = $iapLot['lot_cost'];
				$iapLotAsgn['salelot_lot_date'] = $iapLot['lot_date'];
				$iapLotAsgn['salelot_original_po'] = $iapLot['lot_po'];
				$iapRet = IAP_Update_Data($iapLotAsgn, "sal");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR adding assigned lot to ".$AL_SalesDetail['saledet_item_code']." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapLot['lot_count'] = $iapLot['lot_count'] - $AL_Quantity;
				if ($iapLot['lot_count'] > 0) {
					$iapLot['lot_changed'] = date("Y-m-d");
					$iapLot['lot_changed_by'] = $_REQUEST['IAPUID'];

					if($_REQUEST['debugme'] == "Y") {
						echo " ^^^^^^^^^^^^^^^^^^ Lot updated to new quantity of ".
							 number_format($iapLot['lot_count'], 0, '.', '')."<br>";
					}

					$iapRet = IAP_Update_Data($iapLot, "plot");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
				} else {
					$iapRet = IAP_Delete_Row($iapLot, "plot");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					if($_REQUEST['debugme'] == "Y") {
						echo " ^^^^^^^^^^^^^^^^^^ <span class=iapWarning>Lot Deleted!</span><br>";
					}
				}
				$AL_Quantity = 0;
			}
		}
	}
	if ($AL_SalesDetail['saledet_cost_from_book'] == "Y") {
		$TotalCost = $AL_Price_Cost * $AL_SalesDetail['saledet_quantity'];
		if($_REQUEST['debugme'] == "Y") {
			echo " --------------- Updating sales detail with book price of ".
				 number_format($Catlg["prc_cost"], 2, '.', '');
		}
	} elseif ($AL_SalesDetail['saledet_mult_lots_applied'] == "N") {
		$TotalCost = $AL_SalesDetail['saledet_lot_cost'] * $AL_SalesDetail['saledet_quantity'];
		if($_REQUEST['debugme'] == "Y") {
			echo " --------------- Updating sales detail with cost per lot of ".
				 number_format($AL_SalesDetail['saledet_lot_cost'], 2, '.', '')."<br>";
		}
	} else {
		if($_REQUEST['debugme'] == "Y") {
			echo " --------------- Updating sales detail with cost from multiple lots<br>";
		}
		$AL_SalesDetail['saledet_lot_cost'] = 0;
		$AL_SalesDetail['saledet_lot_date'] = "0000-00-00";
		$TotalCost = 0;
		$TotalQty = 0;
		foreach($iapLotsApplied as $iapAL) {
			$TotalQty = $TotalQty + $iapAL['lot_qty'];
			$TotalCost = $TotalCost + ($iapAL['lot_cost'] * $iapAL['lot_qty']);
		}
		if ($TotalQty < $AL_SalesDetail['saledet_quantity']) {
			echo "<span class=iapError>--- Insufficent inventory found to apply lot to item ".
			     $AL_SalesDetail['saledet_item_code'].
				 " applying book price of ".number_format($AL_Price_Cost, 2, '.', '').
				 " for qunatity of ".$q.
				 "</span><br>";
			wp_ob_end_flush_all();
			flush();
			$q = $AL_SalesDetail['saledet_quantity'] - $TotalQty;
			$TotalQty = $TotalQty + $q;
			$c = $AL_Price_Cost * $q;
			$TotalCost = $TotalCost + $c;
		}
	}
	$TotalProfit = $AL_SalesDetail['saledet_total_price'] - $TotalCost;
	if($_REQUEST['debugme'] == "Y") {
		echo " +++++++++++++++ Sales Detail cost from ".number_format($AL_SalesDetail['saledet_total_cost'], 2, '.', '').
												 " to ".number_format($TotalCost, 2, '.', '').
									" and profit from ".number_format($AL_SalesDetail['saledet_total_profit'], 2, '.', '').
												 " to ".number_format($TotalProfit, 2, '.', '');
		if ($TotalCost == $AL_SalesDetail['saledet_total_cost']) {
			echo " - <span class=iapSuccess>NO CHANGE</span>";
		}
		echo "<br><br>";
	}
	$AL_SalesDetail['saledet_total_cost'] = $TotalCost;
	$AL_SalesDetail['saledet_total_profit'] = $TotalProfit;
	return($AL_SalesDetail);
}


function IAP_BackOut_Sale_Lot($BL_SalesDetail, $BL_Display) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($BL_SalesDetail['saledet_cost_from_book'] == "Y") {
		return(0);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "<span class=iapSuccess>......Backing out lot for item".$BL_SalesDetail['saledet_item_code'].
			 " with net=".number_format($BL_SalesDetail['saledet_total_price'], 2, '.', '').
			 " cost=".number_format($BL_SalesDetail['saledet_total_cost'], 2, '.', '').
			 " profit=".number_format($BL_SalesDetail['saledet_total_profit'], 2, '.', '')."</span><br>";
	}

	$BL_AppLots = IAP_Get_SaleLot($BL_SalesDetail['saledet_sid'], $BL_SalesDetail['saledet_seq']);
	if ($BL_AppLots < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR could not retrieve assigned lots for ".$AL_SalesDetail['saledet_item_code']." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	foreach($BL_AppLots as $al) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "<span class=iapWarning>IAPProcessLot-BackOut_Sale_Lot calling Readd_Lot (2).<br>";
		}

		if ($al['salelot_quantity'] > 0) {
			IAP_Readd_Lot($BL_SalesDetail, $al, $BL_Display);
			$iapRet = IAP_Delete_Row($al, "sal");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR deleting row from sales applied lots table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}
	}
	return(0);
}

function IAP_Readd_Lot($BL_SalesDetail, $BL_Lot, $BL_Display = "Y") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

// get purchase lot if it exists
	$iapLot = IAP_Get_Lot($BL_SalesDetail['saledet_item_code'], $BL_Lot['salelot_lot_date'], $BL_Lot['salelot_lot_cost']);
	if ($iapLot < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR getting area for a lot row. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($iapLot['status'] == "NEW") {

		if ($_REQUEST['debugme'] == "Y") {
			echo "......Purchase lot does not exist --- Adding!";
		}

		$iapLot['lot_company'] = $_REQUEST['CoId'];
		$iapLot['lot_item_code'] = $BL_SalesDetail['saledet_item_code'];
		$iapLot['lot_date'] = $BL_SalesDetail['sale_date'];
		$iapLot['lot_count'] = 0;
		$iapLot['lot_cost'] = $BL_Lot['salelot_lot_cost'];
		$iapLot['lot_po'] = $BL_Lot['salelot_original_po'];
	}
	$iapLot['lot_count'] = $iapLot['lot_count'] + $BL_Lot['salelot_quantity'];
	$iapLot['lot_changed'] = date("Y-m-d", strtotime("now"));
	$iapLot['lot_changed_by'] =  $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapLot, "plot");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR readding purchase lot to ".$AL_SalesDetail['saledet_item_code']." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if ($BL_Display == "Y") {
		echo "<span class=iapSuccess>......Readded lot for ".$iapLot['lot_item_code'].
			 " date=".date("Y-m-d", strtotime($iapLot['lot_date'])).
			 " cost=".number_format($iapLot['lot_cost'], 2, '.', '').
			 " count=".number_format($iapLot['lot_count'], 0, '.', '').
			 " po=".$iapLot['lot_po'].
			 "</span><br>";
	}
}

?>