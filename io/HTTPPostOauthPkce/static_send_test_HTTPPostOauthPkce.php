<?php
require(__DIR__ . "/../_GLOBALS.php");
$dir = __DIR__ . "/samples/";

$msgs = array();
// should 200 ok (valid ESN)
$msgs[] = file_get_contents($dir . 'valid-200.xml'); 
// should 204 no content (invalid ESN)
$msgs[] = file_get_contents($dir . 'valid-204');
// should fail. 500 Internal Server Error (wrong format XML)
$msgs[] = file_get_contents($dir . 'invalid-500.xml');

$sub = 'TEST';

$addr_dev = GDMS_FEDPROXY_DEV_ADDRESS;

$addr_prod = GDMS_FEDPROXY_PROD_ADDRESS;

$test_dev = true;
$test_prod = false;


/*


*/

if($test_dev)
{
    foreach($msgs as $msg)
    {
        print "Testing to DEV with: \n $msg \n";
        $response = HTTPPostOauthPkce::send($msg, $sub, $addr_dev);
        print "Test response to DEV: " . $response . "\n";          
    }
    
}
if($test_prod)
{
    foreach($msgs as $msg)
    {
        print "Testing to PROD with: \n $msg \n";
        $response = HTTPPostOauthPkce::send($msg, $sub, $addr_prod);
        print "Test response to PROD: " . $response . "\n"; 
    }
}


class HTTPPostOauthPkce
{   
    /* HTTPPostOauthPkce
    * Format: "EndpointURL|AuthURL|TokenURL|RedirectURL|ClientID|Scope|SSLCert|SSLKey|SSLPassphrase|usenonce|usestate" 
    * [OPTIONAL] SSLCert, SSLKey, SSLPassphrase, usenonce, usestate
    */

    const CERTS_URI = '/var/www/certs/';

    private static $message;
    private static $subject;
    private static $address;
    private static $endpointUrl;
    private static $authUrl;
    private static $tokenUrl;
    private static $redirectUrl;
    private static $clientId;
    private static $scope;
    private static $useState;
    private static $stateSet;
    private static $stateGot;
    private static $useNonce;
    private static $nonceSet;
    private static $nonceGot;
    private static $codeVerifier;
    private static $codeChallenge;
    private static $accessCode;
    private static $accessToken;
    private static $refreshToken;
    private static $tokenExpiresIn;
    private static $crtPath;
    private static $pkPath;
    private static $passphrase;

    public static function send($message, $subject, $address)
    {
        self::initialize($message, $subject, $address);

        $accessCode = self::requestCode();
        
        $accessToken = self::requestToken($accessCode);

        $response = self::publishMessage($accessToken, self::$message);
        
        return $response;
    }

    private static function initialize(string $message, string $subject, string $address)
    {
        print "Initializing...\n";
        list(
                $endpointUri_component['value'],
                $authUri_component['value'],
                $tokenUri_component['value'],
                $redirectUri_component['value'],
                $clientId_component['value'],
                $scope_component['value'],
                $cert_component['value'],
                $key_component['value'], 
                $passphrase_component['value'],
                $usenonce_component['value'],
                $usestate_component['value']
            ) = explode('|', $address);

        $components = compact(
            'endpointUri_component',
            'authUri_component',
            'tokenUri_component',
            'redirectUri_component', 
            'clientId_component', 
            'scope_component', 
            'cert_component', // optional
            'key_component', // optional
            'passphrase_component', // optional
            'usenonce_component', // optional
            'usestate_component' // optional
        );
        
        $requires = array(
            'endpointUri_component',
            'authUri_component',
            'tokenUri_component',
            'redirectUri_component', 
            'clientId_component', 
            'scope_component'
        );
        
        $optionals = array('cert_component', 'key_component', 'passphrase_component');
        
        foreach ($requires as $key) $components[$key]['required'] = true;
        
        $missing = array();
        foreach($components as $key => $value)
        {
            if(empty(trim($value['value'])) && isset($value['required'])) $missing[] = $key;
        }
        if(count($missing) > 0)
        {
            throw new Exception("The '$address' is malformed. Missing components are: " . join(", ", $missing) . ".");   
        }
        if(strlen($components['cert_component']['value']) > 0 && !file_exists(self::CERTS_URI.$components['cert_component']['value']))
        {
            throw new Exception("The cert path or filename is incorrect: " . self::CERTS_URI.$components['cert_component']['value'] . ".");
        }
        if(strlen($components['key_component']['value']) > 0 && !file_exists(self::CERTS_URI.$components['key_component']['value']))
        {
            throw new Exception("The key path or filename is incorrect: " . self::CERTS_URI.$components['key_component']['value'] . ".");
        }
        if(strlen($components['passphrase_component']['value']) > 0 && !file_exists(self::CERTS_URI.$components['passphrase_component']['value']))
        {
            throw new Exception("The passphrase path or filename is incorrect: " . self::CERTS_URI.$components['passphrase_component']['value'] . ".");
        }

        self::$message       = $message;
        self::$subject       = $subject;
        self::$address       = $address;
        self::$endpointUrl   = $endpointUri_component['value'];
        self::$authUrl       = $authUri_component['value'];
        self::$tokenUrl      = $tokenUri_component['value'];
        self::$redirectUrl   = $redirectUri_component['value'];
        self::$clientId      = $clientId_component['value'];
        self::$scope         = $scope_component['value'];
        self::$useState      = (!empty(trim($components['usestate_component']['value']))) ? true : false;
        self::$stateSet      = (self::$useState) ? bin2hex(random_bytes(5)) : null;
        self::$useNonce      = (!empty(trim($components['usenonce_component']['value']))) ? true : false;
        self::$nonceSet      = (self::$useNonce) ? bin2hex(random_bytes(5)) : null;
        self::$codeVerifier  = bin2hex(random_bytes(50));
        self::$codeChallenge = DataUtils::base64url_encode(hash('sha256', self::$codeVerifier, true));
        self::$crtPath       = $cert_component['value'];
        self::$pkPath        = $key_component['value'];
        self::$passphrase    = $passphrase_component['value'];
    }

    private static function requestCode()
    {
        print "Requesting access code...\n";
        $curl = "curl -s -k -v --cert " . self::$crtPath . " --key " . self::$pkPath;
        $curl .= " -d scope=" . self::$scope;
        $curl .= " -d client_id=" . self::$clientId;
        $curl .= " -d response_type=" . 'code';
        (self::$useState) ? $curl .= " -d state=" . self::$stateSet : null;
        $curl .= " -d code_challenge=" . self::$codeChallenge;
        $curl .= " -d code_challenge_method=" . "S256";
        (self::$useNonce) ? $curl .= " -d nonce=" . self::$nonceSet : null;
        $curl .= " -d redirect_uri=" . self::$redirectUrl;
        $curl .= " --url " . self::$authUrl;
        print "curl: " . $curl . "\n";

        $headers = array
        (
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        );

        $claim = array(
                    'client_id'             => self::$clientId,
                    'scope'                 => self::$scope,
                    'response_type'         => 'code',
                    'state'                 => self::$stateSet,
                    'code_challenge'        => self::$codeChallenge,
                    'code_challenge_method' => 'S256',
                    'redirect_uri'          => self::$redirectUrl
                    );
        
        (self::$useState) ? $claim['state'] = self::$stateSet : null;
        (self::$useNonce) ? $claim['nonce'] = self::$nonceSet : null;
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($claim));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        (strlen(self::$crtPath) > 0) ? curl_setopt($ch, CURLOPT_SSLCERT, self::CERTS_URI.self::$crtPath) : null;
        (strlen(self::$pkPath) > 0) ? curl_setopt($ch, CURLOPT_SSLKEY, self::CERTS_URI.self::$pkPath) : null;
        (strlen(self::$passphrase) > 0) ? curl_setopt($ch, CURLOPT_SSLKEYPASSWD, file_get_contents(self::CERTS_URI.self::$passphrase)) : null;
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            throw new Exception("Server's response was not valid HTTP. Error: " . $err);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        $responseLines = preg_split('/[\r\n]+/', $response);
        if(count($responseLines) > 0) 
        {
            foreach($responseLines as $line)
            {
                if(stripos($line, 'HTTP') === 0)
                {
                    $firstLine = $line; // in the case of multiple HTTP responses
                }
                if(stripos($line, 'Location:') === 0)
                {
                    list($key, $value) = explode(' ', $line, 2);
                    parse_str(parse_url(trim($value), PHP_URL_QUERY), $query);
                    foreach($query as $param => $value)
                    {
                        switch ($param)
                        {
                            case 'code':
                                self::$accessCode = $value;
                                break;
                            case 'state':
                                self::$stateGot = $value;
                                break;
                            case 'nonce':
                                self::$nonceGot = $value;
                                break;
                        }
                    }
                }    
            }                
        }
        else
        {
            $firstLine = $response; 
        }

        if(!preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
        {
            throw new Exception("Server's response was not valid HTTP");
        }
        $success = (substr($http_status, 0, 1) == "3"); // 3xx codes = success
        if(!$success) throw new Exception($firstLine);        
        if(self::$useNonce && self::$nonceSet != self::$nonceGot)
        {
            throw new Exception("Nonce mismatch. Asserted: " . self::$nonceSet . " Returned: " . self::$nonceGot);
        }
        if(self::$useState && self::$stateSet != self::$stateGot)
        {
            throw new Exception("State mismatch. Asserted: " . self::$stateSet . " Returned: " . self::$stateGot);
        }
        if(strlen(self::$accessCode) > 0) return self::$accessCode;
        else throw new Exception("Code not given. Returned: " . print_r($query, true));
    }

    private static function requestToken(string $access_code)
    {
        print "Requesting access token...\n";
        $curl = "curl -k -v";
        $curl .= " -d client_id=" . self::$clientId;
        $curl .= " -d redirect_uri=" . self::$redirectUrl;
        $curl .= " -d code_verifier=" . self::$codeVerifier;
        $curl .= " -d grant_type=" . 'authorization_code';
        $curl .= " -d code=" . self::$accessCode;
        $curl .= " -d response_type=" . 'token';
        $curl .= " --url " . self::$tokenUrl;
        print "curl: " . $curl . "\n";

        $headers = array
        (
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded'
        );

        $claim = array(
                    'client_id'     => self::$clientId,
                    'redirect_uri'  => self::$redirectUrl,
                    'code_verifier' => self::$codeVerifier,
                    'grant_type'    => 'authorization_code',
                    'code'          => $access_code,
                    'response_type' => 'token'                   
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($claim));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            throw new Exception("Error getting access code. Response: " . $body);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        $responseLines = preg_split('/[\r\n]+/', $response);
        if(count($responseLines) > 0) 
        {
            foreach($responseLines as $line)
            {
                if(stripos($line, 'HTTP') === 0)
                {
                    $firstLine = $line; // in the case of multiple HTTP responses
                }
                else
                {
                    $firstLine = $responseLines[0];
                }
            }                
        }
        else
        {
            $firstLine = $response; 
        }
        if(!preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
        {
            throw new Exception("Server's response was not valid HTTP");
        }
        $success = (substr($http_status, 0, 1) == "2"); // 2xx codes = success
        $body = json_decode(array_pop($responseLines), true);
        if(!$success) throw new Exception($firstLine . ' - \'' . (isset($body['error']) ? $body['error'] . '\'' : ''));
        self::$accessToken = isset($body['access_token']) ? $body['access_token'] : null;
        self::$refreshToken = isset($body['refresh_token']) ? $body['refresh_token'] : null;
        self::$tokenExpiresIn = isset($body['expires_in']) ? $body['expires_in'] : null;
        if(strlen(self::$accessToken) > 0) return self::$accessToken;
        else throw new Exception("Token not given. Returned: " . $body);
    }

    private static function publishMessage(string $accessToken, string $message)
    {
        print "Publishing message...\n";
        $curl = "curl -k -v ";
        $curl .= " -H " . '\'Authorization: Bearer ' . $accessToken . '\'';
        $curl .= " -d " . '\'' . $message .'\'';
        $curl .= " --url " . self::$endpointUrl;
        print "curl: " . $curl . "\n";

        if(strlen($message) < 1)
                throw new Exception("The message given is empty: " . $message);
        $headers = array 
        (
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: text/xml',
            'Content-Length: ' . strlen($message)
        );
        
        $timeout = 10;
        
        $ch=curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$endpointUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            throw new Exception("Error publishing message. Response: " . $err);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);

        $responseLines = preg_split('/[\r\n]+/', $response);
        if(count($responseLines) > 0) 
        {
            foreach($responseLines as $line)
            {
                if(stripos($line, 'HTTP') === 0)
                {
                    $firstLine = $line; // in the case of multiple HTTP responses
                }    
            }                
        }
        else
        {
            $firstLine = $response; 
        }
        if(!preg_match('/^HTTP\/\d+\.\d+ (\d{3}) (.*)$/', $firstLine, $matches))
        {
            throw new Exception("Server's response was not valid HTTP");
        }
        $success = (substr($http_status, 0, 1) == "2"); // 2xx codes = success        
        if($success) return $firstLine;
        else throw new Exception($firstLine);
    }
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}