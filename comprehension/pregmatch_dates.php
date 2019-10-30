<?php
$fileName = "some_file_2012-01-18.A.csv";
preg_match('/(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])/',$fileName,$matches);
print_r($matches);
?>
