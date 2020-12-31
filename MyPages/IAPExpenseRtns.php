<?php

function IAP_Load_Expenses($expMod, $expId) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Load_Expenses with module of ".$expMod." and purId of ".strval($purId).".<br>";
	}

	$iapExps = IAP_Get_Expenses($expMod, $expId);
	if ($iapExps < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive expenses for ".$expMod."-".$expId.". [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}
	return($iapExps);
}

function IAP_Format_Expenses($expTextIndex, $expReadOnly, $expOldAmt, $expOldExplain, $expNewExps = NULL) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Format_Expenses.<br>";
	}

	$r = "";
	if ($expOldAmt != 0
	or  $expOldExplain > "") {
		$r = $r.'<label class="iapFormLabel">Other Expenses:</label>&nbsp;<input '.
			 $expReadOnly.' maxlength="10" size="10" tabindex="'.strval($expTextIndex++).
			 '" name="expOAmt" id="expOAmt" align="right" step="0.1" value='.$expOldAmt.'>';
		$r = $r.'<span style="font-size: 80%">Do not include shipping or tax</span><br>';
//	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		$r = $r.'<label class="iapFormLabel">Explain Expenses:</label>&nbsp;';
		$r = $r.'<textarea name="expOExp" id="expOExp" cols="50" rows="4" wrap="soft" tabindex="'.
				strval($expTextIndex++).'" .'.$expReadOnly.'">'.$expOldExplain.'</textarea><br>';
	} else {
?>
		<label class=iapFormLabel>Other Expenses:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<button class=iapButton style="margin:0 0 5px;" 
		        tabindex="<?php echo strval($expTextIndex++); ?>" name="pAddExp" id="paddExp" 
		        onclick="addExpRow(); return false;">Click Here To Add Other Expenses</button>
<?php
		if (isset($expNewExps)) {
			$d = "block";
		} else {
			$d = "none";
		}
		$r = $r."<table id='expTbl' class='iapTable' style='display:".$d."; width:100%'><tbody class=iapTBody>";

		$expCds = IAP_Get_Expense_Codes();
		if ($expCds < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive expense codes. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}

		$r = $r.'<tr><td style="width:5%;"></td>';
		$r = $r.'<td style="width:5%;"></td>';
		$r = $r.'<td style="width:20%; text-align:center;">Exp Type</td>';
		$r = $r.'<td style="width:15%; text-align:center;">Amount</td>';
		$r = $r.'<td style="width:55%; text-align:center;">More Information</td>';
		$r = $r.'<td style="width:5%"> </td></tr>';
		if (!empty($expExps)) {
			$i = 0;
			foreach($expExps as $e) {						// format a row for each expense used
				$i++;
				$r = $r.'<tr style="margin:0; border:0;">';
				$r = $r."<td><input type='hidden' id='expRow".$i
					   ."' name='expRow".$i
					   ."' value='row".$i
					   ."'></td>";
				$r = $r."<td><img src='".$_REQUEST['IAPUrl'].
						"/MyImages/Icons/DeleteRedSM.png' onclick='expDel(".$i."); return(false);'></td>";
				$r = $r."<td><select size='1' id='expSel".$i."' name='expSel".$i.
						"' required>";
				foreach($expCds as $c) {
					$r = $r."<option value='".$c['expcd_type']."'";
					if ($e['expType'] == $c['expcd_type']) {
						$r = $r." selected";
					}
					$r = $r.">".$c['expcd_value']."</option>";
				}
				$r = $r."</select></td>";
				$r = $r."<td><input type='number' id='expAmt".$i.
						"' name='expAmt".$i."'style='text-align:right;' size='10' maxlength='10' ".
						"placeholder='x,xxx.xx' step='0.01' min='0' value='".
						number_format($e['expAmt'], 0, '.', ',')."'></td>";
				$r = $r."<td><input type='text' id='expInfo".$i."' name='expInfo".$i.
						"' size='50' maxlength='250' value='".$e['expInfo']."'></td><td></td></tr>";
			}
		}
		$r = $r."</tbody></table>";
	}
	return($r);
}

function IAP_Validate_Expenses($expTbl) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Expenses.<br>";
	}

	require_once($_REQUEST['IAPDir']."IAPValidators.php");
	$expNew = array();
	$expChg = "N";
	$expErr = "N";
	for($i = 0; $i < $_REQUEST['EXPCNT']; $i++){
		$expNew[$i] = array("expType" => $_REQUEST["expSel".strval($i+1)], "expAmt" => $_REQUEST["expAmt".strval($i+1)], "expInfo" => $_REQUEST["expInfo".strval($i+1)]);

		if ($expNew[$i]['expType'] != $expTbl[$i]['expType']) {
			$expChg = "Y";
		}
		if (isset($expNew[$i]['expAmt'])) {
			$iapRet = IAP_Validate_Nonblank($expTbl[$i]['expAmt'], $expNew[$i]['expAmt'], "Y");
			if ($iapRet['Changed'] == "Y") {
			    $iapChanged = "Y";
			}
			if ($iapRet['Error'] == "1") {
			    echo "<span class=iapError>Expense Amount on expense row ".strval($i)." is invaid!</span><br>";
				$expErr = "1";
			}
		} elseif (empty($iapPurchase['pur_order'])) {
			echo "<span class=iapError>Expense Amount on expense row ".strval($i)." must be numeric if entered!</span><br>";
			$expErr = "1";
		}
		if (isset($expNew[$i]['expInfo'])) {
			$iapRet = IAP_Validate_Nonblank($expTbl[$i]['expInfo'], $expNew[$i]['expInfo']);
			if ($iapRet['Changed'] == "Y") {
			    $iapChanged = "Y";
			}
			if ($iapRet['Error'] == "1") {
			    echo "<span class=iapError>Explanation on expense row ".strval($i)." is invaid!</span><br>";
				$expErr = "2";
			}
		} elseif (empty($iapPurchase['pur_order'])) {
			echo "<span class=iapError>Explanation on expense row ".strval($i)." must be entered!</span><br>";
			$expErr = "2";
		}
		if ($expNew[$i]['expType'] == 99 
		and (empty($expNew[$i]['expInfo']))) {
			echo "<span class=iapError>Explanation on expense row ".strval($i)." must be entered!</span><br>";
			$expErr = "3";
		}
	}
	return(array("Error" => $expErr, "Changed" => $expChg, "Table" => $expNew));
}

function IAP_Write_Expenses($expMod, $expId, $expTbl) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Write_Expenses.<br>";
	}

	foreach($expTbl as $t) {
		$expRec = IAP_Build_New_Row(array("table" => "exp"));
		if ($expRec < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot get new row in expenses. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		$expRec = $expRec[0];
		$expRec['exp_company'] = $_REQUEST['CoId'];
		$expRec['exp_module'] = $expMod;
		$expRec['exp_id'] = $expId;
		$expRec['exp_type'] = $t['expType'];
		$expRec['exp_amount'] = $t['expAmt'];
		$expRec['exp_info'] = $t['expInfo'];
		$expRec['exp_changed'] = date("Y-m-d", strtotime("now"));
		$expRec['exp_changed_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($expRec, "exp");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot write expenses. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
	}
}

?>
<script type="text/javascript">

function addExpRow() {

	document.getElementById("expTbl").style.display = "inline-block";

	var expTable = document.getElementById("expTbl");
	var expRowCnt = expTable.rows.length;
	var expNewRow = expTable.insertRow(-1);
	var expRowNum = expNewRow.insertCell(0);
	var expRowDel = expNewRow.insertCell(1);
	var expSelect = expNewRow.insertCell(2);
	var expAmount = expNewRow.insertCell(3);
	var expInfo   = expNewRow.insertCell(4);
	var expFiller = expNewRow.insertCell(5);

	var expRows = expTable.rows.length;
	expNewRow.setAttribute("id", "expRow"+expRowCnt, 0);
	expNewRow.style.margin = "0 0 0 0";
	expNewRow.style.border = "0";

	expRowNum.innerHTML = "<input type='hidden' id='expRow" + expRowCnt + "' name='expRow" + expRowCnt + 
						  "' size='5' maxlength='5' value='row" + expRowCnt + "'>";

	expURL = document.getElementById("PIAPURL").value;
	expRowDel.innerHTML = "<img src='" + expURL + "/MyImages/Icons/DeleteRedSM.png' onclick='expDel(" + 
						  expRowCnt + "); return(false);'>";
	expSelect.innerHTML = "<select size='1' id='expSel" + expRowCnt + "' name='expSel" + expRowCnt + 
						  "' required>" + expSelText + "</select></td>";
	expAmount.innerHTML = "<input type='number' id='expAmt" + expRowCnt + "' name='expAmt" + expRowCnt + 
					"'style='text-align:right;' size='10' maxlength='10' placeholder='x,xxx.xx' step='0.01' min='0'>"
	expInfo.innerHTML = "<input type='text' id='expInfo" + expRowCnt + "' name='expInfo" + expRowCnt + 
						"' size='50' maxlength='250'>";
	expFiller.innerHTML = " ";

	expRows = parseInt(document.getElementById("EXPCNT").value);
	expRows = expRows + 1;
	document.getElementById("EXPCNT").value = expRows.toString();
	return false;
}

function expDel(expRow) {
	var expRowId = "expRow" + expRow;
	var expTable = document.getElementById("expTbl");
	var expTblRow = document.getElementById(expRowId);
	expTblRow.parentNode.removeChild(expTblRow);
	var expRowCnt = expTable.rows.length;
	if (expRowCnt == 1) {
		document.getElementById("expTbl").style.display = "none";
	}
	return(false);
}

</script>