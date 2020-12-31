<?php

/*
----------------------------------------------------------------------------------------------------------
SELECT  `ws_item_code` ,  `ws_description` ,  `ws_ss_item_cd` ,  `ws_ss_count` ,  `ws_cat_item` ,  `ws_cat_count` ,  `ws_lot_item` ,  `ws_lot_count` , `ws_physical_count` 
FROM  `iap_xinv_worksheet` 
WHERE  `ws_category` != Standard 1 Inch Inserts"
LIMIT 0 , 3000

-----------------------------------------------------------------------------------------------------------

update iap_catalog set cat_on_hand = `ws_physical_count`
join iap_xinv_worksheet on `ws_item_code` = cat_item_code
where `ws_category` != "Standard 1 Inch Standards"
  and `ws_category` != "Premium 1 Inch Standards"
  and `ws_category` != "Bittie Inserts"
  and `ws_category` != "Bittie Standard Inserts"
  and `ws_category` != "Bittie Premium Inserts"
  and `ws_category` != "Premium Select 1 Inch Inserts"
  and `ws_category` != "Standard Select 1 Inch Inserts"

SELECT  `cat_item_code` ,  `cat_description` ,  `cat_on_hand` , ws_physical_count, SUM( purdet_quantity ) AS pd_qty, sum(saledet_quantity) AS sd_qty 
FROM  `iap_catalog` 
LEFT JOIN iap_xinv_worksheet ON ws_item_code = cat_item_code
LEFT JOIN iap_purchase_detail ON purdet_item = cat_item_code
LEFT JOIN iap_sales_detail ON saledet_item_code = cat_item_code
GROUP BY cat_item_code
LIMIT 0 , 3000

----------------------------------------------------------------------------------------------------------------
SELECT  `cat_item_code` as Item ,  `cat_description` as Description , ws_category as Category,  `cat_on_hand` as CQty , ws_physical_count as WSQty, b.pl_qty as PLQty, c.pd_qty as PDQty, d.sd_qty as SDQty
FROM  `iap_catalog` 

LEFT JOIN iap_xinv_worksheet ON ws_item_code = cat_item_code

LEFT JOIN 
    ( select lot_item_code, SUM( lot_count ) AS pl_qty from iap_purchase_lots
         group by lot_item_code
     ) as b
     on b.lot_item_code = cat_item_code

LEFT JOIN 
    ( select purdet_item, SUM( purdet_quantity ) AS pd_qty from iap_purchase_detail
         group by purdet_item
     ) as c
     on c.purdet_item = cat_item_code

LEFT JOIN
    ( select saledet_item_code, SUM( saledet_quantity ) AS sd_qty from iap_sales_detail
         group by saledet_item_code
     ) as d
     on d.saledet_item_code = cat_item_code

GROUP BY cat_item_code
LIMIT 0 , 3000
-----------------------------------------------------------------------------------------------------------------
SELECT  cat_item_code as Item ,
		cat_description as Description , 
		b.code_value as Category, 
		b.prc_effective as PRCEff,
		b.prc_effective_until as PRCEffUntil,
		cat_on_hand as CQty, 
		(cat_on_hand - pl_qty) as CQtyNoPL,
		b.prc_cost_unit as PRCCost, 
		(prc_cost_unit * (cat_on_hand - pl_qty)) as CValue, 
		c.pl_qty as PLQty, 
		c.pl_cost as PLCost, 
		c.pl_value as PLValue

FROM  `iap_catalog` 

LEFT JOIN iap_xinv_worksheet ON ws_item_code = cat_item_code

LEFT JOIN 
	( SELECT `prc_item_code`, `prc_effective_until`, `prc_effective`, `prc_cost_unit`, code_value
	    FROM `iap_prices` 
		LEFT JOIN iap_codes ON code_type = "cat" AND code_code = `prc_cat_code` 
		WHERE `prc_effective_until` = "2099-12-31"
		ORDER BY `prc_item_code`, `prc_effective_until` 
     ) as b
     on b.prc_item_code = cat_item_code

LEFT JOIN 
    ( select lot_item_code, SUM(lot_count) AS pl_qty, lot_cost as pl_cost, lot_cost * SUM(lot_count) AS pl_value from iap_purchase_lots
         group by lot_item_code
     ) as c
     on c.lot_item_code = cat_item_code

GROUP BY cat_item_code
LIMIT 0 , 3000
-----------------------------------------------------------------------------------------------------------------
SELECT  b.code_value as Category, 
	sum(cat_on_hand) as CQty, 
	sum(cat_on_hand - pl_qty) as CQtyNoPL,
	sum(prc_cost_unit * (cat_on_hand - pl_qty)) as CValue, 
	sum(c.pl_qty) as PLQty,  
	sum(c.pl_value) as PLValue

FROM  `iap_catalog` 

LEFT JOIN iap_xinv_worksheet ON ws_item_code = cat_item_code

LEFT JOIN 
	( SELECT `prc_item_code`, `prc_effective_until`, `prc_effective`, `prc_cost_unit`, code_value
	    FROM `iap_prices` 
		LEFT JOIN iap_codes ON code_type = "cat" AND code_code = `prc_cat_code` 
		WHERE `prc_effective_until` = "2099-12-31"
		ORDER BY `prc_item_code`, `prc_effective_until` 
     ) as b
     on b.prc_item_code = cat_item_code

LEFT JOIN 
    ( select lot_item_code, SUM(lot_count) AS pl_qty, lot_cost as pl_cost, lot_cost * SUM(lot_count) AS pl_value from iap_purchase_lots
         group by lot_item_code
     ) as c
     on c.lot_item_code = cat_item_code

GROUP BY b.code_value
-----------------------------------------------------------------------------------------------------
update iap_customers

join iap_maggiemail_20160212 on `EmailAddress` = cust_email

set cust_newsletter_add_date = DATE_FORMAT(`DateCreated`,"%Y-%m-%d")
-----------------------------------------------------------------------------------------------------
SELECT  `cust_first_name` ,  `cust_last_name` ,  `cust_email` ,  `cust_street` ,  `cust_city` ,  `cust_state` ,  `cust_zip` ,  "",  ""
FROM  `iap_customers` 
WHERE  `cust_email` !=  ""
AND  `cust_newsletter` !=  "N"
AND  `cust_newsletter_add_date` =  "0000-00-00"
LIMIT 0 , 3000
-------------------------------------------------------------------------------------------------------
update iap_customers set `cust_first_name` = SUBSTRING_INDEX(`cust_name`,' ',1)
where `cust_first_name` = ""
-------------------------------------------------------------------------------------------------------
update `iap_customers` 
set `cust_met_date` = concat(SUBSTRING_INDEX(`cust_comments`, '/' , -1 ), "-", SUBSTRING_INDEX(`cust_comments`, '/', 1 ), "-", SUBSTRING_INDEX( SUBSTRING_INDEX(cust_comments, '/', 2 ) , '/' , -1 ))
WHERE cust_comments !=  ""
-------------------------------------------------------------------------------------------------------
cust_company	smallint(6)	No 	 	 
cust_no	smallint(6)	No 	 	 
cust_type	char(1)	No 	C 	C=Customer, P=Prospect, D=Downline  
cust_name	varchar(50)	No 	 	 
cust_first_name	varchar(30)	No 	 	 
cust_last_name	varchar(30)	No 	 	 
cust_street	varchar(50)	No 	 	 
cust_city	varchar(30)	No 	 	 
cust_state	char(2)	No 	 	 
cust_zip	char(10)	No 	 	 
cust_home_phone	varchar(15)	No 	 	 
cust_cell_phone	varchar(15)	No 	 	 
cust_email	varchar(100)	No 	 	 
cust_birthday	char(5)	Yes 	NULL 	 
cust_birthday_event	smallint(6)	No 	0 	 
cust_comments	varchar(250)	No 	 	 
cust_xel_ss	char(5)	No 	 	 
cust_newsletter	char(1)	No 	Y 	 
cust_newsletter_add_date	date	No 	0000-00-00 	 
cust_followup_consultant	char(1)	No 	N 	 
cust_followup_party	char(1)	No 	N 	 
cust_followup_set	date	No 	 	 
cust_met_at_pe	smallint(6)	No 	0 	 
cust_notes	varchar(500)	No 	 	 
cust_changed	datetime	No 	 	 
cust_changed_by	smallint(6)	No 	 
-------------------------------------------------------------------------------------------------------
*/

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In ExportCustomers with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("413") < 0) {
	return;
};

if ($_REQUEST['action'] == 'p413ret') {

// get customer 

	if ($_REQUEST['CSTATUS'] == "NEW") {
		IAP_Remove_Savearea("IAP134CM", $_REQUEST['IAPUID']);
		$iapC = (array) IAP_Build_New_Row(array("table" => "cust"));
		$iapCustomer = $iapC[0];
		if ($iapCustomer < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR creating customer savearea [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$iapRet = IAP_Create_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	} else {
		$iapCustomer = (array) IAP_Get_Savearea("IAP134CM", $_REQUEST['IAPUID']);
		if (empty($iapCustomer)) {
		    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		    return;
		}







	$iapRet = IAP_Update_Savearea("IAP134CM", $iapCustomer, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot update savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapOrigAction = $_REQUEST['origaction'];

	$DivSelect = "block";
	$DivShow = "block";	

} else {

	if (IAP_Remove_Savearea("IAP134CM") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the customer savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}





	$DivSelect = "block";
	$DivShow = "none";
}

$iapSelEna = "readonly";







$iapReadOnly = IAP_Format_Heading("Export Customers");

?>


<p style='text-indent:50px; width:100%'>

<form name='cdetform' action='?page_id=134&action=p134retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<table>
<tbody>

<tr><td style='width:5%'>&nbsp;</td><td style='width:14%'>&nbsp;</td><td style='width:81%'>&nbsp;</td></tr>












</tbody></table>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="CUPDATETYPE" id="CUPDATETYPE" value="">
<input type="hidden" name="CCUSTNO" id="CCUSTNO" value="">
<input type="hidden" name="CSTATUS" id="CSTATUS" value="">

</form>
</p>

<script type="text/javascript">








function empty(e) {
	switch(e) {
		case "":
		case 0:
		case "0":
		case null:
		case false:
		case typeof this == "undefined":
			return true;
		default : return false;
	}
}

</script>
