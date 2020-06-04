<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server = new Mix\Http\Server\Server('0.0.0.0', 9596, false, false);
            $server->handle('/', function (\Mix\Http\Message\ServerRequest $request, \Mix\Http\Message\Response $response) {
                $response->withBody(new \Mix\Http\Message\Stream\ContentStream('hello, world!'));
                $response->send();
            });
            go(function () use ($server) {
                $server->start();
            });

            $handler = new \Mix\Guzzle\Handler\StreamHandler();
            $stack   = \GuzzleHttp\HandlerStack::create($handler);
            $client  = new \GuzzleHttp\Client([
                'handler' => $stack,
            ]);
            $resp    = $client->get('http://127.0.0.1:9596/');
            $body    = $resp->getBody()->__toString();

            $_this->assertContains('hello, world!', $body);

            $server->shutdown();
        };
        run($func);
    }

}
