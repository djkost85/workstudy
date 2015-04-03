<?php
$cube_unique = array('ZH022991','ZH007569');
	$mail = '753052299@qq.com';
	foreach(array_filter($cube_unique) as $k=>$v){
		sendMail($v,$mail,'uniq');
	}
?>