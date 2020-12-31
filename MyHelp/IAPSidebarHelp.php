<?php

$x = basename($_SERVER['PHP_SELF']);
$y = $_SERVER['PHP_SELF'];
if (basename($_SERVER['PHP_SELF']) == "post"
or basename($_SERVER['PHP_SELF']) == "admin-ajax") {
	return;
}

require_once($_REQUEST['IAPDir']."IAPServices.php");

global $current_user;
get_currentuserinfo();
$iapCurrentUser = (array) $current_user;
$_REQUEST['IAPUID'] = $iapCurrentUser['ID'];
$uid = $iapCurrentUser['ID'];

$hl = IAP_Get_Help_Level();

if ($hl < 0) {
	$iapPgHelpLvl = '3';
}

if (is_array($hl)) {
	$iapPgHelpLvl = $hl['hl_level'];
} elseif ($hl == NULL) {
	$iapPgHelpLvl = 3;
} else {
	$iapPgHelpLvl = $hl;
}
?>
<span class='iapFormInput'>What level of help would you like for this page? If you make a choice, we will use it rather than the one chosen on the "About Me" page.</span>

<img src='<?php echo $_REQUEST['IAPUrl']; ?>/MyHelp/LHCQuestionMark.png' class='tooltip' title='Choosing one of these options overrides your choice on the "About Me" page for this page only. Typical usage is to turn on full help for difficult pages or while learning a page when the standard choice is no help or minimal help. Remember the level of help set here is only for this page. To set the help level for the entire application, see the "About Me" page. Changes here will take affect the next time the page is displayed.'>

<br><br>
<input type='hidden' name='HELPLVL' id='HELPLVL' value='<?php echo $iapPgHelpLvl; ?>'>
<select name='PgHelpLvl' id='PgHelpLvl' onchange='procHelpChoice();'>
<option value='3'
<?php if ($iapPgHelpLvl == 3) { echo " selected"; } ?>
>Walk me through.
</option><option value='2'
<?php if ($iapPgHelpLvl == 2) { echo " selected"; } ?>
>Give Me Hints.
</option><option value='1'
<?php if ($iapPgHelpLvl == 1) { echo " selected"; } ?>
>I'll select what I need.
</option><option value='0'
<?php if ($iapPgHelpLvl == 0) { echo " selected"; } ?>
>I've got this. No help needed.</option>
</select>
<br>
<div id="phl0" style="display:none;">
	<span class=iapFormLabel">Use this level when the usage of the page and its fields are familar and no interruption from help narative is desired.</span>
</div>
<div id="phl1" style="display:none;">
	<span class=iapFormLabel">With this level a help bubble appear next to the label of most fields. Clicking on the bubble shows the help narative.</span>
</div>
<div id="phl2" style="display:none;">
	<span class=iapFormLabel">Helpful hints are shown for more complex areas of a page.</span>
</div>
<div id="phl3" style="display:none;">
	<span class=iapFormLabel">Use of this level will provide help narratives to walk through the usage of the page and its fields.</span>
</div>

<?php
/*
	global $current_user;
	get_currentuserinfo();
	$iapCurrentUser = (array) $current_user;
	$uid = $iapCurrentUser['ID'];
*/
?>


<script type="text/javascript">

function procHelpChoice() {
	var hPage = <?php if (!isset($_REQUEST['page_id'])) {echo "0"; } else {echo $_REQUEST['page_id'];} ?>;
	var hOption = document.getElementById('PgHelpLvl').value;
	phcShowDesc(hOption);
	document.getElementById('PgHelpLvl').value = hOption;
	document.getElementById('HELPLVL').value = hOption;

	var hUserId = <?php echo $uid; ?>;
	var helpRec = hUserId +"|" + hPage + "|" + hOption;
	iapPrepCall("/Ajax/iapPutDB", "H", helpRec, hProcHelp);

// redo page?

}

function hProcHelp(hRec) {
	var a = 1;
}

function phcShowDesc(phcChoice) {
	document.getElementById('phl0').style.display = "none";
	document.getElementById('phl1').style.display = "none";
	document.getElementById('phl2').style.display = "none";
	document.getElementById('phl3').style.display = "none";
	var phcDesc = "phl" + phcChoice.toString();
	document.getElementById(phcDesc).style.display = "block";
}
</script>
