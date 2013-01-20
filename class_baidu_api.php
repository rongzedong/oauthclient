<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_baidu_api.php
 * 
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.18 rong zedong.
 **/

class baidu_api
{

	var $fields = array(
		'passport/users/getInfo' => 'userid,username,realname,portrait,userdetail,birthday,marriage,sex,blood,figure,constellation,education,trade,job',
		//'passport/users/getLoggedInUser'=>'',
		);

	var $home_url 		= 'http://www.oo8h.com/';
	var $api_url 		= 'https://openapi.baidu.com/rest/2.0/';
	var $op;

	function call($action, $postFields = ''){

		$req = array(
			//'format' 		=> 'json',
			'access_token' 	=> $this->op->token['access_token'],
			//'method' 		=> $action,
			//'v' 			=> '2.0',
			);

		if(array_key_exists($action, $this->fields))
			$req['fields'] = $this->fields[$action];

		if(is_array($postFields)) $req = array_merge($req, $postFields);

		$url = $this->api_url .$action. "?" . http_build_query($req, '', '&');
		//echo($url);
		$rp = json_decode($this->op->get($url));
		//$response_name = str_replace('.', '_', $action) . "_response"; 
		return $rp;
	}

	public static function generateSign($params, $secret, $namespace = 'sign')
	{
		$str = '';
		ksort($params);
		foreach ($params as $k => $v) {
			if ($k != $namespace) {
				$str .= "$k=$v";
			}
		}
		$str .= $secret;
		return md5($str);
	}

	public function __construct(&$op) {
		$this->op = &$op;
	}
}
