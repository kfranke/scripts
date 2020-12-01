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
        // search for all ESNs list entries and add to records. 
        
        /*
        * 5/8/2020,18:41:25,ListViewItem 7877| 3314684
		* 5/8/2020,18:42:11,818| Successfully queried the ESN 3314684
		* 5/15/2020,11:50:31,ESN Received: 3303450
		* 5/15/2020,11:50:31,ESN: 3303450
		* 5/8/2020,18:44:50,newLI ListViewItem: {3314684}
		* 5/15/2020,10:54:41,Successfully Programmed: 3314684
		* 5/15/2020,12:08:41,Unable to Program: 3314684
        * 6/7/2019,10:31:06,ListViewItem B| 3315175 :: 1.713 - COM38 -- 9600
        * 5/7/2019,10:54:39,ListViewItem 7877| 3316369 :: 1.7.0 - COM28 -- 9600
		*/
    	
		$query_ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*(33[0-9]{5})/';
		preg_match($query_ptn, $buffer, $matches);
		if( count($matches) === 3 && isset($matches[1]) && isset($matches[2]) )
		{
			// add query record and set state to uncertain
			$ts = strtotime(str_replace(',', ' ', $matches[1]) );
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
    print "Found " . $stats['unique'] . " ESN records\n";
    if($debug) print '$records: ' . print_r($records, true);
    fseek($handle, 0); // rewind
    $contents = fread($handle, filesize($log_file));
    fclose($handle);	
}

// search for success via serial cable
// 6/7/2019,11:11:07,Successfully queried the ESN 3313740 on: COM41... ...Written all the commands
$pass_ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Successfully(?: queried the ESN).*(33[0-9]{5})(?:[\s\S]+?)(Written all the commands)[\s\S]+?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})/';
preg_match_all($pass_ptn, $contents, $matches);
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count(array_filter($matches[1])) . " passing via cable ESN records\n";

if( count($matches) === 5 )
{
	$successes = array();
	for ($i=1; $i < count(array_filter($matches)); $i++)
	{ 
		foreach ($matches[$i] as $key => $value)
		{
			$successes[$key][] = $value;
		}
	}
}
if( isset($successes) && count($successes) > 0 )
{
	foreach ($successes as $success)
	{
		if($debug) print "Found successful program response for: $success[1] via serial cable\n";
		// add successful record to array keyed on ESN
		// just b/c there is a successful doesn't mean most recent for ESN is successful
		$ts = strtotime(str_replace(',', ' ', $success[3]));
		$records[$success[1]][] = array(
		'timestamp' => $ts,
		'date' => date("m/d/Y H:i:s", $ts), 
		'esn' => $success[1], 
		'state' => 'PASS',
		'desc' => 'successful program response serial');
	}
}
unset($matches, $successes);

// search for failure via serial cable			
// 5/8/2020,18:36:15,818| Successfully queried the ESN 3314684 on: COM4... ...fail() True
$fail_ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Successfully(?: queried the ESN).*(33[0-9]{5})(?:[\s\S]+?)(fail\(\) True)[\s\S]+?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})/';
preg_match_all($fail_ptn, $contents, $matches);
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count(array_filter($matches[1])) . " failing via cable ESN records\n";

if( count($matches) === 5 )
{
	$failures = array();
	for ($i=1; $i < count(array_filter($matches)); $i++)
	{ 
		foreach ($matches[$i] as $key => $value)
		{
			$failures[$key][] = $value;
		}
	}
}
if( isset($failures) && count($failures) > 0 )
{
	foreach ($failures as $failed)
	{
		if($debug) print "Found failure program response for: $failed[1] via serial cable\n";
		// add failed record to array keyed on ESN
		// just b/c there is a failure doesn't mean most recent for ESN is failure
		$ts = strtotime(str_replace(',', ' ', $failed[3]));
		$records[$failed[1]][] = array(
		'timestamp' => $ts,
		'date' => date("m/d/Y H:i:s", $ts), 
		'esn' => $failed[1], 
		'state' => 'FAIL',
		'desc' => 'failed program response serial');
	}
}
unset($matches, $failures);

// search for success via btle
//5/15/2020,12:26:45,Successfully Programmed: 3303450 via Bluetooth
$pass_ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Successfully Programmed.*(33[0-9]{5})(?:[\s\S]+?)(via Bluetooth)[\s\S]+?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})/';
preg_match_all($pass_ptn, $contents, $matches);
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count(array_filter($matches[1])) . " passing via cable ESN records\n";

if( count($matches) === 5 )
{
	$successes = array();
	for ($i=1; $i < count(array_filter($matches)); $i++)
	{ 
		foreach ($matches[$i] as $key => $value)
		{
			$successes[$key][] = $value;
		}
	}
}
if( isset($successes) && count($successes) > 0 )
{
	foreach ($successes as $success)
	{
		if($debug) print "Found successful program response for: $success[1] via serial cable\n";
		// add successful record to array keyed on ESN
		// just b/c there is a successful doesn't mean most recent for ESN is successful
		$ts = strtotime(str_replace(',', ' ', $success[0]));
		$records[$success[1]][] = array(
		'timestamp' => $ts,
		'date' => date("m/d/Y H:i:s", $ts), 
		'esn' => $success[1], 
		'state' => 'PASS',
		'desc' => 'successful program response btle');
	}
}
unset($matches, $successes);
// search for failure via serial btle
// 5/15/2020,12:08:41,Unable to Program: 3314684 via Bluetooth
$fail_ptn = '/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2}).*Unable to Program.*(33[0-9]{5})(?:[\s\S]+?)(via Bluetooth)[\s\S]+?([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4},[0-9]{2}:[0-9]{2}:[0-9]{2})/';
preg_match_all($fail_ptn, $contents, $matches);
print "Regex errors: " . array_flip(array_filter(get_defined_constants(true)['pcre']))[preg_last_error()] . "\n";
print "Found " . count(array_filter($matches[1])) . " failing via cable ESN records\n";

if( count($matches) === 5 )
{
	$failures = array();
	for ($i=1; $i < count(array_filter($matches)); $i++)
	{ 
		foreach ($matches[$i] as $key => $value)
		{
			$failures[$key][] = $value;
		}
	}
}
if( isset($failures) && count($failures) > 0 )
{
	foreach ($failures as $failed)
	{
		if($debug) print "Found failure program response for: $failed[1] via serial cable\n";
		// add failed record to array keyed on ESN
		// just b/c there is a failure doesn't mean most recent for ESN is failure
		$ts = strtotime(str_replace(',', ' ', $failed[0]));
		$records[$failed[1]][] = array(
		'timestamp' => $ts,
		'date' => date("m/d/Y H:i:s", $ts), 
		'esn' => $failed[1], 
		'state' => 'FAIL',
		'desc' => 'failed program response btle');
	}
}
unset($matches, $failures);

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
			if($debug) print "Entry of: ".$entry['date']." for ".$entry['esn']." is >= than ts: ".date("m/d/Y H:i:s", $ts)."\n";
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
			if($debug) print "Entry of: ".$entry['date']." for ".$entry['esn']." is not >= than ts: ".date("m/d/Y H:i:s", $ts)."\n";
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
