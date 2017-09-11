<?php

// web入口文件

define('MIX_DEBUG', true);
define('MIX_ENV', 'dev');
define('DS', DIRECTORY_SEPARATOR);

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../mixphp/mix1/base/Mix.php';

$config = require __DIR__ . '/../config/main_web.php';
(new mix\web\Application($config))->run();
