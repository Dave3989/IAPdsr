// ---------------------------------------------------------------------------------
//
// Sale Field Functions
//
// ---------------------------------------------------------------------------------

$(function() {
	$("#sSaleList").autocomplete({
		source: sSList,
		minLength: 0,
		change: function(sEvent, sSale) {
					if (document.getElementById("sSaleList").value == "") {
						document.getElementById("sError").style.display="inline";
						document.getElementById("sSaleList").focus();	
					} else {
						var saleId = sSale.item.saleid;
						iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
						document.getElementById("sSaleList").value = "";
						document.getElementById("sError").style.display="none";
						document.getElementById("sdetail").style.display="inline";
						document.getElementById("scustomers").value = sSale.item.custname;
						document.getElementById("scustomers").readOnly = true;
						document.getElementById("srefundbtn").style.display="inline";
						document.getElementById("scustomers").focus();
					}
				}
	});

	$("#scustomers").autocomplete({
		source: sCList,
		minLength: 0,
		change: function(sEvent, sCust) {
					if (document.getElementById("scustomers").value  == "") {
						document.getElementById("cError").style.display="inline";
						document.getElementById("scustomers").focus();	
					} else {
						var sCId = sCust.item.custid;
						var sHoldCust = document.getElementById("scustomers").value;
						sClearNewCust();
						document.getElementById("scustomers").value = sHoldCust;
						document.getElementById("SNEWCUST").value = "N";
						document.getElementById("iapNewCust").style.display="none";
						sCustRet = iapPrepCall("/Ajax/iapGetDB", "C#", sCId, sProcCust);
						if (document.getElementById("STAXRATE").value != sCust.item.taxrate) {
							document.getElementById("STAXREGION").value = sCust.item.taxreg;
							document.getElementById("STAXRATE").value = sCust.item.taxrate;
							document.getElementById("staxregion").innerHTML = sCust.item.taxreg;
							var tr = sCust.item.taxrate * 100;
							document.getElementById("staxrate").value = number_format(tr, 2, ".", "");
							document.getElementById("STAXOVERRIDE").value = "N";
							var sNet = parseFloat(document.getElementById("snetsale").value);
							if (isNaN(sNet)) {
								sNet = 0;
							}
							sShowTotals(sNet);
						}
					}
				}
	});

	$("#speparty").autocomplete({
		source: sPList,
		minLength: 0,
		change: function(sEvent, sParty) {
					if (document.getElementById("speparty").value  == "") {
						document.getElementById("parError").style.display="inline";
						document.getElementById("speparty").focus();	
					} else {
						sSelectP();
						document.getElementById("SSELPE").value = sParty.item.id;
						document.getElementById("SNEWPE").value = "S";
						if (document.getElementById("STAXRATE").value != sParty.item.taxrate) {
							document.getElementById("STAXREGION").value = sParty.item.taxreg;
							document.getElementById("STAXRATE").value = sParty.item.taxrate;
							document.getElementById("staxregion").innerHTML = sParty.item.taxreg;
							var tr = sParty.item.taxrate * 100;
							document.getElementById("staxrate").value = number_format(tr, 2, ".", "");
							document.getElementById("STAXOVERRIDE").value = "N";
							var sNet = parseFloat(document.getElementById("snetsale").value);
							if (isNaN(sNet)) {
								sNet = 0;
							}
							sShowTotals(sNet);
						}
					}
				}
	});

	$("#speevent").autocomplete({
		source: sEList,
		minLength: 0,
		change: function(sEvent, sEvt) {
					if (document.getElementById("speevent").value  == "") {
						document.getElementById("pevtError").style.display="inline";
						document.getElementById("speevent").focus();	
					} else {
						sSelectE();
						document.getElementById("SSELPE").value = sEvt.item.id;
						document.getElementById("SNEWPE").value = "S";
						if (document.getElementById("STAXRATE").value != sEvt.item.taxrate) {
							document.getElementById("STAXREGION").value = sEvt.item.taxreg;
							document.getElementById("STAXRATE").value = sEvt.item.taxrate;
							document.getElementById("staxregion").innerHTML = sEvt.item.taxreg;
							var tr = sEvt.item.taxrate * 100;
							document.getElementById("staxrate").value = number_format(tr, 2, ".", "");
							document.getElementById("STAXOVERRIDE").value = "N";
							var sNet = parseFloat(document.getElementById("snetsale").value);
							if (isNaN(sNet)) {
								sNet = 0;
							}
							sShowTotals(sNet);
						}
					}
				}
	});

	$("#sitemcode").autocomplete({
		source: sItemList,
		minLength: 0,
		change: function(iEvent, iCode) { 
						if (!iCode.item) {
							iItemCd = document.getElementById("sitemcode").value;
							if (iItemCd == "") {
								sGenerateError("Enter an item code or description then click Add Item!");
								return;
							}
							var iSource = 0;
						} else {
							var iItemCd = iCode.item.cd;
							var iSource = iCode.item.src;
						}
						sICodeClicked(iItemCd, iSource);
				}
	});

	$("#sitemdesc").autocomplete({
		source: sDescList,
		minLength: 0,
		change: function(iEvent, iDesc) { 
						if (!iDesc.item) {
							sIDescClicked();
							return;
						}
						var iItemCd = iDesc.item.cd;
						var iSource = iDesc.item.src;
						sICodeClicked(iItemCd, iSource);
				}
	});
});

function sAddClicked() {
	sClearForm();
	document.getElementById("sSaleList").value = "";
	document.getElementById("snewcnews").checked = false;
	document.getElementById('scposscon').checked = false;
	document.getElementById('scposspar').checked = false;

	document.getElementById("SUPDATETYPE").value = "NEW"; 
	document.getElementById("stypeparty").checked = true;
	sTurnPOn();
	document.getElementById("scustomers").readOnly = false;
	document.getElementById("sError").style.display="none";
	document.getElementById("sdetail").style.display="inline";
	document.getElementById("srefundbtn").style.display="none";
	document.getElementById("scustomers").focus();
}

function sGoClicked() {

/*
	var saleName = document.getElementById("sSaleList").value;
	if (saleName == "") {
		document.getElementById("sError").style.display = "inline";
		return false;
	}

// sSList = '{"label": "'.$s.'", "saleid": "'.strval($iapS['sale_id']).'"}'
	saleId = -1;
	for (var i = 0, len = sSList.length; i < len; i++) {
		var sNm = sSList[i].label;
 		if (sSList[i].label == saleName) {
			saleId = sSList[i].saleid;
			break;
		}
	}
	if (saleId == -1) {
		document.getElementById("sError").style.display = "inline";
		return false;
	}

	sSale = iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
	document.getElementById("sSaleList").value = "";
	document.getElementById("sError").style.display="none";
	document.getElementById("sdetail").style.display="inline";
	document.getElementById("scustomers").readOnly = true;
	document.getElementById("scustomers").focus();
*/
	var sDummy = "dummy";
}

function sSelectClicked() {
	var saleName = document.getElementById("sSaleList").value;
	if (saleName == "") {
		document.getElementById("sError").style.display = "inline";
		return false;
	}

	var saleId = document.getElementById("sSelect").value;
//	sSale = iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
//	document.getElementById("sdetail").style.display="inline";

	if (document.getElementById("sSaleList").value == "") {
		document.getElementById("sError").style.display="inline";
		document.getElementById("sSaleList").focus();						
	} else {
		var saleId = sSale.item.saleid;
		sSale = iapPrepCall("/Ajax/iapGetDB", "S#", saleId, sProcSale);
		document.getElementById("sSaleList").value = "";
		document.getElementById("sError").style.display="none";
		document.getElementById("sdetail").style.display="inline";
		document.getElementById("scustomers").readOnly = true;
		document.getElementById("srefundbtn").style.display="inline";
		document.getElementById("scustomers").focus();
	}
}

function sProcSale(sSale) {
	sClearForm();
	if (sSale < 0) {
		return sSale;
	} else if (sSale == 0) {
		document.getElementById("SUPDATETYPE").value = "NEW";
		document.getElementById("SALEID").value = "";
		return 0;
	}

	document.getElementById("SPEID").value = sSale.sale_peid;
	document.getElementById("SNEWPE").value = "N";

	if (sSale.cust_followup_consultant == "Y") {
		document.getElementById('scposscon').checked = true;
	}
	if (sSale.cust_followup_party == "Y") {
		document.getElementById('scposspar').checked = true;
	}

	switch(sSale.sale_type) {
		case "P":
			document.getElementById("stypeparty").checked = true;
			document.getElementById("help29114").style.display = "inline";
			break;
		case "E":
			document.getElementById("stypeevent").checked = true;
			document.getElementById("help29114").style.display = "inline";
			break;
		case "I":
			document.getElementById("stypeindiv").checked = true;
			document.getElementById("help29114").style.display = "none";
			break;
		case "F":
			document.getElementById("stypefacebk").checked = true;
			document.getElementById("help29114").style.display = "none";
			break;
		case "W":
			document.getElementById("stypeweb").checked = true;
			document.getElementById("help29114").style.display = "none";
			break;
		case "O":
			document.getElementById("stypeother").checked = true;
			document.getElementById("help29114").style.display = "none";
			break;
		default:
			document.getElementById("stypeparty").checked = true;
			document.getElementById("help29114").style.display = "none";
	}
	sSetType(sSale.sale_type);
	if (sSale.sale_type == "E") {
		var sSearchKey = moment(sSale.pe_date).format("MM/DD/YYYY") + " event at " + sSale.pe_sponsor;
		document.getElementById('SDATE').value = moment(sSale.pe_date).format("MM/DD/YYYY")
		document.getElementById('speevent').value = sSearchKey;
	} else {
		if (sSale.pe_party_no != "") {
			var sSearchKey = moment(sSale.pe_date).format("MM/DD/YYYY") + " " + sSale.pe_party_no + " " + sSale.pe_sponsor;
			document.getElementById('SDATE').value = moment(sSale.pe_date).format("MM/DD/YYYY")
			document.getElementById('speparty').value = sSearchKey;
		}
		if (sSale.sale_type != "P") {
			document.getElementById('ssaledate').value = moment(sSale.sale_date).format("MM/DD/YYYY")
			document.getElementById('SDATE').value = moment(sSale.sale_date).format("MM/DD/YYYY")
			document.getElementById('smileage').value = number_format(sSale.sale_mileage, 2, '.', ',');
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
	document.getElementById("STAXREGION").value = sSale.sale_tax_region;
	document.getElementById("STAXRATE").value = sSale.sale_tax_rate;
	document.getElementById("staxregion").innerHTML = sSale.sale_tax_region;
	document.getElementById('staxrate').value = number_format(sSale.sale_tax_rate * 100, 2, '.', ',');
	document.getElementById('staxamt').value = number_format(sSale.sale_sales_tax, 2, '.', ',');
	document.getElementById('sshipping').value = number_format(sSale.sale_shipping, 2, '.', ',');
	document.getElementById('strackno').value = sSale.sale_tracking_no;
	document.getElementById('stotalsale').value = number_format(sSale.sale_total_amt, 2, '.', ',');
	if (sSale.sale_pay_method == "$") {
		document.getElementById('spaycash').checked = true;
		document.getElementById('spaychkno').value = "";
	} else if (sSale.sale_pay_method == "C"){
		document.getElementById('spaycredit').checked = true;
		document.getElementById('spaychkno').value = "";
	} else if (sSale.sale_pay_method == "K"){
		document.getElementById('spaycheck').checked = true;
		document.getElementById('spaychkno').value = sSale.sale_check_number;
	}
	document.getElementById('scomment').value = sSale.sale_comment;

	var sPrtHref = document.getElementById('sprtreclink').href;
	var sPrtLoc = sPrtHref.indexOf("&");
	var sPrtLink = sPrtHref.substring(0, sPrtLoc);
	var sPrtNewHref = sPrtLink + "&co=" + sSale.sale_company + "&s=" + sSale.sale_id;
	document.getElementById('sprtreclink').href = sPrtNewHref;

// --------------------------------------------------------------
/*
	var sPrtBtnLink = "window.open('MyReports/IAPSaleRec.php?action=selected&co=" + sSale.sale_company + "&s=" +  sSale.sale_id + "', '_blank'); return false;";
	document.getElementById("sprtrec2").onclick = function() { 
		location.href = "MyReports/IAPSaleRec.php?action=selected&co=" + sSale.sale_company + "&s=" +  sSale.sale_id + "', '_blank'";
		return false;
	}
*/
// --------------------------------------------------------------
	document.getElementById("sprtrec").style.display="inline";
//	document.getElementById("SDATE").value = sSale.sale_date;
	document.getElementById("SUPDATETYPE").value = "EXISTING";
	document.getElementById("SALEID").value = sSale.sale_id;
	document.getElementById("sdetail").style.display="inline";

	return false;
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
			if (!sdRec.CO_DESC) {
				sdRec.saledet_desc = sdRec.SUPP_DESC;
			} else {
				sdRec.saledet_desc = sdRec.CO_DESC;
			}
			document.getElementById("sitemdesc").value = sdRec.saledet_desc;
			document.getElementById('sitemqty').value = number_format(sdRec.saledet_quantity, 0, '.', ',');
			document.getElementById('sitemprice').value = number_format(sdRec.saledet_price, 2, '.', ',');
			document.getElementById("STHISITEMSOURCE").value = sdRec.saledet_item_source;
			document.getElementById("STHISITEMSTATUS").value = "EXISTING";
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

function sRefundSale() {
	var sSaleId = document.getElementById("SALEID").value;
	if (sSaleId == 0) {
		return false;
	}
	 if (confirm('Are you sure you want to refund this sale?')) {
		return true;
	} else {
		return false;
	} 
}

function sPrintSale(sCompany, sSaleId) {

	var sHref = "MyReports/IAPSaleRec.php?action=selected&co=" + sCompany + "&s=" + sSaleId + " target='_blank'";
	sPrtWindow = window.open(sHref, "_blank");
	sPrtWindow.focus();
	return false;
}

// ---------------------------------------------------------------------------------
//
// Customer Functions
//
// ---------------------------------------------------------------------------------

function sClearNewCust() {
	document.getElementById("iapNewCust").style.display="none";
	document.getElementById("snewcustlbl").innerHTML = "Please provide the following information about the new customer. This new customer will need to be editted later to enter any additional information.";
	document.getElementById("snewcustlbl").style.color = "#666666";
	document.getElementById("snewcname").value = "";
	document.getElementById("snewcstrt").value = "";
	document.getElementById("snewccity").value = "";
	document.getElementById("snewcstate").value = "";
	document.getElementById("snewczip").value = "";
	document.getElementById("snewcemail").value = "";
	document.getElementById("snewcphone").value = "";
	document.getElementById("snewcbirth").value = "";
	document.getElementById("snewcnews").checked = true;
	document.getElementById('scposscon').checked = false;
	document.getElementById('scposspar').checked = false;
	document.getElementById('staxregion').innerHTML = document.getElementById('SCOTAXREGION').value;
	var tr = document.getElementById('SCOTAXRATE').value;
	document.getElementById('staxrate').value = number_format(tr*100, 2, '.', ',');
	document.getElementById('STAXREGION').value = document.getElementById('SCOTAXREGION').value
	document.getElementById('STAXRATE').value = document.getElementById('SCOTAXRATE').value;
}

function sAddCustomer() {
	sClearNewCust();
	document.getElementById("scustomers").value = "";
	document.getElementById("scustomers").readOnly = false;
	document.getElementById("SNEWCUST").value = "Y";
	document.getElementById("iapNewCust").style.display="inline";
	document.getElementById("snewcname").focus();
}

function iapChkNewCustomer() {
	var sNewCust = document.getElementById("snewcname").value;
	sNewCustRet = iapPrepCall("/Ajax/iapGetDB", "CN", sNewCust, sProcNewCust);
}

function sProcNewCust(sNewCust) {
	if (sNewCust < 0) {
		return sNewCust;
	} else if (sNewCust == 0) {
		document.getElementById("snewcstrt").focus();	
	} else {
		document.getElementById("cError").innerHTML = "\nA customer by the new name entered already exists.";
		document.getElementById("cError").style.display = "inline";
		document.getElementById("scustomers").value = document.getElementById("snewcname").value;
		sClearNewCust();
		document.getElementById("SNEWCUST").value = "N";
		document.getElementById("scustomers").focus();
	}
}

function sSelCustomer() {
	var sHoldCust = document.getElementById("scustomers").value;
	sClearNewCust();
	document.getElementById("scustomers").value = sHoldCust;
	document.getElementById("SNEWCUST").value = "N";
	document.getElementById("iapNewCust").style.display="none";
	sCustRet = iapPrepCall("/Ajax/iapGetDB", "CN", sHoldCust, sProcCust);
}

function sProcCust(sCust) {
	if (sCust < 0) {
		return sCust;
	} else if (sCust == 0) {
		document.getElementById("cError").innerHTML = "\nThe customer was not found. Retry or click Add.";
		document.getElementById("cError").style.display = "inline";
		document.getElementById("scustomers").focus();
		return false;
	} else {
		if (sCust.cust_followup_consultant == "Y") {
			document.getElementById('scposscon').checked = true;
		}
		if (sCust.cust_followup_party == "Y") {
			document.getElementById('scposspar').checked = true;
		}
		document.getElementById("cError").style.display = "none";
		return true;
	}
}


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
		 	document.getElementById("snewpecmt1").innerHTML = "party";
		 	document.getElementById("snewpecmt2").innerHTML = "party";
			document.getElementById("help29114").style.display = 'inline';
			sTurnPOn();
			break;
		case "E":
		 	document.getElementById("snewpenamelbl").innerHTML = "Sponsor:";
		 	document.getElementById("STYPE").value = "Event";
		 	document.getElementById("snewpecmt1").innerHTML = "event";
		 	document.getElementById("snewpecmt2").innerHTML = "event";
			document.getElementById("help29114").style.display = 'inline';
			sTurnEOn();
			break;
		case "I":
		 	document.getElementById("STYPE").value = "Indivdual";
			document.getElementById("help29114").style.display = 'none';
			sTurnPEOff();
			break;
		case "F":
		 	document.getElementById("STYPE").value = "Facebook";
			document.getElementById("help29114").style.display = 'none';
			sTurnPEOff();
			break;
		case "W":
		 	document.getElementById("STYPE").value = "Web";
			document.getElementById("help29114").style.display = 'none';

// 2018-09-14 Change default of pay cash to pay credit if website
			document.getElementById("spaycash").checked = false;
			document.getElementById("spaycredit").checked = true

			sTurnPEOff();
			sTurnIOn();
			break;
		case "O":
		 	document.getElementById("STYPE").value = "Other";
			document.getElementById("help29114").style.display = 'none';
			sTurnPEOff();
			sTurnIOn();
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
 	document.getElementById("saddpe").innerHTML = "New Party";
	document.getElementById("SDATE").value = document.getElementById("speparty").value;
 	document.getElementById("speevent").style.display = "none";
 	document.getElementById("speparty").style.display = "inline";
}

function sTurnEOn() {
	sClearNewPE();
	document.getElementById("iapNewPE").style.display="none";
	document.getElementById("snonpediv1").style.display="none";
	document.getElementById("snonpediv2").style.display="none";
	document.getElementById("snonpediv3").style.display="none";
	document.getElementById("spelabel").innerHTML = "Select an Event: ";
 	document.getElementById("saddpe").innerHTML = "New Event";
	document.getElementById("SDATE").value = document.getElementById("speevent").value;
 	document.getElementById("speparty").style.display = "none";
 	document.getElementById("speevent").style.display = "inline";
}

function sTurnPEOff() {
	sClearNewPE();
	document.getElementById("iapNewPE").style.display="none";
	document.getElementById("spelabel").innerHTML = "Select a Party: ";
 	document.getElementById("saddpe").innerHTML = "New Party";
	document.getElementById("SDATE").value = document.getElementById("ssaledate").value;
 	document.getElementById("speevent").style.display = "none";
 	document.getElementById("speparty").style.display = "inline";
 	document.getElementById("snonpediv1").style.display="inline";
	document.getElementById("snonpediv2").style.display="inline";
	document.getElementById("snonpediv3").style.display="none";
}

function sTurnIOn() {
		document.getElementById("snonpediv3").style.display="inline";
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
/*
	document.getElementById('STAXREGION').value = document.getElementById('SCOTAXREGION').value
	document.getElementById('staxregion').innerHTML = document.getElementById('SCOTAXREGION').value;
	document.getElementById('STAXRATE').value = document.getElementById('SCOTAXRATE').value;
	var tr = document.getElementById('SCOTAXRATE').value;
	document.getElementById('staxrate').value = number_format(tr*100, 2, '.', ',');
	var sNet = parseFloat(document.getElementById("snetsale").value);
	if (isNaN(sNet)) {
		sNet = 0;
	}
	sShowTotals(sNet);
*/
}

function sAddPE() {
	sClearNewPE();
	document.getElementById("SNEWPE").value = "Y";
	document.getElementById("SPEID").value = "";
	document.getElementById("iapNewPE").style.display="inline";
	document.getElementById("snewpedate").focus();
}

function sSelectP() {
//	"Party ".$iapP['pe_party_no']." on ".date("m/d/Y", strtotime($iapP['pe_date']))." for ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);

	var sParty = document.getElementById("speparty").value;
	var sPs = sParty.split(" ");
	document.getElementById("SDATE").value = sPs[0];
	document.getElementById("snonpediv2").style.display="none";
	return;
}

function sSelectE() {
//	"Event on ".date("m/d/Y", strtotime($iapP['pe_date']))." at ".trim($iapP['pe_sponsor']);
//	$peText = date("m/d/Y", strtotime($iapP['pe_date']))."event at ".trim($iapP['pe_sponsor']);

	var sEvent = document.getElementById("speevent").value;
	var sEs = sEvent.split(" ");
	document.getElementById("SDATE").value = sEs[0];
	document.getElementById("snonpediv2").style.display="none";
	return;
}

function sNewPEDateChg() {
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
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
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
	var sShipping = parseFloat(document.getElementById("sshipping").value);
	if (isNaN(sShipping)) {
		sShipping = 0;
	}
	if (sTaxRate > 0) {
		sTax = (sTotalNet + sShipping) * sTaxRate;
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
	if (document.getElementById("spaycheck").checked == true) {
		document.getElementById("spaychkno").focus();		
	}
}


// ---------------------------------------------------------------------------------
//
// Item Functions
//
// ---------------------------------------------------------------------------------

function sClrItemData() {
	sClearItem();
	document.getElementById("sitemcode").focus();
	return false;
}

function sClearItem() {
	document.getElementById("sitemcode").value = "";
	document.getElementById("sitemdesc").value = "";
	document.getElementById("sitemqty").value = "";
	document.getElementById("sitemprice").value = "";
	document.getElementById("snewitemcost").value = "";
	document.getElementById("snewitemunits").value = "";
	document.getElementById("snewitemcat").selectedIndex = 0;
	document.getElementById("STHISITEMSOURCE").value = 0;
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
		document.getElementById("iapNewItem").style.display = "inline";
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
		document.getElementById("iapNewItem").style.display = "inline";
		document.getElementById("sitemdesc").focus();
		document.getElementById("STHISITEMSOURCE").value = 0;
		document.getElementById("STHISITEMSTATUS").value = "NEW";
		return false;
	}
}

function sICodeClicked(iCode, iSource) {
	var dateErr = sItemDateOK();
	if (dateErr == "Y") {
		return false;
	}
	if (iCode == "") {
		sGenerateError("Enter an item code or description then click Add Item!");
		return;
	}
	if (document.getElementById("STHISITEMSTATUS").value == "EXISTING") {
		document.getElementById("iapNewItem").style.display = "none";
		sGenerateError("<RESET>");
		document.getElementById("sitemdesc").value = "";	
	}
	document.getElementById("STHISITEMSOURCE").value = iSource;
	var keyId = iSource + "|" + iCode + "~" + document.getElementById("SDATE").value;
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
	if (sItem.SUPPID == "CO") {
		document.getElementById("STHISITEMSOURCE").value = 0;	
	} else {
		document.getElementById("STHISITEMSOURCE").value = sItem.cat_supplier_id;		
	}
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
	if (sQtyIn < 0) {
		document.getElementById("sitemqtylbl").style.color = "red";
		sGenerateError("Quantity cannot be negative. Use Returns to bring back an item.");
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
		var sCostUnits = parseFloat(document.getElementById("snewitemunits").value);
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
		sGenerateError("All item fields must be valid prior to clicking Record Item.");
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
	var sCode = document.getElementById("sitemcode").value;
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
	var sSIdCell = sNewRow.insertCell(7);

	var sRows = sTable.rows.length - 1;
	sNewRow.setAttribute("id", "Sold"+sRows , 0);
	var sEorN = document.getElementById("STHISITEMSTATUS").value;
	sEorN = sEorN.substr(0,1);
	sIndent.innerHTML = "<input type='hidden' id='recrow" + sRows + "' value='" + sEorN + "'>";

	sURL = document.getElementById("SIAPURL").value;
	sDel.innerHTML = "<img src='" + sURL + "/MyImages/Icons/DeleteRedSM.png' onclick='sDelSold(" + sRows + "); return(false);'>&nbsp;&nbsp;";
	sCodeCell.innerHTML = sCode.toUpperCase();
	sDescCell.innerHTML = document.getElementById("sitemdesc").value;
	sQtyCell.innerHTML = sQtyIn;
	sPriceCell.innerHTML = number_format(sItemPrc, 2, '.', ',');
	sValueCell.innerHTML = number_format(sValueExt, 2, '.', ',');
	var sSId = document.getElementById("STHISITEMSOURCE").value;
	sSIdCell.innerHTML = "<input type='hidden' id='recsid" + sRows + "' value='|" + sSId + "|'>";

	sShowTotals(sTotalNet);

	var sNewData = document.getElementById("SNEWITEMINFO").value;
	var sitemcode = sCode.toUpperCase();
	var sStatus = document.getElementById("STHISITEMSTATUS").value;
	var sNewUnits = document.getElementById("snewitemunits").value;
	var sNewCost = document.getElementById("snewitemcost").value;
	var sNewCat = document.getElementById("snewitemcat").value;
	var sItemSupp = document.getElementById("STHISITEMSOURCE").value;
	document.getElementById("SNEWITEMINFO").value = sNewData + sitemcode + "~" + sStatus + "~" + sNewUnits + "~" + sNewCost + "~" + sNewCat + "~" + sItemSupp  + "|";

	sClearItem();
	document.getElementById("STHISITEMSTATUS").value = "";
	document.getElementById("iapNewItem").style.display = "none";
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
// //////////////////////////////////////////////////////////////////////////////////////////////
			sTax = (sTotalNet + sShpn) * (sTaxRate / 100);
// //////////////////////////////////////////////////////////////////////////////////////////////
		}
	}
	document.getElementById("staxamt").value = number_format(sTax, 2, '.', ',');

	var sTotal = sTotalNet + sTax + sShpn;
	document.getElementById("stotalsale").value = number_format(sTotal, 2, '.', ',');
}


// -----------------------------------------------------------------------------------
//
// Form Functions
//
// -----------------------------------------------------------------------------------

function sClearForm() {
	document.getElementById("scustomers").value = "";
	document.getElementById("scustomers").readOnly = false;
	sClearNewCust();
	document.getElementById("stypeparty").checked = true;
	document.getElementById("speparty").value = "";
	document.getElementById("speparty").style.display = "inline";
	document.getElementById("speevent").value = "";
	document.getElementById("speevent").style.display = "none";
	sClearNewPE();
	document.getElementById("svendord").value = "";
	document.getElementById("ssaledate").value = "";
	document.getElementById("smileage").value = "";
	document.getElementById("sotherexp").value = "";
	document.getElementById("sexpexplain").value = "";
	document.getElementById("ssaleloc").value = "";
	document.getElementById("spaycash").checked = true;
	document.getElementById('spaychkno').value = "";
	document.getElementById("staxamt").value = "";
	document.getElementById("sshipping").value = "";
	document.getElementById("strackno").value = "";
	document.getElementById("scomment").value = "";
	document.getElementById("snetsale").value = "";
	document.getElementById("stotalsale").value = "";
	document.getElementById("sprtrec").style.display="none";
	document.getElementById("SALEID").value = "";
	document.getElementById("SDATE").value = "";
// Tax region and tax rate reset in sClearNewCust()
	document.getElementById("STAXOVERRIDE").value = "N";
	document.getElementById("STYPE").value = "Party";
	document.getElementById("SNEWCUST").value = "N";
	document.getElementById("SPEID").value = "";
	document.getElementById("SSELPE").value = "";
	document.getElementById("SNEWPE").value = "N";
	document.getElementById("SNEWITEMINFO").value = "";
	document.getElementById("STHISITEMSTATUS").value = "";
	document.getElementById("SUPDATETYPE").value = "";

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
	if (document.getElementById("sitemcode").value != ""
	||  document.getElementById("sitemdesc").value != "") {
		sGenerateError("An Item has not been Recorded. Either click Record Item or clear the data.");
		document.getElementById("sitemcode").focus();
		return false;
	}

	var sData = "";
	var sTable = document.getElementById('iapSold');
	var sFirst = "Y";
	for (var r = 1, n = sTable.rows.length; r < n; r++) {
		if (sFirst == "Y") {
			sFirst = "N";
		} else {
			sData = sData + "|";
		}
		for (var c = 2, m = sTable.rows[r].cells.length; c < m - 1; c++) {
			sData = sData + sTable.rows[r].cells[c].innerHTML + "~";
		}
		var sSId = sTable.rows[r].cells[m-1].innerHTML;

		var sSIdStart = sSId.indexOf("|") + 1;
		var sSIdEnd = sSId.lastIndexOf("|");
		var sSidLen = sSIdEnd - sSIdStart;
		var sSIdVal = sSId.substr(sSIdStart, sSidLen);
		sData = sData + sSIdVal + "~";
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
