<?php

const DATABASE_DSN = 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test';
const DATABASE_USERNAME = 'root';
const DATABASE_PASSWORD = '123456';

/**
 * @return \Mix\Database\Database
 */
function db()
{
    return new \Mix\Database\Database(DATABASE_DSN, DATABASE_USERNAME, DATABASE_PASSWORD);
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
