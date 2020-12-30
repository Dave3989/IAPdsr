<?php

function IAP_Setup_PE($iapSale) {

// TODO Need to debug this before using it


	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

// -------------------------------------------------------
// Set up PE record
// -------------------------------------------------------

	$iapAddUpd = "";
	$iapGetPE = "N";

	if ($_REQUEST['SNEWPE'] == "Y") {
// will add a new pe rec
		$iapSale['sale_peid'] = 0;
		if ($iapSale['newpe'] == "N") {			// 1st time as new pe.
			$iapSale['newpe'] = "Y";
			$iapSale['perec'] = "";
			$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
			$iapPE = $iapP[0];
		} else {
			$iapPE = $iapSale['perec'];			// had built new pe and saved in savearea.
		}
		$iapAddUpd = "A";

// --- If the sale has a pe id backout this sales totals 
// --- OrigPE will have original PE rec -OR- empty if no pe id in sales record
	} elseif ($iapSale['sale_peid'] > 0) {
		$iapOrigPE = IAP_Get_PartyEvent_By_Id($iapSale['sale_peid']);
		if ($iapOrigPE < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapPE['status'] == "NEW") {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapOrigPE['pe_sales_cnt'] = $iapOrigPE['pe_sales_cnt'] - 1;
		$iapOrigPE['pe_cost_of_items'] = $iapOrigPE['pe_cost_of_items'] - $iapSale['sale_item_cost'];
		$iapOrigPE['pe_net_sales'] = $iapOrigPE['pe_net_sales'] - $iapSale['sale_net'];
		$iapOrigPE['pe_profit'] = $iapOrigPE['pe_profit'] - $iapSale['sale_profit'];
		$iapOrigPE['pe_shipping'] = $iapOrigPE['pe_shipping'] - $iapSale['sale_shipping'];
		$iapOrigPE['pe_sales_tax'] = $iapOrigPE['pe_sales_tax'] - $iapSale['sale_sales_tax'];
		$iapOrigPE['pe_total_sales'] = $iapOrigPE['pe_total_sales'] - $iapSale['sale_total_amt'];
		$iapSale['sale_item_cost'] = 0;
		$iapSale['sale_net'] = 0;
		$iapSale['sale_profit'] = 0;
		$iapSale['sale_shipping'] = 0;
		$iapSale['sale_sales_tax'] = 0;
		$iapSale['sale_total_amt'] = 0;
		$iapAddUpd = "U";
	} elseif ($iapSale['sale_type'] == "E") {	// Event? Get that PE rec
//	  $peText = "Event on ".date("m/d/Y", strtotime($iapP['pe_date']))." at ".trim($iapP['pe_sponsor']);
//	  $peText = date("m/d/Y", strtotime($iapP['pe_date']))." event at ".trim($iapP['pe_sponsor']);
		$iapP = explode(" ", $_REQUEST['speevent']);
		$iapPEDate = $iapP[0];
		$sp = $iapP[3];
		$sl = strpos($_REQUEST['speevent'], $sp);
		$iapSponsor = substr($_REQUEST['speevent'], $sl);
		$iapGetPE = "Y";
	} elseif (empty($_REQUEST['speparty'])) {	// Must be nonType P or E and no party selected.
		$iapSale['sale_peid'] = 0;
		$iapSale['perec'] = "";
		$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
		$iapPE = $iapP[0];
		$iapAddUpd = "A";						// Indicate pe should be added
	} else {
// Any type but E can select a party so get the PE rec.
//	  $peText = "Party ".$iapP['pe_party_no']." on ".date("m/d/Y", strtotime($iapP['pe_date']))." for ".trim($iapP['pe_sponsor']);
//	  $peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
		$iapP = explode(" ", $_REQUEST['speparty']);
		$iapPEDate = array_shift($iapP);
		$x = array_shift($iapP);
		$iapParty = array_shift($iapP);
		$x = array_shift($iapP);
		$iapSponsor = implode(" ", $iapP);
		$iapGetPE = "Y";
	}

	if ($iapGetPE == "Y") {
		$iapPass['table'] = "parev";
		$iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_date = '".date("Y-m-d", strtotime($iapPEDate))."' AND pe_sponsor = '".$iapSponsor."'";
		$iapRet = (array) IAP_Get_Rows($iapPass);
		if ($iapRet['retcode'] < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapPE = (array) $iapRet['data'][0];
		if ($iapPE['status'] == "NEW") {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapSale['sale_peid'] = $iapPE['pe_id'];
		$iapAddUpd = "U";						// Indicate pe should be updated
	}

// --- Has a new PE been indicated 
	if ($_REQUEST['SNEWPE'] == "Y") {
		$iapSale['sale_peid'] = 0;				// Zero out sales pe id
		if ($iapSale['newpe'] == "N") {			// 1st time as new pe?
			$iapSale['newpe'] = "Y";			// YES - Set to not first time
			$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
			$iapPE = $iapP[0];
		} else {
			$iapPE = $iapSale['perec'];			// NO - had built new pe and saved in savearea.
		}
		$iapAddUpd = "A";						// Indicate pe should be added

// --- Has a PE been selected - Get it
	} elseif ($_REQUEST['SNEWPE'] == "S"
		  and $_REQUEST['SNEWPE'] != $iapSale['sale_peid']) {
		$iapPE = IAP_Get_PartyEvent_By_Id($_REQUEST["SSELPE"]);
		if ($iapPE < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the selected party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapPE['status'] == "NEW") {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot find the selected party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapSale['sale_peid'] = $iapPE['pe_id'];
		$iapAddUpd = "U";

// --- Not a new PE nor selected PE
	} else {
		if ($iapSale['sale_peid'] > 0) {
			$iapPE = $iapOrigPE;				// Original pe is good pe
		} else {
			$iapSale['perec'] = "";				// Must be new non-P or E sale so build rec  
			$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
			$iapPE = $iapP[0];
			$iapSale['sale_peid'] = 0;
			$iapAddUpd = "A";					// Indicate pe should be added
		}
	}
	$iapSale['perec'] = $iapPE;					//       Set pe rec in sales to empty then get a new empty rec
	$iapRet = array($iapAddUpd, $iapSale);
	return($iapRet);
}

?>