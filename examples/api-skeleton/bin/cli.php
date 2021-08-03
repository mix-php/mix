<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

App\Error::register();

switch ($argv[1]) {
    case 'clearcache';
        (new \App\Command\ClearCache())->exec();
        break;
}
