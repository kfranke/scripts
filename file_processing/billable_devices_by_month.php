<?php
date_default_timezone_set('UTC');

//get all files in array
$file_ptn = "*BillingDetails.csv";
$billing_dir = "~/Downloads/billing_details";
$files = glob("$billing_dir/$file_ptn");
$dataset = array();
$dataset[] = array(
	    	'Unixtime', 
	    	'Date', 
	    	'Qty Accounts', 
	    	'Qty SX1', 
	    	'Qty FLEX', 
	    	'Qty SmartOne', 
	    	'Qty SmartOne C', 
	    	'Qty Other', 
	    	'Qty Msgs',
		    'Provisions SX1',
			'Deprovisions SX1',
			'Provisions SXL',
			'Deprovisions SXL',
			'Provisions SmartOne',
			'Deprovisions SmartOne',
			'Provisions SmartOne C',
			'Deprovisions SmartOne C',
			'Provisions Other',
			'Deprovisions Other',
			'Provisions Total',
			'Deprovisions Total');

//loop over files
foreach ($files as $file) {
	preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/', $file, $match);
	$parts = explode('.', $match[0]);
	$unixtime = mktime(0,0,0,$parts[0],$parts[1],$parts[2]);
	$date = date("m/d/Y", $unixtime);
	//$row = 1;
	$orgs = array();
	$device_counts = array('SX1' => 0, 'SXL' => 0, 'SMARTONE' => 0, 'SMARTONE_C' => 0, 'OTHER' => 0);
	$months_msgs = 0;
	$network_changes = array(
		'PROVISIONS_SX1' => 0, 
		'PROVISIONS_SXL' => 0,
		'PROVISIONS_SMARTONE' => 0, 
		'PROVISIONS_SMARTONE_C' => 0,
		'PROVISIONS_OTHER' => 0,
		'PROVISIONS_TOTAL' => 0,
		'DEPROVISIONS_SX1' => 0,
		'DEPROVISIONS_SXL' => 0,
		'DEPROVISIONS_SMARTONE' => 0,
		'DEPROVISIONS_SMARTONE_C' => 0,
		'DEPROVISIONS_OTHER' => 0,
		'DEPROVISIONS_TOTAL' => 0 );
	//loop over rows in file
	$line = 1;
	if (($handle = fopen($file, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	if($line != 1){
		        $num = count($data);
		        $orgs[] = $data[1];
		        $months_msgs = $months_msgs + $data[5];

		        $type = $data[6];
		            switch ($type) {
		            	case 'SX1':
		            		//$device_counts['SX1'] = $device_counts['SX1'] + 1;
		            		$device_counts['SX1'] += 1;
		            		if($data[8] != 0){
		            			$network_changes['PROVISIONS_SX1'] += $data[8];
		            			$network_changes['PROVISIONS_TOTAL'] += $data[8];
		            		}
		            		if($data[9] != 0){
		            			$network_changes['DEPROVISIONS_SX1'] += $data[9];
		            			$network_changes['DEPROVISIONS_TOTAL'] += $data[9];
		            		}
		            		break;
		            	case 'SXL':
		            		$device_counts['SXL'] += 1;
		            		if($data[8] != 0){
		            			$network_changes['PROVISIONS_SXL'] += $data[8];
		            			$network_changes['PROVISIONS_TOTAL'] += $data[8];
		            		}
		            		if($data[9] != 0){
		            			$network_changes['DEPROVISIONS_SXL'] += $data[9];
		            			$network_changes['DEPROVISIONS_TOTAL'] += $data[9];
		            		}
		            		break;
		            	case 'SMARTONE':
		            		//$device_counts['SMARTONE'] = $device_counts['SMARTONE'] + 1;
		            		$device_counts['SMARTONE'] += 1;
		            		if($data[8] != 0){
		            			$network_changes['PROVISIONS_SMARTONE'] += $data[8];
		            			$network_changes['PROVISIONS_TOTAL'] += $data[8];
		            		}
		            		if($data[9] != 0){
		            			$network_changes['DEPROVISIONS_SMARTONE'] += $data[9];
		            			$network_changes['DEPROVISIONS_TOTAL'] += $data[9];
		            		}
		            		break;
		            	case 'SMARTONE C':
		            		// $device_counts['SMARTONE_C'] = $device_counts['SMARTONE_C'] + 1;
		            		$device_counts['SMARTONE_C'] += 1;
		            		if($data[8] != 0){
		            			$network_changes['PROVISIONS_SMARTONE_C'] += $data[8];
		            			$network_changes['PROVISIONS_TOTAL'] += $data[8];
		            		}
		            		if($data[9] != 0){
		            			$network_changes['DEPROVISIONS_SMARTONE_C'] += $data[9];
		            			$network_changes['DEPROVISIONS_TOTAL'] += $data[9];
		            		}
		            		break;
		            	default:
		            		//$device_counts['OTHER'] = $device_counts['OTHER'] + 1;
		            		$device_counts['OTHER'] += 1;
		            		if($data[8] != 0){
		            			$network_changes['PROVISIONS_OTHER'] += $data[8];
		            			$network_changes['PROVISIONS_TOTAL'] += $data[8];
		            		}
		            		if($data[9] != 0){
		            			$network_changes['DEPROVISIONS_OTHER'] += $data[9];
		            			$network_changes['DEPROVISIONS_TOTAL'] += $data[9];
		            		}
		            		break;
		            }
		        //$row++;
		    }
		    $line++;
		}
	    
	    //$dataset[] = implode(',', array($unixtime,$date,count(array_unique($orgs)),$device_counts['SX1'],$device_counts['SXL'],$device_counts['SMARTONE'],$device_counts['SMARTONE_C'],$device_counts['OTHER']));
	    

	    $dataset[] = array(
	    	$unixtime,
	    	$date,
	    	count(array_unique($orgs)),
	    	$device_counts['SX1'],
	    	$device_counts['SXL'],
	    	$device_counts['SMARTONE'],
	    	$device_counts['SMARTONE_C'],
	    	$device_counts['OTHER'],
	    	$months_msgs,
		    $network_changes['PROVISIONS_SX1'],
			$network_changes['DEPROVISIONS_SX1'],
			$network_changes['PROVISIONS_SXL'],
			$network_changes['DEPROVISIONS_SXL'],
			$network_changes['PROVISIONS_SMARTONE'],
			$network_changes['DEPROVISIONS_SMARTONE'],
			$network_changes['PROVISIONS_SMARTONE_C'],
			$network_changes['DEPROVISIONS_SMARTONE_C'],
			$network_changes['PROVISIONS_OTHER'],
			$network_changes['DEPROVISIONS_OTHER'],
			$network_changes['PROVISIONS_TOTAL'],
			$network_changes['DEPROVISIONS_TOTAL']);
	    
	    fclose($handle);
	    print "Processed $file \n";
	}
	// print_r($device_counts);
	// print_r($network_changes);
	// die("\n you shall go no futher \n");
}
// Write dataset to file

$out = fopen('~/Downloads/billing_details/'.date("Ymd").'_file.csv', 'w');
foreach ($dataset as $row) {
   fputcsv($out, $row);
}
fclose($out);
?>