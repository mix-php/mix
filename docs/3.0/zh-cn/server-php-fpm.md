# PHP-FPM

PHP-FPM 部署具有 `热更新` `适合共享开发` `适合 admin 开发` 的特点，即便是在该传统模式下压测结果都接近 `Phalcon` 的性能。

## 入口文件

骨架路径 `public/index.php`

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * PHP-FPM, cli-server 模式专用
 */

use App\Vega;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

App\Error::register();

return Vega::new()->run();
```

## 部署

和 Laravel、ThinkPHP 部署方法完全一致，将 `public/index.php` 在 `nginx` 配置 `rewrite` 重写即可

```
server {
    server_name www.domain.com;
    listen 80;
    root /data/project/public;
    index index.html index.php;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
        }
    }

    location ~ ^(.+\.php)(.*)$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
