<?php

function IAP_Get_Table($IAPPass) {
    $_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";
    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In IAP_Get_Table with table of ".$IAPPass['table']." in ".$_REQUEST['runningapp'].".<br />";
    }
    switch($_REQUEST['runningapp']) {
        case "IAP":
            $IAPTbls = array(
                "avtx" => "iap_avalara_sales_tax",
                "iapcal" => "iap_calendar",
                "iapcexc" => "iap_cal_exceptions",
                "iapcrep" => "iap_cal_repeating",
                "ctlg" => "iap_catalog",
                "code" => "iap_codes",
                "comp" => "iap_company",
                "cosup" => "iap_co_suppliers",
                "cous" => "iap_couser",
                "cset" => "iap_catalog_sets",
                "ctbl" => "iap_code_table",
                "cust" => "iap_customers",
                "eoy" => "iap_eoy_balances",
				"exp" => "iap_expenses", 
				"excd" => "iap_expense_codes", 
                "gcapptxs" => "iap_gift_applied_txs",
                "gftcrt" => "iap_gift_certificates",
                "iaphelp" => "iap_help",
                "iaphlvl" => "iap_help_level",
                "iaphnar" => "iap_help_narrative",
                "inv" => "iap_inventory",
                "jrnl" => "iap_journal",
                "parcl" => "iap_party_closes",
                "parev" => "iap_party_events",
                "pur" => "iap_purchases",
                "pdtl" => "iap_purchase_detail",
                "plot" => "iap_purchase_lots",
                "prc" => "iap_prices",
                "prof" => "iap_profiles",
                "sale" => "iap_sales",
                "sdtl" => "iap_sales_detail",
                "sal" => "iap_sales_applied_lots",
                "supp" => "iap_suppliers",
                "supcat" => "iap_supplier_catalog",
                "supcd" => "iap_supplier_codes",
                "supctbl" => "iap_supplier_cdtbl",
                "supprc" => "iap_supplier_prices",
                "supset" => "iap_supplier_sets",
                "tax" => "iap_avalara_sales_tax",
                "avtx" => "iap_avalara_sales_tax",
                "iaptemp" => "iap_temphold",
                "iaptkey" => "iap_temphold_keys"
            );
            break;
        default:
            $IAPTbls = array();	
    }	

    $IAPT = $IAPPass['table'];

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>> Looking for table ".$IAPT."<br>";
        echo ">>>> Found file ".$IAPTbls[$IAPT]."<br>";	
    }

    return($IAPTbls[$IAPT]);
}

?>