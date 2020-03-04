## Mix JSON-RPC

通用的 JSON-RPC 库，支持所有 Swoole 环境，同时支持 TCP、HTTP 两种协议，并具有微服务调用功能。

## Usage

- 安装

```
composer require mix/json-rpc
```

- Service

首先定义一个服务

```
class Calculator
{
    public function Sum(int $a, int $b): int
    {
        return array_sum([$a, $b]);
    }
}
```

- Server

创建服务器，并注册 Calculator 服务

```
$server  = new \Mix\JsonRpc\Server('127.0.0.1', 9234);
$service = new Calculator();
$server->register($service);
$server->start();
```

扩展 HTTP 支持

```
$http = new \Mix\Http\Server\Server('127.0.0.1', 9235);
$http->start($server);
```

- Client

创建客户端

```
$client = new \Mix\JsonRpc\Client([
    'dialer' => new \Mix\JsonRpc\Dialer([
        'host'    => '127.0.0.1',
        'port'    => 9234,
        'timeout' => 3,
    ]),
]);
```

常规调用:

```
$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [1, 3], 100001)
$response = $client->call($request);
var_dump($response);
```

批量调用:

```
$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [1, 3], 100001)
$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [2, 3], 100002)
$responses = $client->callMultiple(...$requests);
// $responses 不保证顺序，需使用 id 遍历结果
var_dump($responses);
```

- 微服务调用

创建带服务中心的客户端

```
$client = new \Mix\JsonRpc\Client([
    'dialer' => new \Mix\JsonRpc\Dialer([
        'timeout' => 3,
    ]),
    'serviceCenter' => new \Mix\Etcd\ServiceCenter([
       'host'    => '127.0.0.1',
       'port'    => 2379,
       'ttl'     => 10,
    ]);
]);
```

通过服务调用：

```
$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [1, 3], 100001)
$response = $client->service('php.micro.srv.calculator')->call($request);
var_dump($response);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
