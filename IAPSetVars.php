<?php

//
// This module sets the database and $_REQUEST variables for the domain the app is running in.
//

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($MyDebug == "Y") { echo ">>> Entering IAPSetVars.<br>"; }


// Set ABSPATH to base website directory if not already defined
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// Setting Wordpress database defines, salts and connect 
if (!defined('DB_NAME')) {

	if ($MyDebug == "Y") {
		echo ">>> Defining DB Constants.<br>";
	}

	$MySite = $_SERVER['HTTP_HOST'];
	switch($MySite) {
		case "iapdsr.com":
			define('DB_NAME', 'litehaus_iapdsr2');
			break;
		case "iapqa.com":
			define('DB_NAME', 'litehaus_iapqa');
			break;
		case "iapdev.com":
			define('DB_NAME', 'bitnami_wordpress');
			break;
		case "covepoint:8080":
		case "localhost:8080":
			define('DB_NAME', 'litehaus_iapdsr');
			break;
		default:
			echo "<span style='color:red; font-size:120%;'>WP-CONFIG: Unknown IAP server [".$MySite."]<br>";
			echo "In ".basename(__FILE__)."/".__LINE__."</span><br><br>";
			die;
	}
	define('DB_HOST', 'localhost');
	define('DB_USER', 'litehaus_Keeper');
	define('DB_PASSWORD', 'Lite1605!');
	$table_prefix = 'iapwp_';

	if ($_REQUEST['debugme'] == "Y") {
		echo "... DB Host is ".DB_HOST."<br>";
		echo "... DB User is ".DB_USER."<br>";
		echo "... DB Name is ".DB_NAME."<br>";
	}

	if ($MyDebug == "Y") {
		$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
		fwrite($fdebug, "IAPDataConfig ".date("Y/m/d H:i:s")." Host is ".DB_HOST.chr(10).chr(13));
		fclose($fdebug);
	}

	/**#@+
	 * Authentication Unique Keys and Salts.
	 *
	 * Change these to different unique phrases!
	 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
	 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
	 *
	 * @since 2.6.0
	 */
	define( 'AUTH_KEY',         '(}EU{1%w3FZnd(Tn`8r]O.zpr!rKU8[,%l%I~W]%TD;( Q|7>1$;YA?Z;:Z^ZtVV' );
	define( 'SECURE_AUTH_KEY',  'E)mi.)nZ0r(|Fnfa{R#@W#jOfB0vaB@R?{RgeH=_#a/A55R>|jQnSb-V[Xvfxu~c' );
	define( 'LOGGED_IN_KEY',    ')zaKT=&A0>~z,xvaR)O1(G*Pt`o.6q,bwDR_h~-OTo><mQB?^cjoL:Ho(y~P=c(+' );
	define( 'NONCE_KEY',        'Gj4A~eD[4joq7(!:6$P*_i?BXyiATrg4AwTWV7EM;4 x,wc)*p0x6WZdltOU;mpu' );
	define( 'AUTH_SALT',        'tM)|m&(#XMw`Sx[4nulPf#rzJ4rpr9N=i 1ia6:gf^S4u9.UdJ2`->QOWj&FeFj?' );
	define( 'SECURE_AUTH_SALT', 'X:OcZow~/J`0#*F`v{ztKki7pDq$/nPaZ[lI6;+VO4*[j6yxzRe7JBy^~[1@iP`K' );
	define( 'LOGGED_IN_SALT',   'DG$}i*r9S{XZrThu[4%XY !v?GpM4m1aL>J7KuzOCJ#!pY?^QdxHFF3<Aa>#%-cu' );
	define( 'NONCE_SALT',       'e5{9ctR`09*Wr@ym}*S-ad((Z!OY)4p,dS[r[j>w&l3DNOObF_2iPdaepQ`,5}Ml' );

	// Opening Data database
	global $IAPDBConn;
	$IAPDBConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if ($IAPDBConn->connect_errno) {
		$IAPErr = array('retcode' => -101,
						'retmsg' => "Unable to connect to database",
						'mserrno' => $IAPDBConn->connect_errno,
						'mserrmsg' => $IAPDBConn->connect_error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		require_once("IAPDBError.php");
		IAP_MySQL_Error($IAPErr);
		die;
	}
}

// Set up Data database defines and connect
if (!defined('IAPDATA_NAME')) {

	if ($MyDebug == "Y") {
		echo ">>> Defining DATA Constants.<br>";
	}

	$MySite = $_SERVER['HTTP_HOST'];
	switch($MySite) {
		case "iapdsr.com":
			define('IAPDATA_NAME', 'litehaus_iapdsr2');
			break;
		case "iapqa.com":
			define('IAPDATA_NAME', 'litehaus_iapqa');
			break;
		case "iapdev.com":
			define('IAPDATA_NAME', 'bitnami_wordpress');
			break;
		case "covepoint:8080":
		case "localhost:8080":
			define('IAPDATA_NAME', 'litehaus_iapdsr');
			break;
		default:
			echo "<span style='color:red; font-size:120%;'>WP-CONFIG: Unknown IAP server [".$MySite."]<br>";
			echo "In ".basename(__FILE__)."/".__LINE__."</span><br><br>";
			die;
	}

	define('IAPDATA_HOST', 'localhost');
	define('IAPDATA_USER', 'litehaus_Keeper');
	define('IAPDATA_PASSWORD', 'Lite1605!');

	if ($_REQUEST['debugme'] == "Y") {
		echo "... DATA Host is ".IAPDATA_HOST."<br>";
		echo "... DATA User is ".IAPDATA_USER."<br>";
		echo "... DATA Name is ".IAPDATA_NAME."<br>";
	}

// Opening Data database
	global $IAPDataConn;
	$IAPDataConn = new mysqli(IAPDATA_HOST, IAPDATA_USER, IAPDATA_PASSWORD, IAPDATA_NAME);
	if ($IAPDataConn->connect_errno) {
		$IAPErr = array('retcode' => -151,
						'retmsg' => "Unable to connect to data",
						'mserrno' => $IAPDataConn->connect_errno,
						'mserrmsg' => $IAPDataConn->connect_error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		require_once("IAPDBError.php");
		IAP_MySQL_Error($IAPErr);
		die;
	}
}

if (!isset($_REQUEST['IAPPath'])) {

	$MyPath = str_replace("\\", "/", dirname(__FILE__));
	$MyP = explode("/", $MyPath);
	for ($i = 0; $i < count($MyP); $i++) {
		$ln = array_pop($MyP);
		if (strpos(strtolower($ln), "litehausconsulting") !== FALSE
		or  strtolower($ln) == "public_html") {
			array_push($MyP, $ln);
			$LHCPath = implode("/", $MyP);
			$_REQUEST['LHCPath'] = $LHCPath."/";
			break;
		}
	}

	$MySite = $_SERVER['HTTP_HOST'];
	switch($MySite) {
		case "iapdsr.com":
			$_REQUEST['IAPPath'] = $LHCPath."/IAP/";
			$_REQUEST['LHCUrl'] = "https://LitehausConsulting.com";
			$_REQUEST['IAPUrl'] = "https://".$MySite;
			break;
		case "iapqa.com":
			$_REQUEST['IAPPath'] = $LHCPath."/IAPQA/";
			$_REQUEST['LHCUrl'] = "https://LitehausConsulting.com";
			$_REQUEST['IAPUrl'] = "https://".$MySite;
			break;
		case "covepoint:8080":
		case "localhost:8080":
			$_REQUEST['IAPPath'] = $LHCPath."/IAP/";
			$_REQUEST['LHCUrl'] = "http://".$MySite."/LitehausConsulting";
			$_REQUEST['IAPUrl'] = $_REQUEST['LHCUrl']."/IAP";
			break;
		default:
			echo "<span style='color:red; font-size:120%;'>WP-CONFIG: Unknown IAP server [".$MySite."]<br>";
			echo "In ".basename(__FILE__)."/".__LINE__."</span><br><br>";
			die;
	}
	$_REQUEST['runningapp'] = "IAP";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...LHCPath is ".$_REQUEST['LHCPath']."<br>";
		echo "...LHCURL is ".$_REQUEST['LHCUrl']."<br>";
		echo "...IAPPath is ".$_REQUEST['IAPPath']."<br>";
		echo "...IAPURL is ".$_REQUEST['IAPUrl']."<br>";
		echo "...RunningApp is ".$_REQUEST['runningapp']."<br>";
		echo "...ABSPATH = ".ABSPATH."<br>";
	}
}

if(!function_exists('IAP_MySQL_Error')){
	function IAP_MySQL_Error($IAPErr) {
		global $wpdb;

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
// C:\xampp72\htdocs\LitehausConsulting\IAP\wp-content
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

		$IAPRet = $wpdb->query($IAPSql);
		if ($wpdb->last_error != "") {
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

$MyAppl = "IAP";


?>