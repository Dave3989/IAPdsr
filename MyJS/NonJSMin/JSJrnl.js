var pGlobalItem = [];
var jGlobalJrnl = [];

$(function() {
	$("#jselect").autocomplete({
		source: jJList,
		minLength: 0,
		change: function(sEvent, jJrnl) { 
					if (document.getElementById("jselect").value == "") {
						document.getElementById("jError").style.display="block";
						document.getElementById("jselect").focus();						
					} else {
						var jrnlId = jJrnl.item.jrnlid;
						jJrnl = iapPrepCall("/Ajax/iapGetDB", "J#", jrnlId, jProcJrnl);
						document.getElementById("jselect").value = "";
						document.getElementById("jerror").style.display="none";
						document.getElementById("jdetail").style.display="block";

						var jOption = document.getElementById("jtype").value;
						if (jOption == "IP") {
							var iapKey = "cat" + "~" + jGlobalJrnl['jrnl_cat_code'];
							iapPrepCall("/Ajax/iapGetDB", "D#", iapKey, jProcCode);
						}
					}
				}
	});
});

function jAddClicked() {
	jblankJournal();
	document.getElementById("jdetail").style.display="block"; 
	document.getElementById("jdate").focus();
	return;
}

function jblankJournal() {
	document.getElementById("jlink").style.display = "none";
	document.getElementById("jdate").value = "";
	document.getElementById("jdesc").value = "";
	document.getElementById("jtype").value = "";
	document.getElementById("jtype").disabled=false;
	document.getElementById("jcbamt").value=""; 
	document.getElementById("jcbprft").value=""; 
	document.getElementById("jipitem").value = "";
	document.getElementById("jipcost").value = "";
	document.getElementById("jipunits").value = "";
	document.getElementById("jipprice").value = "";
	document.getElementById("jipcat").value = "";
	document.getElementById("joiamt").value=""; 
	document.getElementById("jpivend").value=""; 
	document.getElementById("jpinet").value=""; 
	document.getElementById("jpiship").value=""; 
	document.getElementById("jpitax").value=""; 
	document.getElementById("jpiamt").value=""; 
	document.getElementById("jpsvend").value=""; 
	document.getElementById("jpsamt").value=""; 
	document.getElementById("jpsship").value=""; 
	document.getElementById("jpstax").value=""; 
	document.getElementById("jpsmiles").value="";
	document.getElementById("jmeamt").value=""; 
	document.getElementById("jmeship").value=""; 
	document.getElementById("jmetax").value=""; 
	document.getElementById("jmemiles").value=""; 
	document.getElementById("jwevend").value=""; 
	document.getElementById("jweamt").value=""; 
	document.getElementById("jweship").value=""; 
	document.getElementById("jwetax").value=""; 
	document.getElementById("jwemiles").value=""; 
//	document.getElementById("jpxdir").value=""; 
	document.getElementById("jpxcost").value=""; 
	document.getElementById("jpxship").value="";
	document.getElementById("jpxtax").value="";
	document.getElementById("jsvend").value=""; 
	document.getElementById("jscost").value=""; 
	document.getElementById("jsship").value=""; 
	document.getElementById("jstax").value="";
	document.getElementById("jsprft").value="";
	document.getElementById("jstotal").value = "";
	document.getElementById("jcomment").value = "";
	document.getElementById("JUPDATETYPE").value = "NEW";
	document.getElementById("JJRNLDATE").value = "";
	document.getElementById("JJRNLID").value = "";

	jSetOptionDiv();
	return(true);	
}

function jGoClicked() {
/*
	var jJrnl = document.getElementById("jJList").value;
	if (jJrnl == "") {
		document.getElementById("jError").style.display = "block";
		return false;
	}

//	$j = date('m/d/Y', strtotime($iapJ['jrnl_date']))." ".str_replace('"', "'", $iapJ['jrnl_description']);
//	$jJrnls = $jJrnls.$c.'{"label": "'.$j.'", "jrnlid": "'.strval($iapJ['jrnl_id']).'"}';

	jJrnlId = -1;
	for (var i = 0, len = jJrnlList.length; i < len; i++) {
		var jNm = jJrnlList[i].label;
 		if (jJrnlList.label == jJrnl) {
			jJrnlId = jJrnlList.jrnlid;
			break;
		}
	}
	if (jJrnlId == -1) {
		document.getElementById("jError").style.display = "block";
		return false;
	}

	jJrnl = iapPrepCall("/Ajax/iapGetDB", "J#", jrnlId, jProcJrnl);
	document.getElementById("jselect").value = "";
	document.getElementById("jerror").style.display="none";
	document.getElementById("jdetail").style.display="block";

	var jOption = document.getElementById("jtype").value;
	if (jOption == "IP") {
		var iapKey = "cat" + "~" + jGlobalJrnl['jrnl_cat_code'];
		iapPrepCall("/Ajax/iapGetDB", "D#", iapKey, jProcCode);
	}
*/
}

function jProcJrnl(pJrnl) {

	if (pJrnl == 0) {
		document.getElementById("iupdatetype").value = "NEW";
		jblankJournal();
		return(pJrnl);
	} else {
		jblankJournal();
		jGlobalJrnl = pJrnl;

		document.getElementById("jdate").value = moment(pJrnl.jrnl_date).format("MM/DD/YYYY");
		document.getElementById("jdesc").value = pJrnl.jrnl_description;
		document.getElementById("jtype").value = pJrnl.jrnl_type;
		document.getElementById("jtype").disabled = true;

		var iapNet = format("###,###0.00", pJrnl.jrnl_net);
		var iapAmt = format("###,###0.00", pJrnl.jrnl_amount);
		var iapShip = format("#,###0.00", pJrnl.jrnl_shipping);
		var iapTax = format("#,###0.00", pJrnl.jrnl_tax);
		var iapMiles = format("#,###0.00", pJrnl.jrnl_mileage);
		var iapProfit = format("###,###0.00", pJrnl.jrnl_profit);
		var iapCost = format("###,###0.00", pJrnl.jrnl_cost);
		var iapPrice = format("###,###0.00", pJrnl.jrnl_price);

		switch(pJrnl.jrnl_type) {
			case "CB":
				document.getElementById("jcbamt").value = iapAmt;
				document.getElementById("jcbprft").value = iapProfit;
			    break;
			case "IP":
				document.getElementById("jipitem").value = pJrnl.jrnl_item_code;
				document.getElementById("jipcost").value = iapCost;
				document.getElementById("jipunits").value = pJrnl.jrnl_units;
				document.getElementById("jipprice").value = iapPrice;
				break;
			case "ME":
				document.getElementById("jmeamt").value = iapAmt;
				document.getElementById("jmeship").value = iapShip;
				document.getElementById("jmetax").value = iapTax;
				document.getElementById("jmemiles").value = iapMiles;
			    break;
			case "MI":
			    break;
			case "OI":
				document.getElementById("joiamt").value = iapAmt;
			    break;
			case "PI":
				document.getElementById("jpivend").value = pJrnl.jrnl_vendor;
				document.getElementById("jpinet").value = iapNet;
				document.getElementById("jpiship").value = iapShip;
				document.getElementById("jpitax").value = iapTax;
				document.getElementById("jpiamt").value = iapAmt;
				document.getElementById("jcomment").readonly = true;

				if (pJrnl.jrnl_detail_key != "") {
					document.getElementById("jlink").style.display="none"; 
				} else {
					document.getElementById("jlink").style.display="block"; 
					var link = "?page_id=208&action=selected&pur=" + pJrnl.jrnl_detail_key;
					document.getElementById("jlinkref").href = link;
				}
			    break;
			case "PS":
				document.getElementById("jpsvend").value = pJrnl.jrnl_vendor;
				document.getElementById("jpsamt").value = iapAmt;
				document.getElementById("jpsship").value = iapShip;
				document.getElementById("jpstax").value = iapTax;
				document.getElementById("jpsmiles").value = iapMiles;
			    break;
			case "PX":
//				document.getElementById("jpxdir").value = pJrnl.jrnl_direction;
				document.getElementById("jpxcost").value = iapAmt;
				document.getElementById("jpxship").value = iapShip;
				document.getElementById("jpxtax").value = iapTax;
			    break;
			case "SE":
			case "SP":
				document.getElementById("jsvend").value = pJrnl.jrnl_vendor;
			case "SI":
			case "SO":
			case "SW":
				document.getElementById("jsnet").value = iapNet;
				document.getElementById("jscost").value = iapCost;
				document.getElementById("jsship").value = iapShip;
				document.getElementById("jstax").value = iapTax;
				document.getElementById("jsprft").value = iapProfit;
				document.getElementById("jstotal").value = iapAmt;

				if (pJrnl.jrnl_detail_key == "") {
					document.getElementById("jlink").style.display="none"; 
				} else {
					document.getElementById("jlink").style.display="block"; 
					var jslink = "?page_id=291&action=selected&sale=" + pJrnl.jrnl_detail_key;
					document.getElementById("jlinkref").href = jslink; 
				}
			    break;
			case "WE":
				document.getElementById("jweend").value = pJrnl.jrnl_vendor;
				document.getElementById("jweamt").value = iapAmt;
				document.getElementById("jweship").value = iapShip;
				document.getElementById("jwetax").value = iapTax;
				document.getElementById("jwemiles").value = iapMiles;
			    break;
		}
		document.getElementById("jcomment").value = pJrnl.jrnl_comment;

		document.getElementById("JUPDATETYPE").value = "EXISTING";
		document.getElementById("JJRNLDATE").value = pJrnl.jrnl_date;
		document.getElementById("JJRNLID").value = pJrnl.jrnl_id;

		jSetOptionDiv();
		document.getElementById("jtype").readonly = true;
	}
	return(pJrnl);
}

function jProcCode(jCode) {
	document.getElementById("jipcat").value = jCode.code_value;
	jGlobalJrnl['jrnl_code_value'] = jCode.code_value;
}

function jSetOptionDiv() {
	document.getElementById("jcomm").style.display = "none"; 
	document.getElementById("jitemprc").style.display="none"; 
	document.getElementById("jmiscexp").style.display="none"; 
	document.getElementById("jowninv").style.display="none"; 
	document.getElementById("jpurinv").style.display="none"; 
	document.getElementById("jpursup").style.display="none"; 
	document.getElementById("jprodxchg").style.display="none"; 
	document.getElementById("jsales").style.display="none"; 
	document.getElementById("jsales2").style.display="none"; 
	document.getElementById("jwebexp").style.display = "none"; 

	var jOption = document.getElementById("jtype").value;
	switch(jOption) {
		case "":
			break;
		case "CB":
			document.getElementById("jcomm").style.display = "block"; 
			document.getElementById("jcomment").readonly = false;
			document.getElementById("csubmit").style.display = "block";
			break;
		case "IP":
			document.getElementById("jitemprc").style.display = "block"; 
			document.getElementById("jcomment").readonly = true;
			document.getElementById("csubmit").style.display = "none";
			break;
		case "ME":
			document.getElementById("jmiscexp").style.display = "block"; 
			document.getElementById("jcomment").readonly = false;
			document.getElementById("csubmit").style.display = "block";
			break;
		case "MI":
			document.getElementById("jcomment").readonly = false;
			document.getElementById("csubmit").style.display = "block";
			break;
		case "OI":
			document.getElementById("jowninv").style.display = "block"; 
			document.getElementById("csubmit").style.display = "block";
			document.getElementById("jcomment").readonly = false;
			break;
		case "PI":
			document.getElementById("jpurinv").style.display = "block"; 
			document.getElementById("jcomment").readonly = true;
			document.getElementById("csubmit").style.display = "none";
			break;
		case "PS":
			document.getElementById("jpursup").style.display = "block"; 
			document.getElementById("jcomment").readonly = false;
			document.getElementById("csubmit").style.display = "block";
			break;
		case "PX":
			document.getElementById("jprodxchg").style.display = "block"; 
			document.getElementById("jcomment").readonly = true;
			document.getElementById("csubmit").style.display = "none";
			break;
		case "SE":
		case "SP":
			document.getElementById("jsales").style.display = "block"; 
			document.getElementById("jcomment").readonly = true;
			document.getElementById("csubmit").style.display = "none";
			break;
		case "SI":
		case "SO":
		case "SW":
			document.getElementById("jsales2").style.display = "block"; 
			document.getElementById("jcomment").readonly = true;
			document.getElementById("csubmit").style.display = "none";
			break;
		case "WE":
			document.getElementById("jwebexp").style.display = "block"; 
			document.getElementById("jcomment").readonly = false;
			document.getElementById("csubmit").style.display = "block";
			break;
	}
}