<?php

error_reporting(E_ERROR | E_PARSE);

$iapType = $_POST['iapType'];
$iapOrg = $_POST['iapOrg'];
$iapRec = $_POST['iapRec'];
$iapPath = $_POST['iapPath'];

if ( !defined('ABSPATH') ) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

require_once($iapPath."/IAPDBServices.php");

		$PurRec = Build_Purchase_Rec($iapOrg, $iapRec);
		$PurCols1 = $PurRec[0];
		$PurData1 = $PurRec[1];
		$PurCols2 = $PurRec[2];
		$PurData2 = $PurRec[3];


		$s = "INSERT INTO iap_purchases (".$PurCols1.") Values (".$PurData1.")";
		$s2 = "INSERT INTO iap_purchase_detail (".$PurCols2.") Values (".$PurData2.")";

$iapRet = iapProcessMySQL("insert", $s);
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


function Build_Purchase_Rec($iapOrg, $iapPurRec) {

// Purchases Record
	$iapPCols1 = "pur_company, pur_po, pur_date, pur_vendor, pur_type, pur_net, pur_shipping, pur_tax"; 
	$iapPData1 = $iapOrg.", '".$iapPurRec[2]."', '".$iapPurRec[0]."', '".$iapPurRec[1]."', '".$iapPurRec[3]."', '".$iapPurRec[4]."', '".$iapPurRec[5]."', '".$iapPurRec[6]."'";

// Purchase Detail Record
	$iapPDet = "INSERT INTO iap_purchase_detail ('purdet_company', 'purdet_purid', 'purdet_id', 'purdet_item', 'purdet_cost) VALUES (\'4\', \'123\', NULL, \'111\', \'\'), (\'4\', \'456\', NULL, \'222\', \'\');";
	$c = count($iapPurRec);
	for($i = 7; $i < $c; $i++) {
		$s = "insert into ";



	}

	$iapP = array($iapPCols1, $iapPData1,$iapPCols2, $iapPData2);
	return($iapP);
}



?>