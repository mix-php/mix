<br>

<p align="center">
<img src="https://box.kancloud.cn/90f9b3c1d667aefa77b09ea1b7ffb054_120x120.png" alt="MixPHP">
</p>

<p align="center">高性能 • 轻量级 • 命令行</p>

<p align="center">
<img src="https://img.shields.io/badge/downloads-18.6k-green.svg">
<img src="https://img.shields.io/badge/platform-linux%20%7C%20win%20%7C%20osx-lightgrey.svg">
<img src="https://img.shields.io/badge/size-160%20KB-green.svg">
</p>

## MixPHP 是什么

MixPHP 秉承 **"普及 PHP 常驻内存型解决方案，促进 PHP 往更后端发展"** 的理念而创造，采用 Swoole 扩展作为底层引擎，围绕常驻内存的方式而设计，提供了 Web / Console 开发所需的众多开箱即用的组件，MixPHP 追求简单、实用主义，试图让更多开发者以更低的学习成本享受到 Swoole 带来的高性能与全新的编程体验。

## 与传统 MVC 框架比较

MixPHP 与传统 PHP 的 Web 框架非常不同，可以不需要 Web 服务器(Apache/PHP-FPM)，框架自带基于 SwooleServer 开发的 mix-httpd 高性能 HTTP 服务器，传统的 PHP 应用程序中脚本结束后，所有的对象在请求后都将销毁，而 mix-httpd 不同，框架组件对象常驻于内存，减少对象反复创建销毁的性能损失，具有传统框架无法比拟的性能优势。

MixPHP 参考了 Yii2 框架的组件设计思想，又舍弃了 Yii2 与前端强耦合的缺点，使得整个框架如同 CodeIgniter 一样小巧别致，MixPHP 同时拥有 Yii2 的架构思想、Swoole 的高性能与更后端开发能力。

## 与其他基于 Swoole 的框架比较

现有 Swoole 开发框架大致分为两类：

- 基础封装类型：入门成本低，缺少传统框架那些众多开箱即用的组件。
- 全异步、服务化、分布式类型：入门成本高，适合集群部署提供各种服务接口，当然也缺少传统框架那些众多开箱即用的组件，因为这类框架更偏向数据层，并不是用来写网页的。

MixPHP 则处于以上两种类型中间的位置，入门成本低，又像传统框架一样拥有众多开箱即用的组件，即适合写接口，又适合做网页。

## 核心特征

* 高性能：极简架构 + Swoole引擎，超过 Phalcon 这类 C 扩展框架的性能；
* 服务器：框架自带 mix-httpd 替代 Apache/PHP-FPM 作为高性能 HTTP 服务器；
* 组件：基于组件的框架结构，并集成了大量开箱即用的组件；
* 自动加载：遵循 PSR-4，使用 Composer 构建；
* 模块化：支持 Composer ，可以很方便的使用第三方库；
* 中间件：注册方便，更友好的对请求进行过滤和处理；
* 门面：核心组件全部内置门面类，助力快速开发；
* 路由：底层全正则实现，性能高，配置简单；
* 验证器：集成了使用简单但功能强大的验证器，支持多场景控制；
* 视图：使用 PHP 做模板引擎，支持布局、属性；
* 长连接：按进程保持的长连接，支持 Mysql/Redis；
* 命令行：封装了命令行开发基础设施，可快速开发定时任务、守护进程；
* 多进程：简易的多进程开发方式，充分利用多核性能，可处理大量数据；
* WebSocket：具备长连接开发能力，扩展了 PHP 开发领域；

## 性能测试

[MixPHP 并发性能全面对比测试](http://www.jianshu.com/p/f769b6be1caf)

## 开发文档

MixPHP开发指南：http://doc.mixphp.cn

## 环境要求

* PHP >= 5.5
* Swoole >= 1.9.5

## 快速开始

使用一键安装。

```
$> php -r "copy('https://raw.githubusercontent.com/mixstart/mixphp/master/download.php', 'download.php');include 'download.php';"
```

也可以选择使用 [composer](https://www.phpcomposer.com/) 安装。

```
composer create-project mixstart/mixphp --prefer-dist
```

入口文件安装至：`/usr/local/bin`，可选。

```
$> cd /data/mixphp-master
$> chmod 777 install.sh
$> ./install.sh
```

接下来启动 mix-httpd 服务器

```
$> mix-httpd service start -d
```

如果一切顺利，运行到最后你将看到如下的输出：

```
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/

[2018-06-22 07:22:26] Server    Name: mix-httpd
[2018-06-22 07:22:26] PHP    Version: 7.2.1
[2018-06-22 07:22:26] Swoole Version: 4.0.1
[2018-06-22 07:22:26] Listen    Addr: 127.0.0.1
[2018-06-22 07:22:26] Listen    Port: 9501
```

访问测试：

```
$> curl http://127.0.0.1:9501/
Hello World
```

## 下载

[MixPHP 发行版本](https://github.com/mixstart/mixphp/releases)

## 技术交流

作者微博：http://weibo.com/onanying ，关注最新进展     
官方QQ群：284806582，敲门暗号：phper

## License

GNU General Public License, version 2 see https://www.gnu.org/licenses/gpl-2.0.html
