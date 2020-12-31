<?php

error_reporting(E_ERROR | E_PARSE);

if (isset($_REQUEST['iapType'])) {
	$iapType = $_REQUEST['iapType'];
	$iapOrg = $_REQUEST['iapOrg'];
	$iapKey = $_REQUEST['iapKey'];
} else {
	$in = file('php://input');
	$inarray = urldecode(implode($in));
	$parms = explode("|", $inarray);
	if (count($parms) != 3) {
		echo "-3";
		return;
	}

	$iapType = $parms[0];
	$iapOrg = $parms[1];
	$iapKey = $parms[2];
}

$MyPath = str_replace("\\", "/", dirname(__FILE__));
$MyP = explode("/", $MyPath);
for ($i = 0; $i < count($MyP); $i++) {
	$ln = array_pop($MyP);
	$lnsm = strtolower($ln);
	if (strpos($lnsm, "iap") !== FALSE
	or strpos($lnsm, "itsaparty") !== FALSE) {
		array_push($MyP, $ln);
		$iapPath = implode("/", $MyP);
		break;
	}
}

if ( !defined('ABSPATH') ) {
	define('ABSPATH', $iapPath.'/');
}

$x = $iapPath."/IAPServices.php";
require_once($iapPath."/IAPServices.php");

$iapItems = (array) IAP_Get_Savearea("IAP300PM", $_REQUEST['IAPUID']);
if (empty($iapItems)) {
    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}






















switch($iapType) {
	case "ALL" :
		#t = "";
		break;	
	case "C#":
		$t = " AND cust_no = ".strval($iapKey);
		break;
	case "CN":
		$t = " AND LOWER(cust_name) = ".strtolower($iapKey);
		break;

	case "I#":
		$t = " AND cat_item_code = '".strval($iapKey)."'";
		break;
	case "IA":
		$t = "";
		break;
	case "IN":
		$t = " AND LOWER(cat_description) = '".strtolower($iapKey)."'";
		break;

	case "J#":
		$t = " AND jrnl_id = '".strval($iapKey)."'";
		break;
	case "JD":
		$t = " AND LOWER(jrnl_description) = '".strtolower($iapKey)."'";
		break;

	case "P#":
		$t = " AND pur_id = '".strval($iapKey)."'";
		break;

	default:
		echo "-2";
		return(-2);
}

switch(substr($iapType, 0, 1)) {
	case "C":
		$s = "SELECT * from iap_customers WHERE cust_company = ".$iapOrg.$t;
		break;
	case "I":
	    $iapToday = date("Y-m-d", strtotime("now"));
		$s = "SELECT iap_catalog.*, iap_prices.*, iap_codes.* from iap_catalog ".
	"JOIN iap_prices ON iap_prices.prc_company = iap_catalog.cat_company AND iap_prices.prc_item_code = iap_catalog.cat_item_code ".
	"JOIN iap_codes on iap_codes.code_company = iap_catalog.cat_company AND iap_codes.code_type = 'cat' AND iap_codes.code_code = iap_prices.prc_cat_code ".
	"WHERE iap_catalog.cat_company = '".$iapOrg."' AND iap_prices.prc_effective > '".$iapToday."' AND iap_prices.prc_item_code = iap_catalog.cat_item_code ".
	"ORDER BY iap_catalog.cat_item_code";
		break;
	case "J":
		$s = "SELECT * from iap_journal WHERE jrnl_company = ".$iapOrg.$t;
		break;
	case "P":
		$s = "SELECT * from iap_purchases WHERE pur_company = ".$iapOrg.$t;
		break;
	default:
		echo "-1";
		return(-1);
}

$iapRet = iapProcessMySQL("select", $s);
if ($iapRet['retcode'] < 0) {
	echo "-1";
	return;
}
if ($iapRet['numrows'] == 0){
	echo "0";
	return;
}

if (substr($iapType, 1, 1) == "A") {
	$d = $iapRet['data'];
} else {
	$d = $iapRet['data'][0];
}

if (substr($iapType, 0, 1) == "P") {

	$s = "SELECT * from iap_purchase_detail WHERE purdet_company = ".$iapOrg." AND purdet_purid = '".strval($iapKey)."'";

	$iapRet = iapProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		echo "-1";
		return;
	}
	if ($iapRet['numrows'] == 0){
		echo "0";
		return;
	}

	$dtl = $iapRet['data'];
	$d['purdtl'] = $dtl;
}

$iapOut = json_encode($d);
echo $iapOut;
return;

?>