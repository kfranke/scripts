<?php

(count($argv) < 2) ? die("Must specify csv with pings\n") : false;

$xmlHead = '<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE stuMessages SYSTEM "http://cody.glpconnect.com/DTD/StuMessage_Rev6.dtd">
<stuMessages messageID="790648da343624b8f0eae5ffddc534bd" timeStamp="19/12/2011 15:47:56 GMT">
';
$xmlFoot = '</stuMessages>';
// csv is expecting 
// esn,hexpayload,unixtime
$pingFile = $argv[1];

$xml = $xmlHead;
$row = 1;
if (($handle = fopen($pingFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num 		= count($data);
		$stuHead	= '<stuMessage>';
		$esn 	 	= '<esn>'.$data[0].'</esn>';
		$gps		=	'<gps>N</gps>';
		$payload	= '<payload length="9" source="pc" encoding="hex">'.$data[1].'</payload>';
		$unixtime	= '<unixTime>'.$data[2].'</unixTime>';
		$stuFoot	= '</stuMessage>';
		$xml		.= $stuHead.$esn.$unixtime.$gps.$payload.$stuFoot;
		echo $stuHead.$esn.$unixtime.$gps.$payload.$stuFoot."\n";
        $row++;
    }
    fclose($handle);
}
$xml .= $xmlFoot;
print $xml;


?>
