#! /usr/bin/php
<?php
set_time_limit(0);
set_file_buffer(STDOUT, 0);
while (!feof(STDIN)) 
{
    $line = rtrim(fgets(STDIN), "\n");
    fputs(STDOUT, md5($line)."\n");
}

?>