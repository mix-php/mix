## Mix Sync Invoke

Swoole 同步阻塞代码调用库，用于执行无法被 Swoole Hook 协程化的同步阻塞代码

## Usage

- 安装

```
composer require mix/sync-invoke
```

- Server

创建服务器，用于执行同步代码，第二个参数为 `true` 可复用端口

```
$server = new \Mix\SyncInvoke\Server(9505, true);
$server->start();
```

- Client

```
$dialer = new \Mix\SyncInvoke\Client\Dialer();
$client = $dialer->dial(9505);
$data   = $client->invoke(function () {
    $obj = new Hello();
    return [1, 2, 3, $obj];
});
var_dump($data);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
