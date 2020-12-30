<?php

// Reprice sales based on current contents of purchase Lots and Price tables

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['sec_use_application'] = "Y";
require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

$repYear = "ALL";	// can be any year or ALL


$StartSale = 0;

$ThisPE = 0;
$PECost = 0;
$SalesDone = 0;

//--- get sales rows with date if not ALL
$SaleList = Reprice_Get_Sale_List($repYear, $StartSale);
if ($SaleList < 0) {
	echo "<span class=iapError>IAP DATABASE ERROR getting list of sales. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

foreach($SaleList as $SL) {

//------ only reprice sale from our inventory
	if ($SL['sale_type'] != "E"
	and $SL['sale_type'] != "I"
	and $SL['sale_type'] != "P") {
		continue;	
	}

//------ get sales record 
	$Sale = Reprice_Get_Sale($SL['sale_id']);
	if ($Sale < 0) {
		echo "<span class=iapError>IAP DATABASE ERROR getting sales record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
 	}

	if (!(set_time_limit(800))) {
		echo "<span style=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	echo "<br><span class=iapWarning> >>>>> Repricing sale with id ".strval($Sale['sale_id']).
			" dated ".date("m/d/Y", strtotime($Sale[sale_date])).
			" Current cost is ".number_format($Sale['sale_item_cost'], 2, '.', '').
			" Current price is ".number_format($Sale['sale_net'], 2, '.', '').
			" Current profit is ".number_format($Sale['sale_profit'], 2, '.', '').
			"</span><br>";
	$NewSaleCost = 0;
	$NewSaleProfit = 0;

//------ get sales detail for sale
	$SaleDet = Reprice_Get_SaleDet($Sale[sale_id]); 
	if ($SaleDet < 0) {
		echo "<span class=iapError>IAP DATABASE ERROR getting sales detail record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//------ assign lot to determine cost/price
	foreach($SaleDet as $SD) {

		echo " --------------- Processing sales detail with item code of ".$SD['saledet_item_code']." quantity = ".number_format($SD['saledet_quantity'], 0, '.', '')." cost = ".number_format($SD['saledet_total_cost'], 2, '.', '')."<br>";

		$Catlg = IAP_Get_Catalog($SD['saledet_item_code'], $Sale['sale_date']);

		$SDQty = $SD['saledet_quantity'];
		$SDCost = $SD['saledet_total_cost'];
		$SD['saledet_cost_from_book'] = "N";
		$SD['saledet_mult_lots_applied'] = "N";
		$LotsApplied = array(); 
		while($SDQty > 0) {
			$Lot = Reprice_Get_1st_Lot($SD['saledet_item_code'], $Sale['sale_date']);
			if ($Lot < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($Lot == NULL) {
//   No lots found
				echo "<span class=iapError> --------------- No inventory found to apply cost for item ".$SD['saledet_item_code'].". Applying  cost from catalog of ".number_format($Catlg['prc_cost'], 2, '.', '').".</span><br>";
				wp_ob_end_flush_all();
				flush();
				$SD['saledet_cost_from_book'] = "Y";
				$SD['saledet_lot_date'] = "0000-00-00";
				$SD['saledet_lot_cost'] = 0;
				break;
			} elseif ($Lot['lot_count'] == 0) {
				echo "<span class=iapError> ^^^^^^^^^^^^^^^^^^ Lot with zero count - item code of ".$Lot['lot_item_code'].
					 " Date of ".$Lot['lot_date']." Cost of ".number_format($Lot['lot_cost'], 2, '.', '').
					 " Quantity = ".number_format($Lot['lot_count'], 0, '.', '')."</span><br>";
				$iapRet = IAP_Delete_Row($Lot, "reprpurlot");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				echo " --- <span class=iapWarning>Lot Deleted!</span><br>";
			} else {
				echo " ^^^^^^^^^^^^^^^^^^ Processing lot with item code of ".$Lot['lot_item_code']." Date of ".$Lot['lot_date']." Cost of ".number_format($Lot['lot_cost'], 2, '.', '')." Quantity of ".number_format($Lot['lot_count'], 0, '.', '')."<br>";

				if ($Lot['lot_count'] < $SDQty) {
// Need more than this lot has
					if ($SD['saledet_mult_lots_applied'] != "Y") {
						echo " --------------- Multiple lots needed<br>";
					}
					$SD['saledet_cost_from_book'] = "N";
					$SD['saledet_mult_lots_applied'] = "Y";
					echo " ^^^^^^^^^^^^^^^^^^ Writing assigned lot with sale id of ".$SD['saledet_sid']." Date of ".$Lot['lot_date']." Cost of ".number_format($Lot['lot_cost'], 2, '.', '')." Quantity of ".number_format($Lot['lot_count'], 0, '.', '')."<br>";
					$LA = (array) IAP_Build_New_Row(array("table" => "reprsallot"));
					if ($LA < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR getting area for an assigned lot row. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					$LotAsgn = $LA[0];
					$LotAsgn['salelot_company'] = $_REQUEST['CoId'];
					$LotAsgn['salelot_sid'] = $SD['saledet_sid'];
					$LotAsgn['salelot_sdseq'] = $SD['saledet_seq'];
					$LotAsgn['salelot_quantity'] = $Lot['lot_count'];
					$LotAsgn['salelot_lot_cost'] = $Lot['lot_cost'];
					$LotAsgn['salelot_lot_date'] = $Lot['lot_date'];
					$LotAsgn['salelot_original_po'] = $Lot['lot_po'];
					$iapRet = IAP_Update_Data($LotAsgn, "reprsallot");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR adding assigned lot to ".$SD['saledet_item_code']." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}

					$LotsApplied[] = array("lot_date" => $Lot['lot_date'], "lot_cost" => $Lot['lot_cost'], "lot_qty" => $Lot['lot_count'], "lot_po" => $Lot['lot_po']);
					$SDQty = $SDQty - $Lot['lot_count'];
					$Ret = IAP_Delete_Row($Lot, "reprpurlot");
					if ($Ret < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					echo " ^^^^^^^^^^^^^^^^^^ <span class=iapWarning>Lot Deleted!</span><br>";
					continue;
				} else {
//   This lot has sufficent quantity
					if ($SD['saledet_mult_lots_applied'] == "N") {
						$SD['saledet_cost_from_book'] = "N";
						$SD['saledet_lot_date'] = $Lot['lot_date'];
						$SD['saledet_lot_cost'] = $Lot['lot_cost'];
						$SD['saledet_lot_po'] = $Lot['lot_po'];
					} else {
						$LotsApplied[] = array("lot_date" => $Lot['lot_date'], "lot_cost" => $Lot['lot_cost'],
											   "lot_qty" => $Lot['lot_count'], "lot_po" => $Lot['lot_po']);
					}
					echo " ^^^^^^^^^^^^^^^^^^ Writing assigned lot with sale id of ".$SD['saledet_sid']." Date of ".$Lot['lot_date']." Cost of ".number_format($Lot['lot_cost'], 2, '.', '')." Quantity of ".number_format($SDQty, 0, '.', '')."<br>";
					$LA = (array) IAP_Build_New_Row(array("table" => "reprsallot"));
					if ($LA < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR getting area for an assigned lot row. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					$LotAsgn = $LA[0];
					$LotAsgn['salelot_company'] = $_REQUEST['CoId'];
					$LotAsgn['salelot_sid'] = $SD['saledet_sid'];
					$LotAsgn['salelot_sdseq'] = $SD['saledet_seq'];
					$LotAsgn['salelot_item_code'] = $Lot['lot_item_code'];
					$LotAsgn['salelot_quantity'] = $SDQty;
					$LotAsgn['salelot_lot_cost'] = $Lot['lot_cost'];
					$LotAsgn['salelot_lot_date'] = $Lot['lot_date'];
					$LotAsgn['salelot_original_po'] = $Lot['lot_po'];
					$iapRet = IAP_Update_Data($LotAsgn, "reprsallot");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR adding assigned lot to ".$SD['saledet_item_code']." [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}

					$Lot['lot_count'] = $Lot['lot_count'] - $SDQty;
					if ($Lot['lot_count'] > 0) {
						$Lot['lot_changed'] = date("Y-m-d");
						$Lot['lot_changed_by'] = $_REQUEST['IAPUID'];
						echo " ^^^^^^^^^^^^^^^^^^ Lot updated to new quantity of ".number_format($Lot['lot_count'], 0, '.', '')."<br>";
						$iapRet = IAP_Update_Data($Lot, "reprpurlot");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
					} else {
						$iapRet = IAP_Delete_Row($Lot, "reprpurlot");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete item from the lot table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
						echo " ^^^^^^^^^^^^^^^^^^ <span class=iapWarning>Lot Deleted!</span><br>";
					}
					$SDQty = 0;
				}
			}
		}

		if ($SD['saledet_cost_from_book'] == "Y") {
			$iapTC = $Catlg["prc_cost"] * $SD['saledet_quantity'];
//			$SD['saledet_total_cost'] = $Catlg["prc_cost"] * $SD['saledet_quantity'];
			echo " --------------- Updating sales detail with book price of ".number_format($Catlg["prc_cost"], 2, '.', '')."<br>";
		} elseif ($SD['saledet_mult_lots_applied'] == "N") {
			$iapTC = $SD['saledet_lot_cost'] * $SD['saledet_quantity'];
//			$SD['saledet_total_cost'] = $SD['saledet_lot_cost'] * $SD['saledet_quantity'];
			echo " --------------- Updating sales detail with cost per lot of ".number_format($SD['saledet_lot_cost'], 2, '.', '')."<br>";
		} else {
			echo " --------------- Updating sales detail with cost from multiple lots<br>";
			$SD['saledet_lot_cost'] = 0;
			$SD['saledet_lot_date'] = "0000-00-00";
			$iapTC = 0;
			foreach($LotsApplied as $AL) {
				if ($AL['lot_date'] == "-1") {
					echo "<span class=iapWarning> --------------- Insufficent inventory found to apply lot to item ".$SD['saledet_item_code']."</span><br>";

//////////////////////////////////////////////////////
// x = subtract accumulated qty from needeed qty
// total cost = accumulated cost + (x * book price)
////////////////////////////////////////////////////// 

					wp_ob_end_flush_all();
					flush();
					break;
				}
				$iapTC = $iapTC + ($AL['lot_cost'] * $AL['lot_qty']);
			}
		}
		$iapTP = $SD['saledet_total_price'] - $iapTC;
		echo " +++++++++++++++ Sales Detail cost from ".number_format($SD['saledet_total_cost'], 2, '.', '').
												 " to ".number_format($iapTC, 2, '.', '').
									" and profit from ".number_format($SD['saledet_total_profit'], 2, '.', '').
												 " to ".number_format($iapTP, 2, '.', '');
		if ($iapTC == $SD['saledet_total_cost']) {
			echo " - <span class=iapSuccess>NO CHANGE</span>";
		}
		echo "<br><br>";

		$SD['saledet_total_cost'] = $iapTC;
		$SD['saledet_total_profit'] = $iapTP;

		$NewSaleCost = $NewSaleCost + $SD['saledet_total_cost'];
		$NewSaleProfit = $NewSaleProfit + $SD['saledet_total_profit'];

		$iapRet = IAP_Update_Data($SD, "reprsaldtl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update item from the sales detail table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}
	echo " >>>>> Writing Sale with price of ".number_format($Sale['sale_net'], 2, '.', '').
						 " original cost of ".number_format($Sale['sale_item_cost'], 2, '.', '').
						 " original profit of ".number_format($Sale['sale_profit'], 2, '.', '').
						 " Updating to ".
						 " cost of ".number_format($NewSaleCost, 2, '.', '').
						 " profit of ".number_format($NewSaleProfit, 2, '.', '');
	if ($NewSaleCost == $Sale['sale_item_cost']) {
		echo " - <span class=iapSuccess>NO CHANGE</span>";
	}
	echo "<br>";

	$Sale['sale_item_cost'] = $NewSaleCost;
	$Sale['sale_profit'] = $NewSaleProfit;

	$iapRet = IAP_Update_Data($Sale, "reprsal");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update item from the sales table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
}

echo "<br><br><span style='color:#2eb82e'> ---<(@)>--- Repricing of sales complete. Updating Party/Event with new sales cost and profit.</span><br><br>";

$PE_List = Reprice_Get_PE_List();
if ($PE_List < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve party/event list. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
if ($PE_List == NULL) {
	echo "<span class=iapError>IAP INTERNAL ERROR: No parties/events found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
foreach($PE_List as $PE) {
	echo "<br>>>>>> Updating Party/Event with id ".strval($PE['pe_id'])." dated ".date("m/d/Y", strtotime($PE[pe_date]))."<br>";
	$SaleSum = repricing_sales_summary($PE['pe_id']);
	if ($SaleSum < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot get sales for party/event with id ".strval($PE['pe_id'])." dated ".date("m/d/Y", strtotime($PE[pe_date]))."[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($SaleSum == NULL) {
		echo "<span class=iapError> --------------- No Sales found for id ".strval($PE['pe_id'])." item cost and net sales set to zero. Review and fix other balances!!!</span><br>";		
		$SaleSum['item_cost'] = 0;
		$SaleSum['sale_profit'] = 0;
	}
	echo " ---------------Updating cost from ".number_format($PE['pe_cost_of_items'], 2, '.', '').
		 								" to ".number_format($SaleSum['item_cost'], 2, '.', '').
		 		   		   " and profit from ".number_format($PE['pe_profit'], 2, '.', '').
		 		   						" to ".number_format($SaleSum['sale_profit'], 2, '.', '')."<br>";
	$PE['pe_cost_of_items'] = $SaleSum['item_cost'];
	$PE['pe_profit'] = $SaleSum['sale_profit'];
	$iapRet = IAP_Update_Data($PE, "reprparev");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update party/event. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
}

echo "<br><span class=iapSuccess>All Done!</span><br>";


function Reprice_Get_Sale_List($reprice_year, $StartSale) {
	if ($reprice_year == "ALL") {
		$begin = "2010-01-01";
		$end = "2099-12-31";
	} else {
		$begin = $reprice_year."-01-01";
		$end = $reprice_year."-12-31";
	}
	$iapPass['table'] = "reprsal";
	$iapPass['cols'] = "sale_id, sale_type";
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_date >= '".$begin."' AND sale_date <= '".$end."' AND sale_id >= ".strval($StartSale);
	$iapPass['order'] = "sale_date, sale_id";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapSaleList = (array) $iapRet['data'];
	return($iapSaleList);
}

function Reprice_Get_Sale($iapSaleId) {
	$iapPass['table'] = "reprsal";
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_id = ".$iapSaleId;
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	$iapRec = (array) $iapRet['data'][0];
	return($iapRec);
}

function Reprice_Get_SaleDet($SaleId) {
	$iapPass['table'] = "reprsdtl";
	$iapPass['cols'] = "repricing_sales_detail.*, 'CO' as CO_ID, iap_catalog.cat_description as CO_description, iap_catalog.cat_item_code as CO_item_code, 
	iap_supplier_catalog.cat_supplier_id AS SUPP_ID, iap_supplier_catalog.cat_description as SUPP_description, iap_supplier_catalog.cat_item_code as SUPP_item_code, 
	repricing_sales.sale_peid, repricing_sales.sale_date, repricing_party_events.pe_sponsor, repricing_party_events.pe_type, repricing_party_events.pe_party_no ";

	$iapPass['join'] = "LEFT JOIN iap_catalog ON iap_catalog.cat_company = saledet_company AND UPPER(iap_catalog.cat_item_code) = UPPER(saledet_item_code) ";
	$iapPass['join'] = $iapPass['join']."LEFT JOIN iap_supplier_catalog ON iap_supplier_catalog.cat_supplier_id = saledet_item_source AND UPPER(iap_supplier_catalog.cat_item_code) = UPPER(saledet_item_code) ";
	$iapPass['join'] = $iapPass['join']."JOIN repricing_sales ON sale_company = saledet_company AND sale_id = saledet_sid ";
	$iapPass['join'] = $iapPass['join']."JOIN repricing_party_events ON pe_company = saledet_company AND pe_id = sale_peid";
	$iapPass['where'] = "saledet_company = ".$_REQUEST['CoId']." AND saledet_sid = ".$SaleId;
	$iapPass['order'] = "saledet_seq";

	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	$iapRecs = (array) $iapRet['data'];
	return($iapRecs);
}

function Reprice_Get_PartyEvent_By_Id($iapPEID) {
    $iapPass['table'] = "parev";
    $iapPass['cols'] = "repricing_party_events.*, iap_avalara_sales_tax.tax_combined_rate, iap_avalara_sales_tax.tax_region_name";
    $iapPass['join'] = "LEFT JOIN iap_avalara_sales_tax on iap_avalara_sales_tax.tax_zip_code = repricing_party_events.pe_zip ";
    $iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_id = ".strval($iapPEID);
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	$iapPERec = (array) $iapRet['data'][0];
    return($iapPERec);
}

function Reprice_Get_1st_Lot($iapItem, $iapSaleDate) {
    $iapPass['table'] = "reprpurlot";
    $iapPass['where'] = "lot_company = ".$_REQUEST['CoId']." AND UPPER(lot_item_code) = '".strtoupper($iapItem)."' AND lot_date <= '".$iapSaleDate."'";
    $iapPass['order'] = "lot_date";
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapLotRec = (array) $iapRet['data'][0];	// this would be the first lot record (FIFO).
    return($iapLotRec);
}

function Reprice_Get_PE_List() {
	$iapPass['table'] = "reprparev";
	$iapPass['where'] = "pe_company = ".$_REQUEST['CoId'];
    $iapPass['order'] = "pe_date";
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapPERecs = (array) $iapRet['data'];
    return($iapPERecs);
}

function repricing_sales_summary($PE_Id) {
	$iapPass['table'] = "reprsal";
	$iapPass['cols'] = "sum(sale_item_cost) as item_cost, sum(sale_profit) as sale_profit";
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_peid = ".$PE_Id;
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapRec = (array) $iapRet['data'][0];
	return($iapRec);	
}

?>