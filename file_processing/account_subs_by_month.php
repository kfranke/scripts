<?php
date_default_timezone_set('UTC');

//setup
$file_ptn = "*{2018,2019}.BillingDetails.csv";
$billing_dir = "~/Downloads/billing_details";
$dataset = array();
$headers['fields'] = array(
			'Account',
	    	'Unixtime', 
	    	'Inv Date', 
	    	'Qty SX1', 
	    	'Qty FLEX', 
	    	'Qty SmartOne', 
	    	'Qty SmartOne C', 
	    	'Qty Other',
	    	'Qty Msgs',
	    	'Qty Msging');

//get all files in array
$files = glob("$billing_dir/$file_ptn", GLOB_BRACE);

//loop over files
$record = 0;
foreach ($files as $file) {
	preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/', $file, $match);
	$parts = explode('.', $match[0]);
	$unixtime = mktime(0,0,0,$parts[0],$parts[1],$parts[2]);
	$file_counts = array();

	//loop over rows in file
	$line = 1;
	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	if($line != 1){
		        $inv_date = $data[14];
		        $org_name = $data[1];
		        
		        $file_counts[$org_name]['UNIXTIME'] = $unixtime;
		        $file_counts[$org_name]['INV_DATE'] = $inv_date;
		        // $file_counts[$org_name]['WITH_USAGE'] = 0;
		        
		        $type = $data[6];
		            switch ($type) {
		            	case 'SX1':
		            		$file_counts[$org_name]['SX1'] += 1;
		            		$file_counts[$org_name]['SXL'] += 0;
		            		$file_counts[$org_name]['SMARTONE'] += 0;
		            		$file_counts[$org_name]['SMARTONE_C'] += 0;
		            		$file_counts[$org_name]['OTHER'] += 0;
		            		$file_counts[$org_name]['MSGS'] += $data[5];
		            		break;
		            	case 'SXL':
		            		$file_counts[$org_name]['SX1'] += 0;
		            		$file_counts[$org_name]['SXL'] += 1;
		            		$file_counts[$org_name]['SMARTONE'] += 0;
		            		$file_counts[$org_name]['SMARTONE_C'] += 0;
		            		$file_counts[$org_name]['OTHER'] += 0;
		            		$file_counts[$org_name]['MSGS'] += $data[5];
		            		break;
		            	case 'SMARTONE':
		            		$file_counts[$org_name]['SX1'] += 0;
		            		$file_counts[$org_name]['SXL'] += 0;
		            		$file_counts[$org_name]['SMARTONE'] += 1;
		            		$file_counts[$org_name]['SMARTONE_C'] += 0;
		            		$file_counts[$org_name]['OTHER'] += 0;
		            		$file_counts[$org_name]['MSGS'] += $data[5];
		            		break;
		            	case 'SMARTONE C':
		            		$file_counts[$org_name]['SX1'] += 0;
		            		$file_counts[$org_name]['SXL'] += 0;
		            		$file_counts[$org_name]['SMARTONE'] += 0;
		            		$file_counts[$org_name]['SMARTONE_C'] += 1;
		            		$file_counts[$org_name]['OTHER'] += 0;
		            		$file_counts[$org_name]['MSGS'] += $data[5];
		            		break;
		            	default:
		            		$file_counts[$org_name]['SX1'] += 0;
		            		$file_counts[$org_name]['SXL'] += 0;
		            		$file_counts[$org_name]['SMARTONE'] += 0;
		            		$file_counts[$org_name]['SMARTONE_C'] += 0;
		            		$file_counts[$org_name]['OTHER'] += 1;
		            		$file_counts[$org_name]['MSGS'] += $data[5];
		            		break;
		            }
		            if($data[5] > 0) $file_counts[$org_name]['WITH_USAGE']++;
		    }
		    $line++;
		}
		//store info and move to next file
	    $dataset[] = $file_counts;
	    fclose($handle);
	    print "Processed $file \n";
	}
	$record++;
}

// Write dataset to file
$out = fopen('~/Downloads/billing_details/'.date("Ymd").'_subs_by_account.csv', 'w');
fwrite($out, implode(",", $headers['fields']) . "\r\n");
foreach($dataset as $entry){
	foreach ($entry as $account => $details) {
		$row = '"' . $account . '"' .",". implode(",", $details) . "\r\n";
		fwrite($out, $row);
	}	
}
fclose($out);
?>