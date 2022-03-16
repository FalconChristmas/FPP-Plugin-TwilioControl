<?php
//$DEBUG=true;

include_once "common.php";
include_once "config.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "TwilioControl";

$messageQueue_Plugin = findPlugin("MessageQueue");
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$logFile = $settings['logDirectory']."/".$pluginName.".log";
$messageQueuePluginPath = $settings['pluginDirectory']."/".$messageQueue_Plugin."/";

$DEBUG=ParseBooleanValue($pluginSettings['DEBUG']);

$Plugin_DBName = $settings['configDirectory']."/FPP.".$pluginName.".db";
if (file_exists($messageQueuePluginPath."functions.inc.php")) {
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;
    $Plugin_DBName = $settings['configDirectory']."/FPP." . $messageQueue_Plugin . ".db";
} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}

$blacklistFile = $settings['configDirectory']."/plugin.".$pluginName.".Blacklist";

$delBlacklistNumber=null;
$blacklistNumber=null;
$messageText=null;

$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-TwilioControl";

//echo "PLUGIN DB:NAME: ".$Plugin_DBName;

$db = new SQLite3($Plugin_DBName) or die('Unable to open database');

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));
logEntry("TWILIO MESSAGE MANAGEMENT: ".$messageQueueFile);

$blacklistFile = $settings['configDirectory']."/plugin.".$pluginName.".Blacklist";
$profanityMessageQueueFile = $settings['configDirectory']."/plugin.".$pluginName.".ProfanityQueue";

include_once "pluginSettings.inc.php";



$REFRESH_SECONDS = 5;
$REFRESH_PAGE = "plugin.php?plugin=TwilioControl&page=messageManagement.php&REFRESH=true&AUTO_REFRESH=ON";

if(isset($_GET['REFRESH']) && isset($_GET['AUTO_REFRESH'])) {
    echo "Watch the page reload itself in 10 second!";
}

if($DEBUG) {
	print_r ($_POST);
	
}
if($DEBUG) {
	print_r($_GET);
	
}
if(isset($_POST['delMessageQueue'])) {
	//delete message queue
	
	$deleteMessageQueue = "DELETE FROM messages";
	
	logEntry("TWILIO MESSAGE MANAGEMENT: Deleting TwilioControl messages from message queue file ".$deleteMessageQueue);
	
	$deleteTwilioMessageQueueResult = $db->query($deleteMessageQueue) or die('Unable to delete Twilio Message Table');
}

if(isset($_POST['delProfanityQueue'])) {
	//delete message queue
	$deleteMessageQueue = "DELETE FROM profanity";
	
	logEntry("TWILIO MESSAGE MANAGEMENT: Deleting TwilioControl messages from profanity queue file ".$deleteMessageQueue);
	
	$deleteTwilioMessageQueueResult = $db->query($deleteMessageQueue) or die('Unable to delete Twilio Profanity Message Table');

}

if(isset($_POST['delBlacklistQueue'])) {
	//delete message queue
	$deleteMessageQueue = "DELETE FROM blacklist";
	
	logEntry("TWILIO MESSAGE MANAGEMENT: Deleting TwilioControl messages from blacklist queue file ".$deleteMessageQueue);
	
	$deleteTwilioMessageQueueResult = $db->query($deleteMessageQueue) or die('Unable to delete Twilio Blacklist message Table');
}

if(isset($_POST['removeProfanity'])) {
	logEntry("Removing a profanity list number");
	
	$delProfanityNumber=$_POST['phoneNumber'];
	$messageText=$_POST['messageText'];
	$messageTimestamp = $_POST['timestamp'];
	//$messageID = $_POST['messageID'];
	
	
	
	$deleteProfanityMessageQuery = "DELETE FROM profanity WHERE pluginData = '".$delProfanityNumber."' AND timestamp ='".$messageTimestamp."'";
	
	logEntry("TWILIO MESSAGE MANAGEMENT: Delete profanity query: ".$deleteProfanityMessageQuery);
	
	$deleteProfanityMessageResult = $db->query($deleteProfanityMessageQuery) or die('Query failed');
	//load file into $fc array
	
	//$db.close();
}
if(isset($_POST['sendStandardReply'])) {
	$replyToNumber=urldecode($_POST['phoneNumber']);
	$normalReply=urldecode($_POST['normalReply']);
	$messageID = $_POST['messageID'];
	
	
	$TSMS_from = $replyToNumber;
	
	if(substr($TSMS_from, 0) != "+") {
		$TSMS_from = "+".$TSMS_from;
		
	}
	logEntry("Sending a reply ".$normalReply." to phone number: ".$TSMS_from);
	sendTSMSMessage($normalReply);
}

if(isset($_POST['sendReply'])) {
	$blacklistNumber=urldecode($_POST['phoneNumber']);
	$profanityReply=urldecode($_POST['profanityReply']);
	$messageID = $_POST['messageID'];

	
	$TSMS_from = $blacklistNumber;
	
	if(substr($TSMS_from, 0) != "+") {
		$TSMS_from = "+".$TSMS_from;
		
	}
	logEntry("Sending a reply ".$profanityReply." to phone number: ".$TSMS_from);
	sendTSMSMessage($profanityReply);
}

if (isset($_POST['addBlacklist'])) {// != "") {
	logEntry("Adding a blacklist number");
	
		
		$blacklistNumber=$_POST['phoneNumber'];
		$messageText=$_POST['messageText'];
		$messageID = $_POST['messageID'];
	
		if($DEBUG) {
			echo "Black listing phone number: ID: ".$messageID." number: ".$blacklistNumber. " text: ".$messageText."<br/> \n";
		}
	
	//$blacklistNumber = $_POST['phoneNumber'];
	//$messageText = $_POST['messageText'];	
	insertBlacklistMessage($messageText, $pluginName, $blacklistNumber);
	
	//echo "Number: ".$blacklistNumber." added to ".$pluginName." Blacklist with message: ".$messageText;
	
	}
	
	if(isset($_POST['delBlacklist'])) {// != "") {
		
		logEntry("Removing a blacklist number");
		
		$delBlacklistNumber=$_POST["phoneNumber"];
		$messageText=$_POST['messageText'];
		$messageID = $_POST['messageID'];
		
		$messageTimestamp = $_POST['timestamp'];
	//$messageID = $_POST['messageID'];
	
	
		
		//delete from blacklist all entries of that number!
		$deleteBlacklistQuery = "DELETE FROM blacklist WHERE pluginData = '".$delBlacklistNumber."'";// AND timestamp ='".$messageTimestamp."'";
		
		logEntry("TWILIO MESSAGE MANAGEMENT: Delete blacklist query: ".$deleteBlacklistQuery);
		
		$deleteBlacklistQueryResult = $db->query($deleteBlacklistQuery) or die('Query failed');
		
		}


if(isset($_GET['START'])) {
	$CURRENT_DAY_START_TIMESTAMP = $_GET['START'];
} else {
	$CURRENT_DAY_START_TIMESTAMP = mkTimestamp(date("Y"),date("m"),date("d"),0,0,0);
}

if(isset($_GET['END'])) {
	$CURRENT_DAY_END_TIMESTAMP = $_GET['END'];
} else {
	$CURRENT_DAY_END_TIMESTAMP = mkTimestamp(date("Y"),date("m"),date("d"), 23,59,59);
}
	
	
	
	echo "<br/> \n";
	echo "<a href=\"plugin.php?plugin=".$pluginName."&page=exportMessages.php&nopage=1\">EXPORT Messages</a> \n";
	echo "&nbsp&nbsp&nbsp&nbsp \n";
	echo "<a href=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php&START=".($CURRENT_DAY_START_TIMESTAMP-86400)."&END=".($CURRENT_DAY_END_TIMESTAMP-86400)."\">Previous Day</a> \n";
	echo "&nbsp&nbsp&nbsp&nbsp \n";
	echo "<a href=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php&START=".($CURRENT_DAY_START_TIMESTAMP+86400)."&END=".($CURRENT_DAY_END_TIMESTAMP+86400)."\">Next Day</a> \n";
	echo "<br/> \n";

	
	//put the links as form links to go backwards and forwards to see messages
	//ability to EXPORT messages as CSV
	
	$messagesQuery = "SELECT * FROM messages WHERE pluginName = '".$pluginName."' AND timestamp > ".$CURRENT_DAY_START_TIMESTAMP." AND timestamp < ".$CURRENT_DAY_END_TIMESTAMP." ORDER BY timestamp DESC";
	$messagesResult = $db->query($messagesQuery) or die('Query failed');
	

echo "<center><h1><b>".$pluginName." Message Management</b></h1></center> <br/> \n";

echo "<hr> \n";
echo "<center><h2><b>ALL Messages (Latest on top)</b></h2> \n";
echo "<br/> \n";
echo  date('d M Y H:i:s',$CURRENT_DAY_START_TIMESTAMP)." thru ".date('d M Y H:i:s',$CURRENT_DAY_END_TIMESTAMP)."</center> <br/> \n";
//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";
echo "<tr> \n";
echo "<td> \n";
echo "LEGEND \n";
echo "</td> \n";
echo "<td bgcolor=\"yellow\"> \n";
echo "CONTROL/WHITELIST Number \n";
echo "</td> \n";
echo "<td bgcolor=\"red\"> \n";
echo "Blacklisted Number \n";
echo "</td> \n";
echo "</tr> \n";
echo "<tr> \n";
echo "<td> \n";
echo "Date Received \n";
echo "</td> \n";
echo "<td align=\"center\"> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "<td align=\"center\"> \n";
echo "Options \n";
echo "</td> \n";
echo "<td align=\"center\"> \n";
echo "Send reply \n";
echo "</td> \n";
echo "</tr> \n";
while ($row = $messagesResult->fetchArray()) {

	echo "<form name=\"messageManagementBlacklist\" method=\"post\" action=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php\"> \n";
	
	//check if blacklisted..
	//$blackListCheck = checkBlacklistNumber($row['pluginData']);
	//returns null if not found
	
	$blackListCheck = checkBlacklist($row['pluginData']);
	
	if($DEBUG) {
		logEntry("TWILIO MESSAGE MANAGEMENT: Returned blaklist check: ".$blackListCheck);
	
	}
	
	if (in_array ( $row['pluginData'], $CONTROL_NUMBER_ARRAY )) {
		$TR = "yellow";
	}elseif (in_array ( $row['pluginData'], $WHITELIST_NUMBER_ARRAY )) {
		$TR = "yellow";
	}elseif($blackListCheck != null) {
		$TR = "red";
	} else {
		$TR="";
	}
	
	echo "<tr> \n";
	//unix timestamp
	echo "<td bgcolor=\"".$TR."\"> \n";
	
	echo date('d M Y H:i:s',$row['timestamp']);
	echo "<input type=\"hidden\" name=\"timestamp\" value=\"".$row['timestamp']."\"> \n";
	echo "</td> \n";
	
	echo "<td bgcolor=\"".$TR."\"> \n";
	//message data
	echo urldecode($row['message']);
	echo "<input type=\"hidden\" name=\"messageText\" value=\"".trim($row['message'])."\"> \n";
	echo "</td> \n";
	
	echo "<td bgcolor=\"".$TR."\"> \n";
	//message data
	echo $row['pluginData'];
	echo "<input type=\"hidden\" name=\"phoneNumber\" value=\"".trim($row['pluginData'])."\"> \n";
	echo "</td> \n";
	
	echo "<input type=\"hidden\" name=\"messageID\" value=\"".$row["messageID"]."\"> \n";
	
	echo "<td> \n";
	if($blackListCheck)  {
		echo "BLACK LISTED \n";
	} else {
		echo "<input type=\"submit\" class=\"buttons\" name=\"addBlacklist\" value=\"BLACKLIST\"> \n";
	}
	echo "</td> \n";
	echo "<td> \n";
	echo "<input type=\"text\" size=\"64\" name=\"normalReply\"> \n";
	echo "<br/> \n";
	echo "<center> \n";
	echo "<input type=\"submit\" class=\"buttons\" name=\"sendStandardReply\" value=\"SEND\"> \n";
	echo "</center> \n";
	echo "</td> \n";
	//plugin Subscription
	//echo "<td> \n";
	
	//echo $messageQueueParts[2];
	//echo "</td> \n";
	
	echo "</tr> \n";
	//echo $pluginMessages[$i];

echo "</form> \n";
}
echo "</table> \n";
//echo "</textarea> \n";

echo "<hr> \n";
echo "<center><b><h2>Profanity Messages</h2></b></center>\n";

//echo "<br/> \n";
//echo "Messages received AFTER being blacklisted and also contain profanity will NOT be in this list <br/> \n";
//echo "Those messages are checked for BlackListing First and therefore do not go to the profanity checker <br/> \n";
//echo "<br/> \n";



$profanityMessageQuery = "SELECT * FROM profanity WHERE pluginName = '".$pluginName."' ORDER BY timestamp DESC";

$profanityMessageQueryResult = $db->query($profanityMessageQuery) or die('Query failed');

//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";
//check if blacklisted..
echo "<tr> \n";
	
echo "<td> \n";
echo "Date Received \n";
echo "</td> \n";
echo "<td> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "<td> \n";
echo "Blacklist Status \n";
echo "</td> \n";

echo "<td> \n";
echo "Remove from Profanity File \n";
echo "</td> \n";

echo "<td> \n";
echo "<center>Send message to person</center> \n";
echo "</td> \n";
echo "</tr> \n";
while ($row = $profanityMessageQueryResult->fetchArray()) {

	echo "<form name=\"messageManagementBlacklist\" method=\"post\" action=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php\"> \n";

	
	$blackListCheck = checkBlacklist($row['pluginData']);
	
	if($DEBUG) {
		logEntry("TWILIO MESSAGE MANAGEMENT: Returned blaklist check: ".$blackListCheck);
	
	}
	if (in_array ( $row['pluginData'], $CONTROL_NUMBER_ARRAY )) {
		$TR = "yellow";
	}
	if (in_array ( $row['pluginData'], $WHITELIST_NUMBER_ARRAY )) {
		$TR = "yellow";
	}
	if($blackListCheck != null) {
		$TR = "red";
	}
	
	echo "<tr> \n";
	
	
	//unix timestamp
	echo "<td bgcolor=\"".$TR."\"> \n";
	
	echo date('d M Y H:i:s',$row['timestamp']);
	echo "<input type=\"hidden\" name=\"timestamp\" value=\"".$row['timestamp']."\"> \n";
	echo "</td> \n";
	
	echo "<td bgcolor=\"".$TR."\"> \n";
	//message data
	echo urldecode($row['message']);
	echo "<input type=\"hidden\" name=\"messageText\" value=\"".trim($row['message'])."\"> \n";
	echo "</td> \n";
	
	echo "<td bgcolor=\"".$TR."\"> \n";
	//message data
	echo $row['pluginData'];
	echo "<input type=\"hidden\" name=\"phoneNumber\" value=\"".trim($row['pluginData'])."\"> \n";
	echo "</td> \n";
	
	echo "<input type=\"hidden\" name=\"messageID\" value=\"".$row["messageID"]."\"> \n";
	
	echo "<td> \n";
	if($blackListCheck)  {
		echo "BLACK LISTED \n";
	} else {
		echo "<input type=\"submit\" name=\"addBlacklist\" value=\"BLACKLIST\"> \n";
	}
	echo "</td> \n";
	echo "</td> \n";
	echo "<td> \n";
	echo "<input type=\"submit\" name=\"removeProfanity\" value=\"REMOVE\"> \n";
	echo "</td> \n";
	echo "<td> \n";
	echo "<input type=\"text\" size=\"64\" name=\"profanityReply\"> \n";
	echo "<input type=\"submit\" name=\"sendReply\" value=\"SEND\"> \n";
	echo "</td> \n";

	echo "</tr> \n";
	//echo $pluginMessages[$i];


echo "</form> \n";
}
echo "</table> \n";
//echo "</textarea> \n";

$blacklistMessageQuery = "SELECT * FROM blacklist WHERE pluginName = '".$pluginName."' ORDER BY timestamp DESC";

$blackListMessageQueryResult = $db->query($blacklistMessageQuery) or die('Query failed');

echo "<hr> \n";
echo "<center><b><h2>Blacklisted Messages</h2></b></center>\n";

echo "<br/> \n";
echo "Messages will also be highlighted in RED in the ALL messages area above <br/> \n";

//echo "<textarea class=\"FormElement\" name=\"messages\" id=\"messages\" cols=\"40\" rows=\"".$messageCount."\">\n";
echo "<table cellspacing=\"3\" cellpadding=\"3\" border=\"1\"> \n";
echo "<tr> \n";
echo "<td> \n";
echo "Placed on Blacklist \n";
echo "</td> \n";
echo "<td> \n";
echo "Message \n";
echo "</td> \n";
echo "<td> \n";
echo "From number \n";
echo "</td> \n";
echo "</tr> \n";
while ($row = $blackListMessageQueryResult->fetchArray()) {
	echo "<form name=\"messageManagementBlacklist\" method=\"post\" action=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php\"> \n";
		if (in_array ( $row['pluginData'], $CONTROL_NUMBER_ARRAY )) {
		$TR = "yellow";
	}
	if (in_array ( $row['pluginData'], $WHITELIST_NUMBER_ARRAY )) {
		$TR = "yellow";
	}
	
	
	echo "<tr bgcolor=\"".$TR."\"> \n";

	//$messageQueueParts = explode("|",$pluginMessages[$i]);

	//unix timestamp
	echo "<td> \n";

	echo date('d M Y H:i:s',$row['timestamp']);
	echo "<input type=\"hidden\" name=\"timestamp\" value=\"".$row['timestamp']."\"> \n";
	echo "</td> \n";

	echo "<td> \n";
	//message data
	echo urldecode($row['message']);
	
	echo "</td> \n";

	echo "<td> \n";
	//message data
	echo $row['pluginData'];
	echo "<input type=\"hidden\" name=\"phoneNumber\" value=\"".trim( $row['pluginData'])."\"> \n";
	echo "</td> \n";

	echo "<td> \n";
	echo "<input type=\"submit\" name=\"delBlacklist\" value=\"Remove From Blacklist\"> \n";
	echo "<input type=\"hidden\" name=\"messageID\" value=\"".$row["messageID"]."\"> \n";
	echo "</td> \n";
	//plugin Subscription
	//echo "<td> \n";

	//echo $messageQueueParts[2];
	//echo "</td> \n";

	echo "</tr> \n";
	//echo $pluginMessages[$i];


echo "</form> \n";
}
echo "</table> \n";

echo "<hr/> \n";
echo "Message file management \n";
echo "<form name=\"messageManagementBlacklist\" method=\"post\" action=\"plugin.php?plugin=".$pluginName."&page=messageManagement.php\"> \n";
echo "<input type=\"submit\" class=\"buttons\" name=\"delMessageQueue\" value=\"Delete Twilio Messages from Message Queue\"> \n";
echo "<input type=\"submit\" class=\"buttons\" name=\"delProfanityQueue\" value=\"Delete Profanity Queue\"> \n";
echo "<input type=\"submit\" class=\"buttons\" name=\"delBlacklistQueue\" value=\"Delete Blacklist Queue\"> \n";
echo "</form> \n";

//echo "<form name=\"MessagesPageRefresh\" method=\"get\" action=\"".$REFRESH_PAGE."\"> \n";
//echo "<input type=\"submit\" name=\"REFRESH\" value=\"REFRESH\"> \n";
//echo "Auto Refresh: \n";
//echo "<input type=\"checkbox\" name=\"AUTO_REFRESH\" value=\"".$_GET['AUTO_REFRESH']."\"> \n";
//echo "<input type=\"hidden\" name=\"REFRESH_PAGE\" value=\"".$REFRESH_PAGE."\"> \n";
//echo "</form> \n";

//echo "</form> \n";
//echo "</textarea> \n";

// $db.close();
?>
