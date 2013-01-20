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
include('class_baidu_api.php');
include('class_taobao_api.php');
include('class_qq_api.php');

$op = new oauth_client($oauth_sp);

//print_r($op->token);

switch ($op->oauth_server) {
	case 'taobao':
	{
		$tp = new taobao_api($op);
		$rp = $tp->call('user.buyer.get', array('nick'=>$op->token['user_name']));
		//print_r($rp);
		$user_avatar = $rp->user->avatar;
		echo('<img src="'.$user_avatar.'">');
		break;
	}

	case 'baidu':
	{
		$bd = new baidu_api($op);
		$user = $bd->call('passport/users/getInfo');
		echo('欢迎你， '.$op->token['user_name']);

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
		$qq = new qq_api($op);
		$user = $qq->call('user/get_info');
		echo('欢迎你， '.$op->token['user_name']);

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
		echo('欢迎你， '.$op->token['user_name']);
		break;
	}

	case 'microsoft':
	{
		# code...
		echo('欢迎你， '.$op->token['user_name']);
		break;
	}
	
	default:
		# code...
		echo('欢迎你， '.$op->token['user_name']);
		break;
}
