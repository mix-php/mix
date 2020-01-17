## Mix JSON-RPC

通用的 JSON-RPC 库，所有 Swoole 环境都可使用，支持 TCP、HTTP 两种协议。

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

常规调用:

```
$client = new \Mix\JsonRpc\Client([
    'connection' => new \Mix\JsonRpc\Connection('127.0.0.1', 9234),
]);

$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [1, 3], 100001)
$response = $client->call($request);
var_dump($response);
```

批量调用:

```
$client = new \Mix\JsonRpc\Client([
    'connection' => new \Mix\JsonRpc\Connection('127.0.0.1', 9234),
]);

$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [1, 3], 100001)
$requests[] = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.sum', [2, 3], 100002)
$responses = $client->callMultiple(...$requests);
var_dump($responses);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
