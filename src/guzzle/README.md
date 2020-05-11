## Guzzle

支持 Swoole 协程的 Guzzle, 可 Hook 第三方库

## 安装

支持的 Guzzle 版本，安装后 Guzzle 会切换为以下版本：

- guzzle-6.4
- guzzle-6.5

使用 Composer 安装：

```
composer require mix/guzzle
```

### 方法一

当我们自己项目中使用时，可以手动指定 `handler` 的时候，如下：

```
$handler = new \Mix\Guzzle\Handler\StreamHandler();
$stack   = \GuzzleHttp\HandlerStack::create($handler);
$client  = new \GuzzleHttp\Client([
    'handler'  => $stack,
]);
```

### 方法二

> 适用于 Hook 无法修改源码的情况，比如使用的 alisdk 依赖 guzzle 我们不想去修改 alisdk 的源码

在项目的 `composer.json` 文件中增加 `extra` 配置项，如下：

```
"extra": {
    "include_files": [
      "vendor/mix/guzzle/src/hook.php"
    ]
}
```

更新自动加载：

```
composer dump-autoload
```

## 原理

因为 Swoole 的 Hook 只支持 PHP Stream，Guzzle 库默认是使用 CURL 扩展，导致无法 Hook 为协程，本库修改了 Guzzle 的默认 Handler 为 StreamHandler，让依赖 Guzzle 的第三方库无需修改代码即可使用 Swoole 协程。

## 支持的第三方库

理论上基于 Guzzle 库开发的 SDK 都可使用本库 Hook，下面是已知的支持 Hook 的第三方库清单：

> 欢迎提交 PR 更新此清单

- [alibabacloud/client](https://github.com/aliyun/openapi-sdk-php-client)
- [TencentCloud/tencentcloud-sdk-php](https://github.com/TencentCloud/tencentcloud-sdk-php)

## License

Apache License Version 2.0, http://www.apache.org/licenses/
