<?php
/* parse json file and write values to csv */
/* $ php json_to_csv_v2.php myjsonfile.json things */
/*

myjsonfile.json
{
  "things": [
    {
      "id": "1",
      "name": "thing_1",
      "desc": "my things description",
      "created": "2022-04-28T00:00:00Z",
      "updated": "2025-12-31T00:00:00Z"
    },
    {
      "id": "2",
      "name": "thing_2",
      "desc": "about my thing",
      "created": "2022-04-28T00:00:00Z",
      "updated": "2025-12-31T00:00:00Z"
    },

*/

isset($argv[1]) ? $file = $argv[1] : die("Please specify json file\n");
$data = file_get_contents($file);
$json = json_decode($data);

isset($argv[2]) ? $keys = $argv[2] : die("Please specify obj keys of interest\n"); //root level obj


$path = pathinfo($file);
$dir = $path['dirname'];
$out = $dir . '/' . 'results_' . time() . '.csv';
$headers = array_keys((array)$json->$keys[0]); //can cause mis-match if each item has different keys
$lines = 0;
$fp = fopen($out, "w");
fputcsv($fp, $headers);
foreach($json->$keys as $item)
{
	fputcsv($fp, (array)$item);
	$lines++;
}
fclose($fp);
print "Wrote $lines to $out\n";
?> 