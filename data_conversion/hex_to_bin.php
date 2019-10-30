<?php
if(!$argv[1])
{
    die("Usage: script.php hexToDecodeToBinary\n");
}
$payload = $argv[1];
/* chop 0x off payload */
//$payload = '0x702FCCBAB02E596573';
$payload = substr($payload, 2);
echo "payload: $payload \n";

/* convert hex to binary */
$bytePattern = '/[a-zA-Z0-9]{2}/';
$bytes = array();
$binPayload = '';
preg_match_all($bytePattern,$payload,$bytes);
print_r($bytes);
foreach($bytes[0] as $byte)
{
    $binPayload .= str_pad(base_convert($byte,16,2),8,'0',STR_PAD_LEFT);
}
echo "Binary Payload: $binPayload \n";

/* convert binary back to hex */
$binPattern = '/[a-zA-Z0-9]{8}/';
$twoNibbles	= array();
$hexPayload	= '';
preg_match_all($binPattern,$binPayload,$twoNibbles);
print_r($twoNibbles);
foreach($twoNibbles[0] as $twoNibble)
{
    $hexPayload .= str_pad(base_convert($twoNibble,2,16),2,'0',STR_PAD_LEFT);
}
echo "Hexidecimal Payload: ".strtoupper($hexPayload)." \n";

/* compare start payload and reassembled payload */
print "Original Payload:    $payload \nReassmbled Payload:  ".strtoupper($hexPayload)."\n";

/* battery status */
// $batteryValue	= base_convert(substr($twoNibbles[0][8],5,3),2,10);
// $batteryValue = ($batteryValue * 12.5)+12.5;
// print "Battery Value: $batteryValue \n";
?>
