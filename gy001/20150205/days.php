<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	include('/alidata/www/phpwind/gy/curl.class.php');
	//$cube_str = 'ZH010389,ZH080840,ZH051338,ZH088113,ZH079870,ZH028141,ZH087179,ZH020232,ZH001753,ZH076120,ZH077988,ZH072803,ZH010866,ZH022991,ZH067380,ZH007569,ZH074428,ZH044111,ZH002422,ZH004379,ZH013851,ZH052204,ZH066922,ZH086245,ZH027696,ZH003738,ZH063655,ZH067469,ZH027003,ZH086762,ZH084329,ZH090854,ZH030732,ZH017420,ZH087961,ZH087953,ZH080165,ZH035462,ZH003926,ZH017505,ZH086271,ZH089246,ZH080982,ZH083420,ZH081639,ZH083447,ZH088076,ZH014837,ZH080298,ZH085648,ZH079990,ZH010581,ZH007142,ZH078146,ZH064982,ZH063799,ZH025492,ZH090681,ZH086682,ZH077584,ZH028233,ZH028299,ZH076110,ZH075474,ZH076779,ZH067842,ZH051041,ZH053075,ZH078275,ZH060242,ZH050998,ZH060236,ZH073172,ZH079317,ZH023311,ZH077410,ZH076749,ZH074731,ZH073723,ZH051392,ZH073753,ZH060316,ZH008770,ZH060227,ZH029080,ZH008950,ZH073422,ZH076363,ZH065316,ZH011345,ZH028014,ZH065163,ZH086694,ZH065618,ZH009233,ZH063539,ZH020803,ZH060332,ZH047032';
	$cube_str = str_replace('|',',',get_feb_cube());
	$cube_arr = explode(',',$cube_str);
	foreach(array_filter($cube_arr) as $k=>$v){
		$url = 'http://xueqiu.com/cubes/quote.json?code='.$v;
		$response = task($url);
		$arr = json_decode($response,TRUE);
		$rs = 0.75*$arr[$v]['monthly_gain']+0.25*$arr[$v]['total_gain'];
		$str .= $v.'+'.$rs.'|';
		$daily_gain .= $v.'+'.$arr[$v]['daily_gain'].'|';
	}
	$week = (int)date('W',time());
	$hour = (int)date('H');
	if($hour > 10 && $hour < 15){
		file_put_contents('/alidata/www/phpwind/gy/daysdata/cubedata_total_gain_'.$week.'.txt',$str);
		//file_put_contents('/alidata/www/phpwind/gy/daysdata/cubedata_daily_gain_'.$week.'.txt',$daily_gain);
	}else{
		//@unlink('/alidata/www/phpwind/gy/daysdata/cubedata_daily_gain_'.$week.'.txt');
		$scan_dir = get_scan_code();
		foreach($scan_dir as $v){
			@unlink('/alidata/www/phpwind/gy/496704827/'.$v.'_496704827@qq.com.txt');
		}
	}
	
	function get_scan_code(){
		$arr = scandir('/alidata/www/phpwind/gy/496704827');
		$str = implode('|',$arr);
		preg_match_all('/^|(ZH\d{6})/',$str,$matches);
		return array_filter($matches[1]);
	}
?>