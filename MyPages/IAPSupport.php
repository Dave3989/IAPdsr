<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In Support.<br>";
}

require_once(ABSPATH."IAPServices.php");
if (IAP_Program_Start("NOCHK", $iapUpdate = "N", $iapInit = "N", $iapLicChk = "N", $iapSupport = "Y") < 0) {
    return;
};

echo "<table><tr><td width='15%'><span style='font-size:1px;'> </span></td><td width='50%'></td><td width='35%'></td></tr>";
echo "<tr><td colspan='2' class='iapFormHead'>".$_REQUEST['UserData']['DisplayName']." Support Options</td><td width='35%'></td></tr>";
echo "</table>";
?>

<br><br>
<span style="text-decoration: bold">
Problems can be reported through our support ticket system by <a href="?page_id=1552">clicking here.</a></B><br><br>

Check the status of your tickets <a href="?page_id=699">here.</a><br><br><br>

You may also email us at <a href="mailto:support@litehausconsulting.com">Support@LitehausConsulting.com</a></span><br><br>


<br>
Help about using a page in the application can be found on that page. 
There are three levels of help ranging from none to help with most aspects of the page. 
The level of help is controlled by a setting in your profile. 
Your profile can be viewed/editted by using the <span style="text-decoration: italic">About Me</span> link in the application. 
It can also be set for any one page by changing it on that page.
</span>

<!--
<li>You may access our community forum by <a href="<?php echo $_REQUEST['LHCUrl']; ?>MySupport/" target="_blank">clicking here.</a></li><br><br><br>
-->

