<br>

<p align="center">
<img src="https://box.kancloud.cn/90f9b3c1d667aefa77b09ea1b7ffb054_120x120.png" alt="MixPHP">
</p>

<p align="center">高性能 • 轻量级 • 命令行</p>

<p align="center">
<img src="https://img.shields.io/badge/platform-linux%20%7C%20win%20%7C%20osx-lightgrey.svg">
</p>

## MixPHP 是什么

MixPHP 秉承 **"普及 PHP 常驻内存型解决方案，促进 PHP 往更后端发展"** 的理念而创造，采用 Swoole 扩展作为底层引擎，围绕常驻内存的方式而设计，提供了 Console / Daemon / HTTP / WebSocket / TCP / UDP 开发所需的众多开箱即用的组件，MixPHP 追求简单、实用主义，试图让更多开发者以更低的学习成本享受到 Swoole 带来的高性能与全新的编程体验。

## 与传统 MVC 框架比较

MixPHP 支持三种执行方式：传统模式(Apache/FPM)、常驻模式、协程模式，后两种模式具有传统框架无法比拟的性能优势。

常驻模式：与传统 PHP 框架非常不同，可以不需要 Web 服务器(Apache/PHP-FPM)，框架自带基于 Swoole\Server 开发的 mix-httpd 高性能 HTTP 服务器，传统的 PHP 应用程序中脚本结束后，所有的对象在请求后都将销毁，而 mix-httpd 不同，框架组件对象常驻于内存，减少对象反复创建销毁的性能损失。

协程模式：开启协程后，一个进程可以并行执行 N 个请求，不会像常驻模式/传统模式那样阻塞进程，而每增加一个请求只需增加一些内存消耗，由于协程能并行处理，所以通常只需配置于 CPU 数量 1～2 倍左右的进程数即可，更少的进程带来更少的 CPU 线程切换，又减少很多性能损耗。

## 与其他基于 Swoole 的框架比较

框架非常轻量化，架构简单，源码可读性强，容易掌握与改造。

开发方式几乎与传统 MVC 框架完全一致，用户无需了解 Swoole 即可开始开发。

框架集成了众多开箱即用的组件，方便快速开发。

我们的开发文档可能是所有框架中最详细的，连使用场景都有写明。

唯一支持传统模式(Apache/FPM)、常驻模式、协程模式三种模式的框架，用户可渐进式学习，选择适合自己团队的模式。

采用 Swoole 原生协程与最新的 PHP Stream 一键协程化技术。

## 框架定位

在其他 Swoole 框架都定位于大中型团队、庞大的 PHP 应用集群的时候，MixPHP 决定推动这项技术的普及，我们定位于众多的中小型企业、创业型公司，我们将 Swoole 的复杂度封装起来，用简单的编码方式呈现给用户，让更多的中级程序员也可打造高并发系统，让 Swoole 不再只是高级程序员的专利。

## 核心特征

* 命令行：封装了命令行开发基础设施，可快速开发控制台程序、守护进程；
* HTTP：常驻内存 + 协程 + 传统 MVC 框架相似的使用方法；
* WebSocket：具备长连接开发能力，可开发 IM、消息推送类需求；
* TCP：可轻松打造 RPC 服务，mqtt 物联网项目；
* UDP：可处理一些非连接场景的开发任务；
* 高性能：极简架构 + Swoole引擎 + 协程，超过 Phalcon 这类 C 扩展框架的性能；
* 服务器：框架自带各类型服务器，无需 Apache/PHP-FPM 等外置容器；
* 协程：采用 Swoole 原生协程与最新的 PHP Stream 一键协程化技术。
* 连接池：通用的连接池组件，DB/Redis 等组件默认接入连接池。
* 协程池：封装了 Golang 常用的 Dispatcher、Worker 协程池方案。
* 长连接：按进程保持的长连接，支持 DB/Redis；
* 依赖注入：参考 Java Spring 的 Bean 设计思想，实现了简易好用的 IoC。
* 组件：基于组件的框架结构，并集成了大量开箱即用的组件；
* 中间件：AOP (面向切面编程)，注册方便，能更好的对请求进行过滤和处理；
* 路由：底层全正则实现，性能高，配置简单；
* 验证器：集成了使用简单但功能强大的验证器，支持多场景控制；
* 视图：使用 PHP 做模板引擎，支持布局、属性；
* 自动加载：基于 PSR-4 ，完全使用 Composer 构建；
* 模块化：支持 Composer ，可以很方便的使用第三方库；
* 日志：基于 PSR-3 的日志组件。

## 性能测试

[MixPHP 并发性能全面对比测试](http://www.jianshu.com/p/f769b6be1caf)

## 开发文档

MixPHP开发指南：

- http://doc.mixphp.cn
- https://www.kancloud.cn/onanying/mixphp2/content

## 环境要求

* PHP >= 7.0
* Swoole >= 1.9.5 （常驻同步模式）
* Swoole >= 4.2.9 （常驻协程模式）

## 快速开始

推荐使用 [composer](https://www.phpcomposer.com/) 安装。

```
composer create-project mix/mix --prefer-dist
```

启动服务器：

接下来启动 `mix-httpd` 服务器。

```
$> cd bin
$> php mix-httpd start -c ../applications/http/config/httpd.php
```

如果一切顺利，运行到最后你将看到如下的输出：

```
                             _____
_______ ___ _____ ___   _____  / /_  ____
__/ __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/

Server         Name:      mix-httpd
System         Name:      linux
PHP            Version:   7.2.9
Swoole         Version:   4.2.13
Framework      Version:   2.0.1-RC5
Coroutine      Mode:      enabled
Listen         Addr:      127.0.0.1
Listen         Port:      9501
Reactor        Num:       8
Worker         Num:       8
Configuration  File:      /data/applications/http/config/main_permanent.php
```

访问测试 (新开一个终端)：

```
$> curl http://127.0.0.1:9501/
Hello, World!
```

## 安装自动重启工具

- SwooleFor：[https://github.com/mix-php/swoolefor](https://github.com/mix-php/swoolefor)

## 下载

[MixPHP 发行版本](https://github.com/mix-php/mix/releases)

## 技术交流

作者微博：http://weibo.com/onanying ，关注最新进展     
官方QQ群：284806582(满) [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)，敲门暗号：phper

## License

Apache License Version 2.0, http://www.apache.org/licenses/
