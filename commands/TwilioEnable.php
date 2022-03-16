#!/usr/bin/env php
<?php
$skipJSsettings=true;
include_once $_SERVER["FPPDIR"] . "/www/config.php";
include_once $_SERVER["FPPDIR"] . "/www/common.php";
$pluginName = "TwilioControl";

WriteSettingToFile("ENABLE", "1", $pluginName);
?>