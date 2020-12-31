<?php

/*
error_reporting(E_ERROR | E_PARSE);


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

$_REQUEST['sec_use_application'] = "Y";

$x = $iapPath."/IAPDBServices.php";
require_once($iapPath."/IAPDBServices.php");
*/

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

require_once(ABSPATH."MyPages/IAPProcessLot.php");

// $_REQUEST['CoId'] = 5;

$s = "SELECT `pur_date` as tx_date, `pur_id` as tx_id, 'P' as tx_type FROM `iap_purchases` 
  WHERE `pur_company` = 5
UNION
  SELECT sale_date as tx_date, sale_id as tx_id, 'S' as tx_type FROM iap_sales
  WHERE sale_company = 5
ORDER BY tx_date, tx_type, tx_id";

$iapRet = iapProcessMySQL("select", $s);
if ($iapRet['retcode'] < 0) {
	echo "<span class=iapError>Error in beginning SQL.</span><br>";
	return;
}
$iapList = $iapRet['data'];

foreach($iapList as $iapPurSale) {

	if (!(set_time_limit(120))) {
		echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	if ($iapPurSale['tx_type'] == "P") {
		$iapRet = Process_Purchase($iapPurSale);
	} elseif ($iapPurSale['tx_type'] == "S") {
		$iapRet = Process_Sale($iapPurSale);
	} else {
		echo "<span class=iapError>Erroneous type - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."</span><br>";
	}
}

// update catalog on-hand
/*
$s = "SELECT `lot_item_code`, sum(`lot_count`) as lot_on_hand, iap_catalog.cat_on_hand FROM `iap_purchase_lots` 
JOIN iap_catalog ON cat_item_code = lot_item_code AND cat_company = lot_company
WHERE lot_company = 5
GROUP BY lot_item_code""

$iapRet = iapProcessMySQL("select", $s);
if ($iapRet['retcode'] < 0) {
	echo "<span class=iapError>Error in beginning SQL.</span><br>";
	return;
}

*/
return;

function Process_Purchase($iapPurSale) {
	echo "Processing Purchase - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."<br>";
	wp_ob_end_flush_all();
	flush();

	$iapPurchase = IAP_Get_Purchase($iapPurSale['tx_id']);
	if ($iapPurchase < 0) {
		echo "<span class=iapError>Could not retrieve Purchase - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."</span><br>";
		exit;
	}
	$iapPurDetail = IAP_Get_PurDet($iapPurSale['tx_id']);
	if ($iapPurDetail < 0) {
		echo "<span class=iapError>Could not retrieve Purchase Detail - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."</span><br>";
		exit;
	}
	foreach($iapPurDetail as $iapPDtl) {
//		if ($iapPDtl['purdet_item'] == "111111") {
//			return;
//		}
		$f = substr($iapPDtl['purdet_item'], -4);
		$f = substr($f, 0, -1);
		if ($f != "999") {
			echo "...Adding lot for item ".$iapPDtl['purdet_item']."-".date("Y-m-d",strtotime($iapPDtl['purdet_date']))."-".number_format($iapPDtl['purdet_cost'], 2, '.', '')."-".number_format($iapPDtl['purdet_quantity'], 0, '.', '')."<br>";
			wp_ob_end_flush_all();
			flush();

			$iapNewPD = IAP_Add_Purchase_Lot($iapPDtl, 999, $iapPurchase['pur_order']);
		}
	}
}

function Process_Sale($iapPurSale) {
	echo "Processing Sale - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."<br>";
	wp_ob_end_flush_all();
	flush();

	$iapSale = IAP_Get_Sale($iapPurSale['tx_id']);
	if ($iapSale < 0) {
		echo "<span class=iapError>Could not retrieve Sale - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."</span><br>";
		exit;
	}
	$iapSaleDetail = IAP_Get_SaleDet($iapPurSale['tx_id']);
	if ($iapSaleDetail < 0) {
		echo "<span class=iapError>Could not retrieve sale Detail - ".$iapPurSale['tx_type']."-".$iapPurSale['tx_date']."-".$iapPurSale['tx_id']."</span><br>";
		exit;
	}

	$iapSale['sale_item_cost'] = 0;
	$iapSale['sale_profit'] = 0;

	if ($iapSale['sale_type'] == "F"
	or  $iapSale['sale_type'] == "O"
	or  $iapSale['sale_type'] == "W"
	or  $iapSale['sale_type'] == "X") {
// --- 	Lots are not adjusted for these types
		echo "...Sale type is ".$iapSale['sale_type']." - No update necessary.<br>";
		wp_ob_end_flush_all();
		flush();
		return;
	}
	foreach($iapSaleDetail as $iapSDtl) {
//		if ($iapSDtl['saledet_item'] == "111111") {
//			return;
//		}

		$iapSDtl['saledet_total_cost'] = 0;
		$iapSDtl['saledet_total_profit'] = 0;

		$f = substr($iapSDtl['saledet_item_code'], -4);
		$f = substr($f, 0, -1);
		if ($f != "999") {
			$iapCtlg = IAP_Get_Catalog($iapSDtl['saledet_item_code'], $iapSale['sale_date']);
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR retreiving catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			
			$iapSDtl = IAP_Apply_Lot_To_Sale($iapSDtl, $iapCtlg['prc_cost_unit'], $iapCtlg['prc_effective']);
			$iapSDtl['saledet_total_profit'] = $iapSDtl['saledet_total_price'] - $iapSDtl['saledet_total_cost'];

			echo "...Item ".$iapSDtl['saledet_item_code']." new total cost is ".number_format($iapSDtl['saledet_total_cost'], 2, '.', '')." and profit is ".number_format($iapSDtl['saledet_total_profit'], 2, '.', '')." for quantity of ".number_format($iapSDtl['saledet_quantity'], 0, '.', '')."<br>";
			wp_ob_end_flush_all();
			flush();

			$iapSale['sale_item_cost'] = $iapSale['sale_item_cost'] + $iapSDtl['saledet_total_cost'];
			$iapSale['sale_profit'] = $iapSale['sale_profit'] + $iapSDtl['saledet_total_profit'];
		}
		$iapRet = IAP_Update_Data($iapSDtl, "sdtl");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR writing sales detail [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapSale['sale_changed'] = date("Y-m-d");
		$iapSale['sale_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapSale, "sale");
		if ($iapRet < 0) {
 			echo "<span class=iapError>IAP INTERNAL ERROR writing Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}
}

?>