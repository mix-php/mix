> Produced by OpenMix：[https://openmix.org](https://openmix.org/mix-php)

# Mix Vega

[中文](README.md) | English

Vega is a CLI mode HTTP web framework written in PHP support Swoole, WorkerMan, FPM, CLI-Server

Vega 是一个用 PHP 编写的 CLI 模式 HTTP 网络框架，支持 Swoole、WorkerMan、FPM、CLI-Server

## Overview

Vega is the core component of [MixPHP](https://github.com/mix-php/mix) `V3+` (which can be used independently). It is
developed by referring to golang [gin](https://github.com/gin-gonic/gin) [mux](https://github.com/gorilla/mux). It
contains a lot of functions for Web application processing (excluding database processing), including: routing,
rendering, parameter acquisition, middleware, file uploading, static file processing, strong compatibility with CLI mode, and support
for Swoole, WorkerMan, FPM, CLI-Server and Swoole's various process models.

**Recommended with the following databases:**

- https://github.com/mix-php/database
- https://github.com/mix-php/redis
- https://github.com/top-think/think-orm
- https://github.com/illuminate/database

## Installation

> Need to install [Swoole](https://wiki.swoole.com/#/environment) or [WorkerMan](http://doc.workerman.net/install/requirement.html)

```
composer require mix/vega
```

## Quick start

- Swoole is used in multiple (asynchronous) processes

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http = new Swoole\Http\Server('0.0.0.0', 9501);
$http->on('Request', $vega->handler());
$http->set([
    'worker_num' => 4,
]);
$http->start();
```

Start a multi-process coroutine

```php
$http->on('Request', $vega->handler());
$http->on('WorkerStart', function ($server, $workerId) {
    // 协程初始化
    // 比如：启动 mix/database mix/redis 的连接池
});
$http->set([
    'enable_coroutine' => true,
    'worker_num' => 4,
]);
```

```
php swoole.php
```

- Swoole is used in a single process (coroutine)

```php
<?php
require __DIR__ . '/vendor/autoload.php';

Swoole\Coroutine\run(function () {
    $vega = new Mix\Vega\Engine();
    $vega->handle('/hello', function (Mix\Vega\Context $ctx) {
        $ctx->string(200, 'hello, world!');
    })->methods('GET');
    
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9502, false);
    $server->handle('/', $vega->handler());
    $server->start();
});
```

```
php swooleco.php
```

- Used in the WorkerMan

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');

$http_worker = new Workerman\Worker("http://0.0.0.0:2345");
$http_worker->onMessage = $vega->handler();
$http_worker->count = 4;
Workerman\Worker::runAll();
```

```
php workerman.php start
```

- Used in PHP-FPM

In `nginx` configure `rewrite` to `index.php`

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
$vega->run();
```

- Used in PHP [cli-server](https://www.php.net/manual/zh/features.commandline.webserver.php)

This built-in Web server is primarily used for local development and is not available in an online production environment.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
$vega->run();
```

```
php -S localhost:8000 router.php
```

- Request to test

~~~
% curl http://127.0.0.1:9501/hello
hello, world!
~~~

## Route configure

Configure the `Closure` route

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

Configure the `callable` route

```php
class Hello {
    public function index(Mix\Vega\Context $ctx) {
        $ctx->string(200, 'hello, world!');
    }
}
$vega = new Mix\Vega\Engine();
$vega->handle('/hello', [new Hello(), 'index'])->methods('GET');
```

Configure Routing Variables

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/users/{id:\d+}', function (Mix\Vega\Context $ctx) {
    $id = $ctx->param('id');
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

Configure multiple `method`

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/hello', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET', 'POST');
```

## Route prefix (group)

```php
$vega = new Mix\Vega\Engine();
$sub = $vega->pathPrefix('/foo');
$sub->handle('/bar1', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
$sub->handle('/bar2', function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello1, world!');
})->methods('GET');
```

## Parameters

### Request parameters

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->request: ServerRequestInterface | 符合PSR的请求对象 |
| $ctx->response: ResponseInterface | 符合PSR的响应对象 |
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
| $ctx->method(): string  | 请求类型 |
| $ctx->contentType(): string  | 文档类型 |
| $ctx->header(string $key): string  | 请求头 |
| $ctx->cookie(string $name): string  | cookies |
| $ctx->uri(): UriInterface  | 完整uri |
| $ctx->rawData(): string  | 原始包数据 |

### IP

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->clientIP(): string  | 从反向代理获取用户真实IP |
| $ctx->remoteIP(): string  | 获取远程IP |

## Upload file processing

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->formFile(string $name): UploadedFileInterface  | 获取上传的第一个文件 |
| $ctx->multipartForm(): UploadedFileInterface[]  | 获取上传的全部文件 |

File save

```php
$file = $ctx->formFile('img');
$targetPath = '/data/project/public/uploads/' . $file->getClientFilename();
$file->moveTo($targetPath);
```

## Request context

Some information needs to be saved in the request, such as session, JWT payload, etc.

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->set(string $key, $value): void  | 设置值 |
| $ctx->get(string $key): mixed or null  | 获取值 |
| $ctx->mustGet(string $key): mixed or throws  | 获取值或抛出异常 |

## Interrupt execution

After `abort` is executed, all subsequent code, including middleware, will be stopped.

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->abort(): void  | 中断，需自行处理响应 |
| $ctx->abortWithStatus(int $code): void  | 中断并响应状态码 |
| $ctx->abortWithStatusJSON(int $code, $data): void  | 中断并响应JSON |

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/users/{id}', function (Mix\Vega\Context $ctx) {
    if (true) {
        $ctx->string(401, 'Unauthorized');
        $ctx->abort();
    }
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

## Response processing

|  方法名称   | 描述  |
|  ----  | ----  |
| $ctx->status(int $code): void  | 设置状态码 |
| $ctx->setHeader(string $key, string $value): void  | 设置header |
| $ctx->setCookie(string $name, string $value, int $expire = 0, ...): void  | 设置cookie |
| $ctx->redirect(string $location, int $code = 302): void  | 重定向 |

### Redirect

```php
$ctx->redirect('https://www.baidu.com/');
$ctx->abort();
```

## JSON request and output

Get JSON request data

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/users', function (Mix\Vega\Context $ctx) {
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

`mustGetJSON` comes with a validity check, the following code is equivalent to the above

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/users', function (Mix\Vega\Context $ctx) {
    $obj = $ctx->mustGetJSON();
    var_dump($obj);
    $ctx->JSON(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('POST');
```

### JSONP processing

```php
$vega = new Mix\Vega\Engine();
$vega->handle('/jsonp', function (Mix\Vega\Context $ctx) {
    $ctx->JSONP(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('GET');
```

## HTML view rendering

Create view file `foo.php`

```php
<p>id: <?= $id ?>, name: <?= $name ?></p>
<p>friends:</p>
<ul>
    <?php foreach($friends as $name): ?>
        <li><?= $name ?></li>
    <?php endforeach; ?>
</ul>
```

Import other Views

```php
<?= $this->render('header', $__data__); ?>
```

Configure the view path and respond to html

```php
$vega = new Mix\Vega\Engine();
$vega->withHTMLRoot('/data/project/views');
$vega->handle('/html', function (Mix\Vega\Context $ctx) {
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

## Static file handling

Based on `sendfile` zero copy, not supported in `PHP-FPM`

```php
$vega = new Mix\Vega\Engine();
$vega->static('/static', '/data/project/public/static');
$vega->staticFile('/favicon.ico', '/data/project/public/favicon.ico');
```

## Setup middleware

Configure middleware for a route, you can configure multiple

```php
$vega = new Mix\Vega\Engine();
$func = function (Mix\Vega\Context $ctx) {
    // do something
    $ctx->next();
};
$vega->handle('/hello', $func, function (Mix\Vega\Context $ctx) {
    $ctx->string(200, 'hello, world!');
})->methods('GET');
```

Configure the global middleware, it will be executed even if the route is not matched

```php
$vega = new Mix\Vega\Engine();
$vega->use(function (Mix\Vega\Context $ctx) {
    $ctx->next();
});
```

Before middleware

```php
$vega->use(function (Mix\Vega\Context $ctx) {
    // do something
    $ctx->next();
});
```

After middleware

```php
$vega->use(function (Mix\Vega\Context $ctx) {
    $ctx->next();
    // do something
});
```

### 404 Custom response

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

### 500 Global exception capture

```php
$vega = new Mix\Vega\Engine();
$vega->use(function (Mix\Vega\Context $ctx) {
    try{
        $ctx->next();
    } catch (\Throwable $ex) {
        if ($ex instanceof Mix\Vega\Abort || $ex instanceof Mix\Vega\Exception\NotFoundException) {
            throw $ex;
        }
        $ctx->string(500, 'New 500 response');
        $ctx->abort();
    }
});
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
