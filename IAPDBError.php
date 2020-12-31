<?php

function IAP_MySQL_Error($IAPErr) {
	global $wpdb;

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") { echo ">>>In IAP_MySQL_Errors.<br />"; }

	$IAPErr = (array) $IAPErr;
	$IAPErr = str_replace("'", "~", $IAPErr);
	if (!isset($IAPErr['sql'])) {
		$IAPQuery = NULL;
	} else {
		$IAPQuery = substr(str_replace("'", "~", $IAPErr['sql']), 0, -1);
	}

	$i = 1;
	$IAPCalls = "";
	foreach(debug_backtrace() as $IAPCaller) {
		$f = basename($IAPCaller['file']);
		if ((substr($f, 0, 3) == "iap"
		 or  substr($f, 0, 3) == "lhc")
		and  substr($IAPCaller['function'], 0, 3) == "IAP"
		and  $IAPCaller['function'] <> "IAP_MySQL_Error") {
			$IAPCalls = $IAPCalls."(".$i.") Function ".$IAPCaller['function']." Module ".$f." Line ".strval($IAPCaller['line'])."|";
			$i = $i +1;
		}
	}

//			get_current_user_id().		"', '".

	$IAPSql = "INSERT INTO iap_errors (err_datetime, err_ip_address, err_app, err_client, err_retcode, err_retmsg, err_errcode, err_error, err_module, err_line, err_query, err_call_stack) VALUES ('".
			date("Y-m-d H:i:s").		"', '".
			$_SERVER['REMOTE_ADDR'].	"', '".
			"IAP".						"', '".
			" ".		"', '".
			$IAPErr['retcode'].			"', '".
			$IAPErr['retmsg'].			"', '".
			$IAPErr['mserrno'].			"', '".
			$IAPErr['mserrmsg'].		"', '".
			$IAPErr['module'].			"', '".
			$IAPErr['line'].			"', '".
			$IAPQuery.					"', '".
			$IAPCalls.					"');";


	$MyPath = str_replace("\\", "/", dirname(__FILE__));
	$MyP = explode("/", $MyPath);
	for ($i = 0; $i < count($MyP); $i++) {
		$ln = array_pop($MyP);
		if (strpos(strtolower($ln), "litehausconsulting") !== FALSE
		or  strtolower($ln) == "lhc"
		or  strtolower($ln) == "lhcqa") {
			array_push($MyP, $ln);
			$LHCPath = implode("/", $MyP);
			$_REQUEST['LHCPath'] = $LHCPath."/";
			break;
		}
	}
	$AppPath = $LHCPath."/".strtoupper($lh)."/";
	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	$AppFile = "IAP".date("Ymd-Hi", strtotime("now"))."-".$IAPUserIP."-Error".$IAPErr['retcode'].".err";
	$AppErr = $AppPath.$AppFile;
	$fptr = fopen($AppErr,"w");
	fwrite($fptr, $IAPSql);
	fclose($fptr);

/* -- Can put to db when have time to figure out how to make it work.
TODO: Can put to db when have time to figure out how to make it work.
	$IAPRet = $wpdb->query($IAPPSql);
	if ($wpdb->last_error != "") {
		$IAPDBHandle = $wpdb->dbh;
		echo "<span style='color:red;'>IAP INTERNAL ERROR: Error during error processing: Cannot write db error log [FATAL]<br />";
		echo "   ".$wpdb->last_error." (".@mysql_errno($IAPDBHandle).")<br />";
		echo "   DB Error being processed was in pgm ".$IAPErr['module']." at ".$IAPErr['line']." for client ".get_current_user_id()."<br>";
		echo "   DBServices return was ".$IAPErr['retmsg']." (".$IAPErr['retcode'].") database error was ".$IAPErr['mserrmsg']." (".$IAPErr['mserrno']."<br>";
		echo "   Failing SQL was ".$IAPQuery."<br><br>";
		echo "Please notify support of this error!</span><br><br>";
		exit;
	}
*/
	echo "<span style='color:red;'>An error in ".$IAPErr['module'].
		 " at ".$IAPErr['line'].
		 " during database processing [FATAL]<br /> Error is ".$IAPErr['retmsg'].
		 "(".$IAPErr['mserrmsg'].")<br />";
	echo "Please notify support of this error!</span><br><br>";
	exit;
}




?>