oauthclient
===========
  oauth 的php 客户端其实挺多了，但是通用的 oauth客户端 似乎并不多，或者不容易那么通用。国外的 oauth客户端 做的不错，但是针对国内网站的似乎就没有。
  本客户端基于 php 和 curl 来实现的，目前，可以支持 baidu、qq、renren、淘宝、weibo.com和微软的帐号，只要简单的修改支持这六种帐号登录到你的应用。
  
# [oauth 2.0 client v1](https://github.com/rongzedong/oauthclient) 

目前该项目正在开发过程中，感兴趣的朋友都可以参与到其中来!


## 快速开局

下面:

* [下载主要分支](https://github.com/rongzedong/oauthclient/zipball/master).
* 克隆仓库到你的电脑: `git clone git://github.com/rongzedong/oauthclient.git`.

## 版本号

版本号信息和说明
1.0   第一版发布。
1.0.1 taobao api、 百度 api 发布。
1.0.2 支持主流五大 oauth 2.0 服务发布。
1.0.3 支持第六大主流，weibo.com 。
1.0.4 修正 class_http_client 完善weibo api 发布。

1.1 支持 myqee 框架

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


## 报告错误

错误反馈请访问 [Please open a new issue](https://github.com/rongzedong/oauthclient/issues). 

## 交流

* QQ 85120358

## Authors

**rongzedong**

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
