<?php

function iap_sale_head() {

	$c = str_pad("Customer", 40, " ", STR_PAD_RIGHT);
	$n = str_pad("Net Sale", 8, " ", STR_PAD_LEFT);
	$s = str_pad("Shipping", 8, " ", STR_PAD_LEFT);
	$x = str_pad("Tax", 8, " ", STR_PAD_LEFT);
	$t = str_pad("Total", 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_sale_head2() {

	$c = str_pad("--------", 40, " ", STR_PAD_RIGHT);
	$n = str_pad("--------", 8, " ", STR_PAD_LEFT);
	$s = str_pad("--------", 8, " ", STR_PAD_LEFT);
	$x = str_pad("---", 8, " ", STR_PAD_LEFT);
	$t = str_pad("-----", 8, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_format_sale($iapCust, $iapNet, $iapShip, $iapTax, $iapTotal) {

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
	echo ">>>In Party Close Report.<br />";
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

if ($_REQUEST['action'] !== "selected") {
	echo "<font color='red'><strong>IAPP INTERNAL ERROR Invalid action encountered [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</strong></font><br />";
	exit;
}

if ($_REQUEST['debugme'] == "Y") {
	echo "......action of selected passed so getting pe record.<br />";
}

$iapCompany = IAP_Get_Company($_REQUEST['co']);
if ($iapCompany < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR retreiving the company record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapPClose = IAP_Get_Party_Closes($_REQUEST['pe']);
if ($iapPClose < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive close record for the selected party.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapParty = IAP_Get_PartyEvent_By_Id($iapPClose['pc_pe_id']);
if ($iapParty < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected party.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapHostess = IAP_Get_Customer_By_No($iapPClose['pc_hostess']);
if ($iapHostess < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the hostess.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$IAPHdr = array();
$IAPHdr[] = str_pad("It's A Party DSR Party Close Report", 90, " ", STR_PAD_BOTH);
$IAPHdr[] = " ";

$IAPRpt = array();
$IAPRpt[] = " ";
$IAPRpt[] = str_pad($iapCompany['co_name'], 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = str_pad($iapCompany['co_mail_street'], 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = str_pad($iapCompany['co_mail_city'].", ".$iapCompany['co_mail_state']."  ".$iapCompany['co_mail_zip'], 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = str_pad($iapCompany['co_email'], 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = str_pad($iapCompany['co_phone'], 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = " ";
$IAPRpt[] = str_pad($iapHostess['cust_name'], 90, " ", STR_PAD_RIGHT);
if (!empty($iapHostess['cust_street'])) {
	$IAPRpt[] = str_pad($iapHostess['cust_street'], 90, " ", STR_PAD_RIGHT);
}
if (!empty($iapHostess['cust_city'])
or  !empty($iapHostess['cust_state'])
or  !empty($iapHostess['cust_zip'])) {
	$IAPRpt[] = str_pad($iapHostess['cust_city'].", ".$iapHostess['cust_state']."  ".$iapHostess['cust_zip'], 90, " ", STR_PAD_RIGHT);
}
if (!empty($iapHostess['cust_phone'])) {
	$IAPRpt[] = str_pad($iapHostess['cust_phone'], 90, " ", STR_PAD_RIGHT);
}
$IAPRpt[] = " ";
$IAPRpt[] = str_pad("Party Number: ".trim($iapParty['pe_party_no'])." Date Of Party: ".date("m/d/Y", strtotime($iapParty['pe_date'])), 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = " ";
$IAPRpt[] = " ";

$iapSales = IAP_Get_Sale_By_PE($iapPClose['pc_pe_id']);
if ($iapSales < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR retreiving sales for party record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$IAPRpt[] = str_pad(iap_sale_head(), 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = str_pad(iap_sale_head2(), 90, " ", STR_PAD_RIGHT);
foreach($iapSales as $iapS) {
	if ($iapS['sale_customer'] != $iapPClose['pc_hostess']) {
		$IAPRpt[] = str_pad(iap_format_sale($iapS['cust_name'], $iapS['sale_net'], $iapS['sale_shipping'], $iapS['sale_sales_tax'], $iapS['sale_total_amt']), 90, " ", STR_PAD_RIGHT);
	}
}

$IAPRpt[] = " ";
$IAPRpt[] = str_pad(iap_format_totals("Total Customer Purchases", $iapPClose['pc_customer_sales']), 90, " ", STR_PAD_RIGHT);

if ($iapPClose['pc_add_hostess'] == "Y") {
	$IAPRpt[] = " ";
	$IAPRpt[] = str_pad("The following hostess purchases were used to determine the award:", 90, " ", STR_PAD_RIGHT);
	$IAPRpt[] = " ";
	foreach($iapSales as $iapS) {
		if ($iapS['sale_customer'] == $iapPClose['pc_hostess']) {
			$IAPRpt[] = str_pad(iap_format_sale($iapS['cust_name'], $iapS['sale_net'], $iapS['sale_shipping'], $iapS['sale_sales_tax'], $iapS['sale_total_amt']), 90, " ", STR_PAD_RIGHT);
		}
	}
}
$IAPRpt[] = " ";
$IAPRpt[] = str_pad(iap_format_totals("Total Hostess Purchases", $iapPClose['pc_hostess_purchases']), 90, " ", STR_PAD_RIGHT);

$IAPRpt[] = " ";
$n = $iapPClose['pc_customer_sales'];
if ($iapPClose['pc_add_hostess'] == "Y") {
	$n = $n + $iapPClose['pc_hostess_purchases'];
}
$IAPRpt[] = str_pad(iap_format_totals("Total Net Sales", $n), 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = " ";
$IAPRpt[] = str_pad(iap_format_totals("Award Percent", ($iapPClose['pc_award_percentage'] * 100)), 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = " ";
$IAPRpt[] = str_pad(iap_format_totals("Hostess Award", $iapPClose['pc_award_amount']), 90, " ", STR_PAD_RIGHT);
$IAPRpt[] = " ";
$IAPRpt[] = " ";

if ($iapPClose['pc_add_hostess'] == "N") {
	$IAPRpt[] = " ";
	$IAPRpt[] = str_pad("The following hostess purchases can be used as part of the award:", 90, " ", STR_PAD_RIGHT);
	$IAPRpt[] = " ";
	$IAPRpt[] = str_pad(iap_sale_head(), 90, " ", STR_PAD_RIGHT);
	$IAPRpt[] = str_pad(iap_sale_head2(), 90, " ", STR_PAD_RIGHT);

	foreach($iapSales as $iapS) {
		if ($iapS['sale_customer'] == $iapPClose['pc_hostess']) {
			$IAPRpt[] = str_pad(iap_format_sale($iapS['cust_name'], $iapS['sale_net'], $iapS['sale_shipping'], $iapS['sale_sales_tax'], $iapS['sale_total_amt']), 90, " ", STR_PAD_RIGHT);
		}
	}
	$IAPRpt[] = " ";
	$IAPRpt[] = str_pad(iap_format_totals("Total Hostess Purchases", $iapPClose['pc_hostess_purchases']), 90, " ", STR_PAD_RIGHT);
}

$IAPRpt[] = " ";
$IAPRpt[] = " ";
$IAPRpt[] = str_pad("Prepared ".date("m/d/y", strtotime("now"))." exclusively for ".$iapHostess['cust_name'], 90, " ", STR_PAD_BOTH);
$IAPRpt[] = str_pad("copyright 2016 by Litehaus Consulting. all rights reserved", 90, " ", STR_PAD_BOTH);



/*
pc_company
pc_pe_id
pc_hostess
pc_close_date
pc_customer_sales
pc_hostess_purchases
pc_add_hostess
pc_award_percentage
pc_award_amount
pc_comments
pc_complete
pc_changed
pc_changed_by
*/

$t = $iapHostess['cust_name']." Party Close Report";
if (LHC_Generate_PDF($t, "Party Close Report", $IAPHdr, $IAPRpt, "P") < 0) {
	echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot generate close report for ".$iapHostess['cust_name']." [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/". __LINE__."</font><br />";
	exit;
}

return;



?>