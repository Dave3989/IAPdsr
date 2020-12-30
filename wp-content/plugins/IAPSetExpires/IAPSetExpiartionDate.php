<?php

/*
Plugin Name: IAP Get Expiration
Plugin URI: http://Litehaus Consulting/MyPlugins
Description: Gets the license expiration date and puts into REQUEST
Author: Dave/Litehaus Consulting
Version: 0.1
Author URI: http://LitehausConsulting.com


---- This can go away when the Store link is in place...
----    That link should 
----       1) create the initial company record
----       2) add the set up and expiration dates to the co rec
----       3) set the user to contributor so the app link is visable in the menu


*/

add_action('init', 'iapGetExpiration');
function iapGetExpiration() {

	if (is_admin()) {
		return;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo " SetExpiratioDate --- Requiring IAPServices<br>";
	}

	require_once(ABSPATH."IAPServices.php");

	if ($_REQUEST['debugme'] == "Y") {
		echo "Going to IAP_Program_Init<br>";
	}

	IAP_Program_Init();

	if ($_REQUEST['debugme'] == "Y") {
		echo "Back from Init<br>";
	}

	return;

echo "leaving expire<br>";

}

?>