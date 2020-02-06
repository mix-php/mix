<?php

const DATABASE_DSN      = 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test';
const DATABASE_USERNAME = 'root';
const DATABASE_PASSWORD = '123456';

/**
 * @return \Mix\Database\Connection
 */
function db()
{
    $conn = new \Mix\Database\Connection([
        // 数据源格式
        'dsn'      => DATABASE_DSN,
        // 数据库用户名
        'username' => DATABASE_USERNAME,
        // 数据库密码
        'password' => DATABASE_PASSWORD,
    ]);
    $conn->connect();
    return $conn;
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
