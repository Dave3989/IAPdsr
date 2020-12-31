<?php


if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In IAPStore ";
    if (isset($_REQUEST['applinfo'])) {
        echo " with applinfo of ".$_REQUEST['applinfo'];
    }
    echo ".<br />";
}

global $current_user;
get_currentuserinfo();
$iapCurrentUser = (array) $current_user;
$_REQUEST['UserData']['Id'] = $iapCurrentUser['ID'];
$_REQUEST['IAPUID'] = $iapCurrentUser['ID'];

require_once(ABSPATH."IAPServices.php");
//if (IAP_Program_Start("NOCHK", "N", "Y", "N") < 0) {
//	return;
//};

$iapReadOnly = IAP_Format_Heading("It's A Party Store");

if (!isset($_REQUEST['action'])) {
?>
<span style="text-indent:25em; font_size:110%;">
From this page it is possible to:<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Access my wife&apos;s inventory<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Here you can see the on-hand inventory of <span style='font-weight:bold; font-style:italic;'>retired</span> jewelry purchased from Magnabilities.<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Click this button to access her <button class=iapButton name=storeInv onclick="location.href = '?page_id=274&action=register';"><span style='font-style:italic;'>Inventory Page</span></button>
		<br><br>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For Independant Direct Sales Consultants:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Register for a license to access the <span style='font-style:italic;'>It's A Party</span> application.<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Renew an existing annual license to the <span style='font-style:italic;'>It's A Party</span> application.
<br><br>
<p>The <span style='font-style:italic;'>It's A Party</span> application is design for use by independant consultants of a direct sales company. It provides most of the service needed to manage your business.</p>
<p>Signing up for this application provides a free 60-day license. You are provided with full access to all features of the application so, as a new client, you can 'kick the tires' and set up your company data. We are available to assist with uploading the company data. This trial cannot be extended.</p>
<p>An annual license will be required once the trial is over. The cost of the annual license is $35.00*<br>
&nbsp;&nbsp;&nbsp;*Infrastructure (web site) costs may require us to adjust the annual license fee.</p>

<?php
	if (!is_user_logged_in ()) {
?>
		<span class=iapWarning>
		You must be logged in to continue. Please, click Log In or Register on the menu to enable us to help you further.<br><br> Once signed in return to the Store.</span>
<?php
		return;
}
?>
	The application is design for use by independant consultants of a direct sales company. It is not a <br>
	replacement for that company's product ordering process or consultant services.<br><br>
	Choose what you would like to do:&nbsp;
	<button class=iapButton name=storeNew onclick="location.href = '?page_id=274&action=register';">Register For A New License</button>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<button class=iapButton name=storeUpdate onclick="location.href = '?page_id=274&action=renew';">Renew/Extend A License</button>
	<br><br>
	You can choose another action from the menu above.
	</span>

<?php
	return;

} elseif ($_REQUEST['action'] == "register") {
	
	$iapCo = IAP_Get_CoUser();
	if ($iapCo < 0) {
	    echo "<span class=iapError>iap INTERNAL ERROR: Accessing the Company/User table. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br>";
	    return;
	}
	$iapCompany = $iapCo[0];
	if ($iapCompany['status'] != "NEW") {
		echo "<span class='iapError'>Our records show that you have already registered ".$iapCompany['co_name']." with us.";
		if ($iapCompany['co_license_renewal'] < date("Y-m-d")) {
			echo "<br><br>While your license has expired, you can still renew that license.";
		} else {
			echo "<br><br>Only one company can be registered per log in id.";
		}
		echo "<br>If you are trying to register another company, you can:";
		echo "<br>&nbsp;&nbsp;&nbsp;1) Add another supplier to your existing company using 'About My Company' in the app.";
		echo "<br>&nbsp;&nbsp;&nbsp;2) Register another log in from the Home page by logging out the click Register.";
		echo "<br><br>Either way, I am now going to send you to the 'Renewal' page.</span>";
		require_once("MyLicensing/IAPStoreRenew.php");
		return;
	}
?>
	We are glad you would like to use our application. We offer an annually renewable license<br>
	after a 2 month trial. During the trial we can assist in uploading your existing data.<br><br>
	First, We need you to agree to a few stipulatons.<br>
	1) The application you will be using is copyright and the private property of Litehaus Consulting.<br>
	&nbsp;&nbsp;&nbsp;You must agree that neither you nor anyone connected with you will attempt to copy the pages or programs.<br><br>
	2) This license is granted to you for use with YOUR business only. At your request, we can add other people to use your<br>
	&nbsp;&nbsp;&nbsp;business' data. If you are a consultant for more than one direct sales company, you can track all of that<br>
	&nbsp;&nbsp;&nbsp;activity under this license. However, you may not allow others to track their business activity using<br>
	&nbsp;&nbsp;&nbsp;your license.<br><br>
	3) The data you enter belongs to <span="text-decoration:underline;">you</span>. We will not use any of it except to help you use the application.<br>
	&nbsp;&nbsp;&nbsp;If at any time you choose to stop using the application, we will provide you with a copy of your<br>
	&nbsp;&nbsp;&nbsp;data and a description of the format so you can may import it into another application.<br><br>
	4) We believe strongly in customer service and will endeavor to provide assistance in a timely manner.<br> 
	&nbsp;&nbsp;&nbsp;This includes bug fixes. Timely in this case means allowing time to adequately test changes so as not<br>
	&nbsp;&nbsp;&nbsp;to introduce additional bugs.<br><br>
	5) At times we may need to take the application out-of-service for maintenance. We will notify you in advance of these planned<br>
	&nbsp;&nbsp;&nbsp;outages. We will limit those times and their duration.<br><br>
	7) While we make every attempt to make sure the application is error free, bugs do creep in. We would appreciate being notified<br>
	&nbsp;&nbsp;&nbsp;by you if you discover something not working as it should. You can use the links under the Support menu.<br>
	&nbsp;&nbsp;&nbsp;Please try to describe what you were doing when the error was discovered.<br><br> 
	8) We will attempt to make agreements with direct sales companies to be able to supply our customers with a full set<br>
	&nbsp;&nbsp;&nbsp;of items and keep those items updated to minimize your data entry. This may require you to contact your company<br>
	&nbsp;&nbsp;&nbsp;on our behalf. This would be to your benefit.<br><br>

	Do you agree to these terms?
	<button class=iapButton name=storeAgree onclick="location.href = '?page_id=274&action=licagree';">I Agree</button>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<button class=iapButton name=storeDisagree onclick="location.href = '<?php echo $_REQUEST['IAPUrl']; ?>'">I Disagree</button>
	<br><br>
<?php
	return;
} elseif ($_REQUEST['action'] == "licagree") {
	require_once($_REQUEST['IAPPath']."MyLicensing/IAPStoreTrial.php");
	return;

} elseif ($_REQUEST['action'] == "trialret") {
	require_once("MyLicensing/IAPStoreTrial.php");
	return;

} elseif ($_REQUEST['action'] == "renew") {
	require_once("MyLicensing/IAPStoreRenew.php");
	return;

} else {

}
return;
?>

<!--
.iapFormHead {
  font-size: 1.2em;
  font-weight: bold;
  font-style: italic;
  text-align: center;
}
.iapFormTitle {
  font-size: 1em;
  font-weight: bold;
}
.iapFormLabel {
  font-size: 1em;
  line-height: 2;
}
.iapFormInput {
  font-size: 1em;
}
.iapFormButton {
  height: 2em;
  width: 10em;
  font-size: 1.8em;
  font-weight: bold;
  text-align: center;
  vertical-align: middle;
}
.iapButton {
  font-size: 90%;
  font-weight: bold;
  line-height: 1.5; 
  padding: 3px 10px;
  text-align: center;
}
-->