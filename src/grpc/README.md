> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix gRPC

PHP gRPC based on Swoole coroutine, including protoc code generator, server, and client

基于 Swoole 协程的 PHP gRPC 库，包含 protoc 代码生成器、服务器、客户端

## Overview

由于 PHP-FPM 的特殊生命周期，导致 PHP 的 gRPC 官方代码生成器只能生成数据结构和客户端代码，无法像 golang/node.js/python 一样能同时生成服务器代码；传统方式如果要搭建 PHP gRPC 服务器只能借助
nginx+h2+phpfpm 来搭建，这样就不需要 server 代码了，但是短生命周期又无法很好的支持服务注册，因为这些原因导致 PHP 在 gRPC 中一直都是充当 Client 的角色，Mix gRPC 提供了基于 Swoole
的方案：

- 使用 [Swoole](https://github.com/swoole/swoole-src) 作为 gRPC Server
- 使用 Golang 打造的 protoc-gen-mix 来生成 service 的 server/client 代码
- 完全独立，可在任何 CLI 模式的 php 代码中执行，任何框架的 CLI 模式中执行 Laravel、ThinkPHP、MixPHP 等都可以
- 同时为了降低门槛，我已经将 protoc、protoc-gen-mix 文件编译好了 win、linux、macOS 三个系统的二进制文件，直接下载即可

让 PHP 编写 gRPC 和 Golang 一样方便快捷，同时性能强劲

**推荐搭配以下数据库使用 (支持协程和连接池)：**

- https://github.com/mix-php/database
- https://github.com/mix-php/redis

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a)
, [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)
敲门暗号：grpc

## Installation

- Swoole >= 4.4.4: https://wiki.swoole.com/#/environment
- 需要开启 `--enable-http2`

```
composer require mix/grpc
```

## 下载 protoc 与相关 plugin

- [protoc](https://github.com/protocolbuffers/protobuf) 是 protobuf 数据结构代码生成器，负责将 .proto 数据结构文件生成为对应语言的 class、struct
  供程序使用，
- [protoc-gen-mix](https://github.com/mix-php/grpc/tree/master/protoc-gen-mix) 是 mix 开发的 protoc 插件，用来生成 service 的
  server/client 代码。

以上 2 个二进制文件，我都帮你们编译好了，包含多个常用 OS 类型，直接下载即可：

- [下载 protoc_mix_plugin](https://github.com/mix-php/grpc/releases/tag/binary-210714) `win/macos/linux`

下载完成后 linux、macOS 将二进制文件放入系统 `/usr/local/bin` 目录，win 放入 `C:\WINDOWS\system32`

## 通过 .proto 生成 PHP 代码

首先我们编写一个 proto 文件：

```
syntax = "proto3";

package php.micro.grpc.greeter;

service Say {
	rpc Hello(Request) returns (Response) {}
}

message Request {
	string name = 1;
}

message Response {
	string msg = 1;
}
```

然后使用 protoc 生成代码：

```
protoc --php_out=. --mix_out=. greeter.proto
```

执行命令后将在当前目录生成以下文件：

```
|-- GPBMetadata
|   `-- Greeter.php
|-- Php
|   `-- Micro
|       `-- Grpc
|           `-- Greeter
|               |-- Request.php
|               |-- Response.php
|               |-- SayClient.php
|               `-- SayInterface.php
`-- greeter.proto
```

其中 Request.php、Response.php 为 `--php_out` 生成，SayClient.php SayInterface.php 由 `--mix_out` 生成。

接下来我们将生成的文件加入到 composer autoload 中，我们修改 composer.json：

```
"autoload-dev": {
    "psr-4": {
        "GPBMetadata\\": "protodir/GPBMetadata/",
        "Php\\": "protodir/Php/"
    }
}
```

修改后执行 `composer dump-aotoload` 使其生效。

## 编写一个 gRPC 服务

我们用原生 PHP 代码来编写一个 gRPC 服务器：

```php
// 编写一个服务，实现 protoc-gen-mix 生成的接口
class SayService implements Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(Mix\Grpc\Context $context, Php\Micro\Grpc\Greeter\Request $request): Php\Micro\Grpc\Greeter\Response
    {
        $response = new Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}

$grpc = new Mix\Grpc\Server();
$grpc->register(SayService::class); // or $grpc->register(new SayService());
```

Swoole 多进程 (异步) 中使用

```php
$http = new Swoole\Http\Server('0.0.0.0', 9595);
$http->on('Request', $grpc->handler());
$http->set([
    'worker_num' => 4,
    'open_http2_protocol' => true,
    'http_compression' => false,
]);
$http->start();
```

开启多进程协程

```php
$http->on('Request', $grpc->handler());
$http->on('WorkerStart', function ($server, $workerId) {
    // 协程初始化
    // 比如：启动 mix/database mix/redis 的连接池
});
$http->set([
    'enable_coroutine' => true,
    'worker_num' => 4,
    'open_http2_protocol' => true,
    'http_compression' => false,
]);
```

Swoole 单进程 (协程) 中使用

```php
Swoole\Coroutine\run(function () use ($grpc) {
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9595, false);
    $server->handle('/', $grpc->handler());
    $server->set([
      'open_http2_protocol' => true,
      'http_compression' => false,
    ]);
    $server->start();
});
```

## 客户端调用一个 gRPC 服务

通过 IP 端口调用 gRPC 服务，**注意：请提前预加载客户端，不要在并发请求中实例化**

```php
Swoole\Coroutine\run(function () {
    $client = new Mix\Grpc\Client('127.0.0.1', 9595); // 复用该客户端
    $say  = new Php\Micro\Grpc\Greeter\SayClient($client);
    $request = new Php\Micro\Grpc\Greeter\Request();
    $request->setName('xiaoming');
    $ctx = new Mix\Grpc\Context();
    $response = $say->Hello($ctx, $request);
    var_dump($response->getMsg());
    $client->close(); // 使用完必须关闭，否则会残留在内存
});
```

设置 `header`

```php
$ctx->withHeader('foo', 'bar');
$response = $say->Hello($ctx, $request);
```

设置 `timeout`

```php
$ctx->withTimeout(5.0);
$response = $say->Hello($ctx, $request);
```

## FPM 如何调用 gRPC 服务

像我们传统 PHP FPM 模式中，我们作为客户端调用 gRPC 比 Mix gRPC 提供的客户端要复杂很多，但是我们也经常需要用到，比如在 thinkphp laravel 中调用 Mix gRPC 或者 Mix Go 编写的 gRPC
服务，推荐阅读以下文章：

- [gRPC入坑记](https://www.cnblogs.com/52fhy/p/11110704.html#php%E7%9B%B8%E5%85%B3%E6%94%AF%E6%8C%81)
- [PHP中使用gRPC客户端](https://bbs.huaweicloud.com/blogs/135609)

网上的文章都缺少重要的一环，就是：

```
protoc --php_out=. greeter.proto
```

命令执行时，只会生成数据结构的 class 文件，不会生成 grpc 服务的客户端 class 文件

```
service Say {
	rpc Hello(Request) returns (Response) {}
}
```

以上服务没有被处理，没有生成出 `SayClient.php` ，需要修改编译命令

```
protoc --php_out=. --grpc_out=. --plugin=protoc-gen-grpc=/path/grpc_php_plugin greeter.proto
```

命令中指定了一个 `grpc_php_plugin` 文件是由 [grpc/grpc](https://github.com/grpc/grpc/tree/master/src/php) 提供的源码，官方没有像 `protoc`
一样提供编译好的二进制可以下载，只能自己编译。然而这个库依赖的大量的子仓库，在国内几乎无法拉取成功，其次 win 的 cmake 编译很多人不会弄，导致大量的人无法编译出这个文件，因此我这里直接提供编译好的二进制供大家下载。

- [下载 protoc_grpc_plugin](https://github.com/mix-php/grpc/releases/tag/binary-210714) `win/macos/linux`

## License

Apache License Version 2.0, http://www.apache.org/licenses/
