<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testServerClient(): void
    {
        $_this = $this;
        $func = function () use ($_this) {
            $upgrader = new Mix\WebSocket\Upgrader();

            $vega = new Mix\Vega\Engine();
            $vega->handleFunc('/hello', function (Mix\Vega\Context $ctx) use ($upgrader) {
                $conn = $upgrader->Upgrade($ctx->request, $ctx->response);

                $in = $conn->recv();
                $out = new \Swoole\WebSocket\Frame();
                $out->data = sprintf('hello, %s', $in->data);
                $conn->send($out);
                $conn->close();

            })->methods('GET');

            $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
            $server->handle('/', $vega->handler());
            go(function () use ($server) {
                $server->start();
            });

            $conn = new \Mix\WebSocket\Client('ws://127.0.0.1:9597/websocket');

            $frame = new \Swoole\WebSocket\Frame();
            $frame->data = 'xiaoming';
            $conn->send($frame);
            $frame = $conn->recv();
            $conn->close();

            $_this->assertEquals($frame->data, 'hello, xiaoming');

            $server->shutdown();
        };
        run($func);
    }

}
