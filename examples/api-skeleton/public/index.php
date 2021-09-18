<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * PHP-FPM, cli-server 模式专用
 */

use App\Error;
use App\Vega;
use Dotenv\Dotenv;
use Mix\Init\Finder;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();
Finder::in(__DIR__ . '/../src/Container')->exec('init', 'connect');

return Vega::new()->run();
