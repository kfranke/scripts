<?php
ini_set("auto_detect_line_endings", TRUE); //Important for use on Apple 
date_default_timezone_set("UTC");
$start_time 		= microtime();
$good_to_go 		= FALSE;
$thrown_ping_count 	= 0;
$distance_limit 	= 500; //miles
$abc_angle_limit 	= 40; //deg
$time_limit 		= 6; //hours	
//If this script is run stand alone
if(count($argv) < 2)
{
	echo 'Using hardcoded ping file, to specify your own ~$ find_thrown_pings.php your_file.csv debug[true || false] '."\n";
	$pings_file = "somePings.csv";
	$debug 		= TRUE;
	$good_to_go = TRUE;
}
//If this script is called by specifing a csv file
else
{
	$pings_file = $argv[1];
	if( isset($argv[2]) )
	{
		if($argv[2] == 'false') {$debug = FALSE;}
		if($argv[2] == 'true') {$debug = TRUE; }
	}
	$path_info = pathinfo($pings_file);
	if($path_info['extension'] == 'csv')
	{
		$good_to_go = true;
	}
}

if(!$good_to_go)
{
	echo 'Script aborting, invalid CSV file'."\n";
	return;
}
$location_data 	= array();
$thrown_pings 	= array();
//-----------------------------------------------------------------------------
// Read the CSV file in and build array
//-----------------------------------------------------------------------------
$row = 1;
if (($handle = fopen($pings_file, "r")) !== FALSE)
{
	while (($data = fgetcsv($handle, 300, ",")) !== FALSE)
	{
    	$location_data[] = $data;
        $num = count($data);
        $row++;
    }
    fclose($handle);
}


//======================================================================
// DO THE STUFF
// WE'LL GET A GROUP OF 3 PINGS AND THEN CALCULATE
// THE LEG DISTANCES AND THE BEARINGS OF EACH AND
// FINALLY THE ABC ANGLE. THE ASSUMPTION IS THAT 
// IF LEG DISTANCES ARE GREAT AND ANGLE IS ACUTE
// THAT IDENTIFIES A THROWN PING
//======================================================================
// Start $i at 1 because of header row. 
// Dont go all the way to end because we need full groups of 3
for ($i=1; $i < count($location_data)-2; $i++)
{
	static $j = 0;
	// Get 3 pairs of latitudes & longitudes
	$latA = $location_data[$i][3];
	$latB = $location_data[$i+1][3];
	$latC = $location_data[$i+2][3];
	$lonA = $location_data[$i][4];
	$lonB = $location_data[$i+1][4];
	$lonC = $location_data[$i+2][4];
	// Get 3 pairs of timestamps
	$timeA = $location_data[$i][2];
	$timeB = $location_data[$i+1][2];
	$timeC = $location_data[$i+2][2];
	if($debug)
	{
	print "My three group of locations for: ".$location_data[$i+1][0]."\n";
	print "Location group $i: $latA $lonA @ $timeA\n";
	print "Location group $i: $latB $lonB @ $timeB\n";
	print "Location group $i: $latC $lonC @ $timeC\n";
	}
	//calc A->B & B->C time differences
	$ab_time_difference 	= $timeB - $timeA; //sec
	$bc_time_difference 	= $timeC - $timeB; //sec
	$ab_time_difference_hrs = $ab_time_difference / 60 / 60;
	$bc_time_difference_hrs = $bc_time_difference / 60 / 60;
	//calc B->A & B->C distance in miles
	$ba_distance 	= calculateDistance($latB, $lonB, $latA, $lonA);
	$bc_distance 	= calculateDistance($latB, $lonB, $latC, $lonC);
	$ba_distance_mi = $ba_distance['miles'];
	$bc_distance_mi = $bc_distance['miles'];
	if($debug)
	{
	print "B->A Distance: ".$ba_distance_mi." mi\n";
	print "B->C Distance: ".$bc_distance_mi." mi\n";
	}
	//calc headings rhumb line
	$ab_bearing = getRhumbLineBearing($latA, $lonA, $latB, $lonB);
	$ba_bearing = $ab_bearing < 180 ? $ab_bearing + 180 : $ab_bearing - 180;
	$bc_bearing = getRhumbLineBearing($latB, $lonB, $latC, $lonC);
	if($debug)
	{
	print "A->B Bearing: ".$ab_bearing." deg\n";
	print "B->A Bearing: ".$ba_bearing." deg\n";
	print "B->C Bearing: ".$bc_bearing." deg\n";
	}
	//calc ABC angle in degres
    if($ba_bearing - $bc_bearing > 0)
    {
        $abc_angle = $ba_bearing - $bc_bearing;
    }
    else
    {
        $abc_angle = $bc_bearing - $ba_bearing;
    }
    if($debug)
    {
        print "A-B-C Angle: ".$abc_angle." deg\n";
    }
	//======================================================================
    // DO THE CHECKS FOR THROWN PINGS
    // CHECK TO SEE THAT THE LEG DISTANCES IS GREAT AND THAT
    // THE ABC ANGLE IS VERY ACUTE AND THAT THE TIME
    // BETWEEN THE PINGS IS LESS THAN A TIME LIMIT SO THAT
    // STARTUP PINGS AND PINGS WHERE A UNIT WAS SHIPPED SOME
    // PLACE DON'T CREATE FALSE POSITIVES 
    //======================================================================
	if(
        $ab_time_difference_hrs < $time_limit &&
        $bc_time_difference_hrs < $time_limit &&
        $ba_distance_mi > $distance_limit &&
        $bc_distance_mi > $distance_limit &&
        $abc_angle < $abc_angle_limit
        )
    {
        $thrown_ping_count++;
        $esn 		= $location_data[$i+1][0];
		$time 		= date("m/d/y H:i:s",$location_data[$i+1][2]);
		$unixtime 	= $location_data[$i+1][2];
		$thrownlat 	= $location_data[$i+1][3];
		$thrownlon 	= $location_data[$i+1][4];
		$thrown_fields = array($esn,$time,$unixtime,$thrownlat,$thrownlon,$ab_time_difference_hrs,$bc_time_difference_hrs,$ba_distance_mi,$bc_distance_mi,$distance_limit,$abc_angle,$abc_angle_limit);
		$thrown_pings[$j] = $thrown_fields;
		if($debug)
		{
			print "This is a thrown ping!!!\n";
            print "AB time difference of: ".number_format($ab_time_difference_hrs,4);
            print " AND BC time difference of: ".number_format($bc_time_difference_hrs,4);
            print " is less than ".$time_limit."hr time limit";
            print " AND BA distance of: ".number_format($ba_distance_mi,2);
            print " AND BC distance of: ".number_format($bc_distance_mi,2);
            print " exceeds ".$distance_limit." mile limit";
            print " AND ABC angle of: ".number_format($abc_angle,6);
            print " is less than ".$abc_angle_limit." degree limit\n";
		}
		$j++;
	}
	
}
// How long did it take
$runtime = (microtime() - $start_time);
// Some Statistics on the query 
$stats = array(
                'total pings' => "Total pings " . $number_of_pings,
                'thrown pings' => "Thrown pings " . $thrown_ping_count,
                'time' => "Ran in " . $runtime,
				);
//------------------------------------------------------------------------------
// Put the results into a csv file
//------------------------------------------------------------------------------
// CSV fields
$csv_fields = array(
					'esn',
					'time',
					'unixtime',
					'thrownlat',
					'thrownlon',
					'ab_time_difference_hrs',
					'bc_time_difference_hrs',
					'ba_distance_mi',
					'bc_distance_mi',
					'distance_limit',
					'abc_angle',
					'abc_angle_limit',
					);

if(($fp = fopen("thrownPings.csv", 'w+')) !== FALSE)
{
    // write some stats to first line
    fputcsv($fp, $stats);
    // write header row
    fputcsv($fp, $csv_fields);
    // loop over all the thrown pings and insert 1 by 1
    foreach($thrown_pings as $thrown_ping)
    {
        fputcsv($fp, $thrown_ping);
    }
    fclose($fp);
}
if($debug)
{
    print_r($thrown_pings);
}


print "Script finished in $runtime seconds and found: $thrown_ping_count thrown pings\n";

//------------------------------------------------------------------------------
// Calculate the distance between two latitude, longitude points
// latitude and longitude should be in dd.dd format. Returns an array
// with the common units. Feet, Miles, etc.
//------------------------------------------------------------------------------
function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
{
    $theta      = $longitude1 - $longitude2;
    $miles      = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $miles      = acos($miles);
    $miles      = rad2deg($miles);
    $miles      = $miles * 60 * 1.1515;
    $feet       = $miles * 5280;
    $yards      = $feet / 3;
    $kilometers = $miles * 1.609344;
    $meters     = $kilometers * 1000;
    return compact('miles','feet','yards','kilometers','meters');
}
//------------------------------------------------------------------------------
// Great Circle Bearing in degrees
// Good enough for the needs here. For more precision use haversine
// formula
//------------------------------------------------------------------------------
function calculateBearing($latitude1, $longitude1, $latitude2, $longitude2) {
    $bearing = (rad2deg(atan2(sin(deg2rad($longitude2) - deg2rad($longitude1)) * cos(deg2rad($latitude2)), cos(deg2rad($latitude1)) * sin(deg2rad($latitude2)) - sin(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($longitude2) - deg2rad($longitude1)))) + 360) % 360;
    return $bearing;
}
//------------------------------------------------------------------------------
// Rhumb Line Bearing in degrees
// A Rhumb bearing is a fixed bearing angle from point a to point b.
// whereas a standard bearing changes as you move through latitudes
// due to curvature of the earth.
//------------------------------------------------------------------------------
function getRhumbLineBearing($lat1, $lon1, $lat2, $lon2)
{
    //difference in longitudinal coordinates
    $dLon = deg2rad($lon2) - deg2rad($lon1);
    //difference in the phi of latitudinal coordinates
    $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));
    //we need to recalculate $dLon if it is greater than pi
    if(abs($dLon) > pi())
    {
        if($dLon > 0)
        {
            $dLon = (2 * pi() - $dLon) * -1;
        }
        else
        {
            $dLon = 2 * pi() + $dLon;
        }
    }
    //return the angle 
    $rlb = rad2deg(atan2($dLon, $dPhi)) + 360;
     if($rlb > 360)
     {
        $rlb = $rlb - 360;
     }
     return $rlb;
}
?>