<?php
date_default_timezone_set('UTC');

//setup
(count($argv) < 3) ? die("Usage: script.php log/files/directory/ 0-esn\n") : false;

$valid_dir = false;
$file_ptn = "numerex_transponders_202*.csv";
$esn_ptn = '/0\-[1-4][0-9]{5,6}/';

if( !$argv[1] ){
	die("Error: Missing script parameter.\n" );
}
elseif( is_dir($argv[1]) ){
	$log_dir = $argv[1];
	$valid_dir = true;
	$log_dir_uri = pathinfo($log_dir);
	$output_dir = $log_dir_uri['dirname'];
	$files = glob("$log_dir/$file_ptn", GLOB_BRACE);

	print("Supplied dir set to: $log_dir\n");
}
else{
	die("Error: Directory \"$log_dir\" does not exist!");
}

if( !$argv[2] ){
	die("Error: Missing script parameter.\n");
}
elseif( !preg_match($esn_ptn, $argv[2]) ){
	die("Error: Supplied \"$argv[2]\" did not match any ESNs.\n");
}
else{
	// TODO: add multi ESN support
	$search_esns = array(); 
	preg_match_all($esn_ptn, $argv[2], $search_esns);
	print("Search ESNs: \n");
	print( implode("\n", $search_esns[0]) . "\n");	
}


$esns_to_search = $search_esns[0];

$dataset = array();
/*
ESN 0
LATITUDE 2
LONGITUDE 3
MOST RECENT REPORT TIME STAMP 15
PERCENTAGE BATTERY LIFE REMAINING 16
*/
$headers['fields'] = array(
			'ESN',
	    	'LATITUDE', 
	    	'LONGITUDE', 
	    	'MOST RECENT REPORT TIME STAMP', 
	    	'PERCENTAGE BATTERY LIFE REMAINING',
	    	'REPORT_FILE_DATE');


//loop over files
$record = 0;
foreach ($files as $file) {
	preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', $file, $match);
	$parts = explode('-', $match[0]);
	$file_date = mktime(0,0,0,$parts[1],$parts[2],$parts[0]); //unixtime
	$rows = array();

	//loop over rows in file
	$line = 1;
	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	if($line != 1){
		        $esn = $data[0];
		        $lat = $data[2];
		        $lon = $data[3];
		        //16/02/2017 20:33:26 GMT
		        $mrrts = $data[15];
		        $batt = $data[16];
		        if( in_array($esn, $esns_to_search) )
		        {
		        	print("Found ESN: ". $esn .", grabbing info\n");
		        	if(preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{2}\:[0-9]{2}\:[0-9]{2}\sGMT/", $mrrts))
			        {
			        	print("Convertig time: ". $mrrts ."\n");
			        	$day = substr($mrrts, 0,2);
			        	$mo = substr($mrrts, 3,2);
			        	$yr = substr($mrrts, 6,4);
			        	$hr = substr($mrrts, 11,2);
			        	$min = substr($mrrts, 14,2);
			        	$sec = substr($mrrts, 17,2);
			        	$mrrts = date("m/d/Y H:i:s", mktime($hr,$min,$sec,$mo,$day,$yr));
			        	print("Converted time to: $mrrts \n");
			        }
		        	$rows[] = array('esn' => $esn,
					        		'lat' => $lat,
					        		'lon' => $lon,
					        		'mrrts' => $mrrts,
					        		'batt' => $batt,
					        		'rfd' => date("m/d/Y H:i:s", $file_date));
		        } 
		    }

		    $line++;
		}

		//store info and move to next file
	    $dataset[] = $rows;
	    fclose($handle);
	    print "Processed $file \n";
	}
	$record++;
}

// Write dataset to file
$results = $output_dir.'/'.date("U").'_'.'battery_history'.'_report.csv';
$out = fopen($results, 'w');
fwrite($out, implode(",", $headers['fields']) . "\r\n");
foreach($dataset as $entry){
	foreach ($entry as $item => $details) {
		$row = implode(",", $details) . "\r\n";
		fwrite($out, $row);
	}	
}
fclose($out);
print "Wrote $record to $results\n";
?>