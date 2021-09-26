# 常见问题

## WorkerMan 增加 max_request

常驻内存最大优点就是性能强劲，但是对于不太细心的程序员，就非常容易写出内存溢出的问题，通常不太严重的问题都可以通过 `max_request` 解决，Swoole 官方就提供了该配置参数，但是 WorkerMan 则需要自己实现。

- 修改 `bin/workerman.php` 的 `onMessage` 代码如下

```php
$http->onMessage = function () use ($vega, $http) {
    // max_request
    static $max_request = 0;
    static $timer = null;
    if (++$max_request > 10000000 && is_null($timer)) {
        $timer = Workerman\Timer::add(rand(1, 10), function () use ($http) {
            if (count($http->connections) === 0) {
                Workerman\Worker::stopAll();
            }
        });
    }

    // call
    $handler = $vega->handler();
    $handler(...func_get_args());
};
```


