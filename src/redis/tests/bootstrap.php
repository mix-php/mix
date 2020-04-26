<?php

const REDIS_HOST     = '127.0.0.1';
const REDIS_PORT     = 6379;
const REDIS_PASSWORD = '';
const REDIS_DATABASE = 0;

/**
 * @return \Mix\Redis\Redis
 */
function redis()
{
    $redis = new \Mix\Redis\Redis([
        'host'     => REDIS_HOST,
        'port'     => REDIS_PORT,
        'password' => REDIS_PASSWORD,
        'database' => REDIS_DATABASE,
    ]);
    $redis->init();
    return $redis;
}

function run($func)
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
