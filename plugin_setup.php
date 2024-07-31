<?php

include_once "common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";

$messageQueue_Plugin = findPlugin("MessageQueue");
$MESSAGE_QUEUE_PLUGIN_ENABLED = false;

$Plugin_DBName = $settings['configDirectory'] . "/FPP." . $pluginName . ".db";

$logFile = $settings['logDirectory'] . "/" . $pluginName . ".log";

$messageQueuePluginPath = $settings['pluginDirectory'] . "/" . $messageQueue_Plugin . "/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE", $messageQueue_Plugin));
if (file_exists($messageQueuePluginPath . "functions.inc.php")) {
    include $messageQueuePluginPath . "functions.inc.php";
    $MESSAGE_QUEUE_PLUGIN_ENABLED = true;
    $Plugin_DBName = $settings['configDirectory'] . "/FPP." . $messageQueue_Plugin . ".db";
} else {
    logEntry("Message Queue Plugin not installed, some features will be disabled");
}

$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-TwilioControl";

$Plugin_DBName = $settings['configDirectory'] . "/FPP." . $pluginName . ".db";
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
//create the default tables if they do not exist!
createTwilioTables($db);

?>

<div class='fppTabs'>
    <div id='settingsManager'>
        <ul id='settingsManagerTabs' class='nav nav-pills pageContent-tabs' role='tablist'>
            <li class='nav-item'>
                <a class="nav-link active" id="TwillioSetup-tab" data-bs-toggle="tab" href="#TwillioSetup" data-option="AV" role="tab" aria-controls="TwillioSetup" aria-selected="true">
                    Twilio Configuration
                </a>
            </li>
            <li class='nav-item'>
                <a class="nav-link" id="MatrixSetup-tab" data-bs-toggle="tab" href="#MatrixSetup" data-option="AV" role="tab" aria-controls="MatrixSetup" aria-selected="true">
                    Matrix Setup
                </a>
            </li>
            <li class='nav-item'>
                <a class="nav-link" id="ControlSetup-tab" data-bs-toggle="tab" href="#ControlSetup" data-option="AV" role="tab" aria-controls="ControlSetup" aria-selected="true">
                    Control
                </a>
            </li>
            <li class='nav-item'>
                <a class="nav-link" id="ProfanitySetup-tab" data-bs-toggle="tab" href="#ProfanitySetup" data-option="AV" role="tab" aria-controls="ProfanitySetup" aria-selected="true">
                    Profanity Filter
                </a>
            </li>
            <li class='nav-item'>
                <a class="nav-link" id="ResponsesSetup-tab" data-bs-toggle="tab" href="#ResponsesSetup" data-option="AV" role="tab" aria-controls="ResponsesSetup" aria-selected="true">
                    Responses
                </a>
            </li>
        </ul>
        <div id="settingsManagerTabsContent" class="tab-content">
            <div class="tab-pane fade show active" id="TwillioSetup" role="tabpanel" aria-labelledby="TwillioSetup-tab">
                <?PrintSettingGroup("TwilioSetup", "", "", 1, "TwilioControl");?>
                <div>
                    Web Hooks are the primary mechanism supported by Twilio and provide the best experience for the user.  However, it require some way of forwarding a port
                    from the internet to FPP.  If using Web Hooks, configure Twilio to send messages to <b>http://PUBLICIP:PUBLICPORT/plugin.php?plugin=TwilioControl&page=TSMS.php&nopage=1</b>
                    <p>
                    If forwarding a port is not an option, Polling can be used where FPP will periodically poll the message queue at Twilio and process them in batches.
                    <p>
                </div>
            </div>
            <div class="tab-pane fade" id="MatrixSetup" role="tabpanel" aria-labelledby="MatrixSetup-tab">
                <?PrintSettingGroup("MatrixSetup", "", "", 1, "TwilioControl");?>
            </div>
            <div class="tab-pane fade" id="ControlSetup" role="tabpanel" aria-labelledby="ControlSetup-tab">
                <?PrintSettingGroup("ControlSetup", "", "", 1, "TwilioControl");?>
                <?PrintSettingGroup("ControlCommands", "", "", 1, "TwilioControl");?>
                <ul>
                <li>Configure your whitelist of numbers, and your control number</li>
                <li>Your control numbers, and white list numbers should be comma separated</li>
                <li>Control numbers can send valid commands to be processed</li>
                <li>ALL control numbers will get status commands when including the SMS-STATUS-SEND.FSEQ sequence in a playlist</li>
                <li>The phone numbers for any field need to be in the format of +(countryCode)(number) example USA number 800-555-1212 = +18005551212</li>
                </ul>
            </div>
            <div class="tab-pane fade" id="ProfanitySetup" role="tabpanel" aria-labelledby="ProfanitySetup-tab">
                <?PrintSettingGroup("ProfanitySetup", "", "", 1, "TwilioControl");?>
                <p>
                <ul>
                <li>This plugin allows the use of two profanity checkers. NeutrinoAPI and WebPurify</li>
                <li>Users have reported that Web Purify is much more thurough than Neutrino</li>
                <li>NeutrinoAPI profanity checker located at: <a href="https://www.neutrinoapi.com/api/bad-word-filter/">https://www.neutrinoapi.com/api/bad-word-filter/</a></li>
                <li>WebPurify is located at <a href="http://webpurify.com">http://webpurify.com</a></li>
                <li>You will need to visit their site and generate a userid and API Key</li>
                <li>NOTE: Each have limited checks on FREE accounts</li>
                </ul>
            </div>
            <div class="tab-pane fade" id="ResponsesSetup" role="tabpanel" aria-labelledby="ResponsesSetup-tab">
                <?PrintSettingGroup("ResponsesSetup", "", "", 1, "TwilioControl");?>
            </div>
        </div>
    </div>
</div>


<h4>DISCLAIMER:</h4>
<ul>
<li>The Author and supporters of this plugin are NOT responsible for SMS charges that may be incurred by using this plugin</li>
<li>Check with your mobile provider BEFORE using this to ensure your account status</li>
</ul>


<input class="buttons" id="MessageManagementButton" name="Message Management" type="submit" value="Message Management" onclick="OpenMessageManagementPage()">

<script>
function OpenMessageManagementPage() {
    location.href = "plugin.php?plugin=TwilioControl&page=messageManagement.php";
}
</script>


<p>To report a bug, please file it against the sms Control plugin project on Git:<?echo $gitURL; ?>


