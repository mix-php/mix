#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);

require __DIR__ . '/../vendor/autoload.php';

use App\Error;
use Dotenv\Dotenv;
use Mix\Cli\Cli;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();

Cli::setName('app')->setVersion('0.0.0-alpha');
$cmds = [
    new Mix\Cli\Command([
        'name' => 'clearcache',
        'short' => 'Clear cache',
        'options' => [
            new Mix\Cli\Option([
                'names' => ['k', 'key'],
                'usage' => 'Key name'
            ]),
        ],
        'run' => new App\Command\ClearCache(),
    ])
];
Cli::addCommand(...$cmds)->run();
