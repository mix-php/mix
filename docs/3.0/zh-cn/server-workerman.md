# WorkerMan

[WorkerMan](https://www.workerman.net/) 驱动，该模式为多进程模型，具有 `常驻内存` `兼容 composer 生态` 的特点。

## 入口文件

骨架路径 `bin/workerman.php`

```php
#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('memory_limit', '1G');

require __DIR__ . '/../vendor/autoload.php';

use App\Error;
use App\Container\Logger;
use App\Vega;
use Dotenv\Dotenv;
use Mix\Init\StaticInit;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();

$vega = Vega::new();
$addr = 'http://0.0.0.0:2345';
$http = new Workerman\Worker($addr);
$http->onWorkerStart = function ($worker) {
    StaticInit::finder(__DIR__ . '/../src/Container')->exec('init');
};
$http->onMessage = $vega->handler();
$http->count = 4;

echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
printf("System       Name:       %s\n", strtolower(PHP_OS));
printf("PHP          Version:    %s\n", PHP_VERSION);
printf("Workerman    Version:    %s\n", Workerman\Worker::VERSION);
printf("Listen       Addr:       %s\n", $addr);
Logger::instance()->info('Start workerman server');

Workerman\Worker::runAll();
```

## 启动服务

```
php bin/workerman.php start
```
