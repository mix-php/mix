## Mix WebSocket

基于 Swoole 协程的 WebSocket 类库，包含服务器、客户端

## Usage

安装：

```
composer require mix/websocket
```

### Server

```
$server   = new Mix\Http\Server\Server('0.0.0.0', 9597, false, false);
$upgrader = new \Mix\WebSocket\Upgrader();
$server->handle('/websocket', function (\Mix\Http\Message\ServerRequest $request, \Mix\Http\Message\Response $response) use ($upgrader) {
    // 升级为websocket
    $conn        = $upgrader->Upgrade($request, $response);

    // 接收发送数据
    $in        = $conn->recv();
    $out       = new \Swoole\WebSocket\Frame();
    $out->data = sprintf('hello, %s', $in->data);
    $conn->send($out);
    $conn->close();

    $response->send();
});
$server->start();
```

### Client

```
$dialer = new \Mix\WebSocket\Client\Dialer();
$conn   = $dialer->dial('ws://127.0.0.1:9597/websocket');

$frame       = new \Swoole\WebSocket\Frame();
$frame->data = 'xiaoming';
$conn->send($frame);

$frame = $conn->recv();
$conn->close();
var_dump($frame->data);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
