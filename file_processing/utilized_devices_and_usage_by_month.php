<?php
date_default_timezone_set('UTC');

if( !$argv[1] ){
	print("No source directory specified at startup! \n");
	$source_dir = trim(readline("Please enter source path: "));
	// fails if log file name has spaces
	if( file_exists($source_dir) ) $valid_path = true;
}
else{
	$source_dir = $argv[1];
	// fails if log file name has spaces
	if( file_exists($source_dir) ) $valid_path = true;
}

//setup
$out_dir = ".";
$out_file = 'results_'.time().'.csv';
$report = array();
$headers['details']['fields'] = array(
	    	'Date', 
	    	'Serial', 
	    	'Usage');
$headers['summary']['fields'] = array(
	    	'Date', 
	    	'Deployed',
	    	'Deployed Msg Usage',
	    	'Avg Msg p/Deployment');

//get all files in array
//find . -name "*searchFile.csv" -exec cp {} ~/files \;
// $ptn = "*20{13,14,15,16,17,18,19,20}.BillingDetails.csv";
$ptn = "*20*.Home_Depot*.csv";
$files = glob("$source_dir/$ptn", GLOB_BRACE);
//loop over files
$record = 0;
$threshold = 10;
foreach ($files as $file) {
	$day 	= 1;
	$line 	= 0;
	$usage 	= 0;
	$date 	= '';
	preg_match('/[0-9]{2}\.[0-9]{4}/', $file, $match);
	$parts 	= explode('.', $match[0]);
	$month 	= $parts[0];
	$year 	= $parts[1];
	$unixtime = mktime(0,0,0,$month,$day,$year);
	$date = date("m/d/Y", $unixtime);
	$deployed = 0;
	$usage_total = 0;
	//loop over rows in file
	$skip_rows = 3;
	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	if($line > $skip_rows){
	    		// row is data to evaluate
	    		if(preg_match('/0-[0-9]{6,8}/', $data[0]))
	    		{
	    			$esn 	= $data[0];
		        	$usage 	= $data[3];
	    		}
		    }
		    if($usage > $threshold)
		    {
		    	$report['period'][$date]['rows'][] = array('date' => $date, 'esn' => $esn, 'usage' => $usage);
		    	$deployed += 1;
		    	$usage_total += $usage;
		    }
		    $line++;
		}
		$report['summary'][$date] = array('date' => $date, 'deployed' => $deployed, 'usage_total' => $usage_total);
	    fclose($handle);
	    print "Processed: $file \n";
	}
	$record++;
}

// Write report to file
$handle = fopen("$out_dir/$out_file", 'w');
fwrite($handle, "Device is deployed if per period msg count > " . $threshold . str_repeat("\r\n", 3));
fwrite($handle, implode(",", $headers['details']['fields']) . "\r\n");

foreach($report['period'] as $date) {
	foreach($date['rows'] as $row) {
		fwrite($handle, implode(",", $row) . "\r\n");
	}
	fwrite($handle, str_repeat("\r\n", 1));
}

fwrite($handle, str_repeat("\r\n", 3));
fwrite($handle, implode(",", $headers['summary']['fields']) . "\r\n");

foreach($report['summary'] as $term){
	fwrite($handle, implode(",", $term) . ',' . round($term['usage_total'] / $term['deployed']) . "\r\n");
}
fclose($handle);

?>