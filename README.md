<br>

<p align="center">
<img src="https://git.kancloud.cn/repos/onanying/mixphp1/raw/ba9d4f9d235c24da0a7b8d8a8aa53a57b8e83331/images/logo.png?access-token=1899f10823ef02e1745183298b8c71d3" alt="MixPHP V1">
</p>

<p align="center">高性能 • 极简 • 夸架构</p>

<p align="center">『 让小白也能掌握Swoole的PHP框架 』</p>

<p align="center">
<img src="https://img.shields.io/badge/downloads-503-green.svg">
<img src="https://img.shields.io/badge/platform-linux%20%7C%20win%20%7C%20osx-lightgrey.svg">
<img src="https://img.shields.io/packagist/l/doctrine/orm.svg">
</p>

## 核心特征

* 夸架构：同一份代码可在LAMP/LNMP/WAMP、Nginx+Swoole两种架构中运行；

* 高性能：极简架构+Swoole，达到超过Yaf/phalcon这类C扩展框架的性能，[ [对比测试](http://www.jianshu.com/p/0ce1a9885e01) ] ；

* 长连接：按进程保持的长连接，支持Mysql/Redis；

* 服务器：框架自带MixHttpd替代Apache/phpfpm作为高性能HTTP服务器；

* 组件：基于组件的框架结构，可通用的Web编程框架；

* 自动加载：完全使用Composer，统一且高效；

* 路由：底层全正则实现，性能高，配置简单；

* 模型：模型集成了使用简单但功能强大的验证器，支持多场景控制；

* 查询构造器：独创的SQL构造方法，可灵活优雅的构造复杂语句；

* 视图：使用PHP做引擎，支持布局、属性；

## 快速开始

一键安装

```
$>php -r "copy('https://raw.githubusercontent.com/mixstart/mixphp/master/installer.php', 'installer.php');include 'installer.php';"
```

composer.phar 下载容易出错，多试几次，最后你将看到如下的输出：

```
download mixphp zip ... ok
unzip ... ok
clean temp files ... ok
download composer.phar (It's a little slow) ... ok
Successfully installed in "/data/mixphp-Beta2"

```

接下来启动mixhttpd服务器

```
$>cd /data/mixphp-Beta2/server/mixhttpd/
$>php mixhttpd service/start
```

如果一切顺利，运行到最后你将看到如下的输出：

```
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/

Server     Name: mixhttpd
PHP     Version: 5.4.45
Swoole  Version: 1.9.12
Listen     Addr: 127.0.0.1
Listen     Port: 9501
```

访问测试：

```
$>curl http://127.0.0.1:9501/
Hello World
```

## 框架定位

MixPHP 专注打造兼容LAMP/LNMP/WAMP技术栈，让小白也能掌握Swoole的PHP框架。

将复杂封装在框架里，只留下简单，用户无需了解 Swoole 复杂的生命周期、变量作用域、进程模型等问题。

让更多中小型团队也能低成本通过Swoole提升业务性能。

## 文档

在线手册：[MixPHP开发指南](https://www.kancloud.cn/onanying/mixphp1/379324) | [下载](https://www.kancloud.cn/onanying/mixphp1)

## 下载

[MixPHP V1 发行版本](https://github.com/mixstart/mixphp/releases)

## 技术交流

官方QQ群：284806582

## License

MixPHP 框架基于 MIT license 进行开源。
