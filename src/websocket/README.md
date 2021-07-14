## Mix WebSocket

PHP WebSocket server and client based on Swoole coroutine

基于 Swoole 协程的 PHP WebSocket 服务器与客户端

**推荐搭配以下数据库使用 (支持协程和连接池)：**

- https://github.com/mix-php/database
- https://github.com/mix-php/redis

## Installation

```
composer require mix/websocket
```

## 服务器 Server

在 [Mix Vega](https://github.com/mix-php/vega) 中使用

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

在 Swoole 原生中使用

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
