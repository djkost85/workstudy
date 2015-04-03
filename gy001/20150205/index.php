<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	include('/alidata/www/phpwind/gy/curl.class.php');

	function get_trade_times($cube_symbol){
		$url = 'http://xueqiu.com/cubes/rebalancing/history.json?cube_symbol='.$cube_symbol.'&count=20&page=1';
		$response = task($url);
		$arr = json_decode($response,TRUE);	
		$i = 0;
		foreach(array_reverse($arr['list']) as $k=>$v){
			$s = $v['updated_at']/1000;//交易时间
			$last_monday = strtotime("-2 weeks monday");
			if($s - $last_monday > 0){  //两周之内没有交易
				$i++;
			}
		}
		
		return $i;
	}
	
	
	function get_total_profits($cube_symbol){
			$url = 'http://xueqiu.com/cubes/quote.json?code='.$cube_symbol;
			$response = task($url);
			$arr = json_decode($response,TRUE);
			$rs = 0.6*$arr[$cube_symbol]['monthly_gain']+0.4*$arr[$cube_symbol]['total_gain'];
			return $rs;
	}
					//新版本用来获取种子cube
	$url = 'http://xueqiu.com/v4/statuses/user_timeline.json?user_id=4159443988&page=1&type=&_=1422842777082';
	$response = task($url);
	$arr = json_decode($response,TRUE);
	print_r($arr);

?>