<?php

require __DIR__ . '/../vendor/autoload.php';

use Mix\Vega\Engine;
use Mix\Vega\Context;

$vega = new Engine();
$vega->handleF('/hello', function (Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http_worker = new Workerman\Worker("http://0.0.0.0:2345");
$http_worker->onMessage = $vega->handler();
$http_worker->count = 4;
Workerman\Worker::runAll();
