<?php
$time_start = microtime(true);
(count($argv) < 2) ? die("Must specify source file\n") : false;
$input_file = $argv[1]; // csv with a column of data we want to extract
$output_file_dir = "./"; //use trailing slash
$output_file_template = "msg";
$output_file_extension = ".xml"; // or whatever
$column_index = 7; // The is the column we'll be extracting and putting into individual files
$input_has_header_row = TRUE;
$input_line_count = 0;
echo "-----------------------------------------------------\n";
echo "Input file: $input_file\n";
if(!is_dir($output_file_dir)){
    echo "Output directoy does not exist, making it at: ".$output_file_dir."\n";
    mkdir($output_file_dir, 0755);
}
//This is just to get the number of rows of CSV.
//Other methods don't work due to newlines in field data
if (($handle = fopen($input_file, "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $input_line_count++;
    }
    fclose($handle);
}
echo "Rows in CSV: $input_line_count\n";
$file_counter = 1;
if (($handle = fopen($input_file, "r")) !== FALSE)
{
    if($input_has_header_row == TRUE)
    {
        fgetcsv($handle, 1000, ","); //Skip first row if header
        echo "CSV has a header row\n";
    }
    else echo "CSV does not have a header row\n";
    echo "Output file template: ".$output_file_dir.$output_file_template.str_pad("#",strlen($input_line_count), "#", STR_PAD_LEFT).$output_file_extension."\n";
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        for ($c=0; $c < $num; $c++) {
            $field = $data[$column_index];
            file_put_contents($output_file_dir.$output_file_template.str_pad($file_counter,strlen($input_line_count), "0", STR_PAD_LEFT).$output_file_extension, $field);
        }
        $file_counter++;
    }
    fclose($handle);
    $time_end = microtime(true);
    echo "Wrote: ".($file_counter-1)." files\n";
    echo "Processing time: ".($time_end - $time_start)."\n";
    echo "Peak memory used: ".round((memory_get_peak_usage()/1024),0)." KB\n";
}
echo "-----------------------------------------------------\n";
?>
