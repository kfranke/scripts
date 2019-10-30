<?php

$empty_fads		= '<?xml version="1.0" encoding="UTF-8"?>
<telemetry>
	<esn>^ESN^</esn>
	<timestamp_utc>^TIMESTAMP^</timestamp_utc>
	<latitude>^LATITUDE^</latitude>
	<longitude>^LONGITUDE^</longitude>
	<ignition>N</ignition>
	<geofence></geofence>
	<speed></speed>
	<heading></heading>
	<milestraveled></milestraveled>
	<triggers>^TRIGGERS^</triggers>
	<miscellaneous></miscellaneous>
	<totalaccumulator1hours></totalaccumulator1hours>
	<maintaccumulator1hours></maintaccumulator1hours>
	<accumulator1resetdt></accumulator1resetdt>
	<totalaccumulator2hours></totalaccumulator2hours>
	<maintaccumulator2hours></maintaccumulator2hours>
	<accumulator2resetdt></accumulator2resetdt>
	<totalvibrationhours></totalvibrationhours>
	<maintvibrationhours></maintvibrationhours>
	<vibrationresetdt></vibrationresetdt>
	<contact1counts></contact1counts>
	<contact2counts></contact2counts>
	<io1state>0</io1state>
	<io2state>0</io2state>
</telemetry>
';

/* Just supply and array with the few data elements to 
* encode into the empty xml. fads is super basic so not
* overly complicated here
*/

function fads_formatter($data)
{

	if(
			array_key_exists('esn',$data) && 
			array_key_exists('timestamp',$data) &&
			array_key_exists('lat',$data) &&
			array_key_exists('lon',$data) &&
			array_key_exists('triggers',$data)
		)
	{	
		
		$emptyXml = $empty_fads;
		$xmlMsg				= $emptyXml;
		$xmlMsg 			= str_replace('^ESN^'		,$data['esn'],		$xmlMsg);
		$xmlMsg				= str_replace('^TIMESTAMP^'	,$data['timestamp'],$xmlMsg);
		$xmlMsg				= str_replace('^LATITUDE^'	,$data['lat'],		$xmlMsg);
		$xmlMsg				= str_replace('^LONGITUDE^'	,$data['lon'],		$xmlMsg);
		$xmlMsg				= str_replace('^TRIGGERS^'	,$data['triggers'],	$xmlMsg);			
		$result				= array();
		$result['status']	= "SUCCESS";
		$result['response']	=	$xmlMsg;
		return $result;
	}
	else
	{
		$result				= array();
		$result['status']	= "ERROR";
		$result['response']	= "Required Data Elements Not Supplied";		
		return $result;
	}
}


?>
