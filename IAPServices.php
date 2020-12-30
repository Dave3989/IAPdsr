<?php

// TODO If not company 2 (autotest) or 5 (Pam) force all inventory queries to go to company catalog NOT supplier

function IAP_Program_Init() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Program_Start with Page of ".$iapPage." and Update of ".$iapUpdate."<br />";
	}

	$_REQUEST['sec_use_application'] = "N";

	if ($_REQUEST['debugme'] == "Y") {
		echo "Checking if user logged in<br>";
	}

	if (is_user_logged_in ()) {
		global $current_user;
		get_currentuserinfo();
		$iapCurrentUser = (array) $current_user;
		$_REQUEST['IAPUID'] = $iapCurrentUser['ID'];
		$iapUserInfo = get_userdata($_REQUEST['IAPUID']);
		$iapUsername = $iapUserInfo->user_login;
		$iapUserRole = implode(', ', $iapUserInfo->roles);

		if ($_REQUEST['debugme'] == "Y") {
			echo "Getting CoUser<br>";
		}

		$iapCos = IAP_Get_CoUser();
		if ($iapCos < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    	require_once(ABSPATH."MyPages/IAPGenInfo.php");
	       	die;
	    }
	    if ($iapCos[0]['status'] == "NEW") {
			$iapCo['co_license_renewal'] = "0000-00-00";
			$_REQUEST['sec_use_application'] = "N";
		} elseif (count($iapCos) > 1) {
			$iapCo['co_license_renewal'] = "2099-12-31";
			$_REQUEST['sec_use_application'] = "Y";
		} else {
			$iapCo = $iapCos[0];
			$_REQUEST['sec_use_application'] = "Y";
		}
		$_REQUEST['CoId'] = $iapCo['cu_company'];
		$_REQUEST['Expires'] = $iapCo['co_license_renewal'];

		if ($_REQUEST['debugme'] == "Y") {
			echo "Use applic is ".$_REQUEST['sec_use_application']."<br>";
			echo "Co Id is ".$_REQUEST['CoId']."<br>";
			echo "Expires is ".$_REQUEST['Expires']."<br>";
		}

		if ($_REQUEST['sec_use_application'] == "Y"
		and $iapUserRole == "subscriber") {
			// Fetch the WP_User object of our user.
			$iapUser = new WP_User($_REQUEST['IAPUID']);

			// Replace the current role with 'contributor' role - this will be saved by wordpress for the future
			$iapUser->set_role('contributor');		
		}

		require_once(ABSPATH."/MyHelp/IAPHelp.php");
		IAP_Set_User_HelpLevel();

		$iapRet = IAP_Create_Savearea("IAPHome", "ItsAParty Initialization");
		if ($iapRet < 0) {
			echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create home savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
			return -2;
		}
	} else {
//		echo "<span class=iapError>You are not logged in or have timed out. Please select HOME and log in again.<br>";
	}

	define( 'WP_DEBUG', true );

	return;
}

function IAP_Program_Start($iapPage, $iapUpdate = "N", $iapInit = "N", $iapLicChk = "Y", $iapSupport = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Program_Start with Page of ".$iapPage." and Update of ".$iapUpdate."<br />";
    }

	if ($iapSupport == "Y") {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...Support flag is set so no action.<br />";
		}
		return(0);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...getting HP from savearea.<br />";
	}

	if ($iapInit == "N") {
		$iapHpData = IAP_Get_Savearea("IAPHP", $_REQUEST['IAPUID']);
		if ($iapHpData < 0) {
		    return(-1);
		}
		if ($iapHpData == NULL) {
		    return(-2);
		}	
		$_REQUEST['CoId'] = $iapHpData['CompanyId'];
		$_REQUEST['UserData'] = $iapHpData;
	} else {
		$iapCo = IAP_Get_Company($_REQUEST['CoId']);
		if ($iapCo < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve company. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			require_once(ABSPATH."MyPages/IAPGenInfo.php");
			 return(-3);
		}
		if ($iapCo['status'] == "NEW") {
			$iapCo['co_license_renewal'] = "0000-00-00";
		}
		$_REQUEST['Expires'] = $iapCo['co_license_renewal'];
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...comparing pages - last=".strval($iapSec['nav_last_page'])." and current is ".strval($iapPage).".<br />";
	}

	$iapRet = IAP_Get_Savearea("IAPNav", $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		return(-1);
	}

	$_REQUEST['nav_last_page'] = $iapRet;
	if ($iapPage == "NOCHK") {
		if (IAP_Update_Savearea("IAPNav", " ", $_REQUEST['IAPUID']) < 0) {
		     echo "span class=iapError>IAP INTERNAL ERROR Cannot rewrite IAPNAV to savearea [FATAL]<br />Please notify Support and provide this reference of ".basename(__FILE__)."/".__LINE__."</span><br />";
		     return(-1);
		 }
	} else {
		if (IAP_Update_Savearea("IAPNav", $iapPage, $_REQUEST['IAPUID']) < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Cannot rewrite IAPHP to savearea [FATAL]<br />Please notify Support and provide this reference of ".basename(__FILE__)."/".__LINE__."</span><br />";
			return(-1);
		}

//		echo "<br /><br /><br />nav_last_page = ".$_REQUEST['nav_last_page']." and processing ".$iapPage."<br /><br /><br />";

		if ($_REQUEST['nav_last_page'] <> $iapPage) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...last page not equal so removing savearea.<br />";
			}

			IAP_Remove_Savearea("IAP".$iapPage, $_REQUEST['IAPUID']);
		}
	}

	if ($_REQUEST['sec_use_application'] != "Y") {
		echo "<br /><br /><span class=iapError>You do not have authorization to use this application.</span><br /><br /><br />";
		require_once(ABSPATH."MyPages/IAPGenInfo.php");
		return(-3);
	}

/*
    if ($iapUpdate == "Y") {
        if (!($_REQUEST['sec_update'] == "Y")) {
            echo "<br /><br /><span class=iapError>You do not have authorization to use this application.</span><br /><br /><br />";
		    require_once(ABSPATH."MyPages/IAPGenInfo.php");
            return(-3);
        }
    }
*/

	if ($iapLicChk == "Y") {
		IAP_Check_License();
	}

	return(TRUE);
}


// --------------------------------------------- //
// --- Check Subscription Status --------------- //
function IAP_Check_License() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['UserData']['Mode'] == "expired") {
		echo "<span class=iapError>Error - Your current subscription has expired. You can view your data but make no changes.</span>";
		echo "&nbsp;&nbsp;&nbsp;<img src='MyHelp/LHCQuestionMark.png' class='tooltip' title='Your subscription can be renewed in the store.'>";
		echo "<br><br>";
	} elseif ($_REQUEST['UserData']['Mode'] == "warn") {
		echo "<span class=iapWarning>WARNING - Your current subscription will expire on ".date("m/d/Y", strtotime($_REQUEST['UserData']['Expires']))."</span>";
		echo "&nbsp;&nbsp;&nbsp;<img src='MyHelp/LHCQuestionMark.png' class='tooltip' title='Your subscription can be renewed in the store.'>";	
		echo "<br><br>";
	}
}


// ------------------------------------------------- //
// Non-numeric string offsets - e.g. $a['foo'] where $a is a string - now return false on isset() and true on empty(), and produce a E_WARNING if you try to use them. 
// ------------------------------------------------- //
// --- Format Heading ------------------------------ //
function IAP_Format_Heading($iapPageName) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if (isset($_REQUEST['UserData']['DisplayName'])) {
		$iapHeading = $_REQUEST['UserData']['DisplayName'];
		if (substr_compare($iapHeading, "s", -1, 1) == 0) {
			$iapHeading = $iapHeading."'&nbsp;&nbsp;".$iapPageName;
		} else {
			$iapHeading = $iapHeading."'s&nbsp;&nbsp;".$iapPageName;
		}
	} else {
		$iapHeading = $iapPageName;
	}
	echo "<table style='width:100%'><tr><td style='width:15%;'></td><td style='width:50%;'></td><td style='width:35%;'></td></tr>";
	echo "<tr><td colspan='2' class='iapFormHead'>".$iapHeading."</td><td style='width:35%;'></td></tr></table>";
	echo "<span class='iapNormal'>";

	$iapReadOnly = "";
	if ($_REQUEST['UserData']['Mode'] == "expired") {
		$iapReadOnly = "readonly";
	}
	return($iapReadOnly);
}


// ------------------------------------------------- //
// -- Passwords in pages to prevent post attacks  -- //
function IAP_Gen_Password() {
	global $post;
	$iapPage = $post->ID;
	$iapUser = $_REQUEST['IAPUID'];
	$iapComment = "iAPdSR2016";
	$iapPassword = password_hash($iapUser."(".$iapComment.")".$iapPage,PASSWORD_DEFAULT);
	$iapReturn = "<input type='hidden' value='".$iapPassword."' />";
	return;
}

function IAP_Verify_Password($iapPswd) {
	global $post;
	$iapPage = $post->ID;
	$iapUser = $_REQUEST['IAPUID'];
	$iapComment = "iAPdSR2016";
	$iapRet = password_verify($iapUser."(".$iapComment.")".$iapPage, $iapPswd);
	if ($iapRet === TRUE) {
		return($iapRet);
	}

	echo "<span class=IAPError>HACK ALERT</span>";
	echo "The page you were working on appears to have been hacked.";
	echo "I am notifying support and taking steps to determine how this happened.";
	echo "Unfortunately,";
	echo "&nbsp;&nbsp&nbsp;it is necessary to log you off to protect your data and our system.";
	echo "WE ARE EXTREMELY SORRY for the inconvience.";
	echo "<span style='pad_left: 30px;'>- LHC IAP Support";

	$iapSMSMsg = strval($iapUser)." Appears to have been hacked! On ".$iapPage."! Verification password does not match!";

// 6109844500@txt.att.net $iapSMSMsg
// yourwirelessnumber@mms.att.net (picture or video message)

	wp_logout();
	return;
}


// ------------------------------------------------- //
// -- This is the global require formatter        -- //
function IAP_Require($subdir, $mod) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In Require.<br>";
	}

	$file = $_REQUEST['IAPPath'].$subdir."/".$mod;

	if ($_REQUEST['debugme'] == "Y") {
		echo "Full file name ".$file."<br>";
	}

	if (!file_exists($file)) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot find file to load. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    die;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "File group ".filegroup($file)."<br>";
		echo "Group info <pre>".posix_getgrgid(filegroup($file))."</pre><br>";
		echo "File Owner ".fileowner($file)."<br>";
		echo "File group info <pre>".posix_getpwuid(fileowner($file))."</pre><br>"; 
		echo "File preissions ".substr(sprintf('%o', fileperms($file)), -4)."<br>";
		echo "File type & size ".filetype($file)."-".filesize($file)."<br>";
		echo "File status ".sprintf('%o', stat($file))."<br>";

		echo "Doing require_once<br>";	
	}	
	require_once($file);
	return;
}









// --------------------------------------------- //


// --- Calendar Table

function IAP_Get_All_Events($iapOrg = NULL) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_All_Events with org of ".$iapOrg.".<br />";
	}

	if ($iapOrg == NULL) {
		$o = $_REQUEST['CoId'];
	} else {
		$o = $iapOrg;
	}

	$iapPass['table'] = "iapcal";
	$iapPass['where']= "ev_account = '".$o."'";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	$iapRows = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returned <pre>";
		var_dump($iapRows);
		echo "</pre><br />";
	}

	return ($iapRows);	
}

function IAP_Get_Event_By_Id($iapEventId, $iapGetRepeats = "Y") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Event_By_Id with id of ".$iapEventId.".<br />";
	}

	$iapPass['table'] = "iapcal";
	if ($iapGetRepeats == "Y") {
		$iapPass['join'][] = array("type" => "l", "table" => "iapcrep", "on" => "iap_cal_repeating.cr_id = iap_calendar.ev_id ");
		$iapPass['join'][] = array("type" => "l", "table" => "iapcexc", "on" => "iap_cal_exceptions.ce_id ");
	}
	$iapPass['where']= "ev_account = '".$_REQUEST['CoId']."' AND ev_id = '".$iapEventId."'";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	if ($iapRet['numrows'] == 0){
		return(NULL);		
	}
	$iapRows = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returned <pre>";
		var_dump($iapRows);
		echo "</pre><br />";
	}

	$iapRow = (array) $iapRows[0];
	return ($iapRow);
	
}

function IAP_Cal_Reminder() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

/* From class-pogidude-ereminder.php
	/**
	 * Register 'ereminder' Custom Post Type
	 -/
	public function register_ereminder_post_type(){
		$labels = array(
			'name' => __('E-Reminders'),
			'singular_name' => __('E-Reminder'),
			'add_new' => _x('Create New', 'entry'),
			'add_new_item' => __('Create E-Reminder' ),
			'edit_item' => __( 'Edit E-Reminder' ),
			'new_item' => __( 'New E-Reminder' ),
			'view_item' => __( 'View E-Reminder' ),
			'search_items' => __( 'Search E-Reminders' ),
			'not_found' => __('No E-Reminders found' ),
			'not_found_in_trash' => __('No E-Reminders found in Trash' ),
			'parent_item_colon' => ''
		);
		
		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'publicly_queryable' => false,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(''),
			'description' => 'Stores reminders'
		);
		
		register_post_type( 'ereminder', $args );
	}

*/

/* From G:\xampplite\htdocs\LitehausConsulting\wp-content\plugins\email-reminder\includes\admin.php
	//submitted
	//if( !empty( $_POST ) && $_POST['checker'] === 'submit' ){
	
		//validate and sanitize content
		if( '' == $_POST['pd-reminder-content'] ){
			$error['content'] = 'Please enter a reminder.';
			$content = '';
		} else {
			$content = $_POST['pd-reminder-content'];
		}
		
		//create shortened version of content to use as title
		$title = substr( $content, 0, 30 );
		//add elipses to title if needed
		if( strlen( $content ) > 30 ){
			$title = $title . '...';
		}
		
		//validate email
		if( '' == $_POST['pd-reminder-email'] || !is_email( $_POST['pd-reminder-email'] ) ){
			$error['email'] = 'Please enter a valid e-mail address.';
			$email = '';
		} else {
			$email = $_POST['pd-reminder-email'];
		}
		
		//validate dates and specify default ones if needed
		$date_unformatted = empty( $_POST['pd-reminder-date'] )? $timenow : strtotime( $_POST['pd-reminder-date'] );
		$time_unformatted = empty( $_POST['pd-reminder-time'] ) ? $timenow + 60*60 : strtotime( $_POST['pd-reminder-time'] );
		
		//convert date and time into required format for database entry (YYYY-MM-DD HH:MM:SS)
		$date = date( 'Y-m-d', $date_unformatted );
		$time = date( 'H:i:s', $time_unformatted );
		$date_all = "{$date} {$time}";
		
		//determine gmt time for schedule
		$date_all_gmt = date( 'Y-m-d H:i:s', strtotime( $date_all ) + $timedelta );
		
		$reminder = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_type' => 'ereminder',
			'post_date' => $date_all,
			'post_date_gmt' => $date_all_gmt,
			'post_excerpt' => $email,
			'post_status' => 'draft'
		);
		
		if( empty( $error ) ){
			//create new post
			$insert_post_success = wp_insert_post( $reminder );
			
			if( empty( $insert_post_success ) ){
				$message = '<div class="error message">There was an error scheduling your reminder.</div>' . "\n";
			} else {
				$message = '<div class="updated message">Reminder created successfully!</div>' . "\n";
				//set to default
				$content = '';
				$email = '';
				$time = date( 'h:00 a', $timenow + 60*60 );
				$date = date( 'Y-m-d', $timenow );
			}
			
		} else {
			$message = '<div class="message error">' . "\n";
			foreach( $error as $eid => $e ){
				$message .= $e . "<br />\n";
			}
			$message .= '</div>' . "\n";
		}
		
	}

*/	
}


// --- Calendar Recurring Table

function IAP_Get_Repeating($iapId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Recurring with Id of ".$iapId.".<br />";
	}

	$iapPass['table'] = "iapcrep";
	$iapPass['where'] = "cr_id = ".$iapId;
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	
	$iapRepeats = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returning <br />";
		var_dump($iapRepeats);
	}

	return ($iapRepeats);
}


// --- Catalog Table

function IAP_Get_Catalog($iapItemNo, $iapPurDate = 0, $iapSource = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Catalog<br />";
	}

	if ($iapPurDate == 0) {
		$UseDate = date("Y-m-d", strtotime("now"));
	} else {
		$UseDate = date("Y-m-d", strtotime($iapPurDate));
	}

// 2018-08-11 - to allow selection by supplier id for IAP_SuppCatMaint program
/*
	$iapWhichCat = IAP_Which_Catalog($iapItemNo);
	if ($iapWhichCat == "CO") {
		$iapPass = IAP_Get_CO_Catalog($iapItemNo, $UseDate);
	} else {
		$iapPass = IAP_Get_SUPP_Catalog($iapItemNo, $UseDate);
	}
*/
	if ($iapsource == "CO"
	or ($iapsource == ""
	  and IAP_Which_Catalog($iapItemNo) == "CO")) {
		$iapPass = IAP_Get_CO_Catalog($iapItemNo, $UseDate);
	} else {
		$iapPass = IAP_Get_SUPP_Catalog($iapItemNo, $UseDate, $iapSource);
	}


	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCatRec = (array) $iapRet['data'][0];
	if ($iapWhichCat == "CO") {
		$iapCatRec['SOURCE'] = "COMPANY";
	} else {
		$iapCatRec['SOURCE'] = "SUPPLIER";
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRec);
		echo "</pre>";
	}

	return($iapCatRec);
}
// >>> 4/20/19 removed code_type from query
function IAP_Get_CO_Catalog($iapItemNo, $UseDate) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "ctlg";
	$iapPass['cols'] = "iap_catalog.*, iap_inventory.*, iap_prices.*, iap_codes.*";
	$iapPass['join'][] = array("type" => "l", "table"=> "inv", "on" => "iap_inventory.inv_company = iap_catalog.cat_company AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "prc", "on" => "iap_prices.prc_company = iap_catalog.cat_company AND UPPER(iap_prices.prc_item_code) = UPPER(iap_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "code", "on" => "iap_codes.code_company = iap_catalog.cat_company AND iap_codes.code_code = iap_prices.prc_cat_code");
	$iapPass['where'] = "iap_catalog.cat_company = ".$_REQUEST['CoId']." AND UPPER(iap_catalog.cat_item_code) = '".strtoupper($iapItemNo)."' AND iap_prices.prc_effective <= '".$UseDate."' AND iap_prices.prc_effective_until >= '".$UseDate."'";
	return($iapPass);
}
function IAP_Get_SUPP_Catalog($iapItemNo, $iapPurDate, $iapSource) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "supcat";
	$iapPass['cols'] = "iap_supplier_catalog.*, iap_inventory.*, iap_supplier_prices.*, iap_supplier_codes.*";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "inv_company = ".$_REQUEST['CoId'] ." AND UPPER(inv_item_code) = UPPER(cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "supprc", "on" => "iap_supplier_prices.prc_supplier_id = iap_supplier_catalog.cat_supplier_id AND UPPER(iap_supplier_prices.prc_item_code) = UPPER(iap_supplier_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "supcd", "on" => "iap_supplier_codes.code_supplier_id = iap_supplier_catalog.cat_supplier_id AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code");

// 2018/08/11 - Changed to allow supplier to be specified for IAP_SuppCatMaint
/*
	$iapSupps = $_REQUEST['UserData']['Suppliers'];
	$iapWhere = "";
	$o = "";
	foreach($iapSupps as $s) {
		$iapWhere = $iapWhere.$c."iap_supplier_catalog.cat_supplier_id = ".strval($s);
		$c = " OR ";
	}
*/

	if ($iapSource == "") {
		$iapSupps = $_REQUEST['UserData']['Suppliers'];
		$iapWhere = "";
		$o = "";
		foreach($iapSupps as $s) {
			$iapWhere = $iapWhere.$c."iap_supplier_catalog.cat_supplier_id = ".strval($s);
			$c = " OR ";
		}
	} else {
		$iapWhere = "iap_supplier_catalog.cat_supplier_id = ".strval($iapSource);
	}
	$iapPass['where'] = "UPPER(iap_supplier_catalog.cat_item_code) = '".strtoupper($iapItemNo)."' AND iap_supplier_prices.prc_effective <= '".$iapPurDate."' AND iap_supplier_prices.prc_effective_until >= '".$iapPurDate."' AND (".$iapWhere.")";
	return($iapPass);
}

function IAP_Get_Catalog_Only($iapItemNo, $iapSource = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Catalog_Only<br />";
	}

	if ($iapsource == "CO"
	or ($iapsource == ""
	  and IAP_Which_Catalog($iapItemNo) == "CO")) {
		$iapPass = IAP_Get_CO_Only($iapItemNo);
	} else {
		$iapPass = IAP_Get_SUPP_Only($iapItemNo, $iapSource);
	}

	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCatRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRec);
		echo "</pre>";
	}
	return($iapCatRec);
}
function IAP_Get_CO_Only($iapItemNo) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "ctlg";
	$iapPass['cols'] = "iap_catalog.*, iap_inventory.*";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "iap_inventory.inv_company = iap_catalog.cat_company AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_catalog.cat_item_code)");
	$iapPass['where'] = "cat_company = ".$_REQUEST['CoId']." AND UPPER(cat_item_code) = '".strtoupper($iapItemNo)."'";
	return($iapPass);
}
function IAP_Get_SUPP_Only($iapItemNo, $iapSource) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "supcat";
	$iapPass['cols'] = "iap_supplier_catalog.*, iap_inventory.*";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "iap_inventory.inv_company = ".$_REQUEST['CoId']." AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_supplier_catalog.cat_item_code)");
	if ($iapSource == "") {
		$iapSupps = $_REQUEST['UserData']['Suppliers'];
		$iapWhere = "";
		$o = "";
		foreach($iapSupps as $s) {
			$iapWhere = $iapWhere.$c."iap_supplier_catalog.cat_supplier_id = ".strval($s);
			$c = " OR ";
		}
	} else {
		$iapWhere = "iap_supplier_catalog.cat_supplier_id = ".strval($iapSource);
	}
	$iapPass['where'] = "UPPER(iap_supplier_catalog.cat_item_code) = '".strtoupper($iapItemNo)."' AND (".$iapWhere.")";
	return($iapPass);
}

function IAP_Get_Catalog_List($iapType = "C", $iapSet = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Catalog_List<br />";
	}

	if ($iapType != "C"
	and $iapType != "D") {
		return(-2);
	}

	if ($iapSet != "Y") {
		$iapSet = "N";
	}

	$iapSQL = 'SELECT "CO", "1", iap_catalog.cat_item_code, iap_catalog.cat_description FROM iap_catalog ';
	if ($iapType == "D") {
		$iapSQL = $iapSQL."USE INDEX (catindex_desc) ";
	}
	$iapSQL = $iapSQL.'WHERE iap_catalog.cat_company = '.$_REQUEST['CoId'];
	if ($iapSet == "Y") {
		$iapSQL = $iapSQL.' AND iap_catalog.cat_set = "Y"';
	}
	$iapSupps = $_REQUEST['UserData']['Suppliers'];
	if (!empty($iapSupps)) {
		foreach($iapSupps as $s) {
			$iapSQL = $iapSQL.' UNION '.
							   ' SELECT "'.strval($s).'" , "'.strval(count($iapSupps)).'" , iap_supplier_catalog.cat_item_code, iap_supplier_catalog.cat_description '.
							   ' FROM iap_supplier_catalog ';
			if ($iapType == "D") {
				$iapSQL = $iapSQL."USE INDEX (catindex_desc) ";
			}
			$iapSQL = $iapSQL.'WHERE iap_supplier_catalog.cat_supplier_id = '.strval($s);
			if ($iapSet == "Y") {
				$iapSQL = $iapSQL.' AND iap_supplier_catalog.cat_set = "Y"';
			}
		}
	}
	if ($iapType == "C") {
		$iapSQL = $iapSQL.' ORDER BY cat_item_code';
	} else {
		$iapSQL = $iapSQL.' ORDER BY cat_description';
	}

	$iapRet = IAPProcessMySQL("select", $iapSQL);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCatRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRecs);
		echo "</pre>";
	}
	return($iapCatRecs);
}

function IAP_Get_Catalog_Activity($iapItemNo) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Catalog_Activity<br />";
	}

	if (IAP_Which_Catalog($iapItemNo) == "CO") {
		$iapPass = IAP_Get_CO_Act($iapItemNo);
	} else {
		$iapPass = IAP_Get_SUPP_Act($iapItemNo);
	}
	$iapPass['order'] = "purdet_date, saledet_date";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCatRecs = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRecs);
		echo "</pre>";
	}
	return($iapCatRecs);
}
function IAP_Get_CO_Act($iapItemNo) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "ctlg";
	$iapPass['cols'] = "iap_catalog.*, iap_purdet.*, iap_saledtl.* ";
	$iapPass['join'][] = array("type" => "l", "table" => "pdtl", "on" => "purdet_company = cat_company AND UPPER(purdet_item_code) = UPPER(cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "sdtl", "on" => "saledet_company = cat_company AND UPPER(saledet_item_code) = UPPER(cat_item_code)");	
	$iapPass['where'] = "iap_catalog.cat_company = ".$_REQUEST['CoId']." AND UPPER(iap_catalog.cat_item_code) = '".strtoupper($iapItemNo);
	return($iapPass);
}
function IAP_Get_SUPP_Act($iapItemNo) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "supctlg";
	$iapPass['cols'] = "iap_supplier.catalog.*, iap_purdet.*, iap_saledtl.* ";
	$iapPass['join'][] = array("type" => "l", "table" => "pdtl", "on" => "purdet_company = ".$_REQUEST['CoId']." AND UPPER(purdet_item_code) = UPPER(iap_supplier.catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => "l", "table" => "sdtl", "on" => "saledet_company = ".$_REQUEST['CoId']." AND UPPER(saledet_item_code) = UPPER(iap_supplier.catalog.cat_item_code)");

	$iapSupps = $_REQUEST['UserData']['Suppliers'];
	$iapWhere = "";
	$o = "";
	foreach($iapSupps as $s) {
		$iapWhere = $iapWhere.$c."iap_supplier_catalog.cat_supplier_id = ".strval($s);
		$c = " OR ";
	}
	$iapPass['where'] = "UPPER(iap_supplier_catalog.cat_item_code) = '".strtoupper($iapItemNo)."' AND (".$iapWhere.")";
	return($iapPass);
}

function IAP_Get_Catalog_Codes($PurDate) {

	echo ">>>>>  IAP_Get_Catalog_Codes not changed for suppliers<br>";
	exit;


	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Catalog_Codes<br />";
	}

	if ($iapPurdate == 0) {
		$UseDate = date("Y-m-d", strtotime("now"));
	} else {
		$UseDate = date("Y-m-d", strtotime($iapPurDate));
	}

	if (IAP_Which_Catalog($iapItemNo) == "CO") {
		$iapPass = IAP_Get_CO_Codes($UseDate);
	} else {
		$iapPass = IAP_Get_SUPP_Codes($UseDate);
	}
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapCatRecs = (array) $iapRet['data'];
	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRecs);
		echo "</pre>";
	}
	return($iapCatRecs);
}
function IAP_Get_CO_Codes($UseDate) {
// >>> 4/20/19 Removed code_type from query

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "ctlg";
	$iapPass['cols'] = "iap_catalog.cat_item_code, iap_catalog.cat_description, iap_inventory.inv_on_hand, iap_prices.prc_cost, iap_prices.prc_units, iap_prices.prc_cost_unit, iap_prices.prc_price, iap_codes.code_value, iap_prices.prc_cat_code";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "iap_inventory.inv_company = iap_catalog.cat_company AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => " ", "table" => "prc", "on" => "iap_prices.prc_company = iap_catalog.cat_company AND UPPER(iap_prices.prc_item_code) = UPPER(iap_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => " ", "table" => "code", "on" => "iap_codes.code_company = iap_catalog.cat_company AND iap_codes.code_code = iap_prices.prc_cat_code");
	$iapPass['where'] = "iap_catalog.cat_company = ".$_REQUEST['CoId']." AND iap_codes.code_code = iap_prices.prc_cat_code AND iap_prices.prc_effective <= '".$UseDate."' AND iap_prices.prc_effective_until >= '".$UseDate."'";
	$iapPass['order'] = "iap_catalog.cat_item_code";
	return($iapPass);
}
function IAP_Get_SUPP_Codes($UseDate) {
// >>> 4/20/19 removed code_type from query (should not have been there!)

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "supctlg";
	$iapPass['cols'] = "iap_supplier_catalog.cat_item_code, iap_supplier_catalog.cat_description, iap_inventory.inv_on_hand, iap_supplier_prices.prc_cost, iap_supplier_prices.prc_units, iap_supplier_prices.prc_cost_unit, iap_supplier_prices.prc_price, iap_supplier_codes.code_value, iap_supplier_prices.prc_cat_code";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "iap_inventory.inv_company = iap_supplier_catalog.cat_company AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_supplier_catalog.cat_item_code)");
	$iapPass['join'][] = array("type" => " ", "table" => "prc", "on" => "iap_supplier_prices.prc_company = iap_supplier_catalog.cat_company AND UPPER(iap_supplier_prices.prc_item_code) = UPPER(iap_supplier_catalog.cat_item_code");
	$iapPass['join'][] = array("type" => " ", "table" => "code", "on" => "iap_supplier_codes.code_company = iap_supplier_catalog.cat_company AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code");
	$iapPass['where'] = "iap_supplier_catalog.cat_company = ".$_REQUEST['CoId']." AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code AND iap_supplier_prices.prc_effective <= '".$UseDate."' AND iap_supplier_prices.prc_effective_until >= '".$UseDate."'";
	$iapPass['order'] = "iap_supplier_catalog.cat_item_code";
	return($iapPass);
}

function IAP_Clear_Catalog() {

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Clear_Catalog<br />";
    }

	$iapRow = array("cat_company" => $_REQUEST['CoId']);
	if (IAP_Delete_PartKey($iapRow, "ctlg") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRow = array("prc_company" => $_REQUEST['CoId']);
	if (IAP_Delete_PartKey($iapRow, "prc") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

    return;
}

function IAP_Get_Lowlevel_Items($Supp = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Lowlevel_Items<br />";
	}

	$iapPass = IAP_Get_CO_LowLevel();
	$iapRet1 = (array) IAP_Get_Rows($iapPass);
	if ($iapRet1['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet1['numrows'] == 0) {
		$iapData1 = array();
	} else {
		$iapData1 = $iapRet1['data'];
	}

	$cos = IAP_Get_CoUser();
	if ($cos < 0) {
		echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot retrieve the companies for user [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		return;	
	}
	$iapData2 = array();
	if ($cos[0]['status'] != "NEW") {
		foreach($cos as $c) {
			$Supp = $c['cu_company'];
			$iapPass = IAP_Get_SUPP_LowLevel($Supp);
			$iapRet2 = (array) IAP_Get_Rows($iapPass);
			if ($iapRet2['retcode'] < 0) {
				return(-1);
			}
			if ($iapRet2['numrows'] > 0) {
				$iapData2 = array_merge($iapData2, $iapRet2['data']);
			}
		}
	}
	$iapLLItems = array_merge($iapData1, $iapData2);
	if (empty($iapLLItems)) {
		return(NULL);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapLLItems);
		echo "</pre>";
	}
	return($iapLLItems);
}
function IAP_Get_CO_LowLevel() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "ctlg";
	$iapPass['cols'] = "cat_item_code, cat_description, inv_on_hand, inv_min_onhand";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "inv_company = cat_company AND inv_item_code = cat_item_code");
	$iapPass['where'] = "cat_company=".$_REQUEST['CoId']." AND inv_min_onhand > 0 AND inv_on_hand <= inv_min_onhand";
	$iapPass['order'] = "cat_item_code";
	return($iapPass);
}
function IAP_Get_SUPP_LowLevel($Supp = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "supcat";
	$iapPass['cols'] = "cat_item_code, cat_description, inv_on_hand, inv_min_onhand";
	$iapPass['join'][] = array("type" => "l", "table" => "inv", "on" => "inv_company = ".$_REQUEST['CoId']." AND inv_item_code = cat_item_code");
	$iapWhere = "";
	if ($Supp == "") {
		$iapSupps = $_REQUEST['UserData']['Suppliers'];
		$o = "";
		foreach($iapSupps as $s) {
			$iapWhere = $iapWhere.$c."cat_supplier_id = ".strval($s);
			$c = " OR ";
		}
	} else {
		$iapWhere = "cat_supplier_id = ".strval($Supp);		
	}
	$iapPass['where'] = "inv_min_onhand > 0 AND inv_on_hand <= inv_min_onhand AND (".$iapWhere.")";
	$iapPass['order'] = "cat_item_code";
	return($iapPass);
}

function IAP_Which_Catalog($iapItem) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Which_Catalog.<br />";
	}

	$s = "SELECT 'SUPPLIER' as supp_source, iap_supplier_catalog.cat_item_code AS supp_cat FROM iap_supplier_catalog ".
		"WHERE  iap_supplier_catalog.cat_item_code = '".$iapItem."' ".
		"UNION ".
		"SELECT 'COMPANY' as co_source, iap_catalog.cat_item_code AS co_cat FROM iap_catalog ".
		"WHERE  iap_catalog.cat_company = ".$_REQUEST['CoId']." AND iap_catalog.cat_item_code = '".$iapItem."' ";
	$iapRet = IAPProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCatRec = (array) $iapRet['data'][0];

	if ($iapCatRec['supp_source'] == "SUPPLIER")  {
//		echo "----- Picking Supplier Item.<br>";
		return("SUPP");
	} else {
//		echo "----- Picking Company Item<br>";
		return("CO");
	}
}

// --- Catalog Sets Table

function IAP_Get_CSet($iapSupplier, $iapSetItem) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_CSet with supplier of ".$iapSupplier." and item of ".$iapSetItem.". <br />";
	}

	if ($iapSupplier == 0) {
		$iapPass['table'] = "cset";	
		$iapPass['where'] = "set_company = ".$_REQUEST['CoId'];
	} else {
		$iapPass['table'] = "supset";	
		$iapPass['where'] = "set_supplier = ".$iapSupplier;
	}
	$iapPass['where'] = $iapPass['where']." AND set_item_code = '".$iapSetItem."'";
	$iapPass['order'] = "set_part_item";
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapSetRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRecs);
		echo "</pre>";
	}
	return($iapSetRecs);
}


// --- Code Types Table
/*
function IAP_Get_Codes_Types() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Codes<br />";
	}

	$iapPass['table'] = "ctbl";
	$iapPass['where'] = "ct_company = '".$_REQUEST['CoId'];
	$iapPass['order'] = "ct_value";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCatRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCatRecs);
		echo "</pre>";
	}
	return($iapCatRecs);
}
*/

// --- Codes Table

// function IAP_Get_Codes($iapCType) {		04/18/19 removed code_type
function IAP_Get_Codes() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Codes<br />";
    }
/* 	04/18/19 removed code_type
	if (empty($iapCType)) {
		return(-3);
	}
*/
    $iapPass['table'] = "code";
    $iapPass['cols'] = "code_code, code_value";
//    $iapPass['where'] = "code_company = '".$_REQUEST['CoId']."' AND code_type = '".$iapCType."' AND code_code != code_type"; 	04/18/19 removed code_type
    $iapPass['where'] = "code_company = ".$_REQUEST['CoId'];
    $iapPass['order'] = "code_value";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}
		return(-1);
	} elseif ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapCatRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCatRecs);
        echo "</pre>";
    }
    return($iapCatRecs);
}

// function IAP_Get_Code_By_Code($iapCType, $iapCCode) { 4/20/19 removed code type
function IAP_Get_Code_By_Code($iapCCode) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Codes<br />";
    }

//	if (empty($iapCType)) {
//		return(-3);
//	}

    $iapPass['table'] = "code";
    $iapPass['cols'] = "code_code, code_value";
//    $iapPass['where'] = "code_company = '".$_REQUEST['CoId']."' AND code_type = '".$iapCType."' AND code_code = '".$iapCCode."'";
    $iapPass['where'] = "code_company = ".$_REQUEST['CoId']." AND code_code = '".$iapCCode."'";

    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCdRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCdRec);
        echo "</pre>";
    }
    return($iapCdRec);
}

// function IAP_Get_Code_By_Name($iapCType, $iapCName) { 4/20/19 removed type
function IAP_Get_Code_By_Name($iapCName) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Codes<br />";
    }

//	if (empty($iapCType)) {
//		return(-3);
//	}

    $iapPass['table'] = "code";
    $iapPass['cols'] = "code_code, code_value";
//    $iapPass['where'] = "code_company = '".$_REQUEST['CoId']."' AND code_type = '".$iapCType."' AND code_value = '".$iapCName."'";
    $iapPass['where'] = "code_company = ".$_REQUEST['CoId']." AND code_value = '".$iapCName."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCdRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCdRec);
        echo "</pre>";
    }
    return($iapCdRec);
}


// --- Company Table

function IAP_Get_Company($iapCoId = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Company<br />";
    }

	if ($iapCoId == "") {
		$iapCoId = $_REQUEST['CoId'];
	}

    $iapPass['table'] = "comp";
    $iapPass['where'] = "co_id = '".$iapCoId."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCoRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCoRec);
        echo "</pre>";
    }
    return($iapCoRec);
}


// --- Company Supplier Table

function IAP_Get_Co_Supplier($iapSuppId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Co_Supplier<br />";
    }

    $iapPass['table'] = "cosup";
    $iapPass['where'] = "cs_company = ".$_REQUEST['CoId']." AND cs_supplier = ".strval($iapSuppId);
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCSRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCSRec);
		echo "</pre>";
	}
	return($iapCSRec);
}

function IAP_Get_Co_Suppliers() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Co_Suppliers<br />";
	}

	$iapPass['table'] = "cosup";
	$iapPass['cols'] = "cs_supplier";
	$iapPass['where'] = "cs_company = ".$_REQUEST['CoId'];
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCSRecs = (array) $iapRet['data'];
	if ($iapCSRecs[0]['status'] == "NEW") {
		$iapCsRecs = array();
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapCSRecs);
		echo "</pre>";
	}
	return($iapCSRecs);
}

function IAP_Build_CoSupp_Array() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Build_Supplier_Array >";
	}
	$iapPass['table'] = "cosup";
	$iapPass['cols'] = "supp_id, supp_short_name, supp_name";
	$iapPass['join'][] = array("type" => " ", "table" => "supp", "on" => "supp_id = cs_supplier");
	$iapPass['order'] = "supp_name";
	$iapPass['where'] = "cs_company = ".$_REQUEST['CoId'];
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapArray = array();
//	$iapArray['CO'] = array("SId" => "CO", "SName"  => "Your Inventory", "SShortName" => "Your Inventory");
	$iapArray['CO'] = array("SId" => "0", "SName"  => "Your Inventory", "SShortName" => "Your Inventory");

	if ($iapRet['numrows'] == 0) {
		return($iapArray);
	}
	
	$iapSuppliers = (array) $iapRet['data'];
	foreach($iapSuppliers as $s) {
		$iapArray[$s['supp_id']] = array("SId" => $s['supp_id'], "SName" => $s['supp_name'], "SShortName" => $s['supp_short_name']);
	}
	return($iapArray);	
}


// --- Company User Table

function IAP_Get_CoUser($cu_id = 0) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_CoUser<br />";
    }

	if ($cu_id == 0) {
		$cu_id = $_REQUEST['IAPUID'];
	}

    $iapPass['table'] = "cous";
	$iapPass['cols'] = "cu_company, cu_user, co_name, co_license_renewal";
    $iapPass['join'][] = array('type' => " ", 'table' => "comp", 'on' => "co_id = cu_company");
    $iapPass['where'] = "cu_user = ".$cu_id." AND iap_company.co_id = iap_couser.cu_company";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCURecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCURecs);
        echo "</pre>";
    }
    return($iapCURecs);
}


// --- Customer Table

function IAP_Get_All_Customers($iapDate = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_All_Customers<br />";
    }

    $iapPass['table'] = "cust";
    $iapPass['where'] = "cust_company = ".$_REQUEST['CoId'];
    if ($iapDate == "Y") {
	    $iapPass['where'] = $iapPass['where']." AND cust_newsletter_add_date = '0000-00-00'";
	}
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCustRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCustRecs);
        echo "</pre>";
    }
    return($iapCustRecs);
	
}

function IAP_Get_Customer_By_No($iapCustNo) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Customer_By_No<br />";
    }

    $iapPass['table'] = "cust";
    $iapPass['where'] = "cust_company = ".$_REQUEST['CoId']. " AND cust_no = ".$iapCustNo;
    $iapRet = IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCustRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCustRec);
        echo "</pre>";
    }
    return($iapCustRec);
}

function IAP_Get_Customer_By_Name($iapCustName) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Customer_By_Name<br />";
    }

    $iapPass['table'] = "cust";
    $iapPass['where'] = "cust_company = ".$_REQUEST['CoId']. " AND LOWER(cust_name) = '".strtolower($iapCustName)."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapCustRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCustRec);
        echo "</pre>";
    }
    return($iapCustRec);
}

function IAP_Get_Customer_Followup() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
	    echo ">>>In IAP_Get_Customer_Followup<br />";
	}

	$iapPass['table'] = "cust";
	$iapPass['cols'] = "cust_no, cust_name, cust_email, cust_phone, cust_followup_consultant, cust_followup_party";
	$iapPass['where'] = "cust_company = ".$_REQUEST['CoId']. " AND (cust_followup_consultant = 'Y' or  cust_followup_party = 'Y')";
	$iapPass['order'] = "cust_name";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCustRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
	    echo "...___...returned <pre>";
	    var_dump($iapCustRecs);
	    echo "</pre>";
	}
	return($iapCustRecs);
}

function IAP_Get_Customer_List($iapHow = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Customer_List<br />";
    }

    $iapPass['table'] = "cust";
//	$iapPass['cols'] = "cust_no, cust_name, cust_email, cust_phone";
	$iapPass['cols'] = "cust_no, cust_name, cust_email, cust_phone, tax_region_name, tax_combined_rate";
	$iapPass['join'][] = array("type" => "l", "table" => "avtx", "on" => "tax_zip_code = LEFT(cust_zip, 5)");
    $iapPass['where'] = "cust_company='".$_REQUEST['CoId']. "'";
    if ($iapHow == "E") {
		$iapPass['where'] = $iapPass['where'] . " AND cust_email > ''";
    	$iapPass['index'] = "iapcustemailidx";
	    $iapPass['order'] = "cust_email";
	} elseif ($iapHow == "N") {
    	$iapPass['index'] = "iapcustnameidx";
	    $iapPass['order'] = "cust_name";
	} elseif ($iapHow == "P") {
		$iapPass['where'] = $iapPass['where'] . " AND cust_phone > ''";
    	$iapPass['index'] = "iapcustphoneidx";
	    $iapPass['order'] = "cust_phone";
	} else {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---Invalid 'How' indicator.<br />";
		}

		return(-2);		
	}
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCustRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCustRecs);
        echo "</pre>";
    }
    return($iapCustRecs);
}

function iap_Get_Customer_Cities() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Customer_Cities<br />";
    }

    $iapPass['table'] = "cust";
    $iapPass['cols'] = "DISTINCT cust_city";
    $iapPass['where'] = "cust_company='".$_REQUEST['CoId']. "'";
    $iapPass['order'] = "cust_city";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapCustRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapCustRecs);
        echo "</pre>";
    }
    return($iapCustRecs);
}

function IAP_Clear_Customers() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Clear_Customers<br />";
    }

	$iapRow = array("cust_company" => $_REQUEST['CoId']);
	if (IAP_Delete_PartKey($iapRow, "cust") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

    return;
}


// --- End Of Year Balances Table

function IAP_Get_EOY($iapEOYYear) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_EOY<br />";
    }

    $iapPass['table'] = "eoy";
    $iapPass['where'] = "eoy_company = ".$_REQUEST['CoId']." AND eoy_year = '".$iapEOYYear."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapEOYRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapEOYRec);
        echo "</pre>";
    }
    return($iapEOYRec);
}

function IAP_Calc_Eoy($iapEoy) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Calc_EOY<br />";
	}

    $iapPass['table'] = "plot";
    $iapPass['cols'] = "lot_Item_code, SUM(lot_cost * lot_count) AS lot_value, SUM(lot_count) AS lot_quantity";
    $iapPass['where'] = "lot_company=".$_REQUEST['CoId'];
    $iapRet = (array) IAP_Get_Rows($iapPass);



/* ---------------------------------------------------------------------------------------------------------------------------
	$iapSQL = "SELECT SUM(iap_inventory.inv_on_hand) as eoy_on_hand, SUM(iap_inventory.inv_on_hand * iap_prices.prc_cost) as eoy_value FROM iap_inventory ".
		"JOIN iap_prices on UPPER(iap_prices.prc_item_code) = UPPER(iap_inventory.inv_item_code) ".
		"WHERE iap_inventory.inv_company = ".$_REQUEST['CoId']." AND iap_prices.prc_effective > '".$iapEOY."-12-31'";
	$iapRet = IAPProcessMySQL("select", $iapSQL);
*/


	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapEOYRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		 echo "...___...returned <pre>";
		 var_dump($iapEOYRec);
		 echo "</pre>";
	}
	return($iapEOYRec);	
}


// --- Gift Certificates Table

function IAP_Get_GiftCert($iapGCId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_GiftCert<br />";
    }

    $iapPass['table'] = "gftcrt";
    $iapPass['where'] = "gc_company = ".$_REQUEST['CoId']." AND gc_id = '".$iapGiftCertId."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapGCRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapGCRec);
        echo "</pre>";
    }
    return($iapGCRec);
}

function IAP_Get_GiftCert_List() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_GiftCert_List<br />";
    }

    $iapPass['table'] = "gftcrt";
    $iapPass['cols'] = "gc_id, gc_date_issued, iap_customers.cust_name";
    $iapPass['join'][] = array("type" => " ", "table" => "cust", "on" => "cust_no = gc_for_custno");
    $iapPass['where'] = "gc_company='".$_REQUEST['CoId']. "'";
    $iapPass['order'] = "gc_date_issued Desc";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapGCRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapGCRecs);
        echo "</pre>";
    }
    return($iapGCRecs);
}


// --- Help Tables

function IAP_Get_Help_Level($iapPage = "") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Help_Level<br />";
    }

	if ($iapPage == "") {
		global $post;
		$iapPgId = $post->ID;
	} else {
		$iapPgId = $iapPage;
	}

	if (isset($_REQUEST['PageHelpLevels'][$iapPgId])) {
		$iapHL = $_REQUEST['PageHelpLevels'][$iapPgId];
		return($iapHL);
	}

	$iapPass['table'] = "iaphlvl";
	$iapPass['cols'] = "hl_level";
	$iapPass['where'] = "hl_client = ".$_REQUEST['IAPUID']." AND hl_page = ".$iapPgId;
	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		$iapHL = $_REQUEST['UserData']['HelpLevel'];
	} else {
		$hl = $iapRet['data'][0];
		$iapHL = $hl['hl_level'];
	}
	$_REQUEST['PageHelpLevels'] = array($iapPgId => $iapHL);
	return($iapHL);
}

function IAP_Get_Help_Text($iapPage, $iapSection, $iapLevel) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Help_Text<br />";
    }

    $iapPass['table'] = "iaphnar";
	$iapPass['col'] = "hn_text";
    $iapPass['where'] = "hn_page = ".$iapPage." AND hn_section = ".$iapSection." AND hn_level = ".$iapLevel;
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$ht = $iapRet['data'];
	$ot = "";
	foreach($ht as $t) {
		$ot = $ot.$t['hn_text'];
	}
	return($ot);
}


//  --- Inventory Table

function IAP_Get_Inventory($iapItem) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Inventory.<br />";
    }

    $iapPass['table'] = "inv";
    $iapPass['where'] = "inv_company = ".$_REQUEST['CoId']." AND inv_item_code = '".$iapItem."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapInvRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapInvRec);
        echo "</pre>";
    }
    return($iapInvRec);
}


// --- Journal Table

function IAP_Get_Journal($iapJrnlId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Journal<br />";
    }

    $iapPass['table'] = "jrnl";
    $iapPass['where'] = "jrnl_company = ".$_REQUEST['CoId']." AND jrnl_id = '".$iapJrnlId."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapJrnlRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapJrnlRec);
        echo "</pre>";
    }
    return($iapJrnlRec);
}

function IAP_Get_Journal_By_Detail($iapJrnlType, $iapJrnlKey) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Journal_By_Detail<br />";
    }

    $iapPass['table'] = "jrnl";
    $iapPass['where'] = "jrnl_company = ".$_REQUEST['CoId']." AND jrnl_type = '".$iapJrnlType."' AND jrnl_detail_key = '".$iapJrnlKey."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapJrnlRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapJrnlRec);
        echo "</pre>";
    }
    return($iapJrnlRec);
}

function IAP_Get_Journal_List() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Journal_List<br />";
    }

    $iapPass['table'] = "jrnl";
    $iapPass['cols'] = "jrnl_id, jrnl_date, jrnl_description";
    $iapPass['where'] = "jrnl_company='".$_REQUEST['CoId']. "'";
    $iapPass['order'] = "jrnl_date Desc";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapJrnlRecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapJrnlRecs);
        echo "</pre>";
    }
    return($iapJrnlRecs);
}


// --- Lot Table

function IAP_Get_Lot($iapItem, $iapDate, $iapCost) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Lot<br />";
    }

    $iapPass['table'] = "plot";
    $iapPass['where'] = "lot_company = ".$_REQUEST['CoId']." AND UPPER(lot_item_code) = '".strtoupper($iapItem)."' AND lot_date = '".date("Y-m-d", strtotime($iapDate))."' AND lot_cost = ".$iapCost;
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapLotRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapLotRec);
        echo "</pre>";
    }
    return($iapLotRec);
}

function IAP_Get_1st_Lot($iapItem) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_1st_Lot<br />";
    }

    $iapPass['table'] = "plot";
    $iapPass['where'] = "lot_company = ".$_REQUEST['CoId']." AND UPPER(lot_item_code) = '".strtoupper($iapItem)."'";
    $iapPass['order'] = "lot_date";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned no rows.<br />";
		}

		return(NULL);
	}

	$iapLotRec = (array) $iapRet['data'][0];	// this would be the first lot record (FIFO).

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapLotRec);
        echo "</pre>";
    }
    return($iapLotRec);
}


// --- Party Close Table

function IAP_Get_Party_Closes($iapPEID) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Party_Closes<br />";
    }

    $iapPass['table'] = "parcl";
    $iapPass['where'] = "pc_company = ".$_REQUEST['CoId']." AND pc_pe_id = ".strval($iapPEID);
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapPCRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapPERec);
        echo "</pre>";
    }
    return($iapPCRec);
}


// --- Party/Event Table

function IAP_Get_PartyEvent_By_Id($iapPEID) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_PartyEvent_By_Id<br />";
    }

    $iapPass['table'] = "parev";
    $iapPass['cols'] = "iap_party_events.*, iap_avalara_sales_tax.tax_combined_rate, iap_avalara_sales_tax.tax_region_name";
    $iapPass['join'][] = array("type" => "l", "table" => "avtx", "on" => "tax_zip_code = LEFT(pe_zip, 5)");
    $iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_id = ".strval($iapPEID);
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapPERec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapPERec);
        echo "</pre>";
    }
    return($iapPERec);
}

function IAP_Get_PartyEvent_By_Party($iapParty) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_PartyEvent_By_Party<br />";
    }

    $iapPass['table'] = "parev";
    $iapPass['cols'] = "iap_party_events.*, iap_avalara_sales_tax.tax_combined_rate, iap_avalara_sales_tax.tax_region_name";
    $iapPass['join'][] = array("type" => "l", "table" => "avtx", "on" => "tax_zip_code = LEFT(pe_zip, 5)");
    $iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_party_no = ".strval($iapParty);
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapPERec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapPERec);
        echo "</pre>";
    }
    return($iapPERec);
}

function IAP_Get_PartyEvent($iapPEDate) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_PartyEvent<br />";
    }

    $iapPass['table'] = "parev";
    $iapPass['cols'] = "iap_party_events.*, iap_avalara_sales_tax.tax_combined_rate, iap_avalara_sales_tax.tax_region_name";
    $iapPass['join'][] = array("type" => "l", "table" => "avtx", "on" => "tax_zip_code = LEFT(pe_zip, 5)");
    $iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_date = '".date("Y-m-d", strtotime($iapPEDate))."'";
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapPERecs = (array) $iapRet['data'];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapPERecs);
        echo "</pre>";
    }
    return($iapPERecs);
}

function IAP_Get_All_PEs() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
	    echo ">>>In IAP_Get_All_PEs<br />";
	}

	$iapPass['table'] = "parev";
	$iapPass['where'] = "pe_company = ".$_REQUEST['CoId'];
	$iapPass['order'] = "pe_date";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapPERecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
	    echo "...___...returned <pre>";
	    var_dump($iapPERecs);
	    echo "</pre>";
	}
	return($iapPERecs);
}

function IAP_Get_PE_List($peClosed = "Y", $pePartyOnly = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
	    echo ">>>In IAP_Get_PE_List<br />";
	}

	$iapPass['table'] = "parev";
	$iapPass['cols'] = "pe_id, pe_type, pe_party_no, pe_sponsor, pe_date, pe_street, pe_city, pe_party_complete, tax_region_name, tax_combined_rate";
    $iapPass['join'][] = array("type" => "l", "table" => "avtx", "on" => "tax_zip_code = LEFT(pe_zip, 5)");
//	$iap2Years = date("Y-m-d", strtotime("-2 years"));
//	$iapPass['where'] = "pe_company = ".$_REQUEST['CoId']." AND pe_date > '".$iap2Years."'";
	$iapPass['where'] = "pe_company = ".$_REQUEST['CoId'];

	if ($pePartyOnly == "Y") {
		$iapPass['where'] = $iapPass['where']." AND pe_type = 'P'";
	}

	if ($peClosed == "N") {
		$iapPass['where'] = $iapPass['where']." AND pe_party_complete = 'N'";
	}
	$iapPass['order'] = "pe_date DESC";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapPERecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
	    echo "...___...returned <pre>";
	    var_dump($iapPERecs);
	    echo "</pre>";
	}
	return($iapPERecs);
}


// --- Price Table

function IAP_Get_Price($iapItem, $iapSource="", $iapExact = "Y") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Price<br />";
	}

	if ($iapSource == "") {
		echo "<span class=iapError>A source must be supplied in order to get a price.</span><br>";
		return(-2);
	}
	if ($iapSource == "CO") {
		$iapPass['table'] = "prc";
		$iapWhere = " AND prc_company = ".$_REQUEST['CoId'];
	} else {
		if (!is_numeric($iapSource)) {
			echo "<span class=iapError>A valid source must be supplied in order to get a price.</span><br>";
			return(-2);			
		}
		$iapPass['table'] = "supprc";
		$iapWhere = " AND prc_supplier_id = ".$iapSource;
	}

	if ($iapExact == "Y") {
		$iapWhere = $iapWhere." AND prc_effective_until >= '".date("Y-m-d")."' ";
	}
	$iapPass['where'] = "UPPER(prc_item_code) = '".strtoupper($iapItem)."'".$iapWhere;

	$iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapExact == "Y") {
		$iapRec = (array) $iapRet['data'][0];
	} else {
		$iapRec = (array) $iapRet['data'];
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}

function IAP_Get_Price_History($iapItem, $iapEffective = NULL) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Price_History<br />";
	}

	if (empty($iapEffective)) {
		$iapEffective = date("Y-m-d");
	}

	if (IAP_Which_Catalog($iapItem) == "CO") {
		$s = IAP_Get_CO_Price_History($iapItem, $iapEffective);
	} else {
		$s = IAP_Get_SUPP_Price_History($iapItem, $iapEffective);
	}
	$iapRet = (array) IAPProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		$iapRet['data'] = (array) IAP_Set_Initial($IAPPass);
	}

	$iapRec = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}
function IAP_Get_CO_Price_History($iapItem, $iapEffective) {
// >>> 4/20/19 removed code_type from query

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$s = "SELECT iap_prices.*, iap_codes.*, ".
		"(SELECT code_value FROM iap_codes WHERE iap_codes.code_company = iap_prices.prc_company AND code_code = iap_prices.prc_prev_cat_code) AS prev_cat ".
	"FROM iap_prices ".
	"JOIN iap_codes ON iap_codes.code_company = iap_prices.prc_company AND iap_codes.code_code = iap_prices.prc_cat_code ".
	"WHERE iap_prices.prc_company = '".$_REQUEST['CoId']."' AND UPPER(prc_item_code) = '".strtoupper($iapItem)."' AND prc_effective >= '".$iapEffective."' ".
	"ORDER BY iap_prices.prc_effective Desc";
	return($s);
}
function IAP_Get_SUPP_Price_History($iapItem, $iapEffective) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$s = "SELECT iap_supplier_prices.*, iap_supplier_codes.*, ".
		"(SELECT code_value FROM iap_supplier_codes WHERE iap_supplier_codes.code_supplier_id = iap_supplier_prices.prc_supplier_id AND iap_supplier_codes.code_code = iap_supplier_prices.prc_prev_cat_code) AS prev_cat ".
	"FROM iap_supplier_prices ".
	"JOIN iap_supplier_codes ON iap_supplier_codes.code_supplier_id = iap_supplier_prices.prc_supplier_id AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code ".
	"WHERE UPPER(prc_item_code) = '".strtoupper($iapItem)."' AND prc_effective >= '".$iapEffective."' AND ";

	$iapSupps = $_REQUEST['UserData']['Suppliers'];
	$iapWhere = "";
	$o = "";
	foreach($iapSupps as $su) {
		$iapWhere = $iapWhere.$c."iap_supplier_prices.prc_supplier_id = ".strval($su);
		$c = " OR ";
	}

	$s = $s."(".$iapWhere.")".
		"ORDER BY iap_supplier_prices.prc_effective Desc";
	return($s);
}


// --- Profile Table

function IAP_Get_Profile() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Profile<br />";
    }

    $iapPass['table'] = "prof";
    $iapPass['where'] = "pro_no = ".$_REQUEST['IAPUID'];		// Don't need company since wordpress user id
    $iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRec = (array) $iapRet['data'][0];

    if ($_REQUEST['debugme'] == "Y") {
        echo "...___...returned <pre>";
        var_dump($iapRec);
        echo "</pre>";
    }
    return($iapRec);
}


// --- Purchases Table

function IAP_Get_Purchase($iapPurId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Purchase<br />";
	}

	$iapPass['table'] = "pur";
	$iapPass['where'] = "pur_company = ".$_REQUEST['CoId']." AND pur_id = ".$iapPurId;
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_Purchase_List() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Purchase_List<br />";
	}

	$LastYear = date("Y-m-d", strtotime("now - 1 year"));

	$iapPass['table'] = "pur";
	$iapPass['cols'] = "pur_id, pur_order, pur_date, pur_vendor";
	$iapPass['where'] = "pur_company = ".$_REQUEST['CoId']." AND pur_date > ".$LastYear;
	$iapPass['order'] = "pur_date desc, pur_id";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapPurRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapPurRecs);
		echo "</pre>";
	}
	return($iapPurRecs);
}


// --- Purchase Detail Table

function IAP_Get_PurDet($iapPurId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_PurDet<br />";
	}

	$iapPass['table'] = "pdtl";
	$iapPass['cols'] = "iap_purchase_detail.*, iap_catalog.cat_image_file AS CoImage, iap_supplier_catalog.cat_image_file AS SuppImage";
	$iapPass['join'][] = array("type" => "l", "table" => "ctlg", "on" => "iap_catalog.cat_company = purdet_company AND iap_catalog.cat_item_code = purdet_item");
	$iapPass['join'][] = array("type" => " ", "table" => "supcat", "on" => "iap_supplier_catalog.cat_supplier_id = purdet_item_source AND iap_supplier_catalog.cat_item_code = purdet_item");
	$iapPass['where'] = "purdet_company = ".$_REQUEST['CoId']." AND purdet_purid = ".$iapPurId;
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_PurDet_For_Item($iapItem, $iapFrom) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_PurDet_For_Item.<br />";
	}

	$iapPass['table'] = "pdtl";
	$iapPass['cols'] = "iap_purchase_detail.*, iap_purchases.pur_vendor, iap_purchases.pur_order";
	$iapPass['join'][] = array("type" => " ", "table" => "pur", "on" => "pur_company = purdet_company AND pur_id = purdet_purid");
	$iapPass['where'] = "purdet_company = ".$_REQUEST['CoId']." AND UPPER(purdet_item) = '".strtoupper($iapItem)."' AND purdet_date > '".$iapFrom."'";
	$iapPass['order'] = "purdet_date desc";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'];
	if ($iapRecs[0]['status'] == "NEW") {
		return(NULL);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}


// --- Sales Table

function IAP_Get_Sale($iapSaleId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Sale<br />";
	}

	$iapPass['table'] = "sale";
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_id = ".$iapSaleId;
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}

function IAP_Get_Sale_By_PE($iapPEId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Sale_By_PE<br />";
	}

	$iapPass['table'] = "sale";
	$iapPass['cols'] = "iap_sales.*, iap_customers.cust_no, iap_customers.cust_name";
	$iapPass['join'][] = array("type" => "l", "table" => "cust", "on" => "cust_company = sale_company AND cust_no = sale_customer ");
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_peid = ".$iapPEId." AND iap_customers.cust_no = iap_sales.sale_customer";
	$iapPass['order'] = "iap_customers.cust_name";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_Sale_By_Cust($iapCust) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Sale_By_Cust<br />";
	}

	$iapPass['table'] = "sale";
	$iapPass['join'][] = array("type" => " ", "table" => "parev", "on" => "pe_company = sale_company AND pe_id = sale_peid");
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_customer = ".$iapCust." AND pe_id = sale_peid";
	$iapPass['order'] = "sale_peid";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_Sale_By_PE_Cust($iapPEId, $iapCust) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Sale_By_PE_Cust<br />";
	}

	$iapPass['table'] = "sale";
	$iapPass['where'] = "sale_company = ".$_REQUEST['CoId']." AND sale_peid = ".$iapPEId." AND sale_customer = ".$iapCust;
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}

function IAP_Get_Sale_List() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Sale_List<br />";
	}

	$LastTS = strtotime("now - 1 year");
	$NextTS = strtotime("now + 1 month");
	$NowTS = strtotime("now");

	$LastMonth = date("Y-m-d", $LastTS);
	$NextMonth = date("Y-m-d", $NextTS);

	$iapPass['table'] = "sale";
	$iapPass['cols'] = "iap_sales.sale_id, iap_sales.sale_date, iap_sales.sale_total_amt, iap_customers.cust_name, iap_party_events.pe_type, iap_party_events.pe_party_no, iap_party_events.pe_sponsor";
	$iapPass['join'][] = array("type" => " ", "table" => "cust", "on" => "cust_company = sale_company AND cust_no = sale_customer");
	$iapPass['join'][] = array("type" => "l", "table" => "parev", "on" => "pe_company = sale_company AND pe_id = sale_peid");
	$iapPass['where'] = "iap_sales.sale_company = ".$_REQUEST['CoId']." AND iap_sales.sale_date > '".$LastMonth."' AND iap_sales.sale_date < '".$NextMonth."'";
	$iapPass['order'] = "iap_sales.sale_date DESC, iap_customers.cust_name";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapSaleRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapSaleRecs);
		echo "</pre>";
	}
	return($iapSaleRecs);
}


// --- Sales Detail Table

function IAP_Get_SaleDet($iapSaleId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_SaleDet<br />";
	}

	$iapPass['table'] = "sdtl";
	$iapPass['cols'] = "iap_sales_detail.*, 'CO' as CO_ID, iap_catalog.cat_description as CO_description, iap_catalog.cat_item_code as CO_item_code, 
	iap_supplier_catalog.cat_supplier_id AS SUPP_ID, iap_supplier_catalog.cat_description as SUPP_description, iap_supplier_catalog.cat_item_code as SUPP_item_code, 
	iap_sales.sale_peid, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no ";

	$iapPass['join'][] = array("type" => "l", "table" => "ctlg", "on" => "iap_catalog.cat_company = saledet_company AND UPPER(iap_catalog.cat_item_code) = UPPER(saledet_item_code) ");
	$iapPass['join'][] = array("type" => "l", "table" => "supcat", "on" => "iap_supplier_catalog.cat_supplier_id = saledet_item_source AND UPPER(iap_supplier_catalog.cat_item_code) = UPPER(saledet_item_code) ");
	$iapPass['join'][] = array("type" => " ", "table" => "sale", "on" => "sale_company = saledet_company AND sale_id = saledet_sid ");
	$iapPass['join'][] = array("type" => " ", "table" => "parev", "on" => "pe_company = saledet_company AND pe_id = sale_peid");
	$iapPass['where'] = "saledet_sid = ".$iapSaleId;
	$iapPass['order'] = "saledet_seq";

	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_SaleDet_For_Cust($iapCust) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_SaleDet_For_Cust<br />";
	}

	$iapPass['table'] = "sdtl";

	$iapPass['cols'] = "iap_sales_detail.*, iap_catalog.cat_description AS CO_description, iap_catalog.cat_item_code AS CO_item_code, iap_supplier_catalog.cat_description AS SUPP_description, iap_supplier_catalog.cat_item_code AS SUPP_item_code, iap_sales.sale_peid, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no";
	$iapPass['join'][] = array("type" => "l", "table" => "ctlg", "on" => "iap_catalog.cat_company = saledet_company AND UPPER(iap_catalog.cat_item_code) = UPPER(saledet_item_code) ");
	$iapPass['join'][] = array("type" => "l", "table" => "supcat", "on" => "iap_supplier_catalog.cat_supplier_id = saledet_item_source AND UPPER(iap_supplier_catalog.cat_item_code) = UPPER(saledet_item_code) ");
	$iapPass['join'][] = array("type" => " ", "table" => "sale", "on" => "sale_company = saledet_company AND sale_id = saledet_sid ");
	$iapPass['join'][] = array("type" => " ", "table" => "parev", "on" => "pe_company = saledet_company AND pe_id = sale_peid");
	$iapPass['where'] = " saledet_company = ".$_REQUEST['CoId']." AND sale_customer = ".$iapCust;

	$iapPass['order'] = "sale_date desc, sale_peid";

	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRecs = (array) $iapRet['data'];
	for($i=0; $i<count($iapRecs); $i++) {
		if (empty($iapRecs[$i]['CO_description'])) {
			$iapRecs[$i]['cat_description'] = $iapRecs[$i]['SUPP_description'];
			$iapRecs[$i]['cat_item_code'] = $iapRecs[$i]['SUPP_item_code'];
		} else {
			$iapRecs[$i]['cat_description'] = $iapRecs[$i]['CO_description'];
			$iapRecs[$i]['cat_item_code'] = $iapRecs[$i]['CO_item_code'];
		}
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_SaleDet_For_Item($iapItem, $iapFrom) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_SaleDet_For_Item.<br />";
	}

	if (IAP_Which_Catalog($iapItem) == "CO") {
		$iapPass = IAP_Get_CO_SaleDet4Items($iapItem, $iapFrom);
	} else {
		$iapPass = IAP_Get_SUPP_SaleDet4Items($iapItem, $iapFrom);
	}
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'];
	if ($iapRecs[0]['status'] == "NEW") {
		return(NULL);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

function IAP_Get_CO_SaleDet4Items($iapItem, $iapFrom) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "sdtl";
	$iapPass['cols'] = "iap_sales_detail.*, iap_catalog.cat_description, iap_catalog.cat_item_code, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_customers.cust_name";
	$iapPass['join'][] = array("type" => " ", "table" => "ctlg", "on" => "UPPER(cat_item_code) = UPPER(saledet_item_code)");
	$iapPass['join'][] = array("type" => " ", "table" => "sale", "on" => "sale_company = saledet_company AND sale_id = saledet_sid");
	$iapPass['join'][] = array("type" => " ", "table" => "parev", "on" => "pe_company = sale_company AND pe_id = sale_peid");
	$iapPass['join'][] = array("type" => " ", "table" => "cust", "on" => "cust_company = saledet_company AND cust_no = saledet_customer_no");
	$iapPass['where'] = "saledet_company = ".$_REQUEST['CoId']." AND UPPER(saledet_item_code) = '".strtoupper($iapItem)."' AND sale_date > '".$iapFrom."'";
	$iapPass['order'] = "sale_date desc";
	return($iapPass);
}
function IAP_Get_SUPP_SaleDet4Items($iapItem, $iapFrom) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$iapPass['table'] = "sdtl";
	$iapPass['cols'] = "iap_sales_detail.*, iap_supplier_catalog.cat_description, iap_supplier_catalog.cat_item_code, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_customers.cust_name";
	$iapPass['join'][] = array("type" => " ", "table" => "supcat", "on" => "cat_supplier_id = saledet_item_source ". 
																		   "AND UPPER(cat_item_code) = UPPER(saledet_item_code)");
	$iapPass['join'][] = array("type" => " ", "table" => "sale", "on" => "sale_company = saledet_company AND sale_id = saledet_sid");
	$iapPass['join'][] = array("type" => " ", "table" => "parev", "on" => "pe_company = sale_company AND pe_id = sale_peid");
	$iapPass['join'][] = array("type" => " ", "table" => "cust", "on" => "cust_company = saledet_company AND cust_no = saledet_customer_no");
	$iapPass['where'] = "saledet_company = ".$_REQUEST['CoId']." AND UPPER(saledet_item_code) = '".strtoupper($iapItem)."' AND sale_date > '".$iapFrom."'";
	$iapPass['order'] = "sale_date desc";
	return($iapPass);
}


// --- Sales Lot Table

function IAP_Get_SaleLot($iapSaleId, $iapSaleDetSeq) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_SaleLot<br />";
	}

	$iapPass['table'] = "sal";
	$iapPass['where'] = "salelot_company = ".$_REQUEST['CoId']." AND salelot_sid = ".$iapSaleId." AND salelot_sdseq = ".$iapSaleDetSeq;
	$iapPass['order'] = "salelot_seq";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}


// --- Suppliers Table

function IAP_Get_Supplier($iapSuppId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Supplier >";
	}

	$iapPass['table'] = "supp";
	$iapPass['where'] = "supp_id = ".strval($iapSuppId);
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$iapRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}

function IAP_Get_Supplier_List() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Supplier_List >";
	}

	$iapPass['table'] = "supp";
	$iapPass['cols'] = "supp_id, supp_name";
	$iapPass['order'] = "supp_name";
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRecs = (array) $iapRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRecs);
		echo "</pre>";
	}
	return($iapRecs);
}

// -- Avalara Tax Table

function IAP_Get_Tax($iapZipCode) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Tax >";
	}

	if (empty($iapZipCode)) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...--- No zip code passed.<br />";
		}

		return(NULL);		
	}

	$iapZip = trim(substr($iapZipCode, 0, 5));
	if (strlen($iapZip) != 5) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...--- Bad zip code passed.<br />";
		}

		return(NULL);		
		
	}

	$iapPass['table'] = "avtx";
	$iapPacc["cols"] = "tax_combined_rate, tax_region_name";
	$iapPass['where'] = "tax_zip_code = ".strval($iapZip);
	$iapRet = (array) IAP_Get_Rows($iapPass);

	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}

	$iapRec = (array) $iapRet['data'][0];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...___...returned <pre>";
		var_dump($iapRec);
		echo "</pre>";
	}
	return($iapRec);
}


// -- Miscellanous Functions

function IAP_Split_Name($iapName) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

/*
    $parser = new FullNameParser();
    $parser->parse_name("Mr Anthony R Von Fange III");

**Results:**

    Array (
    	[nickname] =>
        [salutation] => Mr.
        [fname] => Anthony
        [initials] => R
        [lname] => Von Fange
        [suffix] => III
    )
	
*/
	require_once("Ajax/PHP-Name-Parser-master/parser.php");
	$parser = new FullNameParser();
	$iapSplitName = $parser->parse_name($iapName);
	return($iapSplitName);
}

function IAP_Mailer($iapTo, $iapSubject, $iapMessage) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$headers = "From: ".$_REQUEST['sec_full_name']." <sender@LitehausConsulting.com>\r\n";
	if (!wp_mail($iapTo, $iapSubject, $iapMessage, $headers)) {
		return(FALSE);
	}
	return(TRUE);
}

// =====================================================================
//
// This is run when this module is loaded
//

require("IAPSetVars.php");	
// must be REQUIRE not REQUIRE_ONCE because this is called by wp_config but $_REQUEST is cleared after that mod calls SetVars

require_once("IAPDBServices.php");

?>