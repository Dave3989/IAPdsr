<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In HowDoing with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("507") < 0) {
	return;
};

// Get Purchases 
$iapRet = iapProcessMySQL("select", "SELECT SUM(pur_items) AS purchased_items, SUM(pur_net) AS purchased_net, SUM(pur_tax) AS tax_paid FROM iap_purchases WHERE pur_company = ".$_REQUEST['CoId']." AND EXTRACT(YEAR_MONTH FROM pur_date) = EXTRACT(YEAR_MONTH FROM NOW())");
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapCurMTDPurs = $iapRet['data'][0];
$iapRet = iapProcessMySQL("select", "SELECT SUM(pur_items) AS purchased_items, SUM(pur_net) AS purchased_net, SUM(pur_tax) AS tax_paid FROM iap_purchases WHERE pur_company = ".$_REQUEST['CoId']." AND EXTRACT(YEAR FROM pur_date) = EXTRACT(YEAR FROM NOW())");
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapCurYTDPurs = $iapRet['data'][0];
$iapRet = iapProcessMySQL('select', 'SELECT SUM(pur_items) AS purchased_items, SUM(pur_net) AS purchased_net, SUM(pur_tax) AS tax_paid  FROM iap_purchases WHERE pur_company = '.$_REQUEST['CoId'].' AND EXTRACT(YEAR FROM pur_date) = EXTRACT(YEAR FROM CURDATE()) - 1 AND pur_date <= CONCAT(EXTRACT(YEAR FROM CURDATE()) - 1,"-",EXTRACT(MONTH from CURDATE()),"-",EXTRACT(DAY from CURDATE()))');
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapLastYTDPurs = $iapRet['data'][0];
$iapRet = iapProcessMySQL('select', 'SELECT SUM(pur_items) AS purchased_items, SUM(pur_net) AS purchased_net, SUM(pur_tax) AS tax_paid  FROM iap_purchases WHERE pur_company = '.$_REQUEST['CoId'].' AND EXTRACT(YEAR FROM pur_date) = EXTRACT(YEAR FROM CURDATE()) - 1');
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapLastYrPurs = $iapRet['data'][0];

// Get Sales Volumes
$iapRet = iapProcessMySQL("select", "SELECT SUM(sale_items) AS sold_items, SUM(sale_net) as sold_net, SUM(sale_profit) as sold_profit, SUM(sale_sales_tax) AS sold_tax FROM iap_sales WHERE sale_company = ".$_REQUEST['CoId']." AND EXTRACT(YEAR_MONTH FROM sale_date) = EXTRACT(YEAR_MONTH FROM CURDATE())");
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapCurMTDSales = $iapRet['data'][0];

$iapRet = iapProcessMySQL("select", "SELECT SUM(sale_items) AS sold_items, SUM(sale_net) AS sold_net, SUM(sale_profit) as sold_profit, SUM(sale_sales_tax) AS sold_tax FROM iap_sales WHERE sale_company = ".$_REQUEST['CoId']." AND EXTRACT(YEAR FROM sale_date) = EXTRACT(YEAR FROM CURDATE())");
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapCurYTDSales = $iapRet['data'][0];

$iapRet = iapProcessMySQL('select', 'SELECT SUM(sale_items) AS sold_items, SUM(sale_net) AS sold_net, SUM(sale_profit) as sold_profit, SUM(sale_sales_tax) AS sold_tax FROM iap_sales WHERE sale_company = '.$_REQUEST['CoId'].' AND EXTRACT(YEAR FROM sale_date) = EXTRACT(YEAR FROM CURDATE()) - 1 AND sale_date <= CONCAT(EXTRACT(YEAR FROM CURDATE()) - 1,"-",EXTRACT(MONTH from CURDATE()),"-",EXTRACT(DAY from CURDATE()))');
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapLastYTDSales = $iapRet['data'][0];

$iapRet = iapProcessMySQL('select', 'SELECT SUM(sale_items) AS sold_items, SUM(sale_net) AS sold_net, SUM(sale_profit) as sold_profit, SUM(sale_sales_tax) AS sold_tax FROM iap_sales WHERE sale_company = '.$_REQUEST['CoId'].' AND EXTRACT(YEAR FROM sale_date) = EXTRACT(YEAR FROM CURDATE()) - 1');
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapLastYrSales = $iapRet['data'][0];

// Get Inventory 
$iapRet = iapProcessMySQL("select","SELECT sum(lot_cost * lot_count) as lot_ext_cost, sum(prc_price * lot_count) as cat_ext_price  FROM iap_purchase_lots JOIN iap_prices ON prc_item_code = lot_item_code and prc_effective_until = '2099-12-31' WHERE lot_company = ".$_REQUEST['CoId']." AND prc_company = ".$_REQUEST['CoId']);
if ($iapRet['retcode'] < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot retrieve inventory values. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapCurInvCost = $iapRet['data'][0];
$iapCurInvProfit = $iapCurInvCost['cat_ext_price'] - $iapCurInvCost['lot_ext_cost'];

$iapReadOnly = IAP_Format_Heading("How Am I Doing?");

$h = IAP_Do_Help(3, 507, 1); // level 3, page 507, section 1
if ($h != "") {
	echo "<table style='width:100%;'>";
	echo "<tr><td width='100%;'><br></td></tr>";
	echo "<tr><td width='100%;'>";
	echo $h;
	echo "</td></tr>";
	echo "</table>";
}

// show totals
	echo "<table style='width:100%'>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>&nbsp;</td><td style='width:5%'></td><td style='width:10%'></td><td style='width:5%'></td><td style='width:10%'></td><td style='width:30%'></td></tr>";

	echo "<tr><th colspan='3'></th><th class=iapFormLabel style='width:10%; text-align:right;'>Items</th><th style='width:5%'></th><th style='width:10%; text-align:right;'>Value</th><th style='width:30%'></th></tr>";
// 	Help level 1, page 507, section 1

	echo "<tr><td style='width:5%'></td><td style='width:35%'>Purchases made this month:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDPurs['purchased_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDPurs['purchased_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Purchases made this year-to-date:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDPurs['purchased_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDPurs['purchased_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Purchases made thru same month last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYTDPurs['purchased_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYTDPurs['purchased_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Purchases made all last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrPurs['purchased_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrPurs['purchased_net'], 2)."</td><td style='width:30%'></td></tr>";

	echo "<tr><td colspan='7'>&nbsp;</td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales made this month:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDSales['sold_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDSales['sold_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales made this year-to-date:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDSales['sold_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDSales['sold_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales made thru same month last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYTDSales['sold_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYTDSales['sold_net'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales made all last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrSales['sold_items'], 0)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrSales['sold_net'], 2)."</td><td style='width:30%'></td></tr>";

	echo "<tr><td colspan='7'>&nbsp;</td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Profit this month:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDSales['sold_profit'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Profit this year-to-date:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDSales['sold_profit'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Profit thru same month last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYTDSales['sold_profit'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Profit all last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrSales['sold_profit'], 2)."</td><td style='width:30%'></td></tr>";

	echo "<tr><td colspan='7'>&nbsp;</td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Value of current on-hand inventory</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cost when purchased:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurInvCost['lot_ext_cost'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Value if sold at catalog price:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurInvCost['cat_ext_price'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Potiental profit:</span></td><td style='width:5%'></td><td style='width:10%; text-align:right;'></td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurInvProfit, 2)."</td><td style='width:30%'></td></tr>";


	echo "<tr><td colspan='7'>&nbsp;</td></tr>";
	echo "<tr><td colspan='7'>&nbsp;</td></tr>";

	echo "<tr><th colspan='3'></th><th class=iapFormLabel style='width:10%; text-align:right;'>Paid</th><th style='width:5%'></th><th style='width:10%; text-align:right;'>Reimbursed</th><th style='width:30%'></th></tr>";

	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales Tax this month:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDPurs['tax_paid'], 2)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurMTDSales['sold_tax'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales Tax this year-to-date:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDPurs['tax_paid'], 2)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapCurYTDSales['sold_tax'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales Tax thru same month last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastMTDPurs['tax_paid'], 2)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastMTDSales['sold_tax'], 2)."</td><td style='width:30%'></td></tr>";
	echo "<tr><td style='width:5%'></td><td style='width:35%'>Sales Tax all last year:</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrPurs['tax_paid'], 2)."</td><td style='width:5%'></td><td style='width:10%; text-align:right;'>".number_format((double) $iapLastYrSales['sold_tax'], 2)."</td><td style='width:30%'></td></tr>";


	echo "</table>";


// stats by category

?>