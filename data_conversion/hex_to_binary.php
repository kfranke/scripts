<?php
echo 'Usage script.php hexToConvert'."\n\n";
if (!$argv[1])
{
	echo 'Specify hexToConvert'."\n\n";
	exit();
}
else
{
	
	$hexstr = $argv[1];	
	echo "Converting :$hexstr \n\n";
}


$binstr = "";
for($i = 0; $i < strlen($hexstr); $i++)
{
	$hexchunk = substr($hexstr, $i, 1);
	$binchunk = str_pad(base_convert($hexchunk, 16, 2), 4, "0", STR_PAD_LEFT);
	$binstr .= $binchunk;
}

echo "$binstr"."\n\n";


?>