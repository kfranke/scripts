<?php


/*----------------------------------------------------------------------------*/
/*
* Uses http://openweathermap.org
* returns array
* 04/2015
*/

function weatherAPI($city_name,$mode)
{
	$base_url 	= 'http://api.openweathermap.org/data/2.5/weather?';
	$params 	= array("city" => "q=$city_name", "mode" => "mode=$mode");
	$fetch_url 	= $base_url.join('&', array_values($params) );
	$data = file_get_contents($fetch_url);
	if($params['mode'] = 'xml')
	{
		$weather = new SimpleXMLElement($data);
		$temp_k = (float)$weather->temperature->attributes()->value;
		$temp_f = round( ((($temp_k - 273.15) * 1.8) + 32), 1);
		$condition = (string)$weather->weather->attributes()->value;
		
	}
	$result = compact("temp_f","condition");
	return $result;
}

/*----------------------------------------------------------------------------*/

weatherAPI("mountain-view", "xml");
?>