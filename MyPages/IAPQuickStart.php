<style>
p {
    text-indent: 25px;
}
</style>

<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In QuickStart.<br>";
}

if (!is_user_logged_in ()) {
	echo "You must be logged in to use this app. Please, click Home then Log In!";
	return;
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};

?>
<!--
<table style="width:100%;"><tr><td style="5%"></td><td style="95%">
-->
<table><tr><td style="5%"></td><td style="95%">
<p>Congratulations! You have made it into the It's A Party, IAP, application. IAP is a tool that allows you to record your direct sales business information in the way you desire. To that end there are minimal validity checks.</p>
<p>We have attempted to anticipate the needs of a direct sales business but we have come from a distinct background so there may need to be some adjustments for your particular business. If you recognize such a need, please contact us. You can use email address Support@LitehausConsulting.com. The preferred method, though, is to submit a support ticket under the Support menu item above.</p>
<p>On your first access to the application you were asked to enter your business information. At that time it was mentioned to also complete the About Me page. Now would be a good time complete that information, if you haven't already. This will be your profile data. Rest assured that we will not sell or use that information for any purpose other than to have it on file for your license.</p>
<p>The following are topics designed to help you get started. Click on the topic and the narrative will be displayed.</p>

<a href="#Para001" onclick="ShowMe('001')"><span id='iapPara001' class=IAPLabel>Available Help</span></a><br>
<div id='P001' style="display:none">
<p>As mentioned above, we provide support by clicking on the Support menu item on the top menu. Using this menu you will see a knowledge base, a support ticket and our email address. We cannot provide telephone or chat support at this time. These may be available in the future.</p>
<p>Help is available on each page. How much help is available is controlled by a setting on the About Me page. This will set the help level for all pages. It is possible to change the help level for any one page by changing the value at the bottom of the menu on the left on that page.</p>
The available help levels are:<ul>
	<li><span style="text-decoration: underline;">Walk Me Through</span> - Use of this level will provide help narratives to walk through the usage of the page and its fields.</li>

	<li><span style="text-decoration: underline;">Give Me Hints</span> - More general help will be given at strategic points.</li>
	<li><span style="text-decoration: underline;">I'll Select What I Need</span> - Help bubbles will be shown for more complex fields.</li>
	<li><span style="text-decoration: underline;">No Help Needed Here</span> - No help of any kind will be provided.</li>
</ul>
</div><br>

<a href="#Para002" onclick="ShowMe('002')"><span id='iapPara002' class=IAPLabel>Application Home</span></a><br>
<div id='P002' style="display:none">
When this page is shown the very first time you log in, it will show a minimal of information. As you use the application there are two types of information that will be shown.<br>
Upcoming events. Events are entered on a specific date on the Calendar, either automatically such as customer birthdays and party/events, or manually using the Add Event function. These events will then be listed on this page thirty days before that date. Additionally, there may be a time when we need  to put out a notice, such planned downtime. These will also be shown on this page.
Possible consultants and parties. If a customer indicates to you that they might be interested to become a consultant or have a party, you can edit their customer record and indicate to follow up on them. These follow-ups will be shown on the Application Home page.
<br></div><br>

<a href="#Para003" onclick="ShowMe('003')"><span id='iapPara003' class=IAPLabel>Customers</span></a><br>
<div id='P003' style="display:none">
There are two ways to get your existing customers into this application:
Manually enter them by clicking on the "Customers" menu item and completing the page for each of your customers.
There is an Import Customers under Manage Your Data in the menu. To use this function, create a file in CSV format. Most programs, such as Excel or a Maggie Mail download, can create such a file. The instructions for using this function are available when you click on that function. If you are unsure or uncomfortable doing this, contact us. There may be a small fee for us to do the conversion.
<br></div><br>

<a href="#Para004" id='iapPara004' onclick="ShowMe('004')"><span class=IAPLabel>Catalog</span></a><br>
<div id='P004' style="display:none">
This is a table of the items you buy and sell. There are three ways to get your existing items into the application:
Manually enter them by clicking on the "Catalog" menu item and completing the page for each of item.
There is an Import Catalog under Manage Your Data in the menu. To use this function, create a file in CSV format. Most programs, such as Excel, can create such a file. The instructions for using this function are available when you click on that function. If you are unsure or uncomfortable doing this, contact us. There may be a small fee for us to do the conversion and may take a few days to complete.
Check the Store. We may have an agreement with your supplier to provide their catalog of items in a format that can be imported using the above mentioned Import Catalog function. If you choose to have us import the data, there may be a small fee for us to do the conversion and may take a few days to complete. 
Catalog items do not need to be entered prior to a Purchase or Sale. These pages allow for entry of new items.
<br></div><br>

<a href="#Para005" id='iapPara005' onclick="ShowMe('005')"><span class=IAPLabel>Purchasing</span></a><br>
<div id='P005' style="display:none">
At this time the application does not provide help with the entire ordering process. In a future release it will be possible to set a minimum quantity and an notification will be shown when the inventory reaches that limit.<br>
Use the Purchases page once the items have arrived and are ready to be put into inventory.
<br></div><br>

<a href="#Para006" id='iapPara006' onclick="ShowMe('006')"><span class=IAPLabel>Sales</span></a><br>
<div id='P006' style="display:none">
Parties and Events can be entered as soon as booked so they get added to your calendar. This is done using the Party/Event function.
Sales at that party or event are entered using the Sales Function. The function supports sales at a Party or Event, on Facebook, to an individual, or on a web page such as your consultant page on the Supplier's site. It also allows entry of new Customers, Party/Events, and Catalog Items so they need not be entered using another fuction thus reducing entry time.
<br></div><br>

<a href="#Para007" id='iapPara007' onclick="ShowMe('007')"><span class=IAPLabel>Calendar</span></a><br>
<div id='P007' style="display:none">
The calendar provides a place to put events you do not want to forget. Some events are added automatically depending on your settings.<br>
Birthdays are added to your calendar when a customer is added if you indicated you wanted them added on the My Business page.<br>
Parties and Events are added if you indicate they should be when the party or event is entered.<br>
You can add your own events by selecting the Add An Event sub menu item.<br>
Events can be editted by selecting the Edit An Event sub menu item. To edit an event first click on it to get the event id off the pop up.
<br></div><br>

There are two menus visible in the application.<br>
<a href="#Para010" id='iapPara010' onclick="ShowMe('010')">The first is a horizontal menu just below the site banner. The items on this menu are:</a><br>
<div id='P010' style="display:none">
<ul>
<li><span style="text-decoration: underline;">Home</span> - The opening page shown upon entering the website. This page contains verbiage about the application for those who may be interested in using it. There are a few menu items on the right side of this page. They pertain to one's site log in information.</li>
<li><span style="text-decoration: underline;">Blog</span> - Here is where we will post helpful hints about using the application.</li>
<li><span style="text-decoration: underline;">Store</span> - Access to our application store where licenses and tools can be purchased.</li>
<li><span style="text-decoration: underline;">Support</span> - The Its A Party application is continually being improved to better meet our customers needs. These changes are tested and sent through a quality control process. Unfortunately, it is possible for bugs to find their way into the programming. The Support page gives various options to find help with most incidents. There is also a link to enter a bug report, aka: Help Desk Ticket, if nothing can be found in other sources.</li>
<li><span style="text-decoration: underline;">Log In/Log Out/Register</span> - These items are used to gain access to the application.</li>
<li><span style="text-decoration: underline;">Access It's a Party App</span> - This accesses the application. This item only shows if a person is logged in AND has a license for the application. The license can be expired but in that case only viewing of the data is possible.</li>
</ul>
<br></div><br>

<a href="#Para011" id='iapPara011' onclick="ShowMe('011')">The second menu is a vertical menu on the left side of each page of the application. Items on this menu will change as new functions are made available.</a><br>
<div id='P011' style="display:none">
<ul>
<li><span style="text-decoration: underline;">Application Home</span> - The first page seen when entering the application. more information above.</li>
<li><span style="text-decoration: underline;">Quick Start Guide</span> - This document</li>
<li><span style="text-decoration: underline;">About My Business</span> - Name and contact information for your business.</li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">How Am I Doing</span> - Provides statics to give insights into your business' financial situation</li>
<li><span style="text-decoration: underline;">About Me</span> - Information about you and your preferences.</li>
<li><span style="text-decoration: underline;">Activity Journal</span> - A journal of activities such as purchases and sales.</li>
<li><span style="text-decoration: underline;">Customers</span> - A function to view and maintain customer information.</li>
<li><span style="text-decoration: underline;">Catalog</span> - A catalog of items showing inventory balances, prices and activities.</li>
<li><span style="text-decoration: underline;">Purchases</span> - </li>
<li><span style="text-decoration: underline;">Parties/Events</span> - </li>
<li><span style="text-decoration: underline;">Sales</span> - </li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Sales Receipt</span> - </li>
<li><span style="text-decoration: underline;">Calendar</span> - </li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Add An Event</span> - </li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Edit An Event</span> - </li>
<li><span style="text-decoration: underline;">Manage Your Data</span></li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Import Catalog</span> - </li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Import Customers</span> - </li>
<li>&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;">Export Customers</span> - </li>
</ul>
<br></div><br>

</td></tr></table>
<script type="text/javascript">
$(document).ready

function ShowMe(iapParagraph) {

	document.getElementById("P001").style.display="none";
	document.getElementById("P002").style.display="none";
	document.getElementById("P003").style.display="none";
	document.getElementById("P004").style.display="none";
	document.getElementById("P005").style.display="none";
	document.getElementById("P006").style.display="none";
	document.getElementById("P007").style.display="none";
	document.getElementById("P010").style.display="none";
	document.getElementById("P011").style.display="none";

	document.getElementById("P"+iapParagraph.toString()).style.display="block";
	document.getElementById("iapPara" + iapParagraph.toString()).focus();
}
</script>