## Mix Monolog

支持 Swoole 协程的 Monolog，支持 cli 控制台打印

## Usage

安装：

```
composer require mix/monolog
```

创建 Logger：

```
$logger  = new \Mix\Monolog\Logger('API', [new \Mix\Monolog\Handler\ConsoleHandler], [new \Monolog\Processor\PsrLogMessageProcessor]);
$handler = new \Mix\Monolog\Handler\RotatingFileHandler(sprintf('%s/runtime/logs/api.log', $basePath), 7);
$logger->pushHandler($handler);
```

调用：

```
$logger->info('server started');
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
