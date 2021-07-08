<?php

const REDIS_HOST = '127.0.0.1';
const REDIS_PORT = 6379;
const REDIS_PASSWORD = '';
const REDIS_DATABASE = 0;

/**
 * @return \Mix\Redis\Redis
 */
function redis()
{
    return new \Mix\Redis\Redis(REDIS_HOST, REDIS_PORT, REDIS_PASSWORD, REDIS_DATABASE);
}

function swoole_co_run($func)
{
    $scheduler = new \Swoole\Coroutine\Scheduler;
    $scheduler->set([
        'hook_flags' => SWOOLE_HOOK_ALL,
    ]);
    $scheduler->add(function () use ($func) {
        call_user_func($func);
    });
    $scheduler->start();
}
