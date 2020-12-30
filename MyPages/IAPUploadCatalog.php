<?php

function IAP_UpldCat_Initial() {
	echo "<form enctype='multipart/form-data' action='?mod=UC&step=1' method='post'>";

	$h = IAP_Do_Help(4, 125, 1); 	// level 4 (Always display), page 103, section 1
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";
	}

	echo "<table style='width:100%'>";
	echo "<tr><td width='5%'></td><td width='95%'>Select the local file to begin the upload then click Upload.</td></tr>";
	echo "<tr><td width='5%'></td><td width='95%'><span class=iapFormLabel>File name to import:</span></td.</tr>";
	echo "<tr><td width='5%'></td><td width='95%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input size='50' type='file' name='filename'></td.</tr>";
	echo "<tr><td width='5%'></td><td width='95%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit' value='Upload'></td></tr>";
	echo "</table></form>";
}

function IAP_Upload_File($iapSave) {

	if ($_FILES['filename']['error'] != 0){
		echo "<span class=iapError>An error was detected - try again!</span><br><br>";
		IAP_Remove_Savearea("IAP125UI");
		return(-1);
	}
	if ($_FILES['filename']['type'] != "text/csv"
	and $_FILES['filename']['type'] != "text/comma-separated-values"
	and $_FILES['filename']['type'] != "application/vnd.ms-excel"
	and $_FILES['filename']['type'] != "text/plain") {
		echo "<span class=iapError>File must be saved as a CSV file!</span><br><br>";
		IAP_Remove_Savearea("IAP125UI");
		return(-2);
	}

// Copy file to TempFiles dir
	if (!copy($_FILES['filename']['tmp_name'], "TempFiles/CatalogUpload.csv")) {
	    	echo "<span class=iapError>Copy of tempfile failed...<br><br>";
		IAP_Remove_Savearea("IAP125UI");
		return(-2);
	}

//Get Column Names
	$handle = fopen("TempFiles/CatalogUpload.csv", "r");
	$iapColNames = fgetcsv($handle, 1000, ",", '"');

	fclose($handle);
	if ($iapColNames === FALSE) {
		echo "<span class=iapError>The CSV file is empty.</span><br><br>";
		IAP_Remove_Savearea("IAP125UI");
		return(-3);
	}

	$iapCatCols = $iapSave['catcols'];

	echo "<form enctype='multipart/form-data' action='?mod=UC&step=2' method='post'>";
	$h = IAP_Do_Help(4, 125, 2); // level 4 (Always display), page 125, section 2
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";	
	} else {
		echo "<table style='width:100%'><tr><td width='5%'></td><td width='95%'></td></tr>";
		echo "<tr><td width='5%'></td><td width='95%'>";
		echo "<strong>Step 1 Complete - File Uploaded. You are ready for step 2.</strong></td></tr>";
		echo "</table>";
	}

	echo "<table><tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
	echo "<tr><td width='5%'></td><td width='16%' class='iapFormTitle'>Map</td><td width='30%' class='iapFormTitle'>Database Column</td><td width='30%' class='iapFormTitle'>Your CSV Column<td width='19%'></td></tr>";
	echo "<tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
	$i = 0;
	$iapStop = FALSE;
	while($iapStop === FALSE) {
		echo "<tr><td width='5%'></td><td width='16%'>";
		if ($i < count($iapCatCols)) {
			echo "<input type='text' name='colno".strval($i)."' size='3' maxlength='3' class='iapFormTitle'";
			if ($i == 0) {
				echo "  autofocus";
			}
			echo ">";
		}
		echo "</td><td width='30%'>";
		if ($i < count($iapCatCols)) {
			echo $iapCatCols[$i];
		}
		echo "</td><td width='30%'>";
		if ($i < count($iapColNames)) {
			echo strval($i + 1)." - ".$iapColNames[$i];
		}
		echo "<td width='19%'></td></tr>";
		$i = $i + 1;
		if ($i >= count($iapCatCols)
		and $i >= count($iapColNames)) {
			$iapStop = TRUE;
		}
	}
	echo "<tr><td colspan='4'><input class=iapButton style='text-align:center;' type='submit' name='submit' value=' Map Columns '></td></tr>";
	echo "</table></form><br><br>";

	$iapSave['step'] = "2";
	$iapSave['cols'] = $iapColNames;
	$iapRet = IAP_Update_Savearea("IAP125UI", $iapSave);
	if ($iapRet < 0) {
	    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
	    return(-1);
	}
}

function IAP_Do_Mapping($iapSave) {

	$iapCatCols = $iapSave['catcols'];
	$iapColNames = $iapSave['cols'];
	if (isset($iapSave['map'])) {
		$iapMapping = $iapSave['map'];
	} else {
		$iapMapping = array();		
	}
	$iapError = array();
	$iapNextStep = 3;
	$i = -1;
	while($i < count($iapCatCols)) {
		$i = $i + 1;
		$iapError[$i] = "N";
		$iapCN = "colno".strval($i);
		$iapMapping[$i] = $_REQUEST[$iapCN];
		if ($iapMapping[$i] == "") {
			continue;
		}
		if (!(is_numeric($iapMapping[$i]))) {
			$iapSubCols = explode("+", $iapMapping[$i]);
			foreach($iapSubCols as $iapSC) {
				if (!(is_numeric($iapSC))
				or $iapSC > count($iapColNames)) {
					$iapError[$i] = "Y";
					$iapNextStep = "2";
				} elseif ($iapSC > count($iapColNames)) {
					$iapError[$i] = "Y";
					$iapNextStep = "2";
				}
			}
		} elseif ($iapMapping[$i] > count($iapColNames)) {
			$iapError[$i] = "Y";
			$iapNextStep = "2";
		}
	}
	echo "<form enctype='multipart/form-data' action='?mod=UI&step=".$iapNextStep."' method='post'>";
	$h = IAP_Do_Help(4, 125, 3); // level 4 (Always display), page 125, section 3
	if ($h != "") {
		echo "<table style='width:100%'><tr><td width='1%'></td><td width='80%'></td><td width='19%'></td></tr>";
		echo "<tr><td width='1%'></td><td width='80%'>";
		echo $h;
		echo "</td><td width='19%'></td></tr>";
		echo "</table>";	
	} else {
		echo "<table style='width:100%'><tr><tr><td width='10%'></td><td width='95%'></td></tr>";
		echo "<tr><td width='5%'></td><td width='95%'>";
		echo "<strong>Step 2 Complete - Fields Mapped. You are ready for step 3.</strong></td></tr>";
		echo "</table>";
	}

	echo "<table><tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
	echo "<tr><td width='5%'></td><td width='16%' class='iapFormTitle'>Map</td><td width='30%' class='iapFormTitle'>Database Column</td><td width='30%' class='iapFormTitle'>Your CSV Column<td width='19%'></td></tr>";
	echo "<tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
	$i = 0;
	$iapStop = FALSE;
	while($iapStop === FALSE) {
		echo "<tr><td width='5%'></td><td width='16%'>";
		if ($iapError[$i] == "Y") {
			echo "<span class=iapError>*</span>";
		}
		if ($iapNextStep == "2") {
			echo "<input type='text' name='colno".strval($i)."' size='3' maxlength='3' class='iapFormTitle' value='".$iapMapping[$i]."'>";
		} else {
			echo $iapMapping[$i];
		}
		echo "</td><td width='30%'>";
		If ($i < count($iapCatCols)) {
			echo $iapCatCols[$i];
		}
		echo "</td><td width='30%'>";
		If ($i < count($iapColNames)) {
			echo strval($i + 1)." - ".$iapColNames[$i];
		}
		echo "<td width='19%'></td></tr>";
		$i = $i + 1;
		if ($i >= count($iapCatCols)
		and $i >= count($iapColNames)) {
			$iapStop = TRUE;
		}
	}
	if ($iapNextStep == "3") {
		echo "<tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
		echo "<tr><td width='5%'></td><td colspan='3'><input class='iapFormField' name='clrcat' type='checkbox'> Check this box to delete all existing items from the Catalog.</td></tr>";
	} 
	echo "<tr><td width='5%'></td><td width='16%'></td><td width='30%'></td><td width='30%'><td width='19%'></td></tr>";
	echo "<tr><td colspan='4'><input type='submit' style='text-align:center;' class=iapButton name='submit' value='Import Data'></td></tr>";

	echo "</table></form><br><br>";

	$iapSave['step'] = $iapNextStep;
	$iapSave['map'] = $iapMapping;
	$iapRet = IAP_Update_Savearea("IAP125UI", $iapSave);
	if ($iapRet < 0) {
		 echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		IAP_Remove_Savearea("IAP125UI");
		return(-1);
	}
}

function IAP_Import_Data($iapSave) {

	$iapJrnl = IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapJrnl[0];
	$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
	$iapJrnl['jrnl_date'] = date("Y-m-d");
	$iapJrnl['jrnl_description'] = "Import of Catalog Started";
	$iapJrnl['jrnl_type'] = "MI";
	$iapJrnl['jrnl_amount'] = 0;
	$iapJrnl['jrnl_tax'] = 0;
	$iapJrnl['jrnl_shipping'] = 0;
	$iapJrnl['jrnl_mileage'] = 0;
	$iapJrnl['jrnl_expenses'] = 0;
	$iapJrnl['jrnl_exp_explain'] = "";
	$iapJrnl['jrnl_vendor'] = "";
	$iapJrnl['jrnl_comment'] = "Beginning import of an external catalog at ".date("m/d/Y h:m");
	$iapJrnl['jrnl_detail_key'] = "";
	$iapJrnl['jrnl_changed'] = date("Y-m-d");
	$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	$iapCatFlds = $iapSave['realcols'];
	$iapCatCols = $iapSave['catcols'];
	$iapColNames = $iapSave['cols'];
	$iapMapping = $iapSave['map'];
	$iapDefaults = $iapSave['realdef'];

	if (isset($_REQUEST['clrcat'])== "on") {

		echo "<p>Removing current items.</p>\n";
		wp_ob_end_flush_all();
		flush();

		IAP_Clear_Catalog();
	}

	if (!(set_time_limit(800))) {
		echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	$handle = fopen("TempFiles/CatalogUpload.csv", "r");
	$x = fgetcsv($handle, 1000, ","); 	// bypass column names
	$iapRecsAdded = 0;
	while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$iapCatalog = IAP_Build_New_Row(array("table" => "ctlg"));
		$iapCatalog = $iapCatalog[0];

		$iapInventory = IAP_Build_New_Row(array("table" => "inv"));
		$iapInventory = $iapInventory[0];

		$iapPrices = IAP_Build_New_Row(array("table" => "prc"));
		$iapPrices = $iapPrices[0];

		$iCat = 0;
		while($iCat < count($iapCatFlds)) {
			$iapIndex = $iapCatFlds[$iCat];
			if (!(empty($iapMapping[$iCat]))) {
				$iapMap = $iapMapping[$iCat];
				if (!(is_numeric($iapMap))) {
					$iapFlds = explode("+", $iapMap);
				} else {
					$iapFlds = array($iapMap);
				}

				$iapCurrentFld = "";
				foreach($iapFlds as $iapFld) {
					if (empty($data[$iapFld - 1])) {
						$data[$iapFld - 1] = $iapDefaults[$iCat];
					}
					if ($iapDefaults[$iCat] = "0"
					or $iapDefaults[$iCat] = "0.00") {
						if (substr(trim($data[$iapFld - 1]), 0, 1) == "-") {
							$data[$iapFld - 1] = "0.0";
						}
						if (substr(trim($data[$iapFld - 1]), -1, 1) == "-") {
							$data[$iapFld - 1] = "0.0";
						}
						if (substr(trim($data[$iapFld - 1]), 0, 1) == "(") {
							$data[$iapFld - 1] = "0.0";
						}
					}
					$iapValue = str_replace('"', ' Inch', $data[$iapFld - 1]);
					$iapValue = str_replace('&', ' and', $iapValue);

					$iapCurrentFld = $iapCurrentFld.$iapValue." ";
				}
				$iapCurrentFld = str_replace("%", "", $iapCurrentFld);
				$iapCurrentFld = trim($iapCurrentFld);
				if (substr($iapIndex, 0, 3) == "cat") {
					$iapCatalog[$iapIndex] = $iapCurrentFld;
				} elseif (substr($iapIndex, 0, 3) == "inv") {
					$iapInventory[$iapIndex] = $iapCurrentFld;
				} else {
					$iapPrices[$iapIndex] = $iapCurrentFld;
				}
			}
			$iCat = $iCat + 1;
		}
		$iapCat2 = IAP_Get_Catalog($iapCatalog['cat_item_code']);
		if ($iapCat2 < 0) {
			echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot add the Catalog record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
			exit;
		}
		if ($iapCat2['status'] != "NEW") {
			echo "Item ".$iapCatalog['cat_item_code']." already exists in the catalog.<br>Rerun and check the Delete Existing box to reload the items.<br>";
			wp_ob_end_flush_all();
			flush();
			continue;
		}
		$iapCatalog['cat_company'] = $_REQUEST['CoId'];
		$iapCatalog['cat_changed'] = date("Y-m-d");
		$iapCatalog['cat_changed_by'] = $_REQUEST['IAPUID'];
		$iapRecsAdded = $iapRecsAdded + 1;
		$iapRet = IAP_Update_Data($iapCatalog, "ctlg");
		if ($iapRet < 0) {
			echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot add the Catalog record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
			exit;
		}

		$iapInventory['inv_company'] = $_REQUEST['CoId'];
		$iapInventory['inv_item_code'] = $iapCatalog['cat_item_code'];
		$iapInventory['inv_changed'] = date("Y-m-d");
		$iapInventory['invchanged_by'] = $_REQUEST['IAPUID'];
		$iapRet = IAP_Update_Data($iapInventory, "inv");
		if ($iapRet < 0) {
			echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot add the Inventory record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
			exit;
		}

		$iapCatName = str_replace('"', ' Inch', $iapPrices['prc_category']);
		$iapCatName = str_replace('&', ' and ', $iapCatName);
		$iapCode = IAP_Get_Code_By_Name("cat", $iapCatName);
		if ($iapCode < 0) {
			echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot retrieve the Code record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
			exit;
		}
		if ($iapCode == NULL) {
			$iapPrices['prc_cat_code'] = "";
		} else {
			$iapPrices['prc_cat_code'] = $iapCode['code_code'];
		}

		$iapDoOldPrice = "N";
		if (!(empty($iapPrices['prc_prev_cost']))
		and ($iapPrices['prc_prev_cost'] != $iapPrices['prc_cost']
		 or  $iapPrices['prc_prev_units'] != $iapPrices['prc_units']
		 or  $iapPrices['prc_prev_cost_unit'] != $iapPrices['prc_cost_unit']
		 or  $iapPrices['prc_prev_price'] != $iapPrices['prc_price'])) {
			$iapDoOldPrice = "Y";

			$iapP = IAP_Build_New_Row(array("table" => "prc"));
			$iapPrc = $iapP[0];

			$iapPrc['prc_company'] = $_REQUEST['CoId'];
			$iapPrc['prc_item_code'] = $iapCatalog['cat_item_code'];
			$iapPrc['prc_effective'] = "2010-01-01";
			$iapPrc['prc_effective_until'] = date("Y-m-d", strtotime($iapPrices['prc_effective']." - 1 day"));

			$iapPrc['prc_cost'] = $iapPrices['prc_prev_cost'];
			$iapPrc['prc_units'] = $iapPrices['prc_prev_units'];
			$iapPrc['prc_cost_unit'] = $iapPrices['prc_prev_cost_unit'];
			$iapPrc['prc_price'] = $iapPrices['prc_prev_price'];
			$iapPrc['prc_category'] = $iapPrices['prc_category'];
			$iapPrc['prc_cat_code'] = $iapPrices['prc_cat_code'];

			$iapPrc['prc_changed'] = date("Y-m-d");
			$iapPrc['prc_changed_by'] = $_REQUEST['IAPUID'];

			$iapPrc['prc_prev_cost'] = 0;
			$iapPrc['prc_prev_units'] = 0;
			$iapPrc['prc_prev_cost_unit'] = 0;
			$iapPrc['prc_prev_price'] = 0;
			$iapPrc['prc_prev_cat_code'] = "";

		        $iapRet = IAP_Update_Data($iapPrc, "prc");
		        if ($iapRet < 0) {
		            echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot add the Prices record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
		            exit;
		        }
		}

		$iapP = IAP_Build_New_Row(array("table" => "prc"));
		$iapPrc2 = $iapP[0];

		$iapPrc2['prc_company'] = $_REQUEST['CoId'];
		$iapPrc2['prc_item_code'] = $iapCatalog['cat_item_code'];
		if ($iapDoOldPrice == "N") {
			$iapPrc2['prc_effective'] = "2010-01-01";
		} else {
			$iapPrc2['prc_effective'] = date("Y-m-d", strtotime($iapPrices['prc_effective']));
		}
		$iapPrc2['prc_effective_until'] = "2099-12-31";

		$iapPrc2['prc_cost'] = $iapPrices['prc_cost'];
		$iapPrc2['prc_units'] = $iapPrices['prc_units'];
		$iapPrc2['prc_cost_unit'] = $iapPrices['prc_cost_unit'];
		$iapPrc2['prc_price'] = $iapPrices['prc_price'];
		$iapPrc2['prc_category'] = $iapPrices['prc_category'];
		$iapPrc2['prc_cat_code'] = $iapPrices['prc_cat_code'];

		$iapPrc2['prc_changed'] = date("Y-m-d");
		$iapPrc2['prc_changed_by'] = $_REQUEST['IAPUID'];

		$iapPrc2['prc_prev_cost'] = $iapPrices['prc_prev_cost'];
		$iapPrc2['prc_prev_units'] = $iapPrices['prc_prev_units'];
		$iapPrc2['prc_prev_cost_unit'] = $iapPrices['prc_prev_cost_unit'];
		$iapPrc2['prc_prev_price'] = $iapPrices['prc_prev_price'];
		$iapPrc2['prc_prev_cat_code'] = $iapPrices['prc_cat_code'];

	        $iapRet = IAP_Update_Data($iapPrc2, "prc");
	        if ($iapRet < 0) {
	            echo "<font color='red'><strong>IAP INTERNAL ERROR Cannot add the Prices record due to database error [FATAL]<br />Please notify Support and provide this reference of /" . basename(__FILE__) . "/" . __LINE__ . "</font><br />";
	            exit;
	        }
	}
	fclose($handle);
	echo "Import Complete! ".strval($iapRecsAdded)." items added to the catalog.<br>";
	wp_ob_end_flush_all();
	flush();

	$iapJrnl = IAP_Build_New_Row(array("table" => "jrnl"));
	$iapJrnl = $iapJrnl[0];
	$iapJrnl['jrnl_company'] = $_REQUEST['CoId'];
	$iapJrnl['jrnl_date'] = date("Y-m-d");
	$iapJrnl['jrnl_description'] = "Import of Catalog Complete";
	$iapJrnl['jrnl_type'] = "MI";
	$iapJrnl['jrnl_amount'] = 0;
	$iapJrnl['jrnl_tax'] = 0;
	$iapJrnl['jrnl_shipping'] = 0;
	$iapJrnl['jrnl_mileage'] = 0;
	$iapJrnl['jrnl_expenses'] = 0;
	$iapJrnl['jrnl_exp_explain'] = "";
	$iapJrnl['jrnl_vendor'] = "";
	$iapJrnl['jrnl_comment'] = "Import of an external catalog ended at ".date("m/d/Y h:m")." with ".strval($iapRecsAdded)." items added to the catalog.";
	$iapJrnl['jrnl_detail_key'] = "";
	$iapJrnl['jrnl_changed'] = date("Y-m-d");
	$iapJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
	$iapRet = IAP_Update_Data($iapJrnl, "jrnl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		IAP_Remove_Savearea("IAP125UI");
		exit;
	}
	return;
}


///// Program Start //////

if ($_REQUEST['page_id'] != "125") {
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In IAP ";
    if (isset($_REQUEST['applinfo'])) {
        echo " with applinfo of ".$_REQUEST['applinfo'];
    }
    echo ".<br />";
}

require_once(ABSPATH."IAPServices.php");
$ret = IAP_Program_Start("125", "N");

$iapSave = IAP_Get_Savearea("IAP125UI");
if ($iapSave < 0) {
    echo "<font color='red'><strong>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this refrence of /".basename(__FILE__)."/".__LINE__."</font><br />";
    return;
}
$iapStep = $iapSave['step'];
$iapReadOnly = IAP_Format_Heading("Catalog of Items Import");

if ($_REQUEST['UserData']['Mode'] == "expired") {
	echo "You cannot import more items because your license has expired.";
	return;
}

switch($iapStep) {
	case "1":
		$iapRet = IAP_Upload_File($iapSave);
		if ($iapRet < 0) {
			return;
		}
		break;
	case "2":
		IAP_Do_Mapping($iapSave);
		break;
	case "3":
		IAP_Import_Data($iapSave);
		$iapRet = IAP_Remove_Savearea("IAP125UI");
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}
		$_REQUEST['mod'] = "HP";
		require_once("MyPages/IAPAppHome.php"); // go back to App Home
		return;
		break;
	default:	// initial entry
		$iapRet = IAP_Remove_Savearea("IAP125UI");
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot remove savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}

		$iapColSQL = "SHOW FULL COLUMNS FROM iap_catalog";
		$iapRet = IAPProcessMySQL("select", $iapColSQL);
		if ($iapRet['retcode'] < 0) {
		    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve catalog columns. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		if ($iapRet['numrows'] == 0){
		    echo "<span class=iapError>iap INTERNAL ERROR: No catalog columns found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		$iapCCols = (array) $iapRet['data'];
		$iapImportCols = array();
		$iapRealCols = array();
		$iapRealDef = array();
		foreach($iapCCols as $c) {
			if ($c['Comment'] != "-<(NOEXPORT)>-") {
				$iapImportCols[] = $c['Comment'];
				$iapRealCols[] = $c['Field'];
				$iapRealDef[] = $c['Default'];
			}
		}

// Get Inventory Columns
		$iapColSQL = "SHOW FULL COLUMNS FROM iap_inventory";
		$iapRet = IAPProcessMySQL("select", $iapColSQL);
		if ($iapRet['retcode'] < 0) {
		    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve price columns. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		if ($iapRet['numrows'] == 0){
		    echo "<span class=iapError>iap INTERNAL ERROR: No price columns found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		$iapICols = (array) $iapRet['data'];
		foreach($iapICols as $i) {
			if ($i['Comment'] != "-<(NOEXPORT)>-") {
				$iapImportCols[] = $i['Comment'];
				$iapRealCols[] = $i['Field'];
				$iapRealDef[] = $i['Default'];
			}
		}

// Get Price Columns
		$iapColSQL = "SHOW FULL COLUMNS FROM iap_prices";
		$iapRet = IAPProcessMySQL("select", $iapColSQL);
		if ($iapRet['retcode'] < 0) {
		    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve price columns. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		if ($iapRet['numrows'] == 0){
		    echo "<span class=iapError>iap INTERNAL ERROR: No price columns found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
		    return;
		}
		$iapPCols = (array) $iapRet['data'];
		foreach($iapPCols as $p) {
			if ($p['Comment'] != "-<(NOEXPORT)>-") {
				$iapImportCols[] = $p['Comment'];
				$iapRealCols[] = $p['Field'];
				$iapRealDef[] = $p['Default'];
			}
		}

		$iapSave = array("step" => "1", "catcols" => $iapImportCols, "realcols" => $iapRealCols, "realdef" => $iapRealDef,  "cols" => NULL);
		$iapRet = IAP_Create_Savearea("IAP125UI", $iapSave);
		if ($iapRet < 0) {
	    	echo "<span class=iapError>IAP INTERNAL ERROR: Cannot create savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		   	return(-1);
		}
		IAP_UpldCat_Initial();
		break;				
}

?>