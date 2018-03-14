<br>

<p align="center">
<img src="https://box.kancloud.cn/90f9b3c1d667aefa77b09ea1b7ffb054_120x120.png" alt="MixPHP">
</p>

<p align="center">高性能 • 轻量级 • 命令行</p>

<p align="center">
<img src="https://img.shields.io/badge/downloads-6.9k-green.svg">
<img src="https://img.shields.io/badge/platform-linux%20%7C%20win%20%7C%20osx-lightgrey.svg">
<img src="https://img.shields.io/badge/size-238%20KB-green.svg">
</p>

## MixPHP是什么

MixPHP 是一个小巧但功能强大的 PHP 框架，基于 Swoole 开发，他只专注通过常驻内存打造一个简单、实用的 PHP 高性能框架，他并不极致追求全异步、非阻塞，依然采用 PHPer 所熟知的多进程同步阻塞式开发，这样能让 PHPer 以最低的学习成本享受到 Swoole 带来的高性能与全新的编程体验。

MixPHP 的专注使得他在未使用全异步非阻塞的情况下，依然拥有超高性能。

MixPHP 极致追求简单、实用主义，试图让使用过传统 MVC 框架的 PHPer 能立即上手。

## 与其他框架的比较

MixPHP 能快速建立功能完善的高性能 Web 应用程序，还能完成传统 MVC 框架不能想象或者很难实现的需求，如：多进程、WebSocket。

MixPHP 与传统 PHP 的 Web 框架非常不同，MixPHP 可以不需要 Web 服务器(Apache/PHP-FPM)，框架自带 mix-httpd 高性能HTTP服务器，传统的 PHP 应用程序中脚本结束后，所有的对象在请求后都将销毁，而 mix-httpd 不同，框架组件对象常驻于内存，减少对象反复创建销毁的性能损失，具有传统框架无法比拟的性能优势。

MixPHP 参考了 Yii2 框架的组件设计思想，又舍弃了 Yii2 与前端强耦合的缺点，使得整个框架如同 CodeIgniter 一样小巧而别致。

## 性能测试

[MixPHP 并发性能全面对比测试](http://www.jianshu.com/p/f769b6be1caf)

## 开发文档

MixPHP开发指南：http://doc.mixphp.cn

## 核心特征

* 高性能：极简架构 + Swoole引擎，超过 Phalcon 这类 C 扩展框架的性能；
* 长连接：按进程保持的长连接，支持 Mysql/Redis；
* 服务器：框架自带 mix-httpd 替代 Apache/PHP-FPM 作为高性能 HTTP 服务器；
* 组件：基于组件的框架结构，可通用的 Web 编程框架；
* 自动加载：完全使用 Composer，统一且高效；
* 路由：底层全正则实现，性能高，配置简单；
* 模型：模型集成了使用简单但功能强大的验证器，支持多场景控制；
* 视图：使用 PHP 做模板引擎，支持布局、属性；
* 命令行：封装了命令行开发基础设施，可快速开发定时任务、守护进程；
* 多进程：简易的多进程开发方式，充分利用多核性能，可处理大量数据；
* WebSocket：具备长连接开发能力，扩展了 PHP 开发领域；

## 环境要求

* PHP >= 5.4
* Swoole >= 1.9.5

## 快速开始

一键下载

```
$> php -r "copy('https://raw.githubusercontent.com/mixstart/mixphp/master/download.php', 'download.php');include 'download.php';"
```

输出如下：

```
download mixphp zip ... ok
unzip ... ok
clean temp files ... ok
Successful download to "/data/mixphp-master"
```

入口文件安装至：`/usr/local/bin`。

```
$> cd /data/mixphp-master
$> chmod 777 install.sh
$> ./install.sh
```

接下来启动 mix-httpd 服务器

```
$> mix-httpd service/start -d
```

如果一切顺利，运行到最后你将看到如下的输出：

```
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/

[2017-11-20 14:41:32] Server    Name: mix-httpd
[2017-11-20 14:41:32] PHP    Version: 5.4.45
[2017-11-20 14:41:32] Swoole Version: 1.9.21
[2017-11-20 14:41:32] Listen    Addr: 127.0.0.1
[2017-11-20 14:41:32] Listen    Port: 9501
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
