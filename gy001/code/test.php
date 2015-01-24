<?php
/**
 * 服务器端更新类，用于远程采集和远程下载
 * @author Askuy <yoyoloves@qq.com>
 */

include('/alidata/www/phpwind/gy/curl.class.php');
$cube_str='ZH002754,ZH001160,ZH003926,ZH025370,ZH001259,ZH003728,ZH015588,ZH008950,ZH009732,ZH095301,ZH011113,ZH000655,ZH004610,ZH009092,ZH003331,ZH028233,ZH016293,ZH003689,ZH022991,ZH001753,ZH002422,ZH010687,ZH003694,ZH004266,ZH000724,ZH026320,ZH087179,ZH021833,ZH020780,ZH002347,ZH008038,ZH086271,ZH002120,ZH015808,ZH001301,ZH005627,ZH000283,ZH018203,ZH006080,ZH004124,ZH090854,ZH021628,ZH006699,ZH002153,ZH008669,ZH008875,ZH000661,ZH004581,ZH001456,ZH005631,ZH004470,ZH004379,ZH001393,ZH003700,ZH006105,ZH067380,ZH000889,ZH019124,ZH052204,ZH091546,ZH001823,ZH016751,ZH004988,ZH004598,ZH001396,ZH006916,ZH004858,ZH003926,ZH025370,ZH003728,ZH002233,ZH004938,ZH000464,ZH008957,ZH001463';
	
	//组合代码按照周收益排序前十
	//$cube_week_data = get_week_data($cube_str,10);	
	
	//将组合代码排序
	//自定义最优先
	$cube_day = array('ZH022991','ZH020780');
	$mail = '2629798245@qq.com';
	foreach(array_filter($cube_day) as $k=>$v){
		sendMail($v,$mail);
	}
	
	//月收益前八与组合收益前八的交集每日23点存在cube_anuals文件夹下
	$cube_month = get_history($cube_str);
	$cube_month = array_diff($cube_month,$cube_day);
	$mail = '496704827@qq.com';
	foreach(array_filter($cube_month) as $k=>$v){
		sendMail($v,$mail);
	}
		
	//上一周收益排名
	//$cube_anual_diff = array_diff($cube_months_total,$cube_month);
	$cube_week = get_week_data($cube_str,5);
	$cube_week = array_diff($cube_week,$cube_day);
	$cube_week = array_diff($cube_week,$cube_month);
	$mail = '862828799@qq.com';
	foreach(array_filter($cube_week) as $k=>$v){
		sendMail($v,$mail);
	}
	
	function get_history($cube_str){
		$cube_months = set_cube($cube_str,'monthly_gain',5);
		$cube_anuals = set_cube($cube_str,'total_gain',5);
		$cube_month = array_intersect($cube_months,$cube_anuals);
		$week = date('W',time());//本周
		$cube_month_str = implode('|',$cube_month);
		if(date('H-i',time())=='15-00'){
			file_put_contents('/alidata/www/phpwind/gy/anuals_cube_symbol_'.$week.'.txt',$cube_month_str.'|',FILE_APPEND);
		}
		$cube = get_anuals_data(5);
		return $cube;
	}
	function get_anuals_data($limit){
		$week = date('W',time());//正式使用时应该-1上周
		$cube_data = file_get_contents('/alidata/www/phpwind/gy/anuals_cube_symbol_'.$week.'.txt');
		$arr = explode('|',$cube_data);
		$arr = array_filter(array_unique($arr));
		return array_splice($arr,0,$limit);
	}
	
	function get_week_data($cube_str,$limit){
		$week = date('W',time());//上周
		$arr = explode(',',$cube_str);
		foreach($arr as $k=>$v){
			$cube_week[$v] = file_get_contents('/alidata/www/phpwind/gy/weekdata/'.$v.'_daily_gain_'.$week.'.txt');
			$total[$v] = array_filter(explode('|',$cube_week[$v]));
			$total_num = 1;
			foreach($total[$v] as $key=>$value){
				$total_num *= (1+$value); 
				$cube_week_data[$v] = number_format($total_num,4);
			}
		}
			
		natsort($cube_week_data);//array('ZH002754'=>'1.1*1.2',)自然算法由小到大排序
		$i = 0;
		foreach(array_reverse($cube_week_data,true) as $k=>$v){
			if($i<$limit){
				$cube_weeks[] = $k;
				$i++;
			}			
		}
		return $cube_weeks;
	}


	//file_put_contents('/alidata/www/phpwind/gy/gettime.txt',print_r(array_merge($cube_day,$cube_manuals),true),FILE_APPEND);
	function sendMail($cube_symbol,$mail){
		//$response = $http->call('http://xueqiu.com/cubes/rebalancing/history.json','GET',array('cube_symbol'=>$cube_symbol,'count'=>20,'page'=>1));
		$url = 'http://xueqiu.com/cubes/rebalancing/history.json?cube_symbol='.$cube_symbol.'&count=20&page=1';
		$response = task($url);
		$arr = json_decode($response,TRUE);		
		if(file_get_contents('/alidata/www/phpwind/gy/'.$cube_symbol.'_'.$mail.'.txt')==''){
		   file_put_contents('/alidata/www/phpwind/gy/'.$cube_symbol.'_'.$mail.'.txt',strtotime("today"));//-3 day
		}
		foreach(array_reverse($arr['list']) as $k=>$v){
			$s = $v['updated_at']/1000;//交易时间
			$lasttime = file_get_contents('/alidata/www/phpwind/gy/'.$cube_symbol.'_'.$mail.'.txt');
			if($s - $lasttime > 0){
				$status = ($v['status']=='success')?'':'cancel';
				$str =$status.' '.date('Y-m-d H:i:s',$s)."\r\n";
				foreach($v['rebalancing_histories'] as $key=>$value){
					$str .= $value['stock_symbol'].': '.$value['prev_weight_adjusted'].'->'.$value['weight']."\r\n";
				}
				$sendTime = date('H:i:s',time());
				mail($mail,$sendTime.' '.$cube_symbol,$str);
				file_put_contents('/alidata/www/phpwind/gy/'.$cube_symbol.'_'.$mail.'.txt',$s);
			}
		}
	}
	
	function set_cube($cube_str,$sort_method,$limit){
		//$response = $http->call('http://xueqiu.com/cubes/quote.json','GET',array('code'=>$cube_str));
		$url = 'http://xueqiu.com/cubes/quote.json?code='.$cube_str;
		$response = task($url);
		$arr = json_decode($response,TRUE);
		$arr = array_sort($arr,$sort_method);//daily_gain  monthly_gain  total_gain  annualized_gain
		$i=0;
		foreach($arr as $k=>$v){
			
			if($i<$limit){	
				$cube_symbol[]= $k;
				$i++;
			}	
		}
		return $cube_symbol;
	}
  
	function array_sort($arr, $keys, $type = 'desc') {
		$keysvalue = $new_array = array();
		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}
		if ($type == 'asc') {
			asort($keysvalue);
		} else {
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k => $v) {
			$new_array[$k] = $arr[$k];
		}
		return $new_array;
	}
	

	
	
	
