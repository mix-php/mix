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
use Mix\Init\Finder;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();
Finder::in(__DIR__ . '/../src/Container')->exec('init');

$vega = Vega::new();
$addr = 'http://0.0.0.0:2345';
$http = new Workerman\Worker($addr);
$http->onWorkerStart = function ($worker) {
    Finder::in(__DIR__ . '/../src/Container')->exec('connect');
};
$http->onMessage = $vega->handler();
$http->count = 4;

echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
printf("System       Name:       %s\n", strtolower(PHP_OS));
printf("PHP          Version:    %s\n", PHP_VERSION);
printf("Workerman    Version:    %s\n", Workerman\Worker::VERSION);
printf("Listen       Addr:       %s\n", $addr);
Logger::instance()->info('Start workerman server');

Workerman\Worker::runAll();
