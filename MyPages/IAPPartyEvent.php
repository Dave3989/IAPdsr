<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Party/Event Maintenance with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("356") < 0) {
	return;
};

if ($_REQUEST['action'] == 'selected') {

	IAP_Remove_Savearea("IAP356PE", $_REQUEST['IAPUID']);

	if (!empty($_REQUEST['party'])) {
		$pekey = $_REQUEST['party'];
	} elseif (!empty($_REQUEST['peid'])) {
		$pekey = $_REQUEST['peid'];
	} else {
		echo "<span class=iapError>IAP INTERNAL ERROR: Nothing passed. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;		
	}
	$iapParty = IAP_Get_PartyEvent_By_Id($pekey);
	if ($iapParty < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapParty['status'] == "NEW") {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	if (empty($iapParty['pe_tax_region'])) {
		if (empty($iapParty['pe_zip'])) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "......pe zip is empty. setting default tax.<br>";
			}

			$treg = "Pennsylvania State Sales Tax";
			$trate = 0.060000;		
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "......pe zip present but no tax set in pe.<br>";
			}

			$iapTax = IAP_Get_Tax($iapParty['pe_zip']);
			if ($iapTax < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR retrieving tax information [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;	
			}
			if ($iapTax == NULL) {

				if ($_REQUEST['debugme'] == "Y") {
					echo "......pe zip is empty. setting default tax.<br>";
				}

				$treg = "Pennsylvania State Sales Tax";
				$trate = 0.060000;
			} else {
				$treg = ucwords(strtolower(trim($iapTax['tax_region_name'])));
				$trate = $iapTax['tax_combined_rate'];
			}
		}
		if ($treg != $iapParty['pe_tax_region']) {
			$iapParty['pe_tax_region'] = $treg;
			$iapWritePE = "Y";
		}
		if ($trate != $iapParty['pe_tax_rate']) {
			$iapParty['pe_tax_rate'] = $trate;
			$iapWritePE = "Y";
		}
		if ($iapWritePE == "Y") {
			$iapRet = IAP_Update_Data($iapParty, "parev");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($iapParty['status'] == "NEW") {
				$iapParty['pe_id'] = $iapRet;
				$iapParty['status'] == "EXISTING";
			}
		}
	}

	$iapOrigAction = $_REQUEST['action'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "......now create the savearea for key IAP356.<br>";
	}

	$iapRet = IAP_Create_Savearea("IAP356PE", $iapParty, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

//	$DivSelect = "none";
	$DivShow = "block";
	
} elseif ($_REQUEST['action'] == 'p356retB') {

// get party/event

	if ($_REQUEST['PEUPDATETYPE'] == "NEW") {
		IAP_Remove_Savearea("IAP356PE", $_REQUEST['IAPUID']);
		$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
		$iapParty = $iapP[0];
		if ($iapParty < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR creating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapParty['sales'] = "N";
		$iapParty['salescnt'] = 0;
		$iapParty['pe_party_hostess'] = 0;
		$iapRet = IAP_Create_Savearea("IAP356PE", $iapParty, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {
		$iapParty = IAP_Get_Savearea("IAP356PE", $_REQUEST['IAPUID']);
		if (empty($iapParty)) {
		    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		    return;
		}
		if (!empty($_REQUEST['PEID'])) {
			$iapParty = IAP_Get_PartyEvent_By_Id($_REQUEST['PEID']);
			if ($iapParty < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected party/event.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}
		$iapParty['PEHOLDDATE'] = $iapParty['pe_date']; 
		$_REQUEST['PEHOSTESSID'] = $iapParty['pe_party_hostess'];
		$iapParty['status'] = "EXISTING";
	}

	if ($_REQUEST['pedelete'] == "Delete") {
		if ($iapParty['pe_sales_cnt'] > 0) {
			echo "<span class=iapError>Party/Events with recorded sales cannot be deleted!</span>";
			$iapPageError = 1;
		} else {
			$iapRet = IAP_Delete_Row($iapParty, "parev");
			if ($iapRet < 0) {
			    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete party/event. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			    return;
			}
			if (!empty($iapParty['pe_event_id'])) {
				$iapCal = IAP_Get_Event_By_Id($iapParty['pe_event_id']);
				if ($iapCal < 0) {
			    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete party/event. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			    	return;
				}
				$iapRet = IAP_Delete_Row($iapCal, "cal");
				if ($iapRet < 0) {
				    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot delete party/event. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				    return;
				}
				if ($iapCal['event_recur'] == "Y"){
					$s = "DELETE FROM iap_cal_repeating WHERE cr_id = '".strval($iapCal['event_id'])."'";
					IAPProcessMySQL("delete", $s);
				}
			}
			echo "<br><br><span class=iapFormHead style='text-align:center' width:100%>The party or event has been deleted. Please select your next action from the functions menu.</span><br><br>";
			return;
		}
	} else {
		if ($_REQUEST['pesavenew'] == "Save As New Party/Event") {
			$iapParty['pe_id'] = NULL;
			$iapParty['sales'] = "N";
			$iapParty['salescnt'] = 0;
			$iapParty['pe_sales_cnt'] = 0;
			$iapParty['pe_cost_of_items'] = 0;
			$iapParty['pe_net_sales'] = 0;
			$iapParty['pe_other_expenses'] = 0;
			$iapParty['pe_exp_explained'] = "";
			$iapParty['pe_profit'] = 0;
			$iapParty['pe_shipping'] = 0;
			$iapParty['pe_sales_tax'] = 0;
			$iapParty['pe_total_sales'] = 0;
			$iapParty['pe_mileage'] = 0;
			$iapParty['pe_party_hostess'] = 0;
			$iapParty['pe_event_id'] = 0;
			$iapParty['pe_party_complete'] = "N";
			$iapParty['status'] = "NEW";
			$iapParty['clone'] = "Y";
		}
		$iapCheckTax = "N";
		$iapPageError = 0;
		$iapChanged = "N";
		$iapEventChanged = "N";

		require_once("IAPValidators.php");

		if ($iapParty['pe_zip'] != "00000"
		and $iapParty['pe_tax_region'] == "") {
			$iapCheckTax = "Y";
		}

		if (!empty($_REQUEST['petype'])) {
			if ($_REQUEST['petype'] != $iapParty['pe_type']) {
				$iapParty['pe_type'] = $_REQUEST['petype'];
				$iapChanged = "Y";
			}
		}
		if (empty($iapParty['pe_type'])) {
			echo "<span class=iapError>Type cannot be blank. Choose either Party or Event!</span><br>";
			$iapPageError = 1;
		}
		if ($iapParty['pe_type'] == "P") {
			if (empty($_REQUEST['peparty'])) {
				echo "<span class=iapError>A Party Number must be supplied for type of Party!</span><br>";
				$iapPageError = 1;
			} elseif (strtoupper($_REQUEST['peparty']) != $iapParty['pe_party_no']) {
				$iapParty['pe_party_no'] = strtoupper($_REQUEST['peparty']);
				$iapChanged = "Y";
			}
			if (empty($iapParty['pe_party_no'])) {
				echo "<span class=iapError>Party Number cannot be blank!</span><br>";
				$iapPageError = 1;
			}
		} else {
			$iapParty['pe_party_no'] = "";
		}

		$Ret = IAP_Validate_Date($iapParty['pe_date'],$_REQUEST['pedate']);
		if ($Ret['Changed'] == "Y"){
			if (isset($iapParty['clone'])
			and $iapParty['pe_date'] == $Ret['Value']) {
				echo "<span class=iapError>The Date must be changed to save as a new party/event!</span><br>";
				$iapPageError = 1;
			} else {
				$iapParty['pe_date'] = $Ret['Value'];
				$iapEventChanged = "Y";
				$iapChanged = "Y";
			}
		}
		if ($Ret['Error'] == 1) {
			echo "<span class=iapError>A valid Date must be entered!</span><br>";
			$iapPageError = 1;
		} elseif ($Ret['Error'] == 2) {
			echo "<span class=iapError>The entered Date is invalid!</span><br>";
			$iapPageError = 1;
		}
		$Ret = IAP_Validate_Time($iapParty['pe_start_time'], $_REQUEST['pestart'], "Y");
		if ($Ret['Changed'] == "Y"){
			$iapParty['pe_start_time'] = $Ret['Value'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($Ret['Error'] == 1) {
			$iapParty['pe_start_time'] = $_REQUEST['pestart'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		} elseif ($Ret['Error'] == 2) {
			echo "<span class=iapError>The entered Date is invalid!</span><br>";
			$iapPageError = 1;
		}

		$Ret = IAP_Validate_Time($iapParty['pe_end_time'], $_REQUEST['peend'], "Y");
		if ($Ret['Changed'] == "Y"){
			$iapParty['pe_end_time'] = $Ret['Value'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($Ret['Error'] == 1) {
			$iapParty['pe_end_time'] = $_REQUEST['peend'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		} elseif ($Ret['Error'] == 2) {
			echo "<span class=iapError>The entered Date is invalid!</span><br>";
			$iapPageError = 1;
		}

		if ($iapParty['pe_type'] == "P") {
			if (!empty($_REQUEST['pecustomer'])) {
				$Ret = IAP_Validate_Nonblank($iapParty['pe_sponsor'], $_REQUEST['pecustomer']);
				if ($Ret['Changed'] == "Y"){
					$iapEventChanged = "Y";
					$iapParty['pe_sponsor'] = $Ret['Value'];
					$iapChanged = "Y";
				}
				if ($Ret['Error'] == 1) {
					echo "<span class=iapError>Please select a hostess!</span><br>";
					$iapPageError = 1;
				}
			}
		} else {
			if (!empty($_REQUEST['pesponsor'])) {
				$Ret = IAP_Validate_Nonblank($iapParty['pe_sponsor'], ucwords($_REQUEST['pesponsor']));
				if ($Ret['Changed'] == "Y"){
					$iapParty['pe_sponsor'] = ucwords($Ret['Value']);
					$iapEventChanged = "Y";
					$iapChanged = "Y";
				}
				if ($Ret['Error'] == 1) {
					echo "<span class=iapError>Sponsor cannot be blank!</span><br>";
					$iapPageError = 1;
				}
			}
		}
		if (empty($iapParty['pe_sponsor'])) {
			if ($iapParty['pe_type'] == "P") {
				echo "<span class=iapError>Please select a hostess!</span><br>";
				$iapPageError = 1;
			} else {
				echo "<span class=iapError>Sponsor cannot be blank!</span><br>";
				$iapPageError = 1;			
			}
		}

		if ($_REQUEST['pestreet'] != $iapParty['pe_street']) {
			$iapParty['pe_street'] = $_REQUEST['pestreet'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($_REQUEST['pecity'] != $iapParty['pe_city']) {
			$iapParty['pe_city'] = $_REQUEST['pecity'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($_REQUEST['pestate'] != $iapParty['pe_state']) {
			$iapParty['pe_state'] = $_REQUEST['pestate'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($_REQUEST['pezip'] != $iapParty['pe_zip']) {
			$iapParty['pe_zip'] = trim($_REQUEST['pezip']);
			$iapCheckTax = "Y";
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}

		if (trim($iapParty['pe_zip']) == ""
		or $iapParty['pe_zip'] == "00000") {
			echo "<span class=iapWarning>Zip Code of location is needed to assign proper tax rate!</span><br>";
		}
		if ($_REQUEST['peurl'] != $iapParty['pe_website']){
			$iapParty['pe_website'] = $_REQUEST['peurl'];
			$iapEventChanged = "Y";
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec1name'] != $iapParty['pe_contact1']) {
			$iapParty['pe_contact1'] = $_REQUEST['pec1name'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec1email'] != $iapParty['pe_c1email']) {
			$iapParty['pe_c1email'] = $_REQUEST['pec1email'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec1phone'] != $iapParty['pe_c1phone']) {
			$iapParty['pe_c1phone'] = $_REQUEST['pec1phone'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec2name'] != $iapParty['pe_contact2']) {
			$iapParty['pe_contact2'] = $_REQUEST['pec2name'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec2email'] != $iapParty['pe_c2email']) {
			$iapParty['pe_c2email'] = $_REQUEST['pec2email'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pec2phone'] != $iapParty['pe_c2phone']) {
			$iapParty['pe_c2phone'] = $_REQUEST['pec2phone'];
			$iapChanged = "Y";
		}
		if (!empty($_REQUEST['pemiles'])) {
			$Ret = IAP_Validate_Nonblank($iapParty['pe_mileage'], $_REQUEST['pemiles'], "Y");
			if ($Ret['Changed'] == "Y"){
				$iapParty['pe_mileage'] = $Ret['Value'];
				$iapChanged = "Y";
			}
			if ($Ret['Error'] == 1) {
				$iapParty['pe_mileage'] = 0;
				$iapChanged = "Y";
			} elseif ($Ret['Error'] == 2) {
				echo "<span class=iapError>Mileage is invalid!</span><br>";
				$iapPageError = 1;
			}
		}
		if (!empty($_REQUEST['pespaceexp'])) {
			$Ret = IAP_Validate_Nonblank($iapParty['pe_space_charge'], $_REQUEST['pespaceexp'], "Y");
			if ($Ret['Changed'] == "Y"){
				$iapParty['pe_space_charge'] = $Ret['Value'];
				$iapChanged = "Y";
			}
			if ($Ret['Error'] == 1) {
				$iapParty['pe_space_charge'] = 0;
				$iapChanged = "Y";
			} elseif ($Ret['Error'] == 2) {
				echo "<span class=iapError>Space Charge is invalid!</span><br>";
				$iapPageError = 1;
			}
		}
		if (!empty($_REQUEST['peotherexp'])) {
			$Ret = IAP_Validate_Nonblank($iapParty['pe_other_expenses'], $_REQUEST['peotherexp'], "Y");
			if ($Ret['Changed'] == "Y"){
				$iapParty['pe_other_expenses'] = $Ret['Value'];
				$iapChanged = "Y";
			}
			if ($Ret['Error'] == 1) {
				$iapParty['pe_other_expenses'] = 0;
				$iapChanged = "Y";
			} elseif ($Ret['Error'] == 2) {
				echo "<span class=iapError>Other Expenses is invalid!</span><br>";
				$iapPageError = 1;
			}
		}
		if ($_REQUEST['peexplexp'] != $iapParty['pe_exp_explained']) {
			$iapParty['pe_exp_explained'] = $_REQUEST['peexplexp'];
			$iapChanged = "Y";
		}
		if ($_REQUEST['pecomments'] != $iapParty['pe_comment']) {
			$iapParty['pe_comment'] = $_REQUEST['pecomments'];
			$iapChanged = "Y";
		}

		if ($iapPageError == 0) {
			$iapWritePE = "N";
			if ($iapChanged == "Y") {
				if ($iapParty['status'] != "NEW"
				and $iapParty['pe_date'] != $iapParty['PEHOLDDATE']) {
// Reschedule sales
					$iapSales = IAP_Get_Sale_By_PE($iapParty['pe_id']);
					if ($iapSales < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales for party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
					if ($iapSales != NULL) {
						foreach($iapSales as $iapS) {
							if ($iapS['sale_type'] == "E"
							or  $iapS['sale_type'] == "F"
							or  $iapS['sale_type'] == "P") {
								$iapS['sale_date'] = $iapParty['pe_date'];
								$iapRet = IAP_Update_Data($iapSale, "sale");
								if ($iapRet < 0) {
						 			echo "<span class=iapError>IAP INTERNAL ERROR writing Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
									exit;
								}
							}
						}
					}
				}
				$iapParty['pe_party_hostess'] = $_REQUEST['PEHOSTESSID'];
				$iapParty['pe_company'] = $_REQUEST['CoId'];
				$iapParty['pe_changed'] = date("Y-m-d");
				$iapParty['pe_changed_by'] = $_REQUEST['IAPUID']; 
				$iapWritePE = "Y";

				if (isset($_REQUEST['peaddcal'])
				and $_REQUEST['peaddcal'] == 'on') {
					if ($iapEventChanged == "Y") {
						if (!empty($iapParty['pe_event_id'])) {
							$iapCal = IAP_Get_Event_By_Id($iapParty['pe_event_id'], "N");
							if ($iapCal < 0) {
								echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
								exit;
							}
						} else {
							$iapC = (array) IAP_Build_New_Row(array("table" => "iapcal"));
							$iapCal = $iapC[0];
							$iapCal['event_account'] = $_REQUEST['CoId'];
						}

						$iapCal['event_begin'] = $iapParty['pe_date'];
						$iapCal['event_end'] = $iapParty['pe_date'];
						if (empty($iapParty['pe_start_time'])
						and empty($iapParty['pe_end_time'])) {
							$iapCal['event_allday'] = "Y";
							$iapCal['event_btime'] = "00:00";
							$iapCal['event_etime'] = "23:59";
						} else {
							$iapCal['event_allday'] = "N";
							if (empty($iapParty['pe_start_time'])) {
								$iapCal['event_btime'] = "00:00";
							} else {
								$iapCal['event_btime'] = $iapParty['pe_start_time'];
							}
							if (empty($iapParty['pe_end_time'])) {
								$iapCal['event_etime'] = "23:59";
							} else {
								$iapCal['event_etime'] = $iapParty['pe_end_time'];
							}
						}
						$iapCal['event_start_timestamp'] = strtotime($iapCal['event_begin']." ".$iapCal['event_btime']);
						$iapCal['event_end_timestamp'] = strtotime($iapCal['event_end']." ".$iapCal['event_etime']);

						if ($iapParty['pe_type'] == "P") {
							$t = "Party ".$iapParty['pe_party_no']." for ".$iapParty['pe_sponsor'];				
						} else {
							$t = "Event ".$iapParty['pe_sponsor'];
						}
						$iapCal['event_title'] = $t;
						$iapCal['event_desc'] = $t;
						if ($iapParty['pe_contact1'] != "") {
							$iapCal['event_desc'] = $iapCal['event_desc']."\nContact: ".$iapParty['pe_contact1'];
							if ($iapParty['pe_c1email'] != "") {
								$iapCal['event_desc'] = $iapCal['event_desc']."\nEmail: ".$iapParty['pe_c1email'];
							}
							if ($iapParty['pe_c1phone'] != "") {
								$iapCal['event_desc'] = $iapCal['event_desc']."\nPhone: ".$iapParty['pe_c1phone'];
							}
						}
						if ($iapParty['pe_contact2'] != "") {
							$iapCal['event_desc'] = $iapCal['event_desc']."\nOR\nContact: ".$iapParty['pe_contact2'];
							if ($iapParty['pe_c2email'] != "") {
								$iapCal['event_desc'] = $iapCal['event_desc']."\nEmail: ".$iapParty['pe_c2email'];
							}
							if ($iapParty['pe_c2phone'] != "") {
								$iapCal['event_desc'] = $iapCal['event_desc']."\nPhone: ".$iapParty['pe_c2phone'];
							}
						}
						$iapCal['event_loc_name'] = $iapParty['pe_sponsor'];
						$iapCal['event_loc_street'] = $iapParty['pe_street'];
						$iapCal['event_loc_city'] = $iapParty['pe_city'];
						$iapCal['event_loc_state'] = $iapParty['pe_state'];
						$iapCal['event_loc_zip'] = $iapParty['pe_zip'];
						$iapCal['event_link'] = $iapParty['pe_website'];
						if ($iapCal['event_'] = "NEW") {
							$iapCal['event_recur'] = "N";
						}
						$iapCal['event_changed'] = date("Y-m-d");
						$iapCal['event_changed_by'] = $_REQUEST['IAPUID'];
						$iapRet = IAP_Update_Data($iapCal, "iapcal");
						if ($iapRet < 0) {
							echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
							exit;
						}
						if ($iapCal['status'] == "NEW") {
							$iapParty['pe_event_id'] = $iapRet;
							if ($iapParty['pe_type'] == "P") {
								$t = "Party";
							} else {
								$t = "Event";
							}
							echo "<br><span class=iapSuccess id='pcalmsg' >".$t." added to your calendar. You should review it. Its Event Id is ".strval($iapRet)."</span><br>";
						}
					}
				}
			}

			if (empty($iapParty['pe_tax_region'])) {
				$t = $iapParty['pe_zip'];
				if (empty($iapParty['pe_zip'])) {

					if ($_REQUEST['debugme'] == "Y") {
						echo "......pe zip is empty. setting default tax.<br>";
					}

					$t = "00000";
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "......pe zip present but no tax set in pe.<br>";
				}

				$iapTax = IAP_Get_Tax($t);
				if ($iapTax < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retrieving tax information [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;	
				}
				if ($iapTax == NULL) {

					if ($_REQUEST['debugme'] == "Y") {
						echo "......pe zip is empty. setting default tax.<br>";
					}

					$iapTax = IAP_Get_Tax("00000");
					if ($iapTax < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR retrieving default tax information [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;	
					}
				}

				$treg = ucwords($iapTax['tax_region_name']);
				$trate = $iapTax['tax_combined_rate'];
				if ($treg != $iapParty['pe_tax_region']) {
					$iapParty['pe_tax_region'] = $treg;
					$iapWritePE = "Y";
				}
				if ($trate != $iapParty['pe_tax_rate']) {
					$iapParty['pe_tax_rate'] = $trate;
					$iapWritePE = "Y";
				}
			}

			if ($iapWritePE == "Y") {
				$iapRet = IAP_Update_Data($iapParty, "parev");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				if ($iapParty['status'] == "NEW") {
					$iapParty['pe_id'] = $iapRet;
					$iapParty['status'] == "EXISTING";
					$iapU = "added";
				} else {
					$iapU = "updated";
				}
				if ($iapParty['pe_type'] == "P") {
					$t = "Party";
				} else {
					$t = "Event";
				}
				echo "<span class=iapSuccess style='font-weight:bold;'>".$t." was successfully ".$iapU.".</span><br><br>";
			}
		}
	}

	if (IAP_Update_Savearea("IAP356PE", $iapParty, $_REQUEST['IAPUID']) < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update the party/event record savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;		
	}

	$iapOrigAction = $_REQUEST['origaction'];

//	$DivSelect = "none";
	$DivShow = "block";	

} else {

	if (IAP_Remove_Savearea("IAP356PE") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the party/event record savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$iapP = (array) IAP_Build_New_Row(array("table" => "parev"));
	$iapParty = $iapP[0];
	$iapRet = IAP_Create_Savearea("IAP356PE", $iapParty, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for party/event record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$_REQUEST['PEHOSTESSID'] = 0;

//	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";

$iapParEv = IAP_Get_PE_List("Y");	// Even get closed parties
if ($iapParEv < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties/events. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
$iapPEList = "";
$c = "";
if ($iapParEv != NULL) {
	foreach ($iapParEv as $iapPE) {
		$sponsor = trim($iapPE['pe_sponsor']);
		$sponsor = str_replace('.', '', $sponsor);
		$sponsor = str_replace(',', '', $sponsor);
		$sponsor = str_replace("'", "", $sponsor);
		$sponsor = str_replace('-', '', $sponsor);
		if ($iapPE['pe_type'] == "P"
		or  $iapPE['pe_type'] == "E") {
			if ($iapPE['pe_type'] == "P") {
				$p = date("m/d/Y", strtotime($iapPE['pe_date']))." party ".$iapPE['pe_party_no']." for ".$sponsor;
			} elseif ($iapPE['pe_type'] == "E")  {
				$p = date("m/d/Y", strtotime($iapPE['pe_date']))." event at ".$sponsor;
			}
			$iapPEList = $iapPEList.$c.'{"label": "'.$p.'", "id": "'.strval($iapPE['pe_id']).'"}';
			$c = ",";
		}
	}
}

$iapCusts = iap_Get_Customer_List();
if ($iapCusts < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
$iapCustList = "";
$c = "";
if ($iapCusts != NULL) {
	foreach($iapCusts as $iapC) {
		$iapCNm = str_replace('"', '', $iapC['cust_name']);
		$iapCustList = $iapCustList.$c.'{"label": "'.$iapCNm.'", "id": "'.strval($iapC['cust_no']).'"}';
		$c = ",";
	}
	$iapSelEna = "";
}

if (empty($iapParty['pe_id'])) {
	$iapSales = NULL;
} else {
	$iapSales = IAP_Get_Sale_By_PE($iapParty['pe_id']);
	if ($iapSales <0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}
}
$iapParty['Sales'] = $iapSales;

$iapReadOnly = IAP_Format_Heading("Parties and Events");

$h = IAP_Do_Help(3, 356, 1); // level 3, page 356, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='pechoose'>
<p style='text-indent:50px; width:100%'>
<form name='peselform' action='?action=p356retA&origaction=initial' method='POST' onsubmit='return cNoSubmit();'>
<?php
	if (empty($iapPEList)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "There are no party/events on file. Click on ADD.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
/*
	echo "<span class=iapFormLabel style='padding-left: 40px;'>";
	if ($iapParty['pe_type'] == "E") {
		$p = "Event";
	} else {
		$p = "Party";
	}
	echo "<label for='pPEList' id='pPEListLbl'>Select a ".$p.": </label>";
*/
	echo "<label for='pPEList' id='pPEListLbl'>Select a party or event:&nbsp;&nbsp;&nbsp;</label>";
	echo "<input id='pPEList' size='50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 356, 1); //   <!-- level 1, page 356, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='".$_REQUEST['IAPUrl']."/MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='pGoClicked()'>";
	echo "<br><span class=iapError id='pError' style='display:none; padding-left:40px;'>Party/Event was not found. Retry or add it.</span>";

	if ($iapReadOnly != "readonly") {
		echo "<br><span class=iapFormLabel style='padding-left: 50px;'>";
		echo "<input class=iapButton type='button' name='peadd' id='peadd' value='Add A New Party/Event' onclick='peAddClicked()' />";
		echo "</span>";
	}

	echo "<br><span class=iapFormLabel style='padding-left: 40px;'>".$iapMsg."</span>";
?>
</form>
</p>
</div>

<div id='pedetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='text-indent:50px; width:100%'>

<form name='pedetform' action='?action=p356retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<?php
$d = "none";
if ($iapParty['pe_party_complete'] == "Y") {
	$d = "inline";
}
?>
<div id=pepartycomp style='display:<?php echo $d; ?>'>
<table>
<tr><td style="width: 13%;">&nbsp;</td>
<td style="width: 87%;"><span class=iapWarning>This Party Has Been Closed!</span>
</td></tr>
<tr><td style="width: 13%;">&nbsp;</td>
<td style="width: 87%;">&nbsp;
</td></tr>
</table>
</div>

<table>
<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Party/Event:</span></td>
<td style="width: 87%;">
	<input <?php echo $iapReadOnly; ?> type='radio' name='petype' id='petypeparty' value='P' tabindex="1"
<?php
	if ($iapParty['pe_type'] != "E") {
		echo " checked";
	}
?>
	 onchange='pesetpartyon(); autofocus'>Party
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='peparty' id='pepartylbl'><?php if ($iapParty['pe_type'] == "E") { echo ""; } else { echo "Party Number:"; } ?> </label>
	<input <?php echo $iapReadOnly; ?> type='<?php if ($iapParty['pe_type'] == "E") { echo "hidden"; } else { echo "text"; } ?>' tabindex="2" size="15" maxlength="15" name="peparty" id="peparty" value="<?php echo $iapParty['pe_party_no']; ?>" <?php if ($iapParty['pe_type'] != "E") echo "autofocus"; ?>>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input <?php echo $iapReadOnly; ?> type='radio' name='petype' id='petypeevent' value='E' tabindex="3"
<?php
	if ($iapParty['pe_type'] == "E") {
		echo " checked";
	}
?>
	 onchange='pesetpartyoff();'>Event
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<?php
if (!empty($iapParty['pe_date'])) {
	$dt = date("m/d/Y", strtotime($iapParty['pe_date']));
} else {
	$dt = "";
}
if (!empty($iapParty['pe_start_time'])) {
	$stm = date("h:i a",strtotime($iapParty['pe_start_time']));	
} else {
	$stm = "";
}
if (!empty($iapParty['pe_end_time'])) {
	$etm = date("h:i a",strtotime($iapParty['pe_end_time']));	
} else {
	$etm = "";
}
?>
<tr>
<td style="width: 13%;"><label for='pedate' class='iapFormLabel'>Date: </label></td>
<td style="width: 87%;">
	<input <?php echo $iapReadOnly; ?> tabindex='4' placeholder='mm/dd/yyyy' maxlength='15' size='15' name='pedate' id='pedate' value='<?php echo $dt; ?>'<?php if ($iapParty['pe_type'] == "E") echo " autofocus"; ?>>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='pestart' class='iapFormLabel'>Start Time: </label>
	<input <?php echo $iapReadOnly; ?> tabindex='5' placeholder='hh:mm pm' maxlength='10' size='10' name='pestart' id='pestart' value='<?php echo $stm; ?>'>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='peend' class='iapFormLabel'>End Time: </label>
	<input <?php echo $iapReadOnly; ?> tabindex='6' placeholder='hh:mm pm' maxlength='10' size='10' name='peend' id='peend' value='<?php echo $etm; ?>'>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><label for='pesponsor' class=iapFormLabel id='pesponsorlbl'><?php if ($iapParty['pe_type'] == "E") { echo "Sponsor:"; } else { echo "Hostess:"; } ?></label></td>
<td style="width: 87%;">

	<input <?php echo $iapReadOnly; ?> type=<?php if ($iapParty['pe_type'] == "E") { echo "text"; } else { echo "hidden"; } ?> tabindex="7" maxlength="50" size="50" name="pesponsor" id="pesponsor" value="<?php echo $iapParty['pe_sponsor']; ?>">

	<input <?php echo $iapReadOnly; ?> type=<?php if ($iapParty['pe_type'] == "E") { echo "hidden"; } else { echo "text"; } ?> tabindex="7" maxlength="100" size="50" name="pecustomer" id="pecustomer"  value="<?php echo $iapParty['pe_sponsor']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 2, $iapParty['pe_type']); ?> <!-- level 1, page 356, section 2 -->
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr><td colspan="2"><span class='iapFormLabel'>Address:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 4); ?> <!-- level 1, page 356, section 4  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Street:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="8" maxlength="50" size="50" name="pestreet" id="pestreet" value="<?php echo $iapParty['pe_street']; ?>">
		</td></tr>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>City, State, Zip:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="9" maxlength="35" size="35" name="pecity" id="pecity" value="<?php echo $iapParty['pe_city']; ?>">
			<input <?php echo $iapReadOnly; ?> tabindex="10" maxlength="2" size="2" name="pestate" id="pestate" value="<?php echo $iapParty['pe_state']; ?>">
			<input <?php echo $iapReadOnly; ?> tabindex="11" maxlength="10" size="10" name="pezip" id="pezip" value="<?php echo $iapParty['pe_zip']; ?>">
		</td></tr>

<?php
$a = trim($iapParty['pe_street'])."|".trim($iapParty['pe_city']).", ".trim($iapParty['pe_state'])." ".trim($iapParty['pe_zip']);
$a = str_replace("||", "|", $a);
if ($a == ",") {	// If only the comma added between city and state exists, get rid of it.
	$a = NULL;
	$d = "none";
}
if ($a !== NULL) {
	$a = str_replace(" ", "+", $a);
	$a = str_replace("|", ",", $a);
	$d = "inline";
}
?>
		<tr>
		<td style="width: 5%"></td>
		<td style='width: 14%;'></td>
		<td style='width: 81%;'
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style='display:<php echo $d; ?>' id='pemap'>
		<a id='pemapa' href='https://www.google.com/maps/place/<?php echo $a; ?>' target='_blank'>See On The Map.</a>
		</span>
		</td></tr>
	</table>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Website:</span></td>
<td style="width: 87%;">
	<input <?php echo $iapReadOnly; ?> tabindex="12" type="text" maxlength="100" size="50" name="peurl" id="peurl" value="<?php echo $iapParty['pe_website']; ?>">
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2"><span class='iapFormLabel'>Contacts:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 5); ?> <!-- level 1, page 356, section 5  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Name:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="13" maxlength="50" size="50" name="pec1name" id="pec1name" value="<?php echo $iapParty['pe_contact1']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Email:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="14" maxlength="100" size="50" name="pec1email" id="pec1email" value="<?php echo $iapParty['pe_c1email']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Phone:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="15" maxlength="15" size="15" name="pec1phone" id="pec1phone" value="<?php echo $iapParty['pe_c1phone']; ?>">
		</td></tr>

		<tr>
		<td colspan="3">&nbsp;</td>
		</tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Name:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="17" maxlength="50" size="50" name="pec2name" id="pec2name" value="<?php echo $iapParty['pe_contact2']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Email:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="18" maxlength="100" size="50" name="pec2email" id="pec2email" value="<?php echo $iapParty['pe_c2email']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Home Phone:</span></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> tabindex="19" maxlength="15" size="15" name="pec2phone" id="pec2phone" value="<?php echo $iapParty['pe_c2phone']; ?>">
		</td></tr>

</table></td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2"><span class='iapFormLabel'>Expenses:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 6); ?> <!-- level 1, page 356, section 6  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=pemiles class='iapFormLabel'>Mileage:</label></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" maxlength="10" size="10" tabindex="21" name="pemiles" id="pemiles" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_mileage'], 2, '.', ''); ?>>
		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=pespaceexp class='iapFormLabel'>Space Charge:</label></td>
		<td style="width: 81%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" maxlength="10" size="10" tabindex="22" name="pespaceexp" id="pespaceexp" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_space_charge'], 2, '.', ''); ?>>&nbsp;&nbsp;&nbsp;If Event

		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=peotherexp class='iapFormLabel'>Other Expenses:</label></td>
		<td style="width: 81%;">
		<input <?php echo $iapReadOnly; ?> style="text-align:right;" maxlength="10" size="10" tabindex="23" name="peotherexp" id="peotherexp" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_other_expenses'], 2, '.', ''); ?>>
		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=peexplexp class='iapFormLabel'>Explain Expenses:</label></td>
		<td style="width: 81%;">
			<textarea name='peexplexp' id='peexplexp' cols='50' rows='4' wrap='soft' tabindex="24" <?php echo $iapReadOnly; ?>><?php echo $iapParty['pe_exp_explained']; ?></textarea>
		</td></tr>
		</table>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2">
	<table>
	<tr>
	<td style="width: 19%;"><label for=peaddcal>Add To Calendar:</label></td>
	<td style="width: 81%;">
		<input <?php echo $iapReadOnly; ?> type="checkbox" tabindex="25" name="peaddcal" id="peaddcal" 
<?php
		if (!empty($iapParty['pe_event_id'])) {
			echo " checked";
		}
?>
		>
		&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 7); ?> <!-- level 1, page 356, section 7  -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel' id='peeventno'>
<?php
		if (!empty($iapParty['pe_event_id'])) {
			echo "To edit the event use Event Id ".strval($iapParty['pe_event_id']);
		}
?>
		</span></td></tr></table>

</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Notes:</span></td>
<td style="width: 87%;">
	<textarea name='pecomments' id='pecomments' tabindex="26" cols='40' rows='6' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapParty['pe_comment']; ?></textarea>
</td></tr>

<tr style='line-height:200%;'><td style="width: 13%;"> </td><td style="width: 87%;"></td></tr>
<tr style='line-height:200%;'><td style="width: 13%;"> </td><td style="width: 87%;">

<?php
	if ($iapReadOnly != "readonly") {
?>
	<tr>
	<td colspan='2'>
		<table>
		<tr>
		<td style="width: 19%;"></td>
		<td style="width: 81%;">
			<input class=iapButton tabindex='30' type='submit' name='pesubmit' id='pesubmit' value='Submit'>
<?php
		$dSave = "none";
		$dDel = "none";
		if ($iapParty['status'] != "NEW") {
			$dSave = "inline";
			if ($iapParty['sales'] == "N") {
				$dDel = "inline";
			}
		}
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input class=iapButton style='display:".$dSave.";' tabindex='31' type=submit name='pesavenew' id='pesavenew' value='Save As New Party/Event'>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input class=iapButton style='display:".$dDel.";' tabindex='32' type=submit name='pedelete' id='pedelete' value='Delete'>";

?>
		</td></tr></table>
	</td></tr>

<?php
	}
?>
</td></tr>
</table>

<?php

/*
sale types
E = event - need an event address (selectable or add)
F = facebook - can have party id 
P = party - need a party number (selectable or add)
O = other
I = individual direct - need a customer record 
W = website - can have party id - need number from online (SALE DOES NOT REDUCE INVENTORY)
X = exchange - another program
*/

	if ($iapParty['pe_sales_cnt'] == 0) {
		$iapSDsply = "none";
	} else {
		if ($iapParty['pe_type'] != "P"
		and $iapParty['pe_type'] != "E"
		and $iapParty['pe_type'] != "F") {
			$iapSDsply = "none";
		} else {
			$iapSDsply = "inline";
			if ($iapParty['pe_type'] == "P") {
				$peName = "Sales For This Party";
			} elseif ($iapParty['pe_type'] == "E") {
				$peName = "Sales For This Event";
			} elseif ($iapParty['pe_type'] == "F") {
				$peName = "Sales For This Facebook Party";
			}
			$iapSales = $iapParty['Sales'];
		}
	}
?>

<table style='width:100%;'>
<tr style='line-height:200%;'><td> </td></tr>
<tr style='line-height:200%;'><td> </td></tr>


<tr style='width:100%; display:<?php echo $iapSDsply; ?>' id='pesaletottitle'>
	<td style='line-height:200%;'><span id='pesaletotname' style='font-size:110%; text-decoration:underline;'>
	<?php echo $peName; ?>
	</span>&nbsp;&nbsp;&nbsp;<?php IAP_Do_Help(1, 356, 3); ?> <!-- level 1, page 356, section 3  -->
	</td>
</tr>


<tr><td style='line-height:200%;'>
		<table id=pesalestbl style='width:100%; display:<?php echo $iapSDsply; ?>;'>
		<tr style='width:100%;'>
		<td style='width:5%;'></td>
		<td style='width:20%; text-align:center;'><span style='text-decoration:underline;'>Customer</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Net Sales</span></td>
		<td style='width:9%; text-align:center;'><span style='text-decoration:underline;'>Shipping</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Tax</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Total</span></td>
		<td style='width:12%; text-align:center;'><span style='text-decoration:underline;'>Item Cost</span></td>
		<td style='width:9%; text-align:center;'><span style='text-decoration:underline;'>Profit</span></td>
<!--		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Items</span></td>  -->
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:5%;'></td>
		</tr>
	<?php
		if ($iapParty['pe_sales_cnt'] > 0) {
			foreach($iapSales as $iapS) {
	?>
			<tr>
			<td style='width:5%;'></td>
			<td style='width:20%;'><a href='?page_id=291&action=selected&sale=<?php echo $iapS['sale_id']; ?>'><?php echo $iapS['cust_name']; ?></a></td>
			<td style='width:10%; text-align:right;' id="pesnet"><a href='?page_id=291&action=selected&sale=<?php echo $iapS['sale_id']; ?>'><?php echo number_format($iapS['sale_net'], 2, '.', ',') ?></a></td>
			<td style='width:9%; text-align:right;' id="pesship"><?php echo number_format($iapS['sale_shipping'], 2, '.', ','); ?></td>
			<td style='width:10%; text-align:right;' id="pestax"><?php echo number_format($iapS['sale_sales_tax'], 2, '.', ','); ?></td>
			<td style='width:10%; text-align:right;' id="pestotal"><?php echo number_format($iapS['sale_total_amt'], 2, '.', ','); ?></td>
			<td style='width:12%; text-align:right;' id="pescost"><?php echo number_format($iapS['sale_item_cost'], 2, '.', ','); ?></td>
			<td style='width:9%; text-align:right;' id="pesprofit"><?php echo number_format($iapS['sale_profit'], 2, '.', ','); ?></td>
<!--			<td style='width:10%; text-align:right;' id="pesitems"><?php echo number_format($iapS['sale_items'], 0, '.', ','); ?></td> -->
			<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
			<td style='width:5%;'></td>
			</tr>
	<?php
			}
		}
	?>
		</table>
		<table id=pesalestot style='width:100%; display:<?php echo $iapSDsply; ?>;'>
		<tr>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:20%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:12%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'>Total All Sales</td>
		<td style='width:10%; text-align:right'><span id='petnet'> <?php echo number_format($iapParty['pe_net_sales'], 2, '.', ','); ?></span></td>
		<td style='width:9%; text-align:right'><span id='petship'> <?php echo number_format($iapParty['pe_shipping'], 2, '.', ','); ?></span></td>
		<td style='width:10%; text-align:right'><span id='pettax'> <?php echo number_format($iapParty['pe_sales_tax'], 2, '.', ','); ?></span></td>
		<td style='width:10%; text-align:right'><span id='pettotal'> <?php echo number_format($iapParty['pe_total_sales'], 2, '.', ','); ?></span></td>
		<td style='width:12%; text-align:right'><span id='petcost'> <?php echo number_format($iapParty['pe_cost_of_items'], 2, '.', ','); ?></span></td>
		<td style='width:9%; text-align:right'><span id='petprofit'> <?php echo number_format($iapParty['pe_profit'], 2, '.', ','); ?></span></td>
		<td style='width:10%;'></td>
		<td style='width:5%;'></td>
		</tr>
		</table>
	</td></tr>
</table>

<br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="PEUPDATETYPE" id="PEUPDATETYPE" value="">
<input type="hidden" name="PESALES" id="PESALES" value="">
<input type="hidden" name="PEID" id="PEID" value="">
<input type="hidden" name="PEHOSTESSID" id="PEHOSTESSID" value="">
<input type="hidden" name="PETAXREGION" id="PETAXREGION" value="">
<input type="hidden" name="PETAXRATE" id="PETAXRATE" value=0>
<input type="hidden" name="PEPARTYCOMP" id="PEPARTYCOMP" value="<?php echo $iapParty['pe_party_complete'];?>">


</form>
</p>
</div>

<script type="text/javascript">
<?php
require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSPrtyEvts.js");
//require_once($_REQUEST['IAPPath']."MyJS/JSPrtyEvts.min.js");
?>
var pAllPEs = [<?php echo $iapPEList; ?>];
var pCusts = [<?php echo $iapCustList; ?>];
</script>