<?php
function IAP_Get_Item_Lists($iapSet = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In GetItemLists.<br>";
	}

	if ($_REQUEST['debuginfo'] == "Y") {
		phpinfo(INFO_VARIABLES);
	}

	require_once(ABSPATH."IAPServices.php");
	if (iap_Program_Start("NOCHK", "N") < 0) {
		return;
	};

	$iapSuppList = "";
	$iapSuppliers = IAP_Build_CoSupp_Array();
	if ($iapSuppliers < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve catalog suppliers. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}

	$iapDescList = "";
	$iapItems = IAP_Get_Catalog_List("D", $iapSet);
	if ($iapItems < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve catalog items. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}
	if ($iapItems != NULL) {
		$c = "";
		foreach ($iapItems as $iapI) {
			$nm = "";
			if (!empty($iapI['cat_description'])) {
				$i = $iapI['CO'];
				$id = $iapSuppliers[$i]['SId'];
				if (count($iapSuppliers) > 1) {
//					$nm = " From ".$iapSuppliers[$i]['SShortName'];
					$nm = $iapSuppliers[$i]['SShortName'];
				}
//				$l = $iapI['cat_description'].$nm;
				$l = $iapI['cat_description'];
				$iapDescList = $iapDescList.$c.'{"label": "'.$l.'", "src": "'.$id.'", "sname": "'.$nm.'", "cd": "'.$iapI['cat_item_code'].'"}';
				$c = ",";
			}
		}
	}

	$iapItemList = "";
	$iapItems = IAP_Get_Catalog_List("C", $iapSet);
	if ($iapItems < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Cannot retrieve catalog items. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}
	if ($iapItems != NULL) {
		$c = "";
		foreach ($iapItems as $iapI) {
			$nm = "";
			if (!empty($iapI['cat_description'])) {
				$i = $iapI['CO'];		// ['CO'] is field name containing the supplier code NOT always CO
				$id = $iapSuppliers[$i]['SId'];
				if (count($iapSuppliers) > 1) {
//					$nm = " From ".$iapSuppliers[$i]['SShortName'];
					$nm = $iapSuppliers[$i]['SShortName'];
				}
//				$l = $iapI['cat_item_code'].$nm;
				$l = $iapI['cat_item_code'];
				$iapItemList = $iapItemList.$c.'{"label": "'.$l.'", "src": "'.$id.'", "sname": "'.$nm.'", "cd": "'.$iapI['cat_item_code'].'"}';
				$c = ",";
			}
		}
	}
	return(array($iapItemList, $iapDescList));
}
?>