## Mix Redis

redis for use in multiple execution environments, with support for FPM, CLI, Swoole, WorkerMan, and optional
connection pool (coroutine)

可在各种环境中使用的 redis，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a)
, [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)
敲门暗号：redis

## Installation

```
composer require mix/redis
```

## Quick start



## Start Pool

在 `Swoole` 协程环境中，启动连接池

```php
$maxOpen = 50;        // 最大开启连接数
$maxIdle = 20;        // 最大闲置连接数
$maxLifetime = 3600;  // 连接的最长生命周期
$waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
$db->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
```

连接池统计

```php
$db->poolStats(); // array, fields: total, idle, active
```

## Multi & Pipeline



## Watch

## License

Apache License Version 2.0, http://www.apache.org/licenses/
