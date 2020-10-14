## Mix Guzzle

支持 Swoole 协程的 Guzzle, 可无感 Hook 第三方库，同时支持调低 PHP 错误级别

## 安装

> 注意：安装后整个项目的 Guzzle 默认 CurlHandler 将会替换为该项目的 StreamHandler，实现了无感 Hook

使用 Composer 安装：

```
composer require mix/guzzle
```

## 使用

根据 `Guzzle` 官方文档使用即可：

```php
$client   = new \GuzzleHttp\Client();
$response = $client->get('https://www.baidu.com/');
```


也可以手动指定 `handler` 如下：

```php
$handler = new \Mix\Guzzle\Handler\StreamHandler();
$stack   = \GuzzleHttp\HandlerStack::create($handler);
$client  = new \GuzzleHttp\Client([
    'handler'  => $stack,
]);
```

### `Elasticsearch PHP` 支持

> 安装后 GuzzleHttp\Ring 的 CurlHandler 将会替换为该项目的 Ring\StreamHandler，实现了无感 Hook

根据 [Elasticsearch PHP](https://github.com/elastic/elasticsearch-php) 官方文档使用即可：

```php
use GuzzleHttp\Ring\Client\StreamHandler;
use Elasticsearch\ClientBuilder;

$handler = new StreamHandler();
$builder = ClientBuilder::create();
$builder->setHosts(['127.0.0.1:9200']);
$builder->setHandler($handler);
$client = $builder->build();
```

## 原理

因为 Swoole 的 Hook 只支持 PHP Stream，Guzzle 库默认是使用 CURL 扩展，而 Swoole 不支持在协程中使用 CURL，因此本库将 Guzzle 默认的 CurlHandler 替换为 StreamHandler，并做了一些协程优化处理，让依赖 Guzzle 的第三方库无需修改代码即可使用 Swoole 协程。

## 支持的第三方库

理论上基于 Guzzle 库开发的 SDK 都可使用本库 Hook，下面是已知的支持 Hook 的第三方库清单：

> 欢迎提交 PR 更新此清单

- [alibabacloud/client](https://github.com/aliyun/openapi-sdk-php-client)
- [TencentCloud/tencentcloud-sdk-php](https://github.com/TencentCloud/tencentcloud-sdk-php)
- [elastic/elasticsearch-php](https://github.com/elastic/elasticsearch-php)

## License

Apache License Version 2.0, http://www.apache.org/licenses/
