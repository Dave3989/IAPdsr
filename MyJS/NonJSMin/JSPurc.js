var pGlblTotalNet = 0.00;

$(function() {
	$("#pPurList").autocomplete({
		source: pPrchList,
		minLength: 0,
		change: function(pEvent, pPurchase) { 
					if (document.getElementById("pPurList").value == "") {
						document.getElementById("pError").style.display="block";
						document.getElementById("pPurList").focus();						
					} else {
						var purchaseId = pPurchase.item.id;
						document.getElementById("pError").style.display = "none";
						iapPrepCall("/Ajax/iapGetDB", "P#", purchaseId, pProcPurchase);
						document.getElementById("pdetail").style.display="block";
						document.getElementById("pPurDate").focus();
					}
				}
	});

	$("#pPurVendor").autocomplete({
		source: pSplrList,
		minLength: 0,
		change: function(pEvent, pSupplier) {
					if (document.getElementById("pPurVendor").value == "") {
						document.getElementById("PSUPPID").value = 0;
						document.getElementById("UsePriceDiv").style.display = "none";
					} else {
						document.getElementById("PSUPPID").value = pSupplier.item.id;
						if (document.getElementById("pPurVendor").value == "Magnabilities LLC") {
							document.getElementById("UsePriceDiv").style.display = "block";
						}
					}
				}
	})

	$("#pItemCd").autocomplete({
		source: pItemCodes,
		minLength: 0,
		change: function(pEvent, pCode) { 
					if (!pCode.item) {
						pItemCd = document.getElementById("pItemCd").value;
						if (pItemCd == "") {
							sGenerateError("Enter an item code or description then click Add Item!");
							return;
						}
						var pSource = 0;
					} else {
						var pItemCd = pCode.item.cd;
						var pSource = pCode.item.src;
					}
					pICodeClicked(pItemCd, pSource);
				}
	});

	$("#pItemDesc").autocomplete({
		source: pItemDescs,
		minLength: 0,
		change: function(pEvent, pDesc) { 
					if (!pDesc.item) {
						pIDescClicked();
						return;
					}
					var pItemCd = pDesc.item.cd;
					var pSource = pDesc.item.src;
					pICodeClicked(pItemCd, pSource);
				}
	});
});

function pAddClicked() {
	pClearForm();
	document.getElementById("PUPDATETYPE").value = "NEW"; 
	document.getElementById("pdetail").style.display="block";
	document.getElementById("pPurDate").focus();
}

function pGoClicked() {
/*
	var pPrch = document.getElementById("pPurList").value;
	if (pPrch == "") {
		document.getElementById("pError").style.display = "block";
		return false;
	}

//	$p = $iapP['pur_order']." on ".date("m/d/Y", strtotime($iapP['pur_date']))." from ".$v;
//	$pPurs = $pPurs.$c.'{"label": "'.$p.'", "id": "'.strval($iapP['pur_id']).'"}';

	pPrchId = -1;
	for (var i = 0, len = pPrchList.length; i < len; i++) {
		var pNm = pPrchList[i].label;
 		if (pPrchList.label == pPrchName) {
			pPrchId = pPrchList.id;
			break;
		}
	}
	if (pPrchId == -1) {
		document.getElementById("pError").style.display = "block";
		return false;
	}

	iapPrepCall("/Ajax/iapGetDB", "P#", pPrchId, pProcPurchase);
	document.getElementById("pError").style.display = "none";
	document.getElementById("pdetail").style.display="inline";
	document.getElementById("pPurDate").focus();	
*/
}

function pCheckPO() {
	pPO = document.getElementById("pPurOrder").value;
	if (empty(pPO)) {
		return false;
	}
	iapPrepCall("/Ajax/iapGetDB", "PO", pPO, pChkPOReturn);
	return false;
}
function pChkPOReturn(pRet) {
	if (pRet < 0) {
		return false;
	}
	if (pRet == 0) {
		document.getElementById("pOrdNoExists").style.display="none";
	} else {
		document.getElementById("pOrdNoExists").style.display="inline";
	}
	return false;
}

function pProcPurchase(pPurchase) {
	pClearForm();
	if (pPurchase < 0) {
		return pPurchase;
	}
	if (pPurchase == 0) {
		document.getElementById("PUPDATETYPE").value = "NEW";
		document.getElementById("POID").value = "";
		return pPurchase;
	} else {
		document.getElementById("pPurDate").value = moment(pPurchase.pur_date).format("MM/DD/YYYY");
		document.getElementById("pPurVendor").value = pPurchase.pur_vendor;
		document.getElementById("pPurOrder").value = pPurchase.pur_order;
		document.getElementById("pPurNet").value = number_format(pPurchase.pur_net, 2, '.', ',');
		document.getElementById("pShipping").value = number_format(pPurchase.pur_shipping, 2, '.', ',');
		document.getElementById("pSalesTax").value = number_format(pPurchase.pur_tax, 2, '.', ',');
		document.getElementById("pPurMiles").value = number_format(pPurchase.pur_miles, 2, '.', ',');
		document.getElementById("pPurExp").value = number_format(pPurchase.pur_expenses, 2, '.', ',');
		document.getElementById("pExpExp").value = pPurchase.pur_exp_explained;
		document.getElementById("pComment").value = pPurchase.pur_comment;

		var pPurDtl = pPurchase.purdtl;
		var pPurDtlCnt = pPurDtl.length;
		var pTable = document.getElementById("iapReceived");
		var pIndex = 0;
		while(pIndex < pPurDtlCnt) {
			var pValueExt = pPurDtl[pIndex].purdet_quantity * pPurDtl[pIndex].purdet_cost;
			var pNewRow = pTable.insertRow(-1);
			var pIndent = pNewRow.insertCell(0);
			var pDel = pNewRow.insertCell(1);
			var pCode = pNewRow.insertCell(2);
			var pDesc = pNewRow.insertCell(3);
			var pQty = pNewRow.insertCell(4);
			var pCost = pNewRow.insertCell(5);
			var pValue = pNewRow.insertCell(6);

			pIndent.innerHTML = "";
			var pRows = pTable.rows.length;
			pNewRow.setAttribute("id", "Received"+pRows , 0);
			pIndent.innerHTML = "<input type='hidden' id='recrow" + pRows + "' value='E'>";

			pURL = document.getElementById("PIAPURL").value;
			pDel.innerHTML = "<img src='" + pURL + "/MyImages/Icons/DeleteRedSM.png' onclick='pDelReceived(" + pRows + "); return(false);'>&nbsp;&nbsp;";
			pCode.innerHTML = pPurDtl[pIndex].purdet_item;
			pDesc.innerHTML = pPurDtl[pIndex].purdet_desc;
			pQty.innerHTML = pPurDtl[pIndex].purdet_quantity;
			pCost.innerHTML = number_format(pPurDtl[pIndex].purdet_cost, 2, '.', ',');
			pValue.innerHTML = number_format(pValueExt, 2, '.', ',');

			var pNewData = document.getElementById("PNEWITEMINFO").value;
			document.getElementById("PNEWITEMINFO").value = pNewData + pPurDtl[pIndex].purdet_item + "~EXISTING~~~|";

			pIndex = pIndex + 1;
		}
		document.getElementById("PUPDATETYPE").value = "EXISTING";
		document.getElementById("POID").value = pPurchase.pur_id;
	}
}


// ---------------------------------------------------------------------------------
//
// Item Functions
//
// ---------------------------------------------------------------------------------

function pSetUsePrice() {
	if (document.getElementById("pUsePrc").checked == true) {
		document.getElementById("PUSEPRICE").value = "Y";
	} else {
		document.getElementById("PUSEPRICE").value = "";
	}
}

function pClrItemData() {
	pClearItem();
	document.getElementById("pItemCd").focus();
	return flase;
}

function pClearItem() {
	document.getElementById("pItemCd").value = "";
	document.getElementById("pItemDesc").value = "";
	document.getElementById("pItemQty").value = "";
	document.getElementById("pItemCost").value = "";
	document.getElementById("pNewPrice").value = "";
	document.getElementById("pNewUnits").value = "";
	document.getElementById("pNewCat").selectedIndex = 0;
}

function pItemFocus() {
	if (document.getElementById("pPurDate").value == "") {
		pGenerateError("Please enter the Purchase Date before entering items.");
	}
	return;
}

function pICodeClicked(pItemCd, pSource) {
	if (document.getElementById("pPurDate").value == "") {
		pGenerateError("Please enter the Purchase Date before entering items.");
		return false;
	}
	if (pItemCd == "") {
		sGenerateError("Enter an item code or description then click Add Item!");
		return;
	}
	if (document.getElementById("PTHISITEMSTATUS").value == "EXISTING") {
		document.getElementById("iapNewItem").style.display = "none";
		pGenerateError("<RESET>");
		document.getElementById("pItemDesc").value = "";
	}
////////////
	document.getElementById("PSUPPID").value = pSource;
////////////
	var argFld = pSource + "|" + pItemCd;
	var keyId = argFld + "~" + document.getElementById("pPurDate").value;
	iapPrepCall("/Ajax/iapGetDB", "I#", keyId, pProcItem);
	return false;
}

function pIDescClicked() {
	if (document.getElementById("pPurDate").value == "") {
		pGenerateError("Please enter the Purchase Date before entering items.");
		return false;
	}
	pDesc = document.getElementById("pItemDesc").value;
	if (pDesc == "") {
		sGenerateError("Enter an item code or description then click Add Item!");
		return;
	}
	if (document.getElementById("PTHISITEMSTATUS").value == "EXISTING") {
		document.getElementById("iapNewItem").style.display = "none";
		pGenerateError("<RESET>");
		document.getElementById("pItemCd").value = "";
	}
	var keyId = pDesc + "~" + document.getElementById("pPurDate").value;
	iapPrepCall("/Ajax/iapGetDB", "IN", keyId, pProcItem);
	return false;
}

function pProcItem(pItem) {
	if (document.getElementById("PTHISITEMSTATUS").value == "NEW") {
		pProcNewItem(pItem);
		return;
	}
	if (pItem == 0) {
		pNIClicked();
		pGenerateError("This item was not found. Please enter all the information below.");
		return false;
	}
	pProcItemGood(pItem);
	return;
}

function pProcItemGood(pItem) {
	if (pItem.cat_set == "Y") {
		pGenerateError("This item is a set.");		
	}
	document.getElementById("pItemCd").value = pItem.cat_item_code;
	document.getElementById("pItemDesc").value = pItem.cat_description;
	if (document.getElementById("PUSEPRICE").value == "Y") {
		document.getElementById("pItemCost").value = number_format(pItem.prc_price, 2, '.', ',');
	} else {
		document.getElementById("pItemCost").value = number_format(pItem.prc_cost, 2, '.', ',');
	}
	if (pItem.SUPPID == "CO") {
		document.getElementById("PSUPPID").value = 0;		
	} else {
		document.getElementById("PSUPPID").value = pItem.cat_supplier_id;
	}
	document.getElementById("PTHISITEMSTATUS").value = "EXISTING";
	document.getElementById("iapNewItem").style.display = "none";
	document.getElementById("pItemQty").focus();	
	return;
}

function pProcNewItem(pItem) {
	if (pItem != 0) {
		pProcItemGood(pItem);
	}		
	pItem = document.getElementById("pItemCd").value;
	if (document.getElementById("pItemCd").value == "") {
		document.getElementById("pItemCd").focus();
		return;
	}
	pDesc = document.getElementById("pItemDesc").value;
	if (pDesc == "") {
		document.getElementById("pItemDesc").focus();
		return;
	}
	document.getElementById("pItemQty").focus();
	return;
}

function pNIClicked() {
	if (document.getElementById("pItemCd").value == "") {
		pGenerateError("Enter the new item code then click Add.");
		document.getElementById("iapNewItem").style.display = "block";
		document.getElementById("pItemCode").focus();
		return false;
	}
	pGenerateError("<RESET>");
	if (document.getElementById("iapNewItem").style.display == "none") {
		document.getElementById("pItemQty").value = "";
		document.getElementById("pItemCost").value = "";
		document.getElementById("pNewUnits").value = "";
		document.getElementById("pNewPrice").value = "";
		document.getElementById("pNewCat").selectedIndex = 0;
		document.getElementById("iapNewItem").style.display = "block";
		document.getElementById("pItemDesc").focus();
		document.getElementById("PTHISITEMSTATUS").value = "NEW";
		return false;
	}
}

function pRecordItem() {
	pGenerateError("<RESET>");
	var pErrorFnd = "N";
	if (document.getElementById("pItemCd").value == "") {
		document.getElementById("pItemCdLbl").style.color = "red";
		pGenerateError("Item Code cannot be blank.");
		pErrorFnd = "Y";
	}
	if (document.getElementById("pItemDesc").value == "") {
		document.getElementById("pItemDescLbl").style.color = "red";
		pGenerateError("Description cannot be blank.");
		pErrorFnd = "Y";
	}
	if (document.getElementById("pItemQty").value == "") {
		document.getElementById("pItemQtyLbl").style.color = "red";
		pGenerateError("Quantity cannot be blank.");
		pErrorFnd = "Y";
	}
	var pQtyIn = parseInt(document.getElementById("pItemQty").value);
	if (isNaN(pQtyIn)) {
		document.getElementById("pItemQtyLbl").style.color = "red";
		pGenerateError("Quantity is invalid.");
		pErrorFnd = "Y";
	}
	if (document.getElementById("pItemCost").value == "") {
		document.getElementById("pItemCostLbl").style.color = "red";
		pGenerateError("Cost cannot be blank.");
		pErrorFnd = "Y";
	}
	var pItemCost = parseFloat(document.getElementById("pItemCost").value);
	if (isNaN(pItemCost)) {
		document.getElementById("pItemCostLbl").style.color = "red";
		pGenerateError("Cost is invalid.");
		pErrorFnd = "Y";
	}
	if (document.getElementById("PTHISITEMSTATUS").value == "NEW") {
		if (document.getElementById("pNewUnits").value == "") {
			document.getElementById("pNewUnitsLbl").style.color = "red";
			pGenerateError("Saleable Units cannot be blank.");
			pErrorFnd = "Y";
		}
		if (document.getElementById("pNewPrice").value == "") {
			document.getElementById("pNewPriceLbl").style.color = "red";
			pGenerateError("Selling Price cannot be blank.");
			pErrorFnd = "Y";
		}
		var pItemPrice = parseFloat(document.getElementById("pNewPrice").value);
		if (isNaN(pItemPrice)) {
			document.getElementById("pNewPriceLbl").style.color = "red";
			pGenerateError("Selling Price is invalid.");
			pErrorFnd = "Y";
		}
		if (document.getElementById("pNewCat").selectedIndex == 0) {
			document.getElementById("pNewCatLbl").style.color = "red";
			pGenerateError("Select a valid category for this item.");
			pErrorFnd = "Y";
		}
	}
	if (pErrorFnd == "Y") {
		pGenerateError("All item fields must be valid prior to clicking Record Item.");
		return false;
	}

	pClearItemError();

// -----------------------------------
// Get Item Code
// -----------------------------------
	if (document.getElementById("PTHISITEMSTATUS").value == "NEW") {
		var pItemCode = document.getElementById("pItemCd").value;
		var pOption = document.createElement("option");
		var pDList = document.getElementById("iapItemDL");
		pOption.value = pItemCode;

//	TODO		pDList.appendChild(pOption);

	} else {
		pItemCode = document.getElementById("pItemCd").value;
	}
	pQtyIn = parseInt(document.getElementById("pItemQty").value);
	pItemCost = parseFloat(document.getElementById("pItemCost").value);
	var pValueExt = parseFloat(pQtyIn * pItemCost);
	pGlblTotalNet = pGlblTotalNet + pValueExt;

	var pTable = document.getElementById("iapReceived");
	var pNewRow = pTable.insertRow(-1);
	var pIndent = pNewRow.insertCell(0);
	var pDel = pNewRow.insertCell(1);
	var pCode = pNewRow.insertCell(2);
	var pDesc = pNewRow.insertCell(3);
	var pQty = pNewRow.insertCell(4);
	var pCost = pNewRow.insertCell(5);
	var pValue = pNewRow.insertCell(6);
	var pSuppId = pNewRow.insertCell(7);

	pIndent.innerHTML = "";
	var pRows = pTable.rows.length - 1;
	pNewRow.setAttribute("id", "Received"+pRows , 0);
	var pEorN = document.getElementById("PTHISITEMSTATUS").value;
	pEorN = pEorN.substr(0,1);
	pIndent.innerHTML = "<input type='hidden' id='recrow" + pRows + " value='" + pEorN + "'>";

	pURL = document.getElementById("PIAPURL").value;
	pDel.innerHTML = "<img src='" + pURL + "/MyImages/Icons/DeleteRedSM.png' onclick='pDelReceived(" + pRows + "); return(false);'>&nbsp;&nbsp;";
	pCode.innerHTML = pItemCode;
	pDesc.innerHTML = document.getElementById("pItemDesc").value;
	pQty.innerHTML = pQtyIn;
	pCost.innerHTML = number_format(pItemCost, 2, '.', ',');
	pValue.innerHTML = number_format(pValueExt, 2, '.', ',');
	document.getElementById("pPurNet").value =  number_format(pGlblTotalNet, 2, '.', ',');
	pSuppId.innerHTML = document.getElementById("PSUPPID").value;

	var pNewData = document.getElementById("PNEWITEMINFO").value;
	var pItemCd = pItemCode;
//	if (document.getElementById("IAPDL").value == "N") {
//		pItemCd = document.getElementById("pNewItem").value;
//	}
	var pNewUnits = document.getElementById("pNewUnits").value;
	var pNewPrice = document.getElementById("pNewPrice").value;
	var pNewCat = document.getElementById("pNewCat").value;
//////////
	var pNewSupp = "0";
//////////
	var pStatus = document.getElementById("PTHISITEMSTATUS").value;
	document.getElementById("PNEWITEMINFO").value = pNewData + pItemCd + "~" + pStatus + "~" + pNewUnits + "~" + pNewPrice + "~" + pNewCat+ "~" + pNewSupp + "|";

	pClearItem();
	document.getElementById("PTHISITEMSTATUS").value = "";
	document.getElementById("pItemCd").focus();
	return false;
}

function pDelReceived(pRow) {
	var pRowId = "Received" + pRow;
	var pTblRow = document.getElementById(pRowId);
	var pTblCols = pTblRow.cells;
	var pValue = parseFloat(pTblCols[6].innerHTML);
	var pTotalNet = parseFloat(document.getElementById("pPurNet").value);
	if (isNaN(pTotalNet)) {
		pTotalNet = 0;
	}
	pGlblTotalNet = pGlblTotalNet - pValue;
	document.getElementById("pPurNet").value = number_format(pGlblTotalNet, 2, '.', ',');
	pTblRow.parentNode.removeChild(pTblRow);
	return(false);
}

function pClearForm() {
	document.getElementById("pPurList").value = "";
	document.getElementById("pPurDate").value = "";
	document.getElementById("pPurVendor").value = "";
	document.getElementById("pPurOrder").value = "";
	document.getElementById("pPurNet").value = "";
	document.getElementById("pShipping").value = "";
	document.getElementById("pSalesTax").value = "";
	document.getElementById("pPurMiles").value = "";
	document.getElementById("pPurExp").value = "";
	document.getElementById("pExpExp").value = "";
	document.getElementById("pComment").value = "";
	pClearItem();
// clear table
	var pTable = document.getElementById("iapReceived");
	var pRows = pTable.rows.length;
	while (pRows > 1) {
		document.getElementById("iapReceived").deleteRow(pRows - 1);
		pRows--;
	}
	pGlblTotalNet = 0;
	document.getElementById("PNEWITEMINFO").value = "";
	return false;
}

function pClearItem() {
	document.getElementById("pItemCd").value = "";
	document.getElementById("pItemDesc").value = "";
	document.getElementById("pItemQty").value = "";
	document.getElementById("pItemCost").value = "";
	document.getElementById("iapNewItem").style.display = "none";
	document.getElementById("pNewUnits").value = "";
	document.getElementById("pNewPrice").value = "";
	pClearItemError();
}

function pClearItemError() {
	document.getElementById("pItemCdLbl").style.color = "#666666";
	document.getElementById("pItemDescLbl").style.color = "#666666";
	document.getElementById("pItemQtyLbl").style.color = "#666666";
	document.getElementById("pItemCostLbl").style.color = "#666666";
	if (document.getElementById("IAPDL").value == "N") {
			document.getElementById("pNewItemHead").style.color = "#666666";
	}
	document.getElementById("pNewUnitsLbl").style.color = "#666666";
	document.getElementById("pNewPriceLbl").style.color = "#666666";
	document.getElementById("pNewCatLbl").style.color = "#666666";
	pGenerateError("<RESET>");
}

function pSendForm() {
	if (document.getElementById("pItemCd").value != ""
	||  document.getElementById("pItemDesc").value != "") {
		pGenerateError("An Item has not been Recorded. Either click Record Item or clear the data.");
		document.getElementById("pItemCd").focus();
		return false;
	}
	var pData = "";
	var pTable = document.getElementById('iapReceived');
	for (var r = 1, n = pTable.rows.length; r < n; r++) {
	    for (var c = 2, m = pTable.rows[r].cells.length; c < m; c++) {
			pData = pData + pTable.rows[r].cells[c].innerHTML + "~";
		}
		pData = pData + "|";
	}
	document.getElementById("IAPDATA").value = pData;	
	return true;
}

function pGenerateError(pErrMsg) {

	if (pErrMsg == "<RESET>") {
		document.getElementById("pItemError").innerHTML = " ";
		return;
	}
	var pExistingMsg = document.getElementById("pItemError").innerHTML;
	var pBreak = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	document.getElementById("pItemError").innerHTML = pExistingMsg + pBreak + pErrMsg;	
}
