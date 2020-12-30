var gbl_net_sales = 0;
var gbl_cost_of_items = 0;
var gbl_profit = 0;
var gbl_shipping = 0;
var gbl_sales_tax = 0;
var gbl_total_sales = 0;

$(function() {
	$("#pPEList").autocomplete({
		source: pAllPEs,
		minLength: 0,
		change: function(pEvent, pPE) {
					document.getElementById("pesavenew").style.display="none";
					document.getElementById("pedelete").style.display="none";
					if (pPE.item == null) {
						document.getElementById("pError").style.display = "inline";	
					} else {
						document.getElementById("pesponsor").value = "";
						document.getElementById("pecustomer").value = "";
						var peId = pPE.item.id;
						peGetPE(peId);

						if (document.getElementById("PESALES").value > 0) {
							peShowSales(peId);
						}
						document.getElementById("pPEList").value = "";
						document.getElementById("pError").style.display = "none";	
//						document.getElementById("pechoose").style.display="none";
						document.getElementById("pedetail").style.display="inline";
						document.getElementById("pesavenew").style.display="inline";
						document.getElementById("pedelete").style.display="inline";
					}
				}
	});

	$("#pecustomer").autocomplete({
		source: pCusts,
		minLength: 0,
		change: function(CEvent, cCust) {
					if (cCust.item == null) {
						peProcCustomer(0);
					    document.getElementById("pec1name").value = document.getElementById("pecustomer").value;
					} else {
						var pCustNo = cCust.item.id;
						var typeFld = "C#";
						iapPrepCall("/Ajax/iapGetDB", typeFld, pCustNo, peProcCustomer);
						return;
					}
				}
	});
});

function peAddClicked() {
	peBlankPERec();
//	document.getElementById("pechoose").style.display="none"; 
	document.getElementById("pedetail").style.display="inline";
	document.getElementById("peparty").focus();
}

function peBlankPERec() {
	document.getElementById("petypeparty").checked = true;
	document.getElementById("pepartycomp").style.display = "none";
	document.getElementById("peparty").value = "";
	document.getElementById("pedate").value = "";
	document.getElementById("pestart").value = "";
	document.getElementById("peend").value = "";
	document.getElementById("pesponsor").value = "";
	document.getElementById("pecustomer").value = "";
	document.getElementById("pestreet").value = "";
	document.getElementById("pecity").value = "";
	document.getElementById("pestate").value = "";
	document.getElementById("pezip").value = "";
	document.getElementById("peurl").value = "";
	document.getElementById("pec1name").value = "";
	document.getElementById("pec1email").value = "";
	document.getElementById("pec1phone").value = "";
	document.getElementById("pec2name").value = "";
	document.getElementById("pec2email").value = "";
	document.getElementById("pec2phone").value = "";
	document.getElementById("pemiles").value = "";
	document.getElementById("pespaceexp").value = "";
	document.getElementById("peotherexp").value = "";
	document.getElementById("peexplexp").value = "";
	document.getElementById("peaddcal").checked = true;
	document.getElementById("peeventno").style.display = "none";
	document.getElementById("pecomments").value = "";

//	if (document.getElementById("pesalestbl").style.display == "block") {
// clear table
		document.getElementById("pesaletottitle").style.display = "none";
		var pTable = document.getElementById("pesalestbl");
		var pRows = pTable.rows.length;
		while (pRows > 1) {
			document.getElementById("pesalestbl").deleteRow(pRows - 1);
			pRows--;
		}
		document.getElementById("petnet").innerHTML = " ";
		document.getElementById("petship").innerHTML = " ";
		document.getElementById("pettax").innerHTML = " ";
		document.getElementById("petcost").innerHTML = " ";
		document.getElementById("petprofit").innerHTML = " ";
		document.getElementById("pettotal").innerHTML = " ";
		document.getElementById("pesalestbl").style.display="none";
//	}
	document.getElementById("PEID").value = "";
	document.getElementById("PESALES").value = "";
	document.getElementById("PEHOSTESSID").value = 0;
	document.getElementById("PETAXREGION").value="";
	document.getElementById("PETAXRATE").value=0;
	document.getElementById("PEUPDATETYPE").value = "NEW";
	pesetpartyon();
	return true;
}

function pGoClicked() {
/*
	var pPE = document.getElementById("pPEList").value;
	if (pPE == "") {
		document.getElementById("pError").style.display = "block";
		return false;
	}

//	$iapPEList = $iapPEList.$c.'{"label": "'.$p.'", "id": "'.strval($iapPE['pe_id']).'"}';

	pPartyId = -1;
	for (var i = 0, len = pAllPEs.length; i < len; i++) {
		var jNm = pAllPEs[i].label;
 		if (pAllPEs.label == pParty) {
			pPartyId = pAllPEs.id;
			break;
		}
	}
	if (pPartyId == -1) {
		document.getElementById("pError").style.display = "block";
		return false;
	}

	document.getElementById("pesponsor").value = "";
	document.getElementById("pecustomer").value = "";
	peGetPE(pPartyId);

	if (document.getElementById("PESALES").value > 0) {
		peShowSales(pPartyId);
	}
	document.getElementById("pPEList").value = "";
	document.getElementById("pError").style.display = "none";	
//	document.getElementById("pechoose").style.display="none"; 
	document.getElementById("pedetail").style.display="inline";
*/
}

function peGetPE(peId) {
	peBlankPERec();
	var typeFld = "E#";
	iapPrepCall("/Ajax/iapGetDB", typeFld, peId, peProcPartyEvent);
	return;
}

function peProcPartyEvent(pePartyEvent) {
	if (pePartyEvent == 0) {
		peBlankPERec();
	} else {
		if (pePartyEvent.pe_type == "E") {
			document.getElementById("petypeevent").checked = true;
			pesetpartyoff();
			document.getElementById("peparty").value = "";
		} else {
			document.getElementById("petypeparty").checked = true;
			pesetpartyon();
		    document.getElementById("PEHOSTESSID").value = pePartyEvent.pe_party_hostess;
			document.getElementById("peparty").value = pePartyEvent.pe_party_no;
			if (pePartyEvent.pe_party_complete == "Y") {
				document.getElementById("pepartycomp").style.display = "inline";
			}
		}
		document.getElementById("PEPARTYCOMP").value = pePartyEvent.pe_party_complete;
		document.getElementById("pedate").value = moment(pePartyEvent.pe_date).format("MM/DD/YYYY");
		var stm = pePartyEvent.pe_start_time;
		document.getElementById("pestart").value = stm.substr(0,5);
		var etm = pePartyEvent.pe_end_time;
		document.getElementById("peend").value = etm.substr(0,5);
		document.getElementById("pesponsor").value = pePartyEvent.pe_sponsor;
		document.getElementById("pecustomer").value = pePartyEvent.pe_sponsor;
		document.getElementById("pestreet").value = pePartyEvent.pe_street;
		document.getElementById("pecity").value = pePartyEvent.pe_city;
		document.getElementById("pestate").value = pePartyEvent.pe_state;
		document.getElementById("pezip").value = pePartyEvent.pe_zip;

		var a = pePartyEvent.pe_street.trim() + "| " +
				pePartyEvent.pe_city.trim() + ", " +
				pePartyEvent.pe_state.trim() + " " +
				pePartyEvent.pe_zip.trim();
		a = a.replace("||" , "|");
		if (a == ",  ") {
			document.getElementById("pemap").style.display = "none";
		} else {
			a = a.replace(" ", "+");
			a = a.replace("|", ",");
			a = "https://www.google.com/maps/place/" + a;
			var anc = document.getElementById("pemapa");
			anc.setAttribute("href", a);
			document.getElementById("pemap").style.display = "inline";
		}

		document.getElementById("peurl").value = pePartyEvent.pe_website;
		document.getElementById("pec1name").value = pePartyEvent.pe_contact1;
		document.getElementById("pec1email").value = pePartyEvent.pe_c1email;
		document.getElementById("pec1phone").value = pePartyEvent.pe_c1phone;
		document.getElementById("pec2name").value = pePartyEvent.pe_contact2;
		document.getElementById("pec2email").value = pePartyEvent.pe_c2email;
		document.getElementById("pec2phone").value = pePartyEvent.pe_c2phone;
		if (pePartyEvent.pe_event_id > 0) {
			document.getElementById("peaddcal").checked = true;
			document.getElementById("peeventno").innerHTML = "To edit the event use Event Id " + pePartyEvent.pe_event_id;
		} else {
			document.getElementById("peaddcal").checked = false;
			document.getElementById("peeventno").innerHTML = "";
		}
		document.getElementById("pemiles").value = number_format(pePartyEvent.pe_mileage, 2, '.', ',');
		if (pePartyEvent.pe_type == "E") {
			document.getElementById("pespaceexp").value = number_format(pePartyEvent.pe_space_charge, 2, '.', ',');
		}
		document.getElementById("peotherexp").value = number_format(pePartyEvent.pe_other_expenses, 2, '.', ',');
		document.getElementById("peexplexp").value = pePartyEvent.pe_exp_explained;

		if (pePartyEvent.pe_sales_cnt > 0) {
			if (pePartyEvent.pe_type == "E") {
				document.getElementById("pesaletotname").innerHTML = "Sales For This Event";
			} else if (pePartyEvent.pe_type == "P") {
				document.getElementById("pesaletotname").innerHTML = "Sales For This Party";
			} else if (pePartyEvent.pe_type == "F") {
				document.getElementById("pesaletotname").innerHTML = "Sales For This Facebook Party";
			}
			gbl_net_sales = pePartyEvent.pe_net_sales;
			gbl_cost_of_items = pePartyEvent.pe_cost_of_items;
			gbl_profit = pePartyEvent.pe_profit;
			gbl_shipping = pePartyEvent.pe_shipping;
			gbl_sales_tax = pePartyEvent.pe_sales_tax;
			gbl_total_sales = pePartyEvent.pe_total_sales;
			document.getElementById("pesaletottitle").style.display = "block";
		}
		document.getElementById("pecomments").value = pePartyEvent.pe_comment;
		document.getElementById("PEID").value = pePartyEvent.pe_id;
		document.getElementById("PESALES").value = pePartyEvent.pe_sales_cnt;
		document.getElementById("PETAXREGION").value=pePartyEvent.tax_combined_rate;
		document.getElementById("PETAXRATE").value=pePartyEvent.tax_region_name;
		document.getElementById("PEUPDATETYPE").value = "EXISTING";
	}
	return true;
}

function peShowSales(peId) {
	var typeFld = "SP";
	iapPrepCall("/Ajax/iapGetDB", typeFld, peId, peProcSales);
	return;
}

function peProcSales(peSales) {

// clear table
	var pTable = document.getElementById("pesalestbl");
	var pRows = pTable.rows.length;
	while (pRows > 1) {
        document.getElementById("pesalestbl").deleteRow(pRows - 1);
        pRows--;
    }

	if (peSales == 0) {
		document.getElementById("pesalestbl").style.display="none";
	    document.getElementById("PESALES").value = 0;
		return;
	} else {
	    document.getElementById("PESALES").value = peSales.length;
		var sTable = document.getElementById("pesalestbl");
		for (var r = 0, n = peSales.length; r < n; r++) {
			var sNewRow = sTable.insertRow(-1);
			sNewRow.setAttribute("id", "Sale"+r , 0);

			var sIndent = sNewRow.insertCell(0);
			var sCustCell = sNewRow.insertCell(1);
			var sNetCell = sNewRow.insertCell(2)
			var sShipCell = sNewRow.insertCell(3);
			var sTaxCell = sNewRow.insertCell(4);
			var sTotalCell = sNewRow.insertCell(5);
			var sCostCell = sNewRow.insertCell(6);
			var sProfitCell = sNewRow.insertCell(7);
			var sItemsCell = sNewRow.insertCell(8);
			var sPostdent = sNewRow.insertCell(9);
			sNetCell.setAttribute("style", "text-align:right;");
			sShipCell.setAttribute("style", "text-align:right;");
			sTaxCell.setAttribute("style", "text-align:right;");
			sTotalCell.setAttribute("style", "text-align:right;");
			sCostCell.setAttribute("style", "text-align:right;");
			sProfitCell.setAttribute("style", "text-align:right;");
			sItemsCell.setAttribute("style", "text-align:right;");
			var sSale = peSales[r];
			sIndent.innerHTML = "";
			sCustCell.innerHTML = "<a href='?page_id=291&action=selected&sale=" + sSale.sale_id + "'>" + sSale.cust_name + "</a>";
			sNetCell.innerHTML = number_format(sSale.sale_net, 2, '.', ',');
			sShipCell.innerHTML = number_format(sSale.sale_shipping, 2, '.', ',');
			sTaxCell.innerHTML = number_format(sSale.sale_sales_tax, 2, '.', ',');
			sTotalCell.innerHTML = number_format(sSale.sale_total_amt, 2, '.', ',');
			sCostCell.innerHTML = number_format(sSale.sale_item_cost, 2, '.', ',');
			sProfitCell.innerHTML = number_format(sSale.sale_profit, 2, '.', ',');
//			sItemsCell.innerHTML = number_format(sSale.sale_items, 0, '.', ',');
			sPostdent.innerHTML = "";
		}
		var tNewRow = sTable.insertRow(-1);
		tNewRow.setAttribute("id", "SaleT1" , 0);

		var tIndent = tNewRow.insertCell(0);
		var tTotalCell = tNewRow.insertCell(1);
		var tNetCell = tNewRow.insertCell(2);
		var tShipCell = tNewRow.insertCell(3);
		var tTaxCell = tNewRow.insertCell(4);
		var tTotalCell = tNewRow.insertCell(5);
		var tCostCell = tNewRow.insertCell(6);
		var tProfitCell = tNewRow.insertCell(7);
		var tItemsCell = tNewRow.insertCell(8);
		var tPostdent = tNewRow.insertCell(9);
		tIndent.innerHTML = "&nbsp;";
	    tTotalCell.innerHTML = "&nbsp;";
	    tNetCell.innerHTML = "&nbsp;";
	    tShipCell.innerHTML = "&nbsp;";
	    tTaxCell.innerHTML = "&nbsp;";
	    tTotalCell.innerHTML = "&nbsp;";
	    tCostCell.innerHTML = "&nbsp;";
	    tProfitCell.innerHTML = "&nbsp;";
		tItemsCell.innerHTML = "&nbsp;";
		tPostdent.innerHTML = "&nbsp;";

		var tNewRow2 = sTable.insertRow(-1);
		tNewRow2.setAttribute("id", "SaleT2" , 0);

		var tIndent2 = tNewRow2.insertCell(0);
		var tTitleCell2 = tNewRow2.insertCell(1);
		var tNetCell2 = tNewRow2.insertCell(2);
		var tShipCell2 = tNewRow2.insertCell(3);
		var tTaxCell2 = tNewRow2.insertCell(4);
		var tTotalCell2 = tNewRow2.insertCell(5);
		var tCostCell2 = tNewRow2.insertCell(6);
		var tProfitCell2 = tNewRow2.insertCell(7);
		var tItemsCell2 = tNewRow2.insertCell(8);
		var tPostdent2 = tNewRow2.insertCell(9);

		tNetCell2.setAttribute("style", "text-align:right;");
		tShipCell2.setAttribute("style", "text-align:right;");
		tTaxCell2.setAttribute("style", "text-align:right;");
		tTotalCell2.setAttribute("style", "text-align:right;");
		tCostCell2.setAttribute("style", "text-align:right;");
		tProfitCell2.setAttribute("style", "text-align:right;");

		tIndent2.innerHTML = "";
		tTitleCell2.innerHTML = "Total All Sales:";
	    tNetCell2.innerHTML = number_format(gbl_net_sales, 2, '.', ',');
	    tShipCell2.innerHTML = number_format(gbl_shipping, 2, '.', ',');
	    tTaxCell2.innerHTML = number_format(gbl_sales_tax, 2, '.', ',');
	    tTotalCell2.innerHTML = number_format(gbl_total_sales, 2, '.', ',');
	    tCostCell2.innerHTML = number_format(gbl_cost_of_items, 2, '.', ',');
	    tProfitCell2.innerHTML = number_format(gbl_profit, 2, '.', ',');
		tItemsCell2.innerHTML = "";
		tPostdent2.innerHTML = "";

		document.getElementById("pesalestbl").style.display="inline"; 
	}
}

function pesetpartyon() {
	document.getElementById("pepartycomp").style.display = "none";
	if (document.getElementById("PEPARTYCOMP").value == "Y") {
		document.getElementById("pepartycomp").style.display = "inline";
	}
	document.getElementById("pepartylbl").innerHTML = "Party Number: ";
 	document.getElementById("peparty").type = "text";
 	document.getElementById("pesponsorlbl").innerHTML = "Hostess:";
 	document.getElementById("pesponsor").type = "hidden";
 	document.getElementById("pecustomer").type = "text";
 	document.getElementById("peparty").focus();
 	return false;
}

function pesetpartyoff() {
	document.getElementById("pepartycomp").style.display = "none";
	document.getElementById("pepartylbl").innerHTML = " ";
	document.getElementById("peparty").type = "hidden";
 	document.getElementById("pesponsorlbl").innerHTML = "Sponsor:";
 	document.getElementById("pesponsor").type = "text";
 	document.getElementById("pecustomer").type = "hidden";
    document.getElementById("PEHOSTESSID").value = 0;
	document.getElementById("pedate").focus();
 	return false;
}

function peProcCustomer(cCustomer) {
	if (cCustomer == 0) {
		document.getElementById("pestreet").value = "";
		document.getElementById("pecity").value = "";
		document.getElementById("pestate").value = "";
		document.getElementById("pezip").value = "";
	    document.getElementById("pec1name").value = "";
	    document.getElementById("pec1email").value = "";
	    document.getElementById("pec1phone").value = "";
	    document.getElementById("PEHOSTESSID").value = 0;
	} else {
		document.getElementById("pestreet").value = cCustomer['cust_street'];
		document.getElementById("pecity").value = cCustomer['cust_city'];
		document.getElementById("pestate").value = cCustomer['cust_state'];
		document.getElementById("pezip").value = cCustomer['cust_zip'];
	    document.getElementById("pec1name").value = cCustomer['cust_name'];
	    document.getElementById("pec1email").value = cCustomer['cust_email'];
	    document.getElementById("pec1phone").value = cCustomer['cust_phone'];
	    document.getElementById("PEHOSTESSID").value = cCustomer['cust_no'];
	}
}