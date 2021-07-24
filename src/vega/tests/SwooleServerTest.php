<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Vega\Engine;
use Mix\Vega\Context;

final class SwooleServerTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $_this->assertTrue(true);

        $vega = new Engine();

        // view
        $vega->withHTMLRoot(__DIR__ . '/views');

        // 中间件
        $vega->use(function (Context $ctx) use ($_this) {
            $_this->assertTrue(true);
            $ctx->next();
        });

        // 多个方法
        $vega->handle('/hello', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/hello');
            if ($ctx->request->getMethod() == 'POST') {
                $_this->assertEquals($ctx->postForm('user'), 'abc');
                $_this->assertEquals($ctx->postForm('password'), '123');
            }
            $ctx->string(200, 'hello, world!');
        })->methods('GET', 'POST');

        // callable
        $vega->handle('/hello1', [new Hello(), 'index'])->methods('GET');

        // 分组
        $subrouter = $vega->pathPrefix('/foo');
        $subrouter->handle('/hello', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/foo/hello');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');
        $subrouter->handle('/hello1', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/foo/hello1');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');

        // 获取参数
        // curl http://0.0.0.0:9501/users/1000?name=keda
        $vega->handle('/users/{id}', function (Context $ctx) use ($_this) {
            $id = $ctx->param('id');
            $name = $ctx->query('name');
            $_this->assertEquals($id, '1000');
            $_this->assertEquals($name, 'keda');
            $ctx->string(200, 'hello, world!');
        })->methods('GET', 'POST');

        // POST发送JSON
        // curl -H "Content-Type: application/json" -X POST -d '{"user_id": "123", "coin":100}' "http://0.0.0.0:9501/users"
        $vega->handle('/users', function (Context $ctx) use ($_this) {
            $obj = $ctx->mustGetJSON();
            $_this->assertEquals($obj->user_id, '123');
            $_this->assertEquals($obj->coin, 100);
            $ctx->JSON(200, [
                'code' => 0,
                'message' => 'ok'
            ]);
        })->methods('POST');

        // 视图
        // curl http://0.0.0.0:9501/html
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

        swoole_run($vega);
    }

}

class Hello
{
    public function index(Mix\Vega\Context $ctx)
    {
        $ctx->string(200, 'hello, world!');
    }
}
