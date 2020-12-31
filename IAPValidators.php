<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>Loading IAP Validator<br>";
}

// ----------------------------------------------------------------------------------
// Validation Routines
// --- Field not blank

function IAP_Validate_Nonblank($IAPOrig, $IAPNew, $IAPNumeric = "N") {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Nonblank with original of ".$IAPOrig." and new of ".$IAPNew.".<br />";
	}

	$IAPErr = "N";
	$IAPChg = "N";
	$IAPRetValue = $IAPOrig;

	if ($IAPNew != $IAPOrig) {
		$IAPChg = "Y";
		if (trim($IAPNew) == "") {
			$IAPErr = "Y";
		} else {
			if ($IAPNumeric == "Y") {
				$IAPNewValue = str_replace(",", "", $IAPNew);
				if (is_numeric($IAPNewValue)) {
					$IAPRetValue = $IAPNewValue;
				} else {
					$IAPErr = 2;
				}
			} else {
				$IAPRetValue = $IAPNew;
			}
		}
	}
	if ($IAPRetValue == "") {
		$IAPErr = "Y";
	}

	if ($_REQUEST['runningapp'] == "IAP"
	and $IAPErr == "Y") {
		$IAPErr = 1;	
	} 

	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

//			if ($_REQUEST['debugme'] == "Y") {
//				echo "...---...returning<pre>";
//				var_dump($Ret);
//				echo "</pre>";
//			}

	return($Ret);
}

// --- Validate Date

function IAP_Validate_Date($IAPOrig, $IAPNew, $IAPBlankOK = "N") {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Date with original of ".$IAPOrig." and new of ".$IAPNew.".<br />";
	}

	$IAPErr = 0;

	$IAPOrig = trim($IAPOrig);
	$IAPNew = trim($IAPNew);

	$IAPChg = "N";
	if ($IAPOrig != "") {
		$IAPRetValue = strtotime($IAPOrig);
	} else {
		$IAPRetValue = trim($IAPOrig);
	}

	if (($IAPNew == "")
	and ($IAPBlankOK == "Y")) {
// This is ok!
		$IAPRetValue = $IAPNew;
		if ($IAPOrig != "") {
			$IAPChg = "Y";
		}
	} elseif ($IAPNew == "") {
		$IAPErr = 1;
	} else {
		$IAPDateNew = strtotime($IAPNew);

		if (!is_numeric($IAPDateNew)) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...strtotime failed.<br />";
			}

			$IAPErr = 2;
		}
		$IAPM = date("m", $IAPDateNew);
		$IAPD = date("d", $IAPDateNew);
		$IAPY = date("Y", $IAPDateNew);

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---...checking date with ".$IAPM.", ".$IAPD.", ".$IAPY."<br />";
		}

		if (checkdate($IAPM, $IAPD, $IAPY)) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...good return. setting retval = ".strval($IAPRetValue)."<br />";
			}

			$IAPRetValue = $IAPDateNew;
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...bad return from checking date<br />";
			}

			$IAPErr = 2;
		}

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---...retval = ".strval($IAPRetValue)."<br />";
		}

		if ($IAPRetValue != strtotime($IAPOrig)) {
			$IAPChg = "Y";
		}
	}
	if ($IAPRetValue != "") {
		$IAPRetValue = date("Y-m-d", $IAPRetValue);
	}
	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---...returning<pre>";
		var_dump($Ret);
		echo "</pre>";
	}

	return($Ret);
}

// --- Validate Time

function IAP_Validate_Time($IAPOrig, $IAPNew, $IAPBlankOK = "N") {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Time with original of ".$IAPOrig." and new of ".$IAPNew.".<br />";
	}

	$IAPErr = 0;
	$IAPChg = "N";

	$IAPOrig = trim($IAPOrig);
	$IAPNew = trim($IAPNew);
	
	$IAPOrig = substr($IAPOrig, 0, 5);
	$IAPRetValue = $IAPOrig;

	if (($IAPNew == "")
	and ($IAPBlankOK == "Y")) {
// This is ok!
		$IAPRetValue = $IAPNew;
	} elseif ($IAPNew == "") {
		$IAPErr = 1;
	} else {
		if (!strpos($IAPNew, ":")) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...no colon in time - error 2";
			}

			$IAPErr = 2;
		} else {
			$IAPT2 = explode(":", $IAPNew);
			if (strlen($IAPT2[0]) > 2) {
				$IAPErr = 2;
			} elseif (!is_numeric($IAPT2[0])) {
				$IAPErr = 2;
			} elseif (strlen($IAPT2[1]) == 2) {
				if (intval($IAPT2[0]) < 0
				or  intval($IAPT2[0]) > 23) {
					$IAPErr = 2;
				} elseif (!is_numeric($IAPT2[1])) {
					$IAPErr = 2;
				} elseif (intval($IAPT2[1]) < 0
					or intval($IAPT2[1]) > 59) {
					$IAPErr = 2;
				} else {
					$IAPRetValue = $IAPNew;
				}
			} else {
				if (intval($IAPT2[0]) < 0
				or  intval($IAPT2[0]) > 12) {
					$IAPErr = 2;
				}
				$IAPT3 = explode(" ", $IAPT2[1]);
				if (strlen($IAPT3[0]) == 4) {
					$IAPT3[1] = substr($IAPT3[0],-2);
					$IAPT3[0] = substr($IAPT3[0], 0, 2);
				}
				if (strlen($IAPT3[0]) > 2) {
					$IAPErr = 2;
				} elseif (!is_numeric($IAPT3[0])) {
					$IAPErr = 2;
				} elseif (intval($IAPT3[0]) < 0
				  or intval($IAPT3[0]) > 59) {
					$IAPErr = 2;
				} elseif (strlen($IAPT3[1]) > 2) {
					$IAPErr = 2;
				} elseif (strtolower($IAPT3[1]) != "am"
				  and strtolower($IAPT3[1]) != "pm") {
					$IAPErr = 2;
				} else {
					if (strtolower($IAPT3[1]) == "pm") {
						$IAPT2[0] = intval($IAPT2[0]) + 12;
					}
					$IAPNew = strval($IAPT2[0]).":".strval($IAPT3[0]);
					$IAPRetValue = $IAPNew;
				}
			}
		}
	}
	if ($IAPRetValue != $IAPOrig) {
		$IAPChg = "Y";
	}

	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---...returning<pre>";
		var_dump($Ret);
		echo "</pre>";
	}

	return($Ret);
}


// --- Validate phone number

function IAP_Validate_Phone($IAPOrig, $IAPNew) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Phone with original of ".$IAPOrig." and new of ".$IAPNew.".<br />";
	}

	$IAPErr = "N";
	$IAPChg = "N";
	$IAPRetValue = $IAPOrig;

	if ($IAPNew == "") {
		$IAPErr = "1";
	} else {
		$p = str_ireplace("(", "", $IAPNew);
		$p = str_ireplace(")", "", $p);
		$p = str_ireplace("-", "", $p);
		if(strlen($p) != 10) {
			$IAPErr = "2";	
		} elseif (! is_numeric($p)) {
			$IAPErr = "3";
		} else {
			$IAPRetValue = substr($p, 0, 3)."-".substr($p, 3, 3)."-".substr($p, 6, 4);
		}

		if ($IAPRetValue != $IAPOrig) {
			$IAPChg = "Y";
		}
	}

	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---...returning<pre>";
		var_dump($Ret);
		echo "</pre>";
	}

	return($Ret);
}

// another one if this does not work
//$regex = '/^(?:1(?:[. -])?)?(?:\((?=\d{3}\)))?([2-9]\d{2})(?:(?<=\(\d{3})\))? ?(?:(?<=\d{3})[.-])?([2-9]\d{2})[. ]?(\d{4})(?: (?i:ext)\.? ?(\d{1,5}))?$/';
// or broken up
//$regex = '/^(?:1(?:[. -])?)?(?:\((?=\d{3}\)))?([2-9]\d{2})'
//        .'(?:(?<=\(\d{3})\))? ?(?:(?<=\d{3})[.-])?([2-9]\d{2})'
//        .'[. -]?(\d{4})(?: (?i:ext)\.? ?(\d{1,5}))?$/';
// If you're wondering why all the non-capturing subpatterns (which look like this "(?:", it's so that we can do this:
//$formatted = preg_replace($regex, '($1) $2-$3 ext. $4', $phoneNumber);
// --- Validate email

function IAP_Validate_Email($IAPOrig, $IAPNew) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Email with original of [".$IAPOrig."] and new of [".$IAPNew."].<br />";
	}

	$IAPErr = "N";
	$IAPChg = "N";
	$IAPRetValue = $IAPOrig;

	if ($IAPNew == "") {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---new is blank.<br />";
		}

		$IAPErr = "1";
	} elseif ($IAPNew != $IAPOrig) {
		$IAPChg = "Y";
		// use wordpress email check
		if (!(is_email($IAPNew))) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---new is not valid.<br />";
			}

			$IAPErr = "2";
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---new is good! Sending it back.<br />";
			}

			$IAPRetValue = $IAPNew;
		}
	} elseif ($IAPOrig == "") {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---original is blank.<br />";
		}

		$IAPErr = "1";
		// use wordpress email check
	} elseif (!(is_email($IAPOrig))) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---original is not valid.<br />";
		}

		$IAPErr = "2";
	}

	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

//			if ($_REQUEST['debugme'] == "Y") {
//				echo "...---...returning<pre>";
//				var_dump($Ret);
//				echo "</pre>";
//			}

	return($Ret);
}

// --- Validate web domain

function IAP_Validate_Domain($IAPOrig, $IAPNew) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Domain with original of [".$IAPOrig."] and new of [".$IAPNew."].<br />";
	}

	$IAPErr = "N";
	$IAPChg = "N";
	$IAPRetValue = $IAPOrig;

	if ($IAPNew == "") {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---new is blank.<br />";
		}

		$IAPErr = "1";
	} elseif ($IAPNew != $IAPOrig) {
		$IAPChg = "Y";
		if (!(IAP_Is_Domain($IAPNew))) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---new is not valid.<br />";
			}

			$IAPErr = "2";
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---new is good! Sending it back.<br />";
			}

			$IAPRetValue = $IAPNew;
		}
	} elseif ($IAPOrig == "") {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---original is blank.<br />";
		}

		$IAPErr = "1";
	} elseif (!(IAP_Is_Domain($IAPOrig))) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---original is not valid.<br />";
		}

		$IAPErr = "2";
	}

	$Ret = array('Changed' => $IAPChg, 'Error' => $IAPErr, 'Value' => $IAPRetValue);

//			if ($_REQUEST['debugme'] == "Y") {
//				echo "...---...returning<pre>";
//				var_dump($Ret);
//				echo "</pre>";
//			}

	return($Ret);
}

function IAP_Is_Domain($IAPDomain) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Is_Domain with domain of ".$IAPDomain.".<br />";
	}

	$IAPDomain = str_replace(strtolower("http://"), "", $IAPDomain);
	$IAPDomain = str_replace(strtolower("www."), "", $IAPDomain);
	if (substr($IAPDomain, -1) == "/") {
		$IAPDomain = substr($IAPDomain, 0, -1);
	}
	if (getmxrr($IAPDomain, $IAPMXRec)) {  // will not work without internet connection
		if ($_REQUEST['debugme'] == "Y") {
			echo "...domain is ok.<br />";
		}

		return TRUE;
	} else {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...domain is invalid.<br />";
		}

		return FALSE;
	}
}

function IAP_Validate_Password($IAPOrig, $IAPNew) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Validate_Password with original of ".$IAPOrig." and new of ".$IAPNew.".<br />";
	}

	$IAPErr = "N";
	$IAPChg = "N";
	$IAPRetValue = $IAPOrig;

	if ($IAPNew != $IAPOrig) {
		$IAPChg = "Y";
		if ($IAPNew == "") {
			$IAPErr = "Y";
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---requiring NewPasswordValidator<br />";
			}

			require_once("NewPasswordValidator.php");

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---instantiating class<br />";
			}

			$pv = new NewPasswordValidator($IAPNew);

			//Run validators...

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---validating length<br />";
			}

			$pv->validate_length(6, 30); //Password must be at least 6 and less than 30 characters long

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...returned error of ".$pv->getValid()."<br />";
			}

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---validating no blanks<br />";
			}

			$pv->validate_whitespace(); //No whitespace please

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...returned error of ".$pv->getValid()."<br />";
			}

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---validating 1 non-alpha<br />";
			}

			$pv->validate_non_numeric(1); //Password must have 1 non-alpha character in it.

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---...returned error of ".$pv->getValid()."<br />";
			}

//					if ($_REQUEST['debugme'] == "Y") {  echo "...---...validating format<br />"; }
//
//			$pv->validate_custom("/[a-z]{3}[0-9]{5}/i", "Password must be 3 letters followed by 5 numbers");
//
//					if ($_REQUEST['debugme'] == "Y") {  echo "...---...returned error of ".$pv->getValid()."<br />"; }

			if ($pv->getValid() == 0) {
				$IAPErrMsg = ucwords($pv->getError());
				$IAPErr = "Y";
			} else {
				$IAPRetValue = $IAPNew;
			}
		}
	}
	if ($IAPRetValue == "") {
		$IAPErr = "Y";
	}

	$Ret = array('Changed' => $IAPChg, 'Value' => $IAPRetValue, 'Error' => $IAPErr, 'ErrorMsg' => $IAPErrMsg);

//			if ($_REQUEST['debugme'] == "Y") {
//				echo "...---...returning<pre>";
//				var_dump($Ret);
//				echo "</pre>";
//			}

	return($Ret);
}

?>