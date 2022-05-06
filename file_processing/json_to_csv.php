<?php
/* parse json file and write values to csv */
isset($argv[1]) ? $file = $argv[1] : die("Please specify json file\n");
$data = file_get_contents($file);
$json = json_decode($data);

isset($argv[2]) ? $keys = $argv[2] : die("Please specify obj keys of interest\n");


$path = pathinfo($file);
$dir = $path['dirname'];
$out = $dir . '/' . 'results_' . time() . '.csv';
$headers = array_keys((array)$json->models[0]);
$lines = 0;
$fp = fopen($out, "w");
fputcsv($fp, $headers);
foreach($json->models as $device)
{
	fputcsv($fp, (array)$device);
	$lines++;
}
fclose($fp);
print "Wrote $lines to $out\n";
?> 