<?php

$gmt_start_date = DateTime::createFromFormat('m/d/yH:i:s', '01/30/19'.'00:00:00', new DateTimeZone('UTC'));
$local_start_date = DateTime::createFromFormat('m/d/yH:i:s', '01/30/19'.'00:00:00', new DateTimeZone('EST'));

echo $gmt_start_date->format('Y-m-d H:i:s T');
echo "\n";
echo $gmt_start_date->format('U');
echo "\n";
echo $local_start_date->format('Y-m-d H:i:s T');
echo "\n";
echo $local_start_date->format('U');
echo "\n";
// H:i:s
// 00:00:00

// 0 - $offset, 0, 0, $start_date[0], $start_date[1], (2000 + $start_date[2]

// $start_time   = mktime(0 - $offset, 0, 0, $start_date[0], $start_date[1], (2000 + $start_date[2]));
// $end_time     = mktime(23 - $offset, 59, 59, $end_date[0], $end_date[1], (2000 + $end_date[2]));
?>