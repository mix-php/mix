# Web development skeleton

帮助你快速搭建 Web 项目骨架，并指导你如何使用该骨架的细节，骨架默认开启了 SQL、Redis 日志，压测前请先关闭 `.env` 的 `APP_DEBUG`

## 安装

> 需要先安装 [Swoole](https://wiki.swoole.com/#/environment) 或者 [WorkerMan](http://doc.workerman.net/install/requirement.html)

```
composer create-project --prefer-dist mix/web-skeleton web
```

## 快速开始

启动 [cli-server](https://www.php.net/manual/zh/features.commandline.webserver.php) 开发服务 (零依赖)

```
composer run-script --timeout=0 cliserver:start
```

启动 Swoole 多进程服务

```
composer run-script --timeout=0 swoole:start
```

启动 Swoole 协程服务

```
composer run-script --timeout=0 swooleco:start
```

启动 WorkerMan 多进程服务

```
composer run-script --timeout=0 workerman:start
```

## 执行脚本

- `composer run-script` 命令中的 `--timeout=0` 参数是防止 composer [执行超时](https://getcomposer.org/doc/06-config.md#process-timeout)
- `composer.json` 定义了命令执行脚本，对应上面的执行命令

```json
"scripts": {
    "cliserver:start": "php -S localhost:8000 -t public",
    "swoole:start": "php bin/swoole.php",
    "swooleco:start": "php bin/swooleco.php",
    "workerman:start": "php bin/workerman.php start",
    "cli:clearcache": "php bin/cli.php clearcache"
}
```

当然也可以直接下面这样启动，效果是一样的，但是 `scripts` 能帮你记录到底有哪些可用的命令，同时在IDE中调试更加方便。

```
php bin/swoole.php start
```

## 部署

- CLI

线上部署启动时，修改 `shell/server.sh` 脚本中的绝对路径和参数

```
php=/usr/local/bin/php
file=/project/bin/swoole.php
cmd=start
numprocs=1
```

启动管理

```
sh shell/server.sh start
sh shell/server.sh stop
sh shell/server.sh restart
```

使用 `nginx` 或者 `SLB` 代理到服务器端口即可

```
server {
    server_name www.domain.com;
    listen 80; 
    root /data/project/public;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        if (!-f $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```

- PHP-FPM

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

## 编写一个 Web 页面

首先修改根目录 `.env` 文件的数据库信息

然后在 `routes/index.php` 定义一个新的路由

```php
$vega->handle('/', [new Hello(), 'index'])->methods('GET');
```

路由里使用了 `Hello` 控制器，我们需要创建他

- 如何配置路由：[mix/vega](https://github.com/mix-php/vega#readme)

```php
<?php

namespace App\Controller;

use Mix\Vega\Context;

class Hello
{

    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        $ctx->HTML(200, 'index', [
            'title' => 'Hello, World!'
        ]);
    }

}
```

在 `views` 目录创建 `index.php` 视图文件

```php
<html>
<h1>
    <?= $title ?>
</h1>
</html>
```

重新启动服务器后方可测试新开发的接口

> 实际开发中使用 PhpStorm 的 Run 功能，只需要点击一下重启按钮即可

```
// 查找进程 PID
ps -ef | grep swoole

// 通过 PID 停止进程
kill PID

// 重新启动进程
composer run-script swoole:start

// curl 测试
curl http://127.0.0.1:9501/
```

## 使用容器中的对象

容器采用了一个简单的单例模式，你可以修改为更加适合自己的方式。

- 数据库：[mix/database](https://github.com/mix-php/database#readme)

```
DB::instance()
```

- Redis：[mix/redis](https://github.com/mix-php/redis#readme)

```
RDS::instance()
```

- 日志：[monolog/monolog](https://seldaek.github.io/monolog/doc/01-usage.html)

```
Logger::instance()
```

- 配置：[hassankhan/config](https://github.com/hassankhan/config#getting-values)

```
Config::instance()
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
