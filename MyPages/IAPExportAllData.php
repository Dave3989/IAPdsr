<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

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

if($_REQUEST['action'] == "p413retA") {

	$iapCols = (array) IAP_Get_Savearea("IAP413EC", $_REQUEST['IAPUID']);
	if (empty($iapCols)) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}

	$iapPageError = "N";

	$iapWhichCusts = "";
	if ($_REQUEST['ecwhichcusts'] == "ecexpall") {
		$iapWhichCusts = "ALL";
	}	
	if ($_REQUEST['ecwhichcusts'] == "ecexpnew") {
		$iapWhichCusts = "NEW";
	}
	if ($_REQUEST['ecwhichcusts'] == "ecexpsel") {
		$iapWhichCusts = "SEL";
	}
	if ($iapWhichCusts == "") {
		echo "<span class=iapError>Please indicate which customers you would like to export.</span>";
		$iapPageError = "Y";
	}

	if ($iapWhichCusts == "SEL"
	and count($_REQUEST['eccustsellist'] == 0)) {
		echo "<span class=iapError>Only use selected customers indicated but no customers selected from list.</span>";
		$iapPageError = "Y";
	}

	$iapEmail = "N";
	if ($_REQUEST['ecemail'] == "ecemail") {
		$iapEmail = "Y";
	}

	$iapNewsletter = "N";
	if ($_REQUEST['ecnwltr'] == "ecnwltr") {
		$iapNewsletter = "Y";
	}

	if ($_REQUEST['eccnames'] != "N"
	and $_REQUEST['eccnames'] != "Y") {
		echo "<span class=iapError>Inclde Column Names can only be Y or N.</span>";
		$iapPageError = "Y";
	}
	$iapIncColNames = $_REQUEST['eccnames'];

	$iapFldList = array();
	$iapFldOrder = array();
	$i = 0;
	foreach($iapCols as $iapCol) {
		$iapFldList[$i]['sequence'] = "";
		$iapFldList[$i]['colname'] = $iapCol['Field'];
		$iapFldList[$i]['comment'] = $iapCol['Comment'];
		if ($iapCol['Comment'] != "-<(NOEXPORT)>-") {
			$f = "fld".strval($i);
			$e = $_REQUEST[$f];
			if ($e > "") {
				$iapFldList[$i]['sequence'] = $e;
				if (!empty($iapFldOrder[$e])) {
					echo "<span class=iapError>Duplicate sequence numbers (".strval($e).") encountered in the field list.</span>";
					$iapPageError = "Y";
				} else {
					$iapFieldOrder[$e] = $i;
				}
			}
			$i = $i + 1;
		}
	}

	$i = 0;
	foreach($iapFieldOrder as $iapFO) {
		if (empty($iapFO)) {
			echo "<span class=iapWarn>There are gap(s) in the sequence numbers. These fields will be filled with blank entries.</span>";
			$iapFieldOrder[$i] = "";
		}
		$i = $i + 1;	
	}

	if ($iapPageError == "N") {

		echo "<span class=iapSuccess>Building export file.</span><br>";
		wp_ob_end_flush_all();
		flush();

		if ($iapWhichCusts != "SEL") {
			if ($iapWhichCusts == "NEW") {
				$d = "Y";
			} else {
				$d = "N";
			}
			$iapCustomers = IAP_Get_All_Customers($d);
			if ($iapCustomers < 0) {
			    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
			    return;
			}
		} else {
			$iapSelCustList = $_REQUEST['eccustsellist'];
		}

		$fn = "IAPCustomerExport".date("Ymd", strtotime("now")).".csv";
		$fnd = "TempFiles/".$fn;
		$fp = fopen($fnd, 'w');

		if ($iapIncColNames == "Y") {
			$iapRec = array();
			foreach($iapFieldOrder as $iapFO) {
				if (empty($iapFO)) {
					$iapRec[] = "Blank Field";
				} else {
					$i = $iapFO;
					$iapColName = $iapFldList[$i]['comment'];
					$iapRec[] = $iapColName;
				}
			}
            if (!( fputcsv($fp, $iapRec, ',', '"') )) {
                echo "<span class=iapError>IAP INTERNAL ERROR: Cannot write data to CSV file. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br>";
                exit;
            }			
		}

		$c = -1;
		$iapEnd = "N";
		while($iapEnd == "N") {
			$c = $c + 1;
			if ($iapWhichCusts != "SEL") {
				if ($c > count($iapCustomers)) {
					$iapEnd = "Y";
				} else {
					$iapCust = $iapCustomers[$c];
				}
			} else {
				if ($c > count($iapSelCustList)) {
					$iapEnd = "Y";
				} else {
					$iapCust = IAP_Get_Customer_By_No($iapSelCustList[$c]);
					if ($iapCust < 0) {
					    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customer. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
					    return;
					}
				}
			}

			if ($iapEnd == "N") {
				if ($iapWhichCusts == "NEW"
				and $iapCust['cust_newsletter_add_date'] != "0000-00-00") {
					continue;
				}

				if ($iapEmail == "Y"
				and $iapCust['cust_email'] == "") {
					continue;
				}

				if ($iapNewsletter == "Y"
				and $iapCust['cust_newsletter'] != "Y") {
					continue;
				}

				echo "<span class=iapSuccess>Exporting ".$iapCust['cust_name'].".</span><br>";
				wp_ob_end_flush_all();
				flush();

				$iapRec = array();
				foreach($iapFieldOrder as $iapFO) {
					if (empty($iapFO)) {
						$iapRec[] = "";
					} else {
						$i = $iapFO;
						$iapColName = $iapFldList[$i]['colname'];
						$iapColValue = $iapCust[$iapColName];					
						$iapRec[] = $iapColValue;
					}
				}

	            if (!( fputcsv($fp, $iapRec, ',', '"') )) {
	                echo "<span class=iapError>IAP INTERNAL ERROR: Cannot write data to CSV file. [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</span><br>";
	                exit;
	            }
			}
		}
        fclose($fp);

		echo "<span class=iapSuccess>File ".$fn." successfully created.</span><br><br>";
		echo "<span class=iapSuccess><a href='".$fnd."'>Click here to download the file.</a></span><br><br><br>";
		wp_ob_end_flush_all();
		flush();

		if ($iapWhichCusts == "NEW") {
			echo "<span class=iapSuccess>Since new customers were exported I will be updating dates.</span><br>";
			echo "<span class=iapSuccess>Updating exported date in customer records.</span><br>";
			wp_ob_end_flush_all();
			flush();

			$c = -1;
			$iapEnd = "N";
			while($iapEnd == "N") {
				$c = $c + 1;
				if ($c > count($iapCustomers)) {
					$iapEnd = "Y";
				} else {
					$iapCust = $iapCustomers[$c];
				}
				if ($iapEnd == "N") {
					if ($iapCust['cust_newsletter_add_date'] != "0000-00-00") {
						continue;
					}

					if ($iapEmail == "Y"
					and $iapCust['cust_email'] == "") {
						continue;
					}

					if ($iapNewsletter == "Y"
					and $iapCust['cust_newsletter'] != "Y") {
						continue;
					}
					$iapCust['cust_newsletter_add_date'] = date("Y-m-d", strtotime("now"));
					$iapCust['cust_changed'] = date("Y-m-d", strtotime("now"));
					$iapCust['cust_changed_by'] = $_REQUEST['IAPUID'];
					$iapRet = IAP_Update_Data($iapCust, "cust");
					if ($iapRet < 0) {
						echo "<span class=iapError>IAP INTERNAL ERROR updating customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
						exit;
					}
				}
			}

			echo "<span class=iapSuccess>Updating exported date in company record.</span><br>";
			wp_ob_end_flush_all();
			flush();

			$iapCompany = IAP_Get_Company();
			if ($iapCompany < 0) {
			    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve company record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
			    return;
			}

			$iapCompany['co_last_customer_export'] = date("Y-m-d", strtotime("now"));

			$iapCompany['co_changed'] = date("Y-m-d", strtotime("now"));
			$iapCompany['co_changed_by'] = $_REQUEST['IAPUID'];
			$iapRet = IAP_Update_Data($iapCompany, "comp");
			if ($iapRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating company [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		}

		echo "<span class=iapSuccess>Export Complete!</span><br><br>";
		wp_ob_end_flush_all();
		flush();
	}
	
} else {
	if (IAP_Remove_Savearea("IAP413EC") < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove the customer savearea. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapColSQL = "SHOW FULL COLUMNS FROM iap_customers";
	$iapRet = IAPProcessMySQL("select", $iapColSQL);
	if ($iapRet['retcode'] < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}
	if ($iapRet['numrows'] == 0){
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}
	$iapCols = (array) $iapRet['data'];

	$iapRet = IAP_Create_Savearea("IAP413EC", $iapCols, $_REQUEST['IAPUID']);
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea for customer [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
}

$iapCompany = IAP_Get_Company();
if ($iapCompany < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve company record. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}

$iapCustomers = IAP_Get_Customer_List();
if ($iapCustomers < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
$iapCustOpts = "";
foreach($iapCustomers as $iapC) {
	$iapCustOpts = $iapCustOpts."<option value=".strval($iapC['cust_no']).">".$iapC['cust_name']."</option>";
}

$iapReadOnly = IAP_Format_Heading("Export Customers");
?>

<form name='cselform' action='?action=p413retA&origaction=initial' method='POST' onsubmit='return cNoSubmit();' onkeypress='stopEnterSubmitting(window.event)'>

<table style='width:100%'>
<tr><td style='width:5%;'></td><td style='width:10%;'></td><td style='width:20%'></td><td style='width:65%;'></td></tr>
<tr><td style='width:5%;'></td><td colspan='3' class=iapFormLabel>Which Customers Would You Like To Export?</td></tr>
<tr><td style='width:5%;'></td><td style='width:10%;'></td><td colspan="2">
<input type='radio' name='ecwhichcusts' id='ecexpall' value='ecexpall' onclick='ecclosecusts();'>Export all customers<br>
<?php 
	if ($iapCompany['co_last_customer_export'] != "0000-00-00 00:00:00") {
?>
		<input type='radio' name='ecwhichcusts' id='ecexpnew' value='ecexpnew' onclick='ecclosecusts();'>Export customers added after <?php echo date("m/d/Y", strtotime($iapCompany['co_last_customer_export'])); ?><br>
<?php
	}
?>
<input type='radio' name='ecwhichcusts' id='ecexpsel' value='ecexpsel' onclick='ecopencusts();'>Selected customers<br>

<div id=eccustlist style='display:none;'>
	<select name='eccustsellist[]' id='eccustsellist' size='10' multiple='multiple'><?php echo $iapCustOpts; ?></select>
</div>
</td></tr>

<tr><td style='width:5%;'>&nbsp;</td><td style='width:10%;'>&nbsp;</td><td style='width:20%'>&nbsp;</td><td style='width:65%;'>&nbsp;</td></tr>

<tr><td style='width:5%;'></td><td colspan='3' class=iapFormLabel>Check the box to only export customers with:</td></tr>
<tr><td style='width:5%;'></td><td style='width:10%;'></td><td colspan="2">
	<input type="checkbox" name="ecemail" value="ecemail">Those with an email address.<br>
	<input type="checkbox" name="ecnwltr" value="ecnwltr">Those wanting newsletters.<br>	
</td></tr>

<tr><td style='width:5%;'>&nbsp;</td><td style='width:10%;'>&nbsp;</td><td style='width:20%'>&nbsp;</td><td style='width:65%;'>&nbsp;</td></tr>

<tr><td style='width:5%;'></td><td colspan='3' class=iapFormLabel>Include Column Names As First Row?</td></tr>
<tr><td style='width:5%;'></td><td style='width:10%;'></td><td colspan="2">
	<input type="text" name='eccnames' size='2' maxlength='2' value='N'>&nbsp;&nbsp;&nbsp;Y or N only
</td></tr>

<tr><td style='width:5%;'>&nbsp;</td><td style='width:10%;'>&nbsp;</td><td style='width:20%'>&nbsp;</td><td style='width:65%;'>&nbsp;</td></tr>
<tr><td style='width:5%;'>&nbsp;</td><td style='width:10%;'>&nbsp;</td><td style='width:20%'>&nbsp;</td><td style='width:65%;'>&nbsp;</td></tr>

<tr><td style='width:5%;'></td><td colspan='3'>Fields</td></tr>
<tr><td style='width:5%;'></td><td style='width:10%;'></td><td colspan='2'>
Map the fields you want in the exported file. Place a number next to the field name representing<br>the order they are to appear.<br>
&nbsp;&nbsp;&nbsp;ex: First Name 1, Full Name 2, Street Address 3, etc.
</td></tr>

<?php
	$i = 0;
	foreach($iapCols as $iapC) {
		if ($iapC['Comment'] != "-<(NOEXPORT)>-") {
			echo "<tr><td colspan='2'></td><td colspan='2'><input type='text' size='5' maxlen='5' name='fld".strval($i)."'>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$iapC['Comment']."</td></tr>";
			$i = $i + 1;
		}
	}
?>

<tr style='line-height:200%;'><td colspan='2'></td><td colspan='2'>
	<input type='submit' name='ecsubmit' value='Submit'>
</td></tr>

<tr><td style='width:5%;'>&nbsp;</td><td style='width:10%;'>&nbsp;</td><td style='width:20%'>&nbsp;</td><td style='width:65%;'>&nbsp;</td></tr>

<tr><td style='width:5%;'></td><td colspan='3'>
	<fieldset style='border: 2px double #ae9471; top: 5px; right: 5px; bottom: 5px; left: 5px; padding: 10px; text-align: justify;'>
	<legend> An Example </legend>
	As an example: Suppose you created a mail merge document which has the customer's address and their first name in the salutation. In MS Word merge fields are mapped during the mail merge process. Other documants may need the merge fields to be in the order in which the are used. Place a 1 in the space in front of Full Name, 2 for Street, 3 for City, 4 for State, 5 for Zip and 6 for First Name. This will create a file with these fields in the proper sequence for each customer selected.
	</fieldset>
	<br>
	<fieldset style='border: 2px double #ae9471; top: 5px; right: 5px; bottom: 5px; left: 5px; padding: 10px; text-align: justify;'>
	<legend> For Magnabilities Newsletter </legend>
	To create a CSV file to be uploaded to Magnabilities mail Use the "Export customers added after..." option, check both "Those with an email address." and "Those wanting newsletters.", do not include column names and place a 1 in front of First Name, a 2 in front of Last Name and a 3 in front Email Address. This wil create a fileof new customers that can be uploaded directly into the mail system.
	</fieldset>
</td></tr>



</table>
</form>


<!--
	<textarea readonly rows="10" cols="60" maxlength="600">As an example: Suppose you created a mail merge document which has the customer's address and their first name in the salutation. In MS Word merge fields are mapped during the mail merge process. Other documants may need the merge fields to be in the order in which the are used. Place a 1 in the space in front of Full Name, 2 for Street, 3 for City, 4 for State, 5 for Zip and 6 for First Name. This will create a file with these fields in the proper sequence for each customer selected.</textarea> 



Select by
| Extract
| |
N N	cust_company				smallint(6)		No 	 	 
N N	cust_no						smallint(6)		No 	 	 
  	cust_type					char(1)	No 		C 	C=Customer, P=Prospect, D=Downline  
N	cust_name					varchar(50)		No 	 	 
N	cust_first_name				varchar(30)		No 	 	 
N	cust_last_name				varchar(30)		No 	 	 
N	cust_street					varchar(50)		No 	 	 
	cust_city					varchar(30)		No 	 	 
	cust_state					char(2)			No 	 	 
	cust_zip					char(10)		No 	 	 
N	cust_home_phone				varchar(15)		No 	 	 
N	cust_cell_phone				varchar(15)		No 	 	 
N	cust_email					varchar(100)	No 	 	 
	cust_birthday				char(5)			Yes 	NULL 	 
N N	cust_birthday_event			smallint(6)		No 	0 	 
N N	cust_xel_ss					char(5)			No 	 	 
	cust_newsletter				char(1)			No 	Y 	 
  N	cust_newsletter_add_date	date			No 	0000-00-00 	 
N 	cust_followup_consultant	char(1)			No 	N 	 
N	cust_followup_party			char(1)			No 	N 	 
N N	cust_followup_set			date			No 	 	 
	cust_met_at_pe				smallint(6)		No 	0 	 
N	cust_notes					varchar(500)	No 	 	 	
N N	cust_changed				datetime		No 	 	 
N N	cust_changed_by				smallint(6)		No 	 
-------------------------------------------------------------------------------------------------------
*/

-->



<script type="text/javascript">

function ecopencusts() {
	var eccuslst = document.getElementById("eccustlist").style.display;
	document.getElementById("eccustlist").style.display="block";
	document.getElementById("eccustsellist").focus();
}

function ecclosecusts() {
	var eccuslst = document.getElementById("eccustlist").style.display;
	document.getElementById("eccustlist").style.display="none";
	document.getElementById("ecexpsel").checked = false;
}

</script>