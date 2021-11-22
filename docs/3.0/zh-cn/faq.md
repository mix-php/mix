# 常见问题

## 抛出异常：`The Connection::class cannot be executed repeatedly, please use the Database::class call`

这是 `mix/database` 抛出的异常，原因是：

- 为了实现协程和同步两种模式的数据库代码完全一致，我们主动限制了 `$db->table()`、`$db->insert()` 方法返回的 `Connection::class` 对象只能执行一次查询，不可复用，必须每次都从 `$db->` 开始一个新的查询。
- 这样设计的原因是：当使用协程带有连接池时，`Connection::class` 是一次性的，当他执行完查询会自动归还到池，为了实现用户无感的自动归还，因此不能复用这个连接对象，同时为了让用户的代码可以在协程和同步中都可以兼容，同步模式也人为的增加了以上限制。
- 关于连接复用：底层已经处理好了，`$db->table()`、`$db->insert()` 方法返回的 `Connection::class` 虽然是一次性的，但是底层的数据库连接是复用的，用户无需自己去复用这个对象，只需要每次都从 `$db->` 开始一个新的查询。

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


