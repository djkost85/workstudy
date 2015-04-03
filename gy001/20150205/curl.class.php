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
	
	//返回种子字符串
	function get_cube_str(){
		$month = (int)date('m',time());
		$month_gain_cube = '|';
		$month_gain_cube .= file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_monthly_gain_'.$month.'_cube.txt');
		$month_gain_cube .= file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_gain_'.$month.'_cube.txt');
		preg_match_all('/^|(ZH\d{6})/',$month_gain_cube,$matches);
		
		return implode(',',array_filter($matches[1]));
	}
	
			//新版本用来获取种子cube
	function get_all_cube(){
		$month = (int)date('m',time());
		$month_gain_cube = file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_monthly_gain_'.$month.'_cube.txt');
		$month_gain_cube .= file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_gain_'.$month.'_cube.txt');
		$month_cube = explode('|',$month_gain_cube);
		foreach(array_filter(array_unique($month_cube)) as $k=>$v){
			$arr = explode('+',$v);
			$cube_arr[$arr[0]] = $arr[1];//array('ZH087961'=>'88.16')
		}
		arsort($cube_arr);
		$cubr_arr = array_slice($cube_arr,0,100,true);
		foreach($cubr_arr as $k=>$v){
			$cube_str .= $k.',';
		}
		return $cube_str;
	}
	
	function get_feb_cube(){
		$month = (int)date('m',time());
		$month_gain_cube = file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_gain_'.$month.'_cube.txt');
		return $month_gain_cube;
	}
	
	?>