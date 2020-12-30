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
	if (strpos($lnsm, "iap") !== FALSE) {
		array_push($MyP, $ln);
		$iapPath = implode("/", $MyP);
		break;
	}
}

if ( !defined('ABSPATH') ) {
	define('ABSPATH', $iapPath.'/');
}

$x = $iapPath."/IAPDBServices.php";
require_once($iapPath."/IAPDBServices.php");

switch($iapType) {
	case "H":
		$s = Build_Help_Rec($iapKey);
		break;
	case "P":
		$PurRec = Build_Purchase_Rec($iapOrg, $iapRec);
		$PurCols1 = $PurRec[0];
		$PurData1 = $PurRec[1];
		$PurCols2 = $PurRec[2];
		$PurData2 = $PurRec[3];
		$s = "INSERT INTO iap_purchases (".$PurCols1.") Values (".$PurData1.")";
		$s2 = "INSERT INTO iap_purchase_detail (".$PurCols2.") Values (".$PurData2.")";
		break;
	case "S":
//		$s = "INSERT INTO iap_suppliers (".$SuppCols.") Values (".$SuppData.")";
		break;
	default:
		echo "-2";
		return;
}


$ts = explode(" ", $s);
$t = strtolower($ts[0]);
$iapRet = iapProcessMySQL($t, $s);
if ($iapRet['retcode'] < 0) {
	echo "-4";
	return;
}
$d = $iapRet;

if ($iapType == "P") {
	$d1 = $d;
	$iapRet = iapProcessMySQL("insert", $s2);
	if ($iapRet['retcode'] < 0) {
		echo "-4";
		return;
	}
	$d2 = $iapRet;
	$d = array($d1, $d2);
}

$iapOut = json_encode($d);
echo $iapOut;
return;

function Build_Help_Rec($iapHelpRec) {

	$hr = explode("|", $iapHelpRec);

	$s1 = "SELECT * FROM iap_help_level ";
	$s1 = $s1."WHERE hl_client = ".$hr[0]." AND hl_page = ".$hr[1];
	$iapRet = iapProcessMySQL("select", $s1);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}

	if ($iapRet['numrows'] == 0) {
		$s2 = "INSERT INTO iap_help_level (hl_client, hl_page, hl_level, hl_changed, hl_changed_by) Values (".$hr[0].", ".$hr[1].", '".$hr[2]."', '".date("Y-m-d", strtotime("now"))."', ".$hr[0].")";
	} else {
		$s2 = "UPDATE iap_help_level set hl_level = '".$hr[2]."', hl_changed = ".date("Y-m-d", strtotime("now")).", hl_changed_by = ".$hr[0]." WHERE hl_client = ".$hr[0]." AND hl_page = ".$hr[1];
	}
	return($s2);
}

function Build_Purchase_Rec($iapOrg, $iapPurRec) {

	$iapPCols1 = "pur_company, pur_id, pur_po, pur_date, pur_vendor, pur_type, pur_net, pur_shipping, pur_tax"; 
	$iapPData1 = $iapOrg.", '".$iapPurRec[0]."', '".$iapPurRec[1]."', '".$iapPurRec[2]."', '".$iapPurRec[3]."', '".$iapPurRec[4]."', '".$iapPurRec[5]."', '".$iapPurRec[6]."', '".$iapPurRec[7]."', '".$iapPurRec[8]."'"; 
	$iapP = array($iapPCols1, $iapPData1,$iapPCols2, $iapPData2);
	return($iapP);
}

function Build_Supplier_Rec($iapOrg, $iapSupplierRec) {

	$iapSCols = "vend_company, vend_name, vend_street, vend_city, vend_state, vend_zip, vend_phone, vend_fax, vend_email, vend_website";	$iapSData = $iapOrg.", '".$iapSupplierRec[0]."', '".$iapSupplierRec[1]."', '".$iapSupplierRec[2]."', '".$iapSupplierRec[3]."', '".$iapSupplierRec[4]."', '".$iapSupplierRec[5]."', '".$iapSupplierRec[6]."', '".$iapSupplierRec[7]."', '".$iapSupplierRec[8]."'"; 
	$iapS = array($iapSCols, $iapSData);
	return($iapS);
}



?>