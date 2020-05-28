## Mix JSON-RPC

基于 Swoole 协程的 JSON-RPC 库，包含服务器、客户端，同时支持 TCP、HTTP 两种协议，并具有微服务调用功能

## Usage

安装：

- Swoole >= 4.4.4: https://wiki.swoole.com/#/environment

```
composer require mix/json-rpc
```

- Service

首先定义一个服务

```
class Calculator
{
    public const NAME = "php.micro.jsonrpc.calculator.Calculator";

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

- Client 调用

创建客户端

```
$dialer = new \Mix\JsonRpc\Client\Dialer();
$conn   = $dialer->dial('127.0.0.1', 9234);

$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 100001)
$response = $conn->call($request);
var_dump($response);
```

- Client 微服务调用

```
$dialer = new \Mix\JsonRpc\Client\Dialer([
    'registry' => new \Mix\Micro\Etcd\Registry('http://127.0.0.1:2379/v3'),
]);
$conn   = $dialer->dialFromService('php.micro.jsonrpc.calculator');

$request = (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 100001)
$response = $conn->call($request);
var_dump($response);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
