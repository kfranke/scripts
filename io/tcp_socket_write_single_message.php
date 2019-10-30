<?php
error_reporting(E_ALL);
require(__DIR__ . "/../_GLOBALS.php");
(count($argv) < 2) ? die("Must specify xml file\n") : false;

$xml = file_get_contents($argv[1]);

// Should we expect a response
$expectResponse = FALSE;

/* Get the port for the WWW service. */
//$service_port = getservbyname('www', 'tcp');
$service_port = TEST_PORT;

/* Get the IP address for the target host. */
//$address = gethostbyname('www.example.com');
$address = TEST_IP_ADDRESS;

/* Create a TCP TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false)
{
    die("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
}

echo "Attempting to connect to '$address' on port '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false)
{
    die("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
} 

echo "Sending XML request...\n";
$byteWrote = socket_write($socket, $xml, strlen($xml));
echo "OK. $byteWrote B\n";
echo "Reading response...\n";
if($expectResponse === TRUE)
{
    $out = socket_read($socket, 2048);
    echo "Got response: $out";
}
else
{
    echo "No response expected!\n";
}
echo "Closing socket...\n";
socket_close($socket);
echo "Done\n\n";
?>