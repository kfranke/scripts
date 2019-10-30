<?php
/**
* I'm not proud of this. Quick and dirty.
* Takes the exported Tasks.ics file from OSX 'Reminders.app'
* and puts them into a csv spreadsheet file. 
* @author Kevin Franke
* @version 1.0 Feb. 2016
**/

/** Error reporting */
//error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('UTC');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
$daily_seconds = 86400;
$todays_time = time();

if(!$argv[1]) { exit('ERROR! Usage ~$php script.php icsFile.ics output.csv [TIMELIMIT]'."\n\n"); }
$ics_file = $argv[1];

if(!$argv[2]) { exit('ERROR! No output path specified!'."\n\n"); }

$output_file_name = dirname($argv[2]) . "/" . date("Y-m-d",$todays_time) . " - " . basename($argv[2]);

if(isset($argv[3])) {
	$restrict_limit = $argv[3];
	switch ($restrict_limit) {
		// improvement would be for calendar time, eg for the calendar month not just past 30 days
		case 'DAILY':
			$restriction = TRUE;
			$events_time_limit = $daily_seconds;
			break;
		case 'WEEKLY':
			$restriction = TRUE;
			$events_time_limit = 7 * $daily_seconds;
			// future expansion to provide for yearly, monthly, weekly
			break;
		case 'MONTHLY':
			$restriction = TRUE;
			$events_time_limit = 30 * $daily_seconds;
			break;
		case 'YEARLY':
			$restriction = TRUE;
			$events_time_limit = 365 * $daily_seconds;
			break;
		default:
			// someone specified something but it wasnt anything we recognized. 
			exit('ERROR! Unrecognized limit option specified :'.$restrict_limit."Valid: DAILY, WEEKLY, MONTHLY, YEARLY\n");
			break;
	}
}
else {
	$restriction = FALSE;
}
if(!file_exists($ics_file)) {
	exit('Specified file '.$ics_file.' not found'."\n\n");
} else {
	echo date('H:i:s') , " Parsing tasks file: ".$ics_file , EOL;
}

$ics_data = file_get_contents($ics_file);
$events = [];
$tasks = [];
preg_match_all("/BEGIN\:VTODO[\s\S]+?END\:VTODO/", $ics_data, $events);
for ($i=0; $i < count($events); $i++) { 
	$event = $events[$i];
	for ($i=0; $i < count($event); $i++) { 
		$lines = explode("\n", $event[$i]);
		$created = 0;
		$summary = "";
		$dtstamp = 0;
		$completed = 0;
		foreach ($lines as $line) {
			if (preg_match("/CREATED:/", $line)) {
				//$created = date('m/d/Y H:i:s',strtotime(substr($line, 8)));
				$created = strtotime(substr($line, 8));
			}
			elseif (preg_match("/SUMMARY:/", $line)) {
				$summary = substr($line, 8);
			}
			elseif (preg_match("/COMPLETED:/", $line)) {
				//$completed = date('m/d/Y H:i:s',strtotime(substr($line, 10)));
				$completed = strtotime(substr($line, 10));
			}
			elseif (preg_match("/DTSTAMP:/", $line)) {
				//$dtstamp = date('m/d/Y H:i:s',strtotime(substr($line, 8)));
				$dtstamp = strtotime(substr($line, 8));
			}
			if($completed > 0 && $dtstamp > $completed) {
				$completed = $dtstamp;
			}
			if($completed == null || $completed < 0 || $completed == FALSE){
				$completed = "Open";
			}
	 	}
	 	// restrict events by datetime
	 	if($restriction == TRUE) {
	 		// don't add data unless
	 		if($created > ($todays_time - $events_time_limit)) {
	 			$tasks[] = array('created' => $created, 'summary' => $summary, 'completed' => $completed);	
	 		}
	 		else {
	 			// don't add it, out of range
	 		}
	 	}
	 	else {
	 		// add everything
	 		$tasks[] = array('created' => $created, 'summary' => $summary, 'completed' => $completed);
	 	}
	}
}

// Sort the array. Keep in mind this retains the index. EG 5 might come before 4...
$sorted_tasks = array_sort($tasks, 'created', SORT_ASC);
$headers = array('Created On', 'Task Summary', 'Completed On');
$rows = array();
array_push($rows, implode(",", $headers) );

foreach($sorted_tasks as $task) {
	$cells = array(); 	
	$cells[] = date("m/d/Y h:i:s", $task['created']);
	$cells[] = '"' . trim($task['summary']) . '"';
	if(is_numeric($task['completed'])) $cells[] = date("m/d/Y h:i:s", $task['completed']);
	else $cells[] = $task['completed'];
	$row = implode(",", $cells);
	$rows[] = $row;
}

$callStartTime = microtime(true);
// write the data
file_put_contents($output_file_name, implode("\n", $rows) );
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo date('H:i:s') , " File written to " , $output_file_name , EOL;
echo date('H:i:s') , ' Call time to write spreadsheet was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
echo date('H:i:s') , " Done writing files" , EOL;

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
?>