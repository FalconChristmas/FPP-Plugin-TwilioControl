<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TwilioMessages.csv');
$pluginName = "TwilioControl";
include_once "common.php";
include_once 'functions.inc.php';

$messageQueue_Plugin = findPlugin("MessageQueue");
$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE", $messageQueue_Plugin));
$MESSAGE_QUEUE_PLUGIN_ENABLED = file_exists($settings['pluginDirectory'] . "/" . $messageQueue_Plugin . "/functions.inc.php");
$Plugin_DBName = twilioMessageDatabasePath($MESSAGE_QUEUE_PLUGIN_ENABLED, $messageQueueFile);

$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');
$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."'  ORDER BY timestamp DESC";
$messagesResult = $db->query($messagesQuery) or die('Query failed');
//loop over the rows, outputting them
while ($row = $messagesResult->fetchArray()) fputcsv($output, $row);
exit;
?>
