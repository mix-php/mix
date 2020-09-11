## Mix Micro

与 go-micro 生态深度集成的 php 微服务开发框架

PHP microservice development framework deeply integrated with go-micro ecology

## 安装

要求：

- Swoole >= 4.5.0: https://wiki.swoole.com/#/environment

```
composer require mix/micro
```

## 概述

在很多 Swoole 框架自建微服务生态的时候，我认为微服务生态要是能与主流生态直接无缝通信可能更具有实用价值，Mix 从 V2.1 开始完全切换为单线程协程，这一特性非常适合微服务的打造，同时 Mix 一直都是 Go 风格框架，因此我决定将微服务融入 go-micro 生态，让 Mix 框架用户能与 go-micro 编写的微服务直接代码级无缝通信，并且能直接使用 go-micro 运行时工具包的网关、代理、Dashboard 等全部微服务治理基础设施，让 PHP 与 Go 能一起开发高性能微服务，取长补短。

- Mix Micro：动态语言，开发效率高，view 模板渲染更加适合，单线程协程没有锁的烦恼，try/catch 不用忍受 go 大量 err 处理的烦恼，更加完善的 OOP 让业务封装更加人性。
- Go Micro: 静态语言，编译时即可过滤大量代码问题，CPU 密集型计算更加擅长，原生的协程库生态更加丰富。

基于以上 Mix 打造了可独立使用的 [Mix Micro](https://github.com/mix-php/micro) 同时还提供了包含大量代码的 [Mix Micro Skeleton](https://github.com/mix-php/mix-micro-skeleton) 微服务开发骨架。

支持的服务器

- [Mix gRPC](https://github.com/mix-php/grpc) 
- [Mix JSON-RPC](https://github.com/mix-php/json-rpc)
- [Mix Http Server](https://github.com/mix-php/http-server)

支持的注册中心、配置中心

- [Mix Micro Etcd](https://github.com/mix-php/micro-etcd)

支持的网关

- [Micro](https://github.com/micro/micro) v1, v2

其他微服务治理组件

- [Mix Micro Hystrix](https://github.com/mix-php/micro-hystrix) 
- [Mix Tracing Zipkin](https://github.com/mix-php/tracing-zipkin) 

## 使用

### 创建一个 gRPC 服务器

详细的 gRPC 服务编写请查看：

- [Mix gRPC](https://github.com/mix-php/grpc)

```php
$server = new \Mix\Grpc\Server('0.0.0.0'); // 默认会随机分配端口，也可以指定
$server->register(SayService::class);
```

### 创建一个注册中心

```php
$registry = new \Mix\Micro\Etcd\Registry('http://127.0.0.1:2379/v3');
```

### 创建一个配置中心

```php
$config = new \Mix\Micro\Etcd\Config('http://127.0.0.1:2379/v3');
$config->listen($eventDispatcher); // PSR-14 Event Dispatcher
```

### 执行一个微服务

启动后会自动监听 SIGINT, SIGTERM, SIGQUIT 信号，自动优雅停止，如果想自己处理信号，传入 `Micro::signal(false)` 配置

```php
use Mix\Micro\Micro;

Micro::newService(
    Micro::server($server),
    Micro::registry($registry),
    Micro::config($config),
    Micro::logger($logger), // PSR-3 Logger
    Micro::version('latest'),
    Micro::metadata(['foo' => 'bar'])
)->run();
```

执行后日志打印：

```php
[2020-05-21 21:01:18] API.INFO: Server started [0.0.0.0:51438]
[2020-05-21 21:01:18] API.INFO: Register service [php.micro.grpc.greeter-2938109e-9b63-11ea-80bd-000057bdb82e]
```

启动后的微服务可使用 [Micro](https://micro.mu/docs/runtime.html) 工具包 API Gateway、Web Dashboard、Web Proxy 等多种微服务管理工具，也可在 PHP 代码中使用 [Mix gRPC](https://github.com/mix-php/grpc) 的 Client 直接调用 [go-micro](https://github.com/micro/go-micro) 编写的微服务。

## License

Apache License Version 2.0, http://www.apache.org/licenses/
