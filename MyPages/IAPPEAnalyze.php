<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Party/Event Maintenance with action of ".$_REQUEST['action']."<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("356") < 0) {
	return;
};

$iapParEv = IAP_Get_PE_List("Y");	// Even get closed parties
if ($iapParEv < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties/events. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
    return;
}
$iapPEList = "";
$c = "";
if ($iapParEv != NULL) {
	foreach ($iapParEv as $iapPE) {
		if ($iapPE['pe_type'] == "P"
		or  $iapPE['pe_type'] == "E") {
			if ($iapPE['pe_type'] == "P") {
				$p = date("m/d/Y", strtotime($iapPE['pe_date']))." party ".$iapPE['pe_party_no']." for ".trim($iapPE['pe_sponsor']);
			} elseif ($iapPE['pe_type'] == "E")  {
				$p = date("m/d/Y", strtotime($iapPE['pe_date']))." event at ".trim($iapPE['pe_sponsor']);
			}
			$iapPEList = $iapPEList.$c.'{"label": "'.$p.'", "id": "'.strval($iapPE['pe_id']).'"}';
			$c = ",";
		}
	}
}

$DivShow = "none";

$iapReadOnly = IAP_Format_Heading("Analyze Parties and Events");

$h = IAP_Do_Help(3, 356, 1); // level 3, page 356, section 1
if ($h != "") {
	echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
	echo "<tr><td width='1%'></td><td width='80%'>";
	echo $h;
	echo "</td><td width='19%'></td></tr>";
	echo "</table>";
}
?>

<div id='pechoose'>
<p style='text-indent:50px; width:100%'>
<form name='peselform' action='?action=p356retA&origaction=initial' method='POST' onsubmit='return cNoSubmit();'>
<?php
	if (empty($iapPEList)) {
		$iapOptsReadOnly = "readonly ";
		$iapMsg = "There are no party/events on file.";
	} else {
		$iapOptsReadOnly = "";
		$iapMsg = "";
	}
	echo "<label for='pPEList' id='pPEListLbl'>Select a party or event: </label>";
	echo "<input id='pPEList' size='50'></span>";
	echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 356, 1); //   <!-- level 1, page 356, section 1 -->
	echo "<br><span class=iapSuccess style='padding-left: 50px;'>&nbsp;&nbsp;&nbsp;Then click the Go button to see the detail.</span>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<img src='MyImages/LHCGoGreen.jpg' style='width:25px;height:25px;vertical-align:bottom;border-style:none;' title='iapGo' onclick='pGoClicked()'>";
	echo "<br><span class=iapError id='pError' style='display:none; padding-left:40px;'>Party/Event was not found.</span>";
	echo "<br><span class=iapFormLabel style='padding-left: 40px;'>".$iapMsg."</span>";
?>
</form>
</p>
</div>

/* Form

									pe_date
	pe_sponsor

	Sales (Number of Sales #)
		Net Sales					$
		Sales Tax					$
			TOTAL SALES:					Total Value


	Expenses
		Cost of Space:				$
		Miles:				#		Extended
		Misc Expenses				$
			TOTAL EXPENSES					$

	Cost Products							$
	
	PROFIT									$

*/


<div id='pedetail' style='display:<?php echo $DivShow; ?>;'>
<hr>
<p style='text-indent:50px; width:100%'>

<form name='pedetform' action='?action=p356retB&origaction=<?php echo $iapOrigAction; ?>' method='POST'>
<br>
<?php
$d = "none";
if ($iapParty['pe_party_complete'] == "Y") {
	$d = "inline";
}
?>

<div id=pepartycomp style='display:<?php echo $d; ?>'>
<table>
<tr><td style="width: 13%;">&nbsp;</td>
<td style="width: 87%;"><span class=iapWarning>This Party Has Been Closed!</span>
</td></tr>
<tr><td style="width: 13%;">&nbsp;</td><td style="width: 87%;">&nbsp;</td></tr>
</table>
</div>

<table>
<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Party/Event:</span></td>
<td style="width: 87%;">
	<input readonly type='radio' name='petype' id='petypeparty' value='P' tabindex="1"
<?php
	if ($iapParty['pe_type'] != "E") {
		echo " checked";
	}
?>
	onchange='pesetpartyon(); autofocus'>Party
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='peparty' id='pepartylbl'><?php if ($iapParty['pe_type'] == "E") { echo ""; } else { echo "Party Number:"; } ?> </label>
	<input readonly type='<?php if ($iapParty['pe_type'] == "E") { echo "hidden"; } else { echo "text"; } ?>' tabindex="2" size="15" maxlength="15" name="peparty" id="peparty" value="<?php echo $iapParty['pe_party_no']; ?>">

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input readonly type='radio' name='petype' id='petypeevent' value='E' tabindex="3"
<?php
	if ($iapParty['pe_type'] == "E") {
		echo " checked";
	}
?>
	 onchange='pesetpartyoff();'>Event
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<?php
if (!empty($iapParty['pe_date'])) {
	$dt = date("m/d/Y", strtotime($iapParty['pe_date']));
} else {
	$dt = "";
}
if (!empty($iapParty['pe_start_time'])) {
	$stm = date("h:i a",strtotime($iapParty['pe_start_time']));	
} else {
	$stm = "";
}
if (!empty($iapParty['pe_end_time'])) {
	$etm = date("h:i a",strtotime($iapParty['pe_end_time']));	
} else {
	$etm = "";
}
?>
<tr>
<td style="width: 13%;"><label for='pedate' class='iapFormLabel'>Date: </label></td>
<td style="width: 87%;">
	<input readonly tabindex='4' placeholder='mm/dd/yyyy' maxlength='15' size='15' name='pedate' id='pedate' value='<?php echo $dt; ?>'>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='pestart' class='iapFormLabel'>Start Time: </label>
	<input readonly tabindex='5' placeholder='hh:mm pm' maxlength='10' size='10' name='pestart' id='pestart' value='<?php echo $stm; ?>'>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<label for='peend' class='iapFormLabel'>End Time: </label>
	<input readonly tabindex='6' placeholder='hh:mm pm' maxlength='10' size='10' name='peend' id='peend' value='<?php echo $etm; ?>'>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><label for='pesponsor' class=iapFormLabel id='pesponsorlbl'><?php if ($iapParty['pe_type'] == "E") { echo "Sponsor:"; } else { echo "Hostess:"; } ?></label></td>
<td style="width: 87%;">

	<input readonly type=<?php if ($iapParty['pe_type'] == "E") { echo "text"; } else { echo "hidden"; } ?> tabindex="7" maxlength="50" size="50" name="pesponsor" id="pesponsor" value="<?php echo $iapParty['pe_sponsor']; ?>">

	<input readonly type=<?php if ($iapParty['pe_type'] == "E") { echo "hidden"; } else { echo "text"; } ?> tabindex="7" maxlength="100" size="50" name="pecustomer" id="pecustomer"  value="<?php echo $iapParty['pe_sponsor']; ?>">
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 2, $iapParty['pe_type']); ?> <!-- level 1, page 356, section 2 -->
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr><td colspan="2"><span class='iapFormLabel'>Address:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 4); ?> <!-- level 1, page 356, section 4  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Street:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="8" maxlength="50" size="50" name="pestreet" id="pestreet" value="<?php echo $iapParty['pe_street']; ?>">
		</td></tr>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>City, State, Zip:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="9" maxlength="35" size="35" name="pecity" id="pecity" value="<?php echo $iapParty['pe_city']; ?>">
			<input readonly tabindex="10" maxlength="2" size="2" name="pestate" id="pestate" value="<?php echo $iapParty['pe_state']; ?>">
			<input readonly tabindex="11" maxlength="10" size="10" name="pezip" id="pezip" value="<?php echo $iapParty['pe_zip']; ?>">
		</td></tr>
	</table>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Website:</span></td>
<td style="width: 87%;">
	<input readonly tabindex="12" type="text" maxlength="100" size="50" name="peurl" id="peurl" value="<?php echo $iapParty['pe_website']; ?>">
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2"><span class='iapFormLabel'>Contacts:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 5); ?> <!-- level 1, page 356, section 5  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Name:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="13" maxlength="50" size="50" name="pec1name" id="pec1name" value="<?php echo $iapParty['pe_contact1']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Email:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="14" maxlength="100" size="50" name="pec1email" id="pec1email" value="<?php echo $iapParty['pe_c1email']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Phone:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="15" maxlength="15" size="15" name="pec1phone" id="pec1phone" value="<?php echo $iapParty['pe_c1phone']; ?>">
		</td></tr>

		<tr>
		<td colspan="3">&nbsp;</td>
		</tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Name:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="17" maxlength="50" size="50" name="pec2name" id="pec2name" value="<?php echo $iapParty['pe_contact2']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Email:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="18" maxlength="100" size="50" name="pec2email" id="pec2email" value="<?php echo $iapParty['pe_c2email']; ?>">
		</td></tr>

		<tr>
		<td style="width: 5%"></td>
		<td style="width: 14%;"><span class='iapFormLabel'>Home Phone:</span></td>
		<td style="width: 81%;">
			<input readonly tabindex="19" maxlength="15" size="15" name="pec2phone" id="pec2phone" value="<?php echo $iapParty['pe_c2phone']; ?>">
		</td></tr>

</table></td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2"><span class='iapFormLabel'>Expenses:</span>
	&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 6); ?> <!-- level 1, page 356, section 6  -->
</td></tr>
<tr><td colspan="2">
	<table>
		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=pemiles class='iapFormLabel'>Mileage:</label></td>
		<td style="width: 81%;">
			<input readonly style="text-align:right;" maxlength="10" size="10" tabindex="21" name="pemiles" id="pemiles" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_mileage'], 2, '.', ''); ?>>
		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=pespaceexp class='iapFormLabel'>Space Charge:</label></td>
		<td style="width: 81%;">
			<input readonly style="text-align:right;" maxlength="10" size="10" tabindex="22" name="pespaceexp" id="pespaceexp" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_space_charge'], 2, '.', ''); ?>>&nbsp;&nbsp;&nbsp;If Event

		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=peotherexp class='iapFormLabel'>Other Expenses:</label></td>
		<td style="width: 81%;">
		<input readonly style="text-align:right;" maxlength="10" size="10" tabindex="23" name="peotherexp" id="peotherexp" align="right" step="0.1" value=<?php echo number_format($iapParty['pe_other_expenses'], 2, '.', ''); ?>>
		</td></tr>

		<tr>	<td style="width: 5%"></td>
		<td style="width: 14%;"><label for=peexplexp class='iapFormLabel'>Explain Expenses:</label></td>
		<td style="width: 81%;">
			<textarea name='peexplexp' id='peexplexp' cols='50' rows='4' wrap='soft' tabindex="24" <?php echo $iapReadOnly; ?>><?php echo $iapParty['pe_exp_explained']; ?></textarea>
		</td></tr>
		</table>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td colspan="2">
	<table>
	<tr>
	<td style="width: 19%;"><label for=peaddcal>Add To Calendar:</label></td>
	<td style="width: 81%;">
		<input readonly type="checkbox" tabindex="25" name="peaddcal" id="peaddcal" 
<?php
		if (!empty($iapParty['pe_event_id'])) {
			echo " checked";
		}
?>
		>
		&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 356, 7); ?> <!-- level 1, page 356, section 7  -->
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel' id='peeventno'>
<?php
		if (!empty($iapParty['pe_event_id'])) {
			echo "To edit the event use Event Id ".strval($iapParty['pe_event_id']);
		}
?>
		</span></td></tr></table>

</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
<td style="width: 13%;"><span class='iapFormLabel'>Notes:</span></td>
<td style="width: 87%;">
	<textarea name='pecomments' id='pecomments' tabindex="26" cols='40' rows='6' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapParty['pe_comment']; ?></textarea>
</td></tr>

<tr style='line-height:200%;'><td style="width: 13%;"> </td><td style="width: 87%;"></td></tr>
<tr style='line-height:200%;'><td style="width: 13%;"> </td><td style="width: 87%;">

<?php
	if ($iapReadOnly != "readonly") {
?>
	<tr>
	<td colspan='2'>
		<table>
		<tr>
		<td style="width: 19%;"></td>
		<td style="width: 81%;">
			<input class=iapButton tabindex='30' type='submit' name='pesubmit' id='pesubmit' value='Submit'>
<?php
			if ($iapParty['status'] != "NEW"
			and $iapParty['sales'] == "N") {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input class=iapButton tabindex='31' type=submit name='pedelete' id='pedelete' value'Delete'>";
			}
?>
		</td></tr></table>
	</td></tr>

<?php
	}
?>
</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<?php

/*
sale types
E = event - need an event address (selectable or add)
F = facebook - can have party id 
P = party - need a party number (selectable or add)
O = other
I = individual direct - need a customer record 
W = website - can have party id - need number from online (SALE DOES NOT REDUCE INVENTORY)
X = exchange - another program
*/

	if ($iapParty['pe_sales_cnt'] == 0) {
		$iapSDsply = "none";
	} else {
		if ($iapParty['pe_type'] != "P"
		and $iapParty['pe_type'] != "E"
		and $iapParty['pe_type'] != "F") {
			$iapSDsply = "none;";
		} else {
			$iapSDsply = "block;";
		}
	}
	echo "<tr style='width:100%; display:".$iapSDsply.";' id='pesaletottitle'>";
	echo "<td colspan='2'><span id='pesaletotname' style='font-size:110%; text-decoration:underline;'>";
	if ($iapParty['pe_type'] == "P") {
		echo "Sales For This Party";
	} elseif ($iapParty['pe_type'] == "E") {
		echo "Sales For This Event";
	} elseif ($iapParty['pe_type'] == "F") {
		echo "Sales For This Facebook Party";
	}
	echo "</span>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 356, 3); // <!-- level 1, page 356, section 3  -->
	echo "</td></tr>";

	$iapSales = $iapParty['Sales'];
?>

	<tr><td colspan='2'>
		<table id=pesalestbl style='width:100%; display:<?php echo $iapSDsply; ?>'>
		<tr style='width:100%;'>
		<td style='width:5%;'></td>
		<td style='width:20%; text-align:center;'><span style='text-decoration:underline;'>Customer</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Net Sales</span></td>
		<td style='width:9%; text-align:center;'><span style='text-decoration:underline;'>Shipping</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Tax</span></td>
		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Total</span></td>
		<td style='width:12%; text-align:center;'><span style='text-decoration:underline;'>Item Cost</span></td>
		<td style='width:9%; text-align:center;'><span style='text-decoration:underline;'>Profit</span></td>
<!--		<td style='width:10%; text-align:center;'><span style='text-decoration:underline;'>Items</span></td>  -->
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:5%;'></td>
		</tr>
	<?php
		if ($iapParty['pe_sales_cnt'] > 0) {
			foreach($iapSales as $iapS) {
	?>
			<tr>
			<td style='width:5%;'></td>
			<td style='width:20%;'><a href='?page_id=291&action=selected&sale=<?php echo $iapS['sale_id']; ?>'><?php echo $iapS['cust_name']; ?></a></td>
			<td style='width:10%; text-align:right;' id="pesnet"><?php echo number_format($iapS['sale_net'], 2, '.', ',') ?></td>
			<td style='width:9%; text-align:right;' id="pesship"><?php echo number_format($iapS['sale_shipping'], 2, '.', ','); ?></td>
			<td style='width:10%; text-align:right;' id="pestax"><?php echo number_format($iapS['sale_sales_tax'], 2, '.', ','); ?></td>
			<td style='width:10%; text-align:right;' id="pestotal"><?php echo number_format($iapS['sale_total_amt'], 2, '.', ','); ?></td>
			<td style='width:12%; text-align:right;' id="pescost"><?php echo number_format($iapS['sale_item_cost'], 2, '.', ','); ?></td>
			<td style='width:9%; text-align:right;' id="pesprofit"><?php echo number_format($iapS['sale_profit'], 2, '.', ','); ?></td>
<!--			<td style='width:10%; text-align:right;' id="pesitems"><?php echo number_format($iapS['sale_items'], 0, '.', ','); ?></td> -->
			<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
			<td style='width:5%;'></td>
			</tr>
	<?php
			}
		}
	?>
		</table>
		<table id=pesalestot style='width:100%; display:<?php echo $iapSDsply; ?>'>
		<tr>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:20%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:12%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:9%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:10%;'>&nbsp;&nbsp;&nbsp;</td>
		<td style='width:5%;'>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
		<td style='width:5%;'></td>
		<td style='width:20%;'>Total All Sales</td>
		<td style='width:10%; text-align:right'><span id='petnet'> <?php echo number_format($iapParty['pe_net_sales'], 2, '.', ','); ?></span></td>
		<td style='width:9%; text-align:right'><span id='petship'> <?php echo number_format($iapParty['pe_shipping'], 2, '.', ','); ?></span></td>
		<td style='width:10%; text-align:right'><span id='pettax'> <?php echo number_format($iapParty['pe_sales_tax'], 2, '.', ','); ?></span></td>
		<td style='width:10%; text-align:right'><span id='pettotal'> <?php echo number_format($iapParty['pe_total_sales'], 2, '.', ','); ?></span></td>
		<td style='width:12%; text-align:right'><span id='petcost'> <?php echo number_format($iapParty['pe_cost_of_items'], 2, '.', ','); ?></span></td>
		<td style='width:9%; text-align:right'><span id='petprofit'> <?php echo number_format($iapParty['pe_profit'], 2, '.', ','); ?></span></td>
		<td style='width:10%;'></td>
		<td style='width:5%;'></td>
		</tr>
		</table>
	</td></tr>
</table>

<br><br><br>
<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type="hidden" name="LHCAA" id="LHCAA" value="<?php echo $_REQUEST['CoId']; ?>">
<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
<input type='hidden' name='IAPDL' id='IAPDL' value="<?php echo $_REQUEST['UserData']['dlistok']; ?>">
<input type="hidden" name="PEUPDATETYPE" id="PEUPDATETYPE" value="">
<input type="hidden" name="PESALES" id="PESALES" value="">
<input type="hidden" name="PEID" id="PEID" value="">
<input type="hidden" name="PEHOSTESSID" id="PEHOSTESSID" value="">
<input type="hidden" name="PETAXREGION" id="PETAXREGION" value="">
<input type="hidden" name="PETAXRATE" id="PETAXRATE" value=0>
<input type="hidden" name="PEPARTYCOMP" id="PEPARTYCOMP" value="<?php echo $iapParty['pe_party_complete'];?>">


</form>
</p>
</div>


<script type="text/javascript">
<?php
require_once($_REQUEST['LHCPath']."IAP/MyJS/NonJSMin/JSAnalyzePE.js");

// require_once($_REQUEST['LHCPath']."IAP/MyJS/JSAnalyzePE.min.js");
?>
 
 
var pAllPEs = [<?php echo $iapPEList; ?>];
</script>