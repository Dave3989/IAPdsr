<?php

	echo "<br><br>In IAPStoreRenew.";




/*

		} else {

// /////////////////////////
			require_once(ABSPATH."MyLicensing/IAPBilling.php");
			$iapRet = IAP_Do_Billing();
			if ($iapRet < 0) {
				$iapSuccess = FALSE;
			} else {
// /////////////////////////

				if ($iapCompany["co_license_renewal"] < date("Y-m-d")) {
					$iapCoCurrRenew = date("Y-m-d");
				} else {
					$iapCoCurrRenew = $iapCompany["co_license_renewal"];
				}
				$iapNewExpiration = date("Y-m-d",strtotime($iapCoCurrRenew + 1 year));
			}
			if($iapSuccess) {
				$iapCompany["co_license_renewal"] = $iapNewExpiration;
			}
}

*/



/* Old Store Page */
/*
<center>The Direct Sales Spreadsheet</center> 
<br>
The Direct Sales Spreadsheet provides a full accounting model for businesses selling product directly to consumers. Features provided are:
<ul>
<li>Tracking Inventory Purchases and Sales</li>
<li>Tracking Expenses</li>
<li>Tracking Contacts</li>
</ul>

<div class="ecwid ecwid-SingleProduct ecwid-Product ecwid-Product-46588853" itemscope itemtype="http://schema.org/Product" data-single-product-id="46588853">

   <div itemprop="image"></div>

   <div class="ecwid-title" itemprop="name"></div>

   <div itemtype="http://schema.org/Offer" itemscope itemprop="offers">
      <div class="ecwid-productBrowser-price ecwid-price" itemprop="price"></div>
   </div>

   <div itemprop="options"></div>

   <div itemprop="addtobag"></div>

</div>

<script type="text/javascript" src="http://app.ecwid.com/script.js?6278245" charset="utf-8"></script>
<script type="text/javascript">xSingleProduct()</script>
<br><hr><br>
<center>The Direct Sales Inventory Spreadsheet</center> 
<br>
The Direct Sales Inventory Spreadsheet provides features to track inventory for a business that sell items directly to consumers.

<div class="ecwid ecwid-SingleProduct ecwid-Product ecwid-Product-48370748" itemscope itemtype="http://schema.org/Product" data-single-product-id="48370748"><div itemprop="image"></div><div class="ecwid-title" itemprop="name"></div><div itemtype="http://schema.org/Offer" itemscope itemprop="offers"><div class="ecwid-productBrowser-price ecwid-price" itemprop="price"></div></div><div itemprop="addtobag"></div></div><script type="text/javascript" src="http://app.ecwid.com/script.js?6278245" charset="utf-8"></script><script type="text/javascript">xSingleProduct()</script>
*/



//TODO License Options
// An OS license can upgrade to any other but trial
// A Basic license can downgrade to OS, renew, or upgrade to Pro
// A Pro license can downgrade to OS or Basic, or renew
// Renewals can only be done in whatever period I set up before

//TODO Create Images for license options
// Hat with license type subscript on it

//TODO Get PayPal working 
//-----------------------------------------------------------------------

// Program start

/*
if ($_REQUEST['debugme'] == "Y") { echo ">>>In Page274.<br />"; }

if ($_REQUEST['debuginfo'] == "Y") { phpinfo(INFO_VARIABLES); }

require_once( ABSPATH . "LHC_Services.php" );
if (LHC_Program_Start("274", "Y", "N") < 0) {
	return;
};

if ($_REQUEST['debugme'] == "Y") { echo "...Getting LHCSaveArea.<br />"; }

if ($_REQUEST['action'] == "pg274begin") {
	LHC_Remove_Savearea("LHC274");
	$LHCLic = FALSE;
} else {
	$LHCLic = (array) LHC_Get_Savearea("LHC274");
}

if (!( $LHCLic )) {

	if ($_REQUEST['debugme'] == "Y") { echo "...LHCLic not set so build it.<br />"; }

	$LHCL = (array) LHC_Build_New_Row(array("table" => "lic"));
	$LHCLic = (array) $LHCL[0];
	$LHCRet = LHC_Create_Savearea("LHC274", $LHCLic);
	if ($LHCRet < 0) {
		echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: Cannot create savearea for license [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></span><br />";
		exit;
	}
	$LHCCurrentUser = wp_get_current_user();
	$LHCLic['lic_client'] = $LHCCurrentUser->ID;
}

if ($_REQUEST['action'] == "pg274begin") {
	require_once( ABSPATH."MyPages/Page274-Store-GetOrgApp.php" );
	Get_OrgApp($LHCLic);
	return;
} elseif ($_REQUEST['action'] == "pg274ret") {
	$LHCNoUpdate = "N";
	switch ($_REQUEST['lf']) {
		case "1":
			require_once( ABSPATH."MyPages/Page274-Store-GetOrgApp.php" );
			Get_OrgApp($LHCLic);
			break;
		case "2":
			require_once( ABSPATH."MyPages/Page274-Store-ChkLic.php" );
			$LHCRet = Check_Licenses($LHCLic);
			if ($LHCRet === TRUE) {
				switch ($LHCLic['LicenseStatus']) {
					case "OkLic":
						// show get added by acct admin
						$LHCNoUpdate = Show_Existing_License($LHCLic);
						break;
					case "Soon":
						// show renewal
						break;
					case "ExLic":
						// show expired but will renew and show account admin
						break;
					case "NoLic":
						// show new license
						break;
					case "Trial":
						break;
				}
				if ($LHCLic['LicenseStatus'] != "OkLic"
				or  $LHCNoUpdate == "M") {
					require_once( ABSPATH."MyPages/Page274-Store-GetProd.php" );
					$LHCNoUpdate = Get_Product($LHCLic);
				}
			}
			if ($LHCNoUpdate != "Y") {
				$LHCNoUpdate = "N";
			}
			break;
		case "3":
			require_once( ABSPATH."MyPages/Page274-Store-ValProd.php" );
			$LHCRet = Validate_Product($LHCLic);
			if ($LHCRet < 0) {
				return;
			}
			switch( $LHCRet ) {
				case 1:
					require_once( ABSPATH."MyPages/Page274-Store-GetProd.php" );
					Get_Product($LHCLic);
					break;
				case 2: // No Charge Licenses like Trials
					require_once( ABSPATH."MyPages/Page274-Store-Cr8Lic.php" );
					$LHCRet = LHC_Create_License($LHCLic, "NoCharge", "");
					if ($LHCRet === TRUE) {
						if ($LHCLic['LicenseStatus'] == "Trial") {
							echo "<br /><br /><span style='font_size:120%;'><strong>SUCCESS! Your trial has been created and you have been given security to access the application.</strong></span>";
							echo "<br /><br /><span style='font_size:120%;'><strong>It will be available in About Me or the log in box to the right.</strong></span>";
						} else {
							echo "<br /><br /><span style='font_size:120%;'><strong>SUCCESS! Your license has been created.</strong></span>";
							echo "<br /><br /><span style='font_size:120%;'><strong>Access to the application must be given the approporiate persons by the Account Administrator from the About Me page.</strong></span>";
						}
					}
					LHC_Remove_Savearea("LHC274");
					$LHCNoUpdate = "Y";
					break;
				case 3:
					require_once( ABSPATH."MyPages/Page274-Store-GenInv.php" );
					LHC_Generate_Invoice( $LHCLic );
			}
			break;
		case "4": // Pay By Check
			require_once( ABSPATH."MyPages/Page274-Store-Cr8Lic.php" );
			$LHCRet = LHC_Create_License($LHCLic, "Check", "");
			if ($LHCRet === TRUE) {
				echo "<br /><br /><span style='font_size:120%;'><strong>SUCCESS! Your license has been created.</strong></span>";
				if ($LHCLic['site_admin'] == "N") {
					echo "<br /><br /><span style='font_size:120%;'><strong>Your organization did not have an Account Administrator thus you have been set up as the Account Administrator.</strong></span>";
				}
				echo "<br /><br /><span style='font_size:120%;'><strong>The next step should be to set the security for the application.<br />";
				echo "The documention for setting up security can be found under the Documentation tab.</strong></span>";
			}
			LHC_Remove_Savearea("LHC274");
			$LHCNoUpdate = "Y";
			break;
		case "5": // Paypal interim processing - go back to interface
			require_once( ABSPATH."MyPages/Page274_PaypalInterface.php" );
			return;
		case "6": // Paypal good return - create license
			require_once( ABSPATH."MyPages/Page274-Store-Cr8Lic.php" );
			$LHCRet = LHC_Create_License($LHCLic, "PayPal", "");
			if ($LHCRet == TRUE) {
				echo "<br /><br /><span style='font_size:120%;'><strong>SUCCESS! Your license has been created.</span>";
				echo "<br /><br /><span style='font_size:120%;'><strong>It will be available in About Me or the log in box to the right.</span>";
			}
			$LHCNoUpdate = "Y";
			break;
		case "9": // Paypal canceled tx - error msg and return
			echo "<span style='color:red;'><strong>License processing cancelled in Paypal.<br />Select what you would like to do now from the menu above</strong></span><br />";
			LHC_Remove_Savearea("LHC274");
			return;
		default:
			echo "<span style='color:red;'><strong>LHC INTERNAL ERROR Invalid function of <".$_REQUEST['lf']."> sent [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></span><br />";
			return;
	}
} else {
	echo "<span style='color:red;'><strong>LHC INTERNAL ERROR Invalid action of <".$_REQUEST['action']."> [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></span><br />";
	return( -2 );
}

if ($LHCNoUpdate == "N") {
	$LHCRet = LHC_Update_Savearea("LHC274", $LHCLic);
	if ($LHCRet < 0) {
		echo "<span style='color:red;'><strong>LHC INTERNAL ERROR: Cannot update savearea for license [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></span><br />";
		exit;
	}
}

return( TRUE );
?>
