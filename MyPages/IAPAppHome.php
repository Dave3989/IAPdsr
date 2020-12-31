<?php

if ($_REQUEST['back2co'] == "Y") { return; }

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";


if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In IAP ";
    if (isset($_REQUEST['applinfo'])) {
        echo " with applinfo of ".$_REQUEST['applinfo'];
    }
    echo ".<br />";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debugme'] == "Y") echo "--- AppHome - User logged in.<br />";

$iapHomeValue = IAP_Get_Savearea("IAPHome");
if ($iapHomeValue < 0) {
	echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve home savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	return;
}
if ($iapHomeValue == "ItsAParty Disabled"
and $_REQUEST['UserId'] != 1) {
	echo "The System Administrator has temporarily disabled the It's A Party application for maintenance. Please check back later!";
	return;
}
if ($iapHomeValue == "ItsAParty Initialization") {
	$_REQUEST['mod'] = "IN";
	$in = "Y";
} else {
	$in = "N";
}

if ($_REQUEST['debugme'] == "Y") echo "--- AppHome - HomeValue gives in as ".$in.".<br />";

$iapRet = IAP_Remove_Savearea("IAPHome");
if ($iapRet < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot remove home savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}

if (IAP_Program_Start("NOCHK", "N", $in, "N") < 0) {
    return;
};

if ($_REQUEST['mod'] == "EX") {
	IAP_CleanUp_SaveArea();
	return;
}

if ($_REQUEST['mod'] == "IN") {

	$iapProfile = IAP_Get_Profile();
	if ($iapProfile < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	if ($iapProfile['status'] == "NEW") {
		global $current_user;
		get_currentuserinfo();
		$iapCurrentUser = (array) $current_user;
		$iapProfile['pro_no'] = $iapCurrentUser['ID'];
		$iapProfile['pro_first_name'] = get_user_meta($iapCurrentUser['ID'], 'first_name', true);
		$iapProfile['pro_last_name'] = get_user_meta($iapCurrentUser['ID'], 'last_name', true);
		$iapProfile['pro_name'] = $iapProfile['pro_first_name']." ".$iapProfile['pro_last_name'];
		$iapProfile['pro_nickname'] = get_user_meta($iapCurrentUser['ID'], 'nickname', true);
		$iapProfile['pro_street'] = "";
		$iapProfile['pro_city'] = "";
		$iapProfile['pro_state'] = "";
		$iapProfile['pro_zip'] = "";
		$iapProfile['pro_home_phone'] = "";
		$iapProfile['pro_cell_phone'] = "";
		$iapProfile['pro_email'] = "";
		$iapProfile['pro_google_calendar'] = "";
		$iapProfile['pro_help_level'] = 3;
		$iapProfile['pro_changed'] = date("Y-m-d", strtotime("now"));
		$iapProfile['pro_changed_by'] = $iapCurrentUser['ID'];
		$iapRet = IAP_Update_Data($iapProfile, "prof");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update your profile [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapProfile['pro_no'] = $iapRet;
	}

	$iapUserData['Id'] = $iapProfile['pro_no'];
	$iapUserData['FirstName'] = $iapProfile['pro_first_name'];
	$iapUserData['LastName'] = $iapProfile['pro_last_name'];
	$iapUserData['NickName'] = $iapProfile['pro_nickname'];

	if (empty($iapUserData['NickName'])) {
		$iapUserData['DisplayName'] = $iapProfile['pro_name'];
	} else {
		$iapUserData['DisplayName'] = $iapProfile['pro_nickname'];
	}

	$iapUserData['HelpLevel'] = $iapProfile['pro_help_level'];
	$iapUserData['GoogleCal'] = $iapProfile['pro_google_calendar'];
	$iapUserData['UseDatalist'] = $_REQUEST['IAPDL'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "--- AppHome - Current user is ".$iapUserData['Id']."-".$iapUserData['DisplayName'].".<br />";
	}


// Info from plugin php_browser_detection
//	$iapUserData['Browser'] = get_browser_name();
//	$iapUserData['Version'] = get_browser_version();
//	$iapUserData['BrowserInfo'] = php_browser_info();
/*
	if (is_chrome()
	or is_firefox()
	or is_opera(9)
	or is_ie(10)) {
		$iapUserData['dlistok'] = "Y";
	} else {
		$iapUserData['dlistok'] = "N";
	}
*/
	$iapUserData['dlistok'] = "N";




	$_REQUEST['UserData'] = $iapUserData;

// Delete old saveareas
	if ($_REQUEST['debugme'] == "Y") echo "..getting rid of any old savearea records for this IP address/application.<br />";

	IAP_Remove_Appl_Savearea("IAP");

// Put user id in savearea
	if ($_REQUEST['debugme'] == "Y") echo "..creating IAPUID savearea with client id of ".$iapCurrentUser['ID'].	"<br />";

	$iapRet = IAP_Create_Savearea("IAPUID", $iapUserData['Id'], 0);
	if ($iapRet < 0) {
	    echo "<span class=iaperror>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	    return;
	}
	$_REQUEST['IAPUID'] = $iapUserData['Id'];

// Build new home page savearea
	if ($_REQUEST['debugme'] == "Y") echo "..creating IAPPHP savearea.<br />";

	$IAPSec['nav_last_page'] = "HP";
	$iapRet = IAP_Create_Savearea("IAPHP", $iapUserData);
	if ($iapRet < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    return;
	}

	require_once(ABSPATH."MyPages/IAPCompany.php");
	$IAPCos = IAP_Get_CoUser();
	if ($IAPCos < 0) {
		echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot retrieve the companies for user [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		return;	
	}

	if ($IAPCos[0]['status'] == "NEW") {
		echo "It appears you do not have a valid license to use this app. Please contact Support if you feel you received this message in error.";
		require_once(ABSPATH."MyPages/IAPSupport.php");
		return;
	}

	$_REQUEST['iap1st'] = "N";
	if (count($IAPCos) == 1) {
		iapCoGet($IAPCos[0]['cu_company']);
	} else {
		iapCoSelect($IAPCos, 54);	// tell CoSelect we are coming from AppHome.
	}

	if ($_REQUEST['CoSetUp'] == "0000-00-00") {
		$_REQUEST['co_id'] = "NEW";
		$_REQUEST['iap1st'] = "Y";
		$iapUserData['1stTime'] = $_REQUEST['iap1st'];
		$iapUserData['HelpLevel'] = 3;
		$iapRet = IAP_Create_Savearea("IAPHP", $iapUserData);
		if ($iapRet < 0) {
		    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		    return;
		}
		echo IAP_Do_Help(3, 54, 1); // level 3, page 54, section 1 - first time after license acquisition
		iapCoEdit();
		return;
	}
}

IAP_Finish_Home();
return;

function IAP_Finish_Home() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapUserData = IAP_Get_Savearea("IAPHP");
	if ($iapUserData < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    return;
	}

	$_REQUEST['iap1st'] = $iapUserData['1stTime'];

	if ($_REQUEST['mod'] == "IN") {
		$iapUserData['CompanyId'] = $_REQUEST['CoId'];
		$iapUserData['CompanyName'] = $_REQUEST['CoName'];
		$iapUserData['DisplayName'] = $_REQUEST['CoName'];
		$iapUserData['Suppliers'] = $_REQUEST['Suppliers'];
		$iapUserData['AddBirthdays'] = $_REQUEST['AddBdays'];

		if ($_REQUEST['debugme'] == "Y") echo "...Company id is ".$iapUserData['CompanyId']."-".$iapUserData['CompanyName'].".<br />";

		$iapUserData['Expires'] = $_REQUEST['Expires'];
		$iapToday = strtotime("now");
		$iapWarnDate = strtotime("+15 days");
		$iapExpDate = strtotime($iapUserData['Expires']);
		if ($iapExpDate < $iapToday) {
			$iapUserData['Mode'] = "expired";
		} elseif ($iapExpDate < $iapWarnDate) {
			$iapUserData['Mode'] = "warn";
		} else {
			$iapUserData['Mode'] = "ok";
		}

		if ($_REQUEST['debugme'] == "Y") echo "...License mode is ".$iapUserData['Mode'].".<br />";

		$_REQUEST['UserData'] = $iapUserData;

		$iapTax = IAP_Get_Tax($_REQUEST['CoZip']);
		if ($iapTax < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve sales tax information. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			exit;
		}
		$iapUserData['TaxRegion'] = ucwords(trim($iapTax['tax_region_name']));
		$iapUserData['TaxRate'] = $iapTax['tax_combined_rate'];

/*
		$iapRet = IAP_Update_Savearea("IAPHP", $iapUserData);
		if ($iapRet < 0) {
		    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		    return;
		}
*/

	} else {

		if (!(empty($_REQUEST['CoId']))) {
			$iapEOY = date("Y", strtotime("now - 1 year"));
			$iapEOYBalances = IAP_Get_EOY($iapEOY);
			if ($iapEOYBalances < 0) {
			    echo "<font color='red'><strong>IAP INTERNAL ERROR: Unable to retreive EOY Balances due to database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
			    return;
			}
			if ($iapEOYBalances['status'] == "NEW") {
				$iapEOYBalances['eoy_company'] = $_REQUEST['CoId'];
				$iapEOYBalances['eoy_year'] = $iapEOY;
				$iapBals = IAP_Calc_Eoy($iapEOY);
				if ($iapBals < 0) {
				    echo "<font color='red'><strong>IAP INTERNAL ERROR: Unable to calculate EOY Balances due to database error. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
				    return;
				}
				$iapEOYBalances['eoy_on_hand'] = $iapBals['eoy_value'];
				$iapEOYBalances['eoy_created'] = date("Y-m-d");
				$iapEOYBalances['eoy_created_by'] = $_REQUEST['IAPUID'];
				$iapRet = IAP_Update_Data($iapEOYBalances, "eoy");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR writing end of year balances. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}
		}

/*
		$iapRet = IAP_Update_Savearea("IAPHP", $iapUserData);
		if ($iapRet < 0) {
		    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		    return;
		}
*/

		switch($_REQUEST['mod']) {
			case "BI":
//				require_once(ABSPATH."MyPages/IAPCompany.php");
//				iapCoEdit();
				break;
			case "CS":
				require(ABSPATH."MyPages/IAPCompany.php");
				iapCoSelect();
				break;
			case "CM":
				require(ABSPATH."MyPages/IAPCustomerMaint.php");
				iapCoSelect();
				break;
			case "UC":
				require(ABSPATH."MyPages/IAPUploadCustomers.php");
				IAP_Upld_Cust(1);
				break;
			case "UI":
				require(ABSPATH."MyPages/IAPUploadCatalog.php");
				iapCoSelect();
				break;
			case "HP":
				return;
		}
	}

	$iapRet = IAP_Update_Savearea("IAPHP", $iapUserData);
	if ($iapRet < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    return;
	}

//	if ($_REQUEST['iap1st'] == "Y") {
//		return;
//	}

// if (screen_width > ??) {
	require_once("MyViews/IAPAppHomeViewPC.php");
// }

	IAP_Check_License();
	echo "<table><tr><td width='10%'></td><td width='60%'></td><td width='30%'><br></td></tr>";
	echo "<tr><td colspan='2' class='iapFormHead'>".$_REQUEST['UserData']['DisplayName']."</td><td></td></tr></table>";
	AppHomeInit();

	$IAPStockLvl = IAP_Get_Lowlevel_Items();
	if ($IAPStockLvl != "") {
		echo ">>> Home going to AppHomeStkLvl<br>";
		AppHomeStockLevel($IAPStockLvl);		
		echo ">>> Home back from AppHomeStkLve<br>";
	}

	$_GET['start'] = date("Y-m-d");
	$_GET['end'] = date("Y-m-d", strtotime("+1 month"));
	$_GET['LHCHP'] = "Y";

$ev = $_REQUEST['IAPPath']."Ajax/IAPCalendar/IAPGetEvCommon.php";

	require_once($_REQUEST['IAPPath']."Ajax/IAPCalendar/IAPGetEvCommon.php");
	$_GET['LHCA'] = 0;
	$IAPSE = "";
	$IAPSysEvents = FCGetMain();
	if (!empty($IAPSysEvents)) {
		foreach($IAPSysEvents as $se) {
			$sd = date("l F d, Y ", strtotime(substr($se['start'], 0, 10)));
			$ed = date("l F d, Y ", strtotime(substr($se['end'], 0, 10)));
			if ($se['allday'] != "Y") {
				$st = date(" h:i a", strtotime(substr($se['start'], 11, 5)));
				$et = date(" h:i a", strtotime(substr($se['end'], 11, 5)));
			}
			$IAPSE = $IAPSE.
					 "<tr><td style='width:5%'></td><td style='width:95%;'><a href='javascript:void(0)' "
					 ."onclick='appHomeOpenEvent2("
					 .$se['id']
					 ."); return false;'> <span class=iapFormInput>"
					 .$sd;

			if ($se['allday']) {
				if ($ed != $sd) {
					$IAPSE = $IAPSE." thru ".$ed;
				}
				$IAPSE = $IAPSE." All Day";
			} else {
				$IAPSE = $IAPSE." ".$st;
				if ($ed != $sd) {
					$IAPSE = $IAPSE." thru ".$ed;
				}
				$IAPSE = $IAPSE." ".$et;
			}
			$IAPSE = $IAPSE." - ".$se['title']."</span></a></td></tr>";
		}
	}

	$_GET['LHCA'] = $_REQUEST['CoId'];
	$IAPME = "";
	$IAPMyEvents = FCGetMain();
	if (!empty($IAPMyEvents)) {
		foreach($IAPMyEvents as $me) {
			$sd = date("l F d, Y ", strtotime(substr($me['start'], 0, 10)));
			$ed = date("l F d, Y ", strtotime(substr($me['end'], 0, 10)));
			if ($se['allday'] != "Y") {
				$st = date(" h:i a", strtotime(substr($me['start'], 11, 5)));
				$et = date(" h:i a", strtotime(substr($me['end'], 11, 5)));
			}
			$IAPME = $IAPME.
					 "<tr><td style='width:5%'></td><td style='width:95%;'><a href='javascript:void(0)' "
					 ."onclick='appHomeOpenEvent2("
					 .$me['id'].
					 "); return false;'> <span class=iapFormInput>"
					 .$sd;

			if ($me['allDay']) {
				if ($ed != $sd) {
					$IAPME = $IAPME." thru ".$ed;
				}
				$IAPME = $IAPME." All Day";
			} else {
				$IAPME = $IAPME." ".$st;
				if ($ed != $sd) {
					$IAPME = $IAPME." thru ".$ed." ".$et;
				} else {
					$IAPME = $IAPME." to ".$et;
				}
			}
			$IAPME = " ".$IAPME." - ".$me['title']."</span></a></td></tr>";
		}
	}
	AppHomeEvents($IAPSE, $IAPME);

	$iapFollowups = IAP_Get_Customer_Followup();
	if ($iapFollowups < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    die;
	}
	AppHomeFollowUps($iapFollowups);

	$s = "SELECT cat_item_code FROM iap_catalog WHERE cat_company = ".$_REQUEST['CoId']." LIMIT 0,1";
	$iapRet = iapProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		$iapCatInit = "N";
	} elseif ($iapRet['numrows'] == 0){
		$iapCatInit = "N";
	} else {
		$iapCatInit = "Y";
	}
	$s = "SELECT cust_no FROM iap_customers WHERE cust_company = ".$_REQUEST['CoId']." LIMIT 0,1";
	$iapRet = iapProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		$iapCustInit = "N";
	} elseif ($iapRet['numrows'] == 0){
		$iapCustInit = "N";
	} else {
		$iapCustInit = "Y";
	}
	AppHomeFinal($iapCatInit, $iapCustInit);

	AppHomeMenu();

	IAP_Remove_Savearea("IAP079BI"); // IAPCompany
	IAP_Remove_Savearea("IAP154JR"); // IAPJournal
	IAP_Remove_Savearea("IAP208PU"); // IAPPurchases
	IAP_Remove_Savearea("IAP291SA"); // IAPSales
	IAP_Remove_Savearea("IAP356PE"); // IAPPartyEvent
	IAP_Remove_Savearea("IAP168AE"); // IAPAddEvent
	IAP_Remove_Savearea("IAP173EE"); // IAPEditEvent
	IAP_Remove_Savearea("IAP141CM"); // IAPCatalogMaint
	IAP_Remove_Savearea("IAP134CM"); // IAPCustomerMaint
	IAP_Remove_Savearea("IAP125UI"); // IAPUploadCatalog
	IAP_Remove_Savearea("IAP103UC"); // IAPUploadCustomers
	IAP_Remove_Savearea("IAP274ST"); // IAPStore
}

?>
