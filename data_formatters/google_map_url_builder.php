<?php

$latitude = '27.931127';
$longitude = '-82.489149';

echo get_map_URL($latitude, $longitude);

function get_map_URL($lat, $lon)
{
  return 'https://www.google.com/maps/search/?api=1&query=' . urlencode("$lat,$lon");
}

?>