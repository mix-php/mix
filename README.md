> OpenMix 出品：https://openmix.org

<p align="center">
    <br>
    <br>
    <img src="https://openmix.org/static/image/logo_php.png" width="120" alt="MixPHP">
    <br>
    <br>
</p>

<h1 align="center">Mix PHP</h1>

中文 | [English](README_EN.md)

MixPHP 是一个 PHP 命令行模式开发框架；基于 `Vega` 驱动的 HTTP 可以同时支持 Swoole、WorkerMan、FPM、CLI-Server 生态，并且可以无缝切换；`V3` 是一个高度解耦的版本，整体代码基于多个独立的模块构建，即便用户不使用我们的脚手架，也可以使用这些独立模块，并且全部模块都支持原生开发。例如：你可以只使用 mix/vega 来搭配 laravel orm 使用；可以在任意环境中使用 mix/database 和 mix/redis；可以使用 mix/grpc 原生代码编写 gRPC；所有的模块你可以像搭积木一样随意组合。

## 独立模块

核心模块全部可独立使用，并且都支持原生代码开发。

- [mix/vega](src/vega) PHP 编写的 CLI 模式 HTTP 网络框架，支持 Swoole、WorkerMan、FPM、CLI-Server
- [mix/database](src/database) 可在各种环境中使用的轻量数据库，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)
- [mix/redis](src/redis) 可在各种环境中使用的 PHP Redis，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)
- [mix/redis-subscriber](src/redis-subscriber) 基于 Swoole 协程的 Redis 原生协议订阅库
- [mix/grpc](src/grpc) 基于 Swoole 协程的 PHP gRPC 库，包含 protoc 代码生成器、服务器、客户端
- [mix/websocket](src/websocket) 基于 Swoole 协程的 PHP WebSocket 服务器与客户端
- [mix/cli](src/cli) PHP 命令行交互指挥官
- [mix/worker-pool](src/worker-pool) 基于 Swoole 的协程池、工作池库
- [mix/validator](src/validator) 基于 PSR-7 的验证库
- [mix/event](src/event) 基于 PSR-14 标准的事件调度库
- [mix/init](src/init) 帮助执行类的静态初始化，通常用于预加载单例

## 服务器

支持多种服务器驱动，并且可以无缝切换。

- [PHP Built-in CLI-Server](examples/api-skeleton/composer.json#L8) `零扩展依赖` `热更新` `适合本机开发`
- [PHP-FPM](examples/api-skeleton/public/index.php) `热更新` `适合共享开发` `适合 admin 开发`
- [Swoole](examples/api-skeleton/composer.json#L9) `常驻内存` `兼容 composer 生态`
- [Swoole Coroutine](examples/api-skeleton/composer.json#L10) `常驻内存` `协程性能强劲`
- [WorkerMan](examples/api-skeleton/composer.json#L11) `常驻内存` `兼容 composer 生态`

## 开发文档

- `V3.0` https://openmix.org/mix-php/docs/3.0/
- `V2.2` https://www.kancloud.cn/onanying/mixphp2-2/content
- `V2.1` https://www.kancloud.cn/onanying/mixphp2-1/content
- `V2.0` https://www.kancloud.cn/onanying/mixphp2/content
- `V1.*` https://www.kancloud.cn/onanying/mixphp1/content

## 快速开始

提供了现成的脚手架，快速创建项目，立即产出。

- [编写一个 CLI 程序](examples/cli-skeleton#readme)

```
composer create-project --prefer-dist mix/cli-skeleton cli
```

- [编写一个 API 接口](examples/api-skeleton#readme)

```
composer create-project --prefer-dist mix/api-skeleton api
```

- [编写一个 Web 页面](examples/web-skeleton#readme)

```
composer create-project --prefer-dist mix/web-skeleton web
```

- [编写一个 WebSocket 服务](examples/websocket-skeleton#readme)

```
composer create-project --prefer-dist mix/websocket-skeleton websocket
```

- [编写一个 gRPC 接口](examples/grpc-skeleton#readme)

```
composer create-project --prefer-dist mix/grpc-skeleton grpc
```

## 性能测试

- [Web Frameworks Benchmark](https://web-frameworks-benchmark.netlify.app/result?l=php)

![web-frameworks-benchmark.png](web-frameworks-benchmark.png)

## 推荐阅读

- [MixPHP V3 开发流程体验 Swoole, Workerman, FPM, CLI-Server 多种运行模式介绍](https://zhuanlan.zhihu.com/p/398381870)
- [MixPHP V3 增加了 PHP-FPM、CLI-Server 的支持](https://zhuanlan.zhihu.com/p/394059925)
- [MixPHP V3 发布前的感想, 有哪些变化和特点](https://zhuanlan.zhihu.com/p/392558932)

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a), [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20) 敲门暗号：phper

## Golang 框架

OpenMix 同时还有 Golang 生态的框架

- https://github.com/mix-go/mix
- https://gitee.com/mix-go/mix

## License

Apache License Version 2.0, http://www.apache.org/licenses/
