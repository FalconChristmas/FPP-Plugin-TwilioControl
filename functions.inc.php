<?php

function findPlugin($plugin)
{
    if (is_dir("/home/fpp/media/plugins/FPP-Plugin-" . $plugin)) {
        return "FPP-Plugin-" . $plugin;
    }
    if (is_dir("/home/fpp/media/plugins/" . $plugin)) {
        return $plugin;
    }
    if ($plugin == "MatrixMessage") {
        return findPlugin("Matrix-Message");
    }
    echo "Plugin not found: " . $plugin . "\n";
    return $plugin;
}

function mkTimestamp($year, $month, $day, $hours = 0, $minutes = 0, $seconds = 0)
{
    // Same as mktime() but parameters are in most significant to least significant order.
    return mktime($hours, $minutes, $seconds, $month, $day, $year);
}

//create database
function createTwilioTables($db)
{
    //global $db;
    global $DEBUG;

    $createQuery = "CREATE TABLE IF NOT EXISTS profanity (messageID INTEGER PRIMARY KEY AUTOINCREMENT, timestamp int(16) NOT NULL, message varchar(255), pluginName varchar(64), pluginData varchar(64));";
    logEntry("TWILIO: CREATING Messages Table for TWILIO: " . $createQuery);
    $db->exec($createQuery) or die('Create Table Failed');
    $createQuery = "CREATE TABLE IF NOT EXISTS blacklist (messageID INTEGER PRIMARY KEY AUTOINCREMENT, timestamp int(16) NOT NULL, message varchar(255), pluginName varchar(64), pluginData varchar(64));";
    logEntry("TWILIO: CREATING Messages Table for TWILIO: " . $createQuery);
    $db->exec($createQuery) or die('Create Table Failed');
    $createQuery = "CREATE TABLE IF NOT EXISTS messages (messageID INTEGER PRIMARY KEY AUTOINCREMENT, timestamp int(16) NOT NULL, message varchar(255), pluginName varchar(64), pluginData varchar(64));";
    logEntry("TWILIO: CREATING Messages Table for TWILIO: " . $createQuery);
    $db->exec($createQuery) or die('Create Table Failed');
}

function insertTwilioMessage($message, $pluginName, $pluginData)
{
    global $db;
    $messagesTable = "messages";

    $insertQuery = "INSERT INTO " . $messagesTable . " (timestamp, message, pluginName, pluginData) VALUES ('" . time() . "','" . urlencode($message) . "','" . $pluginName . "','" . $pluginData . "');";
    logEntry("TWILIO: INSERT query string: " . $insertQuery);
    $db->exec($insertQuery) or die('could not insert into database');
}
function insertBlacklistMessage($message, $pluginName, $pluginData)
{
    global $db;
    $blackListTable = "blacklist";

    $insertQuery = "INSERT INTO " . $blackListTable . " (timestamp, message, pluginName, pluginData) VALUES ('" . time() . "','" . urlencode($message) . "','" . $pluginName . "','" . $pluginData . "');";

    logEntry("TWILIO: INSERT query string: " . $insertQuery);
    $db->exec($insertQuery) or die('could not insert into database');
}
function insertProfanityMessage($message, $pluginName, $pluginData)
{
    global $db;
    $profanityListTable = "profanity";

    $insertQuery = "INSERT INTO " . $profanityListTable . " (timestamp, message, pluginName, pluginData) VALUES ('" . time() . "','" . urlencode($message) . "','" . $pluginName . "','" . $pluginData . "');";

    logEntry("TWILIO: INSERT query string: " . $insertQuery);
    $db->exec($insertQuery) or die('could not insert into database');
}

//check if the user is in the blacklist
function checkBlacklist($fromNumber)
{
    global $db, $DEBUG;

    $blackListTable = "blacklist";
    $blackListQuery = "SELECT * FROM " . $blackListTable . " WHERE pluginData = '" . $fromNumber . "'";
    if ($DEBUG) {
        logEntry("TWILIO: Blacklist query: " . $blackListQuery);
    }

    $result = $db->query($blackListQuery) or die('Query failed');
    while ($row = $result->fetchArray()) {
        //TODO: return
        $blackListDate = $row['timestamp'];
        return $blackListDate;
    }
    return null;
}

//check how many profanitys for a number in profanity queue
function checkProfanityCount($numberToCheck)
{
    global $db, $profanityMessageQueueFile, $DEBUG;

    $blacklist = false;
    $profanityCount = 0;

    if ($DEBUG) {
        logEntry("TWILIO: Inside Checking profanity number: " . $numberToCheck);
    }

    $profanityListTable = "profanity";

    $profanityQuery = "SELECT COUNT(*) FROM " . $profanityListTable . " WHERE pluginData = '" . $numberToCheck . "'";
    logEntry("TWILIO: Profanity search count query: " . $profanityQuery);

    $profanityCheckCountResult = $db->querySingle($profanityQuery) or die('Query failed');
    logEntry("TWILIO: Profanity check counter: " . $profanityCheckCountResult);

    return $profanityCheckCountResult;
}
//check to see if a number is blacklisted
function checkBlacklistNumber($numberToCheck)
{
    global $blacklistFile, $DEBUG, $db;

    $blacklist = false;
    if ($DEBUG) {
        logEntry("Inside Checking blacklist number: " . $numberToCheck);
    }
    $result = $db->query('SELECT count(*) FROM blacklist where pluginData =\'".$numberToCheck."') or die('Query failed');
    if ($result > 0) {
        return true;
    } else {
        return false;
    }
}

//send a TSMS message https post
function sendTSMSMessage($messageText, $toNumber = "")
{
    global $DEBUG, $TSMS_BODY_CONTAINED_HEX, $TSMS_phoneNumber, $TSMS_from, $TSMS_body, $TSMS_account_sid, $TSMS_auth_token;
    if ($TSMS_BODY_CONTAINED_HEX) {
        $messageText .= " However: we removed any emoticons or non text characters";
    }

    if ($DEBUG) {
        logEntry("Inside sendTSMSMessage");
    }

    $TSMS_URL = "https://api.twilio.com/2010-04-01/Accounts/" . $TSMS_account_sid . "/Messages.json";

    if ($toNumber == "") {
        $toNumber = $TSMS_from;
    }
    $postfields = array(
        'To' => $toNumber,
        'From' => $TSMS_phoneNumber,
        'Body' => $messageText,
    );

    $ch2 = curl_init();

    curl_setopt($ch2, CURLOPT_USERPWD, "" . $TSMS_account_sid . ":" . $TSMS_auth_token);
    curl_setopt($ch2, CURLOPT_URL, $TSMS_URL);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch2, CURLOPT_WRITEFUNCTION, 'do_nothing');
    curl_setopt($ch2, CURLOPT_VERBOSE, false);
    curl_setopt($ch2, CURLOPT_POST, 1);
    // Edit: prior variable $postFields should be $postfields;
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $postfields);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
    $result2 = curl_exec($ch2);

    if ($DEBUG) {
        logEntry("TSMS Curl result: " . $result2);
    }
    if ($DEBUG) {
        logEntry("exiting sending TSMS Message");
    }
    return;
}
//strip hex characters from message - possible emoticons

function stripHexChars($line)
{
    return preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $line);
}

function processSMSMessage($from, $messageText, $messageFile = "")
{
    global $pluginName, $MESSAGE_QUEUE_PLUGIN_ENABLED, $MATRIX_MODE, $NAMES_PRE_TEXT, $messageQueueFile;

    if ($messageFile == "") {
        $messageFile = $messageQueueFile;
    }
    switch ($MATRIX_MODE) {
        case "FREE":
            //do nothing
            break;
        case "NAMES":
            $messageText = $NAMES_PRE_TEXT . " " . $messageText;
            break;
        default:
            break;
    }

    logEntry("TWILIO: Adding message from: " . $from . ": " . $messageText . " to Twillio message queue");
    insertTwilioMessage($messageText, $pluginName, $from);
    return;
}

// this line loads the library
// require('Twilio/Services/Twilio.php');
require 'Twilio/autoload.php';
use Twilio\Rest\Client;

// Send an FPP command to the local or remote FPP HTTP API. Replaces the
// removed `fpp -P/-p/-c` CLI and the removed fppxml.php endpoints (FPP10).
// FPP parses command args as strings, so bool args are passed as "true"/"false".
function runFPPCommand($command, $args = array(), $host = "localhost")
{
    $payload = json_encode(array("command" => $command, "args" => $args));
    $url = "http://" . $host . "/api/command";
    logEntry("FPP API command -> " . $url . " : " . $payload);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($payload)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        logEntry("FPP API command error: " . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

//process the SMS command coming in from a control number
function processSMSCommand($from, $SMSCommand = "", $playlistName = "")
{
    global $DEBUG, $SMS_TYPE, $TSMS_phoneNumber, $REMOTE_FPP_ENABLED, $REMOTE_FPP_IP, $TSMS_account_sid, $TSMS_auth_token;
    $PLAYLIST_NAME = trim($playlistName);

    logEntry("Processing command: " . $SMSCommand . " for playlist: " . $PLAYLIST_NAME);

    $FPPDStatus = isFPPDRunning();
    logEntry("FPPD status: " . $FPPDStatus);
    if ($FPPDStatus != "RUNNING") {
        logEntry("FPPD NOT RUNNING: Sending message to : " . $from . " that FPPD status: " . $FPPDStatus);
        //send a message that the daemon is not running and cannot execute the command
        $client = new Client($TSMS_account_sid, $TSMS_auth_token);
        $client->messages->create($from, ['from' => $TSMS_phoneNumber, 'body' => "FPPD is not running, cannot execute cmd"]);
        return;
    }

    // Local and remote FPP are both driven through the HTTP command API;
    // only the target host differs.
    $host = $REMOTE_FPP_ENABLED ? trim($REMOTE_FPP_IP) : "localhost";
    if ($REMOTE_FPP_ENABLED) {
        logEntry("Remote FPP Command ENABLED -> " . $host);
    }

    switch (trim(strtoupper($SMSCommand))) {

        case "PLAY":
            runFPPCommand("Start Playlist", array($PLAYLIST_NAME, "false"), $host);
            break;

        case "STOP":
            runFPPCommand("Stop Now", array(), $host);
            break;

        case "REPEAT":
            runFPPCommand("Start Playlist", array($PLAYLIST_NAME, "true"), $host);
            break;

        case "STATUS":
            $playlistName = getRunningPlaylist($host);
            if ($playlistName == null) {
                $playlistName = " No current playlist active or FPPD starting, please try your command again in a few";
            }
            logEntry("Sending SMS to : " . $from . " playlist: " . $playlistName);
            $client = new Client($TSMS_account_sid, $TSMS_auth_token);
            $client->messages->create($from, [ 'from' => $TSMS_phoneNumber, 'body' => "Playlist STATUS: " . $playlistName]);
            break;

        default:
            logEntry("Unknown SMS command: " . $SMSCommand);
            break;
    }
}

//is fppd running?????
function isFPPDRunning()
{
    $FPPDStatus = null;
    logEntry("Checking to see if fpp is running...");
    exec("if ps cax | grep -i fppd; then echo \"True\"; else echo \"False\"; fi", $output);

    if ($output[1] == "True" || $output[1] == 1 || $output[1] == "1") {
        $FPPDStatus = "RUNNING";
    }
    //print_r($output);

    return $FPPDStatus;
    //interate over the results and see if avahi is running?

}
//get current running playlist via the FPP status API (local or remote).
//Replaces the removed /tmp/FPP.playlist status file (FPP10).
function getRunningPlaylist($host = "localhost")
{
    $playlistName = null;

    $statusJson = @file_get_contents("http://" . $host . "/api/fppd/status");
    if ($statusJson === false) {
        logEntry("Could not read FPP status from http://" . $host . "/api/fppd/status");
        return null;
    }
    $status = json_decode($statusJson, true);

    // current_playlist.playlist holds the active playlist name (empty when idle).
    if (isset($status['current_playlist']['playlist']) && $status['current_playlist']['playlist'] != "") {
        $playlistName = $status['current_playlist']['playlist'];
        logEntry("We are playing a playlist...: " . $playlistName);
    } else {
        logEntry("No active playlist (status: " . (isset($status['status_name']) ? $status['status_name'] : "unknown") . ")");
    }

    return $playlistName;
}

function processSequenceName($sequenceName, $sequenceAction = "NONE RECEIVED")
{
    global $CONTROL_NUMBER_ARRAY, $PLAYLIST_NAME, $EMAIL, $PASSWORD, $pluginDirectory, $pluginName;
    logEntry("Sequence name: " . $sequenceName);

    $sequenceName = strtoupper($sequenceName);
    //$PLAYLIST_NAME= getRunningPlaylist();

    if ($PLAYLIST_NAME == null) {
        $PLAYLIST_NAME = "FPPD Did not return a playlist name in time, please try again later";
    }
//        switch ($sequenceName) {

    //               case "SMS-STATUS-SEND.FSEQ":

    $messageToSend = "";
    //    $gv = new GoogleVoice($EMAIL, $PASSWORD);

    //send a message to all numbers in control array and then delete them from new messages
    for ($i = 0; $i <= count($CONTROL_NUMBER_ARRAY) - 1; $i++) {
        logEntry("Sending message to : " . $CONTROL_NUMBER_ARRAY[$i] . " that playlist: " . $PLAYLIST_NAME . " is ACTION:" . $sequenceAction);
        //get the current running playlist name! :)

        //$gv->sendSMS($CONTROL_NUMBER_ARRAY[$i], "PLAYLIST EVENT: ".$PLAYLIST_NAME." Action: ".$sequenceAction);
        //    $gv->sendSMS($CONTROL_NUMBER_ARRAY[$i], "PLAYLIST EVENT: Action: ".$sequenceAction);

    }
    // Removed: exec of obsolete getSMS.php. The playlist-event SMS
    // notification this backed was already disabled (loop commented out)
    // and getSMS.php no longer exists; sequence/playlist callbacks log only.
}

//process read/sent messages
function logEntry($data)
{
    global $logFile, $myPid;

    $sourceFile = $_SERVER['PHP_SELF'];
    $data = $sourceFile . " : [" . $myPid . "] " . $data;
    $logWrite = fopen($logFile, "a") or die("Unable to open file!");
    fwrite($logWrite, date('Y-m-d h:i:s A', time()) . ": " . $data . "\n");
    fclose($logWrite);
}

function processCallback($argv)
{
    global $DEBUG, $pluginName;

    if ($DEBUG) {
        print_r($argv);
    }

    //argv0 = program
    //argv2 should equal our registration // need to process all the rgistrations we may have, array??
    //argv3 should be --data
    //argv4 should be json data

    $registrationType = $argv[2];
    $data = $argv[4];

    logEntry("PROCESSING CALLBACK: " . $registrationType);
    $clearMessage = false;

    switch ($registrationType) {
        case "media":
            if ($argv[3] == "--data") {
                $data = trim($data);
                logEntry("DATA: " . $data);
                $obj = json_decode($data);

                $type = $obj->{'type'};
                logEntry("Type: " . $type);
                switch ($type) {
                    case "sequence":
                        logEntry("media sequence name received: ");
                        processSequenceName($obj->{'Sequence'}, "STATUS");
                        break;
                    case "media":
                        logEntry("We do not support type media at this time");
                        //$songTitle = $obj->{'title'};
                        //$songArtist = $obj->{'artist'};
                        //sendMessage($songTitle, $songArtist);
                        //exit(0);
                        break;
                    case "both":
                        logEntry("We do not support type media/both at this time");
                        //    logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
                        //    $songTitle = $obj->{'title'};
                        //    $songArtist = $obj->{'artist'};
                        //    if ($songArtist != "") {
                        //    sendMessage($songTitle, $songArtist);
                        //exit(0);
                        break;
                    default:
                        logEntry("We do not understand: type: " . $obj->{'type'} . " at this time");
                        exit(0);
                        break;
                }
            }
            break;
            exit(0);
        case "playlist":
            logEntry("playlist type received");
            if ($argv[3] == "--data") {
                $data = trim($data);
                logEntry("DATA: " . $data);
                $obj = json_decode($data);
                $sequenceName = $obj->{'sequence0'}->{'Sequence'};
                $sequenceAction = $obj->{'Action'};
                processSequenceName($sequenceName, $sequenceAction);
                //logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
                //      logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
            }
            break;
            exit(0);
        default:
            exit(0);
    }
}
