<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Vega\Engine;
use Mix\Vega\Context;

final class SwooleTest extends TestCase
{

    public function test(): void
    {
        $vega = new Engine();
        $vega->handleF('/hello', function (Context $ctx) {
            $ctx->string(200, 'hello, world!');
        })->methods('GET');
        swoole_run($vega);
    }

}
