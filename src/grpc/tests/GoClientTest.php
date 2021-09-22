<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GoClientTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $grpc = new Mix\Grpc\Server();
            $grpc->register(SayService1::class);
            $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9597, false);
            $server->set([
                'open_http2_protocol' => true,
                'http_compression' => false,
            ]);
            $server->handle('/', $grpc->handler());
            go(function () use ($server) {
                $server->start();
            });

            $result = exec('cd tests/goclient; go run *.go');
            $_this->assertEquals($result, 'hello, xiaoming');
            $server->shutdown();
        };
        run($func);
    }

}

class SayService1 implements \Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(\Mix\Grpc\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}
