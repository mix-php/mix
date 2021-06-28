# Mix Vega

Mix Vega is a HTTP web framework written in PHP support Swoole, WorkerMan

Mix Vega 是一个用 PHP 编写的 HTTP 网络框架，支持 Swoole、WorkerMan

## Overview

Vega 是 [MixPHP](https://github.com/mix-php/mix) `V3` 内置的最核心的组件，与 golang [gin](https://github.com/gin-gonic/gin) 定位完全一致，它包含 Web 应用处理的大量功能 (数据落地除外)，包括：路由、渲染、参数获取、中间件、文件上传处理等；具有 CLI 模式下强大的兼容性，同时支持 Swoole、WorkerMan，未来还有可能支持 RoadRunner。

## Installation

```
composer require mix/vega
```

## Quick start

配置路由

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

Swoole 多进程中使用

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);
$http->on('Request', $vega->handler());
$http->start();
```

Swoole 协程中使用

```php
$scheduler = new \Swoole\Coroutine\Scheduler;
$scheduler->set([
    'hook_flags' => SWOOLE_HOOK_ALL,
]);
$scheduler->add(function () use ($vega) {
    $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
    $server->handle('/', $vega->handler());
    $server->start();
});
$scheduler->start();
```

WorkerMan 中使用

```php
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
