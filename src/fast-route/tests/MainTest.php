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
            $call   = function (\Mix\FastRoute\RouteCollector $collector) {
                $collector->get('/foo', [MainTest::class, 'test'], []);
            };
            $router = new \Mix\FastRoute\Router($call);
            go(function () use ($server, $router) {
                $server->start($router);
            });

            
        };
        run($func);
    }

}
