#!/opt/homebrew/bin/php
<?php
/* Ref: https://www.speedtest.net/apps/cli */
if(!file_exists('/opt/homebrew/bin/speedtest')) exit("Dependency not met\n");
$log = 'speedtest_log.csv';
$print_headers = false;
$output = null; //json
$retval = null; //int
$location_id = 15355;
exec('speedtest -s ' . $location_id . ' -f json', $output, $retval);
if($retval != 0) exit; // anything else is an error
$speed = json_decode($output[0]);

$speed->download->mbps = $speed->download->bandwidth / 125 / 1000;
$speed->upload->mbps = $speed->upload->bandwidth / 125 / 1000;

$headers = array(
				"Timestamp", 
				"Ping",
				"Download Mbps",
				"Upload Mbps",
				"Server Name",
				"Server Location",
				"Server IP");
$values = array(
	date("m/d/y H:i:s", strtotime($speed->timestamp)),
	$speed->ping->latency,
	$speed->download->mbps,
	$speed->upload->mbps,
	$speed->server->name,
	$speed->server->location,
	$speed->server->ip);

if(!file_exists($log)) $print_headers = true;

if(!$fp = fopen($log, 'a')) exit;
if($print_headers) fputcsv($fp, $headers);
fputcsv($fp, $values);
fclose($fp);

exit(0);



