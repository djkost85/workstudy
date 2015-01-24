<?php 

function task($url) {
	//$url = "http://xueqiu.com/P/ZH010389";
    
    $ip = "100.100.".rand(1, 255).".".rand(1, 255);
    $headers = array("X-FORWARDED-FOR:$ip");
	$cookie_file =get_cookie();
	//$cookie_file = 'xq_a_token=JxDkzB0RJmf8aSDaHul92x; xq_r_token=69oXi8d7F1GWLWqFPFyBKP;'; 
  /*   $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT,  "Mozilla/4.0");
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
    $src = curl_exec($curl);
    curl_close($curl); */
	
   // $url = "http://xueqiu.com/cubes/rebalancing/history.json?cube_symbol=ZH016293&count=20&page=1";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie_file); //使用上面获取的cookies
    $response = curl_exec($ch);
    curl_close($ch);
	return  $response; 
	//return $src;
}

function get_cookie(){
	$ip = "100.100.".rand(1, 255).".".rand(1, 255);
    $headers = array("X-FORWARDED-FOR:$ip");
	$ch = curl_init('http://xueqiu.com/P/ZH010389');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// get headers too with this line
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$result = curl_exec($ch);
	// get cookie
	preg_match('/^Set-Cookie:\s*([^(]*)/mi', $result, $cookies);
	$cookies = explode(';',$cookies[0]);
	array_pop($cookies);
	$cookie = implode(';',$cookies);
	//parse_str($m[1], $cookies);
	return str_replace('Set-Cookie: ','',$cookie);
	//return $cookies;
}


	?>