<?php
require(__DIR__ . "/../_GLOBALS.php");
$to = TEST_SMS_ADDRESS;
$subject = "HTML email sub";

$message = "--Apple-Mail=_2C6DF486-14B1-452B-8B42-AC21FFB42CB1
Content-Transfer-Encoding: 7bit
Content-Type: text/plain;
	charset=us-ascii

line 1
line 2
url https://www.felixlive.com
--Apple-Mail=_2C6DF486-14B1-452B-8B42-AC21FFB42CB1
Content-Transfer-Encoding: 7bit
Content-Type: text/html;
	charset=us-ascii

<html><head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=us-ascii\"></head><body style=\"word-wrap: break-word; -webkit-nbsp-mode: space; line-break: after-white-space;\" class=\"\">line 1<div class=\"\">line 2</div><div class=\"\">url <a href=\"https://www.felixlive.com\" class=\"\">https://www.felixlive.com</a></div></body></html>
--Apple-Mail=_2C6DF486-14B1-452B-8B42-AC21FFB42CB1--
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "Content-Type: multipart/alternative;" . "\r\n";
$headers .= "boundary=\"Apple-Mail=_2C6DF486-14B1-452B-8B42-AC21FFB42CB1\"" . "\r\n";
$headers .= "X-Universally-Unique-Identifier: 096F10CB-A757-437E-A016-4CD8A0530627" . "\r\n";
//$headers .= "Message-Id: <1EC4BE97-59CA-41E9-894C-A58FEDE2A5ED@numerex.com>" . "\r\n";
$hearders .= "Date: Fri, 24 Aug 2018 16:59:48 -0400" ."\r\n";

// More headers
$headers .= 'From: felix <automatedmessage@numerex.com>' . "\r\n";
//$headers .= "X-Universally-Unique-Identifier: 096F10CB-A757-437E-A016-4FF8A0530627" . "\r\n";
$headers .= "Date: Fri, 24 Aug 2018 16:29:48 -0400" . "\r\n";
$headers .= "Content-Transfer-Encoding: 7bit" . "\r\n";
//$headers .= "MIME-Version: 1.0" . "\r\n";
//$headers .= "Content-Type: text/plain" . "\r\n";



mail($to,$subject,$message,$headers);
?>