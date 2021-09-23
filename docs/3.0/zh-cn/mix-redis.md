## Mix Redis

可在各种环境中使用的 PHP Redis，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)

## Installation

```
composer require mix/redis
```

## Quick start

注意：[协程环境中，不可在并发请求中使用单例](zh-cn/instructions.md?id=%e5%8d%8f%e7%a8%8b%e5%8d%95%e4%be%8b%e5%ae%9e%e4%be%8b%e5%8c%96)

```php
$rds = new Mix\Redis\Redis('127.0.0.1', 6379, 'password', 0);

$rds->set('foo', 'bar');
$value = $rds->get('foo');
```

## Start Pool

在 `Swoole` 协程环境中，启动连接池

```php
$maxOpen = 50;        // 最大开启连接数
$maxIdle = 20;        // 最大闲置连接数
$maxLifetime = 3600;  // 连接的最长生命周期
$waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
$rds->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
```

连接池统计

```php
$rds->poolStats(); // array, fields: total, idle, active
```

## Transaction Multi & Pipeline

Multi

事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由exec命令原子性(atomic)地执行。

```php
$tx = $rds->multi();
$tx->set('foo', 'bar');
$tx->set('foo1', 'bar1');
$ret = $tx->exec();
```

Pipeline

客户端将执行的命令写入到缓冲中，最后由exec命令一次性发送给redis执行返回。

```php
$tx = $rds->pipeline();
$tx->set('foo', 'bar');
$tx->set('foo1', 'bar1');
$ret = $tx->exec();
```

## Transaction Watch

监听值的变化，如果执行时有变化则事务失败，无变化则事务成功。

```php
$tx = $rds->watch('foo');
$tx->incr('foo');
$ret = $tx->exec();
```

## Logger

日志记录器，配置后可打印全部SQL信息

```php
$db->setLogger($logger);
```

`$logger` 需实现 `Mix\Redis\LoggerInterface`

```php
interface LoggerInterface
{
    public function trace(float $time, string $cmd, array $args, ?\Throwable $exception): void;
}
```
