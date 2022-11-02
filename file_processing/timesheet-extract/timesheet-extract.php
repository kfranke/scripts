<?php
/**
 * composer require phpoffice/phpspreadsheet
 * @author Kevin Franke
 * usage: $ script.php /source/directory
 * purpose: scrape spreadsheets and grab the cells of interest
 * and put those into a csv
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

//setup
isset($argv[1]) ? $dir = $argv[1] : die("Please specify source directory\n");
print "Gathering files from: " . $dir . "\n";
$output = __DIR__ . "/time_and_mileage.csv";
$ptn = "*xlsx";
$files = glob("$dir/$ptn", GLOB_BRACE);
print "Found: " . count($files) . " files to sort through\n";
$timesheets = array();
$entries = 0;

//read the excel files
foreach($files as $file){
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($file);
    $sheet_data = $spreadsheet->getActiveSheet()->rangeToArray('A14:F43'); //those are the cells we care about
    foreach($sheet_data as $row){
        if($row[0] != null){ //not empty
            if(substr($row[0],0,1) != " "){ //no whitepace
                $timesheets[] = $row;
                $entries++;
            }
        }
    }  
}

//write the data to csv
print "Writting: " . $entries . " entries to the output\n";
$fp = fopen($output, 'w');
foreach($timesheets as $timesheet){
    fputcsv($fp, $timesheet);
}
fclose($fp);
print "Wrote output to: " . $output . "\n";