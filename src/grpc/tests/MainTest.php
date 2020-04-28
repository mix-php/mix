<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $server = new \Mix\Grpc\Server('0.0.0.0', 9595);
            $server->register(SayService::class);
            go(function () use ($server) {
                $server->start();
            });

            for ($i = 0; $i < 10; $i++) {
                go(function () use ($i) {
                    $dialer = new \Mix\Grpc\Client\Dialer();
                    /** @var \Php\Micro\Grpc\Greeter\SayClient $client */
                    $client  = $dialer->dial('127.0.0.1', 54953, \Php\Micro\Grpc\Greeter\SayClient::class);
                    $request = new \Php\Micro\Grpc\Greeter\Request();
                    $request->setName('xiaoming');
                    $response = $client->Hello($request);
                });
            }

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
