<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HackTest extends TestCase
{

    public function test()
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server = new Mix\Http\Server\Server('0.0.0.0', 9597, false, false);
            $server->handle('/tests/', \Mix\Http\Server\Server::fileServer(__DIR__ . '/..'));
            go(function () use ($server) {
                $server->start();
            });

            $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
            $client->set([
                'open_eof_check' => true,
                'package_eof'    => "\n",
            ]);
            if (!$client->connect('127.0.0.1', 9597)) {
                throw new \Swoole\Exception('connect failed');
            }
            $request = <<<EOF
GET /tests/../composer.json HTTP/1.1
Host: localhost
Accept: */*
User-Agent: curl/7.64.1


EOF;
            $client->send($request);
            while (true) {
                $data = $client->recv(-1);
                if ($data === false || $data === "") {
                    return;
                }
                $_this->assertContains('HTTP/1.1 404 Not Found', $data);
                break;
            }

            $server->shutdown();
        };
        run($func);
    }

}
