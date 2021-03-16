<?php

echo 'Usage script.php binToConvert'."\n\n";
if (!$argv[1])
{
	echo 'Specify binToConvert'."\n\n";
	exit();
}
else
{
	
	$str = $argv[1];	
	echo "Converting :$str \n\n";
}

print "hex :" . binhex($str) . "\n";

function binhex($binstr)
{
    $hexstr = "";

    while(strlen($binstr) > 0)
    {
        if(strlen($binstr) >= 4)
        {
            // grab last 4 bits
            $binchunk = substr($binstr, strlen($binstr) - 4, 4);
            $hexstr = base_convert($binchunk, 2, 16) . $hexstr;
            $binstr = substr($binstr, 0, strlen($binstr) - 4);
        }
        else
        {
            $binchunk = str_pad($binstr, 4, "0", STR_PAD_LEFT);
            $hexstr = base_convert($binchunk, 2, 16) . $hexstr;
            $binstr = "";
        }
    }
    
    return strtoupper($hexstr);
}