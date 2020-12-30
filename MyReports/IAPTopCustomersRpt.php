<?php

function finrpt_head() {
	$title = IAP_Format_Report_Heading("Financial Activity");
	$ln = str_pad($title, 80, " ", STR_PAD_BOTH)."<br>";	
	return($ln);
}

function finrpt_sale_fldhead() {

	$c = str_pad("Customer", 40, " ", STR_PAD_RIGHT);
	$n = str_pad("Net Sale", 8, " ", STR_PAD_LEFT);
	$s = str_pad("Shipping", 8, " ", STR_PAD_LEFT);
	$x = str_pad("Tax", 8, " ", STR_PAD_LEFT);
	$t = str_pad("Total", 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function finrpt_sale_fldhead2() {

	$c = str_pad("--------", 40, " ", STR_PAD_RIGHT);
	$n = str_pad("--------", 8, " ", STR_PAD_LEFT);
	$s = str_pad("--------", 8, " ", STR_PAD_LEFT);
	$x = str_pad("---", 8, " ", STR_PAD_LEFT);
	$t = str_pad("-----", 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function finrpt_format_sale($iapCust, $iapNet, $iapShip, $iapTax, $iapTotal) {

	$c = str_pad($iapCust, 40, " ", STR_PAD_RIGHT);
	$n = str_pad(number_format($iapNet, 2, '.', ','), 8, " ", STR_PAD_LEFT);
	$s = str_pad(number_format($iapShip, 2, '.', ','), 8, " ", STR_PAD_LEFT);
	$x = str_pad(number_format($iapTax, 2, '.', ','), 8, " ", STR_PAD_LEFT);
	$t = str_pad(number_format($iapTotal, 2, '.', ','), 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_format_totals($iapComment, $iapNet) {

	$c = str_pad($iapComment, 40, " ", STR_PAD_RIGHT);
	$n = str_pad(number_format($iapNet, 2, '.', ','), 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n;
	return($ln);
}



require_once( "IAPReportStart.php" );
if (IAP_Program_Start("R01", "N") < 0) {
	return;
};

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Annual Finanial Report.<br />";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

// if (!( $_REQUEST['sec_report'] == "Y" )) {
//	echo "<br /><br /><strong>You do not have authorization to use this function.</strong><br /><br /><br />";
//	return;
//}

if (!isset($_REQUEST['action'])) {
	echo "<br /><br /><center><span style='color:red; font-size:115%;>";
	echo "This program can only be run from within the ItsAPartyDSR application.<br />";
	echo "</span></center>";
	echo "</body></html>";
	return;
}

if ($_REQUEST['action'] !== "print") {
	echo "<font color='red'><strong>IAPP INTERNAL ERROR Invalid action encountered [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></font><br />";
	exit;
}

if ($_REQUEST['debugme'] == "Y") {
	echo "......action of print passed so getting data.<br />";
}

$company = $_REQUEST['CoId'];
$startDate = $_REQUEST['sd'];
$endDate = $_REQUEST['ed'];
$type = $_REQUEST['t'];
$prod = $_REQUEST['p'];

$rpthd = finrpt_head();
echo $rpthd;

require_once($_REQUEST['IAPPath']."IAPDBServices.php");
//	  	   WHERE sale_company = ".$company." AND sale_date >= ".$startDate." AND sale_date <= ".$endDate." 

$Sales = 'SELECT SUM(sale_net) + SUM(sale_sales_tax) AS NetSales, SUM(sale_item_cost) AS COGS, 
                (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent 
		  FROM iap_sales 
	  	  WHERE sale_company = '.$company.' AND sale_date >= "'.$startDate.'" AND sale_date <= "'.$endDate.'" 
		  GROUP BY sale_company';
$ret = iapProcessMySQL("select", $Sales);
if ($ret['$retcode'] < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot $retrieve selected sales data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
$SalesSummary = $ret['data'];

echo "...___...Sales Query <pre>";
var_dump($Sales);
echo "</pre><br><br>";

echo "...___...Sales Returned <pre>";
var_dump($ret);

//var_dump($SalesSummary);
echo "</pre><br><br>";




if ($type == "Y") {
	$SalesTypes = 'SELECT sale_type, SUM(sale_total_amt) + SUM(sale_sales_tax) AS SalesRevenue, SUM(sale_item_cost) AS COGS, 
                         (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent 
			  	   FROM iap_sales
			  	   WHERE sale_company = '.$company.' AND sale_date >= "'.$startDate.'" AND sale_date <= "'.$endDate.'" 
			  	   GROUP BY sale_type';
	$ret = iapProcessMySQL("select", $SalesTypes);
	if ($ret['$retcode'] < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot $retrieve selected sales data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$SalesByType = $ret['data'];

	echo "...___...Sale Types Query <pre>";
	var_dump($SalesTypes);
	echo "</pre><br><br>";

	echo "...___...Sale Types Returned <pre>";
	var_dump($SalesByType);
	echo "</pre><br><br>";



}

if ($prod == "Y") {
	$SalesProds = 'SELECT code_value, SUM(sale_total_amt) + SUM(sale_sales_tax) AS SalesRevenue, SUM(sale_item_cost) AS COGS, 
                         (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent 
                   FROM iap_sales_detail 
                   JOIN iap_sales on sale_company = saledet_company AND sale_id = saledet_sid
                   JOIN iap_supplier_prices on prc_supplier_id = saledet_item_source 
                         AND prc_item_code = saledet_item_code
                         AND prc_effective_until = "2099-12-31"
                   JOIN iap_supplier_codes on code_supplier_id = saledet_item_source AND prc_cat_code = code_code
                   WHERE saledet_company = 5 AND sale_date >= "2019-01-01" AND sale_date <= "2019-12-31" 
                         AND code_inv_type = "I"
                         GROUP BY prc_cat_code
                         ORDER BY code_value';

	$ret = iapProcessMySQL("select", $SalesProds);
	if ($ret['$retcode'] < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot $retrieve selected sales data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$SalesByProd = $ret['data'];

	echo "...___...Sale Product Groups Query <pre>";
	var_dump($SalesProds);
	echo "</pre><br><br>";

	echo "...___...Sale Product Groups Returned <pre>";
	var_dump($SalesByProd);
	echo "</pre><br><br>";



}



exit;
/*
									Party With Pam's Financial Status
									         As  09/15/2016
                     (This repport does NOT reflect website sales nor commissions)

                                 MTD             QTD              YTD
                               -------         -------          -------

Inventory Previous Year End										xxx,xxx

Inventory Purchased				xx,xxx			xx,xxx			xxx,xxx

Inventory Sold					xx,xxx			xx,xxx			xxx,xxx

Inventory Current YTD											xxx,xxx 


Sales Made (Net)			    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Cost of Sales 				    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Profit From Sales			    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx


Sales Tax Paid				    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Sales Tax Reimbursed		    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Shipping Paid on Purchases	    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Shipping Received on Sales	    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Supplies Purchased			    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Other Expenses				    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Miles Traveled				    xx,xxx.xx	    xx,xxx.xx	    xxx,xxx.xx

Income/Expense Report


Report prepared for [profilename] of [companyname] by its a party dsr, iapdsr.com






*/







?>