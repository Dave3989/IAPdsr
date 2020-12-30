<?php

// This program calls the common get events in the Ajax directory wrapped in a function for calling from PHP.
// 		For some reason js did not work to have the jason call outside the function. 

function EEGetEvents() {

// TODO dates are GM

	$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__LINE__.")";

	require_once(ABSPATH."Ajax/IAPCalendar/IAPGetEvCommon.php");
	$events = FCGetMain();
	return($events);
}

?>