<?php

// TODO Add payment type Electronic - Paypal. etc

// TODO Sales to consultant



/*
sale types
E = event need an event address (selectable or add)
F = facebook can have party id 
P = party need a party number (selectable or add)
O = other
I = individual direct need a customer record 
W = website can have party id - need number from online (SALE DOES NOT REDUCE INVENTORY)
X = exchange is in another program
*/

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Sales with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("291") < 0) {
	return;
};

if ($_REQUEST['action'] == 'selected') {

	$iapOrigAction = $_REQUEST['action'];

	if (IAP_Remove_Savearea("IAP291SA") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the Sale savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapSId = $_REQUEST['sale'];
	$iapSale = IAP_Get_Sale($iapSId);
	if ($iapSale < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapSale['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapSale['PEOrig'] = $iapSale['sale_peid'];

	$iapCust = IAP_Get_Customer_By_No($iapSale['sale_customer']);
	if ($iapCust < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive customer for selected sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapCust['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: Customer missing for selected sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	}

	$iapSaleDet = IAP_Get_SaleDet($iapSale['sale_id']);

//	$iapSaleDet = IAP_Get_SaleDet_For_Cust($iapSale['sale_customer']);
	if ($iaSaleDet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR retreiving prior sale detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapItems = array();
	foreach($iapSaleDet as $iapSD) {
		$iapColumns[0] = $iapSD['saledet_item_code'];
		if (empty($iapSD['CO_description'])) {
			$iapColumns[1] = $iapSD['SUPP_description'];
		} else {
			$iapColumns[1] = $iapSD['CO_description'];
		}
		$iapColumns[2] = $iapSD['saledet_quantity'];
		$iapColumns[3] = $iapSD['saledet_price'];
		$iapColumns[4] = $iapSD['saledet_total_price'];
		if (empty($iapSD['CO_description'])) {
			$iapColumns[5] = $iapSD['SUPP_ID'];
		} else {
			$iapColumns[5] = 0;
		}
		$iapItems[] = implode("~", $iapColumns);
	}
	$iapSale['saledtl'] = $iapItems;
	$iapRet = IAP_Create_Savearea("IAP291SA", $iapSale, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//	$DivSelect = "inline";
	$DivShow = "inline";	

} elseif ($_REQUEST['action'] == 'p291retB') {


	$iapRefundRet = -1;
	if ($_REQUEST['SUPDATETYPE'] == "NEW") {
		$iapOrigAction = "NEW";

		if (IAP_Remove_Savearea("IAP291SA") < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the Sale savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapS = (array) IAP_Build_New_Row(array("table" => "sale"));
		$iapSale = $iapS[0];
		$iapSale['sale_type'] = "P";
		$iapSale['saledtl'] = array();
		$iapSale['newcust'] = "N";
		$iapSale['newpe'] = "N";
		$iapSale['PEOrig'] = "";
		$iapSale['sale_item_cost'] = 0;
		$iapSale['sale_tax_override'] = "N";
		$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
		$iapRet = IAP_Create_Savearea("IAP291SA", $iapSale, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {
		$iapOrigAction = $_REQUEST['action'];

// get Sale

		$iapSale = (array) IAP_Get_Savearea("IAP291SA", $_REQUEST['IAPUID']);
		if (empty($iapSale)) {
		    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		    return;
		}

		$iapPE = $iapSale['PERec'];

		if (!empty($_REQUEST['SALEID'])) {
			$iapSId = $_REQUEST['SALEID'];
			$iapSale = IAP_Get_Sale($iapSId);
			if ($iapSale < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapSale['HOLD_TYPE'] = $iapSale['sale_type'];
			$iapSale['PEOrig'] = $iapSale['sale_peid'];     // ???????
			$iapSale['status'] = "EXISTING";
		}

// -----------------------------------------------------------
// 
// 	If the client clicked Refund Sale we will call to
// 	back out the sale. iapRefundRet is set to know if
// 	that module was successful. If it is iapRefundRet
// 	will be set to 0 (zero) but any other return means
// 	the module was not successful for some reason
// 	and the sale should be processed as if the button
// 	was not clicked.
//
// -----------------------------------------------------------
		if (isset($_REQUEST['srefund'])) {
			require_once(ABSPATH."MyPages/IAPSaleRefund.php");
			$iapRefundRet = IAP_Refund_Sale($iapSale);
			if ($iapRefundRet ==  0) { // Set as if initial entry to program
				$iapOrigAction = "NEW";
				if (IAP_Remove_Savearea("IAP291SA") < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the Sale savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$iapS = (array) IAP_Build_New_Row(array("table" => "sale"));
				$iapSale = $iapS[0];
				$iapSale['sale_type'] = "P";
				$iapSale['saledtl'] = array();
				$iapSale['newcust'] = "N";
				$iapSale['newpe'] = "N";
				$iapSale['PEOrig'] = "";
				$iapSale['sale_item_cost'] = 0;
				$iapSale['sale_tax_override'] = "N";
				$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
				$iapRet = IAP_Create_Savearea("IAP291SA", $iapSale, $_REQUEST['IAPUID']);
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$DivSelect = "inline";
				$DivShow = "none";
			}
		}
	}

	if ($iapRefundRet !=  0) {

		$iapPageError = 0;
		$iapChanged = "N";
		$iapCustChanged = "N";
		$iapPEChanged = "N";

	require_once("IAPValidators.php");

// -------------------------------------------------------
// Validate customer information
// -------------------------------------------------------
		if ($_REQUEST['SNEWCUST'] == "N") {
			if (empty($_REQUEST['scustomers'])) {
				echo "<span class=iapError>Please select a Customer!</span><br>";
				$iapPageError = 1;
			} else {
				$iapCust = IAP_Get_Customer_By_Name($_REQUEST['scustomers']);
				if ($iapCust < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the customer record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				if ($iapCust['status'] == "NEW") {
					echo "<span class=iapError>IAP LOGIC ERROR: Could not find selected customer record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}
		} else {
			if ($iapSale['newcust'] == "N") {
				$iapSale['newcust'] = "Y";
				$iapNC = (array) IAP_Build_New_Row(array("table" => "cust"));
				$iapCust = $iapNC[0];
			} else {
				$iapCust = $iapSale['custrec'];
			}
			if (isset($_REQUEST['snewcname'])) {
				$iapRet = IAP_Validate_Nonblank($iapCust['cust_name'], ucwords($_REQUEST['snewcname']));
				if ($iapRet['Changed'] == "Y") {
				    $iapCust['cust_name'] = ucwords($iapRet['Value']);
				    $iapCustChanged = "Y";
				} elseif ($iapRet['Error'] == 1) {
					echo "<span class=iapError>Customer Name cannot be blank for the new customer!</span><br>";
					$iapPageError = 1;
				} else {
					$iapC = IAP_Get_Customer_By_Name(ucwords($_REQUEST['snewcname']));
					if ($iapC < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the customer record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					} elseif ($iapC['status'] != "NEW") {
			 			echo "<span class=iapError>A customer by this name already exists!</span><br>";
						$iapPageError = 1;
					}
				}
			}
			if (isset($_REQUEST['snewcbirth'])
			and !empty($_REQUEST['snewcbirth'])) {
				$iapBD = str_replace("-", "/", $_REQUEST['snewcbirth']."/1960"); // use a leap year so 2/29 is valid
				$iapRet = IAP_Validate_Date($iapCust['cust_birthday'], $iapBD, "Y");
				if ($iapRet['Changed'] == "Y") {
				    $iapCust['cust_birthday'] = $_REQUEST['snewcbirth'];
				    $iapCustChanged = "Y";
				} elseif ($iapRet['Error'] == 2) {
				    echo "<span class=iapError>Birthday is not valid for the new customer!</span><br>";
				    $iapPageError = 1;
				}
			}
			if (isset($_REQUEST['snewcstrt'])) {
				$iapCust['cust_street'] = $_REQUEST['snewcstrt'];
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewccity'])) {
				$iapCust['cust_city'] = ucwords($_REQUEST['snewccity']);
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewcstate'])) {
				$iapCust['cust_state'] = strtoupper($_REQUEST['snewcstate']);
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewczip'])) {
				$iapCust['cust_zip'] = $_REQUEST['snewczip'];
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewcemail'])) {
				$iapCust['cust_email'] = strtolower($_REQUEST['snewcemail']);
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewcphone'])) {
				$iapCust['cust_phone'] = $_REQUEST['snewcphone'];
				$iapCustChanged = "Y";
			}
			if (isset($_REQUEST['snewcnews'])) {
				if ($_REQUEST['snewcnews'] == "N") {
					$iapCust['cust_newsletter'] = "Y";
					$iapCustChanged = "Y";
				}
			} else {
				if ($_REQUEST['snewcnews'] == "Y") {
					$iapCust['cust_newsletter'] = "N";
					$iapCustChanged = "Y";
				}
			}
			$iapCust['cust_type'] = "C";
		}
		if (isset($_REQUEST['scposscon'])) {
			if ($_REQUEST['scposscon'] == "N") {
				$iapCust['cust_followup_consultant'] = "Y";
				$iapCustChanged = "Y";
			}
		} else {
			if ($_REQUEST['scposscon'] == "Y") {
				$iapCust['cust_followup_consultant'] = "N";
				$iapCustChanged = "Y";
			}
		}
		if (isset($_REQUEST['scposspar'])) {
			if ($_REQUEST['scposspar'] == "N") {
				$iapCust['cust_followup_party'] = "Y";
				$iapCustChanged = "Y";
			}
		} else {
			if ($_REQUEST['scposspar'] == "Y") {
				$iapCust['cust_followup_party'] = "N";
				$iapCustChanged = "Y";
			}
		}

		if ($_REQUEST['stype'] != $iapSale['sale_type']) {
			$iapSale['sale_type'] = $_REQUEST['stype'];
			$iapChanged = "Y";
		}

// -------------------------------------------------------
// TODO call pe module
//		require_once(ABSPATH."MyPages/IAPSalesPE.php");
//		$Ret = IAP_Setup_PE($iapSale);
//		$iapPERet = $Ret[0];
//		$iapSale = $Ret[1];
// -------------------------------------------------------
// Set up PE record
// -------------------------------------------------------
		$iapAddPE = "N";
		$iapGetPE = "N";
		$iapUpdPE = "N";
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
			$iapAddPE = "Y";
		} elseif ($iapSale['sale_peid'] > 0) {
// sale record has a valid pe rec id so clear out this sales totals
			$iapPE = IAP_Get_PartyEvent_By_Id($iapSale['sale_peid']);		
			if ($iapPE < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			$iapPE['pe_sales_cnt'] = $iapPE['pe_sales_cnt'] - 1;
			$iapPE['pe_cost_of_items'] = $iapPE['pe_cost_of_items'] - $iapSale['sale_item_cost'];
			$iapPE['pe_net_sales'] = $iapPE['pe_net_sales'] - $iapSale['sale_net'];
			$iapPE['pe_profit'] = $iapPE['pe_profit'] - $iapSale['sale_profit'];
			$iapPE['pe_other_expenses'] = $iapPE['pe_other_expenses'] - $iapSale['sale_other_exp'];
			$iapPE['pe_shipping'] = $iapPE['pe_shipping'] - $iapSale['sale_shipping'];
			$iapPE['pe_sales_tax'] = $iapPE['pe_sales_tax'] - $iapSale['sale_sales_tax'];
			$iapPE['pe_total_sales'] = $iapPE['pe_total_sales'] - $iapSale['sale_total_amt'];
			$iapSale['sale_item_cost'] = 0;
			$iapSale['sale_net'] = 0;
			$iapSale['sale_profit'] = 0;
			$iapSale['sale_other_exp'] = 0;
			$iapSale['sale_shipping'] = 0;
			$iapSale['sale_sales_tax'] = 0;
			$iapSale['sale_total_amt'] = 0;
			$iapUpdPE = "Y";
		} elseif ($iapSale['sale_type'] == "E") {	// Event? Get that PE rec
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
			$iapAddPE = "Y";
		} else {
// Any type but E can select a party so get the PE rec.
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
//			$iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_date = '".date("Y-m-d", strtotime($iapPEDate))."' AND pe_sponsor = '".$iapSponsor."'";
			$iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_date = '".date("Y-m-d", strtotime($iapPEDate))."' AND pe_id = '".$_REQUEST['SSELPE']."'";
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
			$iapUpdPE = "Y";
		}
// ----------------------------------------------------------------------------------


// -------------------------------------------------------
// Validate party/event information
// -------------------------------------------------------
// ... New PE
		if ($_REQUEST['SNEWPE'] == "Y") {
			if (isset($_REQUEST['snewpedate'])) {
				$iapRet = IAP_Validate_Date($iapPE['pe_date'], $_REQUEST['snewpedate']);
				if ($iapRet['Changed'] == "Y") {
					$iapPE['pe_date'] = $iapRet['Value'];
					$iapPEChanged = "Y";
				} elseif ($iapRet['Error'] == 2) {
					echo "<span class=iapError>Date is not valid for the new party/event!</span><br>";
					$iapPageError = 1;
				}
			} else {
				if (empty($iapPE['pe_date'])) {
					echo "<span class=iapError>Date is not valid for the new party/event!</span><br>";
					$iapPageError = 1;					
				}
			}
			if (isset($_REQUEST['snewpename'])) {
				$iapRet = IAP_Validate_Nonblank($iapPE['pe_sponsor'], ucwords($_REQUEST['snewpename']));
				if ($iapRet['Changed'] == "Y") {
					$iapPE['pe_sponsor'] = ucwords($iapRet['Value']);
					$iapPEChanged = "Y";
				} elseif ($iapRet['Error'] == 1) {
					echo "<span class=iapError>Hostess/Sponsor cannot be blank for the new party/event!</span><br>";
					$iapPageError = 1;
				}
			} else {
				if (empty($iapPE['pe_sponsor'])) {
				    echo "<span class=iapError>Hostess/Sponsor cannot be blank for the new party/event!</span><br>";
				    $iapPageError = 1;
				}
			}			
			if (isset($_REQUEST['snewpestrt'])) {
				$iapPE['pe_street'] = $_REQUEST['snewpestrt'];
				$iapPEChanged = "Y";
			}
			if (isset($_REQUEST['snewpecity'])) {
				$iapPE['pe_city'] = $_REQUEST['snewpecity'];
				$iapPEChanged = "Y";
			}
			if (isset($_REQUEST['snewpestate'])) {
				$iapPE['pe_state'] = $_REQUEST['snewpestate'];
				$iapPEChanged = "Y";
			}
			if (isset($_REQUEST['snewpezip'])) {
				$iapPE['pe_zip'] = $_REQUEST['snewpezip'];
				$iapPEChanged = "Y";
			}
		} else {
			if ($iapSale['sale_peid'] == 0) {		// if no PE record then must supply sale date
// ... No PE
				if (isset($_REQUEST['ssaledate'])) {
					$iapRet = IAP_Validate_Date($iapPE['pe_date'], $_REQUEST['ssaledate']);
					if ($iapRet['Changed'] == "Y") {
						$iapPE['pe_date'] = $iapRet['Value'];
						$iapPEChanged = "Y";
					} elseif ($iapRet['Error'] == 1) {
						echo "<span class=iapError>Sale Date cannot be blank!</span><br>";
						$iapPageError = 1;
					} elseif ($iapRet['Error'] == 2) {
						echo "<span class=iapError>Sale Date is not valid!</span><br>";
						$iapPageError = 1;
					}
				} elseif (empty($iapPE['pe_date'])) {
					echo "<span class=iapError>Sale Date cannot be blank!</span><br>";
					$iapPageError = 1;
				} else {				// reasonability check.
					$LastYear = strtotime("now - 18 months");
					$ThisDate = strtotime($_REQUEST['ssaleDate']);
					if ($ThisDate < $LastYear) {
						echo "<span class=iapError>Sale Date more than 18 months old!</span><br>";
						$iapPageError = 1;			
					}
				}
				$iapPE['pe_sponsor'] = $iapCust['cust_name'];
			}
		}

// -------------------------------------------------------
// Validate Miscellanous Sales information
// -------------------------------------------------------

		if ($_REQUEST['ssaleloc'] != $iapSale['sale_location']) {
			$iapSale['sale_location'] = $_REQUEST['ssaleloc'];
		}
		if (isset($_REQUEST['smileage'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_mileage'], $_REQUEST['smileage'], "Y");
			if ($iapRet['Changed'] == "Y") {
				$iapSale['sale_mileage'] = $iapRet['Value'];
//		    	$iapPEChanged = "Y";
			}
			if ($iapRet['Error'] == 1) {
				$iapSale['sale_mileage'] = 0;
//		    	$iapPEChanged = "Y";
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapError>Mileage is not valid!</span><br>";
				$iapPageError = 1;
			}
			if ($iapSale['sale_type'] == "I"
			or $iapSale['sale_type'] == "W") {
//				$iapSale['sale_mileage'] = $iapPE['pe_mileage'];
//				$iapPE['pe_mileage'] = $iapSale['sale_mileage'];
				$iapPEChanged = "Y";
			}
		}
		if (isset($_REQUEST['sotherexp'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_other_exp'], $_REQUEST['sotherexp'], "Y");
			if ($iapRet['Changed'] == "Y") {
				$iapSale['sale_other_exp'] = $iapRet['Value'];
			} elseif ($iapRet['Error'] == 1) {
			    $iapSale['sale_other_exp'] = 0;
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapError>'Other Expenses' is not valid!</span><br>";
				$iapPageError = 1;
			}
		}
		if (isset($_REQUEST['sexpexplain'])) {
			if ($_REQUEST['sexpexplain'] != $iapSale['sale_exp_explained']) {
				$iapSale['sale_exp_explained'] = $_REQUEST['sexpexplain'];
			}
		}
		if (isset($_REQUEST['svendord'])) {
			if ($iapSale['sale_vendor_order'] != $_REQUEST['svendord']) {
				$iapSale['sale_vendor_order'] = $_REQUEST['svendord'];
				$iapChanged = "Y";
			}
		}

// -------------------------------------------------------
// Validate Financial information
// -------------------------------------------------------
		if (isset($_REQUEST['snetsale'])) {
			if ($iapSale['sale_net'] != $_REQUEST['snetsale']) {
				$iapSale['sale_net'] = $_REQUEST['snetsale'];
				$iapChanged = "Y";
			}
		}
		if (isset($_REQUEST['sshipping'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_shipping'], $_REQUEST['sshipping'], "Y");
			if ($iapRet['Changed'] == "Y") {
			    $iapSale['sale_shipping'] = $iapRet['Value'];
			    $iapChanged = "Y";
			} elseif ($iapRet['Error'] == 1) {
			    $iapSale['sale_shipping'] = 0;
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapError>Shipping is not valid!</span><br>";
		    		$iapPageError = 1;
			}
		}
		if (isset($_REQUEST['strackno'])
		and $_REQUEST['strackno'] != $iapSale['sale_tracking_no']) {
			$iapSale['sale_tracking_no'] = $_REQUEST['strackno'];
			$iapChanged = "Y";
		}

		if (isset($_REQUEST['STAXOVERRIDE'])) {
			if ($iapSale['sale_tax_override'] != $_REQUEST['STAXOVERRIDE']) {
				$iapSale['sale_tax_override'] = $_REQUEST['STAXOVERRIDE'];
				$iapChanged = "Y";
			}
		}

		if (isset($_REQUEST['STAXREGION'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_tax_region'], $_REQUEST['STAXREGION']);
			if ($iapRet['Changed'] == "Y") {
				$iapSale['sale_tax_region'] = $iapRet['Value'];
				$iapChanged = "Y";
			} elseif ($iapRet['Error'] == 1) {
				echo "<span class=iapWarning>IAP Error: Tax region is not present. Setting to local tax.</span><br>";
				$iapSale['sale_tax_region'] = $_REQUEST['UserData']['TaxRegion'];
				$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
				$iapChanged = "Y";
			} elseif ($iapRet['Error'] == 2) {
				echo "<span class=iapWarning>IAP Error: Tax region is invalid. Setting to local tax.</span><br>";
				$iapSale['sale_tax_region'] = $_REQUEST['UserData']['TaxRegion'];
				$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
				$iapChanged = "Y";
			}
		}

		if (isset($_REQUEST['STAXRATE'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_tax_rate'], $_REQUEST['STAXRATE'], "Y");
			if ($iapRet['Changed'] == "Y") {
				$iapSale['sale_tax_rate'] = $iapRet['Value'];
				$iapChanged = "Y";
			}
			if ($iapRet['Error'] == 1) {
				echo "<span class=iapWarning>IAP Error: Tax rate is not present. Setting to local tax.</span><br>";
				$iapSale['sale_tax_region'] = $_REQUEST['UserData']['TaxRegion'];
				$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
				$iapChanged = "Y";
			}
			if ($iapRet['Error'] == 2) {
				echo "<span class=iapWarning>IAP Error: Tax rate is invalid. Setting to local tax.</span><br>";
				$iapSale['sale_tax_region'] = $_REQUEST['UserData']['TaxRegion'];
				$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
				$iapChanged = "Y";
			}
		}
		if (isset($_REQUEST['staxamt'])) {
			$iapRet = IAP_Validate_Nonblank($iapSale['sale_sales_tax'], $_REQUEST['staxamt'], "Y");
			if ($iapRet['Changed'] == "Y") {
				$iapSale['sale_sales_tax'] = $iapRet['Value'];
				$iapChanged = "Y";
			} elseif ($iapRet['Error'] == 1) {
				$iapSale['sale_sales_tax'] = 0;
			} elseif ($iapRet['Error'] == 2) {
		    	echo "<span class=iapError>Sales Tax Amount is not valid!</span><br>";
				$iapPageError = 1;
			}
		}
		if (isset($_REQUEST['spayment'])) {
			if ($_REQUEST['spayment'] == "spaycash") {
				if ($iapSale['sale_pay_method'] != "$") {
					$iapSale['sale_pay_method'] = "$";
			    		$iapChanged = "Y";
			    	}
			} elseif ($_REQUEST['spayment'] == "spaycredit") {
				if ($iapSale['sale_pay_method'] != "C") {
					$iapSale['sale_pay_method'] = "C";
		    		$iapChanged = "Y";
		    	}
		} elseif ($_REQUEST['spayment'] == "spaycheck") {
				if ($iapSale['sale_pay_method'] != "K") {
					$iapSale['sale_pay_method'] = "K";
		    			$iapChanged = "Y";
		    		}
			}
		}
		if (isset($_REQUEST['spaychkno'])) {
			if ($iapSale['sale_check_number'] != $_REQUEST['spaychkno']) {
				$iapSale['sale_check_number'] = $_REQUEST['spaychkno'];
	    			$iapChanged = "Y";
	    		}			 
		}

		if (isset($_REQUEST['stotalsale'])) {
			if ($iapSale['sale_total_amt'] != $_REQUEST['stotalsale']) {
				$iapSale['sale_total_amt'] = $_REQUEST['stotalsale'];
				$iapChanged = "Y";
			}
		}

		if (isset($_REQUEST['scomment'])) {
			if ($iapSale['sale_comment'] != $_REQUEST['scomment']) {
				$iapSale['sale_comment'] = $_REQUEST['scomment'];
				$iapChanged = "Y";
			}
/* ------  Removed 2018/03/30 May want checkbox to say add to customer comments
			if ($iapSale['sale_comment'] != "") {
				$cn = $iapCust['cust_notes'];
				if ($iapCust['cust_notes'] != "") {
					$cn = "\n";
				}
				$iapCust['cust_notes'] = $cn.$iapSale['sale_comment'];
		    		$iapCustChanged = "Y";
			}
*/
		}

// -------------------------------------------------------
// Validate Items
// -------------------------------------------------------


// ------------------------------------- Change 05/25/2016 
// --- To match Purchase Detail Processing which corrects
// --- the way lots are handled when the Purchase/sale is
// --- updated so lots don't get lost when sale updated.
// -------------------------------------------------------
		$iapData = $_REQUEST['IAPDATA'];
		$iapItems = explode("|", $iapData);
		$iapNewData = $_REQUEST['SNEWITEMINFO'];
		$iapNewItems = explode("|", $iapNewData); 
		$iapNewSaleDtl = array();

		if (!(set_time_limit(90))) {
			echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
		}

		$i = 0;
		$p = 0;

// if maintain po and add item get no match error because items coming from existing po are not in the newitem array.
// need to tie items part of a special back to the special to derive correct price for profitability but may be able 
// to sell as whole special?

		foreach($iapItems as $iapI) {
//		$i++;
			if (empty($iapI)) {
				continue;
			}
			$iapColumns = explode("~", $iapI);
			$iapItemCode = $iapColumns[0];
			$iapItemDesc = $iapColumns[1];
			$iapItemQty = $iapColumns[2];
			$iapItemPrc = $iapColumns[3];
			$iapItemValue = $iapColumns[4];
			$iapItemSupp = $iapColumns[5];
			$i++;
			if (empty($iapItemCode)) {
				echo "<span class=iapError>Item Code cannot be blank in row ".$i."!</span><br>";
				$iapPageError = 1;
			}
			if (empty($iapItemDesc)) {
				echo "<span class=iapError>Item Description cannot be blank in row ".$i."; item ".$iapCode."!</span><br>";
				$iapPageError = 1;
			}
			if (empty($iapItemQty)) {
				echo "<span class=iapError>Item Quantity cannot be zero in row ".$i."; item ".$iapCode."!</span><br>";
				$iapPageError = 1;
			}
			if (empty($iapItemPrc)) {
				echo "<span class=iapError>Item Price cannot be zero in row ".$i."; item ".$iapCode."!</span><br>";
				$iapPageError = 1;
			}

			$iapN = $iapNewItems[$p];
			$iapNewCols = explode("~", $iapN);
			$iapNewItem = strtoupper($iapNewCols[0]);
			$iapNewStatus = $iapNewCols[1];
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
			if ($iapNewStatus != "NEW") {
				$iapNewUnits = 0;
				$iapNewCost = 0;
				$iapNewCat = 0;
			} else {
				$iapNewUnits = $iapNewCols[2];
				$iapNewCost = $iapNewCols[3];
				$iapNewCat = $iapNewCols[4];
			}

			$iapSD = array('SDItemCode' => $iapItemCode, 'SDDesc' => $iapItemDesc, 'SDQty' => $iapItemQty, "SDPrice" => $iapItemPrc, "SDValue" => $iapItemValue, "SDUnits" => $iapNewUnits, "SDCost" => $iapNewCost, "SDCat" => $iapNewCat, "SDStatus" => $iapNewStatus, "SDSource" => $iapItemSupp);
			$iapNewSaleDtl[] = $iapSD;
			$iapNewUsed[$p] = "Y";
			$p++;
		}

		$iapSale['sale_items'] = count($iapItems);

// ---------------------------------------- End 05/25/2016

		if ($iapPageError == 0
		and $iapChanged == "Y") {
// -------------------------------------------------------
// Everything checks out so update files
// -------------------------------------------------------
			echo "<span class=iapSuccess>All fields are valid. Beginning update.</span><br>";
			wp_ob_end_flush_all();
			flush();

// -------------------------------------------------------
// ... Add customer if new
// -------------------------------------------------------
			if ($iapSale['newcust'] == "Y") {
				echo "<span class=iapSuccess>Adding new customer ".$iapCust['cust_name'].".</span><br>";
				wp_ob_end_flush_all();
				flush();

				$iapCust['status'] == "NEW";
				$iapCust['cust_met_date'] = $iapPE['pe_date'];
				if ($iapSale['sale_type'] == "E"
				or  $iapSale['sale_type'] == "P") {
					$iapCust['cust_met_at'] = $iapPE['pe_sponsor'];
					$iapCust['cust_met_date'] = $iapPE['pe_date']; 
					$iapCust['cust_met_type'] = $iapSale['pe_type'];
					$iapCust['cust_met_peid'] = $iapPE['pe_id'];
					if ($iapSale['sale_type'] == "P") {
						$iapCust['cust_met_at'] = $iapCust['cust_met_at']." party (#".$iapPE['pe_party_no'].")";
					}
				} elseif ($iapSale['sale_type'] == "F") {
					$iapCust['cust_met_at'] = "Facebook Party.";
				} elseif ($iapSale['sale_type'] == "I") {
					$iapCust['cust_met_at'] = "In person.";
				} elseif ($iapSale['sale_type'] == "W") {
					$iapCust['cust_met_at'] = "Website Order.";
				}
				require_once(ABSPATH."MyPages/IAPCreateCust.php");
				$iapCust = IAP_Create_Customer($iapCust, "Y");

				echo "<span class=iapSuccess>Customer ".$iapCust['cust_name']." was successfully added.</span><br>";
				wp_ob_end_flush_all();
				flush();

			} elseif ($iapCustChanged == "Y") {
				$iapCust['cust_changed'] = date("Y-m-d");
				$iapCust['cust_changed_by'] = $_REQUEST['IAPUID']; 
				$iapRet = IAP_Update_Data($iapCust, "cust");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}

// -------------------------------------------------------
// ... Add/Update party/event if new
// -------------------------------------------------------

//  ---  E = Event - need an event address (selectable or add)
//  ---  P = Party - need a party number (selectable or add)
//  ---  I = Individual - need name&address

//  ---  F = Facebook (SALE DOES NOT REDUCE INVENTORY)
//  ---  O = Other (SALE DOES NOT REDUCE INVENTORY)
//  ---  W = Website - need number from online (SALE DOES NOT REDUCE INVENTORY)

//  ---  X = Exchange - processed in other program 

//  ---  Facebook orders are treated the same as Web orders becaus it is assumed 
//  ---     they result in an order from the website. 
//  ---     This may be a WRONG assumption if consultant places one order to cover FB orders and it does come into inventory.

//  ---  Other orders are treated the same as Web orders because I don't know what they would be yet 

			if ($iapAddPE == "Y") {
				$iapPE['pe_company'] = $_REQUEST['CoId'];
				$iapPE['pe_type'] = $iapSale['sale_type'];

				if ($iapPE['pe_type'] == "I"
				or $iapPE['pe_type'] == "F"
				or $iapPE['pe_type'] == "O"
				or $iapPE['pe_type'] == "W") {
					$iapPE['pe_street'] = $iapCust['cust_street'];
					$iapPE['pe_city'] = $iapCust['cust_city'];
					$iapPE['pe_zip'] = $iapCust['cust_zip'];
				}

/*
				if (empty($iapPE['pe_zip'])) {
					$treg = $_REQUEST['UserData']['TaxRegion'];
					$trate =  $_REQUEST['UserData']['TaxRate'];
				} else {
					$iapTax = IAP_Get_Tax("00000");
					if ($iapTax < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR retrieving default tax information [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						$treg = $_REQUEST['UserData']['TaxRegion'];
						$trate =  $_REQUEST['UserData']['TaxRate'];
					} else {
						$treg = ucwords(trim($iapTax['tax_region_name']));
						$trate = $iapTax['tax_combined_rate'];			
					}
				}
				$iapPE['pe_tax_region'] = $treg;
				$iapPE['pe_tax_rate'] = $trate;
*/
				$iapPE['pe_tax_region'] = $iapSale['sale_tax_region'];
				$iapPE['pe_tax_rate'] = $iapSale['sale_tax_rate'];

				$iapRet = IAP_Update_Data($iapPE, "parev");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				if ($iapPE['status'] == "NEW") {
					$iapPE['pe_id'] = $iapRet;
					$iapPE['status'] = "EXISTING";
					$x = "added";
				} else {
					$x = "updated";
				}
				if ($iapSale['sale_type'] == "E"
				or $iapSale['sale_type'] == "P") {
					echo "<span class=iapSuccess>Party/Event ".$iapPE['pe_sponsor']." on ".date("m/d/Y", strtotime($iapPE['pe_date']))." was successfully ".$x.".</span><br>";
					wp_ob_end_flush_all();
					flush();
				}
			}

// -------------------------------------------------------
// ... Add/Update the sale record
// -------------------------------------------------------
			if ($iapSale['status'] == "NEW") {
				$pe = "...Adding";
				$pd = "added";
				$iapSale['sale_company'] = $_REQUEST['CoId'];
				$iapSale['sale_peid'] = $iapPE['pe_id'];
				$iapSale['sale_customer'] = $iapCust['cust_no'];
			} else {
				$pe = "...Updating";
				$pd = "updated";
			}
//		echo $pe." sale record.<br>";
//		wp_ob_end_flush_all();
//		flush();

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Change to use sale date instead of party date if not type P
			$iapSale['sale_date'] = $iapPE['pe_date'];
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			if (empty($iapSale['sale_miles'])) {
				$iapSale['sale_miles'] = 0;
			}
			if (empty($iapSale['sale_other_exp'])) {
				$iapSale['sale_other_exp'] = 0;
			}
			$iapSale['sale_changed'] = date("Y-m-d");
			$iapSale['sale_changed_by'] = $_REQUEST['IAPUID'];
			$iapRet = IAP_Update_Data($iapSale, "sale");
			if ($iapRet < 0) {
	 			echo " <span class=iapError>IAP INTERNAL ERROR writing Sale [FATAL]<br>Please notify Support and provide this reference of /".
	 				basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapSale['status'] == "NEW") {
				$iapSale['sale_id'] = $iapRet;
			}
			$iapSale['status'] = "EXISTING";
		
			echo "<span class=iapSuccess>Sale was successfully ".$pd.".</span><br>";
			wp_ob_end_flush_all();
			flush();

// ------------------------------------------------------------------
// ... Add/Update the sale detail records
// ------------------------------------------------------------------
//		echo $pe." items sold detail and updating item's on-hand balance.<br>";
//		wp_ob_end_flush_all();
//		flush();

/* --- Eliminated 05/25/2016 - Need to process sales detail records
			if ($iapSale['HOLD_TYPE'] != "F"
			and $iapSale['HOLD_TYPE'] != "O"
			and $iapSale['HOLD_TYPE'] != "W") {
*/
//  ---  Inventory balance are not adjusted for these types
//  ---  Facebook orders are treated the same as Web orders because it is assumed 
//  ---     they result in an order from the website. 
//  ---     This may be a WRONG assumption if consultant places one order to cover FB orders and it does come into inventory.


// ------------------------------------- Change 05/25/2016 
// --- To match Purchase Detail Processing which corrects
// --- the way lots are handled when the Purchase/sale is
// --- updated so lots don't get lost.
// -------------------------------------------------------

			$iapSaleDtl = IAP_Get_SaleDet($iapSale['sale_id']);
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retreiving prior purchase detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapSaleDtl[0]['status'] == "NEW") {
				$iapSaleDtl[0]['saledet_company'] = $_REQUEST['CoId'];
				$iapSaleDtl[0]['saledet_sid'] = $iapSale['sale_id'];
				$iapSaleDtl[0]['saledet_item_code'] = "";
				$iapSaleDtl[0]['RowStatus'] = "REM";
			}

			if (!(set_time_limit(120))) {
				echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
			}

// -------------------------
// Write new sales detail
// -------------------------
			require_once(ABSPATH."MyPages/IAPProcessLot.php");
			$iapWasNewItem = "N";
			$i1 = 0;
			foreach($iapNewSaleDtl as $iapNSD) {
				$iapSDFound = "N";
				for($i2 = 0; $i2 < count($iapSaleDtl); $i2++) {
					if (!isset($iapSaleDtl[$i2]['RowStatus'])) {
						if (strtoupper($iapSaleDtl[$i2]['saledet_item_code']) == strtoupper($iapNSD['SDItemCode'])
						and $iapSaleDtl[$i2]['saledet_quantity'] == $iapNSD['SDQty']) {
							$iapSaleDtl[$i2]['RowStatus'] = "OK";
							$iapSDFound = "Y";
							break;
						}
					}
				}
				if ($iapSDFound == "N") {
					$iapNewSaleDtl[$i1]['RowStatus'] = "NEW";
				}
				$i1 = $i1 + 1;
			}

// ------------------------------------------------------------------------------------
// --- Done finding New Sales Detail
// --- Delete those no longer used. 
//
// --- Delete is done first to release any lots that should be assigned to added items.
// ------------------------------------------------------------------------------------
			foreach($iapSaleDtl as $iapSD) {
				if (!isset($iapSD['RowStatus'])) {
					if ($iapSale['sale_type'] != "F"
					and $iapSale['sale_type'] != "O"
					and $iapSale['sale_type'] != "W") {

						if ($_REQUEST['debugme'] == "Y") {
							echo "<span class=iapWarning>WARNING: IAPSales calling BackOut_Sale_Lot.<br>";
						}

						$iapRet = IAP_BackOut_Sale_Lot($iapSD, "Y");
					}

		            if ($_REQUEST['debugme'] == "Y") {
		                echo "......Deleting SD for ".$iapSD['saledet_item_code'].
		                     " seq=".strval($iapSD['saledet_seq']).
		                     " with cost=".number_format($iapSD['saledet_total_cost'], 2, '.', '').
		                     " profit=".number_format($iapSD['saledet_total_profit'], 2, '.', '').
		                     "<br>";
		            }

					$iapRet = IAP_Delete_Row($iapSD, "sdtl");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR deleting row from sales detail table [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
				} else {
					if ($iapSale['sale_type'] != "F"
					and $iapSale['sale_type'] != "O"
					and $iapSale['sale_type'] != "W") {
						if ($_REQUEST['debugme'] == "Y") {
							echo "......Adding existing ".$iapSD['saledet_item_code'].
								 " with cost=".number_format($iapSD['saledet_total_cost'], 2, '.', '').
								 " profit=".number_format($iapSD['saledet_total_profit'], 2, '.', '')."<br>";
						}
						$iapSale['sale_item_cost'] = $iapSale['sale_item_cost'] + $iapSD['saledet_total_cost'];
						$iapSale['sale_profit'] = $iapSale['sale_profit'] + $iapSD['saledet_total_profit'];
						if ($_REQUEST['debugme'] == "Y") {
							echo " New sales amounts - cost=".number_format($iapSale['sale_item_cost'], 2, '.', '').
								 " profit=".number_format($iapSale['sale_profit'], 2, '.', '')."<br>";
						}
					}
				}
			}

// ---------------------
// --- Now add new items
// ---------------------
			foreach($iapNewSaleDtl as $iapNSD) {
				if ($iapNSD['RowStatus'] == "NEW") {
					$iapS = (array) IAP_Build_New_Row(array("table" => "sdtl"));
					$iapSDtl = $iapS[0];
					$iapSDtl['saledet_company'] = $_REQUEST['CoId'];
					$iapSDtl['saledet_sid'] = $iapSale['sale_id'];
					$iapSDtl['saledet_item_code'] = strtoupper($iapNSD['SDItemCode']);
					$iapSDtl['saledet_item_source'] = strtoupper($iapNSD['SDSource']);
					$iapSDtl['saledet_desc'] = $iapNSD['SDDesc'];
					$iapSDtl['saledet_quantity'] = $iapNSD['SDQty'];
					$iapSDtl['saledet_lot_cost'] = 0;
					$iapSDtl['saledet_lot_date'] = "0000-00-00";
					$iapSDtl['saledet_mult_lots_applied'] = "N";
					$iapSDtl['saledet_total_cost'] = 0;
					$iapSDtl['saledet_price'] = $iapNSD['SDPrice'];
					$iapSDtl['saledet_total_price'] = $iapSDtl['saledet_price'] * $iapSDtl['saledet_quantity'];
					$iapSDtl['saledet_total_profit'] = 0;
					$iapSDtl['saledet_customer_no'] = $iapCust['cust_no'];

// -----------------
// Add any new items
// -----------------
					if ($iapNSD['SDStatus'] == "NEW") {
						echo "<span class=iapWarning>NEW ITEM, ".$iapNSD['SDItemCode'].", being added to your catalog. It may require additional information in the Catalog function.</span><br>";
						wp_ob_end_flush_all();
						flush();

						$iapC = IAP_Build_New_Row(array("table" => "ctlg"));
						$iapI = IAP_Build_New_Row(array("table" => "inv"));
						$iapP = IAP_Build_New_Row(array("table" => "prc"));
						$iapCtlg = array_merge($iapC[0], $iapI[0], $iapP[0]);

						$iapCtlg['cat_company'] = $_REQUEST['CoId'];
						$iapCtlg['cat_item_code'] = $iapNSD['SDItemCode'];
						$iapCtlg['cat_description'] = $iapNSD['SDDesc'];
						$iapCtlg['cat_supplier'] = "";
						$iapCtlg['cat_active'] = "Y";
						$iapCtlg['cat_special_item'] = "N";
						$iapCtlg['cat_changed'] = date("Y-m-d");
						$iapCtlg['cat_changed_by'] = $_REQUEST['IAPUID'];
						$iapCtlg['inv_on_hand'] = 0;
						$iapCtlg['inv_min_onhand'] = 0;
						$iapCtlg['prc_cost'] = $iapNSD['SDCost'];
						$iapCtlg['prc_units'] = $iapNSD['SDUnits'];
						$iapCtlg['prc_cost_unit'] = $iapCtlg['prc_cost'] / $iapCtlg['prc_units'];
						$iapCtlg['prc_price'] = $iapNSD['SDPrice'];
						$iapCtlg['prc_cat_code'] = $iapNSD['SDCat'];
						require_once(ABSPATH."MyPages/IAPCreateCat.php");
						IAP_Create_Cat($iapCtlg, "Y", "Y", "Y");
						$iapCtlg['status'] = "EXISTING";
						$u = "added";
					} else {
						$iapCtlg = IAP_Get_Catalog($iapNSD['SDItemCode'], $iapSale['sale_date']);
						if ($iapCtlg < 0
						or $iapCtlg['status'] == "NEW") {
							echo "<span class=iapError>IAP INTERNAL ERROR retreiving item ".$iapNPD['PDItemCode']." from catalog. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
						$u = "updated";
					}
					if ($iapSale['sale_type'] != "F"
					and $iapSale['sale_type'] != "O"
					and $iapSale['sale_type'] != "W") {
// --- 	Inventory balance are not adjusted for these types
						$iapCtlg['inv_on_hand'] = $iapCtlg['inv_on_hand'] - $iapNSD['SDQty'];
					}
					$iapRet = IAP_Update_Data($iapCtlg, "inv");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR updating item ".$iapCtlg['cat_item_code']." in the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					$iapCtlg['status'] = "EXISTING";
					echo "<span class=iapSuccess>Successfully ".$u." item ".$iapNSD['SDItemCode'].".</span><br>";
					wp_ob_end_flush_all();
					flush();

// ----------------------
// Write New Sales Detail
// ----------------------
					$iapRet = IAP_Update_Data($iapSDtl, "sdtl");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR writing sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					$iapSDtl['saledet_seq'] = $iapRet;
					$iapSDtl['status'] = "EXISTING";

// ----------------
// Update lot table
// ----------------
					if ($iapSale['sale_type'] != "F"
					and $iapSale['sale_type'] != "O"
					and $iapSale['sale_type'] != "W") {
// --- 	Lots are not adjusted for these types
						$iapSDtl = IAP_Apply_Lot_To_Sale($iapSDtl, $iapCtlg['prc_cost_unit'], $iapCtlg['prc_effective']);

						if ($_REQUEST['debugme'] == "Y") {
							echo "......Back from Apply_Lot for ".$iapSDtl['saledet_item_code'].
								 " with cost=".number_format($iapSDtl['saledet_total_cost'], 2, '.', '').
								 " profit=".number_format($iapSDtl['saledet_total_profit'], 2, '.', '')."<br>";
						}

						$iapSale['sale_item_cost'] = $iapSale['sale_item_cost'] + $iapSDtl['saledet_total_cost'];
						$iapSale['sale_profit'] = $iapSale['sale_profit'] + $iapSDtl['saledet_total_profit'];

// echo "......New sales amounts - cost=".number_format($iapSale['sale_item_cost'], 2, '.', '')." profit=".number_format($iapSale['sale_profit'], 2, '.', '')."<br>";


// ----------------------------------
// Update Sales Detail With Cost Info
// ----------------------------------
						$iapRet = IAP_Update_Data($iapSDtl, "sdtl");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR writing sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
					}
				}
			}

// ----------------------------
// END SALES DETAIL LOGIC HERE
// ----------------------------
//		$iapSale['sale_items'] = count();

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

// >>>>>>>>>>>>>>>>> Need to have read new PE if selected

			if (!empty($_REQUEST['SSELPE'])
			and $_REQUEST['SSELPE'] != $iapPE['pe_id']) {
				$iapPE['pe_changed'] = date("Y-m-d");
				$iapPE['pe_changed_by'] = $_REQUEST['IAPUID'];
				$iapRet = IAP_Update_Data($iapPE, "parev");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}
			$iapPE['pe_sales_cnt'] = $iapPE['pe_sales_cnt'] + 1;
			$iapPE['pe_net_sales'] = $iapPE['pe_net_sales'] + $iapSale['sale_net'];
			$iapPE['pe_cost_of_items'] = $iapPE['pe_cost_of_items'] + $iapSale['sale_item_cost']; // will be 0 for F, O, W
			$iapPE['pe_profit'] = $iapPE['pe_profit'] + $iapSale['sale_profit']; 				  // will be 0 for F, O, W
			$iapPE['pe_other_expenses'] = $iapPE['pe_other_expenses'] + $iapSale['sale_other_exp'];
			$iapPE['pe_shipping'] = $iapPE['pe_shipping'] + $iapSale['sale_shipping'];
			$iapPE['pe_sales_tax'] = $iapPE['pe_sales_tax'] + $iapSale['sale_sales_tax'];
			$iapPE['pe_total_sales'] = $iapPE['pe_total_sales'] + $iapSale['sale_total_amt'];
			if ($iapPE['pe_mileage'] == 0) {
				$iapPE['pe_mileage'] = $iapSale['sale_mileage'];
			}
			$iapPE['pe_tax_region'] = $iapSale['sale_tax_region'];
			$iapPE['pe_tax_rate'] = $iapSale['sale_tax_rate'];
			$iapPE['pe_changed'] = date("Y-m-d");
			$iapPE['pe_changed_by'] = $_REQUEST['IAPUID'];
			$iapRet = IAP_Update_Data($iapPE, "parev");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapSale['sale_type'] == "P") {
				$t = "Party ";
			} elseif ($iapSale['sale_type'] == "E") {
				$t = "Event ";
			} else {
				$t = "";
			}
			if ($t != "") {
				echo "<span class=iapSuccess>".$t.$iapPE['pe_sponsor']." was successfully updated.</span><br>";
				wp_ob_end_flush_all();
				flush();
			}

// ------------------------------------------------
// Journal the sale  
// ------------------------------------------------

//		echo "...Adding/Updating Journal record for sale.</span><br>";
//		wp_ob_end_flush_all();
//		flush();

			$iapJrnl = IAP_Get_Journal_By_Detail("S".$iapSale['sale_type'], $iapSale['sale_id']);
			if ($iapJrnl < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR getting row for journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}

			if ($iapJrnl['status'] == "NEW") {
				$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
				$iapJrnl['jrnl_date'] = $iapSale['sale_date'];
				$iapJrnl['jrnl_type'] = "S".$iapSale['sale_type'];
			}
			$iapJrnl['jrnl_description'] = "Sale to ".$iapCust['cust_name']." on ".date("m/d/Y", strtotime($iapSale['sale_date']));
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
			$iapJrnl['jrnl_expenses'] = $iapSale['sale_other_exp'];
			$iapJrnl['jrnl_exp_explain'] = $iapSale['sale_exp_explained'];
			$iapJrnl['jrnl_profit'] = $iapSale['sale_profit'];
			$iapJrnl['jrnl_vendor'] = $iapPE['pe_sponsor'];
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
			if ($iapJrnl['status'] == "NEW") {
				echo "<span class=iapSuccess>Journal was successfully added.</span><br>";
			} else {
				echo "<span class=iapSuccess>Journal was successfully updated.</span><br>";
			}

			echo "<span class=iapSuccess>Updating Complete.</span><br>";
		    wp_ob_end_flush_all();
		    flush();
		}
		$iapSale['saledtl'] = $iapItems;

		if ($iapSale['newcust'] == "Y") {
			$iapSale['custrec'] = $iapCust;
		}
		if ($iapSale['newpe'] == "Y") {
			$iapSale['perec'] = $iapPE;
		}
		$iapRet = IAP_Update_Savearea("IAP291SA", $iapSale, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$iapOrigAction = $_REQUEST['origaction'];

		$DivShow = "inline";	

	} //	if ($iapRefundRet !=  0) 

//
//
//================================================================================================================
// Verify Balances --- Temporary
//

	if (!isset($_REQUEST['srefund'])) {
		echo "<span class=iapSuccess>Verifying Balances.</span><br>";
		require_once(ABSPATH."/IAPDBServices.php");

		$sql = "SELECT sale_id, sale_item_cost, sale_net, sale_profit,  
				   sum(saledet_total_cost) as saledet_sumcost, sum(saledet_total_price) as saledet_sumprice, 
				   sum(saledet_total_profit) as saledet_sumprofit
			FROM iap_sales 
			full join iap_sales_detail on saledet_sid = sale_id
			WHERE sale_id = ".$iapSale['sale_id'];
		$iapRet = iapProcessMySQL("select", $sql);
		if ($iapRet['retcode'] < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve Sale to verify. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		} elseif ($iapRet['numrows'] == 0){
			echo "<span class=iapError>[VERIFY] Sale (".$iapSale['sale_id'].") /Sale Detail has zero rows. Reference is /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		} else {
			$NewSale = $iapRet['data'][0];
			if ($NewSale['sale_item_cost'] != $NewSale['saledet_sumcost']) {
				echo "<span class=iapWarning>[VERIFY] Sale cost of ".
					 number_format((double)$NewSale['sale_item_cost'], 2, '.', '').
					 " does not match sales detail costs of ".
					 number_format((double)$NewSale['saledet_sumcost'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewSale['sale_net'] != $NewSale['saledet_sumprice']) {
				echo "<span class=iapWarning>[VERIFY] Sale net of ".
					 number_format((double)$NewSale['sale_net'], 2, '.', '').
					 " does not match sales detail price of ".
					 number_format((double)$NewSale['saledet_sumprice'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewSale['sale_profit'] != $NewSale['saledet_sumprofit']) {
				echo "<span class=iapWarning>[VERIFY] Sale profit of ".
					 number_format((double)$NewSale['sale_profit'], 2, '.', '').
					 " does not match sales detail profit of ".
					 number_format((double)$NewSale['saledet_sumprofit'], 2, '.', '').
					 "</span><br>";
			}
		}
		echo "<span class=iapSuccess>Sales/Sales Detail Verify Complete.</span><br>";

		$sql = "SELECT pe_type, pe_sales_cnt, pe_cost_of_items, pe_net_sales, pe_profit, pe_other_expenses, pe_shipping, pe_sales_tax, pe_total_sales, pe_mileage, count(sale_id) as sale_cntsales, sum(sale_item_cost) as sale_sumcost, sum(sale_net) as sale_sumnet, sum(sale_profit) as sale_sumprofit, sum(sale_other_exp) as sale_sumother, sum(sale_shipping) as sale_sumship, sum(sale_sales_tax) as sale_sumtax, sum(sale_total_amt) as sale_sumtotal, sum(sale_mileage) as sale_summiles
			   FROM iap_party_events 
			   full join iap_sales on sale_peid = pe_id
			   WHERE pe_id = ".$iapSale['sale_peid'];
		$iapRet = iapProcessMySQL("select", $sql);
		if ($iapRet['retcode'] < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve PE to verify. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		} elseif ($iapRet['numrows'] == 0){
			echo "<span class=iapError>[VERIFY] PE/Sales (".$iapSale['sale_peid']."/".$iapSale['sale_id'].") has zero rows. Reference is /".basename(__FILE__)."/".__LINE__."</span><br>";
		} else {
			$NewPE = $iapRet['data'][0];
			if ($NewPE['pe_sales_cnt'] != $NewPE['sale_cntsales']) {
				echo "<span class=iapWarning>[VERIFY] PE number items of ".
					 number_format((double)$NewPE['pe_sales_cnt'], 2, '.', '').
					 " does not match count of sales of ".
					 number_format((double)$NewPE['sale_cntsales'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_cost_of_items'] != $NewPE['sale_sumcost']) {
				echo "<span class=iapWarning>[VERIFY] PE item cost of ".
					 number_format((double)$NewPE['pe_cost_of_items'], 2, '.', '').
					 " does not match sales cost of ".
					 number_format((double)$NewPE['sale_sumcost'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_net_sales'] != $NewPE['sale_sumnet']) {
				echo "<span class=iapWarning>[VERIFY] PE net sales of ".
					 number_format((double)$NewPE['pe_net sales'], 2, '.', '').
					 " does not match sales net of ".
					 number_format((double)$NewPE['sale_sumnet'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_profit'] != $NewPE['sale_sumprofit']) {
				echo "<span class=iapWarning>[VERIFY] PE profit of ".
					 number_format((double)$NewPE['pe_profit'], 2, '.', '').
					 " does not match sales profit of ".
					 number_format((double)$NewPE['sale_sumprofit'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_shipping'] != $NewPE['sale_sumship']) {
				echo "<span class=iapWarning>[VERIFY] PE shipping of ".
					 number_format((double)$NewPE['pe_shipping'], 2, '.', '').
					 " does not match sales shipping of ".
					 number_format((double)$NewPE['sale_sumship'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_sales_tax'] != $NewPE['sale_sumtax']) {
				echo "<span class=iapWarning>[VERIFY] PE tax of ".
					 number_format((double)$NewPE['pe_tax'], 2, '.', '').
					 " does not match sales tax of ".
					 number_format((double)$NewPE['sale_sumtax'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_total_sales'] != $NewPE['sale_sumtotal']) {
				echo "<span class=iapWarning>[VERIFY] PE total sales of ".
					 number_format((double)$NewPE['pe_total_sales'], 2, '.', '').
					 " does not match sales total of ".
					 number_format((double)$NewPE['sale_sumtotal'], 2, '.', '').
					 "</span><br>";
			}
			if ($NewPE['pe_type'] != "E"
			and $NewPE['pe_type'] != "P") {
				if ($NewPE['pe_mileage'] != $NewPE['sale_summiles']) {
				 	echo "<span class=iapWarning>[VERIFY] PE mileage of ".
						 number_format((double)$NewPE['pe_mileage'], 2, '.', '').
						 " does not match sales mileage of ".
						 number_format((double)$NewPE['sale_summiles'], 2, '.', '').
						 "</span><br>";
				}
			}
		}
		echo "<span class=iapSuccess>PE/Sales Verify Complete.</span><br>";
	}
// VERIFY end
// =============================================================================
	echo "<hr>";

} else {

	$iapOrigAction = "NEW";

	if (IAP_Remove_Savearea("IAP291SA") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the Sale savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapS = (array) IAP_Build_New_Row(array("table" => "sale"));
	$iapSale = $iapS[0];
	$iapSale['sale_type'] = "P";
	$iapSale['saledtl'] = array();
	$iapSale['newcust'] = "N";
	$iapSale['newpe'] = "N";
	$iapSale['sale_item_cost'] = 0;
	$iapSale['sale_tax_override'] = "N";
	$iapSale['sale_tax_region'] = ucwords($_REQUEST['UserData']['TaxRegion']);
	$iapSale['sale_tax_rate'] = $_REQUEST['UserData']['TaxRate'];
	$iapSale['PEOrig'] = "";

	$iapRet = IAP_Create_Savearea("IAP291SA", $iapSale, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$DivSelect = "inline";
	$DivShow = "none";
}

$iapSelEna = "readonly";

$iapSales = IAP_Get_Sale_List();
if ($iapSales < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve sales. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}

$sSales = "";
$c = "";
if ($iapSales != NULL) {
	foreach ($iapSales as $iapS) {
		$iapCNm = str_replace('"', '', $iapS['cust_name']);
		$s = $iapCNm." on ".date("m/d/Y", strtotime($iapS['sale_date']));
		if ($iapS['pe_type'] == "E") {
			$s = $s." At ".$iapS['pe_sponsor']."'s Event";
		} elseif ($iapS['pe_type'] == "I") {
			$s = $s." In Person ";
		} elseif ($iapS['pe_type'] == "P") {
			$s = $s." At Party ".$iapS['pe_party_no'];
		} elseif ($iapS['pe_type'] == "W") {
			$s = $s." On My Website ";
		}

		$s = $s." For $".number_format($iapS['sale_total_amt'], 2, '.', '');
		$sSales = $sSales.$c.'{"label": "'.$s.'", "saleid": "'.strval($iapS['sale_id']).'", "custname": "'.$iapCNm.'"}';
		$c = ',';
	}
}

$iapCusts = IAP_Get_Customer_List();
if ($iapCusts < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
if ($iapCusts != NULL) {
	$sCusts = "";
	$c = "";
	foreach($iapCusts as $iapC) {
		$iapCNm = str_replace('"', '', $iapC['cust_name']);
		if ($iapC['tax_region_name'] == "") {
			$iapC['tax_region_name'] = $_REQUEST['UserData']['TaxRegion'];
			$iapC['tax_combined_rate'] = $_REQUEST['UserData']['TaxRate'];
		}
		$sCusts = $sCusts.$c.'{"label": "'.$iapCNm.'", "custid": "'.$iapC['cust_no'].'", "taxreg": "'.ucwords($iapC['tax_region_name']).'", "taxrate": "'.number_format($iapC['tax_combined_rate'], 6, '.', '').'"}';
		$c = ",";
		if ($iapSale['status'] != "NEW"
		and $iapC['cust_no'] == $iapSale['sale_customer']) {
			$iapSale['cust_name'] = $iapCNm;
		}
	}
	$iapSelEna = "";
}

$iapSale['pe_selector'] = "";
$iapPar = IAP_Get_PE_List("N");		// Do not get closed parties
if ($iapPar < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
if ($iapPar != NULL) {
	$sParties = "";
	$sEvents = "";
	$cE = "";
	$cP = "";
	foreach($iapPar as $iapP) {
		if ($iapP['tax_region_name'] == "") {
			$iapP['tax_region_name'] = $_REQUEST['UserData']['TaxRegion'];
			$iapP['tax_combined_rate'] = $_REQUEST['UserData']['TaxRate'];
		}
		$sponsor = trim($iapP['pe_sponsor']);
		$sponsor = str_replace('.', '', $sponsor);
		$sponsor = str_replace(',', '', $sponsor);
		$sponsor = str_replace("'", "", $sponsor);
		$sponsor = str_replace('-', '', $sponsor);
		if ($iapP['pe_type'] == "P") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".$sponsor;
			$sParties = $sParties.$cP.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'", "taxreg": "'.ucwords($iapP['tax_region_name']).'", "taxrate": "'.number_format($iapP['tax_combined_rate'], 6, '.', '').'"}';
			$cP = ",";
			if ($iapSale['sale_peid'] == $iapP['pe_id']) {
				$iapSale['pe_selector'] = $peText;
			}
		} elseif ($iapP['pe_type'] == "E") {
			$peText = date("m/d/Y", strtotime($iapP['pe_date']))." event at ".$sponsor;
			$sEvents = $sEvents.$cE.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'", "id": "'.$iapP['pe_id'].'", "taxreg": "'.$iapP['tax_region_name'].'", "taxrate": "'.number_format($iapP['tax_combined_rate'], 6, '.', '').'"}';
			$cE = ",";
			if ($iapSale['sale_peid'] == $iapP['pe_id']) {
				$iapSale['pe_selector'] = $peText;
			}
		}
	}
}

require_once("IAPGetItemLists.php");
$iapItemLists = IAP_Get_Item_Lists();
$sCodes = $iapItemLists[0];
$sDescs = $iapItemLists[1];

$iapCats = IAP_Get_Codes();
if ($iapCats < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve categories. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	return;
}
if ($iapCats != NULL) {
	$iapCatOpts = "";
	foreach ($iapCats as $iapC) {
		$iapCatOpts = $iapCatOpts."<option value='".$iapC['code_code']."'>".$iapC['code_value']."</option>";
	}
}

if ($iapPageError != 0) {
	echo "<span class=iapError style='font-weight:bold;'>Errors were detected. Please fix them and submit again.</span><br><br>";
}

$iapReadOnly = IAP_Format_Heading("Sales Entry/Edit");

$h = IAP_Do_Help(3, 291, 1); // level 3, page 291, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}

?>

<div id='pchoose' >
<form name='pselform' action='?action=p291retA&origaction=initial' method='POST'>
<?php
	if (empty($sSales)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "No Sales on file. Click on ADD.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<span class=iapFormLabel style='padding-left: 40px;'>";
	echo "<label for='sSaleList'>Select a sale: </label>";
	echo "<input id='sSaleList' size='50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 291, 1); //		Help Narative	<!-- level 1, page 291, section 1 -->

	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='sGoClicked()'>";
	echo "<br><span class=iapError id=sError style='display:none;'>The sale was not found. Retry or click Add.</span>";

	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapFormLabel style='padding-left: 50px;'>";
		echo "<input type='button' class=iapButton name='sAdd' id='sAdd' value='Add A New Sale' onclick='sAddClicked()' />";
	}
	echo "<br>".$iapMsg."</span>";
?>
</form>
</div>

<div id='sdetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='width:100%'>

<form name='purform' action='?action=p291retB&origaction=<?php echo $iapOrigAction; ?>' method='POST' onkeypress='stopEnterSubmitting(window.event)'>

<?php
if (!empty($iapSale['sale_date'])) {
	$d3 = date("m/d/Y", strtotime($iapSale['sale_date']));
} else {
	$d3 = date("m/d/Y");
}
?>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=iapFormLabel style='font-weight: bold;'>Use the tab key to move between fields</span>
<br><br>

<table>
<tr><td style="width:17%;"><label for='scustomers' class='iapFormLabel' style='vertical-align:top;'>Select a Customer: </label></td>
<td style="width:83%;">
<?php
	if (empty($sCusts)) {
		$iapCustReadOnly = "readonly ";
		$iapMsg = "No customers on file. Click on New Customer..";
	} elseif ($iapSale['status'] != "NEW") {
		$iapCustReadOnly = "readonly ";
	} else {
		$iapCustReadOnly = "";
		$iapMsg = "";
	}
	echo "<input ".$iapCustReadOnly." name='scustomers' id='scustomers' tabindex='1' size=50' value='".$iapSale['cust_name']."'>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 291, 2);     //		Help Narative	<!-- level 1, page 291, section 2 -->

	echo "<span id=cError class=iapError style='display:none;'><br>The customer was not found. Retry or click Add.</span>";

	echo "&nbsp;&nbsp;&nbsp;";
	if ($iapReadOnly != "readonly") {
		echo "<br><button class=iapButton name='saddcust' type='button' onclick='sAddCustomer()'>New Customer</button>";
	}
	echo "<br>".$iapMsg."</span>";

?>
</td></tr>
</table>
<?php
	if ($iapSale['newcust'] == "Y") {
		$c = "inline;";
	} else {
		$c = "none;";
	}
?>
	<div id=iapNewCust style="display:<?php echo $c; ?>">
		<table>
		<tr><td style="width:17%;" class="iapFormLabel"></td><td style="width:83%;"></td></tr>

		<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span class='iapFormLabel' id='snewcustlbl'>Please provide the following information about the new customer.<br> &nbsp;&nbsp;&nbsp;&nbsp;This new customer will need to be editted later to enter any additional information.</span>
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Name:</span></td>

.			<td style="width:83%;"><input tabindex="2" type="text" name="snewcname" id="snewcname" size="50" value="<?php echo $iapCust['cust_name']; ?>" onchange="iapChkNewCustomer()">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Street:</span></td>
			<td style="width:83%;"><input tabindex="3" maxlength="50" size="50" name="snewcstrt" id="snewcstrt" value="<?php echo $iapCust['cust_street']; ?>">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>City, State, Zip:</span></td>
			<td style="width:83%;"><input tabindex="4" maxlength="40" size="40" name="snewccity" id="snewccity" value="<?php echo $iapCust['cust_city']; ?>">
				<input tabindex="5" maxlength="2" size="2" name="snewcstate" id="snewcstate" value="<?php echo $iapCust['cust_state']; ?>">
				<input tabindex="6" maxlength="10" size="10" name="snewczip" id="snewczip" value="<?php echo $iapCust['cust_zip']; ?>">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Email:</span></td>
			<td style="width:83%;"><input tabindex="7" type="text" name="snewcemail" id="snewcemail" size="50" maxlength="75">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Phone:</span></td>
			<td style="width:83%;"><input tabindex="8" type="text" name="snewcphone" id="snewcphone" size="15">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Newsletter:</span></td>
			<td style="width:83%;"><input tabindex="9"  type="checkbox" name="snewcnews" id="snewcnews" value="cnewsyes" checked> Check to send newsletter.
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Birth Day:</span></td>
			<td style="width:83%;"><input tabindex="10" maxlength="7" size="7" name="snewcbirth" id="snewcbirth" placeholder="mm/dd" value="<?php echo $iapCust['cust_birthday']; ?>">
		</td></tr>
		</table>
	</div>

<table>

<tr><td style="width:17%;" class="iapFormLabel"></td><td style="width:83%;"></td></tr>

<tr><td style="width:17%;"><span class='iapFormLabel'>Follow Up For:</span></td>
<td style="width:83%;">Possible Consultant 
	<input <?php echo $iapReadOnly; ?> tabindex="11"  type="checkbox" name="scposscon" id="scposscon" 
		<?php if ($iapCust['cust_followup_consultant'] == "Y") { echo " checked"; } ?>>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Possible Party 
	<input <?php echo $iapReadOnly; ?> tabindex="12"  type="checkbox" name="scposspar" id="scposspar" 
		<?php if ($iapCust['cust_followup_party'] == "Y") { echo " checked"; } ?>>
</td></tr>

<tr><td style="width:17%;" class="iapFormLabel"></td><td style="width:83%;"></td></tr>

<tr><td style="width:17%;"><span class='iapFormLabel'>Type of Sale:</span></td>
<td style="width:83%;">
	<input type="radio" name="stype" id="stypeparty" value="P" tabindex="13" onchange='sSetType("P")'
		<?php if ($iapSale['sale_type'] == "P") { echo " checked"; } ?>
	>Party&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeevent" value="E" tabindex="13" onchange='sSetType("E")'
		<?php if ($iapSale['sale_type'] == "E") { echo " checked"; } ?>
	>Event&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeindiv" value="I" tabindex="13" onchange='sSetType("I")'
		<?php if ($iapSale['sale_type'] == "I") { echo " checked"; } ?>
	>Individual&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypefacebk" value="F" tabindex="13" onchange='sSetType("F")'
		<?php if ($iapSale['sale_type'] == "F") { echo " checked"; } ?>
	>Facebook**&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeweb" value="W" tabindex="13" onchange='sSetType("W")'
		<?php if ($iapSale['sale_type'] == "W") { echo " checked"; } ?>
	>Website**&nbsp;&nbsp;&nbsp;
	<input type="radio" name="stype" id="stypeother" value="O" tabindex="13" onchange='sSetType("O")'
		<?php if ($iapSale['sale_type'] == "O") { echo " checked"; } ?>
	>Other**
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 3);//		Help Narative	<!-- level 1, page 291, section 3 --> ?>
</td></tr>
<tr><td colspan="2">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=iapWarning>**These Types of Sale do NOT affect your inventory.</span>
</td></tr>
</table>

<?php
	$d = "none";
	if ($iapSale['sale_type'] == "I"
	or $iapSale['sale_type'] == "F"
	or $iapSale['sale_type'] == "W"
	or $iapSale['sale_type'] == "O") {
		$d = "inline";
	}
?>
	<div id=snonpediv1 style="display:<?php echo $d;?>">
		<table>
		<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
		<tr><td style="width:17%;">
			<span class='iapFormLabel'>Date of Sale:</span></td>
			<td style="width:83%;">
				<input <?php echo $iapReadOnly; ?> tabindex="14" maxlength="15" size="15" name="ssaledate" id="ssaledate" placeholder="mm/dd/yyyy" value="<?php echo $d3; ?>" onchange='sChangedNonPE()'>
				&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 5);//		Help Narative	<!-- level 1, page 291, section 5  --> ?>
		</td></tr>
		<tr><td style="width:17%;"></td><td style="width:83%;">-- OR --</td></tr>
		</table>
	</div>

<table>
<tr><td style="width:17%;"></td><td style="width:83%;">
<span id=parError class=iapError style='display:none;'>The party was not found. Retry or click Add.</span>
<span id=pevtError class=iapError style='display:none;'>The event was not found. Retry or click Add.</span>
</td></tr>

<?php
	$p = "none";
	if ($iapSale['sale_type'] != "E") {
		$ps = " value='".$iapSale['pe_selector']."'";
		$p = "inline";
	}
	$e = "none";
	if ($iapSale['sale_type'] == "E") {
		$es = " value='".$iapSale['pe_selector']."'";
		$e = "inline";
	}

	echo "<tr style='vertical-align:top;'><td style='width:17%; vertical-align:-top;'>";
	echo "<label class='iapFormLabel'  id='spelabel' style='vertical-align:top;'>Select a Party:</label>";
	echo "<td style='width:83%;'>";
	echo "<input ".$iapReadOnly." style='display:".$p.";' type='text' tabindex='15' name='speparty' id='speparty'  size='50'".$ps.">";
	echo "<input ".$iapReadOnly." style='display:".$e.";' type='text' tabindex='15' name='speevent' id='speevent'  size='50'".$es.">";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 291, 4);//		Help Narative	<!-- level 1, page 291, section 4  -->
	echo "</td></tr>";
	echo "<tr><td style='width:17%;'></td><td style='width:83%;'>";
	if ($iapReadOnly != "readonly") {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class=iapButton name='saddpe' id='saddpe' type='button' onclick='sAddPE()'>New Party</button>";
	}
	echo "</td></tr>";
?>

</table>

	<?php
		if ($iapSale['newpe'] == "Y") {
			$p = "inline;";
		} else {
			$p = "none;";
		}
	?>
	<div id=iapNewPE style="display:<?php echo $p; ?>">
		<table>
		<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
		<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span class='iapFormLabel'>Please provide the following information regarding this new <span id='snewpecmt1'>party</span>. This new <span id='snewpecmt2'>party</span> will need to be editted later to enter any additional information.</span>
		</td></tr>

		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='snewpedatelbl'>Date:</label></td>
			<td style="width:30%;"><input tabindex="16" type="text" name="snewpedate" id="snewpedate" size="15" placeholder="mm/dd/yyyy" <?php echo date("Y-m-d", strtotime($iapSale['sale_date'])); ?> onchange="sNewPEDateChg();">
			&nbsp;&nbsp;&nbsp;


// also in JS sClearNewPE. check for others
			<td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='snewpenolbl'>Party Number:</label></td>
			<td style="width:83%;"><input tabindex="17" type="text" name="snewpedate" id="snewpedate" size="15" placeholder="mm/dd/yyyy" <?php echo date("Y-m-d", strtotime($iapSale['sale_date'])); ?> onchange="sNewPEDateChg();">



		</td></tr>

		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='snewpenamelbl'>Hostess:</label</td>
			<td style="width:83%;"><input tabindex="18" type="text" name="snewpename" id="snewpename" size="50">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Street:</span></td>
			<td style="width:83%;"><input tabindex="19" type="text" name="snewpestrt" id="snewpestrt" size="50">
		</td></tr>
		<tr><td style="width:17%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>City, State, Zip:</td>
			<td style="width:83%;">
				<input tabindex="20" type="text" name="snewpecity" id="snewpecity" size="30">&nbsp;&nbsp;
				<input tabindex="21" type="text" name="snewpestate" id="snewpestate" size="3">&nbsp;&nbsp;
				<input tabindex="22" type="text" name="snewpezip" id="snewpezip" size="10">
		</td></tr>
		</table>
	</div>

<?php
	$d = "none";
	if ($iapSale['sale_type'] == "W") {
		$d = "inline";
	}
?>
	<div id=snonpediv3 style="display:<?php echo $d;?>">
		<table>
		<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
		<tr><td style="width:17%;"><label class=iapFormLabel>Vendor Order No:</label></td>
			<td style="width:83%;">
			<input <?php echo $iapReadOnly; ?> type='text' tabindex='23' maxlength="15" size="15" name="svendord" id="svendord" value='<?php echo $iapSale['sale_vendor_order']; ?>'>
			&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 5);//		Help Narative	<!-- level 1, page 291, section 5  --> ?>
		</td></tr>
		</table>
	</div>

<?php
	$d = "none";
	if ($iapSale['sale_type'] == "I"
	or $iapSale['sale_type'] == "F"
	or $iapSale['sale_type'] == "W"
	or $iapSale['sale_type'] == "O") {
		$d = "inline";
	}
?>
	<div id=snonpediv2 style="display:<?php echo $d;?>">
		<table>
		<tr><td style="width:3%;"></td><td style="width:14%;">	</td><td style="width:83%;"></td></tr>
		<tr><td colspan="3"><span class=iapFormLabel>Expenses Related To This Sale</span>
				&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 6);  //		Help Narative	<!-- level 1, page 291, section 6 --> ?>
		</td></tr>
		<tr><td style="width:3%;"></td><td style="width:14%;"><label class=iapFormLabel>Mileage:</label></td>
			<td style="width:83%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='23' maxlength="7" size="7" name="smileage" id="smileage" step="0.01" value="<?php echo $iapSale['sale_mileage']; ?>" />
		</td></tr>
		<tr><td style="width:3%;"></td><td style="width:14%;"><label class=iapFormLabel>Other Expense:</label></td>
			<td style="width:83%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='24' maxlength="7" size="7" name="sotherexp" id="sotherexp" step="0.01" value="<?php echo $iapSale['sale_other_exp']; ?>" />
		</td></tr>
		<tr><td style="width:3%;"></td><td style="width:14%;"><label class=iapFormLabel>Explain Other Expenses:</label></td>
			<td style="width:83%;">
				<textarea name='sexpexplain' id='sexpexplain' tabindex="25" cols='50' rows='4' wrap='soft' <?php echo $iapReadOnly; ?>><?php echo $iapSale['sale_exp_explained']; ?></textarea>
		</td></tr>

		<tr><td colspan="2"><label class='iapFormLabel' id='ssaleloclbl'>Location of Sale:</label</td>
			<td style="width:83%;"><input tabindex="26" type="text" name="ssaleloc" id="ssaleloc" size="50" value=<?php echo $iapSale['sale_location']; ?>>
		</td></tr>
		</table>
	</div>

<table>
	<tr><td style="width:3%;"></td><td style="width:14%;"></td><td style="width:83%;"></td></tr>

<?php
	if ($iapReadOnly != "readonly") {
?>
		<tr><td colspan="3">
			<span class=iapFormLabel style="text-decoration: underline;">Enter Items Sold</span>
			&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 7);  //		Help Narative	<!-- level 1, page 291, section 7 -->  ?>
			&nbsp;&nbsp;&nbsp;<span class=iapFormLabel>Start typing an item code or description for a list.</span>
		</td></tr>

		<tr><td colspan="2"></td>
		<td style="width:83%;">
			<span id=sitemerror class=iapError> </span>
		</td></tr>

		<tr><td style="width:3%;"></td><td style="width:14%;">
			<label for="sitemcode" class=iapFormLabel id=sitemcodelbl>Item Code:</label></td>
		<td style="width:83%;">
			<input tabindex='27' size='50' name='sitemcode' id='sitemcode' onfocus='sItemFocus()' />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' class=iapButton name='saddnewitem' id='saddnewitem' value='Add A New Item' onclick='sAddItem(); return false;' />
		</td></tr>

		<tr><td style="width:3%;"></td><td style="width:14%;">
			<label for="sitemdesc" class=iapFormLabel id=sitemdesclbl>Description:</label></td>
		<td style="width:83%;">
			<input tabindex='28' maxlength='100' size='50' name='sitemdesc' id='sitemdesc' onfocus='sItemFocus()' />
		</td></tr>

		<tr><td style="width:3%;"></td><td style="width:14%;">
			<label class=iapFormLabel id=sitemqtylbl>Quantity:</label></td>
		<td style="width:83%;">
			<input type='number' tabindex="29" maxlength="10" size="10" name="sitemqty" id="sitemqty" step="1">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class=iapFormLabel id="sitempricelbl">Price:</label>&nbsp;&nbsp;
			<input type='number' tabindex="30" maxlength="10" size="10" name="sitemprice" id="sitemprice" step="0.01">
		</td></tr>
		</table>

		<div id="iapNewItem" style="text-align: left; display: none;">
			<table>
			<tr><td colspan="2">
				<label class=iapFormLabel id=snewitemcostlbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Item Cost:</label>&nbsp;
				<input type='number' tabindex="31" maxlength="10" size="10" name="snewitemcost" id="snewitemcost" step="0.01">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label class=iapFormLabel id=snewitemunitslbl>Saleable Units In Package:</label>&nbsp;
				<input type='number' tabindex="32" maxlength="10" size="10" name="snewitemunits" id="snewitemunits" step="1">
			</td></tr>
			<tr><td colspan="2">
				<label class=iapFormLabel id=snewitemcatlbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category:</label>&nbsp;
				<select tabindex="33" name="snewitemcat" id="snewitemcat" size='1'>
					<option value='---'>Select A Category</option><?php echo $iapCatOpts; ?>
				</select>
			</td></tr>
			</table>
		</div>
<?php
	}
?>

<table>
<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<?php
	if ($iapReadOnly != "readonly") {
?>
		<tr><td colspan="2" style="text-align:center;">
			<span class=iapFormLabel>
			<input <?php echo $iapReadOnly; ?> class=iapButton type='button' tabindex='34' name='sRecItem' id='sRecItem' value='Record This Item' onclick='sRecordItem(); return false;'></span>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=iapButton type='button' tabindex='35' name='sClearItem' id='sClearItem' value='Clear This Item&apos;s Data' onclick='sClrItemData(); return false;'>
		</td></tr>
		<tr><td colspan="2" style="text-align:center;">
		<span class=iapWarning>(WARNING: Do NOT use the Submit button until all items have been recorded)</span>
		</td></tr>
<?php
	}
?>
</table>

<br><br>
<fieldset style='border: 1px solid #000; top: 5px; right: 5px; bottom: 5px; left: 5px;'>
&nbsp;&nbsp;<span style="text-decoration: underline;">Items Sold</span>
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 8);  //		Help Narative	<!-- level 1, page 291, section 8 -->  ?>
&nbsp;&nbsp;&nbsp;<span class=iapWarning>(Don't Forget To Click on Submit When All Items Have Been Recorded!)</span><br>
<br>

<table id='iapSold' class=iapTable><tbody class=iapTBody>
<tr>
<td width='3%'></td><td width='2%'></td>
<td width='13%' class=iapFormLabel><span style='text-decoration: underline;'>Item Code</span></td>
<td width='55%' class=iapFormLabel><span style='text-decoration: underline;'>Description</span></td>
<td width='5%' class=iapFormLabel><span style='text-decoration: underline;'>Qty</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Price</span></td>
<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Value</span></td>
<td width='2%'></td>
</tr>

<?php
	$iapItems = $iapSale['saledtl'];
	$sRows = 0;
	foreach($iapItems as $iapI) {
		$sRows = $sRows + 1;
		$iapColumns = explode("~", $iapI);
		echo "<tr id='Sold<?php echo strval($sRows); ?>'>";
		echo "<td width='2'><span style='color:darkgreen;>v</span>";
		echo "<td width='1%'></td>";
		echo "<td width='2%'><img src='".$_REQUEST['IAPUrl']."/MyImages/Icons/DeleteRedSM.png' onclick='sDelSold(".$sRows."); return(false);'>&nbsp;&nbsp;</td>";
		echo "<td width='13%' class=iapFormLabel>".$iapColumns[0]."</td>";
		echo "<td width='55%' class=iapFormLabel>".$iapColumns[1]."</td>";
		echo "<td width='5%' class=iapFormLabel>".$iapColumns[2]."</td>";
		echo "<td width='10%' class=iapFormLabel>".$iapColumns[3]."</td>";
		echo "<td width='10%' class=iapFormLabel>".number_format($iapColumns[2]*$iapColumns[3], 2, '.', '')."</td>";
		echo "<td width='2%'><input type='hidden' id='recsid".strval($sRows)."' value='|".strval($iapColumns[5])."|'></td></tr>";
	}
?>
</tbody></table>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyImages/Icons/Delete_IconSM.png'><span style='vertical-align: middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clicking on this symbol next to a row removes the row</span><br>
</fieldset>
<br><br>
<table style="width:100%;">
<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<tr><td style="width:17%;"><span class=iapFormLabel>Net Sale:</span>
	</td><td style="width:83%;">
		<input readonly style="text-align:right;" maxlength="15" size="15" name="snetsale" id="snetsale" value="<?php echo number_format($iapSale['sale_net'], 2, '.', ''); ?>"> 
		&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 9);  // Help Narative	<!-- level 1, page 291, section 9 -->  ?>
</td></tr>

<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<tr><td style="width:17%;"><span class=iapFormLabel>Shipping:</span>
	</td><td style="width:83%;">
		<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='36' maxlength="7" size="7" name="sshipping" id="sshipping" step="0.01" onchange="sShippingChg();" value="<?php echo number_format($iapSale['sale_shipping'], 2, '.', ''); ?>">
	&nbsp;&nbsp;&nbsp;
	<span class=iapFormLabel>Tracking Number:</span>&nbsp;
	<input <?php echo $iapReadOnly; ?> type='text' tabindex='37' maxlength="30" size="30" name="strackno" id="strackno" value="<?php echo $iapSale['sale_tracking_no']; ?>">

</td></tr>

<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<?php
	if (empty($iapSale['sale_tax_region'])) {
		$iapSale['sale_tax_region'] = $_REQUEST['UserData']['TaxRegion'];
		$iapSale['sale_tax_rate'] =  $_REQUEST['UserData']['TaxRate'];
	}
?>

<tr><td colspan="2">
	<span class=iapFormLabel id=staxregion><?php echo $iapSale['sale_tax_region']."</span><span class=iapFormLabel > Sales Tax"; ?></span>
</td></tr>
<tr><td style="width:17%;">
	<span class=iapFormLabel>&nbsp;&nbsp;&nbsp;Sales Tax Rate(%):</span>
</td>
<td style="width:83%;">
	<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='38' maxlength="7" size="7" name="staxrate" id="staxrate" step="0.01" onchange="sTaxRateChg();" value=<?php echo number_format($iapSale['sale_tax_rate'] * 100, 2, '.', ''); ?>>
	&nbsp;&nbsp;&nbsp;
	<span class=iapFormLabel>Calculated Tax: (can override)</span>&nbsp;
	<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='39' maxlength="6" size="6" name="staxamt" id="staxamt" step="0.01" onchange="sTaxAmtChg();" value="<?php echo number_format($iapSale['sale_sales_tax'], 2, '.', ''); ?>">
&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 10);  //		Help Narative	<!-- level 1, page 291, section 10 -->  ?>
</td></tr>
<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
<tr><td style="width:17%;"><span class=iapFormLabel style="font-size:larger; color:darkgreen;">TOTAL SALE:</span>
<!-- Display only, filled by script -->
	</td><td style="width:83%;">
		<input readonly style="text-align:right; font-size:larger; color:darkgreen;" maxlength="15" size="15" name="stotalsale" id="stotalsale" value="<?php echo number_format($iapSale['sale_total_amt'], 2, '.', ''); ?>">
</td></tr>

<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>


<tr><td style="width:17%;"><span class=iapFormLabel>Payment Method:</span></td>
	<td style="width:83%;">
		<input <?php echo $iapReadOnly; ?> tabindex='40' name="spayment" id="spaycash" value="spaycash" type="radio" onchange="sPayChg();">Cash
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input <?php echo $iapReadOnly; ?> tabindex='40' name="spayment" id="spaycredit" value="spaycredit" type="radio" onchange="sPayChg();">Debit/Credit Card 
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input <?php echo $iapReadOnly; ?> tabindex='40' name="spayment" id="spaycheck" value="spaycheck" type="radio" onchange="sPayChg();">Check 
		&nbsp;&nbsp;Check Number:&nbsp;
		<input tabindex='41' maxlength='10' size='10' name='spaychkno' id='spaychkno' value='<?php echo $iapSale['sale_check_number']; ?>' />
		&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 11);  //		Help Narative	<!-- level 1, page 291, section 11 -->  ?>

</td></tr>

<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<tr><td style="width:17%;"><label class=iapFormLabel id=scommlbl>Comments:</label></td>
	<td style="width:83%;"><textarea name='scomment' id='scomment' tabindex='41' cols='50' rows='5' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapSale['sale_comment']; ?></textarea>
</td></tr>

<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>
<tr><td style="width:17%;"></td><td style="width:83%;"></td></tr>

<?php
$d = "none";
if ($iapSale['sale_status'] != "NEW") {
	$d = "inline";
}
if ($iapReadOnly != "readonly") {
?>
	<tr><td colspan='2' style='text-align:center;'>

	<input class=iapButton tabindex='50' type='submit' name='ssubmit' id='ssubmit' value='Submit' onclick='return sSendForm();'>


<!--
	<button class=iapButton name='ssubmit' id='ssubmit' tabindex='50' onclick='return sSendForm();'>Submit</button>
-->

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<button class=iapButton name='sclear' id='sclear' tabindex='51' onclick='sClearForm(); return(false);'>Clear</button>

	<span style='display:<?php echo $d; ?>' id=srefundbtn>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<button type='submit' class=iapButton name='srefund' id='srefund' tabindex='52' onclick='return confirm("Are you sure you want to refund this sale?");'>Refund Sale</button>
	&nbsp;<?php echo IAP_Do_Help(1, 291, 12)?>
	</span>
	</td></tr>

<?php
}

$d = "none";
if ($iapSale['status'] != "NEW") {
	$d = "inline";
}
?>
<tr><td style="width:17%;">&nbsp;</td><td style="width:83%;"></td></tr>

<tr><td colspan="2" style='text-align:center;'>
<!--
	<input type=button onClick="location.href='index.html' (target='_blank'??)" value='click here'>
	 onclick="location.href = 'www.yoursite.com';"
-->
	<span id=sprtrec style='font_weight:bold; font-size:+1; display:<?php echo $d; ?>;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a id=sprtreclink href="<?php echo $_REQUEST['IAPUrl']; ?>/MyReports/IAPSaleRec.php?action=selected&co=<?php echo strval($iapSale['sale_company']); ?>&s=<?php echo strval($iapSale['sale_id']); ?>" target='_blank'>Print Sales Receipt
	</a>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 13)?>
	</span>
</td></tr>
<tr><td style="width:17%;">&nbsp;</td><td style="width:83%;"></td></tr>
</table>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="">
<input type='hidden' name='IAPDATA' id='IAPDATA' value="<?php echo $iapSale['saledtl']; ?>">
<input type="hidden" name="SUPDATETYPE" id="SUPDATETYPE" value="">
<input type="hidden" name="SALEID" id="SALEID" value="">
<input type="hidden" name="SSTATUS" id="SSTATUS" value="">
<input type="hidden" name="SDATE" id="SDATE" value="<?php echo $iapSale['sale_date']; ?>">
<input type="hidden" name="STYPE" id="STYPE" value="Party">
<input type="hidden" name="SCOTAXREGION" id="SCOTAXREGION" value="<?php echo $_REQUEST['UserData']['TaxRegion']; ?>">
<input type="hidden" name="SCOTAXRATE" id="SCOTAXRATE" value=<?php echo $_REQUEST['UserData']['TaxRate']; ?>>
<input type="hidden" name="STAXREGION" id="STAXREGION" value="<?php echo $iapSale['sale_tax_region']; ?>">
<input type="hidden" name="STAXRATE" id="STAXRATE" value=<?php echo $iapSale['sale_tax_rate']; ?>>
<input type="hidden" name="STAXOVERRIDE" id="STAXOVERRIDE" value="<?php echo $iapSale['sale_tax_override']; ?>">
<input type="hidden" name="SNEWCUST" id="SNEWCUST" value="">
<input type="hidden" name="SNEWPE" id="SNEWPE" value="N">
<input type="hidden" name="SSELPE" id="SSELPE" value="">
<input type="hidden" name="SPEID" id="SPEID" value="<?php echo $iapSale['PEOrig']; ?>">
<input type="hidden" name="SNEWITEMINFO" id="SNEWITEMINFO" value="">
<input type="hidden" name="STHISITEMSTATUS" id="STHISITEMSTATUS" value="">
<input type="hidden" name="STHISITEMSOURCE" id="STHISITEMSOURCE" value="">
<input type="hidden" name="SIAPURL" id="SIAPURL" value="<?php echo $_REQUEST['IAPUrl']; ?>">

</form>
</p></div>

<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSSales.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSSales.min.js");

?>

var sSList = [<?php echo $sSales; ?>];
var sCList = [<?php echo $sCusts; ?>];
var sPList = [<?php echo $sParties; ?>];
var sEList = [<?php echo $sEvents; ?>];
var sItemList = [<?php echo $sCodes; ?>];
var sDescList = [<?php echo $sDescs; ?>];
</script>
