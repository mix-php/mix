<?php

const DATABASE_DSN      = 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test';
const DATABASE_USERNAME = 'root';
const DATABASE_PASSWORD = '123456';

/**
 * @return \Mix\Database\Connection
 */
function db()
{
    $db = new \Mix\Database\Database([
        'dsn'      => DATABASE_DSN,
        'username' => DATABASE_USERNAME,
        'password' => DATABASE_PASSWORD,
    ]);
    $db->init();
    return $db->borrow();
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
