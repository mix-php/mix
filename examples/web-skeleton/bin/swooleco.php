<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Container\Logger;
use App\Vega;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

App\Error::register();

Swoole\Coroutine\run(function () {
    $vega = Vega::new();
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9502, false, false);
    $server->handle('/', $vega->handler());

    App\Container\DB::enableCoroutine();
    App\Container\RDS::enableCoroutine();

    foreach ([SIGHUP, SIGINT, SIGTERM] as $signal) {
        Swoole\Process::signal($signal, function () use ($server) {
            Logger::instance()->info('Shutdown swoole coroutine server');
            $server->shutdown();
        });
    }

    Logger::instance()->info('Start swoole coroutine server');
    $server->start();
});
