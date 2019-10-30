<?php
$timeZoneId = "UTC";

// run for previous day
$dateTime = new DateTime('now', new DateTimeZone($timeZoneId));
$dateTime->modify("-1 day");
$dateTime->setTime(0, 0, 0);
$startUnixTime = $dateTime->format("U");
$startTimeFormatted = $dateTime->format("Y-m-d H:i:s");
$dateTime->setTime(23, 59, 59);
$endUnixTime = $dateTime->format("U");
$endTimeFormatted = $dateTime->format("Y-m-d H:i:s");






print $startUnixTime." - ".$startTimeFormatted."\n";
print $endUnixTime." - ".$endTimeFormatted."\n";
?>
