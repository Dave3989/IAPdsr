<?php
if ($_REQUEST['debuginfo'] == "Y") {
    phpinfo(INFO_VARIABLES);
}

if ($_REQUEST['debugme'] == "Y") {
    echo ">>>In GenInfo.<br />";
}

$a = 1;

?>
<span style="font-size:115%"><center><h2>Thank you for your interest in the<br><br>
<b><u>It's A Party for Direct Sales Reporting</u></b> Application.</h2><br><br>
This application, a service of Litehaus Consulting, provides tracking functions for<br>
consultants of direct sales companies such as Magnabilities, Jamberry, Paparazzi <br>
Accessories, etc.<br><br>
You can find more information about the application and register for a free 60-day <br>license to use the application at 
<?php
if ($_SERVER["HTTP_HOST"] == "localhost:8080") {
    echo "<a href='http://localhost:8080/LitehausConsulting/IAP'>";
} else {
    echo "<a href='http://IAPDSR.com'>";
}
?>
IAPDSR.Com</a>.</span><br><br>
</center>
