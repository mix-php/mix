<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testServerClient(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $grpc = new Mix\Grpc\Server();
            $grpc->register(SayService::class);
            $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9595, false);
            $server->set([
                'open_http2_protocol' => true,
                'http_compression' => false,
            ]);
            $server->handle('/', $grpc->handler());
            go(function () use ($server) {
                $server->start();
            });

            $client    = new Mix\Grpc\Client('127.0.0.1', 9595);
            $say  = new \Php\Micro\Grpc\Greeter\SayClient($client);
            $request = new \Php\Micro\Grpc\Greeter\Request();
            $request->setName('xiaoming');
            $response = $say->Hello(new \Mix\Grpc\Context(), $request);

            $_this->assertEquals($response->getMsg(), 'hello, xiaoming');
            
            $server->shutdown();
        };
        run($func);
    }

}

class SayService implements \Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(\Mix\Grpc\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}
