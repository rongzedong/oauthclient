<?php
/*
 * oauth client class
 * 2013-1-8 create by rongzedong.
 * 继承自 http client class 因为所有的 oauth操作都是基于 http进行的操作。
 * 
 */

/*
* https://login.live.com/oauth20_authorize.srf?client_id=CLIENT_ID&scope=SCOPES&response_type=code&redirect_uri=REDIRECT_URI
* 然后登录到live.com， 再 callback到指定得地址： /Callback.htm?code=AUTHORIZATION_CODE
* 验证 access_token 和 state 再保存好 access_token 和 refresh：
*/

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
		//
		$_state = &$_SESSION['state'];
		if($this->state)
			return $this->state;
		$this->state = ($_state) ? $_state : 'sp:'.$this->oauth_server.';key:'.rand(1,1000).';';
		return $this->state;
	}

	function chushihua_state_and_sp(){
		if($_GET['state']){
			$_GET['state'] = str_replace('$', ';', $_GET['state']);
			foreach (explode(';', urldecode($_GET['state'])) as $k) {
				# code...
				$k = explode(':', $k);
				if($k[0])
					$state[$k[0]] = $k[1];
			}
		}
		$this->oauth_server = ($state['sp'])? $state['sp']:$_GET['sp'];
	}

	/**
	* 第0步 初始化类 初始化基础信息
	*/
	public function __construct($oauth_sp) {
		parent::__construct();

		$this->chushihua_state_and_sp();

		if(array_key_exists($this->oauth_server, $oauth_sp))
		{
			$this->client_id = $oauth_sp[$this->oauth_server]['client_id'];
			$this->client_secret = $oauth_sp[$this->oauth_server]['client_secret'];
		}else{
			throw new Exception("需要指定一个 oauth得sp服务商。", 1);
		}

		// 默认返回请求本类实例的php程序
		//$this->oauth_redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->oauth_redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["SCRIPT_NAME"];

		// 开启 session
		if(!$this->session_started && !session_start())
		{
			throw new exception('请打开 PHP 的 session 支持。');
			return false;
		}
		$this->session_started = true;
		$this->debug = 1;
		//exit($client_id);
		//echo('info');

		switch ($this->oauth_server) {
			case 'renren':
				# code...
				{
					$this->oauth_dialog_url = 'https://graph.renren.com/oauth/authorize?client_id='.
					$this->client_id.
					'&redirect_uri='.
					$this->oauth_redirect_uri.
					'&response_type=code&scope='.
					$this->oauth_scope.
					'&state='.
					$this->get_oauth_state();

					$this->access_token_url = 'https://graph.renren.com/oauth/token';
				}
				break;
			case 'microsoft':
				{
					$this->oauth_scope = 'wl.signin wl.basic';
					$this->oauth_dialog_url = 'https://login.live.com/oauth20_authorize.srf?client_id='.
					$this->client_id.
					'&redirect_uri='.
					$this->oauth_redirect_uri.
					'&response_type=code&scope='.
					$this->oauth_scope.
					'&state='.
					$this->get_oauth_state();

					$this->access_token_url = 'https://login.live.com/oauth20_token.srf';
				}
				break;
			case 'taobao':
				{
					$this->oauth_dialog_url = 'https://oauth.taobao.com/authorize?client_id='.
					$this->client_id.
					'&redirect_uri='.
					$this->oauth_redirect_uri.
					'&response_type=code'.
					'&state='.
					$this->get_oauth_state().
					'&scope=' . $this->oauth_scope;

					$this->access_token_url = 'https://oauth.taobao.com/token';

				}
				break;
			default:
				# code...
				throw new Exception("必须输入一个参数作为 oauth_server 提供商，可用的有  renren, microsoft, taobao 等", 1);
					return false;
				break;
		}
		// if login else
		if($_GET['code'] && $_GET['state'])
		{
			//
			$this->get_access_token($_GET['code']);
		}
		else
		{
			if($_GET['error'] || $_GET['error_description'])
			{
				throw new Exception("{$_GET['error']}: {$_GET[error_description]}", 1);
			}

			// 转入 登录页面
			Header('HTTP/1.0 302 OAuth Redirection');
			header('Location: '.$this->oauth_dialog_url);
		}
	}

    /**
    * 第二步，获取 access_token
    */
	public function get_access_token($code){
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

			$token = $this->post($this->access_token_url, $postfields);
			//var_dump($token);
			$this->token = array(
				'access_token' => $token->access_token,
				'user_sp'=>$this->oauth_server,
				'refresh_token'=>$token->refresh_token, 	// 暂时不考虑 refresh 问题
				'expires_in'=>$token->expires_in, 		// 没啥用
				'user_name'=>'',
				'user_id'=>'',
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
				
				default:
					# code...
					break;
			}

			//print_r($this->token);
/*
			$_SESSION['oauth2']['access_token'] = $access_token = $token->access_token;
			$_SESSION['oauth2']['refresh_token'] = $refresh_token = $token->refresh_token;
			$_SESSION['oauth2']['user_id'] = $user_id = $token->taobao_user_id;
			$_SESSION['oauth2']['user_nick'] = $user_nick = urldecode($token->taobao_user_nick);
*/
		}
	}

//			$state = $_SESSION['OAUTH_STATE'] = time().'-'.substr(md5(rand().time()), 0, 6);


	/**
	* 暂时不知道是否有必要的编码函数，暂时保留
	*/
	Function Encode($value)
	{
		return(is_array($value) ? $this->EncodeArray($value) : str_replace('%7E', '~', str_replace('+',' ', RawURLEncode($value))));
	}

	Function EncodeArray($array)
	{
		foreach($array as $key => $value)
		{
			$array[$key] = $this->Encode($value);
		}
		return $array;
	}

	/**
	* 编码函数
	* 修改完毕
	*/
	Function HMAC($function, $data, $key = '')
	{
		switch($function)
		{
			case 'sha1':
				if($key=='')
					throw new exception('key is null.');
				$pack = 'H40';
				break;
			case 'md5':
				return md5($data);
				break;
			default:
				throw new exception($function.' is not a supported an HMAC hash type');
				return false;
		}
		if(strlen($key) > 64) // 如果key 大于 64
			$key = pack($pack, $function($key)); // 处理 key
		if(strlen($key) < 64) // 否则这是干啥呢？
			$key = str_pad($key, 64, "\0");
		return base64_encode(pack($pack, $function((str_repeat("\x5c", 64) ^ $key).pack($pack, $function((str_repeat("\x36", 64) ^ $key).$data)))));
	}

}




/*

				$this->dialog_url = 'https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&state={STATE}';
				$this->access_token_url = 'https://accounts.google.com/o/oauth2/token';

					if(IsSet($response['expires'])
					|| IsSet($response['expires_in']))
					{
						$expires = (IsSet($response['expires']) ? $response['expires'] : $response['expires_in']);
						if(strval($expires) !== strval(intval($expires))
						|| $expires <= 0)
							return($this->SetError(__line__.'/OAuth server did not return a supported type of access token expiry time'));
						$this->access_token_expiry = gmstrftime('%Y-%m-%d %H:%M:%S', time() + $expires);
						if($this->debug)
							$this->OutputDebug(__line__.'/Access token expiry: '.$this->access_token_expiry.' UTC');
						$access_token['expiry'] = $this->access_token_expiry;
					}
					else
						$this->access_token_expiry = '';
					if(IsSet($response['token_type']))
					{
						$this->access_token_type = $response['token_type'];
						if($this->debug)
							$this->OutputDebug(__line__.'/Access token type: '.$this->access_token_type);
						$access_token['type'] = $this->access_token_type;
					}
					else
						$this->access_token_type = '';
					if(!$this->StoreAccessToken($access_token))
						return false;
*/



/*
$op = new oauth_client();
$key = $op->Encode($op->client_secret).'&'.$op->Encode($op->access_token_secret);
$r = $op->HMAC('sha1', 'data', $key);
echo($r);
echo($op->encode('info'));

*/