# WebSocket development skeleton

帮助你快速搭建 WebSocket 项目骨架，并指导你如何使用该骨架的细节，骨架默认开启了 SQL、Redis 日志，压测前请先关闭 `.env` 的 `APP_DEBUG`

## 安装

> 需要先安装 [Swoole](https://wiki.swoole.com/#/environment)

- Swoole >= 4.4.15: https://wiki.swoole.com/#/environment

```
composer create-project --prefer-dist mix/websocket-skeleton websocket
```

## 快速开始

启动 Swoole 协程服务

```
composer run-script --timeout=0 swooleco:start
```

## 执行脚本

- `composer run-script` 命令中的 `--timeout=0` 参数是防止 composer [执行超时](https://getcomposer.org/doc/06-config.md#process-timeout)
- `composer.json` 定义了命令执行脚本，对应上面的执行命令

```json
"scripts": {
    "swooleco:start": "php bin/swooleco.php",
    "cli:clearcache": "php bin/cli.php clearcache"
}
```


当然也可以直接下面这样启动，效果是一样的，但是 `scripts` 能帮你记录到底有哪些可用的命令，同时在IDE中调试更加方便。

```
php bin/swooleco.php start
```

## 编写一个 WebSocket 服务

首先修改根目录 `.env` 文件的数据库信息

然后在 `routes/index.php` 定义一个新的路由

```php
$vega->handle('/websocket', [new WebSocket(), 'index'])->methods('GET');
```

路由里使用了 `WebSocket` 控制器，我们需要创建他

- 如何配置路由：[mix/vega](https://github.com/mix-php/vega#readme)
- 如何使用 WebSocket 升级器：[mix/websocket](https://github.com/mix-php/websocket#readme)

```php
<?php

namespace App\Controller;

use App\Container\Upgrader;
use App\Service\Session;
use Mix\Vega\Context;

class WebSocket
{

    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        $conn = Upgrader::instance()->upgrade($ctx->request, $ctx->response);
        $session = new Session($conn);
        $session->start();
    }

}
```

控制器中使用了一个 `Session` 类来处理连接事务

```php
<?php

namespace App\Service;

use App\Handler\Hello;
use Mix\WebSocket\Connection;
use Swoole\Coroutine\Channel;

class Session
{

    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Channel
     */
    protected $writeChan;

    /**
     * Session constructor.
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->writeChan = new Channel(10);
    }

    /**
     * @param string $data
     */
    public function send(string $data): void
    {
        $this->writeChan->push($data);
    }

    public function start(): void
    {
        // 接收消息
        go(function () {
            while (true) {
                $frame = $this->conn->recv();
                $message = $frame->data;

                (new Hello($this))->index($message);
            }
        });

        // 发送消息
        go(function () {
            while (true) {
                $data = $this->writeChan->pop();
                if (!$data) {
                    return;
                }
                $frame = new \Swoole\WebSocket\Frame();
                $frame->data = $data;
                $frame->opcode = WEBSOCKET_OPCODE_TEXT; // or WEBSOCKET_OPCODE_BINARY
                $this->conn->send($frame);
            }
        });
    }

}
```

在接收消息处，使用了 `src/Handler/Hello.php` 处理器对当前发送的消息做逻辑处理，我们只需根据自己的需求增加新的处理器来处理不同消息即可。

```
(new Hello($this))->index($message);
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

使用测试工具测试

- [WEBSOCKET 在线测试工具](http://www.easyswoole.com/wstool.html) `ws://127.0.0.1:9502/websocket`

## 如何使用 WebSocket 客户端

- [mix-php/websocket#客户端-client](https://github.com/mix-php/websocket#%E5%AE%A2%E6%88%B7%E7%AB%AF-client)

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

## 部署

线上部署启动时，修改 `shell/server.sh` 脚本中的绝对路径和参数

```
php=/usr/local/bin/php
file=/project/bin/swooleco.php
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
location /websocket {
    proxy_pass http://127.0.0.1:9502;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
}
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
