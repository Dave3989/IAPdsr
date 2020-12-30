<?php

error_reporting(E_ERROR | E_PARSE);

if (!(isset($_REQUEST['iapType']))) {
	echo "-1";
	return;
}
$iapType = $_REQUEST['iapType'];
$iapOrg = $_REQUEST['iapOrg'];
$iapKey = $_REQUEST['iapKey'];

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

if (!defined('ABSPATH')) {
	define('ABSPATH', $iapPath.'/');
}

$x = $iapPath."/IAPDBServices.php";
require_once($iapPath."/IAPDBServices.php");

if ($iapType != '$R') {
	echo "-2";
	return;
}

$PriceKey = explode("|", $iapKey);
$PKUntil = date("Y-m-d", strtotime($PriceKey[1]." - 1 day"));

$sp = "SELECT * FROM iap_prices WHERE prc_company = ".$iapOrg." AND prc_item_code = '".strtoupper($PriceKey[0])."' AND prc_effective_until = '".$PKUntil."'";
$iapRet = iapProcessMySQL("select", $sp);
if ($iapRet['retcode'] < 0) {
	return(-3);
}
$prcRec = $iapRet['data'][0];

$dp = "DELETE FROM iap_prices WHERE prc_company = ".$iapOrg." AND prc_item_code = '".strtoupper($PriceKey[0])."' AND prc_effective_until = '".$PriceKey[2]."'";
$iapRet = iapProcessMySQL("delete", $dp);
if ($iapRet['retcode'] < 0) {
	return(-4);
}

$up = "UPDATE iap_prices SET prc_effective_until = '".$PriceKey[2]."' WHERE prc_company = ".$iapOrg." AND prc_item_code = '".strtoupper($PriceKey[0])."' AND prc_effective_until = '".$PKUntil."'";
$iapRet = iapProcessMySQL("update", $up);
if ($iapRet['retcode'] < 0) {
	return(-5);
}

$_REQUEST['runningapp'] = "IAP";
$iapJ = IAP_Build_New_Row(array("table" => "jrnl"));
$iapJrnl = $iapJ[0]; 
$iapJrnl['jrnl_company'] = $prcRec['prc_company'];
$iapJrnl['jrnl_date'] = date("Y-m-d", strtotime("now"));
$iapJrnl['jrnl_description'] = "Pricing Rolled Back For Item ".$PriceKey[0]." Effective ".date("m/d/Y", strtotime($prcRec['prc_effective']));
$iapJrnl['jrnl_type'] = "IP";
$iapJrnl['jrnl_amount'] = 0;
$iapJrnl['jrnl_tax'] = 0;
$iapJrnl['jrnl_shipping'] = 0;
$iapJrnl['jrnl_mileage'] = 0;
$iapJrnl['jrnl_expenses'] = 0;
$iapJrnl['jrnl_exp_explain'] = 0;
$iapJrnl['jrnl_vendor'] = 0;
$iapJrnl['jrnl_item_code'] = $PriceKey[0];
$iapJrnl['jrnl_cost'] = $prcRec['prc_cost'];
$iapJrnl['jrnl_units'] = $prcRec['prc_units'];
$iapJrnl['jrnl_price'] = $prcRec['prc_price'];
$iapJrnl['jrnl_cat_code'] = $prcRec['prc_cat_code'];
$iapJrnl['jrnl_comment'] = $iapJrnl['jrnl_description'];
$iapJrnl['jrnl_detail_key'] = "";
$iapJrnl['jrnl_changed'] = $prcRec['prc_changed'];
$iapJrnl['jrnl_changed_by'] = $prcRec['prc_changed_by'];
$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
if ($iapRet < 0) {
	return(-6);
}

return(0);

?>