<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}



$iapReadOnly = IAP_Format_Heading("Inventory Returns");

// RETURNS/ADJUSTMENTS

// Quantity, Reason, Original Order, Return Authorization (if any)

// Reverse Lot, Add comment tp PO


?>