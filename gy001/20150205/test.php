<?php
/**
 * 服务器端更新类，用于远程采集和远程下载
 * @author Askuy <yoyoloves@qq.com>
 */
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	include('/alidata/www/phpwind/gy/curl.class.php');
//$cube_str='ZH002754,ZH001160,ZH003926,ZH025370,ZH001259,ZH003728,ZH015588,ZH008950,ZH009732,ZH095301,ZH011113,ZH000655,ZH004610,ZH009092,ZH003331,ZH028233,ZH016293,ZH003689,ZH022991,ZH001753,ZH002422,ZH010687,ZH003694,ZH004266,ZH000724,ZH026320,ZH087179,ZH021833,ZH020780,ZH002347,ZH008038,ZH086271,ZH002120,ZH015808,ZH001301,ZH005627,ZH000283,ZH018203,ZH006080,ZH004124,ZH090854,ZH021628,ZH006699,ZH002153,ZH008669,ZH008875,ZH000661,ZH004581,ZH001456,ZH005631,ZH004470,ZH004379,ZH001393,ZH003700,ZH006105,ZH067380,ZH000889,ZH019124,ZH052204,ZH091546,ZH001823,ZH016751,ZH004988,ZH004598,ZH001396,ZH006916,ZH004858,ZH003926,ZH025370,ZH003728,ZH002233,ZH004938,ZH000464,ZH008957,ZH001463';
	//$cube_str = get_cube_str();
	//组合代码按照周收益排序前十
	//$cube_week_data = get_week_data($cube_str,10);
	$cube_str = str_replace('|',',',get_feb_cube());
	//$cube_str = 'ZH010389,ZH080840,ZH051338,ZH088113,ZH079870,ZH028141,ZH087179,ZH020232,ZH001753,ZH076120,ZH077988,ZH072803,ZH010866,ZH022991,ZH067380,ZH007569,ZH074428,ZH044111,ZH002422,ZH004379,ZH013851,ZH052204,ZH066922,ZH086245,ZH027696,ZH003738,ZH063655,ZH067469,ZH027003,ZH086762,ZH084329,ZH090854,ZH030732,ZH017420,ZH087961,ZH087953,ZH080165,ZH035462,ZH003926,ZH017505,ZH086271,ZH089246,ZH080982,ZH083420,ZH081639,ZH083447,ZH088076,ZH014837,ZH080298,ZH085648,ZH079990,ZH010581,ZH007142,ZH078146,ZH064982,ZH063799,ZH025492,ZH090681,ZH086682,ZH077584,ZH028233,ZH028299,ZH076110,ZH075474,ZH076779,ZH067842,ZH051041,ZH053075,ZH078275,ZH060242,ZH050998,ZH060236,ZH073172,ZH079317,ZH023311,ZH077410,ZH076749,ZH074731,ZH073723,ZH051392,ZH073753,ZH060316,ZH008770,ZH060227,ZH029080,ZH008950,ZH073422,ZH076363,ZH065316,ZH011345,ZH028014,ZH065163,ZH086694,ZH065618,ZH009233,ZH063539,ZH020803,ZH060332,ZH047032';
	//将组合代码排序
	//自定义最优先
	//$cube_day = array_unique(array_merge($cube_day,array_intersect($cube_week,$cube_month)));
	
	//月收益前八与组合收益前八的交集每日15点存在cube_anuals文件夹下		
	//上一周收益排名
	//$cube_anual_diff = array_diff($cube_months_total,$cube_month);
	//$filter_arr = array('ZH008256');
	$week = (int)date('W');
	$cube_week = get_week_data($cube_str,5);
	//$cube_week = array_diff($cube_week,$filter_arr);
	$mail = '496704827@qq.com';
	foreach(array_filter($cube_week) as $k=>$v){
		sendMail($v,$mail,'496704827');
	}
	
	$get_scan_code = get_scan_code();	
	$cube_day = array('ZH022991','ZH007569');
	$cube_day = array_merge($get_scan_code,$cube_day);
	$cube_day = array_diff($cube_day,$cube_week);
	$mail = '2629798245@qq.com';
	foreach(array_filter(array_unique($cube_day)) as $k=>$v){
		sendMail($v,$mail,'2629798245');
	}
	
	function get_scan_code(){
		$arr = scandir('/alidata/www/phpwind/gy/496704827');
		$str = implode('|',$arr);
		preg_match_all('/^|(ZH\d{6})/',$str,$matches);
		return array_filter($matches[1]);
	}
	//获得日收益前几名的cube值
	function get_week_data($cube_str,$limit){
		$week = (int)date('W',time());//上周
		$arr = explode(',',$cube_str);
		
		//$get_daily_gain = get_daily_gain($cube_str,'daily_gain');
		foreach(array_filter($arr) as $k=>$v){
			$file = '/alidata/www/phpwind/gy/weekdata/'.$week.'/'.$v.'_daily_gain_'.$week.'.txt';
			$cube_week[$v] = file_get_contents($file);
		/* 	$nextweek = $week+1;
			$file = '/alidata/www/phpwind/gy/weekdata/'.$nextweek.'/'.$v.'_daily_gain_'.$nextweek.'.txt';
			if(is_file($file)){
				$cube_week[$v] .= file_get_contents($file);
			}else{
				file_put_contents($file,'');
			} */
			$total[$v] = array_filter(explode('|',$cube_week[$v]));
			$total_num = 1;
			foreach($total[$v] as $key=>$value){
				$total_num *= 10.5+$value; 				
			}
			
			//$total_num *= 10.5 + $get_daily_gain[$v];
			$sqt_num = count($total[$v]);
			$cube_week_data[$v] = $total_num/pow(10,$sqt_num);
			$cube_week_data[$v] = number_format($cube_week_data[$v],4);
		}
			
		//arsort($cube_week_data);//array('ZH002754'=>'1.1*1.2',)自然算法由小到大排序
		$get_total_gain = get_daily_gain($cube_str,'total_gain');
		foreach($cube_week_data as $k=>$v){
			$cube_weeks[$k] =(150*$v +$get_total_gain[$k])/2;					
		}
		arsort($cube_weeks);
		$i = 0;
		foreach($cube_weeks as $k=>$v){		
			if($i < $limit && get_trade_times($k) > 0){
				$cube_week_arr[] = $k;
				$i++;
				//file_put_contents('/alidata/www/phpwind/gy/test.txt',$v.'|',FILE_APPEND);
			}
		}
		return $cube_week_arr;
	}	
	//每日10-15点生成数据，仅存放一天的全月和全天记录，比例为0.75+0.25
	//$type total_gain  daily_gain
	function get_daily_gain($cube_str,$type){
		$week = (int)date('W',time());
		$file_path = '/alidata/www/phpwind/gy/daysdata/cubedata_'.$type.'_'.$week.'.txt';				
		$daily_gain_cube = file_get_contents($file_path);
		$daily_cube = explode('|',$daily_gain_cube);
		foreach(array_filter(array_unique($daily_cube)) as $k=>$v){
			$arr = explode('+',$v);
			$cube_arr[$arr[0]] = $arr[1];//array('ZH087961'=>'88.16')
		}
				
		return $cube_arr;
	}
	

		//获得近两周交易次数,$k=0表示最近两周都没有交易
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
	$user_id = '4159443988';
	$mail = '496704827@qq.com';
	sendRealMail($user_id,$mail);
	//file_put_contents('/alidata/www/phpwind/gy/gettime.txt',print_r(array_merge($cube_day,$cube_manuals),true),FILE_APPEND);
	function sendRealMail($user_id,$mail){
		//$response = $http->call('http://xueqiu.com/cubes/rebalancing/history.json','GET',array('cube_symbol'=>$cube_symbol,'count'=>20,'page'=>1));
		$url = 'http://xueqiu.com/v4/statuses/user_timeline.json?user_id='.$user_id.'&page=1&type=&_=1422842777082';
		$response = task($url);
		$arr = json_decode($response,TRUE);	
		$file = '/alidata/www/phpwind/gy/'.$user_id.'_'.$mail.'.txt';
		if(!is_file($file)){
			file_put_contents($file,strtotime("today"));//-3 day today
		}	
		foreach(array_reverse($arr['statuses']) as $k=>$v){
			$s = $v['created_at']/1000;//交易时间
			$lasttime = file_get_contents($file);
			if($s - $lasttime > 0 && $v['source']=='长城证券'){
				$str =$v['timeBefore']."\r\n";
				$str.=$v['user']['screen_name'].' : '.strip_tags($v['text'])."\r\n";

				$str .="\r\n";
				$sendTime = date('H:i:s',time());
				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n";
				mail($mail,$sendTime.' '.$user_id,$str,$headers);
			
				file_put_contents($file,$s);
			}
			//file_put_contents('/alidata/www/phpwind/gy/log.txt',print_r($str,TRUE),FILE_APPEND);
		}
	}
	

	

	//file_put_contents('/alidata/www/phpwind/gy/gettime.txt',print_r(array_merge($cube_day,$cube_manuals),true),FILE_APPEND);
	function sendMail($cube_symbol,$mail,$folder){
		//$response = $http->call('http://xueqiu.com/cubes/rebalancing/history.json','GET',array('cube_symbol'=>$cube_symbol,'count'=>20,'page'=>1));
		$url = 'http://xueqiu.com/cubes/rebalancing/history.json?cube_symbol='.$cube_symbol.'&count=20&page=1';
		$response = task($url);
		$arr = json_decode($response,TRUE);	
		$file = '/alidata/www/phpwind/gy/'.$folder.'/'.$cube_symbol.'_'.$mail.'.txt';
		if(!is_file($file)){
			file_put_contents($file,strtotime("today"));//-3 day
		}	
		$cube_name = get_cube_name($cube_symbol);
		foreach(array_reverse($arr['list']) as $k=>$v){
			$s = $v['updated_at']/1000;//交易时间
			$lasttime = file_get_contents($file);
			if($s - $lasttime > 0){
				$status = ($v['status']=='success')?'':'cancel';
				$str =$status.' '.date('Y-m-d H:i:s',$s)."\r\n";
				foreach($cube_name as $cube_key=>$cube_value){
					$str.=$cube_key.' : '.$cube_value."\r\n";
				}
				$str .="\r\n";
				foreach($v['rebalancing_histories'] as $key=>$value){
					$prev_weight_adjusted = empty($value['prev_weight_adjusted'])?'0':$value['prev_weight_adjusted'];
					$str .= $value['stock_name'].': '.$prev_weight_adjusted.' to '.$value['weight'].' with price: '.$value['price']."\r\n";				
				}
					$sendTime = date('H:i:s',time());
					// To send HTML mail, the Content-type header must be set
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n";
					mail($mail,$sendTime.' '.$cube_symbol,$str,$headers);				
				file_put_contents($file,$s);
			}
			//file_put_contents('/alidata/www/phpwind/gy/log.txt',print_r($str,TRUE),FILE_APPEND);
		}
	}
	//通过传递过来的cube_str值来查询参数，返回name,month_gain,total_gain
	function get_cube_name($cube_str){
		$url = 'http://xueqiu.com/cubes/quote.json?code='.$cube_str;
		$response = task($url);
		$arr = json_decode($response,TRUE);
		return array('name'=>$arr[$cube_str]['name'],'monthly_gain'=>$arr[$cube_str]['monthly_gain'],'total_gain'=>$arr[$cube_str]['total_gain']);
	}

	
	include('/alidata/www/phpwind/gy/uniq.php');
	
	
	
	
	
	//获得连续日收益值大于某个值的组合
	function get_weeks_profit($cube_str,$limit){
		$week = date('W',time())-1;//上周
		$arr = explode(',',$cube_str);
		foreach($arr as $k=>$v){
			$file = '/alidata/www/phpwind/gy/weekdata/'.$week.'/'.$v.'_daily_gain_'.$week.'.txt';			
			$cube_week[$v] = file_get_contents($file);						
			$nextweek = $week+1;
			$file = '/alidata/www/phpwind/gy/weekdata/'.$nextweek.'/'.$v.'_daily_gain_'.$nextweek.'.txt';
			@unlink('/alidata/www/phpwind/gy/weekdata/'.$week.'/'.$v.'_daily_gain_'.$nextweek.'.txt');
			if(is_file($file)){
				$cube_week[$v] .= file_get_contents($file);
			}else{
				file_put_contents($file,'');
			}
			$total[$v] = array_filter(explode('|',$cube_week[$v]));
			$total_num = 1;
			foreach($total[$v] as $key=>$value){
				$total_num *= (100+$value)/100; 			
			}
			$cube_week_data[$v] = number_format($total_num,4);
		}
		natsort($cube_week_data);//array('ZH002754'=>'1.1*1.2',)自然算法由小到大排序
		$i = 0;
		foreach(array_reverse($cube_week_data,true) as $k=>$v){
			$i++;
			if($i==$limit){
				return $v;
			}			
		}
		
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
	//substr_count($str,'w')
	function get_history($cube_str){
		$folder = '496704827';
		$cube_months = set_cube($cube_str,'monthly_gain',20);
		$cube_anuals = set_cube($cube_str,'total_gain',20);
	
		$cube_month = array_intersect($cube_months,$cube_anuals);
		$week = (int)date('W',time());//本周,此处不需要修改
		$cube_month_str = implode('|',$cube_month);
		if(date('H-i',time())=='14-30'){
			file_put_contents('/alidata/www/phpwind/gy/anuals_cube_symbol/anuals_cube_symbol_'.$week.'.txt',$cube_month_str.'|',FILE_APPEND);
		}
		$cube = get_anuals_data();
		//$times = date('w',time())+5;
		foreach($cube as $k=>$v){
			if(!keep_cube($v,1,get_weeks_profit($cube_str,20))){
				unset($cube[$k]);
				@unlink('/alidata/www/phpwind/gy/'.$folder.'/'.$v.'_'.$folder.'@qq.com.txt');
			}
		}
		$day = (int)date('z');
		$cube_day_str = implode('|',$cube);
		if(date('H-i',time())=='14-30'){
			file_put_contents('/alidata/www/phpwind/gy/cube_days/cube_days'.$week.'.txt',$cube_day_str.'|',FILE_APPEND);
		}
		return $cube;
	}
	
	function get_anuals_data(){		
		$week = date('W',time())-1;//正式使用时应该-1上周
		//$week = ($week==3)?4:$week;
		$file = '/alidata/www/phpwind/gy/anuals_cube_symbol/anuals_cube_symbol_'.$week.'.txt';
		$cube_data = file_get_contents($file);
		$nextweek = $week+1;
		$file = '/alidata/www/phpwind/gy/anuals_cube_symbol/anuals_cube_symbol_'.$nextweek.'.txt';
		if(is_file($file)){
			$cube_data .= file_get_contents($file);
		}else{
			file_put_contents($file,'');
		}
		$arr = explode('|',$cube_data);
		$arr = array_filter(array_unique($arr));
		//return array_splice($arr,0,$limit);
		return $arr;
	}
	
	//获取字符串中出现的次数并按照出现次数排序
	function get_str_count($cube_str){
		//$cube_str = file_get_contents($file);
		$cube_arr = explode('|',$cube_str);
		foreach(array_filter(array_unique($cube_arr)) as $k=>$v){
			$cube_symbol_count[$v] = substr_count($cube_str,$v);
		}
		natsort($cube_symbol_count);
		return $cube_symbol_count;
	}
	
		//根据输入条件判断是否保留cube
	function keep_cube($cube_symbol,$k,$data){
		if(get_trade_times($cube_symbol) < $k){
			return false;//  !keep_cube 删除
		}else{
			return week_profit_judgement($cube_symbol,$data);
		}
	}
	
	//获得连续日收益值大于某个值cube值
	function week_profit_judgement($cube_symbol,$data){
		$week = date('W',time())-1;//上周
		$v = $cube_symbol;
		$file = '/alidata/www/phpwind/gy/weekdata/'.$week.'/'.$v.'_daily_gain_'.$week.'.txt';			
		$cube_week = file_get_contents($file);						
		$nextweek = $week+1;
		$file = '/alidata/www/phpwind/gy/weekdata/'.$nextweek.'/'.$v.'_daily_gain_'.$nextweek.'.txt';
		if(is_file($file)){
			$cube_week .= file_get_contents($file);
		}else{
			file_put_contents($file,'');
		}
		$total = array_filter(explode('|',$cube_week));
		$total_num = 1;
		foreach($total as $key=>$value){
			$total_num *= (100+$value)/100; 
		}
		if(number_format($total_num,4) > $data){
			return true;
		}else{
			return false;
		}
	}
	
		//早前版本获取种子cube
	function get_cube(){
		$month = (int)date('m',time());
		$month_gain_str = file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_monthly_gain_'.$month.'.txt');
		$month_gain = explode('|',$month_gain_str);
		$total_gain_str = file_get_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_gain_'.$month.'.txt');
		$total_gain = explode('|',$total_gain_str);
		$cube_str = array_filter(array_intersect($month_gain,$total_gain));
		return implode(',',$cube_str);
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

	

	
	
	

	

	
	
