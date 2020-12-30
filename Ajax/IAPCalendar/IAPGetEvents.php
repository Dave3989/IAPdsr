<?php

error_reporting(E_ERROR | E_PARSE);

global $MyDebug;
$MyDebug = "N";
$_REQUEST['debugme'] = "N";

if ($_REQUEST['debugme'] == "Y") { echo ">>> In IAPGetEvents.<br />"; }

if ( ! defined( 'ABSPATH' ) ) {
	$vars = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
	$v = substr($vars, 0, strpos($vars, "/Ajax"))."/";
	define( 'ABSPATH', $v);
}
require_once(ABSPATH."IAPSetVars.php");

if ($MyDebug == "Y") {
	echo "...Host is ".ABSPATH." Name is ".$eeApp."<br />";
}

require_once($_REQUEST['IAPPath']."/Ajax/IAPCalendar/IAPGetEvCommon.php");
$events = FCGetMain();

$out = json_encode($events);
echo $out;

if ($MyDebug == "Y") {
	$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
	fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Done!".chr(10).chr(13));
	fclose($fdebug);
}

$MyDebug = "N";
$_REQUEST['debugme'] = "N";

return;

?>