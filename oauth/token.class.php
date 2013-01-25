<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_oauth_token.php
 * 用于普通前端验证是否登录 获取用户基本信息等
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.20 rong zedong.
 **/

class oauth_token
{
	var $user_id;
	var $user_name;
	var $sp;

	function is_login(){
		if(is_array($_SESSION['OAUTH'])){
			//print_r($_SESSION['OAUTH']);
			return (isset($_SESSION['OAUTH'][$_SESSION['OAUTH']['sp']]['user_id']));
		}
		return false;
	}

	function get_token(){
		if(is_array($_SESSION['OAUTH'])){
			//print_r($_SESSION['OAUTH']);
			$this->user_id = $_SESSION['OAUTH'][$_SESSION['OAUTH']['sp']]['user_id'];
			$this->user_name = $_SESSION['OAUTH'][$_SESSION['OAUTH']['sp']]['user_name'];
			$this->sp = $_SESSION['OAUTH']['sp'];

			return (object) $_SESSION['OAUTH'];
		}
		return false;
	}

	public function __construct() {
		@session_start();
		$this->get_token();
	}
}
