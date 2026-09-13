#!/bin/env php
<?php

$skipJSsettings=true;
include_once $_SERVER["FPPDIR"] . "/www/config.php";
include_once $_SERVER["FPPDIR"] . "/www/common.php";
$pluginName = "TwilioControl";

LoadPluginSettings($pluginName);

include_once $_SERVER["MEDIADIR"] . "/plugins/TwilioControl/functions.inc.php";
include_once $_SERVER["MEDIADIR"] . "/plugins/TwilioControl/pluginSettings.inc.php";

use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;


if ($pluginSettings["TSMS_MODE"] == "Polling") {
    // make sure any other TwilioPoll is stopped
    file_put_contents("/tmp/STOPTWILIO", "1");
    sleep(2);
    @unlink("/tmp/STOPTWILIO");
    $interval = intval($pluginSettings["TSMS_POLLING_INTERVAL"]);
    $count = 0;
    $client = new Client($TSMS_account_sid, $TSMS_auth_token);
    //clear the queue
    foreach ($client->messages->read() as $msg) {
        $msg->delete();
    }

    while (true) {
        if (file_exists("/tmp/STOPTWILIO")) {
            exit(0);
        }
        if ($count >= $interval) {
            foreach ($client->messages->stream() as $msg) {
                //echo $msg->direction . " " . $msg->body . " " . $msg->sid . "\n";
                if ($msg->direction == "inbound") {
                    $ch2 = curl_init();

                    $formData = array();
                    $formData["SmsMessageSid"] = $msg->sid;
                    $formData["SmsSid"] = $msg->sid;
                    $formData["MessageSid"] = $msg->sid;
                    $formData["SmsStatus"] = $msg->status;
                    $formData["Body"] = $msg->body;
                    $formData["To"] = $msg->to;
                    $formData["From"] = $msg->from;
                    $formData["ApiVersion"] = $msg->apiVersion;
                    $formData["AccountSid"] = $msg->accountSid;
                    $formData["MessagingServiceSid"] = $msg->messagingServiceSid;
                    $formData["NumMedia"] = $msg->numMedia;
                    
                    // TSMS.php only accepts requests signed with the auth token, so
                    // sign this hand-off exactly the way Twilio signs a webhook
                    $handoffUrl = "http://localhost/plugin.php?plugin=TwilioControl&page=TSMS.php&nopage=1";
                    $validator = new RequestValidator($TSMS_auth_token);
                    curl_setopt($ch2, CURLOPT_HTTPHEADER, array("X-Twilio-Signature: " . $validator->computeSignature($handoffUrl, $formData)));
                    curl_setopt($ch2, CURLOPT_URL, $handoffUrl);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    //curl_setopt($ch2, CURLOPT_WRITEFUNCTION, 'do_nothing');
                    curl_setopt($ch2, CURLOPT_VERBOSE, false);
                    curl_setopt($ch2, CURLOPT_POST, 1);
                    // Edit: prior variable $postFields should be $postfields;
                    curl_setopt($ch2, CURLOPT_POSTFIELDS, $formData);
                    curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
                    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
                    $result2 = curl_exec($ch2);
                }
                $msg->delete();
            }

            $count = 0;
        }
        $count++;
        sleep(1);
    }
}
?>
