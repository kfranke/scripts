<?php
error_reporting(E_ALL);

//check for valid script call
$url = $argv[1];
if($url == '') {
	print "Please supply a URL as an argument. \n $ urlParse.php http://thisurl.com/?var=that \n";
	die();
}

//$url = 'http://user:pass@domain.com:8080/path/script.php?arg1=12345&arg2=324234';

print "Supplied URL:\n";
print "$url\n";
//$url = htmlentities($url);
//$url = urlencode($url);
//print "URL Encoded URL:\n\n";
print $url;
print "\n";
// $myTestUrl = "http://user:pass@domain.com:8080/myfolder/script.php?var1=1235&var2=66666";
// print "$myTestUrl\n";
// print htmlentities($myTestUrl)."\n";


//parse out the url
$urlParts = parse_url($url);
//$urlParts = parse_url(urldecode($url));
print_r($urlParts);
print "\n\n";


//check for query options in the url
if($urlParts['query']) {
	print "Query options found\n";
	$urlQuery = $urlParts['query'];
	$urlArgs = convertUrlQuery($urlQuery);
	print_r($urlArgs);
}



function convertUrlQuery($query) { 
    $queryParts = explode('&', $query); 
    $params = array(); 
    foreach ($queryParts as $param) { 
        $item = explode('=', $param); 
        $params[$item[0]] = $item[1]; 
    } 
    return $params; 
}

?>