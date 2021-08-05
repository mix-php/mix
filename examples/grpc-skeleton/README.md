# gRPC development skeleton

帮助你快速搭建 gRPC 项目骨架，并指导你如何使用该骨架的细节，骨架默认开启了 SQL、Redis 日志，压测前请先关闭 `.env` 的 `APP_DEBUG`

## 安装

> 需要先安装 [Swoole](https://wiki.swoole.com/#/environment)

- Swoole >= 4.4.4: https://wiki.swoole.com/#/environment
- 需要开启 `--enable-http2`

```
composer create-project --prefer-dist mix/grpc-skeleton grpc
```

## 快速开始

启动 Swoole 多进程服务

```
composer run-script --timeout=0 swoole:start
```

启动 Swoole 协程服务

```
composer run-script --timeout=0 swooleco:start
```

## 执行脚本

- `composer run-script` 命令中的 `--timeout=0` 参数是防止 composer [执行超时](https://getcomposer.org/doc/06-config.md#process-timeout)
- `composer.json` 定义了命令执行脚本，对应上面的执行命令

```json
"scripts": {
    "swoole:start": "php bin/swoole.php",
    "swooleco:start": "php bin/swooleco.php",
    "cli:clearcache": "php bin/cli.php clearcache"
},
```

当然也可以直接下面这样启动，效果是一样的，但是 `scripts` 能帮你记录到底有哪些可用的命令，同时在IDE中调试更加方便。

```
php bin/swoole.php start
```

## 部署

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

gRPC 通常都是内部使用，使用内网 `SLB` 代理到服务器IP或者直接使用 IP:PORT 调用

## 编写一个 gRPC 接口

首先修改根目录 `.env` 文件的数据库信息

然后在 `proto` 目录创建 `greeter.proto` 文件，并根据 [使用说明](https://github.com/mix-php/grpc#%E9%80%9A%E8%BF%87-proto-%E7%94%9F%E6%88%90-php-%E4%BB%A3%E7%A0%81) 将 .proto 文件生成为 PHP 代码

```
protoc --php_out=. --mix_out=. greeter.proto
```

然后创建一个新的服务 `src/Service/Say.php`

- `Say` 类实现了代码生成器生成的 `Php\Micro\Grpc\Greeter\SayInterface` 接口

```php
<?php

namespace App\Service;

use Mix\Grpc\Context;
use Php\Micro\Grpc\Greeter\Request;
use Php\Micro\Grpc\Greeter\Response;

/**
 * Class Say
 * @package App\Service
 */
class Say implements \Php\Micro\Grpc\Greeter\SayInterface
{

    /**
     * @param Context $context
     * @param Request $request
     * @return Response
     */
    public function Hello(Context $context, Request $request): Response
    {
        $response = new Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}
```

在 `src/Grpc.php` 中将服务注册到服务器

```php
$server->register(Say::class);
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
```

## 如何使用 gRPC 客户端

- [mix/grpc#客户端调用一个 gRPC 服务](https://github.com/mix-php/grpc#%E5%AE%A2%E6%88%B7%E7%AB%AF%E8%B0%83%E7%94%A8%E4%B8%80%E4%B8%AA-grpc-%E6%9C%8D%E5%8A%A1)

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
