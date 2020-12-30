<?php

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Party/Event Maintenance with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

$iapPEs = IAP_Get_All_PEs();
if ($iapPEs < 0) {
	echo " <span class=iapError>IAP INTERNAL ERROR cannot get PE list [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
foreach($iapPEs as $iapPE) {
	echo "<br>PE ".strval($iapPE['pe_id'])."-".$iapPE['pe_comment']." on ".$iapPE['pe_date']." of type ".$iapPE['pe_type']." for ".$iapPE['pe_sponsor']." Net = ".number_format($iapPE['pe_net_sales'], 2, '.', '')."<br>";





	$iapSales = IAP_Get_Sale_By_PE($iapPE['pe_id']);
	if ($iapSales < 0) {
		echo " <span class=iapError>IAP INTERNAL ERROR cannot get Sales for PE [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	foreach((array)$iapSales as $iapSale) {
		$iapCustomer = IAP_Get_Customer_By_No($iapSale['sale_customer']);
		if ($iapCustomer < 0) {
			echo " <span class=iapError>IAP INTERNAL ERROR cannot get Customer for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		echo "-----Sale for customer ".$iapCustomer['cust_name']."(".$iapSale['sale_customer'].")<br>";





		$iapSalesDets = IAP_Get_SaleDet($iapSale['sale_id']);
		if ($iapSalesDets < 0) {
			echo " <span class=iapError>IAP INTERNAL ERROR cannot get Sales Detail for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($iapSalesDets[0]['status'] == "NEW") {
			echo "..........No Sales Detail for sale ".$iapSale['sale_id']."<br>";
			continue;
		}
		foreach($iapSalesDets as $iapSalesDet) {
			echo "..........Detail for item ".$iapSalesDet['saledet_item_code']." with quantity of ".$iapSalesDet['saledet_quantity']."<br>";




			if ($iapSalesDet['saledet_mult_lots_applied'] == "N") {
				echo "...............Single lot assigned dated ".$iapSalesDet['saledet_lot_date']." for cost of ".number_format($iapSalesDet['saledet_lot_cost'], 2, '.', '')."<br>";
			} else {
				$iapSaleLots = IAP_Get_SaleLot($iapSalesDet['saledet_sid'], $iapSalesDet['saledet_seq']);
				if ($iapSalesLots < 0) {
					echo " <span class=iapError>IAP INTERNAL ERROR cannot get Sales Detail for Sale [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				if ($iapSalesLots[0]['status'] == "NEW") {
					echo "...............No Sales Lots for sales detail ".$iapSalesDet['saledet_id']."<br>";
					continue;
				}
				foreach((array)$iapSalesLots as $iapSalesLot) {
					echo "...............Lot from ".$iapSalesLot['salelot_lot_date']." with cost of ".number_format($iapsalesLot['salelot_lot_cost'], 2, '.', '')." assigned for quantity of ".$iapSalesLot['salelot_quantity']."<br>";
				}
			}
		}
	}
	wp_ob_end_flush_all();
	flush();
}

?>