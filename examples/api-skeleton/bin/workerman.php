<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Container\Logger;
use App\Vega;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

App\Error::register();

$vega = Vega::new();
$http = new Workerman\Worker("http://0.0.0.0:2345");
$http->onMessage = $vega->handler();
$http->count = 4;
Logger::instance()->info('Start workerman server');
Workerman\Worker::runAll();
