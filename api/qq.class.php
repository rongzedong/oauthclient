<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_qq_api.php
 * 
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.18 rong zedong.
 **/

class api_qq
{

	var $fields = array(
		//'user/get_user_info' => '',
		);

	var $home_url 		= 'http://www.oo8h.com/';
	var $api_url 		= 'https://graph.qq.com/';

	var $op;

	function call($action, $postFields = ''){

		$req["access_token"] = $this->op->token['access_token'];
		$req["oauth_consumer_key"] = $this->op->client_id;
		$req["openid"] = $this->op->token['user_id'];
		$req["format"] = "json";

		if(array_key_exists($action, $this->fields))
			$req['fields'] = $this->fields[$action];

		if(is_array($postFields)) $req = array_merge($req, $postFields);

		$url = $this->api_url . $action . "?" . http_build_query($req, '', '&');
		//echo($url);
		$rp = $this->op->get($url);
		//print_r($rp);
		//$response_name = str_replace('.', '_', $action) . "_response"; 
		return $rp;
	}

	public function __construct(&$op) {
		$this->op = &$op;
	}
}

