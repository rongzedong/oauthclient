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

/***
	如果没有登录会自动引向登录地址，选择哪个登录取决于 GET[sp] 和 default sp

        $sp         = Core::config('oauth.sp');
        $default_sp = Core::config('oauth.default_sp');
        $op     = new oauth_client($sp, $default_sp);

	验证是否登录了
        $token  = new oauth_token();
        $token->is_login();

	下面代码只适用于调试及非myqee场合。

	配置文件参考： oauth.config.php 复制到 config 目录中。
	将所有子目录直接复制到 项目目录下的 class 目录中就可以了。
	非子目录下的内容 可以不用部署到 myqee项目中。
*/

include('oauth.config.php');


/***
配置信息如下，可单独保存在config.php中。
$oauth_sp = array(
	'taobao'=>array(
		'client_id'=>'ID',
		'client_secret'=>'SECRET',
		'method'=>'post', // default is post, but some sp need method of get.
		'scope'=>'', // microsoft must be send this param.
		),
	);
*/

include('http/client.class.php');
include('oauth/client.class.php');
include('api/baidu.class.php');
include('api/taobao.class.php');
include('api/qq.class.php');
include('api/weibo.class.php');

$op = new oauth_client($config['sp'], $config['default_sp']);

//print_r($op->token);
echo('欢迎你， '.$op->token['user_name']);

switch ($op->oauth_server) {
	case 'taobao':
	{
		$api = new api_taobao($op);
		$user = $api->call('user.buyer.get', array('nick'=>$op->token['user_name']));
		//print_r($rp);
		$user_avatar = $rp->user->avatar;
		echo('<img src="'.$user_avatar.'">');
		break;
	}

	case 'baidu':
	{
		$api = new api_baidu($op);
		$user = $api->call('passport/users/getInfo');

		echo(
			'realname is '.
			$user->realname.', pic is '.
			$user->portrait.' intro '.
			$user->userdetail. 'birthday is '.
			$user->birthday
			);

		break;
	}

	case 'qq':
	{
		$api = new api_qq($op);
		$user = $api->call('user/get_info');


		if($user->data->name)
		{
		echo(
			' birthday is '.
			$user->data->birth_year.'-'.
			$user->data->birth_month.'-'.
			$user->data->birth_day.', come from '.
			$user->data->location.', weibo is '.
			$user->data->name.', name is '.
			$user->data->nick
			);
		}
		break;
	}

	case 'renren':
	{
		# code...

		break;
	}

	case 'microsoft':
	{
		# code...

		break;
	}
	case 'weibo':

		// 2013.1.22 need check();
	
		$api = new api_weibo($op);
		$user = $api->show_user_by_id($op->token['user_id']);
		//$user = $api->call('users/show', array('uid'=>$op->token['user_id']));

		//print_r($user);
		echo(
			' come from '.
			$user->location.', name is '.
			$user->name
			);
		# code...
		$c = &$api;

		$ms  = $c->home_timeline(); // done
		//print_r($ms);
		$uid = $op->token['user_id'];
		$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

		?>
		<?php echo($user_message->screen_name)?>,您好！ 
			<h2 align="left">发送新微博</h2>
			<form action="" >
				<input type="text" name="text" style="width:300px" />
				<input type="submit" />
			</form>
		<?php
		if( isset($_REQUEST['text']) ) {
			$ret = $c->update( $_REQUEST['text'] );	//发送微博
			if ( isset($ret->error_code) && $ret->error_code > 0 ) {
				echo "<p>发送失败，错误：{$ret->error_code}:{$ret->error}</p>";
			} else {
				//print_r($ret);
				echo "<p>发送成功</p>";
			}
		}
		?>

		<?php if( is_array( $ms->statuses ) ): ?>
		<?php foreach( $ms->statuses as $item ): ?>
		<div style="padding:10px;margin:5px;border:1px solid #ccc">
			<?php echo($item->text);?>
		</div>
		<?php endforeach; ?>
		<?php endif; 

		break;
	default:
		# code...
		break;
}
