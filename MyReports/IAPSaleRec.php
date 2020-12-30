<?php

function iap_alignment_line($iapOrientation) {

//	$iapRpt[] = str_pad(iap_alignment_line("L"), 120, " ", STR_PAD_RIGHT);

	if ($iapOrientation == "P") {
		$ln = "123456789A123456789B123456789C12345789D123456789E123456789F123456789G123456789H123456789I"; // 90
	} else {
		$ln = "123456789A123456789B123456789C12345789D123456789E123456789F123456789G123456789H123456789I123456789J123456789K12345789L"; // 120
	}
	return($ln);
}

function iap_split_desc($iapDesc) {

	$s = wordwrap($iapDesc, 40, "|", true);
	$o = explode("|", $s);
	return($o);
}

function iap_receipt_head() {

	$c = str_pad("Item Code", 10, " ", STR_PAD_RIGHT);
	$n = str_pad("Description", 40, " ", STR_PAD_RIGHT);
	$s = str_pad("Qty", 5, " ", STR_PAD_LEFT);
	$x = str_pad("Price", 8, " ", STR_PAD_LEFT);
	$t = str_pad("Total", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_receipt_head2() {

	$c = str_pad("---------", 10, " ", STR_PAD_RIGHT);
	$n = str_pad("-----------", 40, " ", STR_PAD_RIGHT);
	$s = str_pad("---", 5, " ", STR_PAD_LEFT);
	$x = str_pad("-----", 8, " ", STR_PAD_LEFT);
	$t = str_pad("-----", 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_format_receipt($iapCode, $iapDesc, $iapQty, $iapPrice, $iapTotal) {

	$c = str_pad($iapCode, 10, " ", STR_PAD_RIGHT);
	$n = str_pad($iapDesc, 40, " ", STR_PAD_RIGHT);
	$s = str_pad($iapQty, 5, " ", STR_PAD_LEFT);
	$x = str_pad($iapPrice, 8, " ", STR_PAD_LEFT);
	$t = str_pad($iapTotal, 10, " ", STR_PAD_LEFT);
	$ln = "   ".$c." ".$n." ".$s. " ". $x. " ". $t;
	return($ln);
}

function iap_format_totals($iapComment, $iapValue) {

	$f = str_pad(" ", 49, " ", STR_PAD_RIGHT);
	$c = str_pad($iapComment, 16, " ", STR_PAD_RIGHT);
	$n = str_pad(number_format($iapValue, 2, '.', ','), 10, " ", STR_PAD_LEFT);
	$ln = "   ".$f." ".$c." ".$n;
	return($ln);
}

function iap_split_warranty($iapWarranty) {

	$s = wordwrap($iapWarranty, 80, "|", true);
	$o = explode("|", $s);
	return($o);
}

function iap_format_warranty($iapWarranty) {

//	$f = str_pad(" ", 5, " ", STR_PAD_RIGHT);
	$c = str_pad($iapWarranty, 80, " ", STR_PAD_RIGHT);
//	$ln = "   ".$f." ".$c;
	return($c);
}




require_once( "IAPReportStart.php" );
if (IAP_Program_Start("R02", "N") < 0) {
	return;
};

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Sales Receipt.<br />";
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

$iapCompany = IAP_Get_Company($_REQUEST['coid']);
if ($iapCompany < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR retreiving the company record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapSale = IAP_Get_Sale($_REQUEST['s']);
if ($iapSale < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive sale record.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapSaleDet = IAP_Get_SaleDet($_REQUEST['s']);
if ($iapSaleDet < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive sales detail.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapCustomer = IAP_Get_Customer_By_No($iapSale['sale_customer']);
if ($iapCustomer < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the customer.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapPE = IAP_Get_PartyEvent_By_Id($iapSale['sale_peid']);
if ($iapPE < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive party/event.[FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$iapHdr = array();
$iapHdr[] = str_pad("Sales Receipt For ".$iapCustomer['cust_name']." On ".date("m/d/Y", strtotime($iapSale['sale_date'])), 90, " ", STR_PAD_BOTH);
$iapHdr[] = " ";

$iapRpt = array();
$iapRpt[] = " ";
$iapRpt[] = str_pad($iapCompany['co_name'], 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad($iapCompany['co_mail_street'], 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad($iapCompany['co_mail_city'].", ".$iapCompany['co_mail_state']."  ".$iapCompany['co_mail_zip'], 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad($iapCompany['co_email'], 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad($iapCompany['co_phone'], 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";
$iapRpt[] = str_pad($iapCustomer['cust_name'], 90, " ", STR_PAD_RIGHT);
if (!empty($iapCustomer['cust_street'])) {
	$iapRpt[] = str_pad($iapCustomer['cust_street'], 90, " ", STR_PAD_RIGHT);
}
if (!empty($iapCustomer['cust_city'])
or !empty($iapCustomer['cust_state'])
or !empty($iapCustomer['cust_zip'])) {
	$iapRpt[] = str_pad($iapCustomer['cust_city'].", ".$iapCustomer['cust_state']."  ".$iapCustomer['cust_zip'], 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";
$r = "Receipt for items purchased";
if ($iapPE['pe_type'] == "E"
or  $iapPe['pe_type'] == "P") {
	$r = $r." at ".$iapPE['pe_sponsor'];
	if (!empty($iapPE['pe_party_no'])) {
		$r = $r."'s party";
	}
}
$r = $r." on ".date("m/d/Y", strtotime($iapSale['sale_date']));
$iapRpt[] = $r;
$iapRpt[] = " ";
$iapRpt[] = str_pad(iap_receipt_head(), 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad(iap_receipt_head2(), 90, " ", STR_PAD_RIGHT);

// $iapRpt[] = str_pad(iap_alignment_line("L"), 120, " ", STR_PAD_RIGHT);

foreach($iapSaleDet as $iapSD) {
	if ($iapSD['SUPP_ID'] > 0) {
		$iapDesc = iap_split_desc($iapSD['SUPP_description']);
	} else {
		$iapDesc = iap_split_desc($iapSD['CO_description']);
	}
	$f = "Y";
	foreach($iapDesc as $iapD) {
		if ($f == "Y") {
			$s = number_format($iapSD['saledet_quantity'], 0, '.', ',');
			$x = number_format($iapSD['saledet_price'], 2, '.', ',');
			$t = number_format($iapSD['saledet_total_price'], 2, '.', ',');
			$iapRpt[] = str_pad(iap_format_receipt($iapSD['saledet_item_code'], $iapD, $s, $x, $t), 90, " ", STR_PAD_RIGHT);
			$f = "N";
		} else {
			$iapRpt[] = str_pad(iap_format_receipt(" ", $iapD, " ", " ", " "), 90, " ", STR_PAD_RIGHT);
		}
	}
}

// $iapRpt[] = str_pad(iap_alignment_line("L"), 120, " ", STR_PAD_RIGHT);

$iapRpt[] = " ";
$iapRpt[] = str_pad(iap_format_totals("Net Sale", $iapSale['sale_net']), 90, " ", STR_PAD_RIGHT);
if ($iapSale['sale_shipping'] > 0) {
	$iapRpt[] = " ";
	$iapRpt[] = str_pad(iap_format_totals("Shipping", $iapSale['sale_shipping']), 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";
$iapRpt[] = str_pad(iap_format_totals("Sales Tax", $iapSale['sale_sales_tax']), 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";
$iapRpt[] = str_pad(iap_format_totals("Total Sale", $iapSale['sale_total_amt']), 90, " ", STR_PAD_RIGHT);

$iapRpt[] = " ";
$iapRpt[] = " ";
$iapRpt[] = str_pad("Thank you for your business!", 90, " ", STR_PAD_BOTH);

$iapRpt[] = " ";
$iapRpt[] = " ";
$iapRpt[] = "<(NP)>";	// New Page
$iapRpt[] = str_pad("NOTICE OF RIGHT TO CANCEL", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad("-------------------------", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";

$iapRpt[] = str_pad("     Date of This Transaction: (SEE DATE AT THE TOP OF THIS RECEIPT)", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";

$iapWarr = iap_split_warranty("You may CANCEL this transaction without any penalty or obligation THREE BUSINESS DAYS from the above date (5 business days for AK resdents).");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";

$iapWarr = iap_split_warranty("If you cancel, any property traded in, any payments made by you under the contract or sate, and any negotiable instrument executed by you will be returned within TEN BUSINESS DAYS following receipt by the seller of your notice, and any security interest arising out of the transaction be canceled.");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";

$iapWarr = iap_split_warranty("If you cancel, you must make available to the seller at your residence, in substantially as good condition as when received, any goods delivered to you under this contract or sale, or you may, if you wish, comply with the instructions of the setter regarding the return shipment of the goods at the seller's expense and risk.");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";

$iapWarr = iap_split_warranty("If you do make the goods available to the setter and the seller does not pick them up within 20 days of the date of your Notice of Cancellation, you may retain or dispose of the goods without any further obligation. If you fail to make the goods available to the seller, or if you agree to return the goods to the seller and fail to do so, then you remain liable for performance of all obligations under the contract.");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";

$iapWarr = iap_split_warranty("To cancel this transaction, mail or deliver a signed and dated copy of this Cancellation Notice or any other written notice, or send a telegram, to your Independent Consultant listed on the front of this receipt NOT LATER THAN MIDNIGHT of the third business day following the date set forth above.");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}
$iapRpt[] = " ";

$iapRpt[] = str_pad("     I HEREBY CANCEL THIS TRANSACTION.", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";

$iapRpt[] = str_pad("          Buyer's Signature: __________________________________ Date: ___________ ", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = " ";
$iapRpt[] = " ";

$iapRpt[] = str_pad("Sales Tax on Shipping", 90, " ", STR_PAD_RIGHT);
$iapRpt[] = str_pad("---------------------", 90, " ", STR_PAD_RIGHT);
$iapWarr = iap_split_warranty("The sales tax on shipping fees varies from state to state. Sales tax will be applied to the total amount of the order in accordance with local tax laws.");
foreach($iapWarr as $iapW) {
	$iapRpt[] = str_pad($iapW, 90, " ", STR_PAD_RIGHT);
}

$t = $iapCustomer['cust_name']." Sales Receipt";
if (IAP_Generate_PDF($t, "Sales Receipt", $iapHdr, $iapRpt, "P") < 0) {
	echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot generate sales receipt for ".$iapCustomer['cust_name']." [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/". __LINE__."</font><br />";
	exit;
}

return;

?>