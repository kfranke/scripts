<?php
require(__DIR__ . "/../_GLOBALS.php");
$to      = TEST_SMS_ADDRESS;
$subject = 'the subject';
$message = 'hello' . chr(13);

$headers = "From: Felix <automatedmessage@numerex.com>" . "\r\n" .
                        "Subject:" . "\r\n" .
                        "X-Universally-Unique-Identifier: 096F10CB-A757-437E-A016-4FF8A0530627" . "\r\n" .
                        "Date: Fri, 24 Aug 2018 16:25:48 -0400" . "\r\n" .
                        "Content-Transfer-Encoding: 7bit" . "\r\n" .
                        "MIME-Version: 1.0" . "\r\n" .
                        "Content-Type: text/plain" . "\r\n";

mail($to,$subject,$message,$headers);
echo "sent mail";
?>