<?php

include('config.php');

/***
配置信息如下，可单独保存在config.php中。
$oauth_sp = array(
	'taobao'=>array(
		'client_id'=>'ID',
		'client_secret'=>'SECRET'),
	);
*/

include('class_http_client.php');
include('class_oauth_client.php');



$op = new oauth_client($oauth_sp);

print_r($op->token);

