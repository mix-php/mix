<?php

// web入口文件

require __DIR__ . '/../../../vendor/autoload.php';

mix\base\Env::load(__DIR__ . '/../../../.env');

$config = require __DIR__ . '/../config/http_compatible.php';
(new mix\http\Application($config))->run();
