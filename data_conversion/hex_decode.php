<?php

$hex = "0xCB477616FF99E202070011000D1E00008A54834B825A8263000000000000010430040300";
$hex = substr($hex,0,2) == "0x" ? substr($hex,2) : $hex;
print "Hex: $hex\n";
$byteArray = "";
for ($i=0, $j=0; $i < strlen($hex); $i+=2, $j++) {
  $byte_hex = substr($hex, $i, 2);
  $byte_bin = hexbin($byte_hex);
  print "Byte: ". str_pad($j,2,"0",STR_PAD_LEFT) ." - $byte_hex - $byte_bin\n";
  $byteArray[] = array(
                      'num' => $j,
                      'hex' => $byte_hex,
                      'bin' => $byte_bin
                    );
}

function hexbin($hexstr)
{
    $binstr = "";
    for($i = 0; $i < strlen($hexstr); $i++)
    {
        $hexchunk = substr($hexstr, $i, 1);
        $binchunk = str_pad(base_convert($hexchunk, 16, 2), 4, "0", STR_PAD_LEFT);

       $binstr .= $binchunk;
    }
    return $binstr;
}
 ?>
