<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Vega\Engine;
use Mix\Vega\Context;

final class RunTest extends TestCase
{

    public function testSwoole(): void
    {
        $vega = new Engine();
        $vega->handleF('/hello', function (Context $ctx) {
            $ctx->string('hello, world!');
        })->methods('GET');
        swoole_run($vega);
    }

}
