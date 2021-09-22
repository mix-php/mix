#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('memory_limit', '1G');

require __DIR__ . '/../vendor/autoload.php';

use App\Error;
use App\Container\Logger;
use App\Vega;
use Dotenv\Dotenv;
use Mix\Init\StaticInit;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();

/**
 * 多进程默认开启了协程
 * 关闭协程只需关闭 `enable_coroutine` 配置并注释数据库的 `::enableCoroutine()` 即可退化为多进程同步模式
 */

$vega = Vega::new();
$host = '0.0.0.0';
$port = 9501;
$http = new Swoole\Http\Server($host, $port);
$http->on('Request', $vega->handler());
$http->on('WorkerStart', function ($server, $workerId) {
    // swoole 协程不支持 set_exception_handler 需要手动捕获异常
    try {
        StaticInit::finder(__DIR__ . '/../src/Container')->exec('init');
        App\Container\DB::enableCoroutine();
        App\Container\RDS::enableCoroutine();
    } catch (\Throwable $ex) {
        Error::handle($ex);
    }
});
$http->set([
    'enable_coroutine' => true,
    'worker_num' => 4,
]);

echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
printf("System    Name:       %s\n", strtolower(PHP_OS));
printf("PHP       Version:    %s\n", PHP_VERSION);
printf("Swoole    Version:    %s\n", swoole_version());
printf("Listen    Addr:       http://%s:%d\n", $host, $port);
Logger::instance()->info('Start swoole server');

$http->start();
