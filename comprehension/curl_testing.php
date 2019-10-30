<?php


$data = array(
	'name' => 'kevin franke',
	'occupation' => 'iot',
	'title' => 'product engineer',
	'age' => 'unlimited'
	);
if($i < 0){
	
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1");
curl_setopt($ch, CURLOPT_PORT, 8080);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_exec($ch);
curl_close($ch);

?>
