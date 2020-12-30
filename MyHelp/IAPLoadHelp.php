<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__LINE__.")";

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!<br>";
	return;
}

if ($_REQUEST['debugme'] == "Y") { echo ">>>In IAPLoadHelp.<br>"; }

if ($_REQUEST['debuginfo'] == "Y") { phpinfo(INFO_VARIABLES); }

if ($_REQUEST['debugme'] == "Y") { echo ">>>In IAPLoadHelp.<br>"; }

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start('NOCHK', "N") < 0) {
	return;
};

if (!(set_time_limit(800))) {
	echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
}

echo "...Getting file name in directory MyHelp/Narratives.<br>";
$dh  = opendir(ABSPATH."MyHelp/Narratives/");
while (false !== ($filename = readdir($dh))) {
	$fn = explode(".", $filename);
	if ($fn[1] == "hlp"
	and count($fn) == 2) {
		echo "<br>...file name is ".$filename.". Reading file.<br>";
		$recs = file(ABSPATH."MyHelp/Narratives/".$filename, FILE_IGNORE_NEW_LINES);
		$recIndx = 0;
		while($recIndx < count($recs)) {
			if (strtolower(substr($recs[$recIndx], 0, 8)) != "[[key]]=") {	// only process Key records here
//				echo "<span class=iapWarning>--- Skipping ".substr($recs[$recIndx], -1)."---</span><br>";
				$recIndx++;
				continue;
			}

// [[Key]]=[[54]],[[3]],[[1]],[[Application Home]]

			$k = substr($recs[$recIndx], 8);
			$spg = strpos($k, "[[") + 2;		// page 54
			$epg = strpos($k, "]]");
			$slv = strpos($k, "[[", $epg) + 2;	// level 3
			$elv = strpos($k, "]]", $slv);
			$sfn = strpos($k, "[[", $elv) + 2;	// section 1
			$efn = strpos($k, "]]", $sfn);
			$ssc = strpos($k, "[[", $efn) + 2;	// Scope Application Home
			$esc = strpos($k, "]]", $ssc);
			$key['page']  = substr($k, $spg, $epg - $spg);
			$key['level'] = substr($k, $slv, $elv - $slv);
			$key['section'] = substr($k, $sfn, $efn - $sfn);
			$key['scope'] = substr($k, $ssc, $esc - $ssc);

			echo "<br>Processing key of $k with ".
				  "page of ".$key['page']."(".strval($spg)."-".strval($epg).")".
				  ", help level of ".$key['level']."(".strval($slv)."-".strval($elv).")".
				  " and section of ".$key['section']."(".strval($sfn)."-".strval($efn).")".
				  " The scope of this key is ".$key['scope'].
				  "<br>";

			if (!(set_time_limit(420))) {
				echo "<span class=iapError>Execution Time Could Not Be Set. Program May Terminate Abnormally.</span><br><br>";
			}

			$text = "";
			$recIndx++;

			while($recIndx < count($recs)) {
				if (trim($recs[$recIndx]) == "") {								// Bypass blank records
					$recIndx++;
					continue;
				}
				if ((substr($recs[$recIndx], 0, 2)) == "<<") {					// Bypass comments line
					$recIndx++;
					continue;
				}
				if (strtolower(substr($recs[$recIndx], 0, 8)) == "[[key]]=") {	// Beginning of another help text for this page
					break;
				}
//				echo "--- Processing ".substr($recs[$recIndx], 0, -1)."---<br>";	// Concatenate all help text
				$text = $text.$recs[$recIndx];
				$recIndx++;
			}

			echo "...Deleting help text - ".$key['page']."-".$key['level']."-".$key['section']."<br>";
				$HelpRow = array('hn_page'	=> $key['page'],
								 'hn_section' => $key['section'],
								 'hn_level'	=> $key['level']);

// ------------------------------------------------------------ 
			$Ret = IAP_Delete_Row($HelpRow, "iaphnar");
			if ($Ret < 0) {
				echo "<span class=iapError>......could not delete record.</span><br>";
			}
// ------------------------------------------------------------

//			echo "......modifying text<br>";
			$text = str_replace("\n", "", $text);
			$text = str_replace("'", "[[ap]]", $text);
			$text = str_replace('"', "[[qt]]", $text);
			$text = str_replace("<br>", "[[nl]]", $text);
			$text = str_replace("<br />", "[[nl]]", $text);
			$tfld = str_split($text, 500);

//			echo "......processing text of ".$text."<br>";
			foreach($tfld as $tf) {
				echo "...Adding help text - ".$key['page']."-".$key['level']."-".$key['section']."<br>";

// 	hn_page	hn_section	hn_level	hn_seq	hn_text
// 'help_scope' => $key['scope'], no longer used

				$HelpRow = array('hn_page'  => $key['page'],
								 'hn_section' => $key['section'],
								 'hn_level' => $key['level'],
								 'hn_text'  => $tf,
								 'status'  => "NEW");

//				echo "...processing <pre>";
//				var_dump($HelpRow);
//				echo "</pre><br>";

// -----------------------------------------------------------------
				$Ret = IAP_Update_Data($HelpRow, "iaphnar");
				if ($Ret < 0) {
					echo "<span class=iapError>...Could not insert updated record.</span><br>";
				}
// -----------------------------------------------------------------
			}
		}
	}
}

?>