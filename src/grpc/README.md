## Mix Grpc

PHP gRPC based on Swoole coroutine, including protoc code generator, server, and client

基于 Swoole 协程的 PHP gRPC 库，包含 protoc 代码生成器、服务器、客户端

## Overview

由于 PHP-FPM 的特殊生命周期，导致 PHP 的 gRPC 官方代码生成器只能生成数据结构和客户端代码，无法像 golang/node.js/python 一样能同时生成服务器代码；传统方式如果要搭建 PHP gRPC
服务器只能借助 nginx+h2+phpfpm 来搭建，这样就不需要 server 代码了，但是短生命周期又无法很好的支持服务注册，因为这些原因导致 PHP 在 gRPC 中一直都是充当 Client 的角色，Mix gRPC 提供了基于 Swoole 的方案：

- 使用 [Swoole](https://github.com/swoole/swoole-src) 作为 gRPC Server
- 使用 Golang 打造的 protoc-gen-mix 来生成 service 的 server/client 代码
- 完全独立，可在任何 CLI 模式的 php 代码中执行，任何框架的 CLI 模式中执行 Laravel、ThinkPHP、MixPHP 等都可以
- 同时为了降低门槛，我已经将 protoc、protoc-gen-mix 文件编译好了 win、linux、macOS 三个系统的二进制文件，直接下载即可

让 PHP 编写 gRPC 和 Golang 一样方便快捷，同时性能强劲

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

- [https://github.com/mix-php/grpc/releases/tag/binary](https://github.com/mix-php/grpc/releases/tag/binary)

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

- Linux/Mac

```
protoc --php_out=. --mix_out=. greeter.proto
```

- Win

```
protoc.exe --php_out=. --mix_out=. greeter.proto
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
        "GPBMetadata\\": "protos/GPBMetadata/",
        "Php\\": "protos/Php/"
    }
}
```

修改后执行 `composer dump-aotoload` 使其生效。

## 编写一个 gRPC 服务

我们用原生 PHP 代码来编写一个 gRPC 服务器：

```php
// 编写一个服务，实现 protoc-gen-mix 生成的接口
class SayService implements \Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(\Mix\Context\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}

$grpc = new Mix\Grpc\Server();
$grpc->register(SayService::class); // or $grpc->register(new SayService());

$http = new Swoole\Http\Server('0.0.0.0', 9595);
$http->set([
    'open_http2_protocol' => true,
    'http_compression' => false,
]);
$http->on('Request', $grpc->handler());
$http->start();
```

## 客户端调用一个 gRPC 服务

通过 IP 端口调用 gRPC 服务

```php
$client    = new Mix\Grpc\Client('127.0.0.1', 9595);
$ctx = $client->context();

$say  = new Php\Micro\Grpc\Greeter\SayClient($client);

$request = new Php\Micro\Grpc\Greeter\Request();
$request->setName('xiaoming');
$response = $say->Hello($ctx, $request);

var_dump($response->getMsg());
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
