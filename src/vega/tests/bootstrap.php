<?php

function swoole_run(Mix\Vega\Engine $vega)
{
    $http = new Swoole\Http\Server('0.0.0.0', 9501);
    $http->on('Request', $vega->handler());
    $http->start();
}

function swoole_co_run(Mix\Vega\Engine $vega)
{
    $scheduler = new \Swoole\Coroutine\Scheduler;
    $scheduler->set([
        'hook_flags' => SWOOLE_HOOK_ALL,
    ]);
    $scheduler->add(function () use ($vega) {
        $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
        $server->handle('/', $vega->handler());
        $server->start();
    });
    $scheduler->start();
}

function wokerman_run(Mix\Vega\Engine $vega)
{
    $http_worker = new Workerman\Worker("http://0.0.0.0:2345");
    $http_worker->onMessage = $vega->handler();
    $http_worker->count = 4;
    Workerman\Worker::runAll();
}
