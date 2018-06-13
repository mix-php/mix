<?php

// web入口文件

define('MIX_DEBUG', true);
define('MIX_ENV', 'dev');

require __DIR__ . '/../../../vendor/autoload.php';

$config = require __DIR__ . '/../config/main_compatible.php';
(new mix\http\Application($config))->run();
