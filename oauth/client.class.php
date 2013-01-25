<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_oauth_client.php
 * 继承自 http client class 因为所有的 oauth操作都是基于 http进行的操作。
 * 
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.8 rong zedong.
 **/

class oauth_client extends http_client
{

	var $client_id = ''; 								// 配置信息
	var $client_secret = '';							// 配置信息

	var $access_token_type = '';						// 返回得token类型，可以为空，没啥用
	var $access_token_error = '';						// 换token时候错误保存在此	
	var $access_token_url = ''; 						// code 换 token得请求地址


	var $oauth_server = '';								// sp 提供商
	var $oauth_dialog_url = '';							// 根据sp提供商设置登录地址
	var $oauth_version = '2.0';
	var $oauth_url_parameters = false;
	var $oauth_authorization_header = true;
	var $oauth_redirect_uri = ''; 						// 返回地址
	var $oauth_scope = ''; 								// 请求权限， 微软 必须输入该参数，其他不是必须
	var $oauth_grant_type = 'authorization_code';
	var $oauth_authorization_error = ''; 				// 错误，暂时没用到
	var $oauth_user_agent = 'PHP_OAuth_API_Client'; 	// 没用
	var $oauth_session_started = false; 				// 是否成功开启 session
	var $oauth_token_name = 'OAUTH';
	var $token; 										// 从 session读取得 token信息


	/**
	* 第一步，获取登录地址，进行登录操作
	*/
	public function dialog_url(){
		return $this->oauth_dialog_url;
	}

	public function get_oauth_state(){
		if($_SESSION[$this->oauth_token_name][$this->oauth_server]['state'])
		{
			$this->state = $_SESSION[$this->oauth_token_name][$this->oauth_server]['state'];
		}
		else
		{
			$this->state = 'sp:'.$this->oauth_server.';key:'.rand(1,1000).';';
			$_SESSION[$this->oauth_token_name][$this->oauth_server]['state'] = $this->state;
		}
		return $this->state;
	}

	function chushihua_state_and_sp($default_sp = ''){
		if($_GET['state']){

			$_GET['state'] = str_replace('$', ';', $_GET['state']); // for microsoft
			foreach (explode(';', urldecode($_GET['state'])) as $k) {
				# code...
				$k = explode(':', $k);
				if($k[0])
					$state[$k[0]] = $k[1];
			}
		}
		if($state['sp'])
		{// test part. 使用 sp 参数传回的时候，就刷新token
			unset($_SESSION[$this->oauth_token_name][$this->oauth_server]);
			$this->oauth_server = $state['sp'];
			$_SESSION[$this->oauth_token_name]['sp'] = $this->oauth_server;
			return $this->oauth_server;
		}

		if($_GET['sp'])
		{

			$this->oauth_server = $_GET['sp'];
			$_SESSION[$this->oauth_token_name]['sp'] = $this->oauth_server;
			return $this->oauth_server;
		}

		if($default_sp)
		{

			$this->oauth_server = $default_sp;
			$_SESSION[$this->oauth_token_name]['sp'] = $this->oauth_server;
			return $this->oauth_server;
		}
		throw new Exception("需要指定一个 oauth得sp服务商。", 1);
		return false;
	}

	/**
	* 第0步 初始化类 初始化基础信息
	*/
	public function __construct($oauth_sp, $default_sp = '') {
		parent::__construct();
		// 开启 session
		if(!$this->session_started && !session_start())
		{
			throw new exception('请打开 PHP 的 session 支持。');
			return false;
		}

		//$cookies_life_time = 24 * 3600;   //过期时间，单位为秒，这里的设置即为一天
		//setcookie(session_name() ,session_id(), time() + $cookies_life_time, "/");

		$this->session_started = true;

		$this->chushihua_state_and_sp($default_sp);
		$this->get_oauth_state();

		if(array_key_exists($this->oauth_server, $oauth_sp))
		{
			$this->client_id = $oauth_sp[$this->oauth_server]['client_id'];
			$this->client_secret = $oauth_sp[$this->oauth_server]['client_secret'];
			$this->oauth_scope = $oauth_sp[$this->oauth_server]['scope'];
		}else{
			throw new Exception("需要为sp服务商增加配置信息（config.php）。", 1);
		}

		// 默认返回请求本类实例的php程序
		if(class_exists('Core', true))
		{
			$this->oauth_redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["PATH_INFO"];
		}else{
			//$this->oauth_redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$this->oauth_redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["SCRIPT_NAME"];
			//_SERVER["SCRIPT_NAME"];
		}

		$this->debug = 1;
		//exit($client_id);
		//echo('info');

		switch ($this->oauth_server) {
			case 'renren':
				# code...
				{
					$this->access_token_url = 'https://graph.renren.com/oauth/token';
					$this->oauth_dialog_url = 'https://graph.renren.com/oauth/authorize' . 
					'?client_id=' . $this->client_id .
					'&redirect_uri=' . $this->oauth_redirect_uri .
					'&response_type=code&scope=' . $this->oauth_scope.
					'&state=' . $this->state;
				}
				break;
			case 'microsoft':
				{
					$this->access_token_url = 'https://login.live.com/oauth20_token.srf';
					$this->oauth_dialog_url = 'https://login.live.com/oauth20_authorize.srf'.
					'?client_id=' . $this->client_id .
					'&redirect_uri=' . $this->oauth_redirect_uri .
					'&response_type=code&scope=' . $this->oauth_scope.
					'&state=' . $this->state;
				}
				break;
			case 'taobao':
				{
					$this->access_token_url = 'https://oauth.taobao.com/token';
					$this->oauth_dialog_url = 'https://oauth.taobao.com/authorize' .
					'?client_id=' . $this->client_id .
					'&redirect_uri=' . $this->oauth_redirect_uri .
					'&response_type=code&scope=' . $this->oauth_scope.
					'&state=' . $this->state;
				}
				break;
			case 'google':
			{
				throw new Exception("no support yet.", 1);
				
				$this->access_token_url = 'https://accounts.google.com/o/oauth2/token';
				$this->oauth_dialog_url = 'https://accounts.google.com/o/oauth2/auth'.
				'?client_id=' . $this->client_id .
				'&redirect_uri=' . $this->oauth_redirect_uri .
				'&response_type=code&scope=' . $this->oauth_scope.
				'&state=' . $this->state;
				break;
			}
			case 'baidu':
			{
				$params = array(
					'client_id'		=> $this->client_id,
					'response_type'	=> 'code',
					'redirect_uri'	=> $this->oauth_redirect_uri,
					'scope'			=> $this->oauth_scope,
					'state'			=> $this->state,
					//'display'		=> $display,
				);
				$this->oauth_dialog_url = 'https://openapi.baidu.com/oauth/2.0/authorize' . '?' . http_build_query($params, '', '&');
				$this->access_token_url = 'https://openapi.baidu.com/oauth/2.0/token';
				break;
			}
			case 'qq':
			{
				$this->access_token_url = 'https://graph.qq.com/oauth2.0/token';
				$this->oauth_dialog_url = 'https://graph.qq.com/oauth2.0/authorize'.
				'?client_id=' . $this->client_id .
				'&redirect_uri=' . $this->oauth_redirect_uri .
				'&response_type=code&scope=' . $this->oauth_scope.
				'&state=' . $this->state;
				break;
			}

			case 'weibo':
			{
				$this->access_token_url = 'https://api.weibo.com/oauth2/access_token';
				$this->oauth_dialog_url = 'https://api.weibo.com/oauth2/authorize'.
				'?client_id=' . $this->client_id .
				'&redirect_uri=' . $this->oauth_redirect_uri .
				'&response_type=code&scope=' . $this->oauth_scope.
				'&state=' . $this->state;
				break;
			}

		default:
				# code...
				throw new Exception("必须输入一个参数作为 oauth_server 提供商，可用的有  renren, microsoft, taobao 等", 1);
					return false;
				break;
		}

		// 	正常初始化完毕，如果已经登录则读取token
		if($_SESSION[$this->oauth_token_name][$this->oauth_server]['status'])
		{
			$this->token = &$_SESSION[$this->oauth_token_name][$this->oauth_server];
		}
		else{
			// login else
			if($_GET['code'] && $_GET['state'])
			{
				$method = ($oauth_sp[$this->oauth_server]['method'])?$oauth_sp[$this->oauth_server]['method']:'post';
				$this->get_access_token($_GET['code'], $method);
			}
			else
			{
				if($_GET['error'] || $_GET['error_description'])
				{
					throw new Exception("{$_GET['error']}: {$_GET[error_description]}", 1);
				}
				Header('HTTP/1.0 302 OAuth Redirection');
				header('Location: '.$this->oauth_dialog_url);
			}

		}
	}

    /**
    * 第二步，获取 access_token
    */
	public function get_access_token($code, $method = 'post'){
		//
		if($code){
			//请求参数
			$postfields= array(
				'grant_type'    => $this->oauth_grant_type,
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
				'code'          => $code,
				'redirect_uri'  => $this->oauth_redirect_uri,
				);
			// TODO qq use the get, post is ok?
			if($method == 'get')
			{
				// do get
				$url = $this->access_token_url .'?'. http_build_query($postfields , '', '&');
				$token = $this->get($url);
				if(GetType($token) == 'string')
				{
					parse_str($token, $array_token);
					$token = (object) $array_token;
				}
				//echo($token->access_token);
			}
			else
			{
				$token = $this->post($this->access_token_url, $postfields);
			}
			//var_dump($token);
			$this->token = array(
				'access_token' => $token->access_token,
				'user_sp'=>$this->oauth_server,
				'refresh_token'=>$token->refresh_token, 	// 暂时不考虑 refresh 问题
				'expires_in'=>$token->expires_in, 		// 没啥用
				'user_name'=>'',
				'user_id'=>'',
				'status'=>'0',
				);

			switch ($this->oauth_server) {
				case 'renren':
				{
					$this->token['user_id'] = $token->user->id;
					$this->token['user_name'] = $token->user->name;
					break;
				}
				case 'taobao':
				{
					$this->token['user_id'] = $token->taobao_user_id;
					$this->token['user_name'] = urldecode($token->taobao_user_nick);
					break;
				}
				case 'microsoft':
				{

					//GET 
					$url = 'https://apis.live.net/v5.0/me'.
						'?access_token='. $this->token['access_token'];
					$user = $this->get($url);
					$this->token['user_id'] = $user->id; // no support yet.
					$this->token['user_name'] = $user->name;
					break;
				}
				case 'google':
				{
					//$this->token['user_id'] = $token->taobao_user_id; // no support yet.
					//$this->token['user_name'] = urldecode($token->taobao_user_nick);
					break;
				}
				case 'baidu':
				{
					//print_r($token);
					$url = 'https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser'.
						'?access_token='. $this->token['access_token'];
					$user = json_decode($this->get($url));
					//print_r($user);
					//uid,uname,portrait
					//exit;
					$this->token['user_id'] = $user->uid;
					$this->token['user_name'] = $user->uname;
					break;
				}
				case 'qq':
				{
					$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".
					$this->token['access_token'];
					//echo($graph_url);
					$str = $this->get($graph_url);
					if (strpos($str, "callback") !== false)
					{
						$lpos = strpos($str, "(");
						$rpos = strrpos($str, ")");
						$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
					}
					$user = json_decode($str);
					$openid = $user->openid;

					if (isset($user->error))
					{
					echo "<h3>error:</h3>" . $user->error;
					echo "<h3>msg  :</h3>" . $user->error_description;
					exit;
					}


					$get_user_info_url = 'https://graph.qq.com/user/get_info?access_token='.
						$this->token['access_token'].
						'&oauth_consumer_key='.
						$this->client_id.
						'&openid='.
						$openid;
					$user = json_decode($this->get($get_user_info_url));
					if((string)$user->data->nick<>'') $user_name = $user->data->nick;

					if(!$user_name){
						//$get_user_info_url = 'https://graph.qq.com/user/get_info?access_token='.
						$get_user_info_url = 'https://graph.qq.com/user/get_user_info?access_token='.
							$this->token['access_token'].
							'&oauth_consumer_key='.
							$this->client_id.
							'&openid='.
							$openid;
						$user = $this->get($get_user_info_url);
						$user_name = $user->nickname;
						if($user_name == 'qzuser')
						{
							//
						}
					}
					$this->token['user_name'] = $user_name;
					$this->token['user_id'] = $openid;
					break;
				}
				case 'weibo':
				{
					//throw new Exception("not support yet.", 1);

					$url = "https://api.weibo.com/2/account/get_uid.json?access_token=".$this->token['access_token'];
					$user = $this->get($url);
					$url = 'https://api.weibo.com/2/users/show.json?access_token='.$this->token['access_token'].'&uid='.$user->uid;
					$user = $this->get($url);
					$this->token['user_id'] = $user->id;
					$this->token['user_name'] = $user->screen_name;
					break;
				}
				
				default:
					# code...
					break;
			}
			//var_dump($token);
			//var_dump($this->token);
			if($this->token['user_id'])
			{
				if($_GET['state']==$this->state)
				{
					$this->token['status'] = 1;
					$_SESSION[$this->oauth_token_name][$this->oauth_server] = $this->token;
				}
				else
				{
					throw new Exception("跨站攻击？ GET_STATE:".$_GET['state'].'; SESSION_STATE:'.$this->state, 1);
					
				}
			}else
			{
				throw new Exception("登录失败。", 1);
			}

		}
	}
}
