# PHP Built-in CLI-Server

[CLI-Server](https://www.php.net/manual/zh/features.commandline.webserver.php) 是 PHP 内置的 Web 服务器，具有 `零扩展依赖` `热更新` `适合本机开发` 的特点。

## 入口文件

骨架路径 `public/index.php`

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * PHP-FPM, cli-server 模式专用
 */

use App\Error;
use App\Vega;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();

return Vega::new()->run();
```

## 启动服务

- API

```
php -S localhost:8000 public/index.php
```

- Web

支持静态文件处理

```
php -S localhost:8000 -t public
```