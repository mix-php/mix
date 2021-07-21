> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Worker Pool

Swoole-based worker pool, coroutine pool

基于 Swoole 的工作池，协程池

> go 版本：https://github.com/mix-go/xwp

## Installation

```
composer require mix/worker-pool
```

## 单次调度

- 如果不想阻塞执行，可以使用 `$pool->start()` 启动

```php
$jobQueue = new Swoole\Coroutine\Channel(200);
$maxWorkers = 100;
$handler = function ($data) {
    // do something
};
$pool = new Mix\WorkerPool\WorkerPool($jobQueue, $maxWorkers, $handler);

go(function () use ($jobQueue, $pool) {
    // 投放任务
    for ($i = 0; $i < 1000; $i++) {
        $jobQueue->push($i);
    }
    // 停止
    $pool->stop();
});

$pool->run(); // 阻塞等待
```

上面是采用闭包处理任务，也可以使用对象处理任务

```php
class FooHandler implements \Mix\WorkerPool\RunInterface
{
    public function do($data): void
    {
        // do something
    }
}
$pool = new Mix\WorkerPool\WorkerPool($jobQueue, $maxWorkers, new FooHandler());
```

## 常驻调度

> 适合处理 MQ 队列的异步消费

以 Redis 作为 MQ 为例：

```php
$maxWorkers = 20;
$maxQueue = 10;
$jobQueue = new Swoole\Coroutine\Channel($maxQueue);
$handler = function ($data) {
    // do something
};
$pool = new Mix\WorkerPool\WorkerPool($jobQueue, $maxWorkers, $handler);

$quit = new Swoole\Coroutine\Channel();
foreach ([SIGHUP, SIGINT, SIGTERM] as $signal) {
    Swoole\Process::signal($signal, function () use ($quit) {
        $quit->push(true);
    });
}

go(function () use ($jobQueue, $pool, $quit) {
    // 投放任务
    while (true) {
        if (!$quit->isEmpty()) {
            $pool->stop();
            return;
        }
        try {
            $data = $redis->brPop(['test'], 1);
        } catch (\Throwable $ex) {
            // print log
            $pool->stop();
            return;
        }
        if (!$data) {
            continue;
        }
        $data = array_pop($data); // brPop命令最后一个键才是值
        $jobQueue->push($data);
    }
});

$pool->run(); // 阻塞等待
```

## 异常处理

闭包或者对象 `do` 方法中执行的代码，可能会抛出异常，必须要使用 `try/catch` 避免协程退出

```php
class FooHandler implements \Mix\WorkerPool\RunInterface
{
    public function do($data): void
    {
        try {
            // do something
        } catch (\Throwable $ex){
            // print log
        }
    }
}
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
