<?php

$xml_str = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE stuMessages SYSTEM "http://207.88.248.155/DTD/StuMessage_Rev6.dtd"><stuMessages timeStamp="26/03/2021 18:55:38 GMT" messageID="356a066d835a836e1642113dddcd51c5">    <stuMessage>        <esn>0-287999</esn>        <unixTime>1616784938</unixTime>        <gps>Y</gps>        <payload length="9" source="pc" encoding="hex">0xCBAED455B30E690617</payload>    </stuMessage>    <stuMessage>        <esn>0-287999</esn>        <unixTime>1616784938</unixTime>        <gps>Y</gps>        <payload length="9" source="pc" encoding="hex">0xCBE5F9EBC53FD17110</payload>    </stuMessage>    <stuMessage>        <esn>0-287999</esn>        <unixTime>1616784938</unixTime>        <gps>Y</gps>        <payload length="9" source="pc" encoding="hex">0xCB55BED80F72B52F5B</payload>    </stuMessage>    <stuMessage>        <esn>0-287999</esn>        <unixTime>1616784938</unixTime>        <gps>Y</gps>        <payload length="9" source="pc" encoding="hex">0xCBBDC75282BA9BAA07</payload>    </stuMessage>    <stuMessage>        <esn>0-287999</esn>        <unixTime>1616784938</unixTime>        <gps>Y</gps>        <payload length="9" source="pc" encoding="hex">0xCB6D0737BC23C938D0</payload>    </stuMessage></stuMessages>';

$xml = simplexml_load_string($xml_str);
// print_r($xml);
print $xml['messageID']; // Access an attribute


foreach ($xml->xpath('//stuMessage') as $node) {
     print_r($node); // Access all nodes matching e.g. <stuMessage>
    
}