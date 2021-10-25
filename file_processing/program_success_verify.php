#!/usr/bin/php
<?php
ini_set("pcre.backtrack_limit", "-1");
date_default_timezone_set("UTC"); 
$debug = FALSE;
$runtime = time();
$output_file = 'device_program_results_'.$runtime.'.csv';
print("
	* ********* Device Program Success Validation ********* *
	* This tool will evaluate the software's log file to    *
	* determine which ESNs that were attempted to be        *
	* programmed completed successfully and which did not.  *
	* In default mode every ESN that the software has       *
	* processed will be checked and results returned. Users *
	* can optionally supply an ESN list to check against EG *
	* '0-123456                                             *
	*  0-455678                                             *
	*  0-987653'                                            *
	* If a specific list is given, the results will only    *
	* pertain to those.                                     *
	*                                                       *
	* If ESN is programmed more than 1 time, the most       *
	* recent attempt will be evaluated as final truth thus  *
	* several programming failures followed by a success    *
	* will be presented as a successful program of ESN.     *
	* programVerify.php [string log_file] [string ESN/s]    *
	* Author: @KevinFranke                                  *
	* Date: August 2019                                     *
	* ***************************************************** *
	");
print("\n");
print("
	* **************** Notes to the user ****************** *
	*                                                       *
	* Program can appear to hang due to pcre matching       *
	* 5/15/2020 Programming via BTLE is unreliable          *
	* 10/25/2021 Updated for more strict fail & pass checks *
	* ESN range 0-3300000, 0-4500000                        *
	*                                                       *
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

if( isset($argv[2]) )
{
	$limited_search = true;
	$search_esns = array();
	preg_match_all('/[3-4][0-9]{6}/', $argv[2], $search_esns);
	print("Search ESNs: \n");
	print( implode("\n", $search_esns[0]) . "\n");
	$stats['mode'] = 'filtered';
}
else
{
	print("Searching all ESNs \n");
	$stats['mode'] = 'all';
}

$records 	= array();
$found 		= array();
$handle 	= @fopen($log_file, "r");
if ($handle)
{
    while (($buffer = fgets($handle, 4096)) !== false)
    {
        // Search for all ESNs list entries and add to records. 

		// Contains 5/8/2020,18:41:25 {anything} 3314684
		// Matches[1] == timestamp
		// Matches[2] == ESN

		$ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*(33[0-9]{5}|45[0-9]{5}).*$/';
		preg_match($ptn, $buffer, $matches);
		if( count($matches) === 3 && isset($matches[1]) && isset($matches[2]) )
		{
			// add query record and set state to uncertain
			$ts = 0; // zero else queried can take precedence over programmed
			$records[$matches[2]][] = array(
				'timestamp' => $ts,
				'date' => date("m/d/Y H:i:s", $ts), 
				'esn' => $matches[2], 
				'state' => 'FAIL',
				'desc' => 'esn state uncertain');
			$found[] = $matches[2];
		} 
    }
    $stats['unique'] = count(array_unique($found));
    print "Found " . $stats['unique'] . " unique ESN records in file: " . $log_file . " size: " . human_filesize(filesize($log_file)) . "\n";
    if($debug) print '$records: ' . print_r($records, true);
    fseek($handle, 0); // rewind
    $contents = fread($handle, filesize($log_file));
    fclose($handle);
    unset($matches, $ptn);
}

// Search for success via serial cable

// Beings with "6/7/2019,11:11:07,Successfully queried the ESN 3313740"
// Does not contain "fail() True"
// Ends with "Written all the commands"
// Matches[1] == Begin timestamp
// Matches[2] == ESN
// Matches[3] == End timestamp

$ptn = '/(?m)^([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})(?:.+)(?:Successfully queried the ESN)(?:.+)(33[0-9]{5}|45[0-9]{5})(?:(?!fail\(\) True)[\s\S])*?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})(?:.+)(?:Written all the commands.+)$/';

print "Searching for passing via serial cable..." . "\n";
preg_match_all($ptn, $contents, $matches, PREG_SET_ORDER);
if( count($matches) > 0 )
{
	foreach ($matches as $match)
	{
		if($debug) print "Found successful program response for: $match[2] via serial cable\n";
		// add successful record to array keyed on ESN
		// just b/c there is a successful doesn't mean most recent for ESN is successful
		$ts = strtotime(str_replace(',', ' ', $match[3]));
		$records[$match[2]][] = array(
			'timestamp' => $ts,
			'date' => date("m/d/Y H:i:s", $ts), 
			'esn' => $match[2], 
			'state' => 'PASS',
			'desc' => 'successful program response serial');
	}
}
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count($matches) . " passing via cable ESN records\n";
unset($matches, $ptn);

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

// search for success via btle

// Contains "5/15/2020,12:26:45,Successfully Programmed: 3303450 via Bluetooth"
// Matches[1] == timestamp
// Matches[2] == ESN

$ptn = '/(?m)^([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Successfully Programmed.*(33[0-9]{5}|45[0-9]{5})(?:[\s\S]+?)(?:via Bluetooth.*?)$/';

print "Searching for passing via BTLE..." . "\n";
preg_match_all($ptn, $contents, $matches, PREG_SET_ORDER);
if( count($matches) > 0 )
{
	foreach ($matches as $match)
	{
		if($debug) print "Found failure program response for: $match[2] via serial cable\n";
		// add failed record to array keyed on ESN
		// just b/c there is a failure doesn't mean most recent for ESN is failure
		$ts = strtotime(str_replace(',', ' ', $match[1]));
		$records[$match[2]][] = array(
			'timestamp' => $ts,
			'date' => date("m/d/Y H:i:s", $ts), 
			'esn' => $match[2], 
			'state' => 'PASS',
			'desc' => 'successful program response btle');
	}
}
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count($matches) . " passing via BTLE ESN records\n";
unset($matches, $ptn);

// search for failure via serial btle

// Contains "5/15/2020,12:08:41,Unable to Program: 3314684 via Bluetooth"
// Matches[1] == timestamp
// Matches[2] == ESN

$ptn = '/(?m)^([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Unable to Program.*(33[0-9]{5}|45[0-9]{5})(?:[\s\S]+?)(?:via Bluetooth.*?)$/';

preg_match_all($ptn, $contents, $matches, PREG_SET_ORDER);
if( count($matches) > 0 )
{
	foreach ($matches as $match)
	{
		if($debug) print "Found failure program response for: $failed[1] via BTLE\n";
		// add failed record to array keyed on ESN
		// just b/c there is a failure doesn't mean most recent for ESN is failure
		$ts = strtotime(str_replace(',', ' ', $match[1]));
		$records[$match[2]][] = array(
			'timestamp' => $ts,
			'date' => date("m/d/Y H:i:s", $ts), 
			'esn' => $match[2], 
			'state' => 'FAIL',
			'desc' => 'failed program response btle');
	}
}
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count($matches) . " failing via BTLE ESN records\n";
unset($matches, $ptn);


print "Updated records with status...\n";
if($debug) print '$records: ' . print_r($records, true);

// loop through records and pull out max timestamp record
$results = array();
foreach ($records as $record) {
	$item = array();
	$ts = 0;
	foreach($record as $entry)
	{
		if($entry['timestamp'] >= $ts)
		{
			if($debug) print "Entry of: ".$entry['date']." for ".$entry['esn'] . "(" .$entry['state']. ")" ." is >= than ts: ".date("m/d/Y H:i:s", $ts)."\n";
			$item['esn'] = $entry['esn'];
			$item['timestamp'] = $entry['timestamp'];
			$item['date'] = $entry['date'];
			$item['state'] = $entry['state'];
			$item['desc'] = $entry['desc'];
			$results[$entry['esn']] = $item;
			$ts = $entry['timestamp'];	
		}
		else
		{
			if($debug) print "Entry of: ".$entry['date']." for ".$entry['esn'] . "(" .$entry['state']. ")" . " is not >= than ts: ".date("m/d/Y H:i:s", $ts)."\n";
		}
		
	}
}
$stats['results'] = count($results);
print "Results are in...\n";

if($limited_search)
{
	print "Filtering results for supplied ". count($search_esns[0]) ." ESNs\n";
	$filtered = array();
	foreach ($search_esns[0] as $esn)
	{
		if( isset($results[$esn]) ) $filtered[$esn] = $results[$esn];
		else $filtered[$esn] = array('esn' => $esn, 'timestamp' => 0, 'date' => date("m/d/Y H:i:s", 0),'state' => 'FAIL', 'desc' => 'record for esn not found');
	}
	if($debug) print_r($filtered);
	$stats['results'] = count($filtered);
}

// write records to csv file
if($limited_search)
{
	$columns = '';
	$rows = '';
	foreach ($filtered as $entry)
	{
		$columns = implode(',', array_keys($entry)) . "\n";
		$row = implode(',', array_values($entry)) . "\n";
		$rows .= $row;
		if($entry['state'] == 'PASS') $stats['passed'] += 1;
		if($entry['state'] == 'FAIL') $stats['failed'] += 1;
	}
	$fp = fopen($output_dir . '/' . $output_file, 'w');
	fwrite($fp, $columns);
	fwrite($fp, $rows);
	fclose($fp);
}
else
{
	$columns = '';
	$rows = '';
	foreach ($results as $entry)
	{
		$columns = implode(',', array_keys($entry)) . "\n";
		$row = implode(',', array_values($entry)) . "\n";
		$rows .= $row;
		if($entry['state'] == 'PASS') $stats['passed'] += 1;
		if($entry['state'] == 'FAIL') $stats['failed'] += 1;
	}
	$fp = fopen($output_dir . '/' . $output_file, 'w');
	fwrite($fp, $columns);
	fwrite($fp, $rows);
	fclose($fp);
}

// stats. mode, X total, Y pass, Z fail
$data = file_get_contents($output_dir . '/' . $output_file);
$summary = '';
foreach ($stats as $key => $value) {
	$summary .= str_repeat(',', count(explode(',', $columns))) . ucfirst($key) . ': ,' . $value . "\n";
}
print "Statistics\n";
print str_replace(',', '', $summary);
file_put_contents($output_dir . '/' . $output_file, $summary . $data);
print "Wrote data to: " . $output_dir . '/' . $output_file . "\n";

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}