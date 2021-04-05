<?php
/*
Example of creating typical code verifier and code challenge pair for using in Oauth2 authentication
*/

$code_verifier = bin2hex(random_bytes(50));
$code_challenge = base64url_encode(hash('sha256', $code_verifier, true));

print "code_verifier: " . $code_verifier . "\n";
print "code_challenge: " . $code_challenge . "\n";

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}