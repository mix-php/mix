# Mix Vega

Mix Vega is a HTTP web framework written in PHP support Swoole, WorkerMan

Mix Vega 是一个用 PHP 编写的 HTTP 网络框架，支持 Swoole、WorkerMan

## Overview

Vega 是 [MixPHP](https://github.com/mix-php/mix) `V3+` 内置的最核心的组件 (可独立使用)，参考 golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux) 开发，它包含 Web 应用处理的大量功能 (数据落地除外)，包括：路由、渲染、参数获取、中间件、文件上传处理等；具有 CLI 模式下强大的兼容性，同时支持 Swoole、WorkerMan, 并且支持 Swoole 的多种进程模型。

## Installation

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

## License

Apache License Version 2.0, http://www.apache.org/licenses/
