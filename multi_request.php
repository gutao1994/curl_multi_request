<?php

set_time_limit(0);

$url = "http://bbs.caipiao.163.com/54287.html";
$num = 1000;
$send_urls = [];

for($i=0; $i<$num; $i++){
	$send_urls[] = [
	    'url' => $url,
	];
}

$mh = curl_multi_init();
$count = count($send_urls);

foreach(array_slice($send_urls, 0, 10) as $val){
	$ch = curl_init($val['url']);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_multi_add_handle($mh, $ch);
}
if($count > 10){
	array_splice($send_urls, 0, 10);
}else{
	$send_urls = [];
}

$running = null;
$i = $j = 0;
$http_code = [];

$t_start = microtime(true);

do{
	$mrc = curl_multi_exec($mh, $running);
	curl_multi_select($mh);
			
	if($mrc == CURLM_OK){
		while($mhinfo = curl_multi_info_read($mh)){
			$i++;
			$tmp_ch = $mhinfo['handle'];
			$tmp_res = curl_getinfo($tmp_ch);
			$http_code[] = $tmp_res['http_code'];

			curl_multi_remove_handle($mh, $tmp_ch);
			curl_close($tmp_ch);
				
			if(!empty($send_urls)){
				$tmp_url = array_shift($send_urls);
					
				$ch = curl_init($tmp_url['url']);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_multi_add_handle($mh, $ch);
				$j++;
			}
		}
	}
}while($running > 0);

curl_multi_close($mh);

$t_end = microtime(true);
$usage_time = $t_end - $t_start;

echo $i . "<br/>";
echo $j . "<br/>";

echo $usage_time. "<br/>";

echo "<pre>";
print_r($http_code);






?>