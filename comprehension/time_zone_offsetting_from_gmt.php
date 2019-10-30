<?php
date_default_timezone_set('UTC');

$unixtime = time();
$timezone = 'America/New_York';

echo (new DateTime(null, new DateTimeZone('America/New_York')))->format('T') . "\n";
echo formatTimeStamp($unixtime, $timezone) . "\n";

function formatTimeStamp($unixtime, $timezone)
{
	$datetime = DateTime::createFromFormat('U', $unixtime)->setTimezone(new DateTimeZone($timezone) );
	return $datetime->format('m/d/Y H:i:s');
}



