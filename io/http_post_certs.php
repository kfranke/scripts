<?php

define("HTTP_USE_CURL", false);

$sub = 'test';
$url = "https://user:pass@example.com/path/|dir/server.crt|dir/private.key||false";
$msg = file_get_contents("/samples/file.xml");
$response = send($msg, $sub, $url);

print_r($response);

function send($message, $subject, $address)
{
	$timeout = 10;

	$baseCertificatePath = "/var/www/certs/";
	
    	if(preg_match('/(?<=https\:\/\/|http\:\/\/)(.*)(?=\@)/i', $address, $credentials) === 1)
	{
		list($user, $password) = explode(':', $credentials[0]);
		$address = preg_replace('/(?<=https\:\/\/|http\:\/\/)(.*)(?<=\@)/i', '', $address);
	}
    
	list($address_component, $cert_component, $key_component, $passphrase_component, $allow_self_signed_component) = explode('|', $address);

	$parsedURL = parse_url($address_component);
    
    	isset($user) ? $parsedURL['user'] = $user : null;
	isset($password) ? $parsedURL['pass'] = $password : null;
	
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

		if(!isset($cert_component))
		{
			$missing[] = 'certificate file';
		}

		if(!isset($key_component))
		{
			$missing[] = 'key file';
		}

		if(!isset($allow_self_signed_component))
		{
			$missing[] = 'allow self signed boolean';
		}
		
		throw new Exception("The URL component '$address' is malformed. Host, path, port, certificate path, key path and allow self signed boolean values are required. Missing are: " . join(", ", $missing) . ".");
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


		if(!file_exists($baseCertificatePath . $cert_component))
			throw new Exception("The certificate path or filename is incorrect " . $cert_component . ".");
		if(!file_exists($baseCertificatePath . $key_component))
			throw new Exception("The key path or filename is incorrect " . $cert_component . ".");

		if(isset($passphrase_component))
		{			
			$opts = array(
          		'ssl' => array(
          		'local_cert' => $baseCertificatePath . $cert_component,
          		'local_pk' => $baseCertificatePath . $key_component,
          		'passphrase' => $passphrase_component,
				'verify_peer' => true,
				'verify_peer_name'  => false,
				'capath' => '/etc/ssl/certs/',
          		'allow_self_signed' => $allow_self_signed_component
          		)
        	);
		} else {
			$opts = array(
          		'ssl' => array(
          		'local_cert' => $baseCertificatePath . $cert_component,
          		'local_pk' => $baseCertificatePath . $key_component,	          		
          		'verify_peer' => true,
				'capath' => '/etc/ssl/certs/',
          		'allow_self_signed' => $allow_self_signed_component
          		)
        	);
		}

			$context = stream_context_create($opts);
    	$fp = stream_socket_client ($scheme . $parsedURL['host'] . ":" . $port, $errorNumber, $errorString, $timeout, STREAM_CLIENT_CONNECT, $context);
    	if (!$fp) throw new Exception("Failed to connect to " . $parsedURL['host'] . " on port $port.\n$errorString.");
		
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
