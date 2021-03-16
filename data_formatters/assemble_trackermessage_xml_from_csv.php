<?php
date_default_timezone_set('UTC');

$template = file_get_contents('trackerMessageXML.xml');

if(!$argv[1]) die("Usage: script.php source.csv\n");
else($source = $argv[1]);

$xml = simplexml_load_string($template);

$csv = array_map('str_getcsv', file($source)); 

array_shift($csv); // remove column header

$dir = "./files";
$out = mkdir($dir, 0755);
$ext = '.xml';

/*
Array
(
    [0] [ESN]
    [1] [Message Type]
    [2] [Org]
    [3] [Latitude]
    [4] [Longitude]
    [5] [Address]
    [6] [Time Stamp]
    [7] [Asset Name]
    [8] [Device Type]
    [9] [Reading Details]
    1[0] [Message Cause]
    1[1] [Low Battery]
    1[2] [Motion Status]
    1[3] [GPS Fix Confidence]
)
*/

$id = 1;
foreach($csv as $row)
{
	$message = $xml;
	list($m,$d,$y) = explode("/", substr($row[6], 0, 8));
	list($h,$i,$s) = explode(":", substr($row[6], 9, 8));
	list($street, $city, $state, $zip, $county) = str_getcsv($row[5]);
	
	$message->trackermessage['type'] = str_replace(" ", "_", strtolower($row[1]));
	$message->trackermessage['id'] = str_pad($id, 9, "0", STR_PAD_LEFT);
	$message->trackermessage['positional'] = 'true';
	$message->trackermessage['unixtime'] =  mktime($h,$i,$s,$m,$d,$y);
	$message->trackermessage->asset->esn = $row[0];
	$message->trackermessage->asset->name = $row[7];
	$message->trackermessage->cause->trigger = 'Geofence: 0_0_LatLon';
	$message->trackermessage->cause->condition = 'Outside';
	$message->trackermessage->position->coordinate->latitude = $row[3];
	$message->trackermessage->position->coordinate->longitude = $row[4];
	$message->trackermessage->position->address->street_address = mb_convert_encoding(trim($street), "UTF-8");
	$message->trackermessage->position->address->city = mb_convert_encoding(trim($city), "UTF-8");
	$message->trackermessage->position->address->state = mb_convert_encoding(trim($state), "UTF-8");
	$message->trackermessage->position->address->zip = mb_convert_encoding(trim($zip), "UTF-8");
	$message->trackermessage->position->address->county = mb_convert_encoding(trim($county), "UTF-8");
	$message->trackermessage->motion = ($row[12] != 'Stopped' ? 'true' : 'false');
	foreach($message->trackermessage->payload->field as $field)
	{
		if($field['name'] == 'gps_fix_confidence')
		{
			$field['data'] = ($row[13] == 'High' ? '0' : '1');
		}
		elseif($field['name'] == 'latitude')
		{
			$field['data'] = $row[3];
		}
		elseif($field['name'] == 'longitude')
		{
			$field['data'] = $row[4];
		}
		elseif($field['name'] == 'battery_state')
		{
			$field['data'] = ($row[13] != 'TRUE' ? 'false' : 'true');
		}		
		elseif(strpos($field['name'], 'message_cause'))
		{
			$field['name'] = str_replace(" ", "_", strtolower($row[1])) . '_message_cause';
			$field['data'] = '0100';
		}
		elseif($field['name'] == 'motion')
		{
			$field['data'] = ($row[12] != 'Stopped' ? 'true' : 'false');
		}
		elseif($field['name'] == 'raw_payload')
		{
			$field['data'] = $row[9];
		}
	}
	
	$path = $dir . '/' . 'trackerMessageXML_' . $id . $ext;
	print "Writting document to: " . $path . "\n";
	file_put_contents($path, $message->asXML());
	$id++;
}
print "Wrote: " . $id . " docs\n";
