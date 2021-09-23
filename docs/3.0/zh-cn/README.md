> OpenMix 出品：https://openmix.org

<p align="center">
    <br>
    <br>
    <img src="https://openmix.org/static/image/logo_php.png" width="120" alt="MixPHP">
    <br>
    <br>
</p>

<h1 align="center">Mix PHP</h1>

## 简介

MixPHP 是一个 PHP 命令行模式开发框架；基于 `Vega` 驱动的 HTTP 可以同时支持 Swoole、WorkerMan、FPM、CLI-Server 生态，并且可以无缝切换；`V3` 是一个高度解耦的版本，整体代码基于多个独立的模块构建，即便用户不使用我们的脚手架，也可以使用这些独立模块，并且全部模块都支持原生开发。例如：你可以只使用 mix/vega 来搭配 laravel orm 使用；可以在任意环境中使用 mix/database 和 mix/redis；可以使用 mix/grpc 原生代码编写 gRPC；所有的模块你可以像搭积木一样随意组合。

## 请帮忙 Star 一下

- https://github.com/mix-php/mix
- https://gitee.com/mix-php/mix

## 独立模块

核心模块全部可独立使用，并且都支持原生代码开发。

- [mix/vega](zh-cn/mix-vega) PHP 编写的 CLI 模式 HTTP 网络框架，支持 Swoole、WorkerMan、FPM、CLI-Server
- [mix/database](zh-cn/mix-database) 可在各种环境中使用的轻量数据库，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)
- [mix/redis](zh-cn/mix-redis) 可在各种环境中使用的 PHP Redis，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)
- [mix/redis-subscriber](zh-cn/mix-redis-subscriber) 基于 Swoole 协程的 Redis 原生协议订阅库
- [mix/grpc](zh-cn/mix-grpc) 基于 Swoole 协程的 PHP gRPC 库，包含 protoc 代码生成器、服务器、客户端
- [mix/websocket](zh-cn/mix-websocket) 基于 Swoole 协程的 PHP WebSocket 服务器与客户端
- [mix/cli](zh-cn/mix-cli) PHP 命令行交互指挥官
- [mix/worker-pool](zh-cn/mix-worker-pool) 基于 Swoole 的协程池、工作池库
- [mix/validator](zh-cn/mix-validator) 基于 PSR-7 的验证库
- [mix/event](zh-cn/mix-event) 基于 PSR-14 标准的事件调度库
- [mix/init](src/init) 帮助执行类的静态初始化，通常用于预加载单例

## 服务器

支持多种服务器驱动，并且可以无缝切换。

- [PHP Built-in CLI-Server](zh-cn/server-cli-server.md) `零扩展依赖` `热更新` `适合本机开发`
- [PHP-FPM](zh-cn/server-php-fpm.md) `热更新` `适合共享开发` `适合 admin 开发`
- [Swoole](zh-cn/server-swoole.md) `常驻内存` `兼容 composer 生态`
- [Swoole Coroutine](zh-cn/server-swoole-coroutine.md) `常驻内存` `协程性能强劲`
- [WorkerMan](zh-cn/server-workerman.md) `常驻内存` `兼容 composer 生态`

## MixGo 框架

OpenMix 同时还有 Golang 生态的框架

- https://github.com/mix-go/mix
- https://gitee.com/mix-go/mix

## 旧版文档

- `V2.2` https://www.kancloud.cn/onanying/mixphp2-2/content
- `V2.1` https://www.kancloud.cn/onanying/mixphp2-1/content
- `V2.0` https://www.kancloud.cn/onanying/mixphp2/content
- `V1.*` https://www.kancloud.cn/onanying/mixphp1/content
