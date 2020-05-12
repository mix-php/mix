## Mix Redis Subscribe

基于 Swoole 协程的 Redis 原生订阅库

使用 Socket 直接连接 Redis 服务器，不依赖 phpredis 扩展的订阅器，该订阅器有如下优点：

- 平滑修改：可随时增加、取消订阅通道，实现无缝切换通道的需求。
- 跨协程安全关闭：可在任意时刻关闭订阅。
- 通道获取消息：该库封装风格参考 golang 语言 [go-redis](https://github.com/go-redis/redis) 库封装，通过 channel 获取订阅的消息。

## 环境依赖 (Require)

* PHP >= 7.0
* Swoole >= 4.4

## 使用 (Usage)

安装：

```
composer require mix/redis-subscribe
```

代码：

- 连接、订阅失败会抛出异常

```
$sub = new \Mix\Redis\Subscribe\Subscriber('127.0.0.1', 6379, '', 5); // 连接失败将抛出异常
$sub->subscribe('foo', 'bar'); // 订阅失败将抛出异常

$chan = $sub->channel();
while (true) {
    $data = $chan->pop();
    if (empty($data)) { // 手动close与redis异常断开都会导致返回false
        if (!$sub->closed) {
            // redis异常断开处理
            var_dump('Redis connection is disconnected abnormally');
        }
        break;
    }
    var_dump($data);
}
```

接收到订阅消息：

```
object(Mix\Redis\Subscribe\Message)#8 (2) {
  ["channel"]=>
  string(2) "foo"
  ["payload"]=>
  string(4) "test"
}
```

全部方法：

|  方法  |  描述  |
| --- | --- |
|  subscribe(string ...$channels) : void  |  增加订阅  |
|  unsubscribe(string ...$channels) : void  |  取消订阅  |
|  channel() : Swoole\Coroutine\Channel  |  获取消息通道  |
|  close() : void  |  关闭订阅  |

## License

Apache License Version 2.0, http://www.apache.org/licenses/
