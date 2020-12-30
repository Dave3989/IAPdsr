<?php

$_REQUEST=array();
$_REQUEST['debugme'] = "Y";
$_REQUEST['CoId'] = 5;
$_REQUEST['IAPUID'] = 1;
$_REQUEST['runningapp'] = "IAP";
$_REQUEST['LHCPath'] = "C:/xampp72/htdocs/LitehausConsulting/";

$Prog = "IAPServicesNEW.php";
echo "Emulator running ".$Prog."<br>";

require_once("IAPDataConfig.php");

require_once("IAPServicesNEW.php");

$ret = IAP_Get_Company();

print_r($ret);

?>