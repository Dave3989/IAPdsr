$(function() {
	$("#pPCList").autocomplete({
		source: pParties,
		minLength: 0,
		change: function(pEvent, pPs) {
					if (pPs.item == null) {
						document.getElementById("pselerror").style.display="block";
						document.getElementById("pPCList").focus();
						return;	
					} else {
						document.getElementById("pselerror").style.display="none";
						var pcId = pPs.item.id;
						document.getElementById("PEIDChosen").value = pcId;
						document.forms["pcselform"].submit();
					}
				}
	});
});

function pGoClicked() {
/*
	var pParty = document.getElementById("jJList").value;
	if (pParty == "") {
		document.getElementById("pselerror").style.display = "block";
		return false;
	}

//	$p = date("m/d/Y", strtotime($iapP['pe_date']))." party ".$iapP['pe_party_no']." for ".trim($iapP['pe_sponsor']);
//	$iapPList = $iapPList.$c.'{"label": "'.$p.'", "id": "'.strval($iapP['pe_id']).'"}';

	pPartyId = -1;
	for (var i = 0, len = pParties.length; i < len; i++) {
		var jNm = pParties[i].label;
 		if (pParties.label == pParty) {
			pPartyId = pParties.id;
			break;
		}
	}
	if (pPartyId == -1) {
		document.getElementById("pselerror").style.display = "block";
		return false;
	}

	document.getElementById("pselerror").style.display="none";
	document.getElementById("PEIDChosen").value = pPartyId;
	document.forms["pcselform"].submit();
*/
}