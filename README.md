oauthclient
===========
  oauth 的php 客户端其实挺多了，但是通用的 oauth客户端 似乎并不多，或者不容易那么通用。国外的 oauth客户端 做的不错，但是针对国内网站的似乎就没有。
  本客户端基于 php 和 curl 来实现的，目前，可以支持 baidu、qq、renren、淘宝和微软的帐号，只要简单的修改支持这五种帐号登录到你的应用。
  
# [oauth 2.0 client v1](https://github.com/rongzedong/oauthclient) 

目前该项目正在开发过程中，感兴趣的朋友都可以参与到其中来!


## 快速开局

下面:

* [下载主要分支](https://github.com/rongzedong/oauthclient/zipball/master).
* 克隆仓库到你的电脑: `git clone git://github.com/rongzedong/oauthclient.git`.

## 版本号

版本号信息和说明
1.0.2 已经发布，beta测试中。

## 报告错误

错误反馈请访问 [Please open a new issue](https://github.com/rongzedong/oauthclient/issues). 

## 交流

* QQ 85120358


## 演示

使用起来很简单，直接查看 demo.php 就可以了。

config.php的 demo如下

<?php


$oauth_sp = array(
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
		// 下面两行不要修改
		'scope' => 'get_user_info add_share,check_page_fans,add_t,del_t,add_pic_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idollist,add_idol,del_idol,match_nick_tips_weibo,get_intimate_friends_weibo',
		'method'=>'get',),
	'microsoft'=>array(// https://manage.dev.live.com/Applications/Index
		'client_id'=>'',
		'client_secret'=>'',
		// 下面两行不要修改
		'scope'=>'wl.signin wl.basic',
		'method'=>'get',
		),
	);



## Authors

**rongzedongo**

+ http://www.oo8h.com
+ http://github.com/rongzedong


## Copyright and license

Copyright 2013

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this work except in compliance with the License.
You may obtain a copy of the License in the LICENSE file, or at:

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.