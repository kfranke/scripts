<?php
session_start();

$payload = file_get_contents(''); // some sample message
$client_cert = ''; // .pem format
$client_id = ''; // provided by ? 
$redirect_uri = 'https://gdms.felixlive.com/';
$authorization_endpoint = 'https://proxy-mtls.fedb.digitalaviationservices.com/as/authorization.oauth2';
$token_endpoint = 'https://proxy.boeingservices.com:443/as/token.oauth2';
$resource_endpoint = ''; // where do we post our data to ?

if(!isset($_GET['code'])) 
{
    $_SESSION['state'] = bin2hex(random_bytes(5));
    $_SESSION['code_verifier'] = bin2hex(random_bytes(50));
    $code_challenge = base64url_encode(hash('sha256', $_SESSION['code_verifier'], true));
    $authorize_url = $authorization_endpoint.'?'.http_build_query([
        'client_id' => $client_id,
        'response_type' => 'code',
        'scope' => 'openid profile',
        'state' => $_SESSION['state'],
        'code_challenge' => $code_challenge,
        'code_challenge_method' => 'S256',
        'redirect_uri' => $redirect_uri,
        'pemfile' => $client_cert,
    ]);
} 
else 
{
    if($_SESSION['state'] != $_GET['state'])
    {
        die('Auth server returned non-matching state. Possible CSRF attack');
    }
    if(isset($_GET['error']))
    {
        die('Auth server returned an error: '.htmlspecialchars($_GET['error']));
    }
    
    $response = http($token_endpoint, [
        'client_id' => $client_id,
        'response_type' => 'token',
        'code_verifier' => $_SESSION['code_verifier'],
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'scope' => 'openid profile',
        'state' => $_SESSION['state'],
        'redirect_uri' => $redirect_uri,
    ]);

    if(!isset($response->access_token))
    {
        die('Error fetching access token');
    }
    else 
    {
        $access_token = $response->access_token;
    } 
}

if(!isset($access_token))
{
    die('No access token');
}
else
{
    $post = http($resource_endpoint,
        [
            'payload' => $payload,
        ], 
        [
            'authorization' => ' Bearer ' . $access_token,
        ]
    );
    print 'Resource responded: ' . $post;
}

// 'base64url' variant encoding
function base64url_encode($data) { 
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function http($url, $params=false, $headers=false) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if($headers)
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if($params)
    {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    return json_decode(curl_exec($ch));
}
