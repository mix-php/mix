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
            $vega->handleFunc('/websocket', function (Mix\Vega\Context $ctx) use ($upgrader) {
                $conn = $upgrader->upgrade($ctx->request, $ctx->response);

                $in = $conn->readMessage();
                $out = new \Swoole\WebSocket\Frame();
                $out->data = sprintf('hello, %s', $in->data);
                $conn->writeMessage($out);
                $conn->close();
            })->methods('GET');

            $server = new Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);
            $server->handle('/', $vega->handler());
            go(function () use ($server) {
                $server->start();
            });

            $conn = new \Mix\WebSocket\Client('ws://127.0.0.1:9502/websocket');

            $frame = new \Swoole\WebSocket\Frame();
            $frame->data = 'xiaoming';
            $conn->writeMessage($frame);
            $frame = $conn->readMessage();
            $conn->close();

            $_this->assertEquals($frame->data, 'hello, xiaoming');

            $server->shutdown();
        };
        run($func);
    }

    public function __testRawServerClient(): void
    {
        $_this = $this;
        $func = function () use ($_this) {
            $upgrader = new Mix\WebSocket\Upgrader();

            $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9502, false);
            $server->handle('/websocket', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($upgrader) {
                $conn = $upgrader->upgradeRaw($request, $response);

                $in = $conn->readMessage();
                $out = new \Swoole\WebSocket\Frame();
                $out->data = sprintf('hello, %s', $in->data);
                $conn->writeMessage($out);
                $conn->close();
            });

            go(function () use ($server) {
                $server->start();
            });

            $conn = new \Mix\WebSocket\Client('ws://127.0.0.1:9502/websocket');

            $frame = new \Swoole\WebSocket\Frame();
            $frame->data = 'xiaoming';
            $conn->writeMessage($frame);
            $frame = $conn->readMessage();
            $conn->close();

            $_this->assertEquals($frame->data, 'hello, xiaoming');

            $server->shutdown();
        };
        run($func);
    }

}
