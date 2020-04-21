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
$class = Calculator::class;
$server->register($class);
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
$dialer = new \Mix\JsonRpc\Client\Dialer();
$conn   = $dialer->dial('127.0.0.1', 9234);
```

常规调用:

```
$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 100001)
$response = $conn->call($request);
var_dump($response);
```

批量调用:

```
$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 100001)
$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [2, 3], 100002)
$responses = $conn->callMultiple(...$requests);
// $responses 不保证顺序，需使用 id 遍历结果
var_dump($responses);
```

- 微服务调用

创建带注册中心的客户端

```
$dialer = new \Mix\JsonRpc\Client\Dialer([
    'registry' => new \Mix\Micro\Etcd\Registry([
        'host' => '127.0.0.1',
        'port' => 2379,
        'ttl'  => 10,
    ]),
]);
$conn   = $dialer->dialFromService('php.micro.srv.calculator');
```

常规调用:

```
$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 100001)
$response = $conn->call($request);
var_dump($response);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
