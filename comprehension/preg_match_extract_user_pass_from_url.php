<?php


$address = "https://user:98myJK34?*pass@example.com/path/|cert-dir/server-cert.crt|cert-dir/server-cert-key.key||false";

if(preg_match('/(?<=https\:\/\/|http\:\/\/)(.*)(?=\@)/i', $address, $credentials) === 1)
{
	list($user, $password) = explode(':', $credentials[0]);
	$address = preg_replace('/(?<=https\:\/\/|http\:\/\/)(.*)(?<=\@)/i', '', $address);
	print_r($credentials);
}

list($address_component, $cert_component, $key_component, $passphrase_component, $allow_self_signed_component) = explode('|', $address);

$parsedURL = parse_url($address_component);

isset($user) ? $parsedURL['user'] = $user : null;
isset($password) ? $parsedURL['pass'] = $password : null;

print_r($parsedURL);