var iGlobalICode = "";
var iGlobalItem = [];
var iGlobalPrices = [];
var iGlobalPurchases = [];
var iGlobalSales = [];

$(function() {
	$("#iItemList").autocomplete({
		source: iItemCodes,
		minLength: 0,
		change: function(iEvent, iCode) { 
					if (!iCode.item) {
						iItemCd = document.getElementById("iItemList").value;
						if (iItemCd == "") {
							document.getElementById("iSelError").innerHTML = "Select an item code or description or click Add Item!";
							document.getElementById("iSelError").style.display = "inline";
							return;
						}
						var iSource = 0;
					} else {
						var iItemCd = iCode.item.cd;
						var iSource = iCode.item.src;
					}
					iICodeClicked(iItemCd, iSource);
				}
	});

	$("#iDescList").autocomplete({
		source: iItemDescs,
		minLength: 0,
		change: function(iEvent, iDesc) { 
					if (!iDesc.item) {
						iDesc = document.getElementById("iDescList").value;
						if (iDesc == "") {
							document.getElementById("iSelError").innerHTML = "Select an item code or description or click Add Item!";
							document.getElementById("iSelError").style.display = "inline";
							return;
						}
						iIDescClicked(iDesc, 0);
						return;
					}
					var iItemCd = iDesc.item.cd;
					var iSource = iDesc.item.src;
					iICodeClicked(iItemCd, iSource);
				}
	});
});

function iAddClicked() {
	iClearForm();
	document.getElementById("iretire").style.display = "none";
	document.getElementById("icode").focus();
}

function iGoClicked() {
	iItem = document.getElementById("iItemList").value;
	if (iItem == "") {
		iDesc = document.getElementById("iDescList").value;
		if (iDesc == "") {
			document.getElementById("iSelError").innerHTML = "Enter an item code or description then click Add Item!";
			document.getElementById("iSelError").style.display = "inline";
			return;
		} else {
			iIDescClicked(iDesc, "0");
		}
	} else {
		iICodeClicked(iItem, "0");
	}
}

function iICodeClicked(iCode, iSource) {

	if (iSource == "0") {
		document.getElementById("iretire").style.display = "inline";
	}

//	iItem = document.getElementById("iItemList").value;
	if (iCode== "") {
		document.getElementById("iSelError").innerHTML = "Enter an item code or description then click Add Item!";
		document.getElementById("iSelError").style.display = "inline";
		return;
	}
	document.getElementById("iSelError").style.display = "none";
	document.getElementById("iDescList").value = "";

	igetCatalog("#", iCode, iSource);

	document.getElementById("iItemList").value = "";
	return;
}

function iIDescClicked(iDesc, iSource) {

	if (iSource == 0) {
		document.getElementById("iretire").style.display = "inline";
	}

//	iDesc = document.getElementById("iDescList").value;
	if (iDesc == "") {
		document.getElementById("iSelError").innerHTML = "Enter an item code or description then click Add Item!";
		document.getElementById("iSelError").style.display = "inline";
		return;
	}
	document.getElementById("iSelError").style.display = "none";
	document.getElementById("iItemList").value = "";

	igetCatalog("N", iDesc, iSource);

	document.getElementById("iDescList").value = "";
	return;
}

function igetCatalog(iTypeIn, iCode, iSource) {
	var typeFld = "I" + iTypeIn;
	var argFld = iSource + "|" + iCode;
	iapPrepCall("/Ajax/iapGetDB", typeFld, argFld, pProcItem);

	var iSrc = document.getElementById("IAPSUPPID").value; 
	if (iGlobalICode != 0 ) {
		var typeFld = "$>";
		var priceKey = iSrc + "|" + iGlobalICode + "|" + document.getElementById("IAPLODATE").value;
		iapPrepCall("/Ajax/iapGetDB", typeFld, priceKey, pProcPrices);
		iShowHistory(iCode, iSrc);
	}

//	document.getElementById("ichoose").style.display="none";
	document.getElementById("idetail").style.display="inline";

//	if (iSource == "CO") {
		document.getElementById("iapPrcFldset").style.display="inline";
//	} else {
//		document.getElementById("iapPrcFldset").style.display="none";
//	}

	document.getElementById("icode").focus(); 

	return;
}

function pProcItem(pItem) {
	iClearForm();
	if (pItem == 0) {
		iGlobalICode = 0;
		document.getElementById("iSelError").innerHTML = 
			"Item Code or Description not found. Check it or add as a new item.";
		document.getElementById("iSelError").style.display = "inline";							

		return(pItem);
	} else {
		iGlobalItem = pItem;
		iGlobalICode = pItem.cat_item_code;

		if (pItem.cat_active == "Y") {
			document.getElementById("iActMsgDiv").style.display = "none";
		} else {
			document.getElementById("iActMsgDiv").style.display = "inline";
			document.getElementById("iActMsgDiv").style.color = "red";
		}


		if (pItem.SUPPID == "SUPP") {
			document.getElementById("iSuppMsgDiv").style.display = "inline";
			document.getElementById("iSuppMsgDiv").style.color = "brown";
			document.getElementById("iSuppMsgName").innerHTML = pItem.cat_supplier;
			document.getElementById("iSuppMsgName").style.color = "brown";
			document.getElementById("iEffDateDiv").style.display = "none";			
		} else {
			document.getElementById("iSuppMsgDiv").style.display = "none";
		}


 		document.getElementById("icode").value = pItem.cat_item_code;
		document.getElementById("icode").readOnly = true;
		document.getElementById("idesc").value = pItem.cat_description;
		document.getElementById("isupplier").value = pItem.cat_supplier;

// Set selected category
		var pCatSel = document.getElementById("iselcat");
		for(i=0; i<pCatSel.length; i++) { 
			if(pCatSel.options[i].value == pItem.prc_cat_code) {
				pCatSel.options.selectedIndex = i;
				break; 
			} 
		}
		document.getElementById("ionhand").value = pItem.inv_on_hand;
		document.getElementById("iminonhand").value = pItem.inv_min_onhand;
		document.getElementById("icost").value = pItem.prc_cost;
		document.getElementById("iunits").value = pItem.prc_units;
		document.getElementById("iprice").value = pItem.prc_price;
		document.getElementById("IUPDATETYPE").value = "EXISTING";
		document.getElementById("IITEMCD").value = pItem.cat_item_code;
		document.getElementById("idesc").focus();

		var myDate = new Date();
		var myMth = myDate.getMonth() + 1;
		var myMDY = myMth + "/" + myDate.getDate() + "/" + myDate.getFullYear();
		document.getElementById("ieffdt").value = myMDY;
		document.getElementById("IAPEFFDT").value = myMDY;

		if (pItem.SUPPID == "CO") {
			document.getElementById("IAPSUPPID").value = 0;
		} else {
			document.getElementById("IAPSUPPID").value = pItem.cat_supplier_id;
		}
		document.getElementById("IAPCATIMG").value = pItem.cat_image_file;
		if (pItem.cat_image_file) {
			document.getElementById("iImgBtn").style.display = "inline";
		} else {
			document.getElementById("iImgBtn").style.display = "none";
		}

		if (pItem.cat_active == "N") {
			document.getElementById("idesc").readOnly = true;
			document.getElementById("isupplier").readOnly = true;
			document.getElementById("iselcat").readOnly = true;
			document.getElementById("icost").readOnly = true;
			document.getElementById("iunits").readOnly = true;
			document.getElementById("iprice").readOnly = true;
		}

		if (pItem.SUPPID == "SUPP") {
			document.getElementById("idesc").readOnly = true;
			document.getElementById("isupplier").readOnly = true;
			document.getElementById("iselcat").readOnly = true;
			document.getElementById("icost").readOnly = true;
			document.getElementById("iunits").readOnly = true;
			document.getElementById("iprice").readOnly = true;
		}

		return(pItem);
	}
}

function pProcPrices(pPriceHist) {

	iGlobalPrices = pPriceHist;
	if (pPriceHist == 0) {
		return(pPriceHist);
	}

	var pPH = "";
	var p1st = "Y";

	var todayDate = new Date();
	var todayMth = todayDate.getMonth() + 1;
	var todayMDY = todayMth + "/" + todayDate.getDate() + "/" + todayDate.getFullYear();
	document.getElementById("ieffdt").value = todayMDY;
	document.getElementById("IAPEFFDT").value = todayMDY;
	document.getElementById("IAPEFFTYPE").value = "T";

	var iTable = document.getElementById("iapPriceChgs");

	for(i = 0; i < pPriceHist.length; i++) {
		var pPH = pPriceHist[i];

// set effective date to highest price change date if > today.
		if (p1st == "Y") {
			var effDate = new Date(pPH['prc_effective']);
			effDate.setDate(effDate.getDate() + 2);
			if (effDate > todayDate) {
				var effMth = effDate.getMonth() + 1;
				var effMDY2 = moment(effDate).format("MM/DD/YYYY");
				var effMDY = effMth + "/" + effDate.getDate() + "/" + effDate.getFullYear();
				document.getElementById("ieffdt").value = effMDY;
				document.getElementById("IAPEFFDT").value = effMDY;
				document.getElementById("IAPEFFTYPE").value = "P";
			}
		}

		var iRows = iTable.rows.length;

		var iNewRow = iTable.insertRow(-1);
		iNewRow.setAttribute("id", "Item" + i , 0);
		iNewRow.setAttribute("class", "iapTD1");

		var pIndent1 = iNewRow.insertCell(0);
		pIndent1.innerHTML = " ";

		var pDel = iNewRow.insertCell(1);
		pDel.setAttribute("id", "Del" + i , 0);
		pDel.setAttribute("class", "iapTD1");
		if (p1st == "Y"
		&& pPriceHist.length > 0
		&& document.getElementById("IAPSUPPID").value == 0) {
			pDel.innerHTML = "<img src='MyImages/Icons/DeleteRedSM.png' style='height:13px; width:13px;' onclick='pDelSelected(" + i + "); return(false);'>";
			document.getElementById("IPRICEKEY").value = pPH.prc_item_code + "|" + pPH.prc_effective_until;
		} else {
			pDel.innerHTML = " ";
		}
		p1st = "N";

		var pIndent2 = iNewRow.insertCell(2);
		pIndent2.innerHTML = " ";

		var pDate = iNewRow.insertCell(3);
		pDate.innerHTML = moment(pPH.prc_effective).format("MM/DD/YYYY");

		var pCost = iNewRow.insertCell(4);
		pCost.setAttribute("style", "text-align:right;");
		pItemCost = parseFloat(pPH.prc_cost);
		pCost.innerHTML = number_format(pItemCost, 2, '.', ',') + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		var pUnits = iNewRow.insertCell(5);
		pUnits.setAttribute("style", "text-align:right;");
		pItemUnits = parseFloat(pPH.prc_units);
		pUnits.innerHTML = number_format(pItemUnits, 0, '.', ',') + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		var pCostUnit = iNewRow.insertCell(6);
		pCostUnit.setAttribute("style", "text-align:right;");
		pItemCostUnit = parseFloat(pPH.prc_cost_unit);
		pCostUnit.innerHTML = number_format(pItemCostUnit, 2, '.', ',') + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		var pPrice = iNewRow.insertCell(7);
		pPrice.setAttribute("style", "text-align:right;");
		pItemPrice = parseFloat(pPH.prc_price);
		pPrice.innerHTML = number_format(pItemPrice, 2, '.', ',') + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		var pFiller = iNewRow.insertCell(8);
		pFiller.innerHTML = " ";

		var pCat = iNewRow.insertCell(9);
		pCat.innerHTML = "&nbsp;&nbsp;&nbsp;" + pPH.code_value;
	}
	if (document.getElementById("IAPSUPPID").value == 0) {
		document.getElementById("irollback").style.display="inline";
		document.getElementById("irollbackfill").style.display="inline";
		document.getElementById("irollbackicon").style.display="inline";
		document.getElementById("irollbacktxt").style.display="inline";		
	} else {
		document.getElementById("irollback").style.display="none";
		document.getElementById("irollbackfill").style.display="none";
		document.getElementById("irollbackicon").style.display="none";
		document.getElementById("irollbacktxt").style.display="none";
	}

}

function pDelSelected(pRow) {

	var pPrices = iGlobalPrices[pRow];

	var urlin = document.URL;
	var urllc = urlin.toLowerCase();
	if (urllc.indexOf("litehausconsulting") >= 0) {
		var urldomain = "iap";
	} else {
		var urldomain = "itsapartydsr";
	}
	var uarray = urlin.split("/");
	var ln = "";
	for (i = 0; i <= uarray.length; i++) {
		ln = uarray.pop();
		var lnsm = ln.toLowerCase();
		if (lnsm.indexOf(urldomain) >= 0) {
			uarray.push(ln);
			var iappath = uarray.join("/");
			break;
		}
	}
	var urlFld = iappath+"/Ajax/iapRollBackPrice.php";
	var typeFld = "$R";
	var accountFld = document.getElementById("LHCA").value;
	var keyFld = pPrices.prc_item_code + "|" + pPrices.prc_effective + "|" + pPrices.prc_effective_until;
	iapCall(urlFld, typeFld, accountFld, keyFld, pProcRollBack);

	var pNewPrices = iGlobalPrices[pRow + 1];

// Set selected category
	var pCatSel = document.getElementById("iselcat");
	for(i=0; i<pCatSel.length; i++) { 
		if(pCatSel.options[i].value == pNewPrices.prc_cat_code) {
			pCatSel.options.selectedIndex = i;
			break; 
		} 
	}
	document.getElementById("icost").value = pNewPrices.prc_cost;
	document.getElementById("iunits").value = pNewPrices.prc_units;
	document.getElementById("iprice").value = pNewPrices.prc_price;
	document.getElementById("IPRICEKEY").value = pPrices.prc_item_code + "|" + pPrices.prc_effective_until;

	var todayDate = new Date();
	var todayMth = todayDate.getMonth() + 1;
	var todayMDY = todayMth + "/" + todayDate.getDate() + "/" + todayDate.getFullYear();
	document.getElementById("ieffdt").value = todayMDY;
	document.getElementById("IAPEFFDT").value = todayMDY;
	document.getElementById("IAPEFFTYPE").value = "T";

// set effective date to highest price change date if > today.
	var effDate = new Date(pNewPrices['prc_effective']);
	effDate.setDate(effDate.getDate() + 2);
	if (effDate > todayDate) {
		var effMth = effDate.getMonth() + 1;
		var effMDY = effMth + "/" + effDate.getDate() + "/" + effDate.getFullYear();
		document.getElementById("ieffdt").value = effMDY;
		document.getElementById("IAPEFFDT").value = effMDY;
		document.getElementById("IAPEFFTYPE").value = "P";
	}

// Remove deleted from  the table
	var pRowId = "Item" + pRow;
	var pTblRow = document.getElementById(pRowId);
	pTblRow.parentNode.removeChild(pTblRow);
	var pRow2 = pRow + 1;
	var pDelId = "Del" + pRow2;
	pNewDel = document.getElementById(pDelId);	
	pNewDel.innerHTML = "<img src='MyImages/Icons/DeleteRedSM.png' style='height:13px; width:13px;' onclick='pDelSelected(" + pRow2 + "); return(false);'>";

// Remove Item from array
	iGlobalPrices.splice(pRow, 1);

	return;
}

function pProcRollBack(iRet) {

	if (iRet < 0) {
		
	}
	return(false);
}

function iShowHistory(iCode, iSource) {

// clear table
	var iTable = document.getElementById("ihistory");
	var iRows = iTable.rows.length;
	while (iRows > 6) {
		document.getElementById("ihistory").deleteRow(iRows - 1);
		iRows--;
	}

// Get purchases and sales
	iGetPurchases(iCode, iSource);
	iGetSales(iCode, iSource);

	if (iGlobalPurchases.length == 0
	&&  iGlobalSales.length == 0) {
		document.getElementById("iapActFldset").style.display="none";
		return;
	} else {
		document.getElementById("iapActFldset").style.display="inline";
	}

	var iP = 0;
	var iS = 0;
	var iDone = "N";
	var iPSHistory = "";
	var iPurDate = "";
	var iSaleDate = "";

// Merge purchases and Sales
	while(iDone == "N") {
		if (iP == iGlobalPurchases.length) {
			iPurDate = "2000-01-01";
		} else {
			iPurDate = iGlobalPurchases[iP]['purdet_date'];			
		}
		if (iS == iGlobalSales.length) {
			iSaleDate = "2000-01-01";
		} else {			
			iSaleDate = iGlobalSales[iS]['sale_date'];
		}
		if (iPurDate == "2000-01-01"
		&& iSaleDate == "2000-01-01") {
			iDone = "Y";
		} else {
			if (iPurDate > iSaleDate) {
				iPSHistory = ["P", iGlobalPurchases[iP]['purdet_purid'], iGlobalPurchases[iP]['purdet_item'], iGlobalPurchases[iP]['cat_description'], iGlobalPurchases[iP]['purdet_date'], iGlobalPurchases[iP]['purdet_quantity'], iGlobalPurchases[iP]['purdet_cost'], iGlobalPurchases[iP]['purdet_ext_cost'], iGlobalPurchases[iP]['pur_vendor'] + "-" + iGlobalPurchases[iP]['pur_order'], 0, 0];
				iP = iP + 1;
			} else if (iPurDate < iSaleDate) {
				iPSHistory =["S", iGlobalSales[iS]['saledet_sid'], iGlobalSales[iS]['saledet_item_code'], iGlobalSales[iS]['cat_description'], iGlobalSales[iS]['sale_date'], iGlobalSales[iS]['saledet_quantity'], 0, iGlobalSales[iS]['saledet_total_cost'], iGlobalSales[iS]['cust_name'] + '-' + iGlobalSales[iS]['pe_sponsor'], iGlobalSales[iS]['saledet_price'], iGlobalSales[iS]['saledet_total_price']];
				iS = iS + 1;
			} else {
				if (iGlobalPurchases[iP]['purdet_item'] < iGlobalSales[iS]['saledet_item_code']) {
					iPSHistory = ["P", iGlobalPurchases[iP]['purdet_purid'], iGlobalPurchases[iP]['purdet_item'], iGlobalPurchases[iP]['cat_description'], iGlobalPurchases[iP]['purdet_date'], iGlobalPurchases[iP]['purdet_quantity'], iGlobalPurchases[iP]['purdet_cost'], iGlobalPurchases[iP]['purdet_ext_cost'], iGlobalPurchases[iP]['pur_vendor'] + "-" + iGlobalPurchases[iP]['pur_order'], 0, 0];
					iP = iP + 1;
				} else if  (iGlobalPurchases[iP]['purdet_item'] > iGlobalSales[iS]['saledet_item_code']) {
					iPSHistory =["S", iGlobalSales[iS]['saledet_sid'], iGlobalSales[iS]['saledet_item_code'], iGlobalSales[iS]['cat_description'], iGlobalSales[iS]['sale_date'], iGlobalSales[iS]['saledet_quantity'], 0, iGlobalSales[iS]['saledet_total_cost'], iGlobalSales[iS]['cust_name'] + '-' + iGlobalSales[iS]['pe_sponsor'], iGlobalSales[iS]['saledet_price'], iGlobalSales[iS]['saledet_total_price']];
					iS = iS + 1;
				} else {
					iPSHistory = ["P", iGlobalPurchases[iP]['purdet_purid'], iGlobalPurchases[iP]['purdet_item'], iGlobalPurchases[iP]['cat_description'], iGlobalPurchases[iP]['purdet_date'], iGlobalPurchases[iP]['purdet_quantity'], iGlobalPurchases[iP]['purdet_cost'], iGlobalPurchases[iP]['purdet_ext_cost'], iGlobalPurchases[iP]['pur_vendor'] + "-" + iGlobalPurchases[iP]['pur_order'], 0, 0];
					iP = iP + 1;
					cPutHistoryOut(iPSHistory);

					iPSHistory =["S", iGlobalSales[iS]['saledet_sid'], iGlobalSales[iS]['saledet_item_code'], iGlobalSales[iS]['cat_description'], iGlobalSales[iS]['sale_date'], iGlobalSales[iS]['saledet_quantity'], 0, iGlobalSales[iS]['saledet_total_cost'], iGlobalSales[iS]['cust_name'] + '-' + iGlobalSales[iS]['pe_sponsor'], iGlobalSales[iS]['saledet_price'], iGlobalSales[iS]['saledet_total_price']];
					iS = iS + 1;
				}
			}
			cPutHistoryOut(iPSHistory);
		}
	}
}

function cPutHistoryOut(iPSHistory) {

	var iTable = document.getElementById("ihistory");
	var iNewRow = iTable.insertRow(-1);
	iNewRow.setAttribute("class", "iapTD1");

	var iType = iNewRow.insertCell(0);

	var iType = iNewRow.insertCell(1);
	if (iPSHistory[0] == "P") {
		iType.innerHTML = "<a href='purchase/?action=selected&pur=" + iPSHistory[1] + "'>Purchase</a>";
	} else {
		iType.innerHTML = "<a href='sale/?action=selected&sale=" + iPSHistory[1] + "'>Sale</a>";
	}

	var iDate = iNewRow.insertCell(2);
	iDate.innerHTML = moment(iPSHistory[4]).format("MM/DD/YYYY");

	var iDesc = iNewRow.insertCell(3);
	iDesc.innerHTML = iPSHistory[8];

// Type	Date	Reference	Quantity	Cost/Unit	Total Cost	Price/Unit	Total Price
//	0	 1		  2				3			4			5			6			7
//	1	 4		  8				5			6			7			9			10

	var iQty = iNewRow.insertCell(4);
	iQty.setAttribute("style", "text-align:right;");
	iItemQty = parseFloat(iPSHistory[5]);
	iQty.innerHTML = number_format(iItemQty, 0, '.', ',');

	var iCost = iNewRow.insertCell(5);
	iCost.setAttribute("style", "text-align:right;");
	iItemCost = parseFloat(iPSHistory[6]);
	iCost.innerHTML = number_format(iItemCost, 2, '.', ',');

	var iTCost = iNewRow.insertCell(6);
	iTCost.setAttribute("style", "text-align:right;");
	iItemTCost = parseFloat(iPSHistory[7]);
	iTCost.innerHTML = number_format(iItemTCost, 2, '.', ',');

	var iPrice = iNewRow.insertCell(7);
	if (iPSHistory[0] != "P") {
		iPrice.setAttribute("style", "text-align:right;");
		iItemPrice = parseFloat(iPSHistory[9]);
		iPrice.innerHTML = number_format(iItemPrice, 2, '.', ',');
	}

	var iTPrice = iNewRow.insertCell(8);
	if (iPSHistory[0] != "P") {
		iTPrice.setAttribute("style", "text-align:right;");
		iItemTPrice = parseFloat(iPSHistory[10]);
		iTPrice.innerHTML = number_format(iItemTPrice, 2, '.', ',');
	}

	var iTPrice = iNewRow.insertCell(9);

	return;
}

function iGetPurchases(iCode, iSource) {
	iapPrepCall("/Ajax/iapGetDB", "PS", iCode, iProcPurchases);
	return;
}

function iProcPurchases(iPursIn) {
	if (iPursIn == 0) {
		iGlobalPurchases = [];
		return;
	}
	iGlobalPurchases = iPursIn;
	return;
}

function iGetSales(iCode, iSource) {
	var iKey = iSource+"~"+iCode;
	iapPrepCall("/Ajax/iapGetDB", "SS", iKey, iProcSales);
	return;
}

function iProcSales(iSalesIn) {
	if (iSalesIn == 0) {
		iGlobalSales = [];
		return;
	}
	iGlobalSales = iSalesIn;
	return;
}

function iClearForm() {

// clear form input fields
	document.getElementById("iDescList").value = "";
	document.getElementById("iItemList").value = "";
//	document.getElementById("ichoose").style.display="none";
	document.getElementById("idetail").style.display="inline";
	document.getElementById("icode").value = "";
	document.getElementById("idesc").value = "";
	document.getElementById("iselcat").options.selectedIndex = 0;
	document.getElementById("isupplier").value = "";
	document.getElementById("ionhand").value = "0";
	document.getElementById("iminonhand").value = "0";
	document.getElementById("icost").value = "0";
	document.getElementById("iunits").value = "0";
	document.getElementById("iprice").value = "0";
	document.getElementById("ieffdt").value = "";
	document.getElementById("iapPrcFldset").style.display="none";
	document.getElementById("irollback").style.display="none";
	document.getElementById("irollback").style.display="none";
	document.getElementById("irollbackfill").style.display="none";
	document.getElementById("irollbackicon").style.display="none";
	document.getElementById("irollbacktxt").style.display="none";

	document.getElementById("iretirefill").style.display="none";
	document.getElementById("iretire").style.display = "none";

// clear price history table
	var pTable = document.getElementById("iapPriceChgs");
	var pRows = pTable.rows.length;
	while (pRows > 1) {
		document.getElementById("iapPriceChgs").deleteRow(pRows - 1);
		pRows--;
	}

// clear sales history table
	document.getElementById("iapActFldset").style.display="none";
	var iTable = document.getElementById("ihistory");
	var iRows = iTable.rows.length;
	while (iRows > 6) {
		document.getElementById("ihistory").deleteRow(iRows - 1);
		iRows--;
	}
// clear global fields
	iGlobalItem = [];
	iGlobalPrices = [];
	iGlobalPurchases = [];
	iGlobalSales = [];

// clear hidden fields
	document.getElementById("IUPDATETYPE").value = "NEW";
	document.getElementById("IITEMCD").value = "";
	document.getElementById("IPRICEKEY").value = "";
	document.getElementById("IAPEFFDT").value = "";
	document.getElementById("IAPEFFTYPE").value = "";
}