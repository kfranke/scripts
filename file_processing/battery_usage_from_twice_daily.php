<?php
date_default_timezone_set('UTC');

// $ batt_usage_aggregate.php 0-123456 /path/to/folder/with/files

//setup
(count($argv) < 3) ? die("Usage: script.php 0-esn /path/to/folder/with/files \n") : false;

$esn_to_search = $argv[1];
$dir_to_search = $argv[2];
$file_ptn = "numerex_transponders_*.csv";
$match_ptn = '/0\-[1-4][0-9]{5,6}/';
$results_file_name = __DIR__ . date("U").'_battery_history_'.$esn_to_search.'_report.csv';

if(!preg_match($match_ptn, $esn_to_search)) die("Invalid ESN pattern \n");
if(!is_dir($argv[2])) die("Invalid directory to search \n");

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

//get all files in array
$files = glob("$dir_to_search/$file_ptn", GLOB_BRACE);

//loop over files
$records = 0;
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
	$records++;
}

// Write dataset to file
$out = fopen($results_file_name, 'w');
fwrite($out, implode(",", $headers['fields']) . "\r\n");
foreach($dataset as $entry){
	foreach ($entry as $item => $details) {
		$row = implode(",", $details) . "\r\n";
		fwrite($out, $row);
	}	
}
fclose($out);
print "Wrote $records to $results_file_name\n";
?>