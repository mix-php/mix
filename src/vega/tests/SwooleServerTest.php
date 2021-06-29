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

        $vega->handleF('/hello', function (Context $ctx) use  ($_this) {
            $_this->assertEquals($ctx->uri()->__toString(), 'http://0.0.0.0:9501/hello');
            $ctx->string(200, 'hello, world!');
        })->methods('GET');



        swoole_run($vega);
    }

}
