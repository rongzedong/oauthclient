<?php

/***************************************************************************
 *
 * Copyright (c) 2013 rong zedong
 *
 **************************************************************************/

/**
 * class_http_client.php
 * 这是一个基于 curl 的http操作类，目前提供 HTTP 的 GET、POST、PUT 操作。
 * 特点： 根据返回类型进行测试，如果是json 就自动转换成 obj 或者 ary。
 * @package	oauthclient
 * @author	rongzedong@msn.com
 * @version	1.0
 * history 
 * 2013.1.8 rong zedong.
 **/

class http_client
{
	private $ch;
	public $userAgent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) MIKAA HTTP Client';
	public $timeout=10;
	public $json2array = false;

	public $response;
	public $response_code;
	public $response_headers;

	public $error='';
	public $crrno=0;

	/**
	* 初始化 http client 类
	*/
	function http_client(){
		return true;
	}

	/**
	* 准备 curl
	*/
	function init_curl(){
		if(!function_exists('curl_init')){
			throw new Exception('this class need curl.');
			return false;
		}
		if(!is_resource($this->ch)){
			$this->ch = curl_init();
		}
		return is_resource($this->ch);
	}

	/**
	* 执行相应的方法并返回结果
	*/
	public function execute($method, $url, $param='', $Headers='', $username='', $password=''){
		$method = strtolower($method);
		$this->init_curl();

		if($method == 'get'){
			$this->ch = curl_init(); // hack for after the method of post and other method.
		}

		//echo($method);

		curl_setopt($this->ch, CURLOPT_HEADER, true);				//是否显示头部信息
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);		//
		//curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		if($username != ''){
			curl_setopt($this->ch, CURLOPT_USERPWD, $username . ':' . $password);
		}
		switch($method){
			case 'get' :{
				if(is_array($param)){
					$sets = array();
					foreach ($param AS $key => $val){
						$sets[] = $key . '=' . urlencode($val);
					}
					$param = implode('&',$sets);
				}
				if($param){
					$url = (strpos($url, '?')===false)? $url . '?'.$param : $url.'&'.$param;
				}
				//echo($url);
				break;
			}
			case 'post' : {
				curl_setopt($this->ch, CURLOPT_POST, true);
				if(is_array($param)){
					$sets = array();
					foreach ($param AS $key => $val){
						$sets[] = $key . '=' . urlencode($val);
					}
					$postfields = implode('&',$sets);
				}
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postfields);
				break;
			}
			case 'put':{
				curl_setopt($this->ch, CURLOPT_PUT, true);
				break;
			}

			case 'delete':{
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if(is_array($param)){
					$sets = array();
					foreach ($param AS $key => $val){
						$sets[] = $key . '=' . urlencode($val);
					}
					$postfields = implode('&',$sets);
				}
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
				break;
			}
			default:{
				throw new Exception('not support method: '.$method);
			}
		}

		if(is_string($url) && strlen($url)){
			$ret = curl_setopt($this->ch, CURLOPT_URL, $url);
		}else{
			throw new Exception('need a string of url.');
			return false;
		}

		//curl_setopt($ch, CURLOPT_PROGRESS, true);
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		//curl_setopt($ch, CURLOPT_MUTE, false);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);//设置curl超时秒数
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
		if(is_array($Headers)){
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $Headers);
		}
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);  // for taobao. ssl cert has error. 2013.1.17 rong zedong.
		//curl_setopt($this->ch, CURLOPT_CAINFO,'ca-bundle1.crt');
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0); 

		$response = curl_exec($this->ch);

		$this->error = curl_error($this->ch);
		$this->errno = curl_errno($this->ch);
		if ($this->error != ""){
			throw new Exception("{$this->errno}: {$this->error}", 1);
			return false;
		} 
		$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$headers = substr($response, 0, $header_size);
		$headers_data = explode("\n", trim($headers));
		$this->response_headers = array();
		array_shift($headers_data); // remove line like HTTP 1.0 200 OK
		foreach($headers_data as $part){
		$item=explode(":",$part);
		$this->response_headers[trim($item[0])] = trim($item[1]);
		}

		$this->response = substr( $response, $header_size );
		//print_r($this->response);

		$this->response_code = curl_getinfo($this -> ch,CURLINFO_HTTP_CODE);
		$this->last_url = curl_getinfo($this -> ch,CURLINFO_EFFECTIVE_URL);

		$content_type = (IsSet($this->response_headers['Content-Type']) ? strtolower(trim(strtok($this->response_headers['Content-Type'], ';'))) : 'unspecified');
		//echo($content_type);
		// 根据返回的数据类型进行处理，如果是 json 就直接返回对象或数组
		switch ($content_type) {
			case 'application/x-www-form-urlencoded':
			case 'text/plain':
			case 'text/html':
			case 'text/javascript':
			case 'application/json':
			{
				if(!function_exists('json_decode'))
				{
					throw new Exception('the JSON extension is not available in this PHP setup');
					return false;
				}
				$object = json_decode($this->response);
				//echo(gettype($object));
				switch(GetType($object))
				{
					case 'object':
						if($this->json2array)
						{
							$this->response = array();
							foreach($object as $property => $value)
							{
								$this->response[$property] = $value;
							}
							return $this->response;
						}
						$this->response = $object;
						break;
					case 'array':
						$this->response = $object;
						break;

					default:
						break;
				}
				break;
			}

			default:
				# donothing...
				break;
		}
		return $this->response;
	}

	/**
	* POST 方法
	*/
	function post($url, $param, $Headers = '', $username = '', $password = ''){
		return $this->execute('POST', $url, $param, $Headers, $username, $password);
	}
	 
	/**
	* GET 方法
	*/
	function get($url, $param = '', $Headers = '', $username = '', $password = ''){
		//echo($url);
		return $this->execute('GET', $url, $param, $Headers, $username, $password);
	}

	/**
	* PUT 方法
	*/
	function put($url, $param = '', $headers = '', $username = '', $password = ''){
		return $this->execute('PUT', $url, $param, $headers. $username, $password);
	}

	/**
	* 暂时么有使用
	*/
	private function converJson2Array($json)
	{
		$result = json_decode($json, true);
		return $result;
	}

}
