<?php

require __DIR__ . '/../vendor/autoload.php';

$scheduler = new \Swoole\Coroutine\Scheduler;
$scheduler->add(function () {

    $sub = new \Mix\Redis\Subscribe\Subscriber('127.0.0.1', 6379, '', 5);
    $sub->subscribe('foo', 'bar'); // 订阅失败将抛出异常

    \Swoole\Timer::after(2000, function () use ($sub) {
        var_dump('add subscribe');
        $sub->subscribe('foo1', 'bar1');
        var_dump('un subscribe');
        $sub->unsubscribe('foo', 'bar');
    });

    \Swoole\Timer::after(20000, function () use ($sub) {
        var_dump('close');
        $sub->close();
    });

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

});
$scheduler->start();
