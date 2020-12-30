<?php

function IAP_Sale_ViewPC($iapSale) {

/*
sale types
E = event need an event address (selectable or add)
F = facebook can have party id 
P = party need a party number (selectable or add)
O = other
I = individual direct need a customer record 
W = website can have party id - need number from online (SALE DOES NOT REDUCE INVENTORY)
X = exchange another program
*/

	$iapSelEna = "readonly";

	$iapSales = IAP_Get_Sale_List();
	if ($iapSales < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve sales. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	$sSales = "";
	$c = "";
	if ($iapSales != NULL) {
		foreach ($iapSales as $iapS) {
			$iapCNm = str_replace('"', '', $iapS['cust_name']);
			$s = $iapCNm." on ".date("m/d/Y", strtotime($iapS['sale_date']));
			$sSales = $sSales.$c.'{"label": "'.$s.'", "id": "'.strval($iapS['sale_id']).'"}';
			$c = ',';
		}
	}

	$iapCusts = IAP_Get_Customer_List();
	if ($iapCusts < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve customers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	if ($iapCusts != NULL) {
		$sCusts = "";
		$c = "";
		foreach($iapCusts as $iapC) {
			$iapCNm = str_replace('"', '', $iapC['cust_name']);
			$sCusts = $sCusts.$c.'"'.$iapCNm.'"';
			$c = ",";
		}
		$iapSelEna = "";
	}


	$iapPar = IAP_Get_PE_List();
	if ($iapPar < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve parties. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	    return;
	}
	if ($iapPar != NULL) {
		$sParties = "";
		$sEvents = "";
		$c = "";
		foreach($iapPar as $iapP) {
			if ($iapP['pe_type'] == "P") {
				$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
				$sParties = $sParties.$c.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'"}';
			} elseif ($iapP['pe_type'] == "E") {
				$peText = date("m/d/Y", strtotime($iapP['pe_date']))." event at ".trim($iapP['pe_sponsor']);
				$sEvents = $sEvents.$c.'{"label": "'.$peText.'", "date": "'.$iapP['pe_date'].'"}';
			}
			$c = ",";
		}
	}

	$iapItems = IAP_Get_Catalog_List("C");
	if ($iapItems < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve catalog. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	$sCodes = "";
	$c = "";
	if ($iapItems != NULL) {	
		foreach ($iapItems as $iapI) {
			$sCodes = $sCodes.$c.'"'.$iapI['cat_item_code'].'"';
			$c = ",";
		}
	}
	$iapItems = IAP_Get_Catalog_List("D");
	if ($iapItems < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve catalog. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	$sDescs = "";
	$c = "";
	if ($iapItems != NULL) {	
		foreach ($iapItems as $iapI) {
			if ($iapI['cat_description'] != "") {
				$sDescs = $sDescs.$c.'{"label": "'.$iapI['cat_description'].'", "code": "'.$iapI['cat_item_code'].'"}';
				$c = ",";
			}
		}
	}

	$iapCats = IAP_Get_Codes("cat");
	if ($iapCats < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retrieve categories. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		return;
	}
	if ($iapCats != NULL) {
		$iapCatOpts = "";
		foreach ($iapCats as $iapC) {
			$iapCatOpts = $iapCatOpts."<option value='".$iapC['code_code']."'>".$iapC['code_value']."</option>";
		}
	}

	$iapReadOnly = IAP_Format_Heading("Sales Entry/Edit");

?>

	<div id='pchoose' >
	<form name='pselform' action='?page_id=291&action=p291retA&origaction=initial' method='POST'>
	<?php
		if (empty($sSales)) {
			$iapOptsReadOnly = "readonly ";
			$iapMsg = "No Sales on file. Click on ADD.";
		} else {
			$iapOptsReadOnly = "";
			$iapMsg = "";
		}
		echo "<span class=iapFormLabel style='padding-left: 40px;'>";
		echo "<label for='sSaleList'>Select a sale: </label>";
		echo "<input id='sSaleList' size=50'></span>&nbsp;&nbsp;&nbsp;<?php echo IAP_Do_Help(1, 291, 1); ?>"; 
	//		Help Narative	<!-- level 1, page 291, section 1 -->

		if ($iapReadOnly != "readonly") {
			echo "<br><span class=iapFormLabel style='padding-left: 50px;'>";
			echo "<input type='button' class=iapButton name='sAdd' id='sAdd' value='Add A New Sale' onclick='sAddClicked()' />";
		}
		echo "<br>".$iapMsg."</span>";
	?>
	</form>
	</div>

	<div id='sdetail' style='display:<?php echo $DivShow; ?>;'>
	<hr>
	<p style='width:100%'>

	<form name='purform' action='?page_id=291&action=p291retB&origaction=<?php echo $iapOrigAction; ?>' method='POST' onkeypress='stopEnterSubmitting(window.event)'>

	<?php
	if (!empty($iapSale['sale_date'])) {
		$d3 = date("m/d/Y", strtotime($iapSale['sale_date']));
	} else {
		$d3 = date("m/d/Y");
	}
	?>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=iapFormLabel style='font-weight: bold;'>Use the tab key to move between fields</span><br><br>

	<table>
	<tr><td style="width:16%;"><label for='scustomers' class='iapFormLabel'>Select a Customer: </label></td>
	<td style="width:84%;">
	<?php
		if (empty($sCusts)) {
			$iapCustReadOnly = "readonly ";
			$iapMsg = "No customers on file. Click on ADD.";
		} else {
			$iapCustReadOnly = "";
			$iapMsg = "";
		}
		echo "<input ".$iapCustReadOnly." id='scustomers' tabindex='1' size=50' value='".$iapCust['cust_name']."'></span>&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 291, 2); 
	//		Help Narative	<!-- level 1, page 291, section 2 -->

		echo "&nbsp;&nbsp;&nbsp;";
		if ($iapReadOnly != "readonly") {
			echo "<button class=iapButton name='saddcust' type='button' onclick='sAddCustomer()'>New Customer</button>";
		}
	?>
	</td></tr>
	</table>
	<?php
		if ($iapSale['newcust'] == "Y") {
			$c = "block;";
		} else {
			$c = "none;";
		}
	?>
		<div id=iapNewCust style="display:<?php echo $c; ?>">
			<table>
			<tr><td style="width:16%;" class="iapFormLabel"></td><td style="width:84%;"></td></tr>

			<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class='iapFormLabel'>Please provide the following information about the new customer. 
				This new customer will need to be editted later to enter any additional information.</span>
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Name:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="2" type="text" name="snewcname" id="snewcname" size="50" value="<?php echo $iapCust['cust_name']; ?>">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Birth Day:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="3" maxlength="7" size="7" name="snewcbirth" id="snewcbirth" placeholder="mm/dd" value="<?php echo $iapCust['cust_birthday']; ?>">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Street:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="4" maxlength="50" size="50" name="snewcstrt" id="snewcstrt" value="<?php echo $iapCust['cust_street']; ?>">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>City, State, Zip:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="5" maxlength="40" size="40" name="snewccity" id="snewccity" value="<?php echo $iapCust['cust_city']; ?>">
					<input <?php echo $iapReadOnly; ?> tabindex="6" maxlength="2" size="2" name="snewcstate" id="snewcstate" value="<?php echo $iapCust['cust_state']; ?>">
					<input <?php echo $iapReadOnly; ?> tabindex="7" maxlength="10" size="10" name="snewczip" id="snewczip" value="<?php echo $iapCust['cust_zip']; ?>">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Email:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="8" type="text" name="snewcemail" id="snewcemail" size="50" maxlength="75">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Phone:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="9" type="text" name="snewcphone" id="snewcphone" size="15">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Newsletter:</span></td>
				<td style="width:84%;"><input <?php echo $iapReadOnly; ?> tabindex="10"  type="checkbox" name="snewcnews" id="snewcnews" value="cnewsyes" checked> Check to send newsletter.
			</td></tr>
			</table>
		</div>

	<table>

	<tr><td style="width:16%;" class="iapFormLabel"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%;"><span class='iapFormLabel'>Follow Up For:</span></td>
	<td style="width:84%;">Possible Consultant 
		<input <?php echo $iapReadOnly; ?> tabindex="11"  type="checkbox" name="scposscon" id="cposscon" value="cconsyes"
			<?php if ($iapCust['cust_followup_consultant'] == "Y") { echo " checked"; } ?>>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Possible Party 
		<input <?php echo $iapReadOnly; ?> tabindex="12"  type="checkbox" name="scposspar" id="cposspar" value="cpartyyes"
			<?php if ($iapCust['cust_followup_party'] == "Y") { echo " checked"; } ?>>
	</td></tr>

	<tr><td style="width:16%;" class="iapFormLabel"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%;"><span class='iapFormLabel'>Type of Sale:</span></td>
	<td style="width:84%;">
		<input type="radio" name="stype" id="stypeparty" value="P" tabindex="13" onchange='sSetType("P")'
			<?php if ($iapSale['sale_type'] == "P") { echo " checked"; } ?>
		>Party&nbsp;&nbsp;&nbsp;
		<input type="radio" name="stype" id="stypeevent" value="E" tabindex="13" onchange='sSetType("E")'
			<?php if ($iapSale['sale_type'] == "E") { echo " checked"; } ?>
		>Event&nbsp;&nbsp;&nbsp;
		<input type="radio" name="stype" id="stypeindiv" value="I" tabindex="13" onchange='sSetType("I")'
			<?php if ($iapSale['sale_type'] == "I") { echo " checked"; } ?>
		>Individual&nbsp;&nbsp;&nbsp;
		<input type="radio" name="stype" id="stypefacebk" value="F" tabindex="13" onchange='sSetType("F")'
			<?php if ($iapSale['sale_type'] == "F") { echo " checked"; } ?>
		>Facebook*&nbsp;&nbsp;&nbsp;
		<input type="radio" name="stype" id="stypeweb" value="W" tabindex="13" onchange='sSetType("W")'
			<?php if ($iapSale['sale_type'] == "W") { echo " checked"; } ?>
		>Website*&nbsp;&nbsp;&nbsp;
		<input type="radio" name="stype" id="stypeother" value="O" tabindex="13" onchange='sSetType("O")'
			<?php if ($iapSale['sale_type'] == "O") { echo " checked"; } ?>
		>Other*
		<br>
		*These Types of Sale do NOT affect your inventory.
	</td>
	</tr>
	</table>

		<div id=snonpediv1 style="display:none;">
			<table>
			<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>
			<tr><td style="width:16%;">
				<span class='iapFormLabel'>Date of Sale:</span></td>
				<td style="width:84%;">
					<input <?php echo $iapReadOnly; ?> tabindex="14" maxlength="15" size="15" name="ssaledate" id="ssaledate" placeholder="mm/dd/yyyy" value="<?php echo $d3; ?>" onchange='sChangedNonPE()'>
			</td></tr>
			<tr><td style="width:16%;"></td><td style="width:84%;">-- OR --</td></tr>
			</table>
		</div>

	<table>
	<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%;"><label class='iapFormLabel' id='spelabel'>Select a Party:</label></td>
		<td style="width:84%;">
	<?php
	$p = "none";
	if ($iapSale['sale_type'] != "E") {
		$p = "block";
	}
	$e = "none";
	if ($iapSale['sale_type'] == "E") {
		$e = "block";
	}

	$pn = " value='".$iapSale['sale_pe_name']."'";
	$s = $iapPE['pe_sponsor'];
	echo "<input ".$iapReadOnly." style='display:".$p.";' type='text' tabindex='15' name='speparty' id='speparty'  size='50'".$pn.">";
	echo "<input ".$iapReadOnly." style='display:".$e.";' type='text' tabindex='15' name='speevent' id='speevent'  size='50'".$pn.">";

	echo "</td></tr>";
	echo "<tr><td style='width:16%;'></td><td style='width:84%;'>";
	if ($iapReadOnly != "readonly") {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class=iapButton name='saddpe' id='saddpe' type='button' onclick='sAddPE()'>New Party</button>";
	}
	?>
	</td></tr>
	</table>

		<?php
			if ($iapSale['newpe'] == "Y") {
				$p = "block;";
			} else {
				$p = "none;";
			}
		?>
		<div id=iapNewPE style="display:<?php echo $p; ?>">
			<table>
			<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>
			<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class='iapFormLabel'>Please provide the following information regarding this new <span id='snewpecmt1'>party</span>. This new <span id='snewpecmt2'>party</span> will need to be editted later to enter any additional information.</span>
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='snewpedatelbl'>Date:</label></td>
				<td style="width:84%;"><input tabindex="17" type="text" name="snewpedate" id="snewpedate" size="15" placeholder="mm/dd/yyyy" <?php echo date("Y-m-d", strtotime($iapSale['sale_date'])); ?> onchange="newpedatechg();">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='snewpenamelbl'>Hostess:</label</td>
				<td style="width:84%;"><input tabindex="18" type="text" name="snewpename" id="snewpename" size="50">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>Street:</span></td>
				<td style="width:84%;"><input tabindex="19" type="text" name="snewpestrt" id="snewpestrt" size="50">
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='iapFormLabel'>City, State, Zip:</td>
				<td style="width:84%;">
					<input tabindex="20" type="text" name="snewpecity" id="snewpecity" size="30">
					<input tabindex="21" type="text" name="snewpestate" id="snewpestate" size="3">
					<input tabindex="22" type="text" name="snewpezip" id="snewpezip" size="10">
			</td></tr>
			</table>
		</div>

		<div id=snonpediv3 style="display:none;">
			<table>
			<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>
			<tr><td style="width:16%"><label class=iapFormLabel>Vendor Order No:</label></td>
				<td style="width:84%;">
				<input <?php echo $iapReadOnly; ?> type='text' tabindex='23' maxlength="15" size="15" name="svendord" id="svendord"><?php echo $iapSale['sale_vendor_order']; ?>
			</td></tr>
			</table>
		</div>

		<div id=snonpediv2 style="display:none;">
			<table>
			<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>
			<tr><td style="width:16%"><label class=iapFormLabel>Mileage:</label></td>
				<td style="width:84%;">
				<input <?php echo $iapReadOnly; ?> type='number' tabindex='24' maxlength="7" size="7" name="smileage" id="smileage" step="0.01"><?php echo $iapSale['sale_mileage']; ?>
			</td></tr>
			<tr><td style="width:16%"><label class=iapFormLabel>Other Expense:</label></td>
				<td style="width:84%;">
				<input <?php echo $iapReadOnly; ?> type='number' tabindex='25' maxlength="7" size="7" name="sotherexp" id="sotherexp" step="0.01"><?php echo $iapSale['sale_other_exp']; ?>
			</td></tr>
			<tr><td style="width:16%;"><label class=iapFormLabel>Explain Other Expenses:</label></td>
				<td style="width:84%;">
					<textarea name='sexpexplain' id='sexpexplain' tabindex="26" cols='50' rows='4' wrap='soft' <?php echo $iapReadOnly; ?>><?php echo $iapSale['sale_exp_explained']; ?></textarea>
			</td></tr>
			<tr><td style="width:16%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class='iapFormLabel' id='ssaleloclbl'>Location of Sale:</label</td>
				<td style="width:84%;"><input tabindex="27" type="text" name="ssaleloc" id="ssaleloc" size="50">
			</td></tr>
			</table>
		</div>

	<table>

	<?php
		if ($iapReadOnly != "readonly") {
	?>
			<tr><td colspan="2">
				<span class=iapFormLabel style="text-decoration: underline;">Enter An Item Sold</span>
				<span id=sitemerror class=iapError> </span>
			</td></tr>

			<tr><td style="width:16%">
				<label for="sItemCd" class=iapFormLabel id=sitemcodelbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Item Code:</label>
			</td>
			<td style="width:84%;">
				<input tabindex='21' size='10' name='sItemCd' id='sItemCd' onfocus='pItemFocus()' />
			</td></tr>
			<tr><td style="width:16%"></td>
			<td style="width:84%;">
				&nbsp;&nbsp;&nbsp;<span class=iapFormLabel>Start typing an item code for a list.</span>
			</td></tr>
			<tr><td style="width:16%"></td>
			<td style="width:84%;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' class=iapButton name='saddnewitem' id='saddnewitem' value='Add A New Item' onclick='sAddItem(); return false;' />";
			</td></tr>
			
			<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>

			<tr><td style="width:16%"></td>
				<label for="sItemDesc" class=iapFormLabel id=sItemDescLbl>Description:</label>
			<td style="width:84%;">
				<input tabindex='23' maxlength='100' size='50' name='sItemDesc' id='sItemDesc' onfocus='pItemFocus()' />
			</td></tr>
			<tr><td style="width:16%"></td>
			<td style="width:84%;">
				&nbsp;&nbsp;&nbsp;<span class=iapFormLabel>Start typing an item description for a list.</span>
			</td></tr>

			<tr><td colspan="2">
				<label class=iapFormLabel id=sitemqtylbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Quantity:</label>&nbsp;
				<input type='number' tabindex="31" maxlength="10" size="10" name="sitemqty" id="sitemqty" step="1">
				&nbsp;&nbsp;
				<label class=iapFormLabel id="sitempricelbl">Price:</label>&nbsp;
				<input type='number' tabindex="32" maxlength="10" size="10" name="sitemprice" id="sitemprice" step="0.01">
			</td></tr>
			</table>

			<div id="iapNewItem" style="text-align: left; display: none;">
				<table>
				<tr><td colspan="2">
					<label class=iapFormLabel id=snewitemcostlbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Item Cost:</label>&nbsp;
					<input type='number' tabindex="33" maxlength="10" size="10" name="snewitemcost" id="snewitemcost" step="0.01">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<label class=iapFormLabel id=snewitemunitslbl>Saleable Units In Package:</label>&nbsp;
					<input type='number' tabindex="34" maxlength="10" size="10" name="snewitemunits" id="snewitemunits" step="1">
				</td></tr>
				<tr><td colspan="2">
					<label class=iapFormLabel id=snewitemcatlbl>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Category:</label>&nbsp;
					<select tabindex="35" name="snewitemcat" id="snewitemcat" size='1'>
						<option value='---'>Select A Category</option><?php echo $iapCatOpts; ?>
					</select>
				</td></tr>
				</table>
			</div>
	<?php
		}
	?>

	<table>
	<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>

	<?php
		if ($iapReadOnly != "readonly") {
	?>
			<tr><td colspan="2" style="text-align:center;">
				<span class=iapFormLabel>
				<input <?php echo $iapReadOnly; ?> class=iapButton type='button' tabindex='36' name='sRecItem' id='sRecItem' value='Record This Item' onclick='sRecordItem(); return false;'></span>
			</td></tr>
			<tr><td colspan="2" style="text-align:center;">
				<span class=iapFormLabel >ONLY click Submit after all items have been entered. Check the list of items sold below.</span>
			</td></tr>
	<?php
		}
	?>
	</table>

	<br><br>
	<fieldset style='border: 1px solid #000; top: 5px; right: 5px; bottom: 5px; left: 5px;'>
	&nbsp;&nbsp;<span style="text-decoration: underline;">Items Sold</span> (Don't Forget To Click on Submit When All Items Have Been Recorded!)<br>
	<br>

	<table id='iapSold' class=iapTable><tbody class=iapTBody>
	<tr>
	<td width='5%'></td><td width='2%'></td>
	<td width='13%' class=iapFormLabel><span style='text-decoration: underline;'>Item Code</span></td>
	<td width='50%' class=iapFormLabel><span style='text-decoration: underline;'>Description</span></td>
	<td width='5%' class=iapFormLabel><span style='text-decoration: underline;'>Qty</span></td>
	<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Price</span></td>
	<td width='10%' class=iapFormLabel><span style='text-decoration: underline;'>Value</span></td>
	<td width='7%'></td>
	</tr>

	<?php
		$iapItems = $iapSale['saledtl'];
		$sRows = 0;
		foreach($iapItems as $iapI) {
			$sRows = $sRows + 1;
			$iapColumns = explode("~", $iapI);
			echo "<tr><td width='5%'></td><td width='2%'><img src='MyImages/Icons/DeleteRedSM.png' onclick='sDelSold(".$sRows."); return(false);'>&nbsp;&nbsp;</td>";
			echo "<td width='13%' class=iapFormLabel>".$iapColumns[0]."</td>";
			echo "<td width='55%' class=iapFormLabel>".$iapColumns[1]."</td>";
			echo "<td width='5%' class=iapFormLabel>".$iapColumns[2]."</td>";
			echo "<td width='10%' class=iapFormLabel>".$iapColumns[3]."</td>";
			echo "<td width='10%' class=iapFormLabel>".$iapColumns[2]*$iapColumns[3]."</td></tr>";
		}
	?>
	</tbody></table>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src='MyImages/Icons/Delete_IconSM.png'><span style='vertical-align: middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clicking on this symbol next to a row removes the row</span><br>
	</fieldset>

	<br><br>
	<table>
	<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%"><span class=iapFormLabel>Net Sale:</span>
		</td><td style="width:84%;">
			<input readonly style="text-align:right;" maxlength="15" size="15" name="snetsale" id="snetsale"> <!-- Display only, filled by script -->
	</td></tr>

	<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%"><span class=iapFormLabel>Sales Tax Rate(%):</span>
		</td><td style="width:84%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='37' maxlength="7" size="7" name="staxrate" id="staxrate" step="0.01" onchange="sTaxRateChg();" value="<?php echo number_format($iapSale['sale_tax_rate'] * 100, 4, '.', ''); ?>"> 
		&nbsp;&nbsp;&nbsp;
		<span class=iapFormLabel>Calculated Tax: (can override)</span>&nbsp;
		<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='38' maxlength="6" size="6" name="staxamt" id="staxamt" step="0.01" onchange="sTaxAmtChg();" value="<?php echo number_format($iapSale['sale_sales_tax'], 2, '.', ''); ?>">
	</td></tr>

	<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%"><span class=iapFormLabel>Shipping:</span>
		</td><td style="width:84%;">
			<input <?php echo $iapReadOnly; ?> style="text-align:right;" type='number' tabindex='39' maxlength="7" size="7" name="sshipping" id="sshipping" step="0.01" onchange="sShippingChg();" value="<?php echo number_format($iapSale['sale_shipping'], 2, '.', ''); ?>">
	</td></tr>

	<tr><td style="width:16%"></td><td style="width:84%;"></td></tr>
	-
	<tr><td style="width:16%"><span class=iapFormLabel style="font-size:larger; color:darkgreen;">TOTAL SALE:</span>
	<!-- Display only, filled by script -->
		</td><td style="width:84%;">
			<input readonly style="text-align:right; font-size:larger; color:darkgreen;" maxlength="15" size="15" name="stotalsale" id="stotalsale">
	</td></tr>

	<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>
	<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>

	<tr><td style="width:16%;"><label class=iapFormLabel id=scommlbl>Comments:</label></td>
		<td style="width:84%;"><textarea name='scomment' id='scomment' tabindex='44' cols='50' rows='5' wrap='soft' style="text-indent: 15;" <?php echo $iapReadOnly; ?>><?php echo $iapSale['sale_comment']; ?></textarea>
	</td></tr>

	<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>
	<tr><td style="width:16%;"></td><td style="width:84%;"></td></tr>

	<tr><td colspan="2" style='text-align:center;'>

	<?php
	if ($iapReadOnly != "readonly") {
		echo "<button class=iapButton name='ssubmit' id='ssubmit' tabindex='50' onclick='sSendForm(); return(true);'>Submit</button>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<button class=iapButton name='sclear' id='sclear' tabindex='51' onclick='sClearForm(); return(false);'>Clear</button>";
	}
	?></td></tr></table>

	<br><br><br>
	<input type="hidden" name="LHCA" id="LHCA" value="<?php echo $_REQUEST['CoId']; ?>">
	<input type='hidden' name='IAPMODE' id='IAPMODE' value="<?php echo $_REQUEST['UserData']['Mode']; ?>">
	<input type='hidden' name='IAPDL' id='IAPDL' value="">
	<input type='hidden' name='IAPDATA' id='IAPDATA' value="">
	<input type="hidden" name="SUPDATETYPE" id="SUPDATETYPE" value="">
	<input type="hidden" name="SALEID" id="SALEID" value="">
	<input type="hidden" name="SSTATUS" id="SSTATUS" value="">
	<input type="hidden" name="SDATE" id="SDATE" value="">
	<input type="hidden" name="STYPE" id="STYPE" value="Party">
	<input type="hidden" name="STAXOVERRIDE" id="STAXOVERRIDE" value="<?php echo $iapSale['sale_tax_override']; ?>">
	<input type="hidden" name="SNEWCUST" id="SNEWCUST" value="">
	<input type="hidden" name="SNEWPE" id="SNEWPE" value="">
	<input type="hidden" name="SPEID" id="SPEID" value="">
	<input type="hidden" name="SNEWITEMINFO" id="SNEWITEMINFO" value="">
	<input type="hidden" name="STHISITEMSTATUS" id="STHISITEMSTATUS" value="">

	</form>
	</p></div>

	<script src="<?php echo $_REQUEST['LHCUrl']; ?>Ajax/number_format.js" type="text/javascript"></script>

	<script type="text/javascript">

	// ---------------------------------------------------------------------------------
	//
	// Sale Field Functions
	//
	// ---------------------------------------------------------------------------------

	$(function() {
		var sSList = [<?php echo $sSales; ?>];
		$("#sSaleList").autocomplete({
			source: sSList,
			minLength: 0,
			change: function(sEvent, sSale) { 
						var saleId = sSale.item.id;
						sSale = iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
						document.getElementById("sSaleList").value = "";
						document.getElementById("sdetail").style.display="block";
						document.getElementById("scustomers").focus();
					}
		});

		var sCList = [<?php echo $sCusts; ?>];
		$("#scustomers").autocomplete({
			source: sCList,
			minLength: 0,
			change: function(sEvent, sCust) {
						var sHoldCust = sCust.item.value;
						sClearNewCust();
						document.getElementById("scustomers").value = sHoldCust;
						document.getElementById("SNEWCUST").value = "N";
						document.getElementById("iapNewCust").style.display="none";
					}
		});

		var sPList = [<?php echo $sParties; ?>];
		$("#speparty").autocomplete({
			source: sPList,
			minLength: 0,
			change: function(sEvent, sParty) {
						var sParty = sParty.item.value;
						document.getElementById("SDATE").value = sParty.item.date;
						document.getElementById("SNEWPE").value = "N";
						document.getElementById("snonpediv2").style.display="none";
						document.getElementById("iapNewPE").style.display="none";
					}
		});

		var sEList = [<?php echo $sEvents; ?>];
		$("#sevent").autocomplete({
			source: sEList,
			minLength: 0,
			change: function(sEvent, sEvt) {
						var sEvent = sEvt.item.value;
						document.getElementById("SDATE").value = sEvt.item.date;
						document.getElementById("SNEWPE").value = "N";
						document.getElementById("snonpediv2").style.display="none";
						document.getElementById("iapNewPE").style.display="none";
					}
		});

		var sItemList = [<?php echo $sCodes; ?>];
		$("#sItemCd").autocomplete({
			source: sItemList,
			minLength: 0,
			change: function(iEvent, iCode) { 
						var dateErr = sItemDateOK();
						if (dateErr == "Y") {
							return false;
						}
						sItem = iCode.item.value;
						if (sItem == "") {
							sGenerateError("Enter an item code or description then click Add Item!");
							return;
						}
						if (document.getElementById("STHISITEMSTATUS").value == "EXISTING") {
							document.getElementById("iapNewItem").style.display = "none";
							sGenerateError("<RESET>");
							document.getElementById("sitemdesc").value = "";	
						}
						var keyId = sItem + "~" + document.getElementById("SDATE").value;
						iapPrepCall("/Ajax/iapGetDB", "I#", keyId, sProcItem);
						return false;
					}
		});

		var sDescList = [<?php echo $sDescs; ?>];
		$("#sItemDesc").autocomplete({
			source: sDescList,
			minLength: 0,
			change: function(iEvent, iDesc) {
						var dateErr = sItemDateOK();
						if (dateErr == "Y") {
							return false;
						}
						sItem = iDesc.item.code;
						if (sItem == "") {
							sGenerateError("Enter an item code or description then click Add Item!");
							return;
						}
						if (document.getElementById("STHISITEMSTATUS").value == "EXISTING") {
							document.getElementById("iapNewItem").style.display = "none";
							sGenerateError("<RESET>");
							document.getElementById("sitemcode").value = "";
						}
						var keyId = sItem + "~" + document.getElementById("SDATE").value;
						iapPrepCall("/Ajax/iapGetDB", "I#", keyId, sProcItem);
						return false;
					}
		});
	});


	function sAddClicked() {
		sClearForm();
		document.getElementById("snewcnews").checked = false;

		document.getElementById("SUPDATETYPE").value = "NEW"; 
		document.getElementById("stypeparty").checked = true;
		sTurnPOn();
		document.getElementById("sdetail").style.display="block";
		document.getElementById("scustomers").focus();
	}

	function sSelectClicked() {
		var saleId = document.getElementById("sSelect").value;
		sSale = iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
		document.getElementById("sdetail").style.display="block";
		document.getElementById("scustomers").focus();
	}

	function sProcSale(sSale) {
		sClearForm();
		if (sSale < 0) {
			return sSale;
		} else if (sSale == 0) {
			document.getElementById("SUPDATETYPE").value = "NEW";
			document.getElementById("SALEID").value = "";
			return 0;
		} else {
	// set customer option
			document.getElementById('scustomers').value = sSale.cust_name;
			switch(sSale.sale_type) {
				case "P":
					document.getElementById("stypeparty").checked = true;
					break;
				case "E":
					document.getElementById("stypeevent").checked = true;
					break;
				case "I":
					document.getElementById("stypeindiv").checked = true;
					break;
				case "F":
					document.getElementById("stypefacebk").checked = true;
					break;
				case "W":
					document.getElementById("stypeweb").checked = true;
					break;
				case "O":
					document.getElementById("stypeother").checked = true;
					break;
				default:
					document.getElementById("stypeparty").checked = true;
			}
			sSetType(sSale.sale_type);
			if (sSale.sale_type == "E") {
				var sSearchKey = moment(sSale.pe_date).format("MM/DD/YYYY") + " event at " + sSale.pe_sponsor;
				document.getElementById('speevent').value = sSearchKey;
			} else {
				if (sSale.pe_party_no != "") {
					var sSearchKey = moment(sSale.pe_date).format("MM/DD/YYYY") + " " + sSale.pe_party_no + " " + sSale.pe_sponsor;
					document.getElementById('speparty').value = sSearchKey;
				}
				if (sSale.sale_type != "P") {
					document.getElementById('ssaledate').value = moment(sSale.sale_date).format("MM/DD/YYYY")
					document.getElementById('smileage').value = number_format(sSale.sale_miles, 2, '.', ',');
					document.getElementById('sotherexp').value = number_format(sSale.sale_other_exp, 2, '.', ',');
					document.getElementById('sexpexplain').value = sSale.sale_exp_explained;
					document.getElementById('ssaleloc').value = sSale.sale_location;
				}
			}
			document.getElementById('svendord').value = sSale.sale_vendor_order;

	// Get sales detail and build Sold table
			if (sSale.sale_items > 0) {
				iapPrepCall("/Ajax/iapGetDB", "SD", sSale.sale_id, sProcSaleDet);			
			}
			document.getElementById('snetsale').value = number_format(sSale.sale_net, 2, '.', ',');
			document.getElementById('staxrate').value = number_format(sSale.sale_tax_rate * 100, 4, '.', ',');
			document.getElementById('staxamt').value = number_format(sSale.sale_sales_tax, 2, '.', ',');
			document.getElementById('sshipping').value = number_format(sSale.sale_shipping, 2, '.', ',');
			document.getElementById('stotalsale').value = number_format(sSale.sale_total_amt, 2, '.', ',');

			document.getElementById('spaycash').checked = true;

			document.getElementById('scomment').value = sSale.sale_comment;
		}

		document.getElementById("SDATE").value = sSale.sale_date;
		document.getElementById("SUPDATETYPE").value = "EXISTING";
		document.getElementById("SALEID").value = sSale.sale_id;
		document.getElementById("sdetail").style.display="block";
		document.getElementById("scustomers").focus();
	}

	function sProcSaleDet(sSaleDet) {
		if (sSaleDet < 0) {
			return sSaleDet;
		} else if (sSaleDet == 0) {
			return sSaleDet;
		} else {
			var iSD = 0;
			var sdRec = "";
			for(iSD = 0; iSD < sSaleDet.length; iSD++) {
				sdRec = sSaleDet[iSD];
				sClearItem();
				document.getElementById("sitemcode").value = sdRec.saledet_item_code;
				document.getElementById("sitemdesc").value = sdRec.cat_description;
				document.getElementById('sitemqty').value = number_format(sdRec.saledet_quantity, 0, '.', ',');
				document.getElementById('sitemprice').value = number_format(sdRec.saledet_price, 2, '.', ',');
				document.getElementById("STHISITEMSTATUS").value == "EXISTING";
				sRecordItem();
			}
		}
	}

	function selectOptionByValue(sSelect, sValue) {
	// Find a given VALUE in a select and set as selected.
	// The VALUE is the value parameter of the option as <option value=___ NOT <option value=x>___
		for (var i=0; i < sSelect.options.length; i++)
		{
			if (sSelect.options[i].value === sValue) {
				sSelect.selectedIndex = i;
				break;
			}
		}
	}

	function selectOptionByHTML(sSelect, sValue) {
	// Find a given VALUE in a select and set as selected.
	// The VALUE is the value parameter of the option as <option value=x>___ NOT <option value=___ 
		for (var i=0; i < sSelect.options.length; i++)
		{
			if (sSelect.options[i].innerHTML === sValue) {
				sSelect.selectedIndex = i;
				break;
			}
		}
	}

	// ---------------------------------------------------------------------------------
	//
	// Customer Functions
	//
	// ---------------------------------------------------------------------------------

	function sClearNewCust() {
		document.getElementById("scustomers").value = "";
		document.getElementById("iapNewCust").style.display="none";
		document.getElementById("snewcname").value = "";
		document.getElementById("snewcbirth").value = "";
		document.getElementById("snewcstrt").value = "";
		document.getElementById("snewccity").value = "";
		document.getElementById("snewcstate").value = "";
		document.getElementById("snewczip").value = "";
		document.getElementById("snewcemail").value = "";
		document.getElementById("snewcphone").value = "";
	}

	function sAddCustomer() {
		sClearNewCust();
		document.getElementById("SNEWCUST").value = "Y";
		document.getElementById("iapNewCust").style.display="block";
		document.getElementById("snewcname").focus();
	}

	/*
	function sSelCustomer() {
		var sHoldCust = document.getElementById("scustomers").value;
		sClearNewCust();
		document.getElementById("scustomers").value = sHoldCust;
		document.getElementById("SNEWCUST").value = "N";
		document.getElementById("iapNewCust").style.display="none";
	}
	*/

	// ---------------------------------------------------------------------------------
	//
	// Type Functions
	//
	// ---------------------------------------------------------------------------------

	function sSetType(sTypeChosen) {
		switch(sTypeChosen) {
			case "P":
			 	document.getElementById("snewpenamelbl").innerHTML = "Hostess:";
			 	document.getElementById("STYPE").value = "Party";
			 	document.getElementById("saddpe").innerHTML = "New Party";
			 	document.getElementById("snewpecmt1").innerHTML = "party";
			 	document.getElementById("snewpecmt2").innerHTML = "party";
				sTurnPOn();
				break;
			case "E":
			 	document.getElementById("snewpenamelbl").innerHTML = "Sponsor:";
			 	document.getElementById("STYPE").value = "Event";
			 	document.getElementById("saddpe").innerHTML = "New Event";
			 	document.getElementById("snewpecmt1").innerHTML = "event";
			 	document.getElementById("snewpecmt2").innerHTML = "event";
				sTurnEOn();
				break;
			case "I":
			 	document.getElementById("STYPE").value = "Indivdual";
			 	document.getElementById("saddpe").innerHTML = " ";
				sTurnPEOff();
				break;
			case "F":
			 	document.getElementById("saddpe").innerHTML = " ";
			 	document.getElementById("STYPE").value = "Facebook";
				sTurnPEOff();
				break;
			case "W":
			 	document.getElementById("saddpe").innerHTML = " ";
			 	document.getElementById("STYPE").value = "Web";
				sTurnPEOff();
				break;
			case "O":
			 	document.getElementById("saddpe").innerHTML = " ";
			 	document.getElementById("STYPE").value = "Other";
				sTurnPEOff();
				break;
		}
	}

	function sTurnPOn() {
		sClearNewPE();
		document.getElementById("iapNewPE").style.display="none";
		document.getElementById("snonpediv1").style.display="none";
		document.getElementById("snonpediv2").style.display="none";
		document.getElementById("snonpediv3").style.display="none";
		document.getElementById("spelabel").innerHTML = "Select a Party: ";
		document.getElementById("SDATE").value = document.getElementById("speparty").value;
	 	document.getElementById("speevent").style.display = "none";
	 	document.getElementById("speparty").style.display = "block";
	}

	function sTurnEOn() {
		sClearNewPE();
		document.getElementById("iapNewPE").style.display="none";
		document.getElementById("snonpediv1").style.display="none";
		document.getElementById("snonpediv2").style.display="none";
		document.getElementById("snonpediv3").style.display="none";
		document.getElementById("spelabel").innerHTML = "Select an Event: ";
		document.getElementById("SDATE").value = document.getElementById("speevent").value;
	 	document.getElementById("speparty").style.display = "none";
	 	document.getElementById("speevent").style.display = "block";
	}

	function sTurnPEOff() {
		sClearNewPE();
		document.getElementById("iapNewPE").style.display="none";
		document.getElementById("spelabel").innerHTML = "Select a Party: ";
		document.getElementById("SDATE").value = document.getElementById("ssaledate").value;
	 	document.getElementById("speevent").style.display = "none";
	 	document.getElementById("speparty").style.display = "block";
	 	document.getElementById("snonpediv1").style.display="block";
		document.getElementById("snonpediv2").style.display="block";
		document.getElementById("snonpediv3").style.display="block";
	}


	// ---------------------------------------------------------------------------------
	//
	// Party/Event Functions
	//
	// ---------------------------------------------------------------------------------

	function sClearNewPE() {
		document.getElementById("iapNewPE").style.display="none";
		document.getElementById("snewpename").value = "";
		document.getElementById("snewpedate").value = "";
		document.getElementById("snewpestrt").value = "";
		document.getElementById("snewpecity").value = "";
		document.getElementById("snewpestate").value = "";
		document.getElementById("snewpezip").value = "";
	}

	function sAddPE() {
		sClearNewPE();
		document.getElementById("SNEWPE").value = "Y";
		document.getElementById("SPEID").value = "";
		document.getElementById("iapNewPE").style.display="block";
		document.getElementById("snewpedate").focus();
	}

	function sSelectP() {
	//	"Party ".$iapP['pe_party_no']." on ".date("m/d/Y", strtotime($iapP['pe_date']))." for ".trim($iapP['pe_sponsor']);
	//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);

		var sParty = document.getElementById("speparty").value;
		var sPs = sParty.split(" ");
		document.getElementById("SDATE").value = sPs[0];
		document.getElementById("SNEWPE").value = "N";
		document.getElementById("snonpediv2").style.display="none";
		return;
	}

	function sSelectE() {
	//	"Event on ".date("m/d/Y", strtotime($iapP['pe_date']))." at ".trim($iapP['pe_sponsor']);
	//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))."event at ".trim($iapP['pe_sponsor']);

		var sEvent = document.getElementById("speevent").value;
		var sEs = sEvent.split(" ");
		document.getElementById("SDATE").value = sEs[0];
		document.getElementById("SNEWPE").value = "N";
		return;
	}

	function newpedatechg() {
		var sNewPEDate = document.getElementById("snewpedate").value;
		if (sNewPEDate.trim() == "") {
			return;
		}
		document.getElementById("SDATE").value = sNewPEDate;
		return;
	}


	function sChangedNonPE() {
		var sNonPEDate = document.getElementById("ssaledate").value;
		if (sNonPEDate.trim() == "") {
			return;
		}
		sClearNewPE();
		document.getElementById("SDATE").value = sNonPEDate;
		document.getElementById("SPEID").value = "";
		return;
	}

	// ---------------------------------------------------------------------------------
	//
	// Shipping Change Function
	//
	// ---------------------------------------------------------------------------------

	function sShippingChg() {
		var sTotalNet = parseFloat(document.getElementById("snetsale").value);
		if (isNaN(sTotalNet)) {
			sTotalNet = 0;
		}
		sShowTotals(sTotalNet);
	}

	// ---------------------------------------------------------------------------------
	//
	// Tax Change Function
	//
	// ---------------------------------------------------------------------------------

	function sTaxRateChg() {
		var sT = parseFloat(document.getElementById("staxrate").value);
		if (isNaN(sT)) {
			document.getElementById("staxrate").style.color = "red";
			sGenerateError("Entered tax rate is invalid.");
			sT = 0;
			return;
		}
		var sTaxRate = sT / 100;
		var sTotalNet = parseFloat(document.getElementById("snetsale").value);
		if (isNaN(sTotalNet)) {
			sTotalNet = 0;
		}
		if (sTaxRate > 0) {
			sTax = sTotalNet * sTaxRate;
			document.getElementById("staxamt").value = number_format(sTax, 2, '.', ',');
		}
		document.getElementById("STAXOVERRIDE").value = "N";
		sShowTotals(sTotalNet);
	}

	function sTaxAmtChg() {
		var sT = parseFloat(document.getElementById("staxamt").value);
		if (isNaN(sT)) {
			document.getElementById("staxamt").style.color = "red";
			sGenerateError("Entered tax amount is invalid.");
			sT = 0;
			return;
		}
		var sTotalNet = parseFloat(document.getElementById("snetsale").value);
		if (isNaN(sTotalNet)) {
			sTotalNet = 0;
		}
		if (sT > 0) {
			document.getElementById("staxamt").value = number_format(sT, 2, '.', ',');
		}
		document.getElementById("STAXOVERRIDE").value = "Y";
		sShowTotals(sTotalNet);
	}


	// ---------------------------------------------------------------------------------
	//
	// Payment Fields Change Function
	//
	// ---------------------------------------------------------------------------------

	function sPayChg() {
		if (document.getElementById("spaycash").checked == true) {
			document.getElementById("sccexp").value = 0;
		} else if (document.getElementById("spaycredit").checked == true) {
			sCCChg();
		}
	}


	// ---------------------------------------------------------------------------------
	//
	// Credit Card Fields Change Function
	//
	// ---------------------------------------------------------------------------------

	function sCCChg() {

		var sCCFR = parseFloat(document.getElementById("sccflat").value);
		if (isNaN(sCCFR)) {
			document.getElementById("sccflat").style.color = "red";
			sGenerateError("Entered credit card flat rate is invalid.");
			sCCFR = 0;
			return;
		}
		var sCCP = parseFloat(document.getElementById("sccrate").value);
		if (isNaN(sCCP)) {
			document.getElementById("sccrate").style.color = "red";
			sGenerateError("Entered credit card percent is invalid.");
			sCCExp = 0;
			return;
		}
		if (document.getElementById("spaycredit").checked = true) {
			var sCCEx = sCCP / 100;
			var sTotalNet = parseFloat(document.getElementById("snetsale").value);
			if (isNaN(sTotalNet)) {
				sTotalNet = 0;
			}
			var sCCExp = sTotalNet * sCCEx;
			var sTotalCC = sCCFR + sCCEXp;
			document.getElementById("sccexp").value = sTotalCC;
		}
	}


	// ---------------------------------------------------------------------------------
	//
	// Item Functions
	//
	// ---------------------------------------------------------------------------------

	function sClearItem() {
		document.getElementById("sitemcode").value = "";
		document.getElementById("sitemdesc").value = "";
		document.getElementById("sitemqty").value = "";
		document.getElementById("sitemprice").value = "";
		document.getElementById("snewitemcost").value = "";
		document.getElementById("snewitemunits").value = "";
		document.getElementById("snewitemcat").selectedIndex = 0;
	}

	function sItemFocus() {
		if (document.getElementById("SDATE").value == "") {
			sGenerateError("Please select the appropriate Party, Event or enter the Sale Date above before entering items.");
		}
		return;
	}

	function sItemDateOK() {
		var dateMsg = "";
		var dateErr = "N";
		if (document.getElementById("SNEWPE").value == "Y") {
			if (document.getElementById("snewpedate").value == "") {
				dateErr = "Y";
				dateMsg = "Enter the date for the new " + document.getElementById("STYPE").value;
			}
		}
		if (document.getElementById("SDATE").value == "") {
			dateErr = "Y";
			if (document.getElementById("STYPE").value == "P") {
				dateMsg = "Select a Party";
			} else if (document.getElementById("STYPE").value == "E") {
				dateMsg = "Select an Event";
			} else {
				dateMsg = "Enter a valid Date";
			}
		}
		if (dateErr == "Y") {
			sGenerateError("<RESET>");
			dateMsg = dateMsg + " before entering items.";
			sGenerateError(dateMsg);		
		}
		return dateErr;
	}

	function sAddItem() {
		var dateErr = sItemDateOK();
		if (dateErr == "Y") {
			return false;
		}
		if (document.getElementById("sitemcode").value == "") {
			sGenerateError("Enter an item code then click Add.");
			document.getElementById("iapNewItem").style.display = "block";
			document.getElementById("sitemcode").focus();
			return false;
		}
		sGenerateError("<RESET>");
		if (document.getElementById("iapNewItem").style.display == "none") {
			document.getElementById("sitemqty").value = "";
			document.getElementById("sitemprice").value = "";
			document.getElementById("snewitemunits").value = "";
			document.getElementById("snewitemcost").value = "";
			document.getElementById("snewitemcat").selectedIndex = 0;
			document.getElementById("iapNewItem").style.display = "block";
			document.getElementById("sitemdesc").focus();
			document.getElementById("STHISITEMSTATUS").value = "NEW";
			return false;
		}
	}

	function sICodeClicked() {
		var dateErr = sItemDateOK();
		if (dateErr == "Y") {
			return false;
		}
		sItem = document.getElementById("sitemcode").value;
		if (sItem == "") {
			sGenerateError("Enter an item code or description then click Add Item!");
			return;
		}
		if (document.getElementById("STHISITEMSTATUS").value == "EXISTING") {
			document.getElementById("iapNewItem").style.display = "none";
			sGenerateError("<RESET>");
			document.getElementById("sitemdesc").value = "";	
		}
		var keyId = sItem + "~" + document.getElementById("SDATE").value;
		iapPrepCall("/Ajax/iapGetDB", "I#", keyId, sProcItem);
		return false;
	}

	function sIDescClicked() {
		var dateErr = sItemDateOK();
		if (dateErr == "Y") {
			return false;
		}
		sDesc = document.getElementById("sitemdesc").value;
		if (sDesc == "") {
			sGenerateError("Enter an item code or description then click Add Item!");
			return;
		}
		if (document.getElementById("STHISITEMSTATUS").value == "EXISTING") {
			document.getElementById("iapNewItem").style.display = "none";
			sGenerateError("<RESET>");
			document.getElementById("sitemcode").value = "";
		}
		var keyId = sDesc + "~" + document.getElementById("SDATE").value;
		iapPrepCall("/Ajax/iapGetDB", "IN", keyId, sProcItem);
		return false;
	}

	function sProcItem(sItem) {
		if (document.getElementById("STHISITEMSTATUS").value == "NEW") {
			sProcNewItem(sItem);
			return;
		}
		if (sItem == 0) {
			sAddItem();
			sGenerateError("This item was not found. Please enter all the information below.");
			return false;
		}
		sProcItemGood(sItem);
		return;
	}

	function sProcItemGood(sItem) {
		document.getElementById("sitemcode").value = sItem.cat_item_code;
		document.getElementById("sitemdesc").value = sItem.cat_description;
		document.getElementById("sitemprice").value = number_format(sItem.prc_price, 2, '.', ',');
		document.getElementById("STHISITEMSTATUS").value = "EXISTING";
		document.getElementById("sitemqty").focus();	
	}

	function sProcNewItem(sItem) {
		if (sItem != 0) {
			sProcItemGood(sItem);
		}		
		sItem = document.getElementById("sitemcode").value;
		if (document.getElementById("sitemcode").value == "") {
			document.getElementById("sitemcode").focus();
			return;
		}
		sDesc = document.getElementById("sitemdesc").value;
		if (sDesc == "") {
			document.getElementById("sitemdesc").focus();
			return;
		}
		document.getElementById("sitemqty").focus();
		return;
	}

	function sRecordItem() {
	// TODO need to open new item and new desc inputs if IAPDL = N

		sGenerateError("<RESET>");
		var sErrorFnd = "N";
		if (document.getElementById("sitemcode").value == "") {
			document.getElementById("sitemcodelbl").style.color = "red";
			sGenerateError("Item Code cannot be blank.");
			sErrorFnd = "Y";
		}
		if (document.getElementById("sitemdesc").value == "") {
			document.getElementById("sitemdesclbl").style.color = "red";
			sGenerateError("Description cannot be blank.");
			sErrorFnd = "Y";
		}
		if (document.getElementById("sitemqty").value == "") {
			document.getElementById("sitemqtylbl").style.color = "red";
			sGenerateError("Quantity cannot be blank.");
			sErrorFnd = "Y";
		}
		var sQtyIn = parseInt(document.getElementById("sitemqty").value);
		if (isNaN(sQtyIn)) {
			document.getElementById("sitemqtylbl").style.color = "red";
			sGenerateError("Quantity is invalid.");
			sErrorFnd = "Y";
		}
		if (document.getElementById("sitemprice").value == "") {
			document.getElementById("sitempricelbl").style.color = "red";
			sGenerateError("Selling Price cannot be blank.");
			sErrorFnd = "Y";
		}
		var sItemPrice = parseFloat(document.getElementById("sitemprice").value);
		if (isNaN(sItemPrice)) {
			document.getElementById("sitempricelbl").style.color = "red";
			sGenerateError("Selling Price is invalid.");
			sErrorFnd = "Y";
		}
		if (document.getElementById("STHISITEMSTATUS").value == "NEW") {
	/////// ---- was if dlist == "N"
			if (document.getElementById("sitemcode").value == "") {
				document.getElementById("sitemcodelbl").style.color = "red";
				sGenerateError("Item Code cannot be blank.");
				sErrorFnd = "Y";
			}
	///////
			if (document.getElementById("snewitemunits").value == "") {
				document.getElementById("snewitemunitslbl").style.color = "red";
				sGenerateError("Saleable Units cannot be blank.");
				sErrorFnd = "Y";
			}
			var sCostUnits = parseFloat(document.getElementById("snewitemcost").value);
			if (isNaN(sCostUnits)
			|| sCostUnits < 1) {
				document.getElementById("snewitemunitslbl").style.color = "red";
				sGenerateError("Saleable Units must be valid and > 0.");
				sErrorFnd = "Y";
			}
			if (document.getElementById("snewitemcost").value == "") {
				document.getElementById("snewitemcostlbl").style.color = "red";
				sGenerateError("Cost cannot be blank.");
				sErrorFnd = "Y";
			}
			var sItemCost = parseFloat(document.getElementById("snewitemcost").value);
			if (isNaN(sItemCost)) {
				document.getElementById("snewitemcostlbl").style.color = "red";
				sGenerateError("Cost is invalid.");
				sErrorFnd = "Y";
			}
			if (document.getElementById("snewitemcat").selectedIndex == 0) {
				document.getElementById("snewitemcatlbl").style.color = "red";
				sGenerateError("Select a valid category for this item.");
				sErrorFnd = "Y";
			}
		}
		if (sErrorFnd == "Y") {
			sGenerateError("All item fields must be valide prior to clicking Record Item.");
			return false;
		}
	// ----------------------------------------------------
	// ... Reset fields in case previous error
	// ----------------------------------------------------
		document.getElementById("sitemcodelbl").style.color = "#666666";
		document.getElementById("sitemdesclbl").style.color = "#666666";
		document.getElementById("sitemqtylbl").style.color = "#666666";
		document.getElementById("sitempricelbl").style.color = "#666666";
		document.getElementById("snewitemunitslbl").style.color = "#666666";
		document.getElementById("snewitemcostlbl").style.color = "#666666";
		document.getElementById("snewitemcatlbl").style.color = "#666666";
		sGenerateError("<RESET>");

	// ----------------------------------------------------
	// ... Get Item Code
	// ----------------------------------------------------
		if (document.getElementById("STHISITEMSTATUS").value == "NEW") {
			var sCode = document.getElementById("sitemcode").value;
			var sOption = document.createElement("option");

	//	TODO sDList.appendChild(sOption);

				var sDList = document.getElementById("iapItemDL");
				sOption.value = sCode;
		} else {
			sCode = document.getElementById("sitemcode").value;
		}
		var sQtyIn = parseInt(document.getElementById("sitemqty").value);
		var sItemPrc = parseFloat(document.getElementById("sitemprice").value);
		var sValueExt = parseFloat(sQtyIn * sItemPrc);
		var sTotalNet = parseFloat(document.getElementById("snetsale").value);
		if (isNaN(sTotalNet)) {
			sTotalNet = 0;
		}
		sTotalNet = sTotalNet + sValueExt;

		var sTable = document.getElementById("iapSold");
		var sNewRow = sTable.insertRow(-1);
		var sIndent = sNewRow.insertCell(0);
		var sDel = sNewRow.insertCell(1);
		var sCodeCell = sNewRow.insertCell(2);
		var sDescCell = sNewRow.insertCell(3);
		var sQtyCell = sNewRow.insertCell(4);
		var sPriceCell = sNewRow.insertCell(5);
		var sValueCell = sNewRow.insertCell(6);

		sIndent.innerHTML = "";
		var sRows = sTable.rows.length - 1;
		sNewRow.setAttribute("id", "Sold"+sRows , 0);
		var sEorN = document.getElementById("STHISITEMSTATUS").value;
		sEorN = sEorN.substr(0,1);
		sIndent.innerHTML = "<input type='hidden' id='recrow" + sRows + " value='" + sEorN + "'>";
		sDel.innerHTML = "<img src='MyImages/Icons/DeleteRedSM.png' onclick='sDelSold(" + sRows + "); return(false);'>&nbsp;&nbsp;";
		sCodeCell.innerHTML = sCode;
		sDescCell.innerHTML = document.getElementById("sitemdesc").value;
		sQtyCell.innerHTML = sQtyIn;
		sPriceCell.innerHTML = number_format(sItemPrc, 2, '.', ',');
		sValueCell.innerHTML = number_format(sValueExt, 2, '.', ',');

		sShowTotals(sTotalNet);

		var sNewData = document.getElementById("SNEWITEMINFO").value;
		var sItemCd = sCode;
		var sNewUnits = document.getElementById("snewitemunits").value;
		var sNewCost = document.getElementById("snewitemcost").value;
		var sNewCat = document.getElementById("snewitemcat").value;
		var sStatus = document.getElementById("STHISITEMSTATUS").value;
		document.getElementById("SNEWITEMINFO").value = sNewData + sItemCd + "~" + sStatus + "~" + sNewUnits + "~" + sNewCost + "~" + sNewCat + "|";

		sClearItem();
		document.getElementById("STHISITEMSTATUS").value = "";
		document.getElementById("sitemcode").focus();
		return false;
	}

	function sDelSold(sRow) {
		var sRowId = "Sold" + sRow;
		var sTblRow = document.getElementById(sRowId);
		var sTblCols = sTblRow.cells;
		var sValue = parseFloat(sTblCols[6].innerHTML);
		var sTotalNet = parseFloat(document.getElementById("snetsale").value);
		if (isNaN(sTotalNet)) {
			sTotalNet = 0;
		}
		sTotalNet = sTotalNet - sValue;
		sShowTotals(sTotalNet);
		sTblRow.parentNode.removeChild(sTblRow);
		return(false);
	}

	function sShowTotals(sTotalNet) {
		document.getElementById("snetsale").value =  number_format(sTotalNet, 2, '.', ',');

		var sShpn = parseFloat(document.getElementById("sshipping").value);
		if (isNaN(sShpn)) {
			sShpn = 0;
		}

		var sTaxOv = document.getElementById("STAXOVERRIDE").value;
		if (sTaxOv == "Y") {
			var sTax = parseFloat(document.getElementById("staxamt").value);
		} else {
			var sTax = 0;
			var sTaxRate = parseFloat(document.getElementById("staxrate").value);
			if (sTaxRate > 0) {
				sTax = sTotalNet * (sTaxRate / 100);
			}
		}
		document.getElementById("staxamt").value = number_format(sTax, 2, '.', ',');

		var sTotal = sTotalNet + sTax + sShpn;
		document.getElementById("stotalsale").value = number_format(sTotal, 2, '.', ',');

		var sCCFlat = parseFloat(document.getElementById("sccflat").value);
		var sCCPcnt = parseFloat(document.getElementById("sccrate").value);
		var sCCExp = 0;
		if (sCCPcnt > 0) {
			sCCExp = sTotal * (sCCPcnt / 100);
		}
		if (sCCFlat > 0) {
			sCCExp = sCCExp + sCCFlat;
		}
		document.getElementById("sccexp").value = number_format(sCCExp, 2, '.', ',');
	}

	// -----------------------------------------------------------------------------------
	//
	// Form Functions
	//
	// -----------------------------------------------------------------------------------

	function sClearForm() {
		document.getElementById("scustomers").value = "";
		sClearNewCust();
		document.getElementById("stypeparty").checked = true;
		document.getElementById("speparty").value = "";
		document.getElementById("speevent").value = "";
		sClearNewPE();
		document.getElementById("ssaledate").value = "";
		document.getElementById("spaycash").checked = true;
		document.getElementById("staxamt").value = "";
		document.getElementById("sshipping").value = "";
		document.getElementById("scomment").value = "";
		document.getElementById("snetsale").value = "";
		document.getElementById("sccflat").value = "";
		document.getElementById("sccrate").value = "";
		document.getElementById("sccexp").value = "";
		document.getElementById("stotalsale").value = "";
		document.getElementById("SDATE").value = "";
		document.getElementById("STAXOVERRIDE").value = "N";
		document.getElementById("STYPE").value = "Party";
		document.getElementById("SNEWCUST").value = "N";
		document.getElementById("SNEWPE").value = "N";
		document.getElementById("SNEWITEMINFO").value = "";
		document.getElementById("STHISITEMSTATUS").value = "";

		sClearItem();
		
	// clear table
		var sTable = document.getElementById("iapSold");
		var sRows = sTable.rows.length;
		while (sRows > 1) {
	        document.getElementById("iapSold").deleteRow(sRows - 1);
	        sRows--;
	    }
		return(false);
	}

	function sSendForm() {
		var sData = "";
		var sTable = document.getElementById('iapSold');
		var sFirst = "Y";
		for (var r = 1, n = sTable.rows.length; r < n; r++) {
	    	if (sFirst == "Y") {
				sFirst = "N";
			} else {
				sData = sData + "|";
			}
		    for (var c = 2, m = sTable.rows[r].cells.length; c < m; c++) {
				sData = sData + sTable.rows[r].cells[c].innerHTML + "~";
		    }
		}
		document.getElementById("IAPDATA").value = sData;	
		return(true);
	}


	// -----------------------------------------------------------------------------------
	//
	// Error Functions
	//
	// -----------------------------------------------------------------------------------

	function sGenerateError(sErrMsg) {

		if (sErrMsg == "<RESET>") {
			document.getElementById("sitemerror").innerHTML = " ";
			return;
		}
		var sExistingMsg = document.getElementById("sitemerror").innerHTML;
		var sBreak = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		document.getElementById("sitemerror").innerHTML = sExistingMsg + sBreak + sErrMsg;
	}

	</script>
<?php
}
?>