<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>> Entering IAPDataConfig.<br>";
}
if ($MyDebug == "Y") { echo ">>> Entering IAPDataConfig.<br>"; }

// Setting up to connect to the data database, which is separate 
// from the Wordpress database on the production system.
// Localhost and production use the same parameters.

require_once("IAPSetVars.php");

if ($_REQUEST['debugme'] == "Y") {
	echo "... Data dbHost is ".IAPDATA_HOST."<br>";
	echo "... Data dbUser is ".IAPDATA_USER."<br>";
	echo "... Data dbPswd is ".IAPDATA_PASSWORD."<br>";
	echo "... Data dbName is ".IAPDATA_NAME."<br>";
}

if ($MyDebug == "Y") {
	$fdebug = fopen(ABSPATH."IAPDebug.txt", "a");
	fwrite($fdebug, "$DATAConfig ".date("Y/m/d H:i:s")." Host is ".IAPDATA_HOST.chr(10).chr(13));
	fclose($fdebug);
}

if(!function_exists('IAP_MySQL_Error')){
	function IAP_MySQL_Error($IAPErr) {
		global $wpDB;

		if ($_REQUEST['debugme'] == "Y") { echo ">>>In IAP_MySQL_Errors.<br>"; }

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
			if (substr($f, 0, 2) == "IAP"
			and  substr($IAPCaller['function'], 0, 2) == "IAP"
			and  $IAPCaller['function'] <> "IAP_MySQL_Error") {
				$IAPCalls = $IAPCalls."(".$i.") Function ".$IAPCaller['function']." Module ".$f." Line ".strval($IAPCaller['line'])."|";
				$i = $i +1;
			}
		}

		$IAPSql = "INSERT INTO IAP_errors (err_ip_address, err_datetime, err_client, err_retcode, err_retmsg, err_errcode, err_error, err_module, err_line, err_query, err_call_stack) VALUES ('".
				$_SERVER['REMOTE_ADDR'].	"', '".
				date("Y-m-d H:i:s").		"', '".
				get_current_user_id().		"', '".
				$IAPErr['retcode'].			"', '".
				$IAPErr['retmsg'].			"', '".
				$IAPErr['mserrno'].			"', '".
				$IAPErr['mserrmsg'].		"', '".
				$IAPErr['module'].			"', '".
				$IAPErr['line'].			"', '".
				$IAPQuery.					"', '".
				$IAPCalls.					"');";

		$IAPRet = $wpDB->query($IAPPSql);
		if ($wpDB->last_error != "") {
			$IAPDBHandle = $wpDB->DBh;
			echo "<span style='color:red;'>IAP INTERNAL ERROR: Error during error processing: Cannot write DB error log [FATAL]<br>";
			echo "   ".$wpDB->last_error." (".@mysql_errno($IAPDBHandle).")<br>";
			echo "   DB Error being processed was in pgm ".$IAPErr['module']." at ".$IAPErr['line']." for client ".get_current_user_id()."<br>";
			echo "   DBServices return was ".$IAPErr['retmsg']." (".$IAPErr['retcode'].") database error was ".$IAPErr['mserrmsg']." (".$IAPErr['mserrno']."<br>";
			echo "   Failing SQL was ".$IAPQuery."<br><br>";
			echo "Please notify suppoort of this error!</span><br><br>";
			exit;
		}

		echo "<span style='color:red;'>An error in ".$IAPErr['module']." at ".$IAPErr['line']." during database processing [FATAL]<br> Error is ".$IAPErr['retmsg']."(".$IAPErr['mserrmsg'].")<br>";
		echo "Please notify suppoort of this error!</span><br><br>";
		exit;
	}
}

?>