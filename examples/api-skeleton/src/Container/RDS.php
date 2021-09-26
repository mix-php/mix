<?php

namespace App\Container;

use Mix\Redis\Redis;

class RDS
{

    /**
     * @var Redis
     */
    private static $instance;

    public static function init(): void
    {
        $host = $_ENV['REDIS_HOST'];
        $port = $_ENV['REDIS_PORT'];
        $password = $_ENV['REDIS_PASSWORD'];
        $database = $_ENV['REDIS_DATABASE'];
        $rds = new Redis($host, $port, $password, $database);
        APP_DEBUG and $rds->setLogger(new RDSLogger());
        self::$instance = $rds;
    }

    /**
     * @return Redis
     */
    public static function instance(): Redis
    {
        if (!isset(self::$instance)) {
            static::init();
        }
        return self::$instance;
    }

    public static function enableCoroutine()
    {
        $maxOpen = 30;        // 最大开启连接数
        $maxIdle = 10;        // 最大闲置连接数
        $maxLifetime = 3600;  // 连接的最长生命周期
        $waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

}
