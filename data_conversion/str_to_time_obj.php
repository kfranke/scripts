<?php
date_default_timezone_set('UTC');
$day_start = strtotime(date("Y-m-d")."00:00:00");
$day_start = strtotime("2018-02-16 00:00:00");
$day_end = strtotime(date("Y-m-d")."23:59:59");
print "\n";
print $day_start."\n";
print $day_end."\n";

print "StartTime: ".date("M dS H:i:s e",$day_start)."\n";
print "EndTime: ".date("M dS H:i:s e",$day_end)."\n";
