#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('memory_limit', '1G');

date_default_timezone_set('Asia/Shanghai');

!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require BASE_PATH . '/vendor/autoload.php';

use App\Container\Logger;
use App\Error;
use App\Vega;
use Dotenv\Dotenv;
use App\Container\Swow\Http\Server;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));
Error::register();

$vega = Vega::new();
$server = new Server();
$host = '0.0.0.0';
$port = 9501;
$server->bind($host, $port)->handle($vega->handler());
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
printf("Swow      Version:    %s\n", '0.1.0');
printf("Listen    Addr:       http://%s:%d\n", $host, $port);
Logger::instance()->info('Start swow coroutine server');
$server->start();



