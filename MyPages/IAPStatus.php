<?php

$_REQUEST['ModTrace'][] = basename(__FILE__)."- (".__FUNCTION__."- (".__LINE__.")";

if ($_REQUEST['debugme'] == "Y") {
	echo ">>>In Codes with action of ".$_REQUEST['action']."<br>";
}

if ($_REQUEST['debuginfo'] == "Y") {
	phpinfo(INFO_VARIABLES);
}

require_once(ABSPATH."IAPServices.php");
if (iap_Program_Start("NOCHK") < 0) {
	return;
};
?>

<table style="width:100%;" border="1" cellpadding="2" cellspacing="2">

<tr><td colspan='15' class='iapFormHead'>It's A Party - Direct Sales Recordkeeping</td><td width='10%'></td></tr>
<tr><td colspan='15' class='iapFormHead'>Application Development Status</td><td width='10%'></td></tr>
<tr><td colspan='15' class='iapFormHead'>Last Updated 08/17/2016</td><td width='10%'></td></tr>

<tr style="line-height:2;">
<td style="width:9%;">&nbsp;</td>
<td style="width:20%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr><td style="width:9%;"></td><td colspan="13"><span class="iapFormLabel" style="line-height:normal">
<p>The 'Its A Party - Direct Sales Recordkeeping' application is currently in development. The planned release date of the initial phase is September 2016. Below is the status of the individual modules.</p> 
</span></td><td style="width:11%;">&nbsp;</td></tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;">&nbsp;</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapTH">Module</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">Designed</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">Tested</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">QA</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">Help</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">Deployed</span></td>
<td style="width:5%;">  </td><td style="width:5%;"><span class="iapTH">Ready</span></td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">User Interface:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">N/A</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Help System:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">N/A</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">App Home Page:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Business Info:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">...How Am I Doing:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">50%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">50%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">50%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">0%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">50%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">0%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Client Profile:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Activity Journal:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Catalog Maint:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">...Upload Catalog:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Customer Maint:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">...Upload Customers:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Purchasing:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Party/Event Maint:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Sales:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Calendar:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">100%</td>
<td style="width:11%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:9%;"></td>
<td style="width:20%;"><span class="iapFormLabel">Reporting:</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">25%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">25%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">25%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">0%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">25%</td>
<td style="width:5%;">&nbsp;</td><td style="width:5%;text-align:right;">0%</td>
<td style="width:11%;">&nbsp;</td>
</tr>
</table>
<br><br>
<table style="width:100%;" border="1" cellpadding="2" cellspacing="2">
<tr><td style="width:10%;"></td><td colspan="5"><span class="iapFormLabel" style="line-height:normal">
<p>We are discussing a number of enhancements to be implemented after the initial phase is released and any bugs are eradicated. We will list them here after we agree they are to be included and possibility when they could be implemented.</p> 
</span></td><td style="width:10%;">&nbsp;</td></tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;">&nbsp;</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;"><span class="iapTH">Module</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;"><span class="iapTH">Description</span></td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;"><span class="iapTH">Phase</span></td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Reporting for taxes</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Prepare a report of prior year&apos;s activity for taxes</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 1</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Ability to Refund/Cancel a Sale</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Modify the Sales function to provide a way to refund/cancel a sale.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Complete</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Client&apos;s Data Export</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Ability for clients to export ALL their data.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 1.5</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Gift Certificates</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Generate and track gift certificates</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 2</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Returns</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Provide for entry of returns from you to company and customer to you. Update inventory accordingly.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 2</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Customized Item Categories</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Items are grouped together for statistical purposes. At this time it is a fixed list. With this change the business owner will be able to provide a custom list of catagories.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 3</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Grouped Items</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Some items purchased from the company, such as special, are a group of items that can be sold individually. This feature will allow inventory to be tracked whether the special is sold together or the individual items sold.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 3</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Vendor Interfaces</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;line-height:normal;">Make an agreement with vendors to provide automatic updates to our items when they change their catalog.</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;text-align:right;">Phase 2 or 3</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">Analysis of Sales</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;">Provide statistics concerning events/parties held as well as customers. What would you like to see?</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;">Phase 2</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr style="line-height:2;">
<td style="width:10%;">&nbsp;</td><td style="width:20%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:40%;">&nbsp;</td>
<td style="width:5%;">&nbsp;</td><td style="width:10%;">&nbsp;</td>
<td style="width:10%;">&nbsp;</td>
</tr>

<tr><td style="width:10%;"></td><td colspan="5"><span class="iapFormLabel" style="line-height:normal;text_align:center;font-size:important;">Click on Home on the menu when done viewing.</span></td><td style="width:10%;">&nbsp;</td></tr>
</table>