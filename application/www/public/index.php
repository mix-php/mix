<?php

define('EXPRESS_DEBUG', true);
define('EXPRESS_ENV', 'dev');
define('DS', DIRECTORY_SEPARATOR);

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../express/library/Express.php';

$config = require __DIR__ . '/../config/main.php';

(new express\base\Application($config))->run();
