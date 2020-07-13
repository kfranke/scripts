<?php
ini_set("pcre.backtrack_limit", "-1");
date_default_timezone_set("UTC"); 
$debug = FALSE;
$runtime = time();
$output_file = 'results_'.$runtime.'.csv';
print("
	* ***************************************************** *
	* 													    *
	* Author: @KevinFranke                                  *
	* Date: August 2020                                     *
	* ***************************************************** *
	");
print("\n");
readline("Enter to continue...");
$limited_search = false;
$valid_log_file = false;
$stats = array();

if( !$argv[1] ){
	print("No log file specified at startup! \n");
	$io_log_file = trim(readline("Please enter path to log file for processing? "));
	// fails if log file name has spaces
	if( file_exists($io_log_file) ) $valid_log_file = true;
}
else{
	$io_log_file = $argv[1];
	// fails if log file name has spaces
	if( file_exists($io_log_file) ) $valid_log_file = true;
}

$log_file = $io_log_file;
print("Supplied file set to: $log_file\n");
if(!$valid_log_file) die("Cannot find specified log file: $log_file\n");
$io_log_file_path = pathinfo($io_log_file);
$output_dir = $io_log_file_path['dirname'];
$records 	= array();
$found 		= array();
$handle 	= @fopen($log_file, "r");
$rows = array();

if ($handle)
{
    while (($buffer = fgets($handle, 4096)) !== false)
    {
        // search for entries and add to records. 
    	$ptn = '/([0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})(?: UTC).*(?: sent).*(<stuMessages.*([\w]{32}).*>.*<\/stuMessages>)/';
    	preg_match($ptn, $buffer, $matches);
    	if(isset($matches[1]) && isset($matches[2]) && isset($matches[3]))
    	{
    		$date = mktime(
    			substr($matches[1],11,2),
    			substr($matches[1],14,4),
    			substr($matches[1],17,4),
    			substr($matches[1],5,2),
    			substr($matches[1],8,2),
    			substr($matches[1],0,4));
    		$xml = $matches[2];
    		$id = $matches[3];
    	}
    	else
    	{
    		print "No match on line\n";
    	}
    	// Can be more than 1 singular within plural XML
    	preg_match('/(?:<unixTime>)([0-9]{10})(?:<\/unixTime>)/', $xml, $matches);
    	$timestamps = array();
    	for ($i=1; $i < count($matches); $i++)
    	{ 
    		$timestamps[] = $matches[$i];
    	}
    	foreach ($timestamps as $ts)
    	{
    		$delta = $date - $ts;
    		if($delta < 0)
    		{
    			$sign = '-';
    		}
    		elseif($delta > 0)
    		{
    			$sign = '+';
    		}
    		else
    		{
    			$sign = '-/+';
    		}
    		$line = $id . ',' . date("Y-m-d H:i:s", $date) . ',' . date("Y-m-d H:i:s", $ts) . ',' . $sign . ',' . round(abs($delta), 1);
    		$rows[] = $line;
    		print $line . "\n";
    	}
    	unset($date, $xml, $id);
    }
    fclose($handle);	
}


// write records to csv file
$columns = array('XML ID', 'Log TS', 'Msg TS', 'Sign', 'Latency (sec)');
$fp = fopen($output_dir . '/' . $output_file, 'w');
fwrite($fp, implode(',', $columns) . "\n");
foreach ($rows as $row)
{
	fwrite($fp, $row . "\n");
}
fclose($fp);


