<?php
date_default_timezone_set('UTC');

if( !$argv[1] ){
	print("No source dir specified at startup! \n");
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
$dataset = array();
$headers['fields'] = array(
	    	'Inv Date', 
	    	'Qty Subs', 
	    	'Qty Usage');

//get all files in array
//find . -name "*searchFile.csv" -exec cp {} ~/files \;
$ptn = "*20{13,14,15,16,17,18,19,20}.BillingDetails.csv";
$files = glob("$source_dir/$ptn", GLOB_BRACE);

//loop over files
$record = 0;
foreach ($files as $file) {
	//loop over rows in file
	$line = 1;
	$subs = 0;
	$usage = 0;
	$date = '';
	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	if($line != 1){
		        $date = $data[14];
		        $usage += $data[5];
		    }
		    $subs++;
		    $line++;
		}
		//store info and move to next file
	    $dataset[$date]['subs'] = $subs;
	    $dataset[$date]['usage'] = $usage;
	    fclose($handle);
	    print "Processed: $file \n";
	}
	$record++;
}

// Write dataset to file
$handle = fopen("$out_dir/$out_file", 'w');
fwrite($handle, implode(",", $headers['fields']) . "\r\n");
foreach($dataset as $key => $value){
	$row = $key . "," . implode(",", $value) . "\r\n";
	fwrite($handle, $row);
}	
fclose($handle);

?>