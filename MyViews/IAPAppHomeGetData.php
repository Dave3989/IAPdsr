<?php

function AppHomeGetDate() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	
}

// Customers

function AppHomeCustomers() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In AppHomeCustomers<br />";
    }

/*
SELECT year(`cust_added`), count(`cust_company`)
FROM `iap_customers` 
WHERE `cust_company` = 5
group by year(`cust_added`)
order by year(`cust_added`)
*/

    $iapPass['table'] = "cust";
    $iapPass['cols'] = "YEAR(cust_added) AS cust_year, COUNT(cust_company) AS cust_count";
    $iapPass['where'] = "cust_company = ".$_REQUEST['CoId'];
    $iapPass['group'] = "YEAR(cust_added)";
    $iapPass['order'] = "YEAR(cust_added)";
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}

	$totalCusts = 0;
	$currCusts = 0;
	if ($ret['numrows'] > 0) {
		$custRecs = (array) $iapRet['data'];
		$currDate = new DateTime('NOW');
		$thisYear = $currDate->format('Y');
		foreach($custRecs as $cr) {
			$totalCusts = $cr['cust_count'];
			if ($cr['cust_year'] == $thisYear) {
				$currCusts = $cr['cust_count'];
			}
		}
	} 
    return(array("allCusts" => $totalCusts, "currCusts" => $currCusts));
}

function AppHomeTopCusts() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In AppHomeTopCusts<br />";
    }

/*
SELECT cust_no, `cust_name`, `cust_phone`, `cust_email`, sum(sale_net), max(sale_date)
FROM `iap_customers` 
join iap_sales on sale_customer = cust_no 
WHERE `cust_company` = 5
group by `cust_no`
order by sum(sale_net) desc
Limit 25
*/
    $iapPass['table'] = "cust";
    $iapPass['cols'] = "cust_no, `cust_name`, `cust_phone`, `cust_email`, SUM(sale_net), MAX(sale_date)";
    $iapPass['join']['table'] = "sale";
    $iapPass['join']['on'] = "sale_customer = cust_no";
    $iapPass['where'] = "cust_company = ".$_REQUEST['CoId'];
    $iapPass['group'] = "cust_no";
    $iapPass['order'] = "SUM(sale_net)";
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	if ($ret['numrows'] == 0) {
		return NULL;
	}
	$custRecs = (array) $iapRet['data'];
	return($custRecs);
}

// Inventory

function AppHomeInventoryByValue() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In AppHomeInventory<br />";
    }

/*
SELECT sum(inv_on_hand), sum(inv_on_hand * prc_cost_unit), sum(inv_on_hand * prc_price)
FROM `iap_inventory`
join iap_supplier_prices ON prc_item_code = inv_item_code
						AND prc_effective_until = "2099-12-31"
WHERE `inv_company` = 5

*/
    $iapPass['table'] = "inv";
    $iapPass['cols'] = "SUM(inv_on_hand) AS inv_on_hand, SUM(inv_on_hand * prc_cost_unit) AS inv_cost, SUM(inv_on_hand * prc_price) AS inv_price";
    $iapPass['join']['table'] = "supprc";
    $iapPass['join']['on'] = "prc_item_code = inv_item_code AND prc_effective_until = '2099-12-31'";
    $iapPass['where'] = "inv_company = ".$_REQUEST['CoId'];
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	if ($ret['numrows'] == 0) {
		return NULL;
	}
	$invRecs = (array) $iapRet['data'];
	return($invRecs);
}

function AppHomeInventoryByCat() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

    if ($_REQUEST['debugme'] == "Y") {
        echo ">>>In AppHomeInventory<br />";
    }

/*
SELECT `code_value`, sum(inv_on_hand), sum(inv_on_hand * prc_cost_unit), 
	   sum(inv_on_hand * prc_price)
FROM `iap_inventory`
join iap_supplier_prices ON prc_item_code = inv_item_code
						AND prc_effective_until = "2099-12-31"
join iap_supplier_codes on code_code = prc_cat_code
WHERE `inv_company` = 5
group by prc_cat_code
order by prc_cat_code

*/
    $iapPass['table'] = "inv";
    $iapPass['cols'] = "code_value, SUM(inv_on_hand) AS prod_on_hand, SUM(inv_on_hand * prc_cost_unit) AS prod_cost, SUM(inv_on_hand * prc_price) AS prod_price";
    $iapPass['join']['table'] = "supprc";
    $iapPass['join']['on'] = "prc_item_code = inv_item_code AND prc_effective_until = '2099-12-31'";
    $iapPass['join']['table'] = "code";
    $iapPass['join']['on'] = "code_code = prc_cat_code";
    $iapPass['where'] = "inv_company = ".$_REQUEST['CoId'];
    $iapPass['group'] = "prc_cat_code";
    $iapPass['order'] = "prc_cat_code";
    $iapRet = (array) IAP_Get_Rows($iapPass);
	if ($iapRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-1);
	}
	if ($ret['numrows'] == 0) {
		return NULL;
	}
	$invRecs = (array) $iapRet['data'];
	return($invRecs);
}

// Purchases





// Sales





// Non-Sale Expenses





?>