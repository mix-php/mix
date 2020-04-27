## Mix Sync Invoke

通用的同步代码执行库，支持所有 Swoole 环境，可用于执行无法被 Swoole Hook 的同步阻塞代码。

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
