<?php
require(__DIR__ . "/../_GLOBALS.php");

(count($argv) < 2) ? die("Must specify messages directory\n") : false; // /some/directory/ with trailing slash
$msgDir = $argv[1];

$file_pattern = "*.xml";
$msgDirProccessed = "./processed_replay_msgs";

$config['path'] = PATH;
$config['host'] = HOST;
$config['scheme'] = "http";
//$config['user'] = USERNAME;
//$config['pass'] = PASSWORD;
$config['timeout'] = 20;
$config['port'] = PORT;

if(!is_dir($msgDirProccessed))
{
    echo "Proccessed directoy does not exist, making it at".$msgDirProccessed."\n";
    mkdir($msgDirProccessed, 0755);
}

foreach (glob($msgDir . $file_pattern) as $filename) {
    echo "____________________________________________________\n";
    echo "Beginning transaction on ". $filename . "\n";
    $msg = file_get_contents($filename);
    
    $response = http_post($config, $msg);
    
    if($response['success'] == true)
    {
        if($response['code'] == 200)
        {
            echo "Sucessful POST with HTTP code: " . $response['code'] . "\n";
            rename($filename, $msgDirProccessed."/".basename($filename) );
            echo "Moved " . basename($filename) . " to " . $msgDirProccessed . "/" . basename($filename) . "\n";
        }
        else
        {
            echo "Responded " . $response['code'] . " not moving to successfull\n";
        }
    }
}

function http_post($parsedURL, $message)
{
    $timeout = (isset($parsedURL['timeout']) ? $parsedURL['timeout'] : 30);
    echo "timeout: " . $timeout . "\n";

    $headerStrs = array();
    $headerStrs[] = "POST " . $parsedURL['path'] . " HTTP/1.0";
    $headerStrs[] = "Host: " . $parsedURL['host'];
    $headerStrs[] = "Content-Type: text/xml";
    $headerStrs[] = "Content-Length: " . strlen($message);
    $headerStrs[] = "Connection: close";
    if(isset($parsedURL['user'], $parsedURL['pass']))
    {
        $headerStrs[] = "Authorization: Basic " . base64_encode($parsedURL['user'] . ":" . $parsedURL['pass']);
    }

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

    if(isset($parsedURL['port']))
    {
            // user specified an explicit port, so override the default
            $port = $parsedURL['port'];
    }
    // echo("request\n".$request."\n");
    $starttime = microtime(true);
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
    $endtime = microtime(true);
    $transaction_time = $endtime - $starttime;
    $responseLines = preg_split('/[\r\n]+/', $response);

    if(count($responseLines) > 0) $firstLine = $responseLines[0];
    else $firstLine = $response;

    if(preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
    {
            list($line, $code, $message) = $matches;

            $success = (substr($code, 0, 1) == "2");        // 2xx codes = success
             print(print_r(array("success" => $success, "message" => $firstLine),true));
         print "Transaction time: $transaction_time\n";
             return array("success" => true, "code" => $code, "message" => $firstLine);

    }
    else return array("success" => false, "message" => "Server's response was not valid HTTP");
}



?>