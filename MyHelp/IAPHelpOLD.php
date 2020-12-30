<?php

// ------------------------------------------------
// These functions work with the user's help level
// ------------------------------------------------

function IAP_Save_Page_Option($iapPage, $iapOtion) {

	$iapHLRec = IAP_Get_Help_Level($iapPage);

	if ($iapHLRec['status'] == "NEW") {
		$iapHLRec['hl_client'] = $_REQUEST['IAPUID'];
		$iapHLRec['hl_page'] = $iapPage;
	}
	$iapHLRec['hl_level'] = $iapOtion;
	$iapHLRec['hl_updated'] = date("Y-m-d", strtotime("now"));
	$iapHLRec['hl_updated_by'] = $_REQUEST['IAPUID'];

	$iapRet = IAP_Update_Data($iapHLRec, "iaphlvl");
	if ($iapRet < 0) {
		echo "<span class=iapError>IAP INTERNAL ERROR writing help level [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
		exit;
	}

	return($iapHLRec);
}

function IAP_Set_User_HelpLevel() {

	$iapProfile = IAP_Get_Profile();
	if ($iapProfile < 0) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive your profile. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	    return;
	}
	if ($iapProfile == NULL) {
		$_REQUEST['UserData']['HelpLevel'] = 3;	// if profile not initialized assume level 3
	} else {
		$_REQUEST['UserData']['HelpLevel'] = $iapProfile['pro_help_level'];
	}

	return;
}


// --------------------------------------------
// These function work with the help narrative
// --------------------------------------------

function IAP_Do_Help($iapHelpLvl, $iapHelpPage, $iapHelpSection, $iapHelpVariable = "", $iapHelpForce = "N") {

	if ($iapHelpLvl != 4) {	// Always do Help Level 4
		if ($_REQUEST['co_id'] == "NEW") {
			$iapCHLvl = 3;	// if company id not initialized assume level 3
		} elseif ($iapHelpForce == "Y") {
			$iapCHLvl = $iapHelpLvl;
		} else {
			$iapCHLvl = IAP_Get_Help_Level($iapHelpPage);
			if ($iapCHLvl < 0) {
			    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the help level. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
			    return;
			}
			if ($iapCHLvl == NULL) {
				$iapCHLvl = $_REQUEST['UserData']['HelpLevel'];
			} elseif ($iapCHLvl == 0) {
				return("");
			} elseif ($iapCHLvl < $iapHelpLvl) {
				return("");
			}
		}
	}

	$iapHText = IAP_Get_Help_Text($iapHelpPage, $iapHelpSection, $iapHelpLvl);
	if ($iapHText < 0) {
	    echo "<span class=iapError>IAP INTERNAL ERROR: Cannot retreive the help text. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
	    return;
	}
	if ($iapHText == NULL) {
		return("");
	}

	$iapHExp = IAP_Process_Help($iapHText, $iapHelpVariable, $iapHelpLvl);

	if (empty($iapHExp)) {
		return("");
	}

	$iapHelpId = "help".strval($iapHelpPage).strval($iapHelpLvl).strval($iapHelpSection);
	switch($iapHelpLvl) {
		case 1:
			$iapHRet = "<img style='display:inline; vertical-align:top;' id='".$iapHelpId."' src='MyHelp/LHCQuestionMark.png' class='tooltip' title='".$iapHExp."'>";
			break;
		case 2:
			break;
		case 3:
			if ($iapHelpPage == 54) {
				$w1 = 1;
				$w2 = 84;
				$w3 = 15;
			} else {
				$w1 = 0;
				$w2 = 64;
				$w3 = 36;				
			}
			$iapHRet = "<fieldset id='".$iapHelpId."'' style='border: 2px double #ae9471; top: 5px; right: 5px; bottom: 5px; left: 5px; padding: 10px; text-align: justify;'>";
			$iapHRet = $iapHRet."<legend><span style='color:#ae9471;'> Walk Me Through Help </span></legend>";
			$iapHRet = $iapHRet.$iapHExp;
			$iapHRet = $iapHRet."</fieldset>";
			break;
		case 4:
			$iapHRet = "<fieldset id='".$iapHelpId."' style='border: 2px double #ae9471; top: 5px; right: 5px; bottom: 5px; left: 5px; padding: 10px; text-align: justify;'>";
			$iapHRet = $iapHRet.$iapHExp;
			$iapHRet = $iapHRet."</fieldset>";
			break;
	}
	return($iapHRet);
}

function IAP_Process_Help($iapIn, $iapVar1, $iapHelpLvl) {

// Welcome to Its a Party a direct sales recordkeeping application from Litehaus Consulting. Since this appears  to be the first time you are using this application, we are going to lead you through setting up your business.[[nl]] You will set up the information about your business and options for use through the application. In this first area of the page we will  set up your business[ap]]s name and address. You'll see other help text as you move into the next area.[[nl]] These help texts will be available throughout the system to help you become acquainted with the application. The extent of help can be changed but for now they are set to 'The Max'.[[nl]] We want to thank you for licensing Its A Party.

//	$_REQUEST['debugme'] = "Y";


// process text
	if ($_REQUEST['debugme'] == "Y") {
		echo "...processing help length = ".strval(strlen($iapIn))." and text = [".$iapIn."].<br /><br />";
	}

	$iapEndProcess = "N";
	while ($iapEndProcess == "N") {
		$i = strpos($iapIn, "[[");

		if ($_REQUEST['debugme'] == "Y") {
			echo "...strpos result is ".strval($i).".<br />";
		}

		if ($i === FALSE) {
			$iapEndProcess = "Y";
// no more tags in text
			if ($_REQUEST['debugme'] == "Y") {
				echo "...found FALSE.<br />";
			}
		} else {
// save beginning part of text
			$iapText = substr($iapIn, 0, $i);

			if ($_REQUEST['debugme'] == "Y") {
				echo "...text before processing tag ".$iapText.".<br />";
			}

// get tag
			$iapTag = substr($iapIn, $i + 2, 2);
			switch ($iapTag) {
				case ">>":
					if ($_REQUEST['debugme'] == "Y") echo "...processing tab.<br />";

					$iapText = $iapText."&nbsp;&nbsp;&nbsp;";
					break;

				case "ap":
					if ($_REQUEST['debugme'] == "Y") echo "...processing apostrophe.<br />";

					$iapText = $iapText."&#39;";
					break;

				case "bd":
					if ($_REQUEST['debugme'] == "Y") echo "...processing bold.<br />";

					$iapText = $iapText."<span style='font-weight:bold;'>";
					break;

				case "el":
					echo "<span class=iapError>HELP GENERATOR ERROR: extraneous [[else]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
					break;

				case "en":
					$e = strpos($iapIn, "]]", $i);
					if ($e === FALSE) {
						$e = $i + 7;
					}
					$l = $e - $i + 2;
					$t = substr($iapIn, $i, $l);
					if ($t == "[[endbd]]") {
						if ($_REQUEST['debugme'] == "Y") echo "...processing end bold.<br />";

						$iapText = $iapText."</span>";
						break;						
					}
					echo "<span class=iapError>HELP GENERATOR ERROR: extraneous ".$t." tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
					break;

				case "if":
					if ($_REQUEST['debugme'] == "Y") echo "...processing if.<br />";

					$t = IAP_Process_if($iapIn, $i, $iapVar1);
					if ($t < 0) {
						switch ($t) {
							case -2:
								echo "<span class=iapError>HELP GENERATOR ERROR: No ]] for [[if]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -3:
								echo "<span class=iapError>HELP GENERATOR ERROR: No [[endif]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -4:
								echo "<span class=iapError>HELP GENERATOR ERROR: If condition improperly formatted in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -5:
								echo "<span class=iapError>HELP GENERATOR ERROR: If condition of UserSecurity has invalid parameter in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -6:
								echo "<span class=iapError>HELP GENERATOR ERROR: No ]] for [[elseif]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
						}
					} else {
						if ($_REQUEST['debugme'] == "Y") {
							echo "...returned ".$t.".<br />";
						}

						$iapText = $iapText.$t;
					}
					$i = strpos($iapIn, "[[endif]]");
					if ($i === FALSE) {
						echo "<span class=iapError>HELP GENERATOR ERROR: No [[endif]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
					}
					$iapTag = "endif"; // for error comment if no ]] found
					break;

				case "li":
					if ($_REQUEST['debugme'] == "Y") echo "...processing li.<br />";

					$t = IAP_Process_Li($iapIn, $i);
					if ($t < 0) {
						switch ($t) {
							case -2:
								echo "<span class=iapError>HELP GENERATOR ERROR: No ]] for [[li]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -3:
								echo "<span class=iapError>HELP GENERATOR ERROR: No [[endli]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
						}
					} else {
						if ($_REQUEST['debugme'] == "Y") {
							echo "...returned ".$t.".<br />";
						}

						$iapText = $iapText.$t;
					}
					$i = strpos($iapIn, "[[endli]]");
					if ($i === FALSE) {
						echo "<span class=iapError>HELP GENERATOR ERROR: No [[endli]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
					}
					$iapTag = "endli"; // for error comment if no ]] found
					break;

				case "nl":
					if ($_REQUEST['debugme'] == "Y") echo "...processing nl.<br />";

					if ($iapHelpLvl == 3) {
						$iapText = $iapText."<br>";
					} else {
						$iapText = $iapText." ";	
					}
					break;

				case "rd":
					if ($_REQUEST['debugme'] == "Y") echo "...processing rd.<br />";

					$iapText = $iapText." ".date("m/d/Y", strtotime($_REQUEST['Expires']))." ";
					break;

				case "qt":
					if ($_REQUEST['debugme'] == "Y") echo "...processing qt.<br />";

					$iapText = $iapText.'&quot;';
					break;

				case "ul":
					if ($_REQUEST['debugme'] == "Y") echo "...processing ul.<br />";

					$t = IAP_Process_UL($iapIn, $i);
					if ($t < 0) {
						switch ($t) {
							case -2:
								echo "<span class=iapError>HELP GENERATOR ERROR: No ]] for [[ul]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
							case -3:
								echo "<span class=iapError>HELP GENERATOR ERROR: No [[endul]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
								break;
						}
					} else {
						if ($_REQUEST['debugme'] == "Y") {
							echo "...returned ".$t.".<br />";
						}

						$iapText = $iapText.$t;
					}
					$i = strpos($iapIn, "[[endul]]");
					if ($i === FALSE) {
						echo "<span class=iapError>HELP GENERATOR ERROR: No [[endul]] termininator tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
					}
					$iapTag = "endul"; // for error comment if no ]] found
					break;

				default:
					echo "<span class=iapError>HELP GENERATOR ERROR: Unknown tag, [[".substr($iapIn, $i + 2, 2)."]], in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
			}
			if ($_REQUEST['debugme'] == "Y") {
				echo "...text after processing tag ".$iapText.".<br />";
			}


			$e = strpos($iapIn, "]]", $i);
			if ($e === FALSE) {
				echo "<span class=iapError>HELP GENERATOR ERROR: No ]] for [[".$iapTag."]] tag in help text for ".$iapAppl."-".$fn."-".$iapPageName."</span><br />";
				$e = $i + 2;
			}
			$i = $e + 2;

			if ($_REQUEST['debugme'] == "Y") {
				echo "...integer is ".strval($i).".<br />";
			}

			$iapText = $iapText.substr($iapIn, $i);

			if ($_REQUEST['debugme'] == "Y") {
				echo "...text done ".$iapText.".<br />";
			}

			$iapIn = $iapText;
		}
	}

	return($iapIn);
}

function IAP_Process_If($iapIn, $iapIf, $iapVar1) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Process_If.<br />";
	}

	$text = substr($iapIn, $iapIf);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...if text is ".$text.".<br />";
	}

// get positions of parameters
	$endcond = strpos($text, "]]");

	if ($_REQUEST['debugme'] == "Y") {
		echo "...endcond = ".$endcond.".<br />";
	}

	if ($endcond === FALSE) {
		return(-2);
	}
	$starttrue = $endcond + 2;

	$endtrue = strpos($text, "[[endif]]");

	if ($_REQUEST['debugme'] == "Y") {
		echo "...endtrue = ".$endtrue.".<br />";
	}

	if ($endtrue === FALSE) {
		return(-3);
	}

	$elseif = strpos($text, "[[elseif");
	if ($elseif === FALSE) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...elseif is False<br />";
		}
	} else {
		$endelseif = strpos($text, "]]", $elseif);
		if ($endelseif === FALSE) {
			return(-6);
		}
	}

	$else = strpos($text, "[[else]]");

	if ($_REQUEST['debugme'] == "Y") {
		echo "...else =<pre>";
		var_dump($else);
		echo "</pre>";
	}

	if ($else === FALSE) {
		if ($elseif === FALSE) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...both else and elsif are False.<br />";
			}

			$elsechk = 99999;  // an unreasonably high number for check against endtrue.
		} else {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...else is False and elseif is not False.<br />";
			}

			$elsechk = $elseif;
		}
	} elseif ($elseif === FALSE) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...else is not False but elseif is False.<br />";
		}

		$elsechk = $else;
	} elseif ($else > $elseif) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...else is not False but else > elseif so use elseif.<br />";
		}

		$else = FALSE;
		$elsechk = $elseif;
	} else {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...elseif is not False but elseif > else so use else.<br />";
		}

		$elseif = FALSE;
		$elsechk = $else;
	}

	if ($elsechk > $endtrue) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...elsechk > endtrue.<br />";
		}

		$else = FALSE;
		$elseif = FALSE;
	} else {
		$endfalse = $endtrue;
		$endtrue = $elsechk;
		$startfalse = $elsechk + 8;
		if ($else === FALSE) {
			$startfalse = $elseif + 6; // point to if in elseif
		}
	}

	$lentrue = $endtrue - $starttrue;
	$lenfalse = $endfalse - $startfalse;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...locations: condition end is ".strval($endcond) .
		"<br />...... true text from ".strval($starttrue)." to ".strval($endtrue)." with a length of ".$lentrue .
		"<br />...... false text from ".strval($startfalse)." to ".strval($endfalse)." with a length of ".$lenfalse .
		".<br />";
	}

	$cond = substr($text, 5, $endcond - 5);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...condition is ".$cond.".<br />";
	}

	$parms = explode("|", $cond);
	if (count($parms) != 2) {
		return(-4);
	}
	$r = FALSE;
	switch (strtolower($parms[0])) {
		case "currentuser":
			$iapCurrentUser = wp_get_current_user();
			$iapCurrentUserId = $iapCurrentUser->ID;
			if ($iapCurrentUserId == $parms[1]) {
				$r = TRUE;
			}
			break;
		case "usersecurity":
// TODO change to wordpress acl
			if ($_REQUEST['runningapp'] == "MAFP") {
				switch (strtolower($parms[1])) {
					case "update":
						if ($_REQUEST['sec_update'] == "Y") {
							$r = TRUE;
						}
						break;
					case "report":
						if ($_REQUEST['sec_report'] == "Y") {
							$r = TRUE;
						}
						break;
					case "view";
						if ($_REQUEST['sec_view'] == "Y") {
							$r = TRUE;
						}
						break;
					case "none":
						if ($_REQUEST['sec_view'] == "N") {
							$r = TRUE;
						}
						break;
					default:
						return(-5);
				}
			}
			break;
		case "var1":
			if ($parms[1] == $iapVar1) {
				$r = TRUE;
			}
	}

	if ($r === TRUE) {
		$usetext = substr($text, $starttrue, $lentrue);
	} else {
		if ($startfalse === FALSE) {
			$usetext = "";
		} else {
			if (!($elseif === FALSE)) {
				$usetext = substr($text, $startfalse, $lenfalse);
			} else {
				$usetext = IAP_Process_If($iapIn, $elseif + 6);
			}
		}
	}
	return($usetext);
}

function IAP_Process_Li($iapIn, $iapLi) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Process_Li.<br />";
	}

	$text = substr($iapIn, $iapLi);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...li text is ".$text.".<br />";
	}

// get positions of parameters
	$endtag = strpos($text, "]]");
	if ($endtag === FALSE) {
		return(-2);
	}
	$startlist = $endtag + 2;

	$endlist = strpos($text, "[[endli]]");
	if ($endlist === FALSE) {
		return(-3);
	}

	$lenlist = $endlist - $startlist;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...locations: list text from ".strval($startlist)." to ".strval($endlist)." with a length of ".$lenlist.".<br />";
	}
	$listtext = substr($text, $startlist, $lenlist);
	$list = explode("|", $listtext);

	$usetext = "<ul>";
	foreach ($list as $l) {
		$usetext = $usetext."<li>".$l."</li>";
	}
	$usetext = $usetext."</ul>";

	return($usetext);
}


function IAP_Process_UL($iapIn, $iapLi) {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Process_UL.<br />";
	}

	$text = substr($iapIn, $iapLi);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...ul text is ".$text.".<br />";
	}

// get positions of parameters
	$endtag = strpos($text, "]]");
	if ($endtag === FALSE) {
		return(-2);
	}
	$starttext = $endtag + 2;

	$endtext = strpos($text, "[[endul]]");
	if ($endtext === FALSE) {
		return(-3);
	}

	$lentext = $endtext - $starttext;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...locations: ul text from ".strval($starttext)." to ".strval($endtext)." with a length of ".$lentext.".<br />";
	}
	$ultext = substr($text, $starttext, $lentext);
	$usetext = "<span style='text-decoration:underline;'>".$ultext."</span>";
	return($usetext);
}

?>