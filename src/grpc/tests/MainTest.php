<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testServerClient(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server = new \Mix\Grpc\Server('0.0.0.0', 9595);
            $server->register(SayService::class);
            go(function () use ($server) {
                $server->start();
            });

            $dialer  = new \Mix\Grpc\Client\Dialer();
            $conn    = $dialer->dial('127.0.0.1', 9595);
            $client  = new \Php\Micro\Grpc\Greeter\SayClient($conn);
            $request = new \Php\Micro\Grpc\Greeter\Request();
            $request->setName('xiaoming');
            $response = $client->Hello(new \Mix\Context\Context(), $request);

            $_this->assertEquals($response->getMsg(), 'hello, xiaoming');
            
            $server->shutdown();
        };
        run($func);
    }

}

class SayService implements \Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(\Mix\Context\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}
