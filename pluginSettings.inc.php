<?php

$DEBUG = ParseBooleanValue($pluginSettings['DEBUG']);
//$CONSOLE_DEBUG = ParseBooleanValue($pluginSettings['CONSOLE_DEBUG']));
// $CONSOLE_DEBUG = true;

$MATRIX_MESSAGE_PLUGIN_NAME = findPlugin("MatrixMessage");
// page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php";

$FORCE_UPPERCASE = ParseBooleanValue($pluginSettings['FORCE_UPPERCASE']);
$PLAYLIST_NAME = isset($pluginSettings['PLAYLIST_NAME']) ? $pluginSettings['PLAYLIST_NAME'] : "";
$WHITELIST_NUMBERS = $pluginSettings['WHITELIST_NUMBERS'];
$CONTROL_NUMBERS = $pluginSettings['CONTROL_NUMBERS'];
$REPLY_TEXT = $pluginSettings['REPLY_TEXT'];
$IMMEDIATE_OUTPUT = ParseBooleanValue($pluginSettings['IMMEDIATE_OUTPUT']);
$MATRIX_LOCATION = $pluginSettings['MATRIX_LOCATION'];
$API_KEY = $pluginSettings['API_KEY'];
$API_USER_ID = $pluginSettings['API_USER_ID'];
$PROFANITY_ENGINE = $pluginSettings['PROFANITY_ENGINE'];
$TSMS_account_sid = $pluginSettings['TSMS_ACCOUNT_SID'];
$TSMS_auth_token = $pluginSettings['TSMS_AUTH_TOKEN'];
$TSMS_phoneNumber = $pluginSettings['TSMS_PHONE_NUMBER'];
$playCommands = $pluginSettings['PLAY_COMMANDS'];
$stopCommands = $pluginSettings['STOP_COMMANDS'];
$repeatCommands = $pluginSettings['REPEAT_COMMANDS'];
$statusCommands = $pluginSettings['STATUS_COMMANDS'];
$REMOTE_FPP_ENABLED = $pluginSettings['REMOTE_FPP_ENABLED'];
$REMOTE_FPP_IP = $pluginSettings['REMOTE_FPP_IP'];
$MATRIX_MODE = $pluginSettings['MATRIX_MODE'];
$NAMES_PRE_TEXT = $pluginSettings['NAMES_PRE_TEXT'];
$MATRIX_ACTIVE = isset($pluginSettings['MATRIX_ACTIVE']) ? ParseBooleanValue($pluginSettings['MATRIX_ACTIVE']) : false;
if ($MATRIX_MODE == "") {
	// default to free text
	$MATRIX_MODE = "FREE";
}

$ENABLED = ParseBooleanValue($pluginSettings['ENABLED']);

$CONTROL_NUMBER_ARRAY = explode(",", $CONTROL_NUMBERS);
$WHITELIST_NUMBER_ARRAY = explode(",", $WHITELIST_NUMBERS);

$PROFANITY_RESPONSE = $pluginSettings['PROFANITY_RESPONSE'];
$PROFANITY_THRESHOLD = $pluginSettings['PROFANITY_THRESHOLD'];
$PROFANITY_LANGUAGE = $pluginSettings['PROFANITY_LANGUAGE'];
$BLACKLIST_RESPONSE = $pluginSettings['BLACKLIST_RESPONSE'];

$SYSTEM_DISABLED_RESPONSE = $pluginSettings['SYSTEM_DISABLED_RESPONSE'];
if (trim($SYSTEM_DISABLED_RESPONSE) == "") {
	$SYSTEM_DISABLED_RESPONSE = "We're sorry, the system is not accepting SMS at this time";
}
if (trim($BLACKLIST_RESPONSE) == "") {
	$BLACKLIST_RESPONSE = "We're sorry, we cannot allow this message to be displayed or you have been placed on our blacklist";
}

?>