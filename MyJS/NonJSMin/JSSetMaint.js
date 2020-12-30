var iGlobalICode = "";
var iGlobalItem = [];

$(function() {
	$("#setitem").autocomplete({
		source: sItemList,
		minLength: 0,
		change: function(iEvent, iCode) { 
					if (!iCode.item) {
						iItemCd = document.getElementById("setitem").value;
						if (iItemCd == "") {
							sGenerateError("Enter the item code of the set or click Add!");
							return;
						}
						var iSource = 0;
					} else {
						var iItemCd = iCode.item.cd;
						var iSource = iCode.item.src;
					}
					iCodeClicked("S", iItemCd, iSource);
				}
	});

	$("#sgrpitem").autocomplete({
		source: sItemList,
		minLength: 0,
		change: function(iEvent, iCode) { 
					if (!iCode.item) {
						iItemCd = document.getElementById("sgrpitem").value;
						if (iItemCd == "") {
							sGenerateError("Enter the item code or description of the group item!");
							return;
						}
						var iSource = 0;
					} else {
						var iItemCd = iCode.item.cd;
						var iSource = iCode.item.src;
					}
					document.getElementById("sgrpdesc").value = "";
					iCodeClicked("G", iItemCd, iSource);
				}
	});

	$("#sgrpdesc").autocomplete({
		source: sDescList,
		minLength: 0,
		change: function(iEvent, iDesc) { 
					if (!iDesc.item) {
						iDescVal = document.getElementById("iDescList").value;
						if (iDescVal == "") {
							sGenerateError("Enter the item code or description of the group item!");
							return;
						}
						var iSource = 0;
					} else {
						var iItemCd = iDesc.item.cd;
						var iSource = iDesc.item.src;
					}
					document.getElementById("sgrpitem").value = "";
					iCodeClicked("G", iItemCd, iSource);
				}
	});
});

function iGoClicked() {
/*
	iItem = document.getElementById("iItemList").value;
	if (iItem == "") {
		iDesc = document.getElementById("iDescList").value;
		if (iDesc == "") {
			document.getElementById("sitemerror").innerHTML = "Enter an item code or description then click Add Item!";
			document.getElementById("sitemerror").style.display = "inline";
			return;
		} else {
			iIDescClicked(iDesc, "0");
		}
	} else {
		iICodeClicked(iItem, "0");
	}
*/
}

function iCodeClicked(iType, iCode, iSource) {

	sGenerateError("<RESET>");

	document.getElementById("sitemerror").innerHTML = "";
	document.getElementById("sitemerror").style.display = "none";
	var typeFld = "I#";
	var argFld = iSource + "|" + iCode;
	if (iType == "S") {
		iapPrepCall("/Ajax/iapGetDB", typeFld, argFld, sProcSetItem);	
	} else {
		iapPrepCall("/Ajax/iapGetDB", typeFld, argFld, sProcGrpItem);
	}
	return;
}

function sProcSetItem(pSetItem) {
	sClearGroup();
	if (pSetItem == 0) {
//		sGenerateError("Set item code not found! Please click Add.");
		document.getElementById("setdesc").readOnly = false;
		document.getElementById("setcost").readOnly = false;
		document.getElementById("setprice").readOnly = false;
		document.getElementById("iapNewItem").style.display = "inline";
		sGenerateError("Set Item Code not found! Enter details to add it.");
		document.getElementById("ADDSETITEM").value = "Y";
		document.getElementById("setdesc").focus();
	} else {
		if (pSetItem.cat_set == "Y") {
			document.getElementById("sitemerror").innerHTML = "Set exists for this item. Use phpMyAdmin";
			document.getElementById("sitemerror").style.display = "inline";
			document.getElementById("setitem").focus(); 
			return;
		}
		if (pSetItem.SUPPID == "SUPP") {
			document.getElementById("SUPPID").value = pSetItem.cat_supplier_id;
		} else {
			document.getElementById("SUPPID").value = "CO";			
		}
 		document.getElementById("setitem").value = pSetItem.cat_item_code;
		document.getElementById("setdesc").value = pSetItem.cat_description;
		document.getElementById("SETITEMCD").value = pSetItem.cat_item_code;
		document.getElementById("SETCOST").value = number_format(pSetItem.prc_cost_unit, 2, '.', '');
		document.getElementById("SETPRICE").value = number_format(pSetItem.prc_price, 2, '.', '');
		document.getElementById("setcost").value = number_format(pSetItem.prc_cost_unit, 2, '.', '');
		document.getElementById("setprice").value = number_format(pSetItem.prc_price, 2, '.', '');
		document.getElementById("sgrpitem").focus();
	}
	return(pSetItem);
}

function sAddSetItem() {
	sGenerateError("<RESET>");
	if (document.getElementById("setitem").value == "") {
		sGenerateError("Enter an item code then click Add.");
	}
	if (document.getElementById("iapNewItem").style.display == "none") {
		document.getElementById("setdesc").readOnly = false;
		document.getElementById("setcost").readOnly = false;
		document.getElementById("setprice").readOnly = false;
		document.getElementById("iapNewItem").style.display = "inline";
	}
	document.getElementById("ADDSETITEM").value = "Y";
	document.getElementById("sitemitem").focus();
	return false;
}

function sSaveSetSupp() {
	var s = document.getElementById("snewitemsups");
	var sNewSupp = s.options[s.selectedIndex].value;
	document.getElementById("SUPPID").value = sNewSupp;
	return false;
}


function sProcGrpItem(retGrpItem) {
	sClearGroup();
	if (retGrpItem == 0) {
		iGlobalICode = 0;
		document.getElementById("sitemerror").innerHTML = "Group Item Code or Description not found.";
		document.getElementById("sitemerror").style.display = "inline";							
		document.getElementById("sgrpitem").focus(); 
		return;
	} else {
		if (document.getElementById("SUPPID").value != retGrpItem.cat_supplier_id) {
			document.getElementById("sitemerror").innerHTML = "Group Item not same supplier as Set Item";
			document.getElementById("sitemerror").style.display = "inline";							
			document.getElementById("sgrpitem").focus(); 
			return;			
		}
		iGlobalItem = retGrpItem;
		iGlobalICode = retGrpItem.cat_item_code;

 		document.getElementById("sgrpitem").value = retGrpItem.cat_item_code;
		document.getElementById("sgrpdesc").value = retGrpItem.cat_description;
		document.getElementById("GROUPCOST").value = retGrpItem.prc_cost_unit;
		document.getElementById("GROUPPRICE").value = retGrpItem.prc_price;
		document.getElementById("sgrpqty").focus();
		return;
	}
}

function sRecordItem() {
	sGenerateError("<RESET>");
	var sErrorFnd = "N";
	if (document.getElementById("sgrpitem").value == "") {
		document.getElementById("sgrpitemlbl").style.color = "red";
		sGenerateError("Item Code cannot be blank.");
		sErrorFnd = "Y";
	}
	if (document.getElementById("sgrpdesc").value == "") {
		document.getElementById("sgrpdesclbl").style.color = "red";
		sGenerateError("Description cannot be blank.");
		sErrorFnd = "Y";
	}
	if (document.getElementById("sgrpqty").value == "") {
		document.getElementById("sgrpqtylbl").style.color = "red";
		sGenerateError("Quantity cannot be blank.");
		sErrorFnd = "Y";
	}
	var sQtyIn = parseInt(document.getElementById("sgrpqty").value);
	if (isNaN(sQtyIn)) {
		document.getElementById("sgrpqtylbl").style.color = "red";
		sGenerateError("Quantity is invalid.");
		sErrorFnd = "Y";
	}
	if (sQtyIn < 0) {
		document.getElementById("sgrpqtylbl").style.color = "red";
		sGenerateError("Quantity cannot be negative. Use Returns to bring back an item.");
		sErrorFnd = "Y";
	}
	if (sErrorFnd == "Y") {
		sGenerateError("All item fields must be valid prior to clicking Record Item.");
		return false;
	}
// ----------------------------------------------------
// ... Reset fields in case previous error
// ----------------------------------------------------
	document.getElementById("sgrpitemlbl").style.color = "#666666";
	document.getElementById("sgrpdesclbl").style.color = "#666666";
	document.getElementById("sgrpqtylbl").style.color = "#666666";
	sGenerateError("<RESET>");

// ----------------------------------------------------
// ... Get Item Code
// ----------------------------------------------------
	var sCode = document.getElementById("sgrpitem").value;
	var sQtyIn = parseInt(document.getElementById("sgrpqty").value);

	var sItemCost = parseFloat(document.getElementById("GROUPCOST").value);
	var sCostExt = parseFloat(sQtyIn * sItemCost);
	var sItemPrice = parseFloat(document.getElementById("GROUPPRICE").value);
	var sPriceExt = parseFloat(sQtyIn * sItemPrice);

	var sTable = document.getElementById("iapGroupTbl");

	var sNewRow = sTable.insertRow(-1);

	var sIndent = sNewRow.insertCell(0);
	var sDel = sNewRow.insertCell(1);
	var sFiller = sNewRow.insertCell(2);
	var sCodeCell = sNewRow.insertCell(3);
	var sDescCell = sNewRow.insertCell(4);
	var sQtyCell = sNewRow.insertCell(5);
	var sCostCell = sNewRow.insertCell(6);
	var sCExtCell = sNewRow.insertCell(7);
	var sPriceCell = sNewRow.insertCell(8);
	var sPExtCell = sNewRow.insertCell(9);
	var sSIdCell = sNewRow.insertCell(10);

	var sRows = sTable.rows.length - 1;
	sNewRow.setAttribute("id", "Grp"+sRows , 0);
	var sEorN = document.getElementById("STHISITEMSTATUS").value;
	sEorN = sEorN.substr(0,1);
	sIndent.innerHTML = "<input type='hidden' id='recrow" + sRows + "' value='" + sEorN + "'>";
	sURL = document.getElementById("SIAPURL").value;
	sDel.innerHTML = "<img src='" + sURL + "/MyImages/Icons/DeleteRedSM.png' onclick='sDelGrpItem(" + sRows + "); return(false);'>&nbsp;&nbsp;";
	sCodeCell.innerHTML = sCode.toUpperCase();
	sDescCell.innerHTML = document.getElementById("sgrpdesc").value;
	sQtyCell.innerHTML = sQtyIn;
	sCostCell.innerHTML = number_format(sItemCost, 2, '.', ',');
	sCExtCell.innerHTML = number_format(sCostExt, 2, '.', ',');
	sPriceCell.innerHTML = number_format(sItemPrice, 2, '.', ',');
	sPExtCell.innerHTML = number_format(sPriceExt, 2, '.', ',');
	var sSId = document.getElementById("STHISITEMSOURCE").value;
	sSIdCell.innerHTML = "<input type='hidden' id='recsid" + sRows + "' value='|" + sSId + "|'>";

	var sTotalCost = parseFloat(document.getElementById("grpcost").value);
	if (isNaN(sTotalCost)) {
		sTotalCost = 0;
	}
	sTotalCost = sTotalCost + sCostExt;
	document.getElementById("grpcost").value =  number_format(sTotalCost, 2, '.', ',');

	var sTotalPrice = parseFloat(document.getElementById("grpprice").value);
	if (isNaN(sTotalPrice)) {
		sTotalPrice = 0;
	}
	sTotalPrice = sTotalPrice + sPriceExt;
	document.getElementById("grpprice").value =  number_format(sTotalPrice, 2, '.', ',');

	var sgrpitem = sCode.toUpperCase();
	var sStatus = document.getElementById("STHISITEMSTATUS").value;
	var sItemSupp = document.getElementById("STHISITEMSOURCE").value;
//	document.getElementById("SNEWITEMINFO").value = sNewData + sgrpitem + "~" + sStatus + "~" + sNewUnits + "~" + sNewCost + "~" + sNewCat + "~" + sItemSupp  + "|";

	sClrGrpData();
	document.getElementById("STHISITEMSTATUS").value = "";
	return false;
}

function sDelGrpItem(sRow) {
	var sRowId = "Grp" + sRow;
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


function sClearGroup() {

// clear form input fields
	document.getElementById("sgrpitem").value = "";
	document.getElementById("sgrpdesc").value = "";
	document.getElementById("sgrpqty").value = "";

//	document.getElementById("iapfldset").style.display="none";
// clear global fields
	iGlobalItem = [];

// clear hidden fields
	document.getElementById("SUPDATETYPE").value = "NEW";
	document.getElementById("GROUPCOST").value = "";	
	document.getElementById("GROUPPRICE").value = "";	
}

function sClrGrpData() {
	sClearGroup();
	document.getElementById("sgrpitem").focus();
	return false;
}

function sSendForm() {
	if (document.getElementById("sgrpitem").value != ""
	||  document.getElementById("sgrpdesc").value != "") {
		sGenerateError("An Item has not been Recorded. Either click Record Item or clear the data.");
		document.getElementById("sgrpitem").focus();
		return false;
	}

	var sData = "";
	var sTable = document.getElementById('iapGroupTbl');
	for (var r = 1, n = sTable.rows.length; r < n; r++) {
		for (var c = 3, m = sTable.rows[r].cells.length; c < m - 1; c++) {
			sData = sData + sTable.rows[r].cells[c].innerHTML + "~";
		}
		sData = sData + "|";
	}
	sD = sData.length;
	document.getElementById("IAPDATA").value = sData.substr(0, sD - 1);
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
		document.getElementById("sitemerror").style.display = "none";
		return;
	}
	var sExistingMsg = document.getElementById("sitemerror").innerHTML;
	var sBreak = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	document.getElementById("sitemerror").innerHTML = sExistingMsg + sBreak + sErrMsg;
	document.getElementById("sitemerror").style.display = "inline";
}
