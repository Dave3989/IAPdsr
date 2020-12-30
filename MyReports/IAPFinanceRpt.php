<?php

function finrpt_alignment_line($Orientation) {

//	$iapRpt[] = str_pad(iap_alignment_line("L"), 120, " ", STR_PAD_RIGHT);

	if ($Orientation == "P") {
		$ln = "123456789A123456789B123456789C12345789D123456789E123456789F123456789G123456789H123456789I"; // 90
	} else {
		$ln = "123456789A123456789B123456789C12345789D123456789E123456789F123456789G123456789H123456789I123456789J123456789K12345789L"; // 120
	}
	return($ln);
}

function finrpt_sale_fldhead1() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad(" Net   ", 12, " ", STR_PAD_LEFT);
	$s = str_pad("Cost Of ", 12, " ", STR_PAD_LEFT);
	$x = str_pad("Percent", 10, " ", STR_PAD_LEFT);
	$y = str_pad("Number ", 10, " ", STR_PAD_LEFT);
	$z = str_pad("Average", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $y. " ". $z;
	return($ln);
}

function finrpt_sale_fldhead2() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad("Sales  ", 12, " ", STR_PAD_LEFT);
	$s = str_pad("Goods  ", 12, " ", STR_PAD_LEFT);
	$x = str_pad("Profit", 10, " ", STR_PAD_LEFT);
	$y = str_pad("Of Sales", 10, " ", STR_PAD_LEFT);
	$z = str_pad("Amount", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $y. " ". $z;
	return($ln);
}

function finrpt_sale_fldhead3() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad("----------", 12, " ", STR_PAD_LEFT);
	$s = str_pad("----------", 12, " ", STR_PAD_LEFT);
	$x = str_pad("--------", 10, " ", STR_PAD_LEFT);
	$y = str_pad("--------", 10, " ", STR_PAD_LEFT);
	$z = str_pad("--------", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $y. " ". $z;
	return($ln);
}

function finrpt_format_sale($SaleType, $SaleNet, $SaleCOGS, $SaleProfit, $SaleCount, $SaleAvg) {
	$c = str_pad($SaleType, 30, " ", STR_PAD_RIGHT);
	$n = str_pad(number_format((float)$SaleNet, 2, '.', ','), 12, " ", STR_PAD_LEFT);
	$s = str_pad(number_format((float)$SaleCOGS, 2, '.', ','), 12, " ", STR_PAD_LEFT);
	$x = str_pad(number_format((float)$SaleProfit, 1, '.', ','), 10, " ", STR_PAD_LEFT);
	$y = str_pad(number_format((float)$SaleCount, 0, '.', ','), 10, " ", STR_PAD_LEFT);
	$z = str_pad(number_format((float)$SaleAvg, 2, '.', ','), 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $y. " ". $z;
	return($ln);
}


function finrpt_inv_fldhead1() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad("Beginning", 12, " ", STR_PAD_LEFT);
	$s = str_pad("Additional", 12, " ", STR_PAD_LEFT);
	$x = str_pad("Ending", 12, " ", STR_PAD_LEFT);
	$z = str_pad("Average", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $z;
	return($ln);
}

function finrpt_inv_fldhead2() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad("Inventory   ", 12, " ", STR_PAD_LEFT);
	$s = str_pad("This Period", 12, " ", STR_PAD_LEFT);
	$x = str_pad("Inventory", 12, " ", STR_PAD_LEFT);
	$z = str_pad("Cost", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $z;
	return($ln);
}

function finrpt_inv_fldhead2() {
	$c = str_pad(" ", 30, " ", STR_PAD_RIGHT);
	$n = str_pad("---------", 12, " ", STR_PAD_LEFT);
	$s = str_pad("-----------", 12, " ", STR_PAD_LEFT);
	$x = str_pad("----------", 12, " ", STR_PAD_LEFT);
	$z = str_pad("-------", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $z;
	return($ln);
}

function finrpt_format_inv($InvGroup, $InvStarting, $InvNew, $InvEnding, $InvAvg) {
	$c = str_pad($InvGroup, 30, " ", STR_PAD_RIGHT);
	$n = str_pad(number_format((float)$InvStarting, 0, '.', ','), 12, " ", STR_PAD_LEFT);
	$s = str_pad(number_format((float)$InvNew, 0, '.', ','), 12, " ", STR_PAD_LEFT);
	$x = str_pad(number_format((float)$InvEnding, 0, '.', ','), 12, " ", STR_PAD_LEFT);
	$z = str_pad(number_format((float)$InvAvg, 2, '.', ','), 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $z;
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

$FinRpt = array();

require_once($_REQUEST['IAPPath']."IAPDBServices.php");

$FinRpt[] = finrpt_sale_fldhead1();
$FinRpt[] = finrpt_sale_fldhead2();
$FinRpt[] = finrpt_sale_fldhead3();

$Sales = 'SELECT SUM(sale_net) + SUM(sale_sales_tax) AS NetSales, SUM(sale_item_cost) AS COGS, 
                (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent,
                 COUNT(sale_id) AS NumSales, (SUM(sale_net) + SUM(sale_sales_tax)) / COUNT(sale_type) AS AvgSale  
		  FROM iap_sales 
	  	  WHERE sale_company = '.$company.' AND sale_date >= "'.$startDate.'" AND sale_date <= "'.$endDate.'" 
		  GROUP BY sale_company';
$ret = iapProcessMySQL("select", $Sales);
if ($ret['$retcode'] < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected sales data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
$SalesSummary = $ret['data'];

foreach($SalesSummary as $Sum) {
	$FinRpt[] = finrpt_format_sale("Sales For Period", $Sum['NetSales'], $Sum['COGS'], $Sum['ProfitPercent'], $Sum['NumSales'], $Sum['AvgSale']);
}


if ($type == "Y") {
	$SalesTypes = 'SELECT sale_type, SUM(sale_net) + SUM(sale_sales_tax) AS NetSales, SUM(sale_item_cost) AS COGS, 
                         (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent, 
                          COUNT(sale_id) AS NumSales, (SUM(sale_net) + SUM(sale_sales_tax)) / COUNT(sale_type) AS AvgSale  
			  	   FROM iap_sales
			  	   WHERE sale_company = '.$company.' AND sale_date >= "'.$startDate.'" AND sale_date <= "'.$endDate.'" 
			  	   GROUP BY sale_type';
	$ret = iapProcessMySQL("select", $SalesTypes);
	if ($ret['$retcode'] < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected sales type data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$SalesByType = $ret['data'];

//    echo "...___...returned <pre>";
//    var_dump($SalesByType);
//    echo "</pre>";

	$FinRpt[] = " ";
	$FinRpt[] = "   Sales By Type";
	$FinRpt[] = "   -------------";
	foreach($SalesByType as $Type) {
		switch($Type['sale_type']) {
			case "E":
				$t = "Sale At A Event";
				break;
			case "F":
				$t = "Sales On Facebook";
				break;
			case "I":
				$t = "Sale To Individual";
				break;
			case "P":
				$t = "Sale At A Party";
				break;
			case "O":
				$t = "Other Sale Type";
				break;
			case "W":
				$t = "Sale On Company Website";
				break;
			default:
				$t = "Unknown Type";
		}
		$FinRpt[] = finrpt_format_sale($t, $Type['NetSales'], $Type['COGS'], $Type['ProfitPercent'], $Type['NumSales'], $Type['AvgSale']);
	}
}

if ($prod == "Y") {
	$SalesProds = 'SELECT code_value, SUM(sale_net) + SUM(sale_sales_tax) AS NetSales, SUM(sale_item_cost) AS COGS, 
                         (1 - (SUM(sale_item_cost) / (SUM(sale_total_amt) + SUM(sale_sales_tax)))) * 100 AS ProfitPercent,
                          COUNT(sale_id) AS NumSales, (SUM(sale_net) + SUM(sale_sales_tax)) / COUNT(sale_type) AS AvgSale  
                   FROM iap_sales_detail 
                   JOIN iap_sales on sale_company = saledet_company AND sale_id = saledet_sid
                   JOIN iap_supplier_prices on prc_supplier_id = saledet_item_source 
                         AND prc_item_code = saledet_item_code
                         AND prc_effective_until = "2099-12-31"
                   JOIN iap_supplier_codes on code_supplier_id = saledet_item_source AND prc_cat_code = code_code
                   WHERE saledet_company = '.$company.' AND sale_date >= "'.$startDate.'" AND sale_date <= "'.$endDate.'" 
                         AND code_inv_type = "I"
                         GROUP BY prc_cat_code
                         ORDER BY code_value';

	$ret = iapProcessMySQL("select", $SalesProds);
	if ($ret['$retcode'] < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected sales product data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	$SalesByProd = $ret['data'];

	$FinRpt[] = " ";
	$FinRpt[] = "   Sales By Product Group";
	$FinRpt[] = "   ----------------------";
	foreach($SalesByProd as $Prod) {
		$FinRpt[] = finrpt_format_sale($Prod['code_value'], $Prod['NetSales'], $Prod['COGS'], $Prod['ProfitPercent'], $Prod['NumSales'], $Prod['AvgSale']);
	}
}

// Begin Inventory

$FinRpt[] = finrpt_inv_fldhead1();
$FinRpt[] = finrpt_inv_fldhead2();
$FinRpt[] = finrpt_inv_fldhead3();











$Inventory = 'SELECT inv_item_code, SUM(inv_on_hand) AS iOnHand, SUM(salelot_quantity) AS lQty FROM iap_inventory 
              LEFT JOIN iap_sales_applied_lots ON salelot_company = '.$company.' AND salelot_item_code = inv_item_code 
              AND salelot_lot_date >= "'.$startDate.'" AND salelot_lot_date <= "'.$endDate.'" 
              WHERE inv_company = '.$company.' AND inv_on_hand != 0 GROUP BY inv_item_code';

$ret = iapProcessMySQL("select", $Inventory);
if ($ret['$retcode'] < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve selected inventory data [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}
$InvSummary = $ret['data'];

foreach($InvSummary as $Inv) {
	if (is_null(lQty)) {
		$q1 = $Inv['iOnHand'];
	} else {
		$q1 = $Inv['iOnHand'] + $Inv['lQty'];
	}
	$FinRpt[] = finrpt_format_inv("Inventory During Period", $Inv['iOnHand'], $Inv['lQty'], "", "");
}

/*
SELECT code_value, SUM(inv_on_hand) AS iOnHand, SUM(saledet_quantity) AS dQty 
FROM iap_inventory 
  LEFT JOIN iap_sales_detail ON saledet_company = inv_company AND saledet_item_code = inv_item_code 
  JOIN iap_sales ON sale_id = saledet_sid
  JOIN iap_supplier_prices ON prc_supplier_id = 1 AND prc_item_code = inv_item_code
     AND prc_effective_until = "2099-12-31"
  JOIN iap_supplier_codes on code_code = prc_cat_code
WHERE inv_company = 5 AND inv_on_hand != 0
	 AND code_inv_type != "S" 
	 AND sale_type != "W"
     AND sale_date >= "2019-01-01" AND sale_date <= "2019-12-31" 
GROUP BY prc_cat_code


SELECT saledet_item_code, saledet_item_source, SUM(`saledet_quantity`) as dQty, 
	 SUM(salelot_quantity) AS lQty 
from iap_sales_detail 
LEFT JOIN iap_sales_applied_lots on salelot_item_code = `saledet_item_code` 
join iap_sales on sale_id = `saledet_sid`
WHERE saledet_company = 5 AND sale_type != "W"
     AND sale_date >= "2019-01-01" AND sale_date <= "2019-12-31" 
GROUP BY saledet_item_code


SELECT saledet_item_code, saledet_sid, saledet_seq, `saledet_quantity` as dQty, 
	`salelot_quantity` AS lQty 
FROM iap_sales_detail 
LEFT JOIN iap_sales_applied_lots ON `salelot_item_code` = `saledet_item_code` 
	AND salelot_quantity != saledet_quantity
WHERE saledet_company = 5 
     AND `saledet_sid` > 1448
     AND saledet_item_code != "111111"
     AND saledet_item_code < "X     "
	 AND `saledet_cost_from_book` = "N"
group by `saledet_sid`
order by saledet_item_code


SELECT code_value, SUM(saledet_quantity) AS dQty 
FROM iap_sales_detail 
  JOIN iap_sales ON sale_id = saledet_sid
  JOIN iap_supplier_prices ON prc_supplier_id = 1 AND prc_item_code = saledet_item_code
     AND prc_effective_until = "2099-12-31"
  JOIN iap_supplier_codes on code_code = prc_cat_code
WHERE saledet_company = 5 AND code_inv_type != "S" 
     AND sale_type != "W"
     AND sale_date >= "2019-01-01" AND sale_date <= "2019-12-31" 
GROUP BY code_value


SELECT count(purdet_item), sum(purdet_ext_cost) 
FROM `iap_purchase_detail` 

JOIN iap_supplier_prices ON prc_supplier_id = 1 AND prc_item_code = purdet_item
   AND prc_effective_until = "2099-12-31"
JOIN iap_supplier_codes on code_code = prc_cat_code

WHERE purdet_company = 5
and purdet_date < "2020-01-01"
AND code_inv_type != "S"
*/












 


$FinRpt[] = " ";
$FinRpt[] = " ";
$FinRpt[] = " ";
$FinRpt[] = " ";

$f1 = "Report prepared for ".$_REQUEST['UserData']['FirstName']." ".$_REQUEST['UserData']['LastName']." of ".$_REQUEST['UserData']['DisplayName'];
$FinRpt[] = str_pad($f1, 96, " ", STR_PAD_BOTH);
$f2 = "(c) It's A Party DSR [iapdsr.com] by Litehaus Consulting";
$FinRpt[] = str_pad($f2, 96, " ", STR_PAD_BOTH);
$FinRpt[] = " ";

$FinHead = array();
$h1 = IAP_Format_Report_Heading("Financial Activity");
$FinHead[] = str_pad($h1, 96, " ", STR_PAD_BOTH);
$h2 = "For Date Range Of ".$dateRange = date("m/d/Y", strtotime($startDate))." - ".date("m/d/Y", strtotime($endDate));
$FinHead[] = str_pad($h2, 96, " ", STR_PAD_BOTH);
$FinHead[] = "";
$FinHead[] = "";


if (IAP_Generate_PDF($h1, $h2, $FinHead, $FinRpt, "L") < 0) {
	echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot generate sales receipt for ".$iapCustomer['cust_name']." [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/". __LINE__."</font><br />";
	exit;
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