<?php
require(__DIR__ . "/../_GLOBALS.php");

(count($argv) < 2) ? die("Must specify message file\n") : false;

$timeout = 10;
$parsedURL['path'] = PATH;
$parsedURL['host'] = HOST;
$parsedURL['scheme'] = "https";
$parsedURL['user'] = USERNAME;
$parsedURL['pass'] = PASSWORD;
$message = file_get_contents($argv[1]);

$headerStrs = array();
$headerStrs[] = "POST " . $parsedURL['path'] . " HTTP/1.0";
$headerStrs[] = "Host: " . $parsedURL['host'];
$headerStrs[] = "Content-Type: text/xml";
$headerStrs[] = "Content-Length: " . strlen($message);
$headerStrs[] = "Connection: close";

if(isset($parsedURL['user'], $parsedURL['pass'])) $headerStrs[] = "Authorization: Basic " . base64_encode($parsedURL['user'] . ":" . $parsedURL['pass']);

$request = join("\r\n", $headerStrs) . "\r\n\r\n" . $message;
								
// determine the port and socket scheme based on http/https
$scheme = '';
$port = 80;
if(isset($parsedURL['scheme']))
{
	switch($parsedURL['scheme'])
	{
            case 'https':
            $scheme = 'ssl://';
            $port = 443;
            break;
            case 'http':
            default:
            $scheme = '';
            $port = 80;   
        }
}

// Custom port?		
if(isset($parsedURL['port']))
{
	$port = $parsedURL['port'];
}
echo("request\n".$request."\n");		
// echo("@fsockopen(".$scheme . $parsedURL['host'].",". $port.",". $errorNumber.",". $errorString.",". $timeout.");");
	
$fp = @fsockopen($scheme . $parsedURL['host'], $port, $errorNumber, $errorString, $timeout);
if(!$fp) throw new Exception("Failed to connect to " . $parsedURL['host'] . " on port $port.\n$errorString.");
stream_set_timeout($fp, $timeout);
fwrite($fp, $request);								
			
$response = "";
while(!feof($fp))
{
	try
	{
		$response .= fgets($fp, 4096);
	}
	catch(Exception $ex)
	{
		// just continue -- sometimes we get SSL: Fatal Protocol Error from non-compliant servers
		// if no data was read, we'll catch that later and err on it
	}
}

if($response === false) throw new Exception("Problem reading data from URL: \"$url\".");
fclose($fp);
		        
$responseLines = preg_split('/[\r\n]+/', $response);
				
if(count($responseLines) > 0) $firstLine = $responseLines[0];
else $firstLine = $response;
				
if(preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
{					
	list($line, $code, $message) = $matches;
	$success = (substr($code, 0, 1) == "2");	// 2xx codes = success
	print(print_r(array("success" => $success, "message" => $firstLine),true));
}
else
{
	return array("success" => false, "message" => "Server's response was not valid HTTP");
}

?>
