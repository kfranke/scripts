<?php
require(__DIR__ . "/../_GLOBALS.php");
$to = TEST_SMS_ADDRESS;

$subject = "plain email sub";

$message = "the body with charset spec";

//$headers = "Content-Type: text/plain" . "\r\n";
$headers = "Content-Type: text/plain; charset=\"utf-8\"" . "\r\n";
// Always set content-type when sending HTML email
//$headers = "MIME-Version: 1.0" . "\r\n";
//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: felix <automatedmessage@example.com>' . "\r\n";
//$headers .= "X-Universally-Unique-Identifier: 096F10CB-A757-437E-A016-4FF8A0530627" . "\r\n";
$headers .= "Date: Fri, 24 Aug 2018 16:35:48 -0400" . "\r\n";
$headers .= "Content-Transfer-Encoding: 7bit" . "\r\n";
//$headers .= "MIME-Version: 1.0" . "\r\n";


mail($to,$subject,$message,$headers);
?>