<?php
function IAP_Which_Catalog($iapType, $iapOrg, $iapItem) {
	if ($iapType == "D") {
		$w = "cat_description) = '".strtolower($iapItem)."' ";
	} else {
		$w = "cat_item_code) = '".strtolower($iapItem)."' ";
	}

	$s = "SELECT 'SUPPLIER' as supp_source, iap_supplier_catalog.cat_supplier_id AS supp_src, iap_supplier_catalog.cat_item_code AS supp_cat FROM iap_supplier_catalog ".
		"WHERE LOWER(iap_supplier_catalog.".$w.
		"UNION ".
		"SELECT 'COMPANY' as co_source, iap_catalog.cat_company, iap_catalog.cat_item_code AS co_cat FROM iap_catalog ".
		"WHERE iap_catalog.cat_company = ".$iapOrg." AND LOWER(iap_catalog.".$w;
	$iapRet = IAPProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		return(-1);
	}
	if ($iapRet['numrows'] == 0) {
		return(NULL);
	}
	$iapCatRec = (array) $iapRet['data'][0];
	if ($iapCatRec['supp_source'] == "SUPPLIER")  {
		return($iapCatRec['supp_src']);
	} else {
		return("CO");
	}
}



error_reporting(E_ERROR | E_PARSE);

if (isset($_REQUEST['iapType'])) {
	$iapType = $_REQUEST['iapType'];
	$iapOrg = $_REQUEST['iapOrg'];
	$iapKey = $_REQUEST['iapKey'];
} else {
	$in = file('php://input');
	$inarray = urldecode(implode($in));
	$parms = explode("|", $inarray);
	if (count($parms) != 3) {
		echo "-3";
		return;
	}

	$iapType = $parms[0];
	$iapOrg = $parms[1];
	$iapKey = $parms[2];
}

$MyPath = str_replace("\\", "/", dirname(__FILE__));
$MyP = explode("/", $MyPath);
for ($i = 0; $i < count($MyP); $i++) {
	$ln = array_pop($MyP);
	$lnsm = strtolower($ln);
	if (strpos($lnsm, "iap") !== FALSE
	or strpos($lnsm, "iapdsr") !== FALSE) {
//		$LHCPath = implode("/", $MyP);
		array_push($MyP, $ln);
		$iapPath = implode("/", $MyP);
		break;
	}
}

if (!defined('ABSPATH')) {
	define('ABSPATH', $iapPath.'/');
}

switch($iapType) {

// Customers
	case "C#":
		$t = " AND cust_no = ".strval($iapKey);
		break;
	case "CE":
		$t = " AND LOWER(cust_email) = '".strtolower($iapKey)."'";
		break;
	case "CN":
		$t = " AND LOWER(cust_name) = '".strtolower($iapKey)."'";
		break;
	case "CP":
		$t = " AND cust_phone > '' AND cust_phone = '".$iapKey."'";
		break;

// Codes
	case "D#":															// Item Category Codes
	case "DN":
		$Code_Key = explode("~", $iapKey);
		$t = " AND code_type = '".$Code_Key[0]."' AND LOWER(code_code) = '".strtolower($Code_Key[1])."'";
		break;

// Events/Parties
	case "E#":															// Party/Event Requests
	case "EP":
	case "EE":
		$t = " AND iap_party_events.pe_id = '".strval($iapKey)."'";
		break;
	case "EA":				// DO NOT USE This Yet!						// All Events
		$t = "";
		break;
	case "EV":															// One Event
		$t = " AND ev_id = ".strval($iapKey);
		break;

// Gift Certificates
	case "G#":
		$t = " AND iap_gift_certificates.gc_id = '".strval($iapKey)."'";
		break;

// Items
	case "I#":
		$Item_Key = explode("~", $iapKey);
		if (empty($Item_Key[1])) {
			$Item_Key[1] = date("Y-m-d", strtotime("now"));
		}
		$parms = explode("|", $Item_Key[0]);
		$cat = $parms[0];
		if ($cat == 0) {
			require_once($iapPath."/IAPDBServices.php");
			$cat = IAP_Which_Catalog("#",$iapOrg, strtoupper($parms[1]));
			if ($cat < 0) {
				echo "-1";
				return;
			}
			if ($cat == NULL) {
				echo "0";
				return;
			}
		}

		$t = " AND UPPER(cat_item_code) = '".strtoupper($parms[1])."'";
		break;
	case "IA":
		$Item_Key[1] = date("Y-m-d", strtotime("now"));
		$t = "";
		break;
	case "IN":
		$Item_Key = explode("~", $iapKey);
		if (empty($Item_Key[1])) {
		    $Item_Key[1] = date("Y-m-d", strtotime("now"));
		}
		$parms = explode("|", $Item_Key[0]);
		$cat = $parms[0];
		if ($cat == 0) {
			require_once($iapPath."/IAPDBServices.php");
			$cat = IAP_Which_Catalog("D",$iapOrg, strtoupper($parms[1]));
			if ($cat < 0) {
				echo "-1";
				return;
			}
			if ($cat == NULL) {
				echo "0";
				return;
			}
		}

		$t = " AND LOWER(cat_description) = '".strtolower($Item_Key[0])."'";
		break;

// Journal
	case "J#":
		$t = " AND jrnl_id = '".strval($iapKey)."'";
		break;
	case "JD":
		$t = " AND LOWER(jrnl_description) = '".strtolower($iapKey)."'";
		break;

// Purchases
	case "P#":
		$t = " AND pur_id = '".strval($iapKey)."'";
		break;
	case "PO":
		$t = " AND pur_order = '".strval($iapKey)."'";
		break;

// Purchase Detail
	case "PS":
//		$iap18mo = date('Y-m', strtotime('-18 months'))."-01";
		$iapLoDate = "2010-01-01";
		$t = " AND purdet_item = '".$iapKey."' AND purdet_date > '".$iapLoDate."'";
		break;


// Sales
	case "S#":
		$t = " AND sale_id = ".strval($iapKey);
		break;
	case "SC":
		$t = " AND sale_peid = ".strval($iapKey)." AND iap_party_events.pe_id = iap_sales.sale_peid";
		break;
	case "SI":
		$t = " AND sale_customer = ".strval($iapKey);
		break;
	case "SP":
		$t = " AND sale_peid = ".strval($iapKey)." AND iap_customers.cust_no = iap_sales.sale_customer";
		break;
		
// Sales Detail
	case "SD":
		$t = " AND saledet_sid = ".strval($iapKey);
		break;
	case "SS":
//		$iap18mo = date('Y-m', strtotime('-18 months'))."-01";
		$iapLoDate = "2010-01-01";
		$parms = explode("~", $iapKey);
		$cat = $parms[0];
		$t = " AND UPPER(saledet_item_code) = '".strtoupper($parms[1])."' AND sale_date > '".$iapLoDate."'";
		break;
	case "S2":
		$t = " AND sale_customer = ".strval($iapKey);
		break;

// Pricing
	case "$X":
		$k = explode("|", $iapKey);
		$e = $k[2];
		if ($e == "") {
			$e = date("Y-m-d");
		}



//		$cat = $k[0];



		$cat = "CO";
		$t = " AND UPPER(prc_item_code) = '".strtoupper($k[1])."' AND prc_effective >= '".$e."' AND prc_effective_until <= '".$e."' ";
		break;
	case "$>":
		$k = explode("|", $iapKey);
		$e = $k[2];
		if ($e == "") {
			$e = date("Y-m-d");
		}
		$cat = $k[0];
		$t = " AND UPPER(prc_item_code) = '".strtoupper($k[1])."' AND prc_effective >= '".$e."' ";
		break;

// Supplier
	case "*#":
		$t = " supplier_id = ".$iapKey;
		break;

	default:
		echo "-2";
		return(-2);
}

switch(substr($iapType, 0, 1)) {
	case "C":
		$s = "SELECT iap_customers.*, iap_avalara_sales_tax.tax_region_name, iap_avalara_sales_tax.tax_combined_rate FROM iap_customers ".
			"LEFT JOIN iap_avalara_sales_tax ON iap_avalara_sales_tax.tax_zip_code = iap_customers.cust_zip ".
			"WHERE cust_company = ".$iapOrg.$t;
		break;
	case "D":
		$s = "SELECT * FROM iap_codes WHERE code_company = ".$iapOrg.$t;
		break;
	case "E":
		if ($iapType == "EA" 
		or  $iapType == "EV") {
			$s = "SELECT iap_calendar.*, iap_cal_repeating.* from iap_calendar 
				  left join iap_cal_repeating on iap_cal_repeating.cr_id = iap_calendar.ev_id 
				  where ev_account = ".$iapOrg.$t;
		} else {
			$s = "SELECT iap_party_events.*, iap_avalara_sales_tax.tax_region_name, iap_avalara_sales_tax.tax_combined_rate FROM iap_party_events ".
				"LEFT JOIN iap_avalara_sales_tax ON iap_avalara_sales_tax.tax_zip_code = iap_party_events.pe_zip ".
				"WHERE iap_party_events.pe_company = ".$iapOrg.$t;
		}
		break;
	case "G":
		$s = "SELECT iap_gift_certificates.* FROM iap_gift_certificates ".
			 "WHERE iap_gift_certificates.gc_company = ".$iapOrg.$t;
		break;
	case "I":
		$iapDate = date("Y-m-d", strtotime($Item_Key[1]));
		if ($cat == "CO") {
			$s = "SELECT 'CO' as SUPPID, iap_catalog.*, iap_inventory.*, iap_prices.*, iap_codes.* FROM iap_catalog ".
				"LEFT JOIN iap_inventory ON iap_inventory.inv_company = iap_catalog.cat_company AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_catalog.cat_item_code) ".
				"LEFT JOIN iap_prices ON iap_prices.prc_company = iap_catalog.cat_company AND UPPER(iap_prices.prc_item_code) = UPPER(iap_catalog.cat_item_code) ".
				"LEFT JOIN iap_codes ON iap_codes.code_company = iap_catalog.cat_company AND iap_codes.code_type = 'cat' AND iap_codes.code_code = iap_prices.prc_cat_code ".
				"WHERE iap_catalog.cat_company = '".$iapOrg."' ".$t." AND iap_prices.prc_effective <= '".$iapDate."' AND prc_effective_until >= '".$iapDate."' AND UPPER(iap_prices.prc_item_code) = UPPER(iap_catalog.cat_item_code) ".
				"ORDER BY iap_catalog.cat_item_code";
		} else {
			$s = "SELECT 'SUPP' as SUPPID, iap_supplier_catalog.*, iap_inventory.*, iap_supplier_prices.*, iap_supplier_codes.* FROM iap_supplier_catalog ".
				"LEFT JOIN iap_inventory ON iap_inventory.inv_company = '".$iapOrg."'  AND UPPER(iap_inventory.inv_item_code) = UPPER(iap_supplier_catalog.cat_item_code) ".
				"LEFT JOIN iap_supplier_prices ON iap_supplier_prices.prc_supplier_id = iap_supplier_catalog.cat_supplier_id AND UPPER(iap_supplier_prices.prc_item_code) = UPPER(iap_supplier_catalog.cat_item_code) ".
				"LEFT JOIN iap_supplier_codes ON iap_supplier_codes.code_supplier_id = iap_supplier_catalog.cat_supplier_id AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code ".
				"WHERE cat_supplier_id = ".$cat.$t." AND iap_supplier_prices.prc_effective <= '".$iapDate."' AND iap_supplier_prices.prc_effective_until >= '".$iapDate."' AND UPPER(iap_supplier_prices.prc_item_code) = UPPER(iap_supplier_catalog.cat_item_code) ".
				"ORDER BY iap_supplier_catalog.cat_item_code";
		}
		break;
	case "J":
		$s = "SELECT * FROM iap_journal WHERE jrnl_company = ".$iapOrg.$t;
		break;
	case "P":
		switch($iapType) {
			case "P#":
				$s = "SELECT * FROM iap_purchases WHERE pur_company = ".$iapOrg.$t;
				break;
			case "PO":
				$s = "SELECT pur_date FROM iap_purchases WHERE pur_company = ".$iapOrg.$t;
				break;
			case "PS":
				$s = "SELECT iap_purchase_detail.*, iap_purchases.pur_vendor, iap_purchases.pur_order ".
					 " FROM iap_purchase_detail".
					 " JOIN iap_purchases on pur_company = purdet_company AND pur_id = purdet_purid".
					 " WHERE pur_company = ".$iapOrg.$t.
					 " ORDER BY purdet_date Desc";
				break;
		}
		break;
	case "S":
		switch($iapType) {
			case "S#":
				$s = "SELECT iap_sales.* ,".
					 " iap_customers.cust_name, iap_customers.cust_state, iap_customers.cust_zip, iap_customers.cust_followup_consultant, iap_customers.cust_followup_party,".
					 " iap_party_events.pe_date, iap_party_events.pe_party_no, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no".
					 " FROM iap_sales ".
					 " LEFT JOIN iap_customers ON iap_customers.cust_company = iap_sales.sale_company AND iap_customers.cust_no = iap_sales.sale_customer ".
					 " LEFT JOIN iap_party_events ON pe_company = sale_company AND pe_id = iap_sales.sale_peid".
					 " WHERE sale_company = ".$iapOrg.$t;
				break;
			case "SC":
				$s = "SELECT iap_sales.*, iap_customers.* FROM iap_sales ".
					 " LEFT JOIN iap_party_events ON iap_party_events.pe_company = iap_sales.sale_company AND iap_party_events.pe_id = iap_sales.sale_peid ".
					 " WHERE sale_company = ".$iapOrg.$t.
					 " ORDER BY iap_sales.sale_date";
				break;
			case "SD":
				$s = "SELECT iap_sales_detail.*, iap_catalog.cat_description as CO_DESC, iap_supplier_catalog.cat_description as SUPP_DESC, iap_supplier_catalog.cat_supplier_id FROM iap_sales_detail ".
					 " LEFT JOIN iap_catalog ON iap_catalog.cat_company = iap_sales_detail.saledet_company AND UPPER(iap_catalog.cat_item_code) = UPPER(iap_sales_detail.saledet_item_code)".
					 " LEFT JOIN iap_supplier_catalog ON iap_supplier_catalog.cat_supplier_id = iap_sales_detail.saledet_item_source AND UPPER(iap_supplier_catalog.cat_item_code) = UPPER(iap_sales_detail.saledet_item_code)".
					 " WHERE saledet_company = ".$iapOrg.$t;
				break;
			case "SI":

	echo "SI Not Changed";
	return(-2);



//				if ($cat == "CO") {
					$s = "SELECT iap_sales.sale_id, iap_sales.sale_peid, iap_sales.sale_date, iap_sales.sale_type, ".
						 " iap_sales_detail.saledet_item_code, iap_sales_detail.saledet_quantity, iap_sales_detail.saledet_price, iap_sales_detail.saledet_total_cost, iap_sales_detail.saledet_total_price, ".
						 " iap_catalog.cat_item_code, iap_catalog.cat_description, iap_party_events.pe_id, iap_party_events.pe_date, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no".
						 " FROM iap_sales".
						 " LEFT JOIN iap_sales_detail ON saledet_company = sale_company AND saledet_sid = sale_id".
						 " LEFT JOIN iap_catalog ON cat_company = sale_company AND UPPER(cat_item_code) = UPPER(saledet_item_code)".
						 " LEFT JOIN iap_party_events ON pe_company = sale_company AND pe_id = iap_sales.sale_peid".
						 " WHERE sale_company = ".$iapOrg.$t.
						 " ORDER BY iap_sales_detail.saledet_sid, iap_sales_detail.saledet_item_code";
					break;
/*
				} else {
					$s = "SELECT iap_sales.sale_id, iap_sales.sale_peid, iap_sales.sale_date, iap_sales.sale_type, ".
						 " iap_sales_detail.saledet_item_code, iap_sales_detail.saledet_quantity, iap_sales_detail.saledet_price, iap_sales_detail.saledet_total_cost, iap_sales_detail.saledet_total_price, ".
						 " iap_catalog.cat_item_code, iap_catalog.cat_description, iap_party_events.pe_id, iap_party_events.pe_date, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no".
						 " FROM iap_sales".
						 " LEFT JOIN iap_sales_detail ON saledet_company = sale_company AND saledet_sid = sale_id".
						 " LEFT JOIN iap_supplier_catalog ON cat_supplier_id = ".$cat." AND UPPER(cat_item_code) = UPPER(saledet_item_code)".
						 " LEFT JOIN iap_party_events ON pe_company = sale_company AND pe_id = iap_sales.sale_peid".
						 " WHERE sale_company = ".$iapOrg.$t.
						 " ORDER BY iap_sales_detail.saledet_sid, iap_sales_detail.saledet_item_code";
					break;					
				}
*/
			case "SP":
				$s = "SELECT iap_sales.*, iap_customers.* FROM iap_sales ".
					 " LEFT JOIN iap_customers ON iap_customers.cust_company = iap_sales.sale_company AND iap_customers.cust_no = iap_sales.sale_customer ".
					 " WHERE sale_company = ".$iapOrg.$t.
					 " ORDER BY iap_customers.cust_name";
				break;
			case "SS":
				if ($cat == "CO") {
					$s = "SELECT iap_sales_detail.*, iap_catalog.cat_description, iap_catalog.cat_item_code, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_customers.cust_name".
						 " FROM iap_sales_detail".
						 " JOIN iap_catalog on cat_company = saledet_company AND UPPER(cat_item_code) = UPPER(saledet_item_code)".
						 " JOIN iap_sales ON sale_company = saledet_company AND sale_id = saledet_sid".
						 " JOIN iap_party_events ON pe_company = saledet_company AND pe_id = sale_peid".
						 " JOIN iap_customers ON cust_company = saledet_company AND cust_no = iap_sales_detail.saledet_customer_no".
						 " WHERE iap_sales_detail.saledet_company = ".$iapOrg.$t.
						 " ORDER BY iap_sales.sale_date Desc";
				} else {
					$s = "SELECT iap_sales_detail.*, iap_supplier_catalog.cat_description, iap_supplier_catalog.cat_item_code, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_customers.cust_name".
						 " FROM iap_sales_detail".
						 " JOIN iap_supplier_catalog ON cat_supplier_id = ".$cat." AND UPPER(cat_item_code) = UPPER(saledet_item_code)".
						 " JOIN iap_sales ON sale_company = saledet_company AND sale_id = saledet_sid".
						 " JOIN iap_party_events ON pe_company = saledet_company AND pe_id = sale_peid".
						 " JOIN iap_customers ON cust_company = saledet_company AND cust_no = iap_sales_detail.saledet_customer_no".
						 " WHERE iap_sales_detail.saledet_company = ".$iapOrg.$t.
						 " ORDER BY iap_sales.sale_date Desc";
					
				}
				break;
			case "S2":
				$s = "SELECT iap_sales_detail.*, ".
					"iap_catalog.cat_description AS CO_description, iap_catalog.cat_item_code as CO_item_code, ".
					"iap_supplier_catalog.cat_description as SUPP_description,   iap_supplier_catalog.cat_item_code as SUPP_Item_code, ".
					"iap_sales.sale_peid, iap_sales.sale_date, iap_party_events.pe_sponsor, iap_party_events.pe_type, iap_party_events.pe_party_no".
					" FROM iap_sales_detail".

					" LEFT JOIN iap_catalog on cat_company = saledet_company AND UPPER(iap_catalog.cat_item_code) = UPPER(saledet_item_code)".
					" LEFT JOIN iap_supplier_catalog on cat_supplier_id = saledet_item_source AND UPPER(iap_supplier_catalog.cat_item_code) = UPPER(saledet_item_code) ".
					" JOIN iap_sales ON sale_company = saledet_company AND sale_id = saledet_sid".
					" JOIN iap_party_events ON pe_company = saledet_company AND pe_id = sale_peid".
					" WHERE iap_sales_detail.saledet_company = ".$iapOrg.$t.
					" ORDER BY iap_sales.sale_date Desc, sale_peid, saledet_sid";
				break;
		}
		break;
	case "$":
		if ($cat == "CO") {
			$s = "SELECT iap_prices.*, iap_codes.*, ".
				"(SELECT code_value FROM iap_codes WHERE code_type = 'cat' AND code_code = iap_prices.prc_prev_cat_code) as prev_cat ".
				"FROM iap_prices ".
				"JOIN iap_codes ON iap_codes.code_company = iap_prices.prc_company AND iap_codes.code_type = 'cat' AND iap_codes.code_code = iap_prices.prc_cat_code ".
				"WHERE iap_prices.prc_company = '".$iapOrg."'".$t.
				"ORDER BY iap_prices.prc_effective Desc";
		} else {
			$s = "SELECT iap_supplier_prices.*, iap_supplier_codes.*, ".
				"(SELECT code_value FROM iap_supplier_codes ".
				   "WHERE  code_supplier_id = iap_supplier_prices.prc_supplier_id ".
				   "AND iap_supplier_codes.code_code = iap_supplier_prices.prc_prev_cat_code) as prev_cat ".
				"FROM iap_supplier_prices ".
				"JOIN iap_supplier_codes ON iap_supplier_codes.code_supplier_id = iap_supplier_prices.prc_supplier_id AND iap_supplier_codes.code_code = iap_supplier_prices.prc_cat_code ".
				"WHERE iap_supplier_prices.prc_supplier_id = ".$cat.$t.
				"ORDER BY iap_supplier_prices.prc_effective Desc";
		}
		break;
	case "*":
		$s = "SELECT * FROM iap_suppliers WHERE ".$t;
		break;
	default:
		echo "-1";
		return(-1);
}

require_once($iapPath."/IAPDBServices.php");
$iapRet = iapProcessMySQL("select", $s);
if ($iapRet['retcode'] < 0) {
	echo "-1";
	return;
}
if ($iapRet['numrows'] == 0){
	echo "0";
	return;
}

if (substr($iapType, 1, 1) == "A"
or (substr($iapType, 0, 1) == "E" and substr($iapType, 1, 1) == "A")
or (substr($iapType, 0, 1) == "P" and substr($iapType, 1, 1) != "#" and substr($iapType, 1, 1) != "O") 
or (substr($iapType, 0, 1) == "S" and substr($iapType, 1, 1) != "#") 
or  substr($iapType, 0, 1) == "$") {
	$d = $iapRet['data'];
} else {
	$d = $iapRet['data'][0];
}

if ($iapType == "EA" 
or  $iapType == "EV") {
	require_once($iapPath."/AJAX/IAPCalendar/IAPFormatEvent.php");
	$d = iapFormatEvent($d);
}
if ($iapType == "P#") {

	$s = "SELECT * FROM iap_purchase_detail WHERE purdet_company = ".$iapOrg." AND purdet_purid = '".strval($iapKey)."'";

	$iapRet = iapProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		echo "-1";
		return;
	}
	if ($iapRet['numrows'] == 0){
		echo "0";
		return;
	}

	$dtl = $iapRet['data'];
	$d['purdtl'] = $dtl;
}
/*
if ($iapType == "S#") {

	$s = "SELECT * FROM iap_sales_detail WHERE saledet_company = ".$iapOrg." AND saledet_saleid = '".strval($iapKey)."'";

	$iapRet = iapProcessMySQL("select", $s);
	if ($iapRet['retcode'] < 0) {
		echo "-1";
		return;
	}
	if ($iapRet['numrows'] == 0){
		echo "0";
		return;
	}

	$dtl = $iapRet['data'];
	$d['saledtl'] = $dtl;
}
*/

$iapOut = json_encode($d);
echo $iapOut;
return;

?>