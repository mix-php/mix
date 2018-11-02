<?php

// web入口文件

require __DIR__ . '/../../../vendor/autoload.php';

Mix\Base\Env::load(__DIR__ . '/../../../.env');

$config = require __DIR__ . '/../config/http_compatible.php';
(new Mix\Http\Application($config))->run();
