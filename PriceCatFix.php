<?php

error_reporting(E_ERROR | E_PARSE);

$_REQUEST['sec_use_application'] = "Y";
require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

$iapPass['table'] = "supprc";
$iapPass['join'] = "join iap_supplier_catalog on cat_item_code = prc_item_code";
$iapPass['where'] = "prc_effective_until = '2099-12-31' AND prc_supplier_id = 1";
$iapPass['order'] = "prc_item_code";

$iapRet = (array) IAP_Get_Rows($iapPass);
if ($iapRet['retcode'] < 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: Error retrieving price records. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
if ($iapRet['numrows'] == 0) {
    echo "<span class=iapError>iap INTERNAL ERROR: No price records retrieved. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
    return;
}
$iapPrices = (array) $iapRet['data'];

$Changed = 0;
foreach($iapPrices as $Price) {

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
		cat011	Bittie Inserts
		cat029	Supplies
		cat030	Miscellaneous
		cat031  Keychains
		cat032	Charms
		cat033  Hostess Awards
		cat034  Join Incentive Set
		cat035  Hair Pin
		cat036  Petite Bracelet Slide
*/
	$ProdCat = "";
	$n = substr($Price['prc_item_code'], 1,1);
	if (is_numeric($n)) {
		$ProdCat = "cat021";					// Standard 1 Inch Inserts
	} else {
		$ProdType1 = strtoupper(substr($Price['prc_item_code'], 0, 1));
		$ProdType2 = strtoupper(substr($Price['prc_item_code'], 0, 2));
		$ProdType3 = strtoupper(substr($Price['prc_item_code'], 0, 3));
		$ProdType4 = strtoupper(substr($Price['prc_item_code'], 0, 4));
		$ProdTypeEnd = strtoupper(substr($Price['prc_item_code'], -1, 1));
		$ProdDesc = " ".strtolower($Price['cat_description']);

		switch($ProdType2) {
			case "AD":
				$ProdCat = "cat012";			// Supplies
				break;
			case "AP":
				$ProdCat = "cat021";			// 1 Inch Insert
				break;
			case "BA":
				$ProdCat = "cat002";			// 1 Inch Halo Insert Adapters
				break;
			case "BB":
				$ProdCat = "cat026";			// Business Builders
				break;
			case "BG":
			case "BH":
				$ProdCat = "cat006";			// Badge Reels and Lanyards
				break;
			case "BR":
				if (strpos($ProdDesc, "tribal") > 0) {
					$ProdCat = "cat001";		// Tribal beat 1" Bracelets
				} elseif (strpos($ProdDesc, "bittie") > 0) {
					$ProdCat = "cat007";		// Bittie Bracelets
				} elseif (strpos($ProdDesc, "delight") > 0) {
					$ProdCat = "cat024";		// Tween Bracelets
				} elseif (strpos($ProdDesc, "mini") > 0) {
					$ProdCat = "cat007";		// Bittie Bracelets
				} elseif (strpos($ProdDesc, "petite") > 0) {
					$ProdCat = "cat036";		// Petite Bracelets
				} else {
					$ProdCat = "cat001";		// 1 Inch Bracelets
				}
				break;
			case "CB":
				$ProdCat = "cat012";			// Brochures
				break;
			case "CH":
				$ProdCat = "cat032";			// Charms
				break;
			case "CL":
				$ProdCat = "cat001";			// Clasp Bracelets
				break;
			case "CP":
				$ProdCat = "cat030";			// Mag cell phone holder 
				break;
			case "DP":
			case "DT":
				$ProdCat = "cat014";			// Display and Supplies 
				break;
			case "ER":
				$ProdCat = "cat015";			// Earrings
				break;
			case "GC":
				$ProdCat = "cat033";			// Gift Certificates
				break;
			case "GS":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "GT":
				$ProdCat = "cat012";			// Gift Tags
				break;
			case "HA":
				if ($ProdType3 == "HAM") {
					$ProdCat = "cat017";		// Ponytail Holder
				} elseif ($ProdType3 == "HAP") {
					$ProdCat = "cat035";		// Hair Pin							
				}
				break;
			case "HE":
				$ProdCat = "cat021";			// Standard 1 Inch Inserts
				break;
			case "HG":
				$ProdCat = "cat033";			// Hostess Awards
				break;
			case "HH":
				$ProdCat = "cat030";			// Miscelaneous
				break;
			case "IB":
				$ProdCat = "cat003";			// Inventory Builders
				break;
			case "IN":
			case "IS":
				if ($ProdType3 == "INC") {
					$ProdCat = "cat021";		// Classic 1 Inch Inserts
				} elseif ($ProdType3 == "INM") {
					$ProdCat = "cat011";		// Mini Inserts
				} elseif ($ProdType3 == "INS") {
					$ProdCat = "cat022";		// Standard Select 1 Inch Inserts
				} elseif ($ProdTypeEnd == "P") {
					$ProdCat = "cat003";		// Insert Packs
				} elseif ($ProdTypeEnd == "S") {
					$ProdCat = "cat011";		// Bittie Inserts
				} elseif (strpos($ProdDesc, "premium select") > 0) {
					$ProdCat = "cat019";		// Premium Select 1 Inch Inserts
				} elseif (strpos($ProdDesc, "premium") > 0) {
					$ProdCat = "cat018";		// Premium 1 Inch Inserts
				} elseif (strpos($ProdDesc, "bittie") > 0) {
					$ProdCat = "cat011";		// Bittie Inserts
				} elseif (strpos($ProdDesc, "mini") > 0) {
					$ProdCat = "cat011";		// Bittie Inserts
				} else {
					$ProdCat = "cat021";		// Standard 1 Inch Insert
				}
				break;
			case "JC":
				$ProdCat = "cat012";			// Jewelry Case
				break;
			case "JS":
				$ProdCat = "cat034";			// Join Incentive Set
				break;
			case "KC":
				$ProdCat = "cat031";			// Keychains
				break;
			case "LA":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "MA":
				$ProdCat = "cat021";			// March2014 
				break;
			case "MB":
			case "MC":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "MI":
				$ProdCat = "cat011";			// Mini-Supprise
				break;
			case "MP":
				if (strpos($ProdDesc, "bittie") > 0) {
					$ProdCat = "cat009";		// Bittie Pins and Broches
				} elseif (strpos($ProdDesc, "mini") > 0) {
					$ProdCat = "cat009";		// Bittie Pins and Broches
				} else {
					$ProdCat = "cat005";		// 1 Inch Pins and Broches
				}
				break;
			case "MT":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "NK":
				if (strpos($Price['cat_description'], "Lanyard") > 0) {
					$ProdCat = "cat006";		// Lanyard
				} else {
					$ProdCat = "cat016";		// Necklaces
				}
				break;
			case "OB":
			case "OF":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "OR":
				$ProdCat = "cat028";			// Ornaments
				break; 
			case "PB":
				$ProdCat = "cat012";			// Party Flip Book
				break; 
			case "PD":
				if (strtolower(substr($Price['prc_item_code'], -1)) == "s") {
					$ProdCat = "cat025";		// 1 Inch Pendant Sets
				} elseif (strpos($ProdDesc, "delight") > 0) {
					$ProdCat = "cat024";		// Tween Pendants
				} elseif (strpos($ProdDesc, "dog-tag") > 0) {
					$ProdCat = "cat024";		// Tween Pendants
				} elseif (strpos($ProdDesc, "bitile") > 0) {
					$ProdCat = "cat008";		// Bittie Pendants
				} elseif (strpos($ProdDesc, "bittie") > 0) {
					$ProdCat = "cat008";		// Bittie Pendants
				} elseif (strpos($ProdDesc, "mini") > 0) {
					$ProdCat = "cat008";		// Mini Pendants
				} else {
					$ProdCat = "cat004";		// 1 Inch Pendants
				}
				break;
			case "PF":
				$ProdCat = "cat012";			// Ponytail Holders
				break;
			case "PH":
				$ProdCat = "cat017";			// Ponytail Holders
				break;
			case "PL":
			case "PN":
				$ProdCat = "cat012";			// Ponytail Holders
				break;
			case "QP":
				$ProdCat = "cat027";			// Special Packs
				break;
			case "RB":
				$ProdCat = "cat017";			// Ponytail Replacement Band
				break;
			case "RG":
				if (strpos($ProdDesc, "delight") > 0) {
					$ProdCat = "cat024";		// Tween Rings
				} else {
					$ProdCat = "cat020";		// Rings
				}
				break;
			case "SC":
				$ProdCat = "cat030";			// Misc
				break;
			case "SI":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "SL":						// Slides for Bracelets
				if ($ProdType3 == "SLC"
				or $ProdType4 == "SL-C") { 
					$ProdCat = "cat001";		// Classic Slide
				} elseif ($ProdType3 == "SLM"
					  or $ProdType4 == "SL-M") { 
					$ProdCat = "cat007";		// Mini Slide
				} elseif ($ProdType3 == "SLP") {
					$ProdCat = "cat036";		// Petite Slide
				} else {
					$ProdCat = "cat012";
				}
				break;
			case "SP":
				$ProdCat = "cat027";			// Special Packs
				break;
			case "SS":
				if ($ProdType3 == "SSC") {
					$ProdCat = "cat025";		// Classic Pendant Start Set							
				} elseif ($ProdType3 == "SSM") {
					$ProdCat = "cat008";		// Mini Pendant Start Set							
				} else {
					$ProdCat = "cat004";		// Classic Pendants
				}
				break;
			case "TC":
				$ProdCat = "cat030";			// Misc
				break;
			case "TE":
				$ProdCat = "cat012";			// Consultant Supplies
				break;
			case "TN":
				$ProdCat = "cat014";			// Display and Storage
				break;
			default:
				$ProdCat = "UNKN";				// Unknown Category
				iapDisplayMsg(">>> Cannot determine category for ".$Price['prc_item_code']);
		}
	}
	if ($ProdCat == "") {
		$ProdCat = "UNKN";						// Unknown Category
		iapDisplayMsg(">>> Cannot determine category for ".$Price['prc_item_code']);
	}

	if ($ProdCat != $Price['prc_cat_code']) {
		$ProdCatOld = $Price['prc_cat_code'];
		$Price['prc_cat_code'] = $ProdCat;
		$Price['prc_changed'] = "2019-04-20";
		$Price['prc_changed_by'] = 1;
		$iapRet = IAP_Update_Data($Price, "supprc");
		if ($iapRet < 0) {
			echo "<span class=iapError>IAP INTERNAL ERROR Updating Price record [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			exit;
		}
		iapDisplayMsg(">>> ".$Price['cat_description'].
						   " (".$Price['prc_item_code'].")".
						   " was ".$ProdCatOld.
						   " changed to ".$ProdCat);			
		$Changed = $Changed + 1;
	}
}

echo "<br><br>Finished! Number of changes is ".$Changed."<br>";

function iapDisplayMsg($iapMsg) {
	echo $iapMsg."<br>";
	wp_ob_end_flush_all();
	flush();
}

?>