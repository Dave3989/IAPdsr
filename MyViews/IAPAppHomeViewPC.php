<?php

function AppHomeInit() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$h = IAP_Do_Help(3, 54, 3); // level 3, page 54, section 3
	if ($h != "") {
		echo "<table style='width:100%;'><tr><td width='100%;'><br></td></tr>";
		echo "<tr><td width='100%;'>";
		echo $h;
		echo "</td></tr></table>";
	}
	return;
}

function AppHomeEvents($IAPSE, $IAPME) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if (!empty($IAPSE)) {
		echo "<table style='width:100%;'>";
		echo "<tr><td colspan='2'><span class='iapImportant'>System Events That May Affect You During The Next Month</span> <span style='font-size:85%; color:brown;'>(click on the event for details):</span>";

		echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 54, 4); // level 1, page 54, section 4

		echo "</td></tr>";
		echo $IAPSE;
		echo "</table>";
	}

	if (!empty($IAPME)) {
		echo "<table style='width:100%;'>";
		echo "<tr><td colspan='2'><span class='iapImportant'>Your Events During The Next Month:</span><span style='font-size:85%; color:brown;'>&nbsp;&nbsp;&nbsp;(click on the event for details):</span>";

		echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 54, 1); // level 1, page 54, section 1

		echo "</td></tr>";
		echo $IAPME;
		echo "</table>";
	}
}

function AppHomeFollowUps($FollowUps) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if (!empty($FollowUps)) {
		$FDo = "N";
		foreach($FollowUps as $iapF) {
			if ($iapF['cust_followup_consultant'] == "Y") {
				$FDo = "Y";
			}
		}
		$first = "Y";
		if ($FDo == "Y") {
			echo "<table style='width:100%;'>";
			foreach($FollowUps as $iapF) {
				$iapP = "";
				if (!empty($iapF['cust_home_phone'])) {
					$iapP = $iapF['cust_home_phone'];
				}
				if (!empty($iapF['cust_cell_phone'])) {
					$iapP = $iapF['cust_cell_phone'];
				}
				if ($iapF['cust_followup_consultant'] == "Y") {
					if ($first == "Y") {
						echo "<tr><td colspan='7'><span class='iapImportant'>The following people have been marked as possible consultants:</span>";
						echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 54, 2); // level 1, page 54, section 2
						echo "</td></tr>";
						$first = "N";				
					}
					echo "<tr><td style='width:5%;'></td>";
					echo "<td style='width:30%;'><a href=?page_id=134&action=selected&custno=".strval($iapF['cust_no']).">".$iapF['cust_name']."</a></td>";
					echo "<td style='width:5%;'></td>";
					echo "<td style='width:15%;'>".$iapP."</td>";
					echo "<td style='width:5%;'></td>";
					echo "<td style='width:15%;'>".$iapF['cust_email']."</td>";
					echo "<td style='width:25%;'></tr>";
				}
			}
			echo "</table>";
		}
		$FDo = "N";
		foreach($FollowUps as $iapF) {
			if ($iapF['cust_followup_party'] == "Y") {
				$FDo = "Y";
			}
		}
		$first = "Y";
		if ($FDo == "Y") {
			echo "<table style='width:100%;'>";
			foreach($FollowUps as $iapF) {
				$iapP = "";
				if (!empty($iapF['cust_home_phone'])) {
					$iapP = $iapF['cust_home_phone'];
				}
				if (!empty($iapF['cust_cell_phone'])) {
					$iapP = $iapF['cust_cell_phone'];
				}
				if ($iapF['cust_followup_party'] == "Y") {
					if ($first == "Y") {
						echo "<tr><td colspan='7'><span class='iapImportant'>The following people may be interested in hosting a party:</span>";
						echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 54, 3); // level 1, page 54, section 3
						echo "</td></tr>";
						$first = "N";				
					}
					echo "<tr><td style='width:5%;'></td>";
					echo "<td style='width:30%;'><a href=?page_id=134&action=selected&custno=".strval($iapF['cust_no']).">".$iapF['cust_name']."</a></td>";
					echo "<td style='width:5%;'></td>";
					echo "<td style='width:15%;'>".$iapP."</td>";
					echo "<td style='width:5%;'></td>";
					echo "<td style='width:15%;'>".$iapF['cust_email']."</td>";
					echo "<td style='width:25%;'></tr>";
				}
			}
			echo "</table>";
		}
	}
}

function AppHomeStockLevel($IAPItems) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if (!empty($IAPItems)) {
		echo "<table style='width:100%;'>";
		echo "<tr><td colspan='9'><span class='iapImportant iapWarning'>The following items have fallen below their minimum on-hand balance";
		echo "&nbsp;&nbsp;&nbsp;".IAP_Do_Help(1, 54, 5); // level 1, page 54, section 5
		echo "</td></tr>";
		echo "<tr><th style='width:5%;'></th><th style='width:13%;'>Item Code</th><th style='width:2%;'></th><th style='width:43%;'>Description</th><th style='width:2%;'></th><th style='width:13%;text-align:right;'>On Hand</th><th style='width:2%;'></th><th style='width:20%;text-align:right;'>Min. On Hand</th><th style='width:5%;'></th></tr>";
		foreach($IAPItems as $i) {
			echo "<tr><td style='width:5%;'></td><td style='width:13%;'><a href=?page_id=141&action=selected&item=".$i[cat_item_code].">".$i[cat_item_code]."</a></td>";
			echo "<td style='width:2%;'></td><td style='width:40%;'>".$i[cat_description]."</td>";
			echo "<td style='width:2%;'></td><td style='width:13%;text-align:right;'>".number_format((float) $i['inv_on_hand'], 0, '.', ',')."</td>";
			echo "<td style='width:2%;'></td><td style='width:20%;text-align:right;'>".number_format((float) $i['inv_min_onhand'], 0, '.', ',')."</td>";
			echo "<td style='width:8%;'></td></tr>";
		}
	}
}

function AppHomeFinal($iapCatInit, $iapCustInit) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['iap1st'] == "Y") {
		$h = IAP_Do_Help(3, 54, 7); // level 3, page 54, section 8
		if ($h != "") {
			echo "<table style='width:100%;'>";
			echo "<tr><td width='100%;'><br></td></tr>";
			echo "<tr><td width='100%;'>";
			echo $h;
			echo "</td></tr>";
			echo "</table>";
		}
	} else {
		if ($iapCustInit == "N") {
			$h = IAP_Do_Help(3, 54, 8); // level 3, page 54, section 8
			if ($h != "") {
				echo "<table style='width:100%;'>";
				echo "<tr><td width='100%;'><br></td></tr>";
				echo "<tr><td width='100%;'>";
				echo $h;
				echo "</td></tr>";
				echo "</table>";
			}
		}
		if ($iapCatInit == "N") {
			$h = IAP_Do_Help(3, 54, 9); // level 3, page 54, section 9
			if ($h != "") {
				echo "<table style='width:100%;'>";
				echo "<tr><td width='100%;'><br></td></tr>";
				echo "<tr><td width='100%;'>";
				echo $h;
				echo "</td></tr>";
				echo "</table>";
			}
		}
	}
	return;
}


function AppHomeMenu() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$h = IAP_Do_Help(3, 54, 2); // level 3, page 54, section 2
	if ($h != "") {
		echo "<table style='width:100%;'>";
		echo "<tr><td width='100%;'><br></td></tr>";
		echo "<tr><td width='100%;'>";
		echo $h;
		echo "</td></tr>";
		echo "</table>";
	}
	return;
}

echo "<input type='hidden' name='LHCA' id='LHCA' value='".$_REQUEST['CoId']."'>";
echo "<input type='hidden' name='IAPMODE' id='IAPMODE' value='".$_REQUEST['UserData']['Mode']."'>";
echo "<input type='hidden' name='IAPDL' id='IAPDL' value=''>";

?>

<script type="text/javascript">

function appHomeOpenEvent(eid, uid) {

//	var eleft = (window.screen.width/2) - 250 - 10;
//	eventWindow=window.open(eurl,'_blank','width=500,height=500,left='+eleft+',top=110,location=no,menubar=no,resizable=no,titlebar=no,toolbar=no');
	var eurl = '<?php echo $_REQUEST['IAPUrl']; ?>/MyPages/IAPShowEvent.php?eid='+eid+'&uid='+uid;
	eventWindow=window.open(eurl,'_blank');
	return false;
}

<?php

require_once($_REQUEST['IAPPath']."MyJS/NonJSMin/JSAppHome.js");
// require_once($_REQUEST['IAPPath']."MyJS/JSAppHome.min.js");

?>

</script>
