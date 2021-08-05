> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

## Mix CLI

PHP CLI Interactive Commander

PHP 命令行交互指挥官

> go 版本：https://github.com/mix-go/xcli

## Overview

一个命令行交互与指挥管理工具，它可以让单个 CLI 可执行多种功能，同时它还包括命令行参数获取、全局异常捕获与处理等命令行开发常用功能。

## Installation

```
composer require mix/cli
```

## Quick start

```php
Mix\Cli\Cli::setName('app')->setVersion('0.0.0-alpha');
$cmd = new Mix\Cli\Command([
    'name' => 'hello',
    'short' => 'Echo demo', 
    'run' => function () {
        $name = Mix\Cli\Flag::match('n', 'name')->string('default');
        // do something
    }
]);
$opt = new Mix\Cli\Option([
    'names' => ['n', 'name'],
    'usage' => 'Your name'
]);
$cmd->addOption($opt);
Mix\Cli\Cli::addCommand($cmd)->run();
```

上面是采用闭包，也可以使用对象

```php
class FooCommand implements Mix\Cli\RunInterface
{
    public function main(): void
    {
        // do something
    }
}
$cmd = new Mix\Cli\Command([
    'name' => 'hello',
    'short' => 'Echo demo', 
    'run' => new FooCommand(),
]);
```

查看整个命令行程序的帮助

```
$ php app.php
Usage: app.php [OPTIONS] COMMAND [ARG...]

Commands:
  hello         Echo demo

Global Options:
  -h, --help    Print usage
  -v, --version Print version information

Run 'app.php COMMAND --help' for more information on a command.

Developed with Mix PHP framework. (openmix.org/mix-php)
```

查看命令行程序的版本信息

```
$ php app.php -v
app 0.0.0-alpha
```

查看 `hello` 命令的帮助

```
$ php app.php hello --help
Usage: app.php hello [ARG...]

Command Options:
  -n, --name    Your name

Developed with Mix PHP framework. (openmix.org/mix-php)
```

执行 `hello` 命令

```
$ php app.php hello
```

## Flag 参数获取

参数规则 (部分UNIX风格+GNU风格)

```
php /examples/app.php home -d -rf --debug -v vvv --page 23 -s=test --name=john arg0
```

- 命令：
    - 第一个参数，可以为空：`home`
- 选项：
    - 短选项：一个中杠，如 `-d`、`-rf`
    - 长选项：二个中杠，如：`--debug`
- 选项值：
    - 无值：`-d`、`-rf`、 `--debug`
    - 有值(空格)：`-v vvv`、`--page 23`
    - 有值(等号)：`-s=test`、`--name=john`
- 参数：
    - 没有定义 `-` 的参数：`arg0`

获取选项，可以获取 `string`、`bool`、`int`、`float` 多种类型，也可以指定默认值。

```php
$name = Mix\Cli\Flag::match('n', 'name')->string('Xiao Ming');
```

获取第一个参数

```php
$arg0 = Mix\Cli\Flag::arguments()->first()->string();
```

获取全部参数

```php
foreach (Mix\Cli\Flag::arguments()->values() as $k => $v) {
    // do something
}
```

## Handle panic 错误处理

使用中间件处理异常，也可以单独对某个命令配置中间件

```php
$h = function ($next) {
    try {
        $next();
    } catch (\Throwable $ex){
        if ($ex instanceof Mix\Cli\Exception\NotFoundException) {
            throw $ex;
        }
        // handle panic
    }
};
$cmd = new Mix\Cli\Command([
    'name' => 'hello',
    'short' => 'Echo demo', 
    'run' => function () {
        // do something
    }
]);
Mix\Cli\Cli::use($h)->addCommand($cmd)->run();
```

## Application

我们在编写代码时，可能会要用到 App 中的一些信息。

```
// 获取基础路径(入口文件所在目录路径)
Mix\Cli\Cli::app()->basePath

// App名称
Mix\Cli\Cli::app()->name

// App版本号
Mix\Cli\Cli::app()->version

// 是否开启debug
Mix\Cli\Cli::app()->debug
```

## Singleton 单命令

当我们的 CLI 只有一个命令时，只需要配置一下 `Singleton`：

~~~php
$cmd = new Mix\Cli\Command([
    'name' => 'hello',
    'short' => 'Echo demo', 
    'run' => function () {
        // do something
    },
    'singleton' => true,
]);
~~~

命令的 Options 将会在 `-h/--help` 中打印

~~~
$ php app.php
Usage: app.php [OPTIONS] COMMAND [ARG...]

Command Options:
  -n, --name    Your name

Global Options:
  -h, --help    Print usage
  -v, --version Print version information

Run 'app.php COMMAND --help' for more information on a command.

Developed with Mix PHP framework. (openmix.org/mix-php)
~~~

## License

Apache License Version 2.0, http://www.apache.org/licenses/
