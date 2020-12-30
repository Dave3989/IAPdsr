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


/*
									Party With Pam's Financial Status
									         As  09/15/2016

                                                          MTD                   QTD                    YTD
								  -------			 -------			 -------

Inventory Previous Year End										xxx,xxx

Inventory Purchased				xx,xxx			xx,xxx			xxx,xxx

Inventory Sold					xx,xxx			xx,xxx			xxx,xxx

Inventory Current YTD											xxx,xxx 


Sales Made (Net)					xx,xxx.xx		xx,xxx.xx		xxx,xxx.xx

Cost of Sales 						xx,xxx.xx		xx,xxx.xx		xxx,xxx.xx

Profit From Sales					xx,xxx.xx		xx,xxx.xx		xxx,xxx.xx


Sales Tax Paid					  x,xxx.xx		 x,xxx.xx			 xx,xxx.xx

Sales Tax Reimbursed				  x,xxx.xx		 x,xxx.xx			 xx,xxx.xx

Shipping Paid on Purchases

Shipping Received on Sales

Supplies Purchased

Other Expenses

Miles Traveled

Income/Expense Report









*/







?>