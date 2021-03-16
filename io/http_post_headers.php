<?php

(count($argv) < 3) ? die('~$ http_post_headers.php /dir/sample_msg.xml https://example.com/cgi-bin/script.php?queryToHeader=true&headerkey=headervalue') : false;

$message = file_get_contents($argv[1]);
$subject = '';
$address = $argv[2];

$response = send($message, $subject, $address);
print $response;

function send($message, $subject, $address)
{
		$timeout = 10;	
		$parsedURL = parse_url($address);
		
		if(!isset($parsedURL['host'], $parsedURL['path']))
		{
			$missing = array();
			
			if(!isset($parsedURL['host']))
			{
				$missing[] = 'host';
			}
			
			if(!isset($parsedURL['path']))
			{
				$missing[] = 'path';
			}							
			throw new Exception("The URL '$address' is malformed. Host, and path values are required. Missing are: " . join(", ", $missing) . ".");
		}
		
		if(isset($parsedURL['user'], $parsedURL['pass'])) $headers['Authorization'] = "Basic " . base64_encode($parsedURL['user'] . ":" . $parsedURL['pass']);
		
		if(defined("HTTP_USE_CURL") && HTTP_USE_CURL && is_callable("curl_init"))
	    {
	    	$headers = array
			(
				"Content-Type" => "text/xml",
				"Content-Length" => strlen($message)
			);
		
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $address);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_POST, true) ;
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
	        $response = curl_exec($ch);
	        curl_close($ch);
	        
			return "Server said: $response";
	    }
	    else
	    {				
			$headerStrs = array();
            $headerStrs[] = "POST " . $parsedURL['path'] . " HTTP/1.0";
			$headerStrs[] = "Host: " . $parsedURL['host'];
	        $headerStrs[] = "Content-Type: text/xml";

			if(isset($parsedURL['query']) == true) 
			{						    	
		    	$queryParts = explode('&', $parsedURL['query']); 
    			$params = array(); 
    			foreach ($queryParts as $param) 
    			{ 
        			$item = explode('=', $param); 
        			$params[$item[0]] = $item[1]; 
    			} 
    			$urlArgs = $params; 

				if(isset($urlArgs['queryToHeader']) == true) 
				{
					unset($urlArgs['queryToHeader']);
					while (list($key, $val) = each($urlArgs)) 
					{
 		 				$headerStrs[] = urldecode($key) . ": " . urldecode($val);
					}
				}
			}

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
			
			if(isset($parsedURL['port']))
			{
				// user specified an explicit port, so override the default
				$port = $parsedURL['port'];
			}
			
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

	        if($response === false) throw new Exception("Problem reading data from URL: \"$address\".");
	        fclose($fp);
	        
			$responseLines = preg_split('/[\r\n]+/', $response);
			
			if(count($responseLines) > 0) $firstLine = $responseLines[0];
			else $firstLine = $response;
			
			if(preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
			{					
				list($line, $code, $message) = $matches;
				
				$success = (substr($code, 0, 1) == "2");	// 2xx codes = success
				if($success)
				{
					return $firstLine;
				}
				else
				{
					throw new Exception($firstLine);
				}
				
			}
			else
			{
				throw new Exception("Server's response was not valid HTTP");
			}
    	}
	}

?>