> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix WebSocket

PHP WebSocket server and client based on Swoole coroutine

基于 Swoole 协程的 PHP WebSocket 服务器与客户端

## Overview

该 WebSocket 支持处理服务器和客户端，服务器基于 Swoole 单进程协程 `Swoole\Coroutine\Http\Server` 驱动，没有多进程那些复杂的作用域和生命周期概念，开发体验和 Golang 一致，简单又高效。

**推荐搭配以下数据库使用 (支持协程和连接池)：**

- https://github.com/mix-php/database
- https://github.com/mix-php/redis

**推荐搭配以下库处理 Subscribe：**

- https://github.com/mix-php/redis-subscribe

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a)
, [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)
敲门暗号：ws

## Installation

```
composer require mix/websocket
```

## 服务器 Server

在 [Mix Vega](https://github.com/mix-php/vega) 中使用 (只支持 Swoole 单进程协程)

```php
$upgrader = new Mix\WebSocket\Upgrader();

$vega = new Mix\Vega\Engine();
$vega->handleFunc('/websocket', function (Mix\Vega\Context $ctx) use ($upgrader) {
     // 升级连接
    $conn      = $upgrader->upgrade($ctx->request, $ctx->response);

    // 接收数据
    $in        = $conn->recv();
    var_dump($in->data);
    
    // 发送数据
    $out       = new \Swoole\WebSocket\Frame();
    $out->data = sprintf('hello, %s', $in->data);
    $conn->send($out);
    
    $conn->close();
})->methods('GET');
```

在 Swoole 原生中使用 (只支持单进程协程)

```php
Swoole\Coroutine\run(function () {
    $upgrader = new Mix\WebSocket\Upgrader();
    $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($upgrader) {
        // 升级连接
        $conn = $upgrader->upgradeRaw($request, $response);
        
        // ...
    });
    $server->start();
});
```

获取当前连接数

```php
$total = $upgrader->count();
```

关闭全部连接

```php
$upgrader->closeAll();
```

## 客户端 Client

可以连接任何 websocket v13 的服务器

```php
$cli   = Mix\WebSocket\Client('ws://127.0.0.1:9502/websocket');

// 发送数据
$out       = new \Swoole\WebSocket\Frame();
$out->data = 'xiaoming';
$cli->send($out);

// 接收数据
$in = $cli->recv();
var_dump($in->data);

$cli->close();
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
