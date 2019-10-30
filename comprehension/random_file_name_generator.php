<?php

$unique = md5(rand() . microtime(true)); 
//$tempfilename = "{$filetype}_{$messageID}_{$unique}.tmp";
$message_id = "123456";
$filename_prefix = "someFile";
$filename_extension = ".tmp";
$temp_filename = $filename_prefix."_".$message_id."_".$unique.$filename_extension;
print $temp_filename."\n\n";
print strlen($temp_filename)."\n\n";
for($i = 1; $i < 5; $i++){
	print rand()."\n";
	}
?>