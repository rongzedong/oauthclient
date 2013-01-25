<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * oauth.config.php
 * 
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.8 rong zedong.
 **/

/**
* oauthclient 得配置文件 for myqee
* myqee 用例
* config demo 
* $sp = Core::config('oauth.sp');
* 其中 oauth 取自 文件名， sp 取自 配置定义。
* 如果保存在 上层，可以通过 Core::config('core'); 获取全局配置。
*/
/*
$config['sp'] = array(
	'taobao'=>array( // http://my.open.taobao.com/xtao/website_list.htm
		'client_id'=>'',
		'client_secret'=>''),
	'renren'=>array( // http://app.renren.com/developers/newapp
		'client_id'=>'',
		'client_secret'=>''),
	'baidu'=>array( // http://developer.baidu.com/dev#/applist!type=2&only_channel_list=1
		'client_id' => '',
		'client_secret' => '',
		'method'=>'get',),
	'qq'=>array( // http://connect.qq.com/manage/
		'client_id' => '',
		'client_secret' => '',
		'scope' => 'get_user_info add_share,check_page_fans,add_t,del_t,add_pic_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idollist,add_idol,del_idol,match_nick_tips_weibo,get_intimate_friends_weibo',
		'method'=>'get',),
	'microsoft'=>array(// https://manage.dev.live.com/Applications/Index
		'client_id'=>'',
		'client_secret'=>'',
		'scope'=>'wl.signin wl.basic',
		'method'=>'get',),
	'weibo'=>array(
		'client_id'=>'',
		'client_secret'=>'',),
	);

$config['default_sp'] = 'taobao';

*/

include('config.php');