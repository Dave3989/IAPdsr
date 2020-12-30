function cAddClicked() {
	cblankCustomer();
	document.getElementById("CSTATUS").value = "NEW";
//	document.getElementById("cchoose").style.display="none"; 
	document.getElementById("cdetail").style.display="block";
	document.getElementById("cname").focus();
}

function cblankCustomer() {
	document.getElementById("cname").value = "";
	document.getElementById("cname").focus();
	document.getElementById("cbirth").value = "";
	document.getElementById("canniv").value = "";
	document.getElementById("cstreet").value = "";
	document.getElementById("ccity").value = "";
	document.getElementById("cstate").value = "";
	document.getElementById("czip").value = "";
	document.getElementById("cmap").style.display = "none";
	document.getElementById("cphone").value = "";
	document.getElementById("cemail").value = "";
	document.getElementById("cfacebk").value = "";
	document.getElementById("cnewsltr").checked = true;
	document.getElementById("cfollowcons").checked = false;
	document.getElementById("cfollowparty").checked = false;
	document.getElementById("cmetdate").value = "";
	document.getElementById("cmetat").value = "";
	document.getElementById("cnotes").value = "";
	document.getElementById("CUPDATETYPE").value = "NEW";
	document.getElementById("CCUSTNO").value = "";

// clear history
	var cTable = document.getElementById("csalestbl");
	var cRows = cTable.rows.length;
	while (cRows > 4) {
        document.getElementById("csalestbl").deleteRow(cRows - 1);
        cRows--;
    }

	document.getElementById("csalestbl").style.display="none";

	return true;
}

$(function() {
	$("#cCustList").autocomplete({
		source: acCustomers,
		minLength: 0,
		change: function(cEvent, cName) { 
						cgetCust();
					}
	});

	$("#cEmailList").autocomplete({
		source: acEmails,
		minLength: 0,
		change: function(cEvent, cEmail) { 
						cgetEmail();
					}
	});

	$("#cPhoneList").autocomplete({
		source: acPhones,
		minLength: 0,
		change: function(cEvent, cPhone) { 
						cgetPhone();
					}
	});

	$("#cpeparty").autocomplete({
		source: cPList,
		minLength: 0,
		change: function(cEvent, cParty) {
					if (document.getElementById("cpeparty").value  == "") {
						document.getElementById("cparError").style.display="inline";
						document.getElementById("cpeparty").focus();	
					} else {
						var MyID = cParty.item.id;

//	"Party ".$iapP['pe_party_no']." on ".date("m/d/Y", strtotime($iapP['pe_date']))." for ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
						var cPar = document.getElementById("cpeparty").value;
						var cPs = cPar.split(" ");
						document.getElementById("cmetdate").value = cPs[0];
						document.getElementById("cmetat").value = cPar;
						document.getElementById("CSELPE").value = cParty.item.id;
					}
				}
	});

	$("#cpeevent").autocomplete({
		source: cEList,
		minLength: 0,
		change: function(cEvent, cEvent) {
					if (document.getElementById("cpeevent").value  == "") {
						document.getElementById("cevtError").style.display="inline";
						document.getElementById("cpeevent").focus();	
					} else {
						var MyID = cEvent.item.id;

//	"Event on ".date("m/d/Y", strtotime($iapP['pe_date']))." at ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))."event at ".trim($iapP['pe_sponsor']);

						var cEvt = document.getElementById("cpeevent").value;
						var cEs = cEvt.split(" ");
						document.getElementById("cmetdate").value = cEs[0];
						document.getElementById("cmetat").value = cEvt;

						document.getElementById("CSELPE").value = cEvent.item.id;
					}
				}
	});
});

function cGoClicked() {
	cNm = document.getElementById("cCustList").value;
	if (cNm == "") {
		cEm = document.getElementById("cEmailList").value;
		if (cEm == "") {
			document.getElementById("cError").style.display = "block";
		} else {
			cgetEmail();
		}
	} else {
		cgetCust();
	}
}

function cgetCust() {
	var cNm = document.getElementById("cCustList").value;
	var typeFld = "CN";
	iapPrepCall("/Ajax/iapGetDB", typeFld, cNm, cProcCustomer);
	document.getElementById("cCustList").value="";
	if (document.getElementById("CSTATUS").value != "NEW") {
		cShowItems();
	}
	document.getElementById("cname").focus(); 											
	return;
}

function cgetEmail() {
	var cEm = document.getElementById("cEmailList").value;
	var typeFld = "CE";
	iapPrepCall("/Ajax/iapGetDB", typeFld, cEm, cProcCustomer);
	document.getElementById("cEmailList").value="";
	if (document.getElementById("CSTATUS").value != "NEW") {
		cShowItems();
	}
	document.getElementById("cname").focus(); 											
	return;
}

function cgetPhone() {
	var cPh = document.getElementById("cPhoneList").value;
	var typeFld = "CP";
	iapPrepCall("/Ajax/iapGetDB", typeFld, cPh, cProcCustomer);
	document.getElementById("cPhoneList").value="";
	if (document.getElementById("CSTATUS").value != "NEW") {
		cShowItems();
	}
	document.getElementById("cname").focus(); 											
	return;
}

function cProcCustomer(cCustomer) {
	if (cCustomer == 0) {
//		document.getElementById("CSTATUS").value = "NEW";
//		cblankCustomer();
		document.getElementById("cError").style.display = "inline";
		return;
	} else {
		document.getElementById("CSTATUS").value = "EXISTING";

		document.getElementById("cname").value = cCustomer.cust_name;
		document.getElementById("cstreet").value = cCustomer.cust_street;
		document.getElementById("ccity").value = cCustomer.cust_city;
		document.getElementById("cstate").value = cCustomer.cust_state;
		document.getElementById("czip").value = cCustomer.cust_zip;

		var a = cCustomer.cust_street.trim() + "| " +
				cCustomer.cust_city.trim() + ", " +
				cCustomer.cust_state.trim() + " " +
				cCustomer.cust_zip.trim();
		a = a.replace("||" , "|");
		if (a == ",  ") {
			document.getElementById("cmap").style.display = "none";
		} else {
			a = a.replace(" ", "+");
			a = a.replace("|", ",");
			a = "https://www.google.com/maps/place/" + a;
			var anc = document.getElementById("cmapa");
			anc.setAttribute("href", a);
			document.getElementById("cmap").style.display = "inline";
		}
		document.getElementById("cemail").value = cCustomer.cust_email;
		document.getElementById("cfacebk").value = cCustomer.cust_facebook;

/*
		vCard = cGenerateVCard(cCustomer);
*/

		if (empty(cCustomer.cust_phone)) {
			document.getElementById("cphone").value = "";
		} else {
			document.getElementById("cphone").value = cCustomer.cust_phone;
		}

		if (empty(cCustomer.cust_birthday)) {
			document.getElementById("cbirth").value = "";
		} else {
			document.getElementById("cbirth").value = moment(cCustomer.cust_birthday).format("MM/DD");			
		}

		if (empty(cCustomer.cust_anniversary)) {
			document.getElementById("canniv").value = "";
		} else {
			document.getElementById("canniv").value = moment(cCustomer.cust_anniversary).format("MM/DD");			
		}

	    document.getElementById("cnewsltr").checked = false;
		if (cCustomer.cust_newsletter == "Y") {
		    document.getElementById("cnewsltr").checked = true;
		}

		document.getElementById("cfollowcons").checked = false;
		if (cCustomer.cust_followup_consultant == "Y") {
			document.getElementById("cfollowcons").checked = true;
		}

		document.getElementById("cfollowparty").checked = false;
		if (cCustomer.cust_followup_party == "Y") {
			document.getElementById("cfollowparty").checked = true;
		}

		if (cCustomer.cust_met_date == "0000-00-00") {
			document.getElementById("cmetdate").value = "";
		} else {
			document.getElementById("cmetdate").value = moment(cCustomer.cust_met_date).format("MM/DD/YYYY");
		}
		if (empty(cCustomer.cust_met_at)) {
			document.getElementById("cmetat").value = "";
		} else {
			document.getElementById("cmetat").value = cCustomer.cust_met_at;
		}

		if (empty(cCustomer.cust_notes)) {
			document.getElementById("cnotes").value = "";
		} else {
			document.getElementById("cnotes").value = cCustomer.cust_notes;
		}

		document.getElementById("CUPDATETYPE").value = "EXISTING";
		document.getElementById("CCUSTNO").value = cCustomer.cust_no;
		document.getElementById("cError").style.display = "none";
		document.getElementById("cdetail").style.display="block";
	}
	return true;
}


// ---------------------------------------------------------------------------------
//
// Type Functions
//
// ---------------------------------------------------------------------------------

function sSetType(sTypeChosen) {
	switch(sTypeChosen) {
		case "P":
		 	document.getElementById("CTYPE").value = "Party";
			document.getElementById("cpelabel").innerHTML = "Select a Party: ";
			document.getElementById("cpelabel").style.display = "inline";
		 	document.getElementById("cpeevent").style.display = "none";
		 	document.getElementById("cpeparty").style.display = "inline";
			break;
		case "E":
		 	document.getElementById("CTYPE").value = "Event";
			document.getElementById("cpelabel").innerHTML = "Select an Event: ";
			document.getElementById("cpelabel").style.display = "inline";
		 	document.getElementById("cpeparty").style.display = "none";
		 	document.getElementById("cpeevent").style.display = "inline";
			break;
		case "N":
		 	document.getElementById("CTYPE").value = "Neither";
			document.getElementById("cpelabel").innerHTML = " ";
			document.getElementById("cpelabel").style.display = "none";
		 	document.getElementById("cpeevent").style.display = "none";
		 	document.getElementById("cpeparty").style.display = "none";
			break;
	}
}

function cShowItems() {
// clear table
	var cTable = document.getElementById("csalestbl");
	var cRows = cTable.rows.length;
	while (cRows > 4) {
		document.getElementById("csalestbl").deleteRow(cRows - 1);
		cRows--;
	}
	var typeFld = "S2";
	var custNo = document.getElementById("CCUSTNO").value;
	iapPrepCall("/Ajax/iapGetDB", typeFld, custNo, cProcItems);
	return;
}

function cProcItems(cItems) {
	var subtot_price = 0;
	var subtot_cost = 0;
	var total_price = 0;
	var total_cost = 0;

	if (cItems == 0) {
		document.getElementById("csalestbl").style.display="none";
		return;
	} else {
		var sTable = document.getElementById("csalestbl");
		var sLastKey = "";
		var sType = "";
		var n = cItems.length;
		for (var r = 0; r < n; r++) {
			var sItem = cItems[r];

			var sItem_code = sItem.saledet_item_code;
			if (!sItem.CO_description) {
				var sItem_description = sItem.SUPP_description;
			} else {
				var sItem_description = sItem.CO_description;
			}

			sKey = moment(sItem.sale_date).format("MM/DD/YYYY") + sItem.pe_sponsor + sItem.saledet_sid;
			if (sKey != sLastKey) {
				if (sLastKey != "") {
/*
					var stNewRow = sTable.insertRow(-1);
					stNewRow.setAttribute("id", "ItemT1" , 0);

					var stIndent = stNewRow.insertCell(0);
					var stItemCell = stNewRow.insertCell(1);
					var stDescCell = stNewRow.insertCell(2);
					var stQtyCell = stNewRow.insertCell(3);
					var stPriceCell = stNewRow.insertCell(4);
					var stTPriceCell = stNewRow.insertCell(5);
					var stTCostCell = stNewRow.insertCell(6);
					var stPostdent = stNewRow.insertCell(7);
					stIndent.innerHTML = "&nbsp;";
					stItemCell.innerHTML = "&nbsp;";
					stDescCell.innerHTML = "&nbsp;";
					stQtyCell.innerHTML = "&nbsp;";
					stPriceCell.innerHTML = "&nbsp;";
					stTPriceCell.innerHTML = "&nbsp;";
					stTCostCell.innerHTML = "&nbsp;";
					stPostdent.innerHTML = "&nbsp;";
*/
					var st2NewRow = sTable.insertRow(-1);
					st2NewRow.setAttribute("id", "ItemT2" , 0);

					var st2Indent = st2NewRow.insertCell(0);
					var st2ItemCell = st2NewRow.insertCell(1);
					var st2DescCell = st2NewRow.insertCell(2);
					var st2QtyCell = st2NewRow.insertCell(3);
					var st2PriceCell = st2NewRow.insertCell(4);
					var st2TPriceCell = st2NewRow.insertCell(5);
					var st2TCostCell = st2NewRow.insertCell(6);
					var st2Postdent = st2NewRow.insertCell(7);

					st2TPriceCell.setAttribute("style", "text-align:right;");
					st2TCostCell.setAttribute("style", "text-align:right;");

					st2Indent.innerHTML = "&nbsp;";
					st2ItemCell.innerHTML = "&nbsp;";
					st2DescCell.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total This Sale";
					st2QtyCell.innerHTML = "&nbsp;";
					st2PriceCell.innerHTML = "&nbsp;";
					st2TPriceCell.innerHTML = "&nbsp;";
					st2TCostCell.innerHTML = "&nbsp;";
					st2Postdent.innerHTML = "&nbsp;";

					st2TPriceCell.innerHTML = number_format(subtot_price, 2, '.', ',');
					st2TCostCell.innerHTML = number_format(subtot_cost, 2, '.', ',');
					st2Postdent.innerHTML = "";
					subtot_cost = 0;
					subtot_price = 0;
				}
				sLastKey = sKey;
				var sPELink = "N";
				switch(sItem.pe_type) {
					case "E":
						sType = "Event";
						sPELink = "E";
						break;
					case "P":
						sType = "Party " + sItem.pe_party_no.toString();
						sPELink = "P";
						break;
					case "I":
						sType = "Sale To Individual";
						if (empty(sItem.pe_party_no) == false) {
							sPELink = "P";
							sType = sType.concat(" For Party ", sItem.pe_party_no.toString());
						}
						break;
					case "F":
						sType = "Facebook Party - Party Number " + sItem.pe_party_no.toString();
						sPELink = "P";
						break;
					case "W":
						sType = "Sale From Website";
						if (empty(sItem.pe_party_no) == false) {
							sPELink = "P";
							sType = sType.concat(" For Party ", sItem.pe_party_no.toString());
						}
						break;
					case "X":
						sType = "Exchange";
						break;
					case "O":
						sType = "Other Type Sale";
						break;
					default:
						sType = "Unknown Type";
				}

				var sBlankRow = sTable.insertRow(-1);
				sBlankRow.setAttribute("id", "ItemB"+r , 0);

				var sBIndent = sBlankRow.insertCell(0);
				var sBDate = sBlankRow.insertCell(1);
				var sBWhere = sBlankRow.insertCell(2);
				var sBPostdent = sBlankRow.insertCell(3);

				sBPostdent.setAttribute("colSpan", 5);

				sBIndent.innerHTML = "&nbsp;";
				sBDate.innerHTML = "&nbsp;";
				sBWhere.innerHTML = "&nbsp;";
				sBPostdent.innerHTML = "&nbsp;";

				var sNewRow2 = sTable.insertRow(-1);
				sNewRow2.setAttribute("id", "Item2"+r , 0);

				var sIndent2 = sNewRow2.insertCell(0);
				var sDateCell = sNewRow2.insertCell(1);
				var sWhereCell = sNewRow2.insertCell(2);
				var sPostdent2 = sNewRow2.insertCell(3);

				sPostdent2.setAttribute("colSpan", 5);

				sIndent2.innerHTML = "";

				var sPELinkText1 = "";
				var sPELinkText2 = "";
				if (sPELink == "E") {
					sPELinkText1 = sPELinkText1.concat("<a href='partyevent/?action=selected&peid=", sItem.sale_peid.toString(), "'>");
				}
				if (sPELink == "P") {
					sPELinkText1 = sPELinkText1.concat("<a href='partyevent/?action=selected&peid=", sItem.sale_peid.toString(), "'>");
				}
				if (sPELink != "N") {
					sPELinkText2 = "</a>";
				}
			    sDateCell.innerHTML = sDateCell.innerHTML.concat(sPELinkText1, moment(sItem.sale_date).format("MM/DD/YYYY"), sPELinkText2);
			    sWhereCell.innerHTML = sItem.pe_sponsor;
				sPostdent2.innerHTML = sType;
			}

			var sNewRow = sTable.insertRow(-1);
			sNewRow.setAttribute("id", "Item"+r , 0);

			var sIndent = sNewRow.insertCell(0);
			var sItemCell = sNewRow.insertCell(1);
			var sDescCell = sNewRow.insertCell(2);
			var sQtyCell = sNewRow.insertCell(3);
			var sPriceCell = sNewRow.insertCell(4);
			var sTPriceCell = sNewRow.insertCell(5);
			var sTCostCell = sNewRow.insertCell(6);
			var sPostdent = sNewRow.insertCell(7);

			sDescCell.setAttribute("style", "padding-left:10px;");
			sQtyCell.setAttribute("style", "text-align:right;");
			sPriceCell.setAttribute("style", "text-align:right;");
			sTPriceCell.setAttribute("style", "text-align:right;");
			sTCostCell.setAttribute("style", "text-align:right;");

			sIndent.innerHTML = "";
			sItemCell.innerHTML = "&nbsp;&nbsp;<a href='catalog/?action=selected&item=" + sItem_code + "'>" + sItem_code + "</a>";
			sDescCell.innerHTML = sItem_description;
			sQtyCell.innerHTML = number_format(sItem.saledet_quantity, 0, '.', ',');
			sPriceCell.innerHTML = number_format(sItem.saledet_price, 2, '.', ',');
			sTPriceCell.innerHTML = number_format(sItem.saledet_total_price, 2, '.', ',');
			sTCostCell.innerHTML = number_format(sItem.saledet_total_cost, 2, '.', ',');
			sPostdent.innerHTML = "";

			sItemTPrice = parseFloat(sItem.saledet_total_price);
			if (isNaN(sItemTPrice)) {
				sItemTPrice = 0;
			}			
			subtot_price = subtot_price + sItemTPrice;
			total_price = total_price + sItemTPrice;
			sItemTCost = parseFloat(sItem.saledet_total_cost);
			if (isNaN(sItemTCost)) {
				sItemTCost = 0;
			}			
			subtot_cost = subtot_cost + sItemTCost;
			total_cost = total_cost + sItemTCost;
		}
		var st2NewRow = sTable.insertRow(-1);
		st2NewRow.setAttribute("id", "ItemT2" , 0);

		var st2Indent = st2NewRow.insertCell(0);
		var st2ItemCell = st2NewRow.insertCell(1);
		var st2DescCell = st2NewRow.insertCell(2);
		var st2QtyCell = st2NewRow.insertCell(3);
		var st2PriceCell = st2NewRow.insertCell(4);
		var st2TPriceCell = st2NewRow.insertCell(5);
		var st2TCostCell = st2NewRow.insertCell(6);
		var st2Postdent = st2NewRow.insertCell(7);

		st2TPriceCell.setAttribute("style", "text-align:right;");
		st2TCostCell.setAttribute("style", "text-align:right;");

		st2Indent.innerHTML = "&nbsp;";
		st2ItemCell.innerHTML = "&nbsp;";
		st2DescCell.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total This Sale";
		st2QtyCell.innerHTML = "&nbsp;";
		st2PriceCell.innerHTML = "&nbsp;";
		st2TPriceCell.innerHTML = "&nbsp;";
		st2TCostCell.innerHTML = "&nbsp;";
		st2Postdent.innerHTML = "&nbsp;";

		st2TPriceCell.innerHTML = number_format(subtot_price, 2, '.', ',');
		st2TCostCell.innerHTML = number_format(subtot_cost, 2, '.', ',');
		st2Postdent.innerHTML = "";

		var tNewRow = sTable.insertRow(-1);
		tNewRow.setAttribute("id", "ItemT1" , 0);

		var tIndent = tNewRow.insertCell(0);
		var tItemCell = tNewRow.insertCell(1);
		var tDescCell = tNewRow.insertCell(2);
		var tQtyCell = tNewRow.insertCell(3);
		var tPriceCell = tNewRow.insertCell(4);
		var tTPriceCell = tNewRow.insertCell(5);
		var tTCostCell = tNewRow.insertCell(6);
		var tPostdent = tNewRow.insertCell(7);
		tIndent.innerHTML = "&nbsp;";
		tItemCell.innerHTML = "&nbsp;";
		tDescCell.innerHTML = "&nbsp;";
		tQtyCell.innerHTML = "&nbsp;";
		tPriceCell.innerHTML = "&nbsp;";
		tTPriceCell.innerHTML = "&nbsp;";
		tTCostCell.innerHTML = "&nbsp;";
		tPostdent.innerHTML = "&nbsp;";

		var t2NewRow = sTable.insertRow(-1);
		t2NewRow.setAttribute("id", "ItemT2" , 0);

		var t2Indent = t2NewRow.insertCell(0);
		var t2ItemCell = t2NewRow.insertCell(1);
		var t2DescCell = t2NewRow.insertCell(2);
		var t2QtyCell = t2NewRow.insertCell(3);
		var t2PriceCell = t2NewRow.insertCell(4);
		var t2TPriceCell = t2NewRow.insertCell(5);
		var t2TCostCell = t2NewRow.insertCell(6);
		var t2Postdent = t2NewRow.insertCell(7);

		t2TPriceCell.setAttribute("style", "text-align:right;");
		t2TCostCell.setAttribute("style", "text-align:right;");

		t2Indent.innerHTML = "&nbsp;";
		t2ItemCell.innerHTML = "&nbsp;";
		t2DescCell.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total For Customer";
		t2QtyCell.innerHTML = "&nbsp;";
		t2PriceCell.innerHTML = "&nbsp;";
		t2TPriceCell.innerHTML = "&nbsp;";
		t2TCostCell.innerHTML = "&nbsp;";
		t2Postdent.innerHTML = "&nbsp;";

		t2TPriceCell.innerHTML = number_format(total_price, 2, '.', ',');
		t2TCostCell.innerHTML = number_format(total_cost, 2, '.', ',');
		t2Postdent.innerHTML = "";

		document.getElementById("csalestbl").style.display="block"; 
	}
}

function cGenerateVCard(cCust) {
	var vCard = require('vcards-js');

//create a new vCard
	vCard = vCard();

//set basic properties shown before
	vCard.firstName = cCust.cust_first_name;
	vCard.lastName = cCust.cust_last_name;
	vCard.birthday = new Date(cCust.cust_birthday);

//set other phone numbers
	vCard.cellPhone = cCust.cust_phone;

// set email addresses
	vCard.email = cCust.cust_email;

//set address information
	vCard.homeAddress.label = 'Home Address';
	vCard.homeAddress.street = cCust.cust_street;
	vCard.homeAddress.city = cCust.cust_city;
	vCard.homeAddress.stateProvince = cCust.cust_state;
	vCard.homeAddress.postalCode = cCust.cust_zip;
	vCard.homeAddress.countryRegion = 'United States';

	vCard.version = '3.0'; //can also support 2.1 and 4.0, certain versions only support certain fields

//save to file
	vCard.saveToFile('./test.vcf');

//get as formatted string
	console.log(vCard.getFormattedString());
}


function empty(e) {
	switch(e) {
		case "":
		case 0:
		case "0":
		case null:
		case false:
		case typeof this == "undefined":
			return true;
		default : 
			return false;
	}
}
