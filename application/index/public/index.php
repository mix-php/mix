<?php

// web入口文件

define('MIX_DEBUG', true);
define('MIX_ENV', 'dev');

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../mixphp/mix1/Mix.php';

$config = require __DIR__ . '/../config/main_traditional.php';
(new mix\web\Application($config))->run();
