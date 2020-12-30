$(function() {
	$("#setitem").autocomplete({
		source: sItemList,
		minLength: 0,
		change: function(iEvent, iCode) { 
						if (!iCode.item) {
							iItemCd = document.getElementById("setitem").value;
							if (iItemCd == "") {
								sGenerateError("Enter an item code of the set!");
								return;
							}
							var iSource = 0;
						} else {
							var iItemCd = iCode.item.cd;
							var iSource = iCode.item.src;
						}
						sGetSpecial(iItemCd, iSource);
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


// ---------------------------------------------------------------------------------
//
// Item Functions
//
// ---------------------------------------------------------------------------------

function sClrItemData() {
	sClearItem();
	document.getElementById("sitemcode").focus();
	return flase;
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

	sDel.innerHTML = "<img src='MyImages/Icons/DeleteRedSM.png' onclick='sDelSold(" + sRows + "); return(false);'>&nbsp;&nbsp;";
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

