<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Container\Logger;
use App\Grpc;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

App\Error::register();

/**
 * 多进程默认开启了协程
 * 关闭协程只需关闭 `enable_coroutine` 配置并注释数据库的 `::enableCoroutine()` 即可退化为多进程同步模式
 */

$grpc = Grpc::new();
$http = new Swoole\Http\Server('0.0.0.0', 9501);
$http->on('Request', $grpc->handler());
$http->on('WorkerStart', function ($server, $workerId) {
    // swoole 协程不支持 set_exception_handler 需要手动捕获异常
    try {
        App\Container\DB::enableCoroutine();
        App\Container\RDS::enableCoroutine();
    } catch (\Throwable $ex) {
        App\Error::handle($ex);
    }
});
$http->set([
    'enable_coroutine' => true,
    'worker_num' => 4,
    'open_http2_protocol' => true,
    'http_compression' => false,
]);
Logger::instance()->info('Start swoole server');
$http->start();
