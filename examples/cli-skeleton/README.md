# CLI development skeleton

帮助你快速搭建 CLI 项目骨架，并指导你如何使用该骨架的细节，骨架默认开启了 SQL、Redis 日志，压测前请先关闭 `.env` 的 `APP_DEBUG`

## 安装

```
composer create-project --prefer-dist mix/cli-skeleton cli
```

## 快速开始

使用 `composer` 执行命令

```
composer run-script --timeout=0 cli:clearcache
```

## 执行脚本

- `composer run-script` 命令中的 `--timeout=0` 参数是防止 composer [执行超时](https://getcomposer.org/doc/06-config.md#process-timeout)
- `composer.json` 定义了命令执行脚本，对应上面的执行命令

```json
"scripts": {
    "cli:clearcache": "php bin/cli.php clearcache"
}
```

当然也可以直接下面这样启动，效果是一样的，但是 `scripts` 能帮你记录到底有哪些可用的命令，同时在IDE中调试更加方便。

```
php bin/cli.php clearcache
```

## 编写一个 CLI 程序

首先我们在 `bin/cli.php` 入口文件中增加一个命令

- 如何配置命令：[mix/cli](https://github.com/mix-php/cli#readme)

```php
Cli::setName('app')->setVersion('0.0.0-alpha');
$cmds = [
    new Mix\Cli\Command([
        'name' => 'clearcache',
        'short' => 'Clear cache',
        'options' => [
            new Mix\Cli\Option([
                'names' => ['k', 'key'],
                'usage' => 'Key name'
            ]),
        ],
        'run' => new App\Command\ClearCache(),
    ])
];
Cli::addCommand(...$cmds)->run();
```

查看命令帮助，检查配置是否正确

```
$ php bin/cli.php 
Usage: bin/cli.php [OPTIONS] COMMAND [ARG...]

Commands:
  clearcache    Clear cache

Global Options:
  -h, --help    Print usage
  -v, --version Print version information

Run 'bin/cli.php COMMAND --help' for more information on a command.

Developed with Mix PHP framework. (openmix.org/mix-php)
```

执行 `clearcache` 命令

```
$ php bin/cli.php clearcache
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
