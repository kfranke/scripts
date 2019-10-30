<?php

// curl -H "Content-Type: application/json" -X POST -b cookiefile -d '[{"file":"moment", "action":"get", "filter":"Moment>2018-10-04T17:57:00Z", "limit":10}]' https://mapdata.assetlinkglobal.com/Portal/api.php
require(__DIR__ . "/../_GLOBALS.php");

$username = ASSETLINK_USERNAME;
$password = ASSETLINK_PASSWORD;
$scheme = "https://";

define("COOKIE_FILE", "/tmp/al_cookiefile");
$authenticated = authenticate($username,$password);
$isCookie = file_exists(COOKIE_FILE);

if($authenticated == true) print "---Auth Ok---\n";
if($isCookie == true) print "---Cookie Ok---\n";
if($authenticated == false) print "---No auth---\n";
if($isCookie == false) print "---No cookie\n";

$lastMsgTimestamp = 1540339200; // Oct 24 2018 00:00:00
print "---ISO 8601 TS: " . date(DATE_ISO8601, $lastMsgTimestamp) . "---\n"; //---ISO 8601 TS: 2018-10-04T17:57:00+0000---

$url = $scheme . ASSETLINK_HOST . ASSETLINK_PATH;

/* Basic check. Works */
// $data = array(
// "file" => "moment", 
// "action" => "getMostRecent");

/* Basic++ check. Works */
// $data = array(
// "file" => "moment", 
// "action" => "getMostRecent",
// "filter" => "esn=300234065312110",
// "limit" => 10);

/* Expected function. Causes error */
// $data = array(
// 	"file" => "moment", 
// 	"action" => "get", 
// 	"filter" => 'Moment>2018-10-04T17:57:00Z', 
// 	"limit" => 10);

/* How this might be done programatically. Causes error */
// $data = array(
// 	"file" => "moment", 
// 	"action" => "get", 
// 	"filter" => "Moment>" . date(DATE_ISO8601, $lastMsgTimestamp), 
// 	"limit" => 10);

/* AssetLinks suggestion. Works. Note the single quotes around the time string */
// $data = array(
// 	"file" => "moment", 
// 	"action" => "get", 
// 	"filter" => "Moment>'2018-10-24T17:57:00Z'", 
// 	"limit" => 10);

/* How this might be done programatically. Works. Note the single quotes around the time string */
$data = array(
	"file" => "moment", 
	"action" => "get", 
	"filter" => "Moment>" . "'" . date(DATE_ISO8601, $lastMsgTimestamp) . "'", 
	"limit" => 10);


if($authenticated && $isCookie){
	$result = makeCall($url, "POST", $data);
	// print "---JSON---\n";
	// print_r($result);
}
else{
	die("---Not properly authenticated---\n");
}

function makeCall($url, $type, $data){
	print "---Assemble data packet---\n";
	$data_string = "[". json_encode($data) . "]"; // Janky that you need to include brackets. Else 500 error.
	print "$data_string\n";
	print "---Initializing cURL---\n";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);	                                                             
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	/* If you try to set Content-Type headers you will produce an error
	 * [{"result":"error","error":"Could not JSON-parse POST contents '[{\"file\":\"moment\",\"action\":\"getMostRecent\",\"filter\":\"esn=300234065312110\",\"limit\":10'","tag":null}]
	*/

	// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	// 	'Content-Type: application/json',
	// 	'Content-Length: ' . strlen($data_string))
	// ); 
	                                         
	// curl_setopt($ch, CURLOPT_VERBOSE, true); 
	print "---Executing cURL---\n";
	$result = curl_exec($ch);
	// $info = curl_getinfo($ch);
	// print "\nInfo:\n";
	// print_r($info);
	curl_close($ch);
	print "---Result---\n";
	print_r($result) + print "\n";
	print "---Ready---\n";
	$obj = json_decode($result);
	return $obj;
}


function authenticate($user, $pass)
{
	$data = array(
		"USER" => array(
			"NAME" => $user,
			"PASSWORD" => $pass)
	);
	$data_string = json_encode($data);

	$url = "https://mapdata.assetlinkglobal.com/Portal/autologin.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
	curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
	    'Content-Type: application/json',                                                                                
	    'Content-Length: ' . strlen($data_string))                                                                       
	);                                               	

	$result = curl_exec($ch);
	$obj = json_decode($result);
	curl_close($ch);
	// print_r($obj);

	if($obj->USER->STATUS == "VERIFIED") {return true;}
	else{return false;}
}

?>