<?php
/**
 * 服务器端更新类，用于远程采集和远程下载
 * @author Askuy <yoyoloves@qq.com>
 */
	 ini_set('display_errors', 1);
	error_reporting(E_ALL); 
include('/alidata/www/phpwind/gy/curl.class.php');
	function save_data($j){
		$month = (int)date('m',time());
		for($i=$j;$i<$j+500;$i++){			
			$m = ($i<10000)?'ZH00':'ZH0';
			//$response = $http->call('http://xueqiu.com/cubes/quote.json','GET',array('code'=>$m.$i));
			$url = 'http://xueqiu.com/cubes/quote.json?code='.$m.$i;
			$response = task($url);
			$arr = json_decode($response,TRUE);	
			foreach($arr as $k=>$v){
				if(isset($v['market'])&&$v['market']=='cn'&&$v['total_gain']>85&&$v['total_gain']<200){
					file_put_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_gain_'.$month.'_cube.txt',$k.',',FILE_APPEND);
				}else{
					file_put_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_'.$j.'_filter.txt',$k.',',FILE_APPEND);					
				}	
			}
			//file_put_contents('/alidata/www/phpwind/gy/cube_anuals/all_cube_total_'.$j.'_data.txt',print_r($arr,true),FILE_APPEND);
		}

	}	
	do_save();

	function do_save(){	
		$i = (int)date('H');
		if(15000*$i+1000 < 100000){		
			for($j=0;$j<30;$j++){
				$w = 2*$j;
				$w=($w<10)?'0'.$w:$w;
				if(date('i')==$w){
					save_data(15000*$i+500*$j+1000);
				}
			}
		}
	}	
	
