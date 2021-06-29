# Mix Vega

Vega is a CLI HTTP web framework written in PHP support Swoole, WorkerMan

Vega 是一个用 PHP 编写的 CLI HTTP 网络框架，支持 Swoole、WorkerMan

## Overview

Vega is the core component of [MixPHP](https://github.com/mix-php/mix) `V3+` (which can be used independently). It is developed by referring to golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux). It contains a lot of functions for Web application processing (excluding database processing), including: routing, rendering, parameter acquisition, middleware, file upload processing, strong compatibility with CLI mode, and support for Swoole, Workerman, and Swoole's various process models.

Vega 是 [MixPHP](https://github.com/mix-php/mix) `V3+` 内置的最核心的组件 (可独立使用)，参考 golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux) 开发，它包含 Web 应用处理的大量功能 (数据库处理除外)，包括：路由、渲染、参数获取、中间件、文件上传处理等；具有 CLI 模式下强大的兼容性，同时支持 Swoole、WorkerMan, 并且支持 Swoole 的多种进程模型。

## Installation

> 需要先安装 [Swoole](https://wiki.swoole.com/#/environment) 或者 [WorkerMan](http://doc.workerman.net/install/requirement.html)

```
composer require mix/vega
```

## Quick start

Swoole 多进程 (异步) 中使用

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http = new Swoole\Http\Server('0.0.0.0', 9501);
$http->on('Request', $vega->handler());
$http->start();
```

Swoole 单进程 (协程) 中使用

```php

Swoole\Coroutine\run(function () {
    $vega = new Mix\Vega\Engine();
    $vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
        $ctx->string(200, 'hello, world!');
    })->methods('GET');
    
    $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
    $server->handle('/', $vega->handler());
    $server->start();
});
```

WorkerMan 中使用

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http_worker = new Workerman\Worker("http://0.0.0.0:2345");
$http_worker->onMessage = $vega->handler();
$http_worker->count = 4;
Workerman\Worker::runAll();
```

访问测试

~~~
% curl http://0.0.0.0:9501/hello
hello, world!
~~~

## 路由配置

## 路由前缀 (分组)

## 参数获取

### 请求参数

### 客户端IP

## 上传文件处理

## 中断执行

## 请求上下文

## JSON 请求与输出

### JSONP 处理

## 设置中间件

### 404 自定义

### 500 全局异常捕获

## HTML 视图渲染

## License

Apache License Version 2.0, http://www.apache.org/licenses/
