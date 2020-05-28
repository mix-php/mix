## Mix Http Server

基于 Swoole 协程的 http server 类库，遵循 PSR-7 / PSR-15 标准

## Usage

安装：

- Swoole >= 4.4.4: https://wiki.swoole.com/#/environment

```
composer require mix/http-server
```

服务器：

- 使用 `handle` 配置

> '/' 将匹配所有路由

```
$server = new Mix\Http\Server\Server('0.0.0.0', 9596, false, false);
$server->handle('/', function (\Mix\Http\Message\ServerRequest $request, \Mix\Http\Message\Response $response) {
    $response->withBody(new \Mix\Http\Message\Stream\ContentStream('hello, world!'));
    $response->send();
});
$server->start();
```

- 实现 `HandlerInterface` 接口

```
class Router implements \Mix\Http\Server\ServerHandlerInterface
{
    public function handleHTTP(ServerRequest $request, Response $response) {
        // ...
    }
}

$handler = new Router();

$server = new Mix\Http\Server\Server('0.0.0.0', 9596, false, false);
$server->start($handler);
```

中间件：

```
$process    = function (ServerRequest $request, Response $response) use ($result) {
    // ...
    return $response;
};
$dispatcher  = new MiddlewareDispatcher($middleware, $process, $request, $response);
$response    = $dispatcher->dispatch();
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
