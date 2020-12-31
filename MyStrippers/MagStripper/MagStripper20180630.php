<?php

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['sec_use_application'] = "Y";
require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

// set global constants.
$GLOBALS['$maSupplierCode'] = 1;
$GLOBALS['$maSupplierName'] = "Magnabilities LLC";

// set global counters.
$GLOBALS['$maFilesProcessed'] = 0;
$GLOBALS['$maCatsProcessed'] = 0;
$GLOBALS['$maItemsProcessed'] = 0;
$GLOBALS['$maNewItems'] = 0;
$GLOBALS['$maNewPriceCost'] = 0;
$GLOBALS['$maNewPricePrice'] = 0;
$GLOBALS['$maPendingPriceChgs'] = 0;
$GLOBALS['$maUpdPriceCost'] = 0;
$GLOBALS['$maUpdPricePrice'] = 0;
$GLOBALS['$maInactiveItems'] = 0;
$GLOBALS['$maNewItemCodes'] = array();

if ($_REQUEST['debugme'] == "Y") {
	echo "--- HTTP_HOST is ".$_SERVER['HTTP_HOST']."<br>";
}

switch(strtolower($_SERVER['HTTP_HOST'])) {
	case "localhost:8080":
		$StripDir = "D:/Magnabilities/ProductDownload/SavedScreens/";
//		$StripDir = "C:/Family/Magnabitities/ProductDownload/";
		break;
	case "litehausconsulting.info":
		$StripDir = ABSPATH."MyStrippers/MagStripper/ProductDownload/";
		break;
	case "itsapartydsr.com":
		$StripDir = ABSPATH."MyStrippers/MagStripper/ProductDownload/";
		break;
	default:
		echo "<br><br><span class=iapError>Attempt to run from an unauthorized site!</span>";
		return;
}

if ($_REQUEST['debugme'] == "Y") {
	echo "--- Directory is ".$StripDir."<br>";
}

$FSJ = IAP_Build_New_Row(array("table" => "jrnl"));
$FSJrnl = $FSJ[0]; 
$FSJrnl['jrnl_company'] = $_REQUEST['CoId'];
$FSJrnl['jrnl_date'] = date("Y-m-d");
$FSJrnl['jrnl_description'] = "Magnabilities Item Updater (MagStripper) starting.";
$FSJrnl['jrnl_type'] = "MI";
$FSJrnl['jrnl_item_code'] = "";
$FSJrnl['jrnl_units'] = 0;
$FSJrnl['jrnl_amount'] = 0;
$FSJrnl['jrnl_tax'] = 0;
$FSJrnl['jrnl_shipping'] = 0;
$FSJrnl['jrnl_mileage'] = 0;
$FSJrnl['jrnl_expenses'] = 0;
$FSJrnl['jrnl_exp_explain'] = 0;
$FSJrnl['jrnl_vendor'] = 0;
$FSJrnl['jrnl_units'] = 0;
$FSJrnl['jrnl_price'] = 0;
$FSJrnl['jrnl_cat_code'] = "";
$FSJrnl['jrnl_comment'] = "Magnabilities Item Updater (MagStripper) starting.";
$FSJrnl['jrnl_detail_key'] = "";
$FSJrnl['jrnl_changed'] = date("Y-m-d");
$FSJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
$FSRet = IAP_Update_Data($FSJrnl, "jrnl");
if ($FSRet < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

$StripFiles = GetFiles($StripDir);

if ($_REQUEST['debugme'] == "Y") {
	echo "--- Files are ".$StripDir."<br>";
	echo "<pre>";
	print_r($StripFiles);
	echo "</pre>";
}

$once = "Y";
foreach($StripFiles as $SF) {
	if ($SF == "."
	or  $SF == "..") {
		continue;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "--- processing ".$SF."<br>";
	}

	if (substr($SF, -4) != "html"
	and substr($SF, -3) != "htm") {
		continue;
	}

	if (!(set_time_limit(800))) {
		echo "<span style=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	$GLOBALS['$maFilesProcessed'] = $GLOBALS['$maFilesProcessed'] + 1;
	$SFFile = file_get_contents($StripDir.$SF);
	$SFArray = file($StripDir.$SF);

	$SFNoHdrs = StripHdr($SFFile);
	$SFNoHdFtr = StripFooter($SFNoHdrs);
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$SFFileName = basename($SF, ".html");
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	echo "+++ ------------------------------------------------------------------------------------------- +++<br>";
	echo "+++ Processing ".$GLOBALS['$maSupplierName']." file ".$SFFileName." ------------------------------- +++<br>";
	echo "+++ ------------------------------------------------------------------------------------------- +++<br>";

	$SFMore = "Y";
	while($SFMore == "Y") {
		$SFGroup = ExtractGroup($SFNoHdFtr);
		if ($SFGroup == NULL) {
			$SFMore = "N";
			break;
		}
		$SFGrpCat = GetCategory($SFGroup);
		if (strtolower($SFGrpCat) == "convention") {
			iapDisplayMsg("------ Ignoring category ".$SFGrpCat);
		} else {
			iapDisplayMsg("------ Processing category ".$SFGrpCat);
			$GLOBALS['$maCatsProcessed'] = $GLOBALS['$maCatsProcessed'] + 1;

			$SBFGrpItems = GetItems($SFFileName, $SFGroup);		
		}
		$NextGrp = strlen($SFGroup) + 1;
		$SFNoHdFtr = substr($SFNoHdFtr, $NextGrp);		
		$SFGroup = $NextGrp;
		echo "<br>";
	}
}

// Make items inactive if not in current download

$iapPass['table'] = "supcat";
$iapPass['cols'] = "UPPER(iap_supplier_catalog.cat_item_code) as exist_item_cd";
$iapPass['where'] = "iap_supplier_catalog.cat_supplier_id = ".$GLOBALS['$maSupplierCode'];
$iapPass['order'] = "iap_supplier_catalog.cat_item_code";
$iapRet = (array) IAP_Get_Rows($iapPass);
if ($iapRet['retcode'] == 0) {
	if ($iapRet['numrows'] > 0) {
		$CatIn = $iapRet['data'];
		for($i=0; $i<count($CatIn); $i++) {
			$IC = $CatIn[$i]['exist_item_cd'];
			if ($IC == "111111"
			or $IC == "BA9999"		// Generics we created
			or $IC == "BR9999"
			or $IC == "ER9999"
			or $IC == "IN9996"
			or $IC == "IN9997"
			or $IC == "IN9998"
			or $IC == "IN9999"
			or $IC == "IN9997"
			or $IC == "IN9997"
			or $IC == "INS9998"
			or $IC == "INS9999"
			or $IC == "PD9999"
			or $IC == "TN9999") {
				$a = "";
			} else {
				$maExistItemCodes[$i] = $CatIn[$i]['exist_item_cd'];
			}
		}
		$CatItems = $GLOBALS['$maNewItemCodes'];
		foreach($maExistItemCodes as $IC) {		

//	if ($IC == "INS1348") {
//		$A = "INS1348";
//	}

			$IL = array_search($IC, $CatItems);
			if ($IL !== FALSE) {
				$IC = "FOUND";
				continue;
			}

			$maCatItem = IAP_Get_Catalog_Only($IC, "1");
			if ($maCatItem < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected item from the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
			if ($maCatItem['cat_active'] == "Y") {
				$maCatItem['cat_active'] = "N";
				$iapRet = IAP_Update_Data($maCatItem, "supcat");
				if ($iapRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR updating catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$GLOBALS['$maInactiveItems'] = $GLOBALS['$maInactiveItems'] + 1;
				iapDisplayMsg("************** Marking item as inactive ".$IC."-".$maCatItem['cat_description']);

			}
		}
	}
}


// TODO Optimize Table iap_supplier_catalog


$FSJ = IAP_Build_New_Row(array("table" => "jrnl"));
$FSJrnl = $FSJ[0]; 
$FSJrnl['jrnl_company'] = $_REQUEST['CoId'];
$FSJrnl['jrnl_date'] = date("Y-m-d");
$FSJrnl['jrnl_description'] = "Magnabilities Item Updater (MagStripper) Completed";
$FSJrnl['jrnl_type'] = "MI";
$FSJrnl['jrnl_item_code'] = "";
$FSJrnl['jrnl_units'] = 0;
$FSJrnl['jrnl_amount'] = 0;
$FSJrnl['jrnl_tax'] = 0;
$FSJrnl['jrnl_shipping'] = 0;
$FSJrnl['jrnl_mileage'] = 0;
$FSJrnl['jrnl_expenses'] = 0;
$FSJrnl['jrnl_exp_explain'] = 0;
$FSJrnl['jrnl_vendor'] = 0;
$FSJrnl['jrnl_units'] = 0;
$FSJrnl['jrnl_price'] = 0;
$FSJrnl['jrnl_cat_code'] = "";
$FSJrnl['jrnl_comment'] = "Magnabilities Item Updater (MagStripper) Completed with New Items Added = ".number_format((float) $GLOBALS['$maNewItems']).
					    ", Added Price Rec With Cost = ".number_format((float) $GLOBALS['$maNewPriceCost']).
					    ", Updated Price Rec Cost = ".number_format((float) $GLOBALS['$maUpdPriceCost']).
					    ", Added Price Rec With Price = ".number_format((float) $GLOBALS['$maNewPricePrice']).
					    ", Updated Price Rec Price = ".number_format((float) $GLOBALS['$maUpdPricePrice']);

$FSJrnl['jrnl_detail_key'] = "";
$FSJrnl['jrnl_changed'] = date("Y-m-d");
$FSJrnl['jrnl_changed_by'] = $_REQUEST['IAPUID'];
$FSRet = IAP_Update_Data($FSJrnl, "jrnl");
if ($FSRet < 0) {
	echo "<span class=iapError>IAP INTERNAL ERROR writing journal [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
	exit;
}

// Show stats
echo "<table style='width:100%'>";
echo "<tr><td colspan='2'><span style='text-align:center;'>Magnability Item Update Stats</td><td style='width:60%;'>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Files Processed = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maFilesProcessed'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Categories Processed = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maCatsProcessed'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Items Processed = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maItemsProcessed'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>New Items Added = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maNewItems'])."</td></tr>";
if ($GLOBALS['$maNewItems'] > 0) {
	echo "<tr><td colspan='3'><span style='text-align:center;'>These NEW items should be reviewed for correctness!</td></tr>";	
}

echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Items Marked Inactive = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maInactiveItems'])."</td></tr>";

echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Added Price Rec With Cost = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maNewPriceCost'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Added Price Rec With Price = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maNewPricePrice'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Items With Pending Price Changes = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maPendingPriceChg'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Updated Price Rec Cost = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maUpdPriceCost'])."</td></tr>";
echo "<tr><td style='width:10%'></td><td style='width:30% text-align: right;'>Updated Price Rec Price = </td><td style='width:60%;'>";
echo number_format((float) $GLOBALS['$maUpdPricePrice'])."</td></tr>";

echo "<tr><td colspan='3'></td></tr>";
echo "<tr><td colspan='3'></td></tr>";

echo "<tr><td colspan='2'><span style='text-align:center;'>Choose what you would like to do from the menu on the left</td><td style='width:60%;'>";
echo "</td></tr></table>";

return;


function GetFiles($GFDir) {

	$GFDirList = scandir($GFDir);
	return($GFDirList);
}

function StripHdr($SHFile) {

	$HdrEnd = strpos($SHFile, '<div id="results_container"');
	$RecNoHdr = substr($SHFile, $HdrEnd + 23);
	return($RecNoHdr);
}

function StripFooter($SFFile) {

	$FtrStart = strpos($SFFile, "<!-- end Default Content //--");
	$RecNoFtr = substr($SFFile, 0, $FtrStart);
	return($RecNoFtr);
}

function ExtractGroup($EGFile) {

	$EGStart = strpos($EGFile, '<div ');
	if ($EGStart === FALSE) {
		return(NULL);
	}

	$EGDiv = substr($EGFile, $EGStart, 75);
	if (strcmp($EGDiv, '<div style="width:100%; text-align: center; background-color: #62269e; color: #FFFFFF;">') == 0) {
		return(NULL);
	}

	$EGEnd = strpos($EGFile, "</table>", $EGStart + 76);
	if ($EGEnd === FALSE) {
		return(NULL);
	}
	$EGGroup = trim(substr($EGFile, $EGStart, $EGEnd + 10 - $EGStart));
	return($EGGroup);
}

function GetCategory($GCGroup) {

	$EndDiv = strpos($GCGroup, ">");
	$SlshDiv = strpos($GCGroup, "</div>");
	$GrpCat = substr($GCGroup, $EndDiv + 1, $SlshDiv - $EndDiv - 1);
	$GrpCat = trim($GrpCat);
	return($GrpCat);
}

function GetItems($GIFileName, $GIGroup) {
	$GIRealEnd = strpos($GIGroup, "</table>");
	if ($GIRealEnd === FALSE) {
		$GIRealEnd = strlen($GIGroup);
	}
	$GIItems = array();
	$GIEnd = "N";
	$GIEndLastItem = 0;
	while($GIEnd == "N") {
		$GII1 = strpos($GIGroup, '<img src=', $GIEndLastItem);
		if ($GII1 === FALSE
		or  $GII1 > $GIRealEnd) {
			$GIEnd = "Y";
		} else {

// <img src="https://extranet.securefreedom.com/Magnabilities/Shopping/Images/5089_GiftCert_100_1.png" width="150"><br /><b>GC-100</b><br />$100 Gift Certificate!<br />$100.00

// 2018-05-12
// <img src="images/3472_BH1000p_250_1.jpg" width="150"><br /><b>BH1000p</b><br />Badge Holder Quick Pick - 10 pack<br />$100.00
// </td>

//	8/1/17		$GII2 = strpos($GIGroup, '_files/', $GII1) + 7;

			$GIISrc = substr($GIGroup, $GII1 + 10, 5);
			if ($GIISrc == "image") {
				$GII2 = $GII1 + 10;
			} else {
				$GII2 = strpos($GIGroup, 'Images/', $GII1 + 9);				
			}
			$GII3 = strpos($GIGroup, '" width', $GII2);
			$GIImage = substr($GIGroup, $GII2, $GII3 - $GII2);

//	8/1/17		$GII4 = strpos($GIGroup, "<br><b>", $GII3 + 7);
//	8/1/17		$GII5 = strpos($GIGroup, "</b><br>", $GII4);
			$GII4 = strpos($GIGroup, "<br /><b>", $GII3 + 7);
			$GII5 = strpos($GIGroup, "</b><br />", $GII4);
			$GIIPos = strpos($GII5, "?");
			if ($GIIPos > 0) {
				$GII5 = substr($GII5, 0, $GIIPos);
			}
			$GIICode = strtoupper(substr($GIGroup, $GII4 + 9, $GII5 - $GII4 - 9));

			$GIICode = str_replace("IMAGES/", "", $GIICode);




// add code to array to check obsolete later.
			$GLOBALS['$maNewItemCodes'][] = $GIICode;

//	8/1/17		$GII6 = strpos($GIGroup, "<br>", $GII5 + 10);
			$GII6 = strpos($GIGroup, "<br />", $GII5 + 10);
			$GIIDesc = substr($GIGroup, $GII5 + 10, $GII6 - $GII5 - 10);
			$GIIDesc = trim($GIIDesc);

			$GII7 = strpos($GIGroup, "</td>", $GII6);
			$GIIAmt = substr($GIGroup, $GII6 + 6, $GII7 - $GII6 - 6);
			$GIIAmt = trim($GIIAmt);
			$GIItems[] = array("GICode" => $GIICode, "GIDesc" => $GIIDesc, "GIAmt" => $GIIAmt, "GIImage" => $GIImage);
			if (count($GIItems) > 250) {
				iapDisplayMsg("......... Processing ".strval(count($GIItems))." items (Partial)");
				$GLOBALS['$maItemsProcessed'] = $GLOBALS['$maItemsProcessed'] + count($GIItems);
				FormatSQL($GIFileName, $GIItems);
				$GIItems = array();
			}

			$GIEndLastItem = $GII7 + 4;
		}
	}

	if (count($GIItems) > 0) {
		iapDisplayMsg("......... Processing ".strval(count($GIItems))." items");
		$GLOBALS['$maItemsProcessed'] = $GLOBALS['$maItemsProcessed'] + count($GIItems);
		FormatSQL($GIFileName, $GIItems);
		$GIItems = array();
	}
	return;
}

function FormatSQL($FSFileName, $FSGrpItems) {

	if (!(set_time_limit(800))) {
		echo "<span style=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
	}

	$FSType = ucwords(strtolower(substr($FSFileName, -3)));
	iapDisplayMsg(":::::::::::: File type is ".$FSType);
	$NumI = count($FSGrpItems);
	foreach($FSGrpItems as $GI) {
		$I = $I + 1;
		if ($I == $NumI) {
			break;
		}
		$FSAmt = str_replace("$", "", $GI['GIAmt']);
		$FSItem = IAP_Get_Catalog_Only($GI['GICode'], "1");		// only from magnabilities
		if ($FSItem < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive selected item from the catalog [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		if ($FSItem['status'] == "NEW") {
			iapDisplayMsg(">>>>>>>>>>>> Adding item ".$GI['GICode']);
			$GLOBALS['$maNewItems'] = $GLOBALS['$maNewItems'] + 1;
			$FSItem['cat_supplier_id'] = $GLOBALS['$maSupplierCode'];
			$FSItem['cat_item_code'] = $GI['GICode'];
			$FSItem['cat_description'] = $GI['GIDesc'];
			$FSItem['cat_supplier'] = $GLOBALS['$maSupplierName'];
			$FSItem['cat_profitability_amount'] = 0;
			$FSItem['cat_profitability_percent'] = 0;
			iapDisplayMsg("************** Updating picture for item ".$GI['GICode']." to ".$GI['GIImage']);
			$FSItem['cat_image_file'] = $GI['GIImage'];
			$FSItem['cat_changed'] = date("Y-m-d");
			$FSItem['cat_changed_by'] = $_REQUEST['IAPUID'];
			$FSRet = IAP_Update_Data($FSItem, "supcat");
			if ($FSRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR updating catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		} else {
			if ($FSType == "Con"
			and empty($FSItem['cat_image_file'])) {
				iapDisplayMsg("************** Updating picture for item ".$GI['GICode']." to ".$GI['GIImage']);
				$FSItem['cat_image_file'] = $GI['GIImage'];
				$FSRet = IAP_Update_Data($FSItem, "supcat");
				if ($FSRet < 0) {
					echo "<span class=iapError>IAP.
  INTERNAL ERROR updating catalog item [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}
		}

		$NeedUpdate = "N";
		$FSPriceRec = IAP_Get_Price($GI['GICode'], "1", "Y");	// Magnabilities items only
		if ($FSPriceRec['status'] == "NEW") {
			$NeedUpdate = "F";
			if ($FSType == "Con") {
				iapDisplayMsg("+++++++++++++++ Item cost (prc_cost) being added for new item ".$GI['GICode']." from ".
					number_format((float) $FSPriceRec['prc_cost'], 2)." to ".$FSAmt);
				$GLOBALS['$maNewPriceCost'] = $GLOBALS['$maNewPriceCost'] + 1;
			} else {
				iapDisplayMsg("+++++++++++++++ Item price (prc_price) being added for new item ".$GI['GICode']." from ".
					number_format((float) $FSPriceRec['prc_price'], 2)." to ".$FSAmt);
				$GLOBALS['$maNewPricePrice'] = $GLOBALS['$maNewPricePrice'] + 1;
			}
		} else {
			if ($FSPriceRec['prc_effective_until'] != "2099-12-31") {
				iapDisplayMsg("=============== Pending pricing change for item ".$GI['GICode']." in ".$FSFileName." effective until ".date("m/d/Y",strtotime($FSPriceRec['prc_effective_until'])));
				$GLOBALS['$maPendingPriceChgs'] = $GLOBALS['$maPendingPriceChgs'] + 1;
				continue;
			}
			if ($FSType == "Con") {
				if ($FSPriceRec['prc_cost'] != $FSAmt) {
					$NeedUpdate = "Y";
					iapDisplayMsg("+++++++++++++++ Item cost (prc_cost) requires updating for item ".$GI['GICode']." from ".
						number_format((float) $FSPriceRec['prc_cost'], 2)." to ".$FSAmt);
					$GLOBALS['$maUpdPriceCost'] = $GLOBALS['$maUpdPriceCost'] + 1;
				}
			} else {
				if ($FSPriceRec['prc_price'] != $FSAmt) {
					$NeedUpdate = "Y";
					iapDisplayMsg("+++++++++++++++ Item price (prc_price) requires updating for item ".$GI['GICode']." from ".
						number_format((float) $FSPriceRec['prc_price'], 2)." to ".$FSAmt);
					$GLOBALS['$maUpdPricePrice'] = $GLOBALS['$maUpdPricePrice'] + 1;
				}
			}
		}

		if ($NeedUpdate == "N") {
			continue;
		} elseif ($NeedUpdate == "F") { 
			iapDisplayMsg("********************** Adding price rec for ".$GI['GICode']." 2099-12-31");
/*
Item Categories
cat001	1 Inch Bracelets
cat002	1 Inch Halo Insert Adapters
cat003	1 Inch Insert Packs
cat004	1 Inch Pendants
cat005	1 Inch Pins and Brooches
cat006	Badge Reels and Lanyards
cat007	Mini-Bittie Bracelets
cat008	Mini-Bittie Pendants
cat009	Mini-Bittie Pins and Brooches
cat010	Mini-Bittie Premium Inserts
cat011	Mini-Bittie Standard Inserts
cat012	Consultant Supplies
cat013	Custom Designs
cat014	Display and Storage
cat015	Earrings
cat016	Necklaces
cat017	Ponytail Holder
cat018	Premium 1 Inch Inserts
cat019	Premium Select 1 Inch Inserts
cat020	Rings
cat021	Standard 1 Inch Inserts
cat022	Standard Select 1 Inch Inserts
cat023	Starter Sets
cat024	Tween Jewelry
cat025	1 Inch Pendant Sets
cat026	Inventory Builders
cat027	Special Packs
cat028	Bittie Inserts
cat029	Supplies
cat030	Miscellaneous
cat031  Keychains
cat032	Charms
cat033  Gift Certificates
*/
			$ProdCat = "";
			$n = substr($GI['GICode'], 1,1);
			if (is_numeric($n)) {
				$ProdCat = "cat021";					// Standard 1 Inch Inserts
			} else {
				$ProdType1 = substr($GI['GICode'], 0, 1);
				$ProdType2 = substr($GI['GICode'], 0, 2);
				$ProdType3 = substr($GI['GICode'], 0, 3);
				switch($ProdType2) {
					case "BA":
						$ProdCat = "cat002";			// 1 Inch Halo Insert Adapters
						break;
					case "BH":
						$ProdCat = "cat006";			// Badge Reels and Lanyards
						break;
					case "BR":
						if (strtolower(strpos($GI['GIDesc']), "bittie") > 0) {
							$ProdCat = "cat007";		// Bittie Bracelets
						} elseif ($ProdType2 == "BR") {
							$ProdCat = "cat001";		// 1 Inch Bracelets
						}
						break;
					case "CH":
						$ProdCat = "cat032";			// Charms
						break;
					case "ER":
						$ProdCat = "cat015";			// Earrings
						break;
					case "GC":
						$ProdCat = "cat033";			// Gift Certificates
						break;
					case "HE":
						$ProdCat = "cat021";			// Standard 1 Inch Inserts
						break;
					case "HG":
						$ProdCat = "cat029";			// Supplies
						break;
					case "HH":
						$ProdCat = "cat030";			// Miscelaneous
						break;
					case "IB":
						$ProdCat = "cat026";			// Inventory Builders
						break;
					case "IN":
					case "IS":
						if ($ProdType3 == "INS") {
							$ProdCat = "cat022";		// Standard Select 1 Inch Inserts
						} elseif (strtolower(strpos($GI['GIDesc']), "premium select") > 0) {
							$ProdCat = "cat019";		// Premium Select 1 Inch Inserts
						} elseif (strtolower(strpos($GI['GIDesc']), "premium") > 0) {
							$ProdCat = "cat018";		// Premium 1 Inch Inserts
						} elseif (strtolower(strpos($GI['GIDesc']), "mini") > 0) {
							$ProdCat = "cat028";		// Bittie Inserts
						} elseif (substr($GI['GICode'], -1) == "p") {
							$ProdCat = "cat003";		// 1 Inch Insert Packs
						} else {
							$ProdCat = "cat021";		// Standard 1 Inch Insert
						}
						break;
					case "KC":
						$ProdCat = "cat031";			// Keychains
						break;
					case "MB":
					case "MC":
						$ProdCat = "cat012";			// Consultant Supplies
						break;
					case "MP":
						if (strtolower(strpos($GI['GIDesc']), "bittie") > 0) {
							$ProdCat = "cat009";		// Bittie Pins and Broches
						} else {
							$ProdCat = "cat005";		// 1 Inch Pins and Broches
						}
						break;
					case "NK":
						if (strpos($GI['GIDesc'], "Lanyard") > 0) {
							$ProdCat = "cat006";		// Lanyard
						} else {
							$ProdCat = "cat016";		// Necklaces
						}
						break;
					case "PD":
						if (strtolower(substr($GI['GICode'], -1)) == "s") {
							$ProdCat = "cat025";		// 1 Inch Pendant Sets
						} elseif (strtolower(strpos($GI['GIDesc']), "bittie") > 0) {
							$ProdCat = "cat008";		// Bittie Pendants
						} else {
							$ProdCat = "cat004";		// 1 Inch Pendants
						}
						break;
					case "PH":
						$ProdCat = "cat017";			// Ponytail Holders
						break;
					case "QP":
						$ProdCat = "cat027";			// Special Packs
						break;
					case "RG":
						$ProdCat = "cat020";			// Rings
						break;
					case "SP":
						$ProdCat = "cat027";			// Special Packs
						break;
					case "TN":
						$ProdCat = "cat014";			// Display and Storage
						break;
					default:
						$ProdCat = "UNKN";				// Unknown Category
						iapDisplayMsg(">>>>>>>>>>>>>>>>>>>>> Cannot determine category for ".$GI['GICode']);
				}
			}
			$FSPriceRec['prc_supplier_id'] = $GLOBALS['$maSupplierCode'];
			$FSPriceRec['prc_item_code'] = $GI['GICode'];
			$FSPriceRec['prc_effective_until'] = '2099-12-31';
			$FSPriceRec['prc_effective'] = "2010-01-01";
			if ($FSType == "Con") {
				$FSPriceRec['prc_cost'] = $FSAmt;
			} else {
				$FSPriceRec['prc_cost'] = 0;
			}
			$FSPriceRec['prc_units'] = 1;
			$FSPriceRec['prc_cost_unit'] = $FSPriceRec['prc_cost'];
			if ($FSType == "Ret") {
				$FSPriceRec['prc_price'] = $FSAmt;
			} else {
				$FSPriceRec['prc_price'] = 0;
			}
			$FSPriceRec['prc_cat_code'] = $ProdCat;
			$FSPriceRec['prc_changed'] = date("Y-m-d");
			$FSPriceRec['prc_changed_by'] = $_REQUEST['IAPUID'];
			$FSRet = IAP_Update_Data($FSPriceRec, "supprc");
			if ($FSRet < 0) {
				echo "<span class=iapError>IAP INTERNAL ERROR rewriting price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				exit;
			}
		} elseif ($NeedUpdate == "Y") {
			if ($FSPriceRec['prc_changed'] == date("Y-m-d")) {
				if ($FSType == "Con") {
					$FSNewPrice['prc_cost'] = $FSAmt;
					$FSNewPrice['prc_cost_unit'] = $FSAmt / $FSNewPrice['prc_units'];
				} else {
					$FSPriceRec['prc_price'] = $FSAmt;
				}
				$FSRet = IAP_Update_Data($FSPriceRec, "supprc");
				if ($FSRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR rewriting price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			} else {
				$FSPrc = IAP_Build_New_Row(array("table" => "supprc"));
				$FSNewPrice = $FSPrc[0];
				$FSNewPrice = $FSPriceRec;
				$FSNewPrice['prc_effective'] = date("Y-m-d");
				$FSNewPrice['prc_effective_until'] = '2099-12-31';
				if ($FSType == "Con") {
					$FSNewPrice['prc_cost'] = $FSAmt;
					$FSNewPrice['prc_cost_unit'] = $FSAmt / $FSNewPrice['prc_units'];
				} else {
					$FSPriceRec['prc_price'] = $FSAmt;
				}
				$FSNewPrice['prc_prev_cost'] = $FSPriceRec['prc_cost'];
				$FSNewPrice['prc_prev_units'] = $FSPriceRec['prc_units'];
				$FSNewPrice['prc_prev_cost_unit'] = $FSPriceRec['prc_cost_unit'];
				$FSNewPrice['prc_prev_price'] = $FSPriceRec['prc_price'];
				$FSNewPrice['prc_prev_cat_code'] = $FSPriceRec['prc_cat_code'];
				$FSNewPrice['prc_changed'] = date("Y-m-d");
				$FSNewPrice['prc_changed_by'] = $_REQUEST['IAPUID'];
				$FSNewPrice['status'] = "EXISTING";

				$FSPriceRec['prc_effective_until'] = date("Y-m-d", strtotime("-1 day"));
				$FSPriceRec['prc_changed'] = date("Y-m-d");
				$FSPriceRec['prc_changed_by'] = $_REQUEST['IAPUID'];
				$FSPriceRec['status'] = "NEW";
				$FSRet = IAP_Update_Data($FSPriceRec, "supprc");
				if ($FSRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR rewriting price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
				$FSRet = IAP_Update_Data($FSNewPrice, "supprc");
				if ($FSRet < 0) {
					echo "<span class=iapError>IAP INTERNAL ERROR writing new price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					exit;
				}
			}
		}
	}
}

function iapDisplayMsg($iapMsg) {
	echo $iapMsg."<br>";
	wp_ob_end_flush_all();
	flush();
}

?>