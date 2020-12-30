<?php

// TODO Need to find way to get DST value

error_reporting(E_ERROR | E_PARSE);

global $MyDebug;
$MyDebug = "N";

$eePath = str_replace("\\", "/", dirname(__FILE__));
$eePath = explode("/", $eePath);
for ($i = 0; $i < count($eePath); $i++) {
	$ln = array_pop($eePath);
	if (strpos(strtolower($ln), "litehausconsulting") !== FALSE
	or  strtolower($ln) == "lhc"
	or  strtolower($ln) == "lhcqa") {
		array_push($eePath, $ln);
		$eeP = implode("/", $eePath);
		define('ABSPATH', $eeP."/");
		break;
	}
}

require_once(ABSPATH."Ajax/LHCCalendar/FCGetEvCommon.php");
$events = FCGetMain();

$out = json_encode($events);
echo $out;

if ($MyDebug == "Y") {
	$fdebug = fopen(ABSPATH."LHCDebug.txt", "a");
	fwrite($fdebug, "Ajax/FCGetEvents ".date("Y/m/d H:i:s")." /Done!".chr(10).chr(13));
	fclose($fdebug);
}

return;

?>