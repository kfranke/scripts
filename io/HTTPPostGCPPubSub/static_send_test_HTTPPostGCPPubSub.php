<?php
require(__DIR__ . "/../_GLOBALS.php");
$dir = __DIR__ . "/samples/";
$msg = file_get_contents($dir . 'trackerMessage_sxflex_Location.xml');
$sub = 'TEST';
$addr = THD_GCP_PUBSUB_ADDRESS;

$response = HTTPPostGCPPubSub::send($msg, $sub, $addr);
echo $response;

class HTTPPostGCPPubSub
{   
    /* HTTPPostGCPPubSub
    * For publishing messages to Google Cloud Platform 
    * 
    * Pub/Sub is an asynchronous messaging service that 
    * decouples services that produce events from 
    * services that process events.
    * https://cloud.google.com/pubsub/docs/overview
    */

    // const CERTS_URI = '/var/www/certs/';
    const CERTS_URI = '';

    private static $message;
    private static $subject;
    private static $address;
    private static $endpointUrl;
    private static $authUrl;
    private static $account;
    private static $scope;
    private static $topic;
    private static $accessToken;
    private static $pkPath;
    private static $passphrase;

    public static function send($message, $subject, $address)
    {
        self::initialize($message, $subject, $address);
        
        $jwt = self::jwtRequest();

        $accessToken = self::requestToken($jwt);
        
        self::$accessToken = $accessToken;
        
        $response = self::publishMessage(self::$accessToken, self::$message, self::$topic);

        return $response;
    }

    private static function initialize(string $message, string $subject, string $address)
    {
    
        list(
                $endpoint_component, 
                $authentication_component, 
                $account_component, 
                $scope_component, 
                $topic_component, 
                $key_component, 
                $passphrase_component
            ) = explode('|', $address);

        $components = compact(
            'endpoint_component', 
            'authentication_component', 
            'account_component', 
            'scope_component', 
            'topic_component', 
            'key_component'
        );
                
        $missing = array();
        foreach($components as $key => $value)
        {
            if(empty(trim($value))) $missing[] = $key;
        }
        if(count($missing) > 0)
        {
            throw new Exception("The '$address' is malformed. Missing components are: " . join(", ", $missing) . ".");   
        }

        self::$message      = $message;
        self::$subject      = $subject;
        self::$address      = $address;
        self::$endpointUrl  = $endpoint_component;
        self::$authUrl      = $authentication_component;
        self::$account      = $account_component;
        self::$scope        = $scope_component;
        self::$topic        = $topic_component;
        self::$pkPath       = $key_component;
        self::$passphrase   = $passphrase_component;

    }

    private static function jwtRequest()
    {
    
        $jwtHeader = base64url_encode(json_encode(array(
                "alg" => "RS256",
                "typ" => "JWT"
            )));

        $now = time();

        $jwtClaim = base64url_encode(json_encode(array(
                "iss"   => self::$account,
                "scope" => self::$scope,
                "aud"   => self::$authUrl,
                "exp"   => $now + 3600,
                "iat"   => $now
            )));
        
        if(strlen(self::$passphrase) != 0)
        {
            if(!file_exists(self::CERTS_URI.self::$pkPath))
                throw new Exception("The key path or filename is incorrect: " . self::CERTS_URI.self::$pkPath . ".");
            if(!file_exists(self::CERTS_URI.self::$passphrase))
                throw new Exception("The certificate path or filename is incorrect " . self::CERTS_URI.self::$passphrase . ".");
            $privateKey = openssl_pkey_get_private('file://'.self::CERTS_URI.self::$pkPath, self::$passphrase);
        }
        else
        {
            if(!file_exists(self::CERTS_URI.self::$pkPath))
                throw new Exception("The key path or filename is incorrect: " . self::CERTS_URI.self::$pkPath . ".");
            $privateKey = openssl_pkey_get_private('file://'.self::CERTS_URI.self::$pkPath);
        }
        
        openssl_sign(
            $jwtHeader.".".$jwtClaim,
            $jwtSig,
            $privateKey,
            "sha256WithRSAEncryption"
        );
        $jwtSign = base64url_encode($jwtSig);
        $jwtAssertion = $jwtHeader.".".$jwtClaim.".".$jwtSign;
        $request = "grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=".$jwtAssertion;
        
        return $request;
    }

    private static function requestToken(string $jwtRequest)
    {
        
        $headers = array
        (
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jwtRequest);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $body = null;
        if (!$response)
        {
            $body = curl_error($ch);
            $http_status = -1;
            throw new Exception("Failed getting access token. Error: " . $body);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = $response;
        }
        curl_close($ch);
        $body = json_decode($body, true);
    
        if(!$body['access_token'])
        {
            throw new Exception("Access token not given. Response: " . $body);  
        }
        else
        {
            $accessToken = $body['access_token'];   
        }
        
        return $accessToken;
    }

    private static function publishMessage(string $accessToken, string $message, string $topic)
    {
    
        if(strlen($message) < 1)
                throw new Exception("The message given is empty: " . $message);
        
        $headers = array 
        (
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        );
        
        $timeout = 10;
        
        $ch=curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$endpointUrl . self::$topic . ':publish?alt=json');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"messages":[{"data":"'. base64_encode($message) .'"}]}');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
    
        $body = null;
        if(!$response) throw new Exception("Error publishing message. Response: " . $body);
        curl_close($ch);
        
        $responseLines = preg_split('/[\r\n]+/', $response);
        if(count($responseLines) > 0) 
        {
            $firstLine = $responseLines[0];         
        }
        else
        {
            $firstLine = $response; 
        }

        if(preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
        {                   
            list($line, $code, $message) = $matches;
            
            $success = (substr($code, 0, 1) == "2");    // 2xx codes = success
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

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}