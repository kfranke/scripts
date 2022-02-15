<?php 


$log_file = "program_success_x2_cable_Error.log2";
$handle = @fopen($log_file, "r");
$contents = fread($handle, filesize($log_file));
fclose($handle);


// search for failure via serial cable			

// Begins with "5/8/2020,18:36:15,818| Successfully queried the ESN 3314684"
// Does not contain "Written all the commands"
// Ends with "fail() True"

// Match 1 == Begin timestamp
// Match 2 == ESN
// Match 3 == End timestamp

$ptn = '/(?m)^([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})(?:.+)(?:Successfully queried the ESN)(?:.+)(33[0-9]{5}|45[0-9]{5})(?:(?!Written all the commands)[\s\S])*?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})(?:.+)(?:fail\(\) True.*?)$/';

print "Searching for failures via serial cable..." . "\n";
preg_match_all($ptn, $contents, $matches, PREG_SET_ORDER);
if( count($matches) > 0 )
{
	foreach ($matches as $match)
	{
		if($debug) print "Found failure program response for: $match[2] via serial cable\n";
		// add failed record to array keyed on ESN
		// just b/c there is a failure doesn't mean most recent for ESN is failure
		$ts = strtotime(str_replace(',', ' ', $match[3]));
		$records[$match[2]][] = array(
			'timestamp' => $ts,
			'date' => date("m/d/Y H:i:s", $ts), 
			'esn' => $match[2], 
			'state' => 'FAIL',
			'desc' => 'failed program response serial');
	}
}
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count($matches) . " failing via cable ESN records\n";
unset($matches, $ptn);