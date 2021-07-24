<?php

require __DIR__ . '/../vendor/autoload.php';

use Mix\Vega\Engine;
use Mix\Vega\Context;

$vega = new Engine();

// view
$vega->withHTMLRoot(__DIR__ . '/views');

// 中间件
$vega->use(function (Context $ctx) {
    var_dump('first exec');
    $ctx->next();
});

// 多个方法
// curl "http://0.0.0.0:2345/hello"
$vega->handle('/hello', function (Context $ctx) {
    var_dump($ctx->uri()->__toString());
    $ctx->string(200, 'hello, world!');
})->methods('GET', 'POST');

// handleC
// curl "http://0.0.0.0:2345/hello1"
class Hello
{
    public function index(Mix\Vega\Context $ctx)
    {
        $ctx->string(200, 'hello, world!');
    }
}

$vega->handle('/hello1', [new Hello(), 'index'])->methods('GET');

// 分组
// curl "http://0.0.0.0:2345/foo/hello"
$subrouter = $vega->pathPrefix('/foo');
$subrouter->handle('/hello', function (Context $ctx) {
    var_dump($ctx->uri()->__toString());
    $ctx->string(200, 'hello, world!');
})->methods('GET');
// curl "http://0.0.0.0:2345/foo/hello1"
$subrouter->handle('/hello1', function (Context $ctx) {
    var_dump($ctx->uri()->__toString());
    $ctx->string(200, 'hello, world!');
})->methods('GET');

// 获取参数
// curl "http://0.0.0.0:2345/users/1000?name=keda"
$vega->handle('/users/{id}', function (Context $ctx) {
    $id = $ctx->param('id');
    $name = $ctx->query('name');
    var_dump($id, $name);
    $ctx->string(200, 'hello, world!');
})->methods('GET', 'POST');

// POST发送JSON
// curl -H "Content-Type: application/json" -X POST -d '{"user_id": "123", "coin":100}' "http://0.0.0.0:2345/users"
$vega->handle('/users', function (Context $ctx) {
    $obj = $ctx->mustGetJSON();
    var_dump($obj);
    $ctx->JSON(200, [
        'code' => 0,
        'message' => 'ok'
    ]);
})->methods('POST');

// 视图
// curl http://0.0.0.0:2345/html
$vega->handle('/html', function (Context $ctx) {
    $ctx->HTML(200, 'foo', [
        'id' => 1000,
        'name' => '小明',
        'friends' => [
            '小花',
            '小红'
        ]
    ]);
})->methods('GET');

// 静态文件
$vega->static('/static', __DIR__ . '/public/static');
$vega->staticFile('/favicon.ico', __DIR__ . '/public/favicon.ico');

$http_worker = new Workerman\Worker("http://0.0.0.0:2345");
$http_worker->onMessage = $vega->handler();
$http_worker->count = 4;
Workerman\Worker::runAll();
