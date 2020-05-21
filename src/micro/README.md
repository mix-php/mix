## Mix Micro

与 go-micro 生态深度集成的 php 微服务开发框架

PHP microservice development framework deeply integrated with go-micro ecology

## 安装

要求：

- Swoole >= 4.4.4

```
composer require mix/micro
```

## 概述



支持的服务器

- [Mix gRPC](https://github.com/mix-php/grpc)
- [Mix JSON-RPC](https://github.com/mix-php/json-rpc)
- [Mix Http Server](https://github.com/mix-php/http-server)

支持的注册中心、配置中心

- [Mix Micro Etcd](https://github.com/mix-php/micro-etcd)

其他微服务治理组件

- [Mix Micro Hystrix](https://github.com/mix-php/micro-hystrix) `需单独安装`
- [Mix Tracing Zipkin](https://github.com/mix-php/tracing-zipkin) `需单独安装` 

## 使用

### 安装 Swoole

- Swoole >= 4.4.4: https://wiki.swoole.com/#/environment

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

```php
use Mix\Micro\Micro;

Micro::service(
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
[2020-05-21 21:01:18] API.INFO: Register service [php.micro.api.greeter-2938109e-9b63-11ea-80bd-000057bdb82e]
```

启动后的微服务可使用 [Micro](https://micro.mu/docs/runtime.html) 工具包 API Gateway、Web Dashboard、Web Proxy 等多种微服务管理工具，也可在 PHP 代码中使用 [Mix gRPC](https://github.com/mix-php/grpc) 的 Client 直接调用 [go-micro](https://github.com/micro/go-micro) 编写的微服务。

## License

Apache License Version 2.0, http://www.apache.org/licenses/
