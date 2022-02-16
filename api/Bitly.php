<?php
/**
 * Bitly - URL shortening service.
 *
 * @author     Kevin Franke
 * @version    1.0
 * @link
 * @method     shorten()
 * @method     expand()
 * @method     guids()
 */

// Development only. Store elsewhere for production
define('BITLY_ROOT', 'https://api-ssl.bitly.com/v4');
define('BITLY_DOMAIN', 'bit.ly');
define('BITLY_GUID', '{yourGuid}');
define('BITLY_TOKEN', '{yourToken}');

// Instantiate
$bitly = new Bitly(BITLY_TOKEN);

// Shorten URL ($short->link)
$short = $bitly->shorten('https://maps.google.com/maps?f=q&hl=en&geocode=&q=38.12345,-109.12345&ie=UTF8&z=12&om=1');
// print "short: " . "\n";
// print_r($short);

// Expand URL ($long->long_url)
$long = $bitly->expand('https://bit.ly/3gs2u7Z');
// print "long: " . "\n";
// print_r($long);

// Get GUIDs
$guids = $bitly->guids();
// print "guids: " . "\n";
// print_r($guids);


class Bitly
{
    private $domain;
    private $guid;
    private $host;
    private $token;
    private $path;
    private $options;
    private $headers;

    function __construct(string $token)
    {

        $this->token    = $token;
        $this->host     = BITLY_ROOT;
        $this->guid     = BITLY_GUID;
        $this->domain   = BITLY_DOMAIN;
        $this->options  = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        );
        $this->headers['Content-Type'] = 'Content-Type: application/json';
        $this->headers['Authorization'] = 'Authorization: Bearer ' . $this->token;
    }

    public function shorten(string $long_url, string $domain = null, string $guid = null)
    {
        $this->path = '/shorten';
        if(!empty($domain)) $this->domain = $domain;
        if(!empty($guid)) $this->guid = $guid;

        $request = array(
            'long_url' => $long_url,
            'domain' => $this->domain,
            'group_guid' => $this->guid
        );

        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_URL, $this->host . $this->path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);

        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            Notifier::send_exception_report('[BITLY]: Exception', $err);
            throw new Exception("[BITLY]: Exception. Server's response was not valid HTTP. Error: " . $err);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode($response);
        }
        curl_close($ch);
        $success = (substr($http_status, 0, 1) == "2"); // 2xx codes = success
        if(!$success)
        {
            print ("[BITLY] - Error: " . $response->message . "\n");
            $response = false;
        }
        else
        {
            print ("[BITLY] - " .__METHOD__. " " . $long_url . " To: " . $response->link . "\n");
        }
        return $response;
    }

    public function expand(string $short_url)
    {
        $this->path = '/expand';
        $parsed = parse_url($short_url);
        $bitlink_id = $parsed['host'] . $parsed['path'];
        $request = array(
            'bitlink_id' => $bitlink_id
        );

        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_URL, $this->host . $this->path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);

        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            Notifier::send_exception_report('[BITLY] - Exception', $err);
            throw new Exception("[BITLY] - Exception. Server's response was not valid HTTP. Error: " . $err);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode($response);
        }
        curl_close($ch);
        $success = (substr($http_status, 0, 1) == "2"); // 2xx codes = success
        if(!$success)
        {
            print ("[BITLY] - Error: " . $response->message . "\n");
            $response = false;
        }
        else
        {
            print ("[BITLY] - " .__METHOD__. " " . $short_url . " To: " . $response->link . "\n");
        }
        return $response;
    }

    public function guids(string $guid = null)
    {
        $this->path = '/groups';
        if(!empty($guid)) $this->path .= "/$guid";

        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_URL, $this->host . $this->path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($ch);

        if (!$response)
        {
            $err = curl_error($ch);
            $http_status = -1;
            Notifier::send_exception_report('[BITLY] - Exception', $err);
            throw new Exception("[BITLY] - Exception. Server's response was not valid HTTP. Error: " . $err);
        }
        else
        {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode($response);
        }
        curl_close($ch);
        $success = (substr($http_status, 0, 1) == "2"); // 2xx codes = success
        if(!$success)
        {
            print ("[BITLY] - Error: " . $response->message . "\n");
            $response = false;
        }
        else
        {
            $guids = recursiveFind($response, 'guid');
            print ("[BITLY] - " .__METHOD__. " (" . implode(',', $guids) . ")" . "\n");
        }
        return $response;
    }

}


function recursiveFind($array, string $needle)
    {
        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        $matches = array();
        foreach ($recursive as $key => $value)
        {
            if ($key === $needle)
            {
                array_push($matches, $value);
            }
        }
        return $matches;
    }