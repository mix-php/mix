<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testServerClient(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server   = new Mix\Http\Server\Server('0.0.0.0', 9597, false, false);
            $upgrader = new \Mix\WebSocket\Upgrader();
            $server->handle('/websocket', function (\Mix\Http\Message\ServerRequest $request, \Mix\Http\Message\Response $response) use ($upgrader) {
                $conn = $upgrader->Upgrade($request, $response);

                $in        = $conn->recv();
                $out       = new \Swoole\WebSocket\Frame();
                $out->data = sprintf('hello, %s', $in->data);
                $conn->send($out);
                $conn->close();

                //$response->send();
            });
            go(function () use ($server) {
                $server->start();
            });

            $dialer = new \Mix\WebSocket\Client\Dialer();
            $conn   = $dialer->dial('ws://127.0.0.1:9597/websocket');

            $frame       = new \Swoole\WebSocket\Frame();
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
