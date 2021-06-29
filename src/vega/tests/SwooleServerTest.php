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

        $vega = new Engine();

        $vega->use(function (Context $ctx) use ($_this) {
            $_this->assertTrue(true);
            $ctx->next();
        });

        $vega->handleF('/hello', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/hello');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');

        $sub = $vega->pathPrefix('/foo');
        $sub->handleF('/hello', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/foo/hello');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');
        $sub->handleF('/hello1', function (Context $ctx) use ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/foo/hello1');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');

        swoole_run($vega);
    }

}
