#!/usr/bin/php
<?php
$url = "https://www.specialized.com/us/en/s-works-enduro/p/171282?color=264085-171282&searchText=93620-0102";
// $url = "/Users/kfranke/Downloads/site.html";
$str_start_with = "window.productConfig =";
$str_end_with = "window.productConfig.defaultSwatch =";
$site = file_get_contents($url);
$data_start_pos = (strpos($site, $str_start_with) + strlen($str_start_with) + 1);
$data_end_pos 	= (strpos($site, $str_end_with) - 10);
$data_len 		= $data_end_pos - $data_start_pos;
$data = substr($site, $data_start_pos, $data_len);
$json = json_decode($data);

$skus = array(
				array('id' => '93620-0104', 'name' => 'GLOSS DOVE GRAY'),
				array('id' => '93620-0004', 'name' => 'SATIN BLACK TINT')
			);


foreach ($skus as $sku)
{
	$item = $json->skus->{$sku['id']};
	if($item->inStock)
	{
		$msg_title 	= "In Stock Item!";
		$msg_body 	= "S-Works S4 in " . $sku['name'];
		exec('osascript -e \'display notification "' . $msg_body . '" with title "' .$msg_title.'"\' ');
	}
	else
	{
		$msg_title 	= "Out of Stock Item";
		$msg_body 	= "S-Works S4 in " . $sku['name'];
		// exec('osascript -e \'display notification "' . $msg_body . '" with title "' .$msg_title.'"\' ');		
	}
}



