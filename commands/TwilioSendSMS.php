#!/bin/env php
<?php
$skipJSsettings=true;
include_once $_SERVER["FPPDIR"] . "/www/config.php";
include_once $_SERVER["FPPDIR"] . "/www/common.php";
$pluginName = "TwilioControl";

LoadPluginSettings($pluginName);

include_once $_SERVER["MEDIADIR"] . "/plugins/TwilioControl/functions.inc.php";
include_once $_SERVER["MEDIADIR"] . "/plugins/TwilioControl/pluginSettings.inc.php";

sendTSMSMessage($argv[2], $argv[1]);

?>