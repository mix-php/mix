# Mix Vega

Vega is a CLI HTTP web framework written in PHP support Swoole, WorkerMan

Vega 是一个用 PHP 编写的 CLI HTTP 网络框架，支持 Swoole、WorkerMan

## Overview

Vega is the core component of [MixPHP](https://github.com/mix-php/mix) `V3+` (which can be used independently). It is
developed by referring to golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux). It
contains a lot of functions for Web application processing (excluding database processing), including: routing,
rendering, parameter acquisition, middleware, file upload processing, strong compatibility with CLI mode, and support
for Swoole, Workerman, and Swoole's various process models.

Vega 是 [MixPHP](https://github.com/mix-php/mix) `V3+` 内置的最核心的组件 (可独立使用)，参考
golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux) 开发，它包含 Web 应用处理的大量功能 (数据库处理除外)
，包括：路由、渲染、参数获取、中间件、文件上传处理等；具有 CLI 模式下强大的兼容性，同时支持 Swoole、WorkerMan, 并且支持 Swoole 的多种进程模型。

## Installation

> 需要先安装 [Swoole](https://wiki.swoole.com/#/environment) 或者 [WorkerMan](http://doc.workerman.net/install/requirement.html)

```
composer require mix/vega
```

## Quick start

Swoole 多进程 (异步) 中使用

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http = new Swoole\Http\Server('0.0.0.0', 9501);
$http->on('Request', $vega->handler());
$http->start();
```

Swoole 单进程 (协程) 中使用

```php

Swoole\Coroutine\run(function () {
    $vega = new Mix\Vega\Engine();
    $vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
        $ctx->string(200, 'hello, world!');
    })->methods('GET');
    
    $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
    $server->handle('/', $vega->handler());
    $server->start();
});
```

WorkerMan 中使用

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http_worker = new Workerman\Worker("http://0.0.0.0:2345");
$http_worker->onMessage = $vega->handler();
$http_worker->count = 4;
Workerman\Worker::runAll();
```

访问测试

~~~
% curl http://0.0.0.0:9501/hello
hello, world!
~~~

## 路由配置

配置闭包路由

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

配置 `callable` 路由

```php
class Hello {
    public function index(Mix\Vega\Context $ctx) {
        $ctx->string(200, 'hello, world!');
    }
}
$vega = new Mix\Vega\Engine();
$vega->handleC('/hello', [new Hello(), 'index'])->methods('GET');
```

配置路由变量

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/users/{id}', function (Mix\Vega\Context $ctx) {
    $id = $ctx->param('id');
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

配置多个 `method`

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET', 'POST');
```

## 路由前缀 (分组)

```php
$vega = new Mix\Vega\Engine();
$subrouter = $vega->pathPrefix('/foo');
$subrouter->handleF('/bar1', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
$subrouter->handleF('/bar2', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello1, world!');
})->methods('GET');
```

## 参数获取

### 请求参数

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->param(string $key): string  | 获取路由参数 |
| $ctx->query(string $key): string  | 获取url参数，包含路由参数 |
| $ctx->defaultQuery(string $key, string $default): string  | 获取url参数，可配置默认值 |
| $ctx->getQuery(string $key): string or null  | 获取url参数, 可判断是否存在 |
| $ctx->postForm(string $key): string  | 获取post参数 |
| $ctx->defaultPostForm(string $key, string $default): string  | 获取post参数，可配置默认值 |
| $ctx->getPostForm(string $key): string or null  | 获取post参数，可判断是否存在 |

### Headers, Cookies, Uri ...

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->contentType(): string  | 请求类型 |
| $ctx->header(string $key): string  | 请求头 |
| $ctx->cookie(string $name): string  | cookies |
| $ctx->uri(): UriInterface  | 完整uri |
| $ctx->rawData(): string  | 原始包数据 |

### 客户端IP

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->clientIP(): string  | 从反向代理获取用户真实IP |
| $ctx->remoteIP(): string  | 获取远程IP |

## 上传文件处理

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->formFile(string $name): UploadedFileInterface  | 获取上传的第一个文件 |
| $ctx->multipartForm(): UploadedFileInterface[]  | 获取上传的全部文件 |

文件保存

```php
$file = $ctx->formFile('img');
$targetPath = '/data/uploads/' . $file->getClientFilename();
$file->moveTo($targetPath);
```

## 请求上下文

请求当中需要保存一些信息，比如：会话、JWT载荷等。

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->set(string $key, $value): void  | 设置值 |
| $ctx->get(string $key): mixed or null  | 获取值 |
| $ctx->mustGet(string $key): mixed or throws  | 获取值或抛出异常 |

## 中断执行

`abort` 执行后，会停止执行后面的全部代码，包括中间件。

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/users/{id}', function (Mix\Vega\Context $ctx) {
    if (true) {
        $ctx->string(401, 'Unauthorized');
        $ctx->abort();
    }
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

## 响应处理

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->status(int $code): void  | 设置状态码 |
| $ctx->setHeader(string $key, string $value): void  | 设置header |
| $ctx->setCookie(string $name, string $value, int $expire = 0, ...): void  | 设置cookie |
| $ctx->redirect(string $location, int $code = 302): void  | 重定向 |

## JSON 请求与输出

获取 JSON 请求数据

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/users', function (Mix\Vega\Context $ctx) {
    $obj = $ctx->getJSON();
    if (!$obj) {
        throw new \Exception('Parameter error');
    }
    var_dump($obj);
    $ctx->JSON(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('POST');
```

`mustGetJSON` 自带有效性检查，以下代码等用于上面

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/users', function (Mix\Vega\Context $ctx) {
    $obj = $ctx->mustGetJSON();
     var_dump($obj);
    $ctx->JSON(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('POST');
```

### JSONP 处理

```php
$vega = new Mix\Vega\Engine();
$vega->handleF('/jsonp', function (Mix\Vega\Context $ctx) {
    $ctx->JSONP(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('GET');
```

## 设置中间件

配置全局中间件，即便没有匹配到路由也会执行

```php
$vega = new Mix\Vega\Engine();
$vega->use(function (Mix\Vega\Context $ctx) {
    $ctx->next();
});
```

前置中间件

```php
$vega->use(function (Mix\Vega\Context $ctx) {
    // do something
    $ctx->next();
});
```

后置中间件

```php
$vega->use(function (Mix\Vega\Context $ctx) {
    $ctx->next();
    // do something
});
```

给某个路由配置中间件

```php
$vega = new Mix\Vega\Engine();
$func = function (Mix\Vega\Context $ctx) {
    // do something
    $ctx->next();
};
$vega->handleF('/hello', $func, function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

### 404 自定义

```php
$vega = new Mix\Vega\Engine();
$vega->use(function (Mix\Vega\Context $ctx) {
    try{
        $ctx->next();
    } catch (Mix\Vega\Exception\NotFoundException $ex) {
        $ctx->string(404, 'New 404 response');
        $ctx->abort();
    }
});
```

### 500 全局异常捕获

```php
$vega = new Mix\Vega\Engine();
$vega->use(function (Mix\Vega\Context $ctx) {
    try{
        $ctx->next();
    } catch (\Throwable $ex) {
        $ctx->string(500, 'New 500 response');
        $ctx->abort();
    }
});
```

## HTML 视图渲染

创建视图文件 `foo.php`

```php
<p>id: <?= $id ?>, name: <?= $name ?></p>
<p>friends:</p>
<ul>
    <?php foreach($friends as $name): ?>
        <li><?= $name ?></li>
    <?php endforeach; ?>
</ul>
```

配置视图路径，并响应html

```php
$vega = new Mix\Vega\Engine();
$vega->withHTMLRoot('/data/project/views');
$vega->handleF('/html', function (Mix\Vega\Context $ctx) {
    $ctx->HTML(200, 'foo', [
        'id' => 1000,
        'name' => '小明',
        'friends' => [
            '小花',
            '小红'
        ]
    ]);
})->methods('GET');
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
