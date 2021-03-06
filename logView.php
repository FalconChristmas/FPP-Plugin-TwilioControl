<?php


//Version 1 for release
$pluginName ="TwilioControl";
$myPid = getmypid();


//MATRIX ACTIVE - true / false to catch more messages if they arrive
$MATRIX_ACTIVE = false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");
include_once("profanity.inc.php");

// this line loads the library
//require('Twilio/Services/Twilio.php');
require ('Twilio/autoload.php');

$messageQueue_Plugin = findPlugin("MessageQueue");
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

$profanityMessageQueueFile = $settings['configDirectory']."/plugin.".$pluginName.".ProfanityQueue";

$blacklistFile = $settings['configDirectory']."/plugin.".$pluginName.".Blacklist";

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}



$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	$logFile = $settings['logDirectory']."/".$pluginName.".log";
	$DEBUG=urldecode($pluginSettings['DEBUG']);


	$MATRIX_MESSAGE_PLUGIN_NAME = findPlugin("MatrixMessage");
	//page name to run the matrix code to output to matrix (remote or local);
	$MATRIX_EXEC_PAGE_NAME = "matrix.php";

	$PLAYLIST_NAME = urldecode($pluginSettings['PLAYLIST_NAME']);
	$WHITELIST_NUMBERS = urldecode($pluginSettings['WHITELIST_NUMBERS']);
	$CONTROL_NUMBERS = urldecode($pluginSettings['CONTROL_NUMBERS']);
	$REPLY_TEXT = urldecode($pluginSettings['REPLY_TEXT']);
	$VALID_COMMANDS = urldecode($pluginSettings['VALID_COMMANDS']);
	$IMMEDIATE_OUTPUT = urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
	$MATRIX_LOCATION = urldecode($pluginSettings['MATRIX_LOCATION']);
	$API_KEY = urldecode($pluginSettings['API_KEY']);
	$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
	$PROFANITY_ENGINE = urldecode($pluginSettings['PROFANITY_ENGINE']);

	$TSMS_account_sid = urldecode($pluginSettings['TSMS_ACCOUNT_SID']);
	$TSMS_auth_token = urldecode($pluginSettings['TSMS_AUTH_TOKEN']);
	$TSMS_phoneNumber = urldecode($pluginSettings['TSMS_PHONE_NUMBER']);

	$playCommands = urldecode($pluginSettings['PLAY_COMMANDS']);
	$stopCommands = urldecode($pluginSettings['STOP_COMMANDS']);
	$repeatCommands = urldecode($pluginSettings['REPEAT_COMMANDS']);
	$statusCommands = urldecode($pluginSettings['STATUS_COMMANDS']);

	$REMOTE_FPP_ENABLED = urldecode($pluginSettings['REMOTE_FPP_ENABLED']);
	$REMOTE_FPP_IP = urldecode($pluginSettings['REMOTE_FPP_IP']);

	$MATRIX_MODE = urldecode($pluginSettings['MATRIX_MODE']);

	$NAMES_PRE_TEXT = urldecode($pluginSettings['NAMES_PRE_TEXT']);

	$MATRIX_ACTIVE = urldecode($pluginSettings['MATRIX_ACTIVE']);
	if($MATRIX_MODE == "") {
		//default to free text
		$MATRIX_MODE = "FREE";
	}


	$ENABLED = urldecode($pluginSettings['ENABLED']);
	//$COMMAND_ARRAY = explode(",",trim(strtoupper($VALID_COMMANDS)));

	$CONTROL_NUMBER_ARRAY = explode(",",$CONTROL_NUMBERS);


	$WHITELIST_NUMBER_ARRAY = explode(",",$WHITELIST_NUMBERS);


	$PROFANITY_RESPONSE = urldecode($pluginSettings['PROFANITY_RESPONSE']);

	$PROFANITY_THRESHOLD =urldecode($pluginSettings['PROFANITY_THRESHOLD']);

	$TSMS_from = "";
	$TSMS_body = "";
	$TSMS_BODY_CONTAINED_HEX = false;
	?>
	<html>
	<head>
	<script type="text/javascript">
	function createRequestObject() {
	
		var req;
	
		if(window.XMLHttpRequest){
			// Firefox, Safari, Opera...
			req = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			// Internet Explorer 5+
			req = new ActiveXObject("Microsoft.XMLHTTP");
		} else {
			// There is an error creating the object,
			// just as an old browser is being used.
			alert('There was a problem creating the XMLHttpRequest object');
		}
	
		return req;
	
	}
	
	// Make the XMLHttpRequest object
	var http = createRequestObject();
	
	function sendRequest() {
	
		// Open PHP script for requests
		http.open('get', 'viewlog.php');
		http.onreadystatechange = handleResponse;
		http.send(null);
	
	}
	
	function handleResponse() {
	
		if(http.readyState == 4 && http.status == 200){
	
			// Text returned FROM PHP script
			var response = http.responseText;
	
			if(response) {
				// UPDATE ajaxTest content
				document.getElementById("log").innerHTML = response;
				setTimeout(update,1000);
			}
	
		}
	}
	
	function update() {
		sendRequest();
	}
	</script>
	</head>
	<body onLoad="sendRequest()" />
	<pre>
	<span id="log" name="log"></span>
	</pre>
	</body>
	</html>
