<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Response;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server     = new Mix\Http\Server\Server('0.0.0.0', 9596, false, false);
            $middleware = [];
            $callback   = function (\Mix\FastRoute\RouteCollector $collector) {
                $collector->get('/foo', [new Controller, 'foo'], []);
                $collector->group('/foo1', function (\Mix\FastRoute\RouteCollector $collector) {
                    $collector->post('/bar/{id}', [new Controller, 'bar'], []);
                }, []);
            };
            $router     = new \Mix\FastRoute\Router($middleware);
            $router->parse($callback);
            go(function () use ($server, $router) {
                $server->start($router);
            });

            $handler = new \Mix\Guzzle\Handler\StreamHandler();
            $stack   = \GuzzleHttp\HandlerStack::create($handler);
            $client  = new \GuzzleHttp\Client([
                'handler' => $stack,
            ]);

            $resp = $client->get('http://127.0.0.1:9596/foo');
            $_this->assertContains('foo', $resp->getBody()->getContents());

            $resp = $client->post('http://127.0.0.1:9596/foo1/bar/123');
            $_this->assertContains('bar', $resp->getBody()->getContents());
            
            $server->shutdown();
        };
        run($func);
    }

}

class Controller
{
    public function foo(ServerRequest $request, Response $response)
    {
        $response->withBody(new \Mix\Http\Message\Stream\ContentStream('foo'));
        return $response;
    }

    public function bar(ServerRequest $request, Response $response)
    {
        $response->withBody(new \Mix\Http\Message\Stream\ContentStream('bar'));
        return $response;
    }
}
